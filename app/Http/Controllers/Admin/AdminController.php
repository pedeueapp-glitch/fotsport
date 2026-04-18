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
            'is_superadmin' => 'required|boolean',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->update($request->only(['name', 'email', 'is_active', 'is_superadmin']));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.photographers.index')->with('success', 'Fotógrafo atualizado com sucesso.');
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
            'date' => 'required|date',
        ]);

        $event->update($request->all());
        return redirect()->route('admin.events.index')->with('success', 'Evento atualizado com sucesso.');
    }

    public function deleteEvent(Event $event)
    {
        // Deleta todas as fotos e arquivos associados se necessário
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Evento excluído permanentemente.');
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
}
