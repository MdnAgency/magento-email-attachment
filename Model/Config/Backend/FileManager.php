<?php
/**
 * FileUploader
 *
 * @copyright Copyright © 2023 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Maisondunet\EmailAttachment\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File\RequestData;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

class FileManager
{
    const FILE_PATH = "mdn_email_attachments/files/";

    /**
     * @var WriteInterface
     */
    protected WriteInterface $directoryWrite;
    private UploaderFactory $uploaderFactory;
    private RequestData $requestData;
    private array $allowedExtensions;
    private ManagerInterface $messageManager;
    private File $file;
    private LoggerInterface $logger;
    private Filesystem\Directory\ReadInterface $directoryRead;

    /**
     * @param UploaderFactory  $uploaderFactory
     * @param RequestData      $requestData
     * @param Filesystem       $filesystem
     * @param ManagerInterface $messageManager
     * @param File             $file
     * @param LoggerInterface  $logger
     * @param array            $allowedExtensions
     *
     * @throws FileSystemException
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        RequestData $requestData,
        Filesystem $filesystem,
        ManagerInterface $messageManager,
        File $file,
        LoggerInterface $logger,
        array $allowedExtensions = []
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->requestData = $requestData;
        $this->allowedExtensions = $allowedExtensions;
        $this->directoryWrite = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->directoryRead = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->messageManager = $messageManager;
        $this->file = $file;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    private function getAbsolutePath(): string
    {
        return $this->directoryWrite->getAbsolutePath(self::FILE_PATH);
    }

    /**
     * @param string $path
     * @param        $field
     *
     * @return string|null
     * @throws \Exception
     */
    public function handleUpload(string $path, $field): ?string
    {
        $fileData = $this->getFileData($path, $field);
        if (isset($fileData['tmp_name']) && $fileData['tmp_name']) {
            $target = $this->directoryWrite->getAbsolutePath(self::FILE_PATH);
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $fileData]);
                $uploader->setAllowedExtensions($this->allowedExtensions);
                $uploader->setAllowRenameFiles(true);
                $result = $uploader->save($target);
                return $result['file'];
            } catch (ValidationException|LocalizedException $e) {
                $this->messageManager->addErrorMessage($fileData['name'] . ": " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * Receiving uploaded file data
     *
     * @return array
     * @since 100.1.0
     */
    protected function getFileData(string $path, $field)
    {
        $file = [];
        $tmpName = $this->requestData->getTmpName($path);
        if (isset($tmpName[$field]["file"]['file_new'])) {
            $file['tmp_name'] = $tmpName[$field]["file"]['file_new'];
            $name = $this->requestData->getName($path);
            $file['name'] = $name[$field]["file"]['file_new'];
        }
        return $file;
    }

    /**
     * Delete the given file if it exists
     * @param $fileName
     *
     * @return void
     */
    public function delete($fileName)
    {
        try {
            if ($this->file->isExists($this->getAbsolutePath() . $fileName)) {
                $this->file->deleteFile($this->getAbsolutePath() . $fileName);
            }
        } catch (FileSystemException $e) {
            $this->logger->notice("Unable to delete email attachment", ["exception" => $e]);
        }
    }

    public function getFileContent($file): ?string
    {
        $filePath = self::FILE_PATH . DIRECTORY_SEPARATOR . $file;
        if ($this->directoryRead->isFile($filePath)) {
            try {
                return $this->directoryRead->readFile($filePath);
            } catch (FileSystemException $e) {
                return null;
            }
        }
        return null;
    }

    public function getFileMime($file): ?string
    {
        $filePath = $this->getAbsolutePath() . DIRECTORY_SEPARATOR . $file;
        try {
            return mime_content_type($filePath);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
