<?php

namespace common\modules\fileManager\tests\unit\services;

use Codeception\Test\Unit;
use common\modules\fileManager\models\StoredFile;
use common\modules\fileManager\services\FileStorageService;

class FileStorageServiceTest extends Unit
{
    private string $basePath;

    protected function _before(): void
    {
        $this->basePath = codecept_output_dir('file-storage-test');
    }

    protected function _after(): void
    {
        $this->removeDirectory($this->basePath);
    }

    public function testStoreSavesFileAndReturnsMetadata(): void
    {
        $service = new FileStorageService($this->basePath, FakeStoredFile::class);

        $file = $service->store('voice payload', [
            'originalName' => 'voice-note.ogg',
            'mimeType' => 'audio/ogg',
            'category' => 'voice messages',
            'source' => StoredFile::SOURCE_TELEGRAM,
            'sourceFileId' => 'telegram-file-id',
            'sourceUniqueId' => 'telegram-unique-id',
        ]);

        $this->assertInstanceOf(FakeStoredFile::class, $file);
        $this->assertSame(StoredFile::STORAGE_LOCAL, $file->storage);
        $this->assertSame('voice-note.ogg', $file->original_name);
        $this->assertSame('ogg', $file->extension);
        $this->assertSame('audio/ogg', $file->mime_type);
        $this->assertSame(13, $file->size_bytes);
        $this->assertSame(hash('sha256', 'voice payload'), $file->checksum_sha256);
        $this->assertSame(StoredFile::SOURCE_TELEGRAM, $file->source);
        $this->assertSame('telegram-file-id', $file->source_file_id);
        $this->assertSame('telegram-unique-id', $file->source_unique_id);
        $this->assertMatchesRegularExpression('#^telegram/voice-messages/\d{4}/\d{2}/\d{2}/.+\.ogg$#', $file->path);
        $this->assertFileExists($service->getAbsolutePath($file));
        $this->assertSame('voice payload', file_get_contents($service->getAbsolutePath($file)));
    }

    public function testStoreRemovesPhysicalFileWhenModelSaveFails(): void
    {
        $service = new FileStorageService($this->basePath, FailingStoredFile::class);

        $file = $service->store('broken payload', [
            'originalName' => 'broken.ogg',
        ]);

        $this->assertNull($file);
        $this->assertCount(0, $this->listFiles($this->basePath));
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }

    /**
     * @return string[]
     */
    private function listFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );
        $files = [];

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}

class FakeStoredFile extends StoredFile
{
    public function attributes(): array
    {
        return [
            'id',
            'storage',
            'path',
            'original_name',
            'extension',
            'mime_type',
            'size_bytes',
            'checksum_sha256',
            'source',
            'source_file_id',
            'source_unique_id',
            'created_at',
            'updated_at',
        ];
    }

    public function save($runValidation = true, $attributeNames = null): bool
    {
        return true;
    }
}

class FailingStoredFile extends StoredFile
{
    public function attributes(): array
    {
        return [
            'id',
            'storage',
            'path',
            'original_name',
            'extension',
            'mime_type',
            'size_bytes',
            'checksum_sha256',
            'source',
            'source_file_id',
            'source_unique_id',
            'created_at',
            'updated_at',
        ];
    }

    public function save($runValidation = true, $attributeNames = null): bool
    {
        return false;
    }
}
