<?php
/**
 * Event Performance Table
 *
 * @author      ThemeBoy
 * @package     SportsPress/Templates
 * @version     2.7.18
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Initialize totals
$totals = array();

// Set null
if ( ! isset( $section ) ) {
	$section = -1;
}
if ( ! isset( $section_label ) ) {
	$section_label = null;
}
if ( ! isset( $class ) ) {
	$class = null;
}

// Initialize arrays
if ( ! isset( $lineups ) ) {
	$lineups = array();
}
if ( ! isset( $subs ) ) {
	$subs = array();
}

$responsive = get_option( 'sportspress_enable_responsive_tables', 'no' ) == 'yes' ? true : false;
// Create a unique identifier based on the current time in microseconds
$identifier = uniqid( 'performance_' );
// If responsive tables are enabled then load the inline css code
if ( true == $responsive && $mode == 'values' ) {
	// sportspress_responsive_tables_css( $identifier );
}
$i = 0;
?>
<div class="sp-template sp-template-event-performance sp-template-event-performance-<?php echo esc_attr( $mode ); ?>
															   <?php
																if ( isset( $class ) ) {
																	echo ' ' . esc_attr( $class ); }
																?>
">
	<?php if ( $caption ) : ?>
		<h4 class="sp-table-caption"><?php echo wp_kses_post( $caption ); ?></h4>
	<?php endif; ?>
	<div class="sp-table-wrapper">
		<table class="sp-event-performance sp-data-table
		<?php
		if ( $mode == 'values' ) {
			?>
			
			<?php
			if ( $scrollable ) {
				?>
			 sp-scrollable-table
						<?php
			}if ( $responsive ) {
				echo ' sp-responsive-table ' . esc_attr( $identifier ); } if ( $sortable ) {
				?>
	 sp-sortable-table<?php } ?><?php } ?>">
			<thead>
				<tr>
					<?php if ( $mode == 'values' ) : ?>
						<?php if ( $show_players ) : ?>
							<?php if ( apply_filters( 'sportspress_event_performance_show_numbers', $show_numbers, $section ) ) { ?>
								<th class="data-number">#</th>
							<?php } ?>
							<th class="data-name">
								<?php if ( isset( $section_label ) ) { ?>
									<?php echo wp_kses_post( $section_label ); ?>
								<?php } else { ?>
									<?php esc_attr_e( 'Player', 'sportspress' ); ?>
								<?php } ?>
							</th>
						<?php endif; ?>
						<?php foreach ( $labels as $key => $label ) : ?>
							<th class="data-<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $label ); ?></th>
						<?php endforeach; ?>
					<?php endif; ?>
				</tr>
			</thead>
			<?php if ( $show_players ) : ?>
				<tbody>
					<?php

					$lineups = array_filter( $data, array( $event, 'lineup_filter' ) );
					$subs    = array_filter( $data, array( $event, 'sub_filter' ) );

					$lineup_sub_relation = array();
					foreach ( $subs as $sub_id => $sub ) :
						if ( ! $sub_id ) {
							continue;
						}
						$i                         = sp_array_value( $sub, 'sub', 0 );
						$lineup_sub_relation[ $i ] = $sub_id;
					endforeach;

					$data = apply_filters( 'sportspress_event_performance_players', $data, $lineups, $subs, $mode );

					$stars_type = get_option( 'sportspress_event_performance_stars_type', 0 );

					foreach ( $data as $player_id => $row ) :

						if ( ! $player_id ) {
							continue;
						}

						$name = get_the_title( $player_id );

						if ( ! $name ) {
							continue;
						}

                        // --- INIZIO: CALCOLO ARIA-LABEL NATIVO PER TRANSLATEPRESS ---
                        $player_number_txt = sp_array_value( $row, 'number', '-' );
                        
                        // Trova i gol (gestendo sia i numeri semplici che le liste di minuti)
                        $goals = 0;
                        if ( isset($row['goals']) ) {
                            $goals = is_array($row['goals']) ? count($row['goals']) : intval($row['goals']);
                        } else {
                            foreach ( $labels as $k => $label ) {
                                if ( sp_array_value( $formats, $k, 'number' ) === 'icons' && isset($row[$k]) ) {
                                    $goals = is_array($row[$k]) ? count($row[$k]) : intval($row[$k]);
                                    break;
                                }
                            }
                        }

						// --- INIZIO: CALCOLO ARIA-LABEL HARDCODED ---
                        // Controlliamo la lingua usando la tua funzione
                        $is_en = function_exists('uss_is_english') && uss_is_english();
						
						if($is_en) {
							$txt_number   = __( 'Number', 'universal-sport' );
							$txt_scored_1 = __( 'Has scored 1 goal.', 'universal-sport' );
							$txt_scored_n = sprintf( __( 'Has scored %d goals.', 'universal-sport' ), $goals );
							$txt_no_goals = __( 'No goals scored.', 'universal-sport' );
						} else {
							$txt_number   = __( 'Numero', 'universal-sport' );
							$txt_scored_1 = __( 'Ha segnato 1 gol.', 'universal-sport' );
							$txt_scored_n = sprintf( __( 'Ha segnato %d gol.', 'universal-sport' ), $goals );
							$txt_no_goals = __( 'Nessun gol segnato.', 'universal-sport' );
						}

                        $aria_label = $txt_number . ' ' . wp_strip_all_tags($player_number_txt) . ', ' . wp_strip_all_tags($name) . '. ';
                        if ( $goals > 0 ) {
                            $aria_label .= ( $goals === 1 ) ? $txt_scored_1 : $txt_scored_n;
                        } else {
                            $aria_label .= $txt_no_goals;
                        }
                        // --- FINE: CALCOLO ARIA-LABEL ---

                        // Stampa la riga aggiungendo aria-label e tabindex
						echo '<tr class="' . esc_attr( sp_array_value( $row, 'status', 'lineup' ) ) . ' ' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '" aria-label="' . esc_attr($aria_label) . '" tabindex="0">';

						if ( apply_filters( 'sportspress_event_performance_show_numbers', $show_numbers, $section ) ) {
							$number = sp_array_value( $row, 'number', '&nbsp;' );

							// Player number
							echo '<td class="data-number" data-label="#">' . wp_kses_post( $number ) . '</td>';
						}

						if ( $link_posts ) :
                            // --- INIZIO: RIMOZIONE LINK NOME ---
                            // Sostituisce il tag <a href="..."> con uno span neutro
							$name      = '<span class="sp-no-link-name">' . wp_kses_post( $name ) . '</span>';
                            // --- FINE RIMOZIONE LINK ---
						endif;

						if ( $stars_type ) :
							$player_stars = sp_array_value( $stars, $player_id, 0 );
							if ( $player_stars ) :
								switch ( $stars_type ) :
									case 1:
										$name .= ' <span class="sp-event-stars"><i class="sp-event-star dashicons dashicons-star-filled" title="' . esc_attr__( 'Player of the Match', 'sportspress' ) . '"></i></span>';
										break;
									case 2:
										$name .= ' <span class="sp-event-stars">' . str_repeat( '<i class="sp-event-star dashicons dashicons-star-filled" title="' . esc_attr__( 'Stars', 'sportspress' ) . '"></i>', $player_stars ) . '</span>';
										break;
									case 3:
										$name .= ' <span class="sp-event-stars"><i class="sp-event-star sp-event-star-' . $player_stars . '  dashicons dashicons-star-filled" title="' . esc_attr__( 'Stars', 'sportspress' ) . '"></i><span class="sp-event-star-number">' . $player_stars . '</span></span>';
										break;
								endswitch;
							endif;
						endif;

						if ( array_key_exists( $player_id, $lineup_sub_relation ) ) :
							$name .= ' <span class="sub-in" title="' . get_the_title( $lineup_sub_relation[ $player_id ] ) . '">' . sp_array_value( sp_array_value( $data, $lineup_sub_relation[ $player_id ], array() ), 'number', null ) . '</span>';
						elseif ( isset( $row['sub'] ) && $row['sub'] ) :
							$subbed = (int) $row['sub'];
							$name  .= ' <span class="sub-out" title="' . get_the_title( $row['sub'] ) . '">' . sp_array_value( sp_array_value( $data, $subbed, array() ), 'number', null ) . '</span>';
						endif;

						$content  = '';
						$position = null;

						foreach ( $labels as $key => $label ) :
							if ( 'name' == $key ) {
								continue;
							}

							$format      = sp_array_value( $formats, $key, 'number' );
							$placeholder = sp_get_format_placeholder( $format );

							if ( ! array_key_exists( $key, $totals ) ) :
								$totals[ $key ] = $placeholder;
							endif;

							if ( 'time' === $format ) :
								$totals[ $key ] = '&nbsp;';
							endif;

							$value = '-';
							if ( $key == 'position' ) :
								$positions = array();
								if ( array_key_exists( $key, $row ) && $row[ $key ] != '' ) :
									$position_ids = (array) $row[ $key ];
								else :
									$position_ids = (array) sp_get_the_term_id( $player_id, 'sp_position' );
								endif;

								foreach ( $position_ids as $position_id ) {
									$player_position = get_term_by( 'id', $position_id, 'sp_position' );
									if ( $player_position ) {
										$positions[] = $player_position->name;
									}
								}

								$positions = array_unique( $positions );

								if ( sizeof( $positions ) ) :
									$value = $position = implode( ', ', $positions );
								endif;
							else :
								if ( array_key_exists( $key, $row ) && $row[ $key ] !== '' ) :
									if ( 'checkbox' === $format ) :
										$value = '<span class="sp-checkbox">' . $row[ $key ] . '</span>';
									else :
										$value = $row[ $key ];
									endif;
								else :
									$value = $placeholder;
								endif;

								if ( 'number' === $format ) :
									$add             = apply_filters( 'sportspress_event_performance_add_value', floatval( $value ), $key );
									$totals[ $key ] += $add;
								endif;
							endif;

							if ( $mode == 'values' ) :
								$content .= '<td class="data-' . $key . '" data-label="' . $labels[ $key ] . '">' . $value . '</td>';
							elseif ( ( is_array($value) ? count($value) : intval($value) ) && $mode == 'icons' ) :
                                
                                // --- INIZIO: MODIFICA GRAFICA PALLONI ---
								$performance_id = sp_array_value( $performance_ids, $key, null );
								$single_icon    = '';
                                
                                // Determiniamo il numero esatto (se è una lista di minuti, la contiamo)
                                $num_icons = is_array($value) ? count($value) : intval($value);
								
                                if ( $performance_id && has_post_thumbnail( $performance_id ) ) {
                                    $single_icon = get_the_post_thumbnail( $performance_id, 'sportspress-fit-mini' );
                                } else {
                                    $single_icon = apply_filters( 'sportspress_event_performance_icons', '', $performance_id, 1 );
                                }
                                
                                // Rimuoviamo il title inutile per l'accessibilità
                                $single_icon = preg_replace('/title="[^"]*"/i', '', $single_icon);
                                
                                // Struttura: Numero + Pallone
                                $content .= '<span class="sp-icon-multiplier">' . $num_icons . '</span> ' . $single_icon;
                                // --- FINE MODIFICA PALLONI ---
                                
							endif;
						endforeach;

						if ( isset( $position ) && $mode == 'icons' ) :
							$name .= ' <small class="sp-player-position">' . $position . '</small>';
						endif;

						echo '<td class="data-name" data-label="' . ( isset( $section_label ) ? esc_attr( $section_label ) : esc_attr__( 'Player', 'sportspress' ) ) . '">' . wp_kses_post( $name ) . '</td>';

						if ( $mode == 'icons' ) :
							echo '<td class="sp-performance-icons">' . wp_kses_post( $content ) . '</td>';
						else :
							echo wp_kses_post( $content );
						endif;

						echo '</tr>';

						$i++;

					endforeach;

					foreach ( $labels as $key => $label ) :
						$format = sp_array_value( $formats, $key, 'number' );
						if ( 'equation' === $format ) :
							$post = get_page_by_path( $key, OBJECT, 'sp_performance' );
							if ( $post ) {
								$totals[ $key ] = sp_solve( get_post_meta( $post->ID, 'sp_equation', true ), $totals, get_post_meta( $post->ID, 'sp_precision', true ) );
							}
						endif;
					endforeach;
					?>
				</tbody>
			<?php endif; ?>
			<?php if ( apply_filters( 'sportspress_event_performance_show_footer', $show_total ) ) : ?>
				<<?php echo ( $show_players ? 'tfoot' : 'tbody' ); ?>>
					<?php
					do_action( 'sportspress_event_performance_table_footer', $data, $labels, $section, $performance_ids );
					if ( $show_total && ( ! $primary || sizeof( array_intersect_key( $totals, array_flip( (array) $primary ) ) ) ) ) {
						?>
						<tr class="sp-total-row <?php echo ( $i % 2 == 0 ? 'odd' : 'even' ); ?>">
							<?php
							if ( $show_players ) :
								if ( apply_filters( 'sportspress_event_performance_show_numbers', $show_numbers, $section ) ) {
									echo '<td class="data-number" data-label="&nbsp;">&nbsp;</td>';
								}
								if ( $mode == 'values' ) :
									echo '<td class="data-name" data-label="&nbsp;">' . esc_attr__( 'Total', 'sportspress' ) . '</td>';
								endif;
							endif;

							$row = sp_array_value( $data, 0, array() );

							if ( $mode == 'icons' ) {
								echo '<td class="sp-performance-icons" colspan="2">';
							}

							foreach ( $labels as $key => $label ) :
								$format = sp_array_value( $formats, $key, 'number' );
								if ( 'name' == $key ) {
									continue;
								}
								if ( $key == 'position' ) :
									$value = '&nbsp;';
								else :
									if ( $primary && $key !== $primary ) :
										$value = '&nbsp;';
									elseif ( 'equation' !== $format && array_key_exists( $key, $row ) && $row[ $key ] != '' ) :
										$value = $row[ $key ];
									else :
										$value = apply_filters( 'sportspress_event_performance_table_total_value', sp_array_value( $totals, $key, 0 ), $data, $key );
									endif;
								endif;

								if ( $mode == 'values' ) :
									if ( $key == 'position' ) {
										echo '<td class="data-' . esc_attr( $key ) . '" data-label="&nbsp;">' . wp_kses_post( $value ) . '</td>';
									} else {
										echo '<td class="data-' . esc_attr( $key ) . '" data-label="' . esc_attr( $labels[ $key ] ) . '">' . wp_kses_post( $value ) . '</td>';
									}
								elseif ( ( is_array($value) ? count($value) : intval($value) ) && $mode == 'icons' ) :
                                    
                                    // --- INIZIO: MODIFICA GRAFICA PALLONI RIGA TOTALI ---
									$performance_id = sp_array_value( $performance_ids, $key, null );
									$single_icon    = '';
                                    
                                    $num_icons = is_array($value) ? count($value) : intval($value);
                                    
									if ( $performance_id && has_post_thumbnail( $performance_id ) ) {
										$single_icon = get_the_post_thumbnail( $performance_id, 'sportspress-fit-mini' );
									} else {
                                        $single_icon = apply_filters( 'sportspress_event_performance_icons', '', $performance_id, 1 );
                                    }
                                    
                                    $single_icon = preg_replace('/title="[^"]*"/i', '', $single_icon);
                                    
									echo wp_kses_post( '<span class="sp-icon-multiplier">' . $num_icons . '</span> ' . $single_icon );
                                    // --- FINE MODIFICA PALLONI RIGA TOTALI ---
                                    
								endif;
							endforeach;

							if ( $mode == 'icons' ) {
								echo '</td>';
							}
							?>
						</tr>
					<?php } ?>
				</<?php echo ( $show_players ? 'tfoot' : 'tbody' ); ?>>
			<?php endif; ?>
		</table>
		<?php
		if ( isset( $show_staff ) && $show_staff ) {
			$template_content = sp_get_template(
						'event-staff.php',
						array(
							'id'    => $id,
							'index' => $index,
						)
					);
			if ( $template_content !== null ) {
				echo wp_kses_post( $template_content );
			}
		}
		?>
	</div>
	<?php do_action( 'sportspress_after_event_performance_table', $data, $lineups, $subs, $class ); ?>
</div>