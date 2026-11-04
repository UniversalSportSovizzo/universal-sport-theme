<?php get_header(); ?>

<main id="contenuto-principale" class="container">
    <?php 
    if ( have_posts() ) : 
        while ( have_posts() ) : the_post(); 
            ?>
            <h1 class="page-title text-center"><?php the_title(); ?></h1>
            <div class="title-underline"></div>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
            <?php 
        endwhile; 
    endif; 
    ?>
</main>
<?php get_footer(); ?>