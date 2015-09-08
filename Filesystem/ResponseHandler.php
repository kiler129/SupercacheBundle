<?php

namespace noFlash\SupercacheBundle\Filesystem;

use noFlash\SupercacheBundle\Cache\CacheManager;
use noFlash\SupercacheBundle\Exceptions\SecurityViolationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResponseHandler
 * @package noFlash\SupercacheBundle\Filesystem
 */
class ResponseHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
        $this->container = $container;
        $this->addStatusHeader = (bool)$this->container->getParameter('supercache.cache_status_header');
    }

    /**
     * Tries to cache given response.
     *
     * @param Request $request
     * @param Response $response
     *
     * @return bool
     * @throws SecurityViolationException Tried to save cache entry wih unsafe path. Generally it should never occur
     *     unless invalid Request is passed.
     */
    public function cacheResponse(Request $request, Response $response)
    {
        $isCacheable = $this->isCacheable($request, $response);
        if ($isCacheable !== true) {
            if ($this->addStatusHeader) {
                $response->headers->set('X-Supercache',
                    'uncacheable,' . CacheManager::getUncachableReasonFromCode($isCacheable));
            }

            return false;
        }

        $status = $this->cacheManager->saveEntry($request->getPathInfo(), $response->getContent());

        if ($this->addStatusHeader) {
            $response->headers->set('X-Supercache', 'miss,' . (int)$status);
        }

        return (bool)$status;
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @return bool|int Will return integer code if response cannot be cached or true if it's cacheable
     */
    public function isCacheable(Request $request, Response $response)
    {
        if ($request->getMethod() !== 'GET') {
            return CacheManager::UNCACHEABLE_METHOD;
        }

        $queryString = $request->server->get('QUERY_STRING');
        if (!empty($queryString)) {
            return CacheManager::UNCACHEABLE_QUERY;
        }

        //Response::isCacheable() is unusable here due to expiry & code settings
        if (!$response->isSuccessful() || $response->isEmpty()) {
            return CacheManager::UNCACHEABLE_CODE;
        }

        if ($response->headers->hasCacheControlDirective('no-store')) {
            return CacheManager::UNCACHEABLE_NO_STORE_POLICY;
        }

        if ($response->headers->hasCacheControlDirective('private')) {
            return CacheManager::UNCACHEABLE_PRIVATE;
        }

        $environment = $this->container->getParameter('kernel.environment');
        if (($environment !== 'prod' && $environment !== 'dev') || !$this->container->getParameter('supercache.enable_' . $environment)) {
            return CacheManager::UNCACHEABLE_ENVIRONMENT;
        }

        return true;
    }
}
