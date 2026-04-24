import { ArrowUpRight, Plus } from "lucide-react";
import { useState } from "react";
import { Container, Eyebrow, Section, cx, ui } from "../design-system";

// ─── Copy ─────────────────────────────────────────────────────────────────────

const faqCopy = {
  eyebrow: "FAQ",
  title: "Punya lebih banyak pertanyaan?",
  subtitle:
    "Art Market hadir untuk menjembatani seniman dan kolektor Indonesia dengan cara yang mudah dan aman.",
  cantFindTitle: "Tidak menemukan jawabannya?",
  cantFindDesc:
    "Tim kami siap membantu kapan saja. Hubungi kami dan kami akan merespons dalam 1×24 jam.",
  ctaLabel: "Hubungi Kami",
} as const;

// ─── Data ─────────────────────────────────────────────────────────────────────

const faqs = [
  {
    id: "faq-1",
    question: "Apa itu Art Market dan siapa yang bisa menggunakannya?",
    answer:
      "Art Market adalah platform digital yang menghubungkan seniman, galeri, dan kreator seni dengan kolektor serta pencinta seni di seluruh Indonesia. Platform ini terbuka untuk semua — baik seniman yang ingin menjual karya maupun kolektor yang ingin menemukan karya autentik.",
  },
  {
    id: "faq-2",
    question: "Bagaimana cara mendaftarkan diri sebagai seniman?",
    answer:
      "Klik tombol \"Jual Karya\" di header, lengkapi profil dan unggah portofolio minimal 3 karya, lalu tunggu verifikasi identitas kami dalam 1–2 hari kerja. Setelah terverifikasi, Anda bisa listing karya tanpa batas.",
  },
  {
    id: "faq-3",
    question: "Berapa komisi yang diambil Art Market?",
    answer:
      "Art Market mengambil komisi sebesar 10% dari harga jual untuk setiap transaksi yang berhasil. Tidak ada biaya listing, biaya bulanan, atau biaya tersembunyi lainnya.",
  },
  {
    id: "faq-4",
    question: "Bagaimana Art Market menjamin keaslian karya?",
    answer:
      "Setiap seniman melewati proses verifikasi identitas dan portofolio. Setiap karya dilengkapi Sertifikat Keaslian digital. Jika karya terbukti tidak autentik, kami memberikan jaminan pengembalian dana penuh.",
  },
  {
    id: "faq-5",
    question: "Metode pembayaran apa saja yang tersedia?",
    answer:
      "Transfer bank, kartu kredit/debit Visa & Mastercard, dompet digital (GoPay, OVO, DANA, ShopeePay), dan cicilan 0% melalui kartu kredit partner kami. Semua pembayaran diproses melalui sistem escrow yang aman.",
  },
] as const;

// ─── Accordion Item ───────────────────────────────────────────────────────────

function AccordionItem({
  item,
  isOpen,
  onToggle,
}: {
  item: (typeof faqs)[number];
  isOpen: boolean;
  onToggle: () => void;
}) {
  return (
    <div
      className={cx(
        "overflow-hidden rounded-[var(--radius-badge)] border border-ink/8 bg-ink/[0.04] transition-colors duration-200",
        isOpen && "border-ink/12 bg-ink/[0.06]",
      )}
    >
      <button
        id={item.id}
        type="button"
        aria-expanded={isOpen}
        onClick={onToggle}
        className={cx(
          "flex w-full items-center justify-between gap-4 px-5 py-4 text-left",
          ui.focus,
        )}
      >
        <span className="font-display text-sm font-semibold text-ink">{item.question}</span>

        {/* Plus / × toggle */}
        <Plus
          aria-hidden="true"
          className={cx(
            "h-4 w-4 flex-shrink-0 text-ink-muted transition-transform duration-300",
            isOpen && "rotate-45 text-gold",
          )}
        />
      </button>

      {/* Answer */}
      <div
        className={cx(
          "overflow-hidden transition-all duration-300 ease-in-out",
          isOpen ? "max-h-60 opacity-100" : "max-h-0 opacity-0",
        )}
        aria-hidden={!isOpen}
      >
        <p className="border-t border-ink/8 px-5 py-4 text-sm leading-relaxed text-ink-muted">
          {item.answer}
        </p>
      </div>
    </div>
  );
}

// ─── Section ──────────────────────────────────────────────────────────────────

export function FaqSection() {
  const [openId, setOpenId] = useState<string | null>(null);

  const toggle = (id: string) => setOpenId((prev) => (prev === id ? null : id));

  return (
    <Section id="faq" className="border-b border-ink/5">
      <Container data-nav-anchor>
        <div className="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:gap-20">

          {/* ── Left: header + cant-find card ── */}
          <div className="flex flex-col">
            <Eyebrow className="mb-4">{faqCopy.eyebrow}</Eyebrow>
            <h2 className="font-display text-4xl font-bold leading-tight tracking-tight lg:text-5xl">
              {faqCopy.title}
            </h2>
            <p className="mt-4 max-w-sm text-sm leading-relaxed text-ink-muted">
              {faqCopy.subtitle}
            </p>

            {/* Can't find card */}
            <div className="mt-auto pt-14">
              <div className="rounded-[var(--radius-card)] border border-ink/8 bg-paper p-7 shadow-soft">
                <p className="font-display text-lg font-bold text-ink">{faqCopy.cantFindTitle}</p>
                <p className="mt-2 text-sm leading-relaxed text-ink-muted">{faqCopy.cantFindDesc}</p>
                <a
                  id="faq-contact-link"
                  href="#footer"
                  className={cx(
                    "mt-5 inline-flex items-center gap-2 bg-ink px-5 py-2.5 text-xs font-semibold uppercase tracking-widest text-cream transition-opacity duration-200 hover:opacity-80",
                    ui.focus,
                  )}
                >
                  {faqCopy.ctaLabel}
                  <ArrowUpRight aria-hidden="true" className="h-3.5 w-3.5" />
                </a>
              </div>
            </div>
          </div>

          {/* ── Right: accordion list ── */}
          <div className="flex flex-col gap-3">
            {faqs.map((item) => (
              <AccordionItem
                key={item.id}
                item={item}
                isOpen={openId === item.id}
                onToggle={() => toggle(item.id)}
              />
            ))}
          </div>

        </div>
      </Container>
    </Section>
  );
}
