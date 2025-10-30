# Changelog

Minden jelentős változás a projektben dokumentálva van ebben a fájlban.

## [1.0.0] - 2024-10-30

### Hozzáadva
- ✨ Vizuális progress bar a kosár és pénztár oldalakon
- ⚙️ Admin beállítások oldal WooCommerce menüben
- 🎨 Testreszabható színek (progress bar, háttér, szöveg)
- 📊 Küszöbértékek kezelése drag & drop sorrendezéssel
- 🎯 Dashicons integráció az ikonokhoz
- 💬 Dinamikus üzenetek a vásárlóknak
- 🔄 Valós idejű AJAX frissítés
- 📱 Teljesen reszponzív dizájn (desktop, tablet, mobil)
- ⚡ Animált progress bar smooth átmenetekkel
- 🎭 Milestone-ok vizuális jelzése (aktív, elért, eléretlen)
- 🔌 WooCommerce cart fragments integráció
- 🌐 Magyar nyelvi támogatás
- 📖 Részletes dokumentáció (README, INSTALLATION, VISUAL-GUIDE)
- 🛡️ Biztonságos kód (nonce, sanitize, escape)
- ♿ Accessibility támogatás
- 🎨 Tooltip-ek a milestone-okhoz
- ✅ Plugin activation hook alapértelmezett beállításokkal
- 🗑️ Clean uninstall lehetőség
- 🚀 **HPOS (High-Performance Order Storage) teljes kompatibilitás**
- 📄 HPOS kompatibilitási dokumentáció

### Funkciók
- 4 alapértelmezett küszöbérték előre beállítva
- Tetszőleges számú küszöbérték hozzáadása
- Küszöbértékek sorrendezése
- Egyedi jutalom leírások
- Color picker a színválasztáshoz
- Megjelenítés be/kikapcsolása kosár és pénztár oldalakon külön-külön
- Progress százalék automatikus számítása
- Következő szint távolságának mutatása
- Összes szint elérésekor speciális üzenet
- Stílusos animációk (fade-in, bounce, pulse, shine)
- Gyors betöltés és optimalizált teljesítmény
- Cache-barát működés

### Technikai jellemzők
- PHP 7.4+ kompatibilis
- WordPress 5.8+ támogatás
- WooCommerce 5.0+ integráció
- jQuery alapú frontend
- Modern ES5 JavaScript
- CSS3 animációk
- Responsive breakpoints (768px, 480px)
- AJAX error handling
- WordPress Coding Standards
- Biztonságos adatbázis műveletek
- Multisite támogatás az uninstall során

### Fájlok
- `gift-progress-bar.php` - Fő plugin fájl (HPOS deklarációval)
- `includes/class-gpb-admin.php` - Admin beállítások
- `includes/class-gpb-frontend.php` - Frontend megjelenítés
- `includes/class-gpb-ajax.php` - AJAX kezelés
- `includes/class-gpb-activator.php` - Aktiválási logika
- `assets/css/frontend.css` - Frontend stílusok
- `assets/css/admin.css` - Admin stílusok
- `assets/js/frontend.js` - Frontend JavaScript
- `assets/js/admin.js` - Admin JavaScript
- `uninstall.php` - Eltávolítási script
- `README.md` - Projekt dokumentáció
- `INSTALLATION.md` - Telepítési útmutató
- `VISUAL-GUIDE.md` - Vizuális útmutató
- `HPOS-COMPATIBILITY.md` - HPOS kompatibilitási útmutató
- `CHANGELOG.md` - Ez a fájl

### Ismert korlátozások
- A plugin csak WooCommerce jelenlétében működik
- Csak a kosár subtotal értékét veszi figyelembe (shipping nélkül)
- Maximum 10 küszöbérték ajánlott a jó UX miatt

---

## Jövőbeli tervek (roadmap)

### [1.1.0] - Tervezett
- [ ] Shortcode támogatás egyedi elhelyezéshez
- [ ] Widget támogatás sidebar-ba
- [ ] Export/Import beállítások
- [ ] Előnézet funkció az admin felületen
- [ ] Több színséma template
- [ ] Egyedi CSS szerkesztő

### [1.2.0] - Tervezett
- [ ] Email értesítések küszöb elérésekor
- [ ] Statisztikák az elért jutalmakról
- [ ] Feltételes megjelenítés (kategória, user role alapján)
- [ ] Időkorlátozott akciók támogatása
- [ ] Integrációk más popular pluginokkal

### [2.0.0] - Tervezett
- [ ] Többnyelvű admin felület
- [ ] REST API endpoint-ok
- [ ] Gutenberg block támogatás
- [ ] Gamification elemek
- [ ] A/B testing lehetőség

---

**Megjegyzés**: Ez a plugin aktív fejlesztés alatt áll. Javaslatokat és bug reportokat szívesen fogadunk!

## Verziókezelés

A projekt a [Semantic Versioning](https://semver.org/) szabványt követi:
- **MAJOR** verzió: Nem kompatibilis API változások
- **MINOR** verzió: Visszafelé kompatibilis új funkciók
- **PATCH** verzió: Visszafelé kompatibilis hibajavítások

## Támogatás

Ha hibát találsz vagy funkciót szeretnél kérni:
1. Ellenőrizd, hogy már nincs-e jelentve
2. Nyiss egy új issue-t részletes leírással
3. Add meg a WordPress, WooCommerce és PHP verziódat
4. Csatolj képernyőképeket ha lehetséges

---

Utolsó frissítés: 2024-10-30
