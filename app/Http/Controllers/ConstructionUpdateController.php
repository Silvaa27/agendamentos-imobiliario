<?php

namespace App\Http\Controllers;

use App\Models\ConstructionUpdate;
use Illuminate\Http\Request;

class ConstructionUpdateController extends Controller
{
    public function showPhotos($id)
    {
        $constructionUpdate = ConstructionUpdate::findOrFail($id);
        $photos = $constructionUpdate->getMedia('photos');

        if ($photos->isEmpty()) {
            abort(404, 'Nenhuma foto encontrada');
        }

        // Prepare photo data for JavaScript
        $photoData = $photos->map(function ($photo) {
            return [
                'url' => $photo->getUrl(),
                'name' => $photo->name,
                'size' => $photo->size,
                'type' => $photo->mime_type,
                'created_at' => $photo->created_at->toIso8601String(),
            ];
        });

        return view('construction-update.photos', [
            'constructionUpdate' => $constructionUpdate,
            'photos' => $photos,
            'photoData' => $photoData, // Adicione esta linha
        ]);
    }

}