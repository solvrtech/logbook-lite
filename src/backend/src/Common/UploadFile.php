<?php

namespace App\Common;

use App\Common\Config\FileSystemConfig;
use Exception;
use Ketut\RandomString\Random;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;

class UploadFile
{
    /**
     * Creates a temporary directory.
     *
     * @param string $path
     *
     * @return string
     *
     * @throws Exception
     */
    public function createTemporaryDirectory(string $path): string
    {
        $filesystem = new Filesystem();
        $temporaryDirectory = FileSystemConfig::TMP_DIR.$path;

        if (!$filesystem->exists($temporaryDirectory)) {
            try {
                $filesystem->mkdir($temporaryDirectory);
            } catch (IOExceptionInterface $exception) {
                throw new Exception("An error occurred while creating new temporary directory");
            }
        }

        return $temporaryDirectory;
    }

    /**
     * Creates a directory.
     *
     * @param string $path
     *
     * @throws Exception
     */
    public function createDirectory(string $path): void
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($path)) {
            try {
                $filesystem->mkdir($path, 755);
            } catch (IOExceptionInterface $exception) {
                throw new Exception("An error occurred while creating new directory");
            }
        }
    }

    /**
     * Upload file into public directory.
     *
     * @param File $file
     * @param string $destination
     *
     * @return string
     *
     * @throws Exception
     */
    public function uploadFile(File $file, string $destination): string
    {
        $slugger = new AsciiSlugger();
        $urlizedFileName = $slugger->slug(
            self::generateFileName()
        );

        $newFile = $file->move(
            $destination,
            $urlizedFileName.'.'.$file->getExtension()
        );

        return $newFile->getPath().'/'.$newFile->getFilename();
    }

    /**
     * Generate a new filename.
     *
     * @return string
     *
     * @throws Exception
     */
    private function generateFileName(): string
    {
        return (new Random())
            ->length(64)
            ->lowercase()
            ->numeric()
            ->generate();
    }

    /**
     * Retrieves the file extension based on the binary content.
     *
     * @param string $binary
     *
     * @return string
     *
     * @throws Exception
     */
    public function getExtensionOfBinary(string $binary): string
    {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($info, $binary);
        finfo_close($info);

        if ($mimeType) {
            $extensions = [
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
            ];

            // Check if the MIME type is in the extensions array
            if (isset($extensions[$mimeType])) {
                return $extensions[$mimeType];
            }
        }

        throw new Exception('The extension of the file is not recognized');
    }
}