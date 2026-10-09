<?php
/**
 * Posts (if you write any) and anything else without its own template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
  <main id="main">
    <section class="border-b">
      <div class="mx-auto max-w-3xl px-4 pb-12 pt-14 md:px-6 md:pt-20">
        <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl"><?php echo esc_html( is_singular() ? get_the_title() : wp_get_document_title() ); ?></h1>
      </div>
    </section>
    <div class="ap-content mx-auto max-w-3xl px-4 py-12 text-lg leading-relaxed text-muted-foreground md:px-6 md:py-16">
      <?php
      if ( have_posts() ) {
          while ( have_posts() ) {
              the_post();
              if ( is_singular() ) {
                  the_content();
              } else {
                  echo '<h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
                  the_excerpt();
              }
          }
          the_posts_pagination();
      } else {
          echo '<p>' . esc_html__( 'Nothing here yet.', 'adventure-park' ) . '</p>';
      }
      ?>
    </div>
  </main>
<?php
get_footer();
