<?php
/**
 * Universal Sport Sovizzo functions and definitions
 */

// IL CUORE DEL SITO: Cambia questo slug per aggiornare l'intero sito alla nuova stagione!
function uss_get_default_comp() {
    return '1-rassegna-calcio-universale';
}

// FUNZIONE UNIFICATA: Contiene sia le immagini in evidenza che i menu
function universal_sport_setup() {
    // Abilita le immagini in evidenza per post, pagine e squadre
    add_theme_support( 'post-thumbnails' );

    // Opzionale: aggiunge il supporto per il titolo della scheda nel browser
    add_theme_support( 'title-tag' );

    // Registra la "posizione" del menu nel tema
    register_nav_menus( array(
        'primary' => 'Menu Principale Alto',
    ) );
}
add_action( 'after_setup_theme', 'universal_sport_setup' );

// Questa funzione dice a WordPress quali file CSS e JS caricare
function universal_sport_scripts() {
    // Carica il CSS
    wp_enqueue_style( 'universal-sport-style', get_stylesheet_uri() );

    // Carica il nostro nuovo JavaScript per lo slider
    wp_enqueue_script( 'universal-sport-slider', get_template_directory_uri() . '/assets/js/custom-slider.js', array(), '1.0', true );

    // Carica il CSS per la stampa (solo quando l'utente stampa)
    wp_enqueue_style( 'universal-sport-print-style', get_stylesheet_directory_uri() . '/print.css', array(), '1.0', 'print' );
}
// Aggancia questa funzione al momento in cui WordPress prepara la pagina
add_action( 'wp_enqueue_scripts', 'universal_sport_scripts' );


// Rimuove l'attributo href dal link della pagina corrente nel menu
function universal_sport_disable_current_link( $atts, $item, $args ) {
    // Se l'elemento del menu corrisponde alla pagina che sto visitando...
    if ( $item->current ) {
        // Rimuovi l'attributo href
        unset( $atts['href'] );
    }
    return $atts;
}
// Aggancia questa funzione alla creazione degli attributi dei link del menu
add_filter( 'nav_menu_link_attributes', 'universal_sport_disable_current_link', 10, 3 );

// Aggiunge opzioni al Personalizzatore di WordPress
function universal_sport_customize_register( $wp_customize ) {
    
    // 1. Crea una sezione chiamata "Immagini Home"
    $wp_customize->add_section( 'universal_hero_section' , array(
        'title'      => 'Immagine Hero Home',
        'priority'   => 30,
    ) );

    // 2. Crea l'impostazione per l'immagine
    $wp_customize->add_setting( 'hero_background_image', array(
        'default'   => get_template_directory_uri() . '/assets/img/vittoria.jpg', // Immagine di fallback
        'transport' => 'refresh',
    ) );

    // 3. Crea il controllo per caricare l'immagine (il tasto "Sfoglia")
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_background_image', array(
        'label'      => 'Carica l\'immagine della vittoria',
        'section'    => 'universal_hero_section',
        'settings'   => 'hero_background_image',
    ) ) );
}
add_action( 'customize_register', 'universal_sport_customize_register' );

// ==========================================
// 5. SHORTCODE SINCRONIZZATO PER GLI SPONSOR CON GERARCHIA E DIMENSIONI
// ==========================================
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

// Menu di navigazione dinamico per la sezione sportiva (MEMORIZZA LA COMPETIZIONE)
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

// Shortcode per l'H1 Dinamico (Titolo Pagina + Competizione + Badge)
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
    $output .= '<h1 class="page-title h1-sportspress">' . esc_html($page_title) . ' <br><span class="subtitle-comp">' . esc_html($comp_name) . '</span></h1>';
    
    // 2. IL BADGE (Fuori dall'H1, con un div contenitore per distanziarlo)
    $output .= '<div class="badge-wrapper">' . $badge_html . '</div>';
    
    // 3. LA RIGA
    $output .= '<div class="title-underline"></div>';
    $output .= '</header>';
    
    return $output;
}
add_shortcode('titolo_dinamico', 'uss_titolo_dinamico_shortcode');

// Selettore Pillole Dinamiche (Senza Link Circolari e Senza URL Sporchi)
function uss_selettore_competizioni($atts) {
    $a = shortcode_atts(array(
        'default' => uss_get_default_comp()
    ), $atts);

    $leagues = get_terms(array(
        'taxonomy'   => 'sp_league',
        'hide_empty' => false,
        'parent'     => 0 // MAGIA: Pesca SOLO le competizioni principali (genitori)
    ));

    // Se c'è un errore, se è vuoto, o se c'è UNA SOLA competizione, nasconde il selettore
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
            
            // LA MAGIA È QUI: Se è la competizione di default, il link torna pulito!
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

/* ==========================================
   SHORTCODE PARTITE STILE MANCHESTER CITY
   ========================================== */
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
            $output .= '<p class="text-center">Non ci sono partite giocate per questa competizione.</p>';
        } elseif ( is_page( 'prossime-partite' ) ) {
            $output .= '<p class="text-center">Non ci sono partite programmate per questa competizione.</p>';
        } elseif ( is_front_page() && $a['status'] == 'future' ) {
            $output .= '<p class="text-center">Non ci sono partite in programma.</p>';
        } else {
            $output .= '<p class="text-center">Nessuna partita da mostrare al momento.</p>';
        }
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('partite_mancity', 'universal_sport_mancity_style');

