/* ============================================================
   CrownMart - Main JavaScript Application
   ============================================================ */

'use strict';

// ============================================================
// STATE GLOBAL
// ============================================================
const STATE = {
  products:    [],
  cart:        [],
  orders:      [],
  user:        { name:'Emily Davis', email:'emily.davis@example.com', balance:5000, is_seller:false, watchList:[] },
  lang:        localStorage.getItem('cm_lang')     || 'EN',
  currency:    localStorage.getItem('cm_currency') || 'USD',
  activeTab:   'home',
  filterCat:   'All',
  filterType:  'all',
  sortBy:      'relevance',
  searchQuery: '',
  currentProduct: null,
  slideIndex:  0,
  slideTimer:  null,
  countdownTimer: null,
};

const CURRENCIES = {
  USD: { symbol: '$',   rate: 1,     decimals: 2 },
  IDR: { symbol: 'Rp ', rate: 16300, decimals: 0 },
  EUR: { symbol: 'â‚¬',   rate: 0.92,  decimals: 2 },
  GBP: { symbol: 'Â£',   rate: 0.78,  decimals: 2 },
  JPY: { symbol: 'Â¥',   rate: 155,   decimals: 0 },
};

const TRANSLATIONS = {
  EN: {
    marketplaceFeed:'Marketplace Feed', watchlistBidCenter:'Watch List & Bid Center',
    myPurchases:'My Purchases', sellAnItem:'Sell an Item', cart:'Cart',
    deliverTo:'Deliver to', weekendDeal:'âš¡ Weekend Lightning Deal: Bids Increment Extra 5%!',
    wallet:'Wallet', quickFilters:'Quick Filters', allItems:'All Items',
    buyNow:'Buy It Now', auctions:'Live Auctions', sortBy:'Sort by',
    relevance:'Relevance', priceLowHigh:'Price: Low to High',
    priceHighLow:'Price: High to Low', topRated:'Top Rated',
    endingSoon:'Auctions: Ending Soon', currentBid:'Current Bid',
    freeDelivery:'Free Delivery', placeBid:'Place Bid', add:'Add',
    checkout:'Checkout', total:'Total', insufficientWallet:'Insufficient Wallet Funds!',
    highestBidderSuccess:'You are now the highest bidder!',
    listingPublishedSuccess:'Listing published to marketplace!',
    checkoutRecorded:'Order successfully placed!',
    addToCartSuccess:'Added to Cart!', topUpFree:'+ $500 Free Cash',
    topUpPremium:'+ $2,000 Premium Credit', allCategories:'All Categories',
    heroHeadingCollectibles:'CrownMart Auctions: Mints, Rarities & Classics',
    heroSubCollectibles:'Explore mint condition collectibles from 1989 GameBoys to 1st Edition PSA Charizards. Place your bids before the countdown ends!',
    heroHeadingRetail:'CrownMart Direct: Super Express Retail Hub',
    heroSubRetail:'Browse premium consumer products from verified merchants. Authentic items backed by secure logistics and 100% satisfaction guarantee.',
    crownmartGuarantee:'CrownMart Guarantee: Certified Merchant Partners',
    crownmartProtection:'CrownMart Protection: Secure Bid Escrows',
    standardAir:'Standard 2-Day Air Shipping',
    noMatchTitle:'No Match Found', noMatchBtn:'Reset Filters',
    emptyWatchlist:'Watchlist is Empty', emptyWatchlistSub:'Press the â™¥ icon on any product to bookmark it.',
    noOrders:'No orders yet', ordersDashboard:'Your CrownMart Orders Dashboard',
    sellerPortal:'Professional Seller Center', createListing:'Create Custom Listing',
    productTitle:'Product Title', description:'Description',
    priceBuyout:'Price (or starting bid for auction)', listingType:'Listing Type',
    instantBuyout:'Direct Instant Buyout', liveAuction:'Live Auction Lot',
    stockLevel:'Initial Stock Level', durationHours:'Auction Duration (Hours)',
    selectImageCategory:'Image Category', postListing:'Post Listing to Marketplace',
    sellerFeedback:'Seller Rating', backToMarket:'Back to Market',
    writeReview:'Write a Review', submitReview:'Submit Review',
    activeBidding:'Active Bidding', minBidNotice:'Bid must be at least',
    bidAmount:'Bid Amount', placeBidNow:'Place Bid Now',
    upgradeSellerBtn:'Become a CrownMart Seller',
    currentSalesMetrics:'Active Listing & Sales Analytics',
    monthlyRevenue:'Monthly Revenue', totalSalesCount:'Completed Orders',
    activeAuctionsCount:'Active Auction Lots', yourActiveListings:'Your Live Listings',
    noListingsYet:"You haven't listed any items yet.",
    certifiedSeller:'Certified Professional Seller',
    isSellerAccount:'Registered CrownMart Professional Seller',
    notSellerAccountMessage:'Your profile is currently a buyer. Upgrade to start listing!',
    reviewHeading:'Customer Reviews', addReview:'Add Your Review',
    reviewButton:'Submit Feedback', timeRemaining:'Time Remaining',
    bidsCount:'bids placed', instantOneClick:'Instant 1-Click Buy', addToCart:'Add to Cart',
    ended:'Ended', freeShip:'Free Delivery', standardShip:'Standard Shipping',
    days:'day delivery', checkoutAddress:'Shipping Destination Address',
    completePayment:'Confirm & Complete Payment',
  },
  ID: {
    marketplaceFeed:'Umpan Pasar', watchlistBidCenter:'Daftar Pantau & Lelang',
    myPurchases:'Pembelian Saya', sellAnItem:'Jual Barang', cart:'Keranjang',
    deliverTo:'Kirim ke', weekendDeal:'âš¡ Kilat Akhir Pekan: Kenaikan Taruhan Bonus 5%!',
    wallet:'Dompet', quickFilters:'Filter Cepat', allItems:'Semua Barang',
    buyNow:'Beli Langsung', auctions:'Lelang Langsung', sortBy:'Urutkan',
    relevance:'Relevansi', priceLowHigh:'Harga: Rendah ke Tinggi',
    priceHighLow:'Harga: Tinggi ke Rendah', topRated:'Ulasan Terbaik',
    endingSoon:'Lelang: Segera Berakhir', currentBid:'Tawaran Saat Ini',
    freeDelivery:'Gratis Ongkir', placeBid:'Tawar Lelang', add:'Tambah',
    checkout:'Pesan Sekarang', total:'Total', insufficientWallet:'Saldo Dompet Tidak Cukup!',
    highestBidderSuccess:'Kamu sekarang penawar tertinggi!',
    listingPublishedSuccess:'Barang berhasil diterbitkan ke pasar!',
    checkoutRecorded:'Pesanan berhasil dibuat!',
    addToCartSuccess:'Berhasil ditambahkan ke keranjang!',
    topUpFree:'+ Rp500rb Saldo Gratis', topUpPremium:'+ Rp2Jt Kredit Premium',
    allCategories:'Semua Kategori',
    heroHeadingCollectibles:'Lelang CrownMart: Barang Langka & Klasik',
    heroSubCollectibles:'Temukan koleksi kondisi mulus dari GameBoy 1989 hingga Kartu Charizard PSA. Ajukan penawaran sebelum waktu habis!',
    heroHeadingRetail:'CrownMart Direct: Pusat Ritel Pengiriman Cepat',
    heroSubRetail:'Produk berkualitas dari mitra terverifikasi. Dijamin asli dengan pengiriman cepat dan jaminan kepuasan 100%.',
    crownmartGuarantee:'Garansi CrownMart: Mitra Penjual Resmi',
    crownmartProtection:'Perlindungan CrownMart: Rekening Bersama Aman',
    standardAir:'Pengiriman Udara Standar 2 Hari',
    noMatchTitle:'Tidak Ada Hasil', noMatchBtn:'Reset Filter',
    emptyWatchlist:'Daftar Pantau Kosong', emptyWatchlistSub:'Tekan ikon â™¥ pada produk untuk menandainya.',
    noOrders:'Belum ada pesanan', ordersDashboard:'Dasbor Pesanan CrownMart Anda',
    sellerPortal:'Pusat Penjual Profesional', createListing:'Buat Daftar Barang Baru',
    productTitle:'Nama Produk', description:'Deskripsi',
    priceBuyout:'Harga (atau tawaran awal lelang)', listingType:'Jenis Penjualan',
    instantBuyout:'Pembelian Langsung Instan', liveAuction:'Produk Lelang Langsung',
    stockLevel:'Stok Awal', durationHours:'Durasi Lelang (Jam)',
    selectImageCategory:'Kategori Gambar', postListing:'Terbitkan ke Pasar',
    sellerFeedback:'Penilaian Penjual', backToMarket:'Kembali ke Pasar',
    writeReview:'Tulis Ulasan', submitReview:'Kirim Ulasan',
    activeBidding:'Penawaran Aktif', minBidNotice:'Tawaran minimum adalah',
    bidAmount:'Jumlah Penawaran', placeBidNow:'Ajukan Sekarang',
    upgradeSellerBtn:'Menjadi Penjual CrownMart',
    currentSalesMetrics:'Analisis Penjualan & Daftar Barang Aktif',
    monthlyRevenue:'Estimasi Pendapatan Bulanan', totalSalesCount:'Pesanan Selesai',
    activeAuctionsCount:'Lelang Berlangsung', yourActiveListings:'Stok Pasar Aktif Anda',
    noListingsYet:'Anda belum mendaftarkan barang untuk dijual.',
    certifiedSeller:'Penjual Profesional Bersertifikat',
    isSellerAccount:'Akun Penjual Profesional CrownMart',
    notSellerAccountMessage:'Profil Anda saat ini sebagai pembeli. Upgrade untuk mulai berjualan!',
    reviewHeading:'Ulasan Pelanggan', addReview:'Tulis Ulasan Anda',
    reviewButton:'Kirim Masukan', timeRemaining:'Sisa Waktu',
    bidsCount:'tawaran', instantOneClick:'Beli Instan 1-Klik', addToCart:'Keranjang',
    ended:'Berakhir', freeShip:'Gratis Ongkir', standardShip:'Pengiriman Standar',
    days:'hari pengiriman', checkoutAddress:'Alamat Tujuan Pengiriman',
    completePayment:'Konfirmasi & Selesaikan Pembayaran',
  },

  DE: {
    marketplaceFeed:'Marktplatz-Feed', watchlistBidCenter:'Beobachtungsliste & Bietzentrum',
    myPurchases:'Meine KÃ¤ufe', sellAnItem:'Artikel verkaufen', cart:'Einkaufswagen',
    deliverTo:'Liefern an', weekendDeal:'âš¡ Wochenend-Blitzdeal: Gebote +5%!',
    wallet:'GeldbÃ¶rse', quickFilters:'Schnellfilter', allItems:'Alle Artikel',
    buyNow:'Sofort-Kauf', auctions:'Live-Auktionen', sortBy:'Sortieren',
    relevance:'Relevanz', priceLowHigh:'Preis: Niedrigâ€“Hoch',
    priceHighLow:'Preis: Hochâ€“Niedrig', topRated:'Beste Bewertungen',
    endingSoon:'Bald endend', currentBid:'Aktuelles Gebot',
    freeDelivery:'Kostenlose Lieferung', placeBid:'Gebot abgeben', add:'ZufÃ¼gen',
    checkout:'Zur Kasse', total:'Gesamt', insufficientWallet:'Unzureichendes Guthaben!',
    highestBidderSuccess:'Sie sind nun der HÃ¶chstbietende!',
    listingPublishedSuccess:'Angebot freigeschaltet!',
    checkoutRecorded:'Bestellung erfolgreich gebucht!',
    addToCartSuccess:'In den Einkaufswagen abgelegt!',
    topUpFree:'+ 500 â‚¬ Gratisguthaben', topUpPremium:'+ 2.000 â‚¬ Premium',
    allCategories:'Alle Kategorien',
    heroHeadingCollectibles:'CrownMart Auktionen: RaritÃ¤ten & Klassiker',
    heroSubCollectibles:'Seltene SammlerstÃ¼cke in makellosem Zustand.',
    heroHeadingRetail:'CrownMart Direct: Express Versandnetz',
    heroSubRetail:'Erstklassige Produkte von verifizierten Herstellern.',
    crownmartGuarantee:'CrownMart-Garantie: Zertifizierte HÃ¤ndler',
    crownmartProtection:'CrownMart-Schutz: Sichere Treuhandkonten',
    standardAir:'Standard-Luftversand (2 Tage)',
    noMatchTitle:'Keine Treffer', noMatchBtn:'Filter zurÃ¼cksetzen',
    emptyWatchlist:'Beobachtungsliste leer', emptyWatchlistSub:'â™¥ drÃ¼cken um zu speichern.',
    noOrders:'Noch keine Bestellungen', ordersDashboard:'Bestellungs-Dashboard',
    sellerPortal:'VerkÃ¤uferportal', createListing:'Angebot erstellen',
    productTitle:'Produktname', description:'Beschreibung',
    priceBuyout:'Preis (oder Startgebot)', listingType:'Angebotsart',
    instantBuyout:'Direkter Sofortkauf', liveAuction:'Live-Auktion',
    stockLevel:'Anfangsbestand', durationHours:'Auktionsdauer (Std)',
    selectImageCategory:'Bildkategorie', postListing:'Angebot verÃ¶ffentlichen',
    sellerFeedback:'VerkÃ¤uferbewertung', backToMarket:'ZurÃ¼ck',
    writeReview:'Bewertung schreiben', submitReview:'Absenden',
    activeBidding:'Aktives Bieten', minBidNotice:'Mindestgebot:',
    bidAmount:'Gebotsbetrag', placeBidNow:'Jetzt bieten',
    upgradeSellerBtn:'VerkÃ¤ufer werden',
    currentSalesMetrics:'Verkaufsstatistiken',
    monthlyRevenue:'Monatsumsatz', totalSalesCount:'Abgeschlossene Bestellungen',
    activeAuctionsCount:'Aktive Auktionen', yourActiveListings:'Ihre Angebote',
    noListingsYet:'Noch keine Angebote.',
    certifiedSeller:'Zertifizierter VerkÃ¤ufer',
    isSellerAccount:'CrownMart Profi-VerkÃ¤ufer',
    notSellerAccountMessage:'Profil ist als KÃ¤ufer konfiguriert. Upgrade!',
    reviewHeading:'Kundenbewertungen', addReview:'Bewertung schreiben',
    reviewButton:'Absenden', timeRemaining:'Verbleibende Zeit',
    bidsCount:'Gebote', instantOneClick:'1-Klick-Kauf', addToCart:'In Warenkorb',
    ended:'Beendet', freeShip:'Kostenlos', standardShip:'Standardversand',
    days:'-Tage-Lieferung', checkoutAddress:'Lieferadresse',
    completePayment:'Zahlung bestÃ¤tigen',
  },
  JA: {
    marketplaceFeed:'ãƒžãƒ¼ã‚±ãƒƒãƒˆãƒ—ãƒ¬ã‚¤ã‚¹', watchlistBidCenter:'ã‚¦ã‚©ãƒƒãƒãƒªã‚¹ãƒˆ/å…¥æœ­',
    myPurchases:'è³¼å…¥å±¥æ­´', sellAnItem:'å•†å“ã‚’å‡ºå“', cart:'ã‚«ãƒ¼ãƒˆ',
    deliverTo:'ãŠå±Šã‘å…ˆ', weekendDeal:'âš¡ é€±æœ«ç‰¹åˆ¥: å…¥æœ­é¡+5%!',
    wallet:'ã‚¦ã‚©ãƒ¬ãƒƒãƒˆ', quickFilters:'ã‚¯ã‚¤ãƒƒã‚¯ãƒ•ã‚£ãƒ«ã‚¿ãƒ¼', allItems:'ã™ã¹ã¦',
    buyNow:'ä»Šã™ãè²·ã†', auctions:'ãƒ©ã‚¤ãƒ–å…¥æœ­', sortBy:'ä¸¦ã³é †',
    relevance:'ãŠã™ã™ã‚', priceLowHigh:'ä¾¡æ ¼ã®å®‰ã„é †',
    priceHighLow:'ä¾¡æ ¼ã®é«˜ã„é †', topRated:'æ˜Ÿã®æ•°é †',
    endingSoon:'çµ‚äº†é–“è¿‘', currentBid:'ç¾åœ¨ã®å…¥æœ­ä¾¡æ ¼',
    freeDelivery:'å…¨å›½é€æ–™ç„¡æ–™', placeBid:'ä»Šã™ãå…¥æœ­', add:'ã‚«ãƒ¼ãƒˆã¸',
    checkout:'ãƒ¬ã‚¸ã«é€²ã‚€', total:'å°è¨ˆ', insufficientWallet:'æ®‹é«˜ä¸è¶³ï¼',
    highestBidderSuccess:'æœ€é«˜é¡å…¥æœ­è€…ã«ãªã‚Šã¾ã—ãŸï¼',
    listingPublishedSuccess:'å‡ºå“ã•ã‚Œã¾ã—ãŸï¼',
    checkoutRecorded:'æ³¨æ–‡ç¢ºå®šã—ã¾ã—ãŸï¼',
    addToCartSuccess:'ã‚«ãƒ¼ãƒˆã«è¿½åŠ ã—ã¾ã—ãŸï¼',
    topUpFree:'+ $500 ç„¡æ–™ãƒãƒ£ãƒ¼ã‚¸', topUpPremium:'+ $2,000 ãƒ—ãƒ¬ãƒŸã‚¢ãƒ ',
    allCategories:'ã™ã¹ã¦ã®ã‚«ãƒ†ã‚´ãƒª',
    heroHeadingCollectibles:'CrownMart ã‚ªãƒ¼ã‚¯ã‚·ãƒ§ãƒ³ï¼šæ­´å²çš„ãƒ¬ã‚¢ã‚¢ã‚¤ãƒ†ãƒ ',
    heroSubCollectibles:'1989å¹´è£½GameBoyã‹ã‚‰PSAé‘‘å®šæ¸ˆã¿ãƒã‚±ãƒ¢ãƒ³ã‚«ãƒ¼ãƒ‰ã¾ã§ã€‚',
    heroHeadingRetail:'CrownMart é€šè²©ï¼šè¶…é«˜é€Ÿé…é€',
    heroSubRetail:'åŽ³é¸ã•ã‚ŒãŸæœ€é«˜å³°ã‚¢ã‚¤ãƒ†ãƒ ã‚’å³é…ï¼',
    crownmartGuarantee:'CrownMartå“è³ªä¿è¨¼',
    crownmartProtection:'CrownMartå®‰å…¨ä¿è­·',
    standardAir:'æ¨™æº–2æ—¥é…é€',
    noMatchTitle:'çµæžœãªã—', noMatchBtn:'ãƒ•ã‚£ãƒ«ã‚¿ãƒ¼ãƒªã‚»ãƒƒãƒˆ',
    emptyWatchlist:'ã‚¦ã‚©ãƒƒãƒãƒªã‚¹ãƒˆç©º', emptyWatchlistSub:'â™¥ã‚’æŠ¼ã—ã¦è¿½åŠ ã€‚',
    noOrders:'æ³¨æ–‡ãªã—', ordersDashboard:'æ³¨æ–‡å±¥æ­´',
    sellerPortal:'å‡ºå“è€…ã‚»ãƒ³ã‚¿ãƒ¼', createListing:'æ–°ã—ã„å•†å“ã‚’å‡ºå“',
    productTitle:'å•†å“å', description:'å•†å“èª¬æ˜Ž',
    priceBuyout:'ä¾¡æ ¼ï¼ˆã¾ãŸã¯ã‚ªãƒ¼ã‚¯ã‚·ãƒ§ãƒ³é–‹å§‹ä¾¡æ ¼ï¼‰', listingType:'è²©å£²å½¢å¼',
    instantBuyout:'é€šå¸¸è²©å£²', liveAuction:'ã‚ªãƒ¼ã‚¯ã‚·ãƒ§ãƒ³',
    stockLevel:'åˆæœŸåœ¨åº«', durationHours:'ã‚ªãƒ¼ã‚¯ã‚·ãƒ§ãƒ³æœŸé–“ï¼ˆæ™‚é–“ï¼‰',
    selectImageCategory:'ç”»åƒã‚«ãƒ†ã‚´ãƒª', postListing:'å‡ºå“ç¢ºå®š',
    sellerFeedback:'å‡ºå“è€…è©•ä¾¡', backToMarket:'ãƒžãƒ¼ã‚±ãƒƒãƒˆã«æˆ»ã‚‹',
    writeReview:'ãƒ¬ãƒ“ãƒ¥ãƒ¼ã‚’æ›¸ã', submitReview:'æŠ•ç¨¿ã™ã‚‹',
    activeBidding:'å…¥æœ­çŠ¶æ³', minBidNotice:'æœ€ä½Žå…¥æœ­é¡:',
    bidAmount:'å…¥æœ­é¡', placeBidNow:'å…¥æœ­å®Ÿè¡Œ',
    upgradeSellerBtn:'ã‚»ãƒ©ãƒ¼ç™»éŒ²',
    currentSalesMetrics:'è²©å£²çµ±è¨ˆ',
    monthlyRevenue:'æœˆæ¬¡å£²ä¸Š', totalSalesCount:'è²©å£²ç¢ºå®šä»¶æ•°',
    activeAuctionsCount:'é–‹å‚¬ä¸­ç«¶å£²', yourActiveListings:'å‡ºå“ä¸­å•†å“',
    noListingsYet:'å‡ºå“å•†å“ãªã—ã€‚',
    certifiedSeller:'å…¬èªã‚»ãƒ©ãƒ¼',
    isSellerAccount:'CrownMartèªå®šã‚»ãƒ©ãƒ¼',
    notSellerAccountMessage:'ãƒã‚¤ãƒ¤ãƒ¼ã‚¢ã‚«ã‚¦ãƒ³ãƒˆã§ã™ã€‚ã‚»ãƒ©ãƒ¼ç™»éŒ²ã—ã¾ã—ã‚‡ã†ï¼',
    reviewHeading:'ã‚«ã‚¹ã‚¿ãƒžãƒ¼ãƒ¬ãƒ“ãƒ¥ãƒ¼', addReview:'ãƒ¬ãƒ“ãƒ¥ãƒ¼ã‚’æ›¸ã',
    reviewButton:'æŠ•ç¨¿ã™ã‚‹', timeRemaining:'æ®‹ã‚Šæ™‚é–“',
    bidsCount:'å…¥æœ­', instantOneClick:'1ã‚¯ãƒªãƒƒã‚¯æ±ºæ¸ˆ', addToCart:'ã‚«ãƒ¼ãƒˆã«å…¥ã‚Œã‚‹',
    ended:'çµ‚äº†', freeShip:'é€æ–™ç„¡æ–™', standardShip:'æ¨™æº–é…é€',
    days:'æ—¥ä»¥å†…ãŠå±Šã‘', checkoutAddress:'ãŠå±Šã‘å…ˆä½æ‰€',
    completePayment:'è³¼å…¥ç¢ºå®š',
  },
};

