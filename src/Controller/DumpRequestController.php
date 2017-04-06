<?php

namespace davebarnwell\Controller;

class DumpRequestController extends Controller
{

    const FILE_EXTENSION = '.txt';
    const UPLOAD_EXTENSION = '.data';
    const SECONDS_IN_DAY = 86400;

    private $requestBody;

    public function execute(string $targetFile, float $daysToKeep = 7)
    {

        $data = $this->getHeadersAsString();

        $data .= $this->getMethodParamsAsString();

        $data .= $this->getUploadedFilesAsSummaryString($targetFile);

        $data .= $this->getRequestBodyAsString();

        $this->saveToFile($targetFile, $data);

        echo("REQUEST RECEIVED" . PHP_EOL);
        $this->removeFilesOlderThanXseconds(dirname($targetFile), self::SECONDS_IN_DAY * $daysToKeep);
    }

    private function saveToFile(string $filename, string $data)
    {
        return file_put_contents(
            $filename . self::FILE_EXTENSION,
            $data
        );
    }

    public function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isGetRequest()
    {
        return $this->getRequestMethod() == 'GET';
    }

    public function isPostRequest()
    {
        return $this->getRequestMethod() == 'POST';
    }

    public function getRequestBody()
    {
        if (!$this->requestBody) {
            $this->requestBody = file_get_contents('php://input');
        }
        return $this->requestBody;
    }

    public function parseRequestBody()
    {
        parse_str(file_get_contents("php://input"), $vars);
        return $vars;
    }

    private function getMethodParamsAsString()
    {
        $data = '';
        if (!$this->isGetRequest()) {
            $vars = $this->isPostRequest() ? $_POST : $this->parseRequestBody();
            $data .= PHP_EOL . PHP_EOL . $this->getRequestMethod() . ' vars:' . PHP_EOL;
            foreach ($vars as $key => $value) {
                if (is_array($value)) {
                    $value = '["' . implode('","', $value) . '"]';
                }
                $data .= "$key: $value" . PHP_EOL;
            }
        }
        return $data;
    }

    private function getUploadedFilesAsSummaryString(string $filename)
    {
        $data = '';
        if ($_FILES) {
            $data .= PHP_EOL . PHP_EOL . 'Files Uploaded:' . PHP_EOL;
            foreach ($_FILES as $fileField => $meta) {
                $data .= 'File Field Name: ' . $fileField . PHP_EOL;
                foreach ($meta as $key => $value) {
                    $data .= "- $key: " . $value . PHP_EOL;
                }
                if (is_uploaded_file($_FILES[$fileField]['tmp_name'])) {
                    $sanitisedFilename = preg_replace('/[^-_0-9a-zA-Z]+/', '', $fileField);
                    $uploadFilename = $filename . '-' . $sanitisedFilename . self::UPLOAD_EXTENSION;
                    if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $uploadFilename)) {
                        $data .= "- saved to: " . basename($uploadFilename) . PHP_EOL;
                    }
                }
            }
        }
        return $data;
    }

    private function getHeadersAsString(): string
    {
        $data = '';
        $data .= sprintf(
            "%s %s %s\n\nHTTP headers:\n",
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $_SERVER['SERVER_PROTOCOL']
        );

        foreach ($this->getHeaderList() as $name => $value) {
            $data .= $name . ': ' . $value . PHP_EOL;
        }
        return $data;
    }

    private function getRequestBodyAsString()
    {
        $data = PHP_EOL . "Request body:" . PHP_EOL;
        $data .= $this->getRequestBody() . PHP_EOL;
        return $data;
    }

    private function getHeaderList()
    {
        $headerList = [];
        foreach ($_SERVER as $name => $value) {
            if (preg_match('/^HTTP_/', $name)) {
                // convert HTTP_HEADER_NAME to Header-Name
                $name = strtr(substr($name, 5), '_', ' ');
                $name = ucwords(strtolower($name));
                $name = strtr($name, ' ', '-');
                // add to list
                $headerList[$name] = $value;
            }
        }
        return $headerList;
    }

    /**
     * @param string $dir
     * @param int $seconds default 7 days in seconds
     */
    private function removeFilesOlderThanXseconds(string $dir, $seconds = 604800)
    {
        $files = glob($dir . "/*");
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= $seconds) { // 2 days
                    unlink($file);
                }
            }
        }
    }
}