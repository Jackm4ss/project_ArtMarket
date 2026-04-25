import { ArrowRight, ArrowUpRight, Star } from "lucide-react";
import { Button, Container, Eyebrow, MediaFrame } from "../design-system";

const heroImages = {
  main: {
    src: "https://images.unsplash.com/photo-1606159425092-c98d8e8599e7?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxJbmRvbmVzaWFuJTIwY29udGVtcG9yYXJ5JTIwYXJ0JTIwcGFpbnRpbmclMjBnYWxsZXJ5fGVufDF8fHx8MTc3NjY5Njk3OHww&ixlib=rb-4.1.0&q=80&w=1080",
    alt: "Seniman sedang melukis karya seni",
    width: 1080,
    height: 1010,
  },
  abstract: {
    src: "https://images.unsplash.com/photo-1635141849017-c531949fb5b3?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHxhYnN0cmFjdCUyMGFydCUyMGNvbG9yZnVsJTIwY2FudmFzJTIwcGFpbnRpbmd8ZW58MXx8fHwxNzc2Njk2OTc4fDA&ixlib=rb-4.1.0&q=80&w=400",
    alt: "Karya seni abstrak",
    width: 400,
    height: 400,
  },
  studio: {
    src: "https://images.unsplash.com/photo-1658048223386-e1117ffc8298?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHxyZWFsaXN0aWMlMjBwb3J0cmFpdCUyMG9pbCUyMHBhaW50aW5nJTIwYXJ0aXN0fGVufDF8fHx8MTc3NjY5Njk4M3ww&ixlib=rb-4.1.0&q=80&w=400",
    alt: "Studio seniman potret",
    width: 400,
    height: 442,
  },
} as const;

const heroContent = {
  eyebrow: "Platform Seni Indonesia",
  title: ["Temukan", "Karya Seni"],
  highlight: "Luar Biasa",
  description:
    "Menghubungkan seniman berbakat Indonesia dengan kolektor dan pencinta seni. Jual beli karya fisik maupun digital dalam satu platform terpercaya.",
  actions: [
    { id: "hero-explore-btn", href: "/katalog", label: "Jelajahi Koleksi", icon: ArrowRight, variant: "primary" },
    { id: "hero-artist-btn", href: "/seller/register", label: "Daftar Seniman", icon: ArrowUpRight, variant: "outline" },
  ],
  stats: [
    { value: "2.400+", label: "Karya Seni" },
    { value: "850+", label: "Seniman" },
    { value: "34", label: "Kota" },
  ],
} as const;

export function HeroSection() {
  return (
    <section className="relative overflow-hidden pt-20">
      <Container>
        <div className="grid min-h-[85vh] grid-cols-12 items-center gap-6">
          <div className="relative z-10 col-span-12 py-16 lg:col-span-5 lg:py-0">
            <div className="anim-fade-up delay-1">
              <Eyebrow className="mb-8">{heroContent.eyebrow}</Eyebrow>
            </div>

            <h1 className="anim-fade-up delay-2 font-display text-5xl font-bold leading-[1.05] tracking-tight text-ink lg:text-6xl xl:text-7xl">
              {heroContent.title[0]}
              <br />
              {heroContent.title[1]}
              <br />
              <span className="font-heading font-normal italic text-gold-dark">{heroContent.highlight}</span>
            </h1>

            <p className="anim-fade-up delay-3 mt-8 max-w-md text-lg font-light leading-relaxed text-ink-muted">{heroContent.description}</p>

            <div className="anim-fade-up delay-4 mt-10 flex flex-wrap items-center gap-4">
              {heroContent.actions.map((action) => (
                <Button key={action.id} id={action.id} href={action.href} icon={action.icon} variant={action.variant}>
                  {action.label}
                </Button>
              ))}
            </div>

            <div className="anim-fade-up delay-5 mt-14 flex items-center gap-10">
              {heroContent.stats.map((stat, index) => (
                <div key={stat.label} className="flex items-center gap-10">
                  {index > 0 ? <div className="h-10 w-px bg-ink/10" /> : null}
                  <div>
                    <div className="font-display text-3xl font-bold tracking-tight">{stat.value}</div>
                    <div className="mt-1 text-xs uppercase tracking-widest text-ink-muted">{stat.label}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="relative col-span-12 mt-8 flex h-full items-center lg:col-span-7 lg:mt-0">
            <div className="anim-scale-in delay-3 relative mx-auto aspect-square w-full max-w-[540px] lg:ml-auto">
              <MediaFrame
                {...heroImages.main}
                loading="eager"
                className="absolute bottom-12 left-4 right-0 top-0 rounded-ds-frame shadow-soft"
              />
              <MediaFrame
                {...heroImages.abstract}
                className="absolute bottom-24 -left-1 h-28 w-28 rounded-ds-badge border-4 border-paper shadow-float sm:-left-8 sm:h-36 sm:w-36"
              />
              <MediaFrame
                {...heroImages.studio}
                imageClassName="grayscale"
                className="absolute -bottom-4 right-0 h-36 w-32 rounded-ds-badge border-4 border-paper shadow-float sm:h-44 sm:w-40"
              />
              <div className="absolute -right-2 top-8 rounded-ds-badge border border-cream-deeper bg-paper px-4 py-3 shadow-float sm:-right-6">
                <p className="text-xs text-ink-muted">Mulai dari</p>
                <p className="font-bold text-gold-dark">Rp 350.000</p>
              </div>
              <div className="absolute bottom-28 right-10 rounded-ds-badge bg-gold px-4 py-2 shadow-float sm:bottom-32">
                <div className="flex items-center gap-1 text-cream">
                  <Star aria-hidden="true" className="h-3.5 w-3.5 fill-current" />
                  <span className="text-sm font-bold">4.9</span>
                </div>
                <p className="text-xs text-cream/85">Rating Terpercaya</p>
              </div>
            </div>
          </div>
        </div>
      </Container>
      <div className="anim-fade-in delay-6 absolute left-12 top-32 h-32 w-px bg-gradient-to-b from-transparent via-gold/30 to-transparent" />
      <div className="anim-fade-in delay-9 absolute bottom-20 right-24 h-24 w-24 rotate-45 border border-gold/10" />
    </section>
  );
}
