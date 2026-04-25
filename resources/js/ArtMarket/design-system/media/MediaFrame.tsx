import type { ImgHTMLAttributes } from "react";
import { cx } from "../utils";

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
