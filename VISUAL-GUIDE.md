# Gift Progress Bar - Visual Guide

## Frontend Megjelenés

### Desktop Nézet

A progress bar a kosár és pénztár oldalak tetején jelenik meg:

```
┌─────────────────────────────────────────────────────────────┐
│  🎉 Gratulálunk! Már jogosult vagy a következőre:           │
│  Ingyenes szállítás                                          │
│  Már csak 990 Ft kell a következő ajándékhoz:               │
│  Ajándék Filmes póló                                         │
├─────────────────────────────────────────────────────────────┤
│  ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░        │
│  ✓     ⭕      ○         ○                                   │
│  15k   15.9k  19.9k     24.9k                                │
└─────────────────────────────────────────────────────────────┘
```

### Mobil Nézet

Reszponzív dizájn kisebb képernyőkön:

```
┌──────────────────────────┐
│ 🎉 Gratulálunk!          │
│ Ingyenes szállítás       │
│ Még 990 Ft az újabb     │
│ ajándékhoz               │
├──────────────────────────┤
│ ▓▓▓▓▓▓▓▓░░░░░░░░        │
│ ✓  ⭕   ○    ○           │
└──────────────────────────┘
```

## Admin Beállítások

### Megjelenítési beállítások

```
☑ Megjelenítés a kosár oldalon
☑ Megjelenítés a pénztár oldalon
```

### Színek

```
Progress bar színe:  [🎨] #4CAF50
Háttér színe:        [🎨] #e0e0e0
Szöveg színe:        [🎨] #333333
```

### Küszöbértékek

```
┌─────────────────────────────────────────────────┐
│ ⋮⋮  15000 Ft | Ingyenes szállítás | 🛒  [×]    │
├─────────────────────────────────────────────────┤
│ ⋮⋮  15990 Ft | Ajándék Filmes póló | 📦  [×]   │
├─────────────────────────────────────────────────┤
│ ⋮⋮  19990 Ft | Póló és bögre | 🏆  [×]         │
├─────────────────────────────────────────────────┤
│ ⋮⋮  24990 Ft | 2 db Filmes póló | ⭐  [×]      │
└─────────────────────────────────────────────────┘

[+ Új küszöbérték hozzáadása]
```

## Animációk

### Progress Bar Animáció

1. **Betöltéskor**: A bar 0%-ról animálódik a jelenlegi értékre (0.8s)
2. **Kosár frissítéskor**: Smooth átmenet az új értékre (0.8s)
3. **Fényes csík**: Végigfut a baron minden 2 másodpercben

### Milestone Animációk

1. **Betöltéskor**: Fade-in hatás sorrendben (staggered)
2. **Eléréskor**: Bounce-in animáció
3. **Aktív szint**: Pulzáló hatás
4. **Hover**: Tooltip előcsúszás

## Státuszok

### Kezdeti állapot (0 Ft kosárérték)

```
Már csak 15 000 Ft kell az ajándékhoz: Ingyenes szállítás
████░░░░░░░░░░░░░░░░░░░░░░░░
○  ○  ○  ○
```

### Első szint elérve (15 000 Ft)

```
🎉 Gratulálunk! Már jogosult vagy a következőre: Ingyenes szállítás
Már csak 990 Ft kell a következő ajándékhoz: Ajándék Filmes póló
████████████░░░░░░░░░░░░░░░░
✓  ⭕  ○  ○
```

### Második szint elérve (15 990 Ft)

```
🎉 Gratulálunk! Már jogosult vagy a következőre: Ajándék Filmes póló
Már csak 4 000 Ft kell a következő ajándékhoz: Póló és bögre
█████████████████░░░░░░░░░░░
✓  ✓  ⭕  ○
```

### Maximális szint elérve (24 990 Ft+)

```
🥳 Gratulálunk! Maximális ajándékcsomagot értél el!
████████████████████████████
✓  ✓  ✓  ✓
```

## Színsémák

### Alapértelmezett (Zöld)
- Progress: #4CAF50
- Háttér: #e0e0e0
- Szöveg: #333333

### Példa színsémák

#### Kék téma
- Progress: #2196F3
- Háttér: #E3F2FD
- Szöveg: #1565C0

#### Piros téma
- Progress: #F44336
- Háttér: #FFEBEE
- Szöveg: #C62828

#### Narancssárga téma
- Progress: #FF9800
- Háttér: #FFF3E0
- Szöveg: #E65100

## Tooltip megjelenés

Amikor az egér egy milestone fölé ér:

```
┌──────────────────┐
│   15 000 Ft      │
│ Ingyenes         │
│ szállítás        │
└────────▼─────────┘
        ⭕
```

## Reszponzív breakpointok

- **> 768px**: Teljes desktop nézet
- **481-768px**: Tablet optimalizált nézet
- **< 480px**: Kompakt mobil nézet

## Dashicons használata

Példa ikonok a küszöbértékekhez:

```
dashicons-cart          🛒 Kosár / Szállítás
dashicons-products      📦 Termékek
dashicons-awards        🏆 Díjak / Jutalmak
dashicons-star-filled   ⭐ Csillag
dashicons-gift          🎁 Ajándék
dashicons-heart         ❤️ Szív
dashicons-megaphone     📢 Promóció
dashicons-tickets       🎟️ Kuponok
```

## Teljesítmény

- **CSS**: ~3KB (tömörítve)
- **JS**: ~2KB (tömörítve)
- **AJAX hívások**: Csak kosár frissítéskor
- **Animációk**: GPU-gyorsított (transform, opacity)
- **Cache-barát**: WooCommerce fragments használata

---

**Megjegyzés**: Ez egy vizuális útmutató a plugin működéséhez. A tényleges megjelenés a témától és a beállításoktól függ.
