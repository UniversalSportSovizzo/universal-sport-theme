<?php get_header(); ?>

<main id="contenuto-principale" class="container page-content single-news-main single-match-custom">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            
            $event_id = get_the_ID();
            
            // 1. SQUADRE E LOGHI
            $teams = get_post_meta($event_id, 'sp_team', false);
            $teams = array_filter($teams);
            $home_id = isset($teams[0]) ? $teams[0] : 0;
            $away_id = isset($teams[1]) ? $teams[1] : 0;
            
            $home_name = $home_id ? get_the_title($home_id) : '';
            $away_name = $away_id ? get_the_title($away_id) : '';
            $home_logo = $home_id ? get_the_post_thumbnail($home_id, 'sportspress-fit-icon') : '';
            $away_logo = $away_id ? get_the_post_thumbnail($away_id, 'sportspress-fit-icon') : '';
            
            // 2. RISULTATI E PARZIALI
            $results = get_post_meta($event_id, 'sp_results', true);
            $home_res = (is_array($results) && isset($results[$home_id])) ? $results[$home_id] : array();
            $away_res = (is_array($results) && isset($results[$away_id])) ? $results[$away_id] : array();
            
            $home_score = (isset($home_res['goals']) && $home_res['goals'] !== '') ? $home_res['goals'] : '-';
            $away_score = (isset($away_res['goals']) && $away_res['goals'] !== '') ? $away_res['goals'] : '-';
            
            if ($home_score === '-' && !empty($home_res)) {
                $nums = array_filter($home_res, 'is_numeric');
                if (!empty($nums)) $home_score = end($nums);
            }
            if ($away_score === '-' && !empty($away_res)) {
                $nums = array_filter($away_res, 'is_numeric');
                if (!empty($nums)) $away_score = end($nums);
            }
            
            $is_played = ($home_score !== '-' && $away_score !== '-');
            $event_time = get_the_time('H:i');
            $event_date = get_the_date('j F Y');
            
            $period_labels = array(
                'firsthalf'  => '1° T',
                'secondhalf' => '2° T',
                'tempo'      => '3° T',
                'fourt'      => '4° T',
                'goals'      => 'Totale'
            );
            $has_periods = false;
            foreach ($period_labels as $key => $label) {
                if ((isset($home_res[$key]) && $home_res[$key] !== '') || (isset($away_res[$key]) && $away_res[$key] !== '')) {
                    $has_periods = true;
                    break;
                }
            }

            // 3. DETTAGLI
            $leagues = get_the_terms($event_id, 'sp_league');
            $league_name = ($leagues && !is_wp_error($leagues)) ? $leagues[0]->name : '';
            
            $seasons = get_the_terms($event_id, 'sp_season');
            $season_name = ($seasons && !is_wp_error($seasons)) ? $seasons[0]->name : '';
            
            // 4. LUOGO
            $venues = get_the_terms($event_id, 'sp_venue');
            $venue_name = '';
            $venue_address = '';
            if ($venues && !is_wp_error($venues)) {
                $venue_term = $venues[0];
                $venue_name = $venue_term->name;
                $t_id = $venue_term->term_id;
                $term_meta = get_option("taxonomy_$t_id");
                if (is_array($term_meta)) {
                    $venue_address = isset($term_meta['sp_address']) ? $term_meta['sp_address'] : '';
                }
            }
            ?>

            <!-- TESTATA -->
            <div class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
                <div class="title-underline"></div>
                <?php if ($is_played) : ?>
                    <a href="/partite-giocate/" class="back-link-match"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Torna alle partite giocate</a>
                <?php else : ?>
                    <a href="/prossime-partite/" class="back-link-match"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Torna alle prossime partite</a>
                <?php endif; ?>
            </div>

            <div class="sp-section-content sp-section-content-logos">
                <div class="sp-template sp-template-event-logos sp-template-event-logos-inline">
                    <div class="sp-event-logos sp-event-logos-2">
                        <span class="sp-team-logo">
                            <strong class="sp-team-name"><?php echo esc_html($home_name); ?></strong>
                            <?php echo $home_logo; ?>
                        </span>
                        <?php if ($is_played) : ?>
                            <span class="sp-dynamic-center is-score"><?php echo esc_html($home_score . ' - ' . $away_score); ?></span>
                        <?php else : ?>
                            <span class="sp-dynamic-center is-time"><?php echo esc_html($event_time); ?></span>
                        <?php endif; ?>
                        <span class="sp-team-logo">
                            <?php echo $away_logo; ?>
                            <strong class="sp-team-name"><?php echo esc_html($away_name); ?></strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ROSE E STAFF TECNICO -->
            <div class="sp-section-content sp-section-content-performance">
                <?php sp_get_template('event-performance.php'); ?>
            </div>

            <!-- NUOVA SEZIONE INFORMAZIONI -->
            <div class="match-modern-sections">

                <?php if ($is_played && $has_periods) : ?>
                <!-- CARD RISULTATI PARZIALI -->
                <div class="match-info-card match-results-card">
                    <h3><i class="fa-solid fa-futbol"></i> Risultati Parziali</h3>
                    <div class="quarters-scoreboard">
                        <div class="quarters-row quarters-header">
                            <div class="q-team-col">Squadra</div>
                            <?php foreach ($period_labels as $key => $label) : ?>
                                <div class="q-period-col <?php echo ($key === 'goals') ? 'q-total-col' : ''; ?>"><?php echo esc_html($label); ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="quarters-row">
                            <div class="q-team-col">
                                <?php echo $home_logo; ?>
                                <span><?php echo esc_html($home_name); ?></span>
                            </div>
                            <?php foreach ($period_labels as $key => $label) : 
                                $val = (isset($home_res[$key]) && $home_res[$key] !== '') ? $home_res[$key] : '-';
                            ?>
                                <div class="q-period-col <?php echo ($key === 'goals') ? 'q-total-col' : ''; ?>"><?php echo esc_html($val); ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="quarters-row">
                            <div class="q-team-col">
                                <?php echo $away_logo; ?>
                                <span><?php echo esc_html($away_name); ?></span>
                            </div>
                            <?php foreach ($period_labels as $key => $label) : 
                                $val = (isset($away_res[$key]) && $away_res[$key] !== '') ? $away_res[$key] : '-';
                            ?>
                                <div class="q-period-col <?php echo ($key === 'goals') ? 'q-total-col' : ''; ?>"><?php echo esc_html($val); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="single-match-info-grid">
                    <!-- CARD DETTAGLI -->
                    <div class="match-info-card">
                        <h3><i class="fa-solid fa-calendar-check"></i> Dettagli</h3>
                        <div class="match-info-content">
                            <div class="info-row">
                                <span class="info-label"><i class="fa-regular fa-calendar"></i> Data</span>
                                <span class="info-value"><?php echo esc_html($event_date); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><i class="fa-regular fa-clock"></i> Ora</span>
                                <span class="info-value"><?php echo esc_html($event_time); ?></span>
                            </div>
                            <?php if ($league_name) : ?>
                            <div class="info-row">
                                <span class="info-label"><i class="fa-solid fa-trophy"></i> Competizione</span>
                                <span class="info-value"><?php echo esc_html($league_name); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($season_name) : ?>
                            <div class="info-row">
                                <span class="info-label"><i class="fa-solid fa-flag-checkered"></i> Stagione</span>
                                <span class="info-value"><?php echo esc_html($season_name); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CARD LUOGO + MAPPA -->
                    <?php if ($venue_name) : ?>
                    <div class="match-info-card">
                        <h3><i class="fa-solid fa-location-dot"></i> Luogo</h3>
                        <div class="match-info-content">
                            <div class="venue-badge-title">
                                <strong><?php echo esc_html($venue_name); ?></strong>
                            </div>
                            
                            <!-- Stampa la mappa nativa di SportsPress -->
                            <div class="match-map-wrapper">
                                <?php sp_get_template( 'event-venue.php' ); ?>
                            </div>

                            <?php if ($venue_address) : ?>
                            <p class="venue-address-text">
                                <i class="fa-solid fa-map-pin"></i> <?php echo esc_html($venue_address); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
            
            <!-- SCRIPT PER CORREGGERE L'ARIA LABEL "NESSUN GOL FATTO" -->
            <script>
            jQuery(document).ready(function($) {
                $('.sp-event-performance tr[aria-label*="Nessun gol subito"]').each(function() {
                    var oldLabel = $(this).attr('aria-label');
                    $(this).attr('aria-label', oldLabel.replace('Nessun gol subito', 'Nessun gol fatto'));
                });
            });
            </script>

            <?php
        endwhile;
    endif;
    ?>
</main>

<?php get_footer(); ?>