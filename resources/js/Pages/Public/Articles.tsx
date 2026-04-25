import { PageShell } from "@/Components/Marketplace/PageShell";

export default function Articles() {
    return (
        <PageShell
            title="Artikel"
            eyebrow="Editorial"
            description="Artikel production akan mendukung SEO slug, tag, publish schedule, dan sitemap."
        >
            <p className="text-ink-muted">Belum ada artikel published.</p>
        </PageShell>
    );
}
