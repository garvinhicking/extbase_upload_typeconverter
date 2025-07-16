<?php

declare(strict_types=1);

use Garvinhicking\ExtbaseUploadTypeconverter\Domain\Model\FileReference;

// TypeConverter needed!

return [
    /*
    \TYPO3\CMS\Core\Resource\FileReference::class => [
        'tableName' => 'sys_file_reference',
        'properties' => [
            'uid_local' =>
                [
                    'fieldName' => 'originalFileIdentifier',
                ],
        ],
    ],

    \TYPO3\CMS\Extbase\Domain\Model\FileReference::class => [
        'tableName' => 'sys_file_reference',
        'properties' => [
            'uid_local' =>
                [
                    'fieldName' => 'originalFileIdentifier',
                ],
        ],
    ],
    */
    \Garvinhicking\ExtbaseUploadTypeconverter\Domain\Model\FileReference::class => [
        'tableName' => 'sys_file_reference',
        'properties' => [
            'uid_local' =>
                [
                    'fieldName' => 'originalFileIdentifier',
                ],
        ],
    ],
];
