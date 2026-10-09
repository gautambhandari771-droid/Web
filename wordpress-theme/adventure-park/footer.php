<?php
/**
 * Bottom of every page: footer, WhatsApp button and scripts.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
echo adventure_park_render( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput -- every value is escaped in inc/render.php
wp_footer();
?>
</body>
</html>
