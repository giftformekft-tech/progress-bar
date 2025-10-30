# 🔍 Hibaelhárítás - A Progress Bar nem jelenik meg

## ⚠️ Probléma: A plugin be van kapcsolva, de nem látható

### ✅ Gyors Ellenőrző Lista

Kövesd ezeket a lépéseket sorrendben:

---

## 1️⃣ ADMIN BEÁLLÍTÁSOK ELLENŐRZÉSE

### Lépések:
1. Menj ide: **WooCommerce → Ajándék Progress Bar**
2. Ellenőrizd:
   - ☑️ **"Megjelenítés a kosár oldalon"** - BE VAN PIPÁLVA?
   - ☑️ **"Megjelenítés a pénztár oldalon"** - BE VAN PIPÁLVA?
   - 📊 Van legalább **1 küszöbérték** beállítva?

### Ha nincs beállítás:
```
1. Pipáld be mindkét checkboxot
2. Ellenőrizd a küszöbértékeket (4 db legyen alapértelmezetten)
3. Kattints a "Beállítások mentése" gombra
4. Frissítsd az oldalt (F5)
```

### Sikeres mentés után látnod kell:
```
✅ Beállítások sikeresen mentve!
✅ Kosár oldal: BEKAPCSOLVA
✅ Pénztár oldal: BEKAPCSOLVA
✅ Küszöbértékek száma: 4
```

---

## 2️⃣ KOSÁR ELLENŐRZÉSE

### Lépések:
1. **Adj hozzá egy terméket a kosárhoz**
   - Menj egy termék oldalra
   - Kattints a "Kosárba" gombra
   
2. **Menj a kosár oldalra**
   - URL: `/cart/` vagy `/kosar/`
   - Vagy: WooCommerce menü → Kosár
   
3. **Mit kell látnod:**
   ```
   ┌─────────────────────────────────────┐
   │ 🎁 PROGRESS BAR MEGJELENIK ITT 🎁   │  ← A kosár tartalma ELŐTT
   ├─────────────────────────────────────┤
   │ Kosár tartalma:                     │
   │ [Termék 1] ........ 5000 Ft         │
   └─────────────────────────────────────┘
   ```

### Ha nem látod:
- Görgess fel a kosár tetejére
- Ellenőrizd, hogy van-e termék a kosárban
- Frissítsd az oldalt (Ctrl+F5)

---

## 3️⃣ BÖNGÉSZŐ KONZOL ELLENŐRZÉSE

### Nyisd meg a Developer Tools-t:
- **Chrome/Edge**: `F12` vagy `Ctrl+Shift+I`
- **Firefox**: `F12` vagy `Ctrl+Shift+K`
- **Safari**: `Cmd+Option+I`

### Console ellenőrzése:
1. Kattints a **Console** fülre
2. Frissítsd az oldalt (F5)
3. Keresd a piros hibákat

### Gyakori hibák:

#### JavaScript hiba:
```
❌ Uncaught ReferenceError: gpbData is not defined
```
**Megoldás:** A JS fájl nem töltődött be
- Töröld a cache-t
- Ellenőrizd a fájl jogosultságokat

#### CSS nem töltődik:
```
❌ Failed to load resource: net::ERR_BLOCKED_BY_CLIENT
```
**Megoldás:** Adblocker blokkolja
- Kapcsold ki az AdBlockert
- Adj hozzá exception-t

---

## 4️⃣ CACHE TÖRLÉSE

### WordPress Cache (ha van cache plugin):

**WP Rocket:**
```
1. WP Rocket → Cache törlése
2. Preload cache újraindítása
```

**W3 Total Cache:**
```
1. Performance → Purge All Caches
```

**WP Super Cache:**
```
1. Beállítások → WP Super Cache → Delete Cache
```

### Böngésző Cache:
```
Chrome/Edge/Firefox: Ctrl+Shift+Delete
1. Válaszd: "Minden idő"
2. Pipáld be: "Cache/Gyorsítótár"
3. Törlés
```

### Hard Refresh:
```
Windows: Ctrl+F5
Mac: Cmd+Shift+R
```

---

## 5️⃣ TÉMA KOMPATIBILITÁS

### Teszteld alapértelmezett témával:

1. **Megjelenés → Témák**
2. **Aktiválj egy alapértelmezett WordPress témát:**
   - Twenty Twenty-Four
   - Twenty Twenty-Three
   - Storefront (WooCommerce téma)
3. **Frissítsd a kosár oldalt**
4. **Látod most a progress bar-t?**

### Ha most látod:
```
→ A téma nem kompatibilis
→ Lépj kapcsolatba a téma készítőjével
→ Használd a shortcode-ot helyette (lásd lent)
```

---

## 6️⃣ PLUGIN KONFLIKTUS

### Kapcsold ki a többi plugint:

1. **Bővítmények → Telepített bővítmények**
2. **Kapcsolj ki MINDEN plugint** KIVÉVE:
   - WooCommerce
   - Gift Progress Bar
3. **Frissítsd a kosár oldalt**
4. **Látod a progress bar-t?**

### Ha most látod:
```
→ Van egy ütköző plugin
→ Kapcsold be egyesével a pluginokat
→ Teszteld közben a kosár oldalt
→ Azonosítsd az ütköző plugint
```

---

## 7️⃣ SHORTCODE HASZNÁLATA (Alternatív megoldás)

Ha a progress bar nem jelenik meg automatikusan, használd a shortcode-ot:

