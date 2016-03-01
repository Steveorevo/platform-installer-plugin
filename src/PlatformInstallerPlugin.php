<?php

namespace Steveorevo\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class PlatformInstallerPlugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new PlatformInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
