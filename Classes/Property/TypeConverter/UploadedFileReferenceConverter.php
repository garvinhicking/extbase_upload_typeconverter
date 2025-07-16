<?php

declare(strict_types=1);

namespace Garvinhicking\ExtbaseUploadTypeconverter\Property\TypeConverter;

use Exception;
use Garvinhicking\ExtbaseUploadTypeconverter\Domain\Model\FileReference;
use InvalidArgumentException;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File as FalFile;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference as FalFileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use TYPO3\CMS\Extbase\Security\Exception\InvalidHashException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

// TypeConverter needed!
class UploadedFileReferenceConverter extends AbstractTypeConverter
{
    /**
     * Folder where the file upload should go to (including storage).
     */
    public const CONFIGURATION_UPLOAD_FOLDER = '1';

    /**
     * How to handle a upload when the name of the uploaded file conflicts.
     */
    public const CONFIGURATION_UPLOAD_CONFLICT_MODE = '2';

    /**
     * Whether to replace an already present resource.
     * Useful for "maxitems = 1" fields and properties
     * with no ObjectStorage annotation.
     */
    public const CONFIGURATION_ALLOWED_FILE_EXTENSIONS = '4';

    // Note: Must not have a type declaration due to abstract!
    protected string $defaultUploadFolder = '1:/user_upload/';

    // Note: Must not have a type declaration due to abstract!
    protected $sourceTypes = ['array'];

    // Note: Must not have a type declaration due to abstract!
    protected $targetType = 'TYPO3\\CMS\\Extbase\\Domain\\Model\\FileReference';

    /**
     * Take precedence over the available FileReferenceConverter
     * Note: Must not have a type declaration due to abstract!
     */
    protected $priority = 30;

    /**
     * @var FileInterface[]
     */
    protected array $convertedResources = [];

    public function __construct(
        protected ResourceFactory $resourceFactory,
        protected HashService $hashService,
        protected PersistenceManager $persistenceManager,
        protected FileNameValidator $filenameValidator,
        protected StorageRepository $storageRepository,
    ) {
    }

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface|null $configuration
     * @return Error|FileInterface|ExtbaseFileReference|FileReference|null
     * @throws FileDoesNotExistException
     * @throws InvalidArgumentForHashGenerationException
     * @throws InvalidHashException
     * @throws ResourceDoesNotExistException
     * @api
     */
    public function convertFrom(
        mixed $source,
        string $targetType,
        array $convertedChildProperties = [],
        ?PropertyMappingConfigurationInterface $configuration = null
    ): Error|ExtbaseFileReference|FileReference|FileInterface|null {

        if (!isset($source['error']) || $source['error'] === \UPLOAD_ERR_NO_FILE) {
            if (isset($source['submittedFile']['resourcePointer'])) {
                try {
                    $resourcePointer = $this->hashService->validateAndStripHmac(
                        $source['submittedFile']['resourcePointer']
                    );

                    if (str_starts_with($resourcePointer, 'file:')) {
                        $fileUid = substr($resourcePointer, 5);

                        return $this->createFileReferenceFromFalFileObject(
                            $this->resourceFactory->getFileObject((int) $fileUid)
                        );
                    }

                    return $this->createFileReferenceFromFalFileReferenceObject(
                        $this->resourceFactory->getFileReferenceObject((int) $resourcePointer),
                        (int) $resourcePointer
                    );
                } catch (InvalidArgumentException) {
                    // Nothing to do. No file is uploaded and resource pointer is invalid. Discard!
                    return null;
                }
            }

            return null;
        }

        if ($source['error'] !== \UPLOAD_ERR_OK) {
            switch ($source['error']) {
                case \UPLOAD_ERR_INI_SIZE:
                case \UPLOAD_ERR_FORM_SIZE:
                case \UPLOAD_ERR_PARTIAL:
                    return new Error('Error Code: ' . $source['error'], 1264440823);
                default:
                    return new Error(
                        'An error occurred while uploading. ' .
                        'Please try again or contact the administrator if the problem remains. ' .
                        'Target type: ' . $targetType . '. ' .
                        'Children: ' . count($convertedChildProperties) . '. ',
                        1340193849
                    );
            }
        }

        if (isset($this->convertedResources[$source['tmp_name']])) {
            return $this->convertedResources[$source['tmp_name']];
        }

        try {
            $resource = $this->importUploadedResource($source, $configuration);
        } catch (Exception $e) {
            return new Error($e->getMessage(), $e->getCode());
        }

        $this->convertedResources[$source['tmp_name']] = $resource;

        return $resource;
    }

