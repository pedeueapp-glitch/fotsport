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

            if ($pixResponse && isset($pixResponse['status'])) {
                $statusMap = [
                    'EM_PROCESSAMENTO' => 'processing',
                    'REALIZADO' => 'paid',
                    'RECUSADO' => 'rejected'
                ];

                $newStatus = $statusMap[$pixResponse['status']] ?? 'processing';

                $withdrawal->update([
                    'status' => $newStatus,
                    'paid_at' => $newStatus === 'paid' ? now() : null,
                    'efi_payout_id' => $pixResponse['idEnvio'] ?? null,
                    'efi_e2e_id' => $pixResponse['e2eId'] ?? null
                ]);

                if ($newStatus === 'processing') {
                    return back()->with('success', 'Pix aceito pela Efí e está em processamento.');
                }
                
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
    public function checkStatus(Withdrawal $withdrawal)
    {
        if (!auth()->user()->is_superadmin) abort(403);

        if (!$withdrawal->efi_payout_id) {
            return back()->with('error', 'Este saque não possui um ID de envio vinculado.');
        }

        try {
            $efiService = new \App\Services\EfiService();
            $statusResponse = $efiService->getPayoutStatus($withdrawal->efi_payout_id);

            if ($statusResponse && isset($statusResponse['status'])) {
                $statusMap = [
                    'EM_PROCESSAMENTO' => 'processing',
                    'REALIZADO' => 'paid',
                    'RECUSADO' => 'rejected'
                ];

                $newStatus = $statusMap[$statusResponse['status']] ?? $withdrawal->status;

                $withdrawal->update([
                    'status' => $newStatus,
                    'paid_at' => $newStatus === 'paid' ? ($withdrawal->paid_at ?: now()) : $withdrawal->paid_at,
                    'efi_e2e_id' => $statusResponse['e2eId'] ?? $withdrawal->efi_e2e_id
                ]);

                return back()->with('success', 'Status atualizado: ' . $statusResponse['status']);
            }

            return back()->with('error', 'Não foi possível obter o status atualizado da Efí.');

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Erro ao consultar status: ' . $e->getMessage()]);
        }
    }
}
