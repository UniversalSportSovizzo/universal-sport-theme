<?php
/**
 * Event Staff
 *
 * @author      ThemeBoy
 * @package     SportsPress/Templates
 * @version   2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$defaults = array(
    'id'         => get_the_ID(),
    'index'      => 0,
    'link_posts' => get_option( 'sportspress_link_staff', 'yes' ) == 'yes' ? true : false,
);

$staffs = array_filter( sp_array_between( (array) get_post_meta( $id, 'sp_staff', false ), 0, $index ) );

if ( ! $staffs ) {
    return;
}

extract( $defaults, EXTR_SKIP );
?>
<div class="sp-template sp-template-event-staff">
    <p class="sp-event-staff">
    <?php
    foreach ( $staffs as $staff_id ) :

        if ( ! $staff_id ) {
            continue;
        }

        $name = get_the_title( $staff_id );

        if ( ! $name ) {
            continue;
        }

        $staff = new SP_Staff( $staff_id );

        $roles = $staff->roles();
        if ( ! empty( $roles ) ) :
            $roles = wp_list_pluck( $roles, 'name' );
            echo wp_kses_post( implode( '<span class="sp-staff-role-delimiter">/</span>', $roles ) );
        else :
            esc_attr_e( 'Staff', 'sportspress' );
        endif;

        echo ': ';

        if ( $link_posts ) :
            // --- INIZIO: RIMOZIONE LINK NOME STAFF ---
            // Invece di stampare il tag <a>, stampiamo uno span neutro con la tua classe CSS.
            // Questo elimina alla radice il caricamento dei link e i flash visivi.
            $name = '<span class="sp-no-link-name">' . $name . '</span>';
            // --- FINE RIMOZIONE LINK ---
        endif;

        echo wp_kses_post( $name ) . '<br>';

    endforeach;
    ?>
    </p>
</div>