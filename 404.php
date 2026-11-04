<?php get_header(); ?>

<main id="contenuto-principale" class="container text-center error-404-container">
    <h1 class="error-404-title">404</h1>
    <h2 class="error-404-subtitle">Pagina non trovata</h2>
    <p class="error-404-text">Ci dispiace, ma la pagina che stai cercando non esiste...</p>
    <a href="<?php echo home_url(); ?>" class="btn-back-home">&larr; Torna alla Home</a>
</main>

<?php get_footer(); ?>