<?php

/**
 * Class Advanced_Ads_Tracking_Dbop
 */
class Advanced_Ads_Tracking_Dbop {
	/**
	 * The unique instance of this class
	 */
	private static $instance = null;

	private $remove_periods;

	private $export_periods;

	const MIN_DATE = '2010-02-01';

	/**
	 * Global WordPress database class instance.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Impressions table with prefix.
	 *
	 * @var string
	 */
	private $impressions_table;

	/**
	 * Clicks table with prefix.
	 *
	 * @var string
	 */
	private $clicks_table;

	/**
	 * Advanced_Ads_Tracking_Dbop constructor.
	 */
	private function __construct() {
		$this->remove_periods    = [
			'beforethisyear' => __( 'everything before this year', 'advanced-ads-tracking' ),
			'first6months'   => __( 'first 6 months', 'advanced-ads-tracking' ),
		];
		$this->export_periods    = [
			'last12months'   => __( 'last 12 months', 'advanced-ads-tracking' ),
			'lastyear'       => __( 'last year', 'advanced-ads-tracking' ),
			'thisyear'       => __( 'this year', 'advanced-ads-tracking' ),
			'beforethisyear' => __( 'everything before this year', 'advanced-ads-tracking' ),
			'first6months'   => __( 'first 6 months', 'advanced-ads-tracking' ),
		];
		$this->wpdb              = $GLOBALS['wpdb'];
		$this->impressions_table = Advanced_Ads_Tracking_Util::get_instance()->get_impression_table();
		$this->clicks_table      = Advanced_Ads_Tracking_Util::get_instance()->get_click_table();

		add_filter( 'advanced-ads-tracking-get-period-bounds', [ $this, 'add_remove_periods_bounds' ], 25, 2 );

		// AJAX ACTION
		add_action( 'wp_ajax_advads_tracking_remove', [ $this, 'ajax_remove' ] );
		add_action( 'wp_ajax_advads_tracking_export', [ $this, 'ajax_export' ] );
		add_action( 'wp_ajax_advads_tracking_reset', [ $this, 'ajax_reset' ] );
		add_action( 'wp_ajax_advads_tracking_debug_mode', [ $this, 'ajax_debug_mode' ] );
	}

