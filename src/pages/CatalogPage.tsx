import { useState, useMemo } from "react";
import { Link } from "react-router-dom";
import { Search, Minus, Plus, ListFilter, ChevronDown, X } from "lucide-react";
import { Container, Eyebrow, Section, ArtworkCard, cx, ui } from "../design-system";
import { products } from "../data/products";

const categories = ["Semua", "Lukisan", "Patung", "Seni Digital", "Fotografi", "Kerajinan seni"];

const priceRanges = [
  { id: "under-2m", label: "Di bawah Rp 2.000.000", max: 2000000 },
  { id: "2m-5m", label: "Rp 2.000.000 - Rp 5.000.000", min: 2000000, max: 5000000 },
  { id: "5m-10m", label: "Rp 5.000.000 - Rp 10.000.000", min: 5000000, max: 10000000 },
  { id: "over-10m", label: "Di atas Rp 10.000.000", min: 10000000 },
];

const sortOptions = ["Ulasan", "Terbaru", "Harga Tertinggi", "Harga Terendah"];

export function CatalogPage() {
  const [search, setSearch] = useState("");
  const [activeCategory, setActiveCategory] = useState("Semua");
  const [catExpanded, setCatExpanded] = useState(true);
  const [priceExpanded, setPriceExpanded] = useState(true);
  const [mobileFilterOpen, setMobileFilterOpen] = useState(false);
  const [mobileSortOpen, setMobileSortOpen] = useState(false);
  const [activeSort, setActiveSort] = useState("");
  
  // Minimal state for price filters UI
  const [selectedPrices, setSelectedPrices] = useState<string[]>([]);
  const [minPrice, setMinPrice] = useState("");
  const [maxPrice, setMaxPrice] = useState("");

  const filteredProducts = useMemo(() => {
    return products.filter((product) => {
      // Search
      const matchSearch = product.title.toLowerCase().includes(search.toLowerCase()) || 
                          product.artist.toLowerCase().includes(search.toLowerCase());
      // Category
      const matchCategory = activeCategory === "Semua" || product.category === activeCategory;
      
      // Price
      let matchPrice = true;
      if (selectedPrices.length > 0 || (minPrice && maxPrice)) {
        // Simplified price logic for demo: if any selected range matches OR custom range matches
        const price = product.price;
        let inPresetRange = false;
        
        if (selectedPrices.length > 0) {
          inPresetRange = selectedPrices.some(rangeId => {
            const range = priceRanges.find(r => r.id === rangeId);
            if (!range) return false;
            if (range.min && range.max) return price >= range.min && price <= range.max;
            if (range.min) return price >= range.min;
            if (range.max) return price <= range.max;
            return false;
          });
        }
        
        let inCustomRange = false;
        if (minPrice && maxPrice) {
          inCustomRange = price >= parseInt(minPrice) && price <= parseInt(maxPrice);
        }
        
        matchPrice = inPresetRange || inCustomRange;
        // If they selected presets but product doesn't match, and didn't input custom (or didn't match custom)
        if (selectedPrices.length > 0 && minPrice && maxPrice) {
           matchPrice = inPresetRange || inCustomRange;
        } else if (selectedPrices.length > 0) {
           matchPrice = inPresetRange;
        } else if (minPrice && maxPrice) {
           matchPrice = inCustomRange;
        }
      }

      return matchSearch && matchCategory && matchPrice;
    });
  }, [search, activeCategory, selectedPrices, minPrice, maxPrice]);

  const togglePriceRange = (id: string) => {
    setSelectedPrices(prev => 
      prev.includes(id) ? prev.filter(p => p !== id) : [...prev, id]
    );
  };

  return (
    <div className="pt-20">
      <Section id="catalog" className="min-h-screen">
        <Container>
          <div className="mb-14">
            <Eyebrow className="mb-4">Katalog</Eyebrow>
            <h1 className="font-display text-4xl font-bold tracking-tight lg:text-5xl">
              Eksplorasi Karya Seni
            </h1>
            <p className="mt-4 max-w-lg text-sm leading-relaxed text-ink-muted">
              Temukan ribuan karya seni autentik dari seniman berbakat di seluruh Indonesia.
            </p>
          </div>

          <div className="flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-16">
            
            {/* ─── SIDEBAR FILTER (Desktop & Mobile Drawer) ─── */}
            
            {/* Mobile Filter Backdrop */}
            <div 
              aria-hidden="true"
              onClick={() => setMobileFilterOpen(false)}
              className={cx(
                "fixed inset-0 z-[100] bg-ink/40 backdrop-blur-sm transition-opacity duration-300 lg:hidden",
                mobileFilterOpen ? "opacity-100" : "pointer-events-none opacity-0"
              )}
            />

            {/* Mobile Sort Backdrop */}
            <div 
              aria-hidden="true"
              onClick={() => setMobileSortOpen(false)}
              className={cx(
                "fixed inset-0 z-[100] bg-ink/40 backdrop-blur-sm transition-opacity duration-300 lg:hidden",
                mobileSortOpen ? "opacity-100" : "pointer-events-none opacity-0"
              )}
            />

            {/* Mobile Sort Bottom Sheet */}
            <aside 
              className={cx(
                "fixed bottom-0 left-0 z-[110] flex max-h-[85vh] w-full flex-col overflow-y-auto rounded-t-3xl bg-paper px-6 pb-8 pt-6 shadow-[0_-10px_40px_rgba(26,26,26,0.1)] transition-transform duration-300 ease-out lg:hidden",
                mobileSortOpen ? "translate-y-0" : "translate-y-full"
              )}
            >
              <div className="mb-4 flex items-center justify-between border-b border-ink/10 pb-4">
                <div className="flex items-center gap-3">
                  <button onClick={() => setMobileSortOpen(false)} className={cx("text-ink transition-colors hover:text-gold", ui.focus)}>
                    <X className="h-5 w-5" />
                  </button>
                  <span className="font-display text-lg font-bold text-ink">Urutkan</span>
                </div>
                <button 
                  onClick={() => setActiveSort("")}
                  className={cx("text-sm font-bold text-gold transition-colors hover:text-gold-dark", ui.focus)}
                >
                  Reset
                </button>
              </div>

              <div className="flex flex-col">
                {sortOptions.map((option) => (
                  <label key={option} className="group flex cursor-pointer items-center justify-between border-b border-ink/5 py-4 last:border-0">
                    <span className={cx(
                      "text-sm transition-colors",
                      activeSort === option ? "font-bold text-ink" : "font-medium text-ink-muted group-hover:text-ink"
                    )}>
                      {option}
                    </span>
                    <div className="relative flex h-5 w-5 items-center justify-center">
                      <input 
                        type="radio" 
                        name="sort"
                        checked={activeSort === option}
                        onChange={() => {
                          setActiveSort(option);
                          setTimeout(() => setMobileSortOpen(false), 250);
                        }}
                        className="peer sr-only"
                      />
                      <div className={cx(
                        "h-5 w-5 rounded-full border transition-all duration-200 group-hover:border-ink",
                        activeSort === option ? "border-[6px] border-gold" : "border-ink/20"
                      )} />
                    </div>
                  </label>
                ))}
              </div>
            </aside>

            {/* Filter Sidebar */}
            <aside 
              className={cx(
                "fixed left-0 top-0 z-[110] flex h-full w-[300px] flex-col overflow-y-auto bg-paper p-6 shadow-float transition-transform duration-300 ease-in-out lg:static lg:z-auto lg:h-auto lg:w-64 lg:flex-shrink-0 lg:translate-x-0 lg:overflow-y-visible lg:bg-transparent lg:p-0 lg:shadow-none",
                mobileFilterOpen ? "translate-x-0" : "-translate-x-full"
              )}
            >
              {/* Mobile Sidebar Header */}
              <div className="mb-6 flex items-center justify-between border-b border-ink/10 pb-4 lg:hidden">
                <span className="font-display text-xl font-bold">Filter</span>
                <button onClick={() => setMobileFilterOpen(false)} className={cx("text-ink transition-colors hover:text-gold", ui.focus)}>
                  <X className="h-5 w-5" />
                </button>
              </div>

              <div className="flex flex-col gap-8">
                {/* Category Filter */}
              <div className="border-b border-ink/10 pb-8">
                <button 
                  onClick={() => setCatExpanded(!catExpanded)}
                  className={cx("flex w-full items-center justify-between text-left", ui.focus)}
                >
                  <div>
                    <h3 className="font-display text-lg font-bold text-ink">Kategori</h3>
                    <p className="mt-1 text-xs text-ink-muted">Pilih satu medium.</p>
                  </div>
                  {catExpanded ? <Minus className="h-4 w-4 text-ink" /> : <Plus className="h-4 w-4 text-ink" />}
                </button>
                
                {catExpanded && (
                  <div className="mt-6 flex flex-col gap-4">
                    {categories.map((cat) => (
                      <label key={cat} className="flex cursor-pointer items-center gap-3 group">
                        <div className="relative flex h-5 w-5 items-center justify-center">
                          <input 
                            type="radio" 
                            name="category"
                            checked={activeCategory === cat}
                            onChange={() => setActiveCategory(cat)}
                            className="peer sr-only"
                          />
                          <div className="h-4 w-4 rounded-full border border-ink/40 transition-colors group-hover:border-ink peer-checked:border-[5px] peer-checked:border-gold" />
                        </div>
                        <span className={cx(
                          "text-sm font-medium transition-colors",
                          activeCategory === cat ? "text-ink font-bold" : "text-ink-muted group-hover:text-ink"
                        )}>
                          {cat}
                        </span>
                      </label>
                    ))}
                  </div>
                )}
              </div>

              {/* Price Filter */}
              <div className="border-b border-ink/10 pb-8">
                <button 
                  onClick={() => setPriceExpanded(!priceExpanded)}
                  className={cx("flex w-full items-center justify-between text-left", ui.focus)}
                >
                  <h3 className="font-display text-lg font-bold text-ink">Harga</h3>
                  {priceExpanded ? <Minus className="h-4 w-4 text-ink" /> : <Plus className="h-4 w-4 text-ink" />}
                </button>
                
                {priceExpanded && (
                  <div className="mt-6 flex flex-col gap-5">
                    {/* Checkboxes */}
                    <div className="flex flex-col gap-4">
                      {priceRanges.map((range) => (
                        <label key={range.id} className="flex cursor-pointer items-center gap-3 group">
                          <div className="relative flex items-center justify-center">
                            <input 
                              type="checkbox" 
                              checked={selectedPrices.includes(range.id)}
                              onChange={() => togglePriceRange(range.id)}
                              className="peer sr-only"
                            />
                            <div className="h-4 w-4 rounded-[var(--radius-base)] border border-ink/40 transition-colors group-hover:border-ink peer-checked:bg-gold peer-checked:border-gold" />
                            {/* Checkmark icon for checkbox */}
                            <svg className="pointer-events-none absolute h-3 w-3 text-white opacity-0 peer-checked:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                              <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                          </div>
                          <span className="text-sm text-ink-muted transition-colors group-hover:text-ink">
                            {range.label}
                          </span>
                        </label>
                      ))}
                    </div>

                    {/* Custom Range */}
                    <div className="mt-2">
                      <p className="mb-3 text-sm text-ink-muted">Atau masukkan rentang harga</p>
                      <div className="flex items-center gap-2">
                        <input 
                          type="number" 
                          placeholder="Min" 
                          value={minPrice}
                          onChange={(e) => setMinPrice(e.target.value)}
                          className={cx("w-full rounded-[var(--radius-base)] border border-ink/20 px-3 py-2 text-sm text-ink placeholder:text-ink/40", ui.focus)}
                        />
                        <span className="text-ink-muted">-</span>
                        <input 
                          type="number" 
                          placeholder="Max" 
                          value={maxPrice}
                          onChange={(e) => setMaxPrice(e.target.value)}
                          className={cx("w-full rounded-[var(--radius-base)] border border-ink/20 px-3 py-2 text-sm text-ink placeholder:text-ink/40", ui.focus)}
                        />
                      </div>
                    </div>
                  </div>
                )}
              </div>
              </div>
            </aside>

            {/* ─── MAIN CONTENT ─── */}
            <div className="flex-1">
              {/* Mobile Filter Bar & Search */}
              <div className="mb-8 flex flex-col gap-6">
                
                {/* Filter & Sort Bar (Mobile Only) */}
                <div className="flex items-center justify-between border-y border-ink/10 py-4 lg:hidden">
                  <button 
                    onClick={() => setMobileFilterOpen(true)}
                    className={cx("flex items-center gap-2 text-sm font-semibold uppercase tracking-widest text-ink transition-colors hover:text-gold", ui.focus)}
                  >
                    <ListFilter className="h-4 w-4" />
                    Filter
                  </button>
                  <button 
                    onClick={() => setMobileSortOpen(true)}
                    className={cx("flex items-center gap-2 text-sm font-semibold uppercase tracking-widest text-ink transition-colors hover:text-gold", ui.focus)}
                  >
                    {activeSort ? `Urutkan: ${activeSort}` : "Urutkan"}
                    <ChevronDown className="h-4 w-4" />
                  </button>
                </div>

                {/* Count & Search Row */}
                <div className="flex flex-col-reverse gap-4 md:flex-row md:items-end md:justify-between">
                  <p className="text-sm font-medium text-ink-muted">
                    Menampilkan <span className="font-bold text-ink">{filteredProducts.length}</span> dari <span className="font-bold text-ink">{products.length}</span> karya
                  </p>

                  <div className="relative w-full md:max-w-sm">
                    <input
                      type="text"
                      placeholder="Cari karya atau seniman..."
                      value={search}
                      onChange={(e) => setSearch(e.target.value)}
                      className={cx(
                        "w-full rounded-[var(--radius-base)] border border-ink/20 bg-transparent py-3 pl-10 pr-4 text-sm text-ink placeholder:text-ink-muted transition-colors hover:border-ink/40 focus:border-gold",
                        ui.focus
                      )}
                    />
                    <Search className="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" />
                  </div>
                </div>
              </div>

              {/* Grid */}
              <div className="grid grid-cols-2 gap-4 sm:gap-6 xl:grid-cols-3">
                {filteredProducts.map((product) => (
                  <Link to={`/product/${product.id}`} key={product.id} className="block group">
                    <ArtworkCard
                      category={product.category}
                      artist={product.artist}
                      title={product.title}
                      price={`Rp ${product.price.toLocaleString("id-ID")}`}
                      image={product.image}
                    />
                  </Link>
                ))}
              </div>

              {filteredProducts.length === 0 && (
                <div className="py-20 text-center text-ink-muted">
                  Tidak ada karya seni yang sesuai dengan pencarian atau filter.
                </div>
              )}
            </div>

          </div>
        </Container>
      </Section>
    </div>
  );
}
