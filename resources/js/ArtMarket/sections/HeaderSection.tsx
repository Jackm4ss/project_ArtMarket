import { Bell, ChevronRight, LogOut, Mail, Menu, Palette, ShoppingBag, Store, User, X } from "lucide-react";
import { Link, usePage } from "@inertiajs/react";
import { type ReactNode, useEffect, useMemo, useState } from "react";
import { cx, ui } from "../design-system";
import { useCart } from "../context/CartContext";

const navItems = [
  { id: "nav-genres-link", targetId: "genres", label: "Kategori" },
  { id: "nav-gallery-link", targetId: "gallery", label: "Koleksi" },
  { id: "nav-value-link", targetId: "value", label: "Manfaat" },
  { id: "nav-about-link", targetId: "about", label: "Tentang" },
  { id: "nav-blog-link", targetId: "blog", label: "Blog" },
  { id: "nav-faq-link", targetId: "faq", label: "FAQ" },
  { id: "nav-contact-link", targetId: "footer", label: "Kontak" },
] as const;

type HeaderSeller = {
  id: number;
  store_name: string;
  slug: string;
};

type HeaderUser = {
  id: number;
  name: string;
  email: string;
  avatar_url?: string | null;
  roles?: string[];
  seller?: HeaderSeller | null;
  can_manage_store?: boolean;
};

type HeaderPageProps = {
  auth?: {
    user?: HeaderUser | null;
  };
  notifications?: {
    unread_count?: number;
  };
  messages?: {
    unread_count?: number;
  };
};

function badgeLabel(count: number) {
  return count > 99 ? "99+" : String(count);
}

function displayName(name?: string) {
  const cleanName = name?.trim();

  if (!cleanName || isGenericUserName(cleanName)) {
    return "User";
  }

  return cleanName.split(/\s+/)[0] || "Akun Saya";
}

function initials(name?: string) {
  const cleanName = name?.trim();
  const display = !cleanName || isGenericUserName(cleanName) ? "Akun Saya" : cleanName;
  const parts = display.split(/\s+/).filter(Boolean);
  const value = parts.length > 1 ? `${parts[0][0]}${parts[1][0]}` : parts[0]?.slice(0, 1) || "A";

  return value.toUpperCase();
}

function isGenericUserName(name: string) {
  return ["user", "user art market", "test user", "demo user"].includes(name.trim().toLowerCase());
}

function HeaderIconLink({
  href,
  id,
  label,
  count = 0,
  children,
}: {
  href: string;
  id: string;
  label: string;
  count?: number;
  children: ReactNode;
}) {
  return (
    <Link
      href={href}
      id={id}
      aria-label={label}
      className={cx(
        "relative inline-flex h-10 w-10 items-center justify-center text-ink transition-colors duration-200 hover:text-gold",
        ui.focus,
      )}
    >
      {children}
      {count > 0 ? (
        <span className="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-paper">
          {badgeLabel(count)}
        </span>
      ) : null}
    </Link>
  );
}

