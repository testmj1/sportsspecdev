var AdvAdsTrackingUtils = {
	/**
	 * Check if there are ads.
	 *
	 * @param {object} data
	 * @return {boolean}
	 */
	hasAd: function ( data ) {
		for ( var i in data ) {
			if ( Array.isArray( data[i] ) && data[i].length ) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Custom implementation of jQuery.param.
	 *
	 * @param {object} data
	 * @return {string}
	 */
	param: function ( data ) {
		return Object.keys( data ).map(
			function ( k ) {
				if ( Array.isArray( data[k] ) ) {
					return Object.keys( data[k] ).map(
						function ( m ) {
							return encodeURIComponent( k ) + '[]=' + encodeURIComponent( data[k][m] );
						}
					).join( '&' );
				}
				return encodeURIComponent( k ) + '=' + encodeURIComponent( data[k] );
			}
		).join( '&' ).replace( /%20/g, '+' );
	},

	/**
	 * Concat two arrays.
	 *
	 * @return {{}}
	 */
	concat: function () {
		var args   = Array.prototype.slice.call( arguments ),
			result = {};

		for ( var i in args ) {
			for ( var j in args[i] ) {
				if ( 'undefined' == typeof result[j] ) {
					result[j] = args[i][j];
				} else {
					if ( 'function' == typeof result[j].concat ) {
						result[j] = result[j].concat( args[i][j] );
					}
				}
			}
		}
		return result;
	},

	/**
	 * Get the ads for the gived blog id.
	 *
	 * @param {object} ads
	 * @param {integer} bid
	 * @return {object}
	 */
	adsByBlog: function ( ads, bid ) {
		var result = {};
		if ( typeof ads[bid] !== 'undefined' ) {
			result[bid] = ads[bid];
		}
		return result;
	},

	/**
	 * Add the frontend prefix to requested data-attributes.
	 *
	 * @param {string} name
	 * @returns {string}
	 */
	getPrefixedAttribute: function ( name ) {
		return '' + window.advadsTracking.frontendPrefix + name;
	},

	/**
	 * Add the frontend prefix to requested attributes from dataset.
	 * These need to be camelCased.
	 *
	 * @param {string} name
	 * @returns {string}
	 */
	getPrefixedDataSetAttribute: function ( name ) {
		return this.getPrefixedAttribute( name )
				   .toLowerCase()
				   .replace( 'data-', '' )
				   .replace( /-([a-z]?)/g, ( m, g ) => g.toUpperCase() );
	},

	/**
	 * Replacement for jQuery.extend.
	 *
	 * @return {object}
	 */
	extend: function () {
		var extended = {};

		for ( var key in arguments ) {
			var argument = arguments[key];
			for ( var prop in argument ) {
				if ( Object.prototype.hasOwnProperty.call( argument, prop ) ) {
					extended[prop] = argument[prop];
				}
			}
		}

		return extended;
	},

	/**
	 * InArray polyfill.
	 *
	 * @param {(string|int)} needle
	 * @param {Array} haystack
	 * @return {boolean}
	 */
	inArray: function ( needle, haystack ) {
		return haystack.indexOf( needle ) > - 1;
	},

	/**
	 * Find parent element with specific classname
	 *
	 * @param {Element} el
	 * @param {string} className
	 */
	findParentByClassName: function ( el, className ) {
		while ( ( el = el.parentElement ) && ! el.classList.contains( className ) ) {

		}
		return el;
	},

	/**
	 * Create current timestamp
	 *
	 * @return {number}
	 */
	getTimestamp: function () {
		if ( ! Date.now ) {
			Date.now = function () {
				return new Date().getTime();
			};
		}
		return Math.floor( Date.now() / 1000 );
	},

	/**
	 * Extend array with unique function.
	 *
	 * @param value
	 * @param {number} index
	 * @param {Array} self
	 * @returns {*[]} unique array.
	 */
	arrayUnique: function ( value, index, self ) {
		return self.indexOf( value ) === index;
	},

	/**
	 * Check if the current blog uses GA tracking (setting or parallel) and UID is set.
	 *
	 * @param {number} bid
	 * @return {boolean}
	 */
	blogUseGA: function ( bid ) {
		// phpcs:ignore WordPress.WhiteSpace.OperatorSpacing
		return ( advads_tracking_methods[bid] === 'ga' || advads_tracking_parallel[bid] ) && !! advads_gatracking_uids[bid];
	},

	/**
	 * POST XHR, replaces jQuery.post
	 *
	 * @param {string} url
	 * @param {(object|string)} data
	 * @param {boolean} [async=true]
	 * @return {Promise}
	 */
	post: function ( url, data, async ) {
		var xhr     = new XMLHttpRequest();
		xhr.timeout = 5000;

		// Return it as a Promise
		return new Promise( function ( resolve, reject ) {
			xhr.onreadystatechange = function () {
				// Wait for request to complete.
				if ( xhr.readyState !== XMLHttpRequest.DONE ) {
					return;
				}

				// Resolve if 2xx status, reject otherwise.
				if ( xhr.status === 0 || ( xhr.status >= 200 && xhr.status < 300 ) ) {
					resolve( xhr );
				} else {
					reject( {
						status:     xhr.status,
						statusText: xhr.statusText
					} );
				}
			};

			xhr.open( 'POST', url, async || true );
			xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
			xhr.send( typeof data === 'string' ? data : AdvAdsTrackingUtils.param( data ) );
		} );
	}
};