// ============================================================
// HELPERS
// ============================================================
const t = (key) => (TRANSLATIONS[STATE.lang] || TRANSLATIONS.EN)[key] || (TRANSLATIONS.EN[key] || key);

const fmt = (amount) => {
  const cfg = CURRENCIES[STATE.currency];
  const val = amount * cfg.rate;
  return cfg.symbol + val.toLocaleString(STATE.lang === 'ID' ? 'id-ID' : 'en-US', {
    minimumFractionDigits: cfg.decimals,
    maximumFractionDigits: cfg.decimals,
  });
};

const api = async (url, opts = {}) => {
  try {
    const res = await fetch(url, {
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      ...opts,
    });
    return await res.json();
  } catch (e) {
    console.error('API error:', e);
    return { error: e.message };
  }
};

const showToast = (msg, type = 'success', product = null) => {
  // Rich cart toast with product thumbnail
  if (type === 'cart' && product) {
    const existing = document.getElementById('cart-toast');
    if (existing) existing.remove();
    const el = document.createElement('div');
    el.id = 'cart-toast';
    el.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:500;background:white;color:#0f1111;
      border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.25);border:1px solid #e2e8f0;
      max-width:320px;width:100%;overflow:hidden;animation:toastIn .3s ease`;
    el.innerHTML = `
      <div style="padding:14px;display:flex;gap:12px;align-items:flex-start">
        <div style="width:56px;height:56px;border-radius:8px;overflow:hidden;flex-shrink:0;border:1px solid #f1f5f9">
          <img src="${product.image}" referrerpolicy="no-referrer"
               style="width:100%;height:100%;object-fit:cover">
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:10px;font-weight:700;color:#10b981;text-transform:uppercase;
                      display:flex;align-items:center;gap:4px">
            <span style="width:6px;height:6px;border-radius:50%;background:#10b981;animation:pulse 1s infinite;display:inline-block"></span>
            Added to Cart
          </div>
          <div style="font-weight:700;font-size:12px;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            ${product.title}
          </div>
          <div style="font-size:10px;color:#9ca3af;margin-top:1px">Qty: 1 â€¢ Standard Courier Delivery</div>
          <div style="display:flex;gap:8px;margin-top:8px">
            <button onclick="openSidebar();document.getElementById('cart-toast').remove()"
                    style="background:#f0c14b;color:#0f1111;border:1px solid #a88734;font-size:11px;font-weight:700;
                           padding:5px 10px;border-radius:4px;cursor:pointer">
              View Cart
            </button>
            <button onclick="cartCheckout()"
                    style="background:#0f1111;color:white;font-size:11px;font-weight:700;
                           padding:5px 10px;border-radius:4px;border:none;cursor:pointer">
              Checkout Now
            </button>
          </div>
        </div>
        <button onclick="this.closest('#cart-toast').remove()"
                style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:18px;line-height:1;flex-shrink:0">âœ•</button>
      </div>`;
    document.body.appendChild(el);
    setTimeout(() => {
      el.style.animation = 'toastOut .3s ease forwards';
      setTimeout(() => el.remove(), 350);
    }, 4000);
    return;
  }

  // Simple text toast
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = (type === 'success' ? 'âœ…' : 'âŒ') + ' ' + msg;
  document.body.appendChild(el);
  setTimeout(() => {
    el.classList.add('hide');
    setTimeout(() => el.remove(), 350);
  }, 3000);
};

const getCountdown = (endTime) => {
  if (!endTime) return '';
  const diff = endTime - Date.now();
  if (diff <= 0) return t('ended');
  const h = Math.floor(diff / 3600000);
  const m = Math.floor((diff % 3600000) / 60000);
  const s = Math.floor((diff % 60000) / 1000);
  const p = (n) => String(n).padStart(2, '0');
  return `${p(h)}h ${p(m)}m ${p(s)}s`;
};

const starsHtml = (rating, max = 5) => {
  let html = '';
  for (let i = 1; i <= max; i++) {
    html += `<span class="star" style="color:${i <= Math.round(rating) ? '#f59e0b' : '#d1d5db'}">â˜…</span>`;
  }
  return html;
};

// ============================================================
// API CALLS
// ============================================================
const loadUser = async () => {
  const data = await api('api/user.php');
  if (!data.error) STATE.user = data;
  renderUserUI();
};

const loadProducts = async () => {
  const params = new URLSearchParams();
  if (STATE.filterCat !== 'All') params.set('category', STATE.filterCat);
  if (STATE.filterType !== 'all') params.set('type', STATE.filterType);
  if (STATE.searchQuery) params.set('q', STATE.searchQuery);
  params.set('sort', STATE.sortBy);

  const data = await api('api/products.php?' + params);
  if (!data.error && Array.isArray(data)) {
    STATE.products = data;
    renderProductGrid();
    updateSectionHeading();
  }
};

const loadCart = async () => {
  const data = await api('api/cart.php');
  if (!data.error && Array.isArray(data)) {
    STATE.cart = data;
    renderCart();
    updateCartBadge();
  }
};

const loadOrders = async () => {
  const data = await api('api/orders.php');
  if (!data.error && Array.isArray(data)) {
    STATE.orders = data;
    renderOrders();
  }
};

// ============================================================
// RENDER FUNCTIONS
// ============================================================
const renderUserUI = () => {
  const u = STATE.user;
  const el = (id) => document.getElementById(id);
  if (el('wallet-balance')) el('wallet-balance').textContent = fmt(u.balance);
  if (el('tab-watchlist-count')) el('tab-watchlist-count').textContent = (u.watchList || []).length;
  if (el('tab-orders-count')) el('tab-orders-count').textContent = STATE.orders.length || '';
};

const renderProductGrid = () => {
  const grid = document.getElementById('product-grid');
  if (!grid) return;

  if (STATE.products.length === 0) {
    grid.innerHTML = `
      <div class="empty-state" style="grid-column:1/-1">
        <div class="empty-icon">ðŸ“¦</div>
        <h3>${t('noMatchTitle')}</h3>
        <p>No products match your current filters.</p>
        <div class="empty-actions">
          <button class="btn-secondary" onclick="resetFilters()">${t('noMatchBtn')}</button>
          <button class="btn-primary" onclick="switchTab('seller')">${t('sellAnItem')} +</button>
        </div>
      </div>`;
    return;
  }

  grid.innerHTML = STATE.products.map(p => renderProductCard(p)).join('');
};

const renderProductCard = (p) => {
  const isAuction   = p.type === 'auction';
  const isWatched   = (STATE.user.watchList || []).includes(p.id);
  const countdown   = isAuction ? getCountdown(p.end_time) : '';
  const bidCount    = (p.bids || []).length;
  const priceLabel  = isAuction ? t('currentBid') : '';
  const shipLabel   = p.shipping === 'free' ? `<span class="card-shipping">âœ“ ${t('freeDelivery')}</span>` : `<span style="color:#6b7280">+shipping</span>`;

  return `
  <div class="product-card" onclick="openProductModal('${p.id}')">
    <button class="watch-btn ${isWatched ? 'watched' : ''}" onclick="toggleWatch(event,'${p.id}')">â™¥</button>
    <div class="card-img-wrap">
      <img src="${p.image}" alt="${p.title}" referrerpolicy="no-referrer" loading="lazy">
      ${isAuction
        ? `<div class="countdown-badge"><span class="spin">â±</span> <span class="cd-text" data-endtime="${p.end_time}">${countdown}</span></div>
           <div class="card-badge-auction">ðŸ”¨ ${t('auctions')}</div>`
        : `<div class="card-badge-fixed">âœ“ Express</div>`
      }
    </div>
    <div class="card-body">
      <div class="card-meta">
        <span class="card-cat">${p.category}</span>
        <div class="card-rating">${starsHtml(p.rating)}<span class="cnt">(${p.review_count})</span></div>
      </div>
      <div class="card-title">${p.title}</div>
      <div class="card-seller">Seller: <strong>${p.seller.name}</strong><span class="seller-rt">${p.seller.rating}%</span></div>
      <div class="card-price-row">
        <div>
          ${priceLabel ? `<div class="card-price-label">${priceLabel}</div>` : ''}
          <div class="card-price">${fmt(p.price)}</div>
        </div>
      </div>
      ${shipLabel}
      ${isAuction ? `<div class="bid-count-label">ðŸ· ${bidCount} ${t('bidsCount')}</div>` : ''}
      <div class="card-actions">
        ${isAuction
          ? `<button class="btn-primary" onclick="openProductModal('${p.id}');event.stopPropagation()">${t('placeBid')}</button>`
          : `<button class="btn-primary" onclick="buyNow(event,'${p.id}')">${t('instantOneClick')}</button>
             <button class="btn-secondary" onclick="addToCart(event,'${p.id}')">${t('add')}</button>`
        }
      </div>
    </div>
  </div>`;
};

// ============================================================
// PRODUCT MODAL
// ============================================================
const openProductModal = async (productId) => {
  const data = await api(`api/products.php?id=${productId}`);
  if (data.error) return showToast(data.error, 'error');
  STATE.currentProduct = data;
  renderModal(data);
  document.getElementById('modal-overlay').classList.add('open');
};

const renderModal = (p) => {
  const isAuction = p.type === 'auction';
  const isWatched = (STATE.user.watchList || []).includes(p.id);
  const bids      = p.bids || [];
  const reviews   = p.reviews || [];

  const bidsHtml = bids.length > 0
    ? bids.map(b => `
        <div class="bid-row">
          <span class="bidder">ðŸ§‘ ${b.bidder}</span>
          <span class="amount">${fmt(b.amount)}</span>
        </div>`).join('')
    : '<p style="color:#9ca3af;font-size:12px">No bids yet â€” be the first!</p>';

  const topBidder = bids.length > 0 ? bids[0].bidder : null;

  const quickBidHtml = `
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:8px">
      <button onclick="placeBid('${p.id}',${p.price + 10})"
              style="background:#0f1111;color:white;font-family:monospace;font-weight:700;font-size:11px;padding:7px 4px;border-radius:4px;border:none;cursor:pointer">
        +${fmt(10)}
      </button>
      <button onclick="placeBid('${p.id}',${p.price + 50})"
              style="background:#0284c7;color:white;font-family:monospace;font-weight:700;font-size:11px;padding:7px 4px;border-radius:4px;border:none;cursor:pointer">
        +${fmt(50)}
      </button>
      <button onclick="placeBid('${p.id}',${p.price + 200})"
              style="background:#10b981;color:white;font-family:monospace;font-weight:700;font-size:11px;padding:7px 4px;border-radius:4px;border:none;cursor:pointer">
        +${fmt(200)}
      </button>
    </div>`;

  const reviewsHtml = reviews.length > 0
    ? reviews.map(r => `
        <div class="review-item">
          <div class="review-header">
            <span class="review-user">ðŸ‘¤ ${r.user}</span>
            <span class="review-stars">${starsHtml(r.rating)}</span>
          </div>
          <div class="review-comment">${r.comment}</div>
          <div class="review-date">${r.date}</div>
        </div>`).join('')
    : '<p style="color:#9ca3af;font-size:12px;margin-top:8px">No reviews yet.</p>';

  const minBid = p.price > 0 ? (p.price + 5).toFixed(2) : (p.starting_price || 5).toFixed(2);

  document.getElementById('modal-content').innerHTML = `
    <img class="modal-img" src="${p.image}" alt="${p.title}" referrerpolicy="no-referrer">
    <div class="modal-body">
      <div class="modal-header">
        <h2 class="modal-title">${p.title}</h2>
        <button class="modal-close" onclick="closeModal()">âœ•</button>
      </div>

      <div class="modal-meta">
        <span class="meta-chip">${p.category}</span>
        <span class="meta-chip yellow">â­ ${p.rating} (${p.review_count} reviews)</span>
        ${p.shipping === 'free'
          ? `<span class="meta-chip green">ðŸšš ${t('freeDelivery')}</span>`
          : `<span class="meta-chip">ðŸ“¦ Standard Shipping</span>`}
        <span class="meta-chip">âš¡ ${p.delivery_days}${t('days')}</span>
        ${isAuction && p.end_time
          ? `<span class="meta-chip red" id="modal-countdown">â± <span class="cd-text" data-endtime="${p.end_time}">${getCountdown(p.end_time)}</span></span>`
          : ''}
      </div>

      <div class="modal-price">${fmt(p.price)}</div>
      <div class="modal-price-label">${isAuction ? t('currentBid') : ''} â€” Seller: ${p.seller.name} (${p.seller.rating}% positive)</div>

      <p class="modal-desc">${p.description}</p>

      ${isAuction ? `
        <div class="bid-section">
          <h4>ðŸ† ${t('activeBidding')} â€” Min next bid: ${fmt(parseFloat(minBid))}</h4>
          <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:10px">
            <div>
              <div style="font-size:10px;color:#78350f;font-weight:600;text-transform:uppercase">Current Bid</div>
              <div style="font-size:22px;font-weight:900;font-family:monospace;color:#0f1111">${fmt(p.price)}</div>
            </div>
            ${topBidder ? `<div style="text-align:right">
              <div style="font-size:10px;color:#78350f;font-weight:600;text-transform:uppercase">High Bidder</div>
              <div style="font-size:12px;font-weight:700;color:#065f46">${topBidder}</div>
            </div>` : ''}
          </div>
          <div class="bid-input-row">
            <input type="number" id="bid-input" class="bid-input" placeholder="${fmt(parseFloat(minBid))} or more" min="${minBid}" step="5">
            <button class="btn-bid" onclick="placeBid('${p.id}')">${t('placeBidNow')}</button>
          </div>
          ${quickBidHtml}
          <div class="bid-history" style="margin-top:10px">${bidsHtml}</div>
        </div>
      ` : `
        <div class="modal-actions">
          ${p.stock !== null && p.stock <= 0
            ? `<div class="meta-chip red" style="font-size:13px;font-weight:700">Out of Stock</div>`
            : `<button class="btn-buynow" onclick="buyNow(null,'${p.id}',true)">${t('instantOneClick')}</button>
               <button class="btn-addcart" onclick="addToCart(null,'${p.id}',true)">${t('addToCart')}</button>`
          }
          <button class="watch-btn ${isWatched ? 'watched' : ''}" style="position:static;width:auto;height:auto;padding:9px 14px;border-radius:4px;font-size:13px" onclick="toggleWatch(null,'${p.id}')">
            ${isWatched ? 'â™¥ Watching' : 'â™¡ Watch'}
          </button>
        </div>
      `}

      <div class="reviews-list">
        <h4 style="font-size:14px;font-weight:800;margin-top:20px;margin-bottom:4px">ðŸ’¬ ${t('reviewHeading')}</h4>
        ${reviewsHtml}
        <div class="review-form">
          <h5>${t('addReview')}</h5>
          <div class="star-rating" id="review-stars">
            ${[1,2,3,4,5].map(i => `<span class="star filled" data-val="${i}" onclick="setReviewRating(${i})">â˜…</span>`).join('')}
          </div>
          <textarea id="review-text" class="form-textarea" placeholder="Share your experience..." rows="3"></textarea>
          <button class="btn-submit" style="margin-top:8px" onclick="submitReview('${p.id}')">${t('submitReview')}</button>
        </div>
      </div>
    </div>`;
};

const closeModal = () => {
  document.getElementById('modal-overlay').classList.remove('open');
  STATE.currentProduct = null;
};

// ============================================================
// ACTIONS
// ============================================================
const toggleWatch = async (event, productId) => {
  if (event) event.stopPropagation();
  const res = await api('api/user.php', {
    method: 'POST',
    body: JSON.stringify({ action: 'toggle_watchlist', productId }),
  });
  if (res.error) return showToast(res.error, 'error');
  await loadUser();
  // Refresh product card watch button
  document.querySelectorAll('.watch-btn').forEach(btn => {
    if (btn.onclick?.toString().includes(productId)) {
      btn.classList.toggle('watched', res.isWatched);
    }
  });
  if (STATE.activeTab === 'bids') renderWatchlist();
};

// ============================================================
// CEK LOGIN â€” helper untuk guard semua aksi
// ============================================================
const isLoggedIn = () => {
  // CM_SESSION diinject PHP di <script> sebelum app.js
  if (typeof CM_SESSION !== 'undefined' && CM_SESSION.userId > 0) {
    STATE._sessionUserId = CM_SESSION.userId;
    return true;
  }
  if (typeof STATE._sessionUserId !== 'undefined' && STATE._sessionUserId > 0) return true;
  const meta = document.getElementById('cm-session-data');
  if (meta && parseInt(meta.dataset.userId) > 0) {
    STATE._sessionUserId = parseInt(meta.dataset.userId);
    return true;
  }
  return false;
};

// Panggil login modal dengan aman (didefinisikan di index.php setelah app.js)
const triggerLoginModal = (message, icon) => {
  if (typeof showLoginModal === 'function') {
    showLoginModal(message, icon);
  } else {
    // Fallback kalau modal belum siap
    if (confirm(message + '\n\nLogin sekarang?')) {
      window.location.href = 'login.php';
    }
  }
};

const addToCart = async (event, productId, fromModal = false) => {
  if (event) event.stopPropagation();
  if (!isLoggedIn()) {
    triggerLoginModal('Sign in to add items to your cart.');
    return;
  }
  const res = await api('api/cart.php', {
    method: 'POST',
    body: JSON.stringify({ productId }),
  });
  if (res.loginRequired) { triggerLoginModal('Sign in to add items to your cart.'); return; }
  if (res.error) return showToast(res.error, 'error');
  STATE.cart = res.cart;
  renderCart();
  updateCartBadge();
  // Find the product object for the rich toast
  const prod = STATE.products.find(p => p.id === productId)
    || res.cart.find(ci => ci.product.id === productId)?.product;
  if (prod) {
    showToast('', 'cart', prod);
  } else {
    showToast(t('addToCartSuccess'));
  }
  if (fromModal) closeModal();
};

// Transfer bank confirmation modal
const showTransferModal = (orderId, total, info) => {
  const el = document.createElement('div');
  el.style.cssText = 'position:fixed;inset:0;z-index:700;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;padding:20px';
  el.innerHTML = `
    <div style="background:white;border-radius:12px;max-width:400px;width:100%;padding:28px;box-shadow:0 32px 80px rgba(0,0,0,.4)">
      <div style="text-align:center;margin-bottom:20px">
        <div style="font-size:40px">ðŸ¦</div>
        <h3 style="font-size:18px;font-weight:800;margin-top:8px">Transfer Bank</h3>
        <p style="color:#6b7280;font-size:13px;margin-top:4px">Pesanan #${orderId} menunggu pembayaran</p>
      </div>
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-bottom:16px">
        <div style="font-size:12px;color:#6b7280;margin-bottom:6px">Transfer tepat sebesar:</div>
        <div style="font-size:28px;font-weight:900;color:#1e40af;font-family:monospace">${fmt(total)}</div>
        <div style="margin-top:12px;font-size:13px">
          <div>ðŸ¦ <strong>${info.bank}</strong></div>
          <div style="font-family:monospace;font-size:18px;font-weight:800;letter-spacing:2px;margin:4px 0">${info.account}</div>
          <div style="color:#374151">a/n ${info.name}</div>
        </div>
      </div>
      <div style="font-size:12px;color:#9ca3af;text-align:center;margin-bottom:16px">
        Pesanan akan diproses oleh admin setelah pembayaran dikonfirmasi.
      </div>
      <button onclick="this.closest('[style]').remove()" style="width:100%;padding:11px;background:#f0c14b;border:1px solid #a88734;color:#0f1111;font-weight:800;font-size:14px;border-radius:6px;cursor:pointer">
        Mengerti, Saya Akan Transfer
      </button>
    </div>`;
  document.body.appendChild(el);
};

const buyNow = async (event, productId, fromModal = false) => {
  if (event) event.stopPropagation();
  if (!isLoggedIn()) {
    triggerLoginModal('Sign in to purchase items instantly.');
    return;
  }
  const address = document.getElementById('checkout-address')?.value
    || 'Default Address, City, Country';
  const res = await api('api/orders.php', {
    method: 'POST',
    body: JSON.stringify({ mode: 'buynow', productId, address }),
  });
  if (res.error) return showToast(res.error, 'error');
  STATE.user.balance = res.newBalance;
  renderUserUI();
  showSuccessBanner(`${t('checkoutRecorded')} ID: #${res.orderId}`);
  await loadOrders();
  if (fromModal) closeModal();
  await loadProducts();
};

