<?php

use App\Modules\Chaquetas\Http\Controllers\Api\AdminApiController as A;
use App\Modules\Chaquetas\Http\Controllers\Api\AuthApiController as Auth;
use App\Modules\Chaquetas\Http\Controllers\Api\SchoolApiController as S;
use App\Modules\Chaquetas\Http\Controllers\Api\StudentApiController as E;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de la app móvil G'Lauren (iPhone y Android) — /api/v1/...
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::get('ping', fn () => ['ok' => true, 'app' => "G'Lauren", 'min_version' => '1.0.0']);

    Route::middleware('throttle:10,1')->group(function () {
        Route::post('login', [Auth::class, 'login']);
        Route::post('login/verificar', [Auth::class, 'verify']);
        Route::post('login/estudiante', [Auth::class, 'student']);
    });

    // Sesión de cualquiera de los tres perfiles
    foreach (['admin', 'colegio', 'estudiante'] as $role) {
        Route::middleware("app:$role")->prefix($role)->group(function () {
            Route::get('yo', [Auth::class, 'me']);
            Route::post('salir', [Auth::class, 'logout']);
        });
    }

    // Equipo G'Lauren
    Route::middleware('app:admin')->prefix('admin')->group(function () {
        Route::get('hoy', [A::class, 'today']);
        Route::get('catalogo', [A::class, 'catalog']);
        Route::get('cotizaciones', [A::class, 'quotes']);
        Route::post('cotizaciones', [A::class, 'storeQuote']);
        Route::get('cotizaciones/{quote}', [A::class, 'quote']);
        Route::put('cotizaciones/{quote}/cliente', [A::class, 'updateClient']);
        Route::post('cotizaciones/{quote}/etapa', [A::class, 'quoteStage']);
        Route::post('cotizaciones/{quote}/seguimiento', [A::class, 'followUp']);
        Route::post('cotizaciones/{quote}/enviada', [A::class, 'markSent']);
        Route::get('contratos', [A::class, 'contracts']);
        Route::get('contratos/{contract}', [A::class, 'contract']);
        Route::post('contratos/{contract}/etapa', [A::class, 'contractStage']);
        Route::post('contratos/{contract}/fotos', [A::class, 'photos']);
        Route::post('contratos/{contract}/alumnos', [A::class, 'addStudent']);
        Route::put('contratos/{contract}/alumnos/{student}/medidas', [A::class, 'measures']);
        Route::post('contratos/{contract}/anticipo', [A::class, 'deposit']);
        Route::get('pagos', [A::class, 'payments']);
        Route::post('pagos/{student}/aprobar', [A::class, 'approvePayment']);
        Route::post('pagos/{student}/rechazar', [A::class, 'rejectPayment']);
        Route::get('cartera', [A::class, 'cartera']);
    });

    // Colegios
    Route::middleware('app:colegio')->prefix('colegio')->group(function () {
        Route::get('contratos', [S::class, 'contracts']);
        Route::get('contratos/{contract}', [S::class, 'contract']);
        Route::post('contratos/{contract}/alumnos', [S::class, 'addStudent']);
        Route::post('contratos/{contract}/anticipo', [S::class, 'deposit']);
    });

    // Estudiantes
    Route::middleware('app:estudiante')->prefix('estudiante')->group(function () {
        Route::get('/', [E::class, 'show']);
        Route::post('pago', [E::class, 'pay']);
    });
});
