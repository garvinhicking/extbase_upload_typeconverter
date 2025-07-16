<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Extbase Upload development extension',
    'description' => 'TYPO3 Extbase Upload extension for testing purposes only',
    'category' => 'plugin',
    'author' => 'Torben Hansen, Garvin Hicking',
    'author_email' => 'derhansen@gmail.com, garvin@hick.ing',
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
