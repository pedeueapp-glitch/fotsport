<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EfiService;

class RegisterEfiWebhook extends Command
{
    protected $signature = 'efi:register-webhook';
    protected $description = 'Registra a URL de Webhook na API da Efí Pay';

    public function handle()
    {
        $this->info('Iniciando registro de webhook na Efí...');
        
        $service = new EfiService();
        $pixKey = config('services.efi.pix_key');
        $webhookUrl = config('app.url') . '/api/webhook/efi';

        $this->info("Chave Pix: {$pixKey}");
        $this->info("URL do Webhook: {$webhookUrl}");

        $response = $service->registerWebhook($pixKey, $webhookUrl);

        if ($response) {
            $this->success('Webhook registrado com sucesso!');
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
        } else {
            $this->error('Falha ao registrar webhook. Verifique os logs.');
        }
    }
}
