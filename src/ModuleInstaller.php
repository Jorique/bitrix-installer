<?php

namespace Jorique\BitrixModuleInstaller;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

/**
 * Class ModuleInstaller
 * @package Bitrix\Composer
 */
class ModuleInstaller extends LibraryInstaller
{
    const PACKAGE_TYPE = 'bitrix-module';

    /**
     * {@inheritDoc}
     */
    /*public function getPackageBasePath(PackageInterface $package)
    {
        $extras = $package->getExtra();

        if ((array_key_exists('module_name', $extras)) && (!empty($extras['module_name']))) {
            $name = (string)$extras['module_name'];
        } else {
            throw new \Exception(
                'Unable to install module, composer.json must contain module name declaration like this: ' .
                '"extra": { "module_name": "somename" } '
            );
        }

        return 'bitrix/modules/'.$name;
    }*/

    public function getInstallPath(PackageInterface $package)
    {
        return 'bitrix/modules/'.$package->getExtra()['module_name'];
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return self::PACKAGE_TYPE === $packageType;
    }
}