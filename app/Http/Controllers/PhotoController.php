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
        // Previne timeout em uploads em lote (batch) no php artisan serve (que é single-thread)
        set_time_limit(0);

        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'required|image|max:10240', // 10MB max
            'price' => 'nullable|numeric|min:0|max:10000'
        ]);

        foreach ($request->file('photos') as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Save original securely
            $originalPath = $file->storeAs('photos/original', $filename, 'local');

            // Create watermark version and resize to low resolution
            $image = Image::make($file);
            
            // Resize image to max 1000px width/height to make process incredibly fast and prevent theft
            $image->resize(1000, 1000, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            $width = $image->width();
            $height = $image->height();
            
            // Múltiplas marcas em grid textual diagonal
            for ($x = 0; $x <= $width; $x += $width / 3) {
                for ($y = 0; $y <= $height; $y += $height / 4) {
                    $image->text('FOTSPORT', $x, $y, function($font) use ($width) {
                        $font->size(max($width / 8, 40)); 
                        $font->color([255, 255, 255, 0.5]); 
                        $font->align('center');
                        $font->valign('center');
                        $font->angle(35);
                    });
                }
            }
            
            // Desenha as listas protetoras Diagonais com alternância grosso x fino
            $diagonalSpan = $width + $height;
            $counter = 0;
            for ($i = 0; $i < $diagonalSpan; $i += $width / 12) {
                $counter++;
                $isThick = ($counter % 2 === 0);
                $thicknessLoops = $isThick ? 6 : 1;

                for ($t = 0; $t < $thicknessLoops; $t++) {
                    $offset = $i + $t;
                    $image->line($offset, 0, $offset - $height, $height, function ($draw) {
                        $draw->color(array(255, 255, 255, 0.45)); 
                    });
                }
            }
            
            // Marca d'água principal MASSIVA centralizada
            $image->text('FOTSPORT', $width / 2, $height / 2, function($font) use ($width) {
                $font->size(max($width / 4, 100));
                $font->color([255, 255, 255, 0.9]);
                $font->align('center');
                $font->valign('center');
                $font->angle(25);
            });

            // Adiciona texto obrigatório no rodapé / base do centro
            $image->text('FOTO NÃO COMERCIALIZADA', $width / 2, ($height / 2) + (max($width / 4, 100) / 1.5), function($font) use ($width) {
                $font->size(max($width / 15, 30));
                $font->color([255, 255, 255, 0.9]);
                $font->align('center');
                $font->valign('center');
            });

            $watermarkPath = 'public/photos/watermarked/' . $filename;
            
            Storage::disk('local')->put($watermarkPath, (string) $image->encode('jpg', 60));

            // Save to DB — registra qual fotógrafo enviou ESTA foto
            $photoModel = $event->photos()->create([
                'user_id'          => auth()->id(),
                'original_path'    => $originalPath,
                'watermarked_path' => str_replace('public/', 'storage/', $watermarkPath),
                'price'            => $request->input('price', 5.00),
            ]);

            try {
                Http::timeout(60)
                    ->attach('file', fopen($originalPath ? storage_path('app/' . $originalPath) : $file->getRealPath(), 'r'), $filename)
                    ->post('http://localhost:8001/index_photo/', [
                        'photo_id' => $photoModel->id,
                        'event_id' => $event->id
                    ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao indexar face: ' . $e->getMessage());
            }
        }

        return back()->with('message', 'Fotos enviadas com sucesso!');
    }

    public function destroy(Event $event, Photo $photo)
    {
        // Só o fotógrafo que enviou esta foto pode deletá-la
        if ($photo->user_id !== auth()->id() && $photo->event_id !== $event->id) {
            abort(403);
        }

        // Deletar do disco
        if ($photo->original_path) {
            Storage::disk('local')->delete($photo->original_path);
        }
        
        $watermarkVirtualPath = str_replace('storage/', 'public/', $photo->watermarked_path);
        Storage::disk('local')->delete($watermarkVirtualPath);

        // Deletar do BD
        $photo->delete();

        return back()->with('message', 'Foto excluída permanentemente.');
    }
}
