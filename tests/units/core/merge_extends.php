<?php
return array(
    'header' => array(
        'menu' => array(
            'visible' => true,
            'render' => function( $app, $data ) {
                echo '<div class="nav-collapse"><ul class="nav">';
                foreach( $data as $item ) {
                    echo '<li>';
                    echo '<a href="#">';
                    echo $item['title'];
                    echo '</a></li>';
                }
                echo '</ul></div>';
            },
            'data' => array(
                array(
                    'route' => array( 'index' ), 
                    'title' => 'Home'
                )
            )
        )
    ),
    'footer' => array(
        'copyright' => array(
            'visible' => true,
            'render' => function( $app, $data ) {
                echo 
                    '<p>&copy; ' . $data['company'] 
                    . ' ' . $data['date'] . '</p>'
                ;
            },
            'data' => array(
                'company' => 'Your Company',
                'date' => date('Y')
            )
        )
    )
);