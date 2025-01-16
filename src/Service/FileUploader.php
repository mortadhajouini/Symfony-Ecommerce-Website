<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file)
    {
        // Ensure the target directory exists
        if (!file_exists($this->targetDirectory)) {
            mkdir($this->targetDirectory, 0777, true);
        }

        // Validate the file if needed

        // Generate a unique filename
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        try {
            // Move the file to the target directory
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {
            // Log or handle the exception as needed
            throw new FileException('Unable to upload the file');
        }

        // Log the upload if needed

        return $fileName;
    }
}
