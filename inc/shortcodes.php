<?php
/**
 * Universal Sport Sovizzo - Shortcode Library
 * Questo file contiene tutti gli shortcode personalizzati del tema.
 */

// ==========================================================================
// 1. GESTIONE SPONSOR
// ==========================================================================
/**
 * Lista sponsor con gerarchia.
 * @param livello (opzionale) - Il nome dello slug della tassonomia (es: "main", "official").
 * @param dimensione (opzionale) - La classe CSS per la dimensione (es: "medium", "large").
 * Esempio: [lista_sponsor livello="main" dimensione="large"]
 */
function universal_sport_sponsors_shortcode($atts) {
    $a = shortcode_atts( array(
        'livello' => '',
        'dimensione' => 'medium'
    ), $atts );

    $args = array(
        'post_type'      => 'sponsor',
        'posts_per_page' => -1, 
        'orderby'        => 'title',
        'order'          => 'ASC'
    );

    if ( !empty($a['livello']) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'livello_sponsor',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($a['livello']),
            ),
        );
    }

    $sponsor_query = new WP_Query( $args );
    $output = '<div class="dynamic-sponsor-grid">';
    
    if ( $sponsor_query->have_posts() ) {
        while ( $sponsor_query->have_posts() ) {
            $sponsor_query->the_post();
            
            if ( has_post_thumbnail() ) {
                $sponsor_url = trim( wp_strip_all_tags( get_the_content() ) );
                $sponsor_name = get_the_title(); 
                
                $output .= '<div class="dynamic-sponsor-logo size-' . esc_attr($a['dimensione']) . '" title="' . esc_attr( $sponsor_name ) . '">';
                
                $alt_text = $sponsor_name;
                if ( empty( $sponsor_url ) || ! filter_var( $sponsor_url, FILTER_VALIDATE_URL ) ) {
                    $alt_text = 'Logo ' . $sponsor_name;
                }
                
                if ( ! empty( $sponsor_url ) && filter_var( $sponsor_url, FILTER_VALIDATE_URL ) ) {
                    $output .= '<a href="' . esc_url( $sponsor_url ) . '" target="_blank" rel="noopener noreferrer" title="Visita il sito di ' . esc_attr( $sponsor_name ) . '">';
                }
                
                $output .= get_the_post_thumbnail( get_the_ID(), 'medium_large', array( 'alt' => $alt_text ) );
                
                if ( ! empty( $sponsor_url ) && filter_var( $sponsor_url, FILTER_VALIDATE_URL ) ) {
                    $output .= '</a>';
                }
                
                $output .= '</div>';
            }
        }
        wp_reset_postdata(); 
    } else {
        $output .= '<p class="text-center">Stiamo aggiornando questa sezione. Torna presto!</p>';
    }
    
    $output .= '</div>';
    return $output;
}
add_shortcode( 'lista_sponsor', 'universal_sport_sponsors_shortcode' );

// ==========================================================================
// 2. NAVIGAZIONE E UI SPORTIVA
// ==========================================================================
/**
 * Menu sub-nav per le pagine sportive.
 * Memorizza il parametro 'comp' nell'URL per navigare tra le competizioni.
 * Esempio: [nav_sportiva]
 */
function sport_nav_shortcode() {
    $output = '<nav class="sport-sub-nav"><ul>';
    $menu_items = array(
        'partite-giocate'  => 'Partite Giocate',
        'prossime-partite' => 'Prossime Partite',
        'classifica'       => 'Classifica',
        'marcatori'        => 'Marcatori'
    );

    $default_comp = uss_get_default_comp();
    $active_comp = isset($_GET['comp']) ? sanitize_text_field($_GET['comp']) : '';
    
    // Costruisce il parametro SOLO se non è la competizione di default
    $comp_param = (!empty($active_comp) && $active_comp !== $default_comp) ? '?comp=' . $active_comp : '';

    foreach ($menu_items as $slug => $label) {
        $url = home_url('/' . $slug . '/') . $comp_param;
        
        if ( is_page($slug) ) {
            $output .= '<li><span class="active-sport-link">' . $label . '</span></li>';
        } else {
            $output .= '<li><a href="' . $url . '">' . $label . '</a></li>';
        }
    }
    $output .= '</ul></nav>';
    return $output;
}
add_shortcode('nav_sportiva', 'sport_nav_shortcode');

