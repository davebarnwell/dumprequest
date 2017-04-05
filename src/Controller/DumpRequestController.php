<?php

namespace davebarnwell\Controller;

class DumpRequestController extends Controller
{

    private $requestBody;

    public function execute(string $targetFile)
    {

        $data = $this->getHeadersAsString();

        $data .= $this->getMethodParamsAsString();

        $data .= $this->getUploadedFilesAsSummaryString();

        $data .= $this->getRequestBodyAsString();

        $this->saveToFile($targetFile, $data);

        echo("REQUEST RECEIVED" . PHP_EOL);
    }

    private function saveToFile(string $filename, string $data)
    {
        return file_put_contents(
            $filename,
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

    private function getUploadedFilesAsSummaryString()
    {
        $data = '';
        if ($_FILES) {
            $data .= PHP_EOL . PHP_EOL . 'Files Uploaded:' . PHP_EOL;
            foreach ($_FILES as $fileField => $meta) {
                $data .= 'File Field Name: ' . $fileField . PHP_EOL;
                foreach ($meta as $key => $value) {
                    $data .= "- $key: " . $value . PHP_EOL;
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

}