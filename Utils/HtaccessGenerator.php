<?php

namespace noFlash\SupercacheBundle\Utils;


use noFlash\SupercacheBundle\Filesystem\Finder;

/**
 * Generates .htaccess files used by bundle
 */
class HtaccessGenerator
{
    const HEADER = "### >>>SUPERCACHE BUNDLE\n";
    const FOOTER = "### <<<SUPERCACHE BUNDLE\n";
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @param Finder $finder
     */
    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Generates entries to place inside cache directory
     *
     * @return string
     */
    public function getCacheDirectoryCode()
    {
        //@formatter:off
        return static::HEADER .
               "RemoveHandler .php\n" .
               "RemoveType .php\n" .
               "Options -ExecCGI\n" .
               "\n" .
               "<IfModule mod_php5.c>\n" .
               "    php_flag engine off\n" .
               "</IfModule>\n" .
               "\n" .
               "<IfModule mod_headers.c>\n" .
               "    Header set Cache-Control 'max-age=3, must-revalidate'\n" .
               "</IfModule>\n" .
               "\n" .
               "<IfModule mod_expires.c>\n" .
               "    ExpiresActive On\n" .
               "    ExpiresByType text/html A3\n" .
               "</IfModule>\n" .
               static::FOOTER;
        //@formatter:on
    }

    /**
     * Generates entries to place inside web/.htaccess file.
     * Entries should be placed just after following rule: RewriteRule ^app\.php(/(.*)|$) %{ENV:BASE}/$2 [R=301,L]
     *
     * @return string
     */
    public function getWebCode()
    {
        $path = rtrim($this->finder->getRealCacheDir(), '/\\');
        if (DIRECTORY_SEPARATOR !== '/') {
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        }

        //@formatter:off
        return static::HEADER .
               "RewriteCond %{REQUEST_METHOD} ^(GET|HEAD)\n" .
               " RewriteCond %{QUERY_STRING} ^$\n" .
               //" RewriteCond %{DOCUMENT_ROOT}/../webcache/$1/index.html -f \n".
               //"RewriteRule ^(.*) ../webcache/$1/index.html [L]\n".
               " RewriteCond $path/$1/index.html -f \n" .
               "RewriteRule ^(.*) $path/$1/index.html [L]\n" .
               static::FOOTER;
        //@formatter:on
    }
}
