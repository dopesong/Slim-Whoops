<?php

namespace Dopesong\Slim\Error;

use Whoops\Handler\HandlerInterface;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run as WhoopsRun;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

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
    const DEFAULT_STATUS_CODE = 500;

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
     * Transmit exception code as response status code or not
     *
     * @var bool
     */
    protected $transmitExceptionCode = false;

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
     * @param \Throwable              $throwable
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $throwable)
    {
        $contentType = $this->determineContentType($request);

        $this->pushHandlerByContentType($contentType);

        $output = null;

        $output = $this->whoops->handleException($throwable);

        $body = $response->getBody();
        $body->write($output);

        $statusCode = $this->transmitExceptionCode === true ? $throwable->getCode() : self::DEFAULT_STATUS_CODE;

        return $response->withStatus($statusCode)
            ->withHeader('Content-type', $contentType)
            ->withBody($body);
    }

    /**
     * @param $contentType
     */
    protected function pushHandlerByContentType($contentType)
    {
        $contentTypeBasedHandler = null;
        switch ($contentType) {
            case 'application/json':
                $contentTypeBasedHandler = new JsonResponseHandler();
                break;
            case 'text/xml':
            case 'application/xml':
                $contentTypeBasedHandler = new XmlResponseHandler();
                break;
            case 'text/html':
                $contentTypeBasedHandler = new PrettyPageHandler();
                break;
            default:
                return;
        }

        $this->prependHandler($contentTypeBasedHandler);
    }

    /**
     * @param Callable|HandlerInterface $handler
     */
    private function prependHandler($handler)
    {
        $existingHandlers = array_merge([$handler], $this->whoops->getHandlers());
        $this->whoops->clearHandlers();

        foreach ($existingHandlers as $existingHandler) {
            $this->whoops->pushHandler($existingHandler);
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

    /**
     * @param boolean $ption
     */
    protected function setTransmitExceptionCode($option)
    {
        $this->transmitExceptionCode = $option;
        return $this;
    }
}
