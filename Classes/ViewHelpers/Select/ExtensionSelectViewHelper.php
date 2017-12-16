<?php
declare(strict_types=1);

namespace Tx\Extdeveval\ViewHelpers\Select;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tinyurls".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;

class ExtensionSelectViewHelper extends SelectViewHelper
{
    /**
     * @var PackageManager
     */
    private $packageManager;

    public function injectPackageManager(PackageManager $packageManager)
    {
        $this->packageManager = $packageManager;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $extensionOptions = [];

        $activeExtensions = $this->packageManager->getActivePackages();
        foreach ($activeExtensions as $activeExtension) {
            if ($this->isSystemOrVendorPackage($activeExtension)) {
                continue;
            }
            $extensionOptions[$activeExtension->getPackageKey()] = $activeExtension->getPackageKey();
        }

        return $extensionOptions;
    }

    private function isSystemOrVendorPackage(PackageInterface $package)
    {
        $packagePath = $package->getPackagePath();
        if (strpos($packagePath, 'sysext/') !== false) {
            return true;
        }
        if (strpos($packagePath, 'typo3conf/ext/') !== false) {
            return false;
        }
        throw new \RuntimeException('Could not detect Extension type, path is ' . $packagePath);
    }
}
