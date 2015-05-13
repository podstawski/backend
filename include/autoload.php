<?php

function autoload(Array $set_include_path=[])
{
    set_include_path(implode(PATH_SEPARATOR, array_merge([
        realpath(__DIR__ . '/../classes'),
        realpath(__DIR__ . '/../Doctrine'),
        __DIR__,
        get_include_path(),
    ],$set_include_path)));
    
    spl_autoload_register(function ($name) {
        $file_to_include=str_replace('_', '/', $name) . '.php';
        include_once $file_to_include;
    });
}

