<?php

namespace Jorique\BitrixModuleInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

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

    protected function getInstallDir(PackageInterface $package)
    {
        $extra = $package->getExtra();

        return isset($extra['install_dir']) ? trim($extra['install_dir'], '/') : 'bitrix/modules';
    }

    public function getInstallPath(PackageInterface $package)
    {
        $documentRoot = $this->getDocumentRoot($package);
        $installDir = $this->getInstallDir($package);
        $moduleName = $package->getExtra()['module_name'];

        return "$documentRoot/$installDir/$moduleName";
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return self::PACKAGE_TYPE === $packageType;
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $name = $package->getExtra()['module_name'];
        $this->initBitrix($package);
        if (!\CModule::IncludeModule($name)) {
            $module = $this->getModule($package, $name);
            $module->DoInstall();
        }
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $name = $package->getExtra()['module_name'];
        $this->initBitrix($package);
        if (!\CModule::IncludeModule($name)) {
            $module = $this->getModule($package, $name);
            $module->DoUninstall();
        }
        parent::uninstall($repo, $package);
    }

    protected function getDocumentRoot(PackageInterface $package)
    {
        $initDir = dirname(realpath(\Composer\Factory::getComposerFile()));
        $searchDir = realpath($initDir);
        while (true) {
            $searchFile = $searchDir.'/bitrix/modules/main/include/prolog_before.php';
            if (file_exists($searchFile)) {
                return $searchDir;
            }
            if ($searchDir === '/') {
                throw new \Exception('Can not found document root');
            }
            $searchDir = realpath($searchDir.'/../');
        }
    }

    protected function initBitrix(PackageInterface $package)
    {
        $_SERVER['DOCUMENT_ROOT'] = $this->getDocumentRoot($package);
        define('STOP_STATISTICS', true);
        define("NO_KEEP_STATISTIC", "Y");
        define("NO_AGENT_STATISTIC", "Y");
        define("NOT_CHECK_PERMISSIONS", true);
        require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
        $GLOBALS['APPLICATION']->RestartBuffer();
    }

    protected function getModule(PackageInterface $package, $module)
    {
        $installDir = $this->getInstallDir($package);
        require_once $_SERVER['DOCUMENT_ROOT']."/$installDir/".$module."/install/index.php";
        $class = str_replace(".", "_", $module);
        if (!class_exists($class)) {
            throw new \Exception("Class $class does not exist");
        }
        $module = new $class();
        return $module;
    }
}