# Art Market Landing Page

React + Vite + Tailwind implementation of the Art Market landing page. The current static visual has been migrated into reusable sections and a design-system layer so future cloned sections can inherit one project-wide source of truth.

## Run

```bash
npm install
npm run dev
npm run build
```

## Struktur

```text
src/
  App.tsx                      # urutan section halaman
  sections/*Section.tsx         # layout + copy/data untuk satu section
  design-system/index.ts        # public export semua primitive design-system
  design-system/actions/        # Button, IconButton
  design-system/cards/          # ArtworkCard, GenreCard
  design-system/data-display/   # FeatureItem dan komponen informasi
  design-system/layout/         # Container, Section
  design-system/media/          # MediaFrame
  design-system/typography/     # Eyebrow dan komponen teks
  design-system/tokens.ts       # theme id, layout helper, focus ring helper
  design-system/theme.css       # CSS variable theme
public/
  genres/*.png                  # gambar kategori untuk KategoriSection
```

## Cara Maintenance

- Edit urutan section di `src/App.tsx`.
- Edit copy, gambar, list, link, dan layout di file `src/sections/*Section.tsx` terkait.
- Edit gambar kategori di `public/genres`, lalu update path di `src/sections/KategoriSection.tsx` kalau nama file berubah.
- Edit komponen reusable di folder `src/design-system` sesuai domainnya, misalnya `actions/Button.tsx`, `cards/ArtworkCard.tsx`, atau `media/MediaFrame.tsx`.
- Edit warna, font, radius, dan shadow global di `src/design-system/theme.css`.

## Fungsi File Penting

- `src/design-system/index.ts`: barrel export. Section cukup import dari `../design-system`, tidak perlu tahu lokasi internal primitive.
- `src/design-system/tokens.ts`: token TypeScript untuk theme aktif, layout container/section, dan focus ring reusable.
- `src/design-system/theme.css`: source of truth visual berbasis CSS variables untuk warna, font, radius, dan shadow.
- `src/sections/index.ts`: barrel export untuk semua section. `App.tsx` cukup mengatur urutan section dari satu import.

## Section Notes

- `KategoriSection` saat ini memakai 5 card kategori: `Lukisan`, `Patung`, `Relief`, `Kerajinan Seni`, dan `Dekorasi Artistik`.
- Asset kategori berada di `public/genres` agar mudah diganti tanpa build/import asset manual.

## Design System

- `data-theme="art-market"` adalah default.
- `data-theme="gallery-minimal"` adalah contoh alternate theme.
- Tailwind membaca token dari CSS variables, jadi perubahan tema cukup dari `theme.css`.

## Aturan Tim

- Kalau menambah section baru, buat file baru seperti `BlogSection.tsx`.
- Jangan taruh JSX section besar langsung di `App.tsx`.
- Jangan hardcode warna hex di section; gunakan token Tailwind seperti `bg-cream`, `text-ink`, `text-gold`.
- Untuk asset publik, gunakan path absolut dari `public`, contoh `/genres/genre_lukisan.png`.
- Jangan edit banyak section dalam satu PR kalau tidak perlu, supaya conflict antar maintainer tetap kecil.
