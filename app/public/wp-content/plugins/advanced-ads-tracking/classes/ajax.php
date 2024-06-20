<?php

/**
 * Class Advanced_Ads_Tracking_Ajax
 */
class Advanced_Ads_Tracking_Ajax {
	const TRACK_IMPRESSION = 'aatrack-records';
	const TRACK_CLICK      = 'aatrack-click';

	/**
	 * Advanced_Ads_Tracking_Ajax constructor.
	 */
	public function __construct() {
		// register callback
		add_action( 'wp_ajax_' . self::TRACK_IMPRESSION, [ $this, 'track' ] ); // logged in users
		add_action( 'wp_ajax_nopriv_' . self::TRACK_IMPRESSION, [ $this, 'track' ] ); // frontend, not logged in
		add_action( 'wp_ajax_' . self::TRACK_CLICK, [ $this, 'track' ] ); // logged in users
		add_action( 'wp_ajax_nopriv_' . self::TRACK_CLICK, [ $this, 'track' ] ); // frontend, not logged in

		add_action( 'wp_ajax_advads-tracking-check-slug', [ $this, 'check_slug' ] );
		add_action( 'wp_ajax_advads-tracking-immediate-report', [ $this, 'immedate_report' ] );
		add_action( 'wp_ajax_advads_load_stats', [ $this, 'load_stats' ] );
		add_action( 'wp_ajax_advads_load_stats_file', [ $this, 'load_stats_file' ] );
		add_action( 'wp_ajax_advads_stats_file_info', [ $this, 'get_stats_file_info' ] );
	}

	public function get_stats_file_info() {
		$nonce = ( isset( $_POST['nonce'] ) ) ? $_POST['nonce'] : '';
		if ( false === wp_verify_nonce( $nonce, 'advads-stats-page' ) ) {
			die;
		}
		$data   = $this->parse_csv( (int) $_POST['id'] );
		$result = [
			'status' => false,
		];
		if ( isset( $data['firstdate'] ) ) {
			$result = [
				'status'    => true,
				'firstdate' => $data['firstdate'],
				'lastdate'  => $data['lastdate'],
				'ads'       => implode( '-', array_keys( $data['ads'] ) ),
			];
		}
		header( 'Content-Type: application/json' );
		echo json_encode( $result );
		die;
	}

	/**
	 * Parse CSV stats file ( compatible PHP < 5.3 ).
	 *
	 * @param int $id Post/ad ID.
	 *
	 * @return array
	 */
	private function parse_csv( $id ) {
		$file   = get_attached_file( $id );
		$result = [
			'impressions' => [],
			'clicks'      => [],
			'ads'         => [],
			'status'      => true,
		];
		WP_Filesystem();
		global $wp_filesystem;
		$data = $wp_filesystem->get_contents( $file );
		if ( ! $data ) {
			// Ureadable file.
			return [
				'status' => false,
				'msg',
				__( 'unable to read file', 'advanced-ads-tracking' ),
			];
		}
		// Remove evntual BOM.
		$bom  = pack( 'H*', 'EFBBBF' );
		$data = preg_replace( "/^$bom/", '', $data );

		$lines = explode( "\n", $data );

		$lines = array_slice( $lines, 1 );
		foreach ( $lines as $line ) {
			if ( empty( $line ) ) {
				continue;
			}
			$cells       = [];
			$_cells      = explode( ',', $line );
			$cells_count = count( $_cells );

			if ( $cells_count > 5 ) {
				// Some extra commas are present in the ad title.
				foreach ( $_cells as $i => $value ) {
					if ( $i < 4 ) {
						$cells[] = $value;
					} else {
						$_title  = array_slice( $_cells, 4 );
						$cells[] = implode( ',', $_title );
						break;
					}
				}
			} else {
				// No extra commas.
				$cells = $_cells;
			}

			$cells = array_map( [ $this, 'trim_outer_quotes' ], $cells );
			$ts    = (int) str_replace( '-', '', $cells[0] );

			// Impressions.
			if ( ! isset( $result['impressions'][ $ts ] ) ) {
				$result['impressions'][ $ts ] = [];
			}
			$result['impressions'][ $ts ][ $cells[1] ] = (int) $cells[2];

			// Clicks.
			if ( ! isset( $result['clicks'][ $ts ] ) ) {
				$result['clicks'][ $ts ] = [];
			}
			$result['clicks'][ $ts ][ $cells[1] ] = (int) $cells[3];

			// Ad title.
			if ( ! isset( $result['ads'][ $cells[1] ] ) ) {
				$result['ads'][ $cells[1] ] = $cells[4];
			}
		}

		$firstdate = key( $result['impressions'] );
		end( $result['impressions'] );
		$lastdate = key( $result['impressions'] );
		reset( $result['impressions'] );
		$result['firstdate'] = substr( $firstdate, 0, 4 ) . '-' . substr( $firstdate, 4, 2 ) . '-' . substr( $firstdate, 6, 2 );
		$result['lastdate']  = substr( $lastdate, 0, 4 ) . '-' . substr( $lastdate, 4, 2 ) . '-' . substr( $lastdate, 6, 2 );

		return $result;
	}

