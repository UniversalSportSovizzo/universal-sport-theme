<?php
if (
    !is_page( 'partner' ) && !is_page( 'privacy-policy' ) && !is_page( 'cookie-policy-ue' ) && !is_page( 'safeguarding' ) &&
    (
        is_page() ||
        is_single() ||
        is_singular( 'tribe_events' ) ||
        ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() )
    )
) {
    get_template_part( 'template-parts/sponsors' );
}
?>

<footer class="main-footer">
    <div class="container footer-content">
        
        <div class="footer-col">
            <div class="footer-title">Universal Sport Sovizzo</div>
            <p>Con il calcio universale e la boccia inclusiva promuoviamo uno sport inclusivo in cui tutti possono scendere in campo, mettersi in gioco e sentirsi parte di una squadra.</p>
        </div>
        
        <nav class="footer-col" aria-label="Menu secondario">
            <div class="footer-title">Link Utili</div>
            <?php
            wp_nav_menu( array(
                'theme_location' => 'footer-utili',
                'container'      => false,       // Evita che WP aggiunga dei <div> in più
                'menu_class'     => '',          // Lascia il tag <ul> pulito per il tuo CSS
                'fallback_cb'    => false        // Se non c'è nessun menu, non mostra nulla
            ) );
            ?>
        </nav>
        
        <div class="footer-col">
            <div class="footer-title">I Nostri Social</div>
            <p>Seguici online per non perderti nessun aggiornamento.</p>
            <div class="footer-social">
                <a href="https://www.instagram.com/universalsportsovizzo/" target="_blank" rel="noopener noreferrer" aria-label="Visita il nostro profilo Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                <a href="https://www.tiktok.com/@universalsportsovizzo" target="_blank" rel="noopener noreferrer" aria-label="Visita il nostro profilo TikTok"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
                <a href="https://www.facebook.com/profile.php?id=61587804963864" target="_blank" rel="noopener noreferrer" aria-label="Visita la nostra pagina Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                <a href="https://www.linkedin.com/company/universal-sport-sovizzo/posts/" target="_blank" rel="noopener noreferrer" aria-label="Visita il nostro profilo LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                <a href="https://www.youtube.com/@universalsportsovizzo" target="_blank" rel="noopener noreferrer" aria-label="Visita il nostro canale YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom text-center" data-nosnippet>
        <p class="legal-string">
            <span class="legal-item"><strong>ASD UNIVERSAL SPORT SOVIZZO</strong></span>
            <span class="legal-item">SEDE LEGALE: VIA GIAMBATTISTA TIEPOLO 23, 36049 SOVIZZO (VI)</span>
            <span class="legal-item">C.F.: 95158640243</span>
            <span class="legal-item">P.IVA: 04626140240</span>
            <span class="legal-item">PEC: UNIVERSALSPORTSOVIZZO@SPIDMAIL.IT</span>
        </p>
        
        <!-- Riga unita: Copyright + Firma -->
        <p class="copyright-string">
            <span class="copy-block">&copy; <?php echo date('Y'); ?> ASD Universal Sport Sovizzo.</span>
            <span class="copy-block">Tutti i diritti riservati.</span>
            <span class="footer-divider">|</span>
            <span class="copy-block">Sviluppo Web: <a href="https://romeo0212.github.io/landing-page/" target="_blank" rel="noopener" class="developer-link">Romeo Calearo</a></span>
        </p>
    </div>
</footer>

    <?php wp_footer(); ?>
</body>
</html>

