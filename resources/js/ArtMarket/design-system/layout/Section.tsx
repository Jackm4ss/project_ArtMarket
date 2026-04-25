import type { ReactNode } from "react";
import { ui } from "../tokens";
import { cx } from "../utils";

type SectionSpacing = "default" | "compact" | "loose";

export function Section({
  id,
  children,
  className,
  compact = false,
  spacing,
}: {
  id?: string;
  children: ReactNode;
  className?: string;
  compact?: boolean;
  spacing?: SectionSpacing;
}) {
  const resolvedSpacing: SectionSpacing = compact ? "compact" : spacing ?? "default";
  const spacingClass =
    resolvedSpacing === "loose"
      ? ui.sectionYLoose
      : resolvedSpacing === "compact"
        ? ui.sectionYCompact
        : ui.sectionY;

  return (
    <section id={id} className={cx("scroll-mt-28", spacingClass, className)}>
      {children}
    </section>
  );
}
