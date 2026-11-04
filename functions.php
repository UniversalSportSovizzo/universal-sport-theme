<?php
/*
 * Universal Sport Sovizzo functions and definitions
 */

// IL CUORE DEL SITO: Cambia questo slug per aggiornare l'intero sito alla nuova stagione! Ex. 'campionato-26-27'
function uss_get_default_comp() {
    return '1-rassegna-calcio-universale';
}

// Carica tutti gli shortcode del sito
require_once get_template_directory() . '/inc/shortcodes.php';

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
    //wp_enqueue_style( 'universal-sport-style', get_stylesheet_uri() );
    wp_enqueue_style(
    'universal-sport-style', 
    get_stylesheet_uri(), 
    array(), 
    filemtime( get_stylesheet_directory() . '/style.css' ) /* Genera la versione in base al salvataggio */
);

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
           2. ESEGUITO SU TUTTE LE PAGINE SPORTSPRESS
           ======================================================== */
        $('h4.sp-table-caption').replaceWith(function() {
            var newText = $(this).text().trim().toUpperCase() === 'STADIO' ? 'Luogo' : $(this).html();
            return $('<h2/>', { class: this.className, html: newText });
        });

        // Rimuove i link dallo staff della partita (i giocatori ora sono gestiti nativamente dal PHP)
        var linkDaRimuovere = '.sp-event-staff a';
        $(linkDaRimuovere).removeAttr('href').addClass('sp-no-link-name');

        // Fix link vuoto della mappa per l'accessibilità
        $('.sp-event-venue-map-row a').attr('aria-label', 'Apri mappa ingrandita in una nuova finestra');
        
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'uss_enhance_sportspress_ui', 99 );


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
   VISIBILITÀ COMPETIZIONE (Nascondi dal selettore pillole)
   ========================================================================== */

// 1. Mostra la spunta quando CREI un nuovo campionato
add_action('sp_league_add_form_fields', 'uss_add_league_visibility_field');
function uss_add_league_visibility_field() {
    ?>
    <div class="form-field">
        <label for="league_hide">
            <input type="checkbox" name="league_hide" id="league_hide" value="1"> 
            <strong>Nascondi dal selettore pubblico</strong>
        </label>
        <p>Spunta questa casella per non far apparire questa competizione nelle "pillole" del sito (utile per campionati in preparazione).</p>
    </div>
    <?php
}

