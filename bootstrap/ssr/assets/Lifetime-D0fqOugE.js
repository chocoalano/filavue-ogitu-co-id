import { computed, h, defineComponent, mergeProps, withCtx, createVNode, toDisplayString, useSSRContext, unref } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate } from "vue/server-renderer";
import { u as useDashboard } from "./useDashboard-DR5F4MRN.js";
import { _ as _sfc_main$4 } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$6 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$5 } from "./Table-CEJ662o2.js";
import "./useToast-CTuSIf9f.js";
import "@inertiajs/vue3";
import "reka-ui";
import "hookable";
import "@vueuse/core";
import "defu";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "scule";
import "@tanstack/vue-table";
import "@tanstack/vue-virtual";
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
function progressColor(value) {
  if (value >= 100) {
    return "success";
  }
  if (value >= 60) {
    return "warning";
  }
  return "error";
}
function badgeClass(color, size = "sm") {
  const baseClass = "inline-flex items-center rounded-full font-medium";
  const sizeClass = size === "xs" ? "px-2 py-0.5 text-[11px]" : "px-2.5 py-1 text-xs";
  const colorClass = {
    error: "bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300",
    warning: "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300",
    success: "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300",
    neutral: "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300"
  }[color];
  return `${baseClass} ${sizeClass} ${colorClass}`;
}
function useDashboardLifetime(options) {
  const { formatIDR } = useDashboard();
  const summary = computed(() => options.lifetimeRewards.value.summary);
  const rewards = computed(() => options.lifetimeRewards.value.rewards);
  const claimed = computed(() => options.lifetimeRewards.value.claimed);
  const rewardColumns = [
    {
      id: "name",
      accessorKey: "name",
      header: "Reward",
      cell: ({ row }) => {
        const item = row.original;
        const statusText = item.is_claimed ? "Sudah Diklaim" : item.can_claim ? "Siap Klaim" : "Belum Tercapai";
        return h("div", { class: "min-w-0 space-y-1" }, [
          h("p", { class: "truncate text-sm font-semibold text-highlighted" }, item.name),
          h("p", { class: "truncate text-xs text-muted" }, item.reward && item.reward !== "" ? item.reward : "-"),
          h("p", { class: "text-[11px] text-muted" }, statusText)
        ]);
      }
    },
    {
      id: "bv",
      accessorKey: "bv",
      header: "Target BV",
      meta: {
        class: {
          th: "text-right",
          td: "text-right"
        }
      },
      cell: ({ row }) => h("span", { class: "font-mono text-xs text-muted" }, row.original.bv.toLocaleString("id-ID"))
    },
    {
      id: "progress",
      accessorKey: "progress_percent",
      header: "Progress",
      cell: ({ row }) => {
        const item = row.original;
        const progressText = `${item.progress_percent.toFixed(2)}%`;
        return h("div", { class: "min-w-0 space-y-1" }, [
          h("span", { class: badgeClass(progressColor(item.progress_percent), "xs") }, progressText),
          h("p", { class: "truncate text-[11px] text-muted" }, `Kiri ${formatIDR(item.accumulated_left)}`),
          h("p", { class: "truncate text-[11px] text-muted" }, `Kanan ${formatIDR(item.accumulated_right)}`)
        ]);
      }
    },
    {
      id: "status",
      accessorKey: "can_claim",
      header: "Status",
      cell: ({ row }) => {
        const item = row.original;
        const color = item.is_claimed ? "neutral" : item.can_claim ? "success" : "warning";
        const label = item.is_claimed ? "Claimed" : item.can_claim ? "Can Claim" : "Locked";
        return h("span", { class: badgeClass(color) }, label);
      }
    }
  ];
  const claimedColumns = [
    {
      id: "created_at",
      accessorKey: "created_at",
      header: "Tanggal",
      cell: ({ row }) => h("span", { class: "text-sm text-muted" }, formatDateTime(row.original.created_at))
    },
    {
      id: "reward",
      accessorKey: "reward",
      header: "Reward",
      cell: ({ row }) => h("div", { class: "min-w-0 space-y-1" }, [
        h("p", { class: "truncate text-sm font-semibold text-highlighted" }, row.original.reward ?? "-"),
        h("p", { class: "truncate text-xs text-muted" }, row.original.description ?? "-")
      ])
    },
    {
      id: "bv",
      accessorKey: "bv",
      header: "BV",
      meta: {
        class: {
          th: "text-right",
          td: "text-right"
        }
      },
      cell: ({ row }) => h("span", { class: "font-mono text-xs text-muted" }, row.original.bv.toLocaleString("id-ID"))
    },
    {
      id: "amount",
      accessorKey: "amount",
      header: "Nominal",
      meta: {
        class: {
          th: "text-right",
          td: "text-right"
        }
      },
      cell: ({ row }) => h("span", { class: "font-bold text-primary tabular-nums" }, formatIDR(row.original.amount))
    },
    {
      id: "status",
      accessorKey: "status",
      header: "Status",
      cell: ({ row }) => h(
        "span",
        { class: badgeClass(row.original.status === "released" ? "success" : "warning") },
        row.original.status_label
      )
    }
  ];
  return {
    summary,
    rewards,
    claimed,
    rewardColumns,
    claimedColumns,
    formatIDR
  };
}
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "LifetimeSummaryCards",
  __ssrInlineRender: true,
  props: {
    summary: {},
    formatCurrency: { type: Function }
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$4;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<p class="text-[11px] uppercase tracking-wider text-muted"${_scopeId}>Omzet Group Kiri</p><p class="mt-1 text-lg font-bold text-highlighted"${_scopeId}>${ssrInterpolate(__props.formatCurrency(__props.summary.accumulated_left))}</p>`);
          } else {
            return [
              createVNode("p", { class: "text-[11px] uppercase tracking-wider text-muted" }, "Omzet Group Kiri"),
              createVNode("p", { class: "mt-1 text-lg font-bold text-highlighted" }, toDisplayString(__props.formatCurrency(__props.summary.accumulated_left)), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<p class="text-[11px] uppercase tracking-wider text-muted"${_scopeId}>Omzet Group Kanan</p><p class="mt-1 text-lg font-bold text-highlighted"${_scopeId}>${ssrInterpolate(__props.formatCurrency(__props.summary.accumulated_right))}</p>`);
          } else {
            return [
              createVNode("p", { class: "text-[11px] uppercase tracking-wider text-muted" }, "Omzet Group Kanan"),
              createVNode("p", { class: "mt-1 text-lg font-bold text-highlighted" }, toDisplayString(__props.formatCurrency(__props.summary.accumulated_right)), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<p class="text-[11px] uppercase tracking-wider text-muted"${_scopeId}>Siap Klaim</p><p class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400"${_scopeId}>${ssrInterpolate(__props.summary.eligible_count)}</p>`);
          } else {
            return [
              createVNode("p", { class: "text-[11px] uppercase tracking-wider text-muted" }, "Siap Klaim"),
              createVNode("p", { class: "mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400" }, toDisplayString(__props.summary.eligible_count), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<p class="text-[11px] uppercase tracking-wider text-muted"${_scopeId}>Sudah Diklaim</p><p class="mt-1 text-lg font-bold text-primary"${_scopeId}>${ssrInterpolate(__props.summary.claimed_count)}</p>`);
          } else {
            return [
              createVNode("p", { class: "text-[11px] uppercase tracking-wider text-muted" }, "Sudah Diklaim"),
              createVNode("p", { class: "mt-1 text-lg font-bold text-primary" }, toDisplayString(__props.summary.claimed_count), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<p class="text-[11px] uppercase tracking-wider text-muted"${_scopeId}>Sisa Target</p><p class="mt-1 text-lg font-bold text-amber-600 dark:text-amber-400"${_scopeId}>${ssrInterpolate(__props.summary.remaining_count)}</p>`);
          } else {
            return [
              createVNode("p", { class: "text-[11px] uppercase tracking-wider text-muted" }, "Sisa Target"),
              createVNode("p", { class: "mt-1 text-lg font-bold text-amber-600 dark:text-amber-400" }, toDisplayString(__props.summary.remaining_count), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/lifetime/LifetimeSummaryCards.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "LifetimeRewardsTableCard",
  __ssrInlineRender: true,
  props: {
    rewards: {},
    columns: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$4;
      const _component_UTable = _sfc_main$5;
      const _component_UIcon = _sfc_main$6;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "overflow-hidden rounded-2xl",
        ui: { body: "p-0 sm:p-0", header: "px-4 py-4" }
      }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between gap-3"${_scopeId}><div${_scopeId}><h3 class="text-base font-bold text-highlighted"${_scopeId}>Lifetime Rewards</h3><p class="text-xs text-muted"${_scopeId}>Target reward berdasarkan akumulasi omzet group kiri dan kanan.</p></div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("h3", { class: "text-base font-bold text-highlighted" }, "Lifetime Rewards"),
                  createVNode("p", { class: "text-xs text-muted" }, "Target reward berdasarkan akumulasi omzet group kiri dan kanan.")
                ])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UTable, {
              data: __props.rewards,
              columns: __props.columns,
              class: "w-full"
            }, {
              empty: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col items-center justify-center py-10"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-trophy",
                    class: "mb-2 size-8 text-muted"
                  }, null, _parent3, _scopeId2));
                  _push3(`<p class="text-sm text-muted"${_scopeId2}>Belum ada master lifetime reward aktif.</p></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col items-center justify-center py-10" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-trophy",
                        class: "mb-2 size-8 text-muted"
                      }),
                      createVNode("p", { class: "text-sm text-muted" }, "Belum ada master lifetime reward aktif.")
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UTable, {
                data: __props.rewards,
                columns: __props.columns,
                class: "w-full"
              }, {
                empty: withCtx(() => [
                  createVNode("div", { class: "flex flex-col items-center justify-center py-10" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-trophy",
                      class: "mb-2 size-8 text-muted"
                    }),
                    createVNode("p", { class: "text-sm text-muted" }, "Belum ada master lifetime reward aktif.")
                  ])
                ]),
                _: 1
              }, 8, ["data", "columns"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/lifetime/LifetimeRewardsTableCard.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "LifetimeClaimedTableCard",
  __ssrInlineRender: true,
  props: {
    claimed: {},
    columns: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$4;
      const _component_UTable = _sfc_main$5;
      const _component_UIcon = _sfc_main$6;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "overflow-hidden rounded-2xl",
        ui: { body: "p-0 sm:p-0", header: "px-4 py-4" }
      }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between gap-3"${_scopeId}><div${_scopeId}><h3 class="text-base font-bold text-highlighted"${_scopeId}>Riwayat Klaim Lifetime</h3><p class="text-xs text-muted"${_scopeId}>Data klaim reward lifetime dari transaksi bonus reward.</p></div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("h3", { class: "text-base font-bold text-highlighted" }, "Riwayat Klaim Lifetime"),
                  createVNode("p", { class: "text-xs text-muted" }, "Data klaim reward lifetime dari transaksi bonus reward.")
                ])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UTable, {
              data: __props.claimed,
              columns: __props.columns,
              class: "w-full"
            }, {
              empty: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col items-center justify-center py-10"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-history",
                    class: "mb-2 size-8 text-muted"
                  }, null, _parent3, _scopeId2));
                  _push3(`<p class="text-sm text-muted"${_scopeId2}>Belum ada riwayat klaim lifetime.</p></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col items-center justify-center py-10" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-history",
                        class: "mb-2 size-8 text-muted"
                      }),
                      createVNode("p", { class: "text-sm text-muted" }, "Belum ada riwayat klaim lifetime.")
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UTable, {
                data: __props.claimed,
                columns: __props.columns,
                class: "w-full"
              }, {
                empty: withCtx(() => [
                  createVNode("div", { class: "flex flex-col items-center justify-center py-10" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-history",
                      class: "mb-2 size-8 text-muted"
                    }),
                    createVNode("p", { class: "text-sm text-muted" }, "Belum ada riwayat klaim lifetime.")
                  ])
                ]),
                _: 1
              }, 8, ["data", "columns"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/lifetime/LifetimeClaimedTableCard.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Lifetime",
  __ssrInlineRender: true,
  props: {
    lifetimeRewards: { default: () => ({
      summary: {
        accumulated_left: 0,
        accumulated_right: 0,
        eligible_count: 0,
        claimed_count: 0,
        remaining_count: 0
      },
      rewards: [],
      claimed: []
    }) }
  },
  setup(__props) {
    const props = __props;
    const { summary, rewards, claimed, rewardColumns, claimedColumns, formatIDR } = useDashboardLifetime({
      lifetimeRewards: computed(() => props.lifetimeRewards)
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}>`);
      _push(ssrRenderComponent(_sfc_main$3, {
        summary: unref(summary),
        "format-currency": unref(formatIDR)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$2, {
        rewards: unref(rewards),
        columns: unref(rewardColumns)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$1, {
        claimed: unref(claimed),
        columns: unref(claimedColumns)
      }, null, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Lifetime.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