	/**
	 * Remove outer quotes from CSV field.
	 *
	 * @param string $elem the current CSV field.
	 *
	 * @return string
	 */
	private function trim_outer_quotes( $elem ) {
		if ( empty( $elem ) ) {
			return $elem;
		}

		if ( $elem[0] === '"' && $elem[ strlen( $elem ) - 1 ] === '"' ) {
			return substr( $elem, 1, - 1 );
		}

		return $elem;
	}

	/**
	 *  Load stats from file for a given period
	 */
	public function load_stats_file() {
		$nonce = ( isset( $_POST['nonce'] ) ) ? $_POST['nonce'] : '';
		if ( false === wp_verify_nonce( $nonce, 'advads-stats-page' ) ) {
			die;
		}
		$result = [ 'status' => false ];
		parse_str( $_POST['args'], $args );
		$data = $this->parse_csv( (int) $args['file'] );
		if ( isset( $data['status'] ) && $data['status'] ) {
			$result = $this->prepare_stats_from_file( $data, $args['period'], $args['from'], $args['to'], $args['groupby'] );
		}
		header( 'Content-Type: application/json' );
		echo json_encode( $result );
		die;
	}

	public static function split_date( $d ) {
		return substr( $d, 0, 4 ) . '-' . substr( $d, 4, 2 ) . '-' . substr( $d, 6, 2 );
	}

