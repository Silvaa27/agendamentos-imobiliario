<?php

use App\Http\Controllers\ConstructionPhotoController;
use App\Http\Controllers\ConstructionUpdateController;
use App\Http\Controllers\InvoiceDocumentController;
use App\Http\Controllers\ScheduleController;
use App\Livewire\AdvertismentForm;
use Illuminate\Support\Facades\Route;

Route::get('/formularios/{id}', AdvertismentForm::class)->name('advertisement.respond');

Route::get('/construction-update/{id}/photos', [ConstructionUpdateController::class, 'showPhotos'])
    ->name('construction-update.photos')
    ->middleware(['auth']);


Route::get('/invoice/{id}/documents', [InvoiceDocumentController::class, 'showDocuments'])
    ->name('invoice.documents')
    ->middleware(['auth']);