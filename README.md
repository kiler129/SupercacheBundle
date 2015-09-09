# Supercache Bundle
Static pages caching for Symfony Framework.

## What is Supercache?
Some of you may be annoyed looking at simple pages loading times - exactly like me. There's a lot solution for that problem - this is extreme one.
If you ever used Wordpress you probably seen `WP Super Cache` plugin. This bundle is build around similar principles (but it is not affiliated in any way) as that plugin.  

Basically SupercacheBundle caches whole response to static files and serve them at no cost. It's very useful for pages where there're a lot of static routes visited often.

## Installation
Before you start using this bundle you should carefully read [HTTP Cache chapter of Symfony Cookbook](http://symfony.com/doc/current/book/http_cache.html) - it provides a bunch of excellent techniques and tips about caching.

Bundle can be installed like any other Symfony bundle.
  1. Open a command console, enter your project directory and execute following command:  
  `composer require noflash/supercache-bundle`
  2. Enable the bundle by adding following line to `app/AppKernel.php` file:
  ```php
  <?php
  // app/AppKernel.php
  
  // ...
  class AppKernel extends Kernel
  {
      public function registerBundles()
      {
          $bundles = array(
              // ...
  
              new noFlash\SupercacheBundle\SupercacheBundle(),
          );
  
          // ...
      }
  
      // ...
  }
  ```
  3. ~~Execute following command and follow onscreen instructions `app/console supercache:install`~~
  Currently manual installation is required. You should create `webcache` directory in root folder and than perform two steps described in **Troubleshooting** section below.

## Configuration
TBD
Default configuration:
```yaml
supercache:

    # Enable/disable cache while running prod environment
    enable_prod:          true

    # Enable/disable cache while running dev environment
    enable_dev:           false

    # Cache directory, must be http-accessible (so it cannot be located under app/)
    cache_dir:            '%kernel.root_dir%/../webcache'

    # Enable/disable adding X-Supercache header
    cache_status_header:  true

```

## Troubleshooting
### Installation command failed: *Failed to create cache directory*
Looks like specified cache directory cannot be created. It can be permission related problem. You can try creating it yourself. To do that simply create desired cache directory (by default it's set to `./webcache/`). Next put following content in `.htaccess` inside cache directory:
```apacheconf
RemoveHandler .php
RemoveType .php
Options -ExecCGI

<IfModule mod_php5.c>
    php_flag engine off
</IfModule>

<IfModule mod_headers.c>
    Header set Cache-Control 'max-age=3600, must-revalidate'
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/html A3600
</IfModule>

<IfModule mod_mime.c>
    AddType application/javascript .js
    AddType text/html .html
    AddType application/octet-stream .bin
</IfModule>
```

### Installation command failed: *Failed to modify .htaccess (...)*
If you (or some other bundle) modified `web/.htaccess` file, installer may be have trouble automatically applying required changes. You can add following lines manually - they should be placed just below `RewriteRule ^app\.php(/(.*)|$) %{ENV:BASE}/$2 [R=301,L]` (or similar):
```apacheconf
### >>>SUPERCACHE BUNDLE 
RewriteCond %{REQUEST_METHOD} !^(GET|HEAD) [OR]
RewriteCond %{QUERY_STRING} !^$
RewriteRule . - [S=3]

RewriteCond %{DOCUMENT_ROOT}/../webcache/$1/index.html -f
RewriteRule ^(.*) %{DOCUMENT_ROOT}/../webcache/$1/index.html [L]

RewriteCond %{DOCUMENT_ROOT}/../webcache/$1/index.js -f
RewriteRule ^(.*) %{DOCUMENT_ROOT}/../webcache/$1/index.js [L]

RewriteCond %{DOCUMENT_ROOT}/../webcache/$1/index.bin -f
RewriteRule ^(.*) %{DOCUMENT_ROOT}/../webcache/$1/index.bin [L]
### <<<SUPERCACHE BUNDLE
```
Please note cache path need to be adjusted. Path is relative to `web/` directory.

## FAQ
#### Do all responses gets cached?
No, request & response must meet some criteria to be cached:
  * `GET` request method
  * No query string
  * Response code between 200 and 300 (excluding 204)
  * No `private` or `no-store` cache control directives   

#### Are there any limitations?
Yes, there are few:
  * `.htaccess` support must be turned on (see issue #5 for details)
  * It's impossible to serve different content on `/sandbox` and `/sandbox/`
  * You cannot have routes `..` and `.` (they are illegal in HTTP RFC anyway)
  * There are no automatic check for authentication token (it's your responsibility to set `private` cache policy if you're presenting user-specific information)
  * Due to performance reasons files are served from cache with one of the following MIME-Types: text/html, application/javascript or application/octet-stream. See issue #2 for details.
