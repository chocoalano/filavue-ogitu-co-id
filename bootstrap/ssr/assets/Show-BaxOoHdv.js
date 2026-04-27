import { defineComponent, mergeProps, withCtx, createTextVNode, toDisplayString, createVNode, openBlock, createBlock, createCommentVNode, useSSRContext, unref, Fragment, renderList, ref, watch, computed, provide, inject, useModel, isRef } from "vue";
import { ssrRenderComponent, ssrRenderAttr, ssrInterpolate, ssrRenderClass, ssrRenderAttrs, ssrRenderList, ssrIncludeBooleanAttr } from "vue/server-renderer";
import { InfiniteScroll, Link, router, Head } from "@inertiajs/vue3";
import { b as _export_sfc, a as _sfc_main$k } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$e } from "./Carousel-MVkbrILq.js";
import { _ as _sfc_main$d } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$c } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$b } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$f } from "./Breadcrumb-EXGzoFVp.js";
import { s as starsArray, u as useProductDetail, f as formatCurrency } from "./useProductDetail-C2nZxJqq.js";
import { _ as _sfc_main$i } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$h } from "./Separator-CuyFd3xv.js";
import { _ as _sfc_main$g } from "./Tabs-C3xWVm3k.js";
import { P as ProductCard } from "./ProductCard-CYhvI4HJ.js";
import { _ as _sfc_main$j } from "./SelectMenu-D4PFVS1f.js";
import { u as useToast } from "./useToast-CTuSIf9f.js";
import { u as useStoreData } from "./useStoreData-DrTMI0On.js";
import "reka-ui";
import "@vueuse/core";
import "./usePortal-EQErrF6h.js";
import "./Input-DFvIE7JC.js";
import "defu";
import "reka-ui/namespaced";
import "./Checkbox-PZ4-MUog.js";
import "vaul-vue";
import "embla-carousel-vue";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "ufo";
const _sfc_main$a = /* @__PURE__ */ defineComponent({
  __name: "ProductGallery",
  __ssrInlineRender: true,
  props: {
    items: {},
    activeIndex: {},
    discountPercent: {}
  },
  emits: ["update:activeIndex"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function selectImage(index) {
      emit("update:activeIndex", index);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$b;
      const _component_UIcon = _sfc_main$c;
      const _component_UBadge = _sfc_main$d;
      const _component_UCarousel = _sfc_main$e;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "overflow-hidden bg-primary-50 dark:bg-primary-950/40",
        ui: { body: "p-0" }
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="relative aspect-4/3 bg-gray-100 dark:bg-gray-900/40"${_scopeId}>`);
            if (__props.items[__props.activeIndex]) {
              _push2(`<img${ssrRenderAttr("src", __props.items[__props.activeIndex].src)}${ssrRenderAttr("alt", __props.items[__props.activeIndex].alt)} class="h-full w-full object-cover rounded-xl"${_scopeId}>`);
            } else {
              _push2(`<div class="flex h-full w-full items-center justify-center"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-image",
                class: "size-10 text-gray-300 dark:text-gray-700"
              }, null, _parent2, _scopeId));
              _push2(`</div>`);
            }
            _push2(`<div class="absolute left-3 top-3 flex gap-2"${_scopeId}>`);
            if (__props.discountPercent) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "warning",
                variant: "soft"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Hemat ${ssrInterpolate(__props.discountPercent)}% `);
                  } else {
                    return [
                      createTextVNode(" Hemat " + toDisplayString(__props.discountPercent) + "% ", 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div><div class="p-4"${_scopeId}>`);
            if (__props.items.length) {
              _push2(ssrRenderComponent(_component_UCarousel, {
                items: __props.items,
                ui: {
                  container: "flex-row gap-3 ms-0",
                  item: "min-w-0 shrink-0 basis-auto ps-0"
                },
                class: "w-full"
              }, {
                default: withCtx(({ item, index }, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<button type="button" class="${ssrRenderClass([index === __props.activeIndex ? "border-primary-500 ring-2 ring-primary-500/30" : "border-gray-200 dark:border-gray-800", "h-20 w-20 shrink-0 overflow-hidden rounded-xl border transition-all"])}"${_scopeId2}><img${ssrRenderAttr("src", item.src)}${ssrRenderAttr("alt", item.alt)} class="h-full w-full object-cover"${_scopeId2}></button>`);
                  } else {
                    return [
                      createVNode("button", {
                        type: "button",
                        class: ["h-20 w-20 shrink-0 overflow-hidden rounded-xl border transition-all", index === __props.activeIndex ? "border-primary-500 ring-2 ring-primary-500/30" : "border-gray-200 dark:border-gray-800"],
                        onClick: ($event) => selectImage(index)
                      }, [
                        createVNode("img", {
                          src: item.src,
                          alt: item.alt,
                          class: "h-full w-full object-cover"
                        }, null, 8, ["src", "alt"])
                      ], 10, ["onClick"])
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
              createVNode("div", { class: "relative aspect-4/3 bg-gray-100 dark:bg-gray-900/40" }, [
                __props.items[__props.activeIndex] ? (openBlock(), createBlock("img", {
                  key: 0,
                  src: __props.items[__props.activeIndex].src,
                  alt: __props.items[__props.activeIndex].alt,
                  class: "h-full w-full object-cover rounded-xl"
                }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                  key: 1,
                  class: "flex h-full w-full items-center justify-center"
                }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-image",
                    class: "size-10 text-gray-300 dark:text-gray-700"
                  })
                ])),
                createVNode("div", { class: "absolute left-3 top-3 flex gap-2" }, [
                  __props.discountPercent ? (openBlock(), createBlock(_component_UBadge, {
                    key: 0,
                    color: "warning",
                    variant: "soft"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Hemat " + toDisplayString(__props.discountPercent) + "% ", 1)
                    ]),
                    _: 1
                  })) : createCommentVNode("", true)
                ])
              ]),
              createVNode("div", { class: "p-4" }, [
                __props.items.length ? (openBlock(), createBlock(_component_UCarousel, {
                  key: 0,
                  items: __props.items,
                  ui: {
                    container: "flex-row gap-3 ms-0",
                    item: "min-w-0 shrink-0 basis-auto ps-0"
                  },
                  class: "w-full"
                }, {
                  default: withCtx(({ item, index }) => [
                    createVNode("button", {
                      type: "button",
                      class: ["h-20 w-20 shrink-0 overflow-hidden rounded-xl border transition-all", index === __props.activeIndex ? "border-primary-500 ring-2 ring-primary-500/30" : "border-gray-200 dark:border-gray-800"],
                      onClick: ($event) => selectImage(index)
                    }, [
                      createVNode("img", {
                        src: item.src,
                        alt: item.alt,
                        class: "h-full w-full object-cover"
                      }, null, 8, ["src", "alt"])
                    ], 10, ["onClick"])
                  ]),
                  _: 1
                }, 8, ["items"])) : createCommentVNode("", true)
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$a = _sfc_main$a.setup;
_sfc_main$a.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductGallery.vue");
  return _sfc_setup$a ? _sfc_setup$a(props, ctx) : void 0;
};
const _sfc_main$9 = /* @__PURE__ */ defineComponent({
  __name: "ProductHeader",
  __ssrInlineRender: true,
  props: {
    product: {},
    avgRating: {},
    reviewCount: {},
    inStock: { type: Boolean }
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UBreadcrumb = _sfc_main$f;
      const _component_UBadge = _sfc_main$d;
      const _component_UIcon = _sfc_main$c;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "mb-6 py-8 lg:py-10" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UBreadcrumb, {
        items: [
          { label: "Home", icon: "i-lucide-home", to: "/" },
          { label: "Katalog", to: "/shop" },
          { label: __props.product.name }
        ]
      }, null, _parent));
      _push(`<div class="mt-4 min-w-0"><h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">${ssrInterpolate(__props.product.name)}</h1>`);
      if (__props.product.shortDescription) {
        _push(`<div class="mt-2 text-sm text-gray-600 dark:text-gray-400">${__props.product.shortDescription ?? ""}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="mt-3 flex flex-wrap items-center gap-2">`);
      if (__props.product.brand) {
        _push(ssrRenderComponent(_component_UBadge, {
          color: "neutral",
          variant: "subtle"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-tag",
                class: "mr-1 size-4"
              }, null, _parent2, _scopeId));
              _push2(` ${ssrInterpolate(__props.product.brand)}`);
            } else {
              return [
                createVNode(_component_UIcon, {
                  name: "i-lucide-tag",
                  class: "mr-1 size-4"
                }),
                createTextVNode(" " + toDisplayString(__props.product.brand), 1)
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300"><!--[-->`);
      ssrRenderList(unref(starsArray)(__props.avgRating), (filled, i) => {
        _push(ssrRenderComponent(_component_UIcon, {
          key: i,
          name: "i-lucide-star",
          class: ["size-4", filled ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
        }, null, _parent));
      });
      _push(`<!--]--><span class="font-semibold">${ssrInterpolate(__props.avgRating.toFixed(1))}</span><span class="text-gray-500 dark:text-gray-400">(${ssrInterpolate(__props.reviewCount)} ulasan)</span></div>`);
      if (!__props.inStock) {
        _push(ssrRenderComponent(_component_UBadge, {
          color: "error",
          variant: "soft"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`Stok habis`);
            } else {
              return [
                createTextVNode("Stok habis")
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(ssrRenderComponent(_component_UBadge, {
          color: "success",
          variant: "soft"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`Stok tersedia`);
            } else {
              return [
                createTextVNode("Stok tersedia")
              ];
            }
          }),
          _: 1
        }, _parent));
      }
      _push(`</div></div></div>`);
    };
  }
});
const _sfc_setup$9 = _sfc_main$9.setup;
_sfc_main$9.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductHeader.vue");
  return _sfc_setup$9 ? _sfc_setup$9(props, ctx) : void 0;
};
const _sfc_main$8 = /* @__PURE__ */ defineComponent({
  __name: "ProductInfoTabs",
  __ssrInlineRender: true,
  props: {
    description: {},
    highlights: {},
    specs: {},
    reviews: {},
    avgRating: {},
    reviewCount: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UTabs = _sfc_main$g;
      const _component_UCard = _sfc_main$b;
      const _component_UIcon = _sfc_main$c;
      const _component_USeparator = _sfc_main$h;
      const _component_UBadge = _sfc_main$d;
      const _component_UButton = _sfc_main$i;
      _push(ssrRenderComponent(_component_UTabs, mergeProps({ items: [
        { label: "Deskripsi", slot: "desc" },
        { label: "Spesifikasi", slot: "spec" },
        { label: "Ulasan", slot: "review" }
      ] }, _attrs), {
        desc: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UCard, {
              class: "mt-4 bg-primary-50 dark:bg-primary-950/40",
              ui: { body: "p-5" }
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="prose prose-sm max-w-none dark:prose-invert"${_scopeId2}>`);
                  if (__props.description) {
                    _push3(`<p${_scopeId2}>${__props.description ?? ""}</p>`);
                  } else {
                    _push3(`<p class="text-gray-600 dark:text-gray-400"${_scopeId2}> Belum ada deskripsi untuk produk ini. </p>`);
                  }
                  _push3(`</div>`);
                  if (__props.highlights?.length) {
                    _push3(`<div class="mt-5"${_scopeId2}><div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400"${_scopeId2}> Highlight </div><ul class="mt-3 grid gap-2 sm:grid-cols-2"${_scopeId2}><!--[-->`);
                    ssrRenderList(__props.highlights, (h, i) => {
                      _push3(`<li class="flex items-start gap-2 rounded-xl bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-900/40 dark:text-gray-300"${_scopeId2}>`);
                      _push3(ssrRenderComponent(_component_UIcon, {
                        name: "i-lucide-check-circle-2",
                        class: "mt-0.5 size-4 text-primary-500"
                      }, null, _parent3, _scopeId2));
                      _push3(`<span class="min-w-0"${_scopeId2}>${ssrInterpolate(h)}</span></li>`);
                    });
                    _push3(`<!--]--></ul></div>`);
                  } else {
                    _push3(`<!---->`);
                  }
                } else {
                  return [
                    createVNode("div", { class: "prose prose-sm max-w-none dark:prose-invert" }, [
                      __props.description ? (openBlock(), createBlock("p", {
                        key: 0,
                        innerHTML: __props.description
                      }, null, 8, ["innerHTML"])) : (openBlock(), createBlock("p", {
                        key: 1,
                        class: "text-gray-600 dark:text-gray-400"
                      }, " Belum ada deskripsi untuk produk ini. "))
                    ]),
                    __props.highlights?.length ? (openBlock(), createBlock("div", {
                      key: 0,
                      class: "mt-5"
                    }, [
                      createVNode("div", { class: "text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, " Highlight "),
                      createVNode("ul", { class: "mt-3 grid gap-2 sm:grid-cols-2" }, [
                        (openBlock(true), createBlock(Fragment, null, renderList(__props.highlights, (h, i) => {
                          return openBlock(), createBlock("li", {
                            key: i,
                            class: "flex items-start gap-2 rounded-xl bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-900/40 dark:text-gray-300"
                          }, [
                            createVNode(_component_UIcon, {
                              name: "i-lucide-check-circle-2",
                              class: "mt-0.5 size-4 text-primary-500"
                            }),
                            createVNode("span", { class: "min-w-0" }, toDisplayString(h), 1)
                          ]);
                        }), 128))
                      ])
                    ])) : createCommentVNode("", true)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UCard, {
                class: "mt-4 bg-primary-50 dark:bg-primary-950/40",
                ui: { body: "p-5" }
              }, {
                default: withCtx(() => [
                  createVNode("div", { class: "prose prose-sm max-w-none dark:prose-invert" }, [
                    __props.description ? (openBlock(), createBlock("p", {
                      key: 0,
                      innerHTML: __props.description
                    }, null, 8, ["innerHTML"])) : (openBlock(), createBlock("p", {
                      key: 1,
                      class: "text-gray-600 dark:text-gray-400"
                    }, " Belum ada deskripsi untuk produk ini. "))
                  ]),
                  __props.highlights?.length ? (openBlock(), createBlock("div", {
                    key: 0,
                    class: "mt-5"
                  }, [
                    createVNode("div", { class: "text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, " Highlight "),
                    createVNode("ul", { class: "mt-3 grid gap-2 sm:grid-cols-2" }, [
                      (openBlock(true), createBlock(Fragment, null, renderList(__props.highlights, (h, i) => {
                        return openBlock(), createBlock("li", {
                          key: i,
                          class: "flex items-start gap-2 rounded-xl bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-900/40 dark:text-gray-300"
                        }, [
                          createVNode(_component_UIcon, {
                            name: "i-lucide-check-circle-2",
                            class: "mt-0.5 size-4 text-primary-500"
                          }),
                          createVNode("span", { class: "min-w-0" }, toDisplayString(h), 1)
                        ]);
                      }), 128))
                    ])
                  ])) : createCommentVNode("", true)
                ]),
                _: 1
              })
            ];
          }
        }),
        spec: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UCard, {
              class: "mt-4 bg-primary-50 dark:bg-primary-950/40",
              ui: { body: "p-5" }
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400"${_scopeId2}> Spesifikasi </div><div class="mt-4 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-950/40"${_scopeId2}><!--[-->`);
                  ssrRenderList(__props.specs ?? [], (s, i) => {
                    _push3(`<div class="flex items-center justify-between gap-4 p-4"${_scopeId2}><div class="text-sm font-semibold text-gray-700 dark:text-gray-300"${_scopeId2}>${ssrInterpolate(s.label)}</div><div class="text-sm text-gray-600 dark:text-gray-400"${_scopeId2}>${ssrInterpolate(s.value)}</div></div>`);
                  });
                  _push3(`<!--]-->`);
                  if (!__props.specs?.length) {
                    _push3(`<div class="p-4 text-sm text-gray-600 dark:text-gray-400"${_scopeId2}> Belum ada spesifikasi untuk produk ini. </div>`);
                  } else {
                    _push3(`<!---->`);
                  }
                  _push3(`</div>`);
                } else {
                  return [
                    createVNode("div", { class: "text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, " Spesifikasi "),
                    createVNode("div", { class: "mt-4 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-950/40" }, [
                      (openBlock(true), createBlock(Fragment, null, renderList(__props.specs ?? [], (s, i) => {
                        return openBlock(), createBlock("div", {
                          key: i,
                          class: "flex items-center justify-between gap-4 p-4"
                        }, [
                          createVNode("div", { class: "text-sm font-semibold text-gray-700 dark:text-gray-300" }, toDisplayString(s.label), 1),
                          createVNode("div", { class: "text-sm text-gray-600 dark:text-gray-400" }, toDisplayString(s.value), 1)
                        ]);
                      }), 128)),
                      !__props.specs?.length ? (openBlock(), createBlock("div", {
                        key: 0,
                        class: "p-4 text-sm text-gray-600 dark:text-gray-400"
                      }, " Belum ada spesifikasi untuk produk ini. ")) : createCommentVNode("", true)
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UCard, {
                class: "mt-4 bg-primary-50 dark:bg-primary-950/40",
                ui: { body: "p-5" }
              }, {
                default: withCtx(() => [
                  createVNode("div", { class: "text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, " Spesifikasi "),
                  createVNode("div", { class: "mt-4 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-950/40" }, [
                    (openBlock(true), createBlock(Fragment, null, renderList(__props.specs ?? [], (s, i) => {
                      return openBlock(), createBlock("div", {
                        key: i,
                        class: "flex items-center justify-between gap-4 p-4"
                      }, [
                        createVNode("div", { class: "text-sm font-semibold text-gray-700 dark:text-gray-300" }, toDisplayString(s.label), 1),
                        createVNode("div", { class: "text-sm text-gray-600 dark:text-gray-400" }, toDisplayString(s.value), 1)
                      ]);
                    }), 128)),
                    !__props.specs?.length ? (openBlock(), createBlock("div", {
                      key: 0,
                      class: "p-4 text-sm text-gray-600 dark:text-gray-400"
                    }, " Belum ada spesifikasi untuk produk ini. ")) : createCommentVNode("", true)
                  ])
                ]),
                _: 1
              })
            ];
          }
        }),
        review: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UCard, {
              class: "mt-4 bg-primary-50 dark:bg-primary-950/40",
              ui: { body: "p-5" }
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"${_scopeId2}><div${_scopeId2}><div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400"${_scopeId2}> Ulasan pelanggan </div><div class="mt-2 flex items-center gap-2"${_scopeId2}><div class="flex items-center gap-1"${_scopeId2}><!--[-->`);
                  ssrRenderList(unref(starsArray)(__props.avgRating), (filled, i) => {
                    _push3(ssrRenderComponent(_component_UIcon, {
                      key: i,
                      name: "i-lucide-star",
                      class: ["size-4", filled ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
                    }, null, _parent3, _scopeId2));
                  });
                  _push3(`<!--]--></div><div class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId2}>${ssrInterpolate(__props.avgRating.toFixed(1))} / 5 </div><div class="text-sm text-gray-500 dark:text-gray-400"${_scopeId2}> (${ssrInterpolate(__props.reviewCount)} ulasan) </div></div></div></div>`);
                  _push3(ssrRenderComponent(_component_USeparator, { class: "my-5" }, null, _parent3, _scopeId2));
                  _push3(ssrRenderComponent(unref(InfiniteScroll), {
                    data: "reviews",
                    buffer: 320,
                    as: "div"
                  }, {
                    loading: withCtx(({ loadingNext }, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        if (loadingNext) {
                          _push4(`<div class="mt-4 flex items-center justify-center text-xs text-muted"${_scopeId3}> Memuat ulasan berikutnya... </div>`);
                        } else {
                          _push4(`<!---->`);
                        }
                      } else {
                        return [
                          loadingNext ? (openBlock(), createBlock("div", {
                            key: 0,
                            class: "mt-4 flex items-center justify-center text-xs text-muted"
                          }, " Memuat ulasan berikutnya... ")) : createCommentVNode("", true)
                        ];
                      }
                    }),
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        if (!__props.reviews?.data?.length) {
                          _push4(`<div class="rounded-2xl border border-default bg-default p-4 text-sm text-muted"${_scopeId3}> Belum ada ulasan yang disetujui untuk produk ini. </div>`);
                        } else {
                          _push4(`<div class="space-y-4"${_scopeId3}><!--[-->`);
                          ssrRenderList(__props.reviews?.data ?? [], (r) => {
                            _push4(ssrRenderComponent(_component_UCard, {
                              key: r.id,
                              class: "rounded-2xl",
                              ui: { body: "p-4" }
                            }, {
                              default: withCtx((_4, _push5, _parent5, _scopeId4) => {
                                if (_push5) {
                                  _push5(`<div class="flex items-start justify-between gap-3"${_scopeId4}><div${_scopeId4}><div class="flex items-center gap-2"${_scopeId4}><div class="text-sm font-bold text-gray-900 dark:text-white"${_scopeId4}>${ssrInterpolate(r.name)}</div>`);
                                  if (r.verified) {
                                    _push5(ssrRenderComponent(_component_UBadge, {
                                      color: "success",
                                      variant: "soft",
                                      size: "xs"
                                    }, {
                                      default: withCtx((_5, _push6, _parent6, _scopeId5) => {
                                        if (_push6) {
                                          _push6(` Terverifikasi `);
                                        } else {
                                          return [
                                            createTextVNode(" Terverifikasi ")
                                          ];
                                        }
                                      }),
                                      _: 2
                                    }, _parent5, _scopeId4));
                                  } else {
                                    _push5(`<!---->`);
                                  }
                                  _push5(`</div><div class="mt-1 flex items-center gap-1"${_scopeId4}><!--[-->`);
                                  ssrRenderList(5, (i) => {
                                    _push5(ssrRenderComponent(_component_UIcon, {
                                      key: i,
                                      name: "i-lucide-star",
                                      class: ["size-4", i <= r.rating ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
                                    }, null, _parent5, _scopeId4));
                                  });
                                  _push5(`<!--]--><span class="ml-2 text-xs text-gray-500 dark:text-gray-400"${_scopeId4}>${ssrInterpolate(r.date)}</span></div>`);
                                  if (r.title) {
                                    _push5(`<div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white"${_scopeId4}>${ssrInterpolate(r.title)}</div>`);
                                  } else {
                                    _push5(`<!---->`);
                                  }
                                  _push5(`<p class="mt-2 text-sm text-gray-600 dark:text-gray-400"${_scopeId4}>${ssrInterpolate(r.body)}</p></div>`);
                                  _push5(ssrRenderComponent(_component_UButton, {
                                    icon: "i-lucide-more-vertical",
                                    color: "neutral",
                                    variant: "ghost",
                                    size: "xs"
                                  }, null, _parent5, _scopeId4));
                                  _push5(`</div>`);
                                } else {
                                  return [
                                    createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                                      createVNode("div", null, [
                                        createVNode("div", { class: "flex items-center gap-2" }, [
                                          createVNode("div", { class: "text-sm font-bold text-gray-900 dark:text-white" }, toDisplayString(r.name), 1),
                                          r.verified ? (openBlock(), createBlock(_component_UBadge, {
                                            key: 0,
                                            color: "success",
                                            variant: "soft",
                                            size: "xs"
                                          }, {
                                            default: withCtx(() => [
                                              createTextVNode(" Terverifikasi ")
                                            ]),
                                            _: 1
                                          })) : createCommentVNode("", true)
                                        ]),
                                        createVNode("div", { class: "mt-1 flex items-center gap-1" }, [
                                          (openBlock(), createBlock(Fragment, null, renderList(5, (i) => {
                                            return createVNode(_component_UIcon, {
                                              key: i,
                                              name: "i-lucide-star",
                                              class: ["size-4", i <= r.rating ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
                                            }, null, 8, ["class"]);
                                          }), 64)),
                                          createVNode("span", { class: "ml-2 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(r.date), 1)
                                        ]),
                                        r.title ? (openBlock(), createBlock("div", {
                                          key: 0,
                                          class: "mt-2 text-sm font-semibold text-gray-900 dark:text-white"
                                        }, toDisplayString(r.title), 1)) : createCommentVNode("", true),
                                        createVNode("p", { class: "mt-2 text-sm text-gray-600 dark:text-gray-400" }, toDisplayString(r.body), 1)
                                      ]),
                                      createVNode(_component_UButton, {
                                        icon: "i-lucide-more-vertical",
                                        color: "neutral",
                                        variant: "ghost",
                                        size: "xs"
                                      })
                                    ])
                                  ];
                                }
                              }),
                              _: 2
                            }, _parent4, _scopeId3));
                          });
                          _push4(`<!--]--></div>`);
                        }
                      } else {
                        return [
                          !__props.reviews?.data?.length ? (openBlock(), createBlock("div", {
                            key: 0,
                            class: "rounded-2xl border border-default bg-default p-4 text-sm text-muted"
                          }, " Belum ada ulasan yang disetujui untuk produk ini. ")) : (openBlock(), createBlock("div", {
                            key: 1,
                            class: "space-y-4"
                          }, [
                            (openBlock(true), createBlock(Fragment, null, renderList(__props.reviews?.data ?? [], (r) => {
                              return openBlock(), createBlock(_component_UCard, {
                                key: r.id,
                                class: "rounded-2xl",
                                ui: { body: "p-4" }
                              }, {
                                default: withCtx(() => [
                                  createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                                    createVNode("div", null, [
                                      createVNode("div", { class: "flex items-center gap-2" }, [
                                        createVNode("div", { class: "text-sm font-bold text-gray-900 dark:text-white" }, toDisplayString(r.name), 1),
                                        r.verified ? (openBlock(), createBlock(_component_UBadge, {
                                          key: 0,
                                          color: "success",
                                          variant: "soft",
                                          size: "xs"
                                        }, {
                                          default: withCtx(() => [
                                            createTextVNode(" Terverifikasi ")
                                          ]),
                                          _: 1
                                        })) : createCommentVNode("", true)
                                      ]),
                                      createVNode("div", { class: "mt-1 flex items-center gap-1" }, [
                                        (openBlock(), createBlock(Fragment, null, renderList(5, (i) => {
                                          return createVNode(_component_UIcon, {
                                            key: i,
                                            name: "i-lucide-star",
                                            class: ["size-4", i <= r.rating ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
                                          }, null, 8, ["class"]);
                                        }), 64)),
                                        createVNode("span", { class: "ml-2 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(r.date), 1)
                                      ]),
                                      r.title ? (openBlock(), createBlock("div", {
                                        key: 0,
                                        class: "mt-2 text-sm font-semibold text-gray-900 dark:text-white"
                                      }, toDisplayString(r.title), 1)) : createCommentVNode("", true),
                                      createVNode("p", { class: "mt-2 text-sm text-gray-600 dark:text-gray-400" }, toDisplayString(r.body), 1)
                                    ]),
                                    createVNode(_component_UButton, {
                                      icon: "i-lucide-more-vertical",
                                      color: "neutral",
                                      variant: "ghost",
                                      size: "xs"
                                    })
                                  ])
                                ]),
                                _: 2
                              }, 1024);
                            }), 128))
                          ]))
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" }, [
                      createVNode("div", null, [
                        createVNode("div", { class: "text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, " Ulasan pelanggan "),
                        createVNode("div", { class: "mt-2 flex items-center gap-2" }, [
                          createVNode("div", { class: "flex items-center gap-1" }, [
                            (openBlock(true), createBlock(Fragment, null, renderList(unref(starsArray)(__props.avgRating), (filled, i) => {
                              return openBlock(), createBlock(_component_UIcon, {
                                key: i,
                                name: "i-lucide-star",
                                class: ["size-4", filled ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
                              }, null, 8, ["class"]);
                            }), 128))
                          ]),
                          createVNode("div", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.avgRating.toFixed(1)) + " / 5 ", 1),
                          createVNode("div", { class: "text-sm text-gray-500 dark:text-gray-400" }, " (" + toDisplayString(__props.reviewCount) + " ulasan) ", 1)
                        ])
                      ])
                    ]),
                    createVNode(_component_USeparator, { class: "my-5" }),
                    createVNode(unref(InfiniteScroll), {
                      data: "reviews",
                      buffer: 320,
                      as: "div"
                    }, {
                      loading: withCtx(({ loadingNext }) => [
                        loadingNext ? (openBlock(), createBlock("div", {
                          key: 0,
                          class: "mt-4 flex items-center justify-center text-xs text-muted"
                        }, " Memuat ulasan berikutnya... ")) : createCommentVNode("", true)
                      ]),
                      default: withCtx(() => [
                        !__props.reviews?.data?.length ? (openBlock(), createBlock("div", {
                          key: 0,
                          class: "rounded-2xl border border-default bg-default p-4 text-sm text-muted"
                        }, " Belum ada ulasan yang disetujui untuk produk ini. ")) : (openBlock(), createBlock("div", {
                          key: 1,
                          class: "space-y-4"
                        }, [
                          (openBlock(true), createBlock(Fragment, null, renderList(__props.reviews?.data ?? [], (r) => {
                            return openBlock(), createBlock(_component_UCard, {
                              key: r.id,
                              class: "rounded-2xl",
                              ui: { body: "p-4" }
                            }, {
                              default: withCtx(() => [
                                createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                                  createVNode("div", null, [
                                    createVNode("div", { class: "flex items-center gap-2" }, [
                                      createVNode("div", { class: "text-sm font-bold text-gray-900 dark:text-white" }, toDisplayString(r.name), 1),
                                      r.verified ? (openBlock(), createBlock(_component_UBadge, {
                                        key: 0,
                                        color: "success",
                                        variant: "soft",
                                        size: "xs"
                                      }, {
                                        default: withCtx(() => [
                                          createTextVNode(" Terverifikasi ")
                                        ]),
                                        _: 1
                                      })) : createCommentVNode("", true)
                                    ]),
                                    createVNode("div", { class: "mt-1 flex items-center gap-1" }, [
                                      (openBlock(), createBlock(Fragment, null, renderList(5, (i) => {
                                        return createVNode(_component_UIcon, {
                                          key: i,
                                          name: "i-lucide-star",
                                          class: ["size-4", i <= r.rating ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
                                        }, null, 8, ["class"]);
                                      }), 64)),
                                      createVNode("span", { class: "ml-2 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(r.date), 1)
                                    ]),
                                    r.title ? (openBlock(), createBlock("div", {
                                      key: 0,
                                      class: "mt-2 text-sm font-semibold text-gray-900 dark:text-white"
                                    }, toDisplayString(r.title), 1)) : createCommentVNode("", true),
                                    createVNode("p", { class: "mt-2 text-sm text-gray-600 dark:text-gray-400" }, toDisplayString(r.body), 1)
                                  ]),
                                  createVNode(_component_UButton, {
                                    icon: "i-lucide-more-vertical",
                                    color: "neutral",
                                    variant: "ghost",
                                    size: "xs"
                                  })
                                ])
                              ]),
                              _: 2
                            }, 1024);
                          }), 128))
                        ]))
                      ]),
                      _: 1
                    })
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UCard, {
                class: "mt-4 bg-primary-50 dark:bg-primary-950/40",
                ui: { body: "p-5" }
              }, {
                default: withCtx(() => [
                  createVNode("div", { class: "flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" }, [
                    createVNode("div", null, [
                      createVNode("div", { class: "text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, " Ulasan pelanggan "),
                      createVNode("div", { class: "mt-2 flex items-center gap-2" }, [
                        createVNode("div", { class: "flex items-center gap-1" }, [
                          (openBlock(true), createBlock(Fragment, null, renderList(unref(starsArray)(__props.avgRating), (filled, i) => {
                            return openBlock(), createBlock(_component_UIcon, {
                              key: i,
                              name: "i-lucide-star",
                              class: ["size-4", filled ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
                            }, null, 8, ["class"]);
                          }), 128))
                        ]),
                        createVNode("div", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.avgRating.toFixed(1)) + " / 5 ", 1),
                        createVNode("div", { class: "text-sm text-gray-500 dark:text-gray-400" }, " (" + toDisplayString(__props.reviewCount) + " ulasan) ", 1)
                      ])
                    ])
                  ]),
                  createVNode(_component_USeparator, { class: "my-5" }),
                  createVNode(unref(InfiniteScroll), {
                    data: "reviews",
                    buffer: 320,
                    as: "div"
                  }, {
                    loading: withCtx(({ loadingNext }) => [
                      loadingNext ? (openBlock(), createBlock("div", {
                        key: 0,
                        class: "mt-4 flex items-center justify-center text-xs text-muted"
                      }, " Memuat ulasan berikutnya... ")) : createCommentVNode("", true)
                    ]),
                    default: withCtx(() => [
                      !__props.reviews?.data?.length ? (openBlock(), createBlock("div", {
                        key: 0,
                        class: "rounded-2xl border border-default bg-default p-4 text-sm text-muted"
                      }, " Belum ada ulasan yang disetujui untuk produk ini. ")) : (openBlock(), createBlock("div", {
                        key: 1,
                        class: "space-y-4"
                      }, [
                        (openBlock(true), createBlock(Fragment, null, renderList(__props.reviews?.data ?? [], (r) => {
                          return openBlock(), createBlock(_component_UCard, {
                            key: r.id,
                            class: "rounded-2xl",
                            ui: { body: "p-4" }
                          }, {
                            default: withCtx(() => [
                              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                                createVNode("div", null, [
                                  createVNode("div", { class: "flex items-center gap-2" }, [
                                    createVNode("div", { class: "text-sm font-bold text-gray-900 dark:text-white" }, toDisplayString(r.name), 1),
                                    r.verified ? (openBlock(), createBlock(_component_UBadge, {
                                      key: 0,
                                      color: "success",
                                      variant: "soft",
                                      size: "xs"
                                    }, {
                                      default: withCtx(() => [
                                        createTextVNode(" Terverifikasi ")
                                      ]),
                                      _: 1
                                    })) : createCommentVNode("", true)
                                  ]),
                                  createVNode("div", { class: "mt-1 flex items-center gap-1" }, [
                                    (openBlock(), createBlock(Fragment, null, renderList(5, (i) => {
                                      return createVNode(_component_UIcon, {
                                        key: i,
                                        name: "i-lucide-star",
                                        class: ["size-4", i <= r.rating ? "text-amber-400" : "text-gray-300 dark:text-gray-700"]
                                      }, null, 8, ["class"]);
                                    }), 64)),
                                    createVNode("span", { class: "ml-2 text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(r.date), 1)
                                  ]),
                                  r.title ? (openBlock(), createBlock("div", {
                                    key: 0,
                                    class: "mt-2 text-sm font-semibold text-gray-900 dark:text-white"
                                  }, toDisplayString(r.title), 1)) : createCommentVNode("", true),
                                  createVNode("p", { class: "mt-2 text-sm text-gray-600 dark:text-gray-400" }, toDisplayString(r.body), 1)
                                ]),
                                createVNode(_component_UButton, {
                                  icon: "i-lucide-more-vertical",
                                  color: "neutral",
                                  variant: "ghost",
                                  size: "xs"
                                })
                              ])
                            ]),
                            _: 2
                          }, 1024);
                        }), 128))
                      ]))
                    ]),
                    _: 1
                  })
                ]),
                _: 1
              })
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$8 = _sfc_main$8.setup;
_sfc_main$8.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductInfoTabs.vue");
  return _sfc_setup$8 ? _sfc_setup$8(props, ctx) : void 0;
};
const _sfc_main$7 = /* @__PURE__ */ defineComponent({
  __name: "ProductQuickSpecs",
  __ssrInlineRender: true,
  props: {
    avgRating: {},
    reviewCount: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$b;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        ui: { body: "p-5" },
        class: "bg-primary-50 dark:bg-primary-950/40"
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400"${_scopeId}> Ringkasan </div><div class="mt-4 grid gap-2"${_scopeId}><div class="flex items-center justify-between gap-3 text-sm"${_scopeId}><span class="text-gray-600 dark:text-gray-400"${_scopeId}>Ulasan</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.reviewCount)}</span></div><div class="flex items-center justify-between gap-3 text-sm"${_scopeId}><span class="text-gray-600 dark:text-gray-400"${_scopeId}>Rating</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.avgRating.toFixed(1))}</span></div></div>`);
          } else {
            return [
              createVNode("div", { class: "text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, " Ringkasan "),
              createVNode("div", { class: "mt-4 grid gap-2" }, [
                createVNode("div", { class: "flex items-center justify-between gap-3 text-sm" }, [
                  createVNode("span", { class: "text-gray-600 dark:text-gray-400" }, "Ulasan"),
                  createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.reviewCount), 1)
                ]),
                createVNode("div", { class: "flex items-center justify-between gap-3 text-sm" }, [
                  createVNode("span", { class: "text-gray-600 dark:text-gray-400" }, "Rating"),
                  createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.avgRating.toFixed(1)), 1)
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
const _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductQuickSpecs.vue");
  return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
const _sfc_main$6 = /* @__PURE__ */ defineComponent({
  __name: "ProductRecommendations",
  __ssrInlineRender: true,
  props: {
    recommendations: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$b;
      const _component_UCarousel = _sfc_main$e;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "mt-10" }, _attrs))}><div class="mb-4 flex items-end justify-between gap-3"><div><h2 class="text-xl font-black text-gray-900 dark:text-white">Rekomendasi untuk Anda</h2><p class="mt-1 text-sm text-gray-600 dark:text-gray-400"> Produk yang sering dibeli bersamaan atau mirip dengan pilihan Anda. </p></div>`);
      _push(ssrRenderComponent(unref(Link), {
        href: "/shop",
        class: "text-sm font-semibold text-primary-600 hover:underline dark:text-primary-400"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Lihat semua `);
          } else {
            return [
              createTextVNode(" Lihat semua ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_component_UCard, {
        class: "overflow-hidden bg-primary-50 dark:bg-primary-950/40",
        ui: { body: "p-4" }
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UCarousel, {
              items: __props.recommendations,
              class: "w-full",
              ui: { container: "gap-4" }
            }, {
              default: withCtx(({ item }, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(ProductCard, {
                    product: item,
                    class: "block w-72"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(ProductCard, {
                      product: item,
                      class: "block w-72"
                    }, null, 8, ["product"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UCarousel, {
                items: __props.recommendations,
                class: "w-full",
                ui: { container: "gap-4" }
              }, {
                default: withCtx(({ item }) => [
                  createVNode(ProductCard, {
                    product: item,
                    class: "block w-72"
                  }, null, 8, ["product"])
                ]),
                _: 1
              }, 8, ["items"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductRecommendations.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
function useCart() {
  const isAddingToCart = ref(false);
  const addedToCart = ref(false);
  function addToCart(productId, qty) {
    if (isAddingToCart.value) return;
    isAddingToCart.value = true;
    router.post(
      "/cart/add",
      { product_id: productId, qty },
      {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          addedToCart.value = true;
          setTimeout(() => {
            addedToCart.value = false;
          }, 2500);
        },
        onFinish: () => {
          isAddingToCart.value = false;
        }
      }
    );
  }
  return { isAddingToCart, addedToCart, addToCart };
}
function useQtyInput(stockMax) {
  const qty = ref(1);
  watch(stockMax, (newMax) => {
    if (qty.value > newMax) {
      qty.value = Math.max(1, newMax);
    }
  });
  function increaseQty() {
    if (qty.value < stockMax.value) qty.value++;
  }
  function decreaseQty() {
    if (qty.value > 1) qty.value--;
  }
  function onQtyInput(event) {
    const raw = parseInt(event.target.value, 10);
    if (isNaN(raw) || raw < 1) {
      qty.value = 1;
    } else if (raw > stockMax.value) {
      qty.value = stockMax.value;
    } else {
      qty.value = raw;
    }
  }
  return { qty, increaseQty, decreaseQty, onQtyInput };
}
function useShare() {
  const toast = useToast();
  const isSharing = ref(false);
  async function share(title) {
    if (isSharing.value) return;
    isSharing.value = true;
    const url = window.location.href;
    try {
      if (navigator.share) {
        await navigator.share({ title, url });
      } else {
        await navigator.clipboard.writeText(url);
        toast.add({
          title: "Link disalin!",
          description: "Link produk berhasil disalin ke clipboard.",
          color: "success"
        });
      }
    } catch {
    } finally {
      isSharing.value = false;
    }
  }
  return { isSharing, share };
}
function useWishlist(initialIsInWishlist = false) {
  const { isLoggedIn } = useStoreData();
  const isInWishlist = ref(initialIsInWishlist);
  const isToggling = ref(false);
  const justWishlisted = ref(false);
  watch(
    () => initialIsInWishlist,
    (val) => {
      isInWishlist.value = val;
    }
  );
  function toggleWishlist(productId) {
    if (isToggling.value) return;
    if (!isLoggedIn.value) {
      router.visit("/login");
      return;
    }
    isToggling.value = true;
    const previousState = isInWishlist.value;
    isInWishlist.value = !previousState;
    if (isInWishlist.value) {
      justWishlisted.value = true;
      setTimeout(() => {
        justWishlisted.value = false;
      }, 2e3);
    }
    router.post(
      "/wishlist/toggle",
      { product_id: productId },
      {
        preserveState: true,
        preserveScroll: true,
        onError: () => {
          isInWishlist.value = previousState;
        },
        onFinish: () => {
          isToggling.value = false;
        }
      }
    );
  }
  return { isInWishlist, isToggling, justWishlisted, toggleWishlist };
}
const PRODUCT_PAGE_KEY = /* @__PURE__ */ Symbol("productPage");
function makeFallbackProduct(slug) {
  return {
    id: 1,
    slug,
    name: "Produk Premium ogitu.id",
    brand: "ogitu.id",
    shortDescription: "Produk premium dengan kualitas terverifikasi, nyaman dipakai sehari-hari.",
    description: "Deskripsi lengkap produk. Jelaskan manfaat, bahan, cara pakai, dan informasi penting lainnya.",
    rating: 4.8,
    reviewsCount: 128,
    highlights: ["Kualitas premium", "Garansi resmi", "Pengiriman cepat", "Support ramah"],
    specs: [
      { label: "Material", value: "Premium Grade" },
      { label: "Berat", value: "500g" },
      { label: "Asal", value: "Indonesia" },
      { label: "Garansi", value: "7 hari" }
    ],
    media: [
      { url: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=1400&auto=format&fit=crop", alt: "Foto 1" },
      { url: "https://images.unsplash.com/photo-1503602642458-232111445657?w=1400&auto=format&fit=crop", alt: "Foto 2" },
      { url: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1400&auto=format&fit=crop", alt: "Foto 3" },
      { url: "https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=1400&auto=format&fit=crop", alt: "Foto 4" }
    ],
    variants: [
      { id: 1, sku: "PRN-DEFAULT", name: "Default", price: 159e3, inStock: true, stock: 24, options: [] }
    ],
    priceFrom: 159e3
  };
}
const FALLBACK_RECOMMENDATIONS = [
  { id: 1, slug: "produk-1", name: "Produk Rekomendasi 1", price: 129e3, image: "https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=1200&auto=format&fit=crop", rating: 4.7, reviewsCount: 81, badge: "Diskon" },
  { id: 2, slug: "produk-2", name: "Produk Rekomendasi 2", price: 219e3, image: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=1200&auto=format&fit=crop", rating: 4.8, reviewsCount: 112 },
  { id: 3, slug: "produk-3", name: "Produk Rekomendasi 3", price: 99e3, image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1200&auto=format&fit=crop", rating: 4.6, reviewsCount: 45, badge: "Terlaris" },
  { id: 4, slug: "produk-4", name: "Produk Rekomendasi 4", price: 179e3, image: "https://images.unsplash.com/photo-1503602642458-232111445657?w=1200&auto=format&fit=crop", rating: 4.5, reviewsCount: 39 }
];
function useProductPage(props) {
  const product = computed(() => props.product ?? makeFallbackProduct(props.slug));
  const reviews = computed(() => props.reviews?.data ?? []);
  const recommendations = computed(
    () => props.recommendations?.length ? props.recommendations : FALLBACK_RECOMMENDATIONS
  );
  const {
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
  } = useProductDetail(() => product.value);
  const hasRealVariants = computed(
    () => variants.value.length > 1 || variants.value.length === 1 && (variants.value[0]?.options?.length ?? 0) > 0
  );
  const stockMax = computed(() => selectedVariant.value?.stock ?? 99);
  const { qty, increaseQty, decreaseQty, onQtyInput } = useQtyInput(stockMax);
  const { isAddingToCart, addedToCart, addToCart } = useCart();
  function handleAddToCart() {
    addToCart(product.value.id, qty.value);
  }
  const { isInWishlist, isToggling, justWishlisted, toggleWishlist } = useWishlist(
    props.isInWishlist ?? false
  );
  function handleToggleWishlist() {
    toggleWishlist(product.value.id);
  }
  const { isSharing, share } = useShare();
  async function handleShare() {
    await share(product.value.name);
  }
  const ctx = {
    product,
    reviews,
    recommendations,
    variants,
    selectedVariantId,
    selectedVariant,
    hasRealVariants,
    price,
    compareAtPrice,
    inStock,
    discountPercent,
    stockMax,
    galleryItems,
    activeImage,
    avgRating,
    reviewCount,
    qty,
    increaseQty,
    decreaseQty,
    onQtyInput,
    isAddingToCart,
    addedToCart,
    handleAddToCart,
    isInWishlist,
    isToggling,
    justWishlisted,
    handleToggleWishlist,
    isSharing,
    handleShare
  };
  provide(PRODUCT_PAGE_KEY, ctx);
  return ctx;
}
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "ProductQtyStepper",
  __ssrInlineRender: true,
  props: {
    modelValue: {},
    max: {},
    disabled: { type: Boolean },
    compact: { type: Boolean }
  },
  emits: ["update:modelValue"],
  setup(__props, { emit: __emit }) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$c;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex items-center gap-2" }, _attrs))}><div class="flex items-center overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"><button type="button"${ssrIncludeBooleanAttr(__props.disabled || __props.modelValue <= 1) ? " disabled" : ""} class="${ssrRenderClass([__props.compact ? "h-9 w-9" : "h-10 w-10", "flex items-center justify-center text-gray-500 transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-400 dark:hover:bg-gray-800"])}">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-minus",
        class: __props.compact ? "size-3.5" : "size-4"
      }, null, _parent));
      _push(`</button>`);
      if (!__props.compact) {
        _push(`<input${ssrRenderAttr("value", __props.modelValue)} type="number"${ssrRenderAttr("min", 1)}${ssrRenderAttr("max", __props.max)}${ssrIncludeBooleanAttr(__props.disabled) ? " disabled" : ""} class="h-10 w-14 border-x border-gray-200 bg-transparent text-center text-sm font-bold text-gray-900 focus:outline-none disabled:opacity-50 dark:border-gray-700 dark:text-white [appearance:textfield] [&amp;::-webkit-inner-spin-button]:appearance-none [&amp;::-webkit-outer-spin-button]:appearance-none">`);
      } else {
        _push(`<span class="w-8 text-center text-sm font-bold text-gray-900 dark:text-white">${ssrInterpolate(__props.modelValue)}</span>`);
      }
      _push(`<button type="button"${ssrIncludeBooleanAttr(__props.disabled || __props.modelValue >= __props.max) ? " disabled" : ""} class="${ssrRenderClass([__props.compact ? "h-9 w-9" : "h-10 w-10", "flex items-center justify-center text-gray-500 transition-colors hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-400 dark:hover:bg-gray-800"])}">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-plus",
        class: __props.compact ? "size-3.5" : "size-4"
      }, null, _parent));
      _push(`</button></div>`);
      if (!__props.compact) {
        _push(`<span class="text-xs text-gray-400 dark:text-gray-500"> Maks. ${ssrInterpolate(__props.max)} pcs </span>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
    };
  }
});
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductQtyStepper.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "ProductActionButtons",
  __ssrInlineRender: true,
  props: {
    disabled: { type: Boolean },
    isAddingToCart: { type: Boolean },
    addedToCart: { type: Boolean },
    isInWishlist: { type: Boolean },
    isToggling: { type: Boolean },
    justWishlisted: { type: Boolean },
    isSharing: { type: Boolean }
  },
  emits: ["add-to-cart", "toggle-wishlist", "share"],
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$i;
      const _component_UIcon = _sfc_main$c;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid gap-2" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UButton, {
        color: "primary",
        size: "lg",
        icon: __props.addedToCart ? "i-lucide-check" : "i-lucide-shopping-cart",
        loading: __props.isAddingToCart,
        disabled: __props.disabled || __props.isAddingToCart,
        block: "",
        class: ["font-semibold transition-all duration-200", __props.addedToCart ? "bg-green-600! hover:bg-green-700!" : ""],
        onClick: ($event) => _ctx.$emit("add-to-cart")
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(__props.addedToCart ? "Ditambahkan!" : __props.disabled ? "Stok Habis" : "Tambah ke Keranjang")}`);
          } else {
            return [
              createTextVNode(toDisplayString(__props.addedToCart ? "Ditambahkan!" : __props.disabled ? "Stok Habis" : "Tambah ke Keranjang"), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="grid grid-cols-2 gap-2">`);
      _push(ssrRenderComponent(_component_UButton, {
        color: __props.isInWishlist ? "error" : "neutral",
        variant: __props.isInWishlist ? "soft" : "outline",
        loading: __props.isToggling,
        disabled: __props.isToggling,
        block: "",
        class: "font-medium transition-all duration-200",
        onClick: ($event) => _ctx.$emit("toggle-wishlist")
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-heart",
              class: ["mr-1.5 size-4 transition-transform duration-200", [
                __props.isInWishlist ? "fill-current text-red-500" : "text-gray-500",
                __props.justWishlisted ? "scale-125" : ""
              ]]
            }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(__props.isInWishlist ? "Tersimpan" : "Wishlist")}`);
          } else {
            return [
              createVNode(_component_UIcon, {
                name: "i-lucide-heart",
                class: ["mr-1.5 size-4 transition-transform duration-200", [
                  __props.isInWishlist ? "fill-current text-red-500" : "text-gray-500",
                  __props.justWishlisted ? "scale-125" : ""
                ]]
              }, null, 8, ["class"]),
              createTextVNode(" " + toDisplayString(__props.isInWishlist ? "Tersimpan" : "Wishlist"), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        icon: "i-lucide-share-2",
        loading: __props.isSharing,
        block: "",
        class: "font-medium",
        onClick: ($event) => _ctx.$emit("share")
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Bagikan `);
          } else {
            return [
              createTextVNode(" Bagikan ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductActionButtons.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = {};
function _sfc_ssrRender(_ctx, _push, _parent, _attrs) {
  const _component_UIcon = _sfc_main$c;
  _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid gap-1.5 text-xs text-gray-500 dark:text-gray-400" }, _attrs))}><div class="flex items-center gap-2">`);
  _push(ssrRenderComponent(_component_UIcon, {
    name: "i-lucide-truck",
    class: "size-3.5 shrink-0 text-primary-500"
  }, null, _parent));
  _push(`<span>Estimasi kirim 1–3 hari kerja</span></div><div class="flex items-center gap-2">`);
  _push(ssrRenderComponent(_component_UIcon, {
    name: "i-lucide-badge-check",
    class: "size-3.5 shrink-0 text-primary-500"
  }, null, _parent));
  _push(`<span>Garansi &amp; support resmi</span></div><div class="flex items-center gap-2">`);
  _push(ssrRenderComponent(_component_UIcon, {
    name: "i-lucide-lock",
    class: "size-3.5 shrink-0 text-primary-500"
  }, null, _parent));
  _push(`<span>Pembayaran aman &amp; terenkripsi</span></div></div>`);
}
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductTrustSignals.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const ProductTrustSignals = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["ssrRender", _sfc_ssrRender]]);
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "ProductPurchasePanel",
  __ssrInlineRender: true,
  setup(__props) {
    const ctx = inject(PRODUCT_PAGE_KEY);
    if (!ctx) {
      throw new Error("ProductPurchasePanel must be used inside a component that provides PRODUCT_PAGE_KEY");
    }
    const {
      selectedVariant,
      price,
      compareAtPrice,
      inStock,
      discountPercent,
      stockMax,
      qty,
      increaseQty,
      decreaseQty,
      onQtyInput,
      isAddingToCart,
      addedToCart,
      handleAddToCart,
      isInWishlist,
      isToggling,
      justWishlisted,
      handleToggleWishlist,
      isSharing,
      handleShare
    } = ctx;
    function onQtyModelUpdate(value) {
      qty.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UBadge = _sfc_main$d;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "rounded-2xl bg-white/70 p-4 ring-1 ring-gray-200/60 dark:bg-gray-900/40 dark:ring-white/5" }, _attrs))}><div class="flex items-start justify-between gap-3"><div><div class="text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500"> Harga </div><div class="mt-1 text-2xl font-black text-gray-900 dark:text-white">${ssrInterpolate(unref(formatCurrency)(unref(price)))}</div>`);
      if (unref(compareAtPrice)) {
        _push(`<div class="mt-1 flex items-center gap-2"><span class="text-sm text-gray-400 line-through dark:text-gray-500">${ssrInterpolate(unref(formatCurrency)(unref(compareAtPrice)))}</span>`);
        if (unref(discountPercent)) {
          _push(ssrRenderComponent(_component_UBadge, {
            color: "error",
            variant: "soft",
            size: "xs"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(` Hemat ${ssrInterpolate(unref(discountPercent))}% `);
              } else {
                return [
                  createTextVNode(" Hemat " + toDisplayString(unref(discountPercent)) + "% ", 1)
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div class="space-y-1 text-right">`);
      if (!unref(inStock)) {
        _push(ssrRenderComponent(_component_UBadge, {
          color: "error",
          variant: "soft"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`Stok habis`);
            } else {
              return [
                createTextVNode("Stok habis")
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(ssrRenderComponent(_component_UBadge, {
          color: "success",
          variant: "soft"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`Tersedia`);
            } else {
              return [
                createTextVNode("Tersedia")
              ];
            }
          }),
          _: 1
        }, _parent));
      }
      if (unref(selectedVariant)?.stock !== void 0 && unref(inStock)) {
        _push(`<div class="text-xs text-gray-500 dark:text-gray-400"> Sisa <span class="font-semibold text-gray-700 dark:text-gray-300">${ssrInterpolate(unref(selectedVariant).stock)}</span> pcs </div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div>`);
      if (unref(inStock)) {
        _push(`<div class="mt-4"><div class="mb-1.5 text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500"> Jumlah </div>`);
        _push(ssrRenderComponent(_sfc_main$5, {
          "model-value": unref(qty),
          max: unref(stockMax),
          disabled: !unref(inStock),
          "onUpdate:modelValue": onQtyModelUpdate
        }, null, _parent));
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="mt-4">`);
      _push(ssrRenderComponent(_sfc_main$4, {
        disabled: !unref(inStock),
        "is-adding-to-cart": unref(isAddingToCart),
        "added-to-cart": unref(addedToCart),
        "is-in-wishlist": unref(isInWishlist),
        "is-toggling": unref(isToggling),
        "just-wishlisted": unref(justWishlisted),
        "is-sharing": unref(isSharing),
        onAddToCart: unref(handleAddToCart),
        onToggleWishlist: unref(handleToggleWishlist),
        onShare: unref(handleShare)
      }, null, _parent));
      _push(`</div><div class="mt-4">`);
      _push(ssrRenderComponent(ProductTrustSignals, null, null, _parent));
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductPurchasePanel.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "ProductVariantPicker",
  __ssrInlineRender: true,
  props: {
    "selectedVariantId": {},
    "selectedVariantIdModifiers": {}
  },
  emits: ["update:selectedVariantId"],
  setup(__props) {
    useModel(__props, "selectedVariantId");
    const ctx = inject(PRODUCT_PAGE_KEY);
    if (!ctx) {
      throw new Error("ProductVariantPicker must be used inside a component that provides PRODUCT_PAGE_KEY");
    }
    const { variants, selectedVariantId, selectedVariant, hasRealVariants } = ctx;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$b;
      const _component_UBadge = _sfc_main$d;
      const _component_USelectMenu = _sfc_main$j;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        ui: { body: "p-5" },
        class: "bg-primary-50 dark:bg-primary-950/40"
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(unref(hasRealVariants) ? "Pilih Varian" : "Detail Produk")}</div>`);
            if (unref(selectedVariant)?.sku) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "neutral",
                variant: "subtle",
                class: "font-mono text-[11px]"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` SKU: ${ssrInterpolate(unref(selectedVariant).sku)}`);
                  } else {
                    return [
                      createTextVNode(" SKU: " + toDisplayString(unref(selectedVariant).sku), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><div class="mt-4 space-y-4"${_scopeId}>`);
            if (unref(hasRealVariants)) {
              _push2(`<!--[-->`);
              _push2(ssrRenderComponent(_component_USelectMenu, {
                modelValue: unref(selectedVariantId),
                "onUpdate:modelValue": ($event) => isRef(selectedVariantId) ? selectedVariantId.value = $event : null,
                items: unref(variants).map((v) => ({ label: v.name, value: v.id })),
                "value-key": "value",
                "label-key": "label",
                placeholder: "Pilih varian"
              }, null, _parent2, _scopeId));
              if (unref(selectedVariant)?.options?.length) {
                _push2(`<div class="grid gap-2 sm:grid-cols-2"${_scopeId}><!--[-->`);
                ssrRenderList(unref(selectedVariant).options, (opt, i) => {
                  _push2(`<div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><div class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(opt.name)}</div><div class="mt-1 flex items-center justify-between gap-2"${_scopeId}><div class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(opt.value)}</div>`);
                  if (opt.badge) {
                    _push2(ssrRenderComponent(_component_UBadge, {
                      color: "warning",
                      variant: "soft",
                      size: "xs"
                    }, {
                      default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                        if (_push3) {
                          _push3(`${ssrInterpolate(opt.badge)}`);
                        } else {
                          return [
                            createTextVNode(toDisplayString(opt.badge), 1)
                          ];
                        }
                      }),
                      _: 2
                    }, _parent2, _scopeId));
                  } else {
                    _push2(`<!---->`);
                  }
                  _push2(`</div></div>`);
                });
                _push2(`<!--]--></div>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`<!--]-->`);
            } else {
              _push2(`<!---->`);
            }
            _push2(ssrRenderComponent(_sfc_main$2, null, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", { class: "text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, toDisplayString(unref(hasRealVariants) ? "Pilih Varian" : "Detail Produk"), 1),
                unref(selectedVariant)?.sku ? (openBlock(), createBlock(_component_UBadge, {
                  key: 0,
                  color: "neutral",
                  variant: "subtle",
                  class: "font-mono text-[11px]"
                }, {
                  default: withCtx(() => [
                    createTextVNode(" SKU: " + toDisplayString(unref(selectedVariant).sku), 1)
                  ]),
                  _: 1
                })) : createCommentVNode("", true)
              ]),
              createVNode("div", { class: "mt-4 space-y-4" }, [
                unref(hasRealVariants) ? (openBlock(), createBlock(Fragment, { key: 0 }, [
                  createVNode(_component_USelectMenu, {
                    modelValue: unref(selectedVariantId),
                    "onUpdate:modelValue": ($event) => isRef(selectedVariantId) ? selectedVariantId.value = $event : null,
                    items: unref(variants).map((v) => ({ label: v.name, value: v.id })),
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih varian"
                  }, null, 8, ["modelValue", "onUpdate:modelValue", "items"]),
                  unref(selectedVariant)?.options?.length ? (openBlock(), createBlock("div", {
                    key: 0,
                    class: "grid gap-2 sm:grid-cols-2"
                  }, [
                    (openBlock(true), createBlock(Fragment, null, renderList(unref(selectedVariant).options, (opt, i) => {
                      return openBlock(), createBlock("div", {
                        key: i,
                        class: "rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-950/40"
                      }, [
                        createVNode("div", { class: "text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400" }, toDisplayString(opt.name), 1),
                        createVNode("div", { class: "mt-1 flex items-center justify-between gap-2" }, [
                          createVNode("div", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(opt.value), 1),
                          opt.badge ? (openBlock(), createBlock(_component_UBadge, {
                            key: 0,
                            color: "warning",
                            variant: "soft",
                            size: "xs"
                          }, {
                            default: withCtx(() => [
                              createTextVNode(toDisplayString(opt.badge), 1)
                            ]),
                            _: 2
                          }, 1024)) : createCommentVNode("", true)
                        ])
                      ]);
                    }), 128))
                  ])) : createCommentVNode("", true)
                ], 64)) : createCommentVNode("", true),
                createVNode(_sfc_main$2)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductVariantPicker.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$k, inheritAttrs: false },
  __name: "Show",
  __ssrInlineRender: true,
  props: {
    slug: {},
    product: {},
    reviews: {},
    recommendations: {},
    isInWishlist: { type: Boolean }
  },
  setup(__props) {
    const props = __props;
    const {
      product,
      recommendations,
      galleryItems,
      activeImage,
      discountPercent,
      avgRating,
      reviewCount
    } = useProductPage(props);
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(unref(Head), {
        title: `${unref(product).name} | ogitu.id`
      }, null, _parent));
      _push(`<div class="min-h-screen mx-auto max-w-screen-2xl bg-gray-50/60 px-4 sm:px-6 lg:px-8 dark:bg-gray-950">`);
      _push(ssrRenderComponent(_sfc_main$9, {
        product: unref(product),
        "avg-rating": unref(avgRating),
        "review-count": unref(reviewCount),
        "in-stock": !!unref(product).variants?.some((v) => v.inStock)
      }, null, _parent));
      _push(`<div class="grid grid-cols-1 gap-8 lg:grid-cols-12"><div class="lg:col-span-7">`);
      _push(ssrRenderComponent(_sfc_main$a, {
        items: unref(galleryItems),
        "active-index": unref(activeImage),
        "discount-percent": unref(discountPercent),
        "onUpdate:activeIndex": ($event) => activeImage.value = $event
      }, null, _parent));
      _push(`<div class="mt-6">`);
      _push(ssrRenderComponent(_sfc_main$8, {
        description: unref(product).description,
        highlights: unref(product).highlights,
        specs: unref(product).specs,
        reviews: props.reviews,
        "avg-rating": unref(avgRating),
        "review-count": unref(reviewCount)
      }, null, _parent));
      _push(`</div></div><div class="lg:col-span-5"><div class="space-y-6 lg:sticky lg:top-24">`);
      _push(ssrRenderComponent(_sfc_main$1, null, null, _parent));
      _push(ssrRenderComponent(_sfc_main$7, {
        "avg-rating": unref(avgRating),
        "review-count": unref(reviewCount)
      }, null, _parent));
      _push(`</div></div></div>`);
      _push(ssrRenderComponent(_sfc_main$6, { recommendations: unref(recommendations) }, null, _parent));
      _push(`</div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Shop/Show.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
