import { HandCoins, Headset, ScanFace } from "lucide-react";
import { Container, Eyebrow, MediaFrame, Section, cx } from "../design-system/components";

const aboutCopy = {
  eyebrow: "Tentang Kami",
  titleLines: ["Membangun", "Ekosistem Seni"],
  highlight: "Indonesia",
  paragraphs: [
    "Art Market lahir dari keinginan untuk memberikan ruang yang layak bagi seniman Indonesia untuk memamerkan dan menjual karya mereka. Kami percaya bahwa setiap karya seni memiliki cerita dan nilai yang pantas diapresiasi.",
    "Dengan teknologi modern dan pemahaman mendalam tentang dunia seni lokal, kami menciptakan jembatan antara kreator dan penikmat seni \u2014 tanpa batas geografis.",
  ],
} as const;

const aboutImages = [
  {
    src: "https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=500&q=80",
    alt: "Artist at work",
    width: 500,
    height: 667,
  },
  {
    src: "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=500&q=80",
    alt: "Art gallery",
    width: 500,
    height: 667,
  },
] as const;

const aboutStats = [
  { value: "98%", label: "Kepuasan", accent: true },
  { value: "5rb+", label: "Transaksi", accent: false },
] as const;

const aboutBadges = [
  { icon: ScanFace, label: "Verifikasi Identitas" },
  { icon: HandCoins, label: "Escrow Payment" },
  { icon: Headset, label: "Support 24/7" },
] as const;

export function AboutSection() {
  return (
    <Section id="about" className="border-b border-ink/5">
      <Container>
        <div className="grid grid-cols-1 items-center gap-20 lg:grid-cols-2">
          <div className="relative">
            <div className="grid grid-cols-2 gap-4">
              <MediaFrame {...aboutImages[0]} className="aspect-[3/4]" imageClassName="transition-transform duration-700 hover:scale-105" />
              <MediaFrame {...aboutImages[1]} className="mt-12 aspect-[3/4]" imageClassName="transition-transform duration-700 hover:scale-105" />
            </div>
            <div className="absolute -bottom-6 left-1/2 flex -translate-x-1/2 items-center gap-6 bg-paper px-8 py-5 shadow-float">
              {aboutStats.map((stat, index) => (
                <div key={stat.label} className="contents">
                  {index > 0 ? <div className="h-10 w-px bg-ink/10" /> : null}
                  <div className="text-center">
                    <div className={cx("font-display text-2xl font-bold", stat.accent ? "text-gold" : "text-ink")}>{stat.value}</div>
                    <div className="mt-0.5 text-[10px] uppercase tracking-widest text-ink-muted">{stat.label}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>
          <div>
            <Eyebrow className="mb-4">{aboutCopy.eyebrow}</Eyebrow>
            <h2 className="font-display text-4xl font-bold leading-tight tracking-tight lg:text-5xl">
              {aboutCopy.titleLines[0]}
              <br />
              {aboutCopy.titleLines[1]}
              <br />
              <span className="font-heading font-normal italic text-gold-dark">{aboutCopy.highlight}</span>
            </h2>
            {aboutCopy.paragraphs.map((paragraph, index) => (
              <p key={paragraph} className={cx(index === 0 ? "mt-8" : "mt-4", "max-w-lg leading-relaxed text-ink-muted")}>
                {paragraph}
              </p>
            ))}
            <div className="mt-10 flex flex-wrap gap-8">
              {aboutBadges.map(({ icon: Icon, label }) => (
                <div key={label} className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center bg-cream-dark">
                    <Icon aria-hidden="true" className="h-5 w-5 text-gold" />
                  </div>
                  <span className="text-sm font-medium">{label}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </Container>
    </Section>
  );
}
