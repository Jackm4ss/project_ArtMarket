import { ArrowRight } from "lucide-react";
import { Container, Eyebrow, GenreCard, Section, cx, ui } from "../design-system";

const genreCopy = {
  eyebrow: "Beragam Kategori",
  title: "Jelajahi Berbagai Kategori",
  viewAllLabel: "Lihat Semua Kategori",
} as const;

const genres = [
  {
    title: "Lukisan",
    count: "420+ karya",
    image: {
      src: "/genres/genre_lukisan.png",
      alt: "Kategori seni lukisan",
      width: 600,
      height: 800,
    },
  },
  {
    title: "Patung",
    count: "210+ karya",
    image: {
      src: "/genres/genre_patung.png",
      alt: "Kategori seni patung",
      width: 600,
      height: 800,
    },
  },
  {
    title: "Relief",
    count: "150+ karya",
    image: {
      src: "/genres/genre_relief.png",
      alt: "Kategori seni relief",
      width: 600,
      height: 800,
    },
  },
  {
    title: "Kerajinan Seni",
    count: "380+ karya",
    image: {
      src: "/genres/genre_kerajinan.png",
      alt: "Kategori kerajinan seni",
      width: 600,
      height: 800,
    },
  },
  {
    title: "Dekorasi Artistik",
    count: "290+ karya",
    image: {
      src: "/genres/genre_dekorasi.png",
      alt: "Kategori dekorasi artistik",
      width: 600,
      height: 800,
    },
  },
] as const;

export function KategoriSection() {
  return (
    <Section id="genres" compact className="bg-cream lg:flex lg:min-h-screen lg:items-center">
      <Container data-nav-anchor className="w-full">
        <div className="mb-12 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <Eyebrow className="mb-4">{genreCopy.eyebrow}</Eyebrow>
            <h2 className="font-display text-4xl font-bold tracking-tight text-ink lg:text-5xl">{genreCopy.title}</h2>
          </div>
          <a id="genres-viewall-link" href="#" className={cx("group inline-flex items-center gap-2 text-sm font-medium text-gold-dark transition-colors hover:text-gold", ui.focus)}>
            {genreCopy.viewAllLabel}
            <ArrowRight aria-hidden="true" className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </a>
        </div>

        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
          {genres.map((genre) => (
            <GenreCard key={genre.title} {...genre} />
          ))}
        </div>
      </Container>
    </Section>
  );
}
