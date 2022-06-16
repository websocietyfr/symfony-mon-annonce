<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadFileService
{
    private $slugger;
    private $upload_path;

    public function __construct(string $upload_path, SluggerInterface $slugger) {
        $this->slugger = $slugger;
        $this->upload_path = $upload_path;
    }

    public function uploadFile($picture): string
    {
        $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$picture->guessExtension();

        // Move the file to the directory where brochures are stored
        try {
            $picture->move(
                $this->upload_path,
                $newFilename
            );
        } catch (FileException $e) {
            // TODO: gérer l'exception
            dump($e);die;
        }
        return $newFilename;
    }
}