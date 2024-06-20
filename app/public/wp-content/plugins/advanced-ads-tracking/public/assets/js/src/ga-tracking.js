function AdvAdsGATracker( blogId, propertyIds ) {
	this.blogId             = blogId;
	this.propertyIds        = typeof propertyIds === 'string' ? [propertyIds] : propertyIds;
	this.normalTrackingDone = false;
	this.clickTimer         = null;

	const self = this;

	this.getQueryString = function ( URL ) {
		var anchorElement  = document.createElement( 'a' );
		anchorElement.href = URL;
		var queryString    = anchorElement.search;
		if ( queryString.length ) {
			queryString = queryString.substr( 1 );
			queryString = queryString.split( '&' );
			if ( queryString.length ) {
				var results = {};
				for ( var i in queryString ) {
					var exp         = queryString[i].split( '=' );
					results[exp[0]] = exp[1];
				}
				return results;
			}
		}

		return [];
	};

	this.appendQueryString = function ( url, queryString ) {
		for ( var i in queryString ) {
			if ( - 1 !== url.indexOf( '?' ) ) {
				url += '&' + i + '=' + queryString[i];
			} else {
				url += '?' + i + '=' + queryString[i];
			}
		}
		return url;
	};

	this.trackImpressions = function ( delayed ) {
		if ( typeof delayed === 'undefined' ) {
			delayed = false;
		}
		var trackedAds = [];

		// Normal (not deferred) tracking.
		if (
			! this.normalTrackingDone
			&& AdvAdsTrackingUtils.hasAd( AdvAdsTrackingUtils.adsByBlog( advads_tracking_ads, self.blogId ) )
		) {
			trackedAds = trackedAds.concat( advads_tracking_ads[self.blogId] );
		}

		if ( advads_tracking_methods[self.blogId] === 'frontend' ) {
			// means parallel tracking. ads ID-s will be sent at the same time as the normal ajax tracking call
			trackedAds = [];
		}

		if ( delayed ) {
			// delayed ads.
			if (
				typeof advadsGATracking.delayedAds !== 'undefined'
				&& AdvAdsTrackingUtils.hasAd( AdvAdsTrackingUtils.adsByBlog( advadsGATracking.delayedAds, self.blogId ) )
			) {
				trackedAds                               = trackedAds.concat( advadsGATracking.delayedAds[self.blogId] );
				advadsGATracking.delayedAds[self.blogId] = [];
			}
		} else {
			// deferred ads.
			if (
				typeof advadsGATracking.deferedAds !== 'undefined'
				&& AdvAdsTrackingUtils.hasAd( AdvAdsTrackingUtils.adsByBlog( advadsGATracking.deferedAds, self.blogId ) )
			) {
				trackedAds                               = trackedAds.concat( advadsGATracking.deferedAds[self.blogId] );
				advadsGATracking.deferedAds[self.blogId] = [];
			}
		}

		if ( typeof advads !== 'undefined' && typeof advads.privacy.is_ad_decoded !== 'undefined' ) {
			// remove ads that have not been decoded.
			trackedAds = trackedAds.filter( advads.privacy.is_ad_decoded );
		}

		if ( ! trackedAds.length ) {
			// no ads to track
			return;
		}

		for ( var i in trackedAds ) {
			if (
				typeof advads_gatracking_allads[self.blogId][trackedAds[i]] !== 'undefined'
				&& advads_gatracking_allads[self.blogId][trackedAds[i]]['impression']
			) {
				self.sendEvent( window.advadsTrackingGAEvents.impression, {
					'event_category':  'Advanced Ads',
					'event_label':     '[' + trackedAds[i] + '] ' + advads_gatracking_allads[self.blogId][trackedAds[i]]['title'],
					'non_interaction': true
				} );
			}
		}

		this.normalTrackingDone = true;
	};

	this.trackClick = function ( id, serverSide, ev, el ) {
		if ( typeof serverSide === 'undefined' ) {
			serverSide = true;
		}

		var trackData = {
			'event_category':  'Advanced Ads',
			'event_label':     '[' + id + '] ' + advads_gatracking_allads[self.blogId][id]['title'],
			'non_interaction': true
		};

		// Send the data and stop workflow if it is not a linkout link
		if ( ! ev && ! el ) {
			self.sendEvent( window.advadsTrackingGAEvents.click, trackData );
			return;
		}

		var url = advads_gatracking_allads[self.blogId][id]['target'];
		if ( typeof advadsGATracking.postContext === 'undefined' ) {
			url = url.replace( '[CAT_SLUG]', advadsGATracking.postContext.cats );
			url = url.replace( '[POST_ID]', advadsGATracking.postContext.postID );
			url = url.replace( '[POST_SLUG]', advadsGATracking.postContext.postSlug );
		}
		url = url.replace( '[AD_ID]', id );

		var href = el.getAttribute( 'href' );
		if ( serverSide ) {
			url = href;
		} else {
			url = self.appendQueryString( url, self.getQueryString( href ) );
			if (
				typeof advads_gatracking_transmitpageqs[self.blogId] !== 'undefined'
				&& advads_gatracking_transmitpageqs[self.blogId][id]
			) {
				url = self.appendQueryString( url, self.getQueryString( document.location.href ) );
			}
		}
		// phpcs:ignore -- PHPCS can't handle boolean casting this way.
		var newTab = !! el.getAttribute( 'target' );
		if ( newTab ) {
			// the url is opened in a new tab/window
			self.sendEvent( window.advadsTrackingGAEvents.click, trackData );
			// no server side tracking, change the link to the real target before the browser opens a new tab
			if ( ! serverSide ) {
				el.setAttribute( 'href', url );
			}
		} else {
			// intercept the default click event behavior
			ev.preventDefault();
			// Creates a timeout to redirect after one second.
			self.clickTimer = setTimeout( function () {
				abortAndRedirect( url, newTab );
			}, 1000 );

			// create a callback to be used as event callback.
			function abortAndRedirect() {
				if ( self.clickTimer !== null ) {
					clearTimeout( self.clickTimer );
					self.clickTimer = null;
				}
				window.location = url;
			}

			trackData.event_callback = abortAndRedirect;
			self.sendEvent( window.advadsTrackingGAEvents.click, trackData );
		}
	};

	this.sendEvent = ( type, data ) => {
		self.propertyIds.forEach( propertyId => {
			data.send_to = propertyId;
			gtag( 'event', type, structuredClone( data ) );
		} );
	};

	// pseudo-constructor
	( function () {
		if ( typeof gtag !== 'function' ) {
			// No one has requested gtag.js at this point, require it.
			var script   = document.createElement( 'script' );
			script.src   = 'https://www.googletagmanager.com/gtag/js';
			script.async = true;

			document.body.appendChild( script );

			window.dataLayer = window.dataLayer || [];
			window.gtag      = function () {
				dataLayer.push( arguments );
			};
			gtag( 'js', new Date() );
		}

		var config = {'send_page_view': false, 'transport_type': 'beacon'};
		if ( window.advads_gatracking_anonym ) {
			config.anonymize_ip = true;
		}
		self.propertyIds.forEach( id => {
			gtag( 'config', id, config );
		} );

		document.addEventListener( 'advadsGADeferedTrack', function () {
			self.trackImpressions( false );
		} );
		document.addEventListener( 'advadsGADelayedTrack', function () {
			self.trackImpressions( true );
		} );
		self.trackImpressions();
	} )();

	return this;
}

document.addEventListener( 'DOMContentLoaded', function () {
	for ( let bid in advads_tracking_methods ) {
		bid = parseInt( bid, 10 );
		if ( isNaN( bid ) ) {
			continue;
		}
		if ( AdvAdsTrackingUtils.blogUseGA( bid ) ) {
			if ( typeof advads !== 'undefined' && advads.privacy.get_state() === 'unknown' ) {
				document.addEventListener( 'advanced_ads_privacy', function ( event ) {
					if ( event.detail.state === 'not_needed' || event.detail.state === 'accepted' ) {
						advancedAdsGAInstances.getInstance( bid );
					}
				} );
				return;
			}

			advancedAdsGAInstances.getInstance( bid );
		}
	}
} );
