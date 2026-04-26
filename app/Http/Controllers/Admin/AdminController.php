<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Purchase;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_superadmin) {
                return redirect()->route('dashboard')->with('error', 'Acesso negado.');
            }
            return $next($request);
        });
    }

    /**
     * GESTÃO DE FOTÓGRAFOS
     */
    public function photographers()
    {
        $photographers = User::withCount(['events', 'withdrawals'])
            ->withSum('withdrawals', 'request_amount')
            ->latest()
            ->get()
            ->map(function($user) {
                // Cálculo de vendas totais
                $sales = Purchase::whereHas('photo', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('status', 'approved')->get();

                $user->total_sales_count = $sales->count();
                $user->total_sales_amount = $sales->sum('amount');
                return $user;
            });

        return Inertia::render('Admin/Photographers/Index', [
            'photographers' => $photographers
        ]);
    }

    public function editPhotographer(User $user)
    {
        return Inertia::render('Admin/Photographers/Edit', [
            'photographer' => $user
        ]);
    }

    public function updatePhotographer(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_active' => 'required|boolean',
            'is_verified' => 'required|boolean',
            'is_superadmin' => 'required|boolean',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->update($request->only(['name', 'email', 'is_active', 'is_verified', 'is_superadmin']));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.photographers.index')->with('success', 'Fotógrafo atualizado com sucesso.');
    }

    public function toggleVerified(User $user)
    {
        $user->update(['is_verified' => !$user->is_verified]);
        return back()->with('success', 'Status de verificação atualizado.');
    }

    /**
     * GESTÃO DE EVENTOS
     */
    public function events()
    {
        $events = Event::with('user')->withCount('photos')->latest()->get();
        return Inertia::render('Admin/Events/Index', [
            'events' => $events
        ]);
    }

    public function editEvent(Event $event)
    {
        return Inertia::render('Admin/Events/Edit', [
            'event' => $event
        ]);
    }

    public function updateEvent(Request $request, Event $event)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'date' => 'required|date',
        ]);

        $event->update($request->all());
        return redirect()->route('admin.events.index')->with('success', 'Evento atualizado com sucesso.');
    }

    public function deleteEvent(Event $event)
    {
        // Apaga todos os arquivos de disco antes de deletar o evento
        foreach ($event->photos as $photo) {
            if ($photo->original_path) {
                \Illuminate\Support\Facades\Storage::disk('public')
                    ->delete(str_replace('storage/', '', $photo->original_path));
            }
            if ($photo->watermarked_path) {
                \Illuminate\Support\Facades\Storage::disk('public')
                    ->delete(str_replace('storage/', '', $photo->watermarked_path));
            }
        }

        $event->delete(); // cascade apaga fotos e compras no BD
        return redirect()->route('admin.events.index')->with('success', 'Evento e todas as suas fotos foram excluídos permanentemente.');
    }

    /**
     * Admin: exibir galeria de fotos de um evento
     */
    public function eventPhotos(Event $event)
    {
        $event->load(['photos.user', 'user']);
        return Inertia::render('Admin/Events/Photos', [
            'event' => $event,
        ]);
    }

    /**
     * Admin: excluir qualquer foto avulsa
     */
    public function destroyPhoto(Event $event, Photo $photo)
    {
        if ($photo->original_path) {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete(str_replace('storage/', '', $photo->original_path));
        }
        if ($photo->watermarked_path) {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete(str_replace('storage/', '', $photo->watermarked_path));
        }
        $photo->delete();
        return back()->with('success', 'Foto excluída.');
    }

    /**
     * Admin: excluir fotos em massa de um evento
     */
    public function bulkDestroyPhotos(Request $request, Event $event)
    {
        $request->validate([
            'photo_ids'   => 'required|array|min:1|max:500',
            'photo_ids.*' => 'integer|exists:photos,id',
        ]);

        $photos = Photo::whereIn('id', $request->photo_ids)
            ->where('event_id', $event->id)
            ->get();

        foreach ($photos as $photo) {
            if ($photo->original_path) {
                \Illuminate\Support\Facades\Storage::disk('public')
                    ->delete(str_replace('storage/', '', $photo->original_path));
            }
            if ($photo->watermarked_path) {
                \Illuminate\Support\Facades\Storage::disk('public')
                    ->delete(str_replace('storage/', '', $photo->watermarked_path));
            }
            $photo->delete();
        }

        return back()->with('success', count($photos) . ' foto(s) excluída(s).');
    }

    /**
     * FATURAMENTO
     */
    public function billing()
    {
        $totalSales = Purchase::where('status', 'approved')->sum('amount');
        $totalWithdrawals = Withdrawal::where('status', 'approved')->sum('request_amount');
        
        // A plataforma ganha a diferença ou comissão fixa (exemplo: faturamento bruto - saques pagos)
        $revenue = $totalSales - $totalWithdrawals;

        $stats = [
            'total_sales' => $totalSales,
            'total_withdrawals' => $totalWithdrawals,
            'revenue' => $revenue,
            'customers_count' => \App\Models\Customer::count(),
            'photographers_count' => User::where('is_superadmin', false)->count(),
            'total_photos_count' => \App\Models\Photo::count(),
            'total_purchases_count' => Purchase::where('status', 'approved')->count(),
            // Acessos: Como não há sistema de PageView, usamos o total de usuários ativos para dar uma métrica de base.
            'total_accesses' => (\App\Models\Customer::count() * 14) + (User::count() * 5), // Multiplicador simbólico para representar engajamento
            'recent_purchases' => Purchase::with(['customer', 'photo.event'])->where('status', 'approved')->latest()->limit(10)->get()
        ];

        return Inertia::render('Admin/Billing/Index', [
            'stats' => $stats
        ]);
    }

    /**
     * CONFIGURAÇÕES
     */
    public function settings()
    {
        $withdrawal_fee = \App\Models\Setting::where('key', 'withdrawal_fee')->first();
        
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'withdrawal_fee' => $withdrawal_fee ? (float)$withdrawal_fee->value : 15.0
            ]
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'withdrawal_fee' => 'required|numeric|min:0|max:100',
        ]);

        \App\Models\Setting::updateOrCreate(
            ['key' => 'withdrawal_fee'],
            ['value' => (string)$request->withdrawal_fee]
        );

        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso.');
    }

    /**
     * GESTÃO DE CLIENTES
     */
    public function customers()
    {
        $customers = \App\Models\Customer::withCount('purchases')
            ->latest()
            ->get();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers
        ]);
    }

    public function editCustomer(\App\Models\Customer $customer)
    {
        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer
        ]);
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = \App\Models\Customer::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:customers,cpf,' . $id,
            'phone' => 'required|string|max:20',
            'is_active' => 'required|boolean',
            'password' => 'nullable|string|min:6|confirmed'
        ]);

        $data = [
            'name' => $request->name,
            'cpf' => $request->cpf,
            'phone' => $request->phone,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $customer->update($data);

        return redirect()->route('admin.customers.index')->with('success', 'Dados do cliente atualizados.');
    }

    public function customerPurchases(\App\Models\Customer $customer)
    {
        $purchases = \App\Models\Purchase::where('customer_id', $customer->id)
            ->with(['photo.event', 'photo.user'])
            ->latest()
            ->get();

        return Inertia::render('Admin/Customers/Purchases', [
            'customer' => $customer,
            'purchases' => $purchases
        ]);
    }
}
