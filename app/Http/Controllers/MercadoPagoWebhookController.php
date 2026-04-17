<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Mercado Pago Webhook Received', $request->all());

        $type = $request->query('type') ?? $request->input('type');
        $id   = $request->query('data_id') ?? ($request->input('data')['id'] ?? null);

        if ($type === 'payment' && $id) {
            $this->processPayment($id);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function processPayment($paymentId)
    {
        $token = config('services.mercadopago.access_token');

        $response = Http::withToken($token)
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if ($response->successful()) {
            $paymentData = $response->json();
            $status      = $paymentData['status'];
            $externalRef = $paymentData['external_reference'];

            if ($externalRef) {
                $purchaseIds = array_filter(explode(',', $externalRef), 'is_numeric');

                if (!empty($purchaseIds)) {
                    $purchases = Purchase::with('photo.user')
                        ->whereIn('id', $purchaseIds)
                        ->get();

                    foreach ($purchases as $purchase) {
                        if ($purchase->status !== 'approved' && $status === 'approved') {
                            $purchase->status        = 'approved';
                            $purchase->mp_payment_id = (string) $paymentId;
                            $purchase->save();

                            // Credita o saldo do fotógrafo
                            if ($purchase->photo && $purchase->photo->user) {
                                $photographer = $purchase->photo->user;
                                $photographer->balance += $purchase->amount;
                                $photographer->save();
                                Log::info('Webhook: Photographer balance updated', ['user_id' => $photographer->id, 'added' => $purchase->amount]);
                            }
                            
                            Log::info("Webhook: Purchase {$purchase->id} approved.");
                        }
                    }
                }
            }
        } else {
            Log::error('Webhook: Failed to fetch payment data', ['id' => $paymentId, 'response' => $response->json()]);
        }
    }
}
