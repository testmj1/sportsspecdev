/* IE 11 add foreach fix */
if ( window.NodeList && ! NodeList.prototype.forEach ) {
	NodeList.prototype.forEach = Array.prototype.forEach;
}

/**
 * Click tracker class.
 */
var AdvAdsClickTracker = {
	wrappers:      [],
	overTarget:    false,
	currentTarget: false,
	lastClick:     [],
	elements:      [
		'iframe',
		'a.adv-link',
		'button.adv-link'
	],
	// Predefine google adsense iframes.
	targets:       [
		'aswift_0',
		'aswift_1',
		'aswift_3',
		'aswift_4',
		'aswift_5',
		'aswift_6',
		'aswift_7',
		'aswift_8',
		'aswift_9'
	],

	/**
	 * Find targets from selector array and save them into global targets array.
	 */
	findTargets: function () {
		// Loop through wrappers array and search wrapper elements.
		AdvAdsClickTracker.wrappers.forEach( function ( wrapper ) {
			var wrapperElements = document.querySelectorAll( wrapper );

			// Loop through wrapper elements and find targets.
			wrapperElements.forEach( function ( wrapperElement ) {
				// If wrapper is found search for defined child elements.
				if ( wrapperElement !== null ) {
					AdvAdsClickTracker.elements.forEach( function ( element ) {
						// Merge arrays and push detected targets into the global array.
						Array.prototype.push.apply(
							AdvAdsClickTracker.targets,
							// Convert dom nodelist into array.
							Array.prototype.slice.call( wrapperElement.querySelectorAll( element ) )
						);
					} );
				}
			} );
		} );
		AdvAdsClickTracker.targets = AdvAdsClickTracker.targets.filter( AdvAdsTrackingUtils.arrayUnique );

		this.processTargets();
	},

	/**
	 * Initiate targets.
	 */
	processTargets: function () {
		AdvAdsClickTracker.targets.forEach( function ( target ) {
			AdvAdsClickTracker.registerTargetHandlers( target );
		} );
	},

	/**
	 * Register mouseover and mouseout events.
	 *
	 * @param {Element} target
	 */
	registerTargetHandlers: function ( target ) {
		target.onmouseover = this.mouseOver;
		target.onmouseout  = this.mouseOut;
		// Register click on ad with ie fix.
		if ( typeof window.attachEvent !== 'undefined' ) {
			top.attachEvent( 'onblur', this.adClick );
		} else if ( typeof window.addEventListener !== 'undefined' ) {
			// Register click on ad for all other browsers.
			top.addEventListener( 'blur', this.adClick, false );
		}
	},

	/**
	 * Register click handlers for wrapper elements.
	 */
	registerWrapperHandlers: function () {
		var touchmoved;

		// Add auxclick event for middle mouse button clicks.
		['click', 'touchend', 'auxclick'].forEach(
			function ( event ) {
				document.addEventListener( event, function ( e ) {
					// Stop if click is not from left or middle moue button.
					if ( ( e.type === 'auxclick' && ( e.which !== 2 && e.which !== 1 ) ) || touchmoved ) {
						return;
					}

					// Check if clicked element is clickable.
					var clickable = false;
					if ( ['a', 'iframe', 'button'].indexOf( e.target.localName ) !== - 1 ) {
						clickable = true;
					}
					// Loop parent nodes from the target to the delegation node.
					for ( var target = e.target; target && target !== this; target = target.parentNode ) {
						if ( target.parentNode !== null && ! clickable && ( ['a', 'iframe', 'button'].indexOf( target.parentNode.localName ) !== - 1 ) ) {
							clickable = true;
						}
						var match = false;
						// Check if clicked element is in wrappers array.
						AdvAdsClickTracker.wrappers.forEach(
							function ( className ) {
								if ( target.matches ? target.matches( className ) : target.msMatchesSelector( className ) ) {
									// Disable tracking on notrack links and on wrappers without clickable element
									if ( ! e.target.classList.contains( 'notrack' ) && ( clickable || target.querySelector( 'iframe' ) !== null ) ) {
										match = true;
									}
								}
							}
						);
						// If match there is an ad click.
						if ( match ) {
							// Disable clicks if current element equals the wrapper element.
							if ( this.currentTarget === e.target ) {
								return;
							}
							AdvAdsClickTracker.ajaxSend( e.target );
							break;
						}
					}
				}, {capture: true} );
			}
		);

		// Detect swipe and click on mobile devices.
		document.addEventListener( 'touchmove', function ( e ) {
			touchmoved = true;
		}, false );
		document.addEventListener( 'touchstart', function ( e ) {
			touchmoved = false;
		}, false );
	},

	/**
	 * Click on ad action.
	 */
	adClick: function () {
		// If mouse is over target there is an ad click.
		if ( AdvAdsClickTracker.overTarget ) {
			AdvAdsClickTracker.ajaxSend( AdvAdsClickTracker.currentTarget );
			top.focus();
		}

	},

	/**
	 * Handle if mouse leaves ad.
	 */
	mouseOver: function () {
		AdvAdsClickTracker.overTarget    = true;
		AdvAdsClickTracker.currentTarget = this;
	},

	/**
	 * Handle if mouse is over ad.
	 */
	mouseOut: function () {
		AdvAdsClickTracker.overTarget    = false;
		AdvAdsClickTracker.currentTarget = false;
		top.focus();
	},

	/**
	 * Send message to ajax handler
	 */
	ajaxSend: function ( element ) {
		var dataId       = element.getAttribute( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackid' ) );
		var bId          = element.getAttribute( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackbid' ) );
		var redirectLink = element.getAttribute( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'redirect' ) );
		if ( dataId === null ) {
			var parent   = AdvAdsTrackingUtils.findParentByClassName( element, [advadsTracking.targetClass] );
			dataId       = parent.getAttribute( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackid' ) );
			bId          = parent.getAttribute( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackbid' ) );
			redirectLink = parent.getAttribute( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'redirect' ) );
		}

		var ajaxHandler = advads_tracking_urls[bId];
		var postData    = {
			action:   window.advadsTracking.clickActionName,
			referrer: window.location.pathname + window.location.search,
			type:     'ajax',
			ads:      [dataId],
			bid:      bId
		};

		// prevent simultaneous clicks on wrapper and element as well as to fast clicks in a row
		if ( 10 > ( AdvAdsTrackingUtils.getTimestamp() - this.lastClick[dataId] ) ) {
			return false;
		}

		// If google analytics or parallel tracking is activated, track click.
		if ( AdvAdsTrackingUtils.blogUseGA( bId ) ) {
			var tracker = advancedAdsGAInstances.getInstance( bId );
			tracker.trackClick( dataId, false, false, false );
			this.lastClick[dataId] = AdvAdsTrackingUtils.getTimestamp();
			if ( ! advads_tracking_parallel[bId] ) {
				return;
			}
		}

		// don't use frontend tracking on redirect links
		if ( redirectLink ) {
			return;
		}

		// use beacon api to send the request to the webserver
		if ( navigator.sendBeacon && ajaxHandler.indexOf( 'admin-ajax.php' ) === - 1 ) {
			// Deep copy of data object.
			var beaconData  = JSON.parse( JSON.stringify( postData ) );
			beaconData.type = 'beacon';
			beaconData      = new Blob( [JSON.stringify( beaconData )], {type: 'application/json; charset=UTF-8'} );
			navigator.sendBeacon( ajaxHandler, beaconData );
		} else {
			// use synchronous ajax call
			AdvAdsTrackingUtils.post( ajaxHandler, postData, false );
		}
		this.lastClick[dataId] = AdvAdsTrackingUtils.getTimestamp();
	}
};

/* Define Click Tracking class  */
advanced_ads_ready( function () {
	// We can push other custom classes via variables in this array for custom user classes or changeable classes that should be watched
	AdvAdsClickTracker.wrappers = ( advadsTracking.targetClass !== null && advadsTracking.targetClass !== '' )
		? Array( '.' + advadsTracking.targetClass, '.adsbygoogle' )
		: Array( ' ', '.adsbygoogle' );

	// If back button is pressed blur event only works after reloading the page.
	window.onpageshow = function ( event ) {
		if ( event && event.persisted ) {
			window.location.reload();
		}
	};

	// Search for targets after some delay.
	setTimeout( function () {
		AdvAdsClickTracker.findTargets();
	}, 1500 );

	// Register handlers for wrappers.
	AdvAdsClickTracker.registerWrapperHandlers();
}, 'interactive' );