    /**
     * Import a resource and respect configuration given for properties
     *
     * @param array $uploadInfo
     * @param PropertyMappingConfigurationInterface $configuration
     * @return ExtbaseFileReference|FileReference
     * @throws TypeConverterException
     */
    protected function importUploadedResource(
        array $uploadInfo,
        PropertyMappingConfigurationInterface $configuration
    ): ExtbaseFileReference|FileReference {

        if (!$this->filenameValidator->isValid($uploadInfo['name'])) {
            throw new TypeConverterException(
                'File validation error.',
                1399312430
            );
        }

        $allowedFileExtensions = $configuration->getConfigurationValue(
            self::class,
            self::CONFIGURATION_ALLOWED_FILE_EXTENSIONS
        );

        if ($allowedFileExtensions !== null) {
            $filePathInfo = PathUtility::pathinfo($uploadInfo['name']);
            if (!GeneralUtility::inList($allowedFileExtensions, strtolower($filePathInfo['extension']))) {
                throw new TypeConverterException(
                    'File validation error.',
                    1399312430
                );
            }
        }

        $uploadFolderId = $configuration->getConfigurationValue(
            self::class,
            self::CONFIGURATION_UPLOAD_FOLDER
        ) ?: $this->defaultUploadFolder;

        $storage = $this->storageRepository->findByCombinedIdentifier($uploadFolderId);
        $combinedIdentifierParts = explode(':', $uploadFolderId);
        if (!$storage->hasFolder($combinedIdentifierParts[1])) {
            $storage->createFolder($combinedIdentifierParts[1]);
        }

        $defaultConflictMode = DuplicationBehavior::RENAME;

        $conflictMode = $configuration->getConfigurationValue(
            self::class,
            self::CONFIGURATION_UPLOAD_CONFLICT_MODE
        ) ?: $defaultConflictMode;

        $uploadFolder = $this->resourceFactory->retrieveFileOrFolderObject($uploadFolderId);
        $uploadedFile = $uploadFolder->addUploadedFile($uploadInfo, $conflictMode);

        $resourcePointer = isset($uploadInfo['submittedFile']['resourcePointer']) &&
        !str_contains($uploadInfo['submittedFile']['resourcePointer'], 'file:')
            ? (int) $this->hashService->validateAndStripHmac($uploadInfo['submittedFile']['resourcePointer'])
            : null;

        return $this->createFileReferenceFromFalFileObject($uploadedFile, $resourcePointer);
    }

    protected function createFileReferenceFromFalFileObject(
        FalFile $file,
        ?int $resourcePointer = null
    ): FileReference {
        $newObject = [
            'uid_local' => $file->getUid(),
            'uid_foreign' => StringUtility::getUniqueId('NEW'),
            'uid' => StringUtility::getUniqueId('NEW'),
            'crop' => null,
        ];

        $fileReference = $this->resourceFactory->createFileReferenceObject($newObject);

        return $this->createFileReferenceFromFalFileReferenceObject($fileReference, $resourcePointer);
    }

    protected function createFileReferenceFromFalFileReferenceObject(
        FalFileReference $falFileReference,
        ?int $resourcePointer = null
    ): FileReference {
        if ($resourcePointer === null) {
            $fileReference = GeneralUtility::makeInstance(FileReference::class);
        } else {
            $fileReference = $this->persistenceManager->getObjectByIdentifier(
                $resourcePointer,
                FileReference::class
            );
        }

        $fileReference->setOriginalResource($falFileReference);

        return $fileReference;
    }
}
