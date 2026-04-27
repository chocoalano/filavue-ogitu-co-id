import { ref, computed, watch, h, defineComponent, mergeProps, withCtx, createVNode, toDisplayString, useSSRContext, unref } from "vue";
import { ssrRenderAttrs, ssrRenderList, ssrRenderComponent, ssrInterpolate } from "vue/server-renderer";
import { u as useDashboard } from "./useDashboard-DR5F4MRN.js";
import { _ as _sfc_main$4 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$3 } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$8 } from "./Pagination-uWPH5vE_.js";
import { _ as _sfc_main$7 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$6 } from "./Table-CEJ662o2.js";
import { _ as _sfc_main$5 } from "./Tabs-C3xWVm3k.js";
import { _ as _sfc_main$9 } from "./Input-DFvIE7JC.js";
import "./useToast-CTuSIf9f.js";
import "@inertiajs/vue3";
import "hookable";
import "@vueuse/core";
import "defu";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "reka-ui";
import "ufo";
import "scule";
import "@tanstack/vue-table";
import "@tanstack/vue-virtual";
import "./Badge-DqskWDDq.js";
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
function formatMetricValue(value) {
  return new Intl.NumberFormat("id-ID", {
    maximumFractionDigits: 2
  }).format(value);
}
function detailSummary(row) {
  const parts = [];
  if (row.from_member?.name) {
    parts.push(row.from_member.name);
  }
  if (row.from_member?.email) {
    parts.push(row.from_member.email);
  }
  if (row.meta?.level !== void 0 && row.meta.level !== null) {
    parts.push(`Level ${row.meta.level}`);
  }
  if (row.meta?.pairing_count !== void 0 && row.meta.pairing_count !== null) {
    parts.push(`Pair ${row.meta.pairing_count}`);
  }
  if (row.meta?.order_id !== void 0 && row.meta.order_id !== null) {
    parts.push(`Order #${row.meta.order_id}`);
  }
  if (row.meta?.reward_name) {
    parts.push(`Reward ${row.meta.reward_name}`);
  }
  if (row.meta?.reward_type) {
    parts.push(`Type ${row.meta.reward_type}`);
  }
  if (row.type === "promotions_rewards" && row.meta?.index_value !== void 0 && row.meta.index_value !== null) {
    parts.push(`Poin ${formatMetricValue(Number(row.meta.index_value))}`);
  }
  if ((row.type === "promotions_rewards" || row.type === "lifetime_cash_rewards") && row.meta?.bv !== void 0 && row.meta.bv !== null) {
    parts.push(`BV/Omzet ${formatMetricValue(Number(row.meta.bv))}`);
  }
  return parts.join(" • ");
}
function useDashboardBonus(options) {
  const { formatIDR } = useDashboard();
  const categoryDefinitions = [
    { key: "referral_incentive", label: "Referral Incentive", icon: "i-lucide-users" },
    { key: "team_affiliate_commission", label: "Team Affiliate Commission", icon: "i-lucide-handshake" },
    { key: "partner_team_commission", label: "Partner Team Commission", icon: "i-lucide-network" },
    { key: "cashback_commission", label: "Cashback Commission", icon: "i-lucide-percent" },
    { key: "promotions_rewards", label: "Promotions Rewards", icon: "i-lucide-gift" },
    { key: "retail_commission", label: "Retail Commission", icon: "i-lucide-store" },
    { key: "lifetime_cash_rewards", label: "Lifetime Cash Rewards", icon: "i-lucide-trophy" }
  ];
  const statDefinitions = [
    ...categoryDefinitions.map((item) => ({
      key: item.key,
      title: item.label,
      icon: item.icon
    })),
    { key: "total_bonus", title: "Total Bonus", icon: "i-lucide-wallet-cards" }
  ];
  const tabs = [
    { label: "Semua", value: "all", icon: "i-lucide-layout-grid" },
    ...categoryDefinitions.map((item) => ({
      label: item.label,
      value: item.key,
      icon: item.icon
    }))
  ];
  const activeTab = ref("all");
  const searchQuery = ref("");
  const page = ref(1);
  const itemsPerPage = 10;
  const displayedStats = computed(
    () => statDefinitions.map((definition) => {
      const existing = options.bonusStats.value.find((item) => item.key === definition.key);
      return {
        key: definition.key,
        title: definition.title,
        icon: definition.icon,
        amount: Number(existing?.amount ?? 0),
        count: Number(existing?.count ?? 0)
      };
    })
  );
  const rowsByType = computed(() => ({
    referral_incentive: [...options.bonusTables.value.referral_incentive],
    team_affiliate_commission: [...options.bonusTables.value.team_affiliate_commission],
    partner_team_commission: [...options.bonusTables.value.partner_team_commission],
    cashback_commission: [...options.bonusTables.value.cashback_commission],
    promotions_rewards: [...options.bonusTables.value.promotions_rewards],
    retail_commission: [...options.bonusTables.value.retail_commission],
    lifetime_cash_rewards: [...options.bonusTables.value.lifetime_cash_rewards]
  }));
  const allRows = computed(
    () => categoryDefinitions.flatMap((item) => rowsByType.value[item.key]).sort((left, right) => {
      const leftTime = left.created_at ? new Date(left.created_at).getTime() : 0;
      const rightTime = right.created_at ? new Date(right.created_at).getTime() : 0;
      if (leftTime === rightTime) {
        return Number(right.id) - Number(left.id);
      }
      return rightTime - leftTime;
    })
  );
  const filteredRows = computed(() => {
    const keyword = searchQuery.value.trim().toLowerCase();
    const dataset = activeTab.value === "all" ? allRows.value : rowsByType.value[activeTab.value] ?? [];
    if (keyword === "") {
      return dataset;
    }
    return dataset.filter((row) => {
      const haystack = [
        row.type_label,
        row.description ?? "",
        row.from_member?.name ?? "",
        row.from_member?.email ?? "",
        row.meta?.reward_name ?? "",
        row.meta?.reward_type ?? ""
      ].join(" ").toLowerCase();
      return haystack.includes(keyword);
    });
  });
  watch([activeTab, searchQuery], () => {
    page.value = 1;
  });
  const paginatedRows = computed(() => {
    const start = (page.value - 1) * itemsPerPage;
    return filteredRows.value.slice(start, start + itemsPerPage);
  });
  const columns = [
    {
      id: "created_at",
      accessorKey: "created_at",
      header: "Tanggal",
      cell: ({ row }) => h("span", { class: "text-sm text-muted" }, formatDateTime(row.original.created_at))
    },
    {
      id: "description",
      accessorKey: "description",
      header: "Keterangan",
      cell: ({ row }) => {
        const original = row.original;
        const subtitle = detailSummary(original);
        return h("div", { class: "min-w-0 flex flex-col" }, [
          h(
            "span",
            { class: "truncate text-sm font-semibold text-highlighted" },
            original.type_label
          ),
          h(
            "span",
            { class: "truncate text-xs text-muted" },
            original.description && original.description.trim() !== "" ? original.description : "-"
          ),
          h(
            "span",
            { class: "truncate text-[11px] text-muted" },
            subtitle !== "" ? subtitle : "-"
          )
        ]);
      }
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
      cell: ({ row }) => h(
        "span",
        { class: "font-bold text-primary tabular-nums" },
        formatIDR(Number(row.original.amount ?? 0))
      )
    },
    {
      id: "status",
      accessorKey: "status",
      header: "Status",
      cell: ({ row }) => {
        const isReleased = row.original.status === "released";
        return h(
          "span",
          {
            class: [
              "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize",
              isReleased ? "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300" : "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
            ]
          },
          row.original.status_label
        );
      }
    }
  ];
  return {
    activeTab,
    searchQuery,
    page,
    itemsPerPage,
    tabs,
    displayedStats,
    filteredRows,
    paginatedRows,
    columns,
    formatIDR
  };
}
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "BonusStatsGrid",
  __ssrInlineRender: true,
  props: {
    stats: {},
    formatCurrency: { type: Function }
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$3;
      const _component_UIcon = _sfc_main$4;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" }, _attrs))}><!--[-->`);
      ssrRenderList(__props.stats, (stat) => {
        _push(ssrRenderComponent(_component_UCard, {
          key: stat.key,
          class: "rounded-2xl"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<div class="mb-2 flex items-center gap-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: stat.icon,
                class: "size-4 text-primary"
              }, null, _parent2, _scopeId));
              _push2(`<span class="text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>${ssrInterpolate(stat.title)}</span></div><div class="flex flex-col"${_scopeId}><span class="text-xl font-bold text-highlighted"${_scopeId}>${ssrInterpolate(__props.formatCurrency(stat.amount))}</span><span class="text-xs text-muted"${_scopeId}>${ssrInterpolate(stat.count)} transaksi</span></div>`);
            } else {
              return [
                createVNode("div", { class: "mb-2 flex items-center gap-2" }, [
                  createVNode(_component_UIcon, {
                    name: stat.icon,
                    class: "size-4 text-primary"
                  }, null, 8, ["name"]),
                  createVNode("span", { class: "text-[10px] font-bold uppercase tracking-wider text-muted" }, toDisplayString(stat.title), 1)
                ]),
                createVNode("div", { class: "flex flex-col" }, [
                  createVNode("span", { class: "text-xl font-bold text-highlighted" }, toDisplayString(__props.formatCurrency(stat.amount)), 1),
                  createVNode("span", { class: "text-xs text-muted" }, toDisplayString(stat.count) + " transaksi", 1)
                ])
              ];
            }
          }),
          _: 2
        }, _parent));
      });
      _push(`<!--]--></div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/bonus/BonusStatsGrid.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "BonusHistoryTableCard",
  __ssrInlineRender: true,
  props: {
    activeTab: {},
    searchQuery: {},
    page: {},
    itemsPerPage: {},
    tabs: {},
    rows: {},
    totalRows: {},
    columns: {}
  },
  emits: ["update:activeTab", "update:searchQuery", "update:page"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function onSearchUpdate(value) {
      emit("update:searchQuery", String(value ?? ""));
    }
    function onTabUpdate(value) {
      emit("update:activeTab", String(value ?? "all"));
    }
    function onPageUpdate(value) {
      emit("update:page", value);
    }
    const maxPage = computed(() => {
      const perPage = Math.max(1, props.itemsPerPage);
      return Math.max(1, Math.ceil(props.totalRows / perPage));
    });
    const canPrev = computed(() => props.page > 1);
    const canNext = computed(() => props.page < maxPage.value);
    function goPrev() {
      if (!canPrev.value) {
        return;
      }
      emit("update:page", props.page - 1);
    }
    function goNext() {
      if (!canNext.value) {
        return;
      }
      emit("update:page", props.page + 1);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$3;
      const _component_UInput = _sfc_main$9;
      const _component_UTabs = _sfc_main$5;
      const _component_UTable = _sfc_main$6;
      const _component_UIcon = _sfc_main$4;
      const _component_UButton = _sfc_main$7;
      const _component_UPagination = _sfc_main$8;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "overflow-hidden rounded-2xl",
        ui: { body: "p-0 sm:p-0", header: "px-4 py-4" }
      }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><h3 class="text-base font-bold text-highlighted"${_scopeId}>Riwayat Bonus</h3><div class="flex items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UInput, {
              "model-value": props.searchQuery,
              icon: "i-lucide-search",
              placeholder: "Cari deskripsi, member, reward...",
              size: "sm",
              class: "w-full sm:w-72",
              "onUpdate:modelValue": onSearchUpdate
            }, null, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("h3", { class: "text-base font-bold text-highlighted" }, "Riwayat Bonus"),
                createVNode("div", { class: "flex items-center gap-2" }, [
                  createVNode(_component_UInput, {
                    "model-value": props.searchQuery,
                    icon: "i-lucide-search",
                    placeholder: "Cari deskripsi, member, reward...",
                    size: "sm",
                    class: "w-full sm:w-72",
                    "onUpdate:modelValue": onSearchUpdate
                  }, null, 8, ["model-value"])
                ])
              ])
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><span class="text-xs text-muted"${_scopeId}>Total ${ssrInterpolate(props.totalRows)} data</span><div class="flex items-center justify-between gap-4 py-2 sm:hidden border-t border-gray-100 dark:border-gray-800"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              icon: "i-lucide-arrow-left",
              color: "neutral",
              variant: "ghost",
              size: "sm",
              disabled: !canPrev.value,
              onClick: goPrev,
              class: "rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<span class="text-xs font-medium"${_scopeId2}>Prev</span>`);
                } else {
                  return [
                    createVNode("span", { class: "text-xs font-medium" }, "Prev")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="flex flex-col items-center"${_scopeId}><span class="text-[10px] uppercase tracking-widest text-gray-400 font-bold"${_scopeId}>Halaman</span><div class="flex items-baseline gap-1"${_scopeId}><span class="text-sm font-black text-primary-600 dark:text-primary-400"${_scopeId}>${ssrInterpolate(props.page)}</span><span class="text-xs text-gray-300 dark:text-gray-600"${_scopeId}>/</span><span class="text-xs font-medium text-gray-500"${_scopeId}>${ssrInterpolate(maxPage.value)}</span></div></div>`);
            _push2(ssrRenderComponent(_component_UButton, {
              icon: "i-lucide-arrow-right",
              color: "neutral",
              variant: "ghost",
              size: "sm",
              trailing: "",
              disabled: !canNext.value,
              onClick: goNext,
              class: "rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<span class="text-xs font-medium"${_scopeId2}>Next</span>`);
                } else {
                  return [
                    createVNode("span", { class: "text-xs font-medium" }, "Next")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_UPagination, {
              class: "hidden sm:flex",
              page: props.page,
              total: props.totalRows,
              "items-per-page": props.itemsPerPage,
              size: "sm",
              "onUpdate:page": onPageUpdate
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("span", { class: "text-xs text-muted" }, "Total " + toDisplayString(props.totalRows) + " data", 1),
                createVNode("div", { class: "flex items-center justify-between gap-4 py-2 sm:hidden border-t border-gray-100 dark:border-gray-800" }, [
                  createVNode(_component_UButton, {
                    icon: "i-lucide-arrow-left",
                    color: "neutral",
                    variant: "ghost",
                    size: "sm",
                    disabled: !canPrev.value,
                    onClick: goPrev,
                    class: "rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900"
                  }, {
                    default: withCtx(() => [
                      createVNode("span", { class: "text-xs font-medium" }, "Prev")
                    ]),
                    _: 1
                  }, 8, ["disabled"]),
                  createVNode("div", { class: "flex flex-col items-center" }, [
                    createVNode("span", { class: "text-[10px] uppercase tracking-widest text-gray-400 font-bold" }, "Halaman"),
                    createVNode("div", { class: "flex items-baseline gap-1" }, [
                      createVNode("span", { class: "text-sm font-black text-primary-600 dark:text-primary-400" }, toDisplayString(props.page), 1),
                      createVNode("span", { class: "text-xs text-gray-300 dark:text-gray-600" }, "/"),
                      createVNode("span", { class: "text-xs font-medium text-gray-500" }, toDisplayString(maxPage.value), 1)
                    ])
                  ]),
                  createVNode(_component_UButton, {
                    icon: "i-lucide-arrow-right",
                    color: "neutral",
                    variant: "ghost",
                    size: "sm",
                    trailing: "",
                    disabled: !canNext.value,
                    onClick: goNext,
                    class: "rounded-xl hover:bg-gray-100 dark:hover:bg-gray-900"
                  }, {
                    default: withCtx(() => [
                      createVNode("span", { class: "text-xs font-medium" }, "Next")
                    ]),
                    _: 1
                  }, 8, ["disabled"])
                ]),
                createVNode(_component_UPagination, {
                  class: "hidden sm:flex",
                  page: props.page,
                  total: props.totalRows,
                  "items-per-page": props.itemsPerPage,
                  size: "sm",
                  "onUpdate:page": onPageUpdate
                }, null, 8, ["page", "total", "items-per-page"])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="border-b border-default"${_scopeId}><div class="overflow-x-auto px-2 py-1 sm:px-0 sm:py-0"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UTabs, {
              "model-value": props.activeTab,
              items: props.tabs,
              "value-key": "value",
              content: false,
              class: "w-max min-w-full",
              "onUpdate:modelValue": onTabUpdate
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="overflow-x-auto"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UTable, {
              data: props.rows,
              columns: props.columns,
              class: "w-full min-w-180"
            }, {
              empty: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col items-center justify-center py-10"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-database-backup",
                    class: "mb-2 size-8 text-muted"
                  }, null, _parent3, _scopeId2));
                  _push3(`<p class="text-sm text-muted"${_scopeId2}>Belum ada data bonus untuk kategori ini.</p></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col items-center justify-center py-10" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-database-backup",
                        class: "mb-2 size-8 text-muted"
                      }),
                      createVNode("p", { class: "text-sm text-muted" }, "Belum ada data bonus untuk kategori ini.")
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "border-b border-default" }, [
                createVNode("div", { class: "overflow-x-auto px-2 py-1 sm:px-0 sm:py-0" }, [
                  createVNode(_component_UTabs, {
                    "model-value": props.activeTab,
                    items: props.tabs,
                    "value-key": "value",
                    content: false,
                    class: "w-max min-w-full",
                    "onUpdate:modelValue": onTabUpdate
                  }, null, 8, ["model-value", "items"])
                ])
              ]),
              createVNode("div", { class: "overflow-x-auto" }, [
                createVNode(_component_UTable, {
                  data: props.rows,
                  columns: props.columns,
                  class: "w-full min-w-180"
                }, {
                  empty: withCtx(() => [
                    createVNode("div", { class: "flex flex-col items-center justify-center py-10" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-database-backup",
                        class: "mb-2 size-8 text-muted"
                      }),
                      createVNode("p", { class: "text-sm text-muted" }, "Belum ada data bonus untuk kategori ini.")
                    ])
                  ]),
                  _: 1
                }, 8, ["data", "columns"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/bonus/BonusHistoryTableCard.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Bonus",
  __ssrInlineRender: true,
  props: {
    bonusStats: { default: () => [] },
    bonusTables: { default: () => ({
      referral_incentive: [],
      team_affiliate_commission: [],
      partner_team_commission: [],
      cashback_commission: [],
      promotions_rewards: [],
      retail_commission: [],
      lifetime_cash_rewards: []
    }) }
  },
  setup(__props) {
    const props = __props;
    const {
      activeTab,
      searchQuery,
      page,
      itemsPerPage,
      tabs,
      displayedStats,
      filteredRows,
      paginatedRows,
      columns,
      formatIDR
    } = useDashboardBonus({
      bonusStats: computed(() => props.bonusStats),
      bonusTables: computed(() => props.bonusTables)
    });
    function onActiveTabChange(value) {
      activeTab.value = value;
    }
    function onSearchQueryChange(value) {
      searchQuery.value = value;
    }
    function onPageChange(value) {
      page.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}>`);
      _push(ssrRenderComponent(_sfc_main$2, {
        stats: unref(displayedStats),
        "format-currency": unref(formatIDR)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$1, {
        "active-tab": unref(activeTab),
        "search-query": unref(searchQuery),
        page: unref(page),
        "items-per-page": unref(itemsPerPage),
        tabs: unref(tabs),
        rows: unref(paginatedRows),
        "total-rows": unref(filteredRows).length,
        columns: unref(columns),
        "onUpdate:activeTab": onActiveTabChange,
        "onUpdate:searchQuery": onSearchQueryChange,
        "onUpdate:page": onPageChange
      }, null, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Bonus.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
