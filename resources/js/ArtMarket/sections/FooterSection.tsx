import type { SVGProps } from "react";
import { Facebook, Instagram, Send, Twitter, Youtube } from "lucide-react";
import { Container, IconButton, cx, ui } from "../design-system";

const footerContent = {
  brand: "Art Market",
  description: "Platform digital nomor satu di Indonesia yang menghubungkan seniman, kreator, dan galeri dengan kolektor dan pencinta seni.",
  newsletterLabel: "Berlangganan Newsletter",
  newsletterPlaceholder: "Email Anda\u2026",
  newsletterSubmitLabel: "Kirim email newsletter",
  copyright: "\u00a9 2025 Art Market Indonesia. Semua hak cipta dilindungi.",
} as const;

const footerColumns = [
  {
    title: "Jelajahi",
    links: [
      { label: "Lukisan", href: "/katalog?category=lukisan" },
      { label: "Patung", href: "/katalog?category=patung" },
      { label: "Seni Digital", href: "/katalog?category=seni-digital" },
      { label: "Fotografi", href: "/katalog?category=fotografi" },
      { label: "Seni Cetak", href: "/katalog?category=seni-cetak" },
    ],
  },
  {
    title: "Perusahaan",
    links: [
      { label: "Tentang Kami", sectionId: "about" },
      { label: "Karier", href: "mailto:hello@artmarket.id" },
      { label: "Media & Pers", href: "/artikel" },
      { label: "Blog Seni", href: "/artikel" },
      { label: "Hubungi Kami", href: "mailto:hello@artmarket.id" },
    ],
  },
  {
    title: "Bantuan",
    links: [
      { label: "FAQ", sectionId: "faq" },
      { label: "Pengiriman", href: "/artikel" },
      { label: "Pengembalian", href: "/artikel" },
      { label: "Privasi", href: "/artikel" },
      { label: "Syarat & Ketentuan", href: "/artikel" },
    ],
  },
] as const;

const linkClass = "block text-sm text-cream/40 transition-colors hover:text-gold";

function scrollToLandingSection(sectionId: string) {
  if (window.location.pathname !== "/") {
    sessionStorage.setItem("artmarket:pending-scroll", sectionId);
    window.location.assign("/");
    return;
  }

  const section = document.getElementById(sectionId);
  const header = document.querySelector("header");

  if (!section) {
    return;
  }

  const target = section.querySelector<HTMLElement>("[data-nav-anchor]") ?? section;
  const headerOffset = header instanceof HTMLElement ? header.offsetHeight : 0;
  const top = window.scrollY + target.getBoundingClientRect().top - headerOffset - 20;

  window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
}

function TikTokIcon(props: SVGProps<SVGSVGElement>) {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" {...props}>
      <path d="M17.4 6.4c-1.1-.7-1.8-2-2-3.1h-3.2v12.2c0 1.7-1.3 3-3 3a3 3 0 1 1 0-6c.3 0 .6 0 .9.1V9.3a6.2 6.2 0 0 0-.9-.1 6.3 6.3 0 1 0 6.3 6.3V9.4c1.2.9 2.6 1.4 4.2 1.5V7.7c-.8 0-1.6-.5-2.3-1.3Z" />
    </svg>
  );
}

export function FooterSection() {
  return (
    <footer id="footer" className="scroll-mt-28 bg-ink pb-8 pt-20 text-cream">
      <Container data-nav-anchor>
        <div className="grid grid-cols-1 gap-12 border-b border-cream/8 pb-16 md:grid-cols-2 lg:grid-cols-12 lg:gap-8">
          <div className="lg:col-span-4">
            <div className="mb-6 flex items-center gap-3">
              <img
                src="/logo-artmarket.png"
                alt=""
                aria-hidden="true"
                className="h-10 w-auto object-contain"
              />
              <span className="font-display text-xl font-semibold tracking-tight">{footerContent.brand}</span>
            </div>
            <p className="mb-8 max-w-xs text-sm leading-relaxed text-cream/40">{footerContent.description}</p>
            <form className="max-w-sm" onSubmit={(event) => event.preventDefault()}>
              <label htmlFor="newsletter-email" className="mb-3 block text-xs font-semibold uppercase tracking-[0.2em] text-cream/60">
                {footerContent.newsletterLabel}
              </label>
              <div className="flex">
                <input
                  id="newsletter-email"
                  name="email"
                  type="email"
                  inputMode="email"
                  autoComplete="email"
                  spellCheck={false}
                  placeholder={footerContent.newsletterPlaceholder}
                  className={cx(
                    "newsletter-input min-w-0 flex-1 border border-cream/10 bg-cream/5 px-4 py-3 text-sm text-cream transition-colors placeholder:text-cream/30 focus:border-gold",
                    ui.focusDark,
                  )}
                />
                <button
                  type="button"
                  aria-label={footerContent.newsletterSubmitLabel}
                  className={cx("flex-shrink-0 bg-gold px-5 py-3 text-sm font-semibold uppercase tracking-wider text-ink transition-colors hover:bg-gold-light", ui.focusDark)}
                >
                  <Send aria-hidden="true" className="h-4 w-4" />
                </button>
              </div>
            </form>
          </div>

          {footerColumns.map((column, index) => (
            <div key={column.title} className={cx("lg:col-span-2", index === 0 && "lg:col-start-6")}>
              <div className="mb-6 text-xs font-semibold uppercase tracking-[0.2em] text-cream/60">{column.title}</div>
              <div className="space-y-3">
                {column.links.map((link) => (
                  "sectionId" in link ? (
                    <button
                      key={link.label}
                      type="button"
                      onClick={() => scrollToLandingSection(link.sectionId)}
                      className={cx(linkClass, "text-left", ui.focusDark)}
                    >
                      {link.label}
                    </button>
                  ) : (
                    <a key={link.label} href={link.href} className={cx(linkClass, ui.focusDark)}>
                      {link.label}
                    </a>
                  )
                ))}
              </div>
            </div>
          ))}
        </div>

        <div className="flex flex-col items-center justify-between gap-6 pt-8 md:flex-row">
          <div className="text-xs text-cream/30">{footerContent.copyright}</div>
          <div className="flex items-center gap-5">
            <IconButton href="https://www.instagram.com/" target="_blank" rel="noreferrer" label="Instagram Art Market" icon={Instagram} dark />
            <IconButton href="https://x.com/" target="_blank" rel="noreferrer" label="Twitter Art Market" icon={Twitter} dark />
            <IconButton href="https://www.facebook.com/" target="_blank" rel="noreferrer" label="Facebook Art Market" icon={Facebook} dark />
            <IconButton href="https://www.youtube.com/" target="_blank" rel="noreferrer" label="YouTube Art Market" icon={Youtube} dark />
            <a
              href="https://www.tiktok.com/"
              target="_blank"
              rel="noreferrer"
              aria-label="TikTok Art Market"
              className={cx("inline-flex h-9 w-9 items-center justify-center border border-cream/10 text-cream/40 transition-colors duration-300 hover:border-gold hover:text-gold", ui.focusDark)}
            >
              <TikTokIcon aria-hidden="true" className="h-4 w-4" />
            </a>
          </div>
        </div>
      </Container>
    </footer>
  );
}
