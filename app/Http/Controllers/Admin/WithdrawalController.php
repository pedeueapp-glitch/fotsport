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

            // Se chegou aqui, deu erro. Vamos logar o motivo real.
            \Illuminate\Support\Facades\Log::error('Falha no Payout Efí - Resposta Detalhada', [
                'withdrawal_id' => $withdrawal->id,
                'response' => $pixResponse
            ]);

            $errorMessage = 'O Pix não foi concluído.';
            if (isset($pixResponse['nome'])) {
                $errorMessage .= ' Erro: ' . $pixResponse['nome'];
            } elseif (isset($pixResponse['mensagem'])) {
                $errorMessage .= ' Detalhe: ' . $pixResponse['mensagem'];
            }

            return back()->with('error', $errorMessage);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro crítico no Payout Efí (Exception)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['message' => 'Erro crítico ao conectar na Efí: ' . $e->getMessage()]);
        }
    }
}
