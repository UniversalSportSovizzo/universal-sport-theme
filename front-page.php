<?php get_header(); ?>

<main id="contenuto-principale" class="home">
    <section class="champion-hero-image">
        <?php if ( get_locale() === 'en_GB' || get_locale() === 'en_US' ) : ?>
            <img src="/wp-content/uploads/2026/09/home-scaled.png" alt="The Universal Sport Sovizzo team celebrates, raising the cup with medals around their necks" class="hero-team-photo" onerror="this.classList.add('hide-on-error');">
        <?php else : ?>
            <img src="/wp-content/uploads/2026/05/home-scaled.jpg" alt="La squadra Universal Sport Sovizzo esulta alzando la coppa con le medaglie al collo" class="hero-team-photo" onerror="this.classList.add('hide-on-error');">
        <?php endif; ?>
    </section>
    
    <section class="champion-banner">
        <div class="container text-center">
            <h1 class="title-gold">Universal Sport Sovizzo</h1>
            <p class="paragraph-title">
                Siamo i <span class="campioni"> Campioni Nazionali </span> in carica di <span class="subtitle-epic">Calcio Universale EISI</span>. Crediamo nello sport come linguaggio universale e lo promuoviamo per dimostrare che l'inclusione si realizza sul campo da gioco, dove chiunque, indipendentemente dalla propria abilità, può sentirsi parte di una squadra e crescere insieme agli altri.
            </p>
            <a href="/chi-siamo" class="btn-all-events btn-yellow btn_small">Scopri la nostra storia</a>
        </div>
    </section>
    
    <section class="vicenza-matches-widget container">
        <div class="vicenza-match-section">
            <h2 class="vicenza-widget-title"><span>Ultimo Risultato</span></h2>
            <?php echo do_shortcode('[partite_mancity team="377" status="past" limit="1" show_month="no" default_comp="tutte"]'); ?>
        </div>

        <div class="vicenza-match-section home-match-spacing">
            <h2 class="vicenza-widget-title"><span>Prossima Partita</span></h2>
            <?php echo do_shortcode('[partite_mancity team="377" status="future" limit="1" show_month="no" default_comp="tutte"]'); ?>
        </div>
    </section>
    <?php 
    // Controlla se ci sono eventi futuri programmati
    $future_events = tribe_get_events( [ 'posts_per_page' => 1, 'start_date' => 'now' ] );
    if ( ! empty( $future_events ) ) : 
    ?>
    <section class="upcoming-events container" aria-labelledby="titolo-eventi">
        <div class="section-title text-center">
            <h2 id="titolo-eventi">Prossimi Appuntamenti</h2>
            <p>Resta aggiornato sulle attività di Universal Sport Sovizzo</p>
        </div>
        
        <div>
            <?php echo do_shortcode('[eventi_home_split limite="2"]'); ?>
        </div>
        
        <div class="text-center">
            <a href="<?php echo tribe_get_events_link(); ?>" class="btn-all-events">Tutti i nostri eventi</a>
        </div>
    </section>
    <?php endif; ?>

    <?php 
    // Controlla se c'è almeno 1 articolo pubblicato nel sito
    $check_news = wp_count_posts('post');
    if ( isset($check_news->publish) && $check_news->publish > 0 ) : 
    ?>
    <section class="latest-news container" aria-labelledby="titolo-news">
        <div class="section-title text-center">
            <h2 id="titolo-news">I Nostri Racconti</h2>
            <p>Ultime dalla squadra</p>
        </div>
        
        <div>
            <?php echo do_shortcode('[ultime_news limite="2"]'); ?>
        </div>
        
        <div class="text-center">
            <a href="/news/" class="btn-all-events">Tutte le news</a>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>