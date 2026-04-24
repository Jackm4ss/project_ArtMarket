import { defaultTheme } from "./design-system/tokens";
import {
  AboutSection,
  CtaSection,
  FeaturedGallerySection,
  FooterSection,
  GenresSection,
  HeaderSection,
  HeroSection,
  MarqueeSection,
  ValuePropositionSection,
} from "./sections";

export default function App() {
  return (
    <div data-theme={defaultTheme} className="min-h-screen overflow-x-hidden bg-cream font-body text-ink">
      <a href="#main-content" className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[10000] focus:bg-ink focus:px-4 focus:py-2 focus:text-cream">
        Lewati ke konten utama
      </a>
      <div className="grain-overlay" />
      <HeaderSection />
      <main id="main-content">
        <HeroSection />
        <MarqueeSection />
        <GenresSection />
        <FeaturedGallerySection />
        <ValuePropositionSection />
        <AboutSection />
        <CtaSection />
      </main>
      <FooterSection />
    </div>
  );
}