/* ==========================================================================
   CLASSIFICHE E MARCATORI DINAMICI
   ========================================================================== */

// 1. Il traduttore (Trova l'ID della lega dallo slug)
function uss_get_league_id_by_slug($slug) {
    $league = get_term_by('slug', $slug, 'sp_league');
    return $league ? $league->term_id : '';
}

// 2. FUNZIONE CHIAVE: Trova l'ID della Tabella o Lista associata a un Campionato
function uss_get_sp_post_by_league($post_type, $league_id) {
    $args = array(
        'post_type'      => $post_type,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'sp_league',
                'field'    => 'term_id',
                'terms'    => $league_id,
            )
        )
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        return $query->posts[0]->ID; // Ritorna l'ID esatto della tabella trovata!
    }
    return false;
}

// 3. Wrapper per la Classifica Squadre
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

// 4. Wrapper per i Marcatori
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

/* ==========================================
   RINOMINA "EVENTI" DI SPORTSPRESS IN "PARTITE"
   ========================================== */
function universal_sport_rename_sp_events( $args, $post_type ) {
    if ( 'sp_event' === $post_type ) {
        $args['labels']['name']               = 'Partite';
        $args['labels']['singular_name']      = 'Partita';
        $args['labels']['menu_name']          = 'Partite'; /* Aggiunto "Sport" per distinguerlo a colpo d'occhio */
        $args['labels']['all_items']          = 'Tutte le partite';
        $args['labels']['add_new']            = 'Aggiungi nuova';
        $args['labels']['add_new_item']       = 'Aggiungi nuova partita';
        $args['labels']['edit_item']          = 'Modifica partita';
        $args['labels']['new_item']           = 'Nuova partita';
        $args['labels']['view_item']          = 'Vedi partita';
        $args['labels']['search_items']       = 'Cerca partite';
        $args['labels']['not_found']          = 'Nessuna partita trovata';
        $args['labels']['not_found_in_trash'] = 'Nessuna partita nel cestino';
    }
    return $args;
}
add_filter( 'register_post_type_args', 'universal_sport_rename_sp_events', 20, 2 );

/* ==========================================
   RINOMINA STADI IN CAMPI (SPORTSPRESS)
   ========================================== */
add_filter( 'register_taxonomy_args', 'uss_rinomina_stadi_sportspress', 10, 2 );
function uss_rinomina_stadi_sportspress( $args, $taxonomy ) {
    // Controlliamo se stiamo modificando la tassonomia dei luoghi di SportsPress
    if ( 'sp_venue' === $taxonomy ) {
        $args['labels']['name']                       = 'Campi';
        $args['labels']['singular_name']              = 'Campo';
        $args['labels']['menu_name']                  = 'Campi';
        $args['labels']['all_items']                  = 'Tutti i campi';
        $args['labels']['edit_item']                  = 'Modifica campo';
        $args['labels']['view_item']                  = 'Vedi campo';
        $args['labels']['update_item']                = 'Aggiorna campo';
        $args['labels']['add_new_item']               = 'Aggiungi nuovo campo';
        $args['labels']['new_item_name']              = 'Nuovo nome campo';
        $args['labels']['search_items']               = 'Cerca campi';
        $args['labels']['popular_items']              = 'Campi popolari';
        $args['labels']['not_found']                  = 'Nessun campo trovato';
        $args['labels']['no_terms']                   = 'Nessun campo';
        $args['labels']['items_list']                 = 'Elenco campi';
        $args['labels']['items_list_navigation']      = 'Navigazione elenco campi';
    }
    return $args;
}

/* ==========================================
   ACCESSIBILITÀ TABELLE SPORTSPRESS (LEGENDA INVISIBILE)
   ========================================== */
