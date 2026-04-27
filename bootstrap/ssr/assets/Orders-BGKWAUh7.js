import { _ as _sfc_main$7 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$f } from "./Textarea-DFfHqaZw.js";
import { _ as _sfc_main$5 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$6 } from "./SelectMenu-D4PFVS1f.js";
import { _ as _sfc_main$e } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$c } from "./Modal-BB5lXp8z.js";
import { _ as _sfc_main$8 } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$d } from "./Alert-BRMLOsUb.js";
import { ref, watch, computed, onBeforeUnmount, nextTick, defineComponent, mergeProps, withCtx, createTextVNode, useSSRContext, onMounted, createVNode, toDisplayString, openBlock, createBlock, createCommentVNode, Fragment, renderList, unref, isRef } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList, ssrRenderAttr } from "vue/server-renderer";
import { usePage, router } from "@inertiajs/vue3";
import { u as useToast } from "./useToast-CTuSIf9f.js";
import { _ as _sfc_main$4 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$b } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$a } from "./Empty-20F1kDsa.js";
import { _ as _sfc_main$9 } from "./Skeleton-COFz1HE7.js";
import "defu";
import "reka-ui";
import "@vueuse/core";
import "ufo";
import "./usePortal-EQErrF6h.js";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
const DEFAULT_ORDERS_FILTERS = {
  q: "",
  status: "all",
  sort: "newest"
};
function firstErrorMessage(errors) {
  const first = Object.values(errors).find((value) => value !== void 0);
  if (Array.isArray(first)) {
    return first[0] ?? "Request gagal.";
  }
  return first ?? "Request gagal.";
}
function useDashboardOrders(options) {
  const toast = useToast();
  const page = usePage();
  const allOrders = ref([]);
  const currentPage = ref(1);
  const nextPage = ref(null);
  const hasMore = ref(false);
  const isLoadingMore = ref(false);
  const isApplyingFilter = ref(false);
  const checkingPaymentOrderId = ref(null);
  const payingOrderId = ref(null);
  const reviewingOrderItemId = ref(null);
  const pendingReviewCountFromServer = ref(0);
  const hasInitializedFilter = ref(false);
  const isSyncingFilters = ref(false);
  let searchDebounceTimer = null;
  const isDetailOpen = ref(false);
  const selectedOrder = ref(null);
  const isReviewModalOpen = ref(false);
  const reviewTargetOrder = ref(null);
  const reviewTargetItem = ref(null);
  const reviewRating = ref(5);
  const reviewTitle = ref("");
  const reviewComment = ref("");
  const q = ref("");
  const status = ref(DEFAULT_ORDERS_FILTERS.status);
  const sort = ref(DEFAULT_ORDERS_FILTERS.sort);
  function normalizeIncomingStatus(value) {
    const candidate = String(value ?? "").trim().toLowerCase();
    const allowed = ["all", "unpaid", "pending", "paid", "processing", "shipped", "delivered", "cancelled", "refunded"];
    return allowed.includes(candidate) ? candidate : DEFAULT_ORDERS_FILTERS.status;
  }
  function normalizeIncomingSort(value) {
    const candidate = String(value ?? "").trim().toLowerCase();
    const allowed = ["newest", "oldest", "highest", "lowest"];
    return allowed.includes(candidate) ? candidate : DEFAULT_ORDERS_FILTERS.sort;
  }
  function syncFiltersFromPayload(filters) {
    isSyncingFilters.value = true;
    q.value = String(filters?.q ?? DEFAULT_ORDERS_FILTERS.q);
    status.value = normalizeIncomingStatus(filters?.status);
    sort.value = normalizeIncomingSort(filters?.sort);
    setTimeout(() => {
      isSyncingFilters.value = false;
    }, 0);
  }
  watch(
    options.orders,
    (incoming) => {
      const incomingPage = incoming.current_page ?? 1;
      const incomingData = incoming.data ?? [];
      const incomingFilters = incoming.filters ?? {};
      if (incomingPage <= 1) {
        allOrders.value = [...incomingData];
      } else if (incomingPage > currentPage.value) {
        const existingKeys = new Set(allOrders.value.map((order) => String(order.code)));
        const appended = incomingData.filter((order) => !existingKeys.has(String(order.code)));
        allOrders.value = [...allOrders.value, ...appended];
      }
      currentPage.value = incomingPage;
      nextPage.value = incoming.next_page ?? null;
      hasMore.value = Boolean(incoming.has_more);
      pendingReviewCountFromServer.value = Math.max(0, Number(incoming.pending_review_count ?? 0));
      if (!hasInitializedFilter.value) {
        syncFiltersFromPayload(incomingFilters);
        hasInitializedFilter.value = true;
      } else if (incomingPage <= 1) {
        syncFiltersFromPayload(incomingFilters);
      }
    },
    { immediate: true }
  );
  const statusMeta = {
    pending: { label: "Menunggu", color: "warning", icon: "i-lucide-clock" },
    paid: { label: "Dibayar", color: "primary", icon: "i-lucide-badge-check" },
    processing: { label: "Diproses", color: "info", icon: "i-lucide-cog" },
    shipped: { label: "Dikirim", color: "info", icon: "i-lucide-truck" },
    delivered: { label: "Selesai", color: "success", icon: "i-lucide-check-circle-2" },
    cancelled: { label: "Dibatalkan", color: "neutral", icon: "i-lucide-x-circle" },
    refunded: { label: "Refund", color: "neutral", icon: "i-lucide-undo-2" }
  };
  const paymentMeta = {
    unpaid: { label: "Belum bayar", color: "warning", icon: "i-lucide-alert-circle" },
    paid: { label: "Lunas", color: "success", icon: "i-lucide-credit-card" },
    refunded: { label: "Refund", color: "neutral", icon: "i-lucide-undo-2" },
    failed: { label: "Gagal", color: "error", icon: "i-lucide-ban" }
  };
  const statusItems = computed(() => [
    { label: "Semua status", value: "all" },
    { label: "Belum bayar", value: "unpaid" },
    ...Object.entries(statusMeta).map(([value, meta]) => ({ label: meta.label, value }))
  ]);
  const sortItems = [
    { label: "Terbaru", value: "newest" },
    { label: "Terlama", value: "oldest" },
    { label: "Total tertinggi", value: "highest" },
    { label: "Total terendah", value: "lowest" }
  ];
  const reviewRatingItems = [
    { label: "5 - Sangat puas", value: 5 },
    { label: "4 - Puas", value: 4 },
    { label: "3 - Cukup", value: 3 },
    { label: "2 - Kurang puas", value: 2 },
    { label: "1 - Tidak puas", value: 1 }
  ];
  const formatCurrency = (value) => new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(value);
  const formatDateTime = (value) => {
    if (!value) {
      return "-";
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return value;
    }
    return new Intl.DateTimeFormat("id-ID", {
      day: "2-digit",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit"
    }).format(date);
  };
  const filtered = computed(() => [...allOrders.value]);
  const totalCount = computed(() => options.orders.value.total ?? allOrders.value.length);
  const shownCount = computed(() => filtered.value.length);
  const pendingReviewCount = computed(() => pendingReviewCountFromServer.value);
  const hasPendingReview = computed(() => pendingReviewCount.value > 0);
  const detailItems = computed(() => {
    if (!selectedOrder.value) {
      return [];
    }
    if ((selectedOrder.value.items?.length ?? 0) > 0) {
      return selectedOrder.value.items ?? [];
    }
    return selectedOrder.value.items_preview ?? [];
  });
  function reset() {
    q.value = DEFAULT_ORDERS_FILTERS.q;
    status.value = DEFAULT_ORDERS_FILTERS.status;
    sort.value = DEFAULT_ORDERS_FILTERS.sort;
  }
  function buildOrdersQuery(pageNumber) {
    const search = q.value.trim();
    const query = {
      section: "orders",
      orders_page: pageNumber
    };
    if (search !== "") {
      query.orders_q = search;
    }
    if (status.value !== "all") {
      query.orders_status = status.value;
    }
    if (sort.value !== "newest") {
      query.orders_sort = sort.value;
    }
    return query;
  }
  function requestOrders(pageNumber = 1) {
    if (pageNumber > 1) {
      if (isLoadingMore.value || isApplyingFilter.value) {
        return;
      }
      isLoadingMore.value = true;
    }
    isApplyingFilter.value = true;
    router.get("/dashboard", buildOrdersQuery(pageNumber), {
      only: ["orders"],
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        isApplyingFilter.value = false;
        isLoadingMore.value = false;
      }
    });
  }
  function loadMore() {
    if (!hasMore.value || !nextPage.value) {
      return;
    }
    requestOrders(nextPage.value);
  }
  function openDetail(order) {
    selectedOrder.value = { ...order };
    isDetailOpen.value = true;
  }
  function closeDetail() {
    isDetailOpen.value = false;
  }
  watch(isDetailOpen, (open) => {
    if (!open) {
      selectedOrder.value = null;
    }
  });
  watch(isReviewModalOpen, (open) => {
    if (!open) {
      reviewTargetOrder.value = null;
      reviewTargetItem.value = null;
      reviewRating.value = 5;
      reviewTitle.value = "";
      reviewComment.value = "";
      reviewingOrderItemId.value = null;
    }
  });
  watch(q, () => {
    if (!hasInitializedFilter.value || isSyncingFilters.value) {
      return;
    }
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
    }
    searchDebounceTimer = setTimeout(() => {
      requestOrders(1);
    }, 350);
  });
  watch([status, sort], () => {
    if (!hasInitializedFilter.value || isSyncingFilters.value) {
      return;
    }
    requestOrders(1);
  });
  onBeforeUnmount(() => {
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
      searchDebounceTimer = null;
    }
  });
  function isOrderUnpaid(order) {
    return ["unpaid", "failed"].includes(String(order.payment_status ?? "")) || order.payment_status == null && order.status === "pending";
  }
  function isMidtransOrder(order) {
    const code = String(order.payment_method_code ?? "").trim().toLowerCase();
    return ["p-001", "midtrans"].includes(code);
  }
  function canPayNow(order) {
    return isOrderUnpaid(order) && isMidtransOrder(order);
  }
  function canDownloadInvoice(order) {
    return Boolean(order.paid_at);
  }
  function invoiceDownloadUrl(order) {
    return `/dashboard/orders/${order.id}/invoice`;
  }
  function downloadInvoice(order) {
    if (!canDownloadInvoice(order)) {
      toast?.add?.({
        title: "Invoice belum tersedia",
        description: "Invoice hanya dapat diunduh untuk pesanan yang sudah dibayar.",
        color: "warning",
        icon: "i-lucide-file-warning"
      });
      return;
    }
    const url = invoiceDownloadUrl(order);
    const popup = window.open(url, "_blank", "noopener,noreferrer");
    if (popup) {
      return;
    }
    window.location.assign(url);
  }
  function getSnapScriptUrl() {
    const host = options.midtrans.value.env === "production" ? "https://app.midtrans.com" : "https://app.sandbox.midtrans.com";
    return `${host}/snap/snap.js`;
  }
  async function ensureSnapLoaded() {
    if (window.snap?.pay) {
      return true;
    }
    if (!options.midtrans.value.client_key) {
      return false;
    }
    return new Promise((resolve) => {
      const existingScript = document.querySelector('script[data-midtrans-snap="1"]');
      if (existingScript) {
        existingScript.addEventListener("load", () => resolve(!!window.snap?.pay));
        existingScript.addEventListener("error", () => resolve(false));
        return;
      }
      const script = document.createElement("script");
      script.src = getSnapScriptUrl();
      script.async = true;
      script.setAttribute("data-midtrans-snap", "1");
      script.setAttribute("data-client-key", options.midtrans.value.client_key);
      script.onload = () => resolve(!!window.snap?.pay);
      script.onerror = () => resolve(false);
      document.head.appendChild(script);
    });
  }
  async function inertiaPost(url, payload = {}, only = ["flash", "errors"]) {
    const csrfToken = String(page.props.csrf_token ?? "");
    return new Promise((resolve, reject) => {
      router.post(
        url,
        {
          _token: csrfToken,
          ...payload
        },
        {
          only,
          preserveState: true,
          preserveScroll: true,
          replace: true,
          onSuccess: (nextPage2) => {
            const props = nextPage2?.props ?? {};
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
  function normalizeImageUrl(url) {
    const raw = String(url ?? "").trim();
    if (raw === "") {
      return null;
    }
    if (raw.startsWith("data:image/")) {
      return raw;
    }
    if (raw.startsWith("http://") || raw.startsWith("https://")) {
      try {
        const parsed = new URL(raw);
        const normalizedPath = parsed.pathname.replace(/\/{2,}/g, "/");
        if (normalizedPath.startsWith("/storage/")) {
          return `${normalizedPath}${parsed.search}`;
        }
        return raw;
      } catch {
        return raw;
      }
    }
    const normalized = raw.replace(/\/{2,}/g, "/");
    if (normalized.startsWith("/storage/")) {
      return normalized;
    }
    if (normalized.startsWith("/")) {
      return normalized;
    }
    if (normalized.startsWith("public/storage/")) {
      return `/${normalized.slice("public/".length)}`;
    }
    if (normalized.startsWith("storage/")) {
      return `/${normalized}`;
    }
    if (normalized.startsWith("public/")) {
      return `/storage/${normalized.slice("public/".length)}`;
    }
    return `/storage/${normalized}`;
  }
  function shippingAddressLine(order) {
    const address = order.shipping_address;
    if (!address) {
      return "-";
    }
    return [
      address.address_line1,
      address.address_line2,
      address.district,
      address.city,
      address.province,
      address.postal_code,
      address.country
    ].filter((part) => typeof part === "string" && part.trim() !== "").join(", ");
  }
  function canReviewItem(order, item) {
    return Boolean(order) && Boolean(item.can_review) && !Boolean(item.is_reviewed);
  }
  function openReviewModal(order, item) {
    if (!canReviewItem(order, item)) {
      return;
    }
    reviewTargetOrder.value = { ...order };
    reviewTargetItem.value = { ...item };
    reviewRating.value = 5;
    reviewTitle.value = "";
    reviewComment.value = "";
    isReviewModalOpen.value = true;
  }
  function closeReviewModal() {
    isReviewModalOpen.value = false;
  }
  function replaceOrderState(updatedOrder) {
    const targetId = String(updatedOrder.id);
    const previousOrder = allOrders.value.find((order) => String(order.id) === targetId);
    const previousPendingCount = Number(previousOrder?.pending_review_count ?? 0);
    const nextPendingCount = Number(updatedOrder.pending_review_count ?? previousPendingCount);
    allOrders.value = allOrders.value.map((order) => {
      if (String(order.id) !== targetId) {
        return order;
      }
      return { ...order, ...updatedOrder };
    });
    if (selectedOrder.value && String(selectedOrder.value.id) === targetId) {
      selectedOrder.value = { ...selectedOrder.value, ...updatedOrder };
    }
    if (reviewTargetOrder.value && String(reviewTargetOrder.value.id) === targetId) {
      reviewTargetOrder.value = { ...reviewTargetOrder.value, ...updatedOrder };
    }
    const nextGlobalPendingReviewCount = pendingReviewCountFromServer.value + (nextPendingCount - previousPendingCount);
    pendingReviewCountFromServer.value = Math.max(0, nextGlobalPendingReviewCount);
  }
  async function submitReview() {
    if (reviewingOrderItemId.value !== null) {
      return;
    }
    if (!reviewTargetOrder.value || !reviewTargetItem.value) {
      return;
    }
    const normalizedComment = reviewComment.value.trim();
    if (normalizedComment.length < 3) {
      toast?.add?.({
        title: "Review belum lengkap",
        description: "Komentar review minimal 3 karakter.",
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    const itemId = String(reviewTargetItem.value.id);
    reviewingOrderItemId.value = itemId;
    try {
      const response = await inertiaPost(
        `/dashboard/orders/${reviewTargetOrder.value.id}/review`,
        {
          order_item_id: reviewTargetItem.value.id,
          rating: Number(reviewRating.value),
          title: reviewTitle.value.trim() || null,
          comment: normalizedComment
        },
        ["flash", "errors", "orders"]
      );
      const flash = response.flash?.orders;
      const flashPayload = flash?.payload ?? {};
      if (flashPayload.order) {
        replaceOrderState(flashPayload.order);
      } else {
        pendingReviewCountFromServer.value = Math.max(0, pendingReviewCountFromServer.value - 1);
      }
      closeReviewModal();
      toast?.add?.({
        title: "Review berhasil dikirim",
        description: flash?.message ?? "Terima kasih, review Anda menunggu persetujuan admin.",
        color: "success",
        icon: "i-lucide-star"
      });
    } catch (error) {
      const message = error instanceof Error ? error.message : "Gagal mengirim review produk.";
      toast?.add?.({
        title: "Review gagal dikirim",
        description: message,
        color: "error",
        icon: "i-lucide-x-circle"
      });
    } finally {
      if (reviewingOrderItemId.value === itemId) {
        reviewingOrderItemId.value = null;
      }
    }
  }
  async function checkPaymentStatus(order) {
    if (checkingPaymentOrderId.value !== null) {
      return;
    }
    checkingPaymentOrderId.value = String(order.id);
    try {
      const response = await inertiaPost(
        `/dashboard/orders/${order.id}/payment-status`,
        {},
        ["flash", "errors", "orders"]
      );
      const flash = response.flash?.orders;
      const flashPayload = flash?.payload ?? {};
      if (flashPayload.order) {
        replaceOrderState(flashPayload.order);
      }
      toast?.add?.({
        title: "Status pembayaran diperbarui",
        description: flash?.message ?? "Status terbaru berhasil dimuat.",
        color: "success",
        icon: "i-lucide-badge-check"
      });
    } catch (error) {
      const message = error instanceof Error ? error.message : "Gagal memeriksa status pembayaran.";
      toast?.add?.({
        title: "Cek status gagal",
        description: message,
        color: "error",
        icon: "i-lucide-x-circle"
      });
    } finally {
      checkingPaymentOrderId.value = null;
    }
  }
  async function payNow(order) {
    if (payingOrderId.value !== null) {
      return;
    }
    if (!canPayNow(order)) {
      return;
    }
    if (!options.midtrans.value.client_key) {
      toast?.add?.({
        title: "Midtrans belum aktif",
        description: "Client key Midtrans belum tersedia.",
        color: "error",
        icon: "i-lucide-x-circle"
      });
      return;
    }
    payingOrderId.value = String(order.id);
    try {
      if (isDetailOpen.value && selectedOrder.value && String(selectedOrder.value.id) === String(order.id)) {
        isDetailOpen.value = false;
        await nextTick();
      }
      const response = await inertiaPost(`/dashboard/orders/${order.id}/pay-now`);
      const flash = response.flash?.orders;
      const payload = flash?.payload ?? {};
      if (payload.redirectUrl) {
        window.location.assign(payload.redirectUrl);
        return;
      }
      if (!payload.snapToken) {
        throw new Error(flash?.message ?? "Token Midtrans tidak tersedia untuk order ini.");
      }
      const snapLoaded = await ensureSnapLoaded();
      if (!snapLoaded) {
        throw new Error("Midtrans Snap gagal dimuat. Periksa konfigurasi client key.");
      }
      payingOrderId.value = null;
      window.snap?.pay(payload.snapToken, {
        onSuccess: () => {
          router.visit(payload.successUrl ?? "/dashboard");
        },
        onPending: () => {
          router.visit(payload.pendingUrl ?? payload.successUrl ?? "/dashboard");
        },
        onError: () => {
          toast?.add?.({
            title: "Pembayaran gagal",
            description: "Terjadi kesalahan saat memproses pembayaran Midtrans.",
            color: "error",
            icon: "i-lucide-x-circle"
          });
        },
        onClose: () => {
          toast?.add?.({
            title: "Pembayaran dibatalkan",
            description: "Kamu menutup popup pembayaran.",
            color: "warning",
            icon: "i-lucide-alert-circle"
          });
        }
      });
    } catch (error) {
      const message = error instanceof Error ? error.message : "Gagal membuka pembayaran Midtrans.";
      toast?.add?.({
        title: "Bayar sekarang gagal",
        description: message,
        color: "error",
        icon: "i-lucide-x-circle"
      });
    } finally {
      if (payingOrderId.value === String(order.id)) {
        payingOrderId.value = null;
      }
    }
  }
  return {
    allOrders,
    hasMore,
    nextPage,
    isLoadingMore,
    checkingPaymentOrderId,
    payingOrderId,
    reviewingOrderItemId,
    isDetailOpen,
    selectedOrder,
    isReviewModalOpen,
    reviewTargetOrder,
    reviewTargetItem,
    reviewRating,
    reviewTitle,
    reviewComment,
    q,
    status,
    sort,
    statusMeta,
    paymentMeta,
    statusItems,
    sortItems,
    reviewRatingItems,
    filtered,
    totalCount,
    shownCount,
    pendingReviewCount,
    hasPendingReview,
    detailItems,
    formatCurrency,
    formatDateTime,
    reset,
    loadMore,
    openDetail,
    closeDetail,
    canReviewItem,
    openReviewModal,
    closeReviewModal,
    isOrderUnpaid,
    canPayNow,
    canDownloadInvoice,
    downloadInvoice,
    checkPaymentStatus,
    payNow,
    submitReview,
    normalizeImageUrl,
    shippingAddressLine
  };
}
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "OrdersFiltersBar",
  __ssrInlineRender: true,
  props: {
    shownCount: {},
    totalCount: {},
    q: {},
    status: {},
    sort: {},
    statusItems: {},
    sortItems: {}
  },
  emits: ["update:q", "update:status", "update:sort", "reset"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function onSearchUpdate(value) {
      emit("update:q", String(value ?? ""));
    }
    function normalizeSelectValue(value) {
      if (Array.isArray(value)) {
        return normalizeSelectValue(value[0] ?? "");
      }
      if (typeof value === "string" || typeof value === "number") {
        return String(value);
      }
      if (value && typeof value === "object") {
        const payload = value;
        if (payload.value !== void 0) {
          return String(payload.value ?? "");
        }
        if (payload.id !== void 0) {
          return String(payload.id ?? "");
        }
        if (payload.code !== void 0) {
          return String(payload.code ?? "");
        }
        if (payload.key !== void 0) {
          return String(payload.key ?? "");
        }
        if (typeof payload.label === "string") {
          return payload.label;
        }
      }
      return "";
    }
    function onStatusUpdate(value) {
      const normalized = normalizeSelectValue(value).trim().toLowerCase();
      const allowed = ["all", "unpaid", "pending", "paid", "processing", "shipped", "delivered", "cancelled", "refunded"];
      const labels = {
        "semua status": "all",
        "belum bayar": "unpaid",
        "menunggu pembayaran": "unpaid",
        menunggu: "pending",
        dibayar: "paid",
        diproses: "processing",
        dikirim: "shipped",
        selesai: "delivered",
        dibatalkan: "cancelled",
        refund: "refunded"
      };
      const normalizedStatus = allowed.includes(normalized) ? normalized : labels[normalized] ?? props.status;
      emit("update:status", normalizedStatus);
    }
    function onSortUpdate(value) {
      const normalized = normalizeSelectValue(value).trim().toLowerCase();
      const allowed = ["newest", "oldest", "highest", "lowest"];
      const labels = {
        terbaru: "newest",
        terlama: "oldest",
        "total tertinggi": "highest",
        "total terendah": "lowest"
      };
      const normalizedSort = allowed.includes(normalized) ? normalized : labels[normalized] ?? props.sort;
      emit("update:sort", normalizedSort);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$4;
      const _component_UInput = _sfc_main$5;
      const _component_USelectMenu = _sfc_main$6;
      const _component_UButton = _sfc_main$7;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between" }, _attrs))}><div class="flex items-start gap-3"><div class="flex size-10 items-center justify-center rounded-2xl border border-default bg-elevated/60">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-package-search",
        class: "size-5 text-primary"
      }, null, _parent));
      _push(`</div><div class="min-w-0"><p class="text-base font-semibold text-highlighted">Order</p><p class="mt-0.5 text-xs text-muted">${ssrInterpolate(__props.shownCount)} dari ${ssrInterpolate(__props.totalCount)} pesanan </p></div></div><div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">`);
      _push(ssrRenderComponent(_component_UInput, {
        "model-value": props.q,
        icon: "i-lucide-search",
        placeholder: "Cari kode, nama, resi...",
        size: "sm",
        class: "w-full sm:w-64",
        "onUpdate:modelValue": onSearchUpdate
      }, null, _parent));
      _push(ssrRenderComponent(_component_USelectMenu, {
        "model-value": props.status,
        items: props.statusItems,
        "value-key": "value",
        "label-key": "label",
        size: "sm",
        class: "w-full sm:w-44",
        "onUpdate:modelValue": onStatusUpdate
      }, null, _parent));
      _push(ssrRenderComponent(_component_USelectMenu, {
        "model-value": props.sort,
        items: props.sortItems,
        "value-key": "value",
        "label-key": "label",
        size: "sm",
        class: "w-full sm:w-44",
        "onUpdate:modelValue": onSortUpdate
      }, null, _parent));
      if (props.q || props.status !== "all" || props.sort !== "newest") {
        _push(ssrRenderComponent(_component_UButton, {
          size: "sm",
          color: "neutral",
          variant: "ghost",
          icon: "i-lucide-rotate-ccw",
          onClick: ($event) => emit("reset")
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` Reset `);
            } else {
              return [
                createTextVNode(" Reset ")
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/orders/OrdersFiltersBar.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "OrdersListGrid",
  __ssrInlineRender: true,
  props: {
    loading: { type: Boolean, default: false },
    allOrders: {},
    filtered: {},
    statusMeta: {},
    paymentMeta: {},
    isLoadingMore: { type: Boolean },
    hasMore: { type: Boolean },
    payingOrderId: {},
    checkingPaymentOrderId: {},
    formatDateTime: {},
    formatCurrency: {},
    normalizeImageUrl: {},
    isOrderUnpaid: {},
    canPayNow: {},
    canDownloadInvoice: {},
    downloadInvoice: {},
    canReviewItem: {},
    openReviewModal: {}
  },
  emits: ["open-detail", "pay-now", "check-payment-status", "load-more", "reset"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const sentinel = ref(null);
    const failedImageKeys = ref(/* @__PURE__ */ new Set());
    let observer = null;
    function itemImageKey(itemId, rawUrl) {
      const normalizedUrl = props.normalizeImageUrl(rawUrl);
      if (!normalizedUrl) {
        return null;
      }
      return `${String(itemId)}::${normalizedUrl}`;
    }
    function itemImageSrc(itemId, rawUrl) {
      const imageKey = itemImageKey(itemId, rawUrl);
      if (!imageKey || failedImageKeys.value.has(imageKey)) {
        return null;
      }
      const normalizedUrl = props.normalizeImageUrl(rawUrl);
      return normalizedUrl;
    }
    function markItemImageAsFailed(itemId, rawUrl) {
      const imageKey = itemImageKey(itemId, rawUrl);
      if (!imageKey || failedImageKeys.value.has(imageKey)) {
        return;
      }
      failedImageKeys.value = /* @__PURE__ */ new Set([...failedImageKeys.value, imageKey]);
    }
    function observeSentinel() {
      if (!observer) {
        return;
      }
      observer.disconnect();
      if (sentinel.value) {
        observer.observe(sentinel.value);
      }
    }
    onMounted(() => {
      observer = new IntersectionObserver(
        (entries) => {
          if (entries[0]?.isIntersecting) {
            emit("load-more");
          }
        },
        { rootMargin: "420px" }
      );
      observeSentinel();
    });
    watch(sentinel, () => observeSentinel());
    watch(
      () => props.filtered.map((order) => `${order.id}:${order.items_preview?.length ?? 0}`).join("|"),
      () => {
        failedImageKeys.value = /* @__PURE__ */ new Set();
      }
    );
    onBeforeUnmount(() => {
      observer?.disconnect();
      observer = null;
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_USkeleton = _sfc_main$9;
      const _component_UEmpty = _sfc_main$a;
      const _component_UButton = _sfc_main$7;
      const _component_UBadge = _sfc_main$b;
      const _component_UIcon = _sfc_main$4;
      if (props.loading && props.allOrders.length === 0) {
        _push(`<div${ssrRenderAttrs(mergeProps({ class: "p-4 sm:p-6" }, _attrs))}><div class="grid gap-3"><!--[-->`);
        ssrRenderList(4, (i) => {
          _push(ssrRenderComponent(_component_UCard, {
            key: i,
            class: "rounded-2xl",
            ui: { body: "p-4" }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div class="w-full space-y-2"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_USkeleton, { class: "h-4 w-52" }, null, _parent2, _scopeId));
                _push2(ssrRenderComponent(_component_USkeleton, { class: "h-3 w-72" }, null, _parent2, _scopeId));
                _push2(ssrRenderComponent(_component_USkeleton, { class: "h-3 w-56" }, null, _parent2, _scopeId));
                _push2(`</div>`);
                _push2(ssrRenderComponent(_component_USkeleton, { class: "h-7 w-24 rounded-xl" }, null, _parent2, _scopeId));
                _push2(`</div><div class="mt-4 grid gap-2 sm:grid-cols-3"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_USkeleton, { class: "h-12 w-full rounded-2xl" }, null, _parent2, _scopeId));
                _push2(ssrRenderComponent(_component_USkeleton, { class: "h-12 w-full rounded-2xl" }, null, _parent2, _scopeId));
                _push2(ssrRenderComponent(_component_USkeleton, { class: "h-12 w-full rounded-2xl" }, null, _parent2, _scopeId));
                _push2(`</div>`);
              } else {
                return [
                  createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                    createVNode("div", { class: "w-full space-y-2" }, [
                      createVNode(_component_USkeleton, { class: "h-4 w-52" }),
                      createVNode(_component_USkeleton, { class: "h-3 w-72" }),
                      createVNode(_component_USkeleton, { class: "h-3 w-56" })
                    ]),
                    createVNode(_component_USkeleton, { class: "h-7 w-24 rounded-xl" })
                  ]),
                  createVNode("div", { class: "mt-4 grid gap-2 sm:grid-cols-3" }, [
                    createVNode(_component_USkeleton, { class: "h-12 w-full rounded-2xl" }),
                    createVNode(_component_USkeleton, { class: "h-12 w-full rounded-2xl" }),
                    createVNode(_component_USkeleton, { class: "h-12 w-full rounded-2xl" })
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
        });
        _push(`<!--]--></div></div>`);
      } else if (props.filtered.length === 0) {
        _push(ssrRenderComponent(_component_UEmpty, mergeProps({
          icon: "i-lucide-package-search",
          title: "Tidak ada pesanan ditemukan",
          description: "Coba ubah kata kunci atau filter.",
          variant: "outline",
          size: "lg",
          ui: { root: "rounded-2xl py-14" }
        }, _attrs), {
          actions: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(ssrRenderComponent(_component_UButton, {
                size: "sm",
                color: "neutral",
                variant: "outline",
                icon: "i-lucide-rotate-ccw",
                onClick: ($event) => emit("reset")
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Reset filter `);
                  } else {
                    return [
                      createTextVNode(" Reset filter ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              return [
                createVNode(_component_UButton, {
                  size: "sm",
                  color: "neutral",
                  variant: "outline",
                  icon: "i-lucide-rotate-ccw",
                  onClick: ($event) => emit("reset")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Reset filter ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<div${ssrRenderAttrs(_attrs)}><div class="grid gap-3"><!--[-->`);
        ssrRenderList(props.filtered, (order) => {
          _push(ssrRenderComponent(_component_UCard, {
            key: order.code,
            class: "rounded-xl overflow-hidden",
            ui: { root: "group relative hover:bg-elevated/30 transition-colors", body: "p-3 sm:p-3" }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"${_scopeId}><div class="min-w-0"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}><p class="text-sm font-extrabold text-highlighted"${_scopeId}>#${ssrInterpolate(order.code)}</p>`);
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: props.statusMeta[order.status]?.color,
                  variant: "soft",
                  size: "sm",
                  class: "rounded-2xl"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(ssrRenderComponent(_component_UIcon, {
                        name: props.statusMeta[order.status]?.icon,
                        class: "mr-1 size-3.5"
                      }, null, _parent3, _scopeId2));
                      _push3(` ${ssrInterpolate(props.statusMeta[order.status]?.label)}`);
                    } else {
                      return [
                        createVNode(_component_UIcon, {
                          name: props.statusMeta[order.status]?.icon,
                          class: "mr-1 size-3.5"
                        }, null, 8, ["name"]),
                        createTextVNode(" " + toDisplayString(props.statusMeta[order.status]?.label), 1)
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                if (order.payment_status) {
                  _push2(ssrRenderComponent(_component_UBadge, {
                    color: props.paymentMeta[order.payment_status]?.color,
                    variant: "subtle",
                    size: "sm",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(ssrRenderComponent(_component_UIcon, {
                          name: props.paymentMeta[order.payment_status]?.icon,
                          class: "mr-1 size-3.5"
                        }, null, _parent3, _scopeId2));
                        _push3(` ${ssrInterpolate(props.paymentMeta[order.payment_status]?.label)}`);
                      } else {
                        return [
                          createVNode(_component_UIcon, {
                            name: props.paymentMeta[order.payment_status]?.icon,
                            class: "mr-1 size-3.5"
                          }, null, 8, ["name"]),
                          createTextVNode(" " + toDisplayString(props.paymentMeta[order.payment_status]?.label), 1)
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                if (order.tracking_number) {
                  _push2(ssrRenderComponent(_component_UBadge, {
                    color: "neutral",
                    variant: "subtle",
                    size: "sm",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(ssrRenderComponent(_component_UIcon, {
                          name: "i-lucide-scan-line",
                          class: "mr-1 size-3.5"
                        }, null, _parent3, _scopeId2));
                        _push3(` ${ssrInterpolate(order.tracking_number)}`);
                      } else {
                        return [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-scan-line",
                            class: "mr-1 size-3.5"
                          }),
                          createTextVNode(" " + toDisplayString(order.tracking_number), 1)
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                if ((order.pending_review_count ?? 0) > 0) {
                  _push2(ssrRenderComponent(_component_UBadge, {
                    color: "warning",
                    variant: "soft",
                    size: "sm",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(ssrRenderComponent(_component_UIcon, {
                          name: "i-lucide-star",
                          class: "mr-1 size-3.5"
                        }, null, _parent3, _scopeId2));
                        _push3(` ${ssrInterpolate(order.pending_review_count)} belum diulas `);
                      } else {
                        return [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-star",
                            class: "mr-1 size-3.5"
                          }),
                          createTextVNode(" " + toDisplayString(order.pending_review_count) + " belum diulas ", 1)
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div><div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted"${_scopeId}><span class="inline-flex items-center gap-1.5"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-calendar",
                  class: "size-3.5"
                }, null, _parent2, _scopeId));
                _push2(` ${ssrInterpolate(props.formatDateTime(order.created_at))}</span><span class="inline-flex items-center gap-1.5"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-shopping-bag",
                  class: "size-3.5"
                }, null, _parent2, _scopeId));
                _push2(` ${ssrInterpolate(order.items_count)} item </span>`);
                if (order.payment_method) {
                  _push2(`<span class="inline-flex items-center gap-1.5"${_scopeId}>`);
                  _push2(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-credit-card",
                    class: "size-3.5"
                  }, null, _parent2, _scopeId));
                  _push2(` ${ssrInterpolate(order.payment_method)}</span>`);
                } else {
                  _push2(`<!---->`);
                }
                if (order.shipping_method) {
                  _push2(`<span class="inline-flex items-center gap-1.5"${_scopeId}>`);
                  _push2(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-truck",
                    class: "size-3.5"
                  }, null, _parent2, _scopeId));
                  _push2(` ${ssrInterpolate(order.shipping_method)}</span>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div></div><div class="flex items-center justify-between sm:flex-col sm:items-end gap-2"${_scopeId}><p class="text-base font-black text-primary tabular-nums"${_scopeId}>${ssrInterpolate(props.formatCurrency(order.total))}</p><div class="flex flex-wrap items-center justify-end gap-2"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UButton, {
                  size: "xs",
                  color: "primary",
                  variant: "soft",
                  class: "rounded-2xl",
                  icon: "i-lucide-eye",
                  onClick: ($event) => emit("open-detail", order)
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(` Detail `);
                    } else {
                      return [
                        createTextVNode(" Detail ")
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                if (order.has_pending_review) {
                  _push2(ssrRenderComponent(_component_UButton, {
                    size: "xs",
                    color: "warning",
                    variant: "soft",
                    class: "rounded-2xl",
                    icon: "i-lucide-star",
                    onClick: ($event) => emit("open-detail", order)
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` Ulas Produk `);
                      } else {
                        return [
                          createTextVNode(" Ulas Produk ")
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                if (props.canDownloadInvoice(order)) {
                  _push2(ssrRenderComponent(_component_UButton, {
                    size: "xs",
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-2xl",
                    icon: "i-lucide-file-down",
                    onClick: ($event) => props.downloadInvoice(order)
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` Invoice `);
                      } else {
                        return [
                          createTextVNode(" Invoice ")
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                if (props.canPayNow(order)) {
                  _push2(ssrRenderComponent(_component_UButton, {
                    size: "xs",
                    color: "success",
                    variant: "solid",
                    class: "rounded-2xl",
                    icon: "i-lucide-wallet",
                    loading: props.payingOrderId === String(order.id),
                    onClick: ($event) => emit("pay-now", order)
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` Bayar Sekarang `);
                      } else {
                        return [
                          createTextVNode(" Bayar Sekarang ")
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                if (props.isOrderUnpaid(order)) {
                  _push2(ssrRenderComponent(_component_UButton, {
                    size: "xs",
                    color: "warning",
                    variant: "outline",
                    class: "rounded-2xl",
                    icon: "i-lucide-refresh-cw",
                    loading: props.checkingPaymentOrderId === String(order.id),
                    onClick: ($event) => emit("check-payment-status", order)
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` Cek Status Bayar `);
                      } else {
                        return [
                          createTextVNode(" Cek Status Bayar ")
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                if (order.tracking_number) {
                  _push2(ssrRenderComponent(_component_UButton, {
                    size: "xs",
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-2xl",
                    icon: "i-lucide-truck",
                    onClick: ($event) => emit("open-detail", order)
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` Lacak `);
                      } else {
                        return [
                          createTextVNode(" Lacak ")
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div></div></div>`);
                if (order.items_preview?.length) {
                  _push2(`<div class="mt-4"${_scopeId}><div class="flex items-center justify-between"${_scopeId}><p class="text-xs font-bold uppercase tracking-wider text-muted"${_scopeId}>Ringkasan item</p><p class="text-xs text-muted"${_scopeId}>Maks 3 ditampilkan</p></div><div class="mt-2 grid gap-2 sm:grid-cols-3"${_scopeId}><!--[-->`);
                  ssrRenderList(order.items_preview.slice(0, 3), (item) => {
                    _push2(ssrRenderComponent(_component_UCard, {
                      key: item.id,
                      class: "rounded-2xl",
                      ui: { body: "p-3" }
                    }, {
                      default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                        if (_push3) {
                          _push3(`<div class="flex items-center gap-3"${_scopeId2}><div class="size-10 shrink-0 overflow-hidden rounded-xl bg-elevated/40"${_scopeId2}>`);
                          if (itemImageSrc(item.id, item.image)) {
                            _push3(`<img${ssrRenderAttr("src", itemImageSrc(item.id, item.image) ?? void 0)}${ssrRenderAttr("alt", item.name)} class="h-full w-full object-cover" loading="lazy"${_scopeId2}>`);
                          } else {
                            _push3(`<div class="flex h-full w-full items-center justify-center"${_scopeId2}>`);
                            _push3(ssrRenderComponent(_component_UIcon, {
                              name: "i-lucide-image",
                              class: "size-5 text-muted"
                            }, null, _parent3, _scopeId2));
                            _push3(`</div>`);
                          }
                          _push3(`</div><div class="min-w-0 flex-1"${_scopeId2}><p class="truncate text-sm font-semibold text-highlighted"${_scopeId2}>${ssrInterpolate(item.name)}</p><p class="mt-0.5 truncate text-xs text-muted"${_scopeId2}>`);
                          if (item.variant) {
                            _push3(`<span${_scopeId2}>${ssrInterpolate(item.variant)} · </span>`);
                          } else {
                            _push3(`<!---->`);
                          }
                          _push3(`x${ssrInterpolate(item.qty)} <span class="mx-1"${_scopeId2}>·</span><span class="font-semibold tabular-nums text-highlighted/80"${_scopeId2}>${ssrInterpolate(props.formatCurrency(item.price))}</span></p></div>`);
                          if (props.canReviewItem(order, item)) {
                            _push3(ssrRenderComponent(_component_UButton, {
                              size: "xs",
                              color: "warning",
                              variant: "soft",
                              class: "rounded-xl",
                              icon: "i-lucide-star",
                              onClick: ($event) => props.openReviewModal(order, item)
                            }, {
                              default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                                if (_push4) {
                                  _push4(` Ulas `);
                                } else {
                                  return [
                                    createTextVNode(" Ulas ")
                                  ];
                                }
                              }),
                              _: 2
                            }, _parent3, _scopeId2));
                          } else {
                            _push3(`<!---->`);
                          }
                          _push3(`</div>`);
                        } else {
                          return [
                            createVNode("div", { class: "flex items-center gap-3" }, [
                              createVNode("div", { class: "size-10 shrink-0 overflow-hidden rounded-xl bg-elevated/40" }, [
                                itemImageSrc(item.id, item.image) ? (openBlock(), createBlock("img", {
                                  key: 0,
                                  src: itemImageSrc(item.id, item.image) ?? void 0,
                                  alt: item.name,
                                  class: "h-full w-full object-cover",
                                  loading: "lazy",
                                  onError: ($event) => markItemImageAsFailed(item.id, item.image)
                                }, null, 40, ["src", "alt", "onError"])) : (openBlock(), createBlock("div", {
                                  key: 1,
                                  class: "flex h-full w-full items-center justify-center"
                                }, [
                                  createVNode(_component_UIcon, {
                                    name: "i-lucide-image",
                                    class: "size-5 text-muted"
                                  })
                                ]))
                              ]),
                              createVNode("div", { class: "min-w-0 flex-1" }, [
                                createVNode("p", { class: "truncate text-sm font-semibold text-highlighted" }, toDisplayString(item.name), 1),
                                createVNode("p", { class: "mt-0.5 truncate text-xs text-muted" }, [
                                  item.variant ? (openBlock(), createBlock("span", { key: 0 }, toDisplayString(item.variant) + " · ", 1)) : createCommentVNode("", true),
                                  createTextVNode("x" + toDisplayString(item.qty) + " ", 1),
                                  createVNode("span", { class: "mx-1" }, "·"),
                                  createVNode("span", { class: "font-semibold tabular-nums text-highlighted/80" }, toDisplayString(props.formatCurrency(item.price)), 1)
                                ])
                              ]),
                              props.canReviewItem(order, item) ? (openBlock(), createBlock(_component_UButton, {
                                key: 0,
                                size: "xs",
                                color: "warning",
                                variant: "soft",
                                class: "rounded-xl",
                                icon: "i-lucide-star",
                                onClick: ($event) => props.openReviewModal(order, item)
                              }, {
                                default: withCtx(() => [
                                  createTextVNode(" Ulas ")
                                ]),
                                _: 1
                              }, 8, ["onClick"])) : createCommentVNode("", true)
                            ])
                          ];
                        }
                      }),
                      _: 2
                    }, _parent2, _scopeId));
                  });
                  _push2(`<!--]--></div></div>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`<div class="mt-4 flex items-center justify-between text-xs text-muted"${_scopeId}><span class="inline-flex items-center gap-1.5"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-user",
                  class: "size-3.5"
                }, null, _parent2, _scopeId));
                _push2(` ${ssrInterpolate(order.customer?.name ?? "Guest")}</span><span class="hidden sm:inline-flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-mouse-pointer-click",
                  class: "size-3.5"
                }, null, _parent2, _scopeId));
                _push2(` Klik detail untuk info lengkap </span></div>`);
              } else {
                return [
                  createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between" }, [
                    createVNode("div", { class: "min-w-0" }, [
                      createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                        createVNode("p", { class: "text-sm font-extrabold text-highlighted" }, "#" + toDisplayString(order.code), 1),
                        createVNode(_component_UBadge, {
                          color: props.statusMeta[order.status]?.color,
                          variant: "soft",
                          size: "sm",
                          class: "rounded-2xl"
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_UIcon, {
                              name: props.statusMeta[order.status]?.icon,
                              class: "mr-1 size-3.5"
                            }, null, 8, ["name"]),
                            createTextVNode(" " + toDisplayString(props.statusMeta[order.status]?.label), 1)
                          ]),
                          _: 2
                        }, 1032, ["color"]),
                        order.payment_status ? (openBlock(), createBlock(_component_UBadge, {
                          key: 0,
                          color: props.paymentMeta[order.payment_status]?.color,
                          variant: "subtle",
                          size: "sm",
                          class: "rounded-2xl"
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_UIcon, {
                              name: props.paymentMeta[order.payment_status]?.icon,
                              class: "mr-1 size-3.5"
                            }, null, 8, ["name"]),
                            createTextVNode(" " + toDisplayString(props.paymentMeta[order.payment_status]?.label), 1)
                          ]),
                          _: 2
                        }, 1032, ["color"])) : createCommentVNode("", true),
                        order.tracking_number ? (openBlock(), createBlock(_component_UBadge, {
                          key: 1,
                          color: "neutral",
                          variant: "subtle",
                          size: "sm",
                          class: "rounded-2xl"
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_UIcon, {
                              name: "i-lucide-scan-line",
                              class: "mr-1 size-3.5"
                            }),
                            createTextVNode(" " + toDisplayString(order.tracking_number), 1)
                          ]),
                          _: 2
                        }, 1024)) : createCommentVNode("", true),
                        (order.pending_review_count ?? 0) > 0 ? (openBlock(), createBlock(_component_UBadge, {
                          key: 2,
                          color: "warning",
                          variant: "soft",
                          size: "sm",
                          class: "rounded-2xl"
                        }, {
                          default: withCtx(() => [
                            createVNode(_component_UIcon, {
                              name: "i-lucide-star",
                              class: "mr-1 size-3.5"
                            }),
                            createTextVNode(" " + toDisplayString(order.pending_review_count) + " belum diulas ", 1)
                          ]),
                          _: 2
                        }, 1024)) : createCommentVNode("", true)
                      ]),
                      createVNode("div", { class: "mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted" }, [
                        createVNode("span", { class: "inline-flex items-center gap-1.5" }, [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-calendar",
                            class: "size-3.5"
                          }),
                          createTextVNode(" " + toDisplayString(props.formatDateTime(order.created_at)), 1)
                        ]),
                        createVNode("span", { class: "inline-flex items-center gap-1.5" }, [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-shopping-bag",
                            class: "size-3.5"
                          }),
                          createTextVNode(" " + toDisplayString(order.items_count) + " item ", 1)
                        ]),
                        order.payment_method ? (openBlock(), createBlock("span", {
                          key: 0,
                          class: "inline-flex items-center gap-1.5"
                        }, [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-credit-card",
                            class: "size-3.5"
                          }),
                          createTextVNode(" " + toDisplayString(order.payment_method), 1)
                        ])) : createCommentVNode("", true),
                        order.shipping_method ? (openBlock(), createBlock("span", {
                          key: 1,
                          class: "inline-flex items-center gap-1.5"
                        }, [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-truck",
                            class: "size-3.5"
                          }),
                          createTextVNode(" " + toDisplayString(order.shipping_method), 1)
                        ])) : createCommentVNode("", true)
                      ])
                    ]),
                    createVNode("div", { class: "flex items-center justify-between sm:flex-col sm:items-end gap-2" }, [
                      createVNode("p", { class: "text-base font-black text-primary tabular-nums" }, toDisplayString(props.formatCurrency(order.total)), 1),
                      createVNode("div", { class: "flex flex-wrap items-center justify-end gap-2" }, [
                        createVNode(_component_UButton, {
                          size: "xs",
                          color: "primary",
                          variant: "soft",
                          class: "rounded-2xl",
                          icon: "i-lucide-eye",
                          onClick: ($event) => emit("open-detail", order)
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Detail ")
                          ]),
                          _: 1
                        }, 8, ["onClick"]),
                        order.has_pending_review ? (openBlock(), createBlock(_component_UButton, {
                          key: 0,
                          size: "xs",
                          color: "warning",
                          variant: "soft",
                          class: "rounded-2xl",
                          icon: "i-lucide-star",
                          onClick: ($event) => emit("open-detail", order)
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Ulas Produk ")
                          ]),
                          _: 1
                        }, 8, ["onClick"])) : createCommentVNode("", true),
                        props.canDownloadInvoice(order) ? (openBlock(), createBlock(_component_UButton, {
                          key: 1,
                          size: "xs",
                          color: "neutral",
                          variant: "outline",
                          class: "rounded-2xl",
                          icon: "i-lucide-file-down",
                          onClick: ($event) => props.downloadInvoice(order)
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Invoice ")
                          ]),
                          _: 1
                        }, 8, ["onClick"])) : createCommentVNode("", true),
                        props.canPayNow(order) ? (openBlock(), createBlock(_component_UButton, {
                          key: 2,
                          size: "xs",
                          color: "success",
                          variant: "solid",
                          class: "rounded-2xl",
                          icon: "i-lucide-wallet",
                          loading: props.payingOrderId === String(order.id),
                          onClick: ($event) => emit("pay-now", order)
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Bayar Sekarang ")
                          ]),
                          _: 1
                        }, 8, ["loading", "onClick"])) : createCommentVNode("", true),
                        props.isOrderUnpaid(order) ? (openBlock(), createBlock(_component_UButton, {
                          key: 3,
                          size: "xs",
                          color: "warning",
                          variant: "outline",
                          class: "rounded-2xl",
                          icon: "i-lucide-refresh-cw",
                          loading: props.checkingPaymentOrderId === String(order.id),
                          onClick: ($event) => emit("check-payment-status", order)
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Cek Status Bayar ")
                          ]),
                          _: 1
                        }, 8, ["loading", "onClick"])) : createCommentVNode("", true),
                        order.tracking_number ? (openBlock(), createBlock(_component_UButton, {
                          key: 4,
                          size: "xs",
                          color: "neutral",
                          variant: "outline",
                          class: "rounded-2xl",
                          icon: "i-lucide-truck",
                          onClick: ($event) => emit("open-detail", order)
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Lacak ")
                          ]),
                          _: 1
                        }, 8, ["onClick"])) : createCommentVNode("", true)
                      ])
                    ])
                  ]),
                  order.items_preview?.length ? (openBlock(), createBlock("div", {
                    key: 0,
                    class: "mt-4"
                  }, [
                    createVNode("div", { class: "flex items-center justify-between" }, [
                      createVNode("p", { class: "text-xs font-bold uppercase tracking-wider text-muted" }, "Ringkasan item"),
                      createVNode("p", { class: "text-xs text-muted" }, "Maks 3 ditampilkan")
                    ]),
                    createVNode("div", { class: "mt-2 grid gap-2 sm:grid-cols-3" }, [
                      (openBlock(true), createBlock(Fragment, null, renderList(order.items_preview.slice(0, 3), (item) => {
                        return openBlock(), createBlock(_component_UCard, {
                          key: item.id,
                          class: "rounded-2xl",
                          ui: { body: "p-3" }
                        }, {
                          default: withCtx(() => [
                            createVNode("div", { class: "flex items-center gap-3" }, [
                              createVNode("div", { class: "size-10 shrink-0 overflow-hidden rounded-xl bg-elevated/40" }, [
                                itemImageSrc(item.id, item.image) ? (openBlock(), createBlock("img", {
                                  key: 0,
                                  src: itemImageSrc(item.id, item.image) ?? void 0,
                                  alt: item.name,
                                  class: "h-full w-full object-cover",
                                  loading: "lazy",
                                  onError: ($event) => markItemImageAsFailed(item.id, item.image)
                                }, null, 40, ["src", "alt", "onError"])) : (openBlock(), createBlock("div", {
                                  key: 1,
                                  class: "flex h-full w-full items-center justify-center"
                                }, [
                                  createVNode(_component_UIcon, {
                                    name: "i-lucide-image",
                                    class: "size-5 text-muted"
                                  })
                                ]))
                              ]),
                              createVNode("div", { class: "min-w-0 flex-1" }, [
                                createVNode("p", { class: "truncate text-sm font-semibold text-highlighted" }, toDisplayString(item.name), 1),
                                createVNode("p", { class: "mt-0.5 truncate text-xs text-muted" }, [
                                  item.variant ? (openBlock(), createBlock("span", { key: 0 }, toDisplayString(item.variant) + " · ", 1)) : createCommentVNode("", true),
                                  createTextVNode("x" + toDisplayString(item.qty) + " ", 1),
                                  createVNode("span", { class: "mx-1" }, "·"),
                                  createVNode("span", { class: "font-semibold tabular-nums text-highlighted/80" }, toDisplayString(props.formatCurrency(item.price)), 1)
                                ])
                              ]),
                              props.canReviewItem(order, item) ? (openBlock(), createBlock(_component_UButton, {
                                key: 0,
                                size: "xs",
                                color: "warning",
                                variant: "soft",
                                class: "rounded-xl",
                                icon: "i-lucide-star",
                                onClick: ($event) => props.openReviewModal(order, item)
                              }, {
                                default: withCtx(() => [
                                  createTextVNode(" Ulas ")
                                ]),
                                _: 1
                              }, 8, ["onClick"])) : createCommentVNode("", true)
                            ])
                          ]),
                          _: 2
                        }, 1024);
                      }), 128))
                    ])
                  ])) : createCommentVNode("", true),
                  createVNode("div", { class: "mt-4 flex items-center justify-between text-xs text-muted" }, [
                    createVNode("span", { class: "inline-flex items-center gap-1.5" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-user",
                        class: "size-3.5"
                      }),
                      createTextVNode(" " + toDisplayString(order.customer?.name ?? "Guest"), 1)
                    ]),
                    createVNode("span", { class: "hidden sm:inline-flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-mouse-pointer-click",
                        class: "size-3.5"
                      }),
                      createTextVNode(" Klik detail untuk info lengkap ")
                    ])
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
        });
        _push(`<!--]--></div><div class="pt-8 text-center">`);
        if (props.isLoadingMore) {
          _push(`<div class="inline-flex items-center gap-2.5 text-sm text-muted">`);
          _push(ssrRenderComponent(_component_UIcon, {
            name: "i-lucide-loader-2",
            class: "size-4 animate-spin text-primary"
          }, null, _parent));
          _push(` Memuat pesanan... </div>`);
        } else if (props.hasMore) {
          _push(`<div class="space-y-2"><p class="text-xs text-muted">Scroll untuk memuat lebih banyak pesanan.</p>`);
          _push(ssrRenderComponent(_component_UButton, {
            size: "xs",
            color: "neutral",
            variant: "outline",
            onClick: ($event) => emit("load-more")
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(` Muat lebih banyak `);
              } else {
                return [
                  createTextVNode(" Muat lebih banyak ")
                ];
              }
            }),
            _: 1
          }, _parent));
          _push(`</div>`);
        } else {
          _push(ssrRenderComponent(_component_UBadge, {
            color: "neutral",
            variant: "subtle",
            class: "rounded-2xl"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-check-circle-2",
                  class: "mr-1 size-3.5 text-emerald-500"
                }, null, _parent2, _scopeId));
                _push2(` Semua pesanan sudah dimuat `);
              } else {
                return [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-check-circle-2",
                    class: "mr-1 size-3.5 text-emerald-500"
                  }),
                  createTextVNode(" Semua pesanan sudah dimuat ")
                ];
              }
            }),
            _: 1
          }, _parent));
        }
        _push(`</div></div>`);
      }
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/orders/OrdersListGrid.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "OrdersDetailModal",
  __ssrInlineRender: true,
  props: {
    open: { type: Boolean },
    selectedOrder: { default: null },
    detailItems: {},
    statusMeta: {},
    paymentMeta: {},
    checkingPaymentOrderId: {},
    payingOrderId: {},
    formatDateTime: {},
    formatCurrency: {},
    shippingAddressLine: {},
    normalizeImageUrl: {},
    isOrderUnpaid: {},
    canPayNow: {},
    canDownloadInvoice: {},
    downloadInvoice: {},
    canReviewItem: {},
    openReviewModal: {}
  },
  emits: ["update:open", "close", "check-payment-status", "pay-now"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const failedImageKeys = ref(/* @__PURE__ */ new Set());
    function itemImageKey(itemId, rawUrl) {
      const normalizedUrl = props.normalizeImageUrl(rawUrl);
      if (!normalizedUrl) {
        return null;
      }
      return `${String(itemId)}::${normalizedUrl}`;
    }
    function itemImageSrc(itemId, rawUrl) {
      const imageKey = itemImageKey(itemId, rawUrl);
      if (!imageKey || failedImageKeys.value.has(imageKey)) {
        return null;
      }
      return props.normalizeImageUrl(rawUrl);
    }
    function markItemImageAsFailed(itemId, rawUrl) {
      const imageKey = itemImageKey(itemId, rawUrl);
      if (!imageKey || failedImageKeys.value.has(imageKey)) {
        return;
      }
      failedImageKeys.value = /* @__PURE__ */ new Set([...failedImageKeys.value, imageKey]);
    }
    watch(
      () => props.selectedOrder?.id,
      () => {
        failedImageKeys.value = /* @__PURE__ */ new Set();
      }
    );
    function closeModal() {
      emit("close");
      emit("update:open", false);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$c;
      const _component_UBadge = _sfc_main$b;
      const _component_UIcon = _sfc_main$4;
      const _component_UCard = _sfc_main$8;
      const _component_UButton = _sfc_main$7;
      const _component_UEmpty = _sfc_main$a;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: __props.open,
        title: __props.selectedOrder ? `Detail Order #${__props.selectedOrder.code}` : "Detail Order",
        description: "Ringkasan pembayaran, pengiriman, dan item order.",
        scrollable: "",
        content: { class: "w-[calc(100vw-1rem)] sm:max-w-4xl lg:max-w-5xl" },
        ui: { body: "max-h-[72vh] overflow-y-auto px-4 sm:px-6", footer: "px-4 sm:px-6 pb-4" },
        "onUpdate:open": (value) => emit("update:open", value)
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (__props.selectedOrder) {
              _push2(`<div class="space-y-4"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UBadge, {
                color: __props.statusMeta[__props.selectedOrder.status]?.color,
                variant: "soft",
                class: "rounded-2xl"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UIcon, {
                      name: __props.statusMeta[__props.selectedOrder.status]?.icon,
                      class: "mr-1 size-3.5"
                    }, null, _parent3, _scopeId2));
                    _push3(` ${ssrInterpolate(__props.statusMeta[__props.selectedOrder.status]?.label)}`);
                  } else {
                    return [
                      createVNode(_component_UIcon, {
                        name: __props.statusMeta[__props.selectedOrder.status]?.icon,
                        class: "mr-1 size-3.5"
                      }, null, 8, ["name"]),
                      createTextVNode(" " + toDisplayString(__props.statusMeta[__props.selectedOrder.status]?.label), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              if (__props.selectedOrder.payment_status) {
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: __props.paymentMeta[__props.selectedOrder.payment_status]?.color,
                  variant: "subtle",
                  class: "rounded-2xl"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(ssrRenderComponent(_component_UIcon, {
                        name: __props.paymentMeta[__props.selectedOrder.payment_status]?.icon,
                        class: "mr-1 size-3.5"
                      }, null, _parent3, _scopeId2));
                      _push3(` ${ssrInterpolate(__props.paymentMeta[__props.selectedOrder.payment_status]?.label)}`);
                    } else {
                      return [
                        createVNode(_component_UIcon, {
                          name: __props.paymentMeta[__props.selectedOrder.payment_status]?.icon,
                          class: "mr-1 size-3.5"
                        }, null, 8, ["name"]),
                        createTextVNode(" " + toDisplayString(__props.paymentMeta[__props.selectedOrder.payment_status]?.label), 1)
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              if (__props.selectedOrder.tracking_number) {
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: "neutral",
                  variant: "subtle",
                  class: "rounded-2xl"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(ssrRenderComponent(_component_UIcon, {
                        name: "i-lucide-scan-line",
                        class: "mr-1 size-3.5"
                      }, null, _parent3, _scopeId2));
                      _push3(` ${ssrInterpolate(__props.selectedOrder.tracking_number)}`);
                    } else {
                      return [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-scan-line",
                          class: "mr-1 size-3.5"
                        }),
                        createTextVNode(" " + toDisplayString(__props.selectedOrder.tracking_number), 1)
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div class="grid gap-3 sm:grid-cols-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UCard, {
                class: "rounded-2xl",
                ui: { root: "border border-default bg-elevated/20", body: "p-3" }
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<p class="text-xs uppercase tracking-wider text-muted"${_scopeId2}>Informasi Order</p><div class="mt-2 space-y-1.5 text-sm"${_scopeId2}><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Tanggal Order</span><span class="font-medium text-highlighted"${_scopeId2}>${ssrInterpolate(__props.formatDateTime(__props.selectedOrder.created_at))}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Dibayar Pada</span><span class="font-medium text-highlighted"${_scopeId2}>${ssrInterpolate(__props.formatDateTime(__props.selectedOrder.paid_at))}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Metode Bayar</span><span class="font-medium text-highlighted"${_scopeId2}>${ssrInterpolate(__props.selectedOrder.payment_method ?? "-")}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Kurir</span><span class="font-medium text-highlighted"${_scopeId2}>${ssrInterpolate(__props.selectedOrder.shipping_method ?? "-")}</span></p></div>`);
                  } else {
                    return [
                      createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Informasi Order"),
                      createVNode("div", { class: "mt-2 space-y-1.5 text-sm" }, [
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Tanggal Order"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatDateTime(__props.selectedOrder.created_at)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Dibayar Pada"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatDateTime(__props.selectedOrder.paid_at)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Metode Bayar"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.selectedOrder.payment_method ?? "-"), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Kurir"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.selectedOrder.shipping_method ?? "-"), 1)
                        ])
                      ])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UCard, {
                class: "rounded-2xl",
                ui: { root: "border border-default bg-elevated/20", body: "p-3" }
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<p class="text-xs uppercase tracking-wider text-muted"${_scopeId2}>Ringkasan Biaya</p><div class="mt-2 space-y-1.5 text-sm"${_scopeId2}><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Subtotal</span><span class="font-medium text-highlighted"${_scopeId2}>${ssrInterpolate(__props.formatCurrency(__props.selectedOrder.subtotal ?? __props.selectedOrder.total))}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Ongkir</span><span class="font-medium text-highlighted"${_scopeId2}>${ssrInterpolate(__props.formatCurrency(__props.selectedOrder.shipping_cost ?? 0))}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Pajak</span><span class="font-medium text-highlighted"${_scopeId2}>${ssrInterpolate(__props.formatCurrency(__props.selectedOrder.tax_amount ?? 0))}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Diskon</span><span class="font-medium text-highlighted"${_scopeId2}>-${ssrInterpolate(__props.formatCurrency(__props.selectedOrder.discount_amount ?? 0))}</span></p><div class="border-t border-default pt-2"${_scopeId2}><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="font-semibold text-highlighted"${_scopeId2}>Total</span><span class="font-black text-primary tabular-nums"${_scopeId2}>${ssrInterpolate(__props.formatCurrency(__props.selectedOrder.total))}</span></p></div></div>`);
                  } else {
                    return [
                      createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Ringkasan Biaya"),
                      createVNode("div", { class: "mt-2 space-y-1.5 text-sm" }, [
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Subtotal"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatCurrency(__props.selectedOrder.subtotal ?? __props.selectedOrder.total)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Ongkir"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatCurrency(__props.selectedOrder.shipping_cost ?? 0)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Pajak"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatCurrency(__props.selectedOrder.tax_amount ?? 0)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Diskon"),
                          createVNode("span", { class: "font-medium text-highlighted" }, "-" + toDisplayString(__props.formatCurrency(__props.selectedOrder.discount_amount ?? 0)), 1)
                        ]),
                        createVNode("div", { class: "border-t border-default pt-2" }, [
                          createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                            createVNode("span", { class: "font-semibold text-highlighted" }, "Total"),
                            createVNode("span", { class: "font-black text-primary tabular-nums" }, toDisplayString(__props.formatCurrency(__props.selectedOrder.total)), 1)
                          ])
                        ])
                      ])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
              if (__props.selectedOrder.shipping_address) {
                _push2(ssrRenderComponent(_component_UCard, {
                  class: "rounded-2xl",
                  ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`<p class="text-xs uppercase tracking-wider text-muted"${_scopeId2}>Alamat Pengiriman</p><p class="mt-2 text-sm font-semibold text-highlighted"${_scopeId2}>${ssrInterpolate(__props.selectedOrder.shipping_address.recipient_name ?? "-")} <span class="font-normal text-muted"${_scopeId2}>· ${ssrInterpolate(__props.selectedOrder.shipping_address.recipient_phone ?? "-")}</span></p><p class="mt-1 text-sm text-muted"${_scopeId2}>${ssrInterpolate(__props.shippingAddressLine(__props.selectedOrder))}</p>`);
                    } else {
                      return [
                        createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Alamat Pengiriman"),
                        createVNode("p", { class: "mt-2 text-sm font-semibold text-highlighted" }, [
                          createTextVNode(toDisplayString(__props.selectedOrder.shipping_address.recipient_name ?? "-") + " ", 1),
                          createVNode("span", { class: "font-normal text-muted" }, "· " + toDisplayString(__props.selectedOrder.shipping_address.recipient_phone ?? "-"), 1)
                        ]),
                        createVNode("p", { class: "mt-1 text-sm text-muted" }, toDisplayString(__props.shippingAddressLine(__props.selectedOrder)), 1)
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              _push2(ssrRenderComponent(_component_UCard, {
                class: "rounded-2xl",
                ui: { root: "border border-default bg-elevated/20", body: "p-3" }
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<div class="flex items-center justify-between gap-3"${_scopeId2}><p class="text-xs uppercase tracking-wider text-muted"${_scopeId2}>Item Order</p>`);
                    _push3(ssrRenderComponent(_component_UBadge, {
                      color: "neutral",
                      variant: "subtle",
                      class: "rounded-xl"
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(`${ssrInterpolate(__props.detailItems.length)} item `);
                        } else {
                          return [
                            createTextVNode(toDisplayString(__props.detailItems.length) + " item ", 1)
                          ];
                        }
                      }),
                      _: 1
                    }, _parent3, _scopeId2));
                    _push3(`</div>`);
                    if (__props.detailItems.length) {
                      _push3(`<div class="mt-3 space-y-2"${_scopeId2}><!--[-->`);
                      ssrRenderList(__props.detailItems, (item) => {
                        _push3(`<div class="flex items-center gap-3 rounded-xl border border-default bg-default/10 p-2.5"${_scopeId2}><div class="size-14 shrink-0 overflow-hidden rounded-lg bg-elevated/60"${_scopeId2}>`);
                        if (itemImageSrc(item.id, item.image)) {
                          _push3(`<img${ssrRenderAttr("src", itemImageSrc(item.id, item.image) ?? void 0)}${ssrRenderAttr("alt", item.name)} class="h-full w-full object-cover" loading="lazy"${_scopeId2}>`);
                        } else {
                          _push3(`<div class="flex h-full w-full items-center justify-center"${_scopeId2}>`);
                          _push3(ssrRenderComponent(_component_UIcon, {
                            name: "i-lucide-image",
                            class: "size-5 text-muted"
                          }, null, _parent3, _scopeId2));
                          _push3(`</div>`);
                        }
                        _push3(`</div><div class="min-w-0 flex-1"${_scopeId2}><p class="truncate text-sm font-semibold text-highlighted"${_scopeId2}>${ssrInterpolate(item.name)}</p>`);
                        if (item.sku) {
                          _push3(`<p class="truncate text-xs text-muted"${_scopeId2}>SKU: ${ssrInterpolate(item.sku)}</p>`);
                        } else {
                          _push3(`<!---->`);
                        }
                        _push3(`<p class="truncate text-xs text-muted"${_scopeId2}>`);
                        if (item.variant) {
                          _push3(`<span${_scopeId2}>${ssrInterpolate(item.variant)} · </span>`);
                        } else {
                          _push3(`<!---->`);
                        }
                        _push3(` ${ssrInterpolate(item.qty)} x ${ssrInterpolate(__props.formatCurrency(item.price))}</p></div><div class="shrink-0 flex flex-col items-end gap-1"${_scopeId2}><p class="text-sm font-bold tabular-nums text-highlighted"${_scopeId2}>${ssrInterpolate(__props.formatCurrency(item.row_total ?? item.price * item.qty))}</p>`);
                        if (item.is_reviewed) {
                          _push3(ssrRenderComponent(_component_UBadge, {
                            color: item.review_is_approved ? "success" : "neutral",
                            variant: "soft",
                            size: "sm",
                            class: "rounded-xl"
                          }, {
                            default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                              if (_push4) {
                                _push4(ssrRenderComponent(_component_UIcon, {
                                  name: item.review_is_approved ? "i-lucide-check-circle-2" : "i-lucide-hourglass",
                                  class: "mr-1 size-3.5"
                                }, null, _parent4, _scopeId3));
                                _push4(` ${ssrInterpolate(item.review_is_approved ? "Sudah diulas" : "Menunggu approval")}`);
                              } else {
                                return [
                                  createVNode(_component_UIcon, {
                                    name: item.review_is_approved ? "i-lucide-check-circle-2" : "i-lucide-hourglass",
                                    class: "mr-1 size-3.5"
                                  }, null, 8, ["name"]),
                                  createTextVNode(" " + toDisplayString(item.review_is_approved ? "Sudah diulas" : "Menunggu approval"), 1)
                                ];
                              }
                            }),
                            _: 2
                          }, _parent3, _scopeId2));
                        } else {
                          _push3(`<!---->`);
                        }
                        if (__props.canReviewItem(__props.selectedOrder, item)) {
                          _push3(ssrRenderComponent(_component_UButton, {
                            size: "xs",
                            color: "warning",
                            variant: "soft",
                            class: "rounded-xl",
                            icon: "i-lucide-star",
                            onClick: ($event) => __props.openReviewModal(__props.selectedOrder, item)
                          }, {
                            default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                              if (_push4) {
                                _push4(` Ulas produk `);
                              } else {
                                return [
                                  createTextVNode(" Ulas produk ")
                                ];
                              }
                            }),
                            _: 2
                          }, _parent3, _scopeId2));
                        } else {
                          _push3(`<!---->`);
                        }
                        _push3(`</div></div>`);
                      });
                      _push3(`<!--]--></div>`);
                    } else {
                      _push3(ssrRenderComponent(_component_UEmpty, {
                        icon: "i-lucide-shopping-bag",
                        title: "Item order belum tersedia",
                        description: "Data item tidak ditemukan pada order ini.",
                        size: "sm",
                        variant: "outline",
                        ui: { root: "mt-3 rounded-xl py-8" }
                      }, null, _parent3, _scopeId2));
                    }
                  } else {
                    return [
                      createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                        createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Item Order"),
                        createVNode(_component_UBadge, {
                          color: "neutral",
                          variant: "subtle",
                          class: "rounded-xl"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(__props.detailItems.length) + " item ", 1)
                          ]),
                          _: 1
                        })
                      ]),
                      __props.detailItems.length ? (openBlock(), createBlock("div", {
                        key: 0,
                        class: "mt-3 space-y-2"
                      }, [
                        (openBlock(true), createBlock(Fragment, null, renderList(__props.detailItems, (item) => {
                          return openBlock(), createBlock("div", {
                            key: item.id,
                            class: "flex items-center gap-3 rounded-xl border border-default bg-default/10 p-2.5"
                          }, [
                            createVNode("div", { class: "size-14 shrink-0 overflow-hidden rounded-lg bg-elevated/60" }, [
                              itemImageSrc(item.id, item.image) ? (openBlock(), createBlock("img", {
                                key: 0,
                                src: itemImageSrc(item.id, item.image) ?? void 0,
                                alt: item.name,
                                class: "h-full w-full object-cover",
                                loading: "lazy",
                                onError: ($event) => markItemImageAsFailed(item.id, item.image)
                              }, null, 40, ["src", "alt", "onError"])) : (openBlock(), createBlock("div", {
                                key: 1,
                                class: "flex h-full w-full items-center justify-center"
                              }, [
                                createVNode(_component_UIcon, {
                                  name: "i-lucide-image",
                                  class: "size-5 text-muted"
                                })
                              ]))
                            ]),
                            createVNode("div", { class: "min-w-0 flex-1" }, [
                              createVNode("p", { class: "truncate text-sm font-semibold text-highlighted" }, toDisplayString(item.name), 1),
                              item.sku ? (openBlock(), createBlock("p", {
                                key: 0,
                                class: "truncate text-xs text-muted"
                              }, "SKU: " + toDisplayString(item.sku), 1)) : createCommentVNode("", true),
                              createVNode("p", { class: "truncate text-xs text-muted" }, [
                                item.variant ? (openBlock(), createBlock("span", { key: 0 }, toDisplayString(item.variant) + " · ", 1)) : createCommentVNode("", true),
                                createTextVNode(" " + toDisplayString(item.qty) + " x " + toDisplayString(__props.formatCurrency(item.price)), 1)
                              ])
                            ]),
                            createVNode("div", { class: "shrink-0 flex flex-col items-end gap-1" }, [
                              createVNode("p", { class: "text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(__props.formatCurrency(item.row_total ?? item.price * item.qty)), 1),
                              item.is_reviewed ? (openBlock(), createBlock(_component_UBadge, {
                                key: 0,
                                color: item.review_is_approved ? "success" : "neutral",
                                variant: "soft",
                                size: "sm",
                                class: "rounded-xl"
                              }, {
                                default: withCtx(() => [
                                  createVNode(_component_UIcon, {
                                    name: item.review_is_approved ? "i-lucide-check-circle-2" : "i-lucide-hourglass",
                                    class: "mr-1 size-3.5"
                                  }, null, 8, ["name"]),
                                  createTextVNode(" " + toDisplayString(item.review_is_approved ? "Sudah diulas" : "Menunggu approval"), 1)
                                ]),
                                _: 2
                              }, 1032, ["color"])) : createCommentVNode("", true),
                              __props.canReviewItem(__props.selectedOrder, item) ? (openBlock(), createBlock(_component_UButton, {
                                key: 1,
                                size: "xs",
                                color: "warning",
                                variant: "soft",
                                class: "rounded-xl",
                                icon: "i-lucide-star",
                                onClick: ($event) => __props.openReviewModal(__props.selectedOrder, item)
                              }, {
                                default: withCtx(() => [
                                  createTextVNode(" Ulas produk ")
                                ]),
                                _: 1
                              }, 8, ["onClick"])) : createCommentVNode("", true)
                            ])
                          ]);
                        }), 128))
                      ])) : (openBlock(), createBlock(_component_UEmpty, {
                        key: 1,
                        icon: "i-lucide-shopping-bag",
                        title: "Item order belum tersedia",
                        description: "Data item tidak ditemukan pada order ini.",
                        size: "sm",
                        variant: "outline",
                        ui: { root: "mt-3 rounded-xl py-8" }
                      }))
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              if (__props.selectedOrder.notes) {
                _push2(ssrRenderComponent(_component_UCard, {
                  class: "rounded-2xl",
                  ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`<p class="text-xs uppercase tracking-wider text-muted"${_scopeId2}>Catatan Order</p><p class="mt-2 text-sm text-highlighted"${_scopeId2}>${ssrInterpolate(__props.selectedOrder.notes)}</p>`);
                    } else {
                      return [
                        createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Catatan Order"),
                        createVNode("p", { class: "mt-2 text-sm text-highlighted" }, toDisplayString(__props.selectedOrder.notes), 1)
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              __props.selectedOrder ? (openBlock(), createBlock("div", {
                key: 0,
                class: "space-y-4"
              }, [
                createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                  createVNode(_component_UBadge, {
                    color: __props.statusMeta[__props.selectedOrder.status]?.color,
                    variant: "soft",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UIcon, {
                        name: __props.statusMeta[__props.selectedOrder.status]?.icon,
                        class: "mr-1 size-3.5"
                      }, null, 8, ["name"]),
                      createTextVNode(" " + toDisplayString(__props.statusMeta[__props.selectedOrder.status]?.label), 1)
                    ]),
                    _: 1
                  }, 8, ["color"]),
                  __props.selectedOrder.payment_status ? (openBlock(), createBlock(_component_UBadge, {
                    key: 0,
                    color: __props.paymentMeta[__props.selectedOrder.payment_status]?.color,
                    variant: "subtle",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UIcon, {
                        name: __props.paymentMeta[__props.selectedOrder.payment_status]?.icon,
                        class: "mr-1 size-3.5"
                      }, null, 8, ["name"]),
                      createTextVNode(" " + toDisplayString(__props.paymentMeta[__props.selectedOrder.payment_status]?.label), 1)
                    ]),
                    _: 1
                  }, 8, ["color"])) : createCommentVNode("", true),
                  __props.selectedOrder.tracking_number ? (openBlock(), createBlock(_component_UBadge, {
                    key: 1,
                    color: "neutral",
                    variant: "subtle",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-scan-line",
                        class: "mr-1 size-3.5"
                      }),
                      createTextVNode(" " + toDisplayString(__props.selectedOrder.tracking_number), 1)
                    ]),
                    _: 1
                  })) : createCommentVNode("", true)
                ]),
                createVNode("div", { class: "grid gap-3 sm:grid-cols-2" }, [
                  createVNode(_component_UCard, {
                    class: "rounded-2xl",
                    ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                  }, {
                    default: withCtx(() => [
                      createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Informasi Order"),
                      createVNode("div", { class: "mt-2 space-y-1.5 text-sm" }, [
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Tanggal Order"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatDateTime(__props.selectedOrder.created_at)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Dibayar Pada"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatDateTime(__props.selectedOrder.paid_at)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Metode Bayar"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.selectedOrder.payment_method ?? "-"), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Kurir"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.selectedOrder.shipping_method ?? "-"), 1)
                        ])
                      ])
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UCard, {
                    class: "rounded-2xl",
                    ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                  }, {
                    default: withCtx(() => [
                      createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Ringkasan Biaya"),
                      createVNode("div", { class: "mt-2 space-y-1.5 text-sm" }, [
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Subtotal"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatCurrency(__props.selectedOrder.subtotal ?? __props.selectedOrder.total)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Ongkir"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatCurrency(__props.selectedOrder.shipping_cost ?? 0)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Pajak"),
                          createVNode("span", { class: "font-medium text-highlighted" }, toDisplayString(__props.formatCurrency(__props.selectedOrder.tax_amount ?? 0)), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Diskon"),
                          createVNode("span", { class: "font-medium text-highlighted" }, "-" + toDisplayString(__props.formatCurrency(__props.selectedOrder.discount_amount ?? 0)), 1)
                        ]),
                        createVNode("div", { class: "border-t border-default pt-2" }, [
                          createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                            createVNode("span", { class: "font-semibold text-highlighted" }, "Total"),
                            createVNode("span", { class: "font-black text-primary tabular-nums" }, toDisplayString(__props.formatCurrency(__props.selectedOrder.total)), 1)
                          ])
                        ])
                      ])
                    ]),
                    _: 1
                  })
                ]),
                __props.selectedOrder.shipping_address ? (openBlock(), createBlock(_component_UCard, {
                  key: 0,
                  class: "rounded-2xl",
                  ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                }, {
                  default: withCtx(() => [
                    createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Alamat Pengiriman"),
                    createVNode("p", { class: "mt-2 text-sm font-semibold text-highlighted" }, [
                      createTextVNode(toDisplayString(__props.selectedOrder.shipping_address.recipient_name ?? "-") + " ", 1),
                      createVNode("span", { class: "font-normal text-muted" }, "· " + toDisplayString(__props.selectedOrder.shipping_address.recipient_phone ?? "-"), 1)
                    ]),
                    createVNode("p", { class: "mt-1 text-sm text-muted" }, toDisplayString(__props.shippingAddressLine(__props.selectedOrder)), 1)
                  ]),
                  _: 1
                })) : createCommentVNode("", true),
                createVNode(_component_UCard, {
                  class: "rounded-2xl",
                  ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                }, {
                  default: withCtx(() => [
                    createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                      createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Item Order"),
                      createVNode(_component_UBadge, {
                        color: "neutral",
                        variant: "subtle",
                        class: "rounded-xl"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(toDisplayString(__props.detailItems.length) + " item ", 1)
                        ]),
                        _: 1
                      })
                    ]),
                    __props.detailItems.length ? (openBlock(), createBlock("div", {
                      key: 0,
                      class: "mt-3 space-y-2"
                    }, [
                      (openBlock(true), createBlock(Fragment, null, renderList(__props.detailItems, (item) => {
                        return openBlock(), createBlock("div", {
                          key: item.id,
                          class: "flex items-center gap-3 rounded-xl border border-default bg-default/10 p-2.5"
                        }, [
                          createVNode("div", { class: "size-14 shrink-0 overflow-hidden rounded-lg bg-elevated/60" }, [
                            itemImageSrc(item.id, item.image) ? (openBlock(), createBlock("img", {
                              key: 0,
                              src: itemImageSrc(item.id, item.image) ?? void 0,
                              alt: item.name,
                              class: "h-full w-full object-cover",
                              loading: "lazy",
                              onError: ($event) => markItemImageAsFailed(item.id, item.image)
                            }, null, 40, ["src", "alt", "onError"])) : (openBlock(), createBlock("div", {
                              key: 1,
                              class: "flex h-full w-full items-center justify-center"
                            }, [
                              createVNode(_component_UIcon, {
                                name: "i-lucide-image",
                                class: "size-5 text-muted"
                              })
                            ]))
                          ]),
                          createVNode("div", { class: "min-w-0 flex-1" }, [
                            createVNode("p", { class: "truncate text-sm font-semibold text-highlighted" }, toDisplayString(item.name), 1),
                            item.sku ? (openBlock(), createBlock("p", {
                              key: 0,
                              class: "truncate text-xs text-muted"
                            }, "SKU: " + toDisplayString(item.sku), 1)) : createCommentVNode("", true),
                            createVNode("p", { class: "truncate text-xs text-muted" }, [
                              item.variant ? (openBlock(), createBlock("span", { key: 0 }, toDisplayString(item.variant) + " · ", 1)) : createCommentVNode("", true),
                              createTextVNode(" " + toDisplayString(item.qty) + " x " + toDisplayString(__props.formatCurrency(item.price)), 1)
                            ])
                          ]),
                          createVNode("div", { class: "shrink-0 flex flex-col items-end gap-1" }, [
                            createVNode("p", { class: "text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(__props.formatCurrency(item.row_total ?? item.price * item.qty)), 1),
                            item.is_reviewed ? (openBlock(), createBlock(_component_UBadge, {
                              key: 0,
                              color: item.review_is_approved ? "success" : "neutral",
                              variant: "soft",
                              size: "sm",
                              class: "rounded-xl"
                            }, {
                              default: withCtx(() => [
                                createVNode(_component_UIcon, {
                                  name: item.review_is_approved ? "i-lucide-check-circle-2" : "i-lucide-hourglass",
                                  class: "mr-1 size-3.5"
                                }, null, 8, ["name"]),
                                createTextVNode(" " + toDisplayString(item.review_is_approved ? "Sudah diulas" : "Menunggu approval"), 1)
                              ]),
                              _: 2
                            }, 1032, ["color"])) : createCommentVNode("", true),
                            __props.canReviewItem(__props.selectedOrder, item) ? (openBlock(), createBlock(_component_UButton, {
                              key: 1,
                              size: "xs",
                              color: "warning",
                              variant: "soft",
                              class: "rounded-xl",
                              icon: "i-lucide-star",
                              onClick: ($event) => __props.openReviewModal(__props.selectedOrder, item)
                            }, {
                              default: withCtx(() => [
                                createTextVNode(" Ulas produk ")
                              ]),
                              _: 1
                            }, 8, ["onClick"])) : createCommentVNode("", true)
                          ])
                        ]);
                      }), 128))
                    ])) : (openBlock(), createBlock(_component_UEmpty, {
                      key: 1,
                      icon: "i-lucide-shopping-bag",
                      title: "Item order belum tersedia",
                      description: "Data item tidak ditemukan pada order ini.",
                      size: "sm",
                      variant: "outline",
                      ui: { root: "mt-3 rounded-xl py-8" }
                    }))
                  ]),
                  _: 1
                }),
                __props.selectedOrder.notes ? (openBlock(), createBlock(_component_UCard, {
                  key: 1,
                  class: "rounded-2xl",
                  ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                }, {
                  default: withCtx(() => [
                    createVNode("p", { class: "text-xs uppercase tracking-wider text-muted" }, "Catatan Order"),
                    createVNode("p", { class: "mt-2 text-sm text-highlighted" }, toDisplayString(__props.selectedOrder.notes), 1)
                  ]),
                  _: 1
                })) : createCommentVNode("", true)
              ])) : createCommentVNode("", true)
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              onClick: closeModal
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tutup `);
                } else {
                  return [
                    createTextVNode(" Tutup ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (__props.selectedOrder && __props.canDownloadInvoice(__props.selectedOrder)) {
              _push2(ssrRenderComponent(_component_UButton, {
                color: "neutral",
                variant: "outline",
                class: "rounded-xl",
                icon: "i-lucide-file-down",
                onClick: ($event) => __props.downloadInvoice(__props.selectedOrder)
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Download invoice `);
                  } else {
                    return [
                      createTextVNode(" Download invoice ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            if (__props.selectedOrder && __props.isOrderUnpaid(__props.selectedOrder)) {
              _push2(ssrRenderComponent(_component_UButton, {
                color: "warning",
                variant: "solid",
                class: "rounded-xl",
                icon: "i-lucide-refresh-cw",
                loading: __props.checkingPaymentOrderId === String(__props.selectedOrder.id),
                onClick: ($event) => emit("check-payment-status", __props.selectedOrder)
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Cek status pembayaran `);
                  } else {
                    return [
                      createTextVNode(" Cek status pembayaran ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
            if (__props.selectedOrder && __props.canPayNow(__props.selectedOrder)) {
              _push2(ssrRenderComponent(_component_UButton, {
                color: "success",
                variant: "solid",
                class: "rounded-xl",
                icon: "i-lucide-wallet",
                loading: __props.payingOrderId === String(__props.selectedOrder.id),
                onClick: ($event) => emit("pay-now", __props.selectedOrder)
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Bayar sekarang `);
                  } else {
                    return [
                      createTextVNode(" Bayar sekarang ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    onClick: closeModal
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tutup ")
                    ]),
                    _: 1
                  }),
                  __props.selectedOrder && __props.canDownloadInvoice(__props.selectedOrder) ? (openBlock(), createBlock(_component_UButton, {
                    key: 0,
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    icon: "i-lucide-file-down",
                    onClick: ($event) => __props.downloadInvoice(__props.selectedOrder)
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Download invoice ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])) : createCommentVNode("", true),
                  __props.selectedOrder && __props.isOrderUnpaid(__props.selectedOrder) ? (openBlock(), createBlock(_component_UButton, {
                    key: 1,
                    color: "warning",
                    variant: "solid",
                    class: "rounded-xl",
                    icon: "i-lucide-refresh-cw",
                    loading: __props.checkingPaymentOrderId === String(__props.selectedOrder.id),
                    onClick: ($event) => emit("check-payment-status", __props.selectedOrder)
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Cek status pembayaran ")
                    ]),
                    _: 1
                  }, 8, ["loading", "onClick"])) : createCommentVNode("", true)
                ]),
                __props.selectedOrder && __props.canPayNow(__props.selectedOrder) ? (openBlock(), createBlock(_component_UButton, {
                  key: 0,
                  color: "success",
                  variant: "solid",
                  class: "rounded-xl",
                  icon: "i-lucide-wallet",
                  loading: __props.payingOrderId === String(__props.selectedOrder.id),
                  onClick: ($event) => emit("pay-now", __props.selectedOrder)
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Bayar sekarang ")
                  ]),
                  _: 1
                }, 8, ["loading", "onClick"])) : createCommentVNode("", true)
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/orders/OrdersDetailModal.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Orders",
  __ssrInlineRender: true,
  props: {
    orders: { default: () => ({
      data: [],
      current_page: 1,
      next_page: null,
      has_more: false,
      per_page: 10,
      total: 0
    }) },
    midtrans: { default: () => ({
      env: "sandbox",
      client_key: ""
    }) },
    loading: { type: Boolean, default: false }
  },
  setup(__props) {
    const props = __props;
    const {
      allOrders,
      hasMore,
      isLoadingMore,
      checkingPaymentOrderId,
      payingOrderId,
      reviewingOrderItemId,
      isDetailOpen,
      selectedOrder,
      isReviewModalOpen,
      reviewTargetOrder,
      reviewTargetItem,
      reviewRating,
      reviewTitle,
      reviewComment,
      q,
      status,
      sort,
      statusMeta,
      paymentMeta,
      statusItems,
      sortItems,
      reviewRatingItems,
      filtered,
      totalCount,
      shownCount,
      pendingReviewCount,
      hasPendingReview,
      detailItems,
      formatCurrency,
      formatDateTime,
      reset,
      loadMore,
      openDetail,
      closeDetail,
      canReviewItem,
      openReviewModal,
      closeReviewModal,
      isOrderUnpaid,
      canPayNow,
      canDownloadInvoice,
      downloadInvoice,
      checkPaymentStatus,
      payNow,
      submitReview,
      normalizeImageUrl,
      shippingAddressLine
    } = useDashboardOrders({
      orders: computed(() => props.orders),
      midtrans: computed(() => props.midtrans)
    });
    function onSearchChange(value) {
      q.value = value;
    }
    function onStatusChange(value) {
      status.value = value;
    }
    function onSortChange(value) {
      sort.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UAlert = _sfc_main$d;
      const _component_UCard = _sfc_main$8;
      const _component_UModal = _sfc_main$c;
      const _component_UFormField = _sfc_main$e;
      const _component_USelectMenu = _sfc_main$6;
      const _component_UInput = _sfc_main$5;
      const _component_UTextarea = _sfc_main$f;
      const _component_UButton = _sfc_main$7;
      _push(`<!--[--><div class="space-y-4">`);
      if (unref(hasPendingReview)) {
        _push(ssrRenderComponent(_component_UAlert, {
          color: "warning",
          variant: "soft",
          icon: "i-lucide-star",
          title: "Masih ada produk yang belum direview",
          description: `Kamu masih punya ${unref(pendingReviewCount)} produk dari pesanan yang sudah diterima dan belum diulas.`
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-3xl overflow-hidden" }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$3, {
              "shown-count": unref(shownCount),
              "total-count": unref(totalCount),
              q: unref(q),
              status: unref(status),
              sort: unref(sort),
              "status-items": unref(statusItems),
              "sort-items": unref(sortItems),
              "onUpdate:q": onSearchChange,
              "onUpdate:status": onStatusChange,
              "onUpdate:sort": onSortChange,
              onReset: unref(reset)
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$3, {
                "shown-count": unref(shownCount),
                "total-count": unref(totalCount),
                q: unref(q),
                status: unref(status),
                sort: unref(sort),
                "status-items": unref(statusItems),
                "sort-items": unref(sortItems),
                "onUpdate:q": onSearchChange,
                "onUpdate:status": onStatusChange,
                "onUpdate:sort": onSortChange,
                onReset: unref(reset)
              }, null, 8, ["shown-count", "total-count", "q", "status", "sort", "status-items", "sort-items", "onReset"])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$2, {
              loading: __props.loading,
              "all-orders": unref(allOrders),
              filtered: unref(filtered),
              "status-meta": unref(statusMeta),
              "payment-meta": unref(paymentMeta),
              "is-loading-more": unref(isLoadingMore),
              "has-more": unref(hasMore),
              "paying-order-id": unref(payingOrderId),
              "checking-payment-order-id": unref(checkingPaymentOrderId),
              "format-date-time": unref(formatDateTime),
              "format-currency": unref(formatCurrency),
              "normalize-image-url": unref(normalizeImageUrl),
              "is-order-unpaid": unref(isOrderUnpaid),
              "can-pay-now": unref(canPayNow),
              "can-download-invoice": unref(canDownloadInvoice),
              "download-invoice": unref(downloadInvoice),
              "can-review-item": unref(canReviewItem),
              "open-review-modal": unref(openReviewModal),
              onOpenDetail: unref(openDetail),
              onPayNow: unref(payNow),
              onCheckPaymentStatus: unref(checkPaymentStatus),
              onLoadMore: unref(loadMore),
              onReset: unref(reset)
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$2, {
                loading: __props.loading,
                "all-orders": unref(allOrders),
                filtered: unref(filtered),
                "status-meta": unref(statusMeta),
                "payment-meta": unref(paymentMeta),
                "is-loading-more": unref(isLoadingMore),
                "has-more": unref(hasMore),
                "paying-order-id": unref(payingOrderId),
                "checking-payment-order-id": unref(checkingPaymentOrderId),
                "format-date-time": unref(formatDateTime),
                "format-currency": unref(formatCurrency),
                "normalize-image-url": unref(normalizeImageUrl),
                "is-order-unpaid": unref(isOrderUnpaid),
                "can-pay-now": unref(canPayNow),
                "can-download-invoice": unref(canDownloadInvoice),
                "download-invoice": unref(downloadInvoice),
                "can-review-item": unref(canReviewItem),
                "open-review-modal": unref(openReviewModal),
                onOpenDetail: unref(openDetail),
                onPayNow: unref(payNow),
                onCheckPaymentStatus: unref(checkPaymentStatus),
                onLoadMore: unref(loadMore),
                onReset: unref(reset)
              }, null, 8, ["loading", "all-orders", "filtered", "status-meta", "payment-meta", "is-loading-more", "has-more", "paying-order-id", "checking-payment-order-id", "format-date-time", "format-currency", "normalize-image-url", "is-order-unpaid", "can-pay-now", "can-download-invoice", "download-invoice", "can-review-item", "open-review-modal", "onOpenDetail", "onPayNow", "onCheckPaymentStatus", "onLoadMore", "onReset"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_sfc_main$1, {
        open: unref(isDetailOpen),
        "onUpdate:open": ($event) => isRef(isDetailOpen) ? isDetailOpen.value = $event : null,
        "selected-order": unref(selectedOrder),
        "detail-items": unref(detailItems),
        "status-meta": unref(statusMeta),
        "payment-meta": unref(paymentMeta),
        "checking-payment-order-id": unref(checkingPaymentOrderId),
        "paying-order-id": unref(payingOrderId),
        "format-date-time": unref(formatDateTime),
        "format-currency": unref(formatCurrency),
        "shipping-address-line": unref(shippingAddressLine),
        "normalize-image-url": unref(normalizeImageUrl),
        "is-order-unpaid": unref(isOrderUnpaid),
        "can-pay-now": unref(canPayNow),
        "can-download-invoice": unref(canDownloadInvoice),
        "download-invoice": unref(downloadInvoice),
        "can-review-item": unref(canReviewItem),
        "open-review-modal": unref(openReviewModal),
        onClose: unref(closeDetail),
        onCheckPaymentStatus: unref(checkPaymentStatus),
        onPayNow: unref(payNow)
      }, null, _parent));
      _push(ssrRenderComponent(_component_UModal, {
        open: unref(isReviewModalOpen),
        title: unref(reviewTargetItem) ? `Ulas Produk: ${unref(reviewTargetItem).name}` : "Ulas Produk",
        description: "Ulasan akan tampil setelah disetujui admin.",
        ui: { body: "space-y-4 p-4 sm:p-5", footer: "px-4 sm:px-5 pb-4" },
        "onUpdate:open": (value) => {
          if (!value) unref(closeReviewModal)();
        }
      }, {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (unref(reviewTargetOrder) && unref(reviewTargetItem)) {
              _push2(`<div class="space-y-4"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UAlert, {
                color: "primary",
                variant: "soft",
                icon: "i-lucide-package-check",
                title: `Order #${unref(reviewTargetOrder).code}`,
                description: `Qty ${unref(reviewTargetItem).qty} • ${unref(formatCurrency)(unref(reviewTargetItem).price)}`
              }, null, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, { label: "Rating" }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_USelectMenu, {
                      modelValue: unref(reviewRating),
                      "onUpdate:modelValue": ($event) => isRef(reviewRating) ? reviewRating.value = $event : null,
                      items: unref(reviewRatingItems),
                      "value-key": "value",
                      "label-key": "label",
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_USelectMenu, {
                        modelValue: unref(reviewRating),
                        "onUpdate:modelValue": ($event) => isRef(reviewRating) ? reviewRating.value = $event : null,
                        items: unref(reviewRatingItems),
                        "value-key": "value",
                        "label-key": "label",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, { label: "Judul review (opsional)" }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UInput, {
                      modelValue: unref(reviewTitle),
                      "onUpdate:modelValue": ($event) => isRef(reviewTitle) ? reviewTitle.value = $event : null,
                      placeholder: "Contoh: Produk sesuai deskripsi",
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UInput, {
                        modelValue: unref(reviewTitle),
                        "onUpdate:modelValue": ($event) => isRef(reviewTitle) ? reviewTitle.value = $event : null,
                        placeholder: "Contoh: Produk sesuai deskripsi",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, { label: "Komentar" }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UTextarea, {
                      modelValue: unref(reviewComment),
                      "onUpdate:modelValue": ($event) => isRef(reviewComment) ? reviewComment.value = $event : null,
                      rows: 4,
                      placeholder: "Tuliskan pengalamanmu menggunakan produk ini...",
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UTextarea, {
                        modelValue: unref(reviewComment),
                        "onUpdate:modelValue": ($event) => isRef(reviewComment) ? reviewComment.value = $event : null,
                        rows: 4,
                        placeholder: "Tuliskan pengalamanmu menggunakan produk ini...",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              unref(reviewTargetOrder) && unref(reviewTargetItem) ? (openBlock(), createBlock("div", {
                key: 0,
                class: "space-y-4"
              }, [
                createVNode(_component_UAlert, {
                  color: "primary",
                  variant: "soft",
                  icon: "i-lucide-package-check",
                  title: `Order #${unref(reviewTargetOrder).code}`,
                  description: `Qty ${unref(reviewTargetItem).qty} • ${unref(formatCurrency)(unref(reviewTargetItem).price)}`
                }, null, 8, ["title", "description"]),
                createVNode(_component_UFormField, { label: "Rating" }, {
                  default: withCtx(() => [
                    createVNode(_component_USelectMenu, {
                      modelValue: unref(reviewRating),
                      "onUpdate:modelValue": ($event) => isRef(reviewRating) ? reviewRating.value = $event : null,
                      items: unref(reviewRatingItems),
                      "value-key": "value",
                      "label-key": "label",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, { label: "Judul review (opsional)" }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: unref(reviewTitle),
                      "onUpdate:modelValue": ($event) => isRef(reviewTitle) ? reviewTitle.value = $event : null,
                      placeholder: "Contoh: Produk sesuai deskripsi",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, { label: "Komentar" }, {
                  default: withCtx(() => [
                    createVNode(_component_UTextarea, {
                      modelValue: unref(reviewComment),
                      "onUpdate:modelValue": ($event) => isRef(reviewComment) ? reviewComment.value = $event : null,
                      rows: 4,
                      placeholder: "Tuliskan pengalamanmu menggunakan produk ini...",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                })
              ])) : createCommentVNode("", true)
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              onClick: unref(closeReviewModal)
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Batal `);
                } else {
                  return [
                    createTextVNode(" Batal ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "solid",
              class: "rounded-xl",
              loading: unref(reviewingOrderItemId) === String(unref(reviewTargetItem)?.id ?? ""),
              onClick: unref(submitReview)
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Kirim review `);
                } else {
                  return [
                    createTextVNode(" Kirim review ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-xl",
                  onClick: unref(closeReviewModal)
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Batal ")
                  ]),
                  _: 1
                }, 8, ["onClick"]),
                createVNode(_component_UButton, {
                  color: "primary",
                  variant: "solid",
                  class: "rounded-xl",
                  loading: unref(reviewingOrderItemId) === String(unref(reviewTargetItem)?.id ?? ""),
                  onClick: unref(submitReview)
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Kirim review ")
                  ]),
                  _: 1
                }, 8, ["loading", "onClick"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Orders.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
