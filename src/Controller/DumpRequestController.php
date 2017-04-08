<?php

namespace davebarnwell\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class DumpRequestController extends Controller
{

    const FILE_EXTENSION   = '.txt';
    const UPLOAD_EXTENSION = '.data';
    const SECONDS_IN_DAY   = 86400;

    /**
     * Entry point to dump a HTTP request to files
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return ResponseInterface
     */
    public function execute(Request $request, Response $response, array $args): ResponseInterface
    {

        $time    = time();
        $dateStr = date('Y-m-d-H-i-s', $time);
        $uuid    = uniqid();

        $targetFile = $this->container->get('settings_yml')->getDirectorySetting('storeRequests') . '/' . $dateStr . '-request-' . $uuid;

        $data = 'Received at: ' . date('r', $time) . PHP_EOL;

        $data .= $this->getHeadersAsString($request);

        $data .= $this->getMethodParamsAsString($request);

        $data .= $this->getUploadedFilesAsSummaryString($request, $targetFile);

        $data .= $this->getRequestBodyAsString($request);

        $this->saveStringToFile($targetFile, $data);

        $this->removeFilesOlderThanXseconds(dirname($targetFile),
            self::SECONDS_IN_DAY * $this->container->get('settings_yml')->getSetting('deleteOlderThanDays'));


        $viewParams = [
            'status' => "OK"
        ];

        return $this->renderView($request, $response, 'index.twig', $viewParams);
    }

    /**
     * Save a string to a file
     *
     * @param string $filename
     * @param string $data
     *
     * @return bool|int
     */
    private function saveStringToFile(string $filename, string $data)
    {
        return file_put_contents(
            $filename . self::FILE_EXTENSION,
            $data
        );
    }

    /**
     * get dump of method params as a string
     *
     * @param Request $request
     *
     * @return string
     */
    private function getMethodParamsAsString(Request $request)
    {
        $data = '';
        $vars = $request->getParams();
        $data .= PHP_EOL . PHP_EOL . $request->getMethod() . ' vars:' . PHP_EOL;
        foreach ($vars as $key => $value) {
            if (is_array($value)) {
                $value = '["' . implode('","', $value) . '"]';
            }
            $data .= "$key: $value" . PHP_EOL;
        }
        return $data;
    }

    /**
     * Get a summary of uploaded files and also save them off to disk cache
     *
     * @param Request $request
     * @param string  $filename
     *
     * @return string
     */
    private function getUploadedFilesAsSummaryString(Request $request, string $filename)
    {
        $data = '';
        /**
         * @var UploadedFileInterface[] $files
         */
        $files = $request->getUploadedFiles();
        if ($files) {
            $data .= PHP_EOL . PHP_EOL . 'Files Uploaded:' . PHP_EOL;
            foreach ($files as $fileMeta) {
                $data .= 'Filename: ' . $fileMeta->getClientFilename() . PHP_EOL;
                $data .= "- MediaType: " . $fileMeta->getClientMediaType() . PHP_EOL;
                $data .= "- Size: " . $fileMeta->getSize() . PHP_EOL;
                if ($fileMeta->getError() === UPLOAD_ERR_OK) {
                    $sanitisedFilename = preg_replace('/[^-_0-9a-zA-Z\.]+/', '', $fileMeta->getClientFilename());
                    $uploadFilename    = $filename . '-' . $sanitisedFilename . self::UPLOAD_EXTENSION;
                    $fileMeta->moveTo($uploadFilename);
                    $data .= "- saved to: " . basename($uploadFilename) . PHP_EOL;
                } else {
                    $data .= "- Upload Error: " . $fileMeta->getError() . PHP_EOL;
                }
            }
        }
        return $data;
    }

    /**
     * Capture headers to string
     *
     * @param Request $request
     *
     * @return string
     */
    private function getHeadersAsString(Request $request): string
    {
        $data    = '';
        $data    .= sprintf(
            "%s %s %s" . PHP_EOL . PHP_EOL . "HTTP headers:" . PHP_EOL,
            $request->getMethod(),
            $request->getUri(),
            $request->getProtocolVersion()
        );
        $headers = $request->getHeaders();
        foreach ($headers as $name => $value) {
            $data .= $name . ': ' . implode(', ', $value) . PHP_EOL;
        }
        return $data;
    }

    /**
     * Capture request body to a string
     *
     * @param Request $request
     *
     * @return string
     */
    private function getRequestBodyAsString(Request $request)
    {
        $data = PHP_EOL . "Request body:" . PHP_EOL;
        $data .= $request->getBody() . PHP_EOL;
        return $data;
    }


    /**
     * clean up old cache files so the disk doesnt fill up
     *
     * @param string $dir
     * @param int    $seconds default 7 days in seconds
     */
    private function removeFilesOlderThanXseconds(string $dir, $seconds = 604800)
    {
        $files = glob($dir . "/*");
        $now   = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= $seconds) { // 2 days
                    unlink($file);
                }
            }
        }
    }
}