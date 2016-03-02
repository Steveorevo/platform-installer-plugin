<?php

namespace Steveorevo\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;


// Our native trace function for PHP
function trace($msg, $j = false){
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

class PlatformInstaller extends LibraryInstaller
{
    /**
     * Obtain any listed platform-
     */
    public function getInstallPath(PackageInterface $package)
    {
        $extra = $package->getExtra();
        if (! empty( $extra['platform-installer'] )) {
            $pinstall = $extra['platform-installer'];
            trace( $pinstall );
        }
        return 'vendor';
    }

    /**
     *
     */
    public function supports($packageType)
    {
        return 'platform-installer' === $packageType;
    }
}
