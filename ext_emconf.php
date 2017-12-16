<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Extension Development Evaluator',
    'description' => 'A backend development module that offers features to help develop and evaluate various features of extensions under development',
    'category' => 'module',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'TYPO3 v4 Core Team',
    'author_email' => '',
    'author_company' => '',
    'version' => '4.0.0-dev',
    'constraints' => [
        'depends' => [
            'php' => '7.0.0-7.2.0',
            'typo3' => '7.6.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
