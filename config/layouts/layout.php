<?php

return array(
    'top' => array(
        'default' => array(
            'visible' => true,
            'render' => function( $app, $data ) { ?>                
    <h1>Hello, world!</h1>
    <p>
        To customize this message, edit your configuration :
        config/layouts/layout.phtml
    </p>
    <p><a class="btn btn-primary btn-large">Learn more &raquo;</a></p>                    
                <?php
            }
        )
    ),
    'content' => array(
        'left' => array(
            'visible' => true,
            'render' => function( $app, $data ) { ?>
<div class="span4">
    <h2><?php echo $data['title']; ?></h2>
    <?php echo $data['body']; ?>
    <p>
        <a class="btn" href="<?php echo $data['link']['href']; ?>">
            <?php echo $data['link']['title']; ?>
        </a>
    </p>
</div>
                <?php 
            }, 
            'data' => array(
                'title' => 'Heading'
                ,'body' => '<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>'
                ,'link' => array(
                    'href' => '#',
                    'title' => 'View details &raquo;'
                )
            )
        ),
        'center' => array(
            'visible' => true,
            'render' => function( $app, $data ) { ?>
<div class="span4">
    <h2><?php echo $data['title']; ?></h2>
    <?php echo $data['body']; ?>
    <p>
        <a class="btn" href="<?php echo $data['link']['href']; ?>">
            <?php echo $data['link']['title']; ?>
        </a>
    </p>
</div>
                <?php 
            }, 
            'data' => array(
                'title' => 'Heading'
                ,'body' => '<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>'
                ,'link' => array(
                    'href' => '#',
                    'title' => 'View details &raquo;'
                )
            )
        ),
        'right' => array(
            'visible' => true,
            'render' => function( $app, $data ) { ?>
<div class="span4">
    <h2><?php echo $data['title']; ?></h2>
    <?php echo $data['body']; ?>
    <p>
        <a class="btn" href="<?php echo $data['link']['href']; ?>">
            <?php echo $data['link']['title']; ?>
        </a>
    </p>
</div>
                <?php 
            }, 
            'data' => array(
                'title' => 'Heading'
                ,'body' => '<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>'
                ,'link' => array(
                    'href' => '#',
                    'title' => 'View details &raquo;'
                )
            )
        )                    
    )  
);