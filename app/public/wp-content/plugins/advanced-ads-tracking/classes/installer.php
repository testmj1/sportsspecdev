<?php

/**
 * Class Advanced_Ads_Tracking_Installer
 *
 * Install the custom ajax-handler into WP_CONTENT and add database information
 */
class Advanced_Ads_Tracking_Installer {

	const SOURCE_HANDLER = 'nowp-ajax-handler.php';
	const DEST_HANDLER   = 'ajax-handler.php';
	const VERSION_OPTION = 'ajax_dropin_version';

	/**
	 * The destination to write the handler to
	 *
	 * @var string
	 */
	private $dest_file;

	/**
	 * The source file for the handler
	 *
	 * @var string
	 */
	private $source_file;

	/**
	 * Last written drop-in version
	 *
	 * @var string
	 */
	private $ajax_dropin_version = '2.0.0-alpha.3';

	/**
	 * WordPress database abstraction
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * Instance of the tracking plugin
	 *
	 * @var Advanced_Ads_Tracking_Plugin
	 */
	private $plugin;

	/**
	 * The destination directory for the ajax-handler.php file.
	 *
	 * @var string
	 */
	private $dest_dir;

	/**
	 * The destination URL for the ajax-handler.php file.
	 *
	 * @var string
	 */
	private $handler_url;

	/**
	 * Advanced_Ads_Tracking_Installer constructor.
	 */
	public function __construct() {
		$this->db          = $GLOBALS['wpdb'];
		$this->source_file = trailingslashit( WP_PLUGIN_DIR . '/' . AAT_BASE_DIR ) . self::SOURCE_HANDLER;
		$this->plugin      = Advanced_Ads_Tracking_Plugin::get_instance();

		/**
		 * Allow filtering of the destination directory for the ajax handler file.
		 *
		 * @param string WP_CONTENT_DIR by default.
		 */
		$this->dest_dir  = trailingslashit( apply_filters( 'advanced-ads-tracking-ajax-dropin-path', WP_CONTENT_DIR ) );
		$this->dest_file = $this->dest_dir . self::DEST_HANDLER;

		/**
		 * If the destination dir gets filtered, the resulting URL must also be filtered.
		 *
		 * @param string content_url() by default.
		 */
		$this->handler_url = trailingslashit( apply_filters( 'advanced-ads-tracking-ajax-dropin-url', content_url() ) ) . self::DEST_HANDLER;

		// delete ajax handler if not needed. Keep it on multisite installations. Only try this in wp-admin.
		if ( is_admin() && ( $this->plugin->is_legacy_ajax() || ( $this->plugin->get_tracking_method() === 'ga' && ! is_multisite() ) ) ) {
			$this->uninstall();
		}
	}

	/**
	 * Trigger the ajax-handler.php installer to account for changed options.
	 */
	public static function trigger_installer_update() {
		$plugin  = Advanced_Ads_Tracking_Plugin::get_instance();
		$options = $plugin->options();
		if ( array_key_exists( self::VERSION_OPTION, $options ) ) {
			unset( $options[ self::VERSION_OPTION ] );
			$plugin->update_options( $options );
		}
	}

	/**
	 * Check if AJAX handler exists.
	 *
	 * @return bool
	 */
	public function handler_exists() {
		try {
			return $this->get_filesystem()->exists( $this->dest_file );
		} catch ( RuntimeException $e ) {
			return file_exists( $this->dest_file );
		}
	}

	/**
	 * Print error message from the installer
	 *
	 * @param string $message The error message string.
	 */
	public function installer_notice( $message ) {
		add_action(
			'advanced-ads-notices',
			function() use ( $message ) {
				?>
				<div class="notice notice-error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
			}
		);
	}

	/**
	 * Try to write the ajax handler to wp-content dir
	 *
	 * @return bool Whether the dropin file was written.
	 */
	private function write_handler() {
		if ( $this->plugin->is_legacy_ajax() ) {
			return false;
		}

		try {
			$filesystem = $this->get_filesystem();
		} catch ( RuntimeException $e ) {
			return false;
		}

		// check if the handler either exists and is writable or if parent dir is writable
		if ( ! $filesystem->is_writable( $this->dest_dir ) || ( $this->handler_exists() && ! $filesystem->is_writable( $this->dest_file ) ) ) {
			// phpcs:disable WordPress.Security.EscapeOutput
			$message = __( 'The Advanced Ads AJAX tracking drop-in could not be written.' );
			/* translators: 1: WP_CONTENT_DIR 2: <code>wp-config.php</code> 3: <code>define( 'ADVANCED_ADS_TRACKING_LEGACY_AJAX', true )</code> */
			$message .= '<br>' . sprintf( __( 'Please make sure the directory %1$s is writable or add the following to your %2$s: %3$s.', 'advanced-ads-tracking' ), '<code>' . $this->dest_dir . '</code>', '<code>wp-config.php</code>', '<code>define( \'ADVANCED_ADS_TRACKING_LEGACY_AJAX\', true )</code>' );
			/* translators: %s is <code>wp-admin/admin-ajax.php</code> */
			$message .= '<br>' . sprintf( 'Falling back to %s', '<code>wp-admin/admin-ajax.php</code>' );
			// phpcs:enable WordPress.Security.EscapeOutput
			$this->installer_notice( $message );

			return false;
		}

		$written = $filesystem->put_contents(
			$this->dest_file,
			vsprintf(
				$filesystem->get_contents( $this->source_file ),
				$this->gather_variables()
			)
		);
		if ( ! $written ) {
			return $written;
		}

		return $this->check_integrity();
	}

