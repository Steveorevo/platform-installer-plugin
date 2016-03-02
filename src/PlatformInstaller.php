<?php

namespace Steveorevo\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class PlatformInstaller extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        return '../someplace';
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'platform-installer' === $packageType;
    }
}
