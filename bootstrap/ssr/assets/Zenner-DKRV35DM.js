import { _ as _sfc_main$9 } from "./Card-CvchAxCK.js";
import { ref, computed, defineComponent, mergeProps, withCtx, createTextVNode, toDisplayString, useSSRContext, createVNode, openBlock, createBlock, createCommentVNode, unref } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList } from "vue/server-renderer";
import { _ as _sfc_main$5 } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$4 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$8 } from "./SelectMenu-D4PFVS1f.js";
import { _ as _sfc_main$7 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$6 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$a } from "./Button-DLZCZWnW.js";
import "reka-ui";
import "@inertiajs/vue3";
import "hookable";
import "@vueuse/core";
import "defu";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "./usePortal-EQErrF6h.js";
import "ufo";
function useDashboardZenner(options) {
  const selectedCategory = ref("all");
  const searchQuery = ref("");
  const categoryItems = computed(() => {
    const base = [{ label: "Semua kategori", value: "all" }];
    const dynamic = options.categories.value.map((category) => ({
      label: category.name,
      value: category.slug
    }));
    return [...base, ...dynamic];
  });
  const filteredContents = computed(() => {
    const keyword = searchQuery.value.trim().toLowerCase();
    return options.contents.value.filter((content) => {
      const matchesCategory = selectedCategory.value === "all" || String(content.category_slug ?? "") === selectedCategory.value;
      if (!matchesCategory) {
        return false;
      }
      if (keyword === "") {
        return true;
      }
      const haystack = [
        content.title,
        content.excerpt,
        content.category_name ?? "",
        content.slug
      ].join(" ").toLowerCase();
      return haystack.includes(keyword);
    });
  });
  const totalContents = computed(() => options.contents.value.length);
  const totalCategories = computed(() => options.categories.value.length);
  function formatDate(value) {
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
      year: "numeric"
    }).format(date);
  }
  function normalizeFileUrl(file) {
    if (!file) {
      return null;
    }
    if (file.startsWith("http://") || file.startsWith("https://") || file.startsWith("/media/public/")) {
      return file;
    }
    if (file.startsWith("/")) {
      if (file.startsWith("/storage/")) {
        return `/media/public/${file.slice("/storage/".length)}`;
      }
      if (file.startsWith("/public/")) {
        return `/media/public/${file.slice("/public/".length)}`;
      }
      return file;
    }
    const normalized = file.replace(/^\/+/, "");
    if (normalized.startsWith("media/public/")) {
      return `/${normalized}`;
    }
    if (normalized.startsWith("public/storage/")) {
      return `/media/public/${normalized.slice("public/storage/".length)}`;
    }
    if (normalized.startsWith("storage/")) {
      return `/media/public/${normalized.slice("storage/".length)}`;
    }
    if (normalized.startsWith("public/")) {
      return `/media/public/${normalized.slice("public/".length)}`;
    }
    return `/media/public/${normalized}`;
  }
  return {
    selectedCategory,
    searchQuery,
    categoryItems,
    filteredContents,
    totalContents,
    totalCategories,
    formatDate,
    normalizeFileUrl
  };
}
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "ZennerHeaderStats",
  __ssrInlineRender: true,
  props: {
    totalContents: {},
    totalCategories: {},
    shownCount: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$4;
      const _component_UBadge = _sfc_main$5;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col gap-3 md:flex-row md:items-start md:justify-between" }, _attrs))}><div class="flex items-center gap-3">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-sparkles",
        class: "size-5 text-gray-500 dark:text-gray-300"
      }, null, _parent));
      _push(`<div><p class="text-base font-semibold text-gray-900 dark:text-white">Zenner Content Center</p><p class="text-sm text-gray-500 dark:text-gray-400">${ssrInterpolate(__props.totalContents)} konten dari ${ssrInterpolate(__props.totalCategories)} kategori </p></div></div><div class="flex flex-wrap items-center gap-2">`);
      _push(ssrRenderComponent(_component_UBadge, {
        color: "primary",
        variant: "soft",
        class: "rounded-full"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(__props.shownCount)} ditampilkan `);
          } else {
            return [
              createTextVNode(toDisplayString(__props.shownCount) + " ditampilkan ", 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/zenner/ZennerHeaderStats.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "ZennerFilters",
  __ssrInlineRender: true,
  props: {
    search: {},
    selectedCategory: {},
    categoryItems: {}
  },
  emits: ["update:search", "update:selectedCategory"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function onSearchUpdate(value) {
      emit("update:search", String(value ?? ""));
    }
    function onCategoryUpdate(value) {
      emit("update:selectedCategory", value);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UFormField = _sfc_main$6;
      const _component_UInput = _sfc_main$7;
      const _component_USelectMenu = _sfc_main$8;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col gap-3 md:flex-row md:items-end" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UFormField, {
        label: "Cari konten",
        class: "w-full md:flex-1"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UInput, {
              "model-value": props.search,
              placeholder: "Cari judul, kategori, atau ringkasan...",
              icon: "i-lucide-search",
              class: "w-full",
              "onUpdate:modelValue": onSearchUpdate
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UInput, {
                "model-value": props.search,
                placeholder: "Cari judul, kategori, atau ringkasan...",
                icon: "i-lucide-search",
                class: "w-full",
                "onUpdate:modelValue": onSearchUpdate
              }, null, 8, ["model-value"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UFormField, {
        label: "Kategori",
        class: "w-full md:w-80"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_USelectMenu, {
              "model-value": props.selectedCategory,
              items: props.categoryItems,
              "value-key": "value",
              "label-key": "label",
              class: "w-full",
              "onUpdate:modelValue": onCategoryUpdate
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_USelectMenu, {
                "model-value": props.selectedCategory,
                items: props.categoryItems,
                "value-key": "value",
                "label-key": "label",
                class: "w-full",
                "onUpdate:modelValue": onCategoryUpdate
              }, null, 8, ["model-value", "items"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/zenner/ZennerFilters.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "ZennerContentList",
  __ssrInlineRender: true,
  props: {
    contents: {},
    formatDate: { type: Function },
    normalizeFileUrl: { type: Function }
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$9;
      const _component_UIcon = _sfc_main$4;
      const _component_UBadge = _sfc_main$5;
      const _component_UButton = _sfc_main$a;
      if (__props.contents.length === 0) {
        _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<div class="py-10 text-center text-gray-500 dark:text-gray-400"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-file-search",
                class: "mx-auto size-10 opacity-40"
              }, null, _parent2, _scopeId));
              _push2(`<p class="mt-3 text-sm"${_scopeId}>Konten belum tersedia untuk filter ini.</p></div>`);
            } else {
              return [
                createVNode("div", { class: "py-10 text-center text-gray-500 dark:text-gray-400" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-file-search",
                    class: "mx-auto size-10 opacity-40"
                  }),
                  createVNode("p", { class: "mt-3 text-sm" }, "Konten belum tersedia untuk filter ini.")
                ])
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid gap-3" }, _attrs))}><!--[-->`);
        ssrRenderList(__props.contents, (content) => {
          _push(ssrRenderComponent(_component_UCard, {
            key: content.id,
            class: "rounded-xl"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="space-y-3"${_scopeId}><div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"${_scopeId}><div${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(content.title)}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(content.excerpt)}</p></div><div class="flex flex-wrap gap-2"${_scopeId}>`);
                if (content.category_name) {
                  _push2(ssrRenderComponent(_component_UBadge, {
                    color: "neutral",
                    variant: "soft",
                    class: "rounded-full"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(`${ssrInterpolate(content.category_name)}`);
                      } else {
                        return [
                          createTextVNode(toDisplayString(content.category_name), 1)
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: "neutral",
                  variant: "soft",
                  class: "rounded-full"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(content.status_label ?? "Unknown")}`);
                    } else {
                      return [
                        createTextVNode(toDisplayString(content.status_label ?? "Unknown"), 1)
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                _push2(`</div></div><div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400"${_scopeId}><p${_scopeId}>Diperbarui: ${ssrInterpolate(__props.formatDate(content.updated_at ?? content.created_at))}</p><div class="flex gap-2"${_scopeId}>`);
                if (content.vlink) {
                  _push2(ssrRenderComponent(_component_UButton, {
                    to: content.vlink,
                    target: "_blank",
                    size: "xs",
                    color: "primary",
                    variant: "outline",
                    icon: "i-lucide-play-circle",
                    class: "rounded-lg"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` Video `);
                      } else {
                        return [
                          createTextVNode(" Video ")
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                if (__props.normalizeFileUrl(content.file)) {
                  _push2(ssrRenderComponent(_component_UButton, {
                    to: __props.normalizeFileUrl(content.file) ?? void 0,
                    target: "_blank",
                    size: "xs",
                    color: "neutral",
                    variant: "outline",
                    icon: "i-lucide-paperclip",
                    class: "rounded-lg"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` File `);
                      } else {
                        return [
                          createTextVNode(" File ")
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div></div></div>`);
              } else {
                return [
                  createVNode("div", { class: "space-y-3" }, [
                    createVNode("div", { class: "flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between" }, [
                      createVNode("div", null, [
                        createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(content.title), 1),
                        createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(content.excerpt), 1)
                      ]),
                      createVNode("div", { class: "flex flex-wrap gap-2" }, [
                        content.category_name ? (openBlock(), createBlock(_component_UBadge, {
                          key: 0,
                          color: "neutral",
                          variant: "soft",
                          class: "rounded-full"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(content.category_name), 1)
                          ]),
                          _: 2
                        }, 1024)) : createCommentVNode("", true),
                        createVNode(_component_UBadge, {
                          color: "neutral",
                          variant: "soft",
                          class: "rounded-full"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(content.status_label ?? "Unknown"), 1)
                          ]),
                          _: 2
                        }, 1024)
                      ])
                    ]),
                    createVNode("div", { class: "flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400" }, [
                      createVNode("p", null, "Diperbarui: " + toDisplayString(__props.formatDate(content.updated_at ?? content.created_at)), 1),
                      createVNode("div", { class: "flex gap-2" }, [
                        content.vlink ? (openBlock(), createBlock(_component_UButton, {
                          key: 0,
                          to: content.vlink,
                          target: "_blank",
                          size: "xs",
                          color: "primary",
                          variant: "outline",
                          icon: "i-lucide-play-circle",
                          class: "rounded-lg"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Video ")
                          ]),
                          _: 1
                        }, 8, ["to"])) : createCommentVNode("", true),
                        __props.normalizeFileUrl(content.file) ? (openBlock(), createBlock(_component_UButton, {
                          key: 1,
                          to: __props.normalizeFileUrl(content.file) ?? void 0,
                          target: "_blank",
                          size: "xs",
                          color: "neutral",
                          variant: "outline",
                          icon: "i-lucide-paperclip",
                          class: "rounded-lg"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" File ")
                          ]),
                          _: 1
                        }, 8, ["to"])) : createCommentVNode("", true)
                      ])
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
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/zenner/ZennerContentList.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Zenner",
  __ssrInlineRender: true,
  props: {
    categories: { default: () => [] },
    contents: { default: () => [] }
  },
  setup(__props) {
    const props = __props;
    const {
      selectedCategory,
      searchQuery,
      categoryItems,
      filteredContents,
      totalContents,
      totalCategories,
      formatDate,
      normalizeFileUrl
    } = useDashboardZenner({
      categories: computed(() => props.categories),
      contents: computed(() => props.contents)
    });
    function onSearchChange(value) {
      searchQuery.value = value;
    }
    function onCategoryChange(value) {
      selectedCategory.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$9;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$3, {
              "total-contents": unref(totalContents),
              "total-categories": unref(totalCategories),
              "shown-count": unref(filteredContents).length
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$3, {
                "total-contents": unref(totalContents),
                "total-categories": unref(totalCategories),
                "shown-count": unref(filteredContents).length
              }, null, 8, ["total-contents", "total-categories", "shown-count"])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_sfc_main$2, {
              search: unref(searchQuery),
              "selected-category": unref(selectedCategory),
              "category-items": unref(categoryItems),
              "onUpdate:search": onSearchChange,
              "onUpdate:selectedCategory": onCategoryChange
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_sfc_main$1, {
              contents: unref(filteredContents),
              "format-date": unref(formatDate),
              "normalize-file-url": unref(normalizeFileUrl)
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode(_sfc_main$2, {
                  search: unref(searchQuery),
                  "selected-category": unref(selectedCategory),
                  "category-items": unref(categoryItems),
                  "onUpdate:search": onSearchChange,
                  "onUpdate:selectedCategory": onCategoryChange
                }, null, 8, ["search", "selected-category", "category-items"]),
                createVNode(_sfc_main$1, {
                  contents: unref(filteredContents),
                  "format-date": unref(formatDate),
                  "normalize-file-url": unref(normalizeFileUrl)
                }, null, 8, ["contents", "format-date", "normalize-file-url"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Zenner.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
