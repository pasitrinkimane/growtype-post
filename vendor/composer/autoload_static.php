<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit346cec2aa201b0996d3eaabf14f6eb46
{
    public static $files = array (
        '460404fa6d3686d7820838788517c1f9' => __DIR__ . '/..' . '/rappasoft/laravel-helpers/src/arrays.php',
        'c0908dd0408c67235210b4bf031d1290' => __DIR__ . '/..' . '/rappasoft/laravel-helpers/src/classes.php',
        '728cd66d334b33c0fb1ed0fe1060a82b' => __DIR__ . '/..' . '/rappasoft/laravel-helpers/src/helpers.php',
        'daf45b1134c9868f305965e4c0e0f06c' => __DIR__ . '/..' . '/rappasoft/laravel-helpers/src/strings.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit346cec2aa201b0996d3eaabf14f6eb46::$classMap;

        }, null, ClassLoader::class);
    }
}
