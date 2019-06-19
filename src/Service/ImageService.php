<?php

namespace App\Service;

use App\Traits\LoggerTrait;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageService
{
    use LoggerTrait;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(KernelInterface $appKernel)
    {
        $this->projectDir = $appKernel->getProjectDir();
    }

    public function saveShowImage($imageUrl, $imageMediumUrl, $showID): void
    {
        $dir =$this->projectDir . '/public/img/shows/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        if (!is_dir($dir . 'medium/')) {
            mkdir($dir . 'medium/');
        }

        try {
            $newFilename = $dir . $showID . "." . pathinfo($imageUrl, PATHINFO_EXTENSION);
            copy($imageUrl, $newFilename);
        } catch (Exception $e) {
            $this->error($e->getMessage(), $e->getTrace());
        }
        try {
            $newFilename = $dir . '/medium/' . $showID . "." . pathinfo($imageMediumUrl, PATHINFO_EXTENSION);
            copy($imageMediumUrl, $newFilename);
        } catch (Exception $e) {
            $this->error($e->getMessage(), $e->getTrace());
        }
    }
}
