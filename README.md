# beaba [![Build Status](https://secure.travis-ci.org/ichiriac/beaba.png?branch=master)](http://travis-ci.org/ichiriac/beaba)


BEABA is a lightweight MVC framework written in PHP 5.3 :

- core is less than 1K LoC *nb1*
- really K.I.S.S oriented
- S.O.L.I.D respectfull  
- 100% extensible & configurable
- light cook-book documentation

*nb1 : 890 lines of code from building script (removes comments + format brackets to egyptian style) - exclude configuration scripts* 


## Example

```php
<?php
// This example is just for fun, but you have real controllers classes
require_once '../../beaba/framework/bootstrap.php'; 
$app = new beaba\core\WebApp(array(
    'routes' => array(
        // start routes injections
        'index' => array(
            'callback' => function( $app, $args ) {
                $app->getView()
                    ->setTemplate('empty')
                    ->push(
                        'content',
                        function( $app, $data ) {
                            echo '<h1>Hello world</h1>';
                        }
                    )
                ;
            }
        )
        // end of routes injection
    )
));
$app->dispatch();
```

## Install

1. Install with composer the package : beaba/default :

Make the path :
    `$ mkdir -p /usr/local/beaba/public/www/ `

Go the working dir :
    `$ cd /usr/local/beaba/public/www/ `

Create the composer.json file :
```json
{
    "require": {
        "beaba/default":"dev-master"
    },
    "minimum-stability": "dev"
}
```

    `$ composer.phar install `

2. Create an apache vhost :
```xml
<VirtualHost *:80>
    ServerAdmin dev.beaba@localhost.dev
    ServerName beaba.localhost.dev
    Alias /core/ /usr/local/beaba/framework/
    Alias /apps/ /usr/local/beaba/applications/
    DocumentRoot /usr/local/beaba/public/www/
</VirtualHost>
<Directory /usr/local/beaba/public/www/>
    AllowOverride all
    Order Deny,Allow
    Allow from all  
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?p=$1& [QSA,L]
</Directory>
<Directory /usr/local/beaba/framework/assets/>
    Order allow,deny
    Deny from all
    <FilesMatch "\.(gif|jpe?g|png|css|js|svg|ttf)$">
        AllowOverride all
        Order Deny,Allow
        Allow from all  
    </FilesMatch>
</Directory>
<Directory /usr/local/beaba/applications/>
    Order allow,deny
    Deny from all
    <FilesMatch "\.(gif|jpe?g|png|css|js|svg|ttf)$">
        AllowOverride all
        Order Deny,Allow
        Allow from all
    </FilesMatch>
</Directory>
```

3. Add to the domain to /etc/hosts

    `$ echo "127.0.0.1 beaba.localhost.dev" >> /etc/hosts`

## Documentation & Cook-Book

It's in progress ...

# MIT License

Copyright (C) <2012> <PHP Hacks Team : http://coderwall.com/team/php-hacks>

Permission is hereby granted, free of charge, to any person obtaining a copy of 
this software and associated documentation files (the "Software"), to deal in 
the Software without restriction, including without limitation the rights to 
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 the Software, and to permit persons to whom the Software is furnished to do so, 
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS 
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER 
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.