// Quick bid dari watchlist panel (tanpa buka modal)
const quickBid = async (productId, amount) => {
  if (!isLoggedIn()) {
    triggerLoginModal('Sign in to place a bid on this auction.');
    return;
  }
  const res = await api('api/bids.php', {
    method: 'POST',
    body: JSON.stringify({ productId, amount }),
  });
  if (res.error) return showToast(res.message || res.error, 'error');
  showToast(res.message || t('highestBidderSuccess'));
  await loadUser();
  await loadProducts();
  renderWatchlist();
};

const placeBid = async (productId, directAmount) => {
  if (!isLoggedIn()) {
    triggerLoginModal('Sign in to place a bid on this auction.');
    return;
  }
  let amount;
  if (directAmount !== undefined) {
    amount = parseFloat(directAmount);
  } else {
    const input = document.getElementById('bid-input');
    amount = parseFloat(input?.value);
  }
  if (!amount || amount <= 0) return showToast('Enter a valid bid amount', 'error');

  const res = await api('api/bids.php', {
    method: 'POST',
    body: JSON.stringify({ productId, amount }),
  });
  if (res.loginRequired) { triggerLoginModal('Sign in to place a bid on this auction.'); return; }
  if (res.error) return showToast(res.message || res.error, 'error');

  showToast(res.message || t('highestBidderSuccess'));
  await loadUser();
  // Refresh modal with updated product
  await openProductModal(productId);
  await loadProducts();
};

