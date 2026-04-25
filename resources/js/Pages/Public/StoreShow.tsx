import { PageShell } from "@/Components/Marketplace/PageShell";

type StoreShowProps = {
    seller: {
        store_name: string;
        bio?: string | null;
        location?: string | null;
    };
};

export default function StoreShow({ seller }: StoreShowProps) {
    return (
        <PageShell title={seller.store_name} eyebrow="Toko Seniman" description={seller.bio ?? undefined}>
            <p className="text-ink-muted">
                {seller.location ? `Lokasi: ${seller.location}` : "Profil toko dan karya seller akan tampil di sini."}
            </p>
        </PageShell>
    );
}
