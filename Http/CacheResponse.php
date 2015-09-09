<?php

namespace noFlash\SupercacheBundle\Http;


use noFlash\SupercacheBundle\Cache\CacheElement;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class CacheResponse
 */
class CacheResponse extends SymfonyResponse
{
    /**
     * Creates new CacheResponse object from given CacheElement.
     *
     * @param CacheElement $element
     * @param null $status Value for X-Supercache header. By default null (no header will be added).
     *
     * @return static
     */
    public static function createFromElement(CacheElement $element, $status = null)
    {
        $mime = self::getMimeByType($element->getType());
        $headers = array('Content-Type' => $mime);

        if ($status !== null) {
            $headers['X-Supercache'] = $status;
        }

        return new static($element->getContent(), static::HTTP_OK, $headers);
    }

    /**
     * @deprecated Will be moved to CacheType
     */
    private static function getMimeByType($type)
    {
        switch ($type) {
            case CacheElement::TYPE_HTML:
                return 'text/html';

            case CacheElement::TYPE_JAVASCRIPT:
                return 'application/javascript';

            case CacheElement::TYPE_BINARY:
            default:
                return 'application/octet-stream';
        }
    }
}
