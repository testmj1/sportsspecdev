/**
 * Check if there is a link in the content field and a tracking url given
 */
jQuery( document ).ready( function () {
	jQuery( '#advanced-ads-ad-parameters textarea#advads-ad-content-plain' ).on( 'keyup', advads_tracking_check_link );

	advads_tracking_display_click_limit_field( jQuery( '#advanced-ad-type input:checked' ).val() );
} );

/**
 * Validate urlfield & code editor url logic
 */
jQuery(window).on("load", function () {
    const advadsUrl = jQuery("#advads-url");
    const codemirror = Advanced_Ads_Admin.editor?.codemirror;

    function advads_tracking_check() {
        advads_tracking_check_editor();
        advads_tracking_check_link();
    }

    advadsUrl?.on("keyup", advads_tracking_check);
    codemirror?.on("keyup", advads_tracking_check);
});
/**
 * onload check if url field is empty and %link% exists.
 */
jQuery(function(){
	advads_tracking_check_editor();
});
/**
 * Show notice if %link% is in the editor but the URL field is empty.
 * Show notice if URL exists but %link% is not in the editor.
 */
function advads_tracking_check_editor() {
	const text = Advanced_Ads_Admin.get_ad_source_editor_text();
	const advadsUrl = jQuery("#advads-url");
	const condition1 = text && text.includes(" href=") && text.includes("%link%");
	const condition2 = advadsUrl && "" === advadsUrl.val();

	jQuery(".advads-ad-notice-tracking-missing-url-field").toggleClass(
		"hidden",
		!(condition1 && condition2)
	);
}
/**
 * Display click tracking limitation fields based on ad type
 *
 * @param {string} ad_type
 */
function advads_tracking_display_click_limit_field( ad_type ) {
	// Show / hide click tracking row.
	jQuery( '.advads-tracking-click-limit-row' ).toggle( advads_tracking_clickable_ad_types.indexOf( ad_type ) !== - 1 );
	const optionsList = jQuery( '#advanced-ads-ad-parameters' ).siblings( '.advads-option-list' );
	const tracking    = optionsList.find( 'span.label:first-of-type' ).add( optionsList.find( ' > div:first-of-type' ) ).add( optionsList.find( ' > hr:first-of-type' ) );

	switch ( ad_type ) {
		case 'adsense':
		case 'gam':
		case 'group':
			optionsList.find( '> * ' ).not( tracking ).hide();
			break;
		default:
			optionsList.find( '> * ' ).show();
	}
}

jQuery( document ).on( 'change', '#advanced-ad-type input', function () {
	advads_tracking_display_click_limit_field( jQuery( this ).val() );
} );

/**
 * Check if there is a link attribute in the content field that is not %link%
 *
 * @returns {undefined}
 */
function advads_tracking_check_link() {
	// check if url is given and not empty
	if ( ! jQuery( '#advads-url' ).length || '' === jQuery( '#advads-url' ).val() ) {
		return;
	}
	// fetch the contents of the source editor via our global function
	var text = Advanced_Ads_Admin.get_ad_source_editor_text();
	// search for href attribute
	var errormessage = jQuery( '.advads-ad-notice-tracking-link-placeholder-missing' );
	if ( text.search( ' href=' ) > 0 && text.search( '%link%' ) < 0 ) {
		if ( errormessage.is( ':hidden' ) ) {
			errormessage.show();
		}
	} else {
		// hide error message
		errormessage.hide();
	}
}

function makeid( length ) {
	var text     = '';
	var possible = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	for ( var i = 0; i < length; i ++ ) {
		text += possible.charAt( Math.floor( Math.random() * possible.length ) );
	}
	return text;
}

( function ( $ ) {

	$( document ).on( 'click', '#regenerateSharableLink', function ( ev ) {
		ev.preventDefault();
		var pid = makeid( 48 );

		$( '[name="advanced_ad\[tracking\]\[public-id\]"]' ).val( pid );

		$( '#regenerateSharableLink' ).css( 'display', 'none' );
		$( '#save-new-public-link' ).css( 'display', 'inline' );
	} );

	/**
	 *  Draw the graph
	 */
	$( function () {

		// no stats to show yet or not relevant for this ad type (e.g., Analytics tracking method used)
		if ( 'undefined' === typeof advads_stats || false === advads_stats.impressions ) {
			return;
		}

		var imprs = [];
		for ( var date in advads_stats.impressions ) {
			var val = advads_stats.impressions[date][advads_stats.ID] || 0;
			imprs.push( [date, parseInt( val )] );
		}
		var clicks = [];
		for ( var date in advads_stats.clicks ) {
			var val = advads_stats.clicks[date][advads_stats.ID] || 0;
			clicks.push( [date, parseInt( val )] );
		}

		var graphOptions = {
			axes:        {
				xaxis:  {
					tickOptions:  {},
					tickInterval: ''
				},
				yaxis:  {
					min:          0,
					formatString: '$%.2f',
					autoscale:    true,
					label:        '',
					tickOptions:  {formatString: '%\'.0f'}
				},
				y2axis: {
					min:         0,
					autoscale:   true,
					label:       '',
					tickOptions: {formatString: '%\'.0f'}
				}
			},
			grid: {
				background: '#ffffff',
				borderWidth: 1.0,
				shadow: false,
				gridLineColor: '#e5e5e5',
				drawBorder: false
			},
			highlighter: {
				show:       true,
				sizeAdjust: 7.5
			},
			cursor:      {
				show: false
			},
			title:       {
				show: true
			},
			seriesDefaults: {
				rendererOptions: {
					smooth: true
				}
			}
		};

		graphOptions.axes.xaxis.renderer                 = $.jqplot.DateAxisRenderer;
		graphOptions.axes.xaxis.tickInterval             = '1 day';
		graphOptions.axes.xaxis.tickOptions.formatString = '%b&nbsp;%#d';
		graphOptions.axes.yaxis.label                    = advadsStatsLocale.impressions;
		graphOptions.axes.yaxis.labelRenderer            = $.jqplot.CanvasAxisLabelRenderer;
		graphOptions.axes.y2axis.label                   = advadsStatsLocale.clicks;
		graphOptions.axes.y2axis.labelRenderer           = $.jqplot.CanvasAxisLabelRenderer;

		graphOptions['series'] = [
			{
				color:         '#1B183A',
				highlighter:   {
					formatString: '%s: %\'.0f ' + advadsStatsLocale.impressions
				},
				lineWidth:     3,
				markerOptions: {
					size:  5,
					style: 'circle'
				}
		},
			{
				color:         '#0474A2',
				highlighter:   {
					formatString: '%s: %\'.0f ' + advadsStatsLocale.clicks
				},
				linePattern:   'dashed',
				lineWidth:     3,
				markerOptions: {
					size:  5,
					style: 'filledSquare'
				},
				yaxis:         'y2axis'
		}
		];
		var lines              = [imprs, clicks];
		var ticks              = [];
		for ( var i in imprs ) {
			var x = imprs[i];
			ticks.push( x[0] );
		}
		graphOptions.axes.xaxis.ticks = ticks;
		$.jqplot( 'stats-jqplot', lines, graphOptions );

	} );

} )( jQuery );
