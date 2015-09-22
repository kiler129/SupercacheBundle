<?php

namespace noFlash\SupercacheBundle\Cache;

use noFlash\SupercacheBundle\Exceptions\SecurityViolationException;
use noFlash\SupercacheBundle\Http\CacheResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RequestHandler
 */
class RequestHandler
{
    /**
     * @var bool
     */
    private $addStatusHeader;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @param CacheManager $cacheManager
     * @param ContainerInterface $container
     */
    public function __construct(CacheManager $cacheManager, ContainerInterface $container)
    {
        $this->cacheManager = $cacheManager;
        $this->addStatusHeader = (bool)$container->getParameter('supercache.cache_status_header');
    }

    /**
     * Tries to obtain cached response.
     *
     * @param Request $request
     *
     * @return Response|null Will return cached content as response or null if there's nothing to return.
     * @throws SecurityViolationException Tried to obtain cache entry using unsafe path. Generally it should never occur
     *     unless invalid Request is passed.
     */
    public function retrieveCachedResponse(Request $request)
    {
        if ($this->isCacheable($request) !== true) {
            return null;
        }

        $cacheElement = $this->cacheManager->getElement($request->getPathInfo());
        if ($cacheElement === null) {
            return null;
        }

        return CacheResponse::createFromElement($cacheElement, (($this->addStatusHeader) ? 'HIT,PHP' : null));
    }

    /**
     * @param Request $request
     *
     * @return bool|int Will return integer code if response cannot be cached or true if it's cacheable
     */
    public function isCacheable(Request $request)
    {
        if ($request->getMethod() !== 'GET') {
            return CacheManager::UNCACHEABLE_METHOD;
        }

        $queryString = $request->server->get('QUERY_STRING');
        if (!empty($queryString)) {
            return CacheManager::UNCACHEABLE_QUERY;
        }

        return true;
    }
}
