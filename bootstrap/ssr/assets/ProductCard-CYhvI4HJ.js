import { _ as _sfc_main$4 } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$3 } from "./Button-DLZCZWnW.js";
import { e as _sfc_main$2, b as _export_sfc } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$1 } from "./Icon-Chcm7u9q.js";
import { defineComponent, computed, mergeProps, unref, withCtx, createVNode, createTextVNode, toDisplayString, openBlock, createBlock, createCommentVNode, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderAttr, ssrInterpolate, ssrRenderClass } from "vue/server-renderer";
import { Link } from "@inertiajs/vue3";
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "ProductCard",
  __ssrInlineRender: true,
  props: {
    product: {}
  },
  setup(__props) {
    const props = __props;
    const formatPrice = (n) => new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0
    }).format(n);
    const formattedSales = computed(() => {
      const s = props.product.salesCount;
      if (s >= 1e3) return `${(s / 1e3).toFixed(s >= 1e4 ? 0 : 1)}rb`;
      return String(s);
    });
    const badgeColor = computed(() => {
      switch (props.product.badge) {
        case "Terlaris":
          return "warning";
        case "Baru":
          return "info";
        default:
          return "primary";
      }
    });
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$1;
      const _component_UTooltip = _sfc_main$2;
      const _component_UButton = _sfc_main$3;
      const _component_UBadge = _sfc_main$4;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "product-card @container" }, _attrs))} data-v-baf1a17a><div class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200/80 bg-white transition-all duration-300 hover:shadow-lg hover:border-gray-300/80 dark:border-gray-800/80 dark:bg-gray-900 dark:hover:border-gray-700/80 dark:hover:shadow-gray-950/50" data-v-baf1a17a>`);
      _push(ssrRenderComponent(unref(Link), {
        href: `/shop/${__props.product.slug}`,
        class: "relative block aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800/50"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (__props.product.image) {
              _push2(`<img${ssrRenderAttr("src", __props.product.image)}${ssrRenderAttr("alt", __props.product.name)} class="size-full object-cover transition-transform duration-700 ease-out will-change-transform group-hover:scale-[1.06]" loading="lazy" data-v-baf1a17a${_scopeId}>`);
            } else {
              _push2(`<div class="flex size-full items-center justify-center bg-gray-50 dark:bg-gray-800/80" data-v-baf1a17a${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-image",
                class: "size-8 text-gray-300 @xs:size-10 dark:text-gray-600"
              }, null, _parent2, _scopeId));
              _push2(`</div>`);
            }
            _push2(`<div class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/20 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 @xs:pointer-events-auto" data-v-baf1a17a${_scopeId}><div class="absolute bottom-3 left-1/2 hidden -translate-x-1/2 gap-2 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 @xs:flex" data-v-baf1a17a${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UTooltip, { text: "Lihat detail" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UButton, {
                    icon: "i-lucide-eye",
                    color: "neutral",
                    variant: "solid",
                    size: "sm",
                    class: "rounded-full shadow-lg backdrop-blur-sm"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UButton, {
                      icon: "i-lucide-eye",
                      color: "neutral",
                      variant: "solid",
                      size: "sm",
                      class: "rounded-full shadow-lg backdrop-blur-sm"
                    })
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UTooltip, { text: "Wishlist" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UButton, {
                    icon: "i-lucide-heart",
                    color: "neutral",
                    variant: "solid",
                    size: "sm",
                    class: "rounded-full shadow-lg backdrop-blur-sm"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UButton, {
                      icon: "i-lucide-heart",
                      color: "neutral",
                      variant: "solid",
                      size: "sm",
                      class: "rounded-full shadow-lg backdrop-blur-sm"
                    })
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
            if (__props.product.badge) {
              _push2(`<div class="absolute top-2 left-2 @xs:top-3 @xs:left-3" data-v-baf1a17a${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UBadge, {
                color: badgeColor.value,
                variant: "solid",
                size: "sm",
                ui: { root: "shadow-md font-extrabold uppercase tracking-wider text-[9px] @xs:text-[10px]" }
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`${ssrInterpolate(__props.product.badge)}`);
                  } else {
                    return [
                      createTextVNode(toDisplayString(__props.product.badge), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<button class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-full bg-white/80 text-gray-500 shadow-sm backdrop-blur-sm transition-colors hover:bg-white hover:text-red-500 @xs:hidden dark:bg-gray-900/80 dark:text-gray-400" aria-label="Wishlist" data-v-baf1a17a${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-heart",
              class: "size-3.5"
            }, null, _parent2, _scopeId));
            _push2(`</button>`);
          } else {
            return [
              __props.product.image ? (openBlock(), createBlock("img", {
                key: 0,
                src: __props.product.image,
                alt: __props.product.name,
                class: "size-full object-cover transition-transform duration-700 ease-out will-change-transform group-hover:scale-[1.06]",
                loading: "lazy"
              }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                key: 1,
                class: "flex size-full items-center justify-center bg-gray-50 dark:bg-gray-800/80"
              }, [
                createVNode(_component_UIcon, {
                  name: "i-lucide-image",
                  class: "size-8 text-gray-300 @xs:size-10 dark:text-gray-600"
                })
              ])),
              createVNode("div", { class: "pointer-events-none absolute inset-0 bg-linear-to-t from-black/20 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 @xs:pointer-events-auto" }, [
                createVNode("div", { class: "absolute bottom-3 left-1/2 hidden -translate-x-1/2 gap-2 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 @xs:flex" }, [
                  createVNode(_component_UTooltip, { text: "Lihat detail" }, {
                    default: withCtx(() => [
                      createVNode(_component_UButton, {
                        icon: "i-lucide-eye",
                        color: "neutral",
                        variant: "solid",
                        size: "sm",
                        class: "rounded-full shadow-lg backdrop-blur-sm"
                      })
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UTooltip, { text: "Wishlist" }, {
                    default: withCtx(() => [
                      createVNode(_component_UButton, {
                        icon: "i-lucide-heart",
                        color: "neutral",
                        variant: "solid",
                        size: "sm",
                        class: "rounded-full shadow-lg backdrop-blur-sm"
                      })
                    ]),
                    _: 1
                  })
                ])
              ]),
              __props.product.badge ? (openBlock(), createBlock("div", {
                key: 2,
                class: "absolute top-2 left-2 @xs:top-3 @xs:left-3"
              }, [
                createVNode(_component_UBadge, {
                  color: badgeColor.value,
                  variant: "solid",
                  size: "sm",
                  ui: { root: "shadow-md font-extrabold uppercase tracking-wider text-[9px] @xs:text-[10px]" }
                }, {
                  default: withCtx(() => [
                    createTextVNode(toDisplayString(__props.product.badge), 1)
                  ]),
                  _: 1
                }, 8, ["color"])
              ])) : createCommentVNode("", true),
              createVNode("button", {
                class: "absolute top-2 right-2 flex size-7 items-center justify-center rounded-full bg-white/80 text-gray-500 shadow-sm backdrop-blur-sm transition-colors hover:bg-white hover:text-red-500 @xs:hidden dark:bg-gray-900/80 dark:text-gray-400",
                "aria-label": "Wishlist"
              }, [
                createVNode(_component_UIcon, {
                  name: "i-lucide-heart",
                  class: "size-3.5"
                })
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="flex flex-1 flex-col p-2.5 @xs:p-3.5 @sm:p-4" data-v-baf1a17a><div class="mb-1.5 flex items-center justify-between gap-1" data-v-baf1a17a><div class="flex items-center gap-1" data-v-baf1a17a>`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-star",
        class: "size-3 @xs:size-3.5 text-amber-400"
      }, null, _parent));
      _push(`<span class="text-[10px] font-bold text-gray-700 @xs:text-[11px] dark:text-gray-300" data-v-baf1a17a>${ssrInterpolate(Number(__props.product.rating).toFixed(1))}</span><span class="hidden text-[10px] text-gray-400 @xs:inline dark:text-gray-500" data-v-baf1a17a> (${ssrInterpolate(__props.product.reviewCount)}) </span></div>`);
      if (__props.product.salesCount > 0) {
        _push(`<span class="truncate text-[9px] font-medium text-gray-400 @xs:text-[10px] dark:text-gray-500" data-v-baf1a17a>${ssrInterpolate(formattedSales.value)} terjual </span>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
      _push(ssrRenderComponent(unref(Link), {
        href: `/shop/${__props.product.slug}`,
        class: "flex-1"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<h3 class="line-clamp-2 text-[11px] font-bold leading-snug text-gray-900 transition-colors group-hover:text-primary @xs:text-xs @sm:text-sm dark:text-white dark:group-hover:text-primary" data-v-baf1a17a${_scopeId}>${ssrInterpolate(__props.product.name)}</h3>`);
          } else {
            return [
              createVNode("h3", { class: "line-clamp-2 text-[11px] font-bold leading-snug text-gray-900 transition-colors group-hover:text-primary @xs:text-xs @sm:text-sm dark:text-white dark:group-hover:text-primary" }, toDisplayString(__props.product.name), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="mt-auto flex items-end justify-between gap-1.5 pt-2 @xs:pt-2.5" data-v-baf1a17a><div class="min-w-0" data-v-baf1a17a><p class="${ssrRenderClass([__props.product.compareAtPrice ? "text-red-600 dark:text-red-400" : "text-gray-900 dark:text-white", "truncate text-xs font-extrabold tracking-tight @xs:text-sm @sm:text-base"])}" data-v-baf1a17a>${ssrInterpolate(formatPrice(__props.product.price))}</p>`);
      if (__props.product.compareAtPrice) {
        _push(`<p class="truncate text-[10px] text-gray-400 line-through @xs:text-xs dark:text-gray-500" data-v-baf1a17a>${ssrInterpolate(formatPrice(__props.product.compareAtPrice))}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
      _push(ssrRenderComponent(_component_UTooltip, { text: "Tambah ke keranjang" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UButton, {
              icon: "i-lucide-shopping-cart",
              color: "primary",
              variant: "soft",
              size: "sm",
              class: "shrink-0 rounded-full transition-transform active:scale-90 @xs:rounded-xl p-2"
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UButton, {
                icon: "i-lucide-shopping-cart",
                color: "primary",
                variant: "soft",
                size: "sm",
                class: "shrink-0 rounded-full transition-transform active:scale-90 @xs:rounded-xl p-2"
              })
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></div></div></div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/product/ProductCard.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const ProductCard = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-baf1a17a"]]);
export {
  ProductCard as P
};