// ============================================================
// PAYMENT METHOD SELECTOR
// ============================================================
let selectedPaymentMethod = 'wallet';

const selectPayment = (method) => {
  selectedPaymentMethod = method;
  ['wallet','transfer','cod'].forEach(m => {
    const el = document.getElementById(`pay-${m}`);
    if (!el) return;
    if (m === method) {
      el.style.border = '2px solid #f0c14b';
      el.style.background = '#fffbeb';
    } else {
      el.style.border = '2px solid #e5e7eb';
      el.style.background = '#f9fafb';
    }
  });
  // Show/hide info boxes
  const ti = document.getElementById('transfer-info');
  const ci = document.getElementById('cod-info');
  if (ti) ti.style.display = method === 'transfer' ? 'block' : 'none';
  if (ci) ci.style.display = method === 'cod'      ? 'block' : 'none';
};

const cartCheckout = async () => {
  if (!isLoggedIn()) {
    triggerLoginModal('Sign in to complete your purchase.', 'ðŸ”’');
    return;
  }
  const address  = document.getElementById('checkout-address')?.value?.trim();
  const shipping = document.getElementById('shipping-method')?.value || 'regular';
  const payment  = selectedPaymentMethod || 'wallet';

  if (!address) return showToast('Please enter a shipping address', 'error');

  const res = await api('api/orders.php', {
    method: 'POST',
    body: JSON.stringify({
      mode:           'cart',
      address,
      paymentMethod:  payment,
      shippingMethod: shipping,
    }),
  });
  if (res.error) return showToast(res.error, 'error');

  STATE.cart = [];
  STATE.user.balance = res.newBalance;
  renderCart();
  updateCartBadge();
  renderUserUI();
  closeSidebar();

  // Tampilkan info transfer jika pilih transfer bank
  if (payment === 'transfer' && res.transferInfo) {
    showTransferModal(res.orderId, res.total, res.transferInfo);
  } else {
    showSuccessBanner(res.message);
  }
  await loadOrders();
  await loadProducts();
};