/**
 * H1 Dinamico con titolo pagina, nome competizione e badge stato.
 * Recupera automaticamente lo stato della competizione dal database.
 * Esempio: [titolo_dinamico]
 */
function uss_titolo_dinamico_shortcode($atts) {
    $active_comp = isset($_GET['comp']) ? sanitize_text_field($_GET['comp']) : uss_get_default_comp();

    $page_title = get_the_title();

    // SE LA COMPETIZIONE È "TUTTE", MOSTRIAMO SOLO IL TITOLO PULITO
    if ( $active_comp === 'tutte' ) {
        $output = '<header class="page-header">';
        $output .= '<h1 class="page-title">' . esc_html($page_title) . '</h1>';
        $output .= '<div class="title-underline" style="margin-top: 15px;"></div>';
        $output .= '</header>';
        return $output;
    }
    
    $league = get_term_by('slug', $active_comp, 'sp_league');
    $comp_name = $league ? $league->name : '';
    
    // Recupera lo stato dal database (se vuoto, fallback su in_corso)
    $status = $league ? get_term_meta($league->term_id, 'league_status', true) : 'in_corso';
    
    // Crea il badge in base allo stato
    $badge_html = '';
    if ($status === 'in_corso') {
        $badge_html = '<span class="status-badge badge-corso" role="status" aria-label="Stato della competizione: In corso"><i class="fa-solid fa-play" aria-hidden="true"></i> In corso</span>';
    } elseif ($status === 'finita') {
        $badge_html = '<span class="status-badge badge-finita" role="status" aria-label="Stato della competizione: Conclusa"><i class="fa-solid fa-check" aria-hidden="true"></i> Conclusa</span>';
        //$badge_html = '<span class="status-badge badge-finita"><i class="fa-solid fa-flag-checkered" aria-hidden="true"></i> Conclusa</span>';
    } elseif ($status === 'non_iniziata') {
        $badge_html = '<span class="status-badge badge-attesa" role="status" aria-label="Stato della competizione: In arrivo"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i> In arrivo</span>';
        //$badge_html = '<span class="status-badge badge-attesa"><i class="fa-solid fa-clock" aria-hidden="true"></i> In arrivo</span>';
    }
    
    $output = '<header class="page-header">';
    // 1. IL TITOLO (Puro e pulito per Google)
    $output .= '<h1 class="page-title h1-sportspress">' . esc_html($comp_name) . ' <br><span class="subtitle-comp">' . esc_html($page_title) . '</span></h1>';
    
    // 2. IL BADGE (Fuori dall'H1, con un div contenitore per distanziarlo)
    $output .= '<div class="badge-wrapper">' . $badge_html . '</div>';
    
    // 3. LA RIGA
    $output .= '<div class="title-underline"></div>';
    $output .= '</header>';
    
    return $output;
}
add_shortcode('titolo_dinamico', 'uss_titolo_dinamico_shortcode');

/**
 * Selettore pillole dinamico per cambiare competizione.
 * Mostra solo le competizioni che non sono state impostate come "nascoste".
 * Esempio: [selettore_competizioni]
 */