	/**
	 * Install the custom ajax handler if environment permits it.
	 * Override if installed version is too old.
	 */
	public function install() {
		if ( ! is_admin() || wp_doing_ajax() || ! class_exists( 'Advanced_Ads', false ) ) {
			return;
		}

		$options = $this->plugin->options();

		if (
			( $this->plugin->get_tracking_method() !== 'ga' || is_multisite() )
			&& ! $this->plugin->is_legacy_ajax()
			&& ( ! $this->handler_exists() || $this->needs_update( $options ) )
		) {
			if ( $this->write_handler() && $this->needs_update( $options ) ) {
				$options[ self::VERSION_OPTION ] = $this->generate_version_hash();
				$this->plugin->update_options( $options );
			}
		}
	}

	/**
	 * Remove the installed ajax handler.
	 * Return early if the file does not exist.
	 */
	public function uninstall() {
		if ( ! $this->handler_exists() ) {
			return;
		}
		try {
			$this->get_filesystem()->delete( $this->dest_file );
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		} catch ( RuntimeException $e ) {
			// if we don't have a filesystem and can't delete the file, just ignore this.
		}
	}

	/**
	 * Check if the installed ajax handler needs an update.
	 *
	 * @param array $options Options for add-on.
	 *
	 * @return bool
	 */
	private function needs_update( $options ) {
		return ! isset( $options[ self::VERSION_OPTION ] ) || $options[ self::VERSION_OPTION ] !== $this->generate_version_hash();
	}

	/**
	 * Add the full path to the Debugger Class.
	 *
	 * @return string
	 */
	private function get_debugger_file() {
		try {
			$reflection = new ReflectionClass( 'Advanced_Ads_Tracking_Debugger' );

			return $reflection->getFileName();
		} catch ( ReflectionException $e ) {
			return '';
		}
	}

	/**
	 * Get the bots from the main plugin and this add-on.
	 *
	 * @return string regex to match bots, empty if tracking bots.
	 */
	private function get_bots() {
		$options = $this->plugin->options();
		if ( array_key_exists( 'track-bots', $options ) && $options['track-bots'] ) {
			return '';
		}
		$plugin = Advanced_Ads::get_instance();
		$bots   = [];
		if ( method_exists( $plugin, 'get_bots' ) ) {
			$bots = $plugin->get_bots();
		}

		$bots = Advanced_Ads_Tracking_Util::get_instance()->add_bots_triggering_ajax( $bots );
		$bots = implode( '|', $bots );
		// Make sure delimiters in regex are escaped.
		$bots = preg_replace( '/(.*?)(?<!\\\)' . preg_quote( '/', '/' ) . '(.*?)/', '$1\\/$2', $bots );

		return $bots;
	}

	/**
	 * Generate a version hash to check if the drop-in needs to be rewritten.
	 *
	 * @return string
	 */
	private function generate_version_hash() {
		static $hash;
		if ( ! is_null( $hash ) ) {
			return $hash;
		}

		$hash = wp_hash( implode( '', $this->gather_variables() ) . $this->ajax_dropin_version, self::VERSION_OPTION );

		return $hash;
	}

