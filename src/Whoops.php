<?php

namespace Dopesong\Slim\Error;

use Whoops\Handler\HandlerInterface;
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
     * @var WhoopsRun
     */
    protected $whoops;

    /**
     * @var bool
     */
    protected $handlerPushed = false;

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
     */
    public function __construct()
    {
        $this->whoops = new WhoopsRun;
    }

    /**
     * @param Callable|HandlerInterface $handler
     *
     * @throws \InvalidArgumentException  If argument is not callable or instance of HandlerInterface
     */
    public function pushHandler($handler)
    {
        $this->whoops->pushHandler($handler);
        $this->handlerPushed = true;
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
        $contentType = $this->determineContentType($request);

        $this->pushHandlerByContentType($contentType);

        $output = null;

        $output = $this->whoops->handleException($exception);

        $body = $response->getBody();
        $body->write($output);

        return $response->withStatus(500)
            ->withHeader('Content-type', $contentType)
            ->withBody($body);
    }

    /**
     * @param $contentType
     */
    protected function pushHandlerByContentType($contentType)
    {
        switch ($contentType) {
            case 'application/json':
                $this->whoops->pushHandler(new JsonResponseHandler());
                break;
            case 'text/xml':
            case 'application/xml':
                $this->whoops->pushHandler(new XmlResponseHandler());
                break;
            case 'text/html':
                $this->whoops->pushHandler(new PrettyPageHandler());
                break;
        }
    }

    /**
     * Determine which content type we know about is wanted using Accept header
     *
     * @param ServerRequestInterface $request
     *
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
