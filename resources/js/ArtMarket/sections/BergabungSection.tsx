import { Palette, ShoppingBag } from "lucide-react";
import { Button, Eyebrow, Section } from "../design-system";

const ctaContent = {
  eyebrow: "Bergabung Sekarang",
  titleLines: ["Mulai Perjalanan", "Seni Anda"],
  highlight: "Hari Ini",
  description: "Bergabunglah dengan ribuan seniman dan kolektor yang telah mempercayai Art Market sebagai rumah karya seni mereka.",
  helperText: "Gratis untuk bergabung \u00b7 Tanpa biaya bulanan \u00b7 Mulai jual dalam 5 menit",
  actions: [
    { id: "cta-buyer-btn", href: "/register", label: "Daftar Sebagai Pembeli", icon: ShoppingBag, variant: "primary" },
    { id: "cta-seller-btn", href: "/seller/register", label: "Jadi Seniman", icon: Palette, variant: "gold-outline" },
  ],
} as const;

export function BergabungSection() {
  return (
    <Section id="cta" spacing="loose" className="relative overflow-hidden">
      <div className="absolute inset-0 opacity-[0.02] bg-plus-pattern" />
      <div data-nav-anchor className="relative z-10 mx-auto max-w-[900px] px-8 text-center lg:px-12">
        <Eyebrow centered className="mb-6">
          {ctaContent.eyebrow}
        </Eyebrow>
        <h2 className="font-display text-4xl font-bold leading-tight tracking-tight lg:text-6xl">
          {ctaContent.titleLines[0]}
          <br />
          {ctaContent.titleLines[1]} <span className="font-heading font-normal italic text-gold-dark">{ctaContent.highlight}</span>
        </h2>
        <p className="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-ink-muted">{ctaContent.description}</p>
        <div className="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
          {ctaContent.actions.map((action) => (
            <Button key={action.id} id={action.id} href={action.href} icon={action.icon} variant={action.variant} className="w-full px-10 py-5 sm:w-auto">
              {action.label}
            </Button>
          ))}
        </div>
        <p className="mt-6 text-xs text-ink-muted">{ctaContent.helperText}</p>
      </div>
    </Section>
  );
}
