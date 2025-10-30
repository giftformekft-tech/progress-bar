# Gift Progress Bar for WooCommerce

Egy modern, reszponzív WordPress WooCommerce plugin, amely vizuális progress bar-ral ösztönzi a vásárlókat a kosárérték növelésére.

## 🎯 Főbb funkciók

- **Vizuális progress bar** a kosár és pénztár oldalakon
- **Küszöbértékek és jutalmak** testreszabható rendszere
- **Valós idejű frissítés** AJAX-on keresztül
- **Modern, animált UI** reszponzív dizájnnal
- **Könnyen konfigurálható** admin felület
- **Drag & drop** sorrendezés a küszöbértékekhez
- **Testreszabható színek** color picker-rel
- **WooCommerce cart fragments** integráció
- **Többnyelvű támogatás** (magyar fordítással)

## 📋 Követelmények

- WordPress 5.8 vagy újabb
- WooCommerce 5.0 vagy újabb
- PHP 7.4 vagy újabb

### ✅ WooCommerce HPOS Kompatibilitás

A plugin **teljes mértékben kompatibilis** a WooCommerce nagy teljesítményű rendelés tárolás (HPOS) funkciójával. A kompatibilitás automatikusan deklarálva van, nem igényel külön beállítást.

További információ: [HPOS-COMPATIBILITY.md](HPOS-COMPATIBILITY.md)

## 🚀 Telepítés

1. Töltsd le a plugin fájlokat
2. Töltsd fel a `gift-progress-bar` mappát a `/wp-content/plugins/` könyvtárba
3. Aktiváld a plugint a WordPress admin felületen (Bővítmények menüpont)
4. Menj a **WooCommerce → Ajándék Progress Bar** menüpontra a beállításokhoz

## ⚙️ Konfiguráció

### Megjelenítési beállítások

A plugin automatikusan megjelenik a kosár és pénztár oldalakon. Ezeket külön-külön be/kikapcsolhatod:

- ✅ Megjelenítés a kosár oldalon
- ✅ Megjelenítés a pénztár oldalon

### Színek testreszabása

Három szín állítható be color picker segítségével:

- **Progress bar színe** - Az aktív progress bar kitöltés színe
- **Háttér színe** - A progress bar háttérszíne
- **Szöveg színe** - Az üzenetek szövegének színe

### Küszöbértékek beállítása

Adj hozzá tetszőleges számú küszöbértéket:

1. Klikkelj az "Új küszöbérték hozzáadása" gombra
2. Állítsd be az **Összeg**et (Ft-ban)
3. Írd be a **Jutalom leírás**át (pl. "Ingyenes szállítás")
4. Válassz egy **Dashicon** ikont (pl. "dashicons-cart")
5. Húzd az elemeket a kívánt sorrendbe

#### Alapértelmezett küszöbértékek:

- **15 000 Ft** → Ingyenes szállítás
- **15 990 Ft** → Ajándék Filmes póló
- **19 990 Ft** → Ajándék Filmes póló és bögre
- **24 990 Ft** → Ajándék 2 db Filmes póló

## 🎨 Dashicons ikonok

Használható Dashicon osztályok példák:

- `dashicons-cart` - Kosár
- `dashicons-products` - Termékek
- `dashicons-awards` - Díjak
- `dashicons-star-filled` - Csillag
- `dashicons-gift` - Ajándék
- `dashicons-heart` - Szív
- `dashicons-megaphone` - Megafon
- `dashicons-tickets` - Jegyek

