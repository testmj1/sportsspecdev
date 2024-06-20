<?php

/**
 * Class Advanced_Ads_Tracking_Debugger
 *
 * DONT ADD EXTERNAL DEPENDENCIES TO THIS CLASS AS IT WILL BREAK THE CUSTOM AJAX HANDLER
 */
class Advanced_Ads_Tracking_Debugger {
	const DEBUG_OPT          = 'advads_track_debug';
	const DEBUG_FILENAME_OPT = 'advads_track_debug_filename';
	const DEBUG_HOURS        = 48;
	const HEADERS            = [ 'Date', 'Database Table', 'Ad ID', 'Remote IP', 'Handler', 'URL', 'User Agent', 'Execution Time', 'Errors' ];

	/**
	 * Get the debug filename.
	 *
	 * @return string
	 */
	public static function get_debug_filename() {
		return get_option( self::DEBUG_FILENAME_OPT ) ?: self::generate_debug_filename();
	}

	/**
	 * Get the debug file path.
	 *
	 * @return string
	 */
	public static function get_debug_file_path() {
		return WP_CONTENT_DIR . '/' . self::get_debug_filename();
	}

	/**
	 * Get the URL to the debug file.
	 *
	 * @return string
	 */
	public static function get_debug_file_url() {
		return content_url( self::get_debug_filename() );
	}

	/**
	 * Check if debugging constant is set and match the settings in the database.
	 *
	 * @return bool Whether the settings have been changed.
	 */
	public static function check_debugging_constant() {
		$option = get_option( self::DEBUG_OPT );
		if (
			// either we don't have an option set, but the constant is there
			( empty( $option ) && self::parse_debug_constant() )
			// or we have an option, but the value of option and constant do not match
			|| ( isset( $option['id'] ) && ( self::parse_debug_constant() && self::parse_debug_constant() !== $option['id'] ) )
		) {
			return update_option(
				self::DEBUG_OPT,
				[
					'id'   => self::parse_debug_constant(),
					'time' => 0,
				]
			);
		}

		// if the option's time is 0, but the constant is not true or int, delete the option
		if ( ( isset( $option['time'] ) && empty( $option['time'] ) ) && ! self::parse_debug_constant() ) {
			return delete_option( self::DEBUG_OPT );
		}

		return false;
	}

	/**
	 * Check if the debug option as an expiration time, and delete the option.
	 */
	public static function delete_expired_option() {
		$debug_option = get_option( self::DEBUG_OPT, false );
		if ( $debug_option && ! empty( $debug_option['time'] ) && time() > $debug_option['time'] + ( 3600 * self::DEBUG_HOURS ) ) {
			delete_option( self::DEBUG_OPT );
		}
	}

	/**
	 * Check whether debugging is prohibited through the constant.
	 *
	 * @return bool
	 */
	public static function is_debugging_forbidden() {
		if ( ! defined( 'ADVANCED_ADS_TRACKING_DEBUG' ) ) {
			return false;
		}

		return ADVANCED_ADS_TRACKING_DEBUG === 0 || ADVANCED_ADS_TRACKING_DEBUG === false || ADVANCED_ADS_TRACKING_DEBUG === 'false';
	}

	/**
	 * Check whether debugging is enabled.
	 *
	 * @param null|int $id optional ad id to check.
	 *
	 * @return bool
	 */
	public static function debugging_enabled( $id = null ) {
		static $enabled;
		$id = (int) $id;
		if ( isset( $enabled[ $id ] ) ) {
			return $enabled[ $id ];
		}

		$option = get_option( self::DEBUG_OPT, false );
		if ( ! is_array( $option ) ) {
			$enabled[ $id ] = false;

			return $enabled[ $id ];
		}

		// if the option id is an integer, check if the current id is enabled
		if ( is_int( $option['id'] ) ) {
			$enabled[ $id ] = $option['id'] === $id;

			return $enabled[ $id ];
		}

		$enabled[ $id ] = (bool) $option['id'];

		return $enabled[ $id ];
	}

	/**
	 * Get a writeable file handle for the debug file.
	 *
	 * @param string|null $debug_file Optional full path to a debug file (used in custom AJAX handler).
	 *
	 * @return false|resource
	 */
	public static function get_debug_file_handle( $debug_file = null ) {
		if ( is_null( $debug_file ) ) {
			$debug_file = self::get_debug_file_path();
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$handle = fopen( $debug_file, 'ab' );
		if ( ! $handle ) {
			return false;
		}
		// Write headers to logging csv if empty.
		if ( empty( filesize( $debug_file ) ) ) {
			self::write_headers( $handle );
		}

		return $handle;
	}

	/**
	 * Write the debug information to the debug file.
	 *
	 * @param int         $id             The ad id.
	 * @param string      $table          The impression logging table.
	 * @param int         $execution_time Execution time of tracking method in milliseconds.
	 * @param string      $handler        Optional. The handler used for tracking.
	 * @param string      $error_message  Optional. If an error occurred add the error message here.
	 * @param string|null $debug_file     Optional. Full path to the debug file.
	 */
	public static function log( $id, $table, $execution_time, $handler = '', $error_message = '', $debug_file = null ) {
		$handle = self::get_debug_file_handle( $debug_file );
		if ( empty( $handler ) && function_exists( 'get_option' ) ) {
			$handler = self::parse_handler();
		}

		fputcsv(
			$handle,
			[
				self::date_i18n( 'Y-m-d H:i:s' ),
				$table,
				$id,
				sprintf( "'%s", self::get_ip() ),
				urldecode( $handler ),
				self::get_url(),
				self::get_user_agent(),
				$execution_time < 0 ? 'n/a' : $execution_time,
				$error_message,
			]
		);

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $handle );
	}


