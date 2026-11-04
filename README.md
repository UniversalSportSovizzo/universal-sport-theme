# Universal Sport Sovizzo - Custom Theme

Tema WordPress personalizzato per il sito istituzionale dell'A.S.D. Universal Sport Sovizzo (universalsportsovizzo.it). Questo tema è stato sviluppato per garantire leggerezza, accessibilità e un design su misura, ottimizzando i blocchi nativi di WordPress senza ricorrere a page builder pesanti.

## 🛠 Tecnologie e Workflow
- **Sviluppo:** HTML5, CSS3, Vanilla JavaScript, PHP.
- **Ambiente Locale:** LocalWP + Visual Studio Code.
- **Version Control:** Git / GitHub.
- **Deploy Automation:** GitHub Actions (Deploy FTP automatico su server Netsons al push sul branch `main`).

## 🏗 Scelte Architetturali e Soluzioni UI

### 1. Ottimizzazione Blocchi Gutenberg
Il design si basa sull'estensione e stilizzazione dei blocchi nativi di Gutenberg (Gruppi, Colonne, Gallerie), evitando codice superfluo:
- **Gallerie Slider 16:9:** Le gallerie native sono state trasformate tramite CSS (`.galleria-slider`) e JS in slider orizzontali fluidi (`scroll-snap`), garantendo proporzioni perfette e centratura dei dot di navigazione anche all'interno di blocchi affiancati.
- **Layout Flessibili:** Gestione avanzata del `flex-wrap` e del CSS Grid per assicurare che colonne (es. Logo + Video) e gruppi testo-immagine si impilino correttamente su mobile mantenendo il design asimmetrico a zig-zag su desktop.

### 2. Header e Navigazione Mobile-First
- Navbar sticky e responsive con transizione da menu esteso (desktop) a Off-Canvas hamburger menu (mobile).
- Prevenzione del "doppio rimbalzo" su schermi touch tramite `stopImmediatePropagation()` nel JavaScript.
- Blocco dello scroll della pagina (`body.no-scroll`) quando il menu Off-Canvas è aperto per migliorare l'UX.

### 3. Personalizzazione The Events Calendar (TEC)
Il plugin degli eventi è stato radicalmente ri-stilizzato per integrarsi con la UI dell'associazione:
- **Vista Giornaliera e Archivi:** Transizione da layout standard a griglia flessibile a due colonne, con controllo dell'altezza delle card.
- **Locandine:** Forzato l'`object-fit: contain` per evitare il taglio delle grafiche verticali promozionali (es. 9:16).
- **Datepicker:** Risolto il bug di clipping del mini-calendario nativo su mobile, trasformandolo in un pop-up indipendente e scorrevole ancorato al bottone.

### 4. Gestione Dati Sportivi (SportsPress)
Integrazione personalizzata per classifiche, partite e marcatori:
- **Widget Partite "ManCity Style":** Struttura a righe compatte con loghi, badge di vittoria/sconfitta e risultati evidenziati, ottimizzata al millimetro per smartphone.
- **Classifiche e Formazioni:** Override delle tabelle native di SportsPress con CSS Grid per nascondere dati superflui su schermi piccoli, inserimento medaglie (oro, argento, bronzo) per la classifica marcatori e inversione dell'ordine di visualizzazione (Staff Tecnico sopra i giocatori nelle singole partite).

### 5. Accessibilità e UI Components
- **Color Palette:** Contrasti studiati attorno al Blu Primario (`#183F74`) e Arancione Secondario (`#EB5A2D`).
- **Pulsante Back-to-Top:** Implementato in JS vanilla, appare dinamicamente allo scroll con animazioni fluide.
- **Privacy & Compliance:** Integrazione e correzione grafica del banner Complianz (inclusa la correzione stringhe multilingua tramite TranslatePress).