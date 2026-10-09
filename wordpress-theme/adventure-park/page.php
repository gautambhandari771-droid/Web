<?php
/**
 * Any other page you add yourself (for example a privacy note): its title and text in the site's style.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
  <main id="main">
    <section class="relative isolate overflow-hidden border-b">
      <div aria-hidden="true" class="absolute inset-0 -z-10 bg-grid [mask-image:radial-gradient(ellipse_70%_70%_at_50%_0%,#000_45%,transparent_100%)]"></div>
      <div class="mx-auto max-w-3xl px-4 pb-12 pt-14 md:px-6 md:pb-16 md:pt-20">
        <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl"><?php the_title(); ?></h1>
      </div>
    </section>
    <article class="ap-content mx-auto max-w-3xl px-4 py-12 text-lg leading-relaxed text-muted-foreground md:px-6 md:py-16">
      <?php
      while ( have_posts() ) {
          the_post();
          the_content();
      }
      ?>
    </article>
  </main>
<?php
get_footer();
