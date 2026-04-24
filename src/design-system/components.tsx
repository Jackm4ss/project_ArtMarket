import type { AnchorHTMLAttributes, ButtonHTMLAttributes, ImgHTMLAttributes, ReactNode } from "react";
import type { LucideIcon } from "lucide-react";
import { ArrowRight, Heart } from "lucide-react";
import { ds } from "./tokens";

export function cx(...classes: Array<string | false | null | undefined>) {
  return classes.filter(Boolean).join(" ");
}

export function Container({ children, className }: { children: ReactNode; className?: string }) {
  return <div className={cx(ds.container, className)}>{children}</div>;
}

export function Section({
  id,
  children,
  className,
  compact = false,
}: {
  id?: string;
  children: ReactNode;
  className?: string;
  compact?: boolean;
}) {
  return (
    <section id={id} className={cx("scroll-mt-28", compact ? ds.sectionYCompact : ds.sectionY, className)}>
      {children}
    </section>
  );
}

export function Eyebrow({
  children,
  centered = false,
  dark = false,
  className,
}: {
  children: ReactNode;
  centered?: boolean;
  dark?: boolean;
  className?: string;
}) {
  return (
    <span
      className={cx(
        "inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-gold",
        centered && "justify-center",
        dark && "text-gold",
        className,
      )}
    >
      <span className="h-px w-8 bg-gold" />
      {children}
      {centered ? <span className="h-px w-8 bg-gold" /> : null}
    </span>
  );
}

type ButtonBaseProps = {
  children: ReactNode;
  icon?: LucideIcon;
  variant?: "primary" | "outline" | "gold-outline";
  className?: string;
};

type ButtonAsAnchor = ButtonBaseProps & AnchorHTMLAttributes<HTMLAnchorElement> & { href: string };
type ButtonAsButton = ButtonBaseProps & ButtonHTMLAttributes<HTMLButtonElement> & { href?: undefined };

export function Button(props: ButtonAsAnchor | ButtonAsButton) {
  const { children, icon: Icon, variant = "primary", className, ...rest } = props;
  const base =
    "btn-elegant inline-flex items-center justify-center gap-3 px-8 py-4 text-sm font-semibold uppercase tracking-widest transition-colors duration-300";
  const variants = {
    primary: "bg-ink text-cream hover:bg-ink-light",
    outline: "border border-ink/20 text-ink hover:border-gold hover:text-gold",
    "gold-outline": "border-2 border-gold text-gold-dark hover:bg-gold hover:text-ink",
  };
  const content = (
    <>
      {children}
      {Icon ? <Icon aria-hidden="true" className="h-4 w-4 transition-transform group-hover:translate-x-1" /> : null}
    </>
  );

  if ("href" in props && props.href) {
    return (
      <a {...(rest as AnchorHTMLAttributes<HTMLAnchorElement>)} className={cx(base, variants[variant], "group", ds.focus, className)}>
        {content}
      </a>
    );
  }

  return (
    <button
      {...(rest as ButtonHTMLAttributes<HTMLButtonElement>)}
      type={(rest as ButtonHTMLAttributes<HTMLButtonElement>).type ?? "button"}
      className={cx(base, variants[variant], "group", ds.focus, className)}
    >
      {content}
    </button>
  );
}

export function IconButton({
  label,
  icon: Icon,
  className,
  dark = false,
  ...props
}: AnchorHTMLAttributes<HTMLAnchorElement> & { label: string; icon: LucideIcon; dark?: boolean }) {
  return (
    <a
      {...props}
      aria-label={label}
      className={cx(
        "inline-flex h-9 w-9 items-center justify-center border transition-colors duration-300",
        dark ? "border-cream/10 text-cream/40 hover:border-gold hover:text-gold" : "border-ink/15 text-ink hover:border-gold hover:text-gold",
        dark ? ds.focusDark : ds.focus,
        className,
      )}
    >
      <Icon aria-hidden="true" className="h-4 w-4" />
    </a>
  );
}

export function MediaFrame({
  alt,
  className,
  imageClassName,
  loading = "lazy",
  width,
  height,
  ...props
}: ImgHTMLAttributes<HTMLImageElement> & {
  alt: string;
  className?: string;
  imageClassName?: string;
  width: number;
  height: number;
}) {
  return (
    <div className={cx("overflow-hidden bg-cream-dark", className)}>
      <img {...props} alt={alt} width={width} height={height} loading={loading} className={cx("h-full w-full object-cover", imageClassName)} />
    </div>
  );
}

