import { ArrowRight } from "lucide-react";
import { ui } from "../tokens";
import { cx } from "../utils";
import type { CardImage } from "./types";

export function GenreCard({
  title,
  count,
  href,
  image,
  grayscale = false,
}: {
  title: string;
  count: string;
  href: string;
  image: CardImage;
  grayscale?: boolean;
}) {
  return (
    <a href={href} className={cx("art-card group relative aspect-[3/4] overflow-hidden rounded-ds-card bg-cream-dark shadow-soft", ui.focus)}>
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
