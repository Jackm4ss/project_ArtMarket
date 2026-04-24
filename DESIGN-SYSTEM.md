# Art Market — Design System

> **Source of Truth** untuk semua keputusan UI/UX. Agent WAJIB membaca dokumen ini sebelum membuat section, halaman, atau komponen baru. Jangan menebak atau menggunakan nilai hardcoded — selalu gunakan token dan komponen yang terdaftar di sini.

---

## 1. Tema Aktif

Project ini menggunakan tema **`art-market`** (default). Tema di-apply via `data-theme="art-market"` pada `<html>`.

```
Theme ID  : art-market
Karakter  : Warm editorial marketplace — tinta gelap, aksen emas, latar krem
```

> ⚠️ Jangan mengganti atau menambah tema tanpa koordinasi. `gallery-minimal` tersedia tapi tidak digunakan di landing page.

---

## 2. Color Tokens

### CSS Variables → Tailwind Class

Semua warna didefinisikan di `src/design-system/theme.css` dan di-map ke Tailwind via `tailwind.config.ts`.

| CSS Variable | Nilai RGB | Tailwind Class | Gunakan Untuk |
|---|---|---|---|
| `--color-bg` | `245 241 237` | `bg-cream` / `text-cream` | Background halaman utama |
| `--color-paper` | `255 255 255` | `bg-paper` / `text-paper` | Card, modal, panel putih |
| `--color-surface` | `237 232 226` | `bg-surface` / `cream-dark` | Surface sekunder, badge bg |
| `--color-border` | `229 223 216` | `bg-cream-deeper` / `border-cream-deeper` | Border divider halus |
| `--color-text` | `26 26 26` | `text-ink` / `bg-ink` | Teks utama, tombol gelap |
| `--color-text-soft` | `58 58 58` | `text-ink-light` / `bg-ink-light` | Teks sekunder yang sedikit lebih terang |
| `--color-muted` | `107 101 96` | `text-ink-muted` | Teks placeholder, metadata, caption |
| `--color-accent` | `212 175 55` | `text-gold` / `bg-gold` / `border-gold` | Aksen utama — CTA, highlight, icon aktif |
| `--color-accent-strong` | `184 150 46` | `text-gold-dark` / `bg-gold-dark` | Gold lebih gelap untuk hover state |
| `--color-accent-soft` | `232 206 122` | `text-gold-light` / `bg-gold-light` | Gold lebih terang untuk surface ringan |
| `--color-warm` | `139 125 107` | `text-warm` | Teks dekoratif bernuansa hangat |

### Aturan Penggunaan Warna

```
Latar halaman  →  bg-cream
Card / panel   →  bg-paper  (putih)
Surface alt    →  bg-surface atau bg-ink/[0.04]
Teks utama     →  text-ink
Teks softer    →  text-ink-muted
Aksen / CTA    →  gold (text-gold, bg-gold, border-gold)
Dark section   →  bg-ink + text-cream (contoh: ManfaatSection, FooterSection)
```

### Opacity Utilities (Tailwind)

Gunakan opacity modifier Tailwind: `bg-ink/5`, `border-gold/40`, `text-cream/60`, dsb.

```
Divider tipis       →  border-ink/8  atau  border-cream/8
Border card         →  border-ink/8  atau  border-ink/12
Teks muted di dark  →  text-cream/50  atau  text-cream/40
Overlay gambar      →  bg-black/80  (WCAG AA compliant untuk teks putih)
```

---

## 3. Tipografi

### Font Families

| Token CSS | Tailwind Class | Font | Karakter |
|---|---|---|---|
| `--font-display` | `font-display` | Zodiak (serif) | Editorial, heading besar, judul card — **PALING DOMINAN** |
| `--font-heading` | `font-heading` | Gambetta (serif) | Sub-heading, nama karya seni |
| `--font-body` | `font-body` (default) | Satoshi (sans-serif) | Body text, UI label, caption |

> ⚠️ **Aturan Kritis:** `font-display` (Zodiak) digunakan untuk semua heading section (`h2`, `h3` besar). `font-heading` (Gambatta) untuk heading sekunder. Body text menggunakan Satoshi secara default — tidak perlu class `font-body`.

### Hierarki Heading

