export const themes = {
  artMarket: {
    id: "art-market",
    label: "Art Market",
    description: "Warm editorial marketplace theme with ink text and gold accents.",
  },
  galleryMinimal: {
    id: "gallery-minimal",
    label: "Gallery Minimal",
    description: "Cleaner gallery preset for imported sections that need more restraint.",
  },
} as const;

export type ThemeId = (typeof themes)[keyof typeof themes]["id"];

export const defaultTheme: ThemeId = themes.artMarket.id;

export const ui = {
  container: "max-w-[1400px] mx-auto px-8 lg:px-12",
  sectionY: "py-24 lg:py-32",
  sectionYLoose: "py-28 lg:py-36 xl:py-40",
  sectionYCompact: "py-20 lg:py-24",
  focus:
    "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-cream",
  focusDark:
    "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-ink",
} as const;
