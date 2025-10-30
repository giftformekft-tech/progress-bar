# Telepítési útmutató - Gift Progress Bar

## 📦 1. lépés: Plugin feltöltése WordPress-re

### Opció A: Admin felületről

1. Jelentkezz be a WordPress admin felületre
2. Menj a **Bővítmények → Új hozzáadása** menüpontra
3. Kattints a **Bővítmény feltöltése** gombra
4. Válaszd ki a `gift-progress-bar.zip` fájlt (ha van ilyen)
5. Kattints a **Telepítés most** gombra
6. A telepítés után kattints az **Aktiválás** gombra

### Opció B: FTP-n keresztül

1. Töltsd le a plugin mappát
2. Csatlakozz az FTP szerverhez
3. Navigálj a `/wp-content/plugins/` könyvtárba
4. Töltsd fel a teljes `gift-progress-bar` mappát
5. Menj a WordPress admin felületre
6. **Bővítmények** menüpont → Keresd meg a "Gift Progress Bar for WooCommerce" plugint
7. Kattints az **Aktiválás** gombra

## ⚙️ 2. lépés: Alapbeállítások

1. A sikeres aktiválás után menj a **WooCommerce → Ajándék Progress Bar** menüpontra
2. Az alapértelmezett beállítások már működőképesek:
   - ✅ Kosár oldal: bekapcsolva
   - ✅ Pénztár oldal: bekapcsolva
   - 4 alapértelmezett küszöbérték

## 🎨 3. lépés: Testreszabás

### Színek módosítása

1. Görgess le a **Színek** szekcióhoz
2. Kattints a színes négyzetekre a color picker megnyitásához
3. Válaszd ki a kívánt színeket:
   - **Progress bar színe**: Az aktív kitöltés színe
   - **Háttér színe**: A progress bar háttere
   - **Szöveg színe**: Az üzenetek színe
4. Kattints a **Beállítások mentése** gombra

### Küszöbértékek szerkesztése

1. Görgess le a **Küszöbértékek és Ajándékok** szekcióhoz
2. Módosítsd a meglévő értékeket:
   - **Összeg**: Add meg forintban (pl. 15000)
   - **Jutalom leírás**: Írj egy rövid leírást (pl. "Ingyenes szállítás")
   - **Ikon**: Adj meg egy Dashicon osztályt (pl. "dashicons-cart")
3. Új küszöbérték hozzáadásához kattints az **Új küszöbérték hozzáadása** gombra
4. Törléshez kattints a piros kuka ikonra
5. Sorrendezéshez húzd az elemeket a bal oldali ⋮⋮ ikon segítségével
6. Kattints a **Beállítások mentése** gombra

## ✅ 4. lépés: Tesztelés

1. Menj a webshop főoldalára
2. Adj hozzá egy terméket a kosárhoz
3. Nézd meg a kosár oldalt
4. Ellenőrizd, hogy megjelenik-e a progress bar
5. Adj hozzá további termékeket és figyeld a bar frissülését

### Teszt forgatókönyv

**0 Ft kosárérték:**
```
Már csak 15 000 Ft kell az ajándékhoz: Ingyenes szállítás
```

**15 000 Ft felett:**
```
🎉 Gratulálunk! Már jogosult vagy a következőre: Ingyenes szállítás
Már csak 990 Ft kell a következő ajándékhoz: Ajándék Filmes póló
```

**24 990 Ft felett:**
```
🥳 Gratulálunk! Maximális ajándékcsomagot értél el!
```

## 🔧 5. lépés: Finomhangolás

### Megjelenítési helyek

Ha csak bizonyos helyeken szeretnéd megjeleníteni:

1. Menj a **Megjelenítési beállítások** szekcióhoz
2. Kapcsold ki/be a checkboxokat:
   - ☐ Kosár oldal
   - ☐ Pénztár oldal

### Dashicons kiválasztása

Népszerű ikonok a küszöbértékekhez:

| Dashicon osztály | Leírás | Mikor használd |
|-----------------|---------|---------------|
| `dashicons-cart` | Kosár | Szállítási kedvezmények |
| `dashicons-products` | Doboz | Termék ajándékok |
| `dashicons-awards` | Trófea | Különleges jutalmak |
| `dashicons-star-filled` | Csillag | Prémium ajándékok |
| `dashicons-gift` | Ajándék | Bármilyen ajándék |
| `dashicons-heart` | Szív | Hűségprogram |
| `dashicons-tag` | Címke | Kedvezmények |
| `dashicons-tickets` | Kupon | Kuponkódok |

