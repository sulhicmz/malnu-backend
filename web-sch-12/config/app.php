<?php

use Illuminate\Support\ServiceProvider;

return [

    'name'            => env('APP_NAME', 'Laravel'),

    'env'             => env('APP_ENV', 'production'),

    'debug'           => (bool) env('APP_DEBUG', false),

    'url'             => env('APP_URL', 'http://localhost'),

    'timezone'        => 'Asia/Jakarta',

    'locale'          => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale'    => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher'          => 'AES-256-CBC',

    'key'             => env('APP_KEY'),

    'previous_keys'   => [
         ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance'     => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store'  => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    'providers'       => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        Yajra\DataTables\DataTablesServiceProvider::class,
        // Laravel\Passport\PassportServiceProvider::class, // fixing  passport
        // Spatie\Permission\PermissionServiceProvider::class,
        Modules\ERaport\app\Providers\ERaportServiceProvider::class,
        Modules\ELearning\app\Providers\ELearningServiceProvider::class,
        // Modules\Murid\App\Providers\MuridServiceProvider::class,
        Modules\Perpustakaan\app\Providers\PerpustakaanServiceProvider::class,
        Modules\PPDB\app\Providers\PPDBServiceProvider::class,
        Modules\ManajemenSekolah\app\Providers\ManajemenSekolahServiceProvider::class,
        Modules\UjianOnline\app\Providers\UjianOnlineServiceProvider::class,
        Modules\SPP\app\Providers\SPPServiceProvider::class,
        Modules\AiLearning\app\Providers\AiLearningServiceProvider::class,
        Modules\Career\app\Providers\CareerServiceProvider::class,
        Modules\PortalOrangTua\app\Providers\PortalOrangTuaServiceProvider::class,
        Modules\SistemMonetisasi\app\Providers\SistemMonetisasiServiceProvider::class,
        Modules\LaporanAnalitik\app\Providers\LaporanAnalitikServiceProvider::class,

    ])->toArray(),

];
