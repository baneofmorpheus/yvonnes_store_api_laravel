<?php

namespace App\Services;

use App\Models\Media;
use Exception;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileUploadService
{




    static function uploadFile(UploadedFile $file, string $tag = ''): Media
    {


        $file_content = file_get_contents($file->getRealPath());

        $file_name = $tag . "/" .  $file->getClientOriginalName(); //
        Storage::disk('s3')->put($file_name, $file_content);

        return Media::create([
            'name' => $file->getClientOriginalName(),
            'path' => $file_name,
            'slug' => Str::slug($file_name),
            'size' => $file->getSize(),
            'type' => $file->getMimeType()
        ]);
    }
}