add_action( 'wp_footer', 'uss_accessibilita_tabelle_sportspress', 100 );
function uss_accessibilita_tabelle_sportspress() {
    ?>
    <script>
    jQuery(document).ready(function($){
        // Cerca tutte le classifiche a squadre nella pagina
        var $leagueTable = $('.sp-league-table');
        if ($leagueTable.length) {
            // FORZA IL NOME DELLA COLONNA
            var lang = $('html').attr('lang') || '';
            var isEnglish = lang.indexOf('en') !== -1;

            if (isEnglish) {
                // VERSIONE INGLESE
                $leagueTable.find('thead th.data-name').text('Team').attr('aria-label', 'Team');
            } else {
                // VERSIONE ITALIANA
                $leagueTable.find('thead th.data-name').text('Squadra').attr('aria-label', 'Squadra');
            }
            
            // ETICHETTE INVISIBILI (ARIA LABELS)
            $leagueTable.find('thead th.data-rank').attr('aria-label', 'Posizione in classifica');
            $leagueTable.find('thead th.data-name').attr('aria-label', 'Nome della squadra');
            $leagueTable.find('thead th.data-p').attr('aria-label', 'Numero di partite giocate');
            $leagueTable.find('thead th.data-w').attr('aria-label', 'Vittorie');
            $leagueTable.find('thead th.data-d').attr('aria-label', 'Pareggi');
            $leagueTable.find('thead th.data-l').attr('aria-label', 'Sconfitte');
            $leagueTable.find('thead th.data-f').attr('aria-label', 'Gol Fatti');
            $leagueTable.find('thead th.data-a').attr('aria-label', 'Gol Subiti');
            $leagueTable.find('thead th.data-gd').attr('aria-label', 'Differenza Reti');
            $leagueTable.find('thead th.data-pts').attr('aria-label', 'Punti');
        }
    });
    </script>
    <?php
}

/* ==========================================
   SOSTITUZIONE DINAMICA E OTTIMIZZAZIONE INTERFACCIA
   ========================================== */
function uss_enhance_sportspress_ui() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        
        <?php if ( is_singular( 'sp_event' ) ) : ?>
        /* ========================================================
           1. ESEGUITO SOLO SULLA PAGINA DELLA PARTITA SINGOLA
           ======================================================== */
        var score1 = $('.sp-event-results tbody tr:eq(0) .data-goals').text().trim();
        var score2 = $('.sp-event-results tbody tr:eq(1) .data-goals').text().trim();
        var time = $('.sp-event-details tbody tr:eq(0) td:eq(1)').text().trim();
        
        var centerContent = 'VS';
        var extraClass = '';
        
        if (score1 !== '' && score2 !== '') {
            centerContent = score1 + ' - ' + score2;
            extraClass = 'is-score';
        } else if (time !== '') {
            centerContent = time;
            extraClass = 'is-time';
        }
        
        $('.sp-event-logos-2').contents().filter(function() {
            return this.nodeType === 3 && this.nodeValue.trim().toLowerCase() === 'vs';
        }).replaceWith('<span class="sp-dynamic-center ' + extraClass + '">' + centerContent + '</span>');

        // H1 e link "Torna indietro"
        if ($('h1').length === 0) {
            var matchTitle = document.title.split(/[-–—|]/)[0].trim();
            var isPlayed = (score1 !== '' && score2 !== '');
            var backText = isPlayed ? 'Torna alle partite giocate' : 'Torna alle prossime partite';
            var backUrl = isPlayed ? '/partite-giocate/' : '/prossime-partite/';
            
            var backLinkHtml = '<a href="' + backUrl + '" class="back-link-match"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> ' + backText + '</a>';
            var headerHtml = '<div class="page-header"><h1 class="page-title">' + matchTitle + '</h1><div class="title-underline"></div>' + backLinkHtml + '</div>';
            $('main.page-content').prepend(headerHtml);
        }
        <?php endif; ?>

        /* ========================================================
           2. ESEGUITO SU TUTTE LE PAGINE SPORTSPRESS (Classifiche, Marcatori, ecc.)
           ======================================================== */
        $('h4.sp-table-caption').replaceWith(function() {
            var newText = $(this).text().trim().toUpperCase() === 'STADIO' ? 'Luogo' : $(this).html();
            return $('<h2/>', { class: this.className, html: newText });
        });

        // Rimuove i link da Giocatori e Squadre SOLO nelle tabelle generali (Classifica e Marcatori).
        // (La pagina della partita ora è gestita nativamente in PHP tramite Template Override)
        var linkDaRimuovere = '.sp-template-player-list td.data-name a, .sp-template-player-list td.data-team a, .sp-template-league-table td.data-name a';
        $(linkDaRimuovere).removeAttr('href').addClass('sp-no-link-name');

        // Fix link vuoto della mappa per l'accessibilità
        $('.sp-event-venue-map-row a').attr('aria-label', 'Apri mappa ingrandita in una nuova finestra');
        
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'uss_enhance_sportspress_ui', 99 );


/* ==========================================
   Fix alla tabella dei marcatori
   ========================================== */

