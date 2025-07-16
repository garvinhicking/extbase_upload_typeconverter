<?php

defined('TYPO3') or die();

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerPlugin(
    'extbase_upload_typeconverter',
    'Pi1',
    'Upload plugin for single file property in a domain object'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['ExtbaseUploadTypeconverter_pi1'] = 'layout,recursive,pages';
