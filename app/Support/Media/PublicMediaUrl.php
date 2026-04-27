<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\Storage;

class PublicMediaUrl
{
    public static function resolve(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $normalizedPath = trim((string) $path);

        if ($normalizedPath === '') {
            return null;
        }

        if (str_starts_with($normalizedPath, 'data:')) {
            return $normalizedPath;
        }

        if (self::isAbsoluteUrl($normalizedPath)) {
            $storageRelativePath = self::extractPublicStorageRelativePath($normalizedPath);

            if ($storageRelativePath === null) {
                return $normalizedPath;
            }

            return self::publicDiskUrl($storageRelativePath);
        }

        $storageRelativePath = self::extractPublicStorageRelativePath($normalizedPath);

        if ($storageRelativePath !== null) {
            return self::publicDiskUrl($storageRelativePath);
        }

        return str_starts_with($normalizedPath, '/')
            ? asset(ltrim($normalizedPath, '/'))
            : asset($normalizedPath);
    }

    public static function extractPublicStorageRelativePath(string $path): ?string
    {
        $normalizedPath = trim($path);

        if ($normalizedPath === '') {
            return null;
        }

        if (self::isAbsoluteUrl($normalizedPath)) {
            $parsedPath = parse_url($normalizedPath, PHP_URL_PATH);

            if (! is_string($parsedPath) || trim($parsedPath) === '') {
                return null;
            }

            $normalizedPath = trim($parsedPath);
        }

        if (str_starts_with($normalizedPath, '/storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('/storage/'));
        } elseif (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('storage/'));
        } elseif (str_starts_with($normalizedPath, '/public/')) {
            $normalizedPath = substr($normalizedPath, strlen('/public/'));
        } elseif (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, strlen('public/'));
        } elseif (str_starts_with($normalizedPath, '/')) {
            return null;
        }

        $normalizedPath = ltrim($normalizedPath, '/');

        if (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, strlen('public/'));
        }

        return $normalizedPath !== '' ? $normalizedPath : null;
    }

    private static function isAbsoluteUrl(string $path): bool
    {
        return str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '//');
    }

    private static function publicDiskUrl(string $path): string
    {
        $driver = (string) config('filesystems.disks.public.driver', '');

        if ($driver === 'local') {
            return route('media.public', ['path' => ltrim($path, '/')], false);
        }

        $url = Storage::disk('public')->url($path);

        if (self::isAbsoluteUrl($url) || str_starts_with($url, '/')) {
            return $url;
        }

        return '/'.ltrim($url, '/');
    }

    private static function toRelativeUrl(string $url): string
    {
        if (! self::isAbsoluteUrl($url)) {
            return str_starts_with($url, '/') ? $url : '/'.ltrim($url, '/');
        }

        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        if (! is_string($path) || trim($path) === '') {
            return '/';
        }

        $relativeUrl = $path;

        if (is_string($query) && $query !== '') {
            $relativeUrl .= "?{$query}";
        }

        if (is_string($fragment) && $fragment !== '') {
            $relativeUrl .= "#{$fragment}";
        }

        return $relativeUrl;
    }
}