const topUp = async (amount) => {
  const res = await api('api/user.php', {
    method: 'POST',
    body: JSON.stringify({ action: 'topup', amount }),
  });
  if (res.error) return showToast(res.error, 'error');
  STATE.user.balance = res.balance;
  renderUserUI();
  showToast(`Wallet topped up! New balance: ${fmt(res.balance)}`);
};

const updateCartQty = async (productId, delta) => {
  const item = STATE.cart.find(ci => ci.product.id === productId);
  if (!item) return;
  const newQty = item.quantity + delta;
  const res = await api('api/cart.php', {
    method: 'PUT',
    body: JSON.stringify({ productId, quantity: newQty }),
  });
  if (res.error) return showToast(res.error, 'error');
  STATE.cart = res.cart;
  renderCart();
  updateCartBadge();
};

const removeCartItem = async (productId) => {
  const res = await api('api/cart.php', {
    method: 'DELETE',
    body: JSON.stringify({ productId }),
  });
  if (res.error) return showToast(res.error, 'error');
  STATE.cart = res.cart;
  renderCart();
  updateCartBadge();
};

const submitReview = async (productId) => {
  const comment = document.getElementById('review-text')?.value?.trim();
  const rating  = STATE.reviewRating || 5;
  if (!comment) return showToast('Please write a comment', 'error');

  const res = await api('api/reviews.php', {
    method: 'POST',
    body: JSON.stringify({ productId, rating, comment }),
  });
  if (res.error) return showToast(res.error, 'error');
  showToast('Review submitted!');
  await openProductModal(productId);
};

