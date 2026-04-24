import { BadgeCheck, BarChart3, Brush, Gem, Globe, Lock, Percent, ShieldCheck, Sparkles, Truck } from "lucide-react";
import { Container, Eyebrow, FeatureItem, Section, cx } from "../design-system";

const valueCopy = {
  eyebrow: "Mengapa Art Market",
  titlePrefix: "Satu Platform,",
  highlight: "Dua Dunia",
} as const;

const sellerFeatures = [
  {
    icon: Percent,
    title: "Tanpa Biaya Listing",
    description: "Unggah karya tanpa batas, gratis. Kami hanya mengambil komisi kecil saat karya terjual.",
  },
  {
    icon: Globe,
    title: "Jangkauan Nasional",
    description: "Akses ke jaringan kolektor dan pembeli dari 34 provinsi di Indonesia.",
  },
  {
    icon: ShieldCheck,
    title: "Profil Terverifikasi",
    description: "Dapatkan badge verifikasi yang meningkatkan kepercayaan pembeli terhadap karya Anda.",
  },
  {
    icon: BarChart3,
    title: "Dashboard Analitik",
    description: "Pantau performa karya, engagement, dan pendapatan melalui dashboard intuitif.",
  },
] as const;

const collectorFeatures = [
  {
    icon: Sparkles,
    title: "Kurasi Berkualitas",
    description: "Setiap karya melewati proses kurasi untuk memastikan kualitas dan keaslian terjaga.",
  },
  {
    icon: BadgeCheck,
    title: "Seniman Terverifikasi",
    description: "Beli langsung dari seniman asli dengan identitas dan portofolio yang diverifikasi.",
  },
  {
    icon: Lock,
    title: "Transaksi Aman",
    description: "Sistem escrow melindungi pembayaran Anda hingga karya diterima dengan selamat.",
  },
  {
    icon: Truck,
    title: "Pengiriman Terjamin",
    description: "Pengemasan khusus seni dan asuransi pengiriman untuk setiap karya fisik.",
  },
] as const;

const valueColumns = [
  {
    index: "01",
    icon: Brush,
    titleLines: ["Untuk Seniman", "& Kreator"],
    description: "Pamerkan dan jual karya Anda kepada ribuan kolektor dan pencinta seni di seluruh Indonesia.",
    features: sellerFeatures,
  },
  {
    index: "02",
    icon: Gem,
    titleLines: ["Untuk Kolektor", "& Pencinta Seni"],
    description: "Temukan dan koleksi karya seni autentik dari seniman terbaik Indonesia dengan jaminan keaslian.",
    features: collectorFeatures,
  },
] as const;

export function ManfaatSection() {
  return (
    <Section id="value" className="relative overflow-hidden bg-ink text-cream">
      <div className="absolute right-0 top-0 h-96 w-96 -translate-y-1/2 translate-x-1/2 rounded-full border border-gold/5" />
      <div className="absolute bottom-0 left-0 h-64 w-64 -translate-x-1/2 translate-y-1/2 rounded-full border border-gold/5" />
      <Container data-nav-anchor className="relative z-10">
        <div className="mb-20 text-center">
          <Eyebrow centered className="mb-4">
            {valueCopy.eyebrow}
          </Eyebrow>
          <h2 className="font-display text-4xl font-bold tracking-tight text-cream lg:text-5xl">
            {valueCopy.titlePrefix} <span className="font-heading font-normal italic text-gold">{valueCopy.highlight}</span>
          </h2>
        </div>
        <div className="grid grid-cols-1 lg:grid-cols-2">
          {valueColumns.map((column, index) => {
            const Icon = column.icon;
            return (
              <div
                key={column.index}
                className={cx(
                  "group relative border border-cream/8 p-10 transition-colors duration-500 hover:border-gold/20 lg:p-14",
                  index === 1 && "border-t-0 lg:border-l-0 lg:border-t",
                )}
              >
                <div className="absolute right-8 top-8 font-display text-7xl font-bold tracking-tighter text-cream/[0.03]">{column.index}</div>
                <div className="mb-8 flex h-14 w-14 items-center justify-center border border-gold/40">
                  <Icon aria-hidden="true" className="h-7 w-7 text-gold" />
                </div>
                <h3 className="mb-3 font-display text-2xl font-semibold tracking-tight lg:text-3xl">
                  {column.titleLines[0]}
                  <br />
                  {column.titleLines[1]}
                </h3>
                <p className="mb-10 max-w-sm text-sm text-cream/50">{column.description}</p>
                <div className="space-y-6">
                  {column.features.map((feature) => (
                    <FeatureItem key={feature.title} {...feature} />
                  ))}
                </div>
              </div>
            );
          })}
        </div>
      </Container>
    </Section>
  );
}
