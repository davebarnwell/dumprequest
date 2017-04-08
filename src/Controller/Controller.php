<?php

namespace davebarnwell\Controller;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Controller
{
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * render content based on the Accept content header e.g. html, json, xml
     *
     * @param Request  $request
     * @param Response $response
     * @param string   $template
     * @param array    $viewParams
     *
     * @return ResponseInterface
     */
    protected function renderView(
        Request $request,
        Response $response,
        string $template,
        array $viewParams
    ): ResponseInterface {

        $acceptContentTypes = $request->getHeader('Accept');
        $acceptContentType  = $acceptContentTypes[0] ?? 'text/html';

        // Render view based on 1st value in the HTTP_Accept header
        switch ($acceptContentType) {
            case 'application/json':
                return $this->renderJSON($response, $viewParams);
            case 'text/xml':
                return $this->renderXML($response, $viewParams);
            case 'text/html':
            default:
                return $this->container->get('view')->render($response, $template, $viewParams);
        }
    }

    /**
     * render JSON response
     *
     * @param Response $response
     * @param array    $viewParams
     *
     * @return ResponseInterface
     */

    private function renderJSON(Response $response, array $viewParams): ResponseInterface
    {
        return $response->withJson($viewParams);
    }

    /**
     * render XML response
     *
     * @param Response $response
     * @param array    $viewParams
     *
     * @return ResponseInterface
     */
    private function renderXML(Response $response, array $viewParams): ResponseInterface
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
        $this->arrayWalkToXml($viewParams, $xml);
        $response->getBody()->write($xml->asXML());
        return $response->withHeader('Content-Type: ', 'text/xml');
    }

    /**
     * Walk an associative array and create an XML representation
     *
     * @param array             $data
     * @param \SimpleXMLElement $xml_data
     */
    private function arrayWalkToXml(array $data, \SimpleXMLElement &$xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subNode = $xml_data->addChild($key);
                $this->arrayWalkToXml($value, $subNode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

}