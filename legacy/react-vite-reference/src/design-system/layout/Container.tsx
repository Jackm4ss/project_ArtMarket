import type { HTMLAttributes, ReactNode } from "react";
import { ui } from "../tokens";
import { cx } from "../utils";

type ContainerProps = HTMLAttributes<HTMLDivElement> & {
  children: ReactNode;
};

export function Container({ children, className, ...props }: ContainerProps) {
  return (
    <div {...props} className={cx(ui.container, className)}>
      {children}
    </div>
  );
}
