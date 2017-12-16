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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;

class ExtensionFileSelectViewHelper extends SelectViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument(
            'extensionKey',
            'string',
            'The extension key for which the files should be displayed.',
            true
        );

        $this->registerArgument(
            'fileExtensions',
            'array',
            'The extension key for which the files should be displayed.',
            false,
            ['php', 'inc']
        );
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $extensionPath = ExtensionManagementUtility::extPath($this->arguments['extensionKey']);
        $phpFiles = GeneralUtility::removePrefixPathFromList(
            GeneralUtility::getAllFilesAndFoldersInPath(
                [],
                $extensionPath,
                implode(',', $this->arguments['fileExtensions']),
                0,
                99
            ),
            $extensionPath
        );

        sort($phpFiles);
        $optionsArray = ['' => ''];
        foreach ($phpFiles as $phpName) {
            $optionsArray[$phpName] = $phpName;
        }

        return $optionsArray;
    }
}
