<?php defined('ABSPATH') || exit; get_header(); ?>
<main id="main" class="content-page wrap">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<article <?php post_class(); ?>><h1><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div></article>
<?php endwhile; the_posts_navigation(); else : ?>
<h1>هنوز محتوایی منتشر نشده.</h1><a class="button button-primary" href="<?php echo esc_url(home_url('/')); ?>">دیدن کالکشن</a>
<?php endif; ?>
</main>
<?php get_footer(); ?>
