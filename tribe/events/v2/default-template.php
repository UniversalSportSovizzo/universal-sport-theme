<?php
/**
 * View: Default Template for Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/default-template.php
 */

use Tribe\Events\Views\V2\Template_Bootstrap;

get_header();
?>

<main id="contenuto-principale">
	
	<?php /* Aggiungiamo l'H1 fisso SOLO negli archivi, racchiuso nel suo container */ ?>
	<?php if ( ! is_singular( 'tribe_events' ) ) : ?>
		<div class="container">
			<header class="page-header" style="padding-bottom: 0;">
				<h1 class="page-title">Eventi</h1>
				<div class="title-underline"></div>
			</header>
		</div>
	<?php endif; ?>

	<?php 
	// Stampa il calendario vero e proprio (con i suoi spazi nativi intatti)
	echo tribe( Template_Bootstrap::class )->get_view_html(); 
	?>
	
</main>

<?php
get_footer();