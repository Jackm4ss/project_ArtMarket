import { Eye, Palette } from "lucide-react";
import { cx } from "../design-system/components";
import { ds } from "../design-system/tokens";

const navItems = [
  { id: "nav-browse-link", href: "#gallery", label: "Jelajahi" },
  { id: "nav-sellers-link", href: "#value", label: "Untuk Penjual" },
  { id: "nav-about-link", href: "#about", label: "Tentang" },
  { id: "nav-contact-link", href: "#footer", label: "Kontak" },
] as const;

export function HeaderSection() {
  return (
    <header className="fixed left-0 top-0 z-50 w-full border-b border-cream-deeper/60 bg-cream">
      <nav className="mx-auto flex h-20 max-w-[1400px] items-center justify-between px-8 lg:px-12" aria-label="Navigasi utama">
        <a id="nav-logo-link" href="#" className={cx("group flex items-center gap-3", ds.focus)}>
          <div className="flex h-9 w-9 items-center justify-center border-2 border-ink transition-colors duration-300 group-hover:border-gold group-hover:bg-gold">
            <span className="font-display text-sm font-bold tracking-tight transition-colors group-hover:text-ink">A</span>
          </div>
          <span className="font-display text-xl font-semibold tracking-tight">Art Market</span>
        </a>

        <div className="hidden items-center gap-10 md:flex">
          {navItems.map((item) => (
            <a
              key={item.id}
              id={item.id}
              href={item.href}
              className={cx("gold-line text-sm font-medium uppercase tracking-wide text-ink-muted transition-colors hover:text-ink", ds.focus)}
            >
              {item.label}
            </a>
          ))}
        </div>

        <div className="flex items-center gap-3">
          <a
            id="nav-buyer-btn"
            href="#gallery"
            className={cx(
              "hidden items-center gap-2 border border-ink/20 px-5 py-2.5 text-sm font-medium tracking-wide transition-colors duration-300 hover:border-gold hover:text-gold sm:inline-flex",
              ds.focus,
            )}
          >
            <Eye aria-hidden="true" className="h-4 w-4" />
            Pembeli
          </a>
          <a
            id="nav-seller-btn"
            href="#value"
            className={cx(
              "btn-elegant inline-flex items-center gap-2 bg-ink px-5 py-2.5 text-sm font-medium tracking-wide text-cream transition-colors duration-300 hover:bg-ink-light",
              ds.focus,
            )}
          >
            <Palette aria-hidden="true" className="h-4 w-4" />
            Jual Karya
          </a>
        </div>
      </nav>
    </header>
  );
}
