<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index()
    {
        // Todos os fotógrafos veem todos os eventos da plataforma
        $events = Event::with('user')
            ->withCount('photos')
            ->with(['photos' => function ($query) {
                $query->oldest()->limit(1);
            }])
            ->latest()
            ->get();
        return Inertia::render('Events/Index', [
            'events' => $events
        ]);
    }

    public function create()
    {
        return Inertia::render('Events/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'date' => 'required|date',
            'location' => 'nullable|string|max:255'
        ]);

        $request->user()->events()->create($validated);

        return redirect()->route('events.index')->with('message', 'Evento criado com sucesso!');
    }

    public function show(Event $event)
    {
        $event->load(['photos.user', 'user']);
        return Inertia::render('Events/Show', [
            'event' => $event
        ]);
    }
}