```tsx
// Section Title (h2) — gunakan selalu
<h2 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">

// Section Subtitle / Card Title (h3)
<h3 className="font-display text-xl font-bold leading-snug">
// atau untuk card lebih kecil:
<h3 className="font-display text-base font-semibold leading-snug">

// Sub-heading artistic (nama karya, dll)
<h3 className="font-heading text-2xl font-semibold">

// Body text
<p className="text-sm leading-relaxed text-ink-muted">

// Label / metadata kecil
<span className="text-xs font-medium uppercase tracking-widest text-ink-muted">

// Eyebrow label (selalu gunakan komponen <Eyebrow>)
<Eyebrow>Label Kategori</Eyebrow>
```

### Text Size Scale yang Umum Digunakan

| Size | Penggunaan Tipikal |
|---|---|
| `text-[10px]` | Meta sangat kecil (tanggal, read time, badge label) |
| `text-xs` | Caption, label, tag |
| `text-sm` | Body text, deskripsi, paragraph |
| `text-base` | Card title (kecil), body besar |
| `text-lg` | Card title (sedang) |
| `text-xl` – `text-2xl` | Sub-heading section |
| `text-4xl` – `text-5xl` | Section title (h2) — responsif: `text-4xl lg:text-5xl` |

---

## 4. Spacing & Layout

### Container

```tsx
import { Container } from "../design-system";

// Selalu wrap konten section dengan <Container>
<Container>...</Container>
// max-width: 1400px, padding: px-8 lg:px-12
```

### Section

```tsx
import { Section } from "../design-system";

// Default section (padding besar)
<Section id="nama-section">...</Section>
// → py-24 lg:py-32, scroll-mt-28

// Compact section
<Section id="nama-section" compact>...</Section>
// → py-20 lg:py-24, scroll-mt-28
```

> ⚠️ **Wajib:** Tambahkan `data-nav-anchor` pada elemen pertama di dalam `<Container>` agar scroll-to-section berfungsi dengan benar dari header navigation.

```tsx
<Container data-nav-anchor>
```

### Grid Patterns

```tsx
// 2 kolom standar
<div className="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:gap-20">

// 2 kolom asimetris (1:2)
<div className="grid grid-cols-1 gap-16 lg:grid-cols-[1fr_2fr]">

// 2 kolom asimetris (2:1)
<div className="grid grid-cols-1 gap-16 lg:grid-cols-[2fr_1fr]">

// 3 kolom
<div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">

// 4 kolom (untuk kartu kecil)
<div className="grid grid-cols-2 gap-5 lg:grid-cols-4">
```

### Ukuran Gap Standar

| Gap | Konteks |
|---|---|
| `gap-3` | Antar item kecil dalam list |
| `gap-5` | Antar card dalam grid |
| `gap-8` | Antar kolom medium |
| `gap-12` | Antar kolom besar |
| `gap-16` / `gap-20` | Antar section kiri-kanan |

---

## 5. Border Radius

| CSS Variable | Tailwind Class | Nilai | Gunakan Untuk |
|---|---|---|---|
| `--radius-base` | `rounded-ds` | `0` | Default — TIDAK ada radius (kotak sharp) |
| `--radius-card` | `rounded-[var(--radius-card)]` | `1rem` | Card konten (blog, FAQ panel) |
| `--radius-frame` | `rounded-[var(--radius-frame)]` | `1rem` | Frame gambar/media |
| `--radius-badge` | `rounded-[var(--radius-badge)]` | `0.75rem` | Badge, pill, accordion item |

> ⚠️ **Aturan Penting:** Sebagian besar UI (tombol, border, icon box) menggunakan `rounded-ds` = **kotak tanpa radius**. Ini adalah karakter visual khas Art Market. Jangan sembarangan menambah `rounded-lg` atau `rounded-xl` pada elemen yang bukan card/badge.

```tsx
// BENAR — elemen UI sharp (tombol, icon box, input)
<button className="border border-ink/20 px-5 py-2.5">

// BENAR — card konten
<div className="rounded-[var(--radius-card)] border">

// SALAH — jangan rounded-lg pada tombol
<button className="rounded-lg border ...">
```

---

## 6. Shadows

| Tailwind Class | CSS Value | Gunakan Untuk |
|---|---|---|
| `shadow-soft` | `0 24px 60px rgb(26 26 26 / 0.09)` | Card, panel, dropdown — bayangan halus |
| `shadow-float` | `0 18px 45px rgb(26 26 26 / 0.17)` | Elemen floating, hover state yang terangkat |

---

## 7. Komponen Design System

Semua komponen diimpor dari `"../design-system"`:

```tsx
import { Button, Container, Eyebrow, FeatureItem, GenreCard, ArtworkCard,
         IconButton, MediaFrame, Section, cx, ui } from "../design-system";
```

