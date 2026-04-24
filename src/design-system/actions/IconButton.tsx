import type { AnchorHTMLAttributes } from "react";
import type { LucideIcon } from "lucide-react";
import { ui } from "../tokens";
import { cx } from "../utils";

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
        dark ? ui.focusDark : ui.focus,
        className,
      )}
    >
      <Icon aria-hidden="true" className="h-4 w-4" />
    </a>
  );
}