	/**
	 * Write CSV headers to the debug file.
	 *
	 * @param resource $handle Writable file handle for the debug file.
	 */
	private static function write_headers( $handle ) {
		// prevent duplicate headers.
		static $written = false;
		if ( $written ) {
			return;
		}
		$written = true;
		fputcsv( $handle, self::HEADERS );
	}

	/**
	 * Get the remote IP address.
	 *
	 * @return string
	 */
	private static function get_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	}

	/**
	 * Localize the date format if possible, i.e. if WordPress is loaded
	 *
	 * @param string $format The date format.
	 *
	 * @return string
	 */
	private static function date_i18n( $format ) {
		if ( function_exists( 'date_i18n' ) ) {
			return date_i18n( $format );
		}

		// phpcs:ignore WordPress.DateTime.RestrictedFunctions
		return date( $format );
	}

	/**
	 * Get the referring URL.
	 * If this is AJAX-tracked, get the post ID instead of the request UI.
	 *
	 * @return string
	 */
	private static function get_url() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		static $url;
		if ( ! is_null( $url ) ) {
			return $url;
		}

		if ( self::is_cache_busting() ) {
			$args = json_decode( stripslashes( $_REQUEST['deferedAds'][0]['ad_args'] ) );
			if ( isset( $args->url_parameter ) ) {
				$url = $args->url_parameter;
			}
		} elseif ( ! empty( $_REQUEST['referrer'] ) ) {
			$url = urldecode( $_REQUEST['referrer'] );
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$url = ( empty( $url ) && isset( $_SERVER['REQUEST_URI'] ) ) ? $_SERVER['REQUEST_URI'] : $url;
		$url = rtrim( $url, '/' );
		if ( empty( $url ) ) {
			$url = '/';
		}

		return filter_var( $url, FILTER_SANITIZE_URL );
	}

	/**
	 * Check whether cache busting is enabled.
	 *
	 * @return bool
	 */
	private static function is_cache_busting() {
		return isset( $_REQUEST['deferedAds'][0]['ad_args'] );
	}

	/**
	 * Get the user agent.
	 *
	 * @return string
	 */
	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

	/**
	 * Check the value of the debugging constant.
	 *
	 * @return bool|int bool for global debugging, int if enabled for single ad.
	 */
	private static function parse_debug_constant() {
		static $constant;
		if ( ! is_null( $constant ) ) {
			return $constant;
		}
		// not defined ==> false.
		if ( ! defined( 'ADVANCED_ADS_TRACKING_DEBUG' ) ) {
			$constant = false;

			return $constant;
		}

		// is true boolean ==> use value.
		if ( is_bool( ADVANCED_ADS_TRACKING_DEBUG ) ) {
			$constant = ADVANCED_ADS_TRACKING_DEBUG;

			return $constant;
		}

		// is an integer, if 0 or 1 treat as bool, int otherwise.
		if ( is_int( ADVANCED_ADS_TRACKING_DEBUG ) ) {
			if ( ADVANCED_ADS_TRACKING_DEBUG < 2 ) {
				$constant = (bool) ADVANCED_ADS_TRACKING_DEBUG;

				return $constant;
			}

			$constant = ADVANCED_ADS_TRACKING_DEBUG;

			return $constant;
		}

		// string value 'false'.
		if ( ADVANCED_ADS_TRACKING_DEBUG === 'false' ) {
			$constant = false;

			return $constant;
		}

		// string value 'true'.
		if ( ADVANCED_ADS_TRACKING_DEBUG === 'true' ) {
			$constant = true;

			return $constant;
		}

		// is greater than 1 ==> use intval.
		if ( ADVANCED_ADS_TRACKING_DEBUG > 1 ) {
			$constant = (int) ADVANCED_ADS_TRACKING_DEBUG;

			return $constant;
		}

		// something else, constant is set ==> so true.
		$constant = true;

		return $constant;
	}

	/**
	 * Generate a unique name for the debug file and save it to the database.
	 *
	 * @return string filename
	 */
	private static function generate_debug_filename() {
		// domain name without scheme.
		$domain = str_replace( '.', '_', preg_replace( '#^https?://([a-z0-9-.]+)/?.*?$#i', '$1', get_home_url() ) );
		// create a unique filename.
		$filename = sprintf(
			'advanced-ads-tracking-%s-%s.csv',
			$domain,
			wp_hash( implode( '', [ $domain, time() ] ) )
		);
		// save the filename to the db.
		add_option( self::DEBUG_FILENAME_OPT, $filename );

		return $filename;
	}

	/**
	 * Get a human readable name for tracking handler.
	 *
	 * @return string
	 */
	private static function parse_handler() {
		$method = Advanced_Ads_Tracking_Plugin::get_instance()->get_tracking_method();

		if ( 'frontend' === $method ) {
			if ( self::is_cache_busting() ) {
				return 'Frontend with AJAX Cache Busting';
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_REQUEST['handler'] ) && urldecode( $_REQUEST['handler'] ) === 'Frontend on AMP' ) {
				return 'Frontend on AMP (Legacy)';
			}

			return 'Frontend (Legacy)';
		}

		if ( 'onrequest' === $method ) {
			if ( function_exists( 'advads_is_amp' ) && advads_is_amp() ) {
				return 'Database on AMP';
			}

			return 'Database';
		}

		return $method;
	}
}