[Teljes Dashicons lista](https://developer.wordpress.org/resource/dashicons/)

## 💻 Működés

### Progress Bar Logika

1. **Kosárérték figyelése**: A rendszer folyamatosan figyeli a WooCommerce kosár összértékét
2. **Következő szint kiszámítása**: Megkeresi a legközelebbi el nem ért küszöbértéket
3. **Progress frissítése**: A kitöltés százalékát a jelenlegi és legmagasabb küszöb alapján számítja
4. **Üzenetek megjelenítése**: Dinamikus visszajelzés a jelenlegi státuszról

### Szintek vizuális állapotai

- **Aktív szint** (következő elérhető) - Pulzáló animáció
- **Elért szintek** - Zöld színű pipa jellel
- **Eléretlen szintek** - Szürke színű ikon

### AJAX frissítés

A progress bar automatikusan frissül amikor:
- Termék hozzáadása a kosárhoz
- Termék eltávolítása a kosárból
- Mennyiség módosítása
- Kuponkód alkalmazása

## 📱 Reszponzív dizájn

A plugin teljesen reszponzív és három breakpoint-ra optimalizált:

- **Desktop** (768px felett) - Teljes nézet tooltip-ekkel
- **Tablet** (481-768px) - Optimalizált elrendezés
- **Mobil** (480px alatt) - Kompakt nézet rövidített szövegekkel

## 🎯 Használati példák

### Egyszerű beállítás

```
15 000 Ft → Ingyenes szállítás (dashicons-cart)
```

### Többszintű rendszer

```
10 000 Ft → 5% kedvezmény (dashicons-tag)
15 000 Ft → Ingyenes szállítás (dashicons-cart)
20 000 Ft → 10% kedvezmény (dashicons-tickets)
30 000 Ft → Ajándék termék (dashicons-gift)
```

## 🔧 Testreszabás

### CSS testreszabás

Használd a `.cart-progress-bar` osztályt saját CSS szabályok írásához:

```css
.gpb-progress-bar-container {
    /* Saját stílusok */
}

.gpb-progress-bar-fill {
    /* Progress bar kitöltés testreszabása */
}

.gpb-milestone {
    /* Mérföldkövek testreszabása */
}
```

### PHP szűrők (hook-ok)

A plugin támogatja a WooCommerce standard hook-jait:

- `woocommerce_add_to_cart_fragments` - Cart fragments frissítése
- `woocommerce_before_cart` - Progress bar megjelenítése a kosár előtt
- `woocommerce_before_checkout_form` - Progress bar megjelenítése a pénztár előtt

## 🐛 Hibaelhárítás

### A progress bar nem jelenik meg

1. Ellenőrizd, hogy a WooCommerce aktiválva van-e
2. Töröld a WordPress cache-t
3. Ellenőrizd, hogy a plugin be van-e kapcsolva a beállításokban

### A progress bar nem frissül

1. Ellenőrizd a böngésző konzolját hibákért
2. Ellenőrizd, hogy a JavaScript engedélyezve van-e
3. Próbáld meg újratölteni az oldalt (Ctrl+F5)

### Stílus problémák

1. Töröld a böngésző cache-t
2. Ellenőrizd, hogy nincs-e CSS konfliktus más pluginekkel
3. Használj böngésző fejlesztői eszközöket a hibakereséshez

## 📄 Fájlstruktúra

```
gift-progress-bar/
├── gift-progress-bar.php          # Fő plugin fájl
├── includes/
│   ├── class-gpb-admin.php        # Admin beállítások
│   ├── class-gpb-frontend.php     # Frontend megjelenítés
│   └── class-gpb-ajax.php         # AJAX kezelés
├── assets/
│   ├── css/
│   │   ├── admin.css              # Admin stílusok
│   │   └── frontend.css           # Frontend stílusok
│   └── js/
│       ├── admin.js               # Admin JavaScript
│       └── frontend.js            # Frontend JavaScript
└── README.md                      # Ez a fájl
```

## 🔐 Biztonság

A plugin követi a WordPress kódolási szabványokat:

- ✅ Nonce ellenőrzés minden form submit-nél
- ✅ Input sanitizáció és validáció
- ✅ Output escaping
- ✅ AJAX request validáció
- ✅ Capability checks az admin oldalakon

## 🌐 Fordítás

A plugin teljesen fordítható. Magyar fordítás alapértelmezetten elérhető.

Text Domain: `gift-progress-bar`

## 📝 Changelog

### Version 1.0.0
- Első kiadás
- Progress bar funkció
- Admin beállítások
- AJAX frissítés
- Reszponzív dizájn
- Magyar fordítás

## 👨‍💻 Fejlesztő

- Név: Your Name
- Web: https://yourwebsite.com
- Email: your@email.com

## 📜 Licensz

GPL v2 vagy újabb

## 🙏 Köszönetnyilvánítás

- WordPress és WooCommerce közösségnek
- Dashicons ikonkészletért
- Minden felhasználónak és tesztelőnek

## 🔗 Hasznos linkek

- [WordPress Codex](https://codex.wordpress.org/)
- [WooCommerce Docs](https://woocommerce.com/documentation/)
- [Dashicons](https://developer.wordpress.org/resource/dashicons/)

---

**Élvezd a használatát! Ha tetszik, kérlek értékeld! ⭐**
