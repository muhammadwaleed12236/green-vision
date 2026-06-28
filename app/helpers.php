<?php

if (!function_exists('base64storage')) {
    function base64storage(string $relativePath): string
    {
        $storagePath = storage_path("app/public/{$relativePath}");
        $publicPath = public_path("storage/{$relativePath}");

        if (file_exists($publicPath)) {
            $fullPath = $publicPath;
        } elseif (file_exists($storagePath)) {
            $fullPath = $storagePath;
        } else {
            return '';
        }

        $mime = mime_content_type($fullPath);
        $data = base64_encode(file_get_contents($fullPath));

        return "data:{$mime};base64,{$data}";
    }
}
