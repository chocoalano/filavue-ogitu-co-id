import { computed, ref, watch } from "vue";
function resolveMediaUrl(url) {
  if (!url) return null;
  if (url.startsWith("http://") || url.startsWith("https://") || url.startsWith("//") || url.startsWith("/")) return url;
  return `/storage/${url}`;
}
function formatCurrency(val) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0
  }).format(val);
}
function starsArray(rating) {
  const full = Math.floor(rating);
  return Array.from({ length: 5 }, (_, i) => i < full);
}
function useProductDetail(product) {
  const variants = computed(() => product().variants ?? []);
  const selectedVariantId = ref(variants.value[0]?.id ?? null);
  watch(
    () => variants.value,
    (v) => {
      if (!selectedVariantId.value && v?.length) selectedVariantId.value = v[0].id;
    },
    { immediate: true }
  );
  const selectedVariant = computed(() => {
    if (!variants.value.length) return null;
    return variants.value.find((v) => v.id === selectedVariantId.value) ?? variants.value[0];
  });
  const price = computed(() => Number(selectedVariant.value?.price ?? product().priceFrom ?? 0));
  const compareAtPrice = computed(() => {
    const v = selectedVariant.value?.compareAtPrice;
    return v != null ? Number(v) : null;
  });
  const inStock = computed(() => selectedVariant.value?.inStock ?? true);
  const discountPercent = computed(() => {
    if (!compareAtPrice.value || compareAtPrice.value <= price.value) return null;
    return Math.round((compareAtPrice.value - price.value) / compareAtPrice.value * 100);
  });
  const galleryItems = computed(() => {
    const p = product();
    const base = (p.media ?? []).map((m) => ({
      src: resolveMediaUrl(m.url) ?? "",
      alt: m.alt ?? p.name
    }));
    const selectedVariantMedia = (selectedVariant.value?.media ?? []).map((m) => ({
      src: resolveMediaUrl(m.url) ?? "",
      alt: m.alt ?? selectedVariant.value?.name ?? p.name
    }));
    const allVariantMedia = variants.value.flatMap(
      (variant) => (variant.media ?? []).map((media) => ({
        src: resolveMediaUrl(media.url) ?? "",
        alt: media.alt ?? variant.name ?? p.name
      }))
    );
    const merged = [...selectedVariantMedia, ...allVariantMedia, ...base];
    const seen = /* @__PURE__ */ new Set();
    return merged.filter((x) => x.src && (seen.has(x.src) ? false : (seen.add(x.src), true)));
  });
  const activeImage = ref(0);
  watch(galleryItems, () => activeImage.value = 0);
  const avgRating = computed(() => Number(product().rating ?? 0));
  const reviewCount = computed(() => product().reviewsCount ?? 0);
  return {
    variants,
    selectedVariantId,
    selectedVariant,
    price,
    compareAtPrice,
    inStock,
    discountPercent,
    galleryItems,
    activeImage,
    avgRating,
    reviewCount
  };
}
export {
  formatCurrency as f,
  starsArray as s,
  useProductDetail as u
};
