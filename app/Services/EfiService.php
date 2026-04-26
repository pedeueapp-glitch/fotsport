<?php

namespace App\Services;

use Efi\EfiPay;
use Illuminate\Support\Facades\Log;

class EfiService
{
    protected $efi;

    public function __construct()
    {
        $options = [
            'client_id' => config('services.efi.client_id'),
            'client_secret' => config('services.efi.client_secret'),
            'sandbox' => config('services.efi.sandbox'),
            'certificate' => base_path(config('services.efi.certificate')),
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
}