function universalsport_marcatori_fix() {
    if ( ! is_page( 'marcatori' ) ) {
        return;
    }
    ?>
    <script>
    jQuery(document).ready(function($){

        function fixTabellaMarcatori() {
            var $table = $('.sp-player-list');
            if (!$table.length) return;

            // Intestazioni
            $table.find('thead th.data-rank').text('Pos');
            $table.find('thead th.data-position').html('<span class="sr-only">Ruolo</span>').attr('aria-label', 'Ruolo');
            $table.find('thead th.data-goals').text('Gol');
            $table.find('thead th.data-goals').attr('aria-label', 'Gol segnati')
            $table.find('thead th.data-appearances').text('PG');
            $table.find('thead th.data-appearances').attr('aria-label', 'Numero di partite giocate');
            $table.find('td.data-rank').attr('data-label', 'Pos');
            $table.find('td.data-position').attr('data-label', 'Ruolo');
            $table.find('td.data-goals').attr('data-label', 'Gol');
            $table.find('td.data-appearances').attr('data-label', 'PG');

            // Mostra tutte le righe se DataTables è attivo
            if ($.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
                var dt = $table.DataTable();
                dt.page.len(-1).draw(false);
            }

            // Nasconde controlli DataTables
            $table.closest('.dataTables_wrapper').find('.dataTables_paginate, .dataTables_info, .dataTables_length').hide();

            // Ricalcolo posizioni con ex aequo
            var lastGoals = null;
            var lastRank = 0;

            $table.find('tbody tr').each(function(index){
                var $row = $(this);
                var goals = parseInt($row.find('td.data-goals').text().trim(), 10) || 0;
                var currentPosition = index + 1;
                var rankToShow;

                if (lastGoals !== null && goals === lastGoals) {
                    rankToShow = lastRank;
                } else {
                    rankToShow = currentPosition;
                    lastRank = rankToShow;
                    lastGoals = goals;
                }

                var $rankCell = $row.find('td.data-rank');
                $rankCell.text(rankToShow);

                // reset classi
                $rankCell.removeClass('rank-1 rank-2 rank-3');

                // classi dinamiche vere
                if (rankToShow === 1) $rankCell.addClass('rank-1');
                if (rankToShow === 2) $rankCell.addClass('rank-2');
                if (rankToShow === 3) $rankCell.addClass('rank-3');
            });
        }

        fixTabellaMarcatori();

        setTimeout(function() {
            fixTabellaMarcatori();
        }, 300);

        $('.sp-player-list').on('draw.dt', function () {
            fixTabellaMarcatori();
        });

    });
    </script>
    <?php
}
add_action( 'wp_footer', 'universalsport_marcatori_fix', 100 );

/* ==========================================================================
   VIGILE SEO: TITOLO E CANONICAL DINAMICI (ARCHIVIO COMPETIZIONI)
   ========================================================================== */

// 1. Modifica dinamica del Titolo SEO per Rank Math (SOLO per le 4 pagine sportive)
add_filter( 'rank_math/frontend/title', function( $title ) {
    
    // Elenco delle pagine su cui vogliamo il titolo dinamico
    $pagine_sportive = array( 'partite-giocate', 'prossime-partite', 'classifica', 'marcatori' );
    
    // Se NON siamo in una di queste 4 pagine, lascia il titolo originale e fermati
    if ( ! is_page( $pagine_sportive ) ) {
        return $title;
    }

    // Se c'è il parametro usa quello, altrimenti usa il default centralizzato
    $slug = isset( $_GET['comp'] ) ? sanitize_text_field( $_GET['comp'] ) : uss_get_default_comp();
    $league = get_term_by('slug', $slug, 'sp_league');
    
    if ( $league ) {
        $page_title = get_the_title();
        return $league->name . ' | ' . $page_title . ' - ' . get_bloginfo( 'name' );
    }
    
    return $title;
});

add_filter( 'rank_math/frontend/canonical', function( $canonical ) {
	$pagine_sportive = array( 'partite-giocate', 'prossime-partite', 'classifica', 'marcatori' );

	if ( ! is_page( $pagine_sportive ) ) {
		return $canonical;
	}

	$default_comp = uss_get_default_comp();

	if ( isset( $_GET['comp'] ) ) {
		$slug = sanitize_text_field( $_GET['comp'] );

		if ( $slug === $default_comp ) {
			return remove_query_arg( 'comp', $canonical );
		}

		return add_query_arg( 'comp', $slug, remove_query_arg( 'comp', $canonical ) );
	}

	return remove_query_arg( 'comp', $canonical );
} );

