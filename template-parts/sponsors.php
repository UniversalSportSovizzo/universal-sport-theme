<?php
// Non mostrare la sezione sponsor nella pagina con slug 'partner'[cite: 19]
if ( ! is_page( 'partner' ) ) : ?>
    <section class="sponsors sponsors-prefooter" aria-labelledby="titolo-sponsor">
        <div>
            <div class="section-title text-center">
                <h2 id="titolo-sponsor">I Nostri Partner</h2>
                <p>Un ringraziamento a chi sostiene i nostri progetti sportivi</p>
            </div>
            
            <div class="sponsors-hierarchy">
                
                <!-- 1. Home Jersey Sponsor (Sempre in alto a tutta larghezza) -->
                <div class="sponsor-tier tier-home-jersey text-center">
                    <h3 class="tier-title tier-title-home-jersey">Home Jersey Sponsor</h3>
                    <?php echo do_shortcode('[lista_sponsor livello="home-jersey-sponsor" dimensione="home-jersey"]'); ?>
                </div>

                <!-- 2. Riga Flessibile: Official Partner + Supporter affiancati finché ci stanno -->
                <div class="sponsor-tiers-row">
                    
                    <div class="sponsor-tier tier-official text-center">
                        <h3 class="tier-title tier-title-official">Official Partner</h3>
                        <?php echo do_shortcode('[lista_sponsor livello="official-partner" dimensione="official"]'); ?>
                    </div>

                    <div class="sponsor-tier tier-supporter text-center">
                        <h3 class="tier-title tier-title-supporter">Supporter</h3>
                        <?php echo do_shortcode('[lista_sponsor livello="supporter" dimensione="supporter"]'); ?>
                    </div>

                </div>

            </div>
        </div>
    </section>
<?php endif; ?>