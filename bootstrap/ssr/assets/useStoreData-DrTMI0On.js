import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
function useStoreData() {
  const page = usePage();
  const footer = computed(() => page.props.footer);
  const storeSettings = computed(() => page.props.storeSettings);
  const appName = computed(() => page.props.storeSettings?.store?.name ?? page.props.appName ?? "Store");
  const wishlistCount = computed(() => page.props.wishlistCount ?? 0);
  const wishlistItems = computed(() => page.props.wishlistItems ?? []);
  const cartCount = computed(() => page.props.cartCount ?? 0);
  const cartItems = computed(() => page.props.cartItems ?? []);
  const authCustomer = computed(() => page.props.auth?.customer ?? null);
  const isLoggedIn = computed(() => authCustomer.value !== null);
  const storeEmail = computed(() => footer.value?.store?.email ?? "hello@ogitu.id");
  const storePhone = computed(() => footer.value?.store?.phone ?? "+62 812 3456 7890");
  const storeDescription = computed(() => footer.value?.store?.description ?? "Temukan produk pilihan berkualitas tinggi dengan harga terbaik.");
  const storeLogo = computed(() => storeSettings.value?.branding?.logo ?? null);
  const storeFavicon = computed(() => storeSettings.value?.branding?.favicon ?? null);
  const primaryColor = computed(() => storeSettings.value?.branding?.primary_color ?? "#0ea5e9");
  const secondaryColor = computed(() => storeSettings.value?.branding?.secondary_color ?? "#111827");
  const storeTagline = computed(() => storeSettings.value?.branding?.tagline ?? footer.value?.store?.tagline ?? null);
  const seoMetaTitle = computed(() => storeSettings.value?.seo?.meta_title ?? null);
  const seoMetaDescription = computed(() => storeSettings.value?.seo?.meta_description ?? null);
  const seoMetaKeywords = computed(() => storeSettings.value?.seo?.meta_keywords ?? []);
  const seoOgImage = computed(() => storeSettings.value?.seo?.og_image ?? null);
  const currency = computed(() => storeSettings.value?.preferences?.currency ?? "IDR");
  const language = computed(() => storeSettings.value?.preferences?.language ?? "id");
  const topbarEnabled = computed(() => storeSettings.value?.topbar?.enabled ?? true);
  const topbarMessage = computed(() => storeSettings.value?.topbar?.message ?? null);
  const topbarCtaLabel = computed(() => storeSettings.value?.topbar?.cta_label ?? null);
  const topbarCtaUrl = computed(() => storeSettings.value?.topbar?.cta_url ?? null);
  const homeCta = computed(() => storeSettings.value?.home_cta ?? {
    badge_label: "Koleksi Eksklusif",
    heading_main: "Kesehatan & Kecantikan",
    heading_gradient: "Tanpa Batas",
    description: "Masuk ke ekosistem wellness kami. Temukan produk revolusioner yang dirancang khusus untuk meningkatkan kualitas hidup Anda.",
    primary_cta_label: "Jelajahi Produk",
    primary_cta_url: "/shop",
    secondary_cta_label: "Konsultasi Gratis",
    secondary_cta_url: null,
    floating_badge1_label: "Terverifikasi",
    floating_badge1_value: "BPOM & Halal",
    floating_badge2_label: "Terlaris",
    floating_badge2_value: "100K+ Terjual",
    features: [
      { icon: "i-lucide-award", label: "Eksklusif", description: "Produk premium terakurasi" },
      { icon: "i-lucide-percent", label: "Hemat", description: "Diskon member hingga 30%" },
      { icon: "i-lucide-clock", label: "Terbatas", description: "Penawaran kilat mingguan" }
    ]
  });
  const featureHighlights = computed(() => storeSettings.value?.features?.highlights ?? [
    { icon: "i-lucide-truck", title: "Gratis Ongkir", description: "Untuk pembelian di atas Rp 150k" },
    { icon: "i-lucide-shield-check", title: "Pembayaran Aman", description: "Transaksi terenkripsi 100%" },
    { icon: "i-lucide-headset", title: "Support 24/7", description: "Tim kami siap membantu Anda" },
    { icon: "i-lucide-rotate-ccw", title: "Easy Returns", description: "Pengembalian gratis 30 hari" }
  ]);
  const affiliateCta = computed(() => storeSettings.value?.affiliate_cta ?? {
    badge_label: "Entrepreneurship Program",
    heading_main: "Bangun Kerajaan",
    heading_sub: "Bisnis Anda Sendiri",
    description: "Bukan sekadar belanja, ini adalah peluang kemitraan. Manfaatkan sistem pemasaran jaringan kami yang sudah teruji untuk meraih kebebasan finansial dan waktu.",
    stat1_title: "Penghasilan Tanpa Batas",
    stat1_description: "Dapatkan profit retail dan bonus jaringan setiap hari. Sistem bagi hasil yang transparan dan otomatis masuk ke wallet Anda.",
    stat2_value: "75%",
    stat2_label: "Payout Ratio",
    stat3_value: "100+",
    stat3_label: "Kota Terjangkau",
    floating_label: "Target BV Reward",
    floating_value: "Raih Expander 2025",
    primary_cta_label: "Gabung Sekarang",
    primary_cta_url: "/register",
    secondary_cta_label: "Pelajari Sistem",
    secondary_cta_url: null,
    footer_note: "* Syarat dan ketentuan berlaku. BV (Business Volume) dihitung otomatis per transaksi.",
    benefits: [
      { icon: "i-lucide-badge-percent", label: "Komisi Langsung", value: "20%", description: "Komisi retail langsung terhitung otomatis setiap transaksi tervalidasi." },
      { icon: "i-lucide-users-2", label: "Bonus Jaringan", value: "Unlimited", description: "Bangun jaringan mitra tanpa batas wilayah dengan perhitungan bonus real-time." },
      { icon: "i-lucide-trophy", label: "Reward Mewah", value: "Umroh/Mobil", description: "Capai milestone penjualan untuk membuka reward eksklusif bertingkat." }
    ]
  });
  const socialLinks = computed(() => {
    const socialIconMap = {
      instagram: "i-lucide-instagram",
      youtube: "i-lucide-youtube",
      tiktok: "i-lucide-music",
      facebook: "i-lucide-facebook",
      x: "i-lucide-twitter",
      whatsapp: "i-lucide-message-circle"
    };
    const socialLabelMap = {
      instagram: "Instagram",
      youtube: "YouTube",
      tiktok: "TikTok",
      facebook: "Facebook",
      x: "X",
      whatsapp: "WhatsApp"
    };
    return Object.entries(footer.value?.socialLinks ?? {}).map(([key, url]) => ({
      label: socialLabelMap[key] ?? key,
      icon: socialIconMap[key] ?? "i-lucide-link",
      to: url
    }));
  });
  const mapPageLinks = (pages) => pages.filter((page2) => page2.slug?.trim() !== "").map((page2) => ({
    label: page2.title,
    to: `/page/${page2.slug}`
  }));
  const uniqueLinksByUrl = (links) => {
    const seen = /* @__PURE__ */ new Set();
    return links.filter((link) => {
      if (seen.has(link.to)) {
        return false;
      }
      seen.add(link.to);
      return true;
    });
  };
  const footerMainPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.pages ?? [])));
  const headerTopBarPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.headerTopBarPages ?? [])));
  const headerNavbarPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.headerNavbarPages ?? [])));
  const headerBottomBarPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.headerBottomBarPages ?? [])));
  const bottomMainPages = computed(() => uniqueLinksByUrl(mapPageLinks(footer.value?.bottomMainPages ?? [])));
  return {
    footer,
    storeSettings,
    appName,
    wishlistCount,
    wishlistItems,
    cartCount,
    cartItems,
    authCustomer,
    isLoggedIn,
    storeEmail,
    storePhone,
    storeDescription,
    storeTagline,
    socialLinks,
    footerMainPages,
    headerTopBarPages,
    headerNavbarPages,
    headerBottomBarPages,
    bottomMainPages,
    paymentMethods: computed(() => footer.value?.paymentMethods ?? []),
    // Branding
    storeLogo,
    storeFavicon,
    primaryColor,
    secondaryColor,
    // SEO defaults
    seoMetaTitle,
    seoMetaDescription,
    seoMetaKeywords,
    seoOgImage,
    // Preferences
    currency,
    language,
    // Topbar
    topbarEnabled,
    topbarMessage,
    topbarCtaLabel,
    topbarCtaUrl,
    // Home CTA
    homeCta,
    // Feature Highlights
    featureHighlights,
    // Affiliate CTA
    affiliateCta
  };
}
export {
  useStoreData as u
};
