<?php
/**
 * Player List
 *
 * @author      ThemeBoy
 * @package     SportsPress/Templates
 * @version   2.7.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$defaults = array(
	'id'                    => get_the_ID(),
	'title'                 => false,
	'number'                => -1,
	'grouptag'              => 'h4',
	'columns'               => null,
	'grouping'              => null,
	'orderby'               => 'default',
	'order'                 => 'ASC',
	'show_all_players_link' => false,
	'show_title'            => get_option( 'sportspress_list_show_title', 'yes' ) == 'yes' ? true : false,
	'show_player_photo'     => get_option( 'sportspress_list_show_photos', 'no' ) == 'yes' ? true : false,
	'show_player_flag'      => get_option( 'sportspress_list_show_flags', 'no' ) == 'yes' ? true : false,
	'team_format'           => get_option( 'sportspress_list_team_format', 'name' ),
	'link_posts'            => get_option( 'sportspress_link_players', 'yes' ) == 'yes' ? true : false,
	'link_teams'            => get_option( 'sportspress_link_teams', 'no' ) == 'yes' ? true : false,
	'responsive'            => get_option( 'sportspress_enable_responsive_tables', 'no' ) == 'yes' ? true : false,
	'sortable'              => get_option( 'sportspress_enable_sortable_tables', 'yes' ) == 'yes' ? true : false,
	'scrollable'            => get_option( 'sportspress_enable_scrollable_tables', 'yes' ) == 'yes' ? true : false,
	'paginated'             => get_option( 'sportspress_list_paginated', 'yes' ) == 'yes' ? true : false,
	'rows'                  => get_option( 'sportspress_list_rows', 10 ),
	'leagues'               => null,
	'seasons'               => null,
	'team'                  => null,
);

extract( $defaults, EXTR_SKIP );

// Backward compatibility
if ( isset( $performance ) ) {
	$columns = $performance;
}

// Determine number of players to display
if ( -1 === $number ) :
	$number = (int) get_post_meta( $id, 'sp_number', true );
	if ( $number <= 0 ) {
		$number = -1;
	}
endif;

// Explode into array
if ( null !== $columns && ! is_array( $columns ) ) {
	$columns = explode( ',', $columns );
}

$list = new SP_Player_List( $id );
if ( isset( $columns ) && null !== $columns ) :
	$list->columns = $columns;
endif;

$data = apply_filters( 'sportspress_player_list_data', $list->data( false, $leagues, $seasons, $team ), $id );

// The first row should be labels
$labels = $data[0];

// Create a unique identifier based on the current time in microseconds
$identifier = uniqid( 'playerlist_' );
// If responsive tables are enabled then load the inline css code
if ( true == $responsive ) {
	// sportspress_responsive_tables_css( $identifier );
}
// Remove the first row and 'head' row to leave us with the actual data
unset( $data[0] );

// --- INIZIO LOGICA PAGINAZIONE INTELLIGENTE ---
$conteggio_giocatori = count($data);

if ( $conteggio_giocatori <= 15 ) {
    // Se i giocatori sono 15 o meno, spegniamo del tutto il JavaScript della paginazione
    $paginated = false; 
} else {
    // Se sono più di 15, accendiamo la paginazione e la forziamo a 10 righe per pagina
    $paginated = true;
    $rows = 10; 
}
// --- FINE LOGICA PAGINAZIONE INTELLIGENTE ---

if ( $grouping === null || $grouping === 'default' ) :
	$grouping = $list->grouping;
endif;

if ( $orderby == 'default' ) :
	$orderby = $list->orderby;
	$order   = $list->order;
else :
	$list->priorities = array(
		array(
			'key'   => $orderby,
			'order' => $order,
		),
	);
	uasort( $data, array( $list, 'sort' ) );
endif;

$output = '';

if ( $grouping === 'position' ) :
	$groups = get_terms(
		'sp_position',
		array(
			'orderby'    => 'meta_value_num',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => 'sp_order',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'sp_order',
					'compare' => 'EXISTS',
				),
			),
		)
	);
else :
	if ( $show_title && false === $title && $id ) :
		$caption = $list->caption;
		if ( $caption ) {
			$title = $caption;
		} else {
			$title = get_the_title( $id );
		}
	endif;
	if ( $title ) {
		$output .= '<' . $grouptag . ' class="sp-table-caption">' . $title . '</' . $grouptag . '>';
	}
	$group          = new stdClass();
	$group->term_id = null;
	$group->name    = null;
	$group->slug    = null;
	$groups         = array( $group );
endif;

foreach ( $groups as $group ) :
	$i = 0;

	if ( intval( $number ) > 0 ) {
		$limit = $number;
	}

	// --- INIZIO: INTESTAZIONI E ARIA-LABEL MARCATORI HARDCODED ---
    $is_en = function_exists('uss_is_english') && uss_is_english();

	$thead = '<thead>' . '<tr>';

	if ( ! is_array( $labels ) || array_key_exists( 'number', $labels ) ) :
		if ( in_array( $orderby, array( 'number', 'name' ) ) ) :
            $txt_num_aria = $is_en ? 'Shirt Number' : 'Numero di maglia';
			$thead .= '<th class="data-number" aria-label="' . esc_attr($txt_num_aria) . '">#</th>';
		else :
            $txt_pos_th = $is_en ? 'Pos' : 'Pos';
            $txt_pos_aria = $is_en ? 'Rank' : 'Posizione in classifica';
			$thead .= '<th class="data-rank" aria-label="' . esc_attr($txt_pos_aria) . '">' . $txt_pos_th . '</th>';
		endif;
	endif;

	foreach ( $labels as $key => $label ) :
		if ( $key !== 'number' && ( ! is_array( $columns ) || $key == 'name' || in_array( $key, $columns ) ) ) {
            
            $aria_attr = '';
            if ($key === 'name') { 
				$txt_name = $is_en ? 'Player\'s Name' : 'Nome del giocatore';
				$aria_attr = ' aria-label="' . esc_attr($txt_name) . '"';
			}
            if ($key === 'position') { 
                $txt_role = $is_en ? 'Player\'s Role' : 'Ruolo del giocatore';
                $label = '<span class="sr-only">' . $txt_role . '</span>'; 
                $aria_attr = ' aria-label="' . esc_attr($txt_role) . '"';
            }
            if ($key === 'goals') { 
                $label = $is_en ? 'Goals' : 'Gol'; 
                $txt_goals_aria = $is_en ? 'Goals scored' : 'Gol segnati';
                $aria_attr = ' aria-label="' . esc_attr($txt_goals_aria) . '"';
            }
            if ($key === 'appearances') { 
                $label = $is_en ? 'GP' : 'PG'; 
                $txt_app_aria = $is_en ? 'Matches played' : 'Numero di partite giocate';
                $aria_attr = ' aria-label="' . esc_attr($txt_app_aria) . '"';
            }
            
			$thead .= '<th class="data-' . $key . '"' . $aria_attr . '>' . $label . '</th>';
		}
	endforeach;
    // --- FINE ---

	$thead .= '</tr>' . '</thead>';

	$tbody = '';
    
    // --- INIZIO: VARIABILI PER CALCOLO EX-AEQUO IN PHP ---
    $last_goals = null;
    $last_rank = 0;
    $current_position = 0;
    // --- FINE ---

	foreach ( $data as $player_id => $row ) :
		if ( empty( $group->term_id ) || has_term( $group->term_id, 'sp_position', $player_id ) ) :

			if ( isset( $limit ) && $i >= $limit ) {
				continue;
			}

			$name = sp_array_value( $row, 'name', null );
			if ( ! $name ) {
				continue;
			}
            
            $current_position++;
            
            // Calcolo Ex-Aequo in base ai Gol
            $goals = isset($row['goals']) ? intval($row['goals']) : 0;
            if ( $last_goals !== null && $goals === $last_goals ) {
                $rank_to_show = $last_rank;
            } else {
                $rank_to_show = $current_position;
                $last_rank = $rank_to_show;
                $last_goals = $goals;
            }

			$tbody .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

			// --- INIZIO: STAMPA DELLA POSIZIONE (CON CLASSI RANK) ---
			if ( ! is_array( $labels ) || array_key_exists( 'number', $labels ) ) :
				if ( isset( $orderby ) && $orderby != 'number' ) :
                    $rank_class = '';
                    if ($rank_to_show === 1) $rank_class = ' rank-1';
                    if ($rank_to_show === 2) $rank_class = ' rank-2';
                    if ($rank_to_show === 3) $rank_class = ' rank-3';
					$tbody .= '<td class="data-rank' . $rank_class . '" data-label="Pos">' . $rank_to_show . '</td>';
				else :
					$tbody .= '<td class="data-number" data-label="Pos">' . sp_array_value( $row, 'number', '&nbsp;' ) . '</td>';
				endif;
			endif;
            // --- FINE ---

			$name_class = '';

			if ( $show_player_photo ) :
				if ( has_post_thumbnail( $player_id ) ) :
					$logo        = get_the_post_thumbnail( $player_id, 'sportspress-fit-icon' );
					$name        = '<span class="player-photo">' . $logo . '</span>' . $name;
					$name_class .= ' has-photo';
				endif;
				endif;

			if ( $show_player_flag ) :
				$player        = new SP_Player( $player_id );
				$nationalities = $player->nationalities();
				if ( ! empty( $nationalities ) ) :
					foreach ( $nationalities as $nationality ) :
						$name = '<span class="player-flag">' . sp_flags( $nationality ) . '</span>' . $name;
					endforeach;
					$name_class .= ' has-photo';
				endif;
				endif;

			if ( $link_posts ) :
				$name = '<span class="sp-no-link-name">' . $name . '</span>';
			endif;

			$tbody .= '<td class="data-name' . $name_class . '" data-label="' . $labels['name'] . '">' . $name . '</td>';

			if ( array_key_exists( 'team', $labels ) ) :
				$team      = sp_array_value( $row, 'team', get_post_meta( $id, 'sp_current_team', true ) );
				$team_name = $team ? sp_team_short_name( $team ) : '-';
				if ( $team_format == 'logo' && has_post_thumbnail( $team ) ) {
					$logo      = get_the_post_thumbnail( $team, 'sportspress-fit-icon', array( 'title' => '' . $team_name . '' ) );
					$team_name = '<span class="team-logo">' . $logo . '</span>';
				}
				if ( $link_teams && false !== get_post_status( $team ) ) :
					$team_name = '<span class="sp-no-link-name">' . $team_name . '</span>';
				endif;
				$tbody .= '<td class="data-team" data-label="' . $labels['team'] . '">' . $team_name . '</td>';
				endif;

			if ( array_key_exists( 'position', $labels ) ) :
				$position = sp_array_value( $row, 'position', null );
				if ( null === $position || ! $position ) :
					$positions = wp_strip_all_tags( get_the_term_list( $player_id, 'sp_position', '', ', ' ) );
				else :
					$position_term = get_term_by( 'id', $position, 'sp_position', ARRAY_A );
					$positions     = sp_array_value( $position_term, 'name', '&mdash;' );
				endif;
				$tbody .= '<td class="data-position" data-label="' . $labels['position'] . '">' . $positions . '</td>';
				endif;

			foreach ( $labels as $key => $value ) :
				if ( in_array( $key, array( 'number', 'name', 'team', 'position' ) ) ) {
					continue;
				}
				if ( ! is_array( $columns ) || in_array( $key, $columns ) ) {
					$label = $labels[ $key ];
					if ( preg_match( '/title="(.*?)"/', $value, $new_label ) ) {
						$label = $new_label[1];
					}
					$tbody .= '<td class="data-' . $key . '" data-label="' . $label . '">' . sp_array_value( $row, $key, '&mdash;' ) . '</td>';
				}
				endforeach;

			$tbody .= '</tr>';

			$i++;

	endif;
endforeach;

	if ( $i === 0 ) {
		continue;
	}

	$output .= '<div class="sp-template sp-template-player-list">';

	if ( ! empty( $group->name ) ) :
		$output .= '<a name="group-' . $group->slug . '" id="group-' . $group->slug . '"></a>';
		$output .= '<' . $grouptag . ' class="sp-table-caption player-group-name player-list-group-name">' . $group->name . '</' . $grouptag . '>';
	endif;

	$output .= '<div class="sp-table-wrapper">' .
		'<table class="sp-player-list sp-data-table' . ( $sortable ? ' sp-sortable-table' : '' ) . ( $responsive ? ' sp-responsive-table ' . $identifier : '' ) . ( $scrollable ? ' sp-scrollable-table' : '' ) . ( $paginated ? ' sp-paginated-table' : '' ) . '" data-sp-rows="' . $rows . '">';

	$output .= $thead . '<tbody>';

	$output .= $tbody;

	$output .= '</tbody>' . '</table>' . '</div>';

	if ( $show_all_players_link ) :
		$output .= '<div class="sp-player-list-link sp-view-all-link"><a href="' . get_permalink( $id ) . '">' . esc_attr__( 'View all players', 'sportspress' ) . '</a></div>';
	endif;

	$output .= '</div>';
endforeach;
?>
<?php
echo wp_kses_post( $output );
