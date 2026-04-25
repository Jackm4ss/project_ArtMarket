import { PageShell } from "@/Components/Marketplace/PageShell";

type ArticleShowProps = {
    article: {
        title: string;
        excerpt?: string | null;
        body?: string | null;
    };
};

export default function ArticleShow({ article }: ArticleShowProps) {
    return (
        <PageShell title={article.title} eyebrow="Artikel" description={article.excerpt ?? undefined}>
            <article className="prose max-w-none text-ink">
                <p>{article.body ?? "Konten artikel belum tersedia."}</p>
            </article>
        </PageShell>
    );
}
