import type { AnchorHTMLAttributes, ButtonHTMLAttributes, ReactNode } from "react";
import type { LucideIcon } from "lucide-react";
import { ui } from "../tokens";
import { cx } from "../utils";

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
      <a {...(rest as AnchorHTMLAttributes<HTMLAnchorElement>)} className={cx(base, variants[variant], "group", ui.focus, className)}>
        {content}
      </a>
    );
  }

  return (
    <button
      {...(rest as ButtonHTMLAttributes<HTMLButtonElement>)}
      type={(rest as ButtonHTMLAttributes<HTMLButtonElement>).type ?? "button"}
      className={cx(base, variants[variant], "group", ui.focus, className)}
    >
      {content}
    </button>
  );
}
