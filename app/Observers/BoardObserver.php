<?php

namespace App\Observers;

use App\Models\Board;
use Image;
use Illuminate\Support\Facades\Storage;
use File;

class BoardObserver
{
    /**
     * Handle the board "created" event.
     *
     * @param  \App\Board  $board
     * @return void
     */
    public function created(Board $board)
    {
        $thisBoard  = $board->getDirty();
        $imageName  = $thisBoard['image'];
        $request    = request();

        if ($request->image) {
            $request->image->storeAs($board->getImageFolder(), $imageName, 'public');

            $thumbnail = Image::make(Storage::get("public/" 
                       . $board->getImageFolder() . $imageName));

            $path = storage_path('app/public/' . $board->getImageFolder() . 'thumbnail');
            
            if (! File::exists($path)) File::makeDirectory($path, 775, true);

            $thumbnail->resize(250, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $thumbnail->save($path . '/' . $imageName);
        }
        
    }

    /**
     * Handle the board "updated" event.
     *
     * @param  \App\Board  $board
     * @return void
     */
    public function updating(Board $board)
    {
        $imageName  = $board->getOriginal('image');
        $thisBoard  = $board->getDirty();
        $imageNull  = is_null($thisBoard['image'] ?? null);
        $request    = request();

        if ($request->has('deleteImage') || $request->editImage) {
            Storage::delete("public/" . $board->getImageFolder() . $imageName);
            Storage::delete("public/" . $board->getImageFolder() . "thumbnail/{$imageName}");
        }
    }

    public function updated(Board $board)
    {
        $thisBoard  = $board->getDirty();
        $imageName  = $thisBoard['image'] ?? false;
        $request    = request();

        if ($request->editImage && $imageName) {
            
            $request->editImage->storeAs($board->getImageFolder(), $imageName, 'public');

            $thumbnail = Image::make(Storage::get("public/" . $board->getImageFolder() . $imageName));
            $path      = storage_path("app/public/" . $board->getImageFolder() . "thumbnail");

            if (! File::exists($path)) File::makeDirectory($path, 775, true);

            $thumbnail->resize(250, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $thumbnail->save($path . '/' . $imageName);
        } 
    }

    /**
     * Handle the board "deleted" event.
     *
     * @param  \App\Board  $board
     * @return void
     */
    public function deleted(Board $board)
    {
        //
    }

    /**
     * Handle the board "restored" event.
     *
     * @param  \App\Board  $board
     * @return void
     */
    public function restored(Board $board)
    {
        //
    }

    /**
     * Handle the board "force deleted" event.
     *
     * @param  \App\Board  $board
     * @return void
     */
    public function forceDeleted(Board $board)
    {
        //
    }
}
