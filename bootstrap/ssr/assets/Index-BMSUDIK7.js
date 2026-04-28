import { _ as _sfc_main$e, a as _sfc_main$f, b as _sfc_main$g } from "./Page-ws952gfV.js";
import { _ as _sfc_main$7 } from "./Badge-DqskWDDq.js";
import { computed, defineComponent, reactive, mergeProps, withCtx, unref, openBlock, createBlock, Fragment, renderList, createVNode, toDisplayString, createCommentVNode, useSSRContext, ref, onMounted, watch, createTextVNode, isRef } from "vue";
import { ssrRenderComponent, ssrRenderList, ssrRenderAttr, ssrInterpolate, ssrRenderClass, ssrIncludeBooleanAttr, ssrRenderAttrs } from "vue/server-renderer";
import { a as _sfc_main$d } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$6 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$5 } from "./Card-CvchAxCK.js";
import { usePage, router } from "@inertiajs/vue3";
import { _ as _sfc_main$c } from "./Select-Dl3Zaa-r.js";
import { _ as _sfc_main$b } from "./Textarea-DFfHqaZw.js";
import { _ as _sfc_main$a } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$9 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$8 } from "./Button-DLZCZWnW.js";
import "reka-ui";
import "@vueuse/core";
import "./useToast-CTuSIf9f.js";
import "./usePortal-EQErrF6h.js";
import "./useStoreData-DrTMI0On.js";
import "./Separator-CuyFd3xv.js";
import "defu";
import "reka-ui/namespaced";
import "./Checkbox-PZ4-MUog.js";
import "vaul-vue";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "ufo";
function useCheckout() {
  const page = usePage();
  const items = computed(() => page.props.items ?? []);
  const cart = computed(() => page.props.cart ?? null);
  const addresses = computed(() => page.props.addresses ?? []);
  const pickup = computed(() => page.props.pickup ?? null);
  const saldo = computed(() => page.props.saldo ?? 0);
  const midtrans = computed(() => page.props.midtrans);
  const itemCount = computed(() => items.value.reduce((acc, it) => acc + it.qty, 0));
  function formatIDR(n) {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      maximumFractionDigits: 0
    }).format(n);
  }
  return { items, cart, addresses, pickup, saldo, midtrans, itemCount, formatIDR };
}
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "CheckoutItemsList",
  __ssrInlineRender: true,
  props: {
    items: {},
    cart: {}
  },
  setup(__props) {
    const { formatIDR } = useCheckout();
    const imgErrors = reactive({});
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$5;
      const _component_UBadge = _sfc_main$7;
      const _component_UIcon = _sfc_main$6;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Produk yang dibeli</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Pastikan varian, jumlah, dan total sudah sesuai sebelum membayar. </p></div>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              label: `${__props.items.length} produk`,
              color: "neutral",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Produk yang dibeli"),
                  createVNode("p", { class: "mt-1 text-sm text-gray-500 dark:text-gray-400" }, " Pastikan varian, jumlah, dan total sudah sesuai sebelum membayar. ")
                ]),
                createVNode(_component_UBadge, {
                  label: `${__props.items.length} produk`,
                  color: "neutral",
                  variant: "soft",
                  class: "rounded-full"
                }, null, 8, ["label"])
              ])
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between text-sm"${_scopeId}><span class="text-gray-500 dark:text-gray-400"${_scopeId}>Total qty</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.items.reduce((acc, it) => acc + it.qty, 0))} item </span></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between text-sm" }, [
                createVNode("span", { class: "text-gray-500 dark:text-gray-400" }, "Total qty"),
                createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.items.reduce((acc, it) => acc + it.qty, 0)) + " item ", 1)
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (__props.items.length === 0) {
              _push2(`<div class="rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"${_scopeId}> Tidak ada item untuk checkout. </div>`);
            } else {
              _push2(`<div class="space-y-3"${_scopeId}><!--[-->`);
              ssrRenderList(__props.items, (it) => {
                _push2(`<div class="flex w-full items-center gap-3 rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><div class="size-12 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"${_scopeId}>`);
                if (it.image && !imgErrors[it.id]) {
                  _push2(`<img${ssrRenderAttr("src", it.image)}${ssrRenderAttr("alt", it.name)} class="h-full w-full object-cover"${_scopeId}>`);
                } else {
                  _push2(`<div class="grid h-full w-full place-items-center"${_scopeId}>`);
                  _push2(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-image",
                    class: "size-5 text-gray-400"
                  }, null, _parent2, _scopeId));
                  _push2(`</div>`);
                }
                _push2(`</div><div class="min-w-0 flex-1"${_scopeId}><p class="truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(it.name)}</p>`);
                if (it.variant) {
                  _push2(`<p class="truncate text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(it.variant)}</p>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`<p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(unref(formatIDR)(it.price))} × ${ssrInterpolate(it.qty)}</p></div><div class="shrink-0 text-right"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Total</p><p class="whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(it.row_total))}</p></div></div>`);
              });
              _push2(`<!--]--></div>`);
            }
          } else {
            return [
              __props.items.length === 0 ? (openBlock(), createBlock("div", {
                key: 0,
                class: "rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"
              }, " Tidak ada item untuk checkout. ")) : (openBlock(), createBlock("div", {
                key: 1,
                class: "space-y-3"
              }, [
                (openBlock(true), createBlock(Fragment, null, renderList(__props.items, (it) => {
                  return openBlock(), createBlock("div", {
                    key: it.id,
                    class: "flex w-full items-center gap-3 rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"
                  }, [
                    createVNode("div", { class: "size-12 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900" }, [
                      it.image && !imgErrors[it.id] ? (openBlock(), createBlock("img", {
                        key: 0,
                        src: it.image,
                        alt: it.name,
                        class: "h-full w-full object-cover",
                        onError: ($event) => imgErrors[it.id] = true
                      }, null, 40, ["src", "alt", "onError"])) : (openBlock(), createBlock("div", {
                        key: 1,
                        class: "grid h-full w-full place-items-center"
                      }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-image",
                          class: "size-5 text-gray-400"
                        })
                      ]))
                    ]),
                    createVNode("div", { class: "min-w-0 flex-1" }, [
                      createVNode("p", { class: "truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(it.name), 1),
                      it.variant ? (openBlock(), createBlock("p", {
                        key: 0,
                        class: "truncate text-xs text-gray-500 dark:text-gray-400"
                      }, toDisplayString(it.variant), 1)) : createCommentVNode("", true),
                      createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(unref(formatIDR)(it.price)) + " × " + toDisplayString(it.qty), 1)
                    ]),
                    createVNode("div", { class: "shrink-0 text-right" }, [
                      createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Total"),
                      createVNode("p", { class: "whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(it.row_total)), 1)
                    ])
                  ]);
                }), 128))
              ]))
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/checkout/CheckoutItemsList.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
function hasPickupData(location) {
  if (!location) {
    return false;
  }
  return [location.address_line, location.city, location.province].some((part) => String(part ?? "").trim() !== "");
}
function useCheckoutAddress(savedAddresses, pickupLocation = null) {
  const hasPickupLocation = computed(() => hasPickupData(pickupLocation));
  const addressMode = ref(savedAddresses.length ? "saved" : hasPickupLocation.value ? "pickup" : "manual");
  const selectedAddressId = ref(null);
  const recipientName = ref("");
  const phone = ref("");
  const addressLine = ref("");
  const postalCode = ref("");
  const notes = ref("");
  const provinces = ref([]);
  const cities = ref([]);
  const districts = ref([]);
  const selectedProvince = ref("");
  const selectedCity = ref("");
  const selectedDistrict = ref("");
  const isLoadingProvinces = ref(false);
  const isLoadingCities = ref(false);
  const isLoadingDistricts = ref(false);
  const shippingRates = ref([]);
  const selectedRate = ref(null);
  const isLoadingRates = ref(false);
  const shippingError = ref(null);
  onMounted(() => {
    const defaultAddress = savedAddresses.find((a) => a.is_default) ?? savedAddresses[0];
    if (defaultAddress) {
      selectedAddressId.value = defaultAddress.id;
    }
    loadProvinces();
  });
  async function loadProvinces() {
    isLoadingProvinces.value = true;
    try {
      const res = await fetch("/checkout/shipping/provinces");
      if (!res.ok) return;
      provinces.value = await res.json();
    } catch {
    } finally {
      isLoadingProvinces.value = false;
    }
  }
  async function loadCities(province) {
    cities.value = [];
    districts.value = [];
    selectedCity.value = "";
    selectedDistrict.value = "";
    if (!province) return;
    isLoadingCities.value = true;
    try {
      const res = await fetch(`/checkout/shipping/cities?province=${encodeURIComponent(province)}`);
      if (!res.ok) return;
      cities.value = await res.json();
    } catch {
    } finally {
      isLoadingCities.value = false;
    }
  }
  async function loadDistricts(province, city) {
    districts.value = [];
    selectedDistrict.value = "";
    if (!province || !city) return;
    isLoadingDistricts.value = true;
    try {
      const url = `/checkout/shipping/districts?province=${encodeURIComponent(province)}&city=${encodeURIComponent(city)}`;
      const res = await fetch(url);
      if (!res.ok) return;
      console.log(res.ok);
      districts.value = await res.json();
    } catch {
    } finally {
      isLoadingDistricts.value = false;
    }
  }
  async function loadShippingRates(province, city, district) {
    shippingRates.value = [];
    selectedRate.value = null;
    shippingError.value = null;
    if (!province || !city) return;
    isLoadingRates.value = true;
    try {
      const params = new URLSearchParams({ province, city });
      if (district) params.set("district", district);
      const res = await fetch(`/checkout/shipping/cost?${params}`);
      if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        shippingError.value = data?.message ?? "Tujuan pengiriman tidak tersedia.";
        return;
      }
      shippingRates.value = await res.json();
    } catch {
      shippingError.value = "Gagal memuat tarif ongkir.";
    } finally {
      isLoadingRates.value = false;
    }
  }
  watch(selectedProvince, (val) => {
    loadCities(val);
  });
  watch(selectedCity, async (val) => {
    if (val && selectedProvince.value) {
      await loadDistricts(selectedProvince.value, val);
      if (addressMode.value === "manual") {
        await loadShippingRates(selectedProvince.value, val);
      }
    } else {
      shippingRates.value = [];
      selectedRate.value = null;
    }
  });
  watch(selectedDistrict, (district) => {
    if (addressMode.value === "manual" && selectedProvince.value && selectedCity.value) {
      loadShippingRates(selectedProvince.value, selectedCity.value, district || void 0);
    }
  });
  watch(selectedAddressId, (id) => {
    if (addressMode.value !== "saved" || !id) return;
    const address = savedAddresses.find((a) => a.id === id);
    if (address?.province && address?.city) {
      loadShippingRates(address.province, address.city);
    }
  });
  watch(addressMode, () => {
    shippingRates.value = [];
    selectedRate.value = null;
    shippingError.value = null;
    if (addressMode.value === "saved" && selectedAddressId.value) {
      const address = savedAddresses.find((a) => a.id === selectedAddressId.value);
      if (address?.province && address?.city) {
        loadShippingRates(address.province, address.city);
      }
    }
    if (addressMode.value === "manual" && selectedProvince.value && selectedCity.value) {
      loadShippingRates(selectedProvince.value, selectedCity.value, selectedDistrict.value || void 0);
    }
  });
  const selectedAddress = computed(() => {
    if (!selectedAddressId.value) return null;
    return savedAddresses.find((a) => a.id === selectedAddressId.value) ?? null;
  });
  const addressPayload = computed(() => {
    if (addressMode.value === "pickup") {
      if (!hasPickupLocation.value) {
        return null;
      }
      return { address_mode: "pickup" };
    }
    if (addressMode.value === "saved") {
      if (!selectedAddress.value) return null;
      return { address_mode: "saved", address_id: selectedAddress.value.id };
    }
    return {
      address_mode: "manual",
      recipient_name: recipientName.value.trim(),
      phone: phone.value.trim(),
      address_line: addressLine.value.trim(),
      province: selectedProvince.value,
      city: selectedCity.value,
      district: selectedDistrict.value.trim(),
      postal_code: postalCode.value.trim(),
      notes: notes.value.trim()
    };
  });
  const isAddressValid = computed(() => {
    if (addressMode.value === "pickup") {
      return hasPickupLocation.value;
    }
    if (addressMode.value === "saved") return !!selectedAddress.value;
    const p = addressPayload.value;
    if (!p || p.address_mode !== "manual") return false;
    return !!(p.recipient_name && p.phone && p.address_line && p.province && p.city && p.postal_code);
  });
  return {
    addressMode,
    selectedAddressId,
    selectedAddress,
    addressPayload,
    isAddressValid,
    recipientName,
    phone,
    addressLine,
    postalCode,
    notes,
    provinces,
    cities,
    districts,
    selectedProvince,
    selectedCity,
    selectedDistrict,
    isLoadingProvinces,
    isLoadingCities,
    isLoadingDistricts,
    shippingRates,
    selectedRate,
    isLoadingRates,
    shippingError,
    pickupLocation,
    hasPickupLocation
  };
}
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "CheckoutAddressPanel",
  __ssrInlineRender: true,
  props: {
    addresses: {},
    pickupLocation: {},
    shippingFee: {}
  },
  emits: ["update:payload", "update:isValid", "update:rate"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const {
      addressMode,
      selectedAddressId,
      selectedAddress,
      addressPayload,
      isAddressValid,
      recipientName,
      phone,
      addressLine,
      postalCode,
      notes,
      provinces,
      cities,
      districts,
      selectedProvince,
      selectedCity,
      selectedDistrict,
      isLoadingProvinces,
      isLoadingCities,
      isLoadingDistricts,
      shippingRates,
      selectedRate,
      isLoadingRates,
      shippingError,
      hasPickupLocation
    } = useCheckoutAddress(props.addresses, props.pickupLocation);
    watch(addressPayload, (val) => emit("update:payload", val), { immediate: true });
    watch(isAddressValid, (val) => emit("update:isValid", val), { immediate: true });
    watch(selectedRate, (val) => emit("update:rate", val), { immediate: true });
    function formatIDR(n) {
      return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);
    }
    const addressModeGuide = computed(() => {
      if (addressMode.value === "saved") {
        return {
          color: "primary",
          icon: "i-lucide-book-user",
          title: "Checkout pakai alamat tersimpan",
          description: "Pilih satu alamat yang sudah ada. Data alamat terpilih dipakai untuk penerima, tujuan kirim, dan hitung ongkir.",
          rules: [
            "Pilih satu alamat yang paling sesuai dengan tujuan pengiriman.",
            "Pastikan nama penerima, nomor HP, kota, provinsi, dan kode pos di alamat tersebut sudah benar.",
            "Kalau alamat tersimpan salah atau belum lengkap, ubah dulu di dashboard atau gunakan mode Manual."
          ],
          summaryLabel: "Data checkout yang dipakai",
          summaryValue: selectedAddress.value ? `${selectedAddress.value.recipient_name} • ${selectedAddress.value.city}, ${selectedAddress.value.province}` : "Belum ada alamat tersimpan yang dipilih."
        };
      }
      if (addressMode.value === "manual") {
        return {
          color: "warning",
          icon: "i-lucide-pencil-line",
          title: "Checkout isi data manual",
          description: "Isi data penerima dan alamat pengiriman langsung di form ini untuk checkout sekarang.",
          rules: [
            "Nama penerima, nomor HP, alamat lengkap, provinsi, kota, dan kode pos wajib diisi.",
            "Gunakan nomor HP aktif agar kurir atau admin mudah menghubungi penerima.",
            "Alamat harus detail: jalan, nomor rumah, RT/RW, patokan, lalu pilih wilayah yang sesuai."
          ],
          summaryLabel: "Data checkout yang dipakai",
          summaryValue: recipientName.value.trim() !== "" ? `${recipientName.value.trim()} • ${selectedCity.value || "Kota belum dipilih"}` : "Form manual ini akan dipakai sebagai data pengiriman pesanan."
        };
      }
      return {
        color: "success",
        icon: "i-lucide-package-check",
        title: "Checkout self pickup",
        description: "Pesanan diambil sendiri di lokasi pickup kantor. Alamat pengiriman tidak perlu diisi dan ongkir gratis.",
        rules: [
          "Pastikan lokasi pickup kantor tersedia sebelum lanjut ke pembayaran.",
          "Produk diambil sendiri oleh pembeli atau perwakilan di lokasi pickup.",
          "Gunakan nomor akun yang aktif agar admin mudah konfirmasi saat pesanan siap diambil."
        ],
        summaryLabel: "Data checkout yang dipakai",
        summaryValue: hasPickupLocation.value && props.pickupLocation ? `${props.pickupLocation.label} • ${props.pickupLocation.city}, ${props.pickupLocation.province}` : "Lokasi self pickup belum tersedia."
      };
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$5;
      const _component_UButton = _sfc_main$8;
      const _component_UIcon = _sfc_main$6;
      const _component_UBadge = _sfc_main$7;
      const _component_UFormField = _sfc_main$9;
      const _component_UInput = _sfc_main$a;
      const _component_UTextarea = _sfc_main$b;
      const _component_USelect = _sfc_main$c;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"${_scopeId}><div class="min-w-0"${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Alamat Pengiriman</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Pilih kirim ke alamat atau ambil langsung di kantor pickup. </p></div><div class="w-full sm:w-auto"${_scopeId}><div class="grid w-full grid-cols-3 rounded-2xl border border-gray-200 bg-white/70 p-1 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              class: "w-full rounded-xl",
              size: "sm",
              color: "neutral",
              variant: unref(addressMode) === "saved" ? "solid" : "ghost",
              onClick: ($event) => addressMode.value = "saved"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Alamat `);
                } else {
                  return [
                    createTextVNode(" Alamat ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              class: "w-full rounded-xl",
              size: "sm",
              color: "neutral",
              variant: unref(addressMode) === "manual" ? "solid" : "ghost",
              onClick: ($event) => addressMode.value = "manual"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Manual `);
                } else {
                  return [
                    createTextVNode(" Manual ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              class: "w-full rounded-xl",
              size: "sm",
              color: "neutral",
              variant: unref(addressMode) === "pickup" ? "solid" : "ghost",
              onClick: ($event) => addressMode.value = "pickup"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Self pickup `);
                } else {
                  return [
                    createTextVNode(" Self pickup ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between" }, [
                createVNode("div", { class: "min-w-0" }, [
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Alamat Pengiriman"),
                  createVNode("p", { class: "mt-1 text-sm text-gray-500 dark:text-gray-400" }, " Pilih kirim ke alamat atau ambil langsung di kantor pickup. ")
                ]),
                createVNode("div", { class: "w-full sm:w-auto" }, [
                  createVNode("div", { class: "grid w-full grid-cols-3 rounded-2xl border border-gray-200 bg-white/70 p-1 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40" }, [
                    createVNode(_component_UButton, {
                      class: "w-full rounded-xl",
                      size: "sm",
                      color: "neutral",
                      variant: unref(addressMode) === "saved" ? "solid" : "ghost",
                      onClick: ($event) => addressMode.value = "saved"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Alamat ")
                      ]),
                      _: 1
                    }, 8, ["variant", "onClick"]),
                    createVNode(_component_UButton, {
                      class: "w-full rounded-xl",
                      size: "sm",
                      color: "neutral",
                      variant: unref(addressMode) === "manual" ? "solid" : "ghost",
                      onClick: ($event) => addressMode.value = "manual"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Manual ")
                      ]),
                      _: 1
                    }, 8, ["variant", "onClick"]),
                    createVNode(_component_UButton, {
                      class: "w-full rounded-xl",
                      size: "sm",
                      color: "neutral",
                      variant: unref(addressMode) === "pickup" ? "solid" : "ghost",
                      onClick: ($event) => addressMode.value = "pickup"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Self pickup ")
                      ]),
                      _: 1
                    }, 8, ["variant", "onClick"])
                  ])
                ])
              ])
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><div class="text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Status alamat: <span class="${ssrRenderClass(unref(isAddressValid) ? "text-emerald-600 dark:text-emerald-400" : "text-rose-600 dark:text-rose-400")}"${_scopeId}>${ssrInterpolate(unref(isAddressValid) ? "Lengkap ✓" : "Belum lengkap")}</span></div><div class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>`);
            if (unref(addressMode) === "pickup") {
              _push2(`<!--[--> Ongkir: <span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Gratis (Self pickup)</span><!--]-->`);
            } else if (unref(selectedRate)) {
              _push2(`<!--[--> Ongkir: <span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formatIDR(unref(selectedRate).total_tariff))}</span> via ${ssrInterpolate(unref(selectedRate).product)}<!--]-->`);
            } else {
              _push2(`<!--[--> Ongkir: <span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formatIDR(__props.shippingFee))}</span><!--]-->`);
            }
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("div", { class: "text-sm text-gray-500 dark:text-gray-400" }, [
                  createTextVNode(" Status alamat: "),
                  createVNode("span", {
                    class: unref(isAddressValid) ? "text-emerald-600 dark:text-emerald-400" : "text-rose-600 dark:text-rose-400"
                  }, toDisplayString(unref(isAddressValid) ? "Lengkap ✓" : "Belum lengkap"), 3)
                ]),
                createVNode("div", { class: "text-xs text-gray-500 dark:text-gray-400" }, [
                  unref(addressMode) === "pickup" ? (openBlock(), createBlock(Fragment, { key: 0 }, [
                    createTextVNode(" Ongkir: "),
                    createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, "Gratis (Self pickup)")
                  ], 64)) : unref(selectedRate) ? (openBlock(), createBlock(Fragment, { key: 1 }, [
                    createTextVNode(" Ongkir: "),
                    createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(formatIDR(unref(selectedRate).total_tariff)), 1),
                    createTextVNode(" via " + toDisplayString(unref(selectedRate).product), 1)
                  ], 64)) : (openBlock(), createBlock(Fragment, { key: 2 }, [
                    createTextVNode(" Ongkir: "),
                    createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(formatIDR(__props.shippingFee)), 1)
                  ], 64))
                ])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="mb-4 rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><div class="flex items-start gap-3"${_scopeId}><div class="${ssrRenderClass([[
              addressModeGuide.value.color === "primary" ? "bg-primary-50 text-primary-600 dark:bg-primary-950/50 dark:text-primary-300" : "",
              addressModeGuide.value.color === "warning" ? "bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300" : "",
              addressModeGuide.value.color === "success" ? "bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300" : ""
            ], "mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl"])}"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: addressModeGuide.value.icon,
              class: "size-4.5"
            }, null, _parent2, _scopeId));
            _push2(`</div><div class="min-w-0 flex-1"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(addressModeGuide.value.title)}</p><p class="mt-1 text-sm text-gray-600 dark:text-gray-300"${_scopeId}>${ssrInterpolate(addressModeGuide.value.description)}</p><div class="mt-3 grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_260px]"${_scopeId}><ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300"${_scopeId}><!--[-->`);
            ssrRenderList(addressModeGuide.value.rules, (rule) => {
              _push2(`<li class="flex items-start gap-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-check",
                class: "mt-0.5 size-4 shrink-0 text-gray-400 dark:text-gray-500"
              }, null, _parent2, _scopeId));
              _push2(`<span${_scopeId}>${ssrInterpolate(rule)}</span></li>`);
            });
            _push2(`<!--]--></ul><div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3 dark:border-gray-800 dark:bg-gray-900/60"${_scopeId}><p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(addressModeGuide.value.summaryLabel)}</p><p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(addressModeGuide.value.summaryValue)}</p></div></div></div></div></div>`);
            if (unref(addressMode) === "saved") {
              _push2(`<div class="space-y-3"${_scopeId}>`);
              if (__props.addresses.length === 0) {
                _push2(`<div class="w-full rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"${_scopeId}> Belum ada alamat tersimpan. Silakan pilih &quot;Isi manual&quot;. </div>`);
              } else {
                _push2(`<div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}><!--[-->`);
                ssrRenderList(__props.addresses, (a) => {
                  _push2(`<button type="button" class="${ssrRenderClass([[
                    unref(selectedAddressId) === a.id ? "border-primary-500 ring-2 ring-primary-500/20" : "border-gray-200 dark:border-gray-800"
                  ], "w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55"])}"${_scopeId}><div class="flex items-start justify-between gap-2"${_scopeId}><div class="min-w-0"${_scopeId}><p class="truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(a.label)} `);
                  if (a.is_default) {
                    _push2(`<span class="ml-2 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>(Default)</span>`);
                  } else {
                    _push2(`<!---->`);
                  }
                  _push2(`</p><p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(a.recipient_name)} • ${ssrInterpolate(a.phone)}</p></div>`);
                  if (unref(selectedAddressId) === a.id) {
                    _push2(ssrRenderComponent(_component_UBadge, {
                      label: "Dipilih",
                      color: "primary",
                      variant: "soft",
                      size: "xs",
                      class: "shrink-0 rounded-full"
                    }, null, _parent2, _scopeId));
                  } else {
                    _push2(`<!---->`);
                  }
                  _push2(`</div><p class="mt-2 line-clamp-2 text-sm text-gray-700 dark:text-gray-200"${_scopeId}>${ssrInterpolate(a.address_line)}, ${ssrInterpolate(a.city)}, ${ssrInterpolate(a.province)}, ${ssrInterpolate(a.postal_code)}</p>`);
                  if (a.description) {
                    _push2(`<p class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Catatan: ${ssrInterpolate(a.description)}</p>`);
                  } else {
                    _push2(`<!---->`);
                  }
                  _push2(`</button>`);
                });
                _push2(`<!--]--></div>`);
              }
              _push2(`<div class="flex items-center justify-between"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Tip: gunakan alamat &quot;Default&quot; untuk checkout lebih cepat. </p>`);
              _push2(ssrRenderComponent(_component_UButton, {
                to: "/dashboard",
                color: "neutral",
                variant: "ghost",
                class: "rounded-xl",
                size: "sm"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Kelola alamat `);
                  } else {
                    return [
                      createTextVNode(" Kelola alamat ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div></div>`);
            } else if (unref(addressMode) === "manual") {
              _push2(`<div class="space-y-4"${_scopeId}><div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Nama penerima",
                required: "",
                class: "w-full",
                help: "Nama ini dipakai sebagai penerima resmi pada data checkout dan pengiriman."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UInput, {
                      modelValue: unref(recipientName),
                      "onUpdate:modelValue": ($event) => isRef(recipientName) ? recipientName.value = $event : null,
                      placeholder: "Nama lengkap",
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UInput, {
                        modelValue: unref(recipientName),
                        "onUpdate:modelValue": ($event) => isRef(recipientName) ? recipientName.value = $event : null,
                        placeholder: "Nama lengkap",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "No. HP",
                required: "",
                class: "w-full",
                help: "Gunakan nomor aktif agar kurir atau admin bisa menghubungi penerima bila diperlukan."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UInput, {
                      modelValue: unref(phone),
                      "onUpdate:modelValue": ($event) => isRef(phone) ? phone.value = $event : null,
                      placeholder: "08xxxxxxxxxx",
                      class: "w-full",
                      inputmode: "tel"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UInput, {
                        modelValue: unref(phone),
                        "onUpdate:modelValue": ($event) => isRef(phone) ? phone.value = $event : null,
                        placeholder: "08xxxxxxxxxx",
                        class: "w-full",
                        inputmode: "tel"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Alamat lengkap",
                required: "",
                class: "w-full",
                help: "Isi selengkap mungkin: jalan, nomor rumah, RT/RW, blok, lantai, dan patokan."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UTextarea, {
                      modelValue: unref(addressLine),
                      "onUpdate:modelValue": ($event) => isRef(addressLine) ? addressLine.value = $event : null,
                      placeholder: "Jalan, RT/RW, nomor rumah, patokan...",
                      rows: 3,
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UTextarea, {
                        modelValue: unref(addressLine),
                        "onUpdate:modelValue": ($event) => isRef(addressLine) ? addressLine.value = $event : null,
                        placeholder: "Jalan, RT/RW, nomor rumah, patokan...",
                        rows: 3,
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`<div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Provinsi",
                required: "",
                class: "w-full",
                help: "Pilih provinsi tujuan pengiriman."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_USelect, {
                      modelValue: unref(selectedProvince),
                      "onUpdate:modelValue": ($event) => isRef(selectedProvince) ? selectedProvince.value = $event : null,
                      items: unref(provinces).map((p) => ({ label: p, value: p })),
                      placeholder: "Pilih provinsi",
                      loading: unref(isLoadingProvinces),
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_USelect, {
                        modelValue: unref(selectedProvince),
                        "onUpdate:modelValue": ($event) => isRef(selectedProvince) ? selectedProvince.value = $event : null,
                        items: unref(provinces).map((p) => ({ label: p, value: p })),
                        placeholder: "Pilih provinsi",
                        loading: unref(isLoadingProvinces),
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "loading"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Kota/Kab",
                required: "",
                class: "w-full",
                help: "Kota/kabupaten harus sesuai dengan alamat tujuan."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_USelect, {
                      modelValue: unref(selectedCity),
                      "onUpdate:modelValue": ($event) => isRef(selectedCity) ? selectedCity.value = $event : null,
                      items: unref(cities).map((c) => ({ label: c, value: c })),
                      placeholder: "Pilih kota",
                      loading: unref(isLoadingCities),
                      disabled: !unref(selectedProvince),
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_USelect, {
                        modelValue: unref(selectedCity),
                        "onUpdate:modelValue": ($event) => isRef(selectedCity) ? selectedCity.value = $event : null,
                        items: unref(cities).map((c) => ({ label: c, value: c })),
                        placeholder: "Pilih kota",
                        loading: unref(isLoadingCities),
                        disabled: !unref(selectedProvince),
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "loading", "disabled"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Kecamatan",
                class: "w-full",
                help: "Disarankan dipilih agar pencarian area dan ongkir lebih akurat."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_USelect, {
                      modelValue: unref(selectedDistrict),
                      "onUpdate:modelValue": ($event) => isRef(selectedDistrict) ? selectedDistrict.value = $event : null,
                      items: unref(districts),
                      placeholder: "Pilih kecamatan",
                      loading: unref(isLoadingDistricts),
                      disabled: !unref(selectedCity),
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_USelect, {
                        modelValue: unref(selectedDistrict),
                        "onUpdate:modelValue": ($event) => isRef(selectedDistrict) ? selectedDistrict.value = $event : null,
                        items: unref(districts),
                        placeholder: "Pilih kecamatan",
                        loading: unref(isLoadingDistricts),
                        disabled: !unref(selectedCity),
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "loading", "disabled"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Kode pos",
                required: "",
                class: "w-full sm:w-1/3",
                help: "Masukkan 5 digit kode pos tujuan."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UInput, {
                      modelValue: unref(postalCode),
                      "onUpdate:modelValue": ($event) => isRef(postalCode) ? postalCode.value = $event : null,
                      placeholder: "12345",
                      class: "w-full",
                      inputmode: "numeric"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UInput, {
                        modelValue: unref(postalCode),
                        "onUpdate:modelValue": ($event) => isRef(postalCode) ? postalCode.value = $event : null,
                        placeholder: "12345",
                        class: "w-full",
                        inputmode: "numeric"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Catatan kurir (opsional)",
                class: "w-full",
                help: "Tambahkan instruksi singkat seperti warna pagar, titip satpam, atau jam penerimaan."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UInput, {
                      modelValue: unref(notes),
                      "onUpdate:modelValue": ($event) => isRef(notes) ? notes.value = $event : null,
                      placeholder: "Contoh: titip satpam / pagar hitam",
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UInput, {
                        modelValue: unref(notes),
                        "onUpdate:modelValue": ($event) => isRef(notes) ? notes.value = $event : null,
                        placeholder: "Contoh: titip satpam / pagar hitam",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300"${_scopeId}><div class="flex items-start gap-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-info",
                class: "mt-0.5 size-4 text-gray-500 dark:text-gray-400"
              }, null, _parent2, _scopeId));
              _push2(`<div class="min-w-0"${_scopeId}><p class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Panduan singkat</p><ul class="mt-1 list-disc space-y-1 pl-5"${_scopeId}><li${_scopeId}>Masukkan alamat sedetail mungkin (RT/RW dan patokan membantu kurir).</li><li${_scopeId}>Nomor HP dipakai untuk konfirmasi pengantaran.</li></ul></div></div></div></div>`);
            } else {
              _push2(`<div class="space-y-3"${_scopeId}>`);
              if (unref(hasPickupLocation) && __props.pickupLocation) {
                _push2(`<div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/35 dark:text-emerald-200"${_scopeId}><div class="flex items-start gap-2"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-map-pin",
                  class: "mt-0.5 size-4 shrink-0"
                }, null, _parent2, _scopeId));
                _push2(`<div class="min-w-0"${_scopeId}><p class="font-semibold"${_scopeId}>Lokasi Pickup Kantor</p><p class="mt-1"${_scopeId}>${ssrInterpolate(__props.pickupLocation.label)}</p><p class="mt-1"${_scopeId}>${ssrInterpolate(__props.pickupLocation.address_line)}</p><p class="mt-1"${_scopeId}>${ssrInterpolate(__props.pickupLocation.district ? `${__props.pickupLocation.district}, ` : "")}${ssrInterpolate(__props.pickupLocation.city)}, ${ssrInterpolate(__props.pickupLocation.province)}${ssrInterpolate(__props.pickupLocation.postal_code ? ` ${__props.pickupLocation.postal_code}` : "")}</p>`);
                if (__props.pickupLocation.phone) {
                  _push2(`<p class="mt-1 text-xs"${_scopeId}> Kontak: ${ssrInterpolate(__props.pickupLocation.phone)}</p>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div></div></div>`);
              } else {
                _push2(`<div class="rounded-2xl border border-dashed border-rose-300 p-4 text-sm text-rose-700 dark:border-rose-800 dark:text-rose-300"${_scopeId}> Lokasi pickup kantor belum dikonfigurasi admin. Pilih metode alamat lain terlebih dahulu. </div>`);
              }
              _push2(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300"${_scopeId}><div class="flex items-start gap-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-info",
                class: "mt-0.5 size-4 text-gray-500 dark:text-gray-400"
              }, null, _parent2, _scopeId));
              _push2(`<div class="min-w-0"${_scopeId}><p class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Informasi self pickup</p><ul class="mt-1 list-disc space-y-1 pl-5"${_scopeId}><li${_scopeId}>Pesanan diambil sendiri oleh pembeli ke lokasi pickup kantor.</li><li${_scopeId}>Ongkir otomatis gratis.</li></ul></div></div></div></div>`);
            }
            if (unref(addressMode) !== "pickup" && unref(isLoadingRates)) {
              _push2(`<div class="mt-4"${_scopeId}><div class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-loader-circle",
                class: "size-4 animate-spin text-gray-400"
              }, null, _parent2, _scopeId));
              _push2(`<p class="text-sm text-gray-500 dark:text-gray-400"${_scopeId}>Memuat tarif ongkir Lion Parcel…</p></div></div>`);
            } else if (unref(addressMode) !== "pickup" && unref(shippingError)) {
              _push2(`<div class="mt-4"${_scopeId}><div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200"${_scopeId}><div class="flex items-center gap-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-triangle-alert",
                class: "size-4 shrink-0"
              }, null, _parent2, _scopeId));
              _push2(` ${ssrInterpolate(unref(shippingError))}</div></div></div>`);
            } else if (unref(addressMode) !== "pickup" && unref(shippingRates).length > 0) {
              _push2(`<div class="mt-4 space-y-3"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>Layanan Pengiriman (Lion Parcel)</p><div class="grid grid-cols-1 gap-2 sm:grid-cols-2"${_scopeId}><!--[-->`);
              ssrRenderList(unref(shippingRates), (rate) => {
                _push2(`<button type="button" class="${ssrRenderClass([[
                  unref(selectedRate)?.product === rate.product ? "border-primary-500 ring-2 ring-primary-500/20" : "border-gray-200 dark:border-gray-800"
                ], "w-full rounded-2xl border p-3 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55"])}"${_scopeId}><div class="flex items-start justify-between gap-2"${_scopeId}><div class="min-w-0"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(rate.product)}</p><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(rate.estimasi_sla)}</p></div><div class="flex shrink-0 items-center gap-1.5"${_scopeId}><p class="whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formatIDR(rate.total_tariff))}</p>`);
                if (unref(selectedRate)?.product === rate.product) {
                  _push2(ssrRenderComponent(_component_UBadge, {
                    label: "Dipilih",
                    color: "primary",
                    variant: "soft",
                    size: "xs",
                    class: "rounded-full"
                  }, null, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div></div></button>`);
              });
              _push2(`<!--]--></div></div>`);
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              createVNode("div", { class: "mb-4 rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40" }, [
                createVNode("div", { class: "flex items-start gap-3" }, [
                  createVNode("div", {
                    class: ["mt-0.5 grid size-9 shrink-0 place-items-center rounded-xl", [
                      addressModeGuide.value.color === "primary" ? "bg-primary-50 text-primary-600 dark:bg-primary-950/50 dark:text-primary-300" : "",
                      addressModeGuide.value.color === "warning" ? "bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300" : "",
                      addressModeGuide.value.color === "success" ? "bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300" : ""
                    ]]
                  }, [
                    createVNode(_component_UIcon, {
                      name: addressModeGuide.value.icon,
                      class: "size-4.5"
                    }, null, 8, ["name"])
                  ], 2),
                  createVNode("div", { class: "min-w-0 flex-1" }, [
                    createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(addressModeGuide.value.title), 1),
                    createVNode("p", { class: "mt-1 text-sm text-gray-600 dark:text-gray-300" }, toDisplayString(addressModeGuide.value.description), 1),
                    createVNode("div", { class: "mt-3 grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_260px]" }, [
                      createVNode("ul", { class: "space-y-1.5 text-sm text-gray-600 dark:text-gray-300" }, [
                        (openBlock(true), createBlock(Fragment, null, renderList(addressModeGuide.value.rules, (rule) => {
                          return openBlock(), createBlock("li", {
                            key: rule,
                            class: "flex items-start gap-2"
                          }, [
                            createVNode(_component_UIcon, {
                              name: "i-lucide-check",
                              class: "mt-0.5 size-4 shrink-0 text-gray-400 dark:text-gray-500"
                            }),
                            createVNode("span", null, toDisplayString(rule), 1)
                          ]);
                        }), 128))
                      ]),
                      createVNode("div", { class: "rounded-xl border border-gray-200 bg-gray-50/80 p-3 dark:border-gray-800 dark:bg-gray-900/60" }, [
                        createVNode("p", { class: "text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" }, toDisplayString(addressModeGuide.value.summaryLabel), 1),
                        createVNode("p", { class: "mt-1 text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(addressModeGuide.value.summaryValue), 1)
                      ])
                    ])
                  ])
                ])
              ]),
              unref(addressMode) === "saved" ? (openBlock(), createBlock("div", {
                key: 0,
                class: "space-y-3"
              }, [
                __props.addresses.length === 0 ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "w-full rounded-2xl border border-dashed border-gray-300 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"
                }, ' Belum ada alamat tersimpan. Silakan pilih "Isi manual". ')) : (openBlock(), createBlock("div", {
                  key: 1,
                  class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-2"
                }, [
                  (openBlock(true), createBlock(Fragment, null, renderList(__props.addresses, (a) => {
                    return openBlock(), createBlock("button", {
                      key: a.id,
                      type: "button",
                      class: ["w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55", [
                        unref(selectedAddressId) === a.id ? "border-primary-500 ring-2 ring-primary-500/20" : "border-gray-200 dark:border-gray-800"
                      ]],
                      onClick: ($event) => selectedAddressId.value = a.id
                    }, [
                      createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                        createVNode("div", { class: "min-w-0" }, [
                          createVNode("p", { class: "truncate text-sm font-semibold text-gray-900 dark:text-white" }, [
                            createTextVNode(toDisplayString(a.label) + " ", 1),
                            a.is_default ? (openBlock(), createBlock("span", {
                              key: 0,
                              class: "ml-2 text-xs text-gray-500 dark:text-gray-400"
                            }, "(Default)")) : createCommentVNode("", true)
                          ]),
                          createVNode("p", { class: "mt-1 truncate text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(a.recipient_name) + " • " + toDisplayString(a.phone), 1)
                        ]),
                        unref(selectedAddressId) === a.id ? (openBlock(), createBlock(_component_UBadge, {
                          key: 0,
                          label: "Dipilih",
                          color: "primary",
                          variant: "soft",
                          size: "xs",
                          class: "shrink-0 rounded-full"
                        })) : createCommentVNode("", true)
                      ]),
                      createVNode("p", { class: "mt-2 line-clamp-2 text-sm text-gray-700 dark:text-gray-200" }, toDisplayString(a.address_line) + ", " + toDisplayString(a.city) + ", " + toDisplayString(a.province) + ", " + toDisplayString(a.postal_code), 1),
                      a.description ? (openBlock(), createBlock("p", {
                        key: 0,
                        class: "mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400"
                      }, " Catatan: " + toDisplayString(a.description), 1)) : createCommentVNode("", true)
                    ], 10, ["onClick"]);
                  }), 128))
                ])),
                createVNode("div", { class: "flex items-center justify-between" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, ' Tip: gunakan alamat "Default" untuk checkout lebih cepat. '),
                  createVNode(_component_UButton, {
                    to: "/dashboard",
                    color: "neutral",
                    variant: "ghost",
                    class: "rounded-xl",
                    size: "sm"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Kelola alamat ")
                    ]),
                    _: 1
                  })
                ])
              ])) : unref(addressMode) === "manual" ? (openBlock(), createBlock("div", {
                key: 1,
                class: "space-y-4"
              }, [
                createVNode("div", { class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Nama penerima",
                    required: "",
                    class: "w-full",
                    help: "Nama ini dipakai sebagai penerima resmi pada data checkout dan pengiriman."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(recipientName),
                        "onUpdate:modelValue": ($event) => isRef(recipientName) ? recipientName.value = $event : null,
                        placeholder: "Nama lengkap",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UFormField, {
                    label: "No. HP",
                    required: "",
                    class: "w-full",
                    help: "Gunakan nomor aktif agar kurir atau admin bisa menghubungi penerima bila diperlukan."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(phone),
                        "onUpdate:modelValue": ($event) => isRef(phone) ? phone.value = $event : null,
                        placeholder: "08xxxxxxxxxx",
                        class: "w-full",
                        inputmode: "tel"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  })
                ]),
                createVNode(_component_UFormField, {
                  label: "Alamat lengkap",
                  required: "",
                  class: "w-full",
                  help: "Isi selengkap mungkin: jalan, nomor rumah, RT/RW, blok, lantai, dan patokan."
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UTextarea, {
                      modelValue: unref(addressLine),
                      "onUpdate:modelValue": ($event) => isRef(addressLine) ? addressLine.value = $event : null,
                      placeholder: "Jalan, RT/RW, nomor rumah, patokan...",
                      rows: 3,
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode("div", { class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-3" }, [
                  createVNode(_component_UFormField, {
                    label: "Provinsi",
                    required: "",
                    class: "w-full",
                    help: "Pilih provinsi tujuan pengiriman."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelect, {
                        modelValue: unref(selectedProvince),
                        "onUpdate:modelValue": ($event) => isRef(selectedProvince) ? selectedProvince.value = $event : null,
                        items: unref(provinces).map((p) => ({ label: p, value: p })),
                        placeholder: "Pilih provinsi",
                        loading: unref(isLoadingProvinces),
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "loading"])
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UFormField, {
                    label: "Kota/Kab",
                    required: "",
                    class: "w-full",
                    help: "Kota/kabupaten harus sesuai dengan alamat tujuan."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelect, {
                        modelValue: unref(selectedCity),
                        "onUpdate:modelValue": ($event) => isRef(selectedCity) ? selectedCity.value = $event : null,
                        items: unref(cities).map((c) => ({ label: c, value: c })),
                        placeholder: "Pilih kota",
                        loading: unref(isLoadingCities),
                        disabled: !unref(selectedProvince),
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "loading", "disabled"])
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UFormField, {
                    label: "Kecamatan",
                    class: "w-full",
                    help: "Disarankan dipilih agar pencarian area dan ongkir lebih akurat."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelect, {
                        modelValue: unref(selectedDistrict),
                        "onUpdate:modelValue": ($event) => isRef(selectedDistrict) ? selectedDistrict.value = $event : null,
                        items: unref(districts),
                        placeholder: "Pilih kecamatan",
                        loading: unref(isLoadingDistricts),
                        disabled: !unref(selectedCity),
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items", "loading", "disabled"])
                    ]),
                    _: 1
                  })
                ]),
                createVNode(_component_UFormField, {
                  label: "Kode pos",
                  required: "",
                  class: "w-full sm:w-1/3",
                  help: "Masukkan 5 digit kode pos tujuan."
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: unref(postalCode),
                      "onUpdate:modelValue": ($event) => isRef(postalCode) ? postalCode.value = $event : null,
                      placeholder: "12345",
                      class: "w-full",
                      inputmode: "numeric"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, {
                  label: "Catatan kurir (opsional)",
                  class: "w-full",
                  help: "Tambahkan instruksi singkat seperti warna pagar, titip satpam, atau jam penerimaan."
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: unref(notes),
                      "onUpdate:modelValue": ($event) => isRef(notes) ? notes.value = $event : null,
                      placeholder: "Contoh: titip satpam / pagar hitam",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, [
                  createVNode("div", { class: "flex items-start gap-2" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-info",
                      class: "mt-0.5 size-4 text-gray-500 dark:text-gray-400"
                    }),
                    createVNode("div", { class: "min-w-0" }, [
                      createVNode("p", { class: "font-semibold text-gray-900 dark:text-white" }, "Panduan singkat"),
                      createVNode("ul", { class: "mt-1 list-disc space-y-1 pl-5" }, [
                        createVNode("li", null, "Masukkan alamat sedetail mungkin (RT/RW dan patokan membantu kurir)."),
                        createVNode("li", null, "Nomor HP dipakai untuk konfirmasi pengantaran.")
                      ])
                    ])
                  ])
                ])
              ])) : (openBlock(), createBlock("div", {
                key: 2,
                class: "space-y-3"
              }, [
                unref(hasPickupLocation) && __props.pickupLocation ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/35 dark:text-emerald-200"
                }, [
                  createVNode("div", { class: "flex items-start gap-2" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-map-pin",
                      class: "mt-0.5 size-4 shrink-0"
                    }),
                    createVNode("div", { class: "min-w-0" }, [
                      createVNode("p", { class: "font-semibold" }, "Lokasi Pickup Kantor"),
                      createVNode("p", { class: "mt-1" }, toDisplayString(__props.pickupLocation.label), 1),
                      createVNode("p", { class: "mt-1" }, toDisplayString(__props.pickupLocation.address_line), 1),
                      createVNode("p", { class: "mt-1" }, toDisplayString(__props.pickupLocation.district ? `${__props.pickupLocation.district}, ` : "") + toDisplayString(__props.pickupLocation.city) + ", " + toDisplayString(__props.pickupLocation.province) + toDisplayString(__props.pickupLocation.postal_code ? ` ${__props.pickupLocation.postal_code}` : ""), 1),
                      __props.pickupLocation.phone ? (openBlock(), createBlock("p", {
                        key: 0,
                        class: "mt-1 text-xs"
                      }, " Kontak: " + toDisplayString(__props.pickupLocation.phone), 1)) : createCommentVNode("", true)
                    ])
                  ])
                ])) : (openBlock(), createBlock("div", {
                  key: 1,
                  class: "rounded-2xl border border-dashed border-rose-300 p-4 text-sm text-rose-700 dark:border-rose-800 dark:text-rose-300"
                }, " Lokasi pickup kantor belum dikonfigurasi admin. Pilih metode alamat lain terlebih dahulu. ")),
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, [
                  createVNode("div", { class: "flex items-start gap-2" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-info",
                      class: "mt-0.5 size-4 text-gray-500 dark:text-gray-400"
                    }),
                    createVNode("div", { class: "min-w-0" }, [
                      createVNode("p", { class: "font-semibold text-gray-900 dark:text-white" }, "Informasi self pickup"),
                      createVNode("ul", { class: "mt-1 list-disc space-y-1 pl-5" }, [
                        createVNode("li", null, "Pesanan diambil sendiri oleh pembeli ke lokasi pickup kantor."),
                        createVNode("li", null, "Ongkir otomatis gratis.")
                      ])
                    ])
                  ])
                ])
              ])),
              unref(addressMode) !== "pickup" && unref(isLoadingRates) ? (openBlock(), createBlock("div", {
                key: 3,
                class: "mt-4"
              }, [
                createVNode("div", { class: "flex items-center gap-2 rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-loader-circle",
                    class: "size-4 animate-spin text-gray-400"
                  }),
                  createVNode("p", { class: "text-sm text-gray-500 dark:text-gray-400" }, "Memuat tarif ongkir Lion Parcel…")
                ])
              ])) : unref(addressMode) !== "pickup" && unref(shippingError) ? (openBlock(), createBlock("div", {
                key: 4,
                class: "mt-4"
              }, [
                createVNode("div", { class: "rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200" }, [
                  createVNode("div", { class: "flex items-center gap-2" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-triangle-alert",
                      class: "size-4 shrink-0"
                    }),
                    createTextVNode(" " + toDisplayString(unref(shippingError)), 1)
                  ])
                ])
              ])) : unref(addressMode) !== "pickup" && unref(shippingRates).length > 0 ? (openBlock(), createBlock("div", {
                key: 5,
                class: "mt-4 space-y-3"
              }, [
                createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Layanan Pengiriman (Lion Parcel)"),
                createVNode("div", { class: "grid grid-cols-1 gap-2 sm:grid-cols-2" }, [
                  (openBlock(true), createBlock(Fragment, null, renderList(unref(shippingRates), (rate) => {
                    return openBlock(), createBlock("button", {
                      key: rate.product,
                      type: "button",
                      class: ["w-full rounded-2xl border p-3 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55", [
                        unref(selectedRate)?.product === rate.product ? "border-primary-500 ring-2 ring-primary-500/20" : "border-gray-200 dark:border-gray-800"
                      ]],
                      onClick: ($event) => selectedRate.value = rate
                    }, [
                      createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                        createVNode("div", { class: "min-w-0" }, [
                          createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(rate.product), 1),
                          createVNode("p", { class: "mt-0.5 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(rate.estimasi_sla), 1)
                        ]),
                        createVNode("div", { class: "flex shrink-0 items-center gap-1.5" }, [
                          createVNode("p", { class: "whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white" }, toDisplayString(formatIDR(rate.total_tariff)), 1),
                          unref(selectedRate)?.product === rate.product ? (openBlock(), createBlock(_component_UBadge, {
                            key: 0,
                            label: "Dipilih",
                            color: "primary",
                            variant: "soft",
                            size: "xs",
                            class: "rounded-full"
                          })) : createCommentVNode("", true)
                        ])
                      ])
                    ], 10, ["onClick"]);
                  }), 128))
                ])
              ])) : createCommentVNode("", true)
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/checkout/CheckoutAddressPanel.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "CheckoutPaymentPanel",
  __ssrInlineRender: true,
  props: {
    saldo: {},
    total: {},
    midtransClientKey: {},
    modelValue: {}
  },
  emits: ["update:modelValue"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function formatIDR(n) {
      return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);
    }
    const isSaldoEnough = () => props.saldo >= props.total;
    const saldoShortage = () => Math.max(0, props.total - props.saldo);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$5;
      const _component_UIcon = _sfc_main$6;
      const _component_UBadge = _sfc_main$7;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Metode Pembayaran</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Pilih metode pembayaran. Saldo hanya bisa dipilih jika mencukupi total. </p></div>`);
          } else {
            return [
              createVNode("div", null, [
                createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Metode Pembayaran"),
                createVNode("p", { class: "mt-1 text-sm text-gray-500 dark:text-gray-400" }, " Pilih metode pembayaran. Saldo hanya bisa dipilih jika mencukupi total. ")
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-3"${_scopeId}><div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"${_scopeId}><button type="button" class="${ssrRenderClass([[
              isSaldoEnough() ? "hover:bg-white dark:hover:bg-gray-950/55" : "cursor-not-allowed opacity-60",
              __props.modelValue === "saldo" ? "border-primary-500 ring-2 ring-primary-500/20" : "border-gray-200 dark:border-gray-800"
            ], "w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40"])}"${ssrIncludeBooleanAttr(!isSaldoEnough()) ? " disabled" : ""}${_scopeId}><div class="flex items-start justify-between gap-2"${_scopeId}><div${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>Saldo Ewallet</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Saldo: <span class="font-semibold"${_scopeId}>${ssrInterpolate(formatIDR(__props.saldo))}</span></p></div><div class="flex shrink-0 items-center gap-1.5"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-wallet",
              class: "size-4 text-gray-500 dark:text-gray-400"
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              label: isSaldoEnough() ? "Cukup" : "Saldo kurang",
              color: isSaldoEnough() ? "success" : "warning",
              variant: "soft",
              size: "xs",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(`</div></div>`);
            if (!isSaldoEnough()) {
              _push2(`<p class="mt-2 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Kurang ${ssrInterpolate(formatIDR(saldoShortage()))}</p>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</button><button type="button" class="${ssrRenderClass([[
              __props.modelValue === "midtrans" ? "border-primary-500 ring-2 ring-primary-500/20" : "border-gray-200 dark:border-gray-800",
              !__props.midtransClientKey ? "cursor-not-allowed opacity-60" : ""
            ], "w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55"])}"${ssrIncludeBooleanAttr(!__props.midtransClientKey) ? " disabled" : ""}${_scopeId}><div class="flex items-start justify-between gap-2"${_scopeId}><div${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>Online Payment</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> GoPay, ShopeePay, Transfer, Kartu Kredit &amp; lebih </p></div><div class="flex shrink-0 items-center gap-1.5"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-credit-card",
              class: "size-4 text-gray-500 dark:text-gray-400"
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              label: "Snap",
              color: "primary",
              variant: "soft",
              size: "xs",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(`</div></div></button></div>`);
            if (!__props.midtransClientKey) {
              _push2(`<div class="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200"${_scopeId}> Midtrans clientKey belum tersedia. Hubungi admin untuk mengaktifkan metode ini. </div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300"${_scopeId}><div class="flex items-start gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-shield-check",
              class: "mt-0.5 size-4 text-gray-500 dark:text-gray-400"
            }, null, _parent2, _scopeId));
            _push2(`<div class="min-w-0"${_scopeId}><p class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Aman &amp; terlindungi</p><ul class="mt-1 list-disc space-y-1 pl-5"${_scopeId}><li${_scopeId}>Saldo ewallet langsung dipotong saat transaksi berhasil.</li><li${_scopeId}>Online Payment Snap menampilkan popup pembayaran yang aman.</li></ul></div></div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-3" }, [
                createVNode("div", { class: "grid w-full grid-cols-1 gap-3 sm:grid-cols-2" }, [
                  createVNode("button", {
                    type: "button",
                    class: ["w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40", [
                      isSaldoEnough() ? "hover:bg-white dark:hover:bg-gray-950/55" : "cursor-not-allowed opacity-60",
                      __props.modelValue === "saldo" ? "border-primary-500 ring-2 ring-primary-500/20" : "border-gray-200 dark:border-gray-800"
                    ]],
                    disabled: !isSaldoEnough(),
                    onClick: ($event) => isSaldoEnough() && emit("update:modelValue", "saldo")
                  }, [
                    createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                      createVNode("div", null, [
                        createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Saldo Ewallet"),
                        createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, [
                          createTextVNode(" Saldo: "),
                          createVNode("span", { class: "font-semibold" }, toDisplayString(formatIDR(__props.saldo)), 1)
                        ])
                      ]),
                      createVNode("div", { class: "flex shrink-0 items-center gap-1.5" }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-wallet",
                          class: "size-4 text-gray-500 dark:text-gray-400"
                        }),
                        createVNode(_component_UBadge, {
                          label: isSaldoEnough() ? "Cukup" : "Saldo kurang",
                          color: isSaldoEnough() ? "success" : "warning",
                          variant: "soft",
                          size: "xs",
                          class: "rounded-full"
                        }, null, 8, ["label", "color"])
                      ])
                    ]),
                    !isSaldoEnough() ? (openBlock(), createBlock("p", {
                      key: 0,
                      class: "mt-2 text-xs text-gray-500 dark:text-gray-400"
                    }, " Kurang " + toDisplayString(formatIDR(saldoShortage())), 1)) : createCommentVNode("", true)
                  ], 10, ["disabled", "onClick"]),
                  createVNode("button", {
                    type: "button",
                    class: ["w-full rounded-2xl border p-4 text-left transition bg-white/70 backdrop-blur dark:bg-gray-950/40 hover:bg-white dark:hover:bg-gray-950/55", [
                      __props.modelValue === "midtrans" ? "border-primary-500 ring-2 ring-primary-500/20" : "border-gray-200 dark:border-gray-800",
                      !__props.midtransClientKey ? "cursor-not-allowed opacity-60" : ""
                    ]],
                    disabled: !__props.midtransClientKey,
                    onClick: ($event) => __props.midtransClientKey && emit("update:modelValue", "midtrans")
                  }, [
                    createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                      createVNode("div", null, [
                        createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Online Payment"),
                        createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, " GoPay, ShopeePay, Transfer, Kartu Kredit & lebih ")
                      ]),
                      createVNode("div", { class: "flex shrink-0 items-center gap-1.5" }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-credit-card",
                          class: "size-4 text-gray-500 dark:text-gray-400"
                        }),
                        createVNode(_component_UBadge, {
                          label: "Snap",
                          color: "primary",
                          variant: "soft",
                          size: "xs",
                          class: "rounded-full"
                        })
                      ])
                    ])
                  ], 10, ["disabled", "onClick"])
                ]),
                !__props.midtransClientKey ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200"
                }, " Midtrans clientKey belum tersedia. Hubungi admin untuk mengaktifkan metode ini. ")) : createCommentVNode("", true),
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, [
                  createVNode("div", { class: "flex items-start gap-2" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-shield-check",
                      class: "mt-0.5 size-4 text-gray-500 dark:text-gray-400"
                    }),
                    createVNode("div", { class: "min-w-0" }, [
                      createVNode("p", { class: "font-semibold text-gray-900 dark:text-white" }, "Aman & terlindungi"),
                      createVNode("ul", { class: "mt-1 list-disc space-y-1 pl-5" }, [
                        createVNode("li", null, "Saldo ewallet langsung dipotong saat transaksi berhasil."),
                        createVNode("li", null, "Online Payment Snap menampilkan popup pembayaran yang aman.")
                      ])
                    ])
                  ])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/checkout/CheckoutPaymentPanel.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "CheckoutSummary",
  __ssrInlineRender: true,
  props: {
    cart: {},
    saldo: {},
    selectedPlan: {},
    selectedMethod: {},
    selectedRate: {},
    isSelfPickup: { type: Boolean },
    isAddressValid: { type: Boolean },
    isSubmitting: { type: Boolean },
    errorMessage: {},
    midtransEnv: {},
    shippingCost: {},
    total: {}
  },
  emits: ["pay", "update:selectedPlan"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function formatIDR(n) {
      return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);
    }
    function selectPlan(plan) {
      if (plan === props.selectedPlan) {
        return;
      }
      emit("update:selectedPlan", plan);
    }
    function planButtonClass(plan) {
      if (props.selectedPlan === plan) {
        return "border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-500/70 dark:bg-primary-950/40 dark:text-primary-300";
      }
      return "border-gray-200 bg-white text-gray-700 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300 dark:hover:border-gray-700";
    }
    const isSaldoEnough = () => props.selectedMethod === "saldo" && props.saldo >= props.total;
    const isPaymentValid = () => {
      if (!props.selectedMethod) return false;
      if (props.selectedMethod === "saldo") return isSaldoEnough();
      return true;
    };
    const canPay = () => !!(props.cart && props.total > 0 && props.isAddressValid && isPaymentValid() && !props.isSubmitting);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$5;
      const _component_UBadge = _sfc_main$7;
      const _component_UButton = _sfc_main$8;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "lg:sticky lg:top-24 space-y-4" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-2"${_scopeId}><div${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Ringkasan Pembayaran</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Total dihitung otomatis dari keranjang. </p></div>`);
            if (__props.midtransEnv === "sandbox") {
              _push2(ssrRenderComponent(_component_UBadge, {
                label: "Sandbox",
                color: "warning",
                variant: "soft",
                class: "rounded-full shrink-0"
              }, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Ringkasan Pembayaran"),
                  createVNode("p", { class: "mt-1 text-sm text-gray-500 dark:text-gray-400" }, " Total dihitung otomatis dari keranjang. ")
                ]),
                __props.midtransEnv === "sandbox" ? (openBlock(), createBlock(_component_UBadge, {
                  key: 0,
                  label: "Sandbox",
                  color: "warning",
                  variant: "soft",
                  class: "rounded-full shrink-0"
                })) : createCommentVNode("", true)
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-3"${_scopeId}><div class="rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><p class="text-xs font-semibold text-gray-900 dark:text-white"${_scopeId}>Tipe Plan Order</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Pilih plan yang akan disimpan pada transaksi order. </p><div class="mt-2 grid grid-cols-2 gap-2"${_scopeId}><button type="button" class="${ssrRenderClass([planButtonClass("planA"), "rounded-xl border px-3 py-2 text-left transition-colors"])}"${_scopeId}><p class="text-sm font-semibold"${_scopeId}>Plan A</p><p class="text-xs opacity-80"${_scopeId}>Default order</p></button><button type="button" class="${ssrRenderClass([planButtonClass("planB"), "rounded-xl border px-3 py-2 text-left transition-colors"])}"${_scopeId}><p class="text-sm font-semibold"${_scopeId}>Plan B</p><p class="text-xs opacity-80"${_scopeId}>Alternatif plan</p></button></div></div><div class="flex items-center justify-between text-sm"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Subtotal</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formatIDR(__props.cart?.subtotal ?? 0))}</span></div>`);
            if (__props.cart && __props.cart.discount > 0) {
              _push2(`<div class="flex items-center justify-between text-sm"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Diskon</span><span class="font-semibold text-emerald-600 dark:text-emerald-400"${_scopeId}> -${ssrInterpolate(formatIDR(__props.cart.discount))}</span></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="flex items-center justify-between text-sm"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Pajak</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formatIDR(__props.cart?.tax ?? 0))}</span></div><div class="flex items-center justify-between text-sm"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}> Ongkir `);
            if (__props.isSelfPickup) {
              _push2(`<span class="text-xs text-gray-400 dark:text-gray-500"${_scopeId}> (Self pickup) </span>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.selectedRate) {
              _push2(`<span class="text-xs text-gray-400 dark:text-gray-500"${_scopeId}> (${ssrInterpolate(__props.selectedRate.product)}) </span>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formatIDR(__props.shippingCost))}</span></div><div class="my-1 border-t border-gray-200 dark:border-gray-800"${_scopeId}></div><div class="flex items-center justify-between"${_scopeId}><span class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>Total</span><span class="whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formatIDR(__props.total))}</span></div>`);
            if (__props.selectedMethod === "saldo") {
              _push2(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Saldo Ewallet</p><p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formatIDR(__props.saldo))} `);
              if (!isSaldoEnough()) {
                _push2(`<span class="ml-2 text-xs font-medium text-rose-600 dark:text-rose-400"${_scopeId}> (Kurang ${ssrInterpolate(formatIDR(__props.total - __props.saldo))}) </span>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</p></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300"${_scopeId}><p class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Checklist sebelum bayar</p><ul class="mt-1 list-disc space-y-1 pl-5"${_scopeId}><li class="${ssrRenderClass(__props.isAddressValid ? "text-emerald-600 dark:text-emerald-400" : "")}"${_scopeId}>${ssrInterpolate(__props.isSelfPickup ? "Lokasi self pickup tersedia" : "Alamat pengiriman lengkap")}</li><li class="${ssrRenderClass(__props.isSelfPickup || __props.selectedRate ? "text-emerald-600 dark:text-emerald-400" : "")}"${_scopeId}>${ssrInterpolate(__props.isSelfPickup ? "Self pickup aktif (gratis ongkir)" : "Layanan pengiriman dipilih")}</li><li class="${ssrRenderClass(__props.selectedMethod ? "text-emerald-600 dark:text-emerald-400" : "")}"${_scopeId}> Metode pembayaran dipilih </li><li class="${ssrRenderClass(isPaymentValid() ? "text-emerald-600 dark:text-emerald-400" : "")}"${_scopeId}>${ssrInterpolate(__props.selectedMethod === "saldo" ? "Saldo mencukupi total" : "Siap membayar via Midtrans")}</li></ul></div>`);
            if (__props.errorMessage) {
              _push2(`<div class="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200"${_scopeId}>${ssrInterpolate(__props.errorMessage)}</div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "solid",
              class: "rounded-xl",
              size: "lg",
              block: "",
              disabled: !canPay(),
              loading: __props.isSubmitting,
              onClick: ($event) => emit("pay")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(__props.isSubmitting ? "Memproses…" : "Bayar Sekarang")}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(__props.isSubmitting ? "Memproses…" : "Bayar Sekarang"), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(__props.selectedMethod === "midtrans" ? "Kamu akan diarahkan ke popup Midtrans Snap." : "Saldo akan dipotong langsung.")}</p>`);
            _push2(ssrRenderComponent(_component_UButton, {
              to: "/cart",
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              block: ""
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Kembali ke Keranjang `);
                } else {
                  return [
                    createTextVNode(" Kembali ke Keranjang ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-3" }, [
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40" }, [
                  createVNode("p", { class: "text-xs font-semibold text-gray-900 dark:text-white" }, "Tipe Plan Order"),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, " Pilih plan yang akan disimpan pada transaksi order. "),
                  createVNode("div", { class: "mt-2 grid grid-cols-2 gap-2" }, [
                    createVNode("button", {
                      type: "button",
                      class: ["rounded-xl border px-3 py-2 text-left transition-colors", planButtonClass("planA")],
                      onClick: ($event) => selectPlan("planA")
                    }, [
                      createVNode("p", { class: "text-sm font-semibold" }, "Plan A"),
                      createVNode("p", { class: "text-xs opacity-80" }, "Default order")
                    ], 10, ["onClick"]),
                    createVNode("button", {
                      type: "button",
                      class: ["rounded-xl border px-3 py-2 text-left transition-colors", planButtonClass("planB")],
                      onClick: ($event) => selectPlan("planB")
                    }, [
                      createVNode("p", { class: "text-sm font-semibold" }, "Plan B"),
                      createVNode("p", { class: "text-xs opacity-80" }, "Alternatif plan")
                    ], 10, ["onClick"])
                  ])
                ]),
                createVNode("div", { class: "flex items-center justify-between text-sm" }, [
                  createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Subtotal"),
                  createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(formatIDR(__props.cart?.subtotal ?? 0)), 1)
                ]),
                __props.cart && __props.cart.discount > 0 ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "flex items-center justify-between text-sm"
                }, [
                  createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Diskon"),
                  createVNode("span", { class: "font-semibold text-emerald-600 dark:text-emerald-400" }, " -" + toDisplayString(formatIDR(__props.cart.discount)), 1)
                ])) : createCommentVNode("", true),
                createVNode("div", { class: "flex items-center justify-between text-sm" }, [
                  createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Pajak"),
                  createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(formatIDR(__props.cart?.tax ?? 0)), 1)
                ]),
                createVNode("div", { class: "flex items-center justify-between text-sm" }, [
                  createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, [
                    createTextVNode(" Ongkir "),
                    __props.isSelfPickup ? (openBlock(), createBlock("span", {
                      key: 0,
                      class: "text-xs text-gray-400 dark:text-gray-500"
                    }, " (Self pickup) ")) : createCommentVNode("", true),
                    __props.selectedRate ? (openBlock(), createBlock("span", {
                      key: 1,
                      class: "text-xs text-gray-400 dark:text-gray-500"
                    }, " (" + toDisplayString(__props.selectedRate.product) + ") ", 1)) : createCommentVNode("", true)
                  ]),
                  createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(formatIDR(__props.shippingCost)), 1)
                ]),
                createVNode("div", { class: "my-1 border-t border-gray-200 dark:border-gray-800" }),
                createVNode("div", { class: "flex items-center justify-between" }, [
                  createVNode("span", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Total"),
                  createVNode("span", { class: "whitespace-nowrap text-lg font-extrabold text-gray-900 dark:text-white" }, toDisplayString(formatIDR(__props.total)), 1)
                ]),
                __props.selectedMethod === "saldo" ? (openBlock(), createBlock("div", {
                  key: 1,
                  class: "rounded-2xl border border-gray-200 bg-white/70 p-3 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"
                }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Saldo Ewallet"),
                  createVNode("p", { class: "mt-1 text-sm font-semibold text-gray-900 dark:text-white" }, [
                    createTextVNode(toDisplayString(formatIDR(__props.saldo)) + " ", 1),
                    !isSaldoEnough() ? (openBlock(), createBlock("span", {
                      key: 0,
                      class: "ml-2 text-xs font-medium text-rose-600 dark:text-rose-400"
                    }, " (Kurang " + toDisplayString(formatIDR(__props.total - __props.saldo)) + ") ", 1)) : createCommentVNode("", true)
                  ])
                ])) : createCommentVNode("", true),
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, [
                  createVNode("p", { class: "font-semibold text-gray-900 dark:text-white" }, "Checklist sebelum bayar"),
                  createVNode("ul", { class: "mt-1 list-disc space-y-1 pl-5" }, [
                    createVNode("li", {
                      class: __props.isAddressValid ? "text-emerald-600 dark:text-emerald-400" : ""
                    }, toDisplayString(__props.isSelfPickup ? "Lokasi self pickup tersedia" : "Alamat pengiriman lengkap"), 3),
                    createVNode("li", {
                      class: __props.isSelfPickup || __props.selectedRate ? "text-emerald-600 dark:text-emerald-400" : ""
                    }, toDisplayString(__props.isSelfPickup ? "Self pickup aktif (gratis ongkir)" : "Layanan pengiriman dipilih"), 3),
                    createVNode("li", {
                      class: __props.selectedMethod ? "text-emerald-600 dark:text-emerald-400" : ""
                    }, " Metode pembayaran dipilih ", 2),
                    createVNode("li", {
                      class: isPaymentValid() ? "text-emerald-600 dark:text-emerald-400" : ""
                    }, toDisplayString(__props.selectedMethod === "saldo" ? "Saldo mencukupi total" : "Siap membayar via Midtrans"), 3)
                  ])
                ]),
                __props.errorMessage ? (openBlock(), createBlock("div", {
                  key: 2,
                  class: "rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200"
                }, toDisplayString(__props.errorMessage), 1)) : createCommentVNode("", true),
                createVNode(_component_UButton, {
                  color: "primary",
                  variant: "solid",
                  class: "rounded-xl",
                  size: "lg",
                  block: "",
                  disabled: !canPay(),
                  loading: __props.isSubmitting,
                  onClick: ($event) => emit("pay")
                }, {
                  default: withCtx(() => [
                    createTextVNode(toDisplayString(__props.isSubmitting ? "Memproses…" : "Bayar Sekarang"), 1)
                  ]),
                  _: 1
                }, 8, ["disabled", "loading", "onClick"]),
                createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(__props.selectedMethod === "midtrans" ? "Kamu akan diarahkan ke popup Midtrans Snap." : "Saldo akan dipotong langsung."), 1),
                createVNode(_component_UButton, {
                  to: "/cart",
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-xl",
                  block: ""
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Kembali ke Keranjang ")
                  ]),
                  _: 1
                })
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/checkout/CheckoutSummary.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
function firstErrorMessage(errors) {
  const first = Object.values(errors).find((value) => value !== void 0);
  if (Array.isArray(first)) {
    return first[0] ?? "Validasi gagal.";
  }
  return first ?? "Validasi gagal.";
}
function useMidtrans(env, clientKey) {
  const page = usePage();
  const isSubmitting = ref(false);
  const errorMessage = ref(null);
  function getSnapSrc() {
    const host = env === "production" ? "https://app.midtrans.com" : "https://app.sandbox.midtrans.com";
    return `${host}/snap/snap.js`;
  }
  async function ensureSnapLoaded() {
    if (window.snap?.pay) {
      return true;
    }
    if (!clientKey) {
      return false;
    }
    return new Promise((resolve) => {
      const existing = document.querySelector('script[data-midtrans-snap="1"]');
      if (existing) {
        existing.addEventListener("load", () => resolve(!!window.snap?.pay));
        existing.addEventListener("error", () => resolve(false));
        return;
      }
      const script = document.createElement("script");
      script.src = getSnapSrc();
      script.async = true;
      script.setAttribute("data-midtrans-snap", "1");
      script.setAttribute("data-client-key", clientKey);
      script.onload = () => resolve(!!window.snap?.pay);
      script.onerror = () => resolve(false);
      document.head.appendChild(script);
    });
  }
  async function inertiaPost(url, payload) {
    const csrfToken = String(page.props.csrf_token ?? "");
    return new Promise((resolve, reject) => {
      router.post(
        url,
        {
          _token: csrfToken,
          ...payload
        },
        {
          only: ["flash", "errors"],
          preserveState: true,
          preserveScroll: true,
          replace: true,
          onSuccess: (nextPage) => {
            const props = nextPage?.props ?? {};
            resolve(props);
          },
          onError: (errors) => {
            reject(new Error(firstErrorMessage(errors)));
          },
          onCancel: () => {
            reject(new Error("Request dibatalkan."));
          }
        }
      );
    });
  }
  async function payViaMidtrans(payload) {
    errorMessage.value = null;
    isSubmitting.value = true;
    try {
      const requestPayload = typeof payload === "object" && payload !== null ? payload : {};
      const response = await inertiaPost("/checkout/midtrans/token", requestPayload);
      const checkoutFlash = response.flash?.checkout;
      const flashPayload = checkoutFlash?.payload ?? {};
      const snapToken = flashPayload.snapToken;
      if (!snapToken) {
        throw new Error(checkoutFlash?.message ?? "Snap token tidak ditemukan dari server.");
      }
      const snapOk = await ensureSnapLoaded();
      if (!snapOk) {
        throw new Error("Midtrans Snap gagal dimuat. Pastikan clientKey benar.");
      }
      isSubmitting.value = false;
      window.snap?.pay(snapToken, {
        onSuccess: () => {
          router.visit(flashPayload.successUrl ?? "/dashboard");
        },
        onPending: () => {
          router.visit(flashPayload.pendingUrl ?? flashPayload.successUrl ?? "/dashboard");
        },
        onError: () => {
          errorMessage.value = "Pembayaran gagal. Silakan coba lagi.";
        },
        onClose: () => {
          errorMessage.value = "Pembayaran dibatalkan.";
        }
      });
    } catch (err) {
      errorMessage.value = err instanceof Error ? err.message : "Terjadi kesalahan saat memproses pembayaran.";
    } finally {
      if (isSubmitting.value) {
        isSubmitting.value = false;
      }
    }
  }
  async function payViaSaldo(payload) {
    errorMessage.value = null;
    isSubmitting.value = true;
    try {
      const requestPayload = typeof payload === "object" && payload !== null ? payload : {};
      const response = await inertiaPost("/checkout/pay/saldo", requestPayload);
      const checkoutFlash = response.flash?.checkout;
      const flashPayload = checkoutFlash?.payload ?? {};
      router.visit(flashPayload.redirectTo ?? "/dashboard");
    } catch (err) {
      errorMessage.value = err instanceof Error ? err.message : "Terjadi kesalahan saat memproses pembayaran.";
    } finally {
      isSubmitting.value = false;
    }
  }
  return {
    isSubmitting,
    errorMessage,
    payViaMidtrans,
    payViaSaldo
  };
}
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$d },
  __name: "Index",
  __ssrInlineRender: true,
  setup(__props) {
    const { items, cart, addresses, pickup, saldo, midtrans, itemCount } = useCheckout();
    const { isSubmitting, errorMessage, payViaMidtrans, payViaSaldo } = useMidtrans(
      midtrans.value.env,
      midtrans.value.client_key
    );
    const addressPayload = ref(null);
    const isAddressValid = ref(false);
    const selectedRate = ref(null);
    const selectedMethod = ref(null);
    const selectedPlan = ref("planA");
    const isSelfPickup = computed(() => addressPayload.value?.address_mode === "pickup");
    const shippingCost = computed(() => {
      if (isSelfPickup.value) {
        return 0;
      }
      return selectedRate.value?.total_tariff ?? cart.value?.shipping ?? 0;
    });
    const total = computed(() => (cart.value?.subtotal ?? 0) + shippingCost.value + (cart.value?.tax ?? 0) - (cart.value?.discount ?? 0));
    async function payNow() {
      if (!addressPayload.value || !selectedMethod.value) return;
      const payload = {
        ...addressPayload.value,
        order_type: selectedPlan.value,
        shipping_service_code: isSelfPickup.value ? "SELF_PICKUP" : selectedRate.value?.product ?? "",
        shipping_cost: shippingCost.value,
        shipping_etd: isSelfPickup.value ? "Self pickup di kantor" : selectedRate.value?.estimasi_sla ?? ""
      };
      if (selectedMethod.value === "saldo") {
        await payViaSaldo(payload);
      } else {
        await payViaMidtrans(payload);
      }
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UPage = _sfc_main$e;
      const _component_UPageHeader = _sfc_main$f;
      const _component_UBadge = _sfc_main$7;
      const _component_UPageBody = _sfc_main$g;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 mt-8" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UPage, { class: "min-h-screen bg-gray-50/60 dark:bg-gray-950 transition-colors duration-300" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UPageHeader, {
              class: "p-5",
              title: "Checkout",
              description: "Alamat → Pembayaran → Konfirmasi"
            }, {
              right: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex items-center gap-2"${_scopeId2}>`);
                  if (unref(midtrans).env === "sandbox") {
                    _push3(ssrRenderComponent(_component_UBadge, {
                      label: "Sandbox",
                      color: "warning",
                      variant: "soft",
                      class: "rounded-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    _push3(`<!---->`);
                  }
                  _push3(ssrRenderComponent(_component_UBadge, {
                    label: `${unref(itemCount)} item`,
                    color: "neutral",
                    variant: "soft",
                    class: "rounded-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex items-center gap-2" }, [
                      unref(midtrans).env === "sandbox" ? (openBlock(), createBlock(_component_UBadge, {
                        key: 0,
                        label: "Sandbox",
                        color: "warning",
                        variant: "soft",
                        class: "rounded-full"
                      })) : createCommentVNode("", true),
                      createVNode(_component_UBadge, {
                        label: `${unref(itemCount)} item`,
                        color: "neutral",
                        variant: "soft",
                        class: "rounded-full"
                      }, null, 8, ["label"])
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UPageBody, { class: "p-5" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="grid grid-cols-1 gap-5 lg:grid-cols-12"${_scopeId2}><div class="space-y-5 lg:col-span-8"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_sfc_main$3, {
                    addresses: unref(addresses),
                    "pickup-location": unref(pickup),
                    "shipping-fee": unref(cart)?.shipping ?? 0,
                    "onUpdate:payload": ($event) => addressPayload.value = $event,
                    "onUpdate:isValid": ($event) => isAddressValid.value = $event,
                    "onUpdate:rate": ($event) => selectedRate.value = $event
                  }, null, _parent3, _scopeId2));
                  _push3(ssrRenderComponent(_sfc_main$4, {
                    items: unref(items),
                    cart: unref(cart)
                  }, null, _parent3, _scopeId2));
                  _push3(ssrRenderComponent(_sfc_main$2, {
                    saldo: unref(saldo),
                    total: total.value,
                    "midtrans-client-key": unref(midtrans).client_key,
                    "model-value": selectedMethod.value,
                    "onUpdate:modelValue": ($event) => selectedMethod.value = $event
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div class="lg:col-span-4"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_sfc_main$1, {
                    cart: unref(cart),
                    saldo: unref(saldo),
                    "selected-plan": selectedPlan.value,
                    "selected-method": selectedMethod.value,
                    "selected-rate": selectedRate.value,
                    "is-self-pickup": isSelfPickup.value,
                    "is-address-valid": isAddressValid.value,
                    "is-submitting": unref(isSubmitting),
                    "error-message": unref(errorMessage),
                    "midtrans-env": unref(midtrans).env,
                    "shipping-cost": shippingCost.value,
                    total: total.value,
                    "onUpdate:selectedPlan": ($event) => selectedPlan.value = $event,
                    onPay: payNow
                  }, null, _parent3, _scopeId2));
                  _push3(`</div></div>`);
                } else {
                  return [
                    createVNode("div", { class: "grid grid-cols-1 gap-5 lg:grid-cols-12" }, [
                      createVNode("div", { class: "space-y-5 lg:col-span-8" }, [
                        createVNode(_sfc_main$3, {
                          addresses: unref(addresses),
                          "pickup-location": unref(pickup),
                          "shipping-fee": unref(cart)?.shipping ?? 0,
                          "onUpdate:payload": ($event) => addressPayload.value = $event,
                          "onUpdate:isValid": ($event) => isAddressValid.value = $event,
                          "onUpdate:rate": ($event) => selectedRate.value = $event
                        }, null, 8, ["addresses", "pickup-location", "shipping-fee", "onUpdate:payload", "onUpdate:isValid", "onUpdate:rate"]),
                        createVNode(_sfc_main$4, {
                          items: unref(items),
                          cart: unref(cart)
                        }, null, 8, ["items", "cart"]),
                        createVNode(_sfc_main$2, {
                          saldo: unref(saldo),
                          total: total.value,
                          "midtrans-client-key": unref(midtrans).client_key,
                          "model-value": selectedMethod.value,
                          "onUpdate:modelValue": ($event) => selectedMethod.value = $event
                        }, null, 8, ["saldo", "total", "midtrans-client-key", "model-value", "onUpdate:modelValue"])
                      ]),
                      createVNode("div", { class: "lg:col-span-4" }, [
                        createVNode(_sfc_main$1, {
                          cart: unref(cart),
                          saldo: unref(saldo),
                          "selected-plan": selectedPlan.value,
                          "selected-method": selectedMethod.value,
                          "selected-rate": selectedRate.value,
                          "is-self-pickup": isSelfPickup.value,
                          "is-address-valid": isAddressValid.value,
                          "is-submitting": unref(isSubmitting),
                          "error-message": unref(errorMessage),
                          "midtrans-env": unref(midtrans).env,
                          "shipping-cost": shippingCost.value,
                          total: total.value,
                          "onUpdate:selectedPlan": ($event) => selectedPlan.value = $event,
                          onPay: payNow
                        }, null, 8, ["cart", "saldo", "selected-plan", "selected-method", "selected-rate", "is-self-pickup", "is-address-valid", "is-submitting", "error-message", "midtrans-env", "shipping-cost", "total", "onUpdate:selectedPlan"])
                      ])
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UPageHeader, {
                class: "p-5",
                title: "Checkout",
                description: "Alamat → Pembayaran → Konfirmasi"
              }, {
                right: withCtx(() => [
                  createVNode("div", { class: "flex items-center gap-2" }, [
                    unref(midtrans).env === "sandbox" ? (openBlock(), createBlock(_component_UBadge, {
                      key: 0,
                      label: "Sandbox",
                      color: "warning",
                      variant: "soft",
                      class: "rounded-full"
                    })) : createCommentVNode("", true),
                    createVNode(_component_UBadge, {
                      label: `${unref(itemCount)} item`,
                      color: "neutral",
                      variant: "soft",
                      class: "rounded-full"
                    }, null, 8, ["label"])
                  ])
                ]),
                _: 1
              }),
              createVNode(_component_UPageBody, { class: "p-5" }, {
                default: withCtx(() => [
                  createVNode("div", { class: "grid grid-cols-1 gap-5 lg:grid-cols-12" }, [
                    createVNode("div", { class: "space-y-5 lg:col-span-8" }, [
                      createVNode(_sfc_main$3, {
                        addresses: unref(addresses),
                        "pickup-location": unref(pickup),
                        "shipping-fee": unref(cart)?.shipping ?? 0,
                        "onUpdate:payload": ($event) => addressPayload.value = $event,
                        "onUpdate:isValid": ($event) => isAddressValid.value = $event,
                        "onUpdate:rate": ($event) => selectedRate.value = $event
                      }, null, 8, ["addresses", "pickup-location", "shipping-fee", "onUpdate:payload", "onUpdate:isValid", "onUpdate:rate"]),
                      createVNode(_sfc_main$4, {
                        items: unref(items),
                        cart: unref(cart)
                      }, null, 8, ["items", "cart"]),
                      createVNode(_sfc_main$2, {
                        saldo: unref(saldo),
                        total: total.value,
                        "midtrans-client-key": unref(midtrans).client_key,
                        "model-value": selectedMethod.value,
                        "onUpdate:modelValue": ($event) => selectedMethod.value = $event
                      }, null, 8, ["saldo", "total", "midtrans-client-key", "model-value", "onUpdate:modelValue"])
                    ]),
                    createVNode("div", { class: "lg:col-span-4" }, [
                      createVNode(_sfc_main$1, {
                        cart: unref(cart),
                        saldo: unref(saldo),
                        "selected-plan": selectedPlan.value,
                        "selected-method": selectedMethod.value,
                        "selected-rate": selectedRate.value,
                        "is-self-pickup": isSelfPickup.value,
                        "is-address-valid": isAddressValid.value,
                        "is-submitting": unref(isSubmitting),
                        "error-message": unref(errorMessage),
                        "midtrans-env": unref(midtrans).env,
                        "shipping-cost": shippingCost.value,
                        total: total.value,
                        "onUpdate:selectedPlan": ($event) => selectedPlan.value = $event,
                        onPay: payNow
                      }, null, 8, ["cart", "saldo", "selected-plan", "selected-method", "selected-rate", "is-self-pickup", "is-address-valid", "is-submitting", "error-message", "midtrans-env", "shipping-cost", "total", "onUpdate:selectedPlan"])
                    ])
                  ])
                ]),
                _: 1
              })
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Checkout/Index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
