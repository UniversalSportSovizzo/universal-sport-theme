<?php
/* Template Name: Archivio News Completo */
get_header();
?>

<main id="contenuto-principale" class="container">
    <?php while ( have_posts() ) : the_post(); ?>
        
        <header class="page-header">
            <h1 class="page-title"><?php the_title(); ?></h1>
            <div class="title-underline"></div>
        </header>

        <article class="page-content">
            <div class="entry-content">
                <?php 
                // 0. CONTROLLO INIZIALE: Esistono articoli nel sito?
                $total_posts = wp_count_posts('post');
                if ( !isset($total_posts->publish) || $total_posts->publish == 0 ) {
                    // Se non c'è nessuna news in assoluto, mostra il messaggio e salta il resto
                    echo '<div class="news-archive-empty text-center" style="margin: 80px auto; max-width: 650px;">';
                    echo '<i class="fa-solid fa-newspaper" style="font-size: 3.5rem; color: var(--accent-yellow); margin-bottom: 25px;"></i>';
                    echo '<h2 style="color: var(--primary-blue); margin-bottom: 15px;">I nostri racconti arriveranno presto!</h2>';
                    echo '<p style="color: #555; font-size: 1.15rem;">Stiamo scaldando i motori per l\'inizio ufficiale. Torna a trovarci tra qualche giorno per leggere tutte le novità, i risultati e le storie dell\'Associazione.</p>';
                    echo '</div>';
                
                } else {
                    // SE CI SONO NEWS, ESEGUI TUTTA LA LOGICA:
                    
                    // 1. Recupero parametri (Pagina attuale, Ricerca, Categorie Multiple)
                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                    $search_query = isset($_GET['cerca']) ? sanitize_text_field($_GET['cerca']) : '';
                    
                    // Recuperiamo la stringa delle categorie (es. "48,50") e la trasformiamo in un array di numeri
                    $cat_raw = isset($_GET['categoria']) ? sanitize_text_field($_GET['categoria']) : '';
                    $current_cats = !empty($cat_raw) ? array_map('intval', explode(',', $cat_raw)) : array();

                    // 2. Costruzione della Topbar (Ricerca + Filtri)
                    echo '<div class="news-archive-topbar">';
                    echo '<form method="GET" action="' . esc_url(get_permalink()) . '" class="news-filter-form">';
                    
                    // Se ci sono categorie selezionate, le manteniamo come campo nascosto per la barra di ricerca
                    if (!empty($current_cats)) {
                        echo '<input type="hidden" name="categoria" value="' . esc_attr(implode(',', $current_cats)) . '">';
                    }

                    echo '<div class="news-search-box">';
                    echo '<label for="ricerca-news" class="sr-only">Cerca tra gli articoli</label>';
                    echo '<input type="text" id="ricerca-news" name="cerca" placeholder="Cerca tra gli articoli..." value="' . esc_attr($search_query) . '">';
                    echo '<button type="submit" class="btn-all-events btn-cerca"><i class="fas fa-search"></i> Cerca</button>';
                    
                    if (!empty($search_query) || !empty($current_cats)) {
                        $reset_url = remove_query_arg(array('categoria', 'cerca', 'paged'));
                        echo '<a href="' . esc_url($reset_url) . '" class="news-reset-btn"><i class="fas fa-times"></i> Azzera</a>';
                    }
                    echo '</div>';

                    // Filtri Categorie Dinamici (Sistema a Toggle / Multi-selezione)
                    $categories = get_categories(array('hide_empty' => true));
                    // Bottone a tendina visibile solo su mobile
                    echo '<button type="button" class="mobile-filter-toggle"><i class="fas fa-filter"></i> Seleziona categorie <i class="fas fa-chevron-down toggle-icon" style="margin-left:auto;"></i></button>';

                    // Contenitore delle categorie
                    echo '<div class="news-category-filters">';
                    
                    // Bottone "Tutte"
                    if (empty($current_cats)) {
                        echo '<span class="news-cat-link active">Tutte</span>';
                    } else {
                        $url_tutte = remove_query_arg(array('categoria', 'paged'));
                        echo '<a href="' . esc_url($url_tutte) . '" class="news-cat-link">Tutte</a>';
                    }
                    
                    // Memorizziamo l'URL base pulito dalla paginazione
                    $base_url = remove_query_arg('paged');
                    
                    foreach ($categories as $category) {
                        $cat_id = $category->term_id;
                        $new_cats = $current_cats; // Creiamo una copia dell'array corrente
                        
                        // Logica di Toggle (Accendi/Spegni)
                        if (in_array($cat_id, $new_cats)) {
                            // Se era già selezionata, la rimuoviamo dall'array
                            $new_cats = array_diff($new_cats, array($cat_id));
                            $is_active = true;
                        } else {
                            // Se non era selezionata, la aggiungiamo all'array
                            $new_cats[] = $cat_id;
                            $is_active = false;
                        }
                        
                        // Costruiamo l'URL finale per questo specifico bottone
                        if (empty($new_cats)) {
                            $cat_url = remove_query_arg('categoria', $base_url);
                        } else {
                            $cat_url = add_query_arg('categoria', implode(',', $new_cats), $base_url);
                        }
                        
                        $active_class = $is_active ? ' active' : '';
                        
                        // Ora usiamo sempre il tag <a> anche per le attive, così possono essere cliccate per essere disattivate!
                        echo '<a href="' . esc_url($cat_url) . '" class="news-cat-link' . $active_class . '">' . esc_html($category->name) . '</a>';
                    }
                    echo '</div>';
                    echo '</form>';
                    echo '</div>';

                    // 3. Query degli Articoli
                    $args = array(
                        'post_type' => 'post',
                        'posts_per_page' => 10,
                        'paged' => $paged,
                    );
                    
                    if (!empty($search_query)) {
                        $args['s'] = $search_query;
                    }
                    
                    // Utilizziamo 'category__in' per filtrare array multipli (Logica OR: mostra post che hanno la cat A oppure la B)
                    if (!empty($current_cats)) {
                        $args['category__in'] = $current_cats;
                    }

                    $query = new WP_Query($args);

                    // 4. Stampa la Griglia
                    echo '<div class="news-archive-grid">';
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();
                            $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large') ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '/wp-content/uploads/placeholder.jpg';
                            $date = get_the_date('d M Y');
                            $title = get_the_title();
                            $excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
                            $link = get_permalink();
                            $alt_text = get_post_meta( get_post_thumbnail_id( get_the_ID() ), '_wp_attachment_image_alt', true );

                            echo '<article class="news-home-card">';
                            echo '<div class="news-home-img"><a href="' . esc_url($link) . '"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($alt_text) . '"></a></div>';
                            echo '<div class="news-home-content">';
                            echo '<span class="news-home-date">' . esc_html($date) . '</span>';
                            echo '<h3><a href="' . esc_url($link) . '" class="news-home-title-link">' . esc_html($title) . '</a></h3>';
                            echo '<p class="news-home-excerpt">' . esc_html($excerpt) . '</p>';
                            echo '<a href="' . esc_url($link) . '" class="read-more">Leggi tutto</a>';
                            echo '</div>';
                            echo '</article>';
                        }
                    } else {
                        // Messaggio di errore tecnico se i filtri/ricerca non trovano nulla
                        echo '<p class="text-center news-no-results">Nessun articolo trovato con questi criteri di ricerca. Prova a modificare i filtri.</p>';
                    }
                    echo '</div>';

                    // 5. Paginazione Numerica
                    if ($query->max_num_pages > 1) {
                        echo '<div class="news-pagination">';
                        echo paginate_links(array(
                            'total' => $query->max_num_pages,
                            'current' => $paged,
                            'prev_text' => '<span class="screen-reader-text">Pagina precedente</span>&laquo;',
                            'next_text' => '<span class="screen-reader-text">Pagina successiva</span>&raquo;',
                        ));
                        echo '</div>';
                    }
                    
                    wp_reset_postdata();
                } // Fine dell'ELSE
                ?>
            </div>
        </article>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>