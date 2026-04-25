import { Head, Link } from "@inertiajs/react";
import { PropsWithChildren } from "react";

type PageShellProps = PropsWithChildren<{
    title: string;
    eyebrow?: string;
    description?: string;
}>;

export function PageShell({ title, eyebrow = "Art Market", description, children }: PageShellProps) {
    return (
        <>
            <Head title={title} />
            <div className="min-h-screen bg-cream font-body text-ink">
                <header className="border-b border-cream-deeper bg-cream/90 px-6 py-5">
                    <nav className="mx-auto flex max-w-6xl items-center justify-between">
                        <Link href="/" className="flex items-center gap-3 font-display text-xl font-semibold">
                            <img src="/logo-artmarket.png" alt="" className="h-9 w-auto" />
                            Art Market
                        </Link>
                        <div className="flex gap-4 text-sm font-semibold text-ink-muted">
                            <Link href="/katalog" className="hover:text-ink">Katalog</Link>
                            <Link href="/cart" className="hover:text-ink">Keranjang</Link>
                            <Link href="/login" className="hover:text-ink">Login</Link>
                        </div>
                    </nav>
                </header>
                <main className="mx-auto max-w-6xl px-6 py-12">
                    <p className="text-sm font-bold uppercase tracking-[0.3em] text-gold-dark">{eyebrow}</p>
                    <h1 className="mt-4 font-display text-4xl font-semibold md:text-6xl">{title}</h1>
                    {description ? <p className="mt-4 max-w-3xl text-lg text-ink-muted">{description}</p> : null}
                    <section className="mt-10 rounded-ds-card bg-paper p-6 shadow-soft md:p-8">
                        {children}
                    </section>
                </main>
            </div>
        </>
    );
}
