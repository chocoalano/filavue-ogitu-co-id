import { ref, computed, watch, h, defineComponent, mergeProps, withCtx, createVNode, createTextVNode, toDisplayString, useSSRContext, unref } from "vue";
import { ssrRenderComponent, ssrInterpolate, ssrRenderAttrs } from "vue/server-renderer";
import { router } from "@inertiajs/vue3";
import { u as useDashboard } from "./useDashboard-DR5F4MRN.js";
import { _ as _sfc_main$5 } from "./Pagination-uWPH5vE_.js";
import { _ as _sfc_main$4 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$3 } from "./Table-CEJ662o2.js";
import { _ as _sfc_main$8 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$7 } from "./SelectMenu-D4PFVS1f.js";
import { _ as _sfc_main$6 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$2 } from "./Card-CvchAxCK.js";
import "./useToast-CTuSIf9f.js";
import "reka-ui";
import "@vueuse/core";
import "hookable";
import "defu";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "scule";
import "@tanstack/vue-table";
import "@tanstack/vue-virtual";
import "ufo";
import "./usePortal-EQErrF6h.js";
import "./Input-DFvIE7JC.js";
const DEFAULT_GENERATION_NETWORK = {
  data: [],
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
  from: null,
  to: null,
  filters: {
    level: null
  },
  available_generations: []
};
function statusBadgeClass(status) {
  if (status === 3) {
    return "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300";
  }
  if (status === 2) {
    return "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300";
  }
  return "bg-gray-100 text-gray-700 dark:bg-gray-900/60 dark:text-gray-300";
}
function useDashboardGenerationNetwork(options) {
  const { formatDate, formatIDR, formatPhoneDisplay } = useDashboard();
  const selectedGeneration = ref("all");
  const isApplyingFilter = ref(false);
  const payload = computed(
    () => options.generationNetwork.value ?? DEFAULT_GENERATION_NETWORK
  );
  watch(
    payload,
    (value) => {
      const level = Number(value.filters?.level ?? 0);
      selectedGeneration.value = level > 0 ? level : "all";
    },
    { immediate: true }
  );
  const rows = computed(() => payload.value.data ?? []);
  const totalRows = computed(() => Number(payload.value.total ?? 0));
  const currentPage = computed(() => Number(payload.value.current_page ?? 1));
  const itemsPerPage = computed(() => Number(payload.value.per_page ?? 15));
  const lastPage = computed(() => Number(payload.value.last_page ?? 1));
  const shownFrom = computed(() => payload.value.from ?? null);
  const shownTo = computed(() => payload.value.to ?? null);
  const generationItems = computed(() => [
    {
      label: "Semua generasi",
      value: "all"
    },
    ...payload.value.available_generations ?? []
  ]);
  const activeGenerationLabel = computed(() => {
    if (selectedGeneration.value === "all") {
      return "Semua generasi";
    }
    return `Generasi ${selectedGeneration.value}`;
  });
  function buildQuery(pageNumber, generation) {
    const query = {
      section: "generation_network"
    };
    if (pageNumber > 1) {
      query.generation_page = pageNumber;
    }
    if (generation !== "all") {
      query.generation_level = generation;
    }
    return query;
  }
  function visit(pageNumber, generation) {
    isApplyingFilter.value = true;
    router.get("/dashboard", buildQuery(pageNumber, generation), {
      only: ["generationNetwork"],
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onFinish: () => {
        isApplyingFilter.value = false;
      }
    });
  }
  function applyFilter() {
    visit(1, selectedGeneration.value);
  }
  function resetFilter() {
    selectedGeneration.value = "all";
    visit(1, "all");
  }
  function goToPage(page) {
    const targetPage = Math.min(Math.max(1, page), lastPage.value || 1);
    if (targetPage === currentPage.value || isApplyingFilter.value) {
      return;
    }
    visit(targetPage, selectedGeneration.value);
  }
  const columns = [
    {
      id: "generation",
      accessorKey: "generation",
      header: "Generasi",
      cell: ({ row }) => h(
        "span",
        {
          class: "inline-flex items-center rounded-full bg-primary-100 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300"
        },
        `Generasi ${row.original.generation}`
      )
    },
    {
      id: "member",
      accessorKey: "name",
      header: "Member",
      cell: ({ row }) => {
        const member = row.original;
        return h("div", { class: "min-w-0 flex flex-col" }, [
          h("span", { class: "truncate text-sm font-semibold text-highlighted" }, member.name || "-"),
          h("span", { class: "truncate text-xs text-muted" }, member.username ? `@${member.username}` : "-")
        ]);
      }
    },
    {
      id: "contact",
      accessorKey: "phone",
      header: "Kontak",
      cell: ({ row }) => {
        const member = row.original;
        return h("div", { class: "min-w-0 flex flex-col" }, [
          h("span", { class: "truncate text-sm text-highlighted" }, formatPhoneDisplay(member.phone)),
          h("span", { class: "truncate text-xs text-muted" }, member.email || "-")
        ]);
      }
    },
    {
      id: "package",
      accessorKey: "package_name",
      header: "Paket / Peringkat",
      cell: ({ row }) => {
        const member = row.original;
        return h("div", { class: "min-w-0 flex flex-col" }, [
          h("span", { class: "truncate text-sm text-highlighted" }, member.package_name || "-"),
          h("span", { class: "truncate text-xs text-muted" }, member.member_level || "-")
        ]);
      }
    },
    {
      id: "omzet_group",
      accessorKey: "omzet_group",
      header: "Omset Group",
      meta: {
        class: {
          th: "text-right",
          td: "text-right"
        }
      },
      cell: ({ row }) => h(
        "span",
        { class: "font-semibold tabular-nums text-highlighted" },
        formatIDR(Number(row.original.omzet_group ?? 0))
      )
    },
    {
      id: "joined_at",
      accessorKey: "joined_at",
      header: "Bergabung / Status",
      cell: ({ row }) => {
        const member = row.original;
        return h("div", { class: "min-w-0 flex flex-col items-start gap-1" }, [
          h("span", { class: "text-sm text-highlighted" }, formatDate(member.joined_at ?? void 0)),
          h(
            "span",
            {
              class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ${statusBadgeClass(member.status)}`
            },
            member.status_label
          )
        ]);
      }
    }
  ];
  return {
    selectedGeneration,
    generationItems,
    activeGenerationLabel,
    rows,
    columns,
    currentPage,
    itemsPerPage,
    totalRows,
    lastPage,
    shownFrom,
    shownTo,
    isApplyingFilter,
    applyFilter,
    resetFilter,
    goToPage
  };
}
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "GenerationNetworkTableCard",
  __ssrInlineRender: true,
  props: {
    selectedGeneration: {},
    generationItems: {},
    activeGenerationLabel: {},
    rows: {},
    columns: {},
    currentPage: {},
    itemsPerPage: {},
    totalRows: {},
    lastPage: {},
    shownFrom: {},
    shownTo: {},
    isApplying: { type: Boolean }
  },
  emits: ["update:selectedGeneration", "apply", "reset", "pageChange"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function onGenerationUpdate(value) {
      emit("update:selectedGeneration", value === "all" ? "all" : Number(value));
    }
    function onPageUpdate(value) {
      emit("pageChange", value);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$2;
      const _component_UFormField = _sfc_main$6;
      const _component_USelectMenu = _sfc_main$7;
      const _component_UButton = _sfc_main$8;
      const _component_UTable = _sfc_main$3;
      const _component_UIcon = _sfc_main$4;
      const _component_UPagination = _sfc_main$5;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "overflow-hidden rounded-3xl",
        ui: { header: "px-4 py-4 sm:px-6", body: "p-0", footer: "px-4 py-4 sm:px-6" }
      }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"${_scopeId}><div class="space-y-1"${_scopeId}><h3 class="text-lg font-bold text-highlighted"${_scopeId}>Jaringan Generasi</h3><p class="text-sm text-muted"${_scopeId}> Data mengikuti customer yang sedang login atau sedang di-impersonate. </p></div><div class="flex flex-col gap-3 sm:flex-row sm:items-end"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Filter generasi",
              class: "w-full sm:w-56"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    "model-value": props.selectedGeneration,
                    items: props.generationItems,
                    "value-key": "value",
                    "label-key": "label",
                    class: "w-full",
                    "onUpdate:modelValue": onGenerationUpdate
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      "model-value": props.selectedGeneration,
                      items: props.generationItems,
                      "value-key": "value",
                      "label-key": "label",
                      class: "w-full",
                      "onUpdate:modelValue": onGenerationUpdate
                    }, null, 8, ["model-value", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="flex gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              icon: "i-lucide-filter",
              loading: props.isApplying,
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
            _push2(`</div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between" }, [
                createVNode("div", { class: "space-y-1" }, [
                  createVNode("h3", { class: "text-lg font-bold text-highlighted" }, "Jaringan Generasi"),
                  createVNode("p", { class: "text-sm text-muted" }, " Data mengikuti customer yang sedang login atau sedang di-impersonate. ")
                ]),
                createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-end" }, [
                  createVNode(_component_UFormField, {
                    label: "Filter generasi",
                    class: "w-full sm:w-56"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelectMenu, {
                        "model-value": props.selectedGeneration,
                        items: props.generationItems,
                        "value-key": "value",
                        "label-key": "label",
                        class: "w-full",
                        "onUpdate:modelValue": onGenerationUpdate
                      }, null, 8, ["model-value", "items"])
                    ]),
                    _: 1
                  }),
                  createVNode("div", { class: "flex gap-2" }, [
                    createVNode(_component_UButton, {
                      color: "primary",
                      icon: "i-lucide-filter",
                      loading: props.isApplying,
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
              ])
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><div class="text-xs text-muted"${_scopeId}> Total ${ssrInterpolate(props.totalRows)} data jaringan generasi. </div>`);
            _push2(ssrRenderComponent(_component_UPagination, {
              page: props.currentPage,
              total: props.totalRows,
              "items-per-page": props.itemsPerPage,
              "show-edges": "",
              disabled: props.isApplying || props.lastPage <= 1,
              "onUpdate:page": onPageUpdate
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("div", { class: "text-xs text-muted" }, " Total " + toDisplayString(props.totalRows) + " data jaringan generasi. ", 1),
                createVNode(_component_UPagination, {
                  page: props.currentPage,
                  total: props.totalRows,
                  "items-per-page": props.itemsPerPage,
                  "show-edges": "",
                  disabled: props.isApplying || props.lastPage <= 1,
                  "onUpdate:page": onPageUpdate
                }, null, 8, ["page", "total", "items-per-page", "disabled"])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="border-y border-default bg-elevated/20 px-4 py-3 sm:px-6"${_scopeId}><div class="flex flex-col gap-2 text-sm text-muted sm:flex-row sm:items-center sm:justify-between"${_scopeId}><p${_scopeId}> Filter aktif: <span class="font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(props.activeGenerationLabel)}</span></p><p${_scopeId}> Menampilkan <span class="font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(props.shownFrom ?? 0)} - ${ssrInterpolate(props.shownTo ?? 0)}</span> dari <span class="font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(props.totalRows)}</span> member. </p></div></div><div class="overflow-x-auto"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UTable, {
              data: props.rows,
              columns: props.columns,
              class: "min-w-220"
            }, {
              empty: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col items-center justify-center px-4 py-12 text-center"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-users-round",
                    class: "mb-3 size-8 text-muted"
                  }, null, _parent3, _scopeId2));
                  _push3(`<p class="text-sm font-semibold text-highlighted"${_scopeId2}>Belum ada data jaringan generasi</p><p class="mt-1 text-sm text-muted"${_scopeId2}> Coba ubah filter generasi atau pastikan member sudah memiliki jaringan sponsorship. </p></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col items-center justify-center px-4 py-12 text-center" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-users-round",
                        class: "mb-3 size-8 text-muted"
                      }),
                      createVNode("p", { class: "text-sm font-semibold text-highlighted" }, "Belum ada data jaringan generasi"),
                      createVNode("p", { class: "mt-1 text-sm text-muted" }, " Coba ubah filter generasi atau pastikan member sudah memiliki jaringan sponsorship. ")
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "border-y border-default bg-elevated/20 px-4 py-3 sm:px-6" }, [
                createVNode("div", { class: "flex flex-col gap-2 text-sm text-muted sm:flex-row sm:items-center sm:justify-between" }, [
                  createVNode("p", null, [
                    createTextVNode(" Filter aktif: "),
                    createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(props.activeGenerationLabel), 1)
                  ]),
                  createVNode("p", null, [
                    createTextVNode(" Menampilkan "),
                    createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(props.shownFrom ?? 0) + " - " + toDisplayString(props.shownTo ?? 0), 1),
                    createTextVNode(" dari "),
                    createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(props.totalRows), 1),
                    createTextVNode(" member. ")
                  ])
                ])
              ]),
              createVNode("div", { class: "overflow-x-auto" }, [
                createVNode(_component_UTable, {
                  data: props.rows,
                  columns: props.columns,
                  class: "min-w-220"
                }, {
                  empty: withCtx(() => [
                    createVNode("div", { class: "flex flex-col items-center justify-center px-4 py-12 text-center" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-users-round",
                        class: "mb-3 size-8 text-muted"
                      }),
                      createVNode("p", { class: "text-sm font-semibold text-highlighted" }, "Belum ada data jaringan generasi"),
                      createVNode("p", { class: "mt-1 text-sm text-muted" }, " Coba ubah filter generasi atau pastikan member sudah memiliki jaringan sponsorship. ")
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/GenerationNetworkTableCard.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "GenerationNetwork",
  __ssrInlineRender: true,
  props: {
    generationNetwork: { default: () => ({
      data: [],
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
      from: null,
      to: null,
      filters: {
        level: null
      },
      available_generations: []
    }) }
  },
  setup(__props) {
    const props = __props;
    const {
      selectedGeneration,
      generationItems,
      activeGenerationLabel,
      rows,
      columns,
      currentPage,
      itemsPerPage,
      totalRows,
      lastPage,
      shownFrom,
      shownTo,
      isApplyingFilter,
      applyFilter,
      resetFilter,
      goToPage
    } = useDashboardGenerationNetwork({
      generationNetwork: computed(() => props.generationNetwork)
    });
    function onSelectedGenerationChange(value) {
      selectedGeneration.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-4" }, _attrs))}>`);
      _push(ssrRenderComponent(_sfc_main$1, {
        "selected-generation": unref(selectedGeneration),
        "generation-items": unref(generationItems),
        "active-generation-label": unref(activeGenerationLabel),
        rows: unref(rows),
        columns: unref(columns),
        "current-page": unref(currentPage),
        "items-per-page": unref(itemsPerPage),
        "total-rows": unref(totalRows),
        "last-page": unref(lastPage),
        "shown-from": unref(shownFrom),
        "shown-to": unref(shownTo),
        "is-applying": unref(isApplyingFilter),
        "onUpdate:selectedGeneration": onSelectedGenerationChange,
        onApply: unref(applyFilter),
        onReset: unref(resetFilter),
        onPageChange: unref(goToPage)
      }, null, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/GenerationNetwork.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
