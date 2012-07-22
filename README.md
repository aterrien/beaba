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