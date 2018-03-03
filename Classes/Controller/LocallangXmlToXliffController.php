<?php
declare(strict_types=1);

namespace Tx\Extdeveval\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 extension "extdeveval".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;

class LocallangXmlToXliffController extends AbstractModuleController
{
    /**
     * @var string
     */
    protected $extension;

    /**
     * @param string $packageKey
     * @param string $packageFile
     * @throws UnsupportedRequestTypeException If the request is not a web request
     * @throws StopActionException
     */
    public function convertFileAction(string $packageKey = '', string $packageFile = '')
    {
        $this->extension = $packageKey;
        $prefixedFilePath = 'EXT:' . $packageKey . '/' . $packageFile;
        $xmlFile = GeneralUtility::getFileAbsFileName($prefixedFilePath);

        if ($xmlFile === '') {
            throw new \InvalidArgumentException('The path of the file could not be determined: ' . $prefixedFilePath);
        }

        if (!is_file($xmlFile)) {
            throw new \InvalidArgumentException('The selected file does not exist: ' . $xmlFile);
        }

        $fileCheckError = $this->checkXmlFilename($xmlFile);
        if ($fileCheckError) {
            throw new \RuntimeException($fileCheckError);
        }

        $languages = $this->getAvailableTranslations($xmlFile);
        $hasError = false;
        foreach ($languages as $langKey) {
            $newFileName = $langKey === 'default' ? $xmlFile : $this->localizedFileRef($xmlFile, $langKey);
            $newFileName = preg_replace('#\.xml$#', '.xlf', $newFileName);
            if (@is_file($newFileName)) {
                $hasError = true;
                $this->addFlashMessage(
                    'Output file "' . $newFileName . '" already exists!',
                    '',
                    FlashMessage::ERROR
                );
            }
        }

        if ($hasError) {
            $this->redirect(
                'selectFile',
                null,
                null,
                [
                    'packageKey' => $packageKey,
                    'packageFile' => $packageFile,
                ]
            );
        }

        foreach ($languages as $langKey) {
            $newFileName = $langKey === 'default' ? $xmlFile : $this->localizedFileRef(
                $xmlFile,
                $langKey
            );
            $newFileName = preg_replace('#\.xml$#', '.xlf', $newFileName);

            $this->renderSaveDone($xmlFile, $newFileName, $langKey);
        }

        $this->redirect('selectFile');
    }

    /**
     * @param string $packageKey
     * @param string $originalPackageKey
     * @param string $packageFile
     */
    public function selectFileAction(
        string $packageKey = '',
        string $originalPackageKey = '',
        string $packageFile = ''
    ) {
        $this->view->assignMultiple(
            [
                'packageKey' => $packageKey,
                'packageFile' => $packageKey === $originalPackageKey ? $packageFile : '',
            ]
        );
    }

    /**
     * Checking for a valid locallang*.xml filename.
     *
     * @param string $xmlFile Absolute reference to the ll-XML locallang file
     * @return string Empty (false) return value means "OK" while otherwise is an error string
     */
    protected function checkXmlFilename($xmlFile)
    {
        $basename = basename($xmlFile);
        if (!GeneralUtility::isFirstPartOfStr($basename, 'locallang')) {
            return 'Filename didn\'t start with "locallang".';
        }

        return '';
    }

    /**
     * @param string $xmlFile Absolute reference to the ll-XML base locallang file
     * @return array
     */
    protected function getAvailableTranslations($xmlFile)
    {
        $ll = GeneralUtility::xml2array(file_get_contents($xmlFile));
        if (!isset($ll['data'])) {
            throw new \RuntimeException('data section not found in "' . $xmlFile . '"', 1314187884);
        }
        return array_keys($ll['data']);
    }

    /**
     * Includes locallang files and returns raw $LOCAL_LANG array
     *
     * @param string $xmlFile Absolute reference to the ll-XML locallang file.
     * @return array LOCAL_LANG array from ll-XML file (with all possible sub-files for languages included)
     */
    protected function getLLarray($xmlFile)
    {
        $ll = GeneralUtility::xml2array(file_get_contents($xmlFile));
        if (!isset($ll['data'])) {
            throw new \RuntimeException('data section not found in "' . $xmlFile . '"', 1314187884);
        }
        $includedLanguages = array_keys($ll['data']);
        $LOCAL_LANG = [];

        foreach ($includedLanguages as $langKey) {
            $parser = GeneralUtility::makeInstance(LocallangXmlParser::class);
            $llang = $parser->getParsedData($xmlFile, $langKey, $GLOBALS['LANG']->charSet);
            unset($parser);
            ksort($llang[$langKey]);
            $LOCAL_LANG[$langKey] = $llang[$langKey];
        }

        return $LOCAL_LANG;
    }

    /**
     * Returns localized fileRef ([langkey].locallang*.xml)
     *
     * @param string $fileRef Filename/path of a 'locallang*.xml' file
     * @param string $lang Language key
     * @return string Input filename with a '[lang-key].locallang*.xml' name if $this->lang is not 'default'
     */
    protected function localizedFileRef($fileRef, $lang)
    {
        if ($lang === 'default') {
            throw new \RuntimeException('Can not build localized file reference for default language');
        }

        if (substr($fileRef, -4) !== '.xml') {
            throw new \RuntimeException('Can not build localized file reference for non xml file');
        }

        return dirname($fileRef) . '/' . $lang . '.' . basename($fileRef);
    }

    /**
     * Processing of the submitted form; Will create and write the XLIFF file and tell the new file name.
     *
     * @param string $xmlFile Absolute path to the locallang.xml file to convert
     * @param string $newFileName The new file name to write to (absolute path, .xlf ending)
     * @param string $langKey The language key
     */
    protected function renderSaveDone($xmlFile, $newFileName, $langKey)
    {

        // Initialize variables:
        $xml = [];
        $LOCAL_LANG = $this->getLLarray($xmlFile);

        $xml[] = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>';
        $xml[] = '<xliff version="1.0">';
        $xml[] = '	<file source-language="en"' . ($langKey !== 'default' ? ' target-language="' . $langKey . '"' : '')
            . ' datatype="plaintext" original="messages" date="' . gmdate('Y-m-d\TH:i:s\Z') . '"'
            . ' product-name="' . $this->extension . '">';
        $xml[] = '		<header/>';
        $xml[] = '		<body>';

        foreach ($LOCAL_LANG[$langKey] as $key => $data) {
            $source = $data[0]['source'];
            $target = $data[0]['target'];

            if ($langKey === 'default') {
                $xml[] = '			<trans-unit id="' . $key . '" xml:space="preserve">';
                $xml[] = '				<source>' . htmlspecialchars($source) . '</source>';
                $xml[] = '			</trans-unit>';
            } else {
                $xml[] = '			<trans-unit id="' . $key . '" xml:space="preserve" approved="yes">';
                $xml[] = '				<source>' . htmlspecialchars((string)$source) . '</source>';
                $xml[] = '				<target>' . htmlspecialchars((string)$target) . '</target>';
                $xml[] = '			</trans-unit>';
            }
        }

        $xml[] = '		</body>';
        $xml[] = '	</file>';
        $xml[] = '</xliff>';

        if (!file_exists($newFileName)) {
            GeneralUtility::writeFile($newFileName, implode(LF, $xml));
            $this->addFlashMessage('File written to disk: ' . $newFileName);
            return;
        }

        $this->addFlashMessage('File already exists: ' . $newFileName, '', FlashMessage::WARNING);
    }
}
