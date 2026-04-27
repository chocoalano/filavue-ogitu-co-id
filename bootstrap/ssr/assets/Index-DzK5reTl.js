import { defineComponent, computed, mergeProps, withCtx, createVNode, toDisplayString, createTextVNode, unref, openBlock, createBlock, createCommentVNode, useSSRContext, useModel, mergeModels, useSlots, useId, Fragment, renderSlot, renderList, useTemplateRef, toRef, watch, ref, nextTick, isRef } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderClass, ssrRenderSlot, ssrRenderList, ssrRenderStyle, ssrRenderAttr } from "vue/server-renderer";
import { router, Head } from "@inertiajs/vue3";
import { d as _sfc_main$f, b as _export_sfc, e as _sfc_main$i, _ as _sfc_main$m, a as _sfc_main$o } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$b } from "./Separator-CuyFd3xv.js";
import { _ as _sfc_main$e, e as _sfc_main$h, u as useFormField } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$a, t as tv, u as useAppConfig, a as useLocale } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$d } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$c } from "./Breadcrumb-EXGzoFVp.js";
import { _ as _sfc_main$9 } from "./Card-CvchAxCK.js";
import { u as useStoreData } from "./useStoreData-DrTMI0On.js";
import { _ as _sfc_main$g } from "./Input-DFvIE7JC.js";
import { f as formatCurrency } from "./useProductDetail-C2nZxJqq.js";
import { _ as _sfc_main$j } from "./SelectMenu-D4PFVS1f.js";
import { useForwardProps, Primitive, SwitchRoot, SwitchThumb, Label, useForwardPropsEmits, SliderRoot, SliderTrack, SliderRange, SliderThumb } from "reka-ui";
import { reactivePick } from "@vueuse/core";
import { defu } from "defu";
import { useVirtualizer } from "@tanstack/vue-virtual";
import { _ as _sfc_main$l } from "./Empty-20F1kDsa.js";
import { _ as _sfc_main$k } from "./Pagination-uWPH5vE_.js";
import { P as ProductCard } from "./ProductCard-CYhvI4HJ.js";
import { _ as _sfc_main$n } from "./Checkbox-PZ4-MUog.js";
import { debounce } from "lodash-es";
import "./useToast-CTuSIf9f.js";
import "./usePortal-EQErrF6h.js";
import "reka-ui/namespaced";
import "vaul-vue";
import "ufo";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
const _sfc_main$8 = /* @__PURE__ */ defineComponent({
  __name: "ShopPageHeader",
  __ssrInlineRender: true,
  props: {
    currentFilters: {},
    activeCategoryLabel: {},
    totalProducts: {},
    categoriesCount: {},
    hasActiveFilters: { type: Boolean },
    activeFilterCount: {}
  },
  setup(__props) {
    const props = __props;
    const { isLoggedIn } = useStoreData();
    const breadcrumbItems = computed(() => [
      { label: "Home", icon: "i-lucide-home", to: "/" },
      { label: "Katalog", to: "/shop" },
      ...props.currentFilters.category ? [{ label: props.activeCategoryLabel }] : []
    ]);
    const categoryLabel = computed(
      () => props.currentFilters.category ? props.activeCategoryLabel : "Semua Kategori"
    );
    const menuItems = computed(() => [
      [
        { label: "Daftar", icon: "i-lucide-user-plus", to: "/register" },
        { label: "Masuk", icon: "i-lucide-log-in", to: "/login" }
      ]
    ]);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$9;
      const _component_UBreadcrumb = _sfc_main$c;
      const _component_UBadge = _sfc_main$d;
      const _component_UIcon = _sfc_main$a;
      const _component_UButton = _sfc_main$e;
      const _component_UDropdownMenu = _sfc_main$f;
      const _component_USeparator = _sfc_main$b;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "mx-auto max-w-screen-2xl px-4 sm:px-2 lg:px-8 py-6 sm:py-4" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl overflow-hidden" }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3"${_scopeId}><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBreadcrumb, { items: breadcrumbItems.value }, null, _parent2, _scopeId));
            _push2(`<div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "primary",
              variant: "soft",
              size: "sm"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-sparkles",
                    class: "mr-1 size-3.5"
                  }, null, _parent3, _scopeId2));
                  _push3(` Premium `);
                } else {
                  return [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-sparkles",
                      class: "mr-1 size-3.5"
                    }),
                    createTextVNode(" Premium ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "neutral",
              variant: "subtle",
              size: "sm"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-tag",
                    class: "mr-1 size-3.5"
                  }, null, _parent3, _scopeId2));
                  _push3(` ${ssrInterpolate(categoryLabel.value)}`);
                } else {
                  return [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-tag",
                      class: "mr-1 size-3.5"
                    }),
                    createTextVNode(" " + toDisplayString(categoryLabel.value), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (__props.hasActiveFilters) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "neutral",
                variant: "subtle",
                size: "sm"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UIcon, {
                      name: "i-lucide-filter",
                      class: "mr-1 size-3.5"
                    }, null, _parent3, _scopeId2));
                    _push3(` ${ssrInterpolate(__props.activeFilterCount)} filter `);
                  } else {
                    return [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-filter",
                        class: "mr-1 size-3.5"
                      }),
                      createTextVNode(" " + toDisplayString(__props.activeFilterCount) + " filter ", 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            if (!unref(isLoggedIn)) {
              _push2(`<div class="hidden sm:flex items-center gap-2 ml-1"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UButton, {
                to: "/register",
                size: "sm",
                color: "primary",
                variant: "soft",
                icon: "i-lucide-user-plus"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Daftar `);
                  } else {
                    return [
                      createTextVNode(" Daftar ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UButton, {
                to: "/login",
                size: "sm",
                color: "neutral",
                variant: "outline",
                icon: "i-lucide-log-in"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Masuk `);
                  } else {
                    return [
                      createTextVNode(" Masuk ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            if (!unref(isLoggedIn)) {
              _push2(ssrRenderComponent(_component_UDropdownMenu, {
                items: menuItems.value,
                class: "sm:hidden"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UButton, {
                      size: "sm",
                      color: "neutral",
                      variant: "outline",
                      icon: "i-lucide-more-horizontal"
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(` Menu `);
                        } else {
                          return [
                            createTextVNode(" Menu ")
                          ];
                        }
                      }),
                      _: 1
                    }, _parent3, _scopeId2));
                  } else {
                    return [
                      createVNode(_component_UButton, {
                        size: "sm",
                        color: "neutral",
                        variant: "outline",
                        icon: "i-lucide-more-horizontal"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" Menu ")
                        ]),
                        _: 1
                      })
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3" }, [
                createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" }, [
                  createVNode(_component_UBreadcrumb, { items: breadcrumbItems.value }, null, 8, ["items"]),
                  createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                    createVNode(_component_UBadge, {
                      color: "primary",
                      variant: "soft",
                      size: "sm"
                    }, {
                      default: withCtx(() => [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-sparkles",
                          class: "mr-1 size-3.5"
                        }),
                        createTextVNode(" Premium ")
                      ]),
                      _: 1
                    }),
                    createVNode(_component_UBadge, {
                      color: "neutral",
                      variant: "subtle",
                      size: "sm"
                    }, {
                      default: withCtx(() => [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-tag",
                          class: "mr-1 size-3.5"
                        }),
                        createTextVNode(" " + toDisplayString(categoryLabel.value), 1)
                      ]),
                      _: 1
                    }),
                    __props.hasActiveFilters ? (openBlock(), createBlock(_component_UBadge, {
                      key: 0,
                      color: "neutral",
                      variant: "subtle",
                      size: "sm"
                    }, {
                      default: withCtx(() => [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-filter",
                          class: "mr-1 size-3.5"
                        }),
                        createTextVNode(" " + toDisplayString(__props.activeFilterCount) + " filter ", 1)
                      ]),
                      _: 1
                    })) : createCommentVNode("", true),
                    !unref(isLoggedIn) ? (openBlock(), createBlock("div", {
                      key: 1,
                      class: "hidden sm:flex items-center gap-2 ml-1"
                    }, [
                      createVNode(_component_UButton, {
                        to: "/register",
                        size: "sm",
                        color: "primary",
                        variant: "soft",
                        icon: "i-lucide-user-plus"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" Daftar ")
                        ]),
                        _: 1
                      }),
                      createVNode(_component_UButton, {
                        to: "/login",
                        size: "sm",
                        color: "neutral",
                        variant: "outline",
                        icon: "i-lucide-log-in"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" Masuk ")
                        ]),
                        _: 1
                      })
                    ])) : createCommentVNode("", true),
                    !unref(isLoggedIn) ? (openBlock(), createBlock(_component_UDropdownMenu, {
                      key: 2,
                      items: menuItems.value,
                      class: "sm:hidden"
                    }, {
                      default: withCtx(() => [
                        createVNode(_component_UButton, {
                          size: "sm",
                          color: "neutral",
                          variant: "outline",
                          icon: "i-lucide-more-horizontal"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(" Menu ")
                          ]),
                          _: 1
                        })
                      ]),
                      _: 1
                    }, 8, ["items"])) : createCommentVNode("", true)
                  ])
                ])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div${_scopeId}><div class="flex flex-col gap-2"${_scopeId}><div class="flex items-center gap-2"${_scopeId}><div class="flex size-9 items-center justify-center rounded-xl bg-primary/10"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-store",
              class: "size-5 text-primary"
            }, null, _parent2, _scopeId));
            _push2(`</div><h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-highlighted"${_scopeId}> Katalog Produk </h1></div><p class="text-sm text-muted max-w-2xl"${_scopeId}> Jelajahi produk premium dengan filter cepat, rentang harga fleksibel, dan sorting yang rapi. </p></div>`);
            _push2(ssrRenderComponent(_component_USeparator, { class: "my-5" }, null, _parent2, _scopeId));
            _push2(`<div class="grid gap-3 sm:grid-cols-3"${_scopeId}><div class="flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3"${_scopeId}><div class="flex size-9 items-center justify-center rounded-lg bg-primary/10"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-package",
              class: "size-5 text-primary"
            }, null, _parent2, _scopeId));
            _push2(`</div><div${_scopeId}><p class="text-xs text-muted"${_scopeId}>Produk</p><p class="text-lg font-bold tabular-nums text-highlighted"${_scopeId}>${ssrInterpolate(__props.totalProducts)}</p></div></div><div class="flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3"${_scopeId}><div class="flex size-9 items-center justify-center rounded-lg bg-elevated"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-layers",
              class: "size-5 text-muted"
            }, null, _parent2, _scopeId));
            _push2(`</div><div${_scopeId}><p class="text-xs text-muted"${_scopeId}>Kategori</p><p class="text-lg font-bold tabular-nums text-highlighted"${_scopeId}>${ssrInterpolate(__props.categoriesCount)}</p></div></div><div class="flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3"${_scopeId}><div class="flex size-9 items-center justify-center rounded-lg bg-elevated"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-sliders-horizontal",
              class: "size-5 text-muted"
            }, null, _parent2, _scopeId));
            _push2(`</div><div${_scopeId}><p class="text-xs text-muted"${_scopeId}>Filter aktif</p><p class="text-lg font-bold tabular-nums text-highlighted"${_scopeId}>${ssrInterpolate(__props.hasActiveFilters ? __props.activeFilterCount : 0)}</p></div></div></div></div>`);
          } else {
            return [
              createVNode("div", null, [
                createVNode("div", { class: "flex flex-col gap-2" }, [
                  createVNode("div", { class: "flex items-center gap-2" }, [
                    createVNode("div", { class: "flex size-9 items-center justify-center rounded-xl bg-primary/10" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-store",
                        class: "size-5 text-primary"
                      })
                    ]),
                    createVNode("h1", { class: "text-2xl sm:text-3xl font-bold tracking-tight text-highlighted" }, " Katalog Produk ")
                  ]),
                  createVNode("p", { class: "text-sm text-muted max-w-2xl" }, " Jelajahi produk premium dengan filter cepat, rentang harga fleksibel, dan sorting yang rapi. ")
                ]),
                createVNode(_component_USeparator, { class: "my-5" }),
                createVNode("div", { class: "grid gap-3 sm:grid-cols-3" }, [
                  createVNode("div", { class: "flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3" }, [
                    createVNode("div", { class: "flex size-9 items-center justify-center rounded-lg bg-primary/10" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-package",
                        class: "size-5 text-primary"
                      })
                    ]),
                    createVNode("div", null, [
                      createVNode("p", { class: "text-xs text-muted" }, "Produk"),
                      createVNode("p", { class: "text-lg font-bold tabular-nums text-highlighted" }, toDisplayString(__props.totalProducts), 1)
                    ])
                  ]),
                  createVNode("div", { class: "flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3" }, [
                    createVNode("div", { class: "flex size-9 items-center justify-center rounded-lg bg-elevated" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-layers",
                        class: "size-5 text-muted"
                      })
                    ]),
                    createVNode("div", null, [
                      createVNode("p", { class: "text-xs text-muted" }, "Kategori"),
                      createVNode("p", { class: "text-lg font-bold tabular-nums text-highlighted" }, toDisplayString(__props.categoriesCount), 1)
                    ])
                  ]),
                  createVNode("div", { class: "flex items-center gap-3 rounded-xl border border-default bg-elevated/10 px-4 py-3" }, [
                    createVNode("div", { class: "flex size-9 items-center justify-center rounded-lg bg-elevated" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-sliders-horizontal",
                        class: "size-5 text-muted"
                      })
                    ]),
                    createVNode("div", null, [
                      createVNode("p", { class: "text-xs text-muted" }, "Filter aktif"),
                      createVNode("p", { class: "text-lg font-bold tabular-nums text-highlighted" }, toDisplayString(__props.hasActiveFilters ? __props.activeFilterCount : 0), 1)
                    ])
                  ])
                ])
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
const _sfc_setup$8 = _sfc_main$8.setup;
_sfc_main$8.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/shop/ShopPageHeader.vue");
  return _sfc_setup$8 ? _sfc_setup$8(props, ctx) : void 0;
};
const _sfc_main$7 = /* @__PURE__ */ defineComponent({
  __name: "ShopToolbar",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    currentFilters: {},
    hasActiveFilters: { type: Boolean },
    activeCategoryLabel: {},
    activeBrandLabel: {},
    currentSortLabel: {},
    priceRange: {},
    filterStats: {},
    sortOptions: {}
  }, {
    "search": { required: true },
    "searchModifiers": {},
    "viewMode": { required: true },
    "viewModeModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["filter", "reset", "openMobileFilters"], ["update:search", "update:viewMode"]),
  setup(__props, { emit: __emit }) {
    const search = useModel(__props, "search");
    const viewMode = useModel(__props, "viewMode");
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UInput = _sfc_main$g;
      const _component_UChip = _sfc_main$h;
      const _component_UButton = _sfc_main$e;
      const _component_UDropdownMenu = _sfc_main$f;
      const _component_USeparator = _sfc_main$b;
      const _component_UBadge = _sfc_main$d;
      const _component_UIcon = _sfc_main$a;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "toolbar sticky top-25 z-1 mb-6 rounded-2xl border border-default bg-white/80 p-3 backdrop-blur-xl dark:bg-gray-900/80" }, _attrs))} data-v-c1b77840><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" data-v-c1b77840><div class="flex items-center gap-2.5" data-v-c1b77840>`);
      _push(ssrRenderComponent(_component_UInput, {
        modelValue: search.value,
        "onUpdate:modelValue": ($event) => search.value = $event,
        icon: "i-lucide-search",
        placeholder: "Cari produk...",
        size: "md",
        class: "w-full sm:w-72 lg:w-80"
      }, null, _parent));
      _push(ssrRenderComponent(_component_UChip, {
        show: __props.hasActiveFilters,
        color: "primary",
        size: "sm"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UButton, {
              icon: "i-lucide-sliders-horizontal",
              color: "primary",
              variant: "soft",
              size: "md",
              class: "shrink-0 lg:hidden",
              onClick: ($event) => emit("openMobileFilters")
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UButton, {
                icon: "i-lucide-sliders-horizontal",
                color: "primary",
                variant: "soft",
                size: "md",
                class: "shrink-0 lg:hidden",
                onClick: ($event) => emit("openMobileFilters")
              }, null, 8, ["onClick"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="flex flex-wrap items-center gap-2" data-v-c1b77840><div class="inline-flex items-center rounded-lg border border-default bg-elevated/50 p-0.5" data-v-c1b77840>`);
      _push(ssrRenderComponent(_component_UButton, {
        icon: "i-lucide-grid-3x3",
        size: "xs",
        color: viewMode.value === "grid" ? "primary" : "neutral",
        variant: viewMode.value === "grid" ? "soft" : "ghost",
        onClick: ($event) => viewMode.value = "grid"
      }, null, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        icon: "i-lucide-list",
        size: "xs",
        color: viewMode.value === "list" ? "primary" : "neutral",
        variant: viewMode.value === "list" ? "soft" : "ghost",
        onClick: ($event) => viewMode.value = "list"
      }, null, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_component_UDropdownMenu, { items: __props.sortOptions }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              "trailing-icon": "i-lucide-chevron-down"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(__props.currentSortLabel)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(__props.currentSortLabel), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UButton, {
                color: "neutral",
                variant: "outline",
                size: "sm",
                "trailing-icon": "i-lucide-chevron-down"
              }, {
                default: withCtx(() => [
                  createTextVNode(toDisplayString(__props.currentSortLabel), 1)
                ]),
                _: 1
              })
            ];
          }
        }),
        _: 1
      }, _parent));
      if (__props.hasActiveFilters) {
        _push(ssrRenderComponent(_component_UButton, {
          color: "neutral",
          variant: "ghost",
          size: "sm",
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
      if (__props.hasActiveFilters) {
        _push(`<div class="mt-2.5" data-v-c1b77840>`);
        _push(ssrRenderComponent(_component_USeparator, { class: "mb-2.5" }, null, _parent));
        _push(`<div class="flex flex-wrap gap-1.5" data-v-c1b77840>`);
        if (__props.currentFilters.search) {
          _push(ssrRenderComponent(_component_UBadge, {
            color: "primary",
            variant: "soft",
            class: "gap-1"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-search",
                  class: "size-3"
                }, null, _parent2, _scopeId));
                _push2(` &quot;${ssrInterpolate(__props.currentFilters.search)}&quot; `);
                _push2(ssrRenderComponent(_component_UButton, {
                  icon: "i-lucide-x",
                  size: "xs",
                  color: "neutral",
                  variant: "ghost",
                  class: "-mr-1! p-0.5!",
                  onClick: ($event) => emit("filter", { search: "" })
                }, null, _parent2, _scopeId));
              } else {
                return [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-search",
                    class: "size-3"
                  }),
                  createTextVNode(' "' + toDisplayString(__props.currentFilters.search) + '" ', 1),
                  createVNode(_component_UButton, {
                    icon: "i-lucide-x",
                    size: "xs",
                    color: "neutral",
                    variant: "ghost",
                    class: "-mr-1! p-0.5!",
                    onClick: ($event) => emit("filter", { search: "" })
                  }, null, 8, ["onClick"])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        if (__props.currentFilters.category) {
          _push(ssrRenderComponent(_component_UBadge, {
            color: "primary",
            variant: "soft",
            class: "gap-1"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-tag",
                  class: "size-3"
                }, null, _parent2, _scopeId));
                _push2(` ${ssrInterpolate(__props.activeCategoryLabel)} `);
                _push2(ssrRenderComponent(_component_UButton, {
                  icon: "i-lucide-x",
                  size: "xs",
                  color: "neutral",
                  variant: "ghost",
                  class: "-mr-1! p-0.5!",
                  onClick: ($event) => emit("filter", { category: void 0 })
                }, null, _parent2, _scopeId));
              } else {
                return [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-tag",
                    class: "size-3"
                  }),
                  createTextVNode(" " + toDisplayString(__props.activeCategoryLabel) + " ", 1),
                  createVNode(_component_UButton, {
                    icon: "i-lucide-x",
                    size: "xs",
                    color: "neutral",
                    variant: "ghost",
                    class: "-mr-1! p-0.5!",
                    onClick: ($event) => emit("filter", { category: void 0 })
                  }, null, 8, ["onClick"])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        if (__props.currentFilters.brand && __props.activeBrandLabel) {
          _push(ssrRenderComponent(_component_UBadge, {
            color: "primary",
            variant: "soft",
            class: "gap-1"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-tags",
                  class: "size-3"
                }, null, _parent2, _scopeId));
                _push2(` ${ssrInterpolate(__props.activeBrandLabel)} `);
                _push2(ssrRenderComponent(_component_UButton, {
                  icon: "i-lucide-x",
                  size: "xs",
                  color: "neutral",
                  variant: "ghost",
                  class: "-mr-1! p-0.5!",
                  onClick: ($event) => emit("filter", { brand: void 0 })
                }, null, _parent2, _scopeId));
              } else {
                return [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-tags",
                    class: "size-3"
                  }),
                  createTextVNode(" " + toDisplayString(__props.activeBrandLabel) + " ", 1),
                  createVNode(_component_UButton, {
                    icon: "i-lucide-x",
                    size: "xs",
                    color: "neutral",
                    variant: "ghost",
                    class: "-mr-1! p-0.5!",
                    onClick: ($event) => emit("filter", { brand: void 0 })
                  }, null, 8, ["onClick"])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        if (__props.currentFilters.in_stock) {
          _push(ssrRenderComponent(_component_UBadge, {
            color: "success",
            variant: "soft",
            class: "gap-1"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-package-check",
                  class: "size-3"
                }, null, _parent2, _scopeId));
                _push2(` Stok tersedia `);
                _push2(ssrRenderComponent(_component_UButton, {
                  icon: "i-lucide-x",
                  size: "xs",
                  color: "neutral",
                  variant: "ghost",
                  class: "-mr-1! p-0.5!",
                  onClick: ($event) => emit("filter", { in_stock: false })
                }, null, _parent2, _scopeId));
              } else {
                return [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-package-check",
                    class: "size-3"
                  }),
                  createTextVNode(" Stok tersedia "),
                  createVNode(_component_UButton, {
                    icon: "i-lucide-x",
                    size: "xs",
                    color: "neutral",
                    variant: "ghost",
                    class: "-mr-1! p-0.5!",
                    onClick: ($event) => emit("filter", { in_stock: false })
                  }, null, 8, ["onClick"])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        if (__props.currentFilters.min_price && Number(__props.currentFilters.min_price) > Number(__props.filterStats.min_price) || __props.currentFilters.max_price && Number(__props.currentFilters.max_price) < Number(__props.filterStats.max_price)) {
          _push(ssrRenderComponent(_component_UBadge, {
            color: "primary",
            variant: "soft",
            class: "gap-1"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-banknote",
                  class: "size-3"
                }, null, _parent2, _scopeId));
                _push2(` ${ssrInterpolate(unref(formatCurrency)(__props.priceRange[0]))} – ${ssrInterpolate(unref(formatCurrency)(__props.priceRange[1]))} `);
                _push2(ssrRenderComponent(_component_UButton, {
                  icon: "i-lucide-x",
                  size: "xs",
                  color: "neutral",
                  variant: "ghost",
                  class: "-mr-1! p-0.5!",
                  onClick: ($event) => emit("filter", { min_price: __props.filterStats.min_price, max_price: __props.filterStats.max_price })
                }, null, _parent2, _scopeId));
              } else {
                return [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-banknote",
                    class: "size-3"
                  }),
                  createTextVNode(" " + toDisplayString(unref(formatCurrency)(__props.priceRange[0])) + " – " + toDisplayString(unref(formatCurrency)(__props.priceRange[1])) + " ", 1),
                  createVNode(_component_UButton, {
                    icon: "i-lucide-x",
                    size: "xs",
                    color: "neutral",
                    variant: "ghost",
                    class: "-mr-1! p-0.5!",
                    onClick: ($event) => emit("filter", { min_price: __props.filterStats.min_price, max_price: __props.filterStats.max_price })
                  }, null, 8, ["onClick"])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        _push(`</div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
    };
  }
});
const _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/shop/ShopToolbar.vue");
  return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
const ShopToolbar = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["__scopeId", "data-v-c1b77840"]]);
const theme$2 = {
  "slots": {
    "root": "relative flex items-start",
    "base": [
      "inline-flex items-center shrink-0 rounded-full border-2 border-transparent focus-visible:outline-2 focus-visible:outline-offset-2 data-[state=unchecked]:bg-accented",
      "transition-[background] duration-200"
    ],
    "container": "flex items-center",
    "thumb": "group pointer-events-none rounded-full bg-default shadow-lg ring-0 transition-transform duration-200 data-[state=unchecked]:translate-x-0 data-[state=unchecked]:rtl:-translate-x-0 flex items-center justify-center",
    "icon": [
      "absolute shrink-0 group-data-[state=unchecked]:text-dimmed opacity-0 size-10/12",
      "transition-[color,opacity] duration-200"
    ],
    "wrapper": "ms-2",
    "label": "block font-medium text-default",
    "description": "text-muted"
  },
  "variants": {
    "color": {
      "primary": {
        "base": "data-[state=checked]:bg-primary focus-visible:outline-primary",
        "icon": "group-data-[state=checked]:text-primary"
      },
      "secondary": {
        "base": "data-[state=checked]:bg-secondary focus-visible:outline-secondary",
        "icon": "group-data-[state=checked]:text-secondary"
      },
      "success": {
        "base": "data-[state=checked]:bg-success focus-visible:outline-success",
        "icon": "group-data-[state=checked]:text-success"
      },
      "info": {
        "base": "data-[state=checked]:bg-info focus-visible:outline-info",
        "icon": "group-data-[state=checked]:text-info"
      },
      "warning": {
        "base": "data-[state=checked]:bg-warning focus-visible:outline-warning",
        "icon": "group-data-[state=checked]:text-warning"
      },
      "error": {
        "base": "data-[state=checked]:bg-error focus-visible:outline-error",
        "icon": "group-data-[state=checked]:text-error"
      },
      "neutral": {
        "base": "data-[state=checked]:bg-inverted focus-visible:outline-inverted",
        "icon": "group-data-[state=checked]:text-highlighted"
      }
    },
    "size": {
      "xs": {
        "base": "w-7",
        "container": "h-4",
        "thumb": "size-3 data-[state=checked]:translate-x-3 data-[state=checked]:rtl:-translate-x-3",
        "wrapper": "text-xs"
      },
      "sm": {
        "base": "w-8",
        "container": "h-4",
        "thumb": "size-3.5 data-[state=checked]:translate-x-3.5 data-[state=checked]:rtl:-translate-x-3.5",
        "wrapper": "text-xs"
      },
      "md": {
        "base": "w-9",
        "container": "h-5",
        "thumb": "size-4 data-[state=checked]:translate-x-4 data-[state=checked]:rtl:-translate-x-4",
        "wrapper": "text-sm"
      },
      "lg": {
        "base": "w-10",
        "container": "h-5",
        "thumb": "size-4.5 data-[state=checked]:translate-x-4.5 data-[state=checked]:rtl:-translate-x-4.5",
        "wrapper": "text-sm"
      },
      "xl": {
        "base": "w-11",
        "container": "h-6",
        "thumb": "size-5 data-[state=checked]:translate-x-5 data-[state=checked]:rtl:-translate-x-5",
        "wrapper": "text-base"
      }
    },
    "checked": {
      "true": {
        "icon": "group-data-[state=checked]:opacity-100"
      }
    },
    "unchecked": {
      "true": {
        "icon": "group-data-[state=unchecked]:opacity-100"
      }
    },
    "loading": {
      "true": {
        "icon": "animate-spin"
      }
    },
    "required": {
      "true": {
        "label": "after:content-['*'] after:ms-0.5 after:text-error"
      }
    },
    "disabled": {
      "true": {
        "root": "opacity-75",
        "base": "cursor-not-allowed",
        "label": "cursor-not-allowed",
        "description": "cursor-not-allowed"
      }
    }
  },
  "defaultVariants": {
    "color": "primary",
    "size": "md"
  }
};
const _sfc_main$6 = /* @__PURE__ */ Object.assign({ inheritAttrs: false }, {
  __name: "Switch",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    as: { type: null, required: false },
    color: { type: null, required: false },
    size: { type: null, required: false },
    loading: { type: Boolean, required: false },
    loadingIcon: { type: null, required: false },
    checkedIcon: { type: null, required: false },
    uncheckedIcon: { type: null, required: false },
    label: { type: String, required: false },
    description: { type: String, required: false },
    class: { type: null, required: false },
    ui: { type: null, required: false },
    disabled: { type: Boolean, required: false },
    id: { type: String, required: false },
    name: { type: String, required: false },
    required: { type: Boolean, required: false },
    value: { type: String, required: false },
    defaultValue: { type: Boolean, required: false }
  }, {
    "modelValue": { type: Boolean, ...{ default: void 0 } },
    "modelModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["change"], ["update:modelValue"]),
  setup(__props, { emit: __emit }) {
    const props = __props;
    const slots = useSlots();
    const emits = __emit;
    const modelValue = useModel(__props, "modelValue", { type: Boolean, ...{ default: void 0 } });
    const appConfig = useAppConfig();
    const rootProps = useForwardProps(reactivePick(props, "required", "value", "defaultValue"));
    const { id: _id, emitFormChange, emitFormInput, size, color, name, disabled, ariaAttrs } = useFormField(props);
    const id = _id.value ?? useId();
    const ui = computed(() => tv({ extend: tv(theme$2), ...appConfig.ui?.switch || {} })({
      size: size.value,
      color: color.value,
      required: props.required,
      loading: props.loading,
      disabled: disabled.value || props.loading
    }));
    function onUpdate(value) {
      const event = new Event("change", { target: { value } });
      emits("change", event);
      emitFormChange();
      emitFormInput();
    }
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Primitive), mergeProps({
        as: __props.as,
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] })
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div data-slot="container" class="${ssrRenderClass(ui.value.container({ class: props.ui?.container }))}"${_scopeId}>`);
            _push2(ssrRenderComponent(unref(SwitchRoot), mergeProps({ id: unref(id) }, { ...unref(rootProps), ..._ctx.$attrs, ...unref(ariaAttrs) }, {
              modelValue: modelValue.value,
              "onUpdate:modelValue": [($event) => modelValue.value = $event, onUpdate],
              name: unref(name),
              disabled: unref(disabled) || __props.loading,
              "data-slot": "base",
              class: ui.value.base({ class: props.ui?.base })
            }), {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(unref(SwitchThumb), {
                    "data-slot": "thumb",
                    class: ui.value.thumb({ class: props.ui?.thumb })
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        if (__props.loading) {
                          _push4(ssrRenderComponent(_sfc_main$a, {
                            name: __props.loadingIcon || unref(appConfig).ui.icons.loading,
                            "data-slot": "icon",
                            class: ui.value.icon({ class: props.ui?.icon, checked: true, unchecked: true })
                          }, null, _parent4, _scopeId3));
                        } else {
                          _push4(`<!--[-->`);
                          if (__props.checkedIcon) {
                            _push4(ssrRenderComponent(_sfc_main$a, {
                              name: __props.checkedIcon,
                              "data-slot": "icon",
                              class: ui.value.icon({ class: props.ui?.icon, checked: true })
                            }, null, _parent4, _scopeId3));
                          } else {
                            _push4(`<!---->`);
                          }
                          if (__props.uncheckedIcon) {
                            _push4(ssrRenderComponent(_sfc_main$a, {
                              name: __props.uncheckedIcon,
                              "data-slot": "icon",
                              class: ui.value.icon({ class: props.ui?.icon, unchecked: true })
                            }, null, _parent4, _scopeId3));
                          } else {
                            _push4(`<!---->`);
                          }
                          _push4(`<!--]-->`);
                        }
                      } else {
                        return [
                          __props.loading ? (openBlock(), createBlock(_sfc_main$a, {
                            key: 0,
                            name: __props.loadingIcon || unref(appConfig).ui.icons.loading,
                            "data-slot": "icon",
                            class: ui.value.icon({ class: props.ui?.icon, checked: true, unchecked: true })
                          }, null, 8, ["name", "class"])) : (openBlock(), createBlock(Fragment, { key: 1 }, [
                            __props.checkedIcon ? (openBlock(), createBlock(_sfc_main$a, {
                              key: 0,
                              name: __props.checkedIcon,
                              "data-slot": "icon",
                              class: ui.value.icon({ class: props.ui?.icon, checked: true })
                            }, null, 8, ["name", "class"])) : createCommentVNode("", true),
                            __props.uncheckedIcon ? (openBlock(), createBlock(_sfc_main$a, {
                              key: 1,
                              name: __props.uncheckedIcon,
                              "data-slot": "icon",
                              class: ui.value.icon({ class: props.ui?.icon, unchecked: true })
                            }, null, 8, ["name", "class"])) : createCommentVNode("", true)
                          ], 64))
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(unref(SwitchThumb), {
                      "data-slot": "thumb",
                      class: ui.value.thumb({ class: props.ui?.thumb })
                    }, {
                      default: withCtx(() => [
                        __props.loading ? (openBlock(), createBlock(_sfc_main$a, {
                          key: 0,
                          name: __props.loadingIcon || unref(appConfig).ui.icons.loading,
                          "data-slot": "icon",
                          class: ui.value.icon({ class: props.ui?.icon, checked: true, unchecked: true })
                        }, null, 8, ["name", "class"])) : (openBlock(), createBlock(Fragment, { key: 1 }, [
                          __props.checkedIcon ? (openBlock(), createBlock(_sfc_main$a, {
                            key: 0,
                            name: __props.checkedIcon,
                            "data-slot": "icon",
                            class: ui.value.icon({ class: props.ui?.icon, checked: true })
                          }, null, 8, ["name", "class"])) : createCommentVNode("", true),
                          __props.uncheckedIcon ? (openBlock(), createBlock(_sfc_main$a, {
                            key: 1,
                            name: __props.uncheckedIcon,
                            "data-slot": "icon",
                            class: ui.value.icon({ class: props.ui?.icon, unchecked: true })
                          }, null, 8, ["name", "class"])) : createCommentVNode("", true)
                        ], 64))
                      ]),
                      _: 1
                    }, 8, ["class"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            if (__props.label || !!slots.label || (__props.description || !!slots.description)) {
              _push2(`<div data-slot="wrapper" class="${ssrRenderClass(ui.value.wrapper({ class: props.ui?.wrapper }))}"${_scopeId}>`);
              if (__props.label || !!slots.label) {
                _push2(ssrRenderComponent(unref(Label), {
                  for: unref(id),
                  "data-slot": "label",
                  class: ui.value.label({ class: props.ui?.label })
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      ssrRenderSlot(_ctx.$slots, "label", { label: __props.label }, () => {
                        _push3(`${ssrInterpolate(__props.label)}`);
                      }, _push3, _parent3, _scopeId2);
                    } else {
                      return [
                        renderSlot(_ctx.$slots, "label", { label: __props.label }, () => [
                          createTextVNode(toDisplayString(__props.label), 1)
                        ])
                      ];
                    }
                  }),
                  _: 3
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              if (__props.description || !!slots.description) {
                _push2(`<p data-slot="description" class="${ssrRenderClass(ui.value.description({ class: props.ui?.description }))}"${_scopeId}>`);
                ssrRenderSlot(_ctx.$slots, "description", { description: __props.description }, () => {
                  _push2(`${ssrInterpolate(__props.description)}`);
                }, _push2, _parent2, _scopeId);
                _push2(`</p>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              createVNode("div", {
                "data-slot": "container",
                class: ui.value.container({ class: props.ui?.container })
              }, [
                createVNode(unref(SwitchRoot), mergeProps({ id: unref(id) }, { ...unref(rootProps), ..._ctx.$attrs, ...unref(ariaAttrs) }, {
                  modelValue: modelValue.value,
                  "onUpdate:modelValue": [($event) => modelValue.value = $event, onUpdate],
                  name: unref(name),
                  disabled: unref(disabled) || __props.loading,
                  "data-slot": "base",
                  class: ui.value.base({ class: props.ui?.base })
                }), {
                  default: withCtx(() => [
                    createVNode(unref(SwitchThumb), {
                      "data-slot": "thumb",
                      class: ui.value.thumb({ class: props.ui?.thumb })
                    }, {
                      default: withCtx(() => [
                        __props.loading ? (openBlock(), createBlock(_sfc_main$a, {
                          key: 0,
                          name: __props.loadingIcon || unref(appConfig).ui.icons.loading,
                          "data-slot": "icon",
                          class: ui.value.icon({ class: props.ui?.icon, checked: true, unchecked: true })
                        }, null, 8, ["name", "class"])) : (openBlock(), createBlock(Fragment, { key: 1 }, [
                          __props.checkedIcon ? (openBlock(), createBlock(_sfc_main$a, {
                            key: 0,
                            name: __props.checkedIcon,
                            "data-slot": "icon",
                            class: ui.value.icon({ class: props.ui?.icon, checked: true })
                          }, null, 8, ["name", "class"])) : createCommentVNode("", true),
                          __props.uncheckedIcon ? (openBlock(), createBlock(_sfc_main$a, {
                            key: 1,
                            name: __props.uncheckedIcon,
                            "data-slot": "icon",
                            class: ui.value.icon({ class: props.ui?.icon, unchecked: true })
                          }, null, 8, ["name", "class"])) : createCommentVNode("", true)
                        ], 64))
                      ]),
                      _: 1
                    }, 8, ["class"])
                  ]),
                  _: 1
                }, 16, ["id", "modelValue", "onUpdate:modelValue", "name", "disabled", "class"])
              ], 2),
              __props.label || !!slots.label || (__props.description || !!slots.description) ? (openBlock(), createBlock("div", {
                key: 0,
                "data-slot": "wrapper",
                class: ui.value.wrapper({ class: props.ui?.wrapper })
              }, [
                __props.label || !!slots.label ? (openBlock(), createBlock(unref(Label), {
                  key: 0,
                  for: unref(id),
                  "data-slot": "label",
                  class: ui.value.label({ class: props.ui?.label })
                }, {
                  default: withCtx(() => [
                    renderSlot(_ctx.$slots, "label", { label: __props.label }, () => [
                      createTextVNode(toDisplayString(__props.label), 1)
                    ])
                  ]),
                  _: 3
                }, 8, ["for", "class"])) : createCommentVNode("", true),
                __props.description || !!slots.description ? (openBlock(), createBlock("p", {
                  key: 1,
                  "data-slot": "description",
                  class: ui.value.description({ class: props.ui?.description })
                }, [
                  renderSlot(_ctx.$slots, "description", { description: __props.description }, () => [
                    createTextVNode(toDisplayString(__props.description), 1)
                  ])
                ], 2)) : createCommentVNode("", true)
              ], 2)) : createCommentVNode("", true)
            ];
          }
        }),
        _: 3
      }, _parent));
    };
  }
});
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/Switch.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const theme$1 = {
  "slots": {
    "root": "relative flex items-center select-none touch-none",
    "track": "relative bg-accented overflow-hidden rounded-full grow",
    "range": "absolute rounded-full",
    "thumb": "rounded-full bg-default ring-2 focus-visible:outline-2 focus-visible:outline-offset-2"
  },
  "variants": {
    "color": {
      "primary": {
        "range": "bg-primary",
        "thumb": "ring-primary focus-visible:outline-primary/50"
      },
      "secondary": {
        "range": "bg-secondary",
        "thumb": "ring-secondary focus-visible:outline-secondary/50"
      },
      "success": {
        "range": "bg-success",
        "thumb": "ring-success focus-visible:outline-success/50"
      },
      "info": {
        "range": "bg-info",
        "thumb": "ring-info focus-visible:outline-info/50"
      },
      "warning": {
        "range": "bg-warning",
        "thumb": "ring-warning focus-visible:outline-warning/50"
      },
      "error": {
        "range": "bg-error",
        "thumb": "ring-error focus-visible:outline-error/50"
      },
      "neutral": {
        "range": "bg-inverted",
        "thumb": "ring-inverted focus-visible:outline-inverted/50"
      }
    },
    "size": {
      "xs": {
        "thumb": "size-3"
      },
      "sm": {
        "thumb": "size-3.5"
      },
      "md": {
        "thumb": "size-4"
      },
      "lg": {
        "thumb": "size-4.5"
      },
      "xl": {
        "thumb": "size-5"
      }
    },
    "orientation": {
      "horizontal": {
        "root": "w-full",
        "range": "h-full"
      },
      "vertical": {
        "root": "flex-col h-full",
        "range": "w-full"
      }
    },
    "disabled": {
      "true": {
        "root": "opacity-75 cursor-not-allowed"
      }
    }
  },
  "compoundVariants": [
    {
      "orientation": "horizontal",
      "size": "xs",
      "class": {
        "track": "h-[6px]"
      }
    },
    {
      "orientation": "horizontal",
      "size": "sm",
      "class": {
        "track": "h-[7px]"
      }
    },
    {
      "orientation": "horizontal",
      "size": "md",
      "class": {
        "track": "h-[8px]"
      }
    },
    {
      "orientation": "horizontal",
      "size": "lg",
      "class": {
        "track": "h-[9px]"
      }
    },
    {
      "orientation": "horizontal",
      "size": "xl",
      "class": {
        "track": "h-[10px]"
      }
    },
    {
      "orientation": "vertical",
      "size": "xs",
      "class": {
        "track": "w-[6px]"
      }
    },
    {
      "orientation": "vertical",
      "size": "sm",
      "class": {
        "track": "w-[7px]"
      }
    },
    {
      "orientation": "vertical",
      "size": "md",
      "class": {
        "track": "w-[8px]"
      }
    },
    {
      "orientation": "vertical",
      "size": "lg",
      "class": {
        "track": "w-[9px]"
      }
    },
    {
      "orientation": "vertical",
      "size": "xl",
      "class": {
        "track": "w-[10px]"
      }
    }
  ],
  "defaultVariants": {
    "size": "md",
    "color": "primary"
  }
};
const _sfc_main$5 = {
  __name: "Slider",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    as: { type: null, required: false },
    size: { type: null, required: false },
    color: { type: null, required: false },
    orientation: { type: null, required: false, default: "horizontal" },
    tooltip: { type: [Boolean, Object], required: false },
    defaultValue: { type: [Number, Array], required: false },
    class: { type: null, required: false },
    ui: { type: null, required: false },
    name: { type: String, required: false },
    disabled: { type: Boolean, required: false },
    inverted: { type: Boolean, required: false },
    min: { type: Number, required: false, default: 0 },
    max: { type: Number, required: false, default: 100 },
    step: { type: Number, required: false, default: 1 },
    minStepsBetweenThumbs: { type: Number, required: false }
  }, {
    "modelValue": { type: null },
    "modelModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["change"], ["update:modelValue"]),
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emits = __emit;
    const modelValue = useModel(__props, "modelValue");
    const appConfig = useAppConfig();
    const rootProps = useForwardPropsEmits(reactivePick(props, "as", "orientation", "min", "max", "step", "minStepsBetweenThumbs", "inverted"), emits);
    const { id, emitFormChange, emitFormInput, size, color, name, disabled, ariaAttrs } = useFormField(props);
    const defaultSliderValue = computed(() => {
      if (typeof props.defaultValue === "number") {
        return [props.defaultValue];
      }
      return props.defaultValue;
    });
    const sliderValue = computed({
      get() {
        if (typeof modelValue.value === "number") {
          return [modelValue.value];
        }
        return modelValue.value ?? defaultSliderValue.value;
      },
      set(value) {
        modelValue.value = value?.length !== 1 ? value : value[0];
      }
    });
    const thumbs = computed(() => sliderValue.value?.length ?? 1);
    const ui = computed(() => tv({ extend: tv(theme$1), ...appConfig.ui?.slider || {} })({
      disabled: disabled.value,
      size: size.value,
      color: color.value,
      orientation: props.orientation
    }));
    function onChange(value) {
      const event = new Event("change", { target: { value } });
      emits("change", event);
      emitFormChange();
    }
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(SliderRoot), mergeProps({ ...unref(rootProps), ...unref(ariaAttrs) }, {
        id: unref(id),
        modelValue: sliderValue.value,
        "onUpdate:modelValue": [($event) => sliderValue.value = $event, ($event) => unref(emitFormInput)()],
        name: unref(name),
        disabled: unref(disabled),
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] }),
        "default-value": defaultSliderValue.value,
        onValueCommit: onChange
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(SliderTrack), {
              "data-slot": "track",
              class: ui.value.track({ class: props.ui?.track })
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(unref(SliderRange), {
                    "data-slot": "range",
                    class: ui.value.range({ class: props.ui?.range })
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(unref(SliderRange), {
                      "data-slot": "range",
                      class: ui.value.range({ class: props.ui?.range })
                    }, null, 8, ["class"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<!--[-->`);
            ssrRenderList(thumbs.value, (thumb) => {
              _push2(`<!--[-->`);
              if (!!__props.tooltip) {
                _push2(ssrRenderComponent(_sfc_main$i, mergeProps({
                  text: thumbs.value > 1 ? String(sliderValue.value?.[thumb - 1]) : String(sliderValue.value),
                  "disable-closing-trigger": ""
                }, { ref_for: true }, typeof __props.tooltip === "object" ? __props.tooltip : {}), {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(ssrRenderComponent(unref(SliderThumb), {
                        "data-slot": "thumb",
                        class: ui.value.thumb({ class: props.ui?.thumb }),
                        "aria-label": thumbs.value === 1 ? "Thumb" : `Thumb ${thumb} of ${thumbs.value}`
                      }, null, _parent3, _scopeId2));
                    } else {
                      return [
                        createVNode(unref(SliderThumb), {
                          "data-slot": "thumb",
                          class: ui.value.thumb({ class: props.ui?.thumb }),
                          "aria-label": thumbs.value === 1 ? "Thumb" : `Thumb ${thumb} of ${thumbs.value}`
                        }, null, 8, ["class", "aria-label"])
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
              } else {
                _push2(ssrRenderComponent(unref(SliderThumb), {
                  "data-slot": "thumb",
                  class: ui.value.thumb({ class: props.ui?.thumb }),
                  "aria-label": thumbs.value === 1 ? "Thumb" : `Thumb ${thumb} of ${thumbs.value}`
                }, null, _parent2, _scopeId));
              }
              _push2(`<!--]-->`);
            });
            _push2(`<!--]-->`);
          } else {
            return [
              createVNode(unref(SliderTrack), {
                "data-slot": "track",
                class: ui.value.track({ class: props.ui?.track })
              }, {
                default: withCtx(() => [
                  createVNode(unref(SliderRange), {
                    "data-slot": "range",
                    class: ui.value.range({ class: props.ui?.range })
                  }, null, 8, ["class"])
                ]),
                _: 1
              }, 8, ["class"]),
              (openBlock(true), createBlock(Fragment, null, renderList(thumbs.value, (thumb) => {
                return openBlock(), createBlock(Fragment, { key: thumb }, [
                  !!__props.tooltip ? (openBlock(), createBlock(_sfc_main$i, mergeProps({
                    key: 0,
                    text: thumbs.value > 1 ? String(sliderValue.value?.[thumb - 1]) : String(sliderValue.value),
                    "disable-closing-trigger": ""
                  }, { ref_for: true }, typeof __props.tooltip === "object" ? __props.tooltip : {}), {
                    default: withCtx(() => [
                      createVNode(unref(SliderThumb), {
                        "data-slot": "thumb",
                        class: ui.value.thumb({ class: props.ui?.thumb }),
                        "aria-label": thumbs.value === 1 ? "Thumb" : `Thumb ${thumb} of ${thumbs.value}`
                      }, null, 8, ["class", "aria-label"])
                    ]),
                    _: 2
                  }, 1040, ["text"])) : (openBlock(), createBlock(unref(SliderThumb), {
                    key: 1,
                    "data-slot": "thumb",
                    class: ui.value.thumb({ class: props.ui?.thumb }),
                    "aria-label": thumbs.value === 1 ? "Thumb" : `Thumb ${thumb} of ${thumbs.value}`
                  }, null, 8, ["class", "aria-label"]))
                ], 64);
              }), 128))
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
};
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/Slider.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const theme = {
  "slots": {
    "root": "relative",
    "viewport": "relative flex",
    "item": ""
  },
  "variants": {
    "orientation": {
      "vertical": {
        "root": "overflow-y-auto overflow-x-hidden",
        "viewport": "flex-col",
        "item": ""
      },
      "horizontal": {
        "root": "overflow-x-auto overflow-y-hidden",
        "viewport": "flex-row",
        "item": ""
      }
    }
  }
};
const _sfc_main$4 = {
  __name: "ScrollArea",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false },
    orientation: { type: null, required: false, default: "vertical" },
    items: { type: Array, required: false },
    virtualize: { type: [Boolean, Object], required: false, default: false },
    class: { type: null, required: false },
    ui: { type: null, required: false }
  },
  emits: ["scroll"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const props = __props;
    const emits = __emit;
    const { dir } = useLocale();
    const appConfig = useAppConfig();
    const ui = computed(() => tv({ extend: tv(theme), ...appConfig.ui?.scrollArea || {} })({
      orientation: props.orientation
    }));
    const rootRef = useTemplateRef("rootRef");
    const isRtl = computed(() => dir.value === "rtl");
    const isHorizontal = computed(() => props.orientation === "horizontal");
    const isVertical = computed(() => !isHorizontal.value);
    const virtualizerProps = toRef(() => {
      const options = typeof props.virtualize === "boolean" ? {} : props.virtualize;
      return defu(options, {
        estimateSize: 100,
        overscan: 12,
        gap: 0,
        paddingStart: 0,
        paddingEnd: 0,
        scrollMargin: 0
      });
    });
    const lanes = computed(() => {
      const value = virtualizerProps.value.lanes;
      return typeof value === "number" ? value : void 0;
    });
    const virtualizer = !!props.virtualize && useVirtualizer({
      ...virtualizerProps.value,
      get overscan() {
        return virtualizerProps.value.overscan;
      },
      get gap() {
        return virtualizerProps.value.gap;
      },
      get paddingStart() {
        return virtualizerProps.value.paddingStart;
      },
      get paddingEnd() {
        return virtualizerProps.value.paddingEnd;
      },
      get scrollMargin() {
        return virtualizerProps.value.scrollMargin;
      },
      get lanes() {
        return lanes.value;
      },
      get isRtl() {
        return isRtl.value;
      },
      get count() {
        return props.items?.length || 0;
      },
      getScrollElement: () => rootRef.value?.$el,
      get horizontal() {
        return isHorizontal.value;
      },
      estimateSize: (index) => {
        const estimate = virtualizerProps.value.estimateSize;
        return typeof estimate === "function" ? estimate(index) : estimate;
      }
    });
    const virtualItems = computed(() => virtualizer ? virtualizer.value.getVirtualItems() : []);
    const totalSize = computed(() => virtualizer ? virtualizer.value.getTotalSize() : 0);
    const virtualViewportStyle = computed(() => ({
      position: "relative",
      inlineSize: isHorizontal.value ? `${totalSize.value}px` : "100%",
      blockSize: isVertical.value ? `${totalSize.value}px` : "100%"
    }));
    function getVirtualItemStyle(virtualItem) {
      const hasLanes = lanes.value !== void 0 && lanes.value > 1;
      const lane = virtualItem.lane;
      const gap = virtualizerProps.value.gap ?? 0;
      const laneSize = hasLanes ? `calc((100% - ${(lanes.value - 1) * gap}px) / ${lanes.value})` : "100%";
      const lanePosition = hasLanes && lane !== void 0 ? `calc(${lane} * ((100% - ${(lanes.value - 1) * gap}px) / ${lanes.value} + ${gap}px))` : 0;
      return {
        position: "absolute",
        insetBlockStart: isHorizontal.value && hasLanes ? lanePosition : 0,
        insetInlineStart: isVertical.value && hasLanes ? lanePosition : 0,
        blockSize: isHorizontal.value ? hasLanes ? laneSize : "100%" : void 0,
        inlineSize: isVertical.value ? hasLanes ? laneSize : "100%" : void 0,
        transform: isHorizontal.value ? `translateX(${isRtl.value ? -virtualItem.start : virtualItem.start}px)` : `translateY(${virtualItem.start}px)`
      };
    }
    watch(lanes, () => {
      if (virtualizer) {
        virtualizer.value.measure();
      }
    }, { flush: "sync" });
    function measureElement(el) {
      if (el && virtualizer) {
        const element = el instanceof Element ? el : el.$el;
        virtualizer.value.measureElement(element);
      }
    }
    watch(
      () => virtualizer ? virtualizer.value.isScrolling : false,
      (isScrolling) => emits("scroll", isScrolling)
    );
    function getItemKey(item, index) {
      if (virtualizerProps.value.getItemKey) {
        return virtualizerProps.value.getItemKey(index);
      }
      if (item && typeof item === "object" && "id" in item) {
        return item.id;
      }
      return index;
    }
    __expose({
      get $el() {
        return rootRef.value?.$el;
      },
      virtualizer: virtualizer || void 0
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Primitive), mergeProps({
        ref_key: "rootRef",
        ref: rootRef,
        as: __props.as,
        "data-slot": "root",
        "data-orientation": __props.orientation,
        class: ui.value.root({ class: [props.ui?.root, props.class] })
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (unref(virtualizer)) {
              _push2(`<div data-slot="viewport" class="${ssrRenderClass(ui.value.viewport({ class: props.ui?.viewport }))}" style="${ssrRenderStyle(virtualViewportStyle.value)}"${_scopeId}><!--[-->`);
              ssrRenderList(virtualItems.value, (virtualItem) => {
                _push2(`<div${ssrRenderAttr("data-index", virtualItem.index)} data-slot="item" class="${ssrRenderClass(ui.value.item({ class: props.ui?.item }))}" style="${ssrRenderStyle(getVirtualItemStyle(virtualItem))}"${_scopeId}>`);
                ssrRenderSlot(_ctx.$slots, "default", {
                  item: __props.items?.[virtualItem.index],
                  index: virtualItem.index,
                  virtualItem
                }, null, _push2, _parent2, _scopeId);
                _push2(`</div>`);
              });
              _push2(`<!--]--></div>`);
            } else {
              _push2(`<div data-slot="viewport" class="${ssrRenderClass(ui.value.viewport({ class: props.ui?.viewport }))}"${_scopeId}>`);
              if (__props.items?.length) {
                _push2(`<!--[-->`);
                ssrRenderList(__props.items, (item, index) => {
                  _push2(`<div data-slot="item" class="${ssrRenderClass(ui.value.item({ class: props.ui?.item }))}"${_scopeId}>`);
                  ssrRenderSlot(_ctx.$slots, "default", {
                    item,
                    index
                  }, null, _push2, _parent2, _scopeId);
                  _push2(`</div>`);
                });
                _push2(`<!--]-->`);
              } else {
                ssrRenderSlot(_ctx.$slots, "default", {}, null, _push2, _parent2, _scopeId);
              }
              _push2(`</div>`);
            }
          } else {
            return [
              unref(virtualizer) ? (openBlock(), createBlock("div", {
                key: 0,
                "data-slot": "viewport",
                class: ui.value.viewport({ class: props.ui?.viewport }),
                style: virtualViewportStyle.value
              }, [
                (openBlock(true), createBlock(Fragment, null, renderList(virtualItems.value, (virtualItem) => {
                  return openBlock(), createBlock("div", {
                    key: String(virtualItem.key),
                    ref_for: true,
                    ref: measureElement,
                    "data-index": virtualItem.index,
                    "data-slot": "item",
                    class: ui.value.item({ class: props.ui?.item }),
                    style: getVirtualItemStyle(virtualItem)
                  }, [
                    renderSlot(_ctx.$slots, "default", {
                      item: __props.items?.[virtualItem.index],
                      index: virtualItem.index,
                      virtualItem
                    })
                  ], 14, ["data-index"]);
                }), 128))
              ], 6)) : (openBlock(), createBlock("div", {
                key: 1,
                "data-slot": "viewport",
                class: ui.value.viewport({ class: props.ui?.viewport })
              }, [
                __props.items?.length ? (openBlock(true), createBlock(Fragment, { key: 0 }, renderList(__props.items, (item, index) => {
                  return openBlock(), createBlock("div", {
                    key: getItemKey(item, index),
                    "data-slot": "item",
                    class: ui.value.item({ class: props.ui?.item })
                  }, [
                    renderSlot(_ctx.$slots, "default", {
                      item,
                      index
                    })
                  ], 2);
                }), 128)) : renderSlot(_ctx.$slots, "default", { key: 1 })
              ], 2))
            ];
          }
        }),
        _: 3
      }, _parent));
    };
  }
};
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/ScrollArea.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "ShopSidebar",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    categories: {},
    brands: {},
    currentFilters: {},
    totalProducts: {},
    minPrice: {},
    maxPrice: {},
    hasActiveFilters: { type: Boolean },
    activeFilterCount: {},
    ratingItems: {},
    isActiveCat: { type: Function },
    isActiveBrand: { type: Function }
  }, {
    "priceRange": { required: true },
    "priceRangeModifiers": {},
    "inStockOnly": { type: Boolean, ...{ required: true } },
    "inStockOnlyModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["filter", "reset"], ["update:priceRange", "update:inStockOnly"]),
  setup(__props, { emit: __emit }) {
    const priceRange = useModel(__props, "priceRange");
    const inStockOnly = useModel(__props, "inStockOnly");
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$9;
      const _component_UIcon = _sfc_main$a;
      const _component_UBadge = _sfc_main$d;
      const _component_UScrollArea = _sfc_main$4;
      const _component_USlider = _sfc_main$5;
      const _component_USwitch = _sfc_main$6;
      const _component_USelectMenu = _sfc_main$j;
      const _component_UButton = _sfc_main$e;
      _push(`<aside${ssrRenderAttrs(mergeProps({ class: "hidden w-72 shrink-0 lg:block mb-10" }, _attrs))}><div class="sticky top-36 space-y-4">`);
      _push(ssrRenderComponent(_component_UCard, { ui: { root: "shadow-sm overflow-hidden", header: "px-4 pt-4 pb-3", body: "p-0" } }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><div class="flex items-center gap-2"${_scopeId}><div class="flex size-7 items-center justify-center rounded-md bg-primary-50 dark:bg-primary-950/40"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-layout-grid",
              class: "size-3.5 text-primary"
            }, null, _parent2, _scopeId));
            _push2(`</div><span class="text-sm font-semibold text-highlighted"${_scopeId}>Kategori</span></div>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "neutral",
              variant: "subtle",
              size: "xs"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(__props.totalProducts)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(__props.totalProducts), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("div", { class: "flex items-center gap-2" }, [
                  createVNode("div", { class: "flex size-7 items-center justify-center rounded-md bg-primary-50 dark:bg-primary-950/40" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-layout-grid",
                      class: "size-3.5 text-primary"
                    })
                  ]),
                  createVNode("span", { class: "text-sm font-semibold text-highlighted" }, "Kategori")
                ]),
                createVNode(_component_UBadge, {
                  color: "neutral",
                  variant: "subtle",
                  size: "xs"
                }, {
                  default: withCtx(() => [
                    createTextVNode(toDisplayString(__props.totalProducts), 1)
                  ]),
                  _: 1
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UScrollArea, { class: "max-h-72" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col gap-px p-2"${_scopeId2}><button class="${ssrRenderClass([__props.isActiveCat(void 0) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60", "flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors"])}"${_scopeId2}><span${_scopeId2}>Semua Kategori</span>`);
                  _push3(ssrRenderComponent(_component_UBadge, {
                    color: __props.isActiveCat(void 0) ? "primary" : "neutral",
                    variant: "subtle",
                    size: "xs"
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`${ssrInterpolate(__props.totalProducts)}`);
                      } else {
                        return [
                          createTextVNode(toDisplayString(__props.totalProducts), 1)
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                  _push3(`</button><!--[-->`);
                  ssrRenderList(__props.categories, (cat) => {
                    _push3(`<button class="${ssrRenderClass([__props.isActiveCat(cat.slug) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60", "flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors"])}"${_scopeId2}><span class="truncate"${_scopeId2}>${ssrInterpolate(cat.name)}</span>`);
                    _push3(ssrRenderComponent(_component_UBadge, {
                      color: __props.isActiveCat(cat.slug) ? "primary" : "neutral",
                      variant: "subtle",
                      size: "xs"
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(`${ssrInterpolate(cat.products_count || 0)}`);
                        } else {
                          return [
                            createTextVNode(toDisplayString(cat.products_count || 0), 1)
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                    _push3(`</button>`);
                  });
                  _push3(`<!--]--></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col gap-px p-2" }, [
                      createVNode("button", {
                        class: ["flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors", __props.isActiveCat(void 0) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60"],
                        onClick: ($event) => emit("filter", { category: void 0 })
                      }, [
                        createVNode("span", null, "Semua Kategori"),
                        createVNode(_component_UBadge, {
                          color: __props.isActiveCat(void 0) ? "primary" : "neutral",
                          variant: "subtle",
                          size: "xs"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(__props.totalProducts), 1)
                          ]),
                          _: 1
                        }, 8, ["color"])
                      ], 10, ["onClick"]),
                      (openBlock(true), createBlock(Fragment, null, renderList(__props.categories, (cat) => {
                        return openBlock(), createBlock("button", {
                          key: `cat-${cat.slug}`,
                          class: ["flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors", __props.isActiveCat(cat.slug) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60"],
                          onClick: ($event) => emit("filter", { category: cat.slug })
                        }, [
                          createVNode("span", { class: "truncate" }, toDisplayString(cat.name), 1),
                          createVNode(_component_UBadge, {
                            color: __props.isActiveCat(cat.slug) ? "primary" : "neutral",
                            variant: "subtle",
                            size: "xs"
                          }, {
                            default: withCtx(() => [
                              createTextVNode(toDisplayString(cat.products_count || 0), 1)
                            ]),
                            _: 2
                          }, 1032, ["color"])
                        ], 10, ["onClick"]);
                      }), 128))
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UScrollArea, { class: "max-h-72" }, {
                default: withCtx(() => [
                  createVNode("div", { class: "flex flex-col gap-px p-2" }, [
                    createVNode("button", {
                      class: ["flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors", __props.isActiveCat(void 0) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60"],
                      onClick: ($event) => emit("filter", { category: void 0 })
                    }, [
                      createVNode("span", null, "Semua Kategori"),
                      createVNode(_component_UBadge, {
                        color: __props.isActiveCat(void 0) ? "primary" : "neutral",
                        variant: "subtle",
                        size: "xs"
                      }, {
                        default: withCtx(() => [
                          createTextVNode(toDisplayString(__props.totalProducts), 1)
                        ]),
                        _: 1
                      }, 8, ["color"])
                    ], 10, ["onClick"]),
                    (openBlock(true), createBlock(Fragment, null, renderList(__props.categories, (cat) => {
                      return openBlock(), createBlock("button", {
                        key: `cat-${cat.slug}`,
                        class: ["flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors", __props.isActiveCat(cat.slug) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60"],
                        onClick: ($event) => emit("filter", { category: cat.slug })
                      }, [
                        createVNode("span", { class: "truncate" }, toDisplayString(cat.name), 1),
                        createVNode(_component_UBadge, {
                          color: __props.isActiveCat(cat.slug) ? "primary" : "neutral",
                          variant: "subtle",
                          size: "xs"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(cat.products_count || 0), 1)
                          ]),
                          _: 2
                        }, 1032, ["color"])
                      ], 10, ["onClick"]);
                    }), 128))
                  ])
                ]),
                _: 1
              })
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { ui: { root: "shadow-sm", header: "px-4 pt-4 pb-3", body: "px-4 pb-4 pt-2" } }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center gap-2"${_scopeId}><div class="flex size-7 items-center justify-center rounded-md bg-success-50 dark:bg-success-950/40"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-banknote",
              class: "size-3.5 text-success"
            }, null, _parent2, _scopeId));
            _push2(`</div><span class="text-sm font-semibold text-highlighted"${_scopeId}>Rentang Harga</span></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center gap-2" }, [
                createVNode("div", { class: "flex size-7 items-center justify-center rounded-md bg-success-50 dark:bg-success-950/40" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-banknote",
                    class: "size-3.5 text-success"
                  })
                ]),
                createVNode("span", { class: "text-sm font-semibold text-highlighted" }, "Rentang Harga")
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_USlider, {
              modelValue: priceRange.value,
              "onUpdate:modelValue": ($event) => priceRange.value = $event,
              range: "",
              min: __props.minPrice,
              max: __props.maxPrice,
              step: 1e4,
              color: "primary",
              class: "mb-4"
            }, null, _parent2, _scopeId));
            _push2(`<div class="grid grid-cols-2 gap-2"${_scopeId}><div class="rounded-xl bg-elevated/50 p-3 text-center"${_scopeId}><p class="text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Min</p><p class="mt-0.5 text-sm font-bold tabular-nums text-highlighted"${_scopeId}>${ssrInterpolate(unref(formatCurrency)(priceRange.value[0]))}</p></div><div class="rounded-xl bg-elevated/50 p-3 text-center"${_scopeId}><p class="text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Maks</p><p class="mt-0.5 text-sm font-bold tabular-nums text-highlighted"${_scopeId}>${ssrInterpolate(unref(formatCurrency)(priceRange.value[1]))}</p></div></div>`);
          } else {
            return [
              createVNode(_component_USlider, {
                modelValue: priceRange.value,
                "onUpdate:modelValue": ($event) => priceRange.value = $event,
                range: "",
                min: __props.minPrice,
                max: __props.maxPrice,
                step: 1e4,
                color: "primary",
                class: "mb-4"
              }, null, 8, ["modelValue", "onUpdate:modelValue", "min", "max"]),
              createVNode("div", { class: "grid grid-cols-2 gap-2" }, [
                createVNode("div", { class: "rounded-xl bg-elevated/50 p-3 text-center" }, [
                  createVNode("p", { class: "text-[10px] font-bold uppercase tracking-wider text-muted" }, "Min"),
                  createVNode("p", { class: "mt-0.5 text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(unref(formatCurrency)(priceRange.value[0])), 1)
                ]),
                createVNode("div", { class: "rounded-xl bg-elevated/50 p-3 text-center" }, [
                  createVNode("p", { class: "text-[10px] font-bold uppercase tracking-wider text-muted" }, "Maks"),
                  createVNode("p", { class: "mt-0.5 text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(unref(formatCurrency)(priceRange.value[1])), 1)
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      if (__props.brands?.length) {
        _push(ssrRenderComponent(_component_UCard, { ui: { root: "shadow-sm overflow-hidden", header: "px-4 pt-4 pb-3", body: "p-0" } }, {
          header: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<div class="flex items-center gap-2"${_scopeId}><div class="flex size-7 items-center justify-center rounded-md bg-info-50 dark:bg-info-950/40"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-tags",
                class: "size-3.5 text-info"
              }, null, _parent2, _scopeId));
              _push2(`</div><span class="text-sm font-semibold text-highlighted"${_scopeId}>Brand</span></div>`);
            } else {
              return [
                createVNode("div", { class: "flex items-center gap-2" }, [
                  createVNode("div", { class: "flex size-7 items-center justify-center rounded-md bg-info-50 dark:bg-info-950/40" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-tags",
                      class: "size-3.5 text-info"
                    })
                  ]),
                  createVNode("span", { class: "text-sm font-semibold text-highlighted" }, "Brand")
                ])
              ];
            }
          }),
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(ssrRenderComponent(_component_UScrollArea, { class: "max-h-60" }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<div class="flex flex-col gap-px p-2"${_scopeId2}><button class="${ssrRenderClass([!__props.currentFilters.brand ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60", "flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors"])}"${_scopeId2}><span${_scopeId2}>Semua Brand</span></button><!--[-->`);
                    ssrRenderList(__props.brands, (brand) => {
                      _push3(`<button class="${ssrRenderClass([__props.isActiveBrand(brand.slug) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60", "flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors"])}"${_scopeId2}><span class="truncate"${_scopeId2}>${ssrInterpolate(brand.name)}</span>`);
                      _push3(ssrRenderComponent(_component_UBadge, {
                        color: __props.isActiveBrand(brand.slug) ? "primary" : "neutral",
                        variant: "subtle",
                        size: "xs"
                      }, {
                        default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                          if (_push4) {
                            _push4(`${ssrInterpolate(brand.products_count || 0)}`);
                          } else {
                            return [
                              createTextVNode(toDisplayString(brand.products_count || 0), 1)
                            ];
                          }
                        }),
                        _: 2
                      }, _parent3, _scopeId2));
                      _push3(`</button>`);
                    });
                    _push3(`<!--]--></div>`);
                  } else {
                    return [
                      createVNode("div", { class: "flex flex-col gap-px p-2" }, [
                        createVNode("button", {
                          class: ["flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors", !__props.currentFilters.brand ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60"],
                          onClick: ($event) => emit("filter", { brand: void 0 })
                        }, [
                          createVNode("span", null, "Semua Brand")
                        ], 10, ["onClick"]),
                        (openBlock(true), createBlock(Fragment, null, renderList(__props.brands, (brand) => {
                          return openBlock(), createBlock("button", {
                            key: `brand-${brand.slug}`,
                            class: ["flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors", __props.isActiveBrand(brand.slug) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60"],
                            onClick: ($event) => emit("filter", { brand: brand.slug })
                          }, [
                            createVNode("span", { class: "truncate" }, toDisplayString(brand.name), 1),
                            createVNode(_component_UBadge, {
                              color: __props.isActiveBrand(brand.slug) ? "primary" : "neutral",
                              variant: "subtle",
                              size: "xs"
                            }, {
                              default: withCtx(() => [
                                createTextVNode(toDisplayString(brand.products_count || 0), 1)
                              ]),
                              _: 2
                            }, 1032, ["color"])
                          ], 10, ["onClick"]);
                        }), 128))
                      ])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              return [
                createVNode(_component_UScrollArea, { class: "max-h-60" }, {
                  default: withCtx(() => [
                    createVNode("div", { class: "flex flex-col gap-px p-2" }, [
                      createVNode("button", {
                        class: ["flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors", !__props.currentFilters.brand ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60"],
                        onClick: ($event) => emit("filter", { brand: void 0 })
                      }, [
                        createVNode("span", null, "Semua Brand")
                      ], 10, ["onClick"]),
                      (openBlock(true), createBlock(Fragment, null, renderList(__props.brands, (brand) => {
                        return openBlock(), createBlock("button", {
                          key: `brand-${brand.slug}`,
                          class: ["flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors", __props.isActiveBrand(brand.slug) ? "bg-primary-50 font-semibold text-primary dark:bg-primary-950/40" : "text-default hover:bg-elevated/60"],
                          onClick: ($event) => emit("filter", { brand: brand.slug })
                        }, [
                          createVNode("span", { class: "truncate" }, toDisplayString(brand.name), 1),
                          createVNode(_component_UBadge, {
                            color: __props.isActiveBrand(brand.slug) ? "primary" : "neutral",
                            variant: "subtle",
                            size: "xs"
                          }, {
                            default: withCtx(() => [
                              createTextVNode(toDisplayString(brand.products_count || 0), 1)
                            ]),
                            _: 2
                          }, 1032, ["color"])
                        ], 10, ["onClick"]);
                      }), 128))
                    ])
                  ]),
                  _: 1
                })
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(ssrRenderComponent(_component_UCard, { ui: { root: "shadow-sm", header: "px-4 pt-4 pb-3", body: "px-4 pb-4 pt-2" } }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center gap-2"${_scopeId}><div class="flex size-7 items-center justify-center rounded-md bg-warning-50 dark:bg-warning-950/40"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-settings-2",
              class: "size-3.5 text-warning"
            }, null, _parent2, _scopeId));
            _push2(`</div><span class="text-sm font-semibold text-highlighted"${_scopeId}>Preferensi</span></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center gap-2" }, [
                createVNode("div", { class: "flex size-7 items-center justify-center rounded-md bg-warning-50 dark:bg-warning-950/40" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-settings-2",
                    class: "size-3.5 text-warning"
                  })
                ]),
                createVNode("span", { class: "text-sm font-semibold text-highlighted" }, "Preferensi")
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-3"${_scopeId}><label class="flex cursor-pointer items-center justify-between rounded-xl bg-elevated/40 px-3 py-2.5 transition-colors hover:bg-elevated/70"${_scopeId}><div class="flex items-center gap-2.5"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-package-check",
              class: "size-4 text-success"
            }, null, _parent2, _scopeId));
            _push2(`<span class="text-sm font-medium text-highlighted"${_scopeId}>Stok tersedia</span></div>`);
            _push2(ssrRenderComponent(_component_USwitch, {
              modelValue: inStockOnly.value,
              "onUpdate:modelValue": ($event) => inStockOnly.value = $event,
              color: "primary",
              size: "sm"
            }, null, _parent2, _scopeId));
            _push2(`</label><div class="rounded-xl bg-elevated/40 px-3 py-2.5"${_scopeId}><div class="mb-2 flex items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-star",
              class: "size-4 text-amber-400"
            }, null, _parent2, _scopeId));
            _push2(`<span class="text-sm font-medium text-highlighted"${_scopeId}>Rating minimum</span></div>`);
            _push2(ssrRenderComponent(_component_USelectMenu, {
              "model-value": __props.currentFilters.rating,
              items: __props.ratingItems,
              "value-key": "value",
              "label-key": "label",
              placeholder: "Semua rating",
              size: "sm",
              class: "w-full",
              "onUpdate:modelValue": (v) => emit("filter", { rating: v })
            }, null, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-3" }, [
                createVNode("label", { class: "flex cursor-pointer items-center justify-between rounded-xl bg-elevated/40 px-3 py-2.5 transition-colors hover:bg-elevated/70" }, [
                  createVNode("div", { class: "flex items-center gap-2.5" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-package-check",
                      class: "size-4 text-success"
                    }),
                    createVNode("span", { class: "text-sm font-medium text-highlighted" }, "Stok tersedia")
                  ]),
                  createVNode(_component_USwitch, {
                    modelValue: inStockOnly.value,
                    "onUpdate:modelValue": ($event) => inStockOnly.value = $event,
                    color: "primary",
                    size: "sm"
                  }, null, 8, ["modelValue", "onUpdate:modelValue"])
                ]),
                createVNode("div", { class: "rounded-xl bg-elevated/40 px-3 py-2.5" }, [
                  createVNode("div", { class: "mb-2 flex items-center gap-2" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-star",
                      class: "size-4 text-amber-400"
                    }),
                    createVNode("span", { class: "text-sm font-medium text-highlighted" }, "Rating minimum")
                  ]),
                  createVNode(_component_USelectMenu, {
                    "model-value": __props.currentFilters.rating,
                    items: __props.ratingItems,
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Semua rating",
                    size: "sm",
                    class: "w-full",
                    "onUpdate:modelValue": (v) => emit("filter", { rating: v })
                  }, null, 8, ["model-value", "items", "onUpdate:modelValue"])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      if (__props.hasActiveFilters) {
        _push(ssrRenderComponent(_component_UButton, {
          block: "",
          color: "neutral",
          variant: "outline",
          icon: "i-lucide-rotate-ccw",
          size: "sm",
          onClick: ($event) => emit("reset")
        }, {
          trailing: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "primary",
                variant: "soft",
                size: "xs"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`${ssrInterpolate(__props.activeFilterCount)}`);
                  } else {
                    return [
                      createTextVNode(toDisplayString(__props.activeFilterCount), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              return [
                createVNode(_component_UBadge, {
                  color: "primary",
                  variant: "soft",
                  size: "xs"
                }, {
                  default: withCtx(() => [
                    createTextVNode(toDisplayString(__props.activeFilterCount), 1)
                  ]),
                  _: 1
                })
              ];
            }
          }),
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` Reset Semua Filter `);
            } else {
              return [
                createTextVNode(" Reset Semua Filter ")
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div></aside>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/shop/ShopSidebar.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "ShopProductGrid",
  __ssrInlineRender: true,
  props: {
    products: {},
    viewMode: {},
    currentPage: {},
    itemsPerPage: {},
    totalProducts: {},
    shownFrom: {},
    shownTo: {},
    lastPage: {},
    isLoading: { type: Boolean }
  },
  emits: ["pageChange", "resetFilters"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$9;
      const _component_UIcon = _sfc_main$a;
      const _component_UBadge = _sfc_main$d;
      const _component_UButton = _sfc_main$e;
      const _component_UPagination = _sfc_main$k;
      const _component_UEmpty = _sfc_main$l;
      _push(`<main${ssrRenderAttrs(mergeProps({ class: "min-w-0 flex-1" }, _attrs))}>`);
      if (__props.products.length > 0) {
        _push(`<!--[-->`);
        if (__props.viewMode === "grid") {
          _push(`<div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4 lg:gap-5"><!--[-->`);
          ssrRenderList(__props.products, (product) => {
            _push(ssrRenderComponent(ProductCard, {
              key: product.id,
              product
            }, null, _parent));
          });
          _push(`<!--]--></div>`);
        } else {
          _push(`<div class="space-y-3"><!--[-->`);
          ssrRenderList(__props.products, (product) => {
            _push(ssrRenderComponent(_component_UCard, {
              key: product.id,
              ui: { root: "group hover:ring-primary/30 transition-all duration-200 shadow-sm", body: "p-4" }
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(`<div class="flex gap-4"${_scopeId}><div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-elevated/50"${_scopeId}>`);
                  if (product.image) {
                    _push2(`<img${ssrRenderAttr("src", product.image)}${ssrRenderAttr("alt", product.name)} class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"${_scopeId}>`);
                  } else {
                    _push2(`<div class="flex h-full w-full items-center justify-center"${_scopeId}>`);
                    _push2(ssrRenderComponent(_component_UIcon, {
                      name: "i-lucide-image",
                      class: "size-6 text-muted"
                    }, null, _parent2, _scopeId));
                    _push2(`</div>`);
                  }
                  _push2(`</div><div class="min-w-0 flex-1"${_scopeId}><div class="flex items-start justify-between gap-3"${_scopeId}><div class="min-w-0"${_scopeId}><h3 class="line-clamp-2 text-sm font-bold text-highlighted transition-colors group-hover:text-primary"${_scopeId}>${ssrInterpolate(product.name)}</h3><div class="mt-2 flex flex-wrap items-center gap-2"${_scopeId}>`);
                  if (product.badge) {
                    _push2(ssrRenderComponent(_component_UBadge, {
                      color: product.badge === "Baru" ? "info" : "warning",
                      variant: "soft",
                      size: "xs"
                    }, {
                      default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                        if (_push3) {
                          _push3(`${ssrInterpolate(product.badge)}`);
                        } else {
                          return [
                            createTextVNode(toDisplayString(product.badge), 1)
                          ];
                        }
                      }),
                      _: 2
                    }, _parent2, _scopeId));
                  } else {
                    _push2(`<!---->`);
                  }
                  if (product.rating > 0) {
                    _push2(`<div class="flex items-center gap-1 text-xs text-muted"${_scopeId}>`);
                    _push2(ssrRenderComponent(_component_UIcon, {
                      name: "i-lucide-star",
                      class: "size-3.5 text-amber-400"
                    }, null, _parent2, _scopeId));
                    _push2(`<span class="font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(product.rating.toFixed(1))}</span><span${_scopeId}>(${ssrInterpolate(product.reviewCount)})</span></div>`);
                  } else {
                    _push2(`<!---->`);
                  }
                  _push2(`</div></div><div class="text-right"${_scopeId}><p class="text-lg font-extrabold text-primary"${_scopeId}>${ssrInterpolate(unref(formatCurrency)(product.price))}</p>`);
                  _push2(ssrRenderComponent(_component_UButton, {
                    color: "primary",
                    size: "xs",
                    icon: "i-lucide-shopping-cart",
                    class: "mt-2"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(` Beli `);
                      } else {
                        return [
                          createTextVNode(" Beli ")
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                  _push2(`</div></div></div></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex gap-4" }, [
                      createVNode("div", { class: "h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-elevated/50" }, [
                        product.image ? (openBlock(), createBlock("img", {
                          key: 0,
                          src: product.image,
                          alt: product.name,
                          class: "h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                          key: 1,
                          class: "flex h-full w-full items-center justify-center"
                        }, [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-image",
                            class: "size-6 text-muted"
                          })
                        ]))
                      ]),
                      createVNode("div", { class: "min-w-0 flex-1" }, [
                        createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                          createVNode("div", { class: "min-w-0" }, [
                            createVNode("h3", { class: "line-clamp-2 text-sm font-bold text-highlighted transition-colors group-hover:text-primary" }, toDisplayString(product.name), 1),
                            createVNode("div", { class: "mt-2 flex flex-wrap items-center gap-2" }, [
                              product.badge ? (openBlock(), createBlock(_component_UBadge, {
                                key: 0,
                                color: product.badge === "Baru" ? "info" : "warning",
                                variant: "soft",
                                size: "xs"
                              }, {
                                default: withCtx(() => [
                                  createTextVNode(toDisplayString(product.badge), 1)
                                ]),
                                _: 2
                              }, 1032, ["color"])) : createCommentVNode("", true),
                              product.rating > 0 ? (openBlock(), createBlock("div", {
                                key: 1,
                                class: "flex items-center gap-1 text-xs text-muted"
                              }, [
                                createVNode(_component_UIcon, {
                                  name: "i-lucide-star",
                                  class: "size-3.5 text-amber-400"
                                }),
                                createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(product.rating.toFixed(1)), 1),
                                createVNode("span", null, "(" + toDisplayString(product.reviewCount) + ")", 1)
                              ])) : createCommentVNode("", true)
                            ])
                          ]),
                          createVNode("div", { class: "text-right" }, [
                            createVNode("p", { class: "text-lg font-extrabold text-primary" }, toDisplayString(unref(formatCurrency)(product.price)), 1),
                            createVNode(_component_UButton, {
                              color: "primary",
                              size: "xs",
                              icon: "i-lucide-shopping-cart",
                              class: "mt-2"
                            }, {
                              default: withCtx(() => [
                                createTextVNode(" Beli ")
                              ]),
                              _: 1
                            })
                          ])
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
        _push(`<div class="mt-8 rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div class="text-sm text-gray-600 dark:text-gray-300"> Menampilkan <span class="font-semibold text-gray-900 dark:text-white">${ssrInterpolate(props.shownFrom ?? 0)} - ${ssrInterpolate(props.shownTo ?? 0)}</span> dari <span class="font-semibold text-gray-900 dark:text-white">${ssrInterpolate(props.totalProducts)}</span> produk. </div>`);
        _push(ssrRenderComponent(_component_UPagination, {
          page: props.currentPage,
          total: props.totalProducts,
          "items-per-page": props.itemsPerPage,
          "show-edges": "",
          disabled: props.isLoading || props.lastPage <= 1,
          "onUpdate:page": (value) => emit("pageChange", value)
        }, null, _parent));
        _push(`</div>`);
        if (props.isLoading) {
          _push(`<div class="mt-4 inline-flex items-center gap-2.5 text-sm text-muted">`);
          _push(ssrRenderComponent(_component_UIcon, {
            name: "i-lucide-loader-2",
            class: "size-4 animate-spin text-primary"
          }, null, _parent));
          _push(` Memuat produk... </div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div><!--]-->`);
      } else {
        _push(ssrRenderComponent(_component_UEmpty, {
          icon: "i-lucide-search-x",
          title: "Tidak Ada Produk",
          description: "Coba ubah filter atau kata kunci pencarian untuk menemukan produk yang Anda cari.",
          variant: "outline",
          size: "lg",
          actions: [
            {
              label: "Reset Semua Filter",
              icon: "i-lucide-rotate-ccw",
              color: "primary",
              variant: "soft",
              onClick: () => emit("resetFilters")
            }
          ],
          ui: { root: "py-20 rounded-2xl" }
        }, null, _parent));
      }
      _push(`</main>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/shop/ShopProductGrid.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "ShopMobileFilters",
  __ssrInlineRender: true,
  props: /* @__PURE__ */ mergeModels({
    categories: {},
    brands: {},
    currentFilters: {},
    minPrice: {},
    maxPrice: {},
    ratingItems: {},
    isActiveCat: { type: Function },
    isActiveBrand: { type: Function }
  }, {
    "open": { type: Boolean, ...{ required: true } },
    "openModifiers": {},
    "priceRange": { required: true },
    "priceRangeModifiers": {},
    "inStockOnly": { type: Boolean, ...{ required: true } },
    "inStockOnlyModifiers": {}
  }),
  emits: /* @__PURE__ */ mergeModels(["filter", "reset"], ["update:open", "update:priceRange", "update:inStockOnly"]),
  setup(__props, { emit: __emit }) {
    const isOpen = useModel(__props, "open");
    const priceRange = useModel(__props, "priceRange");
    const inStockOnly = useModel(__props, "inStockOnly");
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UDrawer = _sfc_main$m;
      const _component_UButton = _sfc_main$e;
      const _component_USeparator = _sfc_main$b;
      const _component_USlider = _sfc_main$5;
      const _component_UCheckbox = _sfc_main$n;
      const _component_USelectMenu = _sfc_main$j;
      _push(ssrRenderComponent(_component_UDrawer, mergeProps({
        open: isOpen.value,
        "onUpdate:open": ($event) => isOpen.value = $event,
        direction: "right",
        title: "Filter Produk",
        description: "Sesuaikan filter untuk hasil terbaik",
        class: "z-5"
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-6"${_scopeId}><div class="mt-10"${_scopeId}><p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Kategori</p><div class="grid grid-cols-2 gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              size: "sm",
              variant: __props.isActiveCat(void 0) ? "soft" : "outline",
              color: __props.isActiveCat(void 0) ? "primary" : "neutral",
              onClick: ($event) => emit("filter", { category: void 0 })
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Semua `);
                } else {
                  return [
                    createTextVNode(" Semua ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<!--[-->`);
            ssrRenderList(__props.categories, (cat) => {
              _push2(ssrRenderComponent(_component_UButton, {
                key: `dcat-${cat.slug}`,
                size: "sm",
                variant: __props.isActiveCat(cat.slug) ? "soft" : "outline",
                color: __props.isActiveCat(cat.slug) ? "primary" : "neutral",
                onClick: ($event) => emit("filter", { category: cat.slug })
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`${ssrInterpolate(cat.name)}`);
                  } else {
                    return [
                      createTextVNode(toDisplayString(cat.name), 1)
                    ];
                  }
                }),
                _: 2
              }, _parent2, _scopeId));
            });
            _push2(`<!--]--></div></div>`);
            _push2(ssrRenderComponent(_component_USeparator, null, null, _parent2, _scopeId));
            _push2(`<div${_scopeId}><p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Rentang Harga</p>`);
            _push2(ssrRenderComponent(_component_USlider, {
              modelValue: priceRange.value,
              "onUpdate:modelValue": ($event) => priceRange.value = $event,
              range: "",
              min: __props.minPrice,
              max: __props.maxPrice,
              step: 1e4,
              color: "primary"
            }, null, _parent2, _scopeId));
            _push2(`<div class="mt-3 grid grid-cols-2 gap-2.5 text-center"${_scopeId}><div class="rounded-lg bg-elevated/50 p-3"${_scopeId}><div class="text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Min</div><div class="mt-1 text-sm font-bold tabular-nums text-highlighted"${_scopeId}>${ssrInterpolate(unref(formatCurrency)(priceRange.value[0]))}</div></div><div class="rounded-lg bg-elevated/50 p-3"${_scopeId}><div class="text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Maks</div><div class="mt-1 text-sm font-bold tabular-nums text-highlighted"${_scopeId}>${ssrInterpolate(unref(formatCurrency)(priceRange.value[1]))}</div></div></div></div>`);
            _push2(ssrRenderComponent(_component_USeparator, null, null, _parent2, _scopeId));
            if (__props.brands?.length) {
              _push2(`<div${_scopeId}><p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Brand</p><div class="grid grid-cols-2 gap-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UButton, {
                size: "sm",
                variant: !__props.currentFilters.brand ? "soft" : "outline",
                color: !__props.currentFilters.brand ? "primary" : "neutral",
                onClick: ($event) => emit("filter", { brand: void 0 })
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Semua `);
                  } else {
                    return [
                      createTextVNode(" Semua ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`<!--[-->`);
              ssrRenderList(__props.brands, (brand) => {
                _push2(ssrRenderComponent(_component_UButton, {
                  key: `dbrand-${brand.slug}`,
                  size: "sm",
                  variant: __props.isActiveBrand(brand.slug) ? "soft" : "outline",
                  color: __props.isActiveBrand(brand.slug) ? "primary" : "neutral",
                  onClick: ($event) => emit("filter", { brand: brand.slug })
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(brand.name)}`);
                    } else {
                      return [
                        createTextVNode(toDisplayString(brand.name), 1)
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
            if (__props.brands?.length) {
              _push2(ssrRenderComponent(_component_USeparator, null, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div${_scopeId}><p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Ketersediaan</p><label class="flex cursor-pointer items-center gap-3 rounded-lg p-2 transition-colors hover:bg-elevated/50"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UCheckbox, {
              modelValue: inStockOnly.value,
              "onUpdate:modelValue": ($event) => inStockOnly.value = $event
            }, null, _parent2, _scopeId));
            _push2(`<span class="text-sm font-medium text-highlighted"${_scopeId}>Hanya stok tersedia</span></label></div>`);
            _push2(ssrRenderComponent(_component_USeparator, null, null, _parent2, _scopeId));
            _push2(`<div${_scopeId}><p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted"${_scopeId}>Rating</p>`);
            _push2(ssrRenderComponent(_component_USelectMenu, {
              "model-value": __props.currentFilters.rating,
              items: __props.ratingItems,
              "value-key": "value",
              "label-key": "label",
              placeholder: "Pilih rating",
              class: "w-full",
              "onUpdate:modelValue": (v) => emit("filter", { rating: v })
            }, null, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-6" }, [
                createVNode("div", { class: "mt-10" }, [
                  createVNode("p", { class: "mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted" }, "Kategori"),
                  createVNode("div", { class: "grid grid-cols-2 gap-2" }, [
                    createVNode(_component_UButton, {
                      size: "sm",
                      variant: __props.isActiveCat(void 0) ? "soft" : "outline",
                      color: __props.isActiveCat(void 0) ? "primary" : "neutral",
                      onClick: ($event) => emit("filter", { category: void 0 })
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Semua ")
                      ]),
                      _: 1
                    }, 8, ["variant", "color", "onClick"]),
                    (openBlock(true), createBlock(Fragment, null, renderList(__props.categories, (cat) => {
                      return openBlock(), createBlock(_component_UButton, {
                        key: `dcat-${cat.slug}`,
                        size: "sm",
                        variant: __props.isActiveCat(cat.slug) ? "soft" : "outline",
                        color: __props.isActiveCat(cat.slug) ? "primary" : "neutral",
                        onClick: ($event) => emit("filter", { category: cat.slug })
                      }, {
                        default: withCtx(() => [
                          createTextVNode(toDisplayString(cat.name), 1)
                        ]),
                        _: 2
                      }, 1032, ["variant", "color", "onClick"]);
                    }), 128))
                  ])
                ]),
                createVNode(_component_USeparator),
                createVNode("div", null, [
                  createVNode("p", { class: "mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted" }, "Rentang Harga"),
                  createVNode(_component_USlider, {
                    modelValue: priceRange.value,
                    "onUpdate:modelValue": ($event) => priceRange.value = $event,
                    range: "",
                    min: __props.minPrice,
                    max: __props.maxPrice,
                    step: 1e4,
                    color: "primary"
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "min", "max"]),
                  createVNode("div", { class: "mt-3 grid grid-cols-2 gap-2.5 text-center" }, [
                    createVNode("div", { class: "rounded-lg bg-elevated/50 p-3" }, [
                      createVNode("div", { class: "text-[10px] font-bold uppercase tracking-wider text-muted" }, "Min"),
                      createVNode("div", { class: "mt-1 text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(unref(formatCurrency)(priceRange.value[0])), 1)
                    ]),
                    createVNode("div", { class: "rounded-lg bg-elevated/50 p-3" }, [
                      createVNode("div", { class: "text-[10px] font-bold uppercase tracking-wider text-muted" }, "Maks"),
                      createVNode("div", { class: "mt-1 text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(unref(formatCurrency)(priceRange.value[1])), 1)
                    ])
                  ])
                ]),
                createVNode(_component_USeparator),
                __props.brands?.length ? (openBlock(), createBlock("div", { key: 0 }, [
                  createVNode("p", { class: "mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted" }, "Brand"),
                  createVNode("div", { class: "grid grid-cols-2 gap-2" }, [
                    createVNode(_component_UButton, {
                      size: "sm",
                      variant: !__props.currentFilters.brand ? "soft" : "outline",
                      color: !__props.currentFilters.brand ? "primary" : "neutral",
                      onClick: ($event) => emit("filter", { brand: void 0 })
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Semua ")
                      ]),
                      _: 1
                    }, 8, ["variant", "color", "onClick"]),
                    (openBlock(true), createBlock(Fragment, null, renderList(__props.brands, (brand) => {
                      return openBlock(), createBlock(_component_UButton, {
                        key: `dbrand-${brand.slug}`,
                        size: "sm",
                        variant: __props.isActiveBrand(brand.slug) ? "soft" : "outline",
                        color: __props.isActiveBrand(brand.slug) ? "primary" : "neutral",
                        onClick: ($event) => emit("filter", { brand: brand.slug })
                      }, {
                        default: withCtx(() => [
                          createTextVNode(toDisplayString(brand.name), 1)
                        ]),
                        _: 2
                      }, 1032, ["variant", "color", "onClick"]);
                    }), 128))
                  ])
                ])) : createCommentVNode("", true),
                __props.brands?.length ? (openBlock(), createBlock(_component_USeparator, { key: 1 })) : createCommentVNode("", true),
                createVNode("div", null, [
                  createVNode("p", { class: "mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted" }, "Ketersediaan"),
                  createVNode("label", { class: "flex cursor-pointer items-center gap-3 rounded-lg p-2 transition-colors hover:bg-elevated/50" }, [
                    createVNode(_component_UCheckbox, {
                      modelValue: inStockOnly.value,
                      "onUpdate:modelValue": ($event) => inStockOnly.value = $event
                    }, null, 8, ["modelValue", "onUpdate:modelValue"]),
                    createVNode("span", { class: "text-sm font-medium text-highlighted" }, "Hanya stok tersedia")
                  ])
                ]),
                createVNode(_component_USeparator),
                createVNode("div", null, [
                  createVNode("p", { class: "mb-2.5 text-[10px] font-bold uppercase tracking-wider text-muted" }, "Rating"),
                  createVNode(_component_USelectMenu, {
                    "model-value": __props.currentFilters.rating,
                    items: __props.ratingItems,
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih rating",
                    class: "w-full",
                    "onUpdate:modelValue": (v) => emit("filter", { rating: v })
                  }, null, 8, ["model-value", "items", "onUpdate:modelValue"])
                ])
              ])
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              block: "",
              color: "primary",
              onClick: ($event) => isOpen.value = false
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Terapkan Filter `);
                } else {
                  return [
                    createTextVNode(" Terapkan Filter ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              block: "",
              color: "neutral",
              variant: "outline",
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
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex gap-2" }, [
                createVNode(_component_UButton, {
                  block: "",
                  color: "primary",
                  onClick: ($event) => isOpen.value = false
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Terapkan Filter ")
                  ]),
                  _: 1
                }, 8, ["onClick"]),
                createVNode(_component_UButton, {
                  block: "",
                  color: "neutral",
                  variant: "outline",
                  onClick: ($event) => emit("reset")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Reset ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/shop/ShopMobileFilters.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
function resolveProductImage(url) {
  if (!url) return null;
  return url.startsWith("http") ? url : `/storage/${url}`;
}
function transformProduct(p) {
  const rawImage = p.primary_media?.[0]?.url;
  const hasPromo = p.promo_price != null && p.promo_price < p.base_price;
  return {
    ...p,
    price: hasPromo ? Number(p.promo_price) : Number(p.base_price),
    compareAtPrice: hasPromo ? Number(p.base_price) : void 0,
    image: resolveProductImage(rawImage),
    rating: Number(p.avg_rating) || 0,
    reviewCount: p.reviews_count || 0,
    salesCount: Math.floor(Math.random() * 500) + 50,
    badge: hasPromo ? p.promo_label ?? "Promo" : p.id % 3 === 0 ? "Terlaris" : p.id % 5 === 0 ? "Baru" : null
  };
}
function useShopCatalog(props) {
  let _syncing = false;
  const paginatedProducts = ref([...props.products.data]);
  const currentPage = ref(Number(props.products.current_page ?? 1));
  const itemsPerPage = ref(Number(props.products.per_page ?? 12));
  const totalProducts = ref(props.products.total);
  const lastPage = ref(Number(props.products.last_page ?? 1));
  const shownFrom = ref(props.products.from ?? null);
  const shownTo = ref(props.products.to ?? null);
  const isLoading = ref(false);
  const currentFilters = ref({ ...props.filters });
  const search = ref(currentFilters.value.search || "");
  const viewMode = ref("grid");
  const inStockOnly = ref(!!currentFilters.value.in_stock);
  const isFilterDrawerOpen = ref(false);
  const filterStats = props.filterStats;
  const minPrice = computed(() => Number(filterStats.min_price));
  const maxPrice = computed(() => Number(filterStats.max_price));
  const priceRange = ref([
    Number(currentFilters.value.min_price ?? filterStats.min_price),
    Number(currentFilters.value.max_price ?? filterStats.max_price)
  ]);
  const transformedProducts = computed(() => paginatedProducts.value.map(transformProduct));
  const currentSortLabel = computed(() => {
    const labels = {
      newest: "Terbaru",
      price_low: "Harga Terendah",
      price_high: "Harga Tertinggi",
      popular: "Terpopuler",
      rating: "Rating Tertinggi"
    };
    return labels[currentFilters.value.sort || "newest"];
  });
  const activeCategoryLabel = computed(() => {
    if (!currentFilters.value.category) return "Semua Kategori";
    return props.categories.find((c) => c.slug === currentFilters.value.category)?.name || "Semua Kategori";
  });
  const activeBrandLabel = computed(() => {
    if (!currentFilters.value.brand) return null;
    return props.brands?.find((b) => b.slug === currentFilters.value.brand)?.name || null;
  });
  const hasActiveFilters = computed(() => {
    const f = currentFilters.value;
    return !!f.search || !!f.category || !!f.brand || !!f.rating || !!f.in_stock || Number(f.min_price) > minPrice.value || Number(f.max_price) < maxPrice.value;
  });
  const activeFilterCount = computed(() => {
    let count = 0;
    const f = currentFilters.value;
    if (f.search) count++;
    if (f.category) count++;
    if (f.brand) count++;
    if (f.rating) count++;
    if (f.in_stock) count++;
    if (Number(f.min_price) > minPrice.value) count++;
    if (Number(f.max_price) < maxPrice.value) count++;
    return count;
  });
  const ratingItems = computed(() => {
    const base = filterStats.ratings?.length ? filterStats.ratings : [5, 4, 3, 2, 1];
    return [
      { label: "Semua rating", value: void 0 },
      ...base.map((r) => ({ label: `${r}+ bintang`, value: r }))
    ];
  });
  const isActiveCat = (slug) => !currentFilters.value.category && slug === void 0 || currentFilters.value.category === slug;
  const isActiveBrand = (slug) => currentFilters.value.brand === slug;
  function normalizeFilters(f) {
    const out = { ...f };
    if (!out.search) delete out.search;
    if (!out.category) delete out.category;
    if (!out.brand) delete out.brand;
    if (!out.sort) delete out.sort;
    if (Number(out.page ?? 1) <= 1) delete out.page;
    if (out.rating === void 0 || out.rating === null) delete out.rating;
    if (!out.in_stock) delete out.in_stock;
    if (Number(out.min_price) === minPrice.value) delete out.min_price;
    if (Number(out.max_price) === maxPrice.value) delete out.max_price;
    return out;
  }
  function handleFilter(newFilters) {
    const shouldResetPage = !Object.prototype.hasOwnProperty.call(newFilters, "page");
    const merged = normalizeFilters({
      ...currentFilters.value,
      ...newFilters,
      page: shouldResetPage ? 1 : Number(newFilters.page ?? currentFilters.value.page ?? 1)
    });
    isLoading.value = true;
    router.get("/shop", merged, {
      preserveState: true,
      preserveScroll: true,
      only: ["products", "filters"],
      onSuccess: (page) => {
        const p = page.props.products;
        const f = page.props.filters;
        _syncing = true;
        paginatedProducts.value = [...p.data];
        currentPage.value = Number(p.current_page ?? 1);
        itemsPerPage.value = Number(p.per_page ?? itemsPerPage.value);
        totalProducts.value = p.total;
        lastPage.value = Number(p.last_page ?? 1);
        shownFrom.value = p.from ?? null;
        shownTo.value = p.to ?? null;
        currentFilters.value = { ...f };
        search.value = f.search ?? "";
        inStockOnly.value = !!f.in_stock;
        priceRange.value = [
          Number(f.min_price ?? filterStats.min_price),
          Number(f.max_price ?? filterStats.max_price)
        ];
        isLoading.value = false;
        nextTick(() => {
          _syncing = false;
        });
      },
      onFinish: () => {
        isLoading.value = false;
      }
    });
  }
  function resetFilters() {
    handleFilter({
      search: "",
      category: void 0,
      brand: void 0,
      page: 1,
      min_price: filterStats.min_price,
      max_price: filterStats.max_price,
      rating: void 0,
      in_stock: false
    });
  }
  const debouncedSearch = debounce((val) => handleFilter({ search: val, page: 1 }), 450);
  const debouncedPrice = debounce(() => {
    handleFilter({ min_price: priceRange.value[0], max_price: priceRange.value[1], page: 1 });
  }, 650);
  watch(search, (val) => {
    if (_syncing) return;
    debouncedSearch(val);
  });
  watch(priceRange, () => {
    if (_syncing) return;
    debouncedPrice();
  }, { deep: true });
  watch(inStockOnly, (v) => {
    if (_syncing) return;
    handleFilter({ in_stock: !!v, page: 1 });
  });
  const sortOptions = [
    [
      { label: "Terbaru", onSelect: () => handleFilter({ sort: "newest", page: 1 }) },
      { label: "Harga: Terendah", onSelect: () => handleFilter({ sort: "price_low", page: 1 }) },
      { label: "Harga: Tertinggi", onSelect: () => handleFilter({ sort: "price_high", page: 1 }) },
      { label: "Terpopuler", onSelect: () => handleFilter({ sort: "popular", page: 1 }) },
      { label: "Rating Tertinggi", onSelect: () => handleFilter({ sort: "rating", page: 1 }) }
    ]
  ];
  function goToPage(page) {
    const targetPage = Math.min(Math.max(1, page), lastPage.value || 1);
    if (targetPage === currentPage.value || isLoading.value) {
      return;
    }
    handleFilter({ page: targetPage });
  }
  return {
    // Product state
    transformedProducts,
    currentPage,
    itemsPerPage,
    totalProducts,
    lastPage,
    shownFrom,
    shownTo,
    isLoading,
    goToPage,
    // Filter state
    currentFilters,
    search,
    viewMode,
    inStockOnly,
    isFilterDrawerOpen,
    priceRange,
    minPrice,
    maxPrice,
    // Derived
    currentSortLabel,
    activeCategoryLabel,
    activeBrandLabel,
    hasActiveFilters,
    activeFilterCount,
    ratingItems,
    sortOptions,
    // Methods
    handleFilter,
    resetFilters,
    isActiveCat,
    isActiveBrand,
    formatCurrency
  };
}
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$o, inheritAttrs: false },
  __name: "Index",
  __ssrInlineRender: true,
  props: {
    products: {},
    categories: {},
    brands: {},
    filterStats: {},
    filters: {}
  },
  setup(__props) {
    const props = __props;
    const {
      transformedProducts,
      totalProducts,
      currentPage,
      itemsPerPage,
      shownFrom,
      shownTo,
      lastPage,
      isLoading,
      goToPage,
      currentFilters,
      search,
      viewMode,
      inStockOnly,
      isFilterDrawerOpen,
      priceRange,
      minPrice,
      maxPrice,
      currentSortLabel,
      activeCategoryLabel,
      activeBrandLabel,
      hasActiveFilters,
      activeFilterCount,
      ratingItems,
      sortOptions,
      handleFilter,
      resetFilters,
      isActiveCat,
      isActiveBrand
    } = useShopCatalog(props);
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(unref(Head), { title: "Katalog Produk Premium | ogitu.id" }, null, _parent));
      _push(`<div class="min-h-screen bg-gray-50/60 dark:bg-gray-950 transition-colors duration-300">`);
      _push(ssrRenderComponent(_sfc_main$8, {
        "current-filters": unref(currentFilters),
        "active-category-label": unref(activeCategoryLabel),
        "total-products": unref(totalProducts),
        "categories-count": __props.categories.length,
        "has-active-filters": unref(hasActiveFilters),
        "active-filter-count": unref(activeFilterCount)
      }, null, _parent));
      _push(`<div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 mt-10">`);
      _push(ssrRenderComponent(ShopToolbar, {
        search: unref(search),
        "onUpdate:search": ($event) => isRef(search) ? search.value = $event : null,
        "view-mode": unref(viewMode),
        "onUpdate:viewMode": ($event) => isRef(viewMode) ? viewMode.value = $event : null,
        "current-filters": unref(currentFilters),
        "has-active-filters": unref(hasActiveFilters),
        "active-category-label": unref(activeCategoryLabel),
        "active-brand-label": unref(activeBrandLabel),
        "current-sort-label": unref(currentSortLabel),
        "price-range": unref(priceRange),
        "filter-stats": __props.filterStats,
        "sort-options": unref(sortOptions),
        onFilter: unref(handleFilter),
        onReset: unref(resetFilters),
        onOpenMobileFilters: ($event) => isFilterDrawerOpen.value = true
      }, null, _parent));
      _push(`<div class="flex gap-8 lg:gap-10">`);
      _push(ssrRenderComponent(_sfc_main$3, {
        "price-range": unref(priceRange),
        "onUpdate:priceRange": ($event) => isRef(priceRange) ? priceRange.value = $event : null,
        "in-stock-only": unref(inStockOnly),
        "onUpdate:inStockOnly": ($event) => isRef(inStockOnly) ? inStockOnly.value = $event : null,
        categories: __props.categories,
        brands: __props.brands,
        "current-filters": unref(currentFilters),
        "total-products": unref(totalProducts),
        "min-price": unref(minPrice),
        "max-price": unref(maxPrice),
        "has-active-filters": unref(hasActiveFilters),
        "active-filter-count": unref(activeFilterCount),
        "rating-items": unref(ratingItems),
        "is-active-cat": unref(isActiveCat),
        "is-active-brand": unref(isActiveBrand),
        onFilter: unref(handleFilter),
        onReset: unref(resetFilters)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$2, {
        products: unref(transformedProducts),
        "view-mode": unref(viewMode),
        "is-loading": unref(isLoading),
        "current-page": unref(currentPage),
        "items-per-page": unref(itemsPerPage),
        "total-products": unref(totalProducts),
        "shown-from": unref(shownFrom),
        "shown-to": unref(shownTo),
        "last-page": unref(lastPage),
        onPageChange: unref(goToPage),
        onResetFilters: unref(resetFilters)
      }, null, _parent));
      _push(`</div></div>`);
      _push(ssrRenderComponent(_sfc_main$1, {
        open: unref(isFilterDrawerOpen),
        "onUpdate:open": ($event) => isRef(isFilterDrawerOpen) ? isFilterDrawerOpen.value = $event : null,
        "price-range": unref(priceRange),
        "onUpdate:priceRange": ($event) => isRef(priceRange) ? priceRange.value = $event : null,
        "in-stock-only": unref(inStockOnly),
        "onUpdate:inStockOnly": ($event) => isRef(inStockOnly) ? inStockOnly.value = $event : null,
        categories: __props.categories,
        brands: __props.brands,
        "current-filters": unref(currentFilters),
        "min-price": unref(minPrice),
        "max-price": unref(maxPrice),
        "rating-items": unref(ratingItems),
        "is-active-cat": unref(isActiveCat),
        "is-active-brand": unref(isActiveBrand),
        onFilter: unref(handleFilter),
        onReset: unref(resetFilters)
      }, null, _parent));
      _push(`</div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Shop/Index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
