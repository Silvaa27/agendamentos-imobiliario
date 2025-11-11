<?php

use App\Http\Controllers\ScheduleController;
use App\Livewire\AdvertismentForm;
use Illuminate\Support\Facades\Route;

Route::get('/formularios/{id}', AdvertismentForm::class)->name('advertisement.respond');

// Rota padrão do Laravel (opcional)
Route::get('/', function () {
    return view('welcome');
});