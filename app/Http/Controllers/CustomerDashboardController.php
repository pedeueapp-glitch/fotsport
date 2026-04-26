<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $customer = auth()->guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('store.index');
        }

        $purchases = Purchase::where('customer_id', $customer->id)
            ->with(['photo.event', 'photo.user'])
            ->latest()
            ->get();

        return Inertia::render('Customer/Dashboard', [
            'purchases' => $purchases,
            'customer' => $customer
        ]);
    }

    public function download(Purchase $purchase)
    {
        $customer = auth()->guard('customer')->user();

        // Verificar se a compra pertence ao cliente e se está aprovada
        if ($purchase->customer_id !== $customer->id || $purchase->status !== 'approved') {
            abort(403, 'Acesso negado.');
        }

        $photo = $purchase->photo;
        
        if (!$photo) {
            abort(404, 'Foto não encontrada.');
        }

        // Limpar o prefixo 'storage/' do caminho original para buscar no disco 'public'
        $path = str_replace('storage/', '', $photo->original_path);

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            \Illuminate\Support\Facades\Log::error('Download 404: Arquivo inexistente no disco público', ['path' => $path, 'db_path' => $photo->original_path]);
            abort(404, 'Arquivo físico não encontrado no servidor.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($path, "foto-fotsport-{$photo->id}.jpg");
    }


    public function cancel(Purchase $purchase)
    {
        $customer = auth()->guard('customer')->user();

        if ($purchase->customer_id !== $customer->id || $purchase->status !== 'pending') {
            return back()->with('error', 'Não é possível cancelar esta compra.');
        }

        $purchase->delete();

        return back()->with('success', 'Compra cancelada com sucesso.');
    }

    public function repay(Purchase $purchase)
    {
        $customer = auth()->guard('customer')->user();

        if ($purchase->customer_id !== $customer->id || $purchase->status !== 'pending') {
            return back()->with('error', 'Não é possível realizar o pagamento desta compra.');
        }

        $photo = $purchase->photo;
        if (!$photo) {
            return back()->with('error', 'Foto não encontrada.');
        }

        // ── Efí Pix (Reutilizando EfiService) ──
        $efiService = new \App\Services\EfiService();
        $pixData = $efiService->createPixPayment(
            $purchase->amount,
            $customer->name,
            $customer->cpf ?? '000.000.000-00' // Fallback se não tiver CPF no cadastro
        );

        if (!$pixData) {
            return back()->with('error', 'Erro ao gerar Pix na Efí. Tente novamente em instantes.');
        }

        // Atualizar o txid na compra
        $purchase->update([
            'efi_txid' => $pixData['txid']
        ]);

        return back()->with('checkout_data', [
            'pix_qrcode' => $pixData['qrcode'],
            'pix_copy_paste' => $pixData['copy_paste'],
            'txid' => $pixData['txid'],
            'total' => (float) $purchase->amount,
            'itemsCount' => 1
        ]);
    }
}