// =========================================================================
// REGISTRAZIONE CONTENUTI PERSONALIZZATI E TASSONOMIE
// =========================================================================
function universal_sport_custom_post_types() {
    
    // A. Registrazione Tassonomia (Livelli) per gli Sponsor
    register_taxonomy( 'livello_sponsor', 'sponsor', array(
        'labels' => array(
            'name'          => 'Livelli Sponsor',
            'singular_name' => 'Livello',
            'menu_name'     => 'Livelli Sponsor',
        ),
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
    ));

    // B. SEZIONE SPONSOR
    register_post_type( 'sponsor', array(
        'labels' => array(
            'name'               => 'Sponsor',
            'singular_name'      => 'Sponsor',
            'menu_name'          => 'Sponsor',
            'all_items'          => 'Tutti gli Sponsor',
            'add_new'            => 'Aggiungi Nuovo',
            'add_new_item'       => 'Aggiungi Nuovo Sponsor',
            'edit_item'          => 'Modifica Sponsor',
            'not_found'          => 'Nessuno sponsor trovato',
        ),
        'public'              => true,
        'publicly_queryable'  => false, 
        'show_ui'             => true,
        'show_in_menu'        => true,
        'has_archive'         => false,
        'menu_position'       => 25,
        'menu_icon'           => 'dashicons-awards', 
        'supports'            => array( 'title', 'editor', 'thumbnail' ),
    ));

    // C. Registrazione Tassonomie ISOLATE per le Gallerie
    // Categorie delle gallerie
    register_taxonomy( 'categoria_galleria', 'gallerie', array(
        'labels' => array(
            'name'          => 'Categorie Gallerie',
            'singular_name' => 'Categoria',
            'menu_name'     => 'Categorie',
        ),
        'hierarchical'      => true, // Vero = Si comporta come le Categorie
        'show_admin_column' => true,
        'show_in_rest'      => true,
    ));
    // Tag delle gallerie
    register_taxonomy( 'tag_galleria', 'gallerie', array(
        'labels' => array(
            'name'          => 'Tag Gallerie',
            'singular_name' => 'Tag',
            'menu_name'     => 'Tag',
        ),
        'hierarchical'      => false, // Falso = Si comporta come i Tag
        'show_admin_column' => true,
        'show_in_rest'      => true,
    ));

    // D. SEZIONE GALLERIE
    register_post_type( 'gallerie', array(
        'labels' => array(
            'name'               => 'Gallerie Foto',
            'singular_name'      => 'Galleria',
            'menu_name'          => 'Gallerie Foto',
            'all_items'          => 'Tutte le Gallerie',
            'add_new'            => 'Aggiungi Nuova',
            'add_new_item'       => 'Aggiungi Nuova Galleria',
            'edit_item'          => 'Modifica Galleria',
            'not_found'          => 'Nessuna galleria trovata',
        ),
        'public'              => true,
        'has_archive'         => false, 
        'rewrite'             => array( 'slug' => 'gallery' ), 
        'show_in_rest'        => true, 
        'menu_position'       => 26,
        'menu_icon'           => 'dashicons-images-alt2', 
        'supports'            => array( 'title', 'editor', 'thumbnail' ),
        'taxonomies'          => array( 'categoria_galleria', 'tag_galleria' ), // <-- USIAMO LE NUOVE TASSONOMIE ISOLATE!
    ));
}
add_action( 'init', 'universal_sport_custom_post_types' );

/* ==========================================================================
   STATO COMPETIZIONE (Term Meta personalizzato per sp_league)
   ========================================================================== */

// 1. Mostra il campo quando CREI una nuova lega
add_action('sp_league_add_form_fields', 'uss_add_league_status_field');
function uss_add_league_status_field() {
    ?>
    <div class="form-field">
        <label for="league_status">Stato Competizione</label>
        <select name="league_status" id="league_status">
            <option value="in_corso">In corso</option>
            <option value="non_iniziata">Non iniziata</option>
            <option value="finita">Finita</option>
        </select>
        <p>Seleziona lo stato attuale del campionato per mostrare il badge corretto nelle pagine.</p>
    </div>
    <?php
}

// 2. Mostra il campo quando MODIFICHI una lega esistente
add_action('sp_league_edit_form_fields', 'uss_edit_league_status_field', 10, 2);
function uss_edit_league_status_field($term, $taxonomy) {
    $status = get_term_meta($term->term_id, 'league_status', true);
    if (!$status) $status = 'in_corso'; // Default
    ?>
    <tr class="form-field">
        <th scope="row"><label for="league_status">Stato Competizione</label></th>
        <td>
            <select name="league_status" id="league_status">
                <option value="in_corso" <?php selected($status, 'in_corso'); ?>>In corso</option>
                <option value="non_iniziata" <?php selected($status, 'non_iniziata'); ?>>Non iniziata</option>
                <option value="finita" <?php selected($status, 'finita'); ?>>Finita</option>
            </select>
        </td>
    </tr>
    <?php
}

// 3. Salva il dato nel database
add_action('created_sp_league', 'uss_save_league_status_meta');
add_action('edited_sp_league', 'uss_save_league_status_meta');
function uss_save_league_status_meta($term_id) {
    if (isset($_POST['league_status'])) {
        update_term_meta($term_id, 'league_status', sanitize_text_field($_POST['league_status']));
    }
}

/* ==========================================================================
   MODIFICA RAPIDA PER LO STATO COMPETIZIONE (Schermata Campionati)
   ========================================================================== */

// 1. Aggiunge la colonna "Stato" nella tabella dei campionati
add_filter('manage_edit-sp_league_columns', function($columns) {
    $columns['league_status'] = 'Stato';
    return $columns;
});

