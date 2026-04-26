<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProcessPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $photo;
    protected $tempPath;
    protected $filename;

    public $timeout = 600; // 10 minutos para garantir fotos grandes

    /**
     * Create a new job instance.
     */
    public function __construct(Photo $photo, string $tempPath, string $filename)
    {
        $this->photo = $photo;
        $this->tempPath = $tempPath;
        $this->filename = $filename;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $tempFullPath = storage_path('app/' . $this->tempPath);
            if (!file_exists($tempFullPath)) {
                Log::error('Arquivo temporário não encontrado para processamento: ' . $tempFullPath);
                return;
            }

            // --- 1. PROCESSAR E SALVAR ORIGINAL OTIMIZADA ---
            $optimizedOriginal = Image::make($tempFullPath);
            
            $optimizedOriginal->resize(2500, 2500, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $originalDir = 'photos/original';
            Storage::disk('public')->makeDirectory($originalDir);
            $originalPath = $originalDir . '/' . $this->filename;
            
            Storage::disk('public')->put($originalPath, (string) $optimizedOriginal->encode('jpg', 82));

            // --- 2. CRIAR VERSÃO COM MARCA D'ÁGUA (Low Res) ---
            $image = Image::make($tempFullPath);
            
            $image->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            $width = $image->width();
            $height = $image->height();
            
            // ─── 2.1 DESENHAR LINHAS EM "X" GROSSAS ──────────────────────────────
            $lineColor = [255, 255, 255, 0.35];
            $spacing = $width / 6;
            
            for ($i = -$height; $i < $width; $i += $spacing) {
                for ($thickness = -3; $thickness <= 3; $thickness++) {
                    $image->line($i + $thickness, 0, $i + $height + $thickness, $height, function ($draw) use ($lineColor) {
                        $draw->color($lineColor);
                    });
                }
            }

            for ($i = 0; $i < $width + $height; $i += $spacing) {
                for ($thickness = -3; $thickness <= 3; $thickness++) {
                    $image->line($i + $thickness, 0, $i - $height + $thickness, $height, function ($draw) use ($lineColor) {
                        $draw->color($lineColor);
                    });
                }
            }

            // ─── 2.2 REPETIR LOGO EM GRADE ──────────────────
            $rows = 8; $cols = 6;
            $cellWidth = $width / $cols; $cellHeight = $height / $rows;

            for ($r = 0; $r < $rows; $r++) {
                for ($c = 0; $c < $cols; $c++) {
                    $posX = ($c * $cellWidth) + ($cellWidth / 2);
                    $posY = ($r * $cellHeight) + ($cellHeight / 2);
                    if (($r + $c) % 2 === 0) {
                        $image->text('FOTSPORT', $posX, $posY, function($font) use ($cellWidth) {
                            $font->size($cellWidth * 0.4);
                            $font->color([255, 255, 255, 0.4]);
                            $font->align('center');
                            $font->valign('center');
                            $font->angle(25);
                        });
                    }
                }
            }

            // ─── 2.3 MARCA CENTRAL MASSIVA ──────────────────────
            $image->text('FOTSPORT', $width / 2, $height / 2, function($font) use ($width) {
                $font->size($width / 5); $font->color([255, 255, 255, 0.7]);
                $font->align('center'); $font->valign('center'); $font->angle(25);
            });

            $watermarkDir = 'photos/watermarked';
            Storage::disk('public')->makeDirectory($watermarkDir);
            $watermarkPath = $watermarkDir . '/' . $this->filename;
            
            Storage::disk('public')->put($watermarkPath, (string) $image->encode('jpg', 30));

            // 3. Atualizar o Modelo
            $this->photo->update([
                'original_path'    => 'storage/' . $originalPath,
                'watermarked_path' => 'storage/' . $watermarkPath,
            ]);

            // 4. Indexação Facial
            try {
                $faceApiUrl = config('services.face_api.url');
                $indexResponse = Http::timeout(60)
                    ->attach('file', fopen(storage_path('app/public/' . $originalPath), 'r'), $this->filename)
                    ->attach('photo_id', (string) $this->photo->id)
                    ->attach('event_id', (string) $this->photo->event_id)
                    ->post($faceApiUrl . '/index_photo/');
                
                if ($indexResponse->successful()) {
                    $resData = $indexResponse->json();
                    $encodings = $resData['encodings'] ?? [];
                    if (!empty($encodings)) {
                        $this->photo->update([
                            'face_descriptors' => $encodings,
                            'face_indexed'     => true
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Exceção ao indexar face no Job: ' . $e->getMessage());
            }

            // 5. Deletar arquivo temporário
            Storage::delete($this->tempPath);

        } catch (\Exception $e) {
            Log::error('Erro ao processar foto no Job: ' . $e->getMessage());
            throw $e; // Faz a fila tentar novamente se falhar
        }
    }
}
