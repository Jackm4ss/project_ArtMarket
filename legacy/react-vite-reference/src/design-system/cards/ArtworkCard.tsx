import { Heart } from "lucide-react";
import { ui } from "../tokens";
import { cx } from "../utils";
import type { CardImage } from "./types";

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
            <a href="#" className={cx("text-sm font-medium uppercase tracking-widest text-gold transition-colors hover:text-gold-dark", ui.focus)}>
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
              ui.focus,
            )}
          >
            <Heart aria-hidden="true" className="h-4 w-4 group-hover:text-ink" />
          </button>
        </div>
      </div>
    </article>
  );
}
