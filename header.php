<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    
    <?php wp_head(); ?>
</head>
<body>
    
    <a href="#contenuto-principale" class="skip-link">Salta al contenuto principale</a>

    <header class="top-header">
        <div class="container header-flex">
            <div class="logo-area">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Torna alla Home Page">
                    
                    <?php if ( get_locale() === 'en_GB' || get_locale() === 'en_US' || strpos( $_SERVER['REQUEST_URI'], '/en/' ) !== false ) : ?>
                        <img src="/wp-content/uploads/2026/05/USS_Oriz-scaled.png" alt="Universal Sport Sovizzo - Back to Home page" class="main-logo" onerror="this.classList.add('hide-on-error'); this.nextElementSibling.classList.add('show-block-on-error');">
                    <?php else : ?>
                        <img src="/wp-content/uploads/2026/05/USS_Oriz-scaled.png" alt="Universal Sport Sovizzo - Torna alla Home page" class="main-logo" onerror="this.classList.add('hide-on-error'); this.nextElementSibling.classList.add('show-block-on-error');">
                    <?php endif; ?>
                    
                    <div class="brand-name logo-text-fallback">
                        <span class="yellow">UNIVERSAL</span> 
                        <span class="orange">SPORT</span> 
                        SOVIZZO
                    </div>

                </a>
            </div>
            
            <div class="social-area">
                <a href="https://www.instagram.com/universalsportsovizzo/" target="_blank" rel="noopener noreferrer" aria-label="Visita il nostro profilo Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                <a href="https://www.tiktok.com/@universalsportsovizzo" target="_blank" rel="noopener noreferrer" aria-label="Visita il nostro profilo TikTok"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
                <a href="https://www.facebook.com/profile.php?id=61587804963864" target="_blank" rel="noopener noreferrer" aria-label="Visita la nostra pagina Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                <a href="https://www.linkedin.com/company/universal-sport-sovizzo/posts/" target="_blank" rel="noopener noreferrer" aria-label="Visita il nostro profilo LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                <a href="https://www.youtube.com/@universalsportsovizzo" target="_blank" rel="noopener noreferrer" aria-label="Visita il nostro canale YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
            </div>
        </div>
    </header>

    <nav class="main-navbar" aria-label="Menu principale">
        <div class="container navbar-container">
            
            <!-- LOGO MOBILE (A sinistra) -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mobile-nav-logo" aria-label="Torna alla Home">
                <img src="/wp-content/uploads/2026/06/uss_oriz_bianco-scaled.png" alt="Universal Sport Sovizzo">
            </a>

            <!-- Bottone Hamburger (A destra) -->
            <button class="menu-toggle" aria-label="Apri menu" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
            
            <!-- PANNELLO LATERALE OFF-CANVAS -->
            <div class="offcanvas-menu">
                
                <!-- Testata del menu mobile (Stile Vicenza) -->
                <div class="offcanvas-header">
                    <button class="menu-close" aria-label="Chiudi menu">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <!-- Logo nella navbar laterale -->
                    <img src="/wp-content/uploads/2026/05/USS_Pitt_bianco_arancione_giallo-scaled.png" 
                        alt="Universal Sport Sovizzo" 
                        class="offcanvas-logo" 
                        onerror="this.classList.add('hide-on-error'); this.nextElementSibling.classList.add('show-inline-on-error');">

                    <span class="offcanvas-logo-fallback">Universal Sport Sovizzo</span>
                </div>

                <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'nav-links',
                    'fallback_cb'    => 'wp_page_menu', 
                ) );
                ?>
            </div>
        </div>
    </nav>