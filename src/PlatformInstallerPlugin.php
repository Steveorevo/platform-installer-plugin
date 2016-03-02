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

        // Download packages that match the given platform
        foreach( $pi as $platform => $installer ) {
            if ( 'all' === strtolower( $platform ) ) {
                foreach( $installer as $install ) {
                    if ( !empty( $install['url'] ) ) {
                        $url = $install['url'];
                        $dir = $composer->getConfig()->get('vendor-dir');
//                        if ( !empty( $install['dir'] ) ) {
//                            $dir .=  '/' . $install['dir'];
//                        }else{
//                            $dir .= '/platform';
//                        }
                        trace( $url );
                        trace( $dir );
                    }
                }
            }
        }
    }
}
