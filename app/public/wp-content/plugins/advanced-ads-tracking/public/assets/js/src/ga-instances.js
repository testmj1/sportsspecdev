/**
 * Stores Google Analytics tracking script instances
 */
const advancedAdsGAInstances = {
	instances: [],

	/**
	 * Return instance for given blog id, creates and stores instance if it doesn't exists.
	 *
	 * @param {number} bId
	 * @return {AdvAdsGATracker}
	 */
	getInstance: function ( bId ) {
		if ( typeof this.instances[bId] === 'undefined' ) {
			this.instances[bId] = new AdvAdsGATracker( bId, advads_gatracking_uids[bId] );
		}

		return this.instances[bId];
	}
};
