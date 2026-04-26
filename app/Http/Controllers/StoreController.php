<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use App\Models\Purchase;
use App\Models\Brand;
use App\Models\HeroItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');

        $baseQuery = Event::with('user')
            ->withCount('photos')
            ->with(['photos' => function ($query) {
                $query->oldest();
            }]);

        $todayEvents = (clone $baseQuery)
            ->whereDate('date', '=', $today)
            ->latest()
            ->get();

        $pastEvents = (clone $baseQuery)
            ->whereDate('date', '<', $today)
            ->latest()
            ->get();

        $futureEvents = (clone $baseQuery)
            ->whereDate('date', '>', $today)
            ->latest()
            ->get();

        // Agrupamos os futuros no começo dos outros ou em uma aba separada? 
        // O usuário pediu "hoje" em destaque, e separar por data.
        // Vou fundir futuros e passados em uma lista única e manter os de hoje separados.
        $otherEvents = $futureEvents->concat($pastEvents);

        $brands = Brand::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $heroItems = HeroItem::where('is_active', true)->orderBy('order')->get();

        return Inertia::render('Store/Index', [
            'todayEvents' => $todayEvents,
            'otherEvents' => $otherEvents,
            'brands' => $brands,
            'heroItems' => $heroItems
        ]);
    }

    public function searchEvents(Request $request)
    {
        $query = $request->input('q');

        $events = Event::where('name', 'like', "%{$query}%")
            ->orWhere('location', 'like', "%{$query}%")
            ->with('user')
            ->withCount('photos')
            ->with(['photos' => function ($q) {
                $q->oldest()->limit(1);
            }])
            ->latest()
            ->get();

        return Inertia::render('Store/EventSearchResults', [
            'events' => $events,
            'searchTerm' => $query
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
            $faceApiUrl = config('services.face_api.url');
            Log::info('Iniciando busca facial otimizada', ['event_id' => $request->event_id]);
            
            // 1. Obter a encoding da selfie - Aumentado para 60s
            $selfieResponse = Http::timeout(60)
                ->attach('file', fopen($file->getRealPath(), 'r'), 'selfie.jpg')
                ->post($faceApiUrl . '/get_face_encodings/');

            if (!$selfieResponse->successful() || empty($selfieResponse->json()['encodings'])) {
                return back()->withErrors(['photo' => 'Nenhuma face detectada na selfie enviada.']);
            }

            $selfieEncoding = $selfieResponse->json()['encodings'][0];

            // 2. Buscar fotos candidatas no banco de dados
            $query = Photo::where('face_indexed', true);
            if ($request->filled('event_id')) {
                $query->where('event_id', $request->event_id);
            }
            
            // Pegamos apenas o necessário para a comparação
            $candidates = $query->select('id', 'face_descriptors')->get();
            
            if ($candidates->isEmpty()) {
                return redirect()->route('store.index')->with('error', 'Nestas galeria ainda não foram processadas faces para busca.');
            }

            $candidatesMap = [];
            foreach ($candidates as $c) {
                $candidatesMap[$c->id] = $c->face_descriptors;
            }

            // 3. Enviar para comparação matemática no serviço Python - Aumentado para 120s
            $compareResponse = Http::timeout(120)->asForm()->post($faceApiUrl . '/compare_faces/', [
                'source_encoding' => json_encode($selfieEncoding),
                'candidates'      => json_encode($candidatesMap)
            ]);

            if ($compareResponse->successful()) {
                $matchIds = $compareResponse->json()['matches'] ?? [];
                
                Log::info('Busca facial concluída', [
                    'matches_count' => count($matchIds)
                ]);
                
                $matchIds = array_slice($matchIds, 0, 100);
                session(['last_search_ids' => $matchIds]);

                return redirect()->route('store.search.results');
            } else {
                Log::error('Falha na resposta da comparação facial', [
                    'status' => $compareResponse->status(),
                    'body' => $compareResponse->body()
                ]);
                return back()->withErrors(['photo' => 'O serviço de reconhecimento facial falhou na comparação.']);
            }
        } catch (\Exception $e) {
            Log::error('Erro no search_face: ' . $e->getMessage());
        }

        return redirect()->route('store.index')->with('error', 'Não foi possível completar a busca.');
    }


    public function showSearchResults()
    {
        $matchIds = session('last_search_ids', []);

        if (empty($matchIds)) {
            Log::warning('Nenhum ID encontrado na sessão para os resultados da busca.');
            return redirect()->route('store.index')->with('error', 'Nenhuma foto encontrada.');
        }

        // Recupera as fotos respeitando a ordem de relevância retornada pela IA
        $idsString = implode(',', $matchIds);
        $photos = Photo::whereIn('id', $matchIds)
            ->with(['event', 'user'])
            ->orderByRaw("FIELD(id, $idsString)")
            ->get();

        Log::info('Fotos recuperadas do banco para exibição', [
            'ids_solicitados' => count($matchIds),
            'fotos_encontradas' => $photos->count()
        ]);

        return inertia('Store/Results', [
            'photos' => $photos
        ]);
    }

    public function checkout(Request $request)
    {
        if (!auth()->guard('customer')->check()) {
            // Salva as fotos na sessão para não perder a seleção após o login
            session(['pending_checkout_photos' => $request->input('photo_ids', [])]);
            return back()->with('show_login', true);
        }

        $request->validate([
            'photo_ids' => 'required|array|min:1|max:50',
            'photo_ids.*' => 'integer|exists:photos,id'
        ]);

        $photoIds = $request->input('photo_ids', []);
        
        // Limpa a sessão se o checkout for iniciado com sucesso
        session()->forget('pending_checkout_photos');
        $photos   = Photo::with(['event', 'user'])->whereIn('id', $photoIds)->get();

        if ($photos->isEmpty()) {
            return back()->with('error', 'Nenhuma foto selecionada.');
        }

        // ── Efi Pay Pix (via EfiService) ──
        $efiService = new \App\Services\EfiService();

        // Cria registros de compra com status 'pending' e coleta seus IDs
        $purchaseIds = [];
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
            $total += $photo->price;
        }

        $customer = auth()->guard('customer')->user();
        $pixData = $efiService->createPixPayment($total, $customer->name, $customer->cpf);

        if (!$pixData) {
            return back()->with('error', 'Erro ao gerar pagamento Pix. Tente novamente mais tarde.');
        }

        // Associa o TXID às compras
        Purchase::whereIn('id', $purchaseIds)->update(['efi_txid' => $pixData['txid']]);

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return back()->with('checkout_data', [
                'pix_qrcode' => $pixData['qrcode'],
                'pix_copy_paste' => $pixData['copy_paste'],
                'total' => $total,
                'itemsCount' => count($photoIds),
            ]);
        }

        return Inertia::render('Store/Checkout', [
            'pix_qrcode' => $pixData['qrcode'],
            'pix_copy_paste' => $pixData['copy_paste'],
            'photos' => $photos->load(['event', 'user']),
            'total' => (float) $total,
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

        return redirect()->route('customer.dashboard')->with('success', 'Pagamento processado! Suas fotos já estão disponíveis para download.');
    }

    public function photographers()
    {
        $photographers = \App\Models\User::where('is_active', true)
            ->latest()
            ->get(['id', 'name', 'slug', 'avatar', 'is_verified']);

        return Inertia::render('Store/Photographers', [
            'photographers' => $photographers
        ]);
    }

    public function photographerPortfolio(string $slug)
    {
        $user = \App\Models\User::where('slug', $slug)->firstOrFail();

        // Fotos enviadas POR este fotógrafo (não necessariamente do evento dele)
        $photos = Photo::where('user_id', $user->id)
            ->with('event')
            ->latest()
            ->paginate(30);

        return Inertia::render('Store/Photographer', [
            'photographer' => $user,
            'photos'       => $photos,
        ]);
    }
}