type CardImage = {
  src: string;
  alt: string;
  width: number;
  height: number;
};

export function GenreCard({
  title,
  count,
  image,
  grayscale = false,
}: {
  title: string;
  count: string;
  image: CardImage;
  grayscale?: boolean;
}) {
  return (
    <a href="#" className={cx("art-card group relative aspect-[3/4] overflow-hidden rounded-ds-card bg-cream-dark shadow-soft", ds.focus)}>
      <img
        src={image.src}
        alt={image.alt}
        width={image.width}
        height={image.height}
        loading="lazy"
        className={cx("art-img h-full w-full object-cover transition-transform duration-500", grayscale && "grayscale")}
      />
      <div className="absolute inset-0 bg-gradient-to-t from-ink/75 via-ink/15 to-transparent" />
      <span className="absolute right-4 top-4 h-2 w-2 rounded-full bg-gold" />
      <div className="absolute bottom-6 left-5 right-5 text-cream">
        <h3 className="font-heading text-2xl font-semibold leading-none">{title}</h3>
        <p className="mt-2 text-sm text-cream/85">{count}</p>
        <span className="mt-5 inline-flex translate-y-2 items-center gap-2 text-xs font-semibold uppercase tracking-widest text-gold opacity-0 transition-[opacity,transform] duration-300 group-hover:translate-y-0 group-hover:opacity-100">
          Lihat koleksi
          <ArrowRight aria-hidden="true" className="h-3.5 w-3.5" />
        </span>
      </div>
    </a>
  );
}

export function ArtworkCard({
  category,
  artist,
  title,
  price,
  inquiry = false,
  image,
}: {
  category: string;
  artist: string;
  title: string;
  price?: string;
  inquiry?: boolean;
  image: CardImage;
}) {
  return (
    <article className="art-card group cursor-pointer">
      <div className="relative aspect-[3/4] overflow-hidden bg-cream-dark">
        <img
          src={image.src}
          alt={image.alt}
          width={image.width}
          height={image.height}
          loading="lazy"
          className="art-img h-full w-full object-cover transition-transform duration-700"
        />
        <div className="art-overlay absolute inset-0 flex items-center justify-center bg-ink/20 opacity-0 transition-opacity duration-500">
          <span className="bg-cream px-6 py-3 text-xs font-semibold uppercase tracking-widest text-ink">Lihat Detail</span>
        </div>
        <div className="absolute left-4 top-4">
          <span className="art-tag bg-cream/90 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.15em] text-ink transition-colors duration-300">
            {category}
          </span>
        </div>
      </div>
      <div className="pt-5">
        <div className="text-xs font-medium uppercase tracking-[0.15em] text-ink-muted">{artist}</div>
        <h3 className="mt-1 font-heading text-lg font-medium tracking-tight transition-colors group-hover:text-gold">{title}</h3>
        <div className="mt-3 flex items-center justify-between">
          {inquiry ? (
            <a href="#" className={cx("text-sm font-medium uppercase tracking-widest text-gold transition-colors hover:text-gold-dark", ds.focus)}>
              Inquiry {"\u2192"}
            </a>
          ) : (
            <span className="font-display text-base font-semibold">{price}</span>
          )}
          <button
            type="button"
            aria-label={`Simpan ${title}`}
            className={cx(
              "flex h-8 w-8 items-center justify-center border border-ink/15 transition-colors duration-300 group-hover:border-gold group-hover:bg-gold",
              ds.focus,
            )}
          >
            <Heart aria-hidden="true" className="h-4 w-4 group-hover:text-ink" />
          </button>
        </div>
      </div>
    </article>
  );
}

export function FeatureItem({ icon: Icon, title, description }: { icon: LucideIcon; title: string; description: string }) {
  return (
    <div className="feature-item flex items-start gap-4">
      <div className="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center border border-gold/30">
        <Icon aria-hidden="true" className="h-4 w-4 text-gold" />
      </div>
      <div className="min-w-0">
        <div className="text-sm font-medium tracking-tight">{title}</div>
        <div className="mt-0.5 text-xs leading-relaxed text-cream/40">{description}</div>
      </div>
    </div>
  );
}