[Teljes Dashicons lista →](https://developer.wordpress.org/resource/dashicons/)

## 📱 6. lépés: Mobil ellenőrzés

1. Nyisd meg a webshopot mobilon vagy használj böngésző dev tools-t (F12)
2. Kapcsolj át mobil nézetbe (Ctrl+Shift+M Chrome-ban)
3. Ellenőrizd a progress bar megjelenését különböző képernyőméreteken:
   - Telefon (320px-480px)
   - Tablet (768px-1024px)
   - Desktop (1024px+)

## 🎯 Gyakori beállítási példák

### 1. Egyszerű ingyenes szállítás rendszer

```
15 000 Ft → Ingyenes szállítás (dashicons-cart)
```

### 2. Lépcsőzetes kedvezmények

```
10 000 Ft → 5% kedvezmény (dashicons-tag)
20 000 Ft → 10% kedvezmény (dashicons-tickets)
30 000 Ft → 15% kedvezmény (dashicons-awards)
```

### 3. Vegyes rendszer (kedvezmények + ajándékok)

```
15 000 Ft → Ingyenes szállítás (dashicons-cart)
20 000 Ft → 5% kedvezmény (dashicons-tag)
25 000 Ft → Ajándék termék (dashicons-gift)
35 000 Ft → 10% kedvezmény + ajándék (dashicons-star-filled)
```

### 4. Prémium loyalty program

```
50 000 Ft → Bronze tag (dashicons-heart)
100 000 Ft → Ezüst tag (dashicons-awards)
200 000 Ft → Arany tag (dashicons-star-filled)
```

## 🚨 Hibaelhárítás telepítés közben

### "WooCommerce hiányzik" hiba

**Probléma**: A plugin nem aktiválható
**Megoldás**: Először telepítsd és aktiváld a WooCommerce plugint

### Progress bar nem jelenik meg

**Ellenőrizd**:
1. ✅ Plugin aktiválva van-e
2. ✅ WooCommerce működik-e
3. ✅ Kosár/Pénztár beállítások be vannak-e kapcsolva
4. ✅ Van-e legalább 1 küszöbérték beállítva

**Megoldás**:
```
1. Menj a WooCommerce → Ajándék Progress Bar
2. Kapcsold be a megjelenítési opciókat
3. Mentsd a beállításokat
4. Töröld a cache-t (ha van cache plugin)
5. Frissítsd az oldalt (Ctrl+F5)
```

### Stílusproblémák

**Probléma**: A progress bar elcsúszik vagy rossz helyen van
**Megoldás**: 
1. Töröld a böngésző cache-t
2. Ellenőrizd a téma CSS-ét
3. Próbálj más WordPress témát teszteléshez

### JavaScript hibák

**Probléma**: A bar nem frissül automatikusan
**Ellenőrizd**:
1. Nyisd meg a böngésző konzolt (F12)
2. Nézd meg, van-e JavaScript hiba
3. Ellenőrizd, hogy más pluginok nem ütköznek-e

**Megoldás**:
```
1. Kapcsold ki ideiglenesen az összes többi plugint
2. Ha működik, kapcsold be egyesével őket
3. Találd meg az ütköző plugint
4. Jelentsd a problémát
```

## 📊 Cache beállítások

Ha használsz cache plugint (pl. WP Rocket, W3 Total Cache):

1. **Töröld a cache-t** minden változtatás után
2. **Exclude a következőket** a cache-ből:
   - `wp-admin/admin-ajax.php`
   - WooCommerce cart/checkout oldalak
3. Egyes cache pluginoknál beállíthatod a **Dynamic cache**-t

## ✨ Következő lépések

- 📖 Olvasd el a [README.md](README.md) fájlt a részletes funkciókért
- 🎨 Nézd meg a [VISUAL-GUIDE.md](VISUAL-GUIDE.md) fájlt a dizájn beállításokhoz
- 🔧 Testreszabhatod a CSS-t saját stílusokkal
- 📧 Jelentsd a bugokat vagy kérj új funkciókat

## 💡 Tippek

1. **Kezdd alacsony küszöbértékekkel** hogy a legtöbb vásárló lásson eredményt
2. **Ne használj túl sok szintet** (max 5-6) hogy ne legyen túl zsúfolt
3. **Teszteld különböző kosárértékekkel** hogy minden szint jól működik
4. **Figyelj a mobil nézetre** mivel a vásárlók nagy része mobilról vásárol
5. **Frissítsd rendszeresen** a küszöbértékeket az üzleti célok szerint

---

**Sikeres telepítést! 🎉**

Ha bármilyen problémád van, ellenőrizd a hibakeresési lépéseket vagy kérj segítséget a támogatástól.