const setReviewRating = (val) => {
  STATE.reviewRating = val;
  document.querySelectorAll('#review-stars .star').forEach((s, i) => {
    s.classList.toggle('filled', i < val);
  });
};

const renderCart = () => {
  const list  = document.getElementById('cart-items');
  const total = STATE.cart.reduce((s, ci) => s + ci.product.price * ci.quantity, 0);

  if (!list) return;

  if (STATE.cart.length === 0) {
    list.innerHTML = `
      <div class="cart-empty">
        <div class="big-icon">ðŸ›’</div>
        <p>Your cart is empty</p>
      </div>`;
  } else {
    list.innerHTML = STATE.cart.map(ci => `
      <div class="cart-item">
        <img class="cart-item-img" src="${ci.product.image}" alt="${ci.product.title}" referrerpolicy="no-referrer">
        <div class="cart-item-info">
          <div class="cart-item-title">${ci.product.title}</div>
          <div class="cart-item-price">${fmt(ci.product.price)}</div>
          <div class="cart-item-ctrl">
            <button class="qty-btn" onclick="updateCartQty('${ci.product.id}',-1)">âˆ’</button>
            <span class="qty-val">${ci.quantity}</span>
            <button class="qty-btn" onclick="updateCartQty('${ci.product.id}',1)">+</button>
            <button class="cart-item-remove" onclick="removeCartItem('${ci.product.id}')">ðŸ—‘</button>
          </div>
        </div>
      </div>`).join('');
  }

  const totalEl = document.getElementById('cart-total');
  if (totalEl) totalEl.textContent = fmt(total);
};

const updateCartBadge = () => {
  const totalQty = STATE.cart.reduce((s, ci) => s + ci.quantity, 0);
  document.querySelectorAll('.cart-count').forEach(el => {
    el.textContent = totalQty;
    // Bounce animation
    el.closest('.cart-btn')?.classList.remove('bounce');
    void el.closest('.cart-btn')?.offsetWidth;
    el.closest('.cart-btn')?.classList.add('bounce');
  });
};

const openSidebar = () => document.getElementById('cart-sidebar').classList.add('open');
const closeSidebar = () => document.getElementById('cart-sidebar').classList.remove('open');

// ============================================================
// RENDER ORDERS
// ============================================================
const renderOrders = () => {
  const list = document.getElementById('orders-list');
  if (!list) return;

  if (STATE.orders.length === 0) {
    list.innerHTML = `
      <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:24px;box-shadow:var(--shadow)">
        <div style="display:flex;align-items:center;gap:12px;border-bottom:1px solid #f1f5f9;padding-bottom:16px;margin-bottom:20px">
          <div style="background:#2563eb;color:white;padding:8px;border-radius:8px;font-size:20px">ðŸšš</div>
          <div>
            <h2 style="font-size:18px;font-weight:800;text-transform:uppercase">${t('ordersDashboard')}</h2>
            <p style="font-size:12px;color:#9ca3af;margin-top:2px">Track current shipment statuses and active delivery estimates.</p>
          </div>
        </div>
        <div class="empty-state">
          <div class="empty-icon">ðŸ“‹</div>
          <h3>${t('noOrders')}</h3>
          <p>All processed transactions from Buy It Now and won auctions are logged here instantly.</p>
          <div class="empty-actions">
            <button class="btn-primary" onclick="switchTab('home')">Browse Marketplace</button>
          </div>
        </div>
      </div>`;
    return;
  }

  list.innerHTML = `
    <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:24px;box-shadow:var(--shadow)">
      <div style="display:flex;align-items:center;gap:12px;border-bottom:1px solid #f1f5f9;padding-bottom:16px;margin-bottom:20px">
        <div style="background:#2563eb;color:white;padding:8px;border-radius:8px;font-size:20px">ðŸšš</div>
        <div>
          <h2 style="font-size:18px;font-weight:800;text-transform:uppercase">${t('ordersDashboard')}</h2>
          <p style="font-size:12px;color:#9ca3af;margin-top:2px">Track current shipment statuses and active delivery estimates.</p>
        </div>
      </div>
      <div class="orders-list" style="gap:20px">
        ${STATE.orders.map(o => {
          const statusClass = 'status-' + o.status.replace(/ /g, '');
          return `
          <div class="order-card">
            <div class="order-card-head">
              <div class="order-meta-grid">
                <div class="order-meta-item">
                  <span class="order-meta-label">Order Placed</span>
                  <span class="order-meta-value">${o.date_str}</span>
                </div>
                <div class="order-meta-item">
                  <span class="order-meta-label">${t('total')}</span>
                  <span class="order-meta-value mono">${fmt(o.total)}</span>
                </div>
                <div class="order-meta-item">
                  <span class="order-meta-label">Ship To Address</span>
                  <span class="order-meta-value" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${o.address}</span>
                </div>
              </div>
              <div style="text-align:right">
                <span class="order-meta-label">Order ID</span>
                <div class="order-id" style="font-family:monospace">#${o.id}</div>
                <span class="order-status ${statusClass}" style="margin-top:4px;display:inline-block">${o.status}</span>
              </div>
            </div>
            <div class="order-items-list">
              ${(o.items || []).map(item => `
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid #f1f5f9">
                  <div style="display:flex;align-items:center;gap:10px">
                    <img class="order-item-img" src="${item.product.image}" alt="${item.product.title}" referrerpolicy="no-referrer">
                    <div>
                      <div class="order-item-title" style="font-size:12px;font-weight:700;color:#0f1111">${item.product.title}</div>
                      <div style="font-size:10px;color:#9ca3af">Sold by: ${item.product.seller?.name || 'CrownMart'}</div>
                      <div style="font-size:11px;background:#f1f5f9;font-family:monospace;padding:2px 8px;border-radius:3px;display:inline-block;margin-top:3px;font-weight:600">Quantity: ${item.quantity}</div>
                    </div>
                  </div>
                  <div style="text-align:right">
                    <div class="delivery-badge">ðŸšš ${o.status === 'Processing' ? 'Carrier Processing' : 'In Transit'}</div>
                    <div style="font-size:10px;color:#9ca3af;margin-top:4px">Est. delivery: Tomorrow Afternoon</div>
                    <div style="font-size:13px;font-weight:800;font-family:monospace;margin-top:4px">${fmt(item.product.price * item.quantity)}</div>
                  </div>
                </div>`).join('')}
            </div>
          </div>`;
        }).join('')}
      </div>
    </div>`;

  const el = document.getElementById('tab-orders-count');
  if (el) el.textContent = STATE.orders.length;
};

