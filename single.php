<?php get_header(); ?>

<main class="container page-content single-news-main">
    <?php
    // Inizia il loop (ciclo) di WordPress
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            
            // SE È UN ARTICOLO NORMALE (NEWS)
            if ( get_post_type() === 'post' ) :
                ?>
                
                <!-- Pulsante "Indietro" stile Eventi -->
                <div class="single-news-back-wrapper">
                    <a href="/news/" class="back-link-match single-news-back-link"><i class="fa-solid fa-arrow-left"></i> TUTTE LE NEWS</a>
                </div>

                <article id="post-<?php the_ID(); ?>" class="single-news-article">
                    
                    <header class="single-news-header">
                        <h1 class="single-news-title"><?php the_title(); ?></h1>
                        
                        <!-- Meta Dati (Data e Categorie) -->
                        <div class="single-news-meta">
                            <span class="single-news-date">
                                <i class="fa-solid fa-calendar-days"></i> <?php echo get_the_date('d F Y'); ?>
                            </span>
                            
                            <?php
                            // Recupera e stampa le categorie dell'articolo come "pillole"
                            $categories = get_the_category();
                            if ( ! empty( $categories ) ) {
                                echo '<div class="single-news-categories">';
                                foreach ( $categories as $category ) {
                                    // Escludiamo le categorie di default vuote
                                    if ( $category->name !== 'Senza categoria' && $category->name !== 'Uncategorized' ) {
                                        
                                        // Crea il link personalizzato alla tua pagina news con il parametro ID
                                        $custom_link = home_url( '/news/?categoria=' . $category->term_id );
                                        
                                        echo '<a href="' . esc_url( $custom_link ) . '" class="single-news-cat-badge">' . esc_html( $category->name ) . '</a>';
                                    }
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </header>

                    <div class="single-news-content">
                        <?php 
                        // Corpo del testo (apparirà sotto l'immagine, a tutta larghezza)
                        the_content();
                        ?>
                    </div>
                    
                    <!-- Evita sbavature di impaginazione -->
                    <div class="single-news-clear"></div>

                </article>

                <!-- SEZIONE ULTIME NEWS IN FONDO -->
                <div class="single-news-related">
                    <h2>I nostri articoli più recenti</h2>
                    <?php 
                    // Esegue il nostro shortcode forzando il limite a 2
                    echo do_shortcode('[ultime_news limite="2"]'); 
                    ?>
                </div>

            <?php 
            else : 
                // Per le partite di SportsPress lascia tutto com'è
                the_content();
            endif;

        endwhile;
    else :
        echo '<p>Nessun contenuto trovato.</p>';
    endif;
    ?>
</main>

<?php get_footer(); ?>