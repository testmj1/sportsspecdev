<div id="mobile_sidebar_top">
    <div id="stiky_sidebar_block"></div>
<?php if ( is_active_sidebar( 'mobile_top_sidebar' ) ) : ?>
    <div id="primary_sidebar_inner">
        <?php dynamic_sidebar( 'mobile_top_sidebar' ); ?>
    </div>
<?php endif; ?>
</div>