// ============================================================
// RENDER WATCHLIST
// ============================================================
const renderWatchlist = () => {
  const container = document.getElementById('watchlist-content');
  if (!container) return;

  // Load all products first if not loaded
  const allProds = STATE.products;
  const watched  = allProds.filter(p => (STATE.user.watchList || []).includes(p.id));

  if (watched.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-icon">â¤ï¸</div>
        <h3>${t('emptyWatchlist')}</h3>
        <p>${t('emptyWatchlistSub')}</p>
        <div class="empty-actions">
          <button class="btn-primary" onclick="switchTab('home')">Browse Marketplace</button>
        </div>
      </div>`;
    return;
  }

  container.innerHTML = `<div style="display:flex;flex-direction:column;gap:12px">` +
    watched.map(p => {
      const isAuction = p.type === 'auction';
      const bids      = p.bids || [];
      const isWinning = isAuction && bids.length > 0 && bids[0].bidder.includes('(You)');
      const hasBid    = isAuction && bids.some(b => b.bidder.includes('(You)'));
      const countdown = isAuction ? getCountdown(p.end_time) : '';

      let statusHtml = '';
      if (isAuction) {
        if (isWinning) {
          statusHtml = `<span style="font-size:9px;background:#dcfce7;color:#166534;border:1px solid #86efac;padding:2px 8px;border-radius:20px;font-weight:800;text-transform:uppercase">âœ… HIGHEST BIDDER</span>`;
        } else if (hasBid) {
          statusHtml = `<span style="font-size:9px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:2px 8px;border-radius:20px;font-weight:800;text-transform:uppercase;animation:pulse 1s infinite">âš ï¸ OUTBID â€” Act Now!</span>`;
        } else {
          statusHtml = `<span style="font-size:9px;background:#f1f5f9;color:#374151;border:1px solid var(--border);padding:2px 8px;border-radius:20px;font-weight:700;text-transform:uppercase">ðŸ‘ MONITORING</span>`;
        }
      }

      return `
        <div style="background:#f9fafb;border:1px solid var(--border);border-radius:8px;padding:14px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;transition:background .2s">
          <div style="display:flex;align-items:center;gap:12px;min-width:0">
            <img src="${p.image}" alt="${p.title}" referrerpolicy="no-referrer"
                 style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--border);flex-shrink:0">
            <div style="min-width:0">
              <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:3px">
                <span style="font-size:9px;background:#e2e8f0;color:#374151;padding:1px 6px;border-radius:3px;font-weight:600;text-transform:uppercase;font-family:monospace">${p.category}</span>
                ${statusHtml}
              </div>
              <div style="font-size:13px;font-weight:700;color:#0f1111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px">${p.title}</div>
              <div style="font-size:11px;color:#6b7280;margin-top:2px">Seller: <strong>${p.seller.name}</strong></div>
            </div>
          </div>

          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;justify-content:flex-end">
            ${isAuction ? `
              <div style="text-align:right">
                <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600">Time Remaining</div>
                <div style="font-size:12px;font-family:monospace;font-weight:700;color:#dc2626">â± <span class="cd-text" data-endtime="${p.end_time}">${countdown}</span></div>
              </div>` : `
              <div style="text-align:right">
                <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600">Immediate</div>
                <div style="font-size:12px;font-weight:700;color:#10b981">âœ“ In Stock</div>
              </div>`}

            <div style="text-align:right;min-width:90px">
              <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;font-weight:600">${isAuction ? t('currentBid') : 'Price'}</div>
              <div style="font-size:16px;font-weight:900;font-family:monospace;color:#0f1111">${fmt(p.price)}</div>
            </div>

            <div style="display:flex;align-items:center;gap:6px">
              ${isAuction ? `
                <button onclick="quickBid('${p.id}', ${p.price + 10})" style="background:#f0c14b;color:#0f1111;font-weight:800;font-size:11px;padding:6px 10px;border-radius:4px;border:none;cursor:pointer">+${fmt(10)}</button>
                <button onclick="quickBid('${p.id}', ${p.price + 50})" style="background:#10b981;color:white;font-weight:800;font-size:11px;padding:6px 10px;border-radius:4px;border:none;cursor:pointer">+${fmt(50)}</button>
              ` : `
                <button onclick="addToCart(null,'${p.id}')" style="background:#4f46e5;color:white;font-weight:700;font-size:11px;padding:6px 12px;border-radius:4px;border:none;cursor:pointer;display:flex;align-items:center;gap:4px">ðŸ›’ ${t('buyNow')}</button>
              `}
              <button onclick="toggleWatch(null,'${p.id}')" title="Remove from watchlist"
                      style="background:white;border:1px solid var(--border);color:#9ca3af;width:32px;height:32px;border-radius:4px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px">
                ðŸ—‘
              </button>
            </div>
          </div>
        </div>`;
    }).join('') + `</div>`;
};

// ============================================================
// RENDER SELLER PAGE
// ============================================================
const renderSellerPage = () => {
  const container = document.getElementById('seller-content');
  if (!container) return;
  const u = STATE.user;
  const myListings = STATE.products.filter(p => p.seller.name.includes('(You)'));

  if (!u.is_seller) {
    container.innerHTML = `
      <div class="seller-card">
        <p style="color:#374151;margin-bottom:12px">
          ðŸ›’ ${t('notSellerAccountMessage')}
        </p>
        <button class="btn-upgrade-seller" onclick="upgradeSeller()">${t('upgradeSellerBtn')}</button>
      </div>`;
    return;
  }

  const monthRevenue = myListings.reduce((s, p) => s + p.price, 0);
  const myAuctions   = myListings.filter(p => p.type === 'auction').length;

  container.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;align-items:start">

      <!-- Dark sidebar stats panel -->
      <div style="background:#0f1111;color:white;border-radius:12px;padding:24px;box-shadow:0 4px 16px rgba(0,0,0,.3);border:1px solid #1e293b;position:sticky;top:90px">
        <div style="font-size:10px;letter-spacing:.1em;color:#f0c14b;font-family:monospace;text-transform:uppercase;font-weight:700;margin-bottom:4px">CrownMart Seller Center</div>
        <h3 style="font-size:18px;font-weight:800;border-bottom:1px solid #1e293b;padding-bottom:10px;margin-bottom:16px">Sales Metrics Dashboard</h3>
        <div style="margin-bottom:16px">
          <div style="font-size:11px;color:#64748b">Overall Gross Revenue</div>
          <div style="font-size:30px;font-weight:900;font-family:monospace;color:#34d399;margin-top:2px">${fmt(monthRevenue + 18450)}</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;border-top:1px solid #1e293b;padding-top:14px">
          <div>
            <div style="font-size:10px;color:#64748b;text-transform:uppercase">Active Listings</div>
            <div style="font-size:20px;font-weight:800;color:#f0c14b">${myListings.length} items</div>
          </div>
          <div>
            <div style="font-size:10px;color:#64748b;text-transform:uppercase">Seller Rating</div>
            <div style="font-size:20px;font-weight:800;color:#60a5fa">100% â­</div>
          </div>
        </div>
        <div style="margin-top:16px;background:#1e293b;border-radius:6px;padding:10px;font-size:11px;color:#94a3b8;line-height:1.6">
          â„¹ï¸ As an authorized merchant, you can list standard products or high-end collectibles with live timed bidding. Items publish instantly.
        </div>
      </div>

      <!-- Listing form -->
      <div class="seller-card" style="margin-bottom:0">
        <h3>âž• New Merchant Listing Intake</h3>
        <form id="listing-form" onsubmit="submitListing(event)">
          <div class="form-group">
            <label>${t('productTitle')}</label>
            <input type="text" id="new-title" class="form-input"
                   placeholder="e.g. Unopened 1999 PokÃ©mon Card Pack / Sony WH-1000XM5 Premium Headphones" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Catalog Category</label>
              <select id="new-category" class="form-select">
                <option value="Electronics">Electronics</option>
                <option value="Fashion">Fashion</option>
                <option value="Collectibles">Collectibles</option>
                <option value="Home &amp; Kitchen">Home &amp; Kitchen</option>
                <option value="Sports &amp; Outdoors">Sports &amp; Outdoors</option>
              </select>
            </div>
            <div class="form-group">
              <label>${t('selectImageCategory')}</label>
              <select id="new-image-cat" class="form-select">
                <option value="Electronics">Electronics (Modern Gadgets)</option>
                <option value="Collectibles">Collectibles (Retro Nostalgia)</option>
                <option value="Fashion">Fashion (Shoes / Shirts)</option>
                <option value="Home &amp; Kitchen">Home &amp; Kitchen</option>
              </select>
            </div>
          </div>

          <!-- Listing Type Radio Buttons -->
          <div class="form-group">
            <label>Listing Format</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px">
              <label id="lbl-fixed" style="border:2px solid #0284c7;background:#eff6ff;border-radius:8px;padding:12px;display:flex;gap:10px;align-items:flex-start;cursor:pointer;transition:all .2s">
                <input type="radio" name="listFormat" value="fixed" checked onchange="toggleListingFields(this.value)"
                       style="margin-top:3px;accent-color:#0284c7;cursor:pointer">
                <div>
                  <strong style="font-size:13px;display:block;color:#0f1111">ðŸ“¦ Buy It Now (Retail)</strong>
                  <span style="font-size:11px;color:#6b7280;margin-top:3px;display:block;line-height:1.5">Set a direct retail price. Customers buy instantly via cart.</span>
                </div>
              </label>
              <label id="lbl-auction" style="border:2px solid #e2e8f0;background:#f9fafb;border-radius:8px;padding:12px;display:flex;gap:10px;align-items:flex-start;cursor:pointer;transition:all .2s">
                <input type="radio" name="listFormat" value="auction" onchange="toggleListingFields(this.value)"
                       style="margin-top:3px;accent-color:#f59e0b;cursor:pointer">
                <div>
                  <strong style="font-size:13px;display:block;color:#0f1111">â³ Live Auction (Timed Bidding)</strong>
                  <span style="font-size:11px;color:#6b7280;margin-top:3px;display:block;line-height:1.5">Set a starting price and active timer. Users place competitive bids.</span>
                </div>
              </label>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label id="price-label">Direct Buy Retail Price ($)</label>
              <input type="number" id="new-price" class="form-input" placeholder="199.99" min="0.01" step="0.01" required style="font-family:monospace">
            </div>
            <div class="form-group" id="stock-group">
              <label>Quantity Stock Available</label>
              <input type="number" id="new-stock" class="form-input" value="5" min="1" style="font-family:monospace">
            </div>
            <div class="form-group" id="hours-group" style="display:none">
              <label>Auction Duration</label>
              <select id="new-hours" class="form-select">
                <option value="1">1 Hour (Slam Bid!)</option>
                <option value="12">12 Hours</option>
                <option value="24" selected>24 Hours (1 Day)</option>
                <option value="72">72 Hours (3 Days)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>${t('description')}</label>
            <textarea id="new-desc" class="form-textarea"
                      placeholder="Enter thorough specifications, packaging conditions, certificates of authenticity, and physical attributes to increase buyer conversion."
                      required></textarea>
          </div>
          <button type="submit" class="btn-submit">ðŸš€ Publish Interactive Listing Instantly</button>
        </form>
      </div>
    </div>

    <!-- My Listings below -->
    <div class="seller-card" style="margin-top:24px">
      <h3>ðŸ“¦ ${t('yourActiveListings')}</h3>
      ${myListings.length === 0
        ? `<p style="color:#9ca3af">${t('noListingsYet')}</p>`
        : `<div class="product-grid" style="margin-top:12px">${myListings.map(p => renderProductCard(p)).join('')}</div>`
      }
    </div>`;
};

