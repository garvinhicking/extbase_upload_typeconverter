<?php

declare(strict_types=1);

namespace Garvinhicking\ExtbaseUploadTypeconverter\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Singlefile extends AbstractEntity
{
    protected ?FileReference $file = null;

    public function getFile(): ?FileReference
    {
        return $this->file;
    }

    public function setFile(?FileReference $file): void
    {
        $this->file = $file;
    }
}