	/**
	 *  Prepare data from CSV before sending it back to the browser
	 */
	private function prepare_stats_from_file( $data, $period, $from, $to, $groupby ) {
		$result = [
			'status' => true,
			'stats'  => [],
		];
		$_from  = (int) str_replace( [ '-', '/' ], [ '', '' ], $from );
		$_to    = (int) str_replace( [ '-', '/' ], [ '', '' ], $to );

		$periodstart = '';
		$periodend   = '';

		// define the timetsamp for the first and last record to return
		switch ( $period ) {
			case 'firstmonth':
				$firstdate   = date_create( $data['firstdate'] );
				$_from       = (int) $firstdate->format( 'Ym01' );
				$_to         = (int) $firstdate->format( 'Ymt' );
				$periodstart = $firstdate->format( 'Y-m-01' );
				$periodend   = $firstdate->format( 'Y-m-t' );
				break;
			case 'latestmonth':
				$lastdate    = date_create( $data['lastdate'] );
				$_from       = (int) $lastdate->format( 'Ym01' );
				$_to         = (int) $lastdate->format( 'Ymd' );
				$periodstart = $lastdate->format( 'Y-m-01' );
				$periodend   = $lastdate->format( 'Y-m-t' );
				break;
			default: // custom
				$periodstart = $from;
				$periodend   = $to;
		}
		$imprs        = [];
		$clicks       = [];
		$adIDs        = array_keys( $data['ads'] );
		$date         = null;
		$group_clicks = [];
		$group_imprs  = [];

		end( $data['impressions'] );
		$last_ts = key( $data['impressions'] );
		reset( $data['impressions'] );

		foreach ( $data['impressions'] as $ts => $_imprs ) {
			switch ( $groupby ) {
				case 'month':
					if ( $ts >= $_from && $ts <= $_to ) {
						$_date = date_create( self::split_date( $ts ) );
						if ( null === $date ) {
							$date = $_date->format( 'Y-m' );
						}
						if ( $ts === $last_ts ) {
							foreach ( $adIDs as $ad_id ) {
								if ( ! isset( $group_imprs[ $ad_id ] ) ) {
									$group_imprs[ $ad_id ] = 0;
								}
								if ( ! isset( $group_clicks[ $ad_id ] ) ) {
									$group_clicks[ $ad_id ] = 0;
								}
								$group_imprs[ $ad_id ] += (int) $_imprs[ $ad_id ];
								if ( isset( $data['clicks'][ $ts ][ $ad_id ] ) ) {
									$group_clicks[ $ad_id ] += (int) $data['clicks'][ $ts ][ $ad_id ];
								}
							}
						}
						if ( $ts === $last_ts || $date !== $_date->format( 'Y-m' ) ) {
							$imprs[ $date ]  = $group_imprs;
							$clicks[ $date ] = $group_clicks;

							$date         = $_date->format( 'Y-m' );
							$group_clicks = [];
							$group_imprs  = [];
						}
						foreach ( $adIDs as $ad_id ) {
							if ( ! isset( $group_imprs[ $ad_id ] ) ) {
								$group_imprs[ $ad_id ] = 0;
							}
							if ( ! isset( $group_clicks[ $ad_id ] ) ) {
								$group_clicks[ $ad_id ] = 0;
							}
							$group_imprs[ $ad_id ] += (int) $_imprs[ $ad_id ];
							if ( isset( $data['clicks'][ $ts ][ $ad_id ] ) ) {
								$group_clicks[ $ad_id ] += (int) $data['clicks'][ $ts ][ $ad_id ];
							}
						}
					} elseif ( $ts > $_to && ! empty( $group_imprs ) ) {
						foreach ( $adIDs as $ad_id ) {
							if ( ! isset( $group_imprs[ $ad_id ] ) ) {
								$group_imprs[ $ad_id ] = 0;
							}
							if ( ! isset( $group_clicks[ $ad_id ] ) ) {
								$group_clicks[ $ad_id ] = 0;
							}
							$group_imprs[ $ad_id ] += (int) $_imprs[ $ad_id ];
							if ( isset( $data['clicks'][ $ts ][ $ad_id ] ) ) {
								$group_clicks[ $ad_id ] += (int) $data['clicks'][ $ts ][ $ad_id ];
							}
						}
						$imprs[ $date ]  = $group_imprs;
						$clicks[ $date ] = $group_clicks;
						$group_clicks    = [];
						$group_imprs     = [];
					}
					break;
				case 'week':
					if ( $ts >= $_from && $ts <= $_to ) {
						$_date = date_create( self::split_date( $ts ) );
						if ( null === $date ) {
							$date = $_date->format( 'o-\WW' );
						}
						if ( $ts === $last_ts ) {
							foreach ( $adIDs as $ad_id ) {
								if ( ! isset( $group_imprs[ $ad_id ] ) ) {
									$group_imprs[ $ad_id ] = 0;
								}
								if ( ! isset( $group_clicks[ $ad_id ] ) ) {
									$group_clicks[ $ad_id ] = 0;
								}
								$group_imprs[ $ad_id ] += (int) $_imprs[ $ad_id ];
								if ( isset( $data['clicks'][ $ts ][ $ad_id ] ) ) {
									$group_clicks[ $ad_id ] += (int) $data['clicks'][ $ts ][ $ad_id ];
								}
							}
						}
						if ( $ts === $last_ts || $date !== $_date->format( 'o-\WW' ) || $ts > $_to ) {
							$imprs[ $date ]  = $group_imprs;
							$clicks[ $date ] = $group_clicks;

							$date         = $_date->format( 'o-\WW' );
							$group_clicks = [];
							$group_imprs  = [];
						}
						foreach ( $adIDs as $ad_id ) {
							if ( ! isset( $group_imprs[ $ad_id ] ) ) {
								$group_imprs[ $ad_id ] = 0;
							}
							if ( ! isset( $group_clicks[ $ad_id ] ) ) {
								$group_clicks[ $ad_id ] = 0;
							}
							$group_imprs[ $ad_id ] += (int) $_imprs[ $ad_id ];
							if ( isset( $data['clicks'][ $ts ][ $ad_id ] ) ) {
								$group_clicks[ $ad_id ] += (int) $data['clicks'][ $ts ][ $ad_id ];
							}
						}
					} elseif ( $ts > $_to && ! empty( $group_imprs ) ) {
						foreach ( $adIDs as $ad_id ) {
							if ( ! isset( $group_imprs[ $ad_id ] ) ) {
								$group_imprs[ $ad_id ] = 0;
							}
							if ( ! isset( $group_clicks[ $ad_id ] ) ) {
								$group_clicks[ $ad_id ] = 0;
							}
							$group_imprs[ $ad_id ] += (int) $_imprs[ $ad_id ];
							if ( isset( $data['clicks'][ $ts ][ $ad_id ] ) ) {
								$group_clicks[ $ad_id ] += (int) $data['clicks'][ $ts ][ $ad_id ];
							}
						}
						$imprs[ $date ]  = $group_imprs;
						$clicks[ $date ] = $group_clicks;
						$group_clicks    = [];
						$group_imprs     = [];
					}
					break;
				default: // day
					$date = self::split_date( $ts );
					if ( $ts >= $_from && $ts <= $_to ) {
						if ( ! isset( $imprs[ $date ] ) ) {
							$imprs[ $date ] = [];
						}
						if ( ! isset( $clicks[ $date ] ) ) {
							$clicks[ $date ] = [];
						}
						foreach ( $adIDs as $ad_id ) {
							if ( isset( $_imprs[ $ad_id ] ) ) {
								$imprs[ $date ][ $ad_id ] = $_imprs[ $ad_id ];
							}
							if ( isset( $data['clicks'][ $ts ][ $ad_id ] ) ) {
								$clicks[ $date ][ $ad_id ] = $data['clicks'][ $ts ][ $ad_id ];
							}
						}
					}
			}
		}

		if ( $imprs ) {
			// prepare jqplot and datatable variables that depend on date of first record ( if any record is found )
			$formatstring = '%b&nbsp;%#d';
			$firstdate    = key( $imprs );

			switch ( $groupby ) {
				case 'month':
					$formatstring = '%B';
					$firstdate    = '';
					break;
				case 'week':
					$formatstring = _x( 'from %b&nbsp;%#d', 'format for week group in stats table', 'advanced-ads-tracking' );
					$firstdate    = date( 'Y-m-d', strtotime( $firstdate . ' -1 week' ) );
					break;
				default: // day
					$firstdate = date( 'Y-m-d', strtotime( $firstdate . ' -1 day' ) );
			}
			$result['stats']['xAxisThickformat'] = $formatstring;
			$result['stats']['firstDate']        = $firstdate;
			$result['stats']['impr']             = $imprs;
			$result['stats']['click']            = $clicks;
			$result['stats']['periodEnd']        = $periodend;
			$result['stats']['periodStart']      = $periodstart;
			$result['stats']['ads']              = $data['ads'];
		}

		return $result;
	}

