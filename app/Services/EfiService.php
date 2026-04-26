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
            // Formatação da chave baseada no tipo
            $formattedKey = $pixKey;
            if ($pixKeyType === 'phone') {
                $cleanPhone = preg_replace('/[^0-9]/', '', $pixKey);
                $formattedKey = (strlen($cleanPhone) <= 11) ? '+55' . $cleanPhone : '+' . $cleanPhone;
            } elseif ($pixKeyType === 'cpf' || $pixKeyType === 'cnpj') {
                $formattedKey = preg_replace('/[^0-9]/', '', $pixKey);
            }

            $body = [
                'valor' => number_format($amount, 2, '.', ''),
                'chave' => $formattedKey
            ];

            Log::info('Tentando Payout Pix via HTTP Direto', ['idEnvio' => $idEnvio, 'body' => $body]);

            // Converter Base64 para arquivo PEM
            $certBase64 = env('EFI_CERTIFICATE_BASE64');
            $certPath = storage_path('app/efi_cert.pem');
            
            // Sempre garantir que o certificado esteja atualizado e no formato correto (PEM)
            $p12Content = base64_decode($certBase64);
            $certs = [];
            
            if (openssl_pkcs12_read($p12Content, $certs, "")) {
                // É um P12, converte para PEM
                file_put_contents($certPath, $certs['cert'] . $certs['pkey']);
            } else {
                // Se não for P12, assume que já é PEM ou tenta salvar direto
                file_put_contents($certPath, $p12Content);
            }

            // Obter Token de Acesso manualmente para garantir pureza
            $clientId = config('services.efi.client_id');
            $clientSecret = config('services.efi.client_secret');
            $auth = base64_encode("$clientId:$clientSecret");
            
            Log::info('Solicitando Token de Acesso Efí...', ['auth' => "Basic " . substr($auth, 0, 10) . "..."]);

            $tokenResponse = Http::withHeaders(['Authorization' => "Basic $auth"])
                ->withOptions(['cert' => $certPath])
                ->post(config('services.efi.sandbox') ? 'https://api-pix-h.gerencianet.com.br/oauth/token' : 'https://api-pix.gerencianet.com.br/oauth/token', [
                    'grant_type' => 'client_credentials'
                ]);

            $accessToken = $tokenResponse->json()['access_token'];

            // Chamada Final de Envio
            $baseUrl = config('services.efi.sandbox') ? 'https://api-pix-h.gerencianet.com.br' : 'https://api-pix.gerencianet.com.br';
            $response = Http::withToken($accessToken)
                ->withOptions(['cert' => $certPath])
                ->post("$baseUrl/v2/pix/envio/$idEnvio", $body);

            Log::info('Resposta Efí Payout', ['status' => $response->status(), 'data' => $response->json()]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Erro crítico no Payout Pix (HTTP): ' . $e->getMessage());
            return null;
        }
    }
}
