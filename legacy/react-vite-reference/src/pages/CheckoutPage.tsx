import { useState } from "react";
import { Link } from "react-router-dom";
import { ArrowLeft, CheckCircle2, Trash2 } from "lucide-react";
import { Container, Section, Button, cx, ui } from "../design-system";
import { useCart } from "../context/CartContext";

export function CheckoutPage() {
  const { items, removeFromCart, clearCart, totalPrice, totalItems } = useCart();
  const [isSuccess, setIsSuccess] = useState(false);

  const handleCheckout = (e: React.FormEvent) => {
    e.preventDefault();
    clearCart();
    setIsSuccess(true);
  };

  if (isSuccess) {
    return (
      <div className="pt-20 min-h-screen flex items-center justify-center">
        <Container className="text-center max-w-lg">
          <div className="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gold/10">
            <CheckCircle2 className="h-10 w-10 text-gold" />
          </div>
          <h1 className="mb-4 font-display text-4xl font-bold text-ink">Pembayaran Berhasil</h1>
          <p className="mb-8 text-ink-muted">
            Terima kasih atas pesanan Anda. Kami telah mengirimkan detail pesanan dan instruksi pengiriman ke email Anda.
          </p>
          <Link to="/katalog" className="inline-block">
            <Button variant="primary">Lanjut Belanja</Button>
          </Link>
        </Container>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="pt-20 min-h-screen flex items-center justify-center">
        <div className="text-center">
          <p className="mb-4 text-xl font-display text-ink-muted">Keranjang Anda kosong</p>
          <Link to="/katalog">
            <Button variant="outline">Eksplorasi Karya</Button>
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="pt-20">
      <Section id="checkout">
        <Container>
          <div className="mb-10">
            <h1 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">Checkout</h1>
          </div>

          <div className="grid grid-cols-1 gap-12 lg:grid-cols-[1.5fr_1fr] lg:gap-16">
            {/* Cart Items */}
            <div>
              <h2 className="mb-6 font-display text-2xl font-bold">Ringkasan Pesanan ({totalItems})</h2>
              <div className="divide-y divide-ink/8 border-y border-ink/8">
                {items.map((item) => (
                  <div key={item.product.id} className="flex gap-6 py-6">
                    <img
                      src={item.product.image.src}
                      alt={item.product.image.alt}
                      className="h-24 w-24 rounded-[var(--radius-frame)] object-cover shadow-soft"
                    />
                    <div className="flex flex-1 flex-col justify-between">
                      <div className="flex justify-between">
                        <div>
                          <h3 className="font-display text-lg font-bold">{item.product.title}</h3>
                          <p className="text-xs uppercase tracking-widest text-ink-muted">{item.product.artist}</p>
                        </div>
                        <p className="font-semibold text-ink">
                          Rp {(item.product.price * item.quantity).toLocaleString("id-ID")}
                        </p>
                      </div>
                      <div className="flex items-center justify-between mt-4">
                        <span className="text-sm text-ink-muted">Kuantitas: {item.quantity}</span>
                        <button
                          onClick={() => removeFromCart(item.product.id)}
                          className="flex items-center gap-1.5 text-xs font-semibold text-ink-muted transition-colors hover:text-gold"
                        >
                          <Trash2 className="h-4 w-4" />
                          Hapus
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
              <Link to="/katalog" className="mt-6 inline-flex items-center gap-2 text-sm uppercase tracking-widest text-ink-muted hover:text-gold">
                <ArrowLeft className="h-4 w-4" />
                Kembali belanja
              </Link>
            </div>

            {/* Checkout Form */}
            <div>
              <div className="rounded-[var(--radius-card)] bg-surface p-6 sm:p-8">
                <h2 className="mb-6 font-display text-2xl font-bold">Detail Pengiriman</h2>
                <form onSubmit={handleCheckout} className="space-y-4">
                  <div>
                    <label htmlFor="name" className="mb-1 block text-xs font-medium uppercase tracking-widest text-ink-muted">Nama Lengkap</label>
                    <input required id="name" type="text" className={cx("w-full border border-ink/20 bg-transparent px-4 py-2.5 text-sm", ui.focus)} />
                  </div>
                  <div>
                    <label htmlFor="email" className="mb-1 block text-xs font-medium uppercase tracking-widest text-ink-muted">Email</label>
                    <input required id="email" type="email" className={cx("w-full border border-ink/20 bg-transparent px-4 py-2.5 text-sm", ui.focus)} />
                  </div>
                  <div>
                    <label htmlFor="address" className="mb-1 block text-xs font-medium uppercase tracking-widest text-ink-muted">Alamat Pengiriman</label>
                    <textarea required id="address" rows={3} className={cx("w-full border border-ink/20 bg-transparent px-4 py-2.5 text-sm", ui.focus)}></textarea>
                  </div>

                  <div className="my-6 border-y border-ink/10 py-4">
                    <div className="flex justify-between mb-2">
                      <span className="text-sm text-ink-muted">Subtotal</span>
                      <span className="text-sm font-semibold">Rp {totalPrice.toLocaleString("id-ID")}</span>
                    </div>
                    <div className="flex justify-between mb-2">
                      <span className="text-sm text-ink-muted">Pengiriman (Asuransi)</span>
                      <span className="text-sm font-semibold">Gratis</span>
                    </div>
                    <div className="mt-4 flex justify-between">
                      <span className="font-display text-xl font-bold">Total</span>
                      <span className="font-display text-xl font-bold text-gold">Rp {totalPrice.toLocaleString("id-ID")}</span>
                    </div>
                  </div>

                  <Button type="submit" variant="primary" className="w-full">
                    Bayar Sekarang
                  </Button>
                </form>
              </div>
            </div>
          </div>
        </Container>
      </Section>
    </div>
  );
}
