<?php

namespace Steveorevo\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class PlatformInstaller extends LibraryInstaller
{
    /**
     * This changed
     */
    public function getInstallPath(PackageInterface $package)
    {
        return '../someplace';
    }

    /**
     * 
     */
    public function supports($packageType)
    {
        return 'platform-installer' === $packageType;
    }
}
