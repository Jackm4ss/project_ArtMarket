import {
  BergabungSection,
  BlogSection,
  FaqSection,
  HeroSection,
  KategoriSection,
  KoleksiSection,
  ManfaatSection,
  MarqueeSection,
  TentangSection,
} from "../sections";

export function HomePage() {
  return (
    <>
      <HeroSection />
      <MarqueeSection />
      <KategoriSection />
      <KoleksiSection />
      <ManfaatSection />
      <TentangSection />
      <BlogSection />
      <FaqSection />
      <BergabungSection />
    </>
  );
}