	/**
	 *  Load stats for a given period
	 */
	public function load_stats() {
		$nonce = ( isset( $_POST['nonce'] ) ) ? $_POST['nonce'] : '';
		if ( false === wp_verify_nonce( $nonce, 'advads-stats-page' ) ) {
			die;
		}
		$result = [ 'status' => false ];
		parse_str( $_POST['args'], $args );

		if ( ! empty( $args['period'] ) ) {
			$util             = Advanced_Ads_Tracking_Util::get_instance();
			$admin            = new Advanced_Ads_Tracking_Admin();
			$result['status'] = true;
			$result['stats']  = [];

			/**
			 *  Prepare all locale dependant and groupby dependant variables needed jqplot and datatable
			 */

			$dateFormat  = 'Y-m-d';
			$groupFormat = 'Y-m-d';

			// groupby-s formating
			$groupby  = $args['groupby'];
			$groupbys = [
				// group format, axis label, value conversion for graph
				'day'   => [ 'Y-m-d', __( 'day', 'advanced-ads-tracking' ), _x( 'Y-m-d', 'date format on stats page', 'advanced-ads-tracking' ) ],
				'week'  => [ 'o-\WW', __( 'week', 'advanced-ads-tracking' ), _x( 'Y-m-d', 'date format on stats page', 'advanced-ads-tracking' ) ],
				'month' => [ 'Y-m', __( 'month', 'advanced-ads-tracking' ), _x( 'Y-m', 'date format on stats page', 'advanced-ads-tracking' ) ],
			];

			if ( ! isset( $groupbys[ $groupby ] ) ) {
				$groupby = null;
			} else {
				$groupFormat = $groupbys[ $groupby ][0];
				$dateFormat  = $groupbys[ $groupby ][2];
				if ( $groupby === 'week' ) {
					// $groupFormat = 'Y-m-d';
				}
			}

			/**
			 *  Load result from DB
			 */
			$sql_args = [
				'period'      => $args['period'],
				'groupby'     => $args['groupby'],
				'ad_id'       => explode( '-', $_POST['ads'] ),
				'groupFormat' => $groupFormat,
			];

			if ( $args['period'] === 'custom' ) {
				$sql_args['from'] = $args['from'];
				$sql_args['to']   = $args['to'];
			}

			$impr   = $admin->load_stats( $sql_args, $util->get_impression_table() );
			$clicks = $admin->load_stats( $sql_args, $util->get_click_table() );
			if ( ! is_array( $clicks ) ) {
				$clicks = [];
			}
			$firstdate = '';

			if ( $impr || $clicks ) {
				// If clicks only are present, fill impressions with zero.
				foreach ( $clicks as $date => $_stats ) {
					foreach ( $_stats as $key => $value ) {
						if ( ! isset( $impr[ $date ][ $key ] ) ) {
							$impr[ $date ][ $key ] = 0;
						}
					}
				}

				$result['stats']['click'] = $clicks;
				$result['stats']['impr']  = $impr;

				$time  = time();
				$today = date_create( '@' . $time );

				/**
				 *  Get the real start of period, in case it is anterior to the first stat found in order to keep stats length in comparison
				 */
				switch ( $sql_args['period'] ) {
					case 'custom':
						$result['stats']['periodStart'] = $sql_args['from'];
						$result['stats']['periodEnd']   = $sql_args['to'];
						break;

					case 'today':
						$result['stats']['periodStart'] = get_date_from_gmt( $today->format( 'Y-m-d H:i:s' ), 'Y-m-d' );
						$result['stats']['periodEnd']   = $result['stats']['periodStart'];
						break;

					case 'yesterday':
						$yesterday                      = date_create( '@' . ( $time - ( 24 * 3600 ) ) );
						$result['stats']['periodStart'] = get_date_from_gmt( $yesterday->format( 'Y-m-d H:i:s' ), 'Y-m-d' );
						$result['stats']['periodEnd']   = $result['stats']['periodStart'];
						break;

					case 'lastmonth':
						/**
						 *  Get next month start without using DateInterval for PHP 5.2
						 */
						$year                           = (int) $today->format( 'Y' );
						$month                          = (int) $today->format( 'm' );
						$decr_year                      = ( 1 > $month - 1 ) ? 1 : 0;
						$last_month                     = ( 1 > $month - 1 ) ? 12 - $month - 1 : $month - 1;
						$days_count                     = (int) DateTimeImmutable::createFromFormat( 'Y-n-j', sprintf( '%d-%d-1', ( $year - $decr_year ), $last_month ) )->format( 't' );
						$result['stats']['periodStart'] = get_date_from_gmt( ( $year - $decr_year ) . '-' . $last_month . '-1 ' . $today->format( 'H:i:s' ), 'Y-m-d' );
						$result['stats']['periodEnd']   = get_date_from_gmt( ( $year - $decr_year ) . '-' . $last_month . '-' . $days_count . ' ' . $today->format( 'H:i:s' ), 'Y-m-d' );
						break;

					case 'thismonth':
						$result['stats']['periodStart'] = get_date_from_gmt( $today->format( 'Y-m-1 H:i:s' ), 'Y-m-d' );
						/**
						 *  Get next month start without using DateInterval for PHP 5.2
						 */
						$days_count                   = (int) $today->format( 't' );
						$result['stats']['periodEnd'] = get_date_from_gmt( $today->format( 'Y-m-' . $days_count . ' H:i:s' ), 'Y-m-d' );
						break;

					case 'thisyear':
						$result['stats']['periodStart'] = get_date_from_gmt( $today->format( 'Y-1-1 H:i:s' ), 'Y-m-d' );
						$result['stats']['periodEnd']   = get_date_from_gmt( $today->format( 'Y-12-31 H:i:s' ), 'Y-m-d' );
						break;

					case 'lastyear':
						$result['stats']['periodEnd']   = get_date_from_gmt( ( (int) $today->format( 'Y' ) - 1 ) . $today->format( '-12-31 H:i:s' ), 'Y-m-d' );
						$result['stats']['periodStart'] = get_date_from_gmt( ( (int) $today->format( 'Y' ) - 1 ) . $today->format( '-01-01 H:i:s' ), 'Y-m-d' );
						break;

					default: // last 7 days
						$last7days                      = $time - ( 7 * 24 * 3600 );
						$D_last7days                    = date_create( '@' . $last7days );
						$yesterday                      = date_create( '@' . ( $time - ( 24 * 3600 ) ) );
						$result['stats']['periodStart'] = get_date_from_gmt( $D_last7days->format( 'Y-m-d H:i:s' ), 'Y-m-d' );
						$result['stats']['periodEnd']   = get_date_from_gmt( $yesterday->format( 'Y-m-d H:i:s' ), 'Y-m-d' );
				}
			}
			/**
			 *  Prepare jqplot and datatable variables that depend on date of first record ( if any record is found )
			 */
			if ( $impr ) {
				$formatstring = '%b&nbsp;%#d';
				reset( $impr );
				$firstdate = key( $impr );
				switch ( $args['groupby'] ) {
					case 'week':
						$formatstring = _x( 'from %b&nbsp;%#d', 'format for week group in stats table', 'advanced-ads-tracking' );
						$firstdate    = date( 'Y-m-d', strtotime( $firstdate . ' -1 week' ) );
						break;
					case 'month':
						$formatstring = '%B';
						$firstdate    = '';
						break;
					default:
						$firstdate = date( 'Y-m-d', strtotime( $firstdate . ' -1 day' ) );
				}

				$result['stats']['xAxisThickformat'] = $formatstring;
				$result['stats']['firstDate']        = $firstdate;
			}
		}
		if ( ! empty( $firstdate ) && (int) str_replace( '-', '', $firstdate ) < 20100101 ) {
			// an invalid date has been found in the records
			$result = [
				'status' => false,
				'msg'    => 'invalid-record',
			];
		}
		header( 'Content-Type: application/json' );
		echo json_encode( $result );
		die;
	}