// 2. Riempie la colonna con il valore corrente
add_filter('manage_sp_league_custom_column', function($content, $column_name, $term_id) {
    if ($column_name === 'league_status') {
        $status = get_term_meta($term_id, 'league_status', true);
        if ($status === 'finita') {
            $content = '<span style="color:#6c757d; font-weight:bold;">Conclusa</span>';
        } elseif ($status === 'non_iniziata') {
            $content = '<span style="color:#e67e22; font-weight:bold;">In arrivo</span>';
        } else {
            $content = '<span style="color:#28a745; font-weight:bold;">In corso</span>';
        }
    }
    return $content;
}, 10, 3);

// 3. Stampa il campo select all'interno del box della Modifica Rapida
add_action('quick_edit_custom_box', function($column_name, $screen, $taxonomy) {
    if ($taxonomy !== 'sp_league' || $column_name !== 'league_status') return;
    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label class="inline-edit-group">
                <span class="title">Stato Campionato</span>
                <select name="league_status" class="league-status-select">
                    <option value="in_corso">In corso</option>
                    <option value="non_iniziata">In arrivo</option>
                    <option value="finita">Conclusa</option>
                </select>
            </label>
        </div>
    </fieldset>
    <?php
}, 10, 3);

// 4. JavaScript necessario per passare il dato salvato alla tendina quando clicchi "Modifica Rapida"
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if ($screen && $screen->taxonomy === 'sp_league') {
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('#the-list').on('click', '.editinline', function() {
                var row = $(this).closest('tr');
                var termId = row.attr('id').replace('tag-', '');
                // Trova il testo della colonna per capire lo stato attuale
                var statusText = row.find('.column-league_status').text().trim();
                
                var value = 'in_corso';
                if (statusText === 'Conclusa') value = 'finita';
                if (statusText === 'In arrivo') value = 'non_iniziata';
                
                // Imposta il valore corretto nella modifica rapida aperta
                $('#edit-' + termId).find('.league-status-select').val(value);
            });
        });
        </script>
        <?php
    }
});

// =========================================================================
// REGISTRAZIONE MENU DEL FOOTER
// =========================================================================
function uss_registra_menu_footer() {
    register_nav_menus( array(
        'footer-utili' => 'Menu Footer: Link Utili'
    ) );
}
add_action( 'init', 'uss_registra_menu_footer' );

/* ==========================================
   SHORTCODE PER LE ULTIME NEWS
   ========================================== */
function uss_ultime_news_shortcode($atts) {
    $a = shortcode_atts( array( 'limite' => 4 ), $atts );
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => intval($a['limite']),
        'post_status'    => 'publish'
    );
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


/* ==========================================
   SHORTCODE PER I PROSSIMI EVENTI
   ========================================== */
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

/* ==========================================================================
   SHORTCODE PAGINA NEWS COMPLETA (Filtri, Ricerca, Paginazione, Controlli)
   ========================================================================== */
add_shortcode('pagina_news_completa', 'uss_shortcode_pagina_news');
function uss_shortcode_pagina_news($atts) {
    ob_start();
    
    // 0. CONTROLLO INIZIALE: Esistono articoli nel sito?
    $total_posts = wp_count_posts('post');
    if ( !isset($total_posts->publish) || $total_posts->publish == 0 ) {
        // Se non c'è nessuna news in assoluto, mostra il messaggio "Stiamo arrivando" e nient'altro
        echo '<div class="news-archive-empty text-center" style="margin: 80px auto; max-width: 650px;">';
        echo '<i class="fa-solid fa-newspaper" style="font-size: 3.5rem; color: var(--accent-yellow); margin-bottom: 25px;"></i>';
        echo '<h2 style="color: var(--primary-blue); margin-bottom: 15px;">I nostri racconti arriveranno presto!</h2>';
        echo '<p style="color: #555; font-size: 1.15rem;">Stiamo scaldando i motori per l\'inizio ufficiale. Torna a trovarci tra qualche giorno per leggere tutte le novità, i risultati e le storie dell\'Associazione.</p>';
        echo '</div>';
        
        return ob_get_clean();
    }
    
    // 1. Recupero parametri (Pagina attuale, Ricerca, Categoria)
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $search_query = isset($_GET['cerca']) ? sanitize_text_field($_GET['cerca']) : '';
    $cat_query = isset($_GET['categoria']) ? intval($_GET['categoria']) : '';

    // 2. Costruzione della Topbar (Ricerca + Filtri)
    echo '<div class="news-archive-topbar">';
    echo '<form method="GET" action="' . esc_url(get_permalink()) . '" class="news-filter-form">';
    
    if (!empty($cat_query)) {
        echo '<input type="hidden" name="categoria" value="' . esc_attr($cat_query) . '">';
    }

    echo '<div class="news-search-box">';
    echo '<label for="ricerca-news" class="sr-only">Cerca tra gli articoli</label>';
    echo '<input type="text" id="ricerca-news" name="cerca" placeholder="Cerca tra gli articoli..." value="' . esc_attr($search_query) . '">';
    echo '<button type="submit" class="btn-all-events"><i class="fas fa-search"></i> Cerca</button>';
    
    if (!empty($search_query) || !empty($cat_query)) {
        $reset_url = remove_query_arg(array('categoria', 'cerca', 'paged'));
        echo '<a href="' . esc_url($reset_url) . '" class="news-reset-btn"><i class="fas fa-times"></i> Azzera</a>';
    }
    echo '</div>';

    // Filtri Categorie Dinamici (Senza link circolari)
    $categories = get_categories(array('hide_empty' => true));
    echo '<div class="news-category-filters">';
    
    if (empty($cat_query)) {
        echo '<span class="news-cat-link active">Tutte</span>';
    } else {
        $url_tutte = remove_query_arg(array('categoria', 'paged'));
        echo '<a href="' . esc_url($url_tutte) . '" class="news-cat-link">Tutte</a>';
    }
    
    foreach ($categories as $category) {
        if ($cat_query == $category->term_id) {
            echo '<span class="news-cat-link active">' . esc_html($category->name) . '</span>';
        } else {
            $cat_url = add_query_arg('categoria', $category->term_id);
            $cat_url = remove_query_arg('paged', $cat_url); 
            if (!empty($search_query)) { $cat_url = add_query_arg('cerca', $search_query, $cat_url); }
            echo '<a href="' . esc_url($cat_url) . '" class="news-cat-link">' . esc_html($category->name) . '</a>';
        }
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
    if (!empty($search_query)) $args['s'] = $search_query;
    if (!empty($cat_query)) $args['cat'] = $cat_query;

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
            echo '<div class="news-home-img"><a href="' . $link . '"><img src="' . $image_url . '" alt="' . esc_attr($alt_text) . '"></a></div>';
            echo '<div class="news-home-content">';
            echo '<span class="news-home-date">' . $date . '</span>';
            echo '<h3><a href="' . $link . '" class="news-home-title-link">' . $title . '</a></h3>';
            echo '<p class="news-home-excerpt">' . $excerpt . '</p>';
            echo '<a href="' . $link . '" class="read-more">Leggi tutto</a>';
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
            'prev_text' => '&laquo; Precedente',
            'next_text' => 'Successiva &raquo;',
        ));
        echo '</div>';
    }
    
    wp_reset_postdata();
    return ob_get_clean();
}

