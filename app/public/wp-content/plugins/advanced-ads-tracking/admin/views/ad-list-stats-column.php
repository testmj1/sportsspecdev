<?php
/**
 * Markup for the statistics column
 *
 * @var int $ad_id the current ad ID.
 */

$post_data        = get_post();
$impr             = $post_data->impressions ?? 0;
$clicks           = $post_data->clicks ?? 0;
$ad               = new Advanced_Ads_Ad( $ad_id );
$ad_options       = $ad->options();
$tracking_options = Advanced_Ads_Tracking_Plugin::get_instance()->options();
$target           = Advanced_Ads_Tracking_Util::get_link( $ad );

// no tracking for Yielscale ad type.
if ( in_array( $ad->type, [ 'yieldscale' ], true ) ) {
	return;
}

$published = $post_data->post_status === 'publish';
?>
<ul>
	<?php if ( $this->plugin->check_ad_tracking_enabled( $ad, 'impression' ) ) : ?>
		<li>
			<strong><?php esc_html_e( 'Impressions', 'advanced-ads-tracking' ); ?>:</strong>&nbsp;<?php echo esc_html( number_format_i18n( $impr ) ); ?>
		</li>
	<?php else : ?>
        <li>
            <strong><?php esc_html_e( 'Impressions', 'advanced-ads-tracking' ); ?>:</strong>&nbsp;
			<?php esc_html_e( 'disabled', 'advanced-ads-tracking' ); ?>
        </li>
	<?php endif; ?>
	<?php if ( $this->plugin->check_ad_tracking_enabled( $ad, 'click' ) && in_array( $ad->type, Advanced_Ads_Tracking_Plugin::get_clickable_types(), true ) ) : ?>
		<li>
			<strong><?php esc_html_e( 'Clicks', 'advanced-ads-tracking' ); ?>:</strong>&nbsp;<?php echo esc_html( number_format_i18n( $clicks ) ); ?>
		</li>
	<?php else : ?>
        <li>
            <strong><?php esc_html_e( 'Clicks', 'advanced-ads-tracking' ); ?>:</strong>&nbsp;
			<?php esc_html_e( 'disabled', 'advanced-ads-tracking' ); ?>
        </li>
	<?php endif; ?>
	<?php
    if ($this->plugin->check_ad_tracking_enabled($ad, 'impression')
        && $this->plugin->check_ad_tracking_enabled($ad, 'click')
        && in_array($ad->type, Advanced_Ads_Tracking_Plugin::get_clickable_types(), true)
        && $impr !== 0
    ) : ?>
        <li>
			<strong><?php esc_html_e( 'CTR', 'advanced-ads-tracking' ); ?>:</strong>&nbsp;
			<?php echo esc_html( number_format_i18n( 100 * $clicks / $impr, 2 ) ); ?>%
		</li>
    <?php endif; ?>

	<?php if ( $target ) : ?>
		<li>
			<strong><?php esc_html_e( 'Target url', 'advanced-ads-tracking' ); ?>:</strong>&nbsp;
			<div class="target-link-div">
				<div class="target-link-text">
					<a href="<?php echo esc_url( $target ); ?>" target="_blank"><?php echo esc_html( $target ); ?></a>
				</div>
				<a href="<?php echo esc_url( $target ); ?>" target="_blank"><?php esc_html_e( 'show', 'advanced-ads-tracking' ); ?></a>
			</div>
		</li>
	<?php endif; ?>
</ul>
<?php if ( $published ) : // avoid admin stats for non published ads ?>
	<div class="row-actions">
		<a target="blank"
		   href="<?php echo Advanced_Ads_Tracking_Admin::admin_30days_stats_url( $ad_id ); ?>"><?php _e( 'Statistics for the last 30 days', 'advanced-ads-tracking' ); ?></a>
	</div>
<?php endif; ?>
