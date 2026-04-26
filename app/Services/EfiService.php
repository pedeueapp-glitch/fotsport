<?php

namespace App\Services;

use Efi\EfiPay;
use Illuminate\Support\Facades\Log;

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

    public function sendPix($amount, $pixKey, $pixKeyType, $description = 'Saque Fotsport')
    {
        try {
            $body = [
                'valor' => number_format($amount, 2, '.', ''),
                'chave' => $pixKey,
                'mensagem' => $description
            ];

            // Na Efí, transferências Pix imediatas usam pixSend
            $response = $this->efi->pixSend([], $body);
            return $response;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar Pix (Efi): ' . $e->getMessage());
            return null;
        }
    }
}
