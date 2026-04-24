const marqueeItems = [
  "Lukisan",
  "Patung",
  "Seni Digital",
  "Fotografi",
  "Kaligrafi",
  "Instalasi",
  "Batik Kontemporer",
  "Ilustrasi",
  "Seni Cetak",
  "Mixed Media",
] as const;

export function MarqueeSection() {
  return (
    <div className="overflow-hidden border-y border-ink/8 bg-cream-dark/50 py-4">
      <div className="marquee-track flex w-max whitespace-nowrap">
        {[0, 1].map((track) => (
          <span key={track} className="mx-4 flex items-center gap-8 text-xs font-medium uppercase tracking-[0.3em] text-ink-muted">
            {marqueeItems.map((item, index) => (
              <span key={`${track}-${item}-${index}`} className="contents">
                <span>{item}</span>
                <span className="text-gold">{"\u2726"}</span>
              </span>
            ))}
          </span>
        ))}
      </div>
    </div>
  );
}
