beaba
=====

BEABA is a lightweight MVC framework written in PHP :

- core is less than 1K LoC
- really K.I.S.S oriented
- S.O.L.I.D respectfull  
- 100% extensible & configurable
- light cook-book documentation

Example
-------

    <?php
    require_once '../bootstrap.php'; 
    $app = new beaba\core\Application(array(
        'index' => array(
            'callback' => function( $app, $args ) {
                $app->getView()
                    ->setLayout('empty.phtml')
                    ->push(
                        'content',
                        function( $app, $data ) {
                            echo 'Hello world';
                        }
                    )
                ;    
            }
        )
    ));
    $app->dispatch(
    	$_SERVER['REQUEST_URI'],
    	$_REQUEST
    );    
    ?>
    
Install
-------

1. Download this this project

2. Create an apache vhost :
<pre><code>    &lt;VirtualHost *:80&gt;
        ServerAdmin dev.beaba@localhost.dev
        ServerName beaba.localhost.dev
        DocumentRoot /var/www/beaba/public/
    &lt;/VirtualHost&gt;
    &lt;Directory /var/www/beaba/public/&gt;
        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php?p=$1& [QSA,L]         
    &lt;/Directory&gt;
</code></pre>
3. Add to the domain to /etc/hosts

    <code>$ echo "127.0.0.1 beaba.localhost.dev" &gt;&gt; /etc/hosts</code>

MIT License
===========

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