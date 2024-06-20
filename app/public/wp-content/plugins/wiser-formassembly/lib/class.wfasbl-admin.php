<?php

class WFASBL_Admin {

    private static $initiated = false;
	
	public function init( ) {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks( ) {
		self::$initiated = true;
	}


}