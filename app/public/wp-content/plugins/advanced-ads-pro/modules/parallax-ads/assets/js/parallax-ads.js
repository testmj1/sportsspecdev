// phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning -- PHPCS can't handle es5 short functions.

/**
 * Placement definition.
 *
 * @typedef ParallaxPlacement
 * @type {Object.<string, Object>}
 *
 * @property {Boolean} enabled
 * @property {Object} height
 * @property {Number} height.value
 * @property {String} height.unit
 */

/**
 * Associative array with parallax options.
 *
 * @typedef ParallaxOptions
 * @type {Object}
 *
 * @property {ParallaxPlacement[]} placements
 *
 * @property {Array} classes
 * @property {String} classes.prefix - Current frontend prefix.
 * @property {String} classes.container - Parallax container class prefix.
 * @property {String} classes.clip - Parallax clip div class.
 * @property {String} classes.inner - Parallax inner div class.
 */

( () => {
	// The current viewport height. Re-assign on window.resize event.
	let viewportHeight;

	// Object to save placement keys that have been initialized.
	const initializedPlacements = {};

	// Object to save initialized image instances.
	const imageInstances = {};

	// Test via a getter in the options object to see if the passive property is accessed
	let supportsPassive = false;
	try {
		const opts = Object.defineProperty( {}, 'passive', {
			get: () => {
				supportsPassive = {passive: true};
			}
		} );
		window.addEventListener( 'testPassive', null, opts );
		window.removeEventListener( 'testPassive', null, opts );
	} catch ( e ) {}

	/** @type {ParallaxOptions} see type definition at top of file */
	const options = window.advads_parallax_placement_options;

	/**
	 * Set styles on the inner container when the parallax placement gets initialized and on window.resize.
	 *
	 * @param {Element} placementContainer
	 * @param {Element} placementInner
	 */
	const onLoad = ( placementContainer, placementInner ) => {
		placementInner.style.maxWidth   = placementContainer.offsetWidth + 'px';
		placementInner.style.visibility = 'visible';

		viewportHeight = window.innerHeight;
	};

	/**
	 * Iterate all parallax placements. If a placement is found, call the passed callback.
	 *
	 * @param {CallableFunction} callback
	 * @param {ParallaxPlacement[]} placements
	 */
	const calculate = ( callback, placements ) => {
		for ( const placementsKey in placements ) {
			const placementContainer = document.getElementById(
				options.classes.prefix +
				options.classes.container +
				placementsKey
			);
			if ( placementContainer === null ) {
				continue;
			}
			const placementInner = placementContainer.getElementsByClassName(
				options.classes.prefix +
				options.classes.inner
			)[0];

			if ( placementContainer && placementInner ) {
				initializedPlacements[placementsKey] = true;
				callback( placementContainer, placementInner, options.placements[placementsKey] );
			}
		}
	};

	/**
	 * Fit the parallax image into the container.
	 *
	 * @param {Element} placementContainer
	 * @param {Element} placementInner
	 * @param {String} event
	 */
	const fitImage = ( placementContainer, placementInner, event = '' ) => {
		const imgElement = placementInner.querySelector( 'img' );
		if ( ! imageInstances[imgElement.src] ) {
			imageInstances[imgElement.src] = {
				image:    new Image(),
				isLoaded: false
			};
		}
		const containerRect  = placementContainer.getBoundingClientRect();
		const resizeCallback = ( img ) => {
			if ( img.naturalHeight / img.naturalWidth > viewportHeight / placementContainer.clientWidth ) {
				imgElement.style.objectFit = 'contain';
			}

			placementInner.style.left = ( containerRect.width / 2 + containerRect.left ) + 'px';

			if ( img.naturalHeight >= viewportHeight && img.naturalWidth <= placementContainer.clientWidth ) {
				imgElement.style.height        = '100vh';
				placementInner.style.transform = 'translateX(-50%)';
				return;
			}

			if ( placementInner.getBoundingClientRect().height < containerRect.height ) {
				placementInner.style.height = containerRect.height + 'px';
				imgElement.style.objectFit  = 'cover';
			}
		};
		const scrollCallback = () => {
			let offsetY;

			if ( containerRect.bottom >= viewportHeight && containerRect.top <= viewportHeight ) {
				offsetY = viewportHeight - placementInner.getBoundingClientRect().height;
			} else if ( containerRect.top <= 0 && containerRect.bottom > 0 ) {
				offsetY = 0;
			} else {
				offsetY = ( ( viewportHeight - placementInner.getBoundingClientRect().height ) / ( viewportHeight - containerRect.height ) * containerRect.top ).toFixed( 2 );
			}

			placementInner.style.transform = 'translate3d(-50%,' + offsetY + 'px, 0)';
		};

		if ( imageInstances[imgElement.src].isLoaded ) {
			if ( event !== 'scroll' ) {
				resizeCallback( imageInstances[imgElement.src].image );
			}

			scrollCallback();
			return;
		}

		imageInstances[imgElement.src].image.addEventListener( 'load', e => {
			imageInstances[imgElement.src].isLoaded = e.target.complete && e.target.naturalHeight !== 0;

			resizeCallback( e.target );
			scrollCallback();
		} );

		imageInstances[imgElement.src].image.src = imgElement.src;
	};

	/**
	 * Initialize Placements.
	 * Use this on page load and as a callback for deferred injection.
	 */
	const initializePlacements = () => {
		const placements = Object.assign( {}, options.placements );
		for ( const placementsKey in initializedPlacements ) {
			delete placements[placementsKey];
		}

		if ( ! Object.keys( placements ).length ) {
			return;
		}

		calculate( ( placementContainer, placementInner ) => {
			placementContainer.style.visibility = 'hidden';

			onLoad( placementContainer, placementInner );
			fitImage( placementContainer, placementInner );

			placementContainer.style.visibility = 'visible';
		}, placements );
	};

	// register event listeners and initialize existing parallax placements.
	initializePlacements();
	document.addEventListener( 'DOMContentLoaded', initializePlacements, supportsPassive );

	window.addEventListener( 'resize', () => calculate( ( placementContainer, placementInner ) => {
		onLoad( placementContainer, placementInner );
		fitImage( placementContainer, placementInner, 'resize' );
	}, options.placements ), supportsPassive );

	const fitImageOnScroll = () => calculate( ( placementContainer, placementInner ) => {
		fitImage( placementContainer, placementInner, 'scroll' );
	}, options.placements );
	window.addEventListener( 'scroll', fitImageOnScroll, supportsPassive );
	window.addEventListener( 'touchmove', fitImageOnScroll, supportsPassive );

	// add cache-busting event listeners.
	if ( typeof advanced_ads_pro !== 'undefined' && typeof advanced_ads_pro.observers !== 'undefined' ) {
		advanced_ads_pro.observers.add( event => {
			if (
				['inject_passive_ads', 'inject_ajax_ads'].indexOf( event.event ) === - 1
				|| ( event.ad_ids && ! Object.keys( event.ad_ids ).length )
			) {
				return;
			}
			initializePlacements();
		} );
	}
} )();
