<?php

//namespace helpers;

if (!function_exists('get_project_dir')) {
    function get_project_dir(): string
    {
        $dir = $rootDir = getcwd();
        while (!is_file($dir.'/composer.json')) {
            if ($dir === dirname($dir)) {
                return $rootDir;
            }
            $dir = dirname($dir);
        }
        return $dir;
    }
}
