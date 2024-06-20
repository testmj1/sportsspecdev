<?php
/**
 * Render the Statistics page under Advanced Ads > Statistics
 *
 * @var string $tracking_method Tracking method as selected in the Tracking settings.
 */
global $wpdb;

$ad_titles        = [];
$autocomplete_src = [];
foreach ( $all_ads as $_ad ) {
	$ad_titles[ $_ad->ID ] = $_ad->post_title;
	$autocomplete_src[]    = [
		'label' => $_ad->post_title,
		'value' => $_ad->ID,
	];
}

$ad_titles['length'] = count( $ad_titles );

/**
 *  Ad groups
 */
$ad_model            = new Advanced_Ads_Model( $wpdb );
$terms               = $ad_model->get_ad_groups( [ 'post_status' => [ 'publish', 'future', 'draft', 'pending', Advanced_Ads_Tracking_Util::get_expired_post_status() ] ] );
$groups_to_ads       = [];
$ads_to_groups       = [];
$groups_autocomplete = [];
foreach ( $terms as $ad_group ) {
	$_group     = new Advanced_Ads_Group( $ad_group->term_id, [
		'post_status' => [ 'publish', 'future', 'draft', 'pending', Advanced_Ads_Tracking_Util::get_expired_post_status() ],
	] );
	$_group_ads = $_group->get_all_ads();
	$__ads      = [];
	if ( is_array( $_group_ads ) ) {
		foreach ( $_group_ads as $__ad ) {
			$__ads[ $__ad->ID ] = [
				'ID'    => $__ad->ID,
				'title' => $__ad->post_title,
			];
			if ( ! isset( $ads_to_groups[ $__ad->ID ] ) ) {
				$ads_to_groups[ $__ad->ID ] = [];
			}
			$ads_to_groups[ $__ad->ID ][] = $ad_group->term_id;
		}
	}
	$groups_to_ads[ $ad_group->term_id ] = [
		'ID'   => $ad_group->term_id,
		'slug' => $ad_group->slug,
		'name' => $ad_group->name,
		'ads'  => $__ads,
	];
	$groups_autocomplete[]               = [
		'label' => $ad_group->name,
		'value' => $ad_group->term_id,
	];
}
$group_count             = count( $groups_to_ads );
$groups_to_ads['length'] = $group_count;

$formated_number = number_format_i18n( 12345.678, 3 );

?>
<script type="text/javascript">
	var groupsToAds      = <?php echo json_encode( $groups_to_ads ); ?>;
	var adsToGroups      = <?php echo json_encode( $ads_to_groups ); ?>;
	var groupAutoCompSrc = <?php echo json_encode( $groups_autocomplete ); ?>;
	var numbersFormated  = "<?php echo str_replace( '"', '\"', $formated_number ); ?>";