/* ==========================================
   RENDE INTERA CARD CLICCABILE (GUTENBERG)
   ========================================== */
function uss_make_cards_clickable() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Quando si clicca ovunque sulla card...
        $(document).on('click', '.wp-block-post', function(e) {
            // Se l'utente sta già cliccando su un link nativo, lascia fare al browser
            if ($(e.target).closest('a').length) return;
            
            // Altrimenti, trova il link del titolo e apri la pagina!
            var link = $(this).find('.wp-block-post-title a').attr('href');
            if (link) {
                window.location.href = link;
            }
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'uss_make_cards_clickable', 101);

/* ==========================================================================
   TRADUZIONE AUTOMATICA ALT TEXT DEEPL (VERSIONE DEFINITIVA E CORRETTA)
   ========================================================================== */

// 1. RILEVATORE DI LINGUA (Corretto: zero falsi positivi in italiano!)
function uss_is_english() {
    // A. Controlla TranslatePress
    global $TRP_LANGUAGE;
    if ( ! empty( $TRP_LANGUAGE ) && strpos( $TRP_LANGUAGE, 'en' ) === 0 ) {
        return true;
    }
    
    // B. Controlla il normale caricamento della pagina
    if ( ! wp_doing_ajax() ) {
        if ( get_locale() === 'en_GB' || get_locale() === 'en_US' || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/en/') !== false) ) {
            return true;
        }
    } else {
        // C. SOLO PER AJAX (Come il Calendario Eventi): guardiamo la provenienza
        if ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/en/') !== false ) {
            return true;
        }
    }
    return false;
}

// 2. IL MOTORE DEEPL
function uss_deepl_auto_translate_alt( $original_alt ) {
    if ( empty( trim( $original_alt ) ) || strlen( $original_alt ) < 3 ) {
        return $original_alt;
    }

    // INSERISCI QUI LA TUA VERA CHIAVE API
    $api_key = 'fdf1e5d7-f07f-4a73-b512-961a4ed6ff94:fx';

    $cache_key = 'alt_engb_' . md5( $original_alt );
    $cached_translation = get_transient( $cache_key );

    if ( $cached_translation ) { return $cached_translation; }

    $response = wp_remote_post( 'https://api-free.deepl.com/v2/translate', array(
        'headers' => array( 'Authorization' => 'DeepL-Auth-Key ' . $api_key, 'Content-Type'  => 'application/x-www-form-urlencoded' ),
        'body' => array( 'text' => $original_alt, 'target_lang' => 'EN-GB', 'source_lang' => 'IT' ),
        'timeout' => 10
    ));

    if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['translations'][0]['text'] ) ) {
            $translated_text = $body['translations'][0]['text'];
            
            // Forza sempre "Universal Football"
            $translated_text = str_ireplace( 
                array('Universal Soccer', 'Universal soccer', 'universal soccer', 'Universal Calcio', 'universal football'), 
                'Universal Football', 
                $translated_text 
            );

            set_transient( $cache_key, $translated_text, YEAR_IN_SECONDS );
            return $translated_text;
        }
    }
    return $original_alt;
}