function uss_selettore_competizioni($atts) {
    $a = shortcode_atts(array(
        'default' => uss_get_default_comp()
    ), $atts);

    $leagues = get_terms(array(
        'taxonomy'   => 'sp_league',
        'hide_empty' => false,
        'parent'     => 0, // MAGIA 1: Pesca SOLO le competizioni principali (genitori)
        'meta_query' => array(
            'relation' => 'OR', // MAGIA 2: Prendi la lega SE...
            array(
                'key'     => 'league_hide',
                'compare' => 'NOT EXISTS' // ...non ha mai avuto la spunta "Nascondi"
            ),
            array(
                'key'     => 'league_hide',
                'value'   => '1',
                'compare' => '!='         // ...oppure se la spunta è stata tolta
            )
        )
    ));

    // Se c'è un errore, se è vuoto, o se c'è UNA SOLA competizione visibile, nasconde il selettore
    if (is_wp_error($leagues) || count($leagues) <= 1) return '';

    $active_comp = isset($_GET['comp']) ? sanitize_text_field($_GET['comp']) : $a['default'];

    $output = '<div class="competition-selector">';
    $output .= '<span class="comp-label"><i class="fa-solid fa-trophy" aria-hidden="true"></i> Competizione:</span>';

    // URL base pulito (es. /partite-giocate/)
    $base_url = strtok($_SERVER["REQUEST_URI"], '?');

    foreach ($leagues as $league) {
        
        if ($league->slug === $active_comp) {
            // Pillola Attiva: Usa uno span, disattivando il link
            $output .= '<span class="comp-pill active">' . esc_html($league->name) . '</span>';
        } else {
            // Pillola Inattiva: Link normale per cambiare competizione
            
            if ( $league->slug === $a['default'] ) {
                $url = esc_url($base_url);
            } else {
                $url = esc_url(add_query_arg('comp', $league->slug, $base_url));
            }
            
            $output .= '<a href="' . $url . '" class="comp-pill">' . esc_html($league->name) . '</a>';
        }
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('selettore_competizioni', 'uss_selettore_competizioni');

// ==========================================================================
// 3. EVENTI E PARTITE
// ==========================================================================
/**
 * Lista partite stile "Manchester City".
 * Visualizza partite passate o future, raggruppate per mese.
 * Esempio: [partite_mancity team="123" status="past" limit="10"]
 */
function universal_sport_mancity_style($atts) {
    
    $a = shortcode_atts(array(
        'team'           => '', 
        'status'         => 'past', 
        'limit'          => -1, 
        'show_month'     => 'yes', 
        'show_team_logo' => 'yes',
        'show_venue'     => 'no',
        'default_comp'   => uss_get_default_comp() // Slug del campionato di default da modificare quando lo cambiamo!!
    ), $atts);
    
    $team_id = intval($a['team']);
    if(!$team_id) return '<p class="text-center">Inserisci l\'ID della squadra.</p>';

    $args = array(
        'post_type'      => 'sp_event',
        'posts_per_page' => $a['limit'],
        'meta_query'     => array(
            array(
                'key'     => 'sp_team',
                'value'   => $team_id,
                'compare' => '='
            )
        )
    );

    // IL METODO CORRETTO DI SPORTSPRESS: 'publish' = passate, 'future' = prossime
    if($a['status'] == 'past') {
        $args['post_status'] = 'publish';
        $args['order'] = 'DESC'; // Dal più recente al più vecchio
    } else {
        $args['post_status'] = 'future';
        $args['order'] = 'ASC'; // Dal prossimo in poi
    }

    // FIX DINAMICO: Filtra le partite in base al campionato selezionato
    $active_comp = isset($_GET['comp']) ? sanitize_text_field($_GET['comp']) : $a['default_comp'];
    
    if(!empty($active_comp) && $active_comp !== 'tutte') {
        $is_numeric = is_numeric($active_comp);
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'sp_league',
                'field'    => $is_numeric ? 'term_id' : 'slug',
                'terms'    => $is_numeric ? intval($active_comp) : $active_comp,
            )
        );
    }

    $query = new WP_Query($args);
    
    $output = '<div class="mancity-fixtures">';
    $current_month = '';

    if($query->have_posts()){
        while($query->have_posts()){
            $query->the_post();
            $event_id = get_the_ID();
            
            // Raggruppamento per mese
            if($a['show_month'] == 'yes') {
                $event_month = get_the_date('F Y', $event_id);
                if($event_month !== $current_month) {
                    $output .= '<h2 class="mancity-month">' . strtoupper($event_month) . '</h2>';
                    $current_month = $event_month;
                }
            }

            // Ottieni l'estratto della partita 
            $event_excerpt = get_the_excerpt($event_id);

            // Ottieni le due squadre
            $teams = get_post_meta($event_id, 'sp_team', false); 
            if(count($teams) < 2) continue; 
            $home_id = $teams[0];
            $away_id = $teams[1];

            $home_name = get_the_title($home_id);
            $away_name = get_the_title($away_id);
            
            // Applica il controllo sul parametro show_team_logo
            $home_logo = ($a['show_team_logo'] == 'yes') ? get_the_post_thumbnail($home_id, 'thumbnail') : '';
            $away_logo = ($a['show_team_logo'] == 'yes') ? get_the_post_thumbnail($away_id, 'thumbnail') : '';

            // Calcolo Risultati
            $results = get_post_meta($event_id, 'sp_results', true);
            $home_score = '-'; $away_score = '-';
            
            if(is_array($results)) {
                if(isset($results[$home_id]) && is_array($results[$home_id])) { 
                    $home_numbers = array_filter($results[$home_id], 'is_numeric');
                    if(!empty($home_numbers)) $home_score = end($home_numbers); 
                }
                if(isset($results[$away_id]) && is_array($results[$away_id])) { 
                    $away_numbers = array_filter($results[$away_id], 'is_numeric');
                    if(!empty($away_numbers)) $away_score = end($away_numbers); 
                }
            }
            // Calcola W / D / L
            $wdl = ''; $wdl_class = '';
            if($a['status'] == 'past' && $home_score !== '-' && $away_score !== '-') {
                $my_score = ($team_id == $home_id) ? $home_score : $away_score;
                $opp_score = ($team_id == $home_id) ? $away_score : $home_score;
                if($my_score > $opp_score) { $wdl = 'W'; $wdl_class = 'win'; }
                elseif($my_score < $opp_score) { $wdl = 'L'; $wdl_class = 'loss'; }
                else { $wdl = 'D'; $wdl_class = 'draw'; }
            }


            $event_day = get_the_date('D d M', $event_id);
            $event_time = get_the_time('H:i', $event_id);
            $permalink = get_permalink();
            
            $output .= '<div class="mancity">';
            $output .= '<div class="mc-excerpt">' . esc_html($event_excerpt) . '</div>';
            // COSTRUZIONE DELLA RIGA
            $output .= '<div class="mancity-row">';
            $output .= '<div class="mc-date"><strong>' . $event_day . '</strong><br><small>' . $event_time . '</small></div>';
            
            if($wdl) { $output .= '<div class="mc-badge"><span class="badge-' . $wdl_class . '">' . $wdl . '</span></div>'; } 
            else { $output .= '<div class="mc-badge"></div>'; }

            $output .= '<div class="mc-match">';
            $output .= '<div class="mc-team mc-home"><span class="sr-only-mobile">' . $home_name . '</span> ' . $home_logo . '</div>';
            
            if($a['status'] == 'past') {
                $output .= '<div class="mc-score">' . $home_score . ' <span>-</span> ' . $away_score . '</div>';
            } else {
                $output .= '<div class="mc-score time">' . $event_time . '</div>';
            }
            $output .= '<div class="mc-team mc-away">' . $away_logo . ' <span class="sr-only-mobile">' . $away_name . '</span></div>';
            $output .= '</div>';

            $link_text = ($a['status'] == 'past') ? 'REPORT <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>' : 'DETTAGLI <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>';
            $output .= '<div class="mc-links"><a href="' . $permalink . '">' . $link_text . '</a></div>';
            
            $output .= '</div></div>';
        }
        wp_reset_postdata();
    } else {
        // MESSAGGI DINAMICI A SECONDA DI DOVE SIAMO
        if ( is_page( 'partite-giocate' ) ) {
            $output .= '<p class="text-center no-matches">Non ci sono partite giocate per questa competizione.</p>';
        } elseif ( is_page( 'prossime-partite' ) ) {
            $output .= '<p class="text-center no-matches">Non ci sono partite programmate per questa competizione.</p>';
        } elseif ( is_front_page() && $a['status'] == 'future' ) {
            $output .= '<p class="text-center no-matches">Non ci sono partite in programma.</p>';
        } else {
            $output .= '<p class="text-center no-matches">Nessuna partita da mostrare al momento.</p>';
        }
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('partite_mancity', 'universal_sport_mancity_style');

// ==========================================================================
// 4. CLASSIFICHE E MARCATORI DINAMICI
// ==========================================================================
/**
 * Wrapper per [team_standings] che seleziona automaticamente la tabella giusta.
 * Recupera l'ID tabella associato al campionato selezionato tramite parametro 'comp'.
 * Esempio: [classifica_dinamica]
 */
function uss_dynamic_standings_shortcode($atts) {
    $a = shortcode_atts(array('default_comp' => uss_get_default_comp()), $atts);
    $active_comp = isset($_GET['comp']) ? sanitize_text_field($_GET['comp']) : $a['default_comp'];
    
    if (!empty($active_comp)) {
        $league_id = is_numeric($active_comp) ? intval($active_comp) : uss_get_league_id_by_slug($active_comp);
        
        if ($league_id) {
            // Cerca la tabella di SportsPress creata per questo campionato
            $table_id = uss_get_sp_post_by_league('sp_table', $league_id);
            
            if ($table_id) {
                $atts['id'] = $table_id; // Imposta dinamicamente l'ID corretto!
            } else {
                return '<div class="mancity-fixtures"><p class="text-center">Non è ancora disponibile una classifica per questa competizione.</p></div>';
            }
        }
    }

    // Pulisce l'attributo personalizzato e costruisce lo shortcode
    unset($atts['default_comp']);
    $attr_string = '';
    foreach ($atts as $key => $value) {
        if (!empty($value)) $attr_string .= $key . '="' . esc_attr($value) . '" ';
    }
    
    return do_shortcode('[team_standings ' . $attr_string . ']');
}
add_shortcode('classifica_dinamica', 'uss_dynamic_standings_shortcode');

/**
 * Wrapper per [player_list] che seleziona automaticamente la lista giusta.
 * Recupera l'ID lista giocatori associato al campionato selezionato tramite parametro 'comp'.
 * Esempio: [marcatori_dinamici]
 */
function uss_dynamic_players_shortcode($atts) {
    $a = shortcode_atts(array('default_comp' => uss_get_default_comp()), $atts);
    $active_comp = isset($_GET['comp']) ? sanitize_text_field($_GET['comp']) : $a['default_comp'];
    
    if (!empty($active_comp)) {
        $league_id = is_numeric($active_comp) ? intval($active_comp) : uss_get_league_id_by_slug($active_comp);
        
        if ($league_id) {
            // Cerca l'elenco giocatori di SportsPress creato per questo campionato
            $list_id = uss_get_sp_post_by_league('sp_list', $league_id);
            
            if ($list_id) {
                $atts['id'] = $list_id; // Imposta dinamicamente l'ID corretto!
            } else {
                return '<div class="mancity-fixtures"><p class="text-center">Non è ancora disponibile una classifica marcatori per questa competizione.</p></div>';
            }
        }
    }
    
    // Pulisce l'attributo personalizzato e costruisce lo shortcode
    unset($atts['default_comp']);
    $attr_string = '';
    foreach ($atts as $key => $value) {
        if (!empty($value)) $attr_string .= $key . '="' . esc_attr($value) . '" ';
    }
    
    return do_shortcode('[player_list ' . $attr_string . ']');
}
add_shortcode('marcatori_dinamici', 'uss_dynamic_players_shortcode');

// ==========================================================================
// 5. NEWS ED EVENTI (HOME)
// ==========================================================================
/**
 * Visualizza le ultime news pubblicate nel blog.
 * @param limite - Numero di articoli da mostrare.
 * Esempio: [ultime_news limite="4"]
 */
function uss_ultime_news_shortcode($atts) {
    $a = shortcode_atts( array( 'limite' => 4 ), $atts );
    
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => intval($a['limite']),
        'post_status'    => 'publish'
    );
    
    // MAGIA ANTI-DOPPIONE: Se siamo in un articolo singolo, escludilo dai risultati
    if ( is_single() && get_post_type() === 'post' ) {
        $args['post__not_in'] = array( get_the_ID() );
    }

    $query = new WP_Query($args);
    $output = '<div class="news-home-grid news-home-grid--latest-news">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $titolo = get_the_title();
            $link = get_permalink();
            $data = get_the_date('d M Y'); 
            $riassunto = wp_trim_words(get_the_excerpt(), 15, '...'); 
            $immagine = get_the_post_thumbnail_url(get_the_ID(), 'large');
            $alt_text = get_post_meta( get_post_thumbnail_id( get_the_ID() ), '_wp_attachment_image_alt', true );
            
            if (!$immagine) {
                $immagine_html = '<div class="news-home-img-placeholder">Nessuna immagine</div>';
            } else {
                $immagine_html = '<img src="' . esc_url($immagine) . '" alt="' . esc_attr($alt_text) . '">';
            }

            $output .= '<article class="news-home-card news-home-card--vertical">';
            $output .= '<div class="news-home-img"><a href="' . esc_url($link) . '">' . $immagine_html . '</a></div>';
            $output .= '<div class="news-home-content">';
            $output .= '<span class="news-home-date">' . esc_html($data) . '</span>';
            $output .= '<h3><a href="' . esc_url($link) . '" class="news-home-title-link">' . esc_html($titolo) . '</a></h3>';
            $output .= '<p class="news-home-excerpt">' . esc_html($riassunto) . '</p>';
            $output .= '<a href="' . esc_url($link) . '" class="read-more">Leggi tutto</a>';
            $output .= '</div></article>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p class="text-center news-home-empty">Nessuna news pubblicata al momento.</p>';
    }
    $output .= '</div>';
    return $output;
}
add_shortcode('ultime_news', 'uss_ultime_news_shortcode');


