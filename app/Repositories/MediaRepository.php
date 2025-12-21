<?php


namespace App\Repositories;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaRepository extends Repository
{
    public function model()
    {
        return Media::class;
    }

    public function storeByRequest(UploadedFile $file, string $path, string $description = null, string $type = null): Media
    {

        $path = Storage::disk('public')->put('/'. trim($path, '/'), $file);
        $extension = $file->extension();
        if(!$type){
            $type = in_array($extension, ['jpg', 'png', 'jpeg', 'gif']) ? 'Image' : $extension;
        }

        return $this->create([
            'type' => $type,
            'name' => $file->getClientOriginalName(),
            'src' =>  $path,
            'extension' => $extension,
            'path' => $path,
            'description' => $description,
        ]);
    }

    public function updateByRequest(UploadedFile $file,string $path, string $type = null, Media $media): Media
    {

        $path = Storage::disk('public')->put('/'. trim($path, '/'), $file);
        $extension = $file->extension();
        if(!$type){
            $type = in_array($extension, ['jpg', 'png', 'jpeg', 'gif']) ? 'image' : $extension;
        }

        if(Storage::disk('public')->exists($media->src)){
            Storage::disk('public')->delete($media->src);
        }

        $media->update([
            'type' => $type,
            'name' => $file->getClientOriginalName(),
            'src' =>  $path,
            'extension' => $extension,
            'path' => $path,
        ]);
        return $media;
    }

    public function updateOrCreateByRequest(UploadedFile $file, string $path, string $type = 'Image', $media = null): Media
    {
        $src = Storage::disk('public')->put('/'. trim($path, '/'), $file);
        $extension = $file->extension();
        if ($media && Storage::disk('public')->exists($media->src)) {
            Storage::disk('public')->delete($media->src);
        }

        if (!$type) {
            $type = in_array($extension, ['jpg', 'png', 'jpeg', 'gif']) ? 'Image' : $extension;
        }
        return $this->query()->updateOrCreate([
            'id' => $media?->id ?? 0,
        ],[
            'type' => $type,
            'src' => $src,
            'extension' => $extension,
            'path' => $path,
        ]);
    }
}
