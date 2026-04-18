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

            // --- 1. PROCESSAR E SALVAR ORIGINAL OTIMIZADA ---
            $optimizedOriginal = Image::make($file);
            
            $optimizedOriginal->resize(2500, 2500, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $originalDir = 'photos/original';
            Storage::disk('public')->makeDirectory($originalDir);
            $originalPath = $originalDir . '/' . $filename;
            
            Storage::disk('public')->put($originalPath, (string) $optimizedOriginal->encode('jpg', 82));

            // --- 2. CRIAR VERSÃO COM MARCA D'ÁGUA (Low Res) ---
            $image = Image::make($file);
            
            $image->resize(1200, 1200, function ($constraint) {
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
            
            // Desenha as listas protetoras Diagonais
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
            
            // Marca d'água principal MASSIVA
            $image->text('FOTSPORT', $width / 2, $height / 2, function($font) use ($width) {
                $font->size(max($width / 4, 100));
                $font->color([255, 255, 255, 0.9]);
                $font->align('center');
                $font->valign('center');
                $font->angle(25);
            });

            // Texto obrigatório no rodapé
            $image->text('FOTO NÃO COMERCIALIZADA', $width / 2, ($height / 2) + (max($width / 4, 100) / 1.5), function($font) use ($width) {
                $font->size(max($width / 15, 30));
                $font->color([255, 255, 255, 0.9]);
                $font->align('center');
                $font->valign('center');
            });

            $watermarkDir = 'photos/watermarked';
            Storage::disk('public')->makeDirectory($watermarkDir);
            $watermarkPath = $watermarkDir . '/' . $filename;
            
            // SALVA A IMAGEM COM MARCA D'ÁGUA
            Storage::disk('public')->put($watermarkPath, (string) $image->encode('jpg', 60));

            // Salva no Banco de Dados
            $photoModel = $event->photos()->create([
                'user_id'          => auth()->id(),
                'original_path'    => 'storage/' . $originalPath,
                'watermarked_path' => 'storage/' . $watermarkPath,
                'price'            => $request->input('price', 5.00),
            ]);

            try {
                $indexResponse = Http::timeout(60)
                    ->attach('file', fopen(storage_path('app/public/' . $originalPath), 'r'), $filename)
                    ->post('http://face-api:8001/index_photo/', [
                        'photo_id' => $photoModel->id,
                        'event_id' => $event->id
                    ]);
                
                if (!$indexResponse->successful()) {
                    \Illuminate\Support\Facades\Log::error('Falha na indexação facial: ' . $indexResponse->status() . ' - ' . $indexResponse->body());
                } else {
                    \Illuminate\Support\Facades\Log::info('Foto indexada com sucesso: ' . $photoModel->id);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Exceção ao indexar face: ' . $e->getMessage());
            }
        }

        return back()->with('message', 'Fotos otimizadas e enviadas com sucesso!');
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

        // Deletar do BD
        $photo->delete();

        return back()->with('message', 'Foto excluída permanentemente.');
    }
}
