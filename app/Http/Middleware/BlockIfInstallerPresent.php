<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Keamanan: aplikasi menolak melayani request selama folder installer/ masih ada pasca-install. */
class BlockIfInstallerPresent
{
    public function handle(Request $request, Closure $next)
    {
        if (is_file(storage_path('installed.flag')) && is_dir(base_path('installer'))) {
            return response(
                '<h1 style="font-family:sans-serif">Hapus folder <code>installer/</code> dari server untuk melanjutkan (keamanan).</h1>',
                403
            );
        }
        return $next($request);
    }
}
