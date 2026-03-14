<?php

namespace common\modules\fileManager\services;

use common\modules\fileManager\models\StoredFile;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

class FileStorageService
{
    private string $basePathAlias;
    private string $storedFileClass;

    public function __construct(string $basePathAlias = '@runtime/file-manager', string $storedFileClass = StoredFile::class)
    {
        $this->basePathAlias = $basePathAlias;
        $this->storedFileClass = $storedFileClass;
    }

    /**
     * @param array{
     *     originalName?:string|null,
     *     mimeType?:string|null,
     *     extension?:string|null,
     *     category?:string|null,
     *     source?:string,
     *     sourceFileId?:string|null,
     *     sourceUniqueId?:string|null
     * } $metadata
     */
    public function store(string $content, array $metadata = []): ?StoredFile
    {
        if ($content === '') {
            throw new InvalidArgumentException('File content cannot be empty.');
        }

        $extension = $this->normalizeExtension(
            $metadata['extension'] ?? $this->extractExtension($metadata['originalName'] ?? null)
        );
        $relativePath = $this->buildRelativePath(
            $metadata['source'] ?? StoredFile::SOURCE_SYSTEM,
            $metadata['category'] ?? null,
            $extension
        );
        $absolutePath = $this->buildAbsolutePath($relativePath);

        $this->ensureDirectory(dirname($absolutePath));
        if (file_put_contents($absolutePath, $content) === false) {
            throw new Exception('Unable to write file to storage.');
        }

        $file = $this->createStoredFileModel();
        $file->setAttributes([
            'storage' => StoredFile::STORAGE_LOCAL,
            'path' => $relativePath,
            'original_name' => $metadata['originalName'] ?? null,
            'extension' => $extension,
            'mime_type' => $metadata['mimeType'] ?? null,
            'size_bytes' => strlen($content),
            'checksum_sha256' => hash('sha256', $content),
            'source' => $metadata['source'] ?? StoredFile::SOURCE_SYSTEM,
            'source_file_id' => $metadata['sourceFileId'] ?? null,
            'source_unique_id' => $metadata['sourceUniqueId'] ?? null,
        ], false);

        if ($file->save()) {
            return $file;
        }

        @unlink($absolutePath);

        return null;
    }

    /**
     * @param array{
     *     originalName?:string|null,
     *     mimeType?:string|null,
     *     extension?:string|null,
     *     category?:string|null,
     *     source?:string,
     *     sourceFileId?:string|null,
     *     sourceUniqueId?:string|null
     * } $metadata
     */
    public function storeFromPath(string $sourcePath, array $metadata = []): ?StoredFile
    {
        if (!is_file($sourcePath)) {
            throw new InvalidArgumentException('Source file does not exist: ' . $sourcePath);
        }

        $content = file_get_contents($sourcePath);
        if ($content === false) {
            throw new Exception('Unable to read source file: ' . $sourcePath);
        }

        if (!isset($metadata['originalName'])) {
            $metadata['originalName'] = basename($sourcePath);
        }

        if (!isset($metadata['extension'])) {
            $metadata['extension'] = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: null;
        }

        if (!isset($metadata['mimeType']) && function_exists('mime_content_type')) {
            $mimeType = mime_content_type($sourcePath);
            $metadata['mimeType'] = $mimeType !== false ? $mimeType : null;
        }

        return $this->store($content, $metadata);
    }

    public function getAbsolutePath(StoredFile $file): string
    {
        return $this->buildAbsolutePath($file->path);
    }

    private function buildRelativePath(string $source, ?string $category, ?string $extension): string
    {
        $sourceSegment = $this->normalizePathSegment($source) ?? StoredFile::SOURCE_SYSTEM;
        $categorySegment = $this->normalizePathSegment($category);
        $datePath = date('Y/m/d');
        $randomName = Yii::$app->security->generateRandomString(24);
        $fileName = $extension !== null ? $randomName . '.' . $extension : $randomName;

        $pathParts = [$sourceSegment];
        if ($categorySegment !== null) {
            $pathParts[] = $categorySegment;
        }

        $pathParts[] = $datePath;
        $pathParts[] = $fileName;

        return implode('/', $pathParts);
    }

    private function buildAbsolutePath(string $relativePath): string
    {
        return rtrim(Yii::getAlias($this->basePathAlias), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);
    }

    private function ensureDirectory(string $directory): void
    {
        FileHelper::createDirectory($directory, 0775, true);
    }

    private function extractExtension(?string $originalName): ?string
    {
        if ($originalName === null || $originalName === '') {
            return null;
        }

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        return $extension !== '' ? $extension : null;
    }

    private function normalizeExtension(?string $extension): ?string
    {
        if ($extension === null || $extension === '') {
            return null;
        }

        return Inflector::slug(ltrim(mb_strtolower($extension), '.'), '');
    }

    private function normalizePathSegment(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = Inflector::slug(mb_strtolower(trim($value)), '-');

        return $normalized !== '' ? $normalized : null;
    }

    private function createStoredFileModel(): StoredFile
    {
        $storedFileClass = $this->storedFileClass;
        $file = new $storedFileClass();

        if (!$file instanceof StoredFile) {
            throw new InvalidArgumentException('Stored file class must extend ' . StoredFile::class . '.');
        }

        return $file;
    }
}