</script>
<div class="wrap">
	<h2 style="display: none;"><!-- There needs to be an empty H2 headline at the top of the page so that WordPress can properly position admin notifications --></h2>
	<?php if ( $tracking_method === 'ga' ) : ?>
		<div class="notice advads-notice">
			<p>
				<?php
					printf(
						// translators: %1$s is the opening link tag, %2$s is the closing link tag.
						esc_html__( 'You are currently tracking ads with Google Analytics. The statistics can be viewed only within your %1$sAnalytics account%2$s.', 'advanced-ads-tracking' ),
						'<a href="https://analytics.google.com/analytics/web/" class="advads-external-link" target="_blank">',
						'</a>'
					);
				?>
			</p>
		</div>
	<?php endif; ?>
	<div class="postbox advads-box">
		<h2 class="hndle"><?php esc_html_e( 'Filter', 'advanced-ads-tracking' ); ?>
		<?php if ( current_user_can( advanced_ads_tracking_db_cap() ) ) : ?>
			<span class="advads-hndlelinks"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->db_op_page_slug ) ); ?>"><?php esc_html_e( 'Database management', 'advanced-ads-tracking' ); ?></a></span>
		<?php endif; ?>
		</h2>
		<div class="inside">
	<form action="" method="post" id="stats-form">
		<input type="hidden" id="all-ads" value="<?php echo implode( '-', $ads ); ?>"/>
		<table id="period-table">
			<thead style="text-align:left;">
			<th><strong><?php esc_html_e( 'Period', 'advanced-ads-tracking' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Group by:', 'advanced-ads-tracking' ); ?></strong></th>
			<th>
				<?php
				if ( current_user_can( advanced_ads_tracking_db_cap() ) ) :
					?>
					<strong><?php esc_html_e( 'Data source:', 'advanced-ads-tracking' ); ?></strong><?php endif; ?></th>
			<?php if ( current_user_can( advanced_ads_tracking_db_cap() ) ) : ?>
				<th style="padding-left:6em;"></th>
			<?php endif; ?>
			</thead>
			<tbody>
			<tr>
				<td>
					<fieldset class="load-from-db-fields">
						<label>
							<select name="advads-stats[period]" class="advads-stats-period">
								<?php foreach ( $periods as $_period_key => $_period ) : ?>
									<option value="<?php echo esc_attr( $_period_key ); ?>" <?php selected( $_period_key, $period ); ?>><?php echo esc_html( $_period ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<input type="text" name="advads-stats[from]" class="advads-stats-from<?php echo $period !== 'custom' ? ' hidden' : ''; ?>" value="<?php echo esc_attr( $from ); ?>" autocomplete="off" size="10" maxlength="10" placeholder="<?php esc_html_e( 'from', 'advanced-ads-tracking' ); ?>"/>
						<input type="text" name="advads-stats[to]" class="advads-stats-to<?php echo $period !== 'custom' ? ' hidden' : ''; ?>" value="<?php echo esc_attr( $to ); ?>" autocomplete="off" size="10" maxlength="10" placeholder="<?php esc_html_e( 'to', 'advanced-ads-tracking' ); ?>"/>
						<button class="button button-primary" id="load-simple"><?php esc_html_e( 'load stats', 'advanced-ads-tracking' ); ?></button>
					</fieldset>
					<fieldset class="load-from-file-fields" style="display:none;">
						<?php
						if ( current_user_can( advanced_ads_tracking_db_cap() ) ) :
							$load_from_file_period_args = [
								'period-options' => [
									'latestmonth' => esc_html__( 'latest month', 'advanced-ads-tracking' ),
									'firstmonth'  => esc_html__( 'first month', 'advanced-ads-tracking' ),
								],
								'period'         => [ 'stats-file-period', '' ],
								'from'           => [ 'stats-file-from', '' ],
								'to'             => [ 'stats-file-to', '' ],
							];
							Advanced_Ads_Tracking_Dbop::period_select_inputs( $load_from_file_period_args );
							?>
							<button class="button button-primary" disabled id="load-stats-from-file"><?php esc_html_e( 'load stats', 'advanced-ads-tracking' ); ?></button>
						<?php endif; ?>
					</fieldset>
				</td>
				<td>
					<label>
						<select name="advads-stats[groupby]">
							<?php foreach ( $groupbys as $_groupby_key => $_groupby ) : ?>
								<option value="<?php echo esc_attr( $_groupby_key ); ?>" <?php selected( $_groupby_key, $groupby ); ?>><?php echo esc_html( $_groupby[1] ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="ajax-spinner-placeholder" id="statsA-spinner"></span>
					</label>
				</td>
				<td>
					<select id="data-source" <?php echo ! current_user_can( advanced_ads_tracking_db_cap() ) ? 'style="display:none;"' : ''; ?>>
						<option value="db"><?php esc_html_e( 'Database', 'advanced-ads-tracking' ); ?></option>
						<?php if ( current_user_can( advanced_ads_tracking_db_cap() ) ) : ?>
							<option value="file"><?php esc_html_e( 'File', 'advanced-ads-tracking' ); ?></option>
						<?php endif; ?>
					</select>
					<?php if ( current_user_can( advanced_ads_tracking_db_cap() ) ) : ?>
						<span class="load-from-file-fields" style="display:none;">
						<button class="button button-secondary" id="select-file"><?php esc_html_e( 'select file', 'advanced-ads-tracking' ); ?></button>
						<span class="ajax-spinner-placeholder" id="file-spinner"></span>
						<span class="description" id="stats-file-description"><?php esc_html_e( 'no file selected', 'advanced-ads-tracking' ); ?></span>
						<input type="hidden" id="stats-attachment-id" value=""/>
						<input type="hidden" id="stats-attachment-firstdate" value=""/>
						<input type="hidden" id="stats-attachment-lastdate" value=""/>
						<input type="hidden" id="stats-attachment-adIDs" value=""/>
					</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" id="period-td"></td>
			</tr>
			<tr id="compare-tr" <?php echo ( isset( $_REQUEST['advads-stats']['period2'] ) ) ? '' : 'style="display:none;"'; ?>>
				<td colspan="3" style="padding-top:1.5em;">
					<strong><?php esc_html_e( 'Compare with', 'advanced-ads-tracking' ); ?></strong>
					<fieldset>
						<button class="button button-secondary donotreversedisable" id="compare-prev-btn"><?php esc_html_e( 'previous period', 'advanced-ads-tracking' ); ?></button>
						&nbsp;&nbsp;
						<button class="button button-secondary donotreversedisable" id="compare-next-btn"><?php esc_html_e( 'next period', 'advanced-ads-tracking' ); ?></button>
						<input id="compare-offset" value="0" type="hidden"/>
						<input id="compare-from-prev" value="" type="hidden"/>
						<input id="compare-to-prev" value="" type="hidden"/>
						<input id="compare-from-next" value="" type="hidden"/>
						<input id="compare-to-next" value="" type="hidden"/>
					</fieldset>
				</td>
				<?php if ( current_user_can( advanced_ads_tracking_db_cap() ) ) : ?>
					<td></td>
				<?php endif; ?>
			</tr>
			</tbody>
		</table>
		<hr/>
		<div id="ad-filter-wrap" style="float: left;">
			<label><strong><?php esc_html_e( 'Filter by ad', 'advanced-ads-tracking' ); ?></strong></label><br/>
			<input id="ad-filter" class="donotreversedisable" type="text" value="" <?php echo count( $ad_titles ) < 2 ? 'disabled' : ''; ?>/>
			<script type="text/javascript">
				var adTitles    = <?php echo json_encode( $ad_titles ); ?>;
				var adTitlesDB  = <?php echo json_encode( $ad_titles ); ?>;
				var autoCompSrc = <?php echo json_encode( $autocomplete_src ); ?>;
			</script>
		</div>
		<div id="group-filter-wrap">
			<?php if ( $groups_to_ads['length'] > 1 ) : ?>
				<label><strong><?php esc_html_e( 'Filter by group', 'advanced-ads-tracking' ); ?></strong></label><br/>
				<input id="group-filter" class="donotreversedisable" type="text" value=""/>
			<?php endif; ?>
		</div>
		<div id="display-filter-list">
			<strong style="display: block;"><span id="filter-head"><?php esc_html_e( 'Current filters', 'advanced-ads-tracking' ); ?></span></strong>
		</div>
	</form>
		<div class="clearfix" style="overflow: hidden;"></div>
		</div>
	</div>
	<div class="postbox advads-box">
		<div class="inside">
	<div id="advads-stats-graph"></div>
	<div id="advads-graph-legend" style="display:none;">
		<div class="legend-item donotremove">
			<div id="solid-line-legend">
			</div>
			<span><?php _e( 'impressions', 'advanced-ads-tracking' ); ?></span>
		</div>
		<div class="legend-item donotremove">
			<div id="dashed-line-legend">
			</div>
			<span><?php _e( 'clicks', 'advanced-ads-tracking' ); ?></span>
		</div>
	</div>
		</div>
	</div>
	<script type="text/javascript">
		var advadsStatPageNonce = '<?php echo esc_attr( wp_create_nonce( 'advads-stats-page' ) ); ?>';
	</script>
	<div id="table-area">
		<div class="postbox advads-box">
			<h2><?php esc_html_e( 'Statistics by date', 'advanced-ads-tracking' ); ?></h2>
			<div class="inside">
		<div id="dateTable"></div>
			</div>
		</div>
		<div class="postbox advads-box">
			<h2><?php esc_html_e( 'Statistics by ad', 'advanced-ads-tracking' ); ?></h2>
			<div class="inside">
		<div id="adTable" ></div>
			</div>
		</div>
		<br class="clear"/>
	</div>
	<br class="clear"/>
</div>