/**
 * Visualizza i prossimi eventi estratti da "The Events Calendar".
 * @param limite - Numero di eventi da mostrare.
 * Esempio: [ultimi_eventi limite="4"]
 */
function uss_prossimi_eventi_shortcode($atts) {
    $a = shortcode_atts( array( 'limite' => 4 ), $atts );
    $output = '<div class="news-home-grid">';

    if ( function_exists('tribe_get_events') ) {
        $events = tribe_get_events( array(
            'posts_per_page' => intval($a['limite']),
            'start_date'     => 'now',
        ) );

        if ( ! empty( $events ) ) {
            global $post;
            foreach ( $events as $post ) {
                setup_postdata( $post );
                $titolo = get_the_title();
                $link = get_permalink();
                $data_evento = tribe_get_start_date( get_the_ID(), false, 'd F Y' );
                $riassunto = wp_trim_words(get_the_excerpt(), 12, '...'); 
                $immagine = get_the_post_thumbnail_url(get_the_ID(), 'large');
                $alt_text = get_post_meta( get_post_thumbnail_id( get_the_ID() ), '_wp_attachment_image_alt', true );
                
                if (!$immagine) {
                    $immagine_html = '<div class="news-home-img-placeholder">Nessuna locandina</div>';
                } else {
                    $immagine_html = '<img src="' . esc_url($immagine) . '" alt="' . esc_attr($alt_text) . '">';
                }

                // Aggiunta la classe modificatore "is-event-card"
                $output .= '<article class="news-home-card is-event-card">';
                $output .= '<div class="news-home-img"><a href="' . esc_url($link) . '">' . $immagine_html . '</a></div>';
                $output .= '<div class="news-home-content">';
                
                // Aggiunta la classe modificatore "is-event-date"
                $output .= '<span class="news-home-date is-event-date">' . strtoupper(esc_html($data_evento)) . '</span>';
                $output .= '<h3><a href="' . esc_url($link) . '" class="news-home-title-link is-event-title-link">' . esc_html($titolo) . '</a></h3>';
                $output .= '<p class="news-home-excerpt">' . esc_html($riassunto) . '</p>';
                $output .= '<a href="' . esc_url($link) . '" class="read-more is-event-link">Scopri di più</a>';
                $output .= '</div></article>';
            }
            wp_reset_postdata();
        } else {
            $output .= '<p class="text-center news-home-empty">Nessun evento in programma al momento.</p>';
        }
    } else {
         $output .= '<p class="text-center news-home-empty">Il plugin The Events Calendar non è attivo.</p>';
    }
    $output .= '</div>';
    return $output;
}
add_shortcode('ultimi_eventi', 'uss_prossimi_eventi_shortcode');

