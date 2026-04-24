import type { ReactNode } from "react";
import { cx } from "../utils";

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