### `<Eyebrow>` — Label Kategori Section

```tsx
// Light background (default)
<Eyebrow className="mb-4">Judul Kategori</Eyebrow>
// → gold line kiri + teks uppercase tracking lebar

// Centered (untuk section yang teks-nya center)
<Eyebrow centered className="mb-4">Judul Kategori</Eyebrow>
// → gold line kiri + teks + gold line kanan

// Dark background
<Eyebrow dark className="mb-4">Judul Kategori</Eyebrow>
```

**Pola penggunaan standar:**
```tsx
<Eyebrow className="mb-4">Nama Eyebrow</Eyebrow>
<h2 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">
  Judul Section
</h2>
<p className="mt-4 max-w-lg text-sm leading-relaxed text-ink-muted">
  Deskripsi section.
</p>
```

---

### `<Button>` — Tombol Utama

```tsx
// Primary (gelap)
<Button variant="primary" icon={ArrowRight}>Label</Button>
// → bg-ink text-cream, hover: bg-ink-light, animasi shimmer

// Outline (kotak dengan border)
<Button variant="outline" icon={ArrowRight}>Label</Button>
// → border-ink/20, hover: border-gold text-gold

// Gold outline
<Button variant="gold-outline">Label</Button>
// → border-2 border-gold text-gold-dark, hover: bg-gold text-ink
```

---

### `<IconButton>` — Tombol Icon Sosial/Link

```tsx
// Light background
<IconButton href="#" label="Instagram Art Market" icon={Instagram} />

// Dark background (footer)
<IconButton href="#" label="Instagram Art Market" icon={Instagram} dark />
```

---

### `<ArtworkCard>` — Card Karya Seni

```tsx
<ArtworkCard
  category="Lukisan"
  artist="Nama Seniman"
  title="Judul Karya"
  price="Rp 4.500.000"
  image={{ src: "...", alt: "...", width: 400, height: 533 }}
/>

// Dengan inquiry (tanpa harga)
<ArtworkCard
  category="Patung"
  artist="Nama Seniman"
  title="Judul Karya"
  inquiry
  image={{ src: "...", alt: "...", width: 400, height: 533 }}
/>
```

---

### `<GenreCard>` — Card Kategori Seni

```tsx
<GenreCard
  title="Lukisan"
  count="240+ Karya"
  image={{ src: "...", alt: "...", width: 400, height: 533 }}
/>

// Grayscale
<GenreCard title="..." count="..." image={...} grayscale />
```

---

### `<MediaFrame>` — Wrapper Gambar Editorial

```tsx
<MediaFrame
  src="..."
  alt="Deskripsi gambar"
  width={800}
  height={600}
  className="rounded-[var(--radius-frame)]"
/>
```

---

### `<FeatureItem>` — Item Fitur dengan Icon

Digunakan di dark section (`bg-ink`):

```tsx
<FeatureItem
  icon={ShieldCheck}
  title="Judul Fitur"
  description="Deskripsi singkat fitur ini."
/>
```

---

### `cx` — Conditional Class Utility

```tsx
import { cx } from "../design-system";

// Menggabungkan class dengan kondisi
<div className={cx(
  "base-class",
  isActive && "active-class",
  variant === "dark" ? "dark-class" : "light-class",
)} />
```

---

### `ui` — Token Utility Strings

```tsx
import { ui } from "../design-system";

ui.container       // "max-w-[1400px] mx-auto px-8 lg:px-12"
ui.sectionY        // "py-24 lg:py-32"
ui.sectionYCompact // "py-20 lg:py-24"
ui.focus           // focus ring gold (light bg)
ui.focusDark       // focus ring gold (dark bg)
```

---

## 8. CSS Custom Classes (index.css)

### Animasi Tombol

```css
.btn-elegant  /* Shimmer effect pada hover — SELALU dipakai di tombol primary/dark */
.gold-line    /* Underline gold animasi pada hover — untuk nav link */
```

### Animasi Masuk

```css
.anim-fade-up    /* Fade + slide dari bawah (0.8s) */
.anim-fade-in    /* Fade in (0.9s) */
.anim-scale-in   /* Scale in dari 96% (0.9s) */

/* Delay classes: .delay-1 s/d .delay-10 (0.1s increment) */
```

### Card Image Hover

