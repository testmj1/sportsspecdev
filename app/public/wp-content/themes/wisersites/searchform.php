<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Searchform
 *
 *
 * @file           searchform.php
 * @package        Wiser Themes
 * @author
 * @copyright      2015 WiserSites
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/wiserthemes/searchform.php
 * @since          available since Release 1.0
 */
?>
<form action="/" method="get" class="search_form">
	<input type="text" name="s" id="search" value="<?php the_search_query(); ?>" placeholder="<?php _e('Search', 'wiserthemes'); ?>" />
	<input type="submit" alt="<?php _e('Search', 'wiserthemes'); ?>" value="<?php _e('Search', 'wiserthemes'); ?>" class="search_submit_btn" />
</form>
