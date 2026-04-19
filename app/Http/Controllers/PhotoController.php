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
            
            // ─── 2.1 DESENHAR LINHAS EM "X" GROSSAS ──────────────────────────────
            $lineColor = [255, 255, 255, 0.35];
            $spacing = $width / 6; // Espaçamento entre as linhas
            
            // Linhas Diagonais ( \ )
            for ($i = -$height; $i < $width; $i += $spacing) {
                for ($thickness = -3; $thickness <= 3; $thickness++) { // Linha de 7px de espessura
                    $image->line($i + $thickness, 0, $i + $height + $thickness, $height, function ($draw) use ($lineColor) {
                        $draw->color($lineColor);
                    });
                }
            }

            // Linhas Diagonais Inversas ( / )
            for ($i = 0; $i < $width + $height; $i += $spacing) {
                for ($thickness = -3; $thickness <= 3; $thickness++) { // Linha de 7px de espessura
                    $image->line($i + $thickness, 0, $i - $height + $thickness, $height, function ($draw) use ($lineColor) {
                        $draw->color($lineColor);
                    });
                }
            }

            // ─── 2.2 REPETIR LOGO E MENSAGEM EM GRADE ALTERNADA ──────────────────
            $rows = 8;
            $cols = 6;
            $cellWidth = $width / $cols;
            $cellHeight = $height / $rows;

            for ($r = 0; $r < $rows; $r++) {
                for ($c = 0; $c < $cols; $c++) {
                    $posX = ($c * $cellWidth) + ($cellWidth / 2);
                    $posY = ($r * $cellHeight) + ($cellHeight / 2);
                    
                    // Alterna entre LOGO e MENSAGEM
                    if (($r + $c) % 2 === 0) {
                        $image->text('FOTSPORT', $posX, $posY, function($font) use ($cellWidth) {
                            $font->size($cellWidth * 0.4);
                            $font->color([255, 255, 255, 0.4]);
                            $font->align('center');
                            $font->valign('center');
                            $font->angle(25);
                        });
                    } else {
                        $image->text('PROIBIDA A UTILIZAÇÃO', $posX, $posY, function($font) use ($cellWidth) {
                            $font->size($cellWidth * 0.15); // Texto menor para caber
                            $font->color([255, 255, 255, 0.5]);
                            $font->align('center');
                            $font->valign('center');
                            $font->angle(-15);
                        });
                    }
                }
            }

            // ─── 2.3 MARCA CENTRAL MASSIVA E AVISO PRINCIPAL ──────────────────────
            $image->text('FOTSPORT', $width / 2, $height / 2, function($font) use ($width) {
                $font->size($width / 5);
                $font->color([255, 255, 255, 0.7]);
                $font->align('center');
                $font->valign('center');
                $font->angle(25);
            });

            $image->text('ARQUIVO PROTEGIDO - USO PROIBIDO', $width / 2, ($height / 2) + ($width / 10), function($font) use ($width) {
                $font->size($width / 25);
                $font->color([255, 255, 255, 0.8]);
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
                $faceApiUrl = config('services.face_api.url');
                $indexResponse = Http::timeout(60)
                    ->attach('file', fopen(storage_path('app/public/' . $originalPath), 'r'), $filename)
                    ->attach('photo_id', (string) $photoModel->id)
                    ->attach('event_id', (string) $event->id)
                    ->post($faceApiUrl . '/index_photo/');
                
                if (!$indexResponse->successful()) {
                    \Illuminate\Support\Facades\Log::error('Falha na indexação facial: ' . $indexResponse->status() . ' - ' . $indexResponse->body());
                } else {
                    $resData = $indexResponse->json();
                    $encodings = $resData['encodings'] ?? [];
                    
                    if (!empty($encodings)) {
                        $photoModel->update([
                            'face_descriptors' => $encodings,
                            'face_indexed'     => true
                        ]);
                    }
                    
                    \Illuminate\Support\Facades\Log::info('Foto indexada com sucesso: ' . $photoModel->id . ' (Faces: ' . count($encodings) . ')');
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
