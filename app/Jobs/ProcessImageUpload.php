<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MtTree;
use Illuminate\Support\Facades\Log;

class ProcessImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $treeId;
    protected $imagesInput;

    public $tries = 3;        // ✅ 3 baar try karega fail hone par
    public $timeout = 120;    // ✅ 120 seconds max time per job

    public function __construct($treeId, $imagesInput)
    {
        $this->treeId      = $treeId;
        $this->imagesInput = $imagesInput;
    }

    public function handle()
    {
        $savedImagesPaths = [];
        $destinationPath  = public_path('tree_images');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        foreach ((array)$this->imagesInput as $base64Image) {
            if (!empty($base64Image)) {

                // ✅ Remove data:image/... prefix if exists
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                }

                $imageData = base64_decode($base64Image);

                if ($imageData !== false) {
                    $fileName = 'tree_' . time() . '_' . uniqid() . '.jpg';

                    // ✅ Compress & Resize using Intervention/Image
                    \Intervention\Image\Facades\Image::make($imageData)
                        ->resize(1024, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->save($destinationPath . '/' . $fileName, 75);

                    $savedImagesPaths[] = 'tree_images/' . $fileName;
                }
            }
        }

        // ✅ Tree record update karo processed images ke saath
        if (!empty($savedImagesPaths)) {
            $finalJsonPath = json_encode($savedImagesPaths);

            MtTree::where('id', $this->treeId)->update([
                'tree_image_upload'   => $finalJsonPath,
                'all_captured_images' => $finalJsonPath,
            ]);

            Log::info("✅ Images processed for Tree ID: {$this->treeId} | Count: " . count($savedImagesPaths));
        }
    }

    // ✅ Job fail hone par log karega
    public function failed(\Throwable $exception)
    {
        Log::error("❌ Image processing failed for Tree ID: {$this->treeId} | Error: " . $exception->getMessage());
    }
}