// 2. Mostra la spunta quando MODIFICHI un campionato esistente
add_action('sp_league_edit_form_fields', 'uss_edit_league_visibility_field');
function uss_edit_league_visibility_field($term) {
    $hidden = get_term_meta($term->term_id, 'league_hide', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="league_hide">Visibilità Pubblica</label></th>
        <td>
            <label>
                <input type="checkbox" name="league_hide" id="league_hide" value="1" <?php checked($hidden, '1'); ?>> 
                Nascondi questa competizione dalle pillole del sito
            </label>
        </td>
    </tr>
    <?php
}

// 3. Salva la spunta nel database (Compatibile con Modifica Rapida)
add_action('created_sp_league', 'uss_save_league_visibility_meta');
add_action('edited_sp_league', 'uss_save_league_visibility_meta');
function uss_save_league_visibility_meta($term_id) {
    // Controlliamo che stiamo salvando da un form valido (Normale o Modifica Rapida)
    if ( !isset($_POST['action']) || !in_array($_POST['action'], array('add-tag', 'editedtag', 'inline-save-tax')) ) {
        return;
    }

    if (isset($_POST['league_hide']) && $_POST['league_hide'] == '1') {
        update_term_meta($term_id, 'league_hide', '1');
    } else {
        // Se togliamo la spunta, eliminiamo il dato
        delete_term_meta($term_id, 'league_hide');
    }
}

/* ==========================================================================
   MODIFICA RAPIDA PER STATO E VISIBILITÀ (Schermata Campionati)
   ========================================================================== */

// 1. Aggiunge le colonne "Stato" e "Visibilità" nella tabella dei campionati
add_filter('manage_edit-sp_league_columns', function($columns) {
    $columns['league_status'] = 'Stato';
    $columns['league_visibility'] = 'Visibilità';
    return $columns;
});

// 2. Riempie le colonne con il valore corrente (AGGIUNTI DATA-VAL PER SICUREZZA)
add_filter('manage_sp_league_custom_column', function($content, $column_name, $term_id) {
    if ($column_name === 'league_status') {
        $status = get_term_meta($term_id, 'league_status', true);
        if ($status === 'finita') {
            $content = '<span class="uss-status-val" data-val="finita" style="color:#6c757d; font-weight:bold;">Conclusa</span>';
        } elseif ($status === 'non_iniziata') {
            $content = '<span class="uss-status-val" data-val="non_iniziata" style="color:#e67e22; font-weight:bold;">In arrivo</span>';
        } else {
            $content = '<span class="uss-status-val" data-val="in_corso" style="color:#28a745; font-weight:bold;">In corso</span>';
        }
    }
    if ($column_name === 'league_visibility') {
        $hidden = get_term_meta($term_id, 'league_hide', true);
        if ($hidden == '1') {
            $content = '<span class="uss-vis-val" data-val="1" style="color:#dc3545; font-weight:bold;">Nascosta</span>';
        } else {
            $content = '<span class="uss-vis-val" data-val="0" style="color:#28a745; font-weight:bold;">Visibile</span>';
        }
    }
    return $content;
}, 10, 3);

// 3. Stampa i campi all'interno del box della Modifica Rapida
add_action('quick_edit_custom_box', function($column_name, $screen, $taxonomy) {
    if ($taxonomy !== 'sp_league') return;
    
    // Stampiamo i campi solo una volta (WordPress chiama questo hook per ogni colonna)
    if ($column_name === 'league_status') {
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
                <label class="inline-edit-group" style="margin-top: 15px;">
                    <span class="title">Visibilità</span>
                    <input type="checkbox" name="league_hide" class="league-hide-checkbox" value="1">
                    <span class="checkbox-title">Nascondi dalle pillole</span>
                </label>
            </div>
        </fieldset>
        <?php
    }
}, 10, 3);

// 4. JavaScript per passare i dati letti dalla tabella dentro al box di Modifica Rapida
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if ($screen && $screen->taxonomy === 'sp_league') {
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('#the-list').on('click', '.editinline', function() {
                var row = $(this).closest('tr');
                var termId = row.attr('id').replace('tag-', '');
                
                // Leggiamo i valori sicuri dagli attributi data-val
                var statusVal = row.find('.uss-status-val').data('val');
                var isHidden = row.find('.uss-vis-val').data('val');
                
                // Usiamo un setTimeout per "battere sul tempo" WordPress,
                // che altrimenti cancella tutte le spunte appena si apre la modifica rapida!
                setTimeout(function() {
                    var $editRow = $('#edit-' + termId);
                    
                    // Imposta lo stato
                    if (statusVal) {
                        $editRow.find('.league-status-select').val(statusVal);
                    }
                    
                    // Imposta la spunta della visibilità
                    if (isHidden == '1') {
                        $editRow.find('.league-hide-checkbox').prop('checked', true);
                    } else {
                        $editRow.find('.league-hide-checkbox').prop('checked', false);
                    }
                }, 50); // 50 millisecondi di ritardo
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
    if ( uss_is_english() && in_array( $tag, ['ultime_news', 'ultimi_eventi'] ) ) {
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

/* ==========================================================================
   FORZA IL RITORNO A CAPO NELLA DATA (SOLO NELLE CARD, NON NEL SINGOLO)
   ========================================================================== */
add_filter( 'tribe_events_event_schedule_details', function( $details ) {
    // Se NON siamo nella pagina del singolo evento, forziamo l'a capo
    if ( ! is_singular( 'tribe_events' ) ) {
        return str_replace( ' dalle ', ' ', $details );
    }
    // Se siamo nel singolo evento, restituiamo il testo normale in linea
    return $details;
} );

add_filter( 'tribe_events_event_schedule_details', function( $details ) {
    // Se NON siamo nella pagina del singolo evento, forziamo l'a capo
    if ( ! is_singular( 'tribe_events' ) ) {
        return str_replace( ' alle ', ' - ', $details );
    }
    // Se siamo nel singolo evento, restituiamo il testo normale in linea
    return $details;
} );


/* ==========================================================================
   MOTORE CALENDARIO (Usato dal caricamento pagina e da AJAX)
   ========================================================================== */
function uss_generate_mini_calendar($month, $year) {
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $first_day = date('w', strtotime("$year-$month-01"));
    $first_day = $first_day == 0 ? 7 : $first_day; 
    
    $mesi_it = array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');
    $nome_mese = $mesi_it[intval($month) - 1];

    // Calcolo mesi precedente e successivo
    $prev_m = $month - 1; $prev_y = $year;
    if ($prev_m == 0) { $prev_m = 12; $prev_y--; }
    $next_m = $month + 1; $next_y = $year;
    if ($next_m == 13) { $next_m = 1; $next_y++; }

    // Query Eventi
    $cal_args = array(
        'post_type'      => 'tribe_events',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query' => array(
            array(
                'key'     => '_EventStartDate',
                'value'   => array(sprintf("%04d-%02d-01 00:00:00", $year, $month), sprintf("%04d-%02d-%02d 23:59:59", $year, $month, $days_in_month)),
                'compare' => 'BETWEEN',
                'type'    => 'DATETIME'
            )
        )
    );
    $cal_query = new WP_Query($cal_args);
    $event_days = array();
    
    if ($cal_query->have_posts()) {
        while ($cal_query->have_posts()) {
            $cal_query->the_post();
            $event_date = get_post_meta(get_the_ID(), '_EventStartDate', true);
            $day_of_event = intval(date('d', strtotime($event_date)));
            if(!isset($event_days[$day_of_event])) {
                // Invece del singolo evento, creiamo il link alla pagina "Giorno" di The Events Calendar
                $event_days[$day_of_event] = home_url( '/eventi/' . date('Y-m-d', strtotime($event_date)) . '/' );
            }
        }
        wp_reset_postdata();
    }

    ob_start();

    // Generiamo l'URL ufficiale di The Events Calendar per quel mese specifico
    $month_url = home_url( '/eventi/' . $year . '-' . sprintf('%02d', $month) . '/' );
    ?>
    <div class="custom-cal-header">
        <button class="cal-nav-btn ajax-cal-btn" data-m="<?php echo $prev_m; ?>" data-y="<?php echo $prev_y; ?>" aria-label="Mese precedente"><i class="fas fa-chevron-left"></i></button>
        
        <a href="<?php echo esc_url($month_url); ?>" class="cal-month-link" title="Vedi tutti gli eventi di <?php echo esc_attr($nome_mese); ?>">
            <strong><?php echo esc_html($nome_mese . ' ' . $year); ?></strong>
        </a>
        
        <button class="cal-nav-btn ajax-cal-btn" data-m="<?php echo $next_m; ?>" data-y="<?php echo $next_y; ?>" aria-label="Mese successivo"><i class="fas fa-chevron-right"></i></button>
    </div>

    <table class="custom-mini-calendar">
        <thead><tr><th>L</th><th>M</th><th>M</th><th>G</th><th>V</th><th>S</th><th>D</th></tr></thead>
        <tbody><tr>
        <?php
        for ($i = 1; $i < $first_day; $i++) { echo '<td></td>'; }

        $current_day = 1;
        while ($current_day <= $days_in_month) {
            if ($first_day > 7) {
                $first_day = 1;
                echo '</tr><tr>';
            }
            
            // Colora "Oggi" solo se mese e anno coincidono
            $is_today = ($current_day == date('j') && $month == date('m') && $year == date('Y')) ? ' today' : '';
            
            if (isset($event_days[$current_day])) {
                echo '<td class="has-event' . $is_today . '"><a href="' . esc_url($event_days[$current_day]) . '" title="Vedi evento">' . $current_day . '</a></td>';
            } else {
                echo '<td class="' . $is_today . '">' . $current_day . '</td>';
            }

            $current_day++;
            $first_day++;
        }

        while ($first_day > 1 && $first_day <= 7) {
            echo '<td></td>';
            $first_day++;
        }
        ?>
        </tr></tbody>
    </table>
    
    <div class="custom-cal-legend">
        <span class="legend-dot event-dot"></span> Eventi
        <span class="legend-dot today-dot"></span> Oggi
    </div>
    <?php
    return ob_get_clean();
}

/* ==========================================================================
   RICEVITORE AJAX (Per cambiare mese senza ricaricare la pagina)
   ========================================================================== */
add_action('wp_ajax_uss_load_calendar', 'uss_ajax_load_calendar');
add_action('wp_ajax_nopriv_uss_load_calendar', 'uss_ajax_load_calendar'); // Permette anche agli utenti non loggati di usarlo
function uss_ajax_load_calendar() {
    $month = isset($_POST['month']) ? intval($_POST['month']) : date('m');
    $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
    
    // Stampa il nuovo calendario aggiornato
    echo uss_generate_mini_calendar($month, $year);
    wp_die();
}

/* ==========================================================================
   TASTO TORNA SU (BACK TO TOP)
   ========================================================================== */
function uss_add_back_to_top_button() {
    ?>
    <button id="uss-back-to-top" class="back-to-top-btn" aria-label="Torna all'inizio della pagina">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopBtn = document.getElementById('uss-back-to-top');
        
        if (backToTopBtn) {
            // Mostra/Nascondi il tasto allo scroll
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            });

            // Torna su in modo fluido al click
            backToTopBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    });
    </script>
    <?php
}
// Agganciamo la funzione al footer di WordPress
add_action('wp_footer', 'uss_add_back_to_top_button');

/* ==========================================================================
   OTTIMIZZAZIONE QUALITÀ LOGHI SPORTSPRESS (DOPPIO TAGLIO)
   ========================================================================== */

// 1. Taglio per i Report singoli (Qualità altissima, es. 1000px)
add_image_size('uss-logo-report', 1000, 1000, false);

// 2. Taglio per gli Shortcode / Liste (Qualità bilanciata a 600px)
add_image_size('uss-logo-list', 600, 600, false);

// 3. Diciamo a SportsPress nativo di usare il taglio gigante nei report
function uss_custom_sportspress_logo_size($size) {
    return 'uss-logo-report';
}
add_filter('sportspress_logo_size', 'uss_custom_sportspress_logo_size');

/* ==========================================================================
   FIX ANTEPRIMA WHATSAPP: FORZA IMMAGINI LEGGERE IN RANK MATH (OG:IMAGE)
   ========================================================================== */
add_filter( 'rank_math/opengraph/facebook/image', function( $attachment_url ) {
    if ( is_singular() && has_post_thumbnail() ) {
        // Prende la versione 'large' (1024px) invece dell'originale pesante
        $large_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
        if ( $large_url ) {
            return $large_url;
        }
    }
    return $attachment_url;
});