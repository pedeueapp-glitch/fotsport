<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$efiService = new \App\Services\EfiService();
$certPath = storage_path('app/efi_cert.p12');
$auth = base64_encode(config('services.efi.client_id') . ':' . config('services.efi.client_secret'));
$tokenUrl = config('services.efi.sandbox') ? 'https://api-pix-h.gerencianet.com.br/oauth/token' : 'https://api-pix.gerencianet.com.br/oauth/token';

$tokenResponse = Illuminate\Support\Facades\Http::withHeaders(['Authorization' => "Basic $auth"])
    ->withOptions([
        'curl' => [
            CURLOPT_SSLCERT => $certPath,
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLCERTPASSWD => ''
        ]
    ])->post($tokenUrl, ['grant_type' => 'client_credentials']);

$token = $tokenResponse->json()['access_token'] ?? null;

if (!$token) {
    die("Erro ao obter token\n");
}

$baseUrl = config('services.efi.sandbox') ? 'https://api-pix-h.gerencianet.com.br' : 'https://api-pix.gerencianet.com.br';
$inicio = '2026-04-26T00:00:00Z';
$fim = '2026-04-26T23:59:59Z';

$res = Illuminate\Support\Facades\Http::withToken($token)
    ->withOptions([
        'curl' => [
            CURLOPT_SSLCERT => $certPath,
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLCERTPASSWD => ''
        ]
    ])->get("$baseUrl/v2/gn/pix?inicio=$inicio&fim=$fim");

echo "Status: " . $res->status() . "\n";
print_r($res->json());
