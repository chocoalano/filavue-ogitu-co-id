import { ref, computed, watch, onBeforeUnmount, defineComponent, mergeProps, withCtx, createTextVNode, createVNode, openBlock, createBlock, createCommentVNode, toDisplayString, useSSRContext, Fragment, renderList, unref, resolveDynamicComponent } from "vue";
import { ssrRenderComponent, ssrInterpolate, ssrRenderAttrs, ssrRenderList, ssrRenderAttr, ssrRenderVNode } from "vue/server-renderer";
import { router, Head } from "@inertiajs/vue3";
import { _ as _sfc_main$c, a as _sfc_main$f } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$g } from "./SeoHead-CrFJik-H.js";
import { _ as _sfc_main$7 } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$6 } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$b } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$a } from "./SelectMenu-D4PFVS1f.js";
import { _ as _sfc_main$9 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$8 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$d } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$e } from "./Pagination-uWPH5vE_.js";
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
import "ufo";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
const SORT_ITEMS = [
  { label: "Terbaru", value: "newest" },
  { label: "Terlama", value: "oldest" },
  { label: "A-Z", value: "az" },
  { label: "Z-A", value: "za" }
];
function useArticleCatalog(props) {
  const searchQuery = ref(props.filters.search ?? "");
  const selectedTag = ref(props.filters.tag ?? "");
  const sortValue = ref(props.filters.sort ?? "newest");
  const isFilterDrawerOpen = ref(false);
  const isApplying = ref(false);
  const isSyncing = ref(false);
  let searchDebounceTimer = null;
  const articles = computed(() => props.articles.data);
  const pagination = computed(() => props.articles);
  const availableTags = computed(() => props.availableTags);
  const stats = computed(() => props.stats);
  const sortItems = computed(() => SORT_ITEMS);
  const tagItems = computed(() => {
    return [
      { label: "Semua tag", value: "" },
      ...availableTags.value.map((tag) => ({ label: tag, value: tag }))
    ];
  });
  const hasActiveFilters = computed(() => {
    return searchQuery.value.trim() !== "" || selectedTag.value.trim() !== "" || sortValue.value !== "newest";
  });
  const selectedSortLabel = computed(() => {
    const item = sortItems.value.find((option) => option.value === sortValue.value);
    return item?.label ?? "Terbaru";
  });
  const selectedTagLabel = computed(() => {
    if (selectedTag.value.trim() === "") {
      return "Semua tag";
    }
    return selectedTag.value;
  });
  function normalizeFilters(page) {
    const normalized = {};
    const search = searchQuery.value.trim();
    const tag = selectedTag.value.trim();
    if (search !== "") {
      normalized.search = search;
    }
    if (tag !== "") {
      normalized.tag = tag;
    }
    if (sortValue.value !== "newest") {
      normalized.sort = sortValue.value;
    }
    if (page > 1) {
      normalized.page = page;
    }
    return normalized;
  }
  function applyFilters(page = 1) {
    router.get("/articles", normalizeFilters(page), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ["seo", "articles", "filters", "availableTags", "stats"],
      onStart: () => {
        isApplying.value = true;
      },
      onFinish: () => {
        isApplying.value = false;
      }
    });
  }
  function onSearchQueryChange(value) {
    searchQuery.value = value;
  }
  function onTagChange(value) {
    selectedTag.value = value;
    applyFilters(1);
  }
  function onSortChange(value) {
    sortValue.value = value;
    applyFilters(1);
  }
  function onPageChange(page) {
    applyFilters(page);
  }
  function applyCurrentFilters() {
    applyFilters(1);
  }
  function resetFilters() {
    searchQuery.value = "";
    selectedTag.value = "";
    sortValue.value = "newest";
    applyFilters(1);
  }
  function openFilterDrawer() {
    isFilterDrawerOpen.value = true;
  }
  function closeFilterDrawer() {
    isFilterDrawerOpen.value = false;
  }
  function setFilterDrawerOpen(value) {
    isFilterDrawerOpen.value = value;
  }
  watch(
    () => props.filters,
    (filters) => {
      isSyncing.value = true;
      searchQuery.value = filters.search ?? "";
      selectedTag.value = filters.tag ?? "";
      sortValue.value = filters.sort ?? "newest";
      setTimeout(() => {
        isSyncing.value = false;
      }, 0);
    },
    { deep: true }
  );
  watch(searchQuery, () => {
    if (isSyncing.value) {
      return;
    }
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
    }
    searchDebounceTimer = setTimeout(() => {
      applyFilters(1);
    }, 400);
  });
  onBeforeUnmount(() => {
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
      searchDebounceTimer = null;
    }
  });
  return {
    searchQuery,
    selectedTag,
    sortValue,
    isFilterDrawerOpen,
    isApplying,
    articles,
    pagination,
    availableTags,
    stats,
    sortItems,
    tagItems,
    hasActiveFilters,
    selectedSortLabel,
    selectedTagLabel,
    applyCurrentFilters,
    onSearchQueryChange,
    onTagChange,
    onSortChange,
    onPageChange,
    resetFilters,
    openFilterDrawer,
    closeFilterDrawer,
    setFilterDrawerOpen
  };
}
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "ArticlePageHeader",
  __ssrInlineRender: true,
  props: {
    stats: {},
    hasActiveFilters: { type: Boolean }
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$6;
      const _component_UBadge = _sfc_main$7;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "overflow-hidden rounded-3xl border border-primary-100/60 bg-linear-to-br from-primary-50/70 via-white to-cyan-50/40 dark:border-primary-900/40 dark:from-primary-950/50 dark:via-gray-950 dark:to-cyan-950/20" }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-6"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "primary",
              variant: "soft",
              size: "sm",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`Insight`);
                } else {
                  return [
                    createTextVNode("Insight")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "neutral",
              variant: "subtle",
              size: "sm",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`Nuxt UI v4`);
                } else {
                  return [
                    createTextVNode("Nuxt UI v4")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (__props.hasActiveFilters) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "warning",
                variant: "soft",
                size: "sm",
                class: "rounded-full"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Filter aktif `);
                  } else {
                    return [
                      createTextVNode(" Filter aktif ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><div class="space-y-2"${_scopeId}><h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white"${_scopeId}> Artikel &amp; Wawasan Terbaru </h1><p class="max-w-3xl text-sm leading-relaxed text-gray-600 sm:text-base dark:text-gray-300"${_scopeId}> Kumpulan artikel edukasi, strategi bisnis, dan update produk yang dirancang untuk membantu Anda mengambil keputusan lebih cepat. </p></div><div class="grid gap-3 sm:grid-cols-3"${_scopeId}><div class="rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Total Artikel</p><p class="text-xl font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.stats.total_articles)}</p></div><div class="rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Hasil Saat Ini</p><p class="text-xl font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.stats.result_count)}</p></div><div class="rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Tag Tersedia</p><p class="text-xl font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.stats.tag_count)}</p></div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-6" }, [
                createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                  createVNode(_component_UBadge, {
                    color: "primary",
                    variant: "soft",
                    size: "sm",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode("Insight")
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UBadge, {
                    color: "neutral",
                    variant: "subtle",
                    size: "sm",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode("Nuxt UI v4")
                    ]),
                    _: 1
                  }),
                  __props.hasActiveFilters ? (openBlock(), createBlock(_component_UBadge, {
                    key: 0,
                    color: "warning",
                    variant: "soft",
                    size: "sm",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Filter aktif ")
                    ]),
                    _: 1
                  })) : createCommentVNode("", true)
                ]),
                createVNode("div", { class: "space-y-2" }, [
                  createVNode("h1", { class: "text-2xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white" }, " Artikel & Wawasan Terbaru "),
                  createVNode("p", { class: "max-w-3xl text-sm leading-relaxed text-gray-600 sm:text-base dark:text-gray-300" }, " Kumpulan artikel edukasi, strategi bisnis, dan update produk yang dirancang untuk membantu Anda mengambil keputusan lebih cepat. ")
                ]),
                createVNode("div", { class: "grid gap-3 sm:grid-cols-3" }, [
                  createVNode("div", { class: "rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40" }, [
                    createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Total Artikel"),
                    createVNode("p", { class: "text-xl font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.stats.total_articles), 1)
                  ]),
                  createVNode("div", { class: "rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40" }, [
                    createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Hasil Saat Ini"),
                    createVNode("p", { class: "text-xl font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.stats.result_count), 1)
                  ]),
                  createVNode("div", { class: "rounded-2xl border border-default bg-white/80 px-4 py-3 dark:bg-gray-900/40" }, [
                    createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Tag Tersedia"),
                    createVNode("p", { class: "text-xl font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.stats.tag_count), 1)
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
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticlePageHeader.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "ArticleFilterBar",
  __ssrInlineRender: true,
  props: {
    searchQuery: {},
    selectedTag: {},
    sortValue: {},
    tagItems: {},
    sortItems: {},
    selectedSortLabel: {},
    isApplying: { type: Boolean },
    hasActiveFilters: { type: Boolean }
  },
  emits: ["update:searchQuery", "update:selectedTag", "update:sortValue", "apply", "reset", "openMobile"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function onTagChange(value) {
      emit("update:selectedTag", value);
    }
    function onSortChange(value) {
      emit("update:sortValue", value);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$6;
      const _component_UFormField = _sfc_main$8;
      const _component_UInput = _sfc_main$9;
      const _component_USelectMenu = _sfc_main$a;
      const _component_UButton = _sfc_main$b;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3 lg:flex-row lg:items-end"${_scopeId}><div class="w-full lg:max-w-sm"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, { label: "Cari Artikel" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    "model-value": props.searchQuery,
                    icon: "i-lucide-search",
                    placeholder: "Contoh: strategi closing",
                    size: "lg",
                    class: "w-full",
                    "onUpdate:modelValue": (value) => emit("update:searchQuery", value)
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      "model-value": props.searchQuery,
                      icon: "i-lucide-search",
                      placeholder: "Contoh: strategi closing",
                      size: "lg",
                      class: "w-full",
                      "onUpdate:modelValue": (value) => emit("update:searchQuery", value)
                    }, null, 8, ["model-value", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="w-full lg:max-w-xs"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, { label: "Tag" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    "model-value": props.selectedTag,
                    items: props.tagItems,
                    "value-key": "value",
                    class: "w-full",
                    "onUpdate:modelValue": onTagChange
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      "model-value": props.selectedTag,
                      items: props.tagItems,
                      "value-key": "value",
                      class: "w-full",
                      "onUpdate:modelValue": onTagChange
                    }, null, 8, ["model-value", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="w-full lg:max-w-xs"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, { label: "Urutkan" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    "model-value": props.sortValue,
                    items: props.sortItems,
                    "value-key": "value",
                    class: "w-full",
                    "onUpdate:modelValue": onSortChange
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      "model-value": props.sortValue,
                      items: props.sortItems,
                      "value-key": "value",
                      class: "w-full",
                      "onUpdate:modelValue": onSortChange
                    }, null, 8, ["model-value", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="flex gap-2 lg:ml-auto"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              icon: "i-lucide-sliders-horizontal",
              class: "lg:hidden",
              onClick: ($event) => emit("openMobile")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Filter `);
                } else {
                  return [
                    createTextVNode(" Filter ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              icon: "i-lucide-rotate-ccw",
              disabled: !props.hasActiveFilters || props.isApplying,
              onClick: ($event) => emit("reset")
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
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              icon: "i-lucide-check",
              loading: props.isApplying,
              onClick: ($event) => emit("apply")
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
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3 lg:flex-row lg:items-end" }, [
                createVNode("div", { class: "w-full lg:max-w-sm" }, [
                  createVNode(_component_UFormField, { label: "Cari Artikel" }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        "model-value": props.searchQuery,
                        icon: "i-lucide-search",
                        placeholder: "Contoh: strategi closing",
                        size: "lg",
                        class: "w-full",
                        "onUpdate:modelValue": (value) => emit("update:searchQuery", value)
                      }, null, 8, ["model-value", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  })
                ]),
                createVNode("div", { class: "w-full lg:max-w-xs" }, [
                  createVNode(_component_UFormField, { label: "Tag" }, {
                    default: withCtx(() => [
                      createVNode(_component_USelectMenu, {
                        "model-value": props.selectedTag,
                        items: props.tagItems,
                        "value-key": "value",
                        class: "w-full",
                        "onUpdate:modelValue": onTagChange
                      }, null, 8, ["model-value", "items"])
                    ]),
                    _: 1
                  })
                ]),
                createVNode("div", { class: "w-full lg:max-w-xs" }, [
                  createVNode(_component_UFormField, { label: "Urutkan" }, {
                    default: withCtx(() => [
                      createVNode(_component_USelectMenu, {
                        "model-value": props.sortValue,
                        items: props.sortItems,
                        "value-key": "value",
                        class: "w-full",
                        "onUpdate:modelValue": onSortChange
                      }, null, 8, ["model-value", "items"])
                    ]),
                    _: 1
                  })
                ]),
                createVNode("div", { class: "flex gap-2 lg:ml-auto" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    icon: "i-lucide-sliders-horizontal",
                    class: "lg:hidden",
                    onClick: ($event) => emit("openMobile")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Filter ")
                    ]),
                    _: 1
                  }, 8, ["onClick"]),
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    icon: "i-lucide-rotate-ccw",
                    disabled: !props.hasActiveFilters || props.isApplying,
                    onClick: ($event) => emit("reset")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Reset ")
                    ]),
                    _: 1
                  }, 8, ["disabled", "onClick"]),
                  createVNode(_component_UButton, {
                    color: "primary",
                    icon: "i-lucide-check",
                    loading: props.isApplying,
                    onClick: ($event) => emit("apply")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Terapkan ")
                    ]),
                    _: 1
                  }, 8, ["loading", "onClick"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleFilterBar.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "ArticleFilterDrawer",
  __ssrInlineRender: true,
  props: {
    open: { type: Boolean },
    searchQuery: {},
    selectedTag: {},
    sortValue: {},
    tagItems: {},
    sortItems: {},
    isApplying: { type: Boolean },
    hasActiveFilters: { type: Boolean }
  },
  emits: ["update:open", "update:searchQuery", "update:selectedTag", "update:sortValue", "apply", "reset"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function closeDrawer() {
      emit("update:open", false);
    }
    function applyAndClose() {
      emit("apply");
      closeDrawer();
    }
    function resetAndClose() {
      emit("reset");
      closeDrawer();
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UDrawer = _sfc_main$c;
      const _component_UFormField = _sfc_main$8;
      const _component_UInput = _sfc_main$9;
      const _component_USelectMenu = _sfc_main$a;
      const _component_UButton = _sfc_main$b;
      _push(ssrRenderComponent(_component_UDrawer, mergeProps({
        open: props.open,
        title: "Filter Artikel",
        description: "Sesuaikan hasil artikel berdasarkan kata kunci, tag, dan urutan.",
        ui: { overlay: "z-[80]", content: "z-[81] max-h-[85dvh]" },
        "onUpdate:open": (value) => emit("update:open", value)
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Cari Artikel",
              help: "Berdasarkan judul dan SEO"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    "model-value": props.searchQuery,
                    icon: "i-lucide-search",
                    placeholder: "Contoh: omzet",
                    "onUpdate:modelValue": (value) => emit("update:searchQuery", value)
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      "model-value": props.searchQuery,
                      icon: "i-lucide-search",
                      placeholder: "Contoh: omzet",
                      "onUpdate:modelValue": (value) => emit("update:searchQuery", value)
                    }, null, 8, ["model-value", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, { label: "Tag" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    "model-value": props.selectedTag,
                    items: props.tagItems,
                    "value-key": "value",
                    "onUpdate:modelValue": (value) => emit("update:selectedTag", value)
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      "model-value": props.selectedTag,
                      items: props.tagItems,
                      "value-key": "value",
                      "onUpdate:modelValue": (value) => emit("update:selectedTag", value)
                    }, null, 8, ["model-value", "items", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, { label: "Urutkan" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    "model-value": props.sortValue,
                    items: props.sortItems,
                    "value-key": "value",
                    "onUpdate:modelValue": (value) => emit("update:sortValue", value)
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      "model-value": props.sortValue,
                      items: props.sortItems,
                      "value-key": "value",
                      "onUpdate:modelValue": (value) => emit("update:sortValue", value)
                    }, null, 8, ["model-value", "items", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="grid grid-cols-2 gap-2 pt-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              icon: "i-lucide-rotate-ccw",
              disabled: !props.hasActiveFilters || props.isApplying,
              onClick: resetAndClose
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
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              icon: "i-lucide-check",
              loading: props.isApplying,
              onClick: applyAndClose
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
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode(_component_UFormField, {
                  label: "Cari Artikel",
                  help: "Berdasarkan judul dan SEO"
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UInput, {
                      "model-value": props.searchQuery,
                      icon: "i-lucide-search",
                      placeholder: "Contoh: omzet",
                      "onUpdate:modelValue": (value) => emit("update:searchQuery", value)
                    }, null, 8, ["model-value", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, { label: "Tag" }, {
                  default: withCtx(() => [
                    createVNode(_component_USelectMenu, {
                      "model-value": props.selectedTag,
                      items: props.tagItems,
                      "value-key": "value",
                      "onUpdate:modelValue": (value) => emit("update:selectedTag", value)
                    }, null, 8, ["model-value", "items", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode(_component_UFormField, { label: "Urutkan" }, {
                  default: withCtx(() => [
                    createVNode(_component_USelectMenu, {
                      "model-value": props.sortValue,
                      items: props.sortItems,
                      "value-key": "value",
                      "onUpdate:modelValue": (value) => emit("update:sortValue", value)
                    }, null, 8, ["model-value", "items", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }),
                createVNode("div", { class: "grid grid-cols-2 gap-2 pt-2" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    icon: "i-lucide-rotate-ccw",
                    disabled: !props.hasActiveFilters || props.isApplying,
                    onClick: resetAndClose
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Reset ")
                    ]),
                    _: 1
                  }, 8, ["disabled"]),
                  createVNode(_component_UButton, {
                    color: "primary",
                    icon: "i-lucide-check",
                    loading: props.isApplying,
                    onClick: applyAndClose
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Terapkan ")
                    ]),
                    _: 1
                  }, 8, ["loading"])
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
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleFilterDrawer.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "ArticleCardGrid",
  __ssrInlineRender: true,
  props: {
    articles: {},
    isApplying: { type: Boolean }
  },
  setup(__props) {
    const props = __props;
    function formatDate(date) {
      if (!date) {
        return "-";
      }
      return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric"
      }).format(new Date(date));
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$d;
      const _component_UCard = _sfc_main$6;
      const _component_UBadge = _sfc_main$7;
      const _component_UButton = _sfc_main$b;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-4" }, _attrs))}>`);
      if (props.articles.length === 0) {
        _push(`<div class="rounded-3xl border border-dashed border-default p-10 text-center"><div class="mx-auto mb-4 grid size-14 place-items-center rounded-2xl bg-elevated">`);
        _push(ssrRenderComponent(_component_UIcon, {
          name: "i-lucide-newspaper",
          class: "size-7 text-muted"
        }, null, _parent));
        _push(`</div><p class="text-lg font-semibold text-highlighted">Belum ada artikel ditemukan</p><p class="mt-1 text-sm text-muted">Ubah filter atau kata kunci untuk melihat hasil lain.</p></div>`);
      } else {
        _push(`<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"><!--[-->`);
        ssrRenderList(props.articles, (article) => {
          _push(ssrRenderComponent(_component_UCard, {
            key: article.id,
            class: "group overflow-hidden rounded-2xl transition-all hover:-translate-y-0.5 hover:shadow-lg",
            ui: { body: "p-0" }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="relative h-48 overflow-hidden bg-elevated rounded-lg"${_scopeId}>`);
                if (article.banner_image || article.cover_image) {
                  _push2(`<img${ssrRenderAttr("src", article.banner_image || article.cover_image || "")}${ssrRenderAttr("alt", article.title)} class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy"${_scopeId}>`);
                } else {
                  _push2(`<div class="grid h-full w-full place-items-center"${_scopeId}>`);
                  _push2(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-image-off",
                    class: "size-8 text-muted"
                  }, null, _parent2, _scopeId));
                  _push2(`</div>`);
                }
                _push2(`<div class="absolute left-3 top-3 flex items-center gap-2"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: "neutral",
                  variant: "solid",
                  size: "xs",
                  class: "rounded-full"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(formatDate(article.published_at))}`);
                    } else {
                      return [
                        createTextVNode(toDisplayString(formatDate(article.published_at)), 1)
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: "primary",
                  variant: "soft",
                  size: "xs",
                  class: "rounded-full"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(article.read_time_minutes)} menit `);
                    } else {
                      return [
                        createTextVNode(toDisplayString(article.read_time_minutes) + " menit ", 1)
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                _push2(`</div></div><div class="space-y-4 p-4"${_scopeId}><div${_scopeId}><h2 class="line-clamp-2 text-lg font-semibold leading-snug text-highlighted"${_scopeId}>${ssrInterpolate(article.title)}</h2><p class="mt-2 line-clamp-3 text-sm leading-relaxed text-muted"${_scopeId}>${ssrInterpolate(article.excerpt)}</p></div>`);
                if (article.tags.length > 0) {
                  _push2(`<div class="flex flex-wrap gap-1.5"${_scopeId}><!--[-->`);
                  ssrRenderList(article.tags.slice(0, 3), (tag) => {
                    _push2(ssrRenderComponent(_component_UBadge, {
                      key: `${article.id}-${tag}`,
                      color: "primary",
                      variant: "subtle",
                      size: "xs",
                      class: "rounded-full"
                    }, {
                      default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                        if (_push3) {
                          _push3(`${ssrInterpolate(tag)}`);
                        } else {
                          return [
                            createTextVNode(toDisplayString(tag), 1)
                          ];
                        }
                      }),
                      _: 2
                    }, _parent2, _scopeId));
                  });
                  _push2(`<!--]--></div>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`<div class="flex items-center justify-between gap-2"${_scopeId}><span class="text-xs text-muted"${_scopeId}> Diperbarui ${ssrInterpolate(article.published_label ?? formatDate(article.published_at))}</span>`);
                _push2(ssrRenderComponent(_component_UButton, {
                  to: article.url,
                  size: "sm",
                  color: "primary",
                  variant: "outline",
                  "trailing-icon": "i-lucide-arrow-right",
                  loading: props.isApplying
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(` Baca `);
                    } else {
                      return [
                        createTextVNode(" Baca ")
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                _push2(`</div></div>`);
              } else {
                return [
                  createVNode("div", { class: "relative h-48 overflow-hidden bg-elevated rounded-lg" }, [
                    article.banner_image || article.cover_image ? (openBlock(), createBlock("img", {
                      key: 0,
                      src: article.banner_image || article.cover_image || "",
                      alt: article.title,
                      class: "h-full w-full object-cover transition duration-500 group-hover:scale-105",
                      loading: "lazy"
                    }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                      key: 1,
                      class: "grid h-full w-full place-items-center"
                    }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-image-off",
                        class: "size-8 text-muted"
                      })
                    ])),
                    createVNode("div", { class: "absolute left-3 top-3 flex items-center gap-2" }, [
                      createVNode(_component_UBadge, {
                        color: "neutral",
                        variant: "solid",
                        size: "xs",
                        class: "rounded-full"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(toDisplayString(formatDate(article.published_at)), 1)
                        ]),
                        _: 2
                      }, 1024),
                      createVNode(_component_UBadge, {
                        color: "primary",
                        variant: "soft",
                        size: "xs",
                        class: "rounded-full"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(toDisplayString(article.read_time_minutes) + " menit ", 1)
                        ]),
                        _: 2
                      }, 1024)
                    ])
                  ]),
                  createVNode("div", { class: "space-y-4 p-4" }, [
                    createVNode("div", null, [
                      createVNode("h2", { class: "line-clamp-2 text-lg font-semibold leading-snug text-highlighted" }, toDisplayString(article.title), 1),
                      createVNode("p", { class: "mt-2 line-clamp-3 text-sm leading-relaxed text-muted" }, toDisplayString(article.excerpt), 1)
                    ]),
                    article.tags.length > 0 ? (openBlock(), createBlock("div", {
                      key: 0,
                      class: "flex flex-wrap gap-1.5"
                    }, [
                      (openBlock(true), createBlock(Fragment, null, renderList(article.tags.slice(0, 3), (tag) => {
                        return openBlock(), createBlock(_component_UBadge, {
                          key: `${article.id}-${tag}`,
                          color: "primary",
                          variant: "subtle",
                          size: "xs",
                          class: "rounded-full"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(tag), 1)
                          ]),
                          _: 2
                        }, 1024);
                      }), 128))
                    ])) : createCommentVNode("", true),
                    createVNode("div", { class: "flex items-center justify-between gap-2" }, [
                      createVNode("span", { class: "text-xs text-muted" }, " Diperbarui " + toDisplayString(article.published_label ?? formatDate(article.published_at)), 1),
                      createVNode(_component_UButton, {
                        to: article.url,
                        size: "sm",
                        color: "primary",
                        variant: "outline",
                        "trailing-icon": "i-lucide-arrow-right",
                        loading: props.isApplying
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" Baca ")
                        ]),
                        _: 1
                      }, 8, ["to", "loading"])
                    ])
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
        });
        _push(`<!--]--></div>`);
      }
      _push(`</div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleCardGrid.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "ArticlePagination",
  __ssrInlineRender: true,
  props: {
    page: {},
    total: {},
    perPage: {},
    from: {},
    to: {}
  },
  emits: ["pageChange"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$6;
      const _component_UPagination = _sfc_main$e;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl border border-default/70" }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><p class="text-sm text-muted"${_scopeId}> Menampilkan <span class="font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(props.from ?? 0)} - ${ssrInterpolate(props.to ?? 0)}</span> dari <span class="font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(props.total)}</span> artikel. </p>`);
            _push2(ssrRenderComponent(_component_UPagination, {
              page: props.page,
              total: props.total,
              "items-per-page": props.perPage,
              "show-edges": "",
              "onUpdate:page": (value) => emit("pageChange", value)
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("p", { class: "text-sm text-muted" }, [
                  createTextVNode(" Menampilkan "),
                  createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(props.from ?? 0) + " - " + toDisplayString(props.to ?? 0), 1),
                  createTextVNode(" dari "),
                  createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(props.total), 1),
                  createTextVNode(" artikel. ")
                ]),
                createVNode(_component_UPagination, {
                  page: props.page,
                  total: props.total,
                  "items-per-page": props.perPage,
                  "show-edges": "",
                  "onUpdate:page": (value) => emit("pageChange", value)
                }, null, 8, ["page", "total", "items-per-page", "onUpdate:page"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticlePagination.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$f },
  __name: "Index",
  __ssrInlineRender: true,
  props: {
    seo: {},
    articles: {},
    filters: {},
    availableTags: {},
    stats: {}
  },
  setup(__props) {
    const props = __props;
    const {
      searchQuery,
      selectedTag,
      sortValue,
      isFilterDrawerOpen,
      isApplying,
      articles,
      pagination,
      stats,
      sortItems,
      tagItems,
      hasActiveFilters,
      selectedSortLabel,
      selectedTagLabel,
      applyCurrentFilters,
      onSearchQueryChange,
      onTagChange,
      onSortChange,
      onPageChange,
      resetFilters,
      openFilterDrawer,
      setFilterDrawerOpen
    } = useArticleCatalog(props);
    const structuredDataScripts = computed(() => {
      const payload = props.seo.structured_data ?? [];
      return payload.map((item) => JSON.stringify(item));
    });
    function handleSortChange(value) {
      onSortChange(value);
    }
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_sfc_main$g, {
        title: props.seo.title,
        description: props.seo.description,
        canonical: props.seo.canonical,
        robots: props.seo.robots,
        image: props.seo.image ?? void 0
      }, null, _parent));
      _push(ssrRenderComponent(unref(Head), null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<!--[-->`);
            ssrRenderList(structuredDataScripts.value, (script, index) => {
              ssrRenderVNode(_push2, createVNode(resolveDynamicComponent("script"), {
                key: `article-index-ld-${index}`,
                type: "application/ld+json"
              }, null), _parent2, _scopeId);
            });
            _push2(`<!--]-->`);
          } else {
            return [
              (openBlock(true), createBlock(Fragment, null, renderList(structuredDataScripts.value, (script, index) => {
                return openBlock(), createBlock(resolveDynamicComponent("script"), {
                  key: `article-index-ld-${index}`,
                  type: "application/ld+json",
                  innerHTML: script
                }, null, 8, ["innerHTML"]);
              }), 128))
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="min-h-screen bg-gray-50/60 py-8 transition-colors duration-300 dark:bg-gray-950"><div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-4 sm:px-6 lg:px-8">`);
      _push(ssrRenderComponent(_sfc_main$5, {
        stats: unref(stats),
        "has-active-filters": unref(hasActiveFilters)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$4, {
        "search-query": unref(searchQuery),
        "selected-tag": unref(selectedTag),
        "sort-value": unref(sortValue),
        "tag-items": unref(tagItems),
        "sort-items": unref(sortItems),
        "selected-sort-label": unref(selectedSortLabel),
        "is-applying": unref(isApplying),
        "has-active-filters": unref(hasActiveFilters),
        "onUpdate:searchQuery": unref(onSearchQueryChange),
        "onUpdate:selectedTag": unref(onTagChange),
        "onUpdate:sortValue": handleSortChange,
        onApply: unref(applyCurrentFilters),
        onReset: unref(resetFilters),
        onOpenMobile: unref(openFilterDrawer)
      }, null, _parent));
      _push(`<div class="flex flex-wrap items-center justify-between gap-2 text-sm text-muted"><p> Menampilkan <span class="font-semibold text-highlighted">${ssrInterpolate(unref(pagination).from ?? 0)} - ${ssrInterpolate(unref(pagination).to ?? 0)}</span> dari <span class="font-semibold text-highlighted">${ssrInterpolate(unref(pagination).total)}</span> artikel. </p><p> Tag: <span class="font-medium text-highlighted">${ssrInterpolate(unref(selectedTagLabel))}</span> · Sort: <span class="font-medium text-highlighted">${ssrInterpolate(unref(selectedSortLabel))}</span></p></div>`);
      _push(ssrRenderComponent(_sfc_main$2, {
        articles: unref(articles),
        "is-applying": unref(isApplying)
      }, null, _parent));
      if (unref(pagination).last_page > 1) {
        _push(ssrRenderComponent(_sfc_main$1, {
          page: unref(pagination).current_page,
          total: unref(pagination).total,
          "per-page": unref(pagination).per_page,
          from: unref(pagination).from,
          to: unref(pagination).to,
          onPageChange: unref(onPageChange)
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
      _push(ssrRenderComponent(_sfc_main$3, {
        open: unref(isFilterDrawerOpen),
        "search-query": unref(searchQuery),
        "selected-tag": unref(selectedTag),
        "sort-value": unref(sortValue),
        "tag-items": unref(tagItems),
        "sort-items": unref(sortItems),
        "is-applying": unref(isApplying),
        "has-active-filters": unref(hasActiveFilters),
        "onUpdate:open": unref(setFilterDrawerOpen),
        "onUpdate:searchQuery": unref(onSearchQueryChange),
        "onUpdate:selectedTag": unref(onTagChange),
        "onUpdate:sortValue": handleSortChange,
        onApply: unref(applyCurrentFilters),
        onReset: unref(resetFilters)
      }, null, _parent));
      _push(`</div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Article/Index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
