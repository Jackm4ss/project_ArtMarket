import type { ReactNode } from "react";
import { ui } from "../tokens";
import { cx } from "../utils";

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
    <section id={id} className={cx("scroll-mt-28", compact ? ui.sectionYCompact : ui.sectionY, className)}>
      {children}
    </section>
  );
}
