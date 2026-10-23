<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class StreamController extends AbstractController
{
    private readonly string $dir;

    public function __construct()
    {
        $this->dir = dirname(__DIR__, 2) . '/video';
    }

    #[Route('/video/{video}', name: 'video', defaults: ['video' => ''])]
    public function videoAction(string $video): BinaryFileResponse
    {
        $baseDir = realpath($this->dir);

        if ($baseDir === false || $video === '') {
            throw new NotFoundHttpException('Video not found.');
        }

        $path = realpath($baseDir . DIRECTORY_SEPARATOR . $video);

        if (
            $path === false
            || !str_starts_with($path, $baseDir . DIRECTORY_SEPARATOR)
            || !is_file($path)
            || !is_readable($path)
        ) {
            throw new NotFoundHttpException('Video not found.');
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'video/mp4');
        $response->setContentDisposition('inline');

        return $response;
    }

    #[Route('/dir', name: 'dir')]
    public function dirAction(): Response
    {
        $files = glob($this->dir . DIRECTORY_SEPARATOR . '*') ?: [];

        $files = array_filter($files, 'is_file');

        usort(
            $files,
            static fn (string $a, string $b): int => strcmp($b, $a)
        );

        $listedFiles = [];

        foreach ($files as $file) {
            $listedFiles[] = [
                'date' => date('Y-m-d, H:i', filemtime($file)),
                'name' => basename($file),
            ];
        }

        return $this->render('stream/index.html.twig', [
            'dir' => $this->dir,
            'files' => $listedFiles,
        ]);
    }

    #[Route('/stop', name: 'stop')]
    public function stop(): Response
    {
        return new Response('clean');
    }
}