	/**
	 *  Send immediately an email report
	 */
	public function immedate_report() {
		$nonce = ( isset( $_POST['nonce'] ) ) ? $_POST['nonce'] : '';
		if ( false === wp_verify_nonce( $nonce, 'advads-tracking-public-stats' ) ) {
			die;
		}
		$result = [
			'status' => false,
		];
		$result = Advanced_Ads_Tracking_Util::get_instance()->send_email_report();
		header( 'Content-Type: application/json' );
		echo json_encode( $result );
		die;
	}

	/**
	 *  Check if a slug is taken
	 *
	 * @since N/A
	 */
	public function check_slug() {
		$nonce = ( isset( $_POST['nonce'] ) ) ? $_POST['nonce'] : '';
		if ( false === wp_verify_nonce( $nonce, 'advads-tracking-public-stats' ) ) {
			die;
		}
		$result = [
			'status' => false,
		];
		$title  = ( isset( $_POST['title'] ) && ! empty( $_POST['title'] ) ) ? stripslashes( $_POST['title'] ) : false;

		if ( $title ) {
			$to_slug = sanitize_title( $title );

			$category = get_term_by( 'slug', $to_slug, 'category' );
			$tag      = get_term_by( 'slug', $to_slug, 'post_tag' );
			$link     = get_term_by( 'slug', $to_slug, 'link_category' );
			$posts    = new WP_Query( [
				'post_type' => 'any',
				'name'      => $to_slug,
			] );

			if ( $posts->have_posts() ) {
				$result['msg'] = __( 'This base name collides with an existing WordPress content (blog post, page or any public custom content)', 'advanced-ads-tracking' );
			} elseif ( false !== $link ) {
				$result['msg'] = __( 'This base name collides with an existing link category', 'advanced-ads-tracking' );
			} elseif ( false !== $tag ) {
				$result['msg'] = __( 'This base name collides with an existing blog post tag', 'advanced-ads-tracking' );
			} elseif ( false !== $category ) {
				$result['msg'] = __( 'This base name collides with an existing blog post category', 'advanced-ads-tracking' );
			} else {
				// all clear
				$result['status'] = true;
			}
			$result['slug']  = $to_slug;
			$result['title'] = $title;
		}

		header( 'Content-Type:  application/json' );
		echo json_encode( $result );
		die;
	}

	/**
	 * Track impressions.
	 */
	public function track() {
		// phpcs:disable WordPress.Security.NonceVerification
		$start_time = microtime( true );

		// do not stop when user ended the connection
		ignore_user_abort( true );

		// do nothing if called without payload
		if ( ! is_array( $_REQUEST['ads'] ) ) {
			die( 'nothing to track' );
		}

		$ad_ids = array_filter( array_map( function( $value ) { return (int) $value; }, $_REQUEST['ads'] ) );

		if ( empty( $_REQUEST['ads'] ) ) {
			die( 'nothing to track' );
		}

		$util   = Advanced_Ads_Tracking_Util::get_instance();
		$action = sanitize_text_field( $_REQUEST['action'] );

		if ( $action === self::TRACK_CLICK ) {
			foreach ( $ad_ids as $ad_id ) {
				$util->track_click( $ad_id, $start_time );
			}

			return;
		}

		if ( $action === self::TRACK_IMPRESSION ) {
			$util->track_impressions( $ad_ids, $start_time );
		}
		// phpcs:enable WordPress.Security.NonceVerification
	}
}
