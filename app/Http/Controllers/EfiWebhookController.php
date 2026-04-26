<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EfiWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Webhook Efi Pay recebido', $request->all());

        // A Efi envia um array 'pix' contendo os pagamentos confirmados
        $pix = $request->input('pix');

        if ($pix) {
            foreach ($pix as $payment) {
                $txid = $payment['txid'];
                
                // Busca as compras associadas a este TXID
                $purchases = Purchase::where('efi_txid', $txid)
                    ->where('status', 'pending')
                    ->with('photo.user')
                    ->get();

                foreach ($purchases as $purchase) {
                    $purchase->status = 'approved';
                    $purchase->save();

                    // Credita o saldo do fotógrafo
                    if ($purchase->photo && $purchase->photo->user) {
                        $photographer = $purchase->photo->user;
                        $photographer->balance += $purchase->amount;
                        $photographer->save();
                        
                        Log::info('Saldo do fotógrafo atualizado via Pix', [
                            'user_id' => $photographer->id, 
                            'amount' => $purchase->amount,
                            'txid' => $txid
                        ]);
                    }
                }
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
