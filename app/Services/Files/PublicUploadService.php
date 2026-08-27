<?php

namespace App\Services\Files;

use App\Contracts\Files\PublicUploadContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class PublicUploadService implements PublicUploadContract
{
    public function store(UploadedFile $file, string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        abort_if($directory === '' || str_contains($directory, '..'), 422, 'Invalid upload directory.');

        $publicDirectory = public_path($directory);
        if (! is_dir($publicDirectory)) {
            mkdir($publicDirectory, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = now()->format('YmdHis').'-'.Str::random(16).'.'.$extension;
        $file->move($publicDirectory, $filename);

        return $directory.'/'.$filename;
    }

    public function delete(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || Str::startsWith($path, ['http://', 'https://']) || str_contains($path, '..')) {
            return;
        }

        $absolutePath = public_path(ltrim(str_replace('\\', '/', $path), '/'));
        $publicRoot = realpath(public_path());
        $realFile = is_file($absolutePath) ? realpath($absolutePath) : false;

        if ($publicRoot && $realFile && Str::startsWith($realFile, $publicRoot)) {
            @unlink($realFile);
        }
    }
}