import type { LucideIcon } from "lucide-react";

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
