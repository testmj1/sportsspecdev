// needs jQuery because the event gets fired from jQuery.
( function () {
	var targets = 'advads-sticky-trigger';
	if ( typeof advanced_ads_layer_settings !== 'undefined' ) {
		targets += ' ' + advanced_ads_layer_settings.layer_class + '-trigger';
	}
	jQuery( document ).on( targets, function ( ev ) {
		var $target = jQuery( ev.target ),
			ads     = {},
			bid     = parseInt( $target.attr( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackbid' ) ), 10 ),
			id      = parseInt( $target.attr( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackid' ) ), 10 ),
			addAd   = function ( id, bid ) {
				if ( typeof ads[bid] === 'undefined' ) {
					ads[bid] = [];
				}

				ads[bid].push( id );
			};

		if ( bid ) {
			if ( ! $target.data( 'delayed' ) || ! $target.data( AdvAdsTrackingUtils.getPrefixedAttribute( 'impression' ) ) ) {
				return;
			}
			addAd( id, bid );
		} else {
			if ( ! $target.find( '[data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackbid' ) + ']' ).length ) {
				return;
			}
			$target.find( '[data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackbid' ) + ']' ).each( function () {
				var $this = jQuery( this );
				if ( ! $this.data( 'delayed' ) || ! $this.data( AdvAdsTrackingUtils.getPrefixedAttribute( 'impression' ) ) ) {
					return;
				}
				bid = parseInt( $this.attr( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackbid' ) ), 10 );
				id  = parseInt( $this.attr( 'data-' + AdvAdsTrackingUtils.getPrefixedAttribute( 'trackid' ) ), 10 );
				addAd( id, bid );
			} );
		}

		if ( AdvAdsTrackingUtils.blogUseGA( bid ) ) {
			advadsGATracking.delayedAds = AdvAdsTrackingUtils.concat( advadsGATracking.delayedAds, ads );
		}

		AdvAdsImpressionTracker.track( ads, 'delayed' );
	} );
} )( jQuery );