function StoreMenu({ user }: { user: HeaderUser }) {
  const [open, setOpen] = useState(false);
  const hasStore = Boolean(user.seller || user.can_manage_store);
  const storeHref = hasStore ? "/seller" : "/seller/onboarding";
  const storeName = user.seller?.store_name ?? "Toko";

  return (
    <div
      className="group relative"
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
      onFocusCapture={() => setOpen(true)}
      onBlurCapture={(event) => {
        if (!event.currentTarget.contains(event.relatedTarget as Node | null)) {
          setOpen(false);
        }
      }}
    >
      <Link
        id="nav-store-link"
        href={storeHref}
        className={cx(
          "inline-flex h-11 items-center gap-2 rounded-[var(--radius-badge)] px-3 text-sm font-semibold text-ink transition-[background-color,color] duration-200 hover:bg-surface hover:text-ink",
          open && "bg-surface",
          "group-hover:bg-surface",
          ui.focus,
        )}
      >
        <span className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-paper text-gold-dark shadow-[inset_0_0_0_1px_rgba(var(--color-border),0.9)]">
          <Store aria-hidden="true" className="h-4 w-4" />
        </span>
        <span className="max-w-28 truncate">Toko</span>
      </Link>

      <div
        className={cx(
          "invisible absolute right-0 top-full z-[80] w-72 translate-y-2 pt-2 opacity-0",
          "transition-[opacity,transform,visibility] duration-200 ease-out group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100",
          open && "visible translate-y-0 opacity-100",
        )}
      >
        {hasStore ? (
          <div className="border border-ink/10 bg-paper p-4 shadow-float">
            <div>
              <p className="text-xs font-medium uppercase tracking-widest text-ink-muted">Toko aktif</p>
              <p className="mt-1 font-display text-lg font-bold text-ink">{storeName}</p>
            </div>
            <Link
              href="/seller"
              className={cx(
                "inline-flex w-full items-center justify-center bg-gold px-4 py-3 text-sm font-bold text-ink transition-colors hover:bg-gold-dark hover:text-paper",
                ui.focus,
              )}
            >
              Kelola Toko
            </Link>
            {user.seller?.slug ? (
              <Link href={`/toko/${user.seller.slug}`} className={cx("text-xs font-bold text-gold-dark hover:text-ink", ui.focus)}>
                Lihat halaman toko
              </Link>
            ) : null}
          </div>
        ) : (
          <div className="border border-ink/10 bg-paper p-4 text-center shadow-float">
            <p className="text-sm font-medium text-ink-muted">Anda belum memiliki toko.</p>
            <Link
              id="nav-open-store-free"
              href="/seller/onboarding"
              className={cx(
                "mt-4 inline-flex w-full items-center justify-center bg-gold px-4 py-3 text-sm font-bold text-ink transition-colors hover:bg-gold-dark hover:text-paper",
                ui.focus,
              )}
            >
              Buka Toko Gratis
            </Link>
            <p className="mt-3 text-xs text-ink-muted">
              Tokomu hilang?{" "}
              <Link href="/artikel" className={cx("font-bold text-gold-dark hover:text-ink", ui.focus)}>
                Pelajari Selengkapnya
              </Link>
            </p>
          </div>
        )}
      </div>
    </div>
  );
}

function Avatar({ user, size = "md" }: { user: HeaderUser; size?: "sm" | "md" | "lg" }) {
  const sizeClass = {
    sm: "h-8 w-8 text-[10px]",
    md: "h-9 w-9 text-xs",
    lg: "h-12 w-12 text-sm",
  }[size];

  if (user.avatar_url) {
    return (
      <img
        src={user.avatar_url}
        alt={`Foto profil ${displayName(user.name)}`}
        className={cx(sizeClass, "shrink-0 rounded-full object-cover shadow-soft")}
      />
    );
  }

  return (
    <span
      aria-hidden="true"
      className={cx(
        "grid shrink-0 place-items-center rounded-full border border-gold-dark/20 bg-gold font-black text-ink shadow-soft ring-2 ring-paper",
        sizeClass,
      )}
    >
      {initials(user.name)}
    </span>
  );
}

function ProfileMenu({ user }: { user: HeaderUser }) {
  const [open, setOpen] = useState(false);
  const triggerName = displayName(user.name);
  const menuName = isGenericUserName(user.name) ? "Akun Saya" : user.name.trim();

  return (
    <div
      className="group relative"
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
      onFocusCapture={() => setOpen(true)}
      onBlurCapture={(event) => {
        if (!event.currentTarget.contains(event.relatedTarget as Node | null)) {
          setOpen(false);
        }
      }}
    >
      <button
        id="nav-profile-menu-btn"
        type="button"
        aria-haspopup="menu"
        aria-expanded={open}
        onClick={() => setOpen((value) => !value)}
        className={cx(
          "inline-flex h-11 items-center gap-2 rounded-[var(--radius-badge)] px-2 text-sm font-semibold text-ink transition-[background-color,color] duration-200 hover:bg-surface",
          open && "bg-surface",
          "group-hover:bg-surface",
          ui.focus,
        )}
      >
        <Avatar user={user} />
        <span className="max-w-24 truncate">{triggerName}</span>
      </button>

      <div
        role="menu"
        className={cx(
          "invisible absolute right-0 top-full z-[80] w-72 translate-y-2 pt-2 opacity-0",
          "transition-[opacity,transform,visibility] duration-200 ease-out group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100",
          open && "visible translate-y-0 opacity-100",
        )}
      >
        <div className="border border-ink/10 bg-paper p-4 shadow-float">
          <div className="flex items-center gap-3 border-b border-ink/8 pb-4">
            <Avatar user={user} size="lg" />
            <div className="min-w-0">
              <p className="truncate font-display text-lg font-bold leading-tight text-ink">{menuName}</p>
              <p className="mt-1 truncate text-xs text-ink-muted">{user.email}</p>
            </div>
          </div>

          <div className="py-2">
            <AccountMenuLink href="/user" label="Dashboard Pembeli" />
            <AccountMenuLink href="/profile" label="Profil Akun" />
            <AccountMenuLink href="/user/orders" label="Pesanan Saya" />
            <AccountMenuLink href="/user/wishlist" label="Wishlist" />
          </div>

          <Link
            href="/logout"
            method="post"
            as="button"
            className={cx(
              "flex w-full items-center justify-between border-t border-ink/8 px-1 py-3 text-left text-sm font-bold text-ink transition-colors hover:text-gold",
              ui.focus,
            )}
          >
            <span>Keluar</span>
            <LogOut aria-hidden="true" className="h-4 w-4" />
          </Link>
        </div>
      </div>
    </div>
  );
}

