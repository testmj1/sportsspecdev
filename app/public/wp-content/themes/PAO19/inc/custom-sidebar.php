<?php
//$a=wp_get_sidebars_widgets();
//echo '<pre>';
//print_r($a);
//echo '</pre>';
            if(isset($wiser_option['sidebar_logo'])){
            $sidebar_logo = $wiser_option['sidebar_logo'];
            }
            if(isset($wiser_option['sidebar_logo_link'])){
            $sidebar_logo = $wiser_option['sidebar_logo_link'];
            }
            if(!empty($sidebar_logo) && !empty($sidebar_logo_link)){
            ?>
            <div id="sidebar_logo">
                <a target="_blank" href="<?php echo $sidebar_logo_link;?>">
                <img src="<?php echo $sidebar_logo;?>">
                </a>
            </div>
            <?php
            }
            ?>
<div id="primary_sidebar">
    <div id="stiky_sidebar_block"></div>
<?php if ( is_active_sidebar( 'sidebar-primary' ) ) : ?>
    <div id="primary_sidebar_inner">
        <?php dynamic_sidebar( 'sidebar-primary' ); ?>
    </div>
<?php endif; ?>
</div>

<div id="a_sticky_sidebar_parent">
    <div id="a_sticky_sidebar">
        <?php if ( is_active_sidebar( 'sidebar-sticky' ) ) : ?>
        <div id="sticky_sidebar_inner">
            <?php dynamic_sidebar( 'sidebar-sticky' ); ?>
        </div>
    <?php endif; ?>
    </div>
</div>