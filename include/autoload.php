<?php

function autoload(Array $set_include_path=[])
{
    set_include_path(implode(PATH_SEPARATOR, array_merge([
        realpath(__DIR__ . '/../classes'),
        __DIR__,
        get_include_path(),
    ],$set_include_path)));
    
    spl_autoload_register(function ($name) {
        @include_once str_replace('_', '/', $name) . '.php';
    });
}

