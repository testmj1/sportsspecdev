<?php

/**
 * Class Advanced_Ads_Tracking_Public_Stats
 */
class Advanced_Ads_Tracking_Public_Stats {
	const PUBLIC_STATS_DEFAULT = 'ad-stats';
	/**
	 * Current ad options.
	 *
	 * @var array
	 */
	private $ad_options;
	/**
	 * Current ad object.
	 *
	 * @var Advanced_Ads_Ad
	 */
	private $ad;

	/**
	 * Advanced_Ads_Tracking_Public_Stats constructor.
	 *
	 * @param int $ad_id The ad id for which to generate public stats.
	 */
	public function __construct( $ad_id ) {
		$this->ad         = new Advanced_Ads_Ad( $ad_id );
		$this->ad_options = $this->ad->options();
	}

	/**
	 * Check if the current ad has been saved and has tracking enabled.
	 *
	 * @return bool
	 */
	public function ad_has_tracking() {
		return ! empty( $this->ad_options['tracking'] ) && is_array( $this->ad_options['tracking'] ) && array_key_exists( 'enabled', $this->ad_options['tracking'] );
	}

	/**
	 * Get the public stats URL.
	 *
	 * @return string
	 */
	public function get_url() {
		$public_stats_slug = self::get_slug();
		$public_id         = $this->get_id();

		return empty( get_option( 'permalink_structure' ) )
			? add_query_arg( [ $public_stats_slug => $public_id ], site_url() )
			: site_url( '/' . $public_stats_slug . '/' . $public_id . '/' );
	}

	/**
	 * Gets public ID from ad_id
	 *
	 * @return string
	 */
	public function get_id() {
		if ( ! empty( $this->ad_options['tracking']['public-id'] ) ) {
			return $this->ad_options['tracking']['public-id'];
		}

		return $this->set_id();
	}

	/**
	 * Sets public ID from ad ID
	 *
	 * @return string
	 */
	private function set_id() {
		$this->ad_options['tracking']['public-id'] = wp_generate_password( 48, false );
		Advanced_Ads_Ad::save_ad_options( $this->ad->id, (array) $this->ad_options );

		return $this->ad_options['tracking']['public-id'];
	}

	/**
	 * Get the slug for public stats.
	 *
	 * @return string
	 */
	public static function get_slug() {
		$tracking_options = Advanced_Ads_Tracking_Plugin::get_instance()->options();

		return ! empty( $tracking_options['public-stats-slug'] ) ? $tracking_options['public-stats-slug'] : self::PUBLIC_STATS_DEFAULT;
	}

	/**
	 * Get the public ad name as set by the user.
	 *
	 * @param bool $fallback Whether to return the ad title.
	 *
	 * @return string
	 */
	public function get_name( $fallback = false ) {
		if ( ! empty( $this->ad_options['tracking']['public-name'] ) ) {
			return $this->ad_options['tracking']['public-name'];
		}

		return $fallback ? $this->ad->title : '';
	}
}
