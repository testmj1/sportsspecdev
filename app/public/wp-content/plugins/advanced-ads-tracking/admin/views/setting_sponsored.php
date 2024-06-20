<?php
/**
 * Template for rel="sponsored" setting.
 *
 * @var bool $sponsored
 */
?>
<label>
	<input name="<?php echo esc_attr( $this->plugin->options_slug ); ?>[sponsored]" type="checkbox" <?php checked( $sponsored ); ?>/>
	<?php
	printf(
	/* Translators: %s is <code>rel="sponsored"</code> */
		esc_html__( 'Add %s to programatically created links.', 'advanced-ads-tracking' ),
		'<code>rel="sponsored"</code>'
	);
	?>
	<p class="description">
		<a href="https://webmasters.googleblog.com/2019/09/evolving-nofollow-new-ways-to-identify.html" target="_blank" class="advads-external-link"><?php esc_html_e( "Read Google's recommendation on Google Webmaster Central Blog.", 'advanced-ads-tracking' ); ?></a>
	</p>
</label>
<br/>
