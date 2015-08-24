<?php

namespace noFlash\SupercacheBundle\Listeners;


use noFlash\SupercacheBundle\Filesystem\ResponseHandler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Listens for Symfony HttpKernel events
 *
 * @see {http://symfony.com/doc/current/components/http_kernel/introduction.html}
 */
class KernelListener
{
    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @param ResponseHandler $responseHandler
     */
    public function __construct(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    /**
     * This method is executed on kernel.response event
     *
     * @param FilterResponseEvent $event
     *
     * @see {http://symfony.com/doc/current/components/http_kernel/introduction.html#the-kernel-response-event}
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $this->responseHandler->cacheResponse($event->getRequest(), $event->getResponse());
    }
}
