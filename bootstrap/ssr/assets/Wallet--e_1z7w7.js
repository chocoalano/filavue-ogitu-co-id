import { defineComponent, mergeProps, withCtx, createTextVNode, createVNode, toDisplayString, openBlock, createBlock, createCommentVNode, useSSRContext, useModel, mergeModels, ref, computed, onMounted, watch, onBeforeUnmount, unref, isRef } from "vue";
import { ssrRenderComponent, ssrInterpolate, ssrRenderAttrs, ssrRenderList, ssrRenderClass } from "vue/server-renderer";
import { _ as _sfc_main$9 } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$8 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$7 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$6 } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$c } from "./SelectMenu-D4PFVS1f.js";
import { _ as _sfc_main$b } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$a } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$d } from "./Skeleton-COFz1HE7.js";
import { u as useDashboard } from "./useDashboard-DR5F4MRN.js";
import { _ as _sfc_main$g } from "./Textarea-DFfHqaZw.js";
import { _ as _sfc_main$f } from "./Alert-BRMLOsUb.js";
import { _ as _sfc_main$e } from "./Modal-BB5lXp8z.js";
import { usePage, router } from "@inertiajs/vue3";
import { u as useToast } from "./useToast-CTuSIf9f.js";
import "reka-ui";
import "defu";
import "@vueuse/core";
import "ufo";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "./usePortal-EQErrF6h.js";
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "WalletSummaryCard",
  __ssrInlineRender: true,
  props: {
    formattedBalance: {},
    hasPendingWithdrawal: { type: Boolean }
  },
  emits: ["topup", "withdrawal"],
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$6;
      const _component_UIcon = _sfc_main$7;
      const _component_UButton = _sfc_main$8;
      const _component_UBadge = _sfc_main$9;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "overflow-hidden rounded-2xl",
        ui: { body: "p-0" }
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-4 p-4 sm:p-6 lg:flex-row lg:items-center lg:justify-between"${_scopeId}><div class="flex items-center gap-4"${_scopeId}><div class="rounded-xl bg-primary-50 p-3 dark:bg-primary-950"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-wallet",
              class: "size-8 text-primary-600 dark:text-primary-400"
            }, null, _parent2, _scopeId));
            _push2(`</div><div${_scopeId}><p class="text-sm font-medium text-gray-500 dark:text-gray-400"${_scopeId}>Saldo Wallet</p><h3 class="text-2xl font-bold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.formattedBalance)}</h3></div></div><div class="flex w-full flex-col gap-3 lg:w-auto lg:items-end"${_scopeId}><div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              icon: "i-lucide-circle-plus",
              class: "w-full rounded-xl",
              onClick: ($event) => _ctx.$emit("topup")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Topup Midtrans `);
                } else {
                  return [
                    createTextVNode(" Topup Midtrans ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              icon: "i-lucide-arrow-up-right",
              class: "w-full rounded-xl",
              disabled: __props.hasPendingWithdrawal,
              onClick: ($event) => _ctx.$emit("withdrawal")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tarik Wallet `);
                } else {
                  return [
                    createTextVNode(" Tarik Wallet ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            if (__props.hasPendingWithdrawal) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "warning",
                variant: "soft",
                class: "rounded-full lg:self-end"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Ada withdrawal yang masih menunggu proses. `);
                  } else {
                    return [
                      createTextVNode(" Ada withdrawal yang masih menunggu proses. ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-4 p-4 sm:p-6 lg:flex-row lg:items-center lg:justify-between" }, [
                createVNode("div", { class: "flex items-center gap-4" }, [
                  createVNode("div", { class: "rounded-xl bg-primary-50 p-3 dark:bg-primary-950" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-wallet",
                      class: "size-8 text-primary-600 dark:text-primary-400"
                    })
                  ]),
                  createVNode("div", null, [
                    createVNode("p", { class: "text-sm font-medium text-gray-500 dark:text-gray-400" }, "Saldo Wallet"),
                    createVNode("h3", { class: "text-2xl font-bold text-gray-900 dark:text-white" }, toDisplayString(__props.formattedBalance), 1)
                  ])
                ]),
                createVNode("div", { class: "flex w-full flex-col gap-3 lg:w-auto lg:items-end" }, [
                  createVNode("div", { class: "grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto" }, [
                    createVNode(_component_UButton, {
                      color: "primary",
                      icon: "i-lucide-circle-plus",
                      class: "w-full rounded-xl",
                      onClick: ($event) => _ctx.$emit("topup")
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Topup Midtrans ")
                      ]),
                      _: 1
                    }, 8, ["onClick"]),
                    createVNode(_component_UButton, {
                      color: "neutral",
                      variant: "outline",
                      icon: "i-lucide-arrow-up-right",
                      class: "w-full rounded-xl",
                      disabled: __props.hasPendingWithdrawal,
                      onClick: ($event) => _ctx.$emit("withdrawal")
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Tarik Wallet ")
                      ]),
                      _: 1
                    }, 8, ["disabled", "onClick"])
                  ]),
                  __props.hasPendingWithdrawal ? (openBlock(), createBlock(_component_UBadge, {
                    key: 0,
                    color: "warning",
                    variant: "soft",
                    class: "rounded-full lg:self-end"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Ada withdrawal yang masih menunggu proses. ")
                    ]),
                    _: 1
                  })) : createCommentVNode("", true)
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
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/wallet/WalletSummaryCard.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "WalletFilterCard",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    typeItems: {},
    statusItems: {},
    isApplying: { type: Boolean }
  }, {
    "search": { required: true },
    "searchModifiers": {},
    "type": { required: true },
    "typeModifiers": {},
    "status": { required: true },
    "statusModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["apply", "reset"], ["update:search", "update:type", "update:status"]),
  setup(__props) {
    const search = useModel(__props, "search");
    const type = useModel(__props, "type");
    const status = useModel(__props, "status");
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$6;
      const _component_UFormField = _sfc_main$a;
      const _component_UInput = _sfc_main$b;
      const _component_USelectMenu = _sfc_main$c;
      const _component_UButton = _sfc_main$8;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3 md:flex-row md:items-end"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Cari transaksi",
              class: "w-full md:flex-1"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: search.value,
                    "onUpdate:modelValue": ($event) => search.value = $event,
                    placeholder: "Ref transaksi, metode bayar, catatan...",
                    icon: "i-lucide-search",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: search.value,
                      "onUpdate:modelValue": ($event) => search.value = $event,
                      placeholder: "Ref transaksi, metode bayar, catatan...",
                      icon: "i-lucide-search",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Tipe",
              class: "w-full md:w-56"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: type.value,
                    "onUpdate:modelValue": ($event) => type.value = $event,
                    items: __props.typeItems,
                    "value-key": "value",
                    "label-key": "label",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      modelValue: type.value,
                      "onUpdate:modelValue": ($event) => type.value = $event,
                      items: __props.typeItems,
                      "value-key": "value",
                      "label-key": "label",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Status",
              class: "w-full md:w-56"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: status.value,
                    "onUpdate:modelValue": ($event) => status.value = $event,
                    items: __props.statusItems,
                    "value-key": "value",
                    "label-key": "label",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      modelValue: status.value,
                      "onUpdate:modelValue": ($event) => status.value = $event,
                      items: __props.statusItems,
                      "value-key": "value",
                      "label-key": "label",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="flex gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              icon: "i-lucide-filter",
              loading: __props.isApplying,
              onClick: ($event) => _ctx.$emit("apply")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Terapkan `);
                } else {
                  return [
                    createTextVNode(" Terapkan ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              icon: "i-lucide-rotate-ccw",
              onClick: ($event) => _ctx.$emit("reset")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Reset `);
                } else {
                  return [
                    createTextVNode(" Reset ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3 md:flex-row md:items-end" }, [
                createVNode(_component_UFormField, {
                  label: "Cari transaksi",
                  class: "w-full md:flex-1"
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: search.value,
                      "onUpdate:modelValue": ($event) => search.value = $event,
                      placeholder: "Ref transaksi, metode bayar, catatan...",
                      icon: "i-lucide-search",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, {
                  label: "Tipe",
                  class: "w-full md:w-56"
                }, {
                  default: withCtx(() => [
                    createVNode(_component_USelectMenu, {
                      modelValue: type.value,
                      "onUpdate:modelValue": ($event) => type.value = $event,
                      items: __props.typeItems,
                      "value-key": "value",
                      "label-key": "label",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, {
                  label: "Status",
                  class: "w-full md:w-56"
                }, {
                  default: withCtx(() => [
                    createVNode(_component_USelectMenu, {
                      modelValue: status.value,
                      "onUpdate:modelValue": ($event) => status.value = $event,
                      items: __props.statusItems,
                      "value-key": "value",
                      "label-key": "label",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ]),
                  _: 1
                }),
                createVNode("div", { class: "flex gap-2" }, [
                  createVNode(_component_UButton, {
                    color: "primary",
                    icon: "i-lucide-filter",
                    loading: __props.isApplying,
                    onClick: ($event) => _ctx.$emit("apply")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Terapkan ")
                    ]),
                    _: 1
                  }, 8, ["loading", "onClick"]),
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    icon: "i-lucide-rotate-ccw",
                    onClick: ($event) => _ctx.$emit("reset")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Reset ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])
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
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/wallet/WalletFilterCard.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const defaultWithdrawalAdminFee = 6500;
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "WalletTransactionList",
  __ssrInlineRender: true,
  props: {
    transactions: {},
    shownCount: {},
    totalCount: {},
    isLoadingMore: { type: Boolean },
    canLoadMore: { type: Boolean }
  },
  emits: ["loadMore"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const { formatIDR } = useDashboard();
    const typeMeta = {
      topup: { icon: "i-lucide-circle-plus", label: "Top Up Saldo" },
      withdrawal: { icon: "i-lucide-circle-minus", label: "Penarikan Saldo" },
      bonus: { icon: "i-lucide-hand-coins", label: "Bonus Member" },
      purchase: { icon: "i-lucide-shopping-bag", label: "Pembayaran Belanja" },
      refund: { icon: "i-lucide-undo-2", label: "Refund" },
      tax: { icon: "i-lucide-receipt-text", label: "Potongan Pajak" },
      other: { icon: "i-lucide-wallet", label: "Transaksi Wallet" }
    };
    const statusMeta = {
      pending: { color: "warning" },
      completed: { color: "success" },
      failed: { color: "error" },
      cancelled: { color: "neutral" }
    };
    const sentinel = ref(null);
    let observer = null;
    const hasTransactions = computed(() => props.transactions.length > 0);
    function amountClass(transaction) {
      return transaction.direction === "credit" ? "text-emerald-600 dark:text-emerald-400" : "text-rose-600 dark:text-rose-400";
    }
    function signedAmount(transaction) {
      const sign = transaction.direction === "credit" ? "+" : "-";
      return `${sign} ${formatIDR(Math.abs(Number(transaction.amount ?? 0)))}`;
    }
    function parseAdminFeeFromNotes(notes) {
      if (!notes) {
        return null;
      }
      const lines = notes.split(/\r?\n/);
      const matchedLine = lines.find((line) => /biaya admin/i.test(line));
      if (!matchedLine) {
        return null;
      }
      const numericValue = matchedLine.replace(/[^0-9]/g, "");
      if (numericValue === "") {
        return null;
      }
      const parsed = Number(numericValue);
      return Number.isFinite(parsed) ? parsed : null;
    }
    function withdrawalAdminFeeLabel(transaction) {
      if (transaction.type !== "withdrawal") {
        return null;
      }
      const adminFee = parseAdminFeeFromNotes(transaction.notes) ?? defaultWithdrawalAdminFee;
      return `Biaya admin: ${formatIDR(adminFee)}`;
    }
    function formatDateTime(value) {
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
          if (entries[0]?.isIntersecting && props.canLoadMore && !props.isLoadingMore) {
            emit("loadMore");
          }
        },
        { rootMargin: "420px" }
      );
      observeSentinel();
    });
    watch(sentinel, () => observeSentinel());
    onBeforeUnmount(() => {
      observer?.disconnect();
      observer = null;
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UBadge = _sfc_main$9;
      const _component_UCard = _sfc_main$6;
      const _component_UIcon = _sfc_main$7;
      const _component_USkeleton = _sfc_main$d;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-4" }, _attrs))}><div class="flex items-center justify-between px-1"><h4 class="font-semibold text-gray-900 dark:text-white">Riwayat Transaksi Wallet</h4>`);
      _push(ssrRenderComponent(_component_UBadge, {
        color: "neutral",
        variant: "soft",
        class: "rounded-full"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(__props.shownCount)} / ${ssrInterpolate(__props.totalCount)} transaksi `);
          } else {
            return [
              createTextVNode(toDisplayString(__props.shownCount) + " / " + toDisplayString(__props.totalCount) + " transaksi ", 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
      if (!hasTransactions.value) {
        _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl border-dashed border-2 border-gray-200 dark:border-gray-800 shadow-none" }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<div class="py-10 text-center text-gray-500"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-history",
                class: "mx-auto mb-3 size-12 opacity-20"
              }, null, _parent2, _scopeId));
              _push2(`<p${_scopeId}>Belum ada mutasi wallet untuk filter yang dipilih.</p></div>`);
            } else {
              return [
                createVNode("div", { class: "py-10 text-center text-gray-500" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-history",
                    class: "mx-auto mb-3 size-12 opacity-20"
                  }),
                  createVNode("p", null, "Belum ada mutasi wallet untuk filter yang dipilih.")
                ])
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<div class="space-y-3"><!--[-->`);
        ssrRenderList(__props.transactions, (transaction) => {
          _push(ssrRenderComponent(_component_UCard, {
            key: transaction.id,
            class: "rounded-xl transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"${_scopeId}><div class="flex min-w-0 items-start gap-3"${_scopeId}><div class="rounded-xl bg-gray-100 p-2 text-gray-700 dark:bg-gray-900 dark:text-gray-200"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: typeMeta[transaction.type]?.icon ?? typeMeta.other.icon,
                  class: "size-5"
                }, null, _parent2, _scopeId));
                _push2(`</div><div class="min-w-0"${_scopeId}><div class="flex items-center gap-2"${_scopeId}><p class="truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(transaction.type_label || typeMeta[transaction.type]?.label || typeMeta.other.label)}</p>`);
                _push2(ssrRenderComponent(_component_UBadge, {
                  size: "xs",
                  variant: "soft",
                  color: statusMeta[transaction.status]?.color ?? statusMeta.pending.color
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(transaction.status_label)}`);
                    } else {
                      return [
                        createTextVNode(toDisplayString(transaction.status_label), 1)
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                _push2(`</div><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(transaction.description)}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(formatDateTime(transaction.completed_at ?? transaction.created_at))}</p></div></div><div class="text-left sm:text-right"${_scopeId}><p class="${ssrRenderClass([amountClass(transaction), "text-sm font-bold"])}"${_scopeId}>${ssrInterpolate(signedAmount(transaction))}</p>`);
                if (withdrawalAdminFeeLabel(transaction)) {
                  _push2(`<p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(withdrawalAdminFeeLabel(transaction))}</p>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`<p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Saldo akhir: ${ssrInterpolate(unref(formatIDR)(transaction.balance_after))}</p></div></div><div class="mt-3 flex flex-wrap gap-2"${_scopeId}>`);
                if (transaction.transaction_ref) {
                  _push2(ssrRenderComponent(_component_UBadge, {
                    color: "neutral",
                    variant: "outline",
                    size: "xs",
                    class: "rounded-full"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` Ref: ${ssrInterpolate(transaction.transaction_ref)}`);
                      } else {
                        return [
                          createTextVNode(" Ref: " + toDisplayString(transaction.transaction_ref), 1)
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                if (transaction.payment_method) {
                  _push2(ssrRenderComponent(_component_UBadge, {
                    color: "neutral",
                    variant: "outline",
                    size: "xs",
                    class: "rounded-full"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(`${ssrInterpolate(transaction.payment_method)}`);
                      } else {
                        return [
                          createTextVNode(toDisplayString(transaction.payment_method), 1)
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div>`);
              } else {
                return [
                  createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between" }, [
                    createVNode("div", { class: "flex min-w-0 items-start gap-3" }, [
                      createVNode("div", { class: "rounded-xl bg-gray-100 p-2 text-gray-700 dark:bg-gray-900 dark:text-gray-200" }, [
                        createVNode(_component_UIcon, {
                          name: typeMeta[transaction.type]?.icon ?? typeMeta.other.icon,
                          class: "size-5"
                        }, null, 8, ["name"])
                      ]),
                      createVNode("div", { class: "min-w-0" }, [
                        createVNode("div", { class: "flex items-center gap-2" }, [
                          createVNode("p", { class: "truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(transaction.type_label || typeMeta[transaction.type]?.label || typeMeta.other.label), 1),
                          createVNode(_component_UBadge, {
                            size: "xs",
                            variant: "soft",
                            color: statusMeta[transaction.status]?.color ?? statusMeta.pending.color
                          }, {
                            default: withCtx(() => [
                              createTextVNode(toDisplayString(transaction.status_label), 1)
                            ]),
                            _: 2
                          }, 1032, ["color"])
                        ]),
                        createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(transaction.description), 1),
                        createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(formatDateTime(transaction.completed_at ?? transaction.created_at)), 1)
                      ])
                    ]),
                    createVNode("div", { class: "text-left sm:text-right" }, [
                      createVNode("p", {
                        class: ["text-sm font-bold", amountClass(transaction)]
                      }, toDisplayString(signedAmount(transaction)), 3),
                      withdrawalAdminFeeLabel(transaction) ? (openBlock(), createBlock("p", {
                        key: 0,
                        class: "mt-1 text-xs text-gray-500 dark:text-gray-400"
                      }, toDisplayString(withdrawalAdminFeeLabel(transaction)), 1)) : createCommentVNode("", true),
                      createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, " Saldo akhir: " + toDisplayString(unref(formatIDR)(transaction.balance_after)), 1)
                    ])
                  ]),
                  createVNode("div", { class: "mt-3 flex flex-wrap gap-2" }, [
                    transaction.transaction_ref ? (openBlock(), createBlock(_component_UBadge, {
                      key: 0,
                      color: "neutral",
                      variant: "outline",
                      size: "xs",
                      class: "rounded-full"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Ref: " + toDisplayString(transaction.transaction_ref), 1)
                      ]),
                      _: 2
                    }, 1024)) : createCommentVNode("", true),
                    transaction.payment_method ? (openBlock(), createBlock(_component_UBadge, {
                      key: 1,
                      color: "neutral",
                      variant: "outline",
                      size: "xs",
                      class: "rounded-full"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(toDisplayString(transaction.payment_method), 1)
                      ]),
                      _: 2
                    }, 1024)) : createCommentVNode("", true)
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
        });
        _push(`<!--]--></div>`);
      }
      _push(`<div class="h-1"></div>`);
      if (__props.isLoadingMore) {
        _push(`<div class="space-y-2">`);
        _push(ssrRenderComponent(_component_USkeleton, { class: "h-16 rounded-xl" }, null, _parent));
        _push(ssrRenderComponent(_component_USkeleton, { class: "h-16 rounded-xl" }, null, _parent));
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/wallet/WalletTransactionList.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "WalletTopupModal",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    loading: { type: Boolean },
    syncing: { type: Boolean },
    isWaConfirmed: { type: Boolean },
    waConfirmationUrl: {},
    hematMode: { type: Boolean }
  }, {
    "open": { type: Boolean, ...{ required: true } },
    "openModifiers": {},
    "amount": { required: true },
    "amountModifiers": {},
    "notes": { required: true },
    "notesModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["submit"], ["update:open", "update:amount", "update:notes"]),
  setup(__props) {
    const isOpen = useModel(__props, "open");
    const amount = useModel(__props, "amount");
    const notes = useModel(__props, "notes");
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$e;
      const _component_UAlert = _sfc_main$f;
      const _component_UButton = _sfc_main$8;
      const _component_UFormField = _sfc_main$a;
      const _component_UInput = _sfc_main$b;
      const _component_UTextarea = _sfc_main$g;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: isOpen.value,
        "onUpdate:open": ($event) => isOpen.value = $event,
        title: "Topup Wallet via Midtrans",
        description: "Masukkan nominal topup, lalu lanjutkan pembayaran di popup Midtrans."
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (!__props.hematMode && !__props.isWaConfirmed) {
              _push2(`<div class="space-y-4"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UAlert, {
                color: "warning",
                variant: "soft",
                icon: "i-lucide-message-circle-warning",
                title: "Verifikasi WhatsApp Diperlukan",
                description: "Nomor WhatsApp Anda belum terkonfirmasi. Konfirmasi terlebih dahulu untuk dapat menggunakan fitur Topup Wallet."
              }, null, _parent2, _scopeId));
              _push2(`<div class="flex justify-center"${_scopeId}>`);
              if (__props.waConfirmationUrl) {
                _push2(ssrRenderComponent(_component_UButton, {
                  color: "success",
                  icon: "i-lucide-message-circle",
                  to: __props.waConfirmationUrl,
                  target: "_blank"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(` Konfirmasi via WhatsApp `);
                    } else {
                      return [
                        createTextVNode(" Konfirmasi via WhatsApp ")
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div></div>`);
            } else {
              _push2(`<div class="space-y-4"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Nominal topup",
                required: ""
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UInput, {
                      modelValue: amount.value,
                      "onUpdate:modelValue": ($event) => amount.value = $event,
                      type: "number",
                      min: "10000",
                      step: "1000",
                      placeholder: "Contoh: 100000",
                      icon: "i-lucide-banknote",
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UInput, {
                        modelValue: amount.value,
                        "onUpdate:modelValue": ($event) => amount.value = $event,
                        type: "number",
                        min: "10000",
                        step: "1000",
                        placeholder: "Contoh: 100000",
                        icon: "i-lucide-banknote",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, { label: "Catatan (opsional)" }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UTextarea, {
                      modelValue: notes.value,
                      "onUpdate:modelValue": ($event) => notes.value = $event,
                      rows: 3,
                      placeholder: "Catatan tambahan untuk topup.",
                      class: "w-full"
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UTextarea, {
                        modelValue: notes.value,
                        "onUpdate:modelValue": ($event) => notes.value = $event,
                        rows: 3,
                        placeholder: "Catatan tambahan untuk topup.",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            }
          } else {
            return [
              !__props.hematMode && !__props.isWaConfirmed ? (openBlock(), createBlock("div", {
                key: 0,
                class: "space-y-4"
              }, [
                createVNode(_component_UAlert, {
                  color: "warning",
                  variant: "soft",
                  icon: "i-lucide-message-circle-warning",
                  title: "Verifikasi WhatsApp Diperlukan",
                  description: "Nomor WhatsApp Anda belum terkonfirmasi. Konfirmasi terlebih dahulu untuk dapat menggunakan fitur Topup Wallet."
                }),
                createVNode("div", { class: "flex justify-center" }, [
                  __props.waConfirmationUrl ? (openBlock(), createBlock(_component_UButton, {
                    key: 0,
                    color: "success",
                    icon: "i-lucide-message-circle",
                    to: __props.waConfirmationUrl,
                    target: "_blank"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Konfirmasi via WhatsApp ")
                    ]),
                    _: 1
                  }, 8, ["to"])) : createCommentVNode("", true)
                ])
              ])) : (openBlock(), createBlock("div", {
                key: 1,
                class: "space-y-4"
              }, [
                createVNode(_component_UFormField, {
                  label: "Nominal topup",
                  required: ""
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: amount.value,
                      "onUpdate:modelValue": ($event) => amount.value = $event,
                      type: "number",
                      min: "10000",
                      step: "1000",
                      placeholder: "Contoh: 100000",
                      icon: "i-lucide-banknote",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, { label: "Catatan (opsional)" }, {
                  default: withCtx(() => [
                    createVNode(_component_UTextarea, {
                      modelValue: notes.value,
                      "onUpdate:modelValue": ($event) => notes.value = $event,
                      rows: 3,
                      placeholder: "Catatan tambahan untuk topup.",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                })
              ]))
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex w-full justify-end gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              onClick: ($event) => isOpen.value = false
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
            if (__props.hematMode || __props.isWaConfirmed) {
              _push2(ssrRenderComponent(_component_UButton, {
                color: "primary",
                icon: "i-lucide-credit-card",
                loading: __props.loading || __props.syncing,
                onClick: ($event) => _ctx.$emit("submit")
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Bayar Topup `);
                  } else {
                    return [
                      createTextVNode(" Bayar Topup ")
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
              createVNode("div", { class: "flex w-full justify-end gap-2" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  onClick: ($event) => isOpen.value = false
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Batal ")
                  ]),
                  _: 1
                }, 8, ["onClick"]),
                __props.hematMode || __props.isWaConfirmed ? (openBlock(), createBlock(_component_UButton, {
                  key: 0,
                  color: "primary",
                  icon: "i-lucide-credit-card",
                  loading: __props.loading || __props.syncing,
                  onClick: ($event) => _ctx.$emit("submit")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Bayar Topup ")
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
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/wallet/WalletTopupModal.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const withdrawalStepAmount = 500;
const minimumWithdrawalAmount = 1e4;
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "WalletWithdrawalModal",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    loading: { type: Boolean },
    maxAmount: {},
    maxAmountLabel: { default: "" },
    adminFee: { default: 6500 },
    isWaConfirmed: { type: Boolean, default: false },
    waConfirmationUrl: { default: null },
    hematMode: { type: Boolean, default: false }
  }, {
    "open": { type: Boolean, ...{ required: true } },
    "openModifiers": {},
    "amount": { required: true },
    "amountModifiers": {},
    "password": { required: true },
    "passwordModifiers": {},
    "notes": { required: true },
    "notesModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["submit"], ["update:open", "update:amount", "update:password", "update:notes"]),
  setup(__props) {
    const isOpen = useModel(__props, "open");
    const amount = useModel(__props, "amount");
    const password = useModel(__props, "password");
    const notes = useModel(__props, "notes");
    const props = __props;
    const idrFormatter = new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      maximumFractionDigits: 0
    });
    const maxAmountInputValue = computed(() => Math.max(0, Number(props.maxAmount ?? 0)));
    const formattedMaxAmount = computed(() => {
      if (props.maxAmountLabel.trim() !== "") {
        return props.maxAmountLabel;
      }
      return idrFormatter.format(maxAmountInputValue.value);
    });
    const adminFeeValue = computed(() => Math.max(0, Number(props.adminFee ?? 0)));
    const formattedAdminFee = computed(() => idrFormatter.format(adminFeeValue.value));
    const minimumRequestAmount = computed(() => {
      const rounded = Math.ceil(minimumWithdrawalAmount / withdrawalStepAmount) * withdrawalStepAmount;
      return Math.max(withdrawalStepAmount, rounded);
    });
    const formattedMinimumRequestAmount = computed(() => idrFormatter.format(minimumRequestAmount.value));
    const hasAmountKeyup = ref(false);
    const showPassword = ref(false);
    const hasInvalidAmount = computed(() => {
      const requestedAmount = Number(amount.value ?? 0);
      if (!Number.isFinite(requestedAmount)) {
        return true;
      }
      if (!Number.isInteger(requestedAmount) || requestedAmount % withdrawalStepAmount !== 0) {
        return true;
      }
      if (requestedAmount < minimumRequestAmount.value) {
        return true;
      }
      if (requestedAmount > maxAmountInputValue.value) {
        return true;
      }
      return false;
    });
    const hasInvalidSubmission = computed(() => hasInvalidAmount.value || password.value.trim() === "");
    const withdrawalRulesHint = computed(
      () => `Masukkan nominal kelipatan Rp 500, minimal ${formattedMinimumRequestAmount.value}, maksimal ${formattedMaxAmount.value}, dan lebih besar dari biaya admin ${formattedAdminFee.value}.`
    );
    const amountAlert = computed(() => {
      const requestedAmount = Number(amount.value ?? 0);
      if (!hasAmountKeyup.value) {
        return {
          color: "info",
          icon: "i-lucide-info",
          title: "Panduan nominal withdrawal",
          description: withdrawalRulesHint.value
        };
      }
      if (!Number.isFinite(requestedAmount) || requestedAmount <= 0) {
        return {
          color: "warning",
          icon: "i-lucide-alert-triangle",
          title: "Nominal belum valid",
          description: "Masukkan nominal angka yang valid untuk lanjut proses withdrawal."
        };
      }
      if (!Number.isInteger(requestedAmount)) {
        return {
          color: "warning",
          icon: "i-lucide-alert-triangle",
          title: "Nominal harus bilangan bulat",
          description: "Nominal withdrawal tidak boleh mengandung desimal."
        };
      }
      if (requestedAmount % withdrawalStepAmount !== 0) {
        const lowerSuggestion = Math.floor(requestedAmount / withdrawalStepAmount) * withdrawalStepAmount;
        const upperSuggestion = Math.ceil(requestedAmount / withdrawalStepAmount) * withdrawalStepAmount;
        const suggestions = [lowerSuggestion, upperSuggestion].filter((value, index, all) => value > 0 && all.indexOf(value) === index).map((value) => idrFormatter.format(value)).join(" atau ");
        return {
          color: "warning",
          icon: "i-lucide-alert-triangle",
          title: "Nominal wajib kelipatan Rp 500",
          description: suggestions !== "" ? `Contoh nominal terdekat yang valid: ${suggestions}.` : "Ubah nominal agar sesuai kelipatan Rp 500."
        };
      }
      if (requestedAmount < minimumRequestAmount.value) {
        return {
          color: "warning",
          icon: "i-lucide-alert-triangle",
          title: "Nominal di bawah minimum",
          description: `Minimal withdrawal adalah ${formattedMinimumRequestAmount.value}.`
        };
      }
      if (requestedAmount > maxAmountInputValue.value) {
        return {
          color: "warning",
          icon: "i-lucide-alert-triangle",
          title: "Nominal melebihi saldo",
          description: `Maksimal yang bisa diajukan saat ini ${formattedMaxAmount.value}.`
        };
      }
      const estimatedReceivedAmount = Math.max(0, requestedAmount - adminFeeValue.value);
      if (estimatedReceivedAmount <= 0) {
        return {
          color: "warning",
          icon: "i-lucide-alert-triangle",
          title: "Nominal belum memenuhi biaya admin",
          description: `Nominal harus lebih besar dari biaya admin ${formattedAdminFee.value}.`
        };
      }
      return {
        color: "success",
        icon: "i-lucide-badge-check",
        title: "Nominal valid",
        description: `Estimasi dana diterima: ${idrFormatter.format(estimatedReceivedAmount)} setelah biaya admin ${formattedAdminFee.value}.`
      };
    });
    function onAmountKeyup() {
      hasAmountKeyup.value = true;
    }
    watch(isOpen, (open) => {
      if (!open) {
        hasAmountKeyup.value = false;
        showPassword.value = false;
      }
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$e;
      const _component_UAlert = _sfc_main$f;
      const _component_UButton = _sfc_main$8;
      const _component_UFormField = _sfc_main$a;
      const _component_UInput = _sfc_main$b;
      const _component_UTextarea = _sfc_main$g;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: isOpen.value,
        "onUpdate:open": ($event) => isOpen.value = $event,
        title: "Ajukan Withdrawal Wallet",
        description: "Konfirmasi password akun untuk keamanan proses withdrawal.",
        scrollable: ""
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (!props.hematMode && !props.isWaConfirmed) {
              _push2(`<div class="space-y-4"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UAlert, {
                color: "warning",
                variant: "soft",
                icon: "i-lucide-message-circle-warning",
                title: "Verifikasi WhatsApp Diperlukan",
                description: "Nomor WhatsApp Anda belum terkonfirmasi. Konfirmasi terlebih dahulu untuk dapat menggunakan fitur Withdrawal Wallet."
              }, null, _parent2, _scopeId));
              _push2(`<div class="flex justify-center"${_scopeId}>`);
              if (props.waConfirmationUrl) {
                _push2(ssrRenderComponent(_component_UButton, {
                  color: "success",
                  icon: "i-lucide-message-circle",
                  to: props.waConfirmationUrl,
                  target: "_blank"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(` Konfirmasi via WhatsApp `);
                    } else {
                      return [
                        createTextVNode(" Konfirmasi via WhatsApp ")
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div></div>`);
            } else {
              _push2(`<div class="space-y-4"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Nominal withdrawal",
                required: "",
                help: `Minimal ${formattedMinimumRequestAmount.value} • Maksimal ${formattedMaxAmount.value} • Kelipatan Rp 500`
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UInput, {
                      modelValue: amount.value,
                      "onUpdate:modelValue": ($event) => amount.value = $event,
                      type: "number",
                      min: minimumRequestAmount.value,
                      max: maxAmountInputValue.value,
                      step: withdrawalStepAmount,
                      placeholder: "Contoh: 50000",
                      class: "w-full",
                      icon: "i-lucide-wallet",
                      onKeyup: onAmountKeyup
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UInput, {
                        modelValue: amount.value,
                        "onUpdate:modelValue": ($event) => amount.value = $event,
                        type: "number",
                        min: minimumRequestAmount.value,
                        max: maxAmountInputValue.value,
                        step: withdrawalStepAmount,
                        placeholder: "Contoh: 50000",
                        class: "w-full",
                        icon: "i-lucide-wallet",
                        onKeyup: onAmountKeyup
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "min", "max"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UAlert, {
                color: amountAlert.value.color,
                variant: "soft",
                icon: amountAlert.value.icon,
                title: amountAlert.value.title,
                description: amountAlert.value.description
              }, null, _parent2, _scopeId));
              _push2(`<div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-950/30"${_scopeId}><p class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-200"${_scopeId}> Aturan Withdrawal </p><div class="mt-2 space-y-3 text-xs text-amber-700 dark:text-amber-300"${_scopeId}><p class="flex items-start gap-1"${_scopeId}><span class="font-semibold mr-1"${_scopeId}>1.</span> Setiap penarikan dikenakan biaya administrasi <span class="font-bold text-amber-800 dark:text-amber-100"${_scopeId}>${ssrInterpolate(formattedAdminFee.value)}</span> (Rp 6.500). </p><div${_scopeId}><p class="font-semibold"${_scopeId}>2. Periode Pentransferan:</p><ul class="mt-1 ml-4 list-disc space-y-1"${_scopeId}><li${_scopeId}>Penarikan <span class="font-medium"${_scopeId}>00.01 - 12.00 WIB</span>: Ditransfer pukul <span class="font-bold"${_scopeId}>14.00 WIB</span> (hari yang sama).</li><li${_scopeId}>Penarikan <span class="font-medium"${_scopeId}>12.01 - 16.00 WIB</span>: Ditransfer pukul <span class="font-bold"${_scopeId}>17.00 WIB</span> (hari yang sama).</li><li${_scopeId}>Penarikan <span class="font-medium"${_scopeId}>16.01 - 00.00 WIB</span>: Ditransfer pukul <span class="font-bold"${_scopeId}>14.00 WIB</span> (keesokan harinya).</li></ul></div><p${_scopeId}><span class="font-semibold"${_scopeId}>3.</span> Pastikan data rekening Anda sudah benar untuk menghindari kegagalan transaksi. </p></div></div>`);
              _push2(ssrRenderComponent(_component_UFormField, {
                label: "Password akun",
                required: "",
                help: "Password dipakai sebagai konfirmasi withdrawal."
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UInput, {
                      modelValue: password.value,
                      "onUpdate:modelValue": ($event) => password.value = $event,
                      id: "withdrawal-password",
                      type: showPassword.value ? "text" : "password",
                      class: "w-full",
                      placeholder: "Masukkan password akun",
                      icon: "i-lucide-lock-keyhole",
                      ui: { trailing: "pe-1" }
                    }, {
                      trailing: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(ssrRenderComponent(_component_UButton, {
                            color: "neutral",
                            type: "button",
                            variant: "link",
                            size: "sm",
                            icon: showPassword.value ? "i-lucide-eye-off" : "i-lucide-eye",
                            "aria-label": showPassword.value ? "Hide password" : "Show password",
                            "aria-pressed": showPassword.value,
                            "aria-controls": "withdrawal-password",
                            onClick: ($event) => showPassword.value = !showPassword.value
                          }, null, _parent4, _scopeId3));
                        } else {
                          return [
                            createVNode(_component_UButton, {
                              color: "neutral",
                              type: "button",
                              variant: "link",
                              size: "sm",
                              icon: showPassword.value ? "i-lucide-eye-off" : "i-lucide-eye",
                              "aria-label": showPassword.value ? "Hide password" : "Show password",
                              "aria-pressed": showPassword.value,
                              "aria-controls": "withdrawal-password",
                              onClick: ($event) => showPassword.value = !showPassword.value
                            }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                          ];
                        }
                      }),
                      _: 1
                    }, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UInput, {
                        modelValue: password.value,
                        "onUpdate:modelValue": ($event) => password.value = $event,
                        id: "withdrawal-password",
                        type: showPassword.value ? "text" : "password",
                        class: "w-full",
                        placeholder: "Masukkan password akun",
                        icon: "i-lucide-lock-keyhole",
                        ui: { trailing: "pe-1" }
                      }, {
                        trailing: withCtx(() => [
                          createVNode(_component_UButton, {
                            color: "neutral",
                            type: "button",
                            variant: "link",
                            size: "sm",
                            icon: showPassword.value ? "i-lucide-eye-off" : "i-lucide-eye",
                            "aria-label": showPassword.value ? "Hide password" : "Show password",
                            "aria-pressed": showPassword.value,
                            "aria-controls": "withdrawal-password",
                            onClick: ($event) => showPassword.value = !showPassword.value
                          }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                        ]),
                        _: 1
                      }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UFormField, { label: "Catatan (opsional)" }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UTextarea, {
                      modelValue: notes.value,
                      "onUpdate:modelValue": ($event) => notes.value = $event,
                      rows: 3,
                      class: "w-full",
                      placeholder: "Catatan untuk tim finance."
                    }, null, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UTextarea, {
                        modelValue: notes.value,
                        "onUpdate:modelValue": ($event) => notes.value = $event,
                        rows: 3,
                        class: "w-full",
                        placeholder: "Catatan untuk tim finance."
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            }
          } else {
            return [
              !props.hematMode && !props.isWaConfirmed ? (openBlock(), createBlock("div", {
                key: 0,
                class: "space-y-4"
              }, [
                createVNode(_component_UAlert, {
                  color: "warning",
                  variant: "soft",
                  icon: "i-lucide-message-circle-warning",
                  title: "Verifikasi WhatsApp Diperlukan",
                  description: "Nomor WhatsApp Anda belum terkonfirmasi. Konfirmasi terlebih dahulu untuk dapat menggunakan fitur Withdrawal Wallet."
                }),
                createVNode("div", { class: "flex justify-center" }, [
                  props.waConfirmationUrl ? (openBlock(), createBlock(_component_UButton, {
                    key: 0,
                    color: "success",
                    icon: "i-lucide-message-circle",
                    to: props.waConfirmationUrl,
                    target: "_blank"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Konfirmasi via WhatsApp ")
                    ]),
                    _: 1
                  }, 8, ["to"])) : createCommentVNode("", true)
                ])
              ])) : (openBlock(), createBlock("div", {
                key: 1,
                class: "space-y-4"
              }, [
                createVNode(_component_UFormField, {
                  label: "Nominal withdrawal",
                  required: "",
                  help: `Minimal ${formattedMinimumRequestAmount.value} • Maksimal ${formattedMaxAmount.value} • Kelipatan Rp 500`
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: amount.value,
                      "onUpdate:modelValue": ($event) => amount.value = $event,
                      type: "number",
                      min: minimumRequestAmount.value,
                      max: maxAmountInputValue.value,
                      step: withdrawalStepAmount,
                      placeholder: "Contoh: 50000",
                      class: "w-full",
                      icon: "i-lucide-wallet",
                      onKeyup: onAmountKeyup
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "min", "max"])
                  ]),
                  _: 1
                }, 8, ["help"]),
                createVNode(_component_UAlert, {
                  color: amountAlert.value.color,
                  variant: "soft",
                  icon: amountAlert.value.icon,
                  title: amountAlert.value.title,
                  description: amountAlert.value.description
                }, null, 8, ["color", "icon", "title", "description"]),
                createVNode("div", { class: "rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-950/30" }, [
                  createVNode("p", { class: "text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-200" }, " Aturan Withdrawal "),
                  createVNode("div", { class: "mt-2 space-y-3 text-xs text-amber-700 dark:text-amber-300" }, [
                    createVNode("p", { class: "flex items-start gap-1" }, [
                      createVNode("span", { class: "font-semibold mr-1" }, "1."),
                      createTextVNode(" Setiap penarikan dikenakan biaya administrasi "),
                      createVNode("span", { class: "font-bold text-amber-800 dark:text-amber-100" }, toDisplayString(formattedAdminFee.value), 1),
                      createTextVNode(" (Rp 6.500). ")
                    ]),
                    createVNode("div", null, [
                      createVNode("p", { class: "font-semibold" }, "2. Periode Pentransferan:"),
                      createVNode("ul", { class: "mt-1 ml-4 list-disc space-y-1" }, [
                        createVNode("li", null, [
                          createTextVNode("Penarikan "),
                          createVNode("span", { class: "font-medium" }, "00.01 - 12.00 WIB"),
                          createTextVNode(": Ditransfer pukul "),
                          createVNode("span", { class: "font-bold" }, "14.00 WIB"),
                          createTextVNode(" (hari yang sama).")
                        ]),
                        createVNode("li", null, [
                          createTextVNode("Penarikan "),
                          createVNode("span", { class: "font-medium" }, "12.01 - 16.00 WIB"),
                          createTextVNode(": Ditransfer pukul "),
                          createVNode("span", { class: "font-bold" }, "17.00 WIB"),
                          createTextVNode(" (hari yang sama).")
                        ]),
                        createVNode("li", null, [
                          createTextVNode("Penarikan "),
                          createVNode("span", { class: "font-medium" }, "16.01 - 00.00 WIB"),
                          createTextVNode(": Ditransfer pukul "),
                          createVNode("span", { class: "font-bold" }, "14.00 WIB"),
                          createTextVNode(" (keesokan harinya).")
                        ])
                      ])
                    ]),
                    createVNode("p", null, [
                      createVNode("span", { class: "font-semibold" }, "3."),
                      createTextVNode(" Pastikan data rekening Anda sudah benar untuk menghindari kegagalan transaksi. ")
                    ])
                  ])
                ]),
                createVNode(_component_UFormField, {
                  label: "Password akun",
                  required: "",
                  help: "Password dipakai sebagai konfirmasi withdrawal."
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      modelValue: password.value,
                      "onUpdate:modelValue": ($event) => password.value = $event,
                      id: "withdrawal-password",
                      type: showPassword.value ? "text" : "password",
                      class: "w-full",
                      placeholder: "Masukkan password akun",
                      icon: "i-lucide-lock-keyhole",
                      ui: { trailing: "pe-1" }
                    }, {
                      trailing: withCtx(() => [
                        createVNode(_component_UButton, {
                          color: "neutral",
                          type: "button",
                          variant: "link",
                          size: "sm",
                          icon: showPassword.value ? "i-lucide-eye-off" : "i-lucide-eye",
                          "aria-label": showPassword.value ? "Hide password" : "Show password",
                          "aria-pressed": showPassword.value,
                          "aria-controls": "withdrawal-password",
                          onClick: ($event) => showPassword.value = !showPassword.value
                        }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                      ]),
                      _: 1
                    }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, { label: "Catatan (opsional)" }, {
                  default: withCtx(() => [
                    createVNode(_component_UTextarea, {
                      modelValue: notes.value,
                      "onUpdate:modelValue": ($event) => notes.value = $event,
                      rows: 3,
                      class: "w-full",
                      placeholder: "Catatan untuk tim finance."
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                })
              ]))
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "w-full sm:w-auto",
              onClick: ($event) => isOpen.value = false
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
            if (props.hematMode || props.isWaConfirmed) {
              _push2(ssrRenderComponent(_component_UButton, {
                color: "primary",
                icon: "i-lucide-arrow-up-right",
                loading: __props.loading,
                class: "w-full sm:w-auto",
                disabled: __props.loading || hasInvalidSubmission.value,
                onClick: ($event) => _ctx.$emit("submit")
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Kirim Withdrawal `);
                  } else {
                    return [
                      createTextVNode(" Kirim Withdrawal ")
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
              createVNode("div", { class: "flex w-full flex-col-reverse gap-2 sm:flex-row sm:justify-end" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  class: "w-full sm:w-auto",
                  onClick: ($event) => isOpen.value = false
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Batal ")
                  ]),
                  _: 1
                }, 8, ["onClick"]),
                props.hematMode || props.isWaConfirmed ? (openBlock(), createBlock(_component_UButton, {
                  key: 0,
                  color: "primary",
                  icon: "i-lucide-arrow-up-right",
                  loading: __props.loading,
                  class: "w-full sm:w-auto",
                  disabled: __props.loading || hasInvalidSubmission.value,
                  onClick: ($event) => _ctx.$emit("submit")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Kirim Withdrawal ")
                  ]),
                  _: 1
                }, 8, ["loading", "disabled", "onClick"])) : createCommentVNode("", true)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/wallet/WalletWithdrawalModal.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const defaultTransactionsPayload = {
  data: [],
  current_page: 1,
  next_page: null,
  has_more: false,
  filters: {
    search: null,
    type: null,
    status: null
  }
};
function firstErrorMessage(errors) {
  const first = Object.values(errors).find((value) => value !== void 0);
  if (Array.isArray(first)) {
    return first[0] ?? "Request gagal.";
  }
  return first ?? "Request gagal.";
}
function useDashboardWallet(options) {
  const toast = useToast();
  const page = usePage();
  const { formatIDR } = useDashboard();
  const balance = computed(() => Number(options.walletBalance.value ?? options.customer.value?.wallet_balance ?? 0));
  const formattedBalance = computed(() => formatIDR(balance.value));
  const withdrawalMinimumAmount = 1e4;
  const withdrawalAdminFee = 6500;
  const withdrawalStepAmount2 = 500;
  const maxWithdrawalAmount = computed(() => Math.max(0, Math.floor(balance.value)));
  const minimumWithdrawalRequestAmount = computed(() => {
    const rounded = Math.ceil(withdrawalMinimumAmount / withdrawalStepAmount2) * withdrawalStepAmount2;
    return Math.max(withdrawalStepAmount2, rounded);
  });
  const typeItems = computed(() => [
    { label: "Semua tipe", value: "all" },
    { label: "Topup", value: "topup" },
    { label: "Withdrawal", value: "withdrawal" },
    { label: "Bonus", value: "bonus" },
    { label: "Purchase", value: "purchase" },
    { label: "Refund", value: "refund" },
    { label: "Tax", value: "tax" }
  ]);
  const statusItems = computed(() => [
    { label: "Semua status", value: "all" },
    { label: "Pending", value: "pending" },
    { label: "Completed", value: "completed" },
    { label: "Failed", value: "failed" },
    { label: "Cancelled", value: "cancelled" }
  ]);
  const allTransactions = ref([]);
  const currentPage = ref(1);
  const nextPage = ref(null);
  const hasMore = ref(false);
  const isLoadingMore = ref(false);
  const isApplyingFilter = ref(false);
  const searchQuery = ref("");
  const typeFilter = ref("all");
  const statusFilter = ref("all");
  const hasInitializedFilter = ref(false);
  const isTopupModalOpen = ref(false);
  const topupAmount = ref(null);
  const topupNotes = ref("");
  const isSubmittingTopup = ref(false);
  const syncingTopupId = ref(null);
  const isWithdrawalModalOpen = ref(false);
  const withdrawalAmount = ref(null);
  const withdrawalPassword = ref("");
  const withdrawalNotes = ref("");
  const isSubmittingWithdrawal = ref(false);
  const shownCount = computed(() => allTransactions.value.length);
  const totalCount = computed(() => options.transactions.value?.total ?? allTransactions.value.length);
  watch(
    options.transactions,
    (incoming) => {
      const payload = incoming ?? defaultTransactionsPayload;
      const incomingPage = payload.current_page ?? 1;
      const incomingData = payload.data ?? [];
      if (incomingPage <= 1) {
        allTransactions.value = [...incomingData];
      } else if (incomingPage > currentPage.value) {
        const existingKeys = new Set(allTransactions.value.map((transaction) => String(transaction.id)));
        const appended = incomingData.filter((transaction) => !existingKeys.has(String(transaction.id)));
        allTransactions.value = [...allTransactions.value, ...appended];
      }
      currentPage.value = incomingPage;
      nextPage.value = payload.next_page ?? null;
      hasMore.value = Boolean(payload.has_more);
      if (!hasInitializedFilter.value) {
        const incomingFilters = payload.filters ?? {};
        searchQuery.value = incomingFilters.search ?? "";
        typeFilter.value = incomingFilters.type ?? "all";
        statusFilter.value = incomingFilters.status ?? "all";
        hasInitializedFilter.value = true;
      }
    },
    { immediate: true }
  );
  function buildWalletQuery(pageNumber) {
    const search = searchQuery.value.trim();
    const query = {
      section: "wallet",
      wallet_page: pageNumber
    };
    if (search !== "") {
      query.wallet_search = search;
    }
    if (typeFilter.value !== "all") {
      query.wallet_type = typeFilter.value;
    }
    if (statusFilter.value !== "all") {
      query.wallet_status = statusFilter.value;
    }
    return query;
  }
  function requestWallet(pageNumber = 1) {
    if (isApplyingFilter.value) {
      return;
    }
    isApplyingFilter.value = true;
    if (pageNumber > 1) {
      isLoadingMore.value = true;
    }
    router.get("/dashboard", buildWalletQuery(pageNumber), {
      only: ["walletTransactions", "stats", "customer", "hasPendingWithdrawal", "midtrans"],
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        isApplyingFilter.value = false;
        isLoadingMore.value = false;
      }
    });
  }
  function applyFilter() {
    requestWallet(1);
  }
  function resetFilter() {
    searchQuery.value = "";
    typeFilter.value = "all";
    statusFilter.value = "all";
    requestWallet(1);
  }
  function loadMore() {
    if (isLoadingMore.value || !hasMore.value || !nextPage.value) {
      return;
    }
    requestWallet(nextPage.value);
  }
  function getSnapScriptUrl() {
    const env = options.midtrans.value?.env ?? "sandbox";
    const host = env === "production" ? "https://app.midtrans.com" : "https://app.sandbox.midtrans.com";
    return `${host}/snap/snap.js`;
  }
  async function ensureSnapLoaded() {
    if (window.snap?.pay) {
      return true;
    }
    const clientKey = options.midtrans.value?.client_key ?? "";
    if (!clientKey) {
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
      script.setAttribute("data-client-key", clientKey);
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
  async function syncTopupStatus(walletTransactionId) {
    syncingTopupId.value = walletTransactionId;
    try {
      const response = await inertiaPost(
        `/dashboard/wallet/topup/${walletTransactionId}/payment-status`,
        {},
        ["flash", "errors", "walletTransactions", "stats", "customer", "hasPendingWithdrawal", "midtrans"]
      );
      const message = response.flash?.wallet?.message ?? "Status pembayaran topup berhasil disinkronkan.";
      toast?.add?.({
        title: "Status topup diperbarui",
        description: message,
        color: "success",
        icon: "i-lucide-badge-check"
      });
      requestWallet(1);
    } catch (error) {
      const message = error instanceof Error ? error.message : "Gagal sinkronisasi status topup.";
      toast?.add?.({
        title: "Sinkronisasi topup gagal",
        description: message,
        color: "error",
        icon: "i-lucide-x-circle"
      });
    } finally {
      syncingTopupId.value = null;
    }
  }
  async function submitTopup() {
    if (isSubmittingTopup.value) {
      return;
    }
    if (!topupAmount.value || topupAmount.value < 1e4) {
      toast?.add?.({
        title: "Nominal topup tidak valid",
        description: "Nominal topup minimal Rp 10.000.",
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    if (!(options.midtrans.value?.client_key ?? "")) {
      toast?.add?.({
        title: "Midtrans belum aktif",
        description: "Client key Midtrans belum tersedia.",
        color: "error",
        icon: "i-lucide-x-circle"
      });
      return;
    }
    isSubmittingTopup.value = true;
    try {
      const response = await inertiaPost("/dashboard/wallet/topup/token", {
        amount: topupAmount.value,
        notes: topupNotes.value.trim() || null
      });
      const walletFlash = response.flash?.wallet;
      const rawPayload = walletFlash?.payload ?? {};
      const snapToken = rawPayload.snapToken;
      const walletTransactionId = Number(rawPayload.walletTransactionId ?? 0);
      if (!snapToken || !walletTransactionId) {
        throw new Error(walletFlash?.message ?? "Token topup Midtrans tidak tersedia.");
      }
      const snapLoaded = await ensureSnapLoaded();
      if (!snapLoaded) {
        throw new Error("Midtrans Snap gagal dimuat. Periksa konfigurasi client key.");
      }
      isSubmittingTopup.value = false;
      isTopupModalOpen.value = false;
      window.snap?.pay(snapToken, {
        onSuccess: () => {
          void syncTopupStatus(walletTransactionId);
        },
        onPending: () => {
          void syncTopupStatus(walletTransactionId);
        },
        onError: () => {
          toast?.add?.({
            title: "Pembayaran topup gagal",
            description: "Terjadi kesalahan saat proses Midtrans.",
            color: "error",
            icon: "i-lucide-x-circle"
          });
        },
        onClose: () => {
          toast?.add?.({
            title: "Pembayaran ditutup",
            description: "Popup pembayaran ditutup sebelum selesai.",
            color: "warning",
            icon: "i-lucide-alert-circle"
          });
        }
      });
    } catch (error) {
      const message = error instanceof Error ? error.message : "Gagal membuat topup Midtrans.";
      toast?.add?.({
        title: "Topup gagal",
        description: message,
        color: "error",
        icon: "i-lucide-x-circle"
      });
    } finally {
      if (isSubmittingTopup.value) {
        isSubmittingTopup.value = false;
      }
    }
  }
  async function submitWithdrawal() {
    if (isSubmittingWithdrawal.value) {
      return;
    }
    if (maxWithdrawalAmount.value < minimumWithdrawalRequestAmount.value) {
      toast?.add?.({
        title: "Saldo belum mencukupi",
        description: `Saldo wallet Anda saat ini ${formatIDR(maxWithdrawalAmount.value)}.`,
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    const amountValue = Number(withdrawalAmount.value ?? 0);
    if (!Number.isFinite(amountValue) || amountValue < minimumWithdrawalRequestAmount.value) {
      toast?.add?.({
        title: "Nominal withdrawal tidak valid",
        description: `Nominal withdrawal minimal ${formatIDR(minimumWithdrawalRequestAmount.value)}.`,
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    if (!Number.isInteger(amountValue) || amountValue % withdrawalStepAmount2 !== 0) {
      toast?.add?.({
        title: "Nominal withdrawal tidak valid",
        description: "Nominal withdrawal harus kelipatan Rp 500.",
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    if (amountValue > maxWithdrawalAmount.value) {
      toast?.add?.({
        title: "Nominal melebihi saldo",
        description: `Maksimal withdrawal ${formatIDR(maxWithdrawalAmount.value)} sesuai saldo wallet Anda.`,
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    const estimatedReceivedAmount = amountValue - withdrawalAdminFee;
    if (estimatedReceivedAmount <= 0) {
      toast?.add?.({
        title: "Nominal withdrawal tidak valid",
        description: `Nominal withdrawal harus lebih besar dari biaya admin ${formatIDR(withdrawalAdminFee)}.`,
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    const passwordValue = withdrawalPassword.value.trim();
    if (passwordValue === "") {
      toast?.add?.({
        title: "Password wajib diisi",
        description: "Masukkan password untuk konfirmasi withdrawal.",
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    isSubmittingWithdrawal.value = true;
    try {
      const response = await inertiaPost(
        "/dashboard/wallet/withdrawal",
        {
          amount: amountValue,
          password: passwordValue,
          notes: withdrawalNotes.value.trim() || null
        },
        ["flash", "errors", "walletTransactions", "stats", "customer", "hasPendingWithdrawal", "midtrans"]
      );
      const message = response.flash?.wallet?.message ?? "Permintaan withdrawal berhasil dikirim.";
      toast?.add?.({
        title: "Withdrawal terkirim",
        description: message,
        color: "success",
        icon: "i-lucide-badge-check"
      });
      isWithdrawalModalOpen.value = false;
      withdrawalPassword.value = "";
      withdrawalNotes.value = "";
      withdrawalAmount.value = null;
      requestWallet(1);
    } catch (error) {
      const message = error instanceof Error ? error.message : "Gagal mengirim permintaan withdrawal.";
      toast?.add?.({
        title: "Withdrawal gagal",
        description: message,
        color: "error",
        icon: "i-lucide-x-circle"
      });
    } finally {
      isSubmittingWithdrawal.value = false;
    }
  }
  function resetTopupForm() {
    topupAmount.value = null;
    topupNotes.value = "";
  }
  function resetWithdrawalForm() {
    withdrawalAmount.value = null;
    withdrawalPassword.value = "";
    withdrawalNotes.value = "";
  }
  watch(isTopupModalOpen, (open) => {
    if (!open) {
      resetTopupForm();
    }
  });
  watch(isWithdrawalModalOpen, (open) => {
    if (!open) {
      resetWithdrawalForm();
    }
  });
  return {
    balance,
    formattedBalance,
    withdrawalAdminFee,
    allTransactions,
    shownCount,
    totalCount,
    hasMore,
    nextPage,
    isLoadingMore,
    isApplyingFilter,
    searchQuery,
    typeFilter,
    statusFilter,
    typeItems,
    statusItems,
    isTopupModalOpen,
    topupAmount,
    topupNotes,
    isSubmittingTopup,
    syncingTopupId,
    isWithdrawalModalOpen,
    withdrawalAmount,
    withdrawalPassword,
    withdrawalNotes,
    isSubmittingWithdrawal,
    applyFilter,
    resetFilter,
    loadMore,
    submitTopup,
    submitWithdrawal
  };
}
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Wallet",
  __ssrInlineRender: true,
  props: {
    customer: { default: null },
    transactions: { default: () => ({
      data: [],
      current_page: 1,
      next_page: null,
      has_more: false,
      per_page: 15,
      total: 0,
      filters: {
        search: null,
        type: null,
        status: null
      }
    }) },
    hasPendingWithdrawal: { type: Boolean, default: false },
    walletBalance: { default: 0 },
    midtrans: { default: () => ({
      env: "sandbox",
      client_key: ""
    }) },
    waConfirmationUrl: { default: null },
    waGatewayHematMode: { type: Boolean, default: false }
  },
  setup(__props) {
    const props = __props;
    const hasPendingWithdrawal = computed(() => Boolean(props.hasPendingWithdrawal));
    const isWaConfirmed = computed(() => props.customer?.is_wa_confirmed === true);
    const {
      balance,
      formattedBalance,
      withdrawalAdminFee,
      allTransactions,
      shownCount,
      totalCount,
      hasMore,
      nextPage,
      isLoadingMore,
      isApplyingFilter,
      searchQuery,
      typeFilter,
      statusFilter,
      typeItems,
      statusItems,
      isTopupModalOpen,
      topupAmount,
      topupNotes,
      isSubmittingTopup,
      syncingTopupId,
      isWithdrawalModalOpen,
      withdrawalAmount,
      withdrawalPassword,
      withdrawalNotes,
      isSubmittingWithdrawal,
      applyFilter,
      resetFilter,
      loadMore,
      submitTopup,
      submitWithdrawal
    } = useDashboardWallet({
      customer: computed(() => props.customer),
      transactions: computed(() => props.transactions),
      walletBalance: computed(() => props.walletBalance),
      midtrans: computed(() => props.midtrans)
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}>`);
      _push(ssrRenderComponent(_sfc_main$5, {
        "formatted-balance": unref(formattedBalance),
        "has-pending-withdrawal": hasPendingWithdrawal.value,
        onTopup: ($event) => isTopupModalOpen.value = true,
        onWithdrawal: ($event) => isWithdrawalModalOpen.value = true
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$4, {
        search: unref(searchQuery),
        "onUpdate:search": ($event) => isRef(searchQuery) ? searchQuery.value = $event : null,
        type: unref(typeFilter),
        "onUpdate:type": ($event) => isRef(typeFilter) ? typeFilter.value = $event : null,
        status: unref(statusFilter),
        "onUpdate:status": ($event) => isRef(statusFilter) ? statusFilter.value = $event : null,
        "type-items": unref(typeItems),
        "status-items": unref(statusItems),
        "is-applying": unref(isApplyingFilter),
        onApply: unref(applyFilter),
        onReset: unref(resetFilter)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$3, {
        transactions: unref(allTransactions),
        "shown-count": unref(shownCount),
        "total-count": unref(totalCount),
        "is-loading-more": unref(isLoadingMore),
        "can-load-more": unref(hasMore) && !!unref(nextPage) && !unref(isLoadingMore),
        onLoadMore: unref(loadMore)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$2, {
        open: unref(isTopupModalOpen),
        "onUpdate:open": ($event) => isRef(isTopupModalOpen) ? isTopupModalOpen.value = $event : null,
        amount: unref(topupAmount),
        "onUpdate:amount": ($event) => isRef(topupAmount) ? topupAmount.value = $event : null,
        notes: unref(topupNotes),
        "onUpdate:notes": ($event) => isRef(topupNotes) ? topupNotes.value = $event : null,
        loading: unref(isSubmittingTopup),
        syncing: unref(syncingTopupId) !== null,
        "is-wa-confirmed": isWaConfirmed.value,
        "wa-confirmation-url": props.waConfirmationUrl,
        "hemat-mode": props.waGatewayHematMode,
        onSubmit: unref(submitTopup)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$1, {
        open: unref(isWithdrawalModalOpen),
        "onUpdate:open": ($event) => isRef(isWithdrawalModalOpen) ? isWithdrawalModalOpen.value = $event : null,
        amount: unref(withdrawalAmount),
        "onUpdate:amount": ($event) => isRef(withdrawalAmount) ? withdrawalAmount.value = $event : null,
        password: unref(withdrawalPassword),
        "onUpdate:password": ($event) => isRef(withdrawalPassword) ? withdrawalPassword.value = $event : null,
        notes: unref(withdrawalNotes),
        "onUpdate:notes": ($event) => isRef(withdrawalNotes) ? withdrawalNotes.value = $event : null,
        "max-amount": unref(balance),
        "max-amount-label": unref(formattedBalance),
        "admin-fee": unref(withdrawalAdminFee),
        loading: unref(isSubmittingWithdrawal),
        "is-wa-confirmed": isWaConfirmed.value,
        "wa-confirmation-url": props.waConfirmationUrl,
        "hemat-mode": props.waGatewayHematMode,
        onSubmit: unref(submitWithdrawal)
      }, null, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Wallet.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
