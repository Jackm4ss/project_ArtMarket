Page: Art Market — Premium Art Marketplace

```html
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Art Market — Temukan Seni Luar Biasa</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
<link href="https://api.fontshare.com/v2/css?f[]=gambetta@400,500,600,700&f[]=satoshi@300,400,500,600,700&f[]=zodiak@400,500,600,700&display=swap" rel="stylesheet">
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        cream: '#F5F1ED',
        'cream-dark': '#EDE8E2',
        'cream-deeper': '#E5DFD8',
        ink: '#1A1A1A',
        'ink-light': '#3A3A3A',
        'ink-muted': '#6B6560',
        gold: '#D4AF37',
        'gold-dark': '#B8962E',
        'gold-light': '#E8CE7A',
        warm: '#8B7D6B',
      },
      fontFamily: {
        display: ['Zodiak', 'Georgia', 'serif'],
        heading: ['Gambetta', 'Georgia', 'serif'],
        body: ['Satoshi', 'Helvetica', 'sans-serif'],
      }
    }
  }
}
</script>
<style>
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  @keyframes scaleIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
  @keyframes slideRight {
    from { width: 0; }
    to { width: 100%; }
  }
  @keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
  }
  .anim-fade-up { animation: fadeUp 0.8s ease-out forwards; opacity: 0; }
  .anim-fade-in { animation: fadeIn 1s ease-out forwards; opacity: 0; }
  .anim-scale-in { animation: scaleIn 0.7s ease-out forwards; opacity: 0; }
  .delay-1 { animation-delay: 0.1s; }
  .delay-2 { animation-delay: 0.2s; }
  .delay-3 { animation-delay: 0.3s; }
  .delay-4 { animation-delay: 0.4s; }
  .delay-5 { animation-delay: 0.5s; }
  .delay-6 { animation-delay: 0.6s; }
  .delay-7 { animation-delay: 0.7s; }
  .delay-8 { animation-delay: 0.8s; }
  .delay-9 { animation-delay: 0.9s; }
  .delay-10 { animation-delay: 1.0s; }

  .art-card:hover .art-img {
    transform: scale(1.05);
  }
  .art-card:hover .art-overlay {
    opacity: 1;
  }
  .art-card:hover .art-tag {
    background-color: #D4AF37;
    color: #1A1A1A;
  }

  .gold-line {
    position: relative;
  }
  .gold-line::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 1.5px;
    background: #D4AF37;
    transition: width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  }
  .gold-line:hover::after {
    width: 100%;
  }

  .marquee-track {
    animation: marquee 30s linear infinite;
  }
  .marquee-track:hover {
    animation-play-state: paused;
  }

  .grain-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    pointer-events: none;
    z-index: 9999;
    opacity: 0.03;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
  }

  .feature-item {
    transition: all 0.3s ease;
  }
  .feature-item:hover {
    transform: translateX(6px);
  }

  .btn-elegant {
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
  }
  .btn-elegant::before {
    content: '';
    position: absolute;
    top: 0; left: -100%; width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
    transition: left 0.5s ease;
  }
  .btn-elegant:hover::before {
    left: 100%;
  }

  .newsletter-input:focus {
    box-shadow: 0 0 0 2px #D4AF37;
  }

  html { scroll-behavior: smooth; }
</style>
</head>
<body>
<div class="min-h-screen bg-cream text-ink font-body">

  <!-- Grain Overlay -->
  <div class="grain-overlay"></div>

  <!-- ===== NAVIGATION ===== -->
  <header class="fixed top-0 left-0 w-full z-50 bg-cream border-b border-cream-deeper/60">
    <nav class="max-w-[1400px] mx-auto px-8 lg:px-12 flex items-center justify-between h-20">
      <!-- Logo -->
      <a id="nav-logo-link" href="#" class="flex items-center gap-3 group">
        <div class="w-9 h-9 border-2 border-ink flex items-center justify-center group-hover:border-gold group-hover:bg-gold transition-all duration-300">
          <span class="font-display text-sm font-bold tracking-tight group-hover:text-ink transition-colors">A</span>
        </div>
        <span class="font-display text-xl font-semibold tracking-tight">Art Market</span>
      </a>

      <!-- Menu -->
      <div class="hidden md:flex items-center gap-10">
        <a id="nav-browse-link" href="#gallery" class="gold-line text-sm font-medium tracking-wide uppercase text-ink-muted hover:text-ink transition-colors">Jelajahi</a>
        <a id="nav-sellers-link" href="#value" class="gold-line text-sm font-medium tracking-wide uppercase text-ink-muted hover:text-ink transition-colors">Untuk Penjual</a>
        <a id="nav-about-link" href="#about" class="gold-line text-sm font-medium tracking-wide uppercase text-ink-muted hover:text-ink transition-colors">Tentang</a>
        <a id="nav-contact-link" href="#footer" class="gold-line text-sm font-medium tracking-wide uppercase text-ink-muted hover:text-ink transition-colors">Kontak</a>
      </div>

      <!-- CTA Buttons -->
      <div class="flex items-center gap-3">
        <a id="nav-buyer-btn" href="#" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 border border-ink/20 text-sm font-medium tracking-wide hover:border-gold hover:text-gold transition-all duration-300 rounded-none">
          <iconify-icon icon="lucide:eye" class="text-base"></iconify-icon>
          Pembeli
        </a>
        <a id="nav-seller-btn" href="#" class="btn-elegant inline-flex items-center gap-2 px-5 py-2.5 bg-ink text-cream text-sm font-medium tracking-wide hover:bg-ink-light transition-all duration-300 rounded-none">
          <iconify-icon icon="lucide:palette" class="text-base"></iconify-icon>
          Jual Karya
        </a>
      </div>
    </nav>
  </header>

  <!-- ===== HERO SECTION ===== -->
  <section class="pt-20 relative overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-8 lg:px-12">
      <div class="grid grid-cols-12 gap-6 min-h-[85vh] items-center">
        
        <!-- Left Content -->
        <div class="col-span-12 lg:col-span-5 py-16 lg:py-0 relative z-10">
          <div class="anim-fade-up delay-1">
            <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.2em] uppercase text-gold mb-8">
              <span class="w-8 h-px bg-gold"></span>
              Platform Seni Indonesia
            </span>
          </div>
          
          <h1 class="font-display text-5xl lg:text-6xl xl:text-7xl font-bold leading-[1.05] tracking-tight text-ink anim-fade-up delay-2">
            Temukan<br>
            Karya Seni<br>
            <span class="italic font-heading font-normal text-gold-dark">Luar Biasa</span>
          </h1>

          <p class="mt-8 text-lg leading-relaxed text-ink-muted max-w-md font-light anim-fade-up delay-3">
            Menghubungkan seniman berbakat Indonesia dengan kolektor dan pencinta seni. Jual beli karya fisik maupun digital dalam satu platform terpercaya.
          </p>

          <div class="mt-10 flex flex-wrap items-center gap-4 anim-fade-up delay-4">
            <a id="hero-explore-btn" href="#gallery" class="btn-elegant inline-flex items-center gap-3 px-8 py-4 bg-ink text-cream text-sm font-semibold tracking-widest uppercase hover:bg-ink-light transition-all duration-400 group">
              Jelajahi Koleksi
              <iconify-icon icon="lucide:arrow-right" class="text-lg group-hover:translate-x-1 transition-transform"></iconify-icon>
            </a>
            <a id="hero-artist-btn" href="#" class="inline-flex items-center gap-3 px-8 py-4 border border-ink/20 text-sm font-semibold tracking-widest uppercase hover:border-gold hover:text-gold transition-all duration-300 group">
              Daftar Seniman
              <iconify-icon icon="lucide:arrow-up-right" class="text-lg group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></iconify-icon>
            </a>
          </div>

          <!-- Stats -->
          <div class="mt-14 flex items-center gap-10 anim-fade-up delay-5">
            <div>
              <div class="font-display text-3xl font-bold tracking-tight">2.400+</div>
              <div class="text-xs text-ink-muted uppercase tracking-widest mt-1">Karya Seni</div>
            </div>
            <div class="w-px h-10 bg-ink/10"></div>
            <div>
              <div class="font-display text-3xl font-bold tracking-tight">850+</div>
              <div class="text-xs text-ink-muted uppercase tracking-widest mt-1">Seniman</div>
            </div>
            <div class="w-px h-10 bg-ink/10"></div>
            <div>
              <div class="font-display text-3xl font-bold tracking-tight">34</div>
              <div class="text-xs text-ink-muted uppercase tracking-widest mt-1">Kota</div>
            </div>
          </div>
        </div>

        <!-- Right - Hero Artwork Showcase -->
        <div class="col-span-12 lg:col-span-7 relative h-full flex items-center">
          <div class="relative w-full anim-scale-in delay-3">
            <!-- Main artwork -->
            <div class="relative ml-auto w-full max-w-[600px]">
              <div class="aspect-[4/5] bg-cream-dark overflow-hidden relative group">
                <img src="https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=800&q=80" alt="Karya Seni Utama" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-ink/40 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                  <div class="text-cream/70 text-xs uppercase tracking-[0.2em] font-medium">Karya Pilihan</div>
                  <div class="font-display text-2xl font-semibold text-cream mt-1 tracking-tight">Harmoni Nusantara</div>
                  <div class="text-cream/60 text-sm mt-1 font-light">oleh Anindya Kusuma</div>
                </div>
              </div>

              <!-- Floating card - top right -->
              <div class="absolute -top-6 -right-6 bg-white shadow-2xl p-4 anim-fade-up delay-7 hidden xl:block" style="min-width: 180px;">
                <div class="aspect-square w-full bg-cream-dark overflow-hidden mb-3">
                  <img src="https://images.unsplash.com/photo-1549490349-8643362247b5?w=400&q=80" alt="Mini artwork" class="w-full h-full object-cover">
                </div>
                <div class="font-heading text-sm font-medium">Pagi di Ubud</div>
                <div class="text-xs text-ink-muted mt-0.5">Rp 15.000.000</div>
              </div>

              <!-- Floating card - bottom left -->
              <div class="absolute -bottom-4 -left-8 bg-white shadow-2xl p-5 flex items-center gap-4 anim-fade-up delay-8 hidden xl:flex">
                <div class="w-12 h-12 rounded-full bg-cream-dark overflow-hidden flex-shrink-0">
                  <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&q=80" alt="Artist" class="w-full h-full object-cover">
                </div>
                <div>
                  <div class="text-xs text-gold uppercase tracking-[0.15em] font-semibold">Seniman Terverifikasi</div>
                  <div class="font-heading text-sm font-medium mt-0.5">Budi Hartono</div>
                  <div class="text-xs text-ink-muted">42 karya · Jakarta</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Decorative elements -->
    <div class="absolute top-32 left-12 w-px h-32 bg-gradient-to-b from-transparent via-gold/30 to-transparent anim-fade-in delay-6"></div>
    <div class="absolute bottom-20 right-24 w-24 h-24 border border-gold/10 rotate-45 anim-fade-in delay-9"></div>
  </section>

  <!-- ===== MARQUEE STRIP ===== -->
  <div class="border-y border-ink/8 py-4 overflow-hidden bg-cream-dark/50">
    <div class="flex whitespace-nowrap marquee-track" style="width: max-content;">
      <span class="flex items-center gap-8 mx-4 text-xs uppercase tracking-[0.3em] text-ink-muted font-medium">
        <span>Lukisan</span><span class="text-gold">✦</span>
        <span>Patung</span><span class="text-gold">✦</span>
        <span>Seni Digital</span><span class="text-gold">✦</span>
        <span>Fotografi</span><span class="text-gold">✦</span>
        <span>Kaligrafi</span><span class="text-gold">✦</span>
        <span>Instalasi</span><span class="text-gold">✦</span>
        <span>Batik Kontemporer</span><span class="text-gold">✦</span>
        <span>Ilustrasi</span><span class="text-gold">✦</span>
        <span>Seni Cetak</span><span class="text-gold">✦</span>
        <span>Mixed Media</span><span class="text-gold">✦</span>
      </span>
      <span class="flex items-center gap-8 mx-4 text-xs uppercase tracking-[0.3em] text-ink-muted font-medium">
        <span>Lukisan</span><span class="text-gold">✦</span>
        <span>Patung</span><span class="text-gold">✦</span>
        <span>Seni Digital</span><span class="text-gold">✦</span>
        <span>Fotografi</span><span class="text-gold">✦</span>
        <span>Kaligrafi</span><span class="text-gold">✦</span>
        <span>Instalasi</span><span class="text-gold">✦</span>
        <span>Batik Kontemporer</span><span class="text-gold">✦</span>
        <span>Ilustrasi</span><span class="text-gold">✦</span>
        <span>Seni Cetak</span><span class="text-gold">✦</span>
        <span>Mixed Media</span><span class="text-gold">✦</span>
      </span>
    </div>
  </div>

  <!-- ===== FEATURED GALLERY ===== -->
  <section id="gallery" class="py-24 lg:py-32">
    <div class="max-w-[1400px] mx-auto px-8 lg:px-12">
      
      <!-- Section Header -->
      <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-16">
        <div>
          <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.2em] uppercase text-gold mb-4">
            <span class="w-8 h-px bg-gold"></span>
            Koleksi Pilihan
          </span>
          <h2 class="font-display text-4xl lg:text-5xl font-bold tracking-tight">Karya Unggulan</h2>
        </div>
        <a id="gallery-viewall-link" href="#" class="mt-6 md:mt-0 inline-flex items-center gap-2 text-sm font-medium uppercase tracking-widest text-ink-muted hover:text-gold transition-colors group">
          Lihat Semua
          <iconify-icon icon="lucide:arrow-right" class="group-hover:translate-x-1 transition-transform"></iconify-icon>
        </a>
      </div>

      <!-- Gallery Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Art Card 1 -->
        <div class="art-card group cursor-pointer">
          <div class="relative overflow-hidden bg-cream-dark aspect-[3/4]">
            <img src="https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=600&q=80" alt="Abstract Painting" class="art-img w-full h-full object-cover transition-transform duration-700">
            <div class="art-overlay absolute inset-0 bg-ink/20 opacity-0 transition-opacity duration-500 flex items-center justify-center">
              <span class="bg-cream text-ink text-xs font-semibold uppercase tracking-widest px-6 py-3">Lihat Detail</span>
            </div>
            <div class="absolute top-4 left-4">
              <span class="art-tag bg-cream/90 text-ink text-[10px] font-bold uppercase tracking-[0.15em] px-3 py-1.5 transition-all duration-300">Lukisan</span>
            </div>
          </div>
          <div class="pt-5">
            <div class="text-xs text-ink-muted uppercase tracking-[0.15em] font-medium">Anindya Kusuma</div>
            <h3 class="font-heading text-lg font-medium mt-1 tracking-tight group-hover:text-gold transition-colors">Alam Bawah Sadar</h3>
            <div class="flex items-center justify-between mt-3">
              <span class="font-display text-base font-semibold">Rp 28.500.000</span>
              <span class="w-8 h-8 border border-ink/15 flex items-center justify-center group-hover:border-gold group-hover:bg-gold transition-all duration-300">
                <iconify-icon icon="lucide:heart" class="text-sm group-hover:text-ink"></iconify-icon>
              </span>
            </div>
          </div>
        </div>

        <!-- Art Card 2 -->
        <div class="art-card group cursor-pointer">
          <div class="relative overflow-hidden bg-cream-dark aspect-[3/4]">
            <img src="https://images.unsplash.com/photo-1578321272176-b7bbc0679853?w=600&q=80" alt="Sculpture" class="art-img w-full h-full object-cover transition-transform duration-700">
            <div class="art-overlay absolute inset-0 bg-ink/20 opacity-0 transition-opacity duration-500 flex items-center justify-center">
              <span class="bg-cream text-ink text-xs font-semibold uppercase tracking-widest px-6 py-3">Lihat Detail</span>
            </div>
            <div class="absolute top-4 left-4">
              <span class="art-tag bg-cream/90 text-ink text-[10px] font-bold uppercase tracking-[0.15em] px-3 py-1.5 transition-all duration-300">Patung</span>
            </div>
          </div>
          <div class="pt-5">
            <div class="text-xs text-ink-muted uppercase tracking-[0.15em] font-medium">Raka Prasetya</div>
            <h3 class="font-heading text-lg font-medium mt-1 tracking-tight group-hover:text-gold transition-colors">Fragmen Waktu</h3>
            <div class="flex items-center justify-between mt-3">
              <span class="font-display text-base font-semibold">Rp 45.000.000</span>
              <span class="w-8 h-8 border border-ink/15 flex items-center justify-center group-hover:border-gold group-hover:bg-gold transition-all duration-300">
                <iconify-icon icon="lucide:heart" class="text-sm group-hover:text-ink"></iconify-icon>
              </span>
            </div>
          </div>
        </div>

        <!-- Art Card 3 -->
        <div class="art-card group cursor-pointer">
          <div class="relative overflow-hidden bg-cream-dark aspect-[3/4]">
            <img src="https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?w=600&q=80" alt="Digital Art" class="art-img w-full h-full object-cover transition-transform duration-700">
            <div class="art-overlay absolute inset-0 bg-ink/20 opacity-0 transition-opacity duration-500 flex items-center justify-center">
              <span class="bg-cream text-ink text-xs font-semibold uppercase tracking-widest px-6 py-3">Lihat Detail</span>
            </div>
            <div class="absolute top-4 left-4">
              <span class="art-tag bg-cream/90 text-ink text-[10px] font-bold uppercase tracking-[0.15em] px-3 py-1.5 transition-all duration-300">Seni Digital</span>
            </div>
          </div>
          <div class="pt-5">
            <div class="text-xs text-ink-muted uppercase tracking-[0.15em] font-medium">Sari Dewi</div>
            <h3 class="font-heading text-lg font-medium mt-1 tracking-tight group-hover:text-gold transition-colors">Dimensi Paralel #7</h3>
            <div class="flex items-center justify-between mt-3">
              <span class="font-display text-base font-semibold">Rp 8.200.000</span>
              <span class="w-8 h-8 border border-ink/15 flex items-center justify-center group-hover:border-gold group-hover:bg-gold transition-all duration-300">
                <iconify-icon icon="lucide:heart" class="text-sm group-hover:text-ink"></iconify-icon>
              </span>
            </div>
          </div>
        </div>

        <!-- Art Card 4 -->
        <div class="art-card group cursor-pointer">
          <div class="relative overflow-hidden bg-cream-dark aspect-[3/4]">
            <img src="https://images.unsplash.com/photo-1482160549825-59d1b23cb208?w=600&q=80" alt="Photography" class="art-img w-full h-full object-cover transition-transform duration-700">
            <div class="art-overlay absolute inset-0 bg-ink/20 opacity-0 transition-opacity duration-500 flex items-center justify-center">
              <span class="bg-cream text-ink text-xs font-semibold uppercase tracking-widest px-6 py-3">Lihat Detail</span>
            </div>
            <div class="absolute top-4 left-4">
              <span class="art-tag bg-cream/90 text-ink text-[10px] font-bold uppercase tracking-[0.15em] px-3 py-1.5 transition-all duration-300">Fotografi</span>
            </div>
          </div>
          <div class="pt-5">
            <div class="text-xs text-ink-muted uppercase tracking-[0.15em] font-medium">Budi Hartono</div>
            <h3 class="font-heading text-lg font-medium mt-1 tracking-tight group-hover:text-gold transition-colors">Jejak Cahaya Senja</h3>
            <div class="flex items-center justify-between mt-3">
              <a id="gallery-inquiry-link" href="#" class="text-sm font-medium uppercase tracking-widest text-gold hover:text-gold-dark transition-colors">Inquiry →</a>
              <span class="w-8 h-8 border border-ink/15 flex items-center justify-center group-hover:border-gold group-hover:bg-gold transition-all duration-300">
                <iconify-icon icon="lucide:heart" class="text-sm group-hover:text-ink"></iconify-icon>
              </span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== VALUE PROPOSITION ===== -->
  <section id="value" class="py-24 lg:py-32 bg-ink text-cream relative overflow-hidden">
    <!-- Decorative -->
    <div class="absolute top-0 right-0 w-96 h-96 border border-gold/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 border border-gold/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

    <div class="max-w-[1400px] mx-auto px-8 lg:px-12 relative z-10">
      
      <!-- Section Header -->
      <div class="text-center mb-20">
        <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.2em] uppercase text-gold mb-4">
          <span class="w-8 h-px bg-gold"></span>
          Mengapa Art Market
          <span class="w-8 h-px bg-gold"></span>
        </span>
        <h2 class="font-display text-4xl lg:text-5xl font-bold tracking-tight text-cream">Satu Platform, <span class="italic font-heading font-normal text-gold">Dua Dunia</span></h2>
      </div>

      <!-- Two Columns -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
        
        <!-- For Artists -->
        <div class="border border-cream/8 p-10 lg:p-14 relative group hover:border-gold/20 transition-colors duration-500">
          <div class="absolute top-8 right-8 font-display text-7xl font-bold text-cream/[0.03] tracking-tighter">01</div>
          
          <div class="w-14 h-14 border border-gold/40 flex items-center justify-center mb-8">
            <iconify-icon icon="lucide:brush" class="text-2xl text-gold"></iconify-icon>
          </div>
          
          <h3 class="font-display text-2xl lg:text-3xl font-semibold tracking-tight mb-3">Untuk Seniman<br>& Kreator</h3>
          <p class="text-cream/50 text-sm mb-10 max-w-sm">Pamerkan dan jual karya Anda kepada ribuan kolektor dan pencinta seni di seluruh Indonesia.</p>

          <div class="space-y-6">
            <div class="feature-item flex items-start gap-4">
              <div class="w-8 h-8 flex-shrink-0 border border-gold/30 flex items-center justify-center mt-0.5">
                <iconify-icon icon="lucide:percent" class="text-sm text-gold"></iconify-icon>
              </div>
              <div>
                <div class="font-medium text-sm tracking-tight">Tanpa Biaya Listing</div>
                <div class="text-cream/40 text-xs mt-0.5 leading-relaxed">Unggah karya tanpa batas, gratis. Kami hanya mengambil komisi kecil saat karya terjual.</div>
              </div>
            </div>
            <div class="feature-item flex items-start gap-4">
              <div class="w-8 h-8 flex-shrink-0 border border-gold/30 flex items-center justify-center mt-0.5">
                <iconify-icon icon="lucide:globe" class="text-sm text-gold"></iconify-icon>
              </div>
              <div>
                <div class="font-medium text-sm tracking-tight">Jangkauan Nasional</div>
                <div class="text-cream/40 text-xs mt-0.5 leading-relaxed">Akses ke jaringan kolektor dan pembeli dari 34 provinsi di Indonesia.</div>
              </div>
            </div>
            <div class="feature-item flex items-start gap-4">
              <div class="w-8 h-8 flex-shrink-0 border border-gold/30 flex items-center justify-center mt-0.5">
                <iconify-icon icon="lucide:shield-check" class="text-sm text-gold"></iconify-icon>
              </div>
              <div>
                <div class="font-medium text-sm tracking-tight">Profil Terverifikasi</div>
                <div class="text-cream/40 text-xs mt-0.5 leading-relaxed">Dapatkan badge verifikasi yang meningkatkan kepercayaan pembeli terhadap karya Anda.</div>
              </div>
            </div>
            <div class="feature-item flex items-start gap-4">
              <div class="w-8 h-8 flex-shrink-0 border border-gold/30 flex items-center justify-center mt-0.5">
                <iconify-icon icon="lucide:bar-chart-3" class="text-sm text-gold"></iconify-icon>
              </div>
              <div>
                <div class="font-medium text-sm tracking-tight">Dashboard Analitik</div>
                <div class="text-cream/40 text-xs mt-0.5 leading-relaxed">Pantau performa karya, engagement, dan pendapatan melalui dashboard intuitif.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- For Collectors -->
        <div class="border border-cream/8 border-l-0 lg:border-l-0 border-t-0 lg:border-t p-10 lg:p-14 relative group hover:border-gold/20 transition-colors duration-500">
          <div class="absolute top-8 right-8 font-display text-7xl font-bold text-cream/[0.03] tracking-tighter">02</div>
          
          <div class="w-14 h-14 border border-gold/40 flex items-center justify-center mb-8">
            <iconify-icon icon="lucide:gem" class="text-2xl text-gold"></iconify-icon>
          </div>
          
          <h3 class="font-display text-2xl lg:text-3xl font-semibold tracking-tight mb-3">Untuk Kolektor<br>& Pencinta Seni</h3>
          <p class="text-cream/50 text-sm mb-10 max-w-sm">Temukan dan koleksi karya seni autentik dari seniman terbaik Indonesia dengan jaminan keaslian.</p>

          <div class="space-y-6">
            <div class="feature-item flex items-start gap-4">
              <div class="w-8 h-8 flex-shrink-0 border border-gold/30 flex items-center justify-center mt-0.5">
                <iconify-icon icon="lucide:sparkles" class="text-sm text-gold"></iconify-icon>
              </div>
              <div>
                <div class="font-medium text-sm tracking-tight">Kurasi Berkualitas</div>
                <div class="text-cream/40 text-xs mt-0.5 leading-relaxed">Setiap karya melewati proses kurasi untuk memastikan kualitas dan keaslian terjaga.</div>
              </div>
            </div>
            <div class="feature-item flex items-start gap-4">
              <div class="w-8 h-8 flex-shrink-0 border border-gold/30 flex items-center justify-center mt-0.5">
                <iconify-icon icon="lucide:badge-check" class="text-sm text-gold"></iconify-icon>
              </div>
              <div>
                <div class="font-medium text-sm tracking-tight">Seniman Terverifikasi</div>
                <div class="text-cream/40 text-xs mt-0.5 leading-relaxed">Beli langsung dari seniman asli dengan identitas dan portofolio yang diverifikasi.</div>
              </div>
            </div>
            <div class="feature-item flex items-start gap-4">
              <div class="w-8 h-8 flex-shrink-0 border border-gold/30 flex items-center justify-center mt-0.5">
                <iconify-icon icon="lucide:lock" class="text-sm text-gold"></iconify-icon>
              </div>
              <div>
                <div class="font-medium text-sm tracking-tight">Transaksi Aman</div>
                <div class="text-cream/40 text-xs mt-0.5 leading-relaxed">Sistem escrow melindungi pembayaran Anda hingga karya diterima dengan selamat.</div>
              </div>
            </div>
            <div class="feature-item flex items-start gap-4">
              <div class="w-8 h-8 flex-shrink-0 border border-gold/30 flex items-center justify-center mt-0.5">
                <iconify-icon icon="lucide:truck" class="text-sm text-gold"></iconify-icon>
              </div>
              <div>
                <div class="font-medium text-sm tracking-tight">Pengiriman Terjamin</div>
                <div class="text-cream/40 text-xs mt-0.5 leading-relaxed">Pengemasan khusus seni dan asuransi pengiriman untuk setiap karya fisik.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== HOW IT WORKS (mini) ===== -->
  <section id="about" class="py-24 lg:py-32 border-b border-ink/5">
    <div class="max-w-[1400px] mx-auto px-8 lg:px-12">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
        
        <!-- Left: Image Composition -->
        <div class="relative">
          <div class="grid grid-cols-2 gap-4">
            <div class="aspect-[3/4] bg-cream-dark overflow-hidden">
              <img src="https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=500&q=80" alt="Artist at work" class="w-full h-full object-cover hover:scale-105 transition-transform duration-700">
            </div>
            <div class="aspect-[3/4] bg-cream-dark overflow-hidden mt-12">
              <img src="https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=500&q=80" alt="Art gallery" class="w-full h-full object-cover hover:scale-105 transition-transform duration-700">
            </div>
          </div>
          <!-- Floating stat -->
          <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 bg-white shadow-2xl px-8 py-5 flex items-center gap-6">
            <div class="text-center">
              <div class="font-display text-2xl font-bold text-gold">98%</div>
              <div class="text-[10px] text-ink-muted uppercase tracking-widest mt-0.5">Kepuasan</div>
            </div>
            <div class="w-px h-10 bg-ink/10"></div>
            <div class="text-center">
              <div class="font-display text-2xl font-bold text-ink">5rb+</div>
              <div class="text-[10px] text-ink-muted uppercase tracking-widest mt-0.5">Transaksi</div>
            </div>
          </div>
        </div>

        <!-- Right: Content -->
        <div>
          <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.2em] uppercase text-gold mb-4">
            <span class="w-8 h-px bg-gold"></span>
            Tentang Kami
          </span>
          <h2 class="font-display text-4xl lg:text-5xl font-bold tracking-tight leading-tight">
            Membangun<br>Ekosistem Seni<br><span class="italic font-heading font-normal text-gold-dark">Indonesia</span>
          </h2>
          <p class="mt-8 text-ink-muted leading-relaxed max-w-lg">
            Art Market lahir dari keinginan untuk memberikan ruang yang layak bagi seniman Indonesia untuk memamerkan dan menjual karya mereka. Kami percaya bahwa setiap karya seni memiliki cerita dan nilai yang pantas diapresiasi.
          </p>
          <p class="mt-4 text-ink-muted leading-relaxed max-w-lg">
            Dengan teknologi modern dan pemahaman mendalam tentang dunia seni lokal, kami menciptakan jembatan antara kreator dan penikmat seni — tanpa batas geografis.
          </p>

          <div class="mt-10 flex flex-wrap gap-8">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-cream-dark flex items-center justify-center">
                <iconify-icon icon="lucide:scan-face" class="text-lg text-gold"></iconify-icon>
              </div>
              <span class="text-sm font-medium">Verifikasi Identitas</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-cream-dark flex items-center justify-center">
                <iconify-icon icon="lucide:hand-coins" class="text-lg text-gold"></iconify-icon>
              </div>
              <span class="text-sm font-medium">Escrow Payment</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-cream-dark flex items-center justify-center">
                <iconify-icon icon="lucide:headset" class="text-lg text-gold"></iconify-icon>
              </div>
              <span class="text-sm font-medium">Support 24/7</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== CTA SECTION ===== -->
  <section class="py-24 lg:py-32 relative overflow-hidden">
    <!-- BG Pattern -->
    <div class="absolute inset-0 opacity-[0.02]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%231A1A1A&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

    <div class="max-w-[900px] mx-auto px-8 lg:px-12 text-center relative z-10">
      <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.2em] uppercase text-gold mb-6">
        <span class="w-8 h-px bg-gold"></span>
        Bergabung Sekarang
        <span class="w-8 h-px bg-gold"></span>
      </span>
      
      <h2 class="font-display text-4xl lg:text-6xl font-bold tracking-tight leading-tight">
        Mulai Perjalanan<br>Seni Anda <span class="italic font-heading font-normal text-gold-dark">Hari Ini</span>
      </h2>
      
      <p class="mt-6 text-ink-muted text-lg max-w-xl mx-auto leading-relaxed">
        Bergabunglah dengan ribuan seniman dan kolektor yang telah mempercayai Art Market sebagai rumah karya seni mereka.
      </p>

      <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
        <a id="cta-buyer-btn" href="#" class="btn-elegant inline-flex items-center gap-3 px-10 py-5 bg-ink text-cream text-sm font-semibold tracking-widest uppercase hover:bg-ink-light transition-all duration-400 w-full sm:w-auto justify-center group">
          <iconify-icon icon="lucide:shopping-bag" class="text-lg"></iconify-icon>
          Daftar Sebagai Pembeli
          <iconify-icon icon="lucide:arrow-right" class="text-lg group-hover:translate-x-1 transition-transform"></iconify-icon>
        </a>
        <a id="cta-seller-btn" href="#" class="inline-flex items-center gap-3 px-10 py-5 border-2 border-gold text-gold-dark text-sm font-semibold tracking-widest uppercase hover:bg-gold hover:text-ink transition-all duration-400 w-full sm:w-auto justify-center group">
          <iconify-icon icon="lucide:palette" class="text-lg"></iconify-icon>
          Jadi Seniman
          <iconify-icon icon="lucide:arrow-right" class="text-lg group-hover:translate-x-1 transition-transform"></iconify-icon>
        </a>
      </div>

      <p class="mt-6 text-xs text-ink-muted">Gratis untuk bergabung · Tanpa biaya bulanan · Mulai jual dalam 5 menit</p>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer id="footer" class="bg-ink text-cream pt-20 pb-8">
    <div class="max-w-[1400px] mx-auto px-8 lg:px-12">
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 pb-16 border-b border-cream/8">
        
        <!-- Brand Column -->
        <div class="lg:col-span-4">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 border-2 border-gold flex items-center justify-center">
              <span class="font-display text-sm font-bold text-gold">A</span>
            </div>
            <span class="font-display text-xl font-semibold tracking-tight">Art Market</span>
          </div>
          <p class="text-cream/40 text-sm leading-relaxed max-w-xs mb-8">
            Platform digital nomor satu di Indonesia yang menghubungkan seniman, kreator, dan galeri dengan kolektor dan pencinta seni.
          </p>
          
          <!-- Newsletter -->
          <div>
            <div class="text-xs uppercase tracking-[0.2em] font-semibold text-cream/60 mb-3">Berlangganan Newsletter</div>
            <div class="flex">
              <input type="email" placeholder="Email Anda" class="newsletter-input bg-cream/5 border border-cream/10 px-4 py-3 text-sm text-cream placeholder:text-cream/30 flex-1 outline-none focus:border-gold transition-all">
              <button class="bg-gold text-ink px-5 py-3 text-sm font-semibold tracking-wider uppercase hover:bg-gold-light transition-colors flex-shrink-0">
                <iconify-icon icon="lucide:send" class="text-base"></iconify-icon>
              </button>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="lg:col-span-2 lg:col-start-6">
          <div class="text-xs uppercase tracking-[0.2em] font-semibold text-cream/60 mb-6">Jelajahi</div>
          <div class="space-y-3">
            <a id="footer-browse-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Lukisan</a>
            <a id="footer-sculpture-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Patung</a>
            <a id="footer-digital-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Seni Digital</a>
            <a id="footer-photo-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Fotografi</a>
            <a id="footer-print-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Seni Cetak</a>
          </div>
        </div>

        <!-- Company -->
        <div class="lg:col-span-2">
          <div class="text-xs uppercase tracking-[0.2em] font-semibold text-cream/60 mb-6">Perusahaan</div>
          <div class="space-y-3">
            <a id="footer-about-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Tentang Kami</a>
            <a id="footer-careers-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Karier</a>
            <a id="footer-press-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Media & Pers</a>
            <a id="footer-blog-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Blog Seni</a>
            <a id="footer-contact-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Hubungi Kami</a>
          </div>
        </div>

        <!-- Support -->
        <div class="lg:col-span-2">
          <div class="text-xs uppercase tracking-[0.2em] font-semibold text-cream/60 mb-6">Bantuan</div>
          <div class="space-y-3">
            <a id="footer-faq-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">FAQ</a>
            <a id="footer-shipping-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Pengiriman</a>
            <a id="footer-return-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Pengembalian</a>
            <a id="footer-privacy-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Privasi</a>
            <a id="footer-terms-link" href="#" class="block text-sm text-cream/40 hover:text-gold transition-colors">Syarat & Ketentuan</a>
          </div>
        </div>
      </div>

      <!-- Bottom Footer -->
      <div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="text-xs text-cream/30">© 2025 Art Market Indonesia. Semua hak cipta dilindungi.</div>
        
        <!-- Social Icons -->
        <div class="flex items-center gap-5">
          <a id="footer-instagram-link" href="#" class="w-9 h-9 border border-cream/10 flex items-center justify-center hover:border-gold hover:text-gold transition-all duration-300">
            <iconify-icon icon="lucide:instagram" class="text-sm text-cream/40 hover:text-gold"></iconify-icon>
          </a>
          <a id="footer-twitter-link" href="#" class="w-9 h-9 border border-cream/10 flex items-center justify-center hover:border-gold hover:text-gold transition-all duration-300">
            <iconify-icon icon="lucide:twitter" class="text-sm text-cream/40 hover:text-gold"></iconify-icon>
          </a>
          <a id="footer-facebook-link" href="#" class="w-9 h-9 border border-cream/10 flex items-center justify-center hover:border-gold hover:text-gold transition-all duration-300">
            <iconify-icon icon="lucide:facebook" class="text-sm text-cream/40 hover:text-gold"></iconify-icon>
          </a>
          <a id="footer-youtube-link" href="#" class="w-9 h-9 border border-cream/10 flex items-center justify-center hover:border-gold hover:text-gold transition-all duration-300">
            <iconify-icon icon="lucide:youtube" class="text-sm text-cream/40 hover:text-gold"></iconify-icon>
          </a>
          <a id="footer-tiktok-link" href="#" class="w-9 h-9 border border-cream/10 flex items-center justify-center hover:border-gold hover:text-gold transition-all duration-300">
            <iconify-icon icon="ic:baseline-tiktok" class="text-sm text-cream/40 hover:text-gold"></iconify-icon>
          </a>
        </div>
      </div>
    </div>
  </footer>

</div>
</body>
</html>
```

Please reference this design and implement it into our codebase; Try to understand the structure, which part of our codebase is relevant and implement