const toggleListingFields = (type) => {
  if (!type) type = document.querySelector('input[name="listFormat"]:checked')?.value || 'fixed';
  document.getElementById('stock-group').style.display  = type === 'fixed'   ? '' : 'none';
  document.getElementById('hours-group').style.display  = type === 'auction' ? '' : 'none';
  const lblFixed   = document.getElementById('lbl-fixed');
  const lblAuction = document.getElementById('lbl-auction');
  const priceLabel = document.getElementById('price-label');
  if (lblFixed) {
    lblFixed.style.border   = type === 'fixed'   ? '2px solid #0284c7' : '2px solid #e2e8f0';
    lblFixed.style.background = type === 'fixed' ? '#eff6ff' : '#f9fafb';
  }
  if (lblAuction) {
    lblAuction.style.border   = type === 'auction'   ? '2px solid #f59e0b' : '2px solid #e2e8f0';
    lblAuction.style.background = type === 'auction' ? '#fffbeb' : '#f9fafb';
  }
  if (priceLabel) priceLabel.textContent = type === 'fixed' ? 'Direct Buy Retail Price ($)' : 'Starting Floor Bid Price ($)';
};

const upgradeSeller = async () => {
  const res = await api('api/user.php', {
    method: 'POST',
    body: JSON.stringify({ action: 'upgrade_seller' }),
  });
  if (res.error) return showToast(res.error, 'error');
  await loadUser();
  showToast('You are now a seller!');
  renderSellerPage();
};

const submitListing = async (event) => {
  event.preventDefault();
  if (!isLoggedIn()) {
    triggerLoginModal('Sign in as a Seller to list your products.');
    return;
  }
  const type = document.querySelector('input[name="listFormat"]:checked')?.value || 'fixed';
  const res = await api('api/products.php', {
    method: 'POST',
    body: JSON.stringify({
      title:       document.getElementById('new-title').value,
      description: document.getElementById('new-desc').value,
      price:       document.getElementById('new-price').value,
      category:    document.getElementById('new-category').value,
      type,
      stock:       document.getElementById('new-stock')?.value || '5',
      hours:       document.getElementById('new-hours')?.value || '24',
      sellerName:  STATE.user.name,
    }),
  });
  if (res.error) return showToast(res.error, 'error');
  showToast(t('listingPublishedSuccess'));
  event.target.reset();
  await loadProducts();
  switchTab('home');
};

// ============================================================
// NAVIGATION & FILTERS
// ============================================================
const switchTab = (tab) => {
  STATE.activeTab = tab;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
  document.querySelectorAll('.tab-content').forEach(tc => tc.classList.toggle('active', tc.id === `tab-${tab}`));
  // Hide hero on non-home tabs
  const hero = document.getElementById('hero-section');
  if (hero) hero.classList.toggle('hero-hidden', tab !== 'home');

  if (tab === 'bids')   { loadProducts().then(renderWatchlist); }
  if (tab === 'orders') { loadOrders(); }
  if (tab === 'seller') { loadProducts().then(renderSellerPage); }
};

const resetFilters = () => {
  STATE.filterCat   = 'All';
  STATE.filterType  = 'all';
  STATE.sortBy      = 'relevance';
  STATE.searchQuery = '';
  const si = document.getElementById('search-input');
  if (si) si.value = '';
  document.querySelectorAll('.cat-btn').forEach(b => b.classList.toggle('active', b.dataset.cat === 'All'));
  document.querySelectorAll('.fmt-btn').forEach(b => b.classList.remove('active-all','active-fixed','active-auction'));
  document.querySelector('.fmt-btn[data-type="all"]')?.classList.add('active-all');
  loadProducts();
};

const updateSectionHeading = () => {
  const el = document.getElementById('product-count');
  if (el) el.textContent = `(Showing ${STATE.products.length} products)`;
  const heading = document.getElementById('section-cat-label');
  if (heading) heading.textContent = (STATE.filterCat === 'All' ? 'All' : STATE.filterCat) + ' Marketplace Catalog';
};

// ============================================================
// HERO BANNER SLIDE
// ============================================================
const setSlide = (idx) => {
  STATE.slideIndex = idx;
  document.querySelectorAll('.hero-slide').forEach((s, i) => s.classList.toggle('active', i === idx));
  document.querySelectorAll('.hero-dot').forEach((d, i) => d.classList.toggle('active', i === idx));
};

const startSlideTimer = () => {
  STATE.slideTimer = setInterval(() => setSlide(STATE.slideIndex === 0 ? 1 : 0), 8000);
};

// ============================================================
// LIVE COUNTDOWNS
// ============================================================
const startCountdowns = () => {
  STATE.countdownTimer = setInterval(() => {
    document.querySelectorAll('.cd-text[data-endtime]').forEach(el => {
      el.textContent = getCountdown(parseInt(el.dataset.endtime));
    });
  }, 1000);
};

// ============================================================
// GEOLOCATION
// ============================================================
const detectLocation = () => {
  const gpsBtn = document.getElementById('gps-btn');
  if (gpsBtn) gpsBtn.textContent = 'â³';

  // Fast IP fallback
  fetch('https://ipapi.co/json/')
    .then(r => r.json())
    .then(d => {
      if (d.city) {
        const label = `${d.city}${d.postal ? ' ' + d.postal : ''}`;
        const addr  = `100 Main St, ${d.city}${d.region_code ? ', ' + d.region_code : ''}${d.postal ? ' ' + d.postal : ''}${d.country_code ? ', ' + d.country_code : ''}`;
        updateLocationUI(label, addr);
      }
    })
    .catch(() => {});

  // Precise GPS override
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.coords.latitude}&lon=${pos.coords.longitude}&zoom=18&addressdetails=1`)
          .then(r => r.json())
          .then(d => {
            const a     = d.address;
            const city  = a.city || a.town || a.village || a.suburb || 'Local City';
            const post  = a.postcode || '';
            const label = `${city}${post ? ' ' + post : ''}`;
            const parts = [a.road, city, a.state, post, a.country].filter(Boolean);
            updateLocationUI(label, parts.join(', '));
          })
          .finally(() => { if (gpsBtn) gpsBtn.textContent = 'ðŸ“ GPS'; });
      },
      () => { if (gpsBtn) gpsBtn.textContent = 'ðŸ“ GPS'; },
      { enableHighAccuracy: true, timeout: 6000 }
    );
  } else {
    if (gpsBtn) gpsBtn.textContent = 'ðŸ“ GPS';
  }
};

const updateLocationUI = (label, addr) => {
  const ll = document.getElementById('location-label');
  const ai = document.getElementById('checkout-address');
  if (ll) ll.textContent = label;
  if (ai) ai.value = addr;
};

// ============================================================
// SUCCESS BANNER
// ============================================================
const showSuccessBanner = (msg) => {
  const existing = document.getElementById('success-banner');
  if (existing) existing.remove();
  const banner = document.createElement('div');
  banner.id        = 'success-banner';
  banner.className = 'success-banner';
  banner.innerHTML = `âœ… ${msg}`;
  document.querySelector('header.site-header').insertAdjacentElement('afterend', banner);
  setTimeout(() => banner.remove(), 5000);
};

// ============================================================
// LANGUAGE & CURRENCY
// ============================================================
const changeLang = (val) => {
  STATE.lang = val;
  localStorage.setItem('cm_lang', val);
  loadProducts();
  renderUserUI();
};

const changeCurrency = (val) => {
  STATE.currency = val;
  localStorage.setItem('cm_currency', val);
  renderUserUI();
  loadProducts();
};

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', async () => {
  // Set saved lang/currency selectors
  const ls = document.getElementById('lang-select');
  const cs = document.getElementById('currency-select');
  if (ls) ls.value = STATE.lang;
  if (cs) cs.value = STATE.currency;

  // Load everything
  await Promise.all([loadUser(), loadProducts(), loadCart(), loadOrders()]);

  // Activate home tab
  switchTab('home');

  // Start hero slider & countdowns
  setSlide(0);
  startSlideTimer();
  startCountdowns();

  // Auto detect location
  detectLocation();

  // Event: search input
  document.getElementById('search-input')?.addEventListener('input', (e) => {
    STATE.searchQuery = e.target.value;
    // Hide hero when searching (like React original)
    const hero = document.getElementById('hero-section');
    if (hero) hero.classList.toggle('hero-hidden', !!e.target.value);
    clearTimeout(STATE._searchTimer);
    STATE._searchTimer = setTimeout(loadProducts, 300);
  });

  // Event: search btn
  document.getElementById('search-btn')?.addEventListener('click', loadProducts);

  // Event: category in search bar
  document.getElementById('search-cat-select')?.addEventListener('change', (e) => {
    STATE.filterCat = e.target.value;
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.toggle('active', b.dataset.cat === STATE.filterCat));
    loadProducts();
  });

  // Event: close modal on backdrop click
  document.getElementById('modal-overlay')?.addEventListener('click', (e) => {
    if (e.target.id === 'modal-overlay') closeModal();
  });

  // Event: sort
  document.getElementById('sort-select')?.addEventListener('change', (e) => {
    STATE.sortBy = e.target.value;
    loadProducts();
  });
});

