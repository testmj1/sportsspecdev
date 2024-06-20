<?php

global $wiser_option;

//add_shortcode( 'wiser_breadcrumb', 'wiser_breadcrumb' );
/*
 * Social Icons
 */
function wiser_social_links( ) {
	global $theme_url, $wiser_option;
	$return = '<ul class="social_ul clearfix">';

	if( isset( $wiser_option['social'] ) && count( $wiser_option['social'] ) )
	foreach( $wiser_option['social'] as $social => $social_link ){
		if( trim( $social_link ) == '') continue;

		if( $wiser_option['social_icons'] && $wiser_option['social_icons'][$social] != '' ){
			$icon_url = $wiser_option['social_icons'][$social];
		} else {
			$icon_url = $theme_url.'images/socials/'.$social.'.png';
		}

		$return.= ' <li> <a href="'.$social_link.'" title="" target="_blank"> ';
		$return.= '<img src="'.$icon_url.'" alt="">';
		$return.= '</a></li>';
	}

	$return.= '</ul>';

	return $return;
}
add_shortcode( 'wiser_social_links', 'wiser_social_links' );






////////////////////////////////////////////////////
function wiser_example_shortcode( $atts ) {
	$a = shortcode_atts( array(
		'foo' => 'something',
		'bar' => 'something else',
	), $atts );

	return "foo = {$a['foo']}";
}
add_shortcode( 'wiser_example_shortcode', 'wiser_example_shortcode' );

?>