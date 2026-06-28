<?php

if (!function_exists('base64storage')) {
    function base64storage(string $relativePath): string
    {
        $fullPath = storage_path("app/public/{$relativePath}");

        if (!file_exists($fullPath)) {
            return '';
        }

        $mime = mime_content_type($fullPath);
        $data = base64_encode(file_get_contents($fullPath));

        return "data:{$mime};base64,{$data}";
    }
}
