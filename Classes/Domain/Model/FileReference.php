<?php

declare(strict_types=1);

namespace Garvinhicking\ExtbaseUploadTypeconverter\Domain\Model;

use TYPO3\CMS\Core\Resource\FileReference as FileReferenceInterface;
use TYPO3\CMS\Core\Resource\ResourceInterface;

// TypeConverter needed!
class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{
    protected int $originalFileIdentifier = 0;

    public function setOriginalResource(ResourceInterface $originalResource): void
    {
        if (is_a($originalResource, FileReferenceInterface::class)) {
            $this->originalResource = $originalResource;
            $this->originalFileIdentifier = $this->uidLocal = $originalResource->getOriginalFile()->getUid();
        }
    }
}
