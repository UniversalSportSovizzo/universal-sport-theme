<?php get_header(); ?>

<main id="contenuto-principale" class="container">
    <?php while ( have_posts() ) : the_post(); ?>
        
        <?php 
        // Array con gli slug delle pagine in cui VOGLIAMO nascondere il titolo standard
        $pagine_sportive = array( 'partite-giocate', 'prossime-partite', 'classifica', 'marcatori' );
        
        // Stampa l'header standard SOLO se la pagina attuale NON è nell'array
        if ( ! is_page( $pagine_sportive ) ) : 
        ?>
            <header class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
                <div class="title-underline"></div>
            </header>
        <?php endif; ?>

        <article class="page-content">
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
