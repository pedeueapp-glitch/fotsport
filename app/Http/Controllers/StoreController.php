<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function index()
    {
        $events = Event::with('user')
            ->withCount('photos')
            ->with(['photos' => function ($query) {
                $query->oldest()->limit(1);
            }])
            ->latest()
            ->get();

        return Inertia::render('Store/Index', [
            'events' => $events
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'selfie' => 'required|image|max:10240', // Max 10MB
            'event_id' => 'nullable|integer|exists:events,id'
        ]);

        $file = $request->file('selfie');

        try {
            $postData = [];
            if ($request->filled('event_id')) {
                $postData['event_id'] = $request->event_id;
            }

            $response = Http::timeout(60)
                ->attach('file', file_get_contents($file->getRealPath()), 'selfie.jpg')
                ->post('http://face-api:8001/search_face/', $postData);

            if ($response->successful()) {
                $matchIds = $response->json()['matches'] ?? [];
                
                // Limit matches to a reasonable amount
                $matchIds = array_slice($matchIds, 0, 100);
                
                // Salva na sessão para persistir no redirecionamento GET
                session(['last_search_ids' => $matchIds]);

                return redirect()->route('store.search.results');
            }
        } catch (\Exception $e) {
            Log::error('Erro no search_face: ' . $e->getMessage());
        }

        return redirect()->route('store.index')->with('error', 'Não foi possível completar a busca.');
    }

    public function showSearchResults()
    {
        $matchIds = session('last_search_ids');

        if (!$matchIds) {
            return redirect()->route('store.index');
        }

        $photos = Photo::with(['event', 'user'])->whereIn('id', $matchIds)->get();

        return Inertia::render('Store/Results', [
            'photos' => $photos
        ]);
    }

    public function checkout(Request $request)
    {
        if (!auth()->guard('customer')->check()) {
            return back()->with('show_login', true);
        }

        $request->validate([
            'photo_ids' => 'required|array|min:1|max:50',
            'photo_ids.*' => 'integer|exists:photos,id'
        ]);

        $photoIds = $request->input('photo_ids', []);
        $photos   = Photo::with(['event', 'user'])->whereIn('id', $photoIds)->get();

        if ($photos->isEmpty()) {
            return back()->with('error', 'Nenhuma foto selecionada.');
        }

        // ── Mercado Pago (via API direta para evitar bugs do SDK no PHP 8.2) ──
        $token = config('services.mercadopago.access_token');

        // Cria registros de compra com status 'pending' e coleta seus IDs
        $purchaseIds = [];
        $items       = [];
        $total       = 0;

        $customerId = auth()->guard('customer')->id();

        foreach ($photos as $photo) {
            $purchase = Purchase::create([
                'customer_id' => $customerId,
                'photo_id' => $photo->id,
                'status'   => 'pending',
                'amount'   => $photo->price,
            ]);
            $purchaseIds[] = $purchase->id;

            $items[] = [
                'title'       => 'Foto — ' . ($photo->event->name ?? 'Evento'),
                'quantity'    => 1,
                'currency_id' => 'BRL',
                'unit_price'  => (float) $photo->price,
            ];
            $total += $photo->price;
        }

        $preferenceData = [
            'items' => $items,
            'external_reference' => implode(',', $purchaseIds),
            'notification_url' => config('app.url') . '/api/webhook/mercadopago',
            'back_urls' => [
                'success' => route('store.success'),
                'failure' => route('store.index'),
                'pending' => route('store.success'),
            ],
        ];

        if (auth()->guard('customer')->check()) {
            $customer = auth()->guard('customer')->user();
            $preferenceData['payer'] = [
                'name'  => $customer->name,
                'email' => $customer->cpf . '@fotsport.com.br', // Email fictício baseado no CPF para satisfazer o SDK
            ];
        }

        $response = Http::withToken($token)
            ->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);

        if (!$response->successful()) {
            Log::error('Mercado Pago Error', ['response' => $response->json(), 'token_prefix' => substr($token, 0, 10)]);
            return back()->with('error', 'Erro ao criar preferência de pagamento no Mercado Pago.');
        }

        $preferenceId = $response->json()['id'];
        $publicKey    = config('services.mercadopago.public_key');

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return back()->with('checkout_data', [
                'preferenceId' => $preferenceId,
                'publicKey'    => $publicKey,
                'total'        => $total,
                'itemsCount'   => count($photoIds),
                'initPoint'    => $response->json()['init_point'],
            ]);
        }

        return Inertia::render('Store/Checkout', [
            'preferenceId' => $preferenceId,
            'publicKey'    => $publicKey,
            'photos'       => $photos->load(['event', 'user']),
            'total'        => $total,
        ]);
    }

    public function showEvent(Event $event)
    {
        return Inertia::render('Store/EventShow', [
            'event' => $event->load('user'),
            'photos' => $event->photos()->with('user')->latest()->paginate(30),
        ]);
    }

    public function success(Request $request)
    {
        Log::info('Mercado Pago Success Callback', $request->all());

        $request->validate([
            'external_reference' => 'nullable|string|max:1000',
            'status' => 'nullable|string|max:50',
            'payment_id' => 'nullable|string|max:255'
        ]);

        // Mercado Pago envia payment_id, status e external_reference via query string
        $externalRef = $request->query('external_reference');
        $mpStatus    = $request->query('status');
        $paymentId   = $request->query('payment_id');

        if ($externalRef) {
            $purchaseIds = array_filter(explode(',', $externalRef), 'is_numeric');

            Log::info('Processing purchases', ['ids' => $purchaseIds, 'status' => $mpStatus]);

            if (!empty($purchaseIds)) {
                $purchases = Purchase::with('photo.user')
                    ->whereIn('id', $purchaseIds)
                    ->get();

                foreach ($purchases as $purchase) {
                    // Em ambiente local/sandbox, se chegamos aqui com um payment_id, tratamos como sucesso para facilitar testes
                    $isSandbox = config('services.mercadopago.sandbox');
                    $shouldApprove = ($mpStatus === 'approved') || ($isSandbox && $paymentId && !$mpStatus);

                    if ($purchase->status !== 'approved' && ($shouldApprove || $mpStatus === 'pending')) {
                         $purchase->status        = ($shouldApprove) ? 'approved' : 'pending';
                         $purchase->mp_payment_id = strip_tags($paymentId);
                         $purchase->save();

                         Log::info('Purchase updated', ['id' => $purchase->id, 'new_status' => $purchase->status]);

                         // Credita o saldo do fotógrafo que enviou a FOTO
                         if (($shouldApprove || $mpStatus === 'approved') && $purchase->photo && $purchase->photo->user) {
                             $photographer = $purchase->photo->user;
                             $photographer->balance += $purchase->amount;
                             $photographer->save();
                             Log::info('Photographer balance updated', ['user_id' => $photographer->id, 'added' => $purchase->amount]);
                         }
                    }
                }
            }
        }

        return Inertia::render('Store/Success');
    }

    public function photographerPortfolio(string $slug)
    {
        $user = \App\Models\User::where('slug', $slug)->firstOrFail();

        // Fotos enviadas POR este fotógrafo (não necessariamente do evento dele)
        $photos = Photo::where('user_id', $user->id)
            ->with('event')
            ->latest()
            ->paginate(20);

        return Inertia::render('Store/Photographer', [
            'photographer' => $user,
            'photos'       => $photos,
        ]);
    }
}
