<?php

namespace Dopesong\Slim\Error;

use Whoops\Handler\XmlResponseHandler;
use Whoops\Run as WhoopsRun;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Exception;

/**
 * Whoops Error Handler
 *
 * This class is a Slim Framework Error Handler
 * built on top of the Whoops Error Handler.
 * Whoops is a PHP component created by Filipe Dobreira
 * and now maintained by Denis Sokolov
 *
 * @package Slim\ErrorHandlers
 */
class Whoops
{
    /**
     * @var
     */
    protected $displayErrorDetails;

    /**
     * Known handled content types
     *
     * @var array
     */
    protected $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
    ];

    /**
     * Constructor
     *
     * @param boolean $displayErrorDetails Set to true to display full details
     */
    public function __construct($displayErrorDetails = false)
    {
        $this->displayErrorDetails = (bool)$displayErrorDetails;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param Exception              $exception
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, Exception $exception)
    {
        $whoops = new WhoopsRun;
        $contentType = $this->determineContentType($request);

        switch ($contentType) {
            case 'application/json':
                $whoops->pushHandler(new JsonResponseHandler());
                break;
            case 'text/xml':
            case 'application/xml':
                $whoops->pushHandler(new XmlResponseHandler());
                break;
            case 'text/html':
                $whoops->pushHandler(new PrettyPageHandler());
                break;
        }

        $output = null;

        if ($this->displayErrorDetails) {
            $output = $whoops->handleException($exception);
        }

        $body = $response->getBody();
        $body->write($output);

        return $response->withStatus(500)
            ->withHeader('Content-type', $contentType)
            ->withBody($body);
    }

    /**
     * Determine which content type we know about is wanted using Accept header
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    private function determineContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);
        if (count($selectedContentTypes)) {
            return reset($selectedContentTypes);
        }
        return 'text/html';
    }
}
