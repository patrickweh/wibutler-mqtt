<?php

if (!function_exists('name_to_unique_id')) {
    function name_to_unique_id($name): string
    {
        return rtrim(preg_replace(
            '/[^0-9A-Za-z_-]/',
            '_',
            \Illuminate\Support\Str::of($name)->lower()->ascii()->snake()->toString()
        ), '_');
    }
}
