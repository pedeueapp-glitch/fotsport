<?php

namespace App\Services;

use Efi\EfiPay;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EfiService
{
    protected $efi;

    public function __construct()
    {
        $certificate = config('services.efi.certificate');
        $certificateBase64 = config('services.efi.certificate_base64');

        if ($certificateBase64) {
            $certificatePath = storage_path('app/efi_certificate.pem');
            $decoded = base64_decode($certificateBase64);

            // Verifica se o certificado já está em formato PEM
            if (strpos($decoded, '-----BEGIN') === false) {
                // Se for binário (P12), tenta converter para PEM
                if (openssl_pkcs12_read($decoded, $certs, '')) {
                    $pemContent = $certs['cert'] . "\n" . $certs['pkey'];
                    if (isset($certs['extracerts']) && count($certs['extracerts']) > 0) {
                        foreach ($certs['extracerts'] as $extra) {
                            $pemContent .= "\n" . $extra;
                        }
                    }
                    file_put_contents($certificatePath, $pemContent);
                } else {
                    // Se não conseguir ler como P12, salva o binário (pode causar erro no cURL)
                    file_put_contents($certificatePath, $decoded);
                    Log::warning('EfiService: Base64 não é PEM e falhou na conversão P12. Verifique se há senha no certificado.');
                }
            } else {
                // Já é PEM, apenas salva
                file_put_contents($certificatePath, $decoded);
            }
        } else {
            $certificatePath = base_path($certificate);
        }

        $options = [
            'client_id' => config('services.efi.client_id'),
            'client_secret' => config('services.efi.client_secret'),
            'sandbox' => (bool) config('services.efi.sandbox'),
            'certificate' => $certificatePath,
        ];

        $this->efi = new EfiPay($options);
    }

    public function createPixPayment($amount, $customerName, $customerCpf)
    {
        try {
            $body = [
                'calendario' => [
                    'expiracao' => 3600 // 1 hour
                ],
                'devedor' => [
                    'cpf' => preg_replace('/[^0-9]/', '', $customerCpf),
                    'nome' => $customerName
                ],
                'valor' => [
                    'original' => number_format($amount, 2, '.', '')
                ],
                'chave' => config('services.efi.pix_key'),
                'solicitacaoPagador' => 'Pagamento de Fotos - Fotsport'
            ];

            $pix = $this->efi->pixCreateImmediateCharge([], $body);

            if (isset($pix['txid'])) {
                $locId = $pix['loc']['id'];
                $qrcode = $this->efi->pixGenerateQRCode(['id' => $locId]);

                return [
                    'txid' => $pix['txid'],
                    'qrcode' => $qrcode['imagemQrcode'],
                    'copy_paste' => $qrcode['qrcode']
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erro Efi Pay: ' . $e->getMessage());
            return null;
        }
    }

    public function getPixStatus($txid)
    {
        try {
            $params = [
                'txid' => $txid
            ];
            $response = $this->efi->pixDetailCharge($params);
            return $response['status'] ?? 'pending';
        } catch (\Exception $e) {
            Log::error('Erro ao consultar status Pix: ' . $e->getMessage());
            return 'error';
        }
    }

    public function registerWebhook($pixKey, $url)
    {
        try {
            $params = [
                'chave' => $pixKey
            ];
            $body = [
                'webhookUrl' => $url
            ];
            $response = $this->efi->pixConfigWebhook($params, $body);
            return $response;
        } catch (\Exception $e) {
            Log::error('Erro ao registrar Webhook Efí: ' . $e->getMessage());
            return null;
        }
    }

    public function sendPix($amount, $pixKey, $pixKeyType, $idEnvio, $description = 'Saque Fotsport')
    {
        try {
            // 1. Formatação da chave do favorecido
            $formattedKey = $pixKey;
            if ($pixKeyType === 'phone') {
                $cleanPhone = preg_replace('/[^0-9]/', '', $pixKey);
                $formattedKey = (strlen($cleanPhone) <= 11) ? '+55' . $cleanPhone : '+' . $cleanPhone;
            } elseif ($pixKeyType === 'cpf' || $pixKeyType === 'cnpj') {
                $formattedKey = preg_replace('/[^0-9]/', '', $pixKey);
            }

            // 2. Gerar o idEnvio único (UUID sem hifens)
            $idEnvio = str_replace('-', '', (string) \Illuminate\Support\Str::uuid());

            // 3. Montar o corpo conforme o exemplo
            $body = [
                'valor' => number_format($amount, 2, '.', ''),
                'pagador' => [
                    'chave' => config('services.efi.pix_key') 
                ],
                'favorecido' => [
                    'chave' => $formattedKey
                ]
            ];

            $certBase64 = config('services.efi.certificate_base64');
            $certPath = storage_path('app/efi_cert.p12');
            if (!file_exists($certPath) || empty(file_get_contents($certPath))) {
                file_put_contents($certPath, base64_decode($certBase64));
            }

            Log::info('Efí Payout - Iniciando Transferência', ['idEnvio' => $idEnvio, 'body' => $body]);

            // 4. Obter Token mTLS
            $auth = base64_encode(config('services.efi.client_id') . ':' . config('services.efi.client_secret'));
            $tokenUrl = config('services.efi.sandbox') ? 'https://api-pix-h.gerencianet.com.br/oauth/token' : 'https://api-pix.gerencianet.com.br/oauth/token';
            
            $tokenResponse = Http::withHeaders(['Authorization' => "Basic $auth"])
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSLCERT => $certPath,
                        CURLOPT_SSLCERTTYPE => 'P12',
                        CURLOPT_SSLCERTPASSWD => ''
                    ]
                ])->post($tokenUrl, ['grant_type' => 'client_credentials']);

            if ($tokenResponse->failed()) {
                Log::error('Efí Payout - Falha ao obter Token', ['response' => $tokenResponse->json()]);
                return $tokenResponse->json();
            }

            $accessToken = $tokenResponse->json()['access_token'] ?? null;
            if (!$accessToken) {
                Log::error('Efí Payout - Token não encontrado na resposta');
                return ['mensagem' => 'Falha na autenticação com a Efí.'];
            }

            // 5. Chamada de Envio via PUT (Endpoint /v2/gn/pix/)
            $baseUrl = config('services.efi.sandbox') ? 'https://api-pix-h.gerencianet.com.br' : 'https://api-pix.gerencianet.com.br';
            $response = Http::withToken($accessToken)
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSLCERT => $certPath,
                        CURLOPT_SSLCERTTYPE => 'P12',
                        CURLOPT_SSLCERTPASSWD => ''
                    ]
                ])->put("$baseUrl/v2/gn/pix/$idEnvio", $body);

            Log::info('Efí Payout - Resposta', ['status' => $response->status(), 'data' => $response->json()]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Erro crítico no Payout Pix (PUT): ' . $e->getMessage());
            return null;
        }
    }

    public function getPayoutStatus($idEnvio)
    {
        try {
            $certPath = storage_path('app/efi_cert.p12');
            $auth = base64_encode(config('services.efi.client_id') . ':' . config('services.efi.client_secret'));
            $tokenUrl = config('services.efi.sandbox') ? 'https://api-pix-h.gerencianet.com.br/oauth/token' : 'https://api-pix.gerencianet.com.br/oauth/token';
            
            $tokenResponse = Http::withHeaders(['Authorization' => "Basic $auth"])
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSLCERT => $certPath,
                        CURLOPT_SSLCERTTYPE => 'P12',
                        CURLOPT_SSLCERTPASSWD => ''
                    ]
                ])->post($tokenUrl, ['grant_type' => 'client_credentials']);

            $accessToken = $tokenResponse->json()['access_token'] ?? null;
            if (!$accessToken) return null;

            $baseUrl = config('services.efi.sandbox') ? 'https://api-pix-h.gerencianet.com.br' : 'https://api-pix.gerencianet.com.br';
            $response = Http::withToken($accessToken)
                ->withOptions([
                    'curl' => [
                        CURLOPT_SSLCERT => $certPath,
                        CURLOPT_SSLCERTTYPE => 'P12',
                        CURLOPT_SSLCERTPASSWD => ''
                    ]
                ])->get("$baseUrl/v2/gn/pix/$idEnvio");

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Erro ao consultar Payout Pix: ' . $e->getMessage());
            return null;
        }
    }
}