	/**
	 * Handle start/stop debugging setting.
	 */
	public function ajax_debug_mode() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'advads_tracking_dbop' ) || ! current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options' ) ) ) {
			wp_send_json_error( [], 401 );
		}

		// end debugging.
		if ( $_POST['ad'] === 'cancel' ) {
			if ( delete_option( Advanced_Ads_Tracking_Debugger::DEBUG_OPT ) ) {
				wp_send_json_success();
			}
			wp_send_json_error( [ 'message' => __( "Can't delete debugging option", 'advanced-ads-tracking' ) ] );
		}

		// try creating the debug file.
		$debug_file = Advanced_Ads_Tracking_Debugger::get_debug_file_handle();
		if ( ! $debug_file ) {
			$message = __( "The debug log file can't be written.", 'advanced-ads-tracking' );
			$message .= sprintf(
				/* translators: placeholder is path to WP_CONTENT_DIR */
				__( ' Please make sure the directory %s is writable', 'advanced-ads-tracking' ),
				sprintf( '<code>%s</code>', WP_CONTENT_DIR )
			);
			wp_send_json_error( [ 'message' => $message ], 400 );
		}

		// try saving debug option.
		if ( update_option( Advanced_Ads_Tracking_Debugger::DEBUG_OPT, [
			'id'   => $_POST['ad'] === 'all' ? true : (int) $_POST['ad'],
			'time' => time(),
		] ) ) {
			wp_send_json_success();
		}

		wp_send_json_error( [ 'message' => __( "Can't save debugging option", 'advanced-ads-tracking' ) ] );
	}

	/**
	 * Remove stats for a single ad or all ads.
	 */
	public function ajax_reset() {
		if (
			! wp_verify_nonce( $_POST['nonce'], 'advads_tracking_dbop' )
			|| ! current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options' ) )
		) {
			die();
		}

		$result = $this->reset_stats( (int) $_POST['ad'] );

		// if all stats got deleted and the tracking method is ga, redirect to settings.
		if ( ! Advanced_Ads_Tracking_Admin::get_instance()->show_stats() ) {
			$result['redirect'] = esc_url( add_query_arg( 'page', 'advanced-ads-settings', get_admin_url() . 'admin.php' ) ) . '#top#tracking';
		}

		wp_send_json_success( $result );
	}

	/**
	 * Resets stats for ads.
	 *
	 * @param int $ad_id Ad id, 0 for 'all-ads'.
	 *
	 * @return array Successful and message.
	 */
	private function reset_stats( $ad_id = 0 ) {
		$ad_id = empty( $ad_id ) ? 'all-ads' : $ad_id;

		switch ( $this->reset_stats_db( $ad_id ) ) {
			case 1:
				return [
					'status' => true,
					'msg'    => esc_attr__( 'All impressions and clicks removed.', 'advanced-ads-tracking' ),
				];
			case - 1:
				return [
					'status' => false,
					'msg'    => (int) $ad_id < 1
						? esc_attr__( 'No stats removed.', 'advanced-ads-tracking' )
						/* Translators: %d is the ad_id */
						: sprintf( esc_attr__( 'No stats for ad ID %d removed.', 'advanced-ads-tracking' ), $ad_id ),
				];
			case 0:
			default:
				return [
					'status' => true,
					/* Translators: %d is the ad_id */
					'msg'    => sprintf( esc_attr__( 'Impressions and clicks for ad ID %d removed.', 'advanced-ads-tracking' ), $ad_id ),
				];
		}
	}

	/**
	 * Removes records for ads that no more exist.
	 *
	 * @return bool true on success, false if no ad found.
	 */
	private function reset_stats_deleted_ads() {
		$deleted_ads = $this->get_deleted_ads();
		if ( empty( $deleted_ads['impressions'] ) && empty( $deleted_ads['clicks'] ) ) {
			return false;
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- we can't add table names as placeholders.
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- implode int[] to create IN statement.
		if ( ! empty( $deleted_ads['impressions'] ) ) {
			$this->wpdb->query( "DELETE FROM $this->impressions_table WHERE `ad_id` IN (" . implode( ',', $deleted_ads['impressions'] ) . ')' );
		}

		if ( ! empty( $deleted_ads['clicks'] ) ) {
			$this->wpdb->query( "DELETE FROM $this->clicks_table WHERE `ad_id` IN (" . implode( ',', $deleted_ads['clicks'] ) . ')' );
		}

		// phpcs:enable

		return true;
	}

	/**
	 * Resets stats for ads.
	 *
	 * @param string|int $ad_id One of 'deleted-ads', 'all-ads' or and integer ad id.
	 *
	 * @return int 1: success for all ads, -1: no succes, 0: specific ad_id.
	 */
	private function reset_stats_db( $ad_id ) {
		if ( empty( $ad_id ) ) {
			return false;
		}

		/**
		 * Before stats for ads get deleted from the db.
		 *
		 * @param string|int $ad_id One of 'deleted-ads', 'all-ads' or and integer ad id.
		 */
		do_action( 'advanced-ads-pre-reset-stats', $ad_id );

		if ( $ad_id === 'deleted-ads' ) {
			return $this->reset_stats_deleted_ads() ? 1 : - 1;
		}

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- we can't add table names as placeholders.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- we can't add table names as placeholders.
		// Reset the whole table if all stats should be removed.
		if ( $ad_id === 'all-ads' ) {
			$this->wpdb->query( 'TRUNCATE TABLE ' . $this->impressions_table );
			$this->wpdb->query( 'TRUNCATE TABLE ' . $this->clicks_table );

			return 1;
		};

		// Reset stats for individual ad.
		$ad_id = (int) $ad_id;
		if ( $ad_id > 0 ) {
			// Remove impressions.
			$affected_rows = $this->wpdb->query(
				$this->wpdb->prepare( "DELETE FROM $this->impressions_table WHERE ad_id = %d", $ad_id )
			);
			// Remove clicks.
			$affected_rows += $this->wpdb->query(
				$this->wpdb->prepare( "DELETE FROM $this->clicks_table WHERE ad_id = %d", $ad_id )
			);

			return $affected_rows > 0 ? 0 : -1;
		}
		// phpcs:enable

		return -1;
	}

	public function ajax_remove() {
		if ( false !== wp_verify_nonce( $_POST['nonce'], 'advads_tracking_dbop' ) && current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options' ) ) ) {
			$period = ( $_POST['period'] ) ? $_POST['period'] : false;
			$result = $this->remove( $period );
			header( 'Content-Type: application/json' );
			echo json_encode( $result );
			die;
		}
		die;
	}

	/**
	 * AJAX callback to generate a stats export.
	 *
	 * @return void
	 */
	public function ajax_export() {
		if ( false !== wp_verify_nonce( $_GET['nonce'], 'advads_tracking_dbop' ) && current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options' ) ) ) {
			$period = ( $_GET['period'] ) ? stripslashes( $_GET['period'] ) : false;
			$from   = ( isset( $_GET['from'] ) ) ? $_GET['from'] : '';
			$to     = ( isset( $_GET['to'] ) ) ? $_GET['to'] : '';
			$data   = $this->get_export_data( $period, $from, $to );
			if ( false === $data ) {
				echo 'invalid period';
				die;
			} else {
				$file_name = sprintf(
					'%s-advanced-ads-stats',
					sanitize_title( preg_replace(
						'#^(?:[^:]+:)?//(?:www\.)?([^/]+)#',
						'$1',
						get_bloginfo( 'url' )
					) )
				);
				if ( ! empty( $data['impressions'] ) ) {
					$first_date = key( $data['impressions'] );
					end( $data['impressions'] );
					$end_date = key( $data['impressions'] );
					reset( $data['impressions'] );
					$file_name .= "-{$first_date}_{$end_date}";
				}
				$file_name .= '.csv';

				ob_start();
				$str = "Date,Ad ID,Impressions,Clicks,Ad title\n";
				foreach ( $data['impressions'] as $date => $impr_block ) {
					foreach ( $impr_block as $ID => $impr ) {
						$title  = 'deleted';
						$clicks = 0;
						$imprs  = 0;
						if ( array_key_exists( $ID, $data['ads'] ) ) {
							$title = $data['ads'][ $ID ];
							// escape " and , ( RFC 4180 )[http://www.rfc-base.org/rfc-4180.html] - shouldn't be EOL in post title
							if ( false !== strpos( $title, ',' ) || false !== strpos( $title, '"' ) ) {
								$title = str_replace( '",', "'',", $title );
								$title = '"' . $title . '"';
							}
						}
						if ( ! empty( $impr_block[ $ID ] ) ) {
							$imprs = $impr_block[ $ID ];
						}
						if ( isset( $data['clicks'][ $date ] ) && ! empty( $data['clicks'][ $date ][ $ID ] ) ) {
							$clicks = $data['clicks'][ $date ][ $ID ];
						}
						$str .= "$date,$ID,$imprs,$clicks,$title\n";
					}
				}
				echo $str;
				header( 'Content-Description:FileTransfer' );
				header( 'Content-Type:text/csv;' );
				header( 'Content-Disposition:attachment;filename="' . $file_name . '"' );
				header( 'Expires:0' );
				header( 'Cache-Control:must-revalidate' );
				header( 'Pragma:public' );
				header( 'Content-Length:' . ( ob_get_length() ) );
				ob_end_flush();
			}
		}
		die;
	}

	public function get_remove_periods() {
		return $this->remove_periods;
	}

	public function get_export_periods() {
		return $this->export_periods;
	}

	/**
	 *  Get the period limit to be used in SQL query from the period name. applies filter for future extensions
	 *
	 * @param string $period_name Period Name.
	 * @param string $from        jQuery-ui DatePicker range start.
	 * @param string $to          jQuery-ui DatePicker range end.
	 *
	 * @return string[] [startDate, endDate]
	 */
	public function get_period_bounds( $period_name = 'last12months', $from = '', $to = '' ) {
		$now = date_create( 'now', Advanced_Ads_Tracking_Util::get_wp_timezone() );

		switch ( $period_name ) {
			case 'custom':
				if ( false !== strpos( $to, '/' ) ) {
					// from and to are from jQuery-ui DatePicker
					$from   = explode( '/', $from );
					$to     = explode( '/', $to );
					$result = [
						$from[2] . '-' . $from[0] . '-' . $from[1],
						$to[2] . '-' . $to[0] . '-' . $to[1],
					];
				} else {
					// already converted
					$result = [ $from, $to ];
				}
				break;
			case 'lastyear':
				$last_year = (string) ( (int) $now->format( 'Y' ) - 1 );
				$result    = [ $last_year . '-01-01', $last_year . '-12-31' ];
				break;

			case 'thisyear':
				$result = [ $now->format( 'Y' ) . '-01-01', $now->format( 'Y-m-d' ) ];
				break;

			default: // last12months
				$last_month = date_create( $now->format( 'Y-' ) . ( (int) $now->format( 'm' ) - 1 ) . '-1' );
				$end_date   = date_create( $last_month->format( 'Y-m-t' ) );
				$start_date = date_create( ( (int) $now->format( 'Y' ) - 1 ) . $now->format( '-m' ) . '-01' );
				$result     = [ $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) ];
		}

		return apply_filters( 'advanced-ads-tracking-get-period-bounds', $result, $period_name );
	}

	/**
	 *  Returns the period bounds for data removal
	 *
	 * @param string[] $result      The previous result.
	 * @param string   $period_name Period name.
	 *
	 * @return array|string[]
	 */
	public function add_remove_periods_bounds( $result, $period_name ) {
		switch ( $period_name ) {
			case 'beforethisyear':
				$now  = date_create( 'now', Advanced_Ads_Tracking_Util::get_wp_timezone() );
				$year = (int) $now->format( 'Y' ) - 1;

				return [ self::MIN_DATE, $year . '-12-31' ];
			case 'first6months':
				$first_date = date_create( $this->get_first_record_date( 'Y-m-d' ) );
				$year       = (int) $first_date->format( 'Y' );
				$month      = (int) $first_date->format( 'm' );
				if ( 12 < $month + 6 ) {
					$month = $month + 6 - 12;
					$year  += 1;
				} else {
					$month += 6;
				}
				$end_date = date_create( $year . '-' . $month . '-01' );

				return [ $first_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-t' ) ];
			default:
				// Return the result as is.
				return $result;
		}
	}

	/**
	 * Get date as string of first record.
	 *
	 * @param string $format Date format.
	 *
	 * @return string
	 */
	public function get_first_record_date( $format ) {
		global $wpdb;
		$util              = Advanced_Ads_Tracking_Util::get_instance();
		$impressions_table = $util->get_impression_table();
		// phpcs:ignore WordPress.DB.PreparedSQL -- we can't prepare the table names.
		$result            = $wpdb->get_results( "SELECT `timestamp` FROM {$impressions_table} ORDER BY `timestamp` ASC LIMIT 1" );
		if ( ! $result ) {
			return '';
		}

		$oldest_impression = date_create(
			$util->get_date_from_db( $result[0]->timestamp, 'Y-m-d' ),
			Advanced_Ads_Tracking_Util::get_wp_timezone()
		);

		if ( ! empty( $format ) ) {
			return date_i18n( $format, $oldest_impression->getTimestamp() );
		}

		// get format from database.
		return date_i18n( get_option( 'date_format' ), $oldest_impression->getTimestamp() );
	}

	/**
	 *  Load stats groupped by day for export/compression
	 */
	public function load_stats( $period, $from = '', $to = '' ) {
		$bounds = $this->get_period_bounds( $period, $from, $to );
		$util   = Advanced_Ads_Tracking_Util::get_instance();
		$admin  = new Advanced_Ads_Tracking_Admin();
		$bounds = $period === 'custom' ? [ $from, $to ] : $this->get_period_bounds( $period, $from, $to );
		$_ads   = Advanced_Ads::get_ads( [ 'post_status' => [ 'publish', 'future', 'draft', 'pending', Advanced_Ads_Tracking_Util::get_expired_post_status() ] ] );
		$ads    = [];
		foreach ( $_ads as $ad ) {
			$ads[] = (string) $ad->ID;
		}
		// SQL query arguments
		$sql_args = [
			'period'      => 'custom',
			'groupby'     => 'day',
			'ad_id'       => $ads,
			'groupFormat' => 'Y-m-d',
			'from'        => $bounds[0],
			'to'          => $bounds[1],
		];

		$imprs  = $admin->load_stats( $sql_args, $util->get_impression_table() );
		$clicks = $admin->load_stats( $sql_args, $util->get_click_table() );

		return [ $imprs, $clicks ];
	}

	/**
	 *  Delete records for the give period
	 */
	private function remove( $period ) {
		if ( ! array_key_exists( $period, $this->remove_periods ) ) {
			return [
				'status' => false,
				'msg'    => 'invalid period',
				'value'  => $period,
			];
		}
		$util             = Advanced_Ads_Tracking_Util::get_instance();
		$click_table      = $util->get_click_table();
		$impression_table = $util->get_impression_table();
		$bounds           = $this->get_period_bounds( $period );
		$start            = explode( '-', $bounds[0] );
		$end              = explode( '-', $bounds[1] );
		$gmt_offset       = 3600 * (float) get_option( 'gmt_offset', 0 );

		$start_ts = $util->get_timestamp( mktime( 0, 0, 1, (int) $start[1], (int) $start[2], (int) $start[0] ) - $gmt_offset );
		$end_ts   = $util->get_timestamp( mktime( 23, 0, 1, (int) $end[1], (int) $end[2], (int) $end[0] ) - $gmt_offset );

		$query = "DELETE $click_table, $impression_table FROM $click_table, $impression_table WHERE $click_table.timestamp BETWEEN $start_ts AND $end_ts AND $impression_table.timestamp BETWEEN $start_ts AND $end_ts";
		global $wpdb;

		$result = $wpdb->query( $query );
		if ( false === $result ) {
			return [ 'status' => false ];
		} else {
			// OPTIMIZE to retrieve unused space
			$o1 = "OPTIMIZE TABLE $impression_table";
			$o2 = "OPTIMIZE TABLE $click_table";

			$ro1    = $wpdb->query( $o1 );
			$ro2    = $wpdb->query( $o2 );
			$return = [ 'status' => true ];
			if ( false === $ro1 || false === $ro2 ) {
				$return['alt-msg'] = 'optimize-failure';
			}

			return $return;
		}
	}

	/**
	 *  Get info about db size
	 *
	 * @return array
	 */
	public function get_db_size() {
		global $wpdb;
		$clicks_table      = $wpdb->prefix . 'advads_clicks';
		$impressions_table = $wpdb->prefix . 'advads_impressions';
		$q1                = "SELECT round(((data_length + index_length) / 1024), 2) AS `size` FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "' AND table_name = '$clicks_table'";
		$q2                = "SELECT round(((data_length + index_length) / 1024), 2) AS `size` FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "' AND table_name = '$impressions_table'";

		$clicks_size_results      = $wpdb->get_results( $q1 );
		$impressions_size_results = $wpdb->get_results( $q2 );

		$impression_size = '0';
		$click_size      = '0';
		if ( is_array( $impressions_size_results ) && isset( $impressions_size_results[0]->size ) ) {
			$impression_size = $impressions_size_results[0]->size;
		}
		if ( is_array( $clicks_size_results ) && isset( $clicks_size_results[0]->size ) ) {
			$click_size = $clicks_size_results[0]->size;
		}

		$q3 = "SELECT COUNT(*) AS count FROM $clicks_table";
		$q4 = "SELECT COUNT(*) AS count FROM $impressions_table";

		$clicks_count_results      = $wpdb->get_results( $q3 );
		$impressions_count_results = $wpdb->get_results( $q4 );

		$clicks_row_count      = 0;
		$impressions_row_count = 0;

		if ( $impressions_count_results && isset( $impressions_count_results[0]->count ) && ! empty( $impressions_count_results[0]->count ) ) {
			$impressions_row_count = (int) $impressions_count_results[0]->count;
		}
		if ( $clicks_count_results && isset( $clicks_count_results[0]->count ) && ! empty( $clicks_count_results[0]->count ) ) {
			$clicks_row_count = (int) $clicks_count_results[0]->count;
		}

		$util = Advanced_Ads_Tracking_Util::get_instance();

		$q5 = "SELECT `timestamp` FROM $clicks_table ORDER BY `timestamp` ASC LIMIT 1";
		$q6 = "SELECT `timestamp` FROM $impressions_table ORDER BY `timestamp` ASC LIMIT 1";

		$oldest_click      = null;
		$oldest_impression = null;

		$old_click_result      = $wpdb->get_results( $q5 );
		$old_impression_result = $wpdb->get_results( $q6 );
		if ( $old_click_result ) {
			$oldest_click  = $util->get_date_from_db( $old_click_result[0]->timestamp, 'Y-m-d' );
			$_oldest_click = date_create( $oldest_click );
			$oldest_click  = $_oldest_click->format( 'U' );
		}
		if ( $old_impression_result ) {
			$oldest_impression  = $util->get_date_from_db( $old_impression_result[0]->timestamp, 'Y-m-d' );
			$_oldest_impression = date_create( $oldest_impression );
			$oldest_impression  = $_oldest_impression->format( 'U' );
		}

		return [
			'impression_row_count' => $impressions_row_count,
			'click_row_count'      => $clicks_row_count,
			'first_impression'     => $oldest_impression, // UNIX timestamp | NULL
			'first_click'          => $oldest_click, // UNIX timestamp | NULL
			'impression_in_kb'     => $impression_size,
			'click_in_kb'          => $click_size,
		];
	}

	/**
	 * Get IDs of deleted ads from the impression and clicks tables.
	 *
	 * @return array array of int[] for impressions and clicks.
	 */
	public function get_deleted_ads() {
		global $wpdb;
		$impressions_table = Advanced_Ads_Tracking_Util::get_instance()->get_impression_table();
		$clicks_table      = Advanced_Ads_Tracking_Util::get_instance()->get_click_table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- we can't add table names as placeholders.
		$imp = $wpdb->get_col( $wpdb->prepare(
			"SELECT ad_id FROM {$impressions_table} WHERE ad_id NOT IN ( SELECT ID from {$wpdb->posts} WHERE post_type = %s ) GROUP BY ad_id",
			Advanced_Ads::POST_TYPE_SLUG
		) );

		$clk = $wpdb->get_col( $wpdb->prepare(
			"SELECT ad_id FROM {$clicks_table} WHERE ad_id NOT IN ( SELECT ID from {$wpdb->posts} WHERE post_type = %s ) GROUP BY ad_id",
			Advanced_Ads::POST_TYPE_SLUG
		) );

		// phpcs:enable

		return [
			'impressions' => array_map( function( $value ) { return (int) $value; }, $imp ),
			'clicks'      => array_map( function( $value ) { return (int) $value; }, $clk ),
		];
	}

	/**
	 *  Retrieve data to be exported ( ads + stats )
	 */
	private function get_export_data( $period, $from = '', $to = '' ) {
		$bounds = $this->get_period_bounds( $period, $from, $to );
		if ( empty( $bounds ) ) {
			return false;
		}
		$_ads  = Advanced_Ads::get_ads( [ 'post_status' => [ 'publish', 'future', 'draft', 'pending', Advanced_Ads_Tracking_Util::get_expired_post_status() ] ] );
		$stats = $this->load_stats( $period, $bounds[0], $bounds[1] );

		if ( false === $stats[0] ) {
			return false;
		}
		list( $imprs, $clicks ) = $stats;
		$ads = [];
		foreach ( $_ads as $ad ) {
			$ads[ $ad->ID ] = $ad->post_title;
		}

		return [
			'ads'         => $ads,
			'impressions' => $imprs,
			'clicks'      => $clicks,
		];
	}

	/**
	 *  Output the period selection inputs for exporting and compressing data
	 */
	public static function period_select_inputs( $args = [] ) {
		$default_args = [
			'period'         => [ '', '' ],
			'from'           => [ '', '' ],
			'to'             => [ '', '' ],
			'custom'         => true,
			'period-options' => [
				'last12months' => __( 'last 12 months', 'advanced-ads-tracking' ),
				'lastyear'     => __( 'last year', 'advanced-ads-tracking' ),
				'thisyear'     => __( 'this year', 'advanced-ads-tracking' ),
			],
		];
		$_args        = $args + $default_args;
		if ( isset( $args['period-options'] ) && is_array( $args['period-options'] ) ) {
			$_args['period-options'] = $args['period-options'];
		}
		?>
		<span class="advads-period-inputs">
		<select <?php echo ( ! empty( $_args['period'][0] ) ) ? 'id="' . $_args['period'][0] . '"' : ''; ?> class="<?php echo $_args['period'][1]; ?> advads-period">
		<?php foreach ( $_args['period-options'] as $value => $readable ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>"><?php echo wp_strip_all_tags( $readable ); ?></option>
		<?php endforeach; ?>
			<?php if ( $_args['custom'] ) : ?>
				<option value="custom"><?php _e( 'custom', 'advanced-ads-tracking' ); ?></option>
			<?php endif; ?>
		</select>
		<input style="display:none;width:auto;" type="text" <?php echo ( $_args['from'][0] ) ? 'id="' . $_args['from'][0] . '"' : ''; ?> class="<?php echo $_args['from'][1]; ?> advads-from advads-datepicker" value="" autocomplete="off" size="10" maxlength="10" placeholder="<?php _e( 'from', 'advanced-ads-tracking' ); ?>"/>
		<input style="display:none;width:auto;" type="text" <?php echo ( $_args['to'][0] ) ? 'id="' . $_args['to'][0] . '"' : ''; ?> class="<?php echo $_args['to'][1]; ?> advads-to advads-datepicker" value="" autocomplete="off" size="10" maxlength="10" placeholder="<?php _e( 'to', 'advanced-ads-tracking' ); ?>"/>
		</span>
		<?php
	}

	/**
	 * Return the unique instance of this class.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
