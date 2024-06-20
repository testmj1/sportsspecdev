<?php
/**
 * @var string $options_slug      slug for tracking options.
 * @var string $public_stats_slug page slug for public stats page.
 * @var string $nonce             wp_nonce for action 'advads-tracking-public-stats'.
 */
?>
<?php echo site_url(); ?>/<input id="public-stat-base" name="<?php echo $options_slug; ?>[public-stats-slug]" type="text" value="<?php echo $public_stats_slug; ?>" autocomplete="advads-stats-slug"/>/<span id="public-stats-spinner32" style="display:inline-block;vertical-align:middle;margin-left:0.5em;"></span><br/>
<p id="public-stat-notice" style="font-style:italic;"></p>
<script>
	var advadsTrackingAjaxNonce = '<?php echo $nonce; ?>';
</script>
