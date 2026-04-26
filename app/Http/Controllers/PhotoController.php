<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    public function store(Request $request, Event $event)
    {
        // Previne timeout em uploads em lote
        set_time_limit(0);

        $request->validate([
            'photos' => 'required|array|max:20',
            'photos.*' => 'required|file|mimes:jpeg,jpg|max:20480', // Apenas JPEG/JPG aceitos
            'price' => 'nullable|numeric|min:0|max:10000'
        ]);

        foreach ($request->file('photos') as $file) {
            $filename = Str::uuid() . '.jpg';
            
            // Salva temporariamente para o Job processar
            $tempPath = $file->storeAs('photos/temp', $filename);

            // Cria o registro inicial no banco
            $photoModel = $event->photos()->create([
                'user_id'          => auth()->id(),
                'original_path'    => null,
                'watermarked_path' => 'processing.jpg', // Placeholder
                'price'            => $request->input('price', 5.00),
            ]);

            // Dispara o Job para processar em segundo plano
            \App\Jobs\ProcessPhoto::dispatch($photoModel, $tempPath, $filename);
        }

        return back()->with('message', 'As fotos foram enviadas e estão sendo processadas. Elas aparecerão na galeria em instantes.');
    }

    public function destroy(Event $event, Photo $photo)
    {
        // Só o fotógrafo que enviou esta foto ou o SuperAdmin pode deletá-la
        if (!auth()->user()->is_superadmin && $photo->user_id !== auth()->id()) {
            abort(403);
        }

        // Deletar do disco
        if ($photo->original_path) {
            Storage::disk('public')->delete(str_replace('storage/', '', $photo->original_path));
        }
        Storage::disk('public')->delete(str_replace('storage/', '', $photo->watermarked_path));

        $photo->delete();

        return back()->with('message', 'Foto excluída permanentemente.');
    }

    /**
     * Exclusão em massa de fotos (somente suas próprias, ou superadmin)
     */
    public function bulkDestroy(Request $request, Event $event)
    {
        $request->validate([
            'photo_ids'   => 'required|array|min:1|max:200',
            'photo_ids.*' => 'integer|exists:photos,id',
        ]);

        $query = Photo::whereIn('id', $request->photo_ids)
            ->where('event_id', $event->id);

        if (!auth()->user()->is_superadmin) {
            $query->where('user_id', auth()->id());
        }

        $photos = $query->get();

        $deleted = 0;
        foreach ($photos as $photo) {
            if (!auth()->user()->is_superadmin && $photo->user_id !== auth()->id()) {
                continue; // pula fotos que não pertencem ao usuário
            }

            if ($photo->original_path) {
                Storage::disk('public')->delete(str_replace('storage/', '', $photo->original_path));
            }
            Storage::disk('public')->delete(str_replace('storage/', '', $photo->watermarked_path));

            $photo->delete();
            $deleted++;
        }

        return back()->with('message', "{$deleted} foto(s) excluída(s).");
    }

    public function updatePrice(Request $request, Photo $photo)
    {
        if (!auth()->user()->is_superadmin && $photo->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'price' => 'required|numeric|min:0|max:10000'
        ]);

        $photo->update([
            'price' => $request->price
        ]);

        return back()->with('message', 'Preço atualizado com sucesso!');
    }
}
