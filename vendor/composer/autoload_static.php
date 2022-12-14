<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb2c65c271e2388b9f729f3a9ceface3d
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Galih\\JurnalKopnus\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Galih\\JurnalKopnus\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb2c65c271e2388b9f729f3a9ceface3d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb2c65c271e2388b9f729f3a9ceface3d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb2c65c271e2388b9f729f3a9ceface3d::$classMap;

        }, null, ClassLoader::class);
    }
}
