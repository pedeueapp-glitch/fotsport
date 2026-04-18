<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportController extends Controller
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

    public function index()
    {
        $tickets = SupportTicket::with(['user', 'messages' => function($q) {
            $q->latest()->limit(1);
        }])->latest()->get();

        return Inertia::render('Admin/Support/Index', [
            'tickets' => $tickets
        ]);
    }

    public function show(SupportTicket $ticket)
    {
        return Inertia::render('Admin/Support/Show', [
            'ticket' => $ticket->load(['user', 'messages.user']),
        ]);
    }

    public function sendMessage(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|min:1'
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin_reply' => true
        ]);

        return back()->with('success', 'Resposta enviada.');
    }

    public function toggleStatus(SupportTicket $ticket)
    {
        $ticket->status = $ticket->status === 'open' ? 'closed' : 'open';
        $ticket->save();

        return back()->with('success', 'Status do chamado atualizado.');
    }
}
