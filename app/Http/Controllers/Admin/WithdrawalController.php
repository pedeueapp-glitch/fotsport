<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdrawal;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class WithdrawalController extends Controller
{
    public function index()
    {
        if (!auth()->user()->is_superadmin) {
            abort(403, 'Acesso Negado. Somente Super Administradores.');
        }

        $withdrawals = Withdrawal::with('user')->latest()->get();

        return Inertia::render('Admin/Withdrawals', [
            'withdrawals' => $withdrawals
        ]);
    }

    public function authorizeTransfer(Request $request, Withdrawal $withdrawal)
    {
        if (!auth()->user()->is_superadmin) {
            abort(403);
        }

        if ($withdrawal->status !== 'pending') {
            return back()->withErrors(['message' => 'Este saque já foi processado anteriormente.']);
        }

        try {
            $efiService = new \App\Services\EfiService();
            
            // Realiza a transferência Pix real via Efí
            $pixResponse = $efiService->sendPix(
                $withdrawal->net_amount,
                $withdrawal->pix_key ?? $withdrawal->user->pix_key,
                $withdrawal->pix_key_type ?? $withdrawal->user->pix_key_type,
                "Saque Fotsport #{$withdrawal->id}"
            );

            if ($pixResponse && isset($pixResponse['endToEndId'])) {
                $withdrawal->status = 'approved';
                $withdrawal->efi_payout_id = $pixResponse['endToEndId'];
                $withdrawal->save();

                return back()->with('success', 'Transferência PIX enviada e aprovada com sucesso via Efí Pay!');
            } else {
                return back()->withErrors(['message' => 'A Efí recusou a transferência ou saldo insuficiente.']);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Erro crítico ao conectar na Efí: ' . $e->getMessage()]);
        }
    }
}
