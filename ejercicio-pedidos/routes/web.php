<?php

use Illuminate\Support\Facades\Route;

// API-only: la vista "welcome" usa @vite y requeriría compilar assets con Node.
// En su lugar, la raíz responde con un JSON simple (útil para comprobar que el servicio vive).
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'status' => 'ok',
        'docs' => url('/api/documentation'),
    ]);
});
