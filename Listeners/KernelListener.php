<?php

namespace noFlash\SupercacheBundle\Listeners;


use noFlash\SupercacheBundle\Cache\RequestHandler;
use noFlash\SupercacheBundle\Cache\ResponseHandler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Listens for Symfony HttpKernel events
 *
 * @see {http://symfony.com/doc/current/components/http_kernel/introduction.html}
 */
class KernelListener
{
    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @param RequestHandler $requestHandler
     * @param ResponseHandler $responseHandler
     */
    public function __construct(RequestHandler $requestHandler, ResponseHandler $responseHandler)
    {
        $this->requestHandler = $requestHandler;
        $this->responseHandler = $responseHandler;
    }

    /**
     * This method is executed on kernel.request event
     *
     * @param GetResponseEvent $event
     *
     * @see {http://symfony.com/doc/current/components/http_kernel/introduction.html#the-kernel-request-event}
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $cacheResponse = $this->requestHandler->retrieveCachedResponse($request);
        if ($cacheResponse !== null) {
            $request->attributes->set('response_source', 'cache');
            $event->setResponse($cacheResponse);
        }
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
        $request = $event->getRequest();
        if ($request->attributes->get('response_source') === 'cache') { //Prevents re-caching response from cache
            $event->stopPropagation();

            return;
        }

        if (!$event->isMasterRequest()) {
            return; //Caching should only occur on master requests, see https://github.com/kiler129/SupercacheBundle/issues/10
        }

        $this->responseHandler->cacheResponse($event->getRequest(), $event->getResponse());
    }
}
