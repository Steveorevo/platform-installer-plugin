<?php

namespace Steveorevo\Composer;

use Composer\Package\Version\VersionParser;
use Composer\Package\RootPackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Package\Package;
use Composer\IO\IOInterface;
use Composer\Composer;

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
        $installNow = array();
        foreach($pi as $platform => $installer) {
            if ('all' === strtolower($platform)) {
                foreach($installer as $install) {
                    if (!empty($install['url'])) {
                        if (empty($install['dir'])) {
                            $install['dir'] = $config->get('vendor-dir') . '/steveorevo/platform/' . strtolower($platform);
                        }
                        if (!is_dir($install['dir'])) {
                            array_push($installNow, $install);
                        }
                    }
                }
            }else{
                foreach($installer as $install) {
                    if (empty($install['dir'])) {
                        $install['dir'] = $config->get('vendor-dir') . '/steveorevo/platform/' . strtolower($platform);
                    }

                    // Check for architecture
                    $arch = "";
                    if ( substr( $platform, - 3 ) === "_64" || substr( $platform, - 3 ) === "_32" ) {
                        $arch     = substr( $platform, - 3 );
                        $platform = substr( $platform, 0, - 3 );
                    }
                    // Prevent matching 'win' within 'Darwin' and computer name conflicts on Mac
                    $uname = php_uname();
                    if ( PHP_OS === "Darwin" ) {
                        $platform = str_ireplace('Darwin', 'Macintosh', $platform);
                        $uname = str_ireplace('Darwin', 'Macintosh', $uname);
                        $uname = str_ireplace(gethostname(), '', $uname);
                    }
                    if ( false !== stripos( $uname, $platform ) ) {
                        if ( $arch !== "" ) {
                            if ( $arch === '_' . ( 8 * PHP_INT_SIZE ) ) {
                                if (!is_dir($install['dir'])) {
                                    array_push( $installNow, $install );
                                }
                            }
                        } else {
                            if (!is_dir($install['dir'])) {
                                array_push( $installNow, $install );
                            }
                        }
                    }
                }
            }
        }

        // Download platform installers
        foreach($installNow as $install) {
            $targetDir = $install['dir'];
            $url = $install['url'];
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
                    echo $e->getMessage();
                    $io->write("<warning>Error downloading: $url</warning>");
                }
            }
        }
    }
}