```css
.art-card          /* Container karya — triggers hover effects pada children */
.art-card .art-img /* Image yang scale saat hover */
.art-overlay       /* Overlay "Lihat Detail" yang muncul saat hover */
.art-tag           /* Tag kategori yang berubah gold saat hover */
```

### Background Textures

```css
.grain-overlay   /* Fixed noise texture tipis di atas seluruh halaman (z-index: 9999) */
.bg-plus-pattern /* Grid pattern + untuk section tertentu */
```

### Marquee

```css
.marquee-track   /* Infinite scroll horizontal (pause on hover) */
```

---

## 9. Focus & Aksesibilitas

Semua elemen interaktif **WAJIB** menggunakan `ui.focus` atau `ui.focusDark`:

```tsx
// Elemen di atas background terang (cream/paper)
className={cx("...", ui.focus)}

// Elemen di atas background gelap (ink)
className={cx("...", ui.focusDark)}
```

```
ui.focus     → focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-cream
ui.focusDark → focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-ink
```

### WCAG Contrast Rules

- Teks putih di atas gambar: **wajib** gradient overlay minimal `from-black/80`
- Teks gelap di atas `bg-cream`: sudah aman secara default
- Teks di dark section (`bg-ink`): gunakan `text-cream` — jangan `text-white`

---

## 10. Pola Section Standar

Setiap section baru harus mengikuti pola ini:

```tsx
import { Container, Eyebrow, Section } from "../design-system";

export function NamaSection() {
  return (
    <Section id="nama" className="border-b border-ink/5">
      <Container data-nav-anchor>
        {/* Header */}
        <div className="mb-14">
          <Eyebrow className="mb-4">Label Eyebrow</Eyebrow>
          <h2 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">
            Judul Utama
          </h2>
          <p className="mt-4 max-w-lg text-sm leading-relaxed text-ink-muted">
            Deskripsi singkat.
          </p>
        </div>

        {/* Konten section */}
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
          {/* ... */}
        </div>
      </Container>
    </Section>
  );
}
```

### Pola Dark Section

```tsx
<Section id="nama" className="relative overflow-hidden bg-ink text-cream">
  {/* Dekorasi lingkaran subtle (opsional) */}
  <div className="absolute right-0 top-0 h-96 w-96 -translate-y-1/2 translate-x-1/2 rounded-full border border-gold/5" />
  <Container data-nav-anchor className="relative z-10">
    <Eyebrow dark centered className="mb-4">Label</Eyebrow>
    <h2 className="font-display text-4xl font-bold text-cream lg:text-5xl">Judul</h2>
    <p className="text-cream/50">Deskripsi</p>
  </Container>
</Section>
```

---

## 11. Navigation Scroll

Untuk menambah item navigasi ke header, daftarkan di `HeaderSection.tsx`:

```tsx
const navItems = [
  { id: "nav-xxx-link", targetId: "id-section", label: "Label" },
  // ...
] as const;
```

`targetId` harus cocok dengan `id` yang dipass ke `<Section id="...">`.

---

## 12. Urutan Section di Landing Page

```
HeaderSection      ← Fixed top, glassmorphism on scroll
HeroSection        ← id: hero
MarqueeSection     ← Ticker marquee
KategoriSection    ← id: genres
KoleksiSection     ← id: gallery
ManfaatSection     ← id: value  (dark bg-ink)
TentangSection     ← id: about
BlogSection        ← id: blog
FaqSection         ← id: faq
BergabungSection   ← id: cta  (dark bg-ink)
FooterSection      ← id: footer
```

---

## 13. Aturan Tambahan untuk Agent

1. **Jangan hardcode warna** — selalu gunakan token (`text-ink`, `bg-gold`, dll.)
2. **Jangan `rounded-lg` sembarangan** — tombol & elemen UI pakai kotak sharp (`rounded-ds = 0`)
3. **Selalu `data-nav-anchor`** pada `<Container>` pertama di setiap section
4. **Import dari `"../design-system"`**, bukan dari path komponen langsung
5. **Icon dari `lucide-react`** dengan `aria-hidden="true"` dan `className="h-4 w-4"`
6. **Gambar dekoratif** pakai `alt=""` + `aria-hidden="true"`
7. **Hover state** selalu menggunakan gold: `hover:text-gold`, `hover:border-gold`
8. **Teks muted** di light bg: `text-ink-muted` — di dark bg: `text-cream/50`
9. **Responsive** selalu: mobile-first, desktop via `lg:` prefix
10. **Setiap elemen interaktif** wajib `id` unik + `ui.focus` atau `ui.focusDark`
