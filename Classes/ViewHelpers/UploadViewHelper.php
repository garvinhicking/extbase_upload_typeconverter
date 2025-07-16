<?php

declare(strict_types=1);

namespace Garvinhicking\ExtbaseUploadTypeconverter\ViewHelpers;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

// TypeConverter needed!
final class UploadViewHelper extends AbstractFormFieldViewHelper
{
    protected $tagName = 'input';

    public function __construct(private readonly HashService $hashService, private readonly PropertyMapper $propertyMapper)
    {
        parent::__construct();
    }

    /** COPY+PASTE of Core UploadViewHelper::initializeArguments */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
        $this->registerTagAttribute('multiple', 'string', 'Specifies that the file input element should allow multiple selection of files');
        $this->registerTagAttribute('accept', 'string', 'Specifies the allowed file extensions to upload');
        $this->registerTagAttribute('required', 'string', 'Marks the upload field as required');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this ViewHelper', false, 'f3-form-error');
        $this->registerUniversalTagAttributes();
    }

    /** COPY+PASTE of Core UploadViewHelper::render */
    public function parentRender(): string
    {
        $name = $this->getName();
        $allowedFields = ['name', 'type', 'tmp_name', 'error', 'size'];
        foreach ($allowedFields as $fieldName) {
            $this->registerFieldNameForFormTokenGeneration($name . '[' . $fieldName . ']');
        }
        $this->tag->addAttribute('type', 'file');

        if (isset($this->arguments['multiple'])) {
            $this->tag->addAttribute('name', $name . '[]');
        } else {
            $this->tag->addAttribute('name', $name);
        }

        // Setze das required-Attribut falls notwendig
        if (!empty($this->arguments['required'])) {
            $this->tag->addAttribute('required', 'required');
        }

        $this->setErrorClassAttribute();
        return $this->tag->render();
    }

    public function render(): string
    {
        $output = '';
        $resource = $this->getUploadedResource();
        if ($resource !== null) {
            $resourcePointerIdAttribute = '';
            if ($this->hasArgument('id')) {
                $resourcePointerIdAttribute = 'id="' . htmlspecialchars($this->arguments['id'], ENT_QUOTES) . '-file-reference"';
            }

            $resourcePointerValue = $resource->getUid();
            if ($resourcePointerValue === null) {
                // Newly created file reference which is not persisted yet.
                // Use the file UID instead, but prefix it with "file:" to communicate this to the type converter
                $resourcePointerValue = 'file:' . $resource->getOriginalResource()->getOriginalFile()->getUid();
            }

            $output .= sprintf(
                '<input type="hidden"
                               name="%s[submittedFile][resourcePointer]"
                               value="%s" %s />',
                $this->getName(),
                htmlspecialchars($this->hashService->appendHmac((string) $resourcePointerValue), ENT_QUOTES),
                $resourcePointerIdAttribute,
            );

            $this->templateVariableContainer->add('resource', $resource);
            $output .= $this->renderChildren();
            $this->templateVariableContainer->remove('resource');
        }

        $output .= $this->parentRender();
        return $output;
    }

    /**
     * Return a previously uploaded resource.
     * Return NULL if errors occurred during property mapping for this property.
     */
    protected function getUploadedResource(): ?FileReference
    {
        if ($this->getMappingResultsForProperty()->hasErrors()) {
            return null;
        }
        $resource = $this->getPropertyValue();

        if ($resource instanceof FileReference) {
            return $resource;
        }
        return $this->propertyMapper->convert($resource, 'TYPO3\\CMS\\Extbase\\Domain\\Model\\FileReference');
    }
}
