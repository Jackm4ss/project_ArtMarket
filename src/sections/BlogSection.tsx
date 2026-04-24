import { ArrowRight, Clock } from "lucide-react";
import { Container, Eyebrow, Section, cx, ui } from "../design-system";

// ─── Types ────────────────────────────────────────────────────────────────────

interface BlogPost {
  id: string;
  category: string;
  title: string;
  excerpt: string;
  author: string;
  date: string;
  readTime: string;
  image: string;
}

// ─── Data — swap with API / CMS later ────────────────────────────────────────

const blogCopy = {
  eyebrow: "Jurnal Seni",
  title: "Inspirasi & Wawasan",
  subtitle:
    "Temukan cerita di balik karya, tips berkoleksi, dan tren seni terkini dari para ahli.",
  viewAllLabel: "Semua Artikel",
};

const posts: BlogPost[] = [
  {
    id: "post-1",
    category: "Panduan Koleksi",
    title: "Cara Memilih Karya Seni Pertama Anda",
    excerpt: "Panduan lengkap untuk kolektor baru dari memilih medium hingga memahami nilai investasi.",
    author: "Tim Artmarket",
    date: "15 Apr 2026",
    readTime: "5 menit baca",
    image: "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=900&q=80",
  },
  {
    id: "post-2",
    category: "Kisah Seniman",
    title: "Raka Prasetya: Dari Studio ke Galeri Internasional",
    excerpt: "Perjalanan pematung muda Yogyakarta yang kini karyanya dipajang di tiga negara.",
    author: "Tim Artmarket",
    date: "15 Apr 2026",
    readTime: "5 menit baca",
    image: "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=700&q=80",
  },
  {
    id: "post-3",
    category: "Tren Seni",
    title: "Seni Abstrak Ekspresionisme Kembali Bersinar",
    excerpt: "Mengapa pasar seni dunia melirik kembali gaya ekspresionis dan maknanya bagi seniman Indonesia.",
    author: "Tim Artmarket",
    date: "15 Apr 2026",
    readTime: "5 menit baca",
    image: "https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=700&q=80",
  },
  {
    id: "post-4",
    category: "Budaya",
    title: "Mengenal Batik sebagai Warisan Seni Dunia",
    excerpt: "UNESCO mengakui batik sebagai warisan budaya — bagaimana ini berdampak pada pasar seni lokal?",
    author: "Tim Artmarket",
    date: "15 Apr 2026",
    readTime: "5 menit baca",
    image: "https://images.unsplash.com/photo-1618220179428-22790b461013?w=700&q=80",
  },
  {
    id: "post-5",
    category: "Investasi Seni",
    title: "Investasi Seni: ROI dan Strategi Jangka Panjang",
    excerpt: "Pelajari bagaimana kolektor cerdas membangun portofolio berbasis karya seni bernilai tinggi.",
    author: "Tim Artmarket",
    date: "15 Apr 2026",
    readTime: "5 menit baca",
    image: "https://images.unsplash.com/photo-1578321272176-b7bbc0679853?w=700&q=80",
  },
];



// ─── Category glassmorphism tints ─────────────────────────────────────────────────────

const categoryGlass: Record<string, string> = {
  "Panduan Koleksi": "bg-sky-400/30 border-sky-300/40",
  "Kisah Seniman":   "bg-rose-400/30 border-rose-300/40",
  "Tren Seni":       "bg-emerald-400/30 border-emerald-300/40",
  "Budaya":          "bg-amber-400/30 border-amber-300/40",
  "Investasi Seni":  "bg-violet-400/30 border-violet-300/40",
};
const defaultGlass = "bg-white/15 border-white/25";

// ─── Blog Card ────────────────────────────────────────────────────────────────


function BlogCard({ post }: { post: BlogPost }) {
  return (
    <a
      id={`blog-${post.id}`}
      href="#"
      className={cx(
        "group relative flex w-full flex-col justify-end overflow-hidden",
        "rounded-[var(--radius-card)] min-h-[340px]",
        ui.focus,
      )}
    >
      {/* Full-bleed image */}
      <img
        src={post.image}
        alt=""
        aria-hidden="true"
        className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
        loading="lazy"
      />

      {/* Gradient overlay — heavy at bottom so text always readable */}
      <div className="absolute inset-0 bg-gradient-to-t from-black/95 via-black/60 to-black/0" />

      {/* Category pill — colored glassmorphism, top-left */}
      <span
        className={cx(
          "absolute left-4 top-4 z-10 rounded-full border px-3 py-1",
          "text-[10px] font-bold uppercase tracking-widest text-white backdrop-blur-md",
          categoryGlass[post.category] ?? defaultGlass,
        )}
      >
        {post.category}
      </span>

      {/* Content — bottom */}
      <div className="relative z-10 p-5">
        {/* Title + excerpt */}
        <h3 className="font-display text-base font-bold leading-snug text-white lg:text-lg">
          {post.title}
        </h3>
        <p className="mt-1.5 line-clamp-2 text-xs leading-relaxed text-white/75">
          {post.excerpt}
        </p>

        {/* Footer: stacks on mobile, side-by-side on sm+ */}
        <div className="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
          <div className="flex flex-wrap items-center gap-1 text-[10px] text-white/65">
            <span className="font-semibold text-white/80">{post.author}</span>
            <span>·</span>
            <span>{post.date}</span>
            <span>·</span>
            <Clock aria-hidden="true" className="h-3 w-3" />
            <span>{post.readTime}</span>
          </div>
          <div className="inline-flex flex-shrink-0 items-center gap-1.5 text-[10px] font-semibold uppercase tracking-widest text-gold transition-transform duration-300 group-hover:translate-x-1">
            Baca Artikel
            <ArrowRight aria-hidden="true" className="h-3 w-3" />
          </div>
        </div>
      </div>
    </a>
  );
}

// ─── Section ──────────────────────────────────────────────────────────────────

/**
 * Layout: CSS `columns` (masonry) — 1 col mobile, 2 col desktop.
 * Works for any number of posts (2, 3, 4, 5, 6+) without code changes.
 * Card heights follow a repeating rhythm pattern for visual variety.
 */
export function BlogSection() {
  return (
    <Section id="blog" className="border-b border-ink/5">
      <Container data-nav-anchor>
        {/* Header */}
        <div className="mb-14 flex flex-col md:flex-row md:items-end md:justify-between">
          <div>
            <Eyebrow className="mb-4">{blogCopy.eyebrow}</Eyebrow>
            <h2 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">
              {blogCopy.title}
            </h2>
            <p className="mt-4 max-w-lg text-sm leading-relaxed text-ink-muted">
              {blogCopy.subtitle}
            </p>
          </div>
          <a
            id="blog-viewall-link"
            href="#"
            className={cx(
              "group mt-6 inline-flex items-center gap-2 text-sm font-medium uppercase tracking-widest text-ink-muted transition-colors hover:text-gold md:mt-0",
              ui.focus,
            )}
          >
            {blogCopy.viewAllLabel}
            <ArrowRight
              aria-hidden="true"
              className="h-4 w-4 transition-transform group-hover:translate-x-1"
            />
          </a>
        </div>

        {/* 3-col grid: 1 mobile → 2 tablet → 3 desktop */}
        <div className="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
          {posts.map((post) => (
            <BlogCard key={post.id} post={post} />
          ))}
        </div>
      </Container>
    </Section>
  );
}
