<?php

namespace Steveorevo\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class PlatformInstaller extends LibraryInstaller
{
    /**
     *  Supported when including the platfrom-installer.
     */
    public function supports($packageType)
    {
        return 'platform-installer' === $packageType;
    }
}
