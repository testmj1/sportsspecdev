var AdvAdsImpressionTracker = {
	ajaxAds:          {},
	passiveAds:       {},
	initialAds:       {},
	removeDelayedAds: function ( ids ) {
		var trackIds       = document.querySelectorAll( '[data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackid' ) + '][data-delayed="1"]' ),
			trackIdsLength = trackIds.length;

		if ( ! trackIdsLength ) {
			return ids;
		}

		for ( var i = 0; i < trackIdsLength; i ++ ) {
			var id  = parseInt( trackIds[i].dataset[AdvAdsTrackingUtils.getPrefixedDataSetAttribute( 'trackid' )], 10 ),
				bid = parseInt( trackIds[i].dataset[AdvAdsTrackingUtils.getPrefixedDataSetAttribute( 'trackbid' )], 10 );

			if ( AdvAdsTrackingUtils.hasAd( ids ) && typeof ids[bid] !== 'undefined' ) {
				var index = ids[bid].indexOf( id );
				if ( index > - 1 ) {
					ids[bid].splice( index, 1 );
				}
			}
		}

		return ids;
	},
	track:            function ( ids, server ) {
		server = server ? server : 'all';
		if ( ! AdvAdsTrackingUtils.hasAd( ids ) ) {
			return;
		} // do not send empty array

		for ( var bid in ids ) {
			if ( AdvAdsTrackingUtils.blogUseGA( bid ) ) {
				// ad IDs already collected and will be sent automatically once the Analytics tracker is ready
				advadsGATracking.deferedAds = AdvAdsTrackingUtils.concat( advadsGATracking.deferedAds, AdvAdsTrackingUtils.adsByBlog( ids, bid ) );

				if ( server === 'delayed' ) {
					// "Delayed" tracking. Explicitly defined for placements that initially hide ads (timeout/scroll)
					this.triggerEvent( 'advadsGADelayedTrack' );
				} else {
					// the "usual" deferred tracking (once the GA tracker is ready)
					this.triggerEvent( 'advadsGADeferedTrack' );
				}

				if ( server === 'ajax' && AdvAdsTrackingUtils.hasAd( AdvAdsTrackingUtils.adsByBlog( this.ajaxAds, bid ) ) ) {
					// remove all tracked ajax ads
					for ( var i in this.ajaxAds[bid] ) {
						var index = ids[bid].indexOf( this.ajaxAds[bid][i] );
						if ( index > - 1 ) {
							this.ajaxAds[bid].splice( i, 1 );
						}
					}
				}
			}

			if (
				server !== 'ajax' // ads already tracked through AJAX cache-busting
				&& (
					advads_tracking_methods[bid] === 'frontend' // default AJAX handler
					|| advads_tracking_methods[bid] === 'onrequest' // also track locally if delayed ads
				)
			) {
				// send tracking data to the server.
				this.sendTrack( bid, ids[bid] );
			}

			this.ajaxAds = {};
		}
	},
	triggerEvent:     function ( name ) {
		var event = new CustomEvent( name );
		document.dispatchEvent( event );
	},
	sendTrack:        function ( bid, ads ) {
		if ( ! ads.length ) {
			return;
		}
		AdvAdsTrackingUtils.post( advads_tracking_urls[bid], {
			ads:      ads,
			action:   window.advadsTracking.impressionActionName,
			referrer: window.location.pathname + window.location.search,
			bid:      bid
		} );
	}
};

( function () {
	var localTracker = function () {
		if ( typeof advads_tracking_ads === 'undefined' ) {
			return;
		}

		advads_tracking_ads = AdvAdsImpressionTracker.removeDelayedAds( advads_tracking_ads );
		if ( ! AdvAdsTrackingUtils.hasAd( advads_tracking_ads ) ) {
			return;
		}

		for ( var bid in advads_tracking_ads ) {
			if ( advads_tracking_methods[bid] !== 'frontend' ) {
				continue;
			}

			if ( typeof advads !== 'undefined' && typeof advads.privacy.is_ad_decoded !== 'undefined' ) {
				// remove ads that have not been decoded.
				advads_tracking_ads[bid] = advads_tracking_ads[bid].filter( advads.privacy.is_ad_decoded );
			}

			// cache-busting: off
			AdvAdsImpressionTracker.track( advads_tracking_ads );
			// clean cache-busting: off
			advads_tracking_ads = {1: []};
		}
	};

	if ( typeof advads !== 'undefined' && advads.privacy.get_state() === 'unknown' ) {
		document.addEventListener( 'advanced_ads_privacy', function ( event ) {
			if ( event.detail.previousState === 'unknown' ) {
				localTracker();
			}
		} );
	} else {
		advanced_ads_ready( localTracker, 'interactive' );
	}
} )();
