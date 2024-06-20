<div class="container">
  <footer class="py-3 my-4">
  <?php
        wp_nav_menu(
            array(
                'menu' => 'footer',
                'container' => '',
                'theme_location' => 'footer',
                'items_wrap' => '<ul id="" class="nav justify-content-center border-bottom pb-3 mb-3">%3$s</ul>',
            )
        );
    ?>
    <p class="text-center text-muted">© Copyline <?php echo date("Y"); ?></p>
  </footer>
</div>

<?php
    wp_footer();
?>

</body>
</html>