# WooCommerce HPOS Kompatibilitás

## ✅ A plugin TELJES MÉRTÉKBEN kompatibilis a WooCommerce HPOS funkciójával!

### Mi az a HPOS?

A **HPOS (High-Performance Order Storage)** a WooCommerce új, nagy teljesítményű rendelés tárolási rendszere, amely egyedi adatbázis táblákat használ a jobb teljesítmény érdekében a hagyományos WordPress post rendszer helyett.

### Kompatibilitási Deklaráció

A Gift Progress Bar plugin kifejezetten deklarálja a HPOS kompatibilitást a következő kóddal:

```php
// Declare HPOS compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
```

### Miért kompatibilis?

A Gift Progress Bar plugin:

1. **NEM dolgozik rendelésekkel** - Csak a kosár (cart) adatokkal
2. **NEM használ rendelési meta adatokat** - Csak WooCommerce cart API-t
3. **NEM ír az adatbázisba rendelések esetén** - Csak beállításokat tárol
4. **NEM használ post meta-t rendelésekhez** - Csak option API-t

### Plugin által használt WooCommerce funkciók:

✅ `WC()->cart->get_subtotal()` - Kosár összeg lekérése  
✅ `WC()->cart->get_total()` - Kosár teljes összeg  
✅ `woocommerce_add_to_cart_fragments` - Cart frissítés  
✅ `woocommerce_before_cart` - Hook kosár oldalon  
✅ `woocommerce_before_checkout_form` - Hook pénztár oldalon

**Egyik sem érint HPOS funkciókat!**

### Telepítési megjegyzés

Ha a következő hibát látod:
```
⚠ Ez a bővítmény nem kompatibilis a WooCommerce 
'Nagy teljesítményű rendelés tárolás' funkciójával
```

**Ez NEM jelenti azt, hogy a plugin nem működik!** Ez csak azt jelenti, hogy a WooCommerce nem találta a kompatibilitási deklarációt.

### Megoldás:

1. **Telepítsd a frissített verziót** - A javított `gift-progress-bar.php` fájl már tartalmazza a HPOS deklarációt
2. **Aktiváld a plugint** - Most már nem fog hibát adni
3. **Ellenőrizd a HPOS státuszt**:
   - WooCommerce → Beállítások → Haladó → Funkciók
   - Nézd meg, hogy a plugin kompatibilis-e

### Tesztelés HPOS-szal

#### HPOS engedélyezése:
1. WooCommerce → Beállítások → Haladó → Funkciók
2. Kapcsold be: "Nagy teljesítményű rendelés tárolás"
3. Mentsd a beállításokat
4. Teszteld a Gift Progress Bar-t

#### HPOS letiltása:
1. Ugyanott kapcsold ki
2. Plugin továbbra is működik

### Gyakori kérdések

**K: Működik a plugin HPOS nélkül?**  
V: Igen, tökéletesen! A plugin nem függ a HPOS-tól.

**K: Működik a plugin HPOS-szal?**  
V: Igen, tökéletesen! A plugin kompatibilis a HPOS-szal.

**K: Kell-e bármit beállítanom?**  
V: Nem, a plugin automatikusan deklarálja a kompatibilitást.

**K: Mi van, ha mégis hibát látok?**  
V: Győződj meg róla, hogy a legfrissített verziót telepítetted (v1.0.0+), ami már tartalmazza a HPOS deklarációt.

### Technikai részletek

A plugin a WooCommerce alábbi API-jait használja, amelyek mindegyike HPOS-kompatibilis:

```php
// Kosár API (HPOS-független)
WC()->cart                          ✅
WC()->cart->get_subtotal()          ✅
WC()->cart->get_total()             ✅

// Hooks (HPOS-független)
woocommerce_before_cart             ✅
woocommerce_before_checkout_form    ✅
woocommerce_add_to_cart_fragments   ✅

// Options API (HPOS-független)
get_option()                        ✅
update_option()                     ✅
```

### Verziók

- **v1.0.0+**: Teljes HPOS kompatibilitás deklarálva
- **Minden korábbi verzió**: Frissítés ajánlott

### Összegzés

✅ A Gift Progress Bar **100%-ban HPOS kompatibilis**  
✅ Működik HPOS-szal ÉS nélküle is  
✅ Nem igényel semmilyen HPOS specifikus beállítást  
✅ Hivatalosan deklarálja a kompatibilitást  

---

**Ha bármilyen kérdésed van a HPOS kompatibilitással kapcsolatban, ellenőrizd, hogy a legfrissebb verziót használod-e!**
