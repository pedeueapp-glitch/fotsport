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

        $purchase->load('photo.event', 'photo.user');
        $photo = $purchase->photo;

        // ── Mercado Pago (reutilizando lógica do StoreController) ──
        $token = config('services.mercadopago.access_token');

        $items = [[
            'title'       => 'Foto — ' . ($photo->event->name ?? 'Evento'),
            'quantity'    => 1,
            'currency_id' => 'BRL',
            'unit_price'  => (float) $purchase->amount,
        ]];

        $preferenceData = [
            'items' => $items,
            'external_reference' => (string) $purchase->id,
            'notification_url' => config('app.url') . '/api/webhook/mercadopago',
            'back_urls' => [
                'success' => route('store.success'),
                'failure' => route('customer.dashboard'),
                'pending' => route('store.success'),
            ],
            'payer' => [
                'name'  => $customer->name,
            ],
        ];

        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);

        if (!$response->successful()) {
            return back()->with('error', 'Erro ao criar preferência de pagamento no Mercado Pago.');
        }

        $preferenceId = $response->json()['id'];

        return redirect()->route('customer.repay.checkout', [
            'purchase' => $purchase->id,
            'preferenceId' => $preferenceId
        ]);
    }

    public function showRepayCheckout(Request $request, Purchase $purchase)
    {
        $customer = auth()->guard('customer')->user();

        if ($purchase->customer_id !== $customer->id || $purchase->status !== 'pending') {
            return redirect()->route('customer.dashboard');
        }

        $preferenceId = $request->query('preferenceId');
        
        if (!$preferenceId) {
             return redirect()->route('customer.dashboard')->with('error', 'Sessão de pagamento expirada.');
        }

        $purchase->load('photo.event', 'photo.user');

        return Inertia::render('Store/Checkout', [
            'preferenceId' => $preferenceId,
            'publicKey'    => config('services.mercadopago.public_key'),
            'photos'       => [$purchase->photo],
            'total'        => (float) $purchase->amount,
        ]);
    }
}