### Használat:

**WordPress blokkszerkesztőben:**
```
1. Hozz létre új oldalt vagy szerkeszd a kosár oldalt
2. Adj hozzá egy "Shortcode" blokkot
3. Írd be: [gift_progress_bar]
4. Mentés
```

**Classic Editorban:**
```
[gift_progress_bar]
```

**PHP kódban (template fájlban):**
```php
<?php echo do_shortcode('[gift_progress_bar]'); ?>
```

**Specifikus helyre (pl. functions.php):**
```php
// Kosár tetejére
add_action('woocommerce_before_cart', function() {
    echo do_shortcode('[gift_progress_bar]');
}, 5);

// Pénztár tetejére
add_action('woocommerce_before_checkout_form', function() {
    echo do_shortcode('[gift_progress_bar]');
}, 5);
```

---

## 8️⃣ FÁJL JOGOSULTSÁGOK

### Ellenőrizd FTP-n vagy cPanel File Manager-ben:

**Helyes jogosultságok:**
```
/wp-content/plugins/gift-progress-bar/           → 755
/wp-content/plugins/gift-progress-bar/assets/    → 755
/wp-content/plugins/gift-progress-bar/assets/css/ → 755
/wp-content/plugins/gift-progress-bar/*.php      → 644
/wp-content/plugins/gift-progress-bar/assets/css/*.css → 644
/wp-content/plugins/gift-progress-bar/assets/js/*.js → 644
```

### Javítás (SSH-n vagy FTP-n):
```bash
cd /path/to/wordpress/wp-content/plugins/
chmod -R 755 gift-progress-bar/
find gift-progress-bar/ -type f -name "*.php" -exec chmod 644 {} \;
find gift-progress-bar/ -type f -name "*.css" -exec chmod 644 {} \;
find gift-progress-bar/ -type f -name "*.js" -exec chmod 644 {} \;
```

---

## 9️⃣ DEBUG MÓD BEKAPCSOLÁSA

### Szerkeszd a wp-config.php fájlt:

```php
// Debug mód bekapcsolása
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Nézd meg a log fájlt:
```
/wp-content/debug.log
```

### Keresd ezeket a sorokat:
```
Gift Progress Bar: ...
GPB Frontend: ...
```

---

## 🔟 VÉGSŐ MEGOLDÁS: Újratelepítés

### Teljes újratelepítési lépések:

1. **Készíts backupot!**
   ```
   - Exportáld a beállításokat (ha van ilyen opció)
   - Írd fel a küszöbértékeket
   ```

2. **Deaktiváld a plugint**
   ```
   Bővítmények → Deaktiválás
   ```

3. **Töröld a plugint**
   ```
   Bővítmények → Törlés
   ```

4. **Töröld a cache-t**
   ```
   - WordPress cache
   - Böngésző cache
   - CDN cache (ha van)
   ```

5. **Telepítsd újra**
   ```
   Bővítmények → Új hozzáadása → Feltöltés
   gift-progress-bar.zip → Telepítés
   ```

6. **Aktiválás és beállítás**
   ```
   - Aktiváld
   - Állítsd be újra a küszöbértékeket
   - Pipáld be a megjelenítési opciókat
   - Mentés
   ```

---

## 🆘 TÁMOGATÁSI INFORMÁCIÓK GYŰJTÉSE

Ha semmi nem működik, gyűjts össze információkat a támogatáshoz:

### Rendszerinformáció:
```
WordPress verzió: __________
WooCommerce verzió: __________
PHP verzió: __________
Aktív téma: __________
Aktív pluginek száma: __________
```

### Képernyőképek:
1. ✅ Plugin beállítások oldala
2. ✅ Kosár oldal (teljes)
3. ✅ Böngésző konzol (F12 → Console)
4. ✅ Böngésző Network (F12 → Network → Szűrés: gpb)

### Tesztelt lépések:
- [ ] Admin beállítások ellenőrizve
- [ ] Cache törölve
- [ ] Alapértelmezett téma tesztelve
- [ ] Pluginek kikapcsolva tesztelve
- [ ] Shortcode kipróbálva
- [ ] Újratelepítés megtörtént

---

## ✅ GYORS CHECKLIST

```
☐ Plugin aktiválva
☐ WooCommerce működik
☐ Kosár beállítás bekapcsolva az adminban
☐ Van termék a kosárban
☐ Cache törölve
☐ Böngésző konzol ellenőrizve (nincs hiba)
☐ Shortcode kipróbálva
☐ Alapértelmezett téma tesztelve
☐ Hard refresh (Ctrl+F5)
```

---

## 💡 LEGGYAKORIBB OKOK ÉS MEGOLDÁSOK

| Probléma | Ok | Megoldás |
|----------|-----|----------|
| Nem jelenik meg | Beállítás kikapcsolva | Kapcsold be az adminban |
| Nem jelenik meg | Üres a kosár | Adj hozzá terméket |
| Nem jelenik meg | Cache | Töröld minden cache-t |
| Nem jelenik meg | Téma konfliktus | Használd a shortcode-ot |
| Nem jelenik meg | JS hiba | Nézd meg a konzolt |
| Részben látszik | CSS konfliktus | Teszteld alapértelmezett témával |

---

**Ha ezek után sem működik, küldd el a fenti információkat és képernyőképeket!**

Verzió: 1.0.1 | Frissítve: 2024.10.30
