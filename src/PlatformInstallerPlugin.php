<?php

namespace Steveorevo\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

// Our native trace function for PHP
function trace($msg, $j = false) {
    if (! is_string($msg) && $j===false ){
        $msg = "(" . gettype($msg) . ") " . var_export($msg, true);
    }else{
        if ($j===false) {
            $msg = "(" . gettype($msg) . ") " . $msg;
        }
    }
    $h = @fopen('http://127.0.0.1:8189/trace?m='.substr(rawurlencode($msg),0,2000),'r');
    if ($h !== FALSE){
        fclose($h);
    }
}

class PlatformInstallerPlugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new PlatformInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);

        // Look for extra platform-installer definition
        $pi = false;
        $extra = $composer->getPackage()->getExtra();
        if ( !empty( $extra['platform-installer'] ) ) {
            $pi = $extra['platform-installer'];
        }
        if ( false === $pi ) return;

        // Download
        $dir = $composer->getPackage()->getTargetDir();
        foreach( $pi as $platform => $installer ) {
            trace( $platform );
        }
        trace( $dir );
    }
}
