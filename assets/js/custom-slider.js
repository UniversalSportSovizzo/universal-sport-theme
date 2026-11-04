document.addEventListener('DOMContentLoaded', function() {
    
    // --- ANTI-GLITCH RESIZE ---
    let resizeTimer;
    window.addEventListener('resize', () => {
        document.body.classList.add('resize-animation-stopper');
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            document.body.classList.remove('resize-animation-stopper');
        }, 400); 
    });

    // --- MENU MOBILE OFF-CANVAS ---
    const menuToggle = document.querySelector('.menu-toggle');
    const menuClose = document.querySelector('.menu-close');
    const offcanvasMenu = document.querySelector('.offcanvas-menu');

    if(menuToggle && offcanvasMenu) {
        menuToggle.addEventListener('click', () => {
            offcanvasMenu.classList.add('open');
            document.body.classList.add('no-scroll'); // Blocca lo scroll del sito
        });
    }

    if(menuClose && offcanvasMenu) {
        menuClose.addEventListener('click', () => {
            offcanvasMenu.classList.remove('open');
            document.body.classList.remove('no-scroll'); // Sblocca lo scroll del sito
        });
    }

    // === FIX: CHIUDI MENU CLICCANDO FUORI ===
    document.addEventListener('click', function(event) {
        if (offcanvasMenu && offcanvasMenu.classList.contains('open')) {
            const isClickInsideMenu = offcanvasMenu.contains(event.target);
            const isClickOnToggle = menuToggle.contains(event.target);

            if (!isClickInsideMenu && !isClickOnToggle) {
                offcanvasMenu.classList.remove('open');
                document.body.classList.remove('no-scroll'); // Sblocca lo scroll del sito
                menuToggle.setAttribute('aria-expanded', 'false'); 
            }
        }
    });

    // --- SLIDER IMMAGINI (Solo per le gallerie con classe .galleria-slider) ---
    const galleries = document.querySelectorAll('.galleria-slider');

    galleries.forEach(gallery => {
        const items = gallery.querySelectorAll('.wp-block-image');
        if(items.length <= 1) return;

        const dotsContainer = document.createElement('div');
        dotsContainer.className = 'slider-dots';
        gallery.parentNode.insertBefore(dotsContainer, gallery.nextSibling);

        items.forEach((item, index) => {
            const dot = document.createElement('button');
            dot.className = index === 0 ? 'dot active' : 'dot';
            dot.setAttribute('aria-label', 'Mostra l\'immagine ' + (index + 1));
            
            dot.addEventListener('click', () => {
                gallery.scrollTo({
                    left: item.offsetLeft - gallery.offsetLeft,
                    behavior: 'smooth'
                });
            });
            dotsContainer.appendChild(dot);
        });

        gallery.addEventListener('scroll', () => {
            let index = Math.round(gallery.scrollLeft / gallery.offsetWidth);
            const dots = dotsContainer.querySelectorAll('.dot');
            dots.forEach(d => d.classList.remove('active'));
            if(dots[index]) {
                dots[index].classList.add('active');
            }
        });

        // === FIX NAVIGAZIONE TASTIERA E LOOP INFINITO ===
        // Questo blocco DEVE stare dentro il forEach
        gallery.setAttribute('tabindex', '0');
        gallery.style.outline = 'none';
        
        gallery.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                e.preventDefault(); // Evita lo scorrimento della pagina
                
                const dots = dotsContainer.querySelectorAll('.dot');
                // Trova quale pallino è attualmente attivo
                let currentIndex = Array.from(dots).findIndex(dot => dot.classList.contains('active'));
                
                if (e.key === 'ArrowRight') {
                    // Se è l'ultimo, torna al primo (0), altrimenti vai al prossimo
                    currentIndex = (currentIndex + 1) % dots.length;
                } else if (e.key === 'ArrowLeft') {
                    // Se è il primo, vai all'ultimo, altrimenti vai al precedente
                    currentIndex = (currentIndex - 1 + dots.length) % dots.length;
                }
                
                // Simula il click sul pallino calcolato
                if(dots[currentIndex]) {
                    dots[currentIndex].click();
                }
            }
        });
    }); // <-- FINE DEL CICLO FOREACH

    // === TENDINA FILTRI MOBILE (NEWS) ===
    const filterToggle = document.querySelector('.mobile-filter-toggle');
    if (filterToggle) {
        filterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            // Usiamo jQuery per l'effetto "tendina" fluido
            jQuery('.news-category-filters').slideToggle(250);
            
            // Gira la freccina
            const icon = this.querySelector('.toggle-icon');
            if (icon) {
                icon.classList.toggle('fa-chevron-up');
                icon.classList.toggle('fa-chevron-down');
            }
        });
    }

    // === CLICK INTERA CARD NEWS E EVENTI ===
    jQuery(document).on('click', '.wp-block-post, .news-home-card', function(e) {
        // Se clicco su un link interno, lascia fare al browser
        if (jQuery(e.target).closest('a').length) return;
        
        // Trova il link del titolo
        var link = jQuery(this).find('.wp-block-post-title a, .news-home-title-link').attr('href');
        if (link) {
            window.location.href = link;
        }
    });

    // === FIX MENU A TENDINA (COMPORTAMENTO DIVISO PER DESKTOP E MOBILE) ===
    jQuery('.menu-item-has-children > a').on('click', function(e) {
        // Intercetta solo i bottoni del menu che hanno come link "#"
        if (jQuery(this).attr('href') === '#') {
            e.preventDefault(); // Blocca sempre il salto pagina verso l'alto
            
            // ESEGUIAMO LA LOGICA DI APERTURA/CHIUSURA SOLO SU MOBILE (sotto i 768px)
            if (window.innerWidth <= 768) {
                var $subMenu = jQuery(this).siblings('.sub-menu');
                
                // Se la tendina è già aperta (visibile) e clicco di nuovo
                if ($subMenu.is(':visible')) {
                    $subMenu.slideUp(250, function() {
                        jQuery(this).css('display', ''); 
                    });
                    jQuery(this).blur(); 
                    
                } else {
                    // Chiudo le altre e apro quella cliccata
                    jQuery('.menu-item-has-children > a[href="#"]').not(this).siblings('.sub-menu').slideUp(250, function() {
                        jQuery(this).css('display', '');
                    });
                    $subMenu.slideDown(250);
                }
            }
            // Su desktop (> 768px) il JS si ferma al preventDefault() e lascia fare all'hover del CSS
        }
    });


    /*// === NUOVO: ANIMAZIONI FADE-IN-UP ===
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // Fa l'animazione solo la prima volta
            }
        });
    }, { threshold: 0.15 }); 

    document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));*/

});