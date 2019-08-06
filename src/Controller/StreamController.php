<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class StreamController extends AbstractController
{
    /**
     * @var null|string
     */
    private $path = '';

    /**
     * @var string
     */
    private $dir = '';

    /**
     * @var resource
     */
    private $stream = '';

    /**
     * @var int
     */
    private $buffer = 25600;

    /**
     * @var int
     */
    private $start = -1;

    /**
     * @var int
     */
    private $end = -1;

    /**
     * @var int
     */
    private $size = 0;

    /**
     * VideoController constructor.
     */
    public function __construct()
    {
        $this->dir = '../video/';
    }

    /**
     * @param string $video
     *
     * @Route("/video/{video}", name="video", defaults={"video"=""})
     */
    public function videoAction($video)
    {
        $this->path = $this->dir . $video;
        $this->start();
    }

    /**
     * @Route("/dir", name="dir")
     */
    public function dirAction()
    {
        $files = [];
        foreach (glob($this->dir . "*") as $file) {
            $files[] = $file;
        };

        usort($files, function ($a, $b) {
            return $a < $b;
        });
        usort($files, create_function('$a,$b', 'return $a>$b;'));

        $listedFiles = [];

        foreach ($files as $file) {
            $listedFiles[] = [
                'date' => date('Y-m-d, H:i', filemtime($file)),
                'name' => str_replace($this->dir, '', $file)
            ];
        };

        return $this->render(
            'stream/index.html.twig', [
                'dir' => $this->dir,
                'files' => $listedFiles
            ]
        );
    }

    /**
     * Start streaming video content
     */
    public function start()
    {
        $this->open();
        $this->setHeader();
        $this->streamVideo();
        $this->end();
    }

    /**
     * @Route("/stop", name="stop")
     */
    public function stop()
    {
        if (!empty($this->stream)) fclose($this->stream);
        ob_get_clean();
        $response = new Response("clean");
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Open stream
     */
    private function open()
    {
        if (!($this->stream = fopen($this->path, 'rb'))) {
            die('Could not open stream for reading');
        }
    }

    /**
     * Set proper header to serve the video content
     */
    private function setHeader()
    {
        ob_get_clean();
        header("Content-Type: video/mp4");
        header("Cache-Control: max-age=2592000, public");
        header("Expires: " . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
        header("Last-Modified: " . gmdate('D, d M Y H:i:s', @filemtime($this->path)) . ' GMT');
        $this->start = 0;
        $this->size = filesize($this->path);
        $this->end = $this->size - 1;
        header("Accept-Ranges: 0-" . $this->end);
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_end = $this->end;
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            if ($range == '-') {
                $c_start = $this->size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
            }
            $c_end = ($c_end > $this->end) ? $this->end : $c_end;
            if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            $this->start = $c_start;
            $this->end = $c_end;
            $length = $this->end - $this->start + 1;
            fseek($this->stream, $this->start);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: " . $length);
            header("Content-Range: bytes $this->start-$this->end/" . $this->size);
        } else {
            header("Content-Length: " . $this->size);
        }
    }

    /**
     * close currently opened stream
     */
    private function end()
    {
        fclose($this->stream);
        exit;
    }

    /**
     * perform the streaming of calculated range
     */
    private function streamVideo()
    {
        $i = $this->start;
        set_time_limit(0);
        while (!feof($this->stream) && $i <= $this->end) {
            $bytesToRead = $this->buffer;
            if (($i + $bytesToRead) > $this->end) {
                $bytesToRead = $this->end - $i + 1;
            }
            $data = fread($this->stream, $bytesToRead);
            echo $data;
            flush();
            $i += $bytesToRead;
        }
    }
}
