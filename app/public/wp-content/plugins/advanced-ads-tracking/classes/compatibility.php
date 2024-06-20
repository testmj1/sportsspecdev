<?php

/**
 * Helper class to achieve compatibility with third party plugins.
 */
class Advanced_Ads_Tracking_Compatibility {
	/**
	 * Advanced_Ads_Tracking_Compatibility constructor.
	 */
	public function __construct() {
		add_filter( 'advanced-ads-compatibility-critical-inline-js', [ self::class, 'critical_inline_js' ], 10, 2 );
		add_filter( 'rocket_preload_links_exclusions', [ self::class, 'exclude_linkout_from_rocket_preload' ] );
	}

	/**
	 * Add advads-tracking to array not be optimized by WP Rocket, Complianz et al.
	 *
	 * @param array  $inline_js       Array with unique strings (IDs), identifying inline JavaScript.
	 * @param string $frontend_prefix The frontend_prefix option setting.
	 *
	 * @return array
	 */
	public static function critical_inline_js( $inline_js, $frontend_prefix ) {
		$inline_js[] = sprintf( 'id="%stracking"', $frontend_prefix );

		return $inline_js;
	}

	/**
	 * Add the linkout link base to be excluded from WP Rocket's link preloading.
	 *
	 * @param iterable $links Array with existing links/fragments.
	 *
	 * @return iterable
	 */
	public static function exclude_linkout_from_rocket_preload( iterable $links ): iterable {
		// RegEx for excluding all links starting with the link-base prefix.
		$links[] = sprintf( '/%s/.+', Advanced_Ads_Tracking_Util::get_instance()->get_link_base() );

		return $links;
	}
}