	/**
	 * Gather the variables needed for writing the drop-in
	 *
	 * @return array
	 */
	private function gather_variables() {
		static $vars;
		if ( ! is_null( $vars ) ) {
			return $vars;
		}

		// see wp-includes/wp-db.php for documentation.
		list( $host, $port, $socket, $is_ipv6 ) = $this->db->parse_db_host( $this->db->dbhost );
		if ( $is_ipv6 && extension_loaded( 'mysqlnd' ) ) {
			$host = "[$host]";
		}

		$vars = [
			'db_host'       => $host, // 1
			'db_user'       => $this->db->dbuser, // 2
			'db_password'   => $this->db->dbpassword, // 3
			'db_name'       => $this->db->dbname, // 4
			'db_port'       => $port, // 5
			'db_socket'     => $socket, // 6
			'table_prefix'  => $this->db->get_blog_prefix( 1 ), // 7
			'debug_file'    => Advanced_Ads_Tracking_Debugger::get_debug_file_path(), // 8
			'debug_enabled' => Advanced_Ads_Tracking_Debugger::debugging_enabled() ? 'true' : 'false', // 9
			'debug_id'      => get_option( Advanced_Ads_Tracking_Debugger::DEBUG_OPT, [ 'id' => 0 ] )['id'], // 10
			'debug_handler' => $this->get_debugger_file(), // 11
			'bots'          => $this->get_bots(), // 12
			'timezone'      => $this->get_time_zone(), // 13
		];

		return $vars;
	}

	/**
	 * Make sure that the custom ajax handler does not expose database credentials to the public.
	 *
	 * @return bool
	 */
	private function check_integrity() {
		$response = wp_remote_post( $this->handler_url );
		if ( is_wp_error( $response ) ) {
			$this->uninstall();

			return false;
		}
		/** @var WP_HTTP_Requests_Response $response */
		$response = $response['http_response'];

		if ( $response->get_response_object()->body === 'no ads' ) {
			return true;
		}
		// if the response is anything other than 'no ads', there's an issue on the website.
		$message = esc_html__( 'The Advanced Ads AJAX tracking drop-in created unexpected output and has been removed.', 'advanced-ads-tracking' );
		$message .= sprintf(
			'<br><label for="advanced-ads-unexpected-output">%s</label><br><textarea id="advanced-ads-unexpected-output" readonly  style="max-width: 100%%" rows="8" cols="120" onclick="this.select()">%s</textarea>',
			__( 'Please send us the following output:', 'advanced-ads-tracking' ),
			$response->get_response_object()->body
		);
		$message .= '<br>' . sprintf(
			/* translators: %s is <code>wp-admin/admin-ajax.php</code> */
				esc_html__( 'Falling back to %s.', 'advanced-ads-tracking' ),
				'<code>wp-admin/admin-ajax.php</code>'
			);
		/* translators: 1: <code>wp-config.php</code> 2: <code>define( 'ADVANCED_ADS_TRACKING_LEGACY_AJAX', true )</code> */
		$message .= '<br>' . sprintf( esc_html__( 'To make this change permanent, please add the following to your %1$s: %2$s.', 'advanced-ads-tracking' ), '<code>wp-config.php</code>', '<code>define( \'ADVANCED_ADS_TRACKING_LEGACY_AJAX\', true )</code>' );
		/* translators: 1: is <code>ajax-handler.php</code>, 2: is the opening link to the documentation, 3: closing link tag */
		$message .= '<br>' . sprintf( esc_html__( 'You can find more information about the %1$s and fallback method %2$sin the documentation%3$s.', 'advanced-ads-tracking' ), '<code>ajax-handler.php</code>', '<a href="https://wpadvancedads.com/manual/tracking-methods/?utm_source=advanced-ads&utm_medium=link&utm_campaign=tracking-ajax-handler#Frontend" target="_blank" rel="noopener">', '</a>' );

		$this->installer_notice( $message );
		$this->uninstall();

		return false;
	}

	/**
	 * Get a timezone string (or the older UTC offset).
	 *
	 * @return string
	 */
	private function get_time_zone() {
		// check for modern timezone string
		$timezone = get_option( 'timezone_string' );
		if ( ! empty( $timezone ) ) {
			return $timezone;
		}
		// check for UTC offset
		$timezone = get_option( 'gmt_offset' );
		if ( ! empty( $timezone ) ) {
			return $timezone;
		}

		return 'UTC';
	}

	/**
	 * Check for the WP_Filesystem and error if no credentials.
	 *
	 * @return WP_Filesystem_Base
	 * @throws \RuntimeException If we can't find a Filesystem (e.g. not in admin), throw a RuntimeException.
	 */
	private function get_filesystem() {
		static $filesystem;
		if ( $filesystem !== null ) {
			return $filesystem;
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			throw new \RuntimeException( 'Can\'t instantiate WP_Filesystem' );
		}

		// try setting up the wp_filesystem global.
		WP_Filesystem();

		$filesystem = $GLOBALS['wp_filesystem'];
		if ( $filesystem === null || is_wp_error( $filesystem->errors ) ) {
			throw new \RuntimeException( 'Can\'t instantiate WP_Filesystem' );
		}

		return $filesystem;
	}

	/**
	 * Get the filtered URL for the ajax-handler.php.
	 *
	 * @return string the handler URL.
	 */
	public function get_handler_url() {
		return $this->handler_url;
	}
}
