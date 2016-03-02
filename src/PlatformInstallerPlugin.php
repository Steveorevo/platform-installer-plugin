<?php

namespace Steveorevo\Composer;

use Composer\Package\Version\VersionParser;
use Composer\Package\RootPackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Package\Package;
use Composer\IO\IOInterface;
use Composer\Composer;

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
        $package = $composer->getPackage();
        $config = $composer->getConfig();
        $extra = $package->getExtra();
        if (!empty($extra['platform-installer']) ) {
            $pi = $extra['platform-installer'];
        }
        if (false === $pi) return;

        // Cycle through platform installers
        foreach($pi as $platform => $installer) {
            if ('all' === strtolower($platform)) {
                foreach($installer as $install) {
                    if (!empty( $install['url'])) {
                        $url = $install['url'];
                        $targetDir = $config->get('vendor-dir');
                        if (empty($install['dir'])) {
                            $targetDir = $config->get('vendor-dir') . '/platform/' . strtolower($platform);
                        }else{
                            $targetDir = $install['dir'];
                        }
                        if (!is_dir($targetDir)) {
                            $downloadManager = $composer->getDownloadManager();
                            $version = $package->getVersion();
                            $versionParser = new VersionParser();
                            $normVersion = $versionParser->normalize($version);
                            $package = new Package($url, $normVersion, $version);
                            $package->setTargetDir($targetDir);
                            $package->setInstallationSource('dist');
                            if (false === strpos($url, '.zip')) {
                                $package->setDistType('tar');
                            }else{
                                $package->setDistType('zip');
                            }
                            $package->setDistUrl($url);
                            try {
                                $downloadManager->download($package, $targetDir, false);
                            }catch(\Exception $e) {
                                if ($e instanceof \Composer\Downloader\TransportException && $e->getStatusCode() === 404) {
                                    $io->write("<warning>File not found: $url</warning>");
                                }else{
                                    $io->write("<warning>Error downloading: $url</warning>");
                                }
                            }
                        }
                        trace( $url );
                        trace( $targetDir );
                    }
                }
            }
        }
    }
}
