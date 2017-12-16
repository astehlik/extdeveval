<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE == 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Tx.Extdeveval',
        'tools',
        'extDevEvalMain',
        '',
        [
            'LocallangXmlToXliff' => 'selectFile,convertFile',
        ],
        [
            'labels' => 'LLL:EXT:extdeveval/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
}
