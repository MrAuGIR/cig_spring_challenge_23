<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit889f4798968d972654885b9e3e6e6658
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Tests\\' => 6,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Tests',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit889f4798968d972654885b9e3e6e6658::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit889f4798968d972654885b9e3e6e6658::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit889f4798968d972654885b9e3e6e6658::$classMap;

        }, null, ClassLoader::class);
    }
}
