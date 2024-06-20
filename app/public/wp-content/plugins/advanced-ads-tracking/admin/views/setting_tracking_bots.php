<label>
    <input type="checkbox" <?php checked( $track_bots, '1' ); ?> value="1" name="<?php echo esc_attr( $this->plugin->options_slug ); ?>[track-bots]" />
    <?php esc_html_e( 'Activate to also count impressions and clicks for crawlers, bots and empty user agents', 'advanced-ads-tracking' ); ?>
</label>