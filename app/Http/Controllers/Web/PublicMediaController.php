<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function __invoke(string $path): StreamedResponse
    {
        $normalizedPath = ltrim($path, '/');

        if ($normalizedPath === '' || str_contains($normalizedPath, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($normalizedPath)) {
            abort(404);
        }

        return $disk->response($normalizedPath, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
