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
                $withdrawal->id, // Passando o ID do saque como idEnvio
                "Saque Fotsport #{$withdrawal->id}"
            );

            if ($pixResponse && isset($pixResponse['status']) && ($pixResponse['status'] === 'EM_PROCESSAMENTO' || $pixResponse['status'] === 'REALIZADO')) {
                $withdrawal->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'efi_payout_id' => $pixResponse['e2eId'] ?? ($pixResponse['idEnvio'] ?? null)
                ]);

                return back()->with('success', 'Saque aprovado e Pix enviado com sucesso!');
            }

            Log::error('Falha no Payout Efí', ['response' => $pixResponse]);
            return back()->with('error', 'O saque foi processado, mas a Efí retornou um status inesperado. Verifique os logs.');

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Erro crítico ao conectar na Efí: ' . $e->getMessage()]);
        }
    }
}
