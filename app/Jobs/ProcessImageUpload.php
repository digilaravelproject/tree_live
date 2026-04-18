<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MtTree;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $treeId;
    protected $imagesInput;

    public $tries = 3;
    public $timeout = 120;

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

        $manager = new ImageManager(new Driver());

        foreach ((array)$this->imagesInput as $base64Image) {
            if (empty($base64Image)) continue;

            // Remove data:image/... prefix if exists
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }

            $imageData = base64_decode($base64Image);

            if ($imageData !== false) {
                $fileName = 'tree_' . time() . '_' . uniqid() . '.jpg';

                try {
                    $image = $manager->read($imageData);
                    $image->scale(width: 1024);
                    $image->toJpeg(75)->save($destinationPath . '/' . $fileName);

                    $savedImagesPaths[] = 'tree_images/' . $fileName;
                } catch (\Exception $e) {
                    Log::error("Async Image Save Error for Tree {$this->treeId}: " . $e->getMessage());
                }
            }
        }

        if (!empty($savedImagesPaths)) {
            MtTree::where('id', $this->treeId)->update([
                'tree_image_upload'   => $savedImagesPaths,
                'all_captured_images' => $savedImagesPaths,
            ]);

            Log::info("✅ Images processed for Tree ID: {$this->treeId} | Count: " . count($savedImagesPaths));
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("❌ Image processing failed for Tree ID: {$this->treeId} | Error: " . $exception->getMessage());
    }
}