// 3. I TRE VIGILI STRATEGICI (Senza doppioni o conflitti)

// Vigile A: Le Gallerie e i blocchi testo nativi
add_filter( 'the_content', 'uss_translate_content_alts', 99 );
function uss_translate_content_alts( $content ) {
    if ( uss_is_english() ) {
        $content = preg_replace_callback( '/alt="([^"]+)"/i', function( $matches ) {
            return 'alt="' . esc_attr( uss_deepl_auto_translate_alt( $matches[1] ) ) . '"';
        }, $content );
    }
    return $content;
}

// Vigile B: Le immagini della Libreria Media (Articoli singoli ed Eventi standard)
add_filter( 'wp_get_attachment_image_attributes', 'uss_translate_attachment_alts', 99, 3 );
function uss_translate_attachment_alts( $attr, $attachment, $size ) {
    if ( uss_is_english() && isset( $attr['alt'] ) && ! empty( $attr['alt'] ) ) {
        $attr['alt'] = uss_deepl_auto_translate_alt( $attr['alt'] );
    }
    return $attr;
}

// Vigile C: Le Card della Home e degli Archivi (I tuoi Shortcode)
add_filter( 'do_shortcode_tag', 'uss_translate_shortcodes_alts', 10, 4 );
function uss_translate_shortcodes_alts( $output, $tag, $attr, $m ) {
    if ( uss_is_english() && in_array( $tag, ['ultime_news', 'ultimi_eventi', 'pagina_news_completa'] ) ) {
        $output = preg_replace_callback( '/alt="([^"]+)"/i', function( $matches ) {
            return 'alt="' . esc_attr( uss_deepl_auto_translate_alt( $matches[1] ) ) . '"';
        }, $output );
    }
    return $output;
}

// ==========================================================================
// VIGILE SEO: TRADUZIONE DINAMICA TITOLI E DESCRIZIONI (RANK MATH) CON DEEPL
// ==========================================================================

// 1. Traduzione Dinamica dei Titoli
add_filter( 'rank_math/frontend/title', 'uss_translate_rankmath_title_dynamic' );
function uss_translate_rankmath_title_dynamic( $title ) {
    if ( uss_is_english() && ! empty( $title ) ) {
        return uss_deepl_auto_translate_alt( $title );
    }
    return $title;
}

// 2. Traduzione Dinamica delle Descrizioni
add_filter( 'rank_math/frontend/description', 'uss_translate_rankmath_description' );
function uss_translate_rankmath_description( $description ) {
    if ( uss_is_english() && ! empty( $description ) ) {
        return uss_deepl_auto_translate_alt( $description );
    }
    return $description;
}

/* ==========================================================================
   TRADUZIONE PERSONALIZZATA TITOLI BROWSER (COMPATIBILE CON RANK MATH)
   ========================================================================== */
add_filter( 'rank_math/frontend/title', 'uss_translate_rankmath_title_hybrid' );

function uss_translate_rankmath_title_hybrid( $title ) {
    if ( uss_is_english() && ! empty( $title ) ) {
        
        // 1. LE TUE TRADUZIONI PERSONALIZZATE (FORZATURE MANUALI)
        // Puoi aggiungere nuove righe qui dentro seguendo lo schema: 'slug-pagina' => 'Titolo Desiderato',
        $custom_titles = array(
            'direttivo'         => 'Board of Directors - Universal Sport Sovizzo',
            'presentazione'     => 'About Us - Universal Sport Sovizzo',
            'cose-il-calcio-universale'     => 'What is Universal Football - Universal Sport Sovizzo',
            'partite-giocate'     => 'Results - Universal Sport Sovizzo',
            'prossime-partite'     => 'Fixtures - Universal Sport Sovizzo',
            'classifica'     => 'Table - Universal Sport Sovizzo',
            'marcatori'     => 'Top Scorers - Universal Sport Sovizzo',
            'partner'           => 'Partners - Universal Sport Sovizzo',
            'contatti'          => 'Contact - Universal Sport Sovizzo',
            'gallery'           => 'Gallery - Universal Sport Sovizzo',
            'privacy-policy'    => 'Privacy Policy - Universal Sport Sovizzo',
        );

        // Controlliamo se la pagina attuale è presente nella tua lista di forzature
        foreach ( $custom_titles as $slug => $forced_title ) {
            if ( is_page( $slug ) ) {
                return $forced_title; // Se la trova, usa la tua traduzione ed esce subito
            }
        }

        // 2. FALLBACK AUTOMATICO (Per articoli delle news, eventi e query loop)
        // Se la pagina non è nella lista sopra, ci pensa DeepL in automatico
        return uss_deepl_auto_translate_alt( $title );
    }
    return $title;
}
