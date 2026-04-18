<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = auth()->user()->supportTickets()->withCount(['messages' => function($q) {
            $q->where('is_admin_reply', true);
        }])->latest()->get();

        return Inertia::render('Photographer/Support/Index', [
            'tickets' => $tickets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:5'
        ]);

        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'status' => 'open'
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin_reply' => false
        ]);

        return redirect()->route('photographer.support.show', $ticket->id)->with('success', 'Chamado aberto com sucesso.');
    }

    public function show(SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Photographer/Support/Show', [
            'ticket' => $ticket->load(['messages.user']),
        ]);
    }

    public function sendMessage(Request $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        if ($ticket->status === 'closed') {
            return back()->with('error', 'Este chamado está encerrado.');
        }

        $request->validate([
            'message' => 'required|string|min:1'
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin_reply' => false
        ]);

        return back()->with('success', 'Mensagem enviada.');
    }
}
