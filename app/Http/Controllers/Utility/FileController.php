<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Serve a file from the public disk (storage/app/public)
     * Uses an authenticated route to avoid direct reliance on a public symlink.
     */
    public function show(Request $request, $path)
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $absolute = $disk->path($path);

        // We return the file response so browser can open it in a new tab.
        return response()->file($absolute);
    }
}


