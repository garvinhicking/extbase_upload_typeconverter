<?php

declare(strict_types=1);

namespace Garvinhicking\ExtbaseUploadTypeconverter\Controller;

use Garvinhicking\ExtbaseUploadTypeconverter\Domain\Model\Singlefile;
use Garvinhicking\ExtbaseUploadTypeconverter\Domain\Repository\SinglefileRepository;
// TypeConverter needed!
use Garvinhicking\ExtbaseUploadTypeconverter\Property\TypeConverter\UploadedFileReferenceConverter;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class SingleFileUploadController extends ActionController
{
    public function __construct(protected readonly SinglefileRepository $singlefileRepository)
    {
    }

    public function listAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'items' => $this->singlefileRepository->findAll(),
        ]);

        return $this->htmlResponse();
    }

    public function newAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'item' => GeneralUtility::makeInstance(Singlefile::class),
        ]);

        return $this->htmlResponse();
    }

    // TypeConverter needed!
    public function initializeCreateAction(): void
    {
        $this->setTypeConverterConfiguration();
    }

    private function setTypeConverterConfiguration() {
        // 'item' is the name of the <f:form> object
        // You probably do not want this hardcoded here.
        $propertyMapConfig = $this->arguments['item']->getPropertyMappingConfiguration();

        // You'll need to configure allowed file types decentrally or allow
        // shortcuts like 'common-image-types'
        $allowedFileTypes = ['jpg', 'jpeg', 'gif', 'png'];

        // You also do not want to have this hardcoded here:
        $falStorage = '1:/uploads';
        $uploadConfiguration = [
            UploadedFileReferenceConverter::CONFIGURATION_ALLOWED_FILE_EXTENSIONS
            => implode(
                ',',
                $allowedFileTypes
            ),
            UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER
            => $falStorage,
        ];

        // Name of the property of the model where the file is stored.
        // You guessed right: you wouldn't want this hard-coded here.
        $propertyMapConfig->forProperty('file')
            ->setTypeConverterOptions(
                UploadedFileReferenceConverter::class,
                $uploadConfiguration
            );

    }

    public function createAction(Singlefile $item): ResponseInterface
    {
        $item->setPid((int)($this->settings['singleFileUploadPid'] ?? 0));
        $this->singlefileRepository->add($item);

        return $this->redirect('list');
    }

    public function showAction(Singlefile $item): ResponseInterface
    {
        $this->view->assignMultiple([
            'item' => $item,
        ]);

        return $this->htmlResponse();
    }

    /**
     * @IgnoreValidation("item")
     */
    public function editAction(Singlefile $item): ResponseInterface
    {
        $this->view->assignMultiple([
            'item' => $item,
        ]);

        return $this->htmlResponse();
    }

    public function updateAction(Singlefile $item): ResponseInterface
    {
        $this->singlefileRepository->update($item);

        return $this->redirect('list');
    }
}