function AccountMenuLink({ href, label }: { href: string; label: string }) {
  return (
    <Link
      href={href}
      role="menuitem"
      className={cx(
        "flex items-center justify-between px-1 py-3 text-sm font-semibold text-ink-muted transition-colors hover:text-gold",
        ui.focus,
      )}
    >
      <span>{label}</span>
      <ChevronRight aria-hidden="true" className="h-4 w-4" />
    </Link>
  );
}

export function HeaderSection() {
  const page = usePage();
  const pageProps = page.props as unknown as HeaderPageProps;
  const user = pageProps.auth?.user ?? null;
  const unreadNotifications = pageProps.notifications?.unread_count ?? 0;
  const unreadMessages = pageProps.messages?.unread_count ?? 0;
  const [scrolled, setScrolled] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { totalItems } = useCart();

  const isLoggedIn = Boolean(user);
  const userName = useMemo(() => displayName(user?.name), [user?.name]);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = sidebarOpen ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [sidebarOpen]);

  const scrollToSection = (sectionId: string) => {
    setSidebarOpen(false);

    if (window.location.pathname !== "/") {
      sessionStorage.setItem("artmarket:pending-scroll", sectionId);
      window.location.assign("/");
      return;
    }

    setTimeout(() => doScroll(sectionId), 300);
  };

  const doScroll = (sectionId: string) => {
    const section = document.getElementById(sectionId);
    const header = document.querySelector("header");
    if (!section) return;
    const target = section.querySelector<HTMLElement>("[data-nav-anchor]") ?? section;
    const headerOffset = header instanceof HTMLElement ? header.offsetHeight : 0;
    const top = window.scrollY + target.getBoundingClientRect().top - headerOffset - 20;
    window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
  };

  return (
    <>
      <header
        className={cx(
          "fixed left-0 top-0 z-50 w-full transition-[background-color,border-color,box-shadow,backdrop-filter] duration-300",
          scrolled
            ? "border-b border-cream-deeper/40 bg-cream/70 shadow-soft backdrop-blur-md"
            : "border-b border-cream-deeper/60 bg-cream",
        )}
      >
        <nav
          className="mx-auto flex h-20 max-w-[1400px] items-center justify-between px-8 lg:px-12"
          aria-label="Navigasi utama"
        >
          <button
            id="nav-logo-link"
            type="button"
            onClick={() => {
              if (window.location.pathname !== "/") {
                window.location.assign("/");
                return;
              }

              window.scrollTo({ top: 0, behavior: "smooth" });
            }}
            className={cx("group flex items-center gap-3", ui.focus)}
          >
            <img src="/logo-artmarket.png" alt="" aria-hidden="true" className="h-9 w-auto object-contain" />
            <span className="font-display text-xl font-semibold tracking-tight">Art Market</span>
          </button>

          <div className="hidden items-center gap-7 lg:flex xl:gap-10">
            {navItems.map((item) => (
              <button
                key={item.id}
                id={item.id}
                type="button"
                onClick={() => scrollToSection(item.targetId)}
                className={cx(
                  "gold-line text-sm font-medium uppercase tracking-wide text-ink-muted transition-colors hover:text-ink",
                  ui.focus,
                )}
              >
                {item.label}
              </button>
            ))}
          </div>

          <div className="hidden items-center gap-2 lg:flex">
            {isLoggedIn && user ? (
              <>
                <HeaderIconLink href="/cart" id="nav-cart-btn" label="Keranjang belanja" count={totalItems}>
                  <ShoppingBag aria-hidden="true" className="h-5 w-5" />
                </HeaderIconLink>
                <HeaderIconLink href="/user/notifications" id="nav-notifications-btn" label="Notifikasi" count={unreadNotifications}>
                  <Bell aria-hidden="true" className="h-5 w-5" />
                </HeaderIconLink>
                <HeaderIconLink href="/user/chats" id="nav-messages-btn" label="Pesan" count={unreadMessages}>
                  <Mail aria-hidden="true" className="h-5 w-5" />
                </HeaderIconLink>
                <span aria-hidden="true" className="mx-2 h-8 w-px bg-ink/10" />
                <StoreMenu user={user} />
                <ProfileMenu user={user} />
              </>
            ) : (
              <>
                <Link
                  href="/cart"
                  id="nav-cart-btn"
                  aria-label="Keranjang belanja"
                  className={cx(
                    "relative inline-flex h-10 w-10 items-center justify-center border border-ink/20 text-ink-muted transition-colors duration-200 hover:border-gold hover:text-gold",
                    ui.focus,
                  )}
                >
                  <ShoppingBag aria-hidden="true" className="h-4 w-4" />
                  {totalItems > 0 ? (
                    <span className="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-gold text-[10px] font-bold text-ink">
                      {badgeLabel(totalItems)}
                    </span>
                  ) : null}
                </Link>
                <Link
                  href="/katalog"
                  id="nav-buyer-btn"
                  className={cx(
                    "hidden items-center gap-2 border border-ink/20 px-5 py-2.5 text-sm font-medium tracking-wide transition-colors duration-300 hover:border-gold hover:text-gold sm:inline-flex",
                    ui.focus,
                  )}
                >
                  <User aria-hidden="true" className="h-4 w-4" />
                  Pembeli
                </Link>
                <button
                  id="nav-seller-btn"
                  type="button"
                  onClick={() => scrollToSection("cta")}
                  className={cx(
                    "btn-elegant inline-flex items-center gap-2 bg-ink px-5 py-2.5 text-sm font-medium tracking-wide text-cream transition-colors duration-300 hover:bg-ink-light",
                    ui.focus,
                  )}
                >
                  <Palette aria-hidden="true" className="h-4 w-4" />
                  Jual Karya
                </button>
              </>
            )}
          </div>

          <button
            id="nav-menu-btn"
            type="button"
            aria-label="Buka menu"
            aria-expanded={sidebarOpen}
            onClick={() => setSidebarOpen(true)}
            className={cx(
              "inline-flex h-10 w-10 items-center justify-center border border-ink/20 text-ink transition-colors hover:border-gold hover:text-gold lg:hidden",
              ui.focus,
            )}
          >
            <Menu aria-hidden="true" className="h-5 w-5" />
          </button>
        </nav>
      </header>

      <div
        aria-hidden="true"
        onClick={() => setSidebarOpen(false)}
        className={cx(
          "fixed inset-0 z-[60] bg-ink/40 backdrop-blur-sm transition-opacity duration-300 lg:hidden",
          sidebarOpen ? "opacity-100" : "pointer-events-none opacity-0",
        )}
      />

      <aside
        id="mobile-sidebar"
        aria-label="Menu navigasi"
        aria-hidden={!sidebarOpen}
        className={cx(
          "fixed right-0 top-0 z-[70] flex h-full w-[300px] flex-col bg-paper shadow-float transition-transform duration-300 ease-in-out lg:hidden",
          sidebarOpen ? "translate-x-0" : "translate-x-full",
        )}
      >
        <div className="flex items-center justify-between border-b border-ink/8 px-6 py-5">
          <span className="font-display text-base font-semibold tracking-tight">{isLoggedIn ? userName : "Menu"}</span>
          <button
            type="button"
            aria-label="Tutup menu"
            onClick={() => setSidebarOpen(false)}
            className={cx(
              "inline-flex h-9 w-9 items-center justify-center border border-ink/15 text-ink transition-colors hover:border-gold hover:text-gold",
              ui.focus,
            )}
          >
            <X aria-hidden="true" className="h-4 w-4" />
          </button>
        </div>

        <nav className="flex-1 overflow-y-auto">
          {isLoggedIn && user ? (
            <>
              <Link
                href="/user"
                onClick={() => setSidebarOpen(false)}
                className={cx("flex w-full items-center justify-between border-b border-ink/8 px-6 py-4 text-left transition-colors hover:bg-cream", ui.focus)}
              >
                <span className="font-display text-base font-semibold text-ink">Dashboard Pembeli</span>
                <ChevronRight aria-hidden="true" className="h-4 w-4 text-ink-muted" />
              </Link>
              <Link
                href="/profile"
                onClick={() => setSidebarOpen(false)}
                className={cx("flex w-full items-center justify-between border-b border-ink/8 px-6 py-4 text-left transition-colors hover:bg-cream", ui.focus)}
              >
                <span className="font-display text-base font-semibold text-ink">Profil Akun</span>
                <ChevronRight aria-hidden="true" className="h-4 w-4 text-ink-muted" />
              </Link>
              <Link
                href={user.seller || user.can_manage_store ? "/seller" : "/seller/onboarding"}
                onClick={() => setSidebarOpen(false)}
                className={cx("flex w-full items-center justify-between border-b border-ink/8 px-6 py-4 text-left transition-colors hover:bg-cream", ui.focus)}
              >
                <span className="font-display text-base font-semibold text-ink">Toko</span>
                <ChevronRight aria-hidden="true" className="h-4 w-4 text-ink-muted" />
              </Link>
              <Link
                href="/user/notifications"
                onClick={() => setSidebarOpen(false)}
                className={cx("flex w-full items-center justify-between border-b border-ink/8 px-6 py-4 text-left transition-colors hover:bg-cream", ui.focus)}
              >
                <span className="font-display text-base font-semibold text-ink">Notifikasi</span>
                <span className="text-xs font-bold text-gold-dark">{unreadNotifications > 0 ? badgeLabel(unreadNotifications) : ""}</span>
              </Link>
              <Link
                href="/user/chats"
                onClick={() => setSidebarOpen(false)}
                className={cx("flex w-full items-center justify-between border-b border-ink/8 px-6 py-4 text-left transition-colors hover:bg-cream", ui.focus)}
              >
                <span className="font-display text-base font-semibold text-ink">Pesan</span>
                <span className="text-xs font-bold text-gold-dark">{unreadMessages > 0 ? badgeLabel(unreadMessages) : ""}</span>
              </Link>
            </>
          ) : null}

          {navItems.map((item) => (
            <button
              key={item.id}
              id={`mobile-${item.id}`}
              type="button"
              onClick={() => scrollToSection(item.targetId)}
              className={cx(
                "flex w-full items-center justify-between border-b border-ink/8 px-6 py-4 text-left transition-colors hover:bg-cream",
                ui.focus,
              )}
            >
              <span className="font-display text-base font-semibold text-ink">{item.label}</span>
              <ChevronRight aria-hidden="true" className="h-4 w-4 text-ink-muted" />
            </button>
          ))}
        </nav>

        <div className="flex flex-col gap-3 border-t border-ink/8 p-6">
          {isLoggedIn && user ? (
            <div className="grid grid-cols-3 gap-2">
              <Link href="/cart" onClick={() => setSidebarOpen(false)} className={cx("grid h-12 place-items-center border border-ink/15 text-ink hover:border-gold hover:text-gold", ui.focus)} aria-label="Keranjang belanja">
                <ShoppingBag aria-hidden="true" className="h-5 w-5" />
              </Link>
              <Link href="/user/notifications" onClick={() => setSidebarOpen(false)} className={cx("grid h-12 place-items-center border border-ink/15 text-ink hover:border-gold hover:text-gold", ui.focus)} aria-label="Notifikasi">
                <Bell aria-hidden="true" className="h-5 w-5" />
              </Link>
              <Link href="/user/chats" onClick={() => setSidebarOpen(false)} className={cx("grid h-12 place-items-center border border-ink/15 text-ink hover:border-gold hover:text-gold", ui.focus)} aria-label="Pesan">
                <Mail aria-hidden="true" className="h-5 w-5" />
              </Link>
            </div>
          ) : (
            <>
              <div className="flex gap-3">
                <Link
                  href="/cart"
                  onClick={() => setSidebarOpen(false)}
                  id="mobile-cart-btn"
                  aria-label="Keranjang belanja"
                  className="relative flex h-12 w-12 flex-shrink-0 items-center justify-center border border-ink/20 text-ink-muted transition-colors duration-200 hover:border-gold hover:text-gold"
                >
                  <ShoppingBag aria-hidden="true" className="h-5 w-5" />
                </Link>
                <Link
                  href="/katalog"
                  onClick={() => setSidebarOpen(false)}
                  id="mobile-buyer-btn"
                  className={cx(
                    "flex flex-1 items-center justify-center gap-2 border border-ink/20 py-3.5 text-sm font-semibold uppercase tracking-widest text-ink transition-colors hover:border-gold hover:text-gold",
                    ui.focus,
                  )}
                >
                  <User aria-hidden="true" className="h-4 w-4" />
                  Pembeli
                </Link>
              </div>
              <button
                id="mobile-seller-btn"
                type="button"
                onClick={() => scrollToSection("cta")}
                className={cx(
                  "btn-elegant flex w-full items-center justify-center gap-2 bg-ink py-3.5 text-sm font-semibold uppercase tracking-widest text-cream transition-colors hover:bg-ink-light",
                  ui.focus,
                )}
              >
                <Palette aria-hidden="true" className="h-4 w-4" />
                Jual Karya
              </button>
            </>
          )}
        </div>
      </aside>
    </>
  );
}
