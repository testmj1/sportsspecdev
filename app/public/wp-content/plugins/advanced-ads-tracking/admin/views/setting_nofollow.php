<?php
/**
 * Template for rel="nofollow" setting.
 *
 * @var bool $nofollow
 */
?>
<label>
	<input name="<?php echo esc_attr( $this->plugin->options_slug ); ?>[nofollow]" type="checkbox" <?php checked( $nofollow ); ?>/>
	<?php
	printf(
	/* Translators: %s is <code>rel="nofollow"</code> */
		esc_html__( 'Add %s to programatically created links.', 'advanced-ads-tracking' ),
		'<code>rel="nofollow"</code>'
	);
	?>
</label>
<br/>
