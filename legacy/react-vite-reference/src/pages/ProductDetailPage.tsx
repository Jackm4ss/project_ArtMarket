import { useParams, Link } from "react-router-dom";
import { ArrowLeft, ShoppingBag, ShieldCheck, Truck } from "lucide-react";
import { Container, Section, Button, MediaFrame, cx, ui } from "../design-system";
import { products } from "../data/products";
import { useCart } from "../context/CartContext";

export function ProductDetailPage() {
  const { id } = useParams();
  const { addToCart } = useCart();
  
  const product = products.find((p) => p.id === id);

  if (!product) {
    return (
      <div className="pt-20 min-h-screen flex items-center justify-center">
        <div className="text-center">
          <p className="mb-4 text-xl font-display text-ink-muted">Produk tidak ditemukan</p>
          <Link to="/katalog" className="text-gold hover:underline">Kembali ke Katalog</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="pt-20">
      <Section id="product-detail">
        <Container>
          <div className="mb-8">
            <Link to="/katalog" className="inline-flex items-center gap-2 text-sm uppercase tracking-widest text-ink-muted transition-colors hover:text-gold">
              <ArrowLeft className="h-4 w-4" />
              Kembali ke Katalog
            </Link>
          </div>

          <div className="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:gap-20">
            {/* Image */}
            <div className="sticky top-28">
              <MediaFrame
                src={product.image.src}
                alt={product.image.alt}
                width={product.image.width}
                height={product.image.height}
                className="rounded-[var(--radius-frame)] shadow-soft"
              />
            </div>

            {/* Info */}
            <div className="flex flex-col">
              <div className="mb-4 inline-flex items-center gap-2">
                <span className="bg-ink/5 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.15em] text-ink">
                  {product.category}
                </span>
              </div>
              <h1 className="font-display text-4xl font-bold leading-tight lg:text-5xl">
                {product.title}
              </h1>
              <p className="mt-2 text-sm font-medium uppercase tracking-[0.15em] text-ink-muted">
                Oleh <span className="text-ink">{product.artist}</span>
              </p>

              <div className="my-8 border-y border-ink/8 py-6">
                <p className="font-display text-3xl font-semibold text-ink">
                  Rp {product.price.toLocaleString("id-ID")}
                </p>
              </div>

              <div className="mb-10 text-sm leading-relaxed text-ink-muted">
                {product.description}
              </div>

              {/* Action */}
              <Button
                variant="primary"
                icon={ShoppingBag}
                onClick={() => addToCart(product)}
                className="w-full sm:w-auto"
              >
                Tambah ke Keranjang
              </Button>

              {/* Trust badges */}
              <div className="mt-12 space-y-4 rounded-[var(--radius-card)] bg-surface p-6">
                <div className="flex items-center gap-3">
                  <ShieldCheck className="h-5 w-5 text-gold" />
                  <span className="text-sm font-medium text-ink">Jaminan Keaslian 100%</span>
                </div>
                <div className="flex items-center gap-3">
                  <Truck className="h-5 w-5 text-gold" />
                  <span className="text-sm font-medium text-ink">Pengiriman Aman berasuransi</span>
                </div>
              </div>
            </div>
          </div>
        </Container>
      </Section>
    </div>
  );
}