// ==========================================================================
// 6. UTILITÀ TESTUALI
// ==========================================================================
/**
 * Shortcode per inserire icone FontAwesome nei testi.
 * @param nome - Il nome dell'icona (es: "fa-arrow-right", "fa-video", "fa-camera").
 * @param stile - Lo stile dell'icona (es: "fa-solid", "fa-regular").
 * @param colore - Il colore (es: "var(--secondary-orange)" o "#ff0000").
 * Esempio: [icona nome="fa-arrow-right" colore="var(--secondary-orange)"] Testo...
 */
function uss_icona_shortcode($atts) {
    $a = shortcode_atts( array(
        'nome'   => 'fa-arrow-right', // Icona di default se non specifichi nulla
        'stile'  => 'fa-solid',       // Stile di default
        'colore' => 'inherit'         // Colore di default (prende quello del testo)
    ), $atts );
    
    return '<i class="' . esc_attr($a['stile']) . ' ' . esc_attr($a['nome']) . '" style="color: ' . esc_attr($a['colore']) . '; margin-right: 5px;"></i>';
}
add_shortcode('icona', 'uss_icona_shortcode');

/* ==========================================================================
   SHORTCODE HOME PAGE: EVENTI SPLIT LAYOUT + CALENDARIO AJAX
   Uso: [eventi_home_split limite="2"]
   ========================================================================== */
