import { ArrowRight } from "lucide-react";
import { ArtworkCard, Container, Eyebrow, Section, cx, ui } from "../design-system";

const galleryCopy = {
  eyebrow: "Koleksi Pilihan",
  title: "Karya Unggulan",
  viewAllLabel: "Lihat Semua",
} as const;

const artworks = [
  {
    category: "Lukisan",
    artist: "Anindya Kusuma",
    title: "Alam Bawah Sadar",
    href: "/katalog?category=lukisan",
    price: "Rp 28.500.000",
    image: {
      src: "https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=600&q=80",
      alt: "Abstract Painting",
      width: 600,
      height: 800,
    },
  },
  {
    category: "Patung",
    artist: "Raka Prasetya",
    title: "Fragmen Waktu",
    href: "/katalog?category=patung",
    price: "Rp 45.000.000",
    image: {
      src: "https://images.unsplash.com/photo-1578321272176-b7bbc0679853?w=600&q=80",
      alt: "Sculpture",
      width: 600,
      height: 800,
    },
  },
  {
    category: "Seni Digital",
    artist: "Sari Dewi",
    title: "Dimensi Paralel #7",
    href: "/katalog?category=seni-digital",
    price: "Rp 8.200.000",
    image: {
      src: "https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?w=600&q=80",
      alt: "Digital Art",
      width: 600,
      height: 800,
    },
  },
  {
    category: "Fotografi",
    artist: "Budi Hartono",
    title: "Jejak Cahaya Senja",
    href: "/katalog?category=fotografi",
    inquiry: true,
    image: {
      src: "https://images.unsplash.com/photo-1482160549825-59d1b23cb208?w=600&q=80",
      alt: "Photography",
      width: 600,
      height: 800,
    },
  },
] as const;

export function KoleksiSection() {
  return (
    <Section id="gallery" spacing="loose">
      <Container data-nav-anchor>
        <div className="mb-16 flex flex-col md:flex-row md:items-end md:justify-between">
          <div>
            <Eyebrow className="mb-4">{galleryCopy.eyebrow}</Eyebrow>
            <h2 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">{galleryCopy.title}</h2>
          </div>
          <a
            id="gallery-viewall-link"
            href="/katalog"
            className={cx(
              "group mt-6 inline-flex items-center gap-2 text-sm font-medium uppercase tracking-widest text-ink-muted transition-colors hover:text-gold md:mt-0",
              ui.focus,
            )}
          >
            {galleryCopy.viewAllLabel}
            <ArrowRight aria-hidden="true" className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </a>
        </div>
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
          {artworks.map((artwork) => (
            <ArtworkCard key={artwork.title} {...artwork} />
          ))}
        </div>
      </Container>
    </Section>
  );
}
