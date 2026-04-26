<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
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

    public function destroy(Event $event)
    {
        if (!auth()->user()->is_superadmin) {
            abort(403, 'Apenas superadmins podem excluir eventos.');
        }

        // Buscar todas as fotos do evento e contar compras
        $photos = $event->photos()->withCount('purchases')->get();

        foreach ($photos as $photo) {
            // Se a foto não foi comprada, apagamos do disco e do banco
            if ($photo->purchases_count === 0) {
                // Remover arquivos físicos
                $original = str_replace('storage/', '', $photo->original_path);
                $watermarked = str_replace('storage/', '', $photo->watermarked_path);
                
                Storage::disk('public')->delete([$original, $watermarked]);
                
                // Excluir permanentemente do banco
                $photo->delete();
            }
        }

        // Soft delete do evento (mantém no banco para não quebrar fotos compradas, mas some das listas)
        $event->delete();

        return Redirect::route('events.index')->with('success', 'Evento excluído com sucesso. Fotos não compradas foram removidas.');
    }
}