add_shortcode('eventi_home_split', 'shortcode_eventi_split_layout_custom');
function shortcode_eventi_split_layout_custom($atts) {
    $a = shortcode_atts(array(
        'limite' => 2
    ), $atts);

    ob_start();
    ?>
    <div class="eventi-split-container">
        
        <!-- COLONNA SINISTRA: Lista Eventi -->
        <div class="eventi-lista-compatta">
            <?php
            $events_args = array(
                'post_type'      => 'tribe_events',
                'posts_per_page' => intval($a['limite']),
                'post_status'    => 'publish',
                'meta_key'       => '_EventStartDate',
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
                'meta_query' => array(
                    array(
                        'key'     => '_EventStartDate',
                        'value'   => date('Y-m-d H:i:s'),
                        'compare' => '>=',
                        'type'    => 'DATETIME'
                    )
                )
            );
            $events_query = new WP_Query($events_args);

            if ($events_query->have_posts()) {
                while ($events_query->have_posts()) {
                    $events_query->the_post();
                    $event_id = get_the_ID();
                    
                    $start_date = get_post_meta($event_id, '_EventStartDate', true);
                    $formatted_date = strtoupper(date_i18n('d M Y', strtotime($start_date)));
                    $formatted_time = date_i18n('H:i', strtotime($start_date));
                    $testo_pulito = wp_strip_all_tags(get_the_excerpt());
                    $excerpt = mb_strimwidth($testo_pulito, 0, 70, '...');
                    
                    $link = get_permalink();
                    $title = get_the_title();
                    $image_url = get_the_post_thumbnail_url($event_id, 'medium'); 

                    echo '<article class="evento-compatto-card">';
                    
                    if ($image_url) {
                        echo '<a href="' . esc_url($link) . '" class="evento-compatto-img" style="background-image: url(' . esc_url($image_url) . ');" aria-label="' . esc_attr('Locandina evento: ' . $title) . '"></a>';
                    } else {
                        echo '<a href="' . esc_url($link) . '" class="evento-compatto-img placeholder-img" aria-label="' . esc_attr('Vai all\'evento: ' . $title) . '"><i class="fas fa-calendar-alt" aria-hidden="true"></i></a>';
                    }
                    
                    echo '<div class="evento-compatto-info">';
                    echo '<span class="evento-compatto-data">' . esc_html($formatted_date) . ' - ' . esc_html($formatted_time) . '</span>';
                    echo '<h4 class="evento-compatto-titolo"><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h4>';
                    if (!empty($excerpt)) {
                        echo '<p class="evento-compatto-excerpt">' . esc_html($excerpt) . '</p>';
                    }
                    echo '<a href="' . esc_url($link) . '" class="read-more">Scopri di più &rarr;</a>';
                    echo '</div>';
                    
                    echo '</article>';
                }
                wp_reset_postdata();
            } else {
                echo '<p>Nessun appuntamento in programma.</p>';
            }
            ?>
        </div>

        <!-- COLONNA DESTRA: Calendario AJAX -->
        <div class="eventi-calendario-widget">
            <div id="uss-calendar-ajax-wrap">
                <?php 
                // Al primo caricamento della pagina, stampa il mese corrente
                echo uss_generate_mini_calendar(date('m'), date('Y')); 
                ?>
            </div>
            
            <!-- SCRIPT AJAX PER IL CAMBIO MESE (Transizione Morbida) -->
            <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '.ajax-cal-btn', function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    var targetMonth = btn.data('m');
                    var targetYear = btn.data('y');
                    var wrap = $('#uss-calendar-ajax-wrap');

                    // Abbassa leggermente l'opacità durante il caricamento per feedback visivo
                    wrap.css({'opacity': '0.5', 'pointer-events': 'none', 'transition': 'opacity 0.2s'});

                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'uss_load_calendar',
                            month: targetMonth,
                            year: targetYear
                        },
                        success: function(response) {
                            wrap.html(response);
                            wrap.css({'opacity': '1', 'pointer-events': 'auto'});
                        },
                        error: function() {
                            // In caso di errore ripristina la visibilità
                            wrap.css({'opacity': '1', 'pointer-events': 'auto'});
                        }
                    });
                });
            });
            </script>
        </div>

    </div>
    <?php
    return ob_get_clean();
}

?>