import { defineComponent, mergeProps, withCtx, createTextVNode, createVNode, toDisplayString, unref, useSSRContext, openBlock, createBlock, createCommentVNode, computed, Fragment, renderList, ref } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList, ssrRenderAttr, ssrRenderClass } from "vue/server-renderer";
import { _ as _sfc_main$a } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$9 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$8 } from "./Card-CvchAxCK.js";
import { u as useDashboard } from "./useDashboard-DR5F4MRN.js";
import { _ as _sfc_main$b } from "./Badge-DqskWDDq.js";
import { u as useStoreData } from "./useStoreData-DrTMI0On.js";
import "defu";
import "reka-ui";
import "@inertiajs/vue3";
import "@vueuse/core";
import "ufo";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "./useToast-CTuSIf9f.js";
const _sfc_main$7 = /* @__PURE__ */ defineComponent({
  __name: "DashboardStatCards",
  __ssrInlineRender: true,
  props: {
    stats: {}
  },
  emits: ["navigate"],
  setup(__props) {
    const { formatIDR } = useDashboard();
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Order Total</p><p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.stats?.orders_total ?? 0)}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Pending: <span class="font-semibold"${_scopeId}>${ssrInterpolate(__props.stats?.orders_pending ?? 0)}</span></p></div><div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-receipt",
              class: "size-5 text-gray-600 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="mt-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "orders")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Lihat Pesanan `);
                } else {
                  return [
                    createTextVNode(" Lihat Pesanan ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Order Total"),
                  createVNode("p", { class: "mt-1 text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(__props.stats?.orders_total ?? 0), 1),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, [
                    createTextVNode(" Pending: "),
                    createVNode("span", { class: "font-semibold" }, toDisplayString(__props.stats?.orders_pending ?? 0), 1)
                  ])
                ]),
                createVNode("div", { class: "grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-receipt",
                    class: "size-5 text-gray-600 dark:text-gray-300"
                  })
                ])
              ]),
              createVNode("div", { class: "mt-4" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "orders")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Lihat Pesanan ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Wallet</p><p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(__props.stats?.wallet_balance ?? 0))}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Siap dipakai checkout</p></div><div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-wallet",
              class: "size-5 text-gray-600 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="mt-4 grid grid-cols-2 gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "wallet")
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
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "soft",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "wallet")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Top up `);
                } else {
                  return [
                    createTextVNode(" Top up ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Wallet"),
                  createVNode("p", { class: "mt-1 text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(__props.stats?.wallet_balance ?? 0)), 1),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, "Siap dipakai checkout")
                ]),
                createVNode("div", { class: "grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-wallet",
                    class: "size-5 text-gray-600 dark:text-gray-300"
                  })
                ])
              ]),
              createVNode("div", { class: "mt-4 grid grid-cols-2 gap-2" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "wallet")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Detail ")
                  ]),
                  _: 1
                }, 8, ["onClick"]),
                createVNode(_component_UButton, {
                  color: "primary",
                  variant: "soft",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "wallet")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Top up ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Statistik Jaringan</p><p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.stats?.network_total ?? 0)}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Aktif: <span class="font-semibold"${_scopeId}>${ssrInterpolate(__props.stats?.network_active ?? 0)}</span><span class="mx-2 text-gray-400"${_scopeId}>•</span> Level: <span class="font-semibold"${_scopeId}>${ssrInterpolate(__props.stats?.network_level ?? 0)}</span></p></div><div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-network",
              class: "size-5 text-gray-600 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="mt-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "network")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Buka Network `);
                } else {
                  return [
                    createTextVNode(" Buka Network ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Statistik Jaringan"),
                  createVNode("p", { class: "mt-1 text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(__props.stats?.network_total ?? 0), 1),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, [
                    createTextVNode(" Aktif: "),
                    createVNode("span", { class: "font-semibold" }, toDisplayString(__props.stats?.network_active ?? 0), 1),
                    createVNode("span", { class: "mx-2 text-gray-400" }, "•"),
                    createTextVNode(" Level: "),
                    createVNode("span", { class: "font-semibold" }, toDisplayString(__props.stats?.network_level ?? 0), 1)
                  ])
                ]),
                createVNode("div", { class: "grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-network",
                    class: "size-5 text-gray-600 dark:text-gray-300"
                  })
                ])
              ]),
              createVNode("div", { class: "mt-4" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "network")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Buka Network ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Statistik Bonus</p><p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(__props.stats?.bonus_total ?? 0))}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Rekap total bonus sejak bergabung </p></div><div class="grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-coins",
              class: "size-5 text-gray-600 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div></div><div class="mt-4 grid grid-cols-2 gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "bonus")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Menu Bonus `);
                } else {
                  return [
                    createTextVNode(" Menu Bonus ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "soft",
              size: "sm",
              class: "rounded-xl",
              block: "",
              onClick: ($event) => _ctx.$emit("navigate", "wallet")
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
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Statistik Bonus"),
                  createVNode("p", { class: "mt-1 text-2xl font-extrabold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(__props.stats?.bonus_total ?? 0)), 1),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, " Rekap total bonus sejak bergabung ")
                ]),
                createVNode("div", { class: "grid size-10 place-items-center rounded-2xl bg-gray-100 dark:bg-gray-900" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-coins",
                    class: "size-5 text-gray-600 dark:text-gray-300"
                  })
                ])
              ]),
              createVNode("div", { class: "mt-4 grid grid-cols-2 gap-2" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "bonus")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Menu Bonus ")
                  ]),
                  _: 1
                }, 8, ["onClick"]),
                createVNode(_component_UButton, {
                  color: "primary",
                  variant: "soft",
                  size: "sm",
                  class: "rounded-xl",
                  block: "",
                  onClick: ($event) => _ctx.$emit("navigate", "wallet")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Tarik Wallet ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
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
const _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardStatCards.vue");
  return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
const _sfc_main$6 = /* @__PURE__ */ defineComponent({
  __name: "DashboardAddressWidget",
  __ssrInlineRender: true,
  props: {
    defaultAddress: {}
  },
  emits: ["navigate"],
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UButton = _sfc_main$a;
      const _component_UBadge = _sfc_main$b;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"${_scopeId}><div${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Kelola Alamat</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Atur alamat default untuk mempercepat checkout. </p></div><div class="flex gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              size: "sm",
              onClick: ($event) => _ctx.$emit("navigate", "addresses")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Lihat semua `);
                } else {
                  return [
                    createTextVNode(" Lihat semua ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "solid",
              class: "rounded-xl",
              size: "sm",
              icon: "i-lucide-plus",
              onClick: ($event) => _ctx.$emit("navigate", "addresses")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tambah `);
                } else {
                  return [
                    createTextVNode(" Tambah ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Kelola Alamat"),
                  createVNode("p", { class: "mt-1 text-sm text-gray-500 dark:text-gray-400" }, " Atur alamat default untuk mempercepat checkout. ")
                ]),
                createVNode("div", { class: "flex gap-2" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    size: "sm",
                    onClick: ($event) => _ctx.$emit("navigate", "addresses")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Lihat semua ")
                    ]),
                    _: 1
                  }, 8, ["onClick"]),
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "solid",
                    class: "rounded-xl",
                    size: "sm",
                    icon: "i-lucide-plus",
                    onClick: ($event) => _ctx.$emit("navigate", "addresses")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tambah ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])
                ])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (__props.defaultAddress) {
              _push2(`<div class="rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><div class="flex items-start justify-between gap-3"${_scopeId}><div class="min-w-0"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.defaultAddress.label)} `);
              if (__props.defaultAddress.is_default) {
                _push2(ssrRenderComponent(_component_UBadge, {
                  label: "Default",
                  color: "success",
                  variant: "soft",
                  size: "xs",
                  class: "ml-2 rounded-full"
                }, null, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              _push2(`</p><p class="mt-1 text-sm text-gray-600 dark:text-gray-300"${_scopeId}>${ssrInterpolate(__props.defaultAddress.recipient_name)} • ${ssrInterpolate(__props.defaultAddress.phone)}</p><p class="mt-2 text-sm text-gray-700 dark:text-gray-200"${_scopeId}>${ssrInterpolate(__props.defaultAddress.address_line)}, ${ssrInterpolate(__props.defaultAddress.city)}, ${ssrInterpolate(__props.defaultAddress.province)}, ${ssrInterpolate(__props.defaultAddress.postal_code)}</p></div>`);
              _push2(ssrRenderComponent(_component_UButton, {
                color: "neutral",
                variant: "ghost",
                class: "rounded-xl",
                size: "sm",
                icon: "i-lucide-pencil",
                onClick: ($event) => _ctx.$emit("navigate", "addresses")
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Edit `);
                  } else {
                    return [
                      createTextVNode(" Edit ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div></div>`);
            } else {
              _push2(`<div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 p-6 text-center"${_scopeId}><p class="text-sm text-gray-600 dark:text-gray-300"${_scopeId}>Kamu belum punya alamat. Tambahkan sekarang.</p><div class="mt-4"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UButton, {
                color: "primary",
                variant: "solid",
                class: "rounded-xl",
                icon: "i-lucide-plus",
                onClick: ($event) => _ctx.$emit("navigate", "addresses")
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Tambah alamat `);
                  } else {
                    return [
                      createTextVNode(" Tambah alamat ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div></div>`);
            }
          } else {
            return [
              __props.defaultAddress ? (openBlock(), createBlock("div", {
                key: 0,
                class: "rounded-2xl border border-gray-200 bg-white/70 p-4 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40"
              }, [
                createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                  createVNode("div", { class: "min-w-0" }, [
                    createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, [
                      createTextVNode(toDisplayString(__props.defaultAddress.label) + " ", 1),
                      __props.defaultAddress.is_default ? (openBlock(), createBlock(_component_UBadge, {
                        key: 0,
                        label: "Default",
                        color: "success",
                        variant: "soft",
                        size: "xs",
                        class: "ml-2 rounded-full"
                      })) : createCommentVNode("", true)
                    ]),
                    createVNode("p", { class: "mt-1 text-sm text-gray-600 dark:text-gray-300" }, toDisplayString(__props.defaultAddress.recipient_name) + " • " + toDisplayString(__props.defaultAddress.phone), 1),
                    createVNode("p", { class: "mt-2 text-sm text-gray-700 dark:text-gray-200" }, toDisplayString(__props.defaultAddress.address_line) + ", " + toDisplayString(__props.defaultAddress.city) + ", " + toDisplayString(__props.defaultAddress.province) + ", " + toDisplayString(__props.defaultAddress.postal_code), 1)
                  ]),
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "ghost",
                    class: "rounded-xl",
                    size: "sm",
                    icon: "i-lucide-pencil",
                    onClick: ($event) => _ctx.$emit("navigate", "addresses")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Edit ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])
                ])
              ])) : (openBlock(), createBlock("div", {
                key: 1,
                class: "rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 p-6 text-center"
              }, [
                createVNode("p", { class: "text-sm text-gray-600 dark:text-gray-300" }, "Kamu belum punya alamat. Tambahkan sekarang."),
                createVNode("div", { class: "mt-4" }, [
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "solid",
                    class: "rounded-xl",
                    icon: "i-lucide-plus",
                    onClick: ($event) => _ctx.$emit("navigate", "addresses")
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tambah alamat ")
                    ]),
                    _: 1
                  }, 8, ["onClick"])
                ])
              ]))
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardAddressWidget.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "DashboardNetworkProfile",
  __ssrInlineRender: true,
  props: {
    customer: {},
    networkProfile: {}
  },
  emits: ["navigate"],
  setup(__props, { emit: __emit }) {
    const { digitsOnly, formatIDR, formatPhoneDisplay, copyToClipboard } = useDashboard();
    const props = __props;
    const emit = __emit;
    const numericNik = computed(() => digitsOnly(props.customer?.nik) || "—");
    const formattedPhone = computed(() => formatPhoneDisplay(props.customer?.phone));
    const numericBankAccount = computed(() => digitsOnly(props.customer?.bank_account) || "—");
    function moveToFormAccount() {
      emit("navigate", "form_account");
    }
    function moveToWallet() {
      emit("navigate", "wallet");
    }
    function moveToBonus() {
      emit("navigate", "bonus");
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><div class="flex items-center gap-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-circle-user-round",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`<p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Profil Network</p></div>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "ghost",
              size: "xs",
              class: "rounded-xl",
              icon: "i-lucide-arrow-right",
              onClick: moveToFormAccount
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Lengkapi Profil `);
                } else {
                  return [
                    createTextVNode(" Lengkapi Profil ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("div", { class: "flex items-center gap-3" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-circle-user-round",
                    class: "size-5 text-gray-500 dark:text-gray-300"
                  }),
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Profil Network")
                ]),
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "ghost",
                  size: "xs",
                  class: "rounded-xl",
                  icon: "i-lucide-arrow-right",
                  onClick: moveToFormAccount
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Lengkapi Profil ")
                  ]),
                  _: 1
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="grid grid-cols-1 gap-2 sm:grid-cols-2"${_scopeId}><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Nama</p><p class="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(props.customer?.name ?? "—")}</p></div><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Username</p><p class="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(props.networkProfile?.username ?? "—")}</p></div><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Level</p><p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(props.networkProfile?.level ?? "—")}</p></div><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Kode Referral</p><div class="mt-0.5 flex items-center gap-1.5"${_scopeId}><p class="truncate font-mono text-sm font-semibold tracking-wider text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(props.networkProfile?.referral_code ?? "—")}</p>`);
            if (props.networkProfile?.username) {
              _push2(ssrRenderComponent(_component_UButton, {
                color: "neutral",
                variant: "ghost",
                size: "xs",
                icon: "i-lucide-copy",
                class: "shrink-0 rounded-lg",
                onClick: ($event) => unref(copyToClipboard)(props.networkProfile?.username ?? "")
              }, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>NIK</p><p class="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(numericNik.value)}</p></div><div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>No. WA / Telepon</p><p class="mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(formattedPhone.value)}</p></div><div class="sm:col-span-2 rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>Nomor Rekening</p><p class="mt-0.5 truncate font-mono text-sm font-semibold tracking-wide text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(numericBankAccount.value)}</p></div><div class="sm:col-span-2 rounded-xl bg-primary-50 dark:bg-primary-950/30 px-3 py-2.5"${_scopeId}><p class="text-xs text-primary-600 dark:text-primary-400"${_scopeId}>Saldo</p><p class="mt-0.5 text-lg font-extrabold text-primary-700 dark:text-primary-300"${_scopeId}>${ssrInterpolate(unref(formatIDR)(props.networkProfile?.balance ?? 0))}</p></div><div class="sm:col-span-2 grid grid-cols-1 gap-2 pt-1 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "soft",
              class: "rounded-xl",
              icon: "i-lucide-arrow-up-right",
              onClick: moveToWallet
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
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              icon: "i-lucide-coins",
              onClick: moveToBonus
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Menu Bonus `);
                } else {
                  return [
                    createTextVNode(" Menu Bonus ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "grid grid-cols-1 gap-2 sm:grid-cols-2" }, [
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Nama"),
                  createVNode("p", { class: "mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(props.customer?.name ?? "—"), 1)
                ]),
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Username"),
                  createVNode("p", { class: "mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(props.networkProfile?.username ?? "—"), 1)
                ]),
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Level"),
                  createVNode("p", { class: "mt-0.5 text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(props.networkProfile?.level ?? "—"), 1)
                ]),
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Kode Referral"),
                  createVNode("div", { class: "mt-0.5 flex items-center gap-1.5" }, [
                    createVNode("p", { class: "truncate font-mono text-sm font-semibold tracking-wider text-gray-900 dark:text-white" }, toDisplayString(props.networkProfile?.referral_code ?? "—"), 1),
                    props.networkProfile?.username ? (openBlock(), createBlock(_component_UButton, {
                      key: 0,
                      color: "neutral",
                      variant: "ghost",
                      size: "xs",
                      icon: "i-lucide-copy",
                      class: "shrink-0 rounded-lg",
                      onClick: ($event) => unref(copyToClipboard)(props.networkProfile?.username ?? "")
                    }, null, 8, ["onClick"])) : createCommentVNode("", true)
                  ])
                ]),
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "NIK"),
                  createVNode("p", { class: "mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(numericNik.value), 1)
                ]),
                createVNode("div", { class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "No. WA / Telepon"),
                  createVNode("p", { class: "mt-0.5 truncate text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(formattedPhone.value), 1)
                ]),
                createVNode("div", { class: "sm:col-span-2 rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, "Nomor Rekening"),
                  createVNode("p", { class: "mt-0.5 truncate font-mono text-sm font-semibold tracking-wide text-gray-900 dark:text-white" }, toDisplayString(numericBankAccount.value), 1)
                ]),
                createVNode("div", { class: "sm:col-span-2 rounded-xl bg-primary-50 dark:bg-primary-950/30 px-3 py-2.5" }, [
                  createVNode("p", { class: "text-xs text-primary-600 dark:text-primary-400" }, "Saldo"),
                  createVNode("p", { class: "mt-0.5 text-lg font-extrabold text-primary-700 dark:text-primary-300" }, toDisplayString(unref(formatIDR)(props.networkProfile?.balance ?? 0)), 1)
                ]),
                createVNode("div", { class: "sm:col-span-2 grid grid-cols-1 gap-2 pt-1 sm:grid-cols-2" }, [
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "soft",
                    class: "rounded-xl",
                    icon: "i-lucide-arrow-up-right",
                    onClick: moveToWallet
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tarik Wallet ")
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    icon: "i-lucide-coins",
                    onClick: moveToBonus
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Menu Bonus ")
                    ]),
                    _: 1
                  })
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardNetworkProfile.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "DashboardNetworkStats",
  __ssrInlineRender: true,
  props: {
    networkStats: {}
  },
  emits: ["navigate"],
  setup(__props) {
    const { formatIDR } = useDashboard();
    const props = __props;
    const statItems = computed(() => [
      { label: "Jaringan Kiri", value: String(props.networkStats?.left_count ?? 0) },
      { label: "Jaringan Kanan", value: String(props.networkStats?.right_count ?? 0) },
      { label: "Total Downline", value: String(props.networkStats?.total_downline ?? 0) },
      { label: "Omset Group", value: formatIDR(props.networkStats?.omset_group ?? 0) },
      { label: "Omset NB Kiri", value: formatIDR(props.networkStats?.omset_nb_left ?? 0) },
      { label: "Omset NB Kanan", value: formatIDR(props.networkStats?.omset_nb_right ?? 0) },
      { label: "Omset Retail Kiri", value: formatIDR(props.networkStats?.omset_retail_left ?? 0) },
      { label: "Omset Retail Kanan", value: formatIDR(props.networkStats?.omset_retail_right ?? 0) }
    ]);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><div class="flex items-center gap-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-bar-chart-2",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`<p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Statistik Jaringan</p></div>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "ghost",
              size: "xs",
              class: "rounded-xl",
              icon: "i-lucide-arrow-right",
              onClick: ($event) => _ctx.$emit("navigate", "network")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Selengkapnya `);
                } else {
                  return [
                    createTextVNode(" Selengkapnya ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("div", { class: "flex items-center gap-3" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-bar-chart-2",
                    class: "size-5 text-gray-500 dark:text-gray-300"
                  }),
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Statistik Jaringan")
                ]),
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "ghost",
                  size: "xs",
                  class: "rounded-xl",
                  icon: "i-lucide-arrow-right",
                  onClick: ($event) => _ctx.$emit("navigate", "network")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Selengkapnya ")
                  ]),
                  _: 1
                }, 8, ["onClick"])
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="grid grid-cols-2 gap-2"${_scopeId}><!--[-->`);
            ssrRenderList(statItems.value, (item) => {
              _push2(`<div class="rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"${_scopeId}><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}>${ssrInterpolate(item.label)}</p><p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(item.value)}</p></div>`);
            });
            _push2(`<!--]--></div>`);
          } else {
            return [
              createVNode("div", { class: "grid grid-cols-2 gap-2" }, [
                (openBlock(true), createBlock(Fragment, null, renderList(statItems.value, (item) => {
                  return openBlock(), createBlock("div", {
                    key: item.label,
                    class: "rounded-xl bg-gray-50 dark:bg-gray-900 px-3 py-2.5"
                  }, [
                    createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, toDisplayString(item.label), 1),
                    createVNode("p", { class: "mt-0.5 text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(item.value), 1)
                  ]);
                }), 128))
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardNetworkStats.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "DashboardMemberCard",
  __ssrInlineRender: true,
  props: {
    customer: {},
    networkProfile: {}
  },
  emits: ["navigate"],
  setup(__props, { emit: __emit }) {
    const { appName } = useStoreData();
    const { formatDate } = useDashboard();
    const props = __props;
    const emit = __emit;
    const memberName = computed(() => props.customer?.name?.trim() || "Member");
    const memberUsername = computed(() => props.customer?.username?.trim() || "username-belum-diisi");
    const memberTier = computed(() => props.customer?.tier?.trim() || "Member");
    const memberId = computed(() => props.customer?.ewallet_id?.trim() || `MEM-${String(props.customer?.id ?? 0).padStart(6, "0")}`);
    const referralCode = computed(() => props.networkProfile?.referral_code?.trim() || "Belum tersedia");
    const memberSinceLabel = computed(() => formatDate(props.customer?.member_since));
    const downloadFileName = computed(() => `kartu-anggota-${memberUsername.value}.jpg`);
    const isDownloadingCard = ref(false);
    function escapeSvgText(value) {
      return value.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;");
    }
    const memberCardSvg = computed(() => {
      const storeLabel = escapeSvgText(appName.value ?? "Store");
      const nameLabel = escapeSvgText(memberName.value);
      const usernameLabel = escapeSvgText(`@${memberUsername.value}`);
      const tierLabel = escapeSvgText(memberTier.value);
      const memberIdLabel = escapeSvgText(memberId.value);
      const referralLabel = escapeSvgText(referralCode.value);
      const sinceLabel = escapeSvgText(memberSinceLabel.value);
      return `
<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720" fill="none">
  <defs>
    <linearGradient id="card-bg" x1="84" y1="72" x2="1196" y2="648" gradientUnits="userSpaceOnUse">
      <stop stop-color="#0F172A"/>
      <stop offset="0.52" stop-color="#1D4ED8"/>
      <stop offset="1" stop-color="#0EA5E9"/>
    </linearGradient>
    <radialGradient id="glow" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(1120 140) rotate(132.2) scale(480 480)">
      <stop stop-color="#FDE68A" stop-opacity="0.9"/>
      <stop offset="1" stop-color="#FDE68A" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <rect width="1280" height="720" rx="40" fill="url(#card-bg)"/>
  <rect x="40" y="40" width="1200" height="640" rx="32" fill="white" fill-opacity="0.06" stroke="white" stroke-opacity="0.12"/>
  <circle cx="1120" cy="140" r="280" fill="url(#glow)"/>
  <circle cx="180" cy="620" r="180" fill="white" fill-opacity="0.08"/>

  <text x="96" y="122" fill="#E2E8F0" font-size="34" font-family="Arial, sans-serif" font-weight="700">${storeLabel}</text>
  <text x="96" y="170" fill="#F8FAFC" font-size="56" font-family="Arial, sans-serif" font-weight="800">KARTU ANGGOTA</text>
  <text x="96" y="216" fill="#BFDBFE" font-size="26" font-family="Arial, sans-serif">Member dashboard identity card</text>

  <rect x="96" y="278" width="1088" height="300" rx="28" fill="white" fill-opacity="0.12" stroke="white" stroke-opacity="0.14"/>
  <text x="144" y="360" fill="#DBEAFE" font-size="24" font-family="Arial, sans-serif">Nama Anggota</text>
  <text x="144" y="430" fill="#FFFFFF" font-size="54" font-family="Arial, sans-serif" font-weight="800">${nameLabel}</text>
  <text x="144" y="480" fill="#BFDBFE" font-size="30" font-family="Arial, sans-serif">${usernameLabel}</text>

  <text x="144" y="640" fill="#E2E8F0" font-size="24" font-family="Arial, sans-serif">ID Anggota</text>
  <text x="144" y="676" fill="#FFFFFF" font-size="30" font-family="Arial, sans-serif" font-weight="700">${memberIdLabel}</text>

  <text x="640" y="640" fill="#E2E8F0" font-size="24" font-family="Arial, sans-serif">Kode Referral</text>
  <text x="640" y="676" fill="#FFFFFF" font-size="30" font-family="Arial, sans-serif" font-weight="700">${referralLabel}</text>

  <rect x="824" y="318" width="304" height="76" rx="20" fill="#082F49" fill-opacity="0.66"/>
  <text x="856" y="364" fill="#BAE6FD" font-size="22" font-family="Arial, sans-serif">Tier Keanggotaan</text>
  <text x="856" y="432" fill="#FFFFFF" font-size="42" font-family="Arial, sans-serif" font-weight="800">${tierLabel}</text>

  <rect x="824" y="466" width="304" height="76" rx="20" fill="#082F49" fill-opacity="0.66"/>
  <text x="856" y="512" fill="#BAE6FD" font-size="22" font-family="Arial, sans-serif">Member Sejak</text>
  <text x="856" y="580" fill="#FFFFFF" font-size="36" font-family="Arial, sans-serif" font-weight="800">${sinceLabel}</text>
</svg>`.trim();
    });
    const memberCardImageUrl = computed(() => `data:image/svg+xml;charset=utf-8,${encodeURIComponent(memberCardSvg.value)}`);
    function triggerDownload(url, fileName) {
      const link = document.createElement("a");
      link.href = url;
      link.download = fileName;
      link.rel = "noopener";
      document.body.append(link);
      link.click();
      link.remove();
    }
    async function downloadMemberCardAsJpg() {
      if (isDownloadingCard.value) {
        return;
      }
      isDownloadingCard.value = true;
      const svgBlob = new Blob([memberCardSvg.value], {
        type: "image/svg+xml;charset=utf-8"
      });
      const svgObjectUrl = URL.createObjectURL(svgBlob);
      const image = new Image();
      try {
        await new Promise((resolve, reject) => {
          image.onload = () => resolve();
          image.onerror = () => reject(new Error("Gagal memuat SVG kartu anggota."));
          image.src = svgObjectUrl;
        });
        const canvas = document.createElement("canvas");
        canvas.width = 1280;
        canvas.height = 720;
        const context = canvas.getContext("2d");
        if (!context) {
          throw new Error("Browser tidak mendukung canvas 2D.");
        }
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        const jpegBlob = await new Promise((resolve) => {
          canvas.toBlob(resolve, "image/jpeg", 0.92);
        });
        if (!jpegBlob) {
          throw new Error("Gagal mengonversi kartu anggota ke JPEG.");
        }
        const jpegObjectUrl = URL.createObjectURL(jpegBlob);
        triggerDownload(jpegObjectUrl, downloadFileName.value);
        setTimeout(() => URL.revokeObjectURL(jpegObjectUrl), 1e3);
      } finally {
        URL.revokeObjectURL(svgObjectUrl);
        isDownloadingCard.value = false;
      }
    }
    function moveToWallet() {
      emit("navigate", "wallet");
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Kartu Anggota</p>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-id-card",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Kartu Anggota"),
                createVNode(_component_UIcon, {
                  name: "i-lucide-id-card",
                  class: "size-5 text-gray-500 dark:text-gray-300"
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}><div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-950 shadow-sm dark:border-gray-800"${_scopeId}><img${ssrRenderAttr("src", memberCardImageUrl.value)}${ssrRenderAttr("alt", `Kartu anggota ${memberName.value}`)} class="aspect-[16/9] w-full object-cover"${_scopeId}></div><div class="space-y-2"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(memberName.value)}</p><p class="text-sm text-gray-600 dark:text-gray-300"${_scopeId}> Unduh kartu anggota berbentuk image untuk identitas member, data referral, dan informasi masa bergabung. </p></div><div class="grid grid-cols-1 gap-2 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "solid",
              class: "w-full rounded-xl",
              size: "sm",
              icon: "i-lucide-download",
              loading: isDownloadingCard.value,
              disabled: isDownloadingCard.value,
              onClick: downloadMemberCardAsJpg
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Download Kartu `);
                } else {
                  return [
                    createTextVNode(" Download Kartu ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              size: "sm",
              icon: "i-lucide-arrow-up-right",
              onClick: moveToWallet
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
            _push2(`</div><p class="text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Member sejak ${ssrInterpolate(memberSinceLabel.value)}. </p></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode("div", { class: "overflow-hidden rounded-2xl border border-gray-200 bg-gray-950 shadow-sm dark:border-gray-800" }, [
                  createVNode("img", {
                    src: memberCardImageUrl.value,
                    alt: `Kartu anggota ${memberName.value}`,
                    class: "aspect-[16/9] w-full object-cover"
                  }, null, 8, ["src", "alt"])
                ]),
                createVNode("div", { class: "space-y-2" }, [
                  createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(memberName.value), 1),
                  createVNode("p", { class: "text-sm text-gray-600 dark:text-gray-300" }, " Unduh kartu anggota berbentuk image untuk identitas member, data referral, dan informasi masa bergabung. ")
                ]),
                createVNode("div", { class: "grid grid-cols-1 gap-2 sm:grid-cols-2" }, [
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "solid",
                    class: "w-full rounded-xl",
                    size: "sm",
                    icon: "i-lucide-download",
                    loading: isDownloadingCard.value,
                    disabled: isDownloadingCard.value,
                    onClick: downloadMemberCardAsJpg
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Download Kartu ")
                    ]),
                    _: 1
                  }, 8, ["loading", "disabled"]),
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "outline",
                    class: "rounded-xl",
                    size: "sm",
                    icon: "i-lucide-arrow-up-right",
                    onClick: moveToWallet
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Tarik Wallet ")
                    ]),
                    _: 1
                  })
                ]),
                createVNode("p", { class: "text-xs text-gray-500 dark:text-gray-400" }, " Member sejak " + toDisplayString(memberSinceLabel.value) + ". ", 1)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardMemberCard.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "DashboardLifetimeCard",
  __ssrInlineRender: true,
  props: {
    stats: {},
    bonusTables: { default: () => ({
      referral_incentive: [],
      team_affiliate_commission: [],
      partner_team_commission: [],
      cashback_commission: [],
      promotions_rewards: [],
      retail_commission: [],
      lifetime_cash_rewards: []
    }) },
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
    const { formatIDR } = useDashboard();
    const props = __props;
    const promotionRewardMetrics = computed(
      () => props.bonusTables.promotions_rewards.reduce(
        (totals, row) => ({
          points: totals.points + Number(row.meta?.index_value ?? 0),
          omzet: totals.omzet + Number(row.meta?.bv ?? 0)
        }),
        { points: 0, omzet: 0 }
      )
    );
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Lifetime &amp; Promo</p>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-trophy",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Lifetime & Promo"),
                createVNode(_component_UIcon, {
                  name: "i-lucide-trophy",
                  class: "size-5 text-gray-500 dark:text-gray-300"
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}><div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-3 dark:border-gray-800 dark:bg-gray-900/40"${_scopeId}><div class="flex items-center justify-between gap-3"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>Lifetime</p><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"${_scopeId}>${ssrInterpolate(props.lifetimeRewards.summary.eligible_count)} siap klaim </span></div><div class="mt-3 space-y-2 text-sm"${_scopeId}><div class="flex items-center justify-between gap-3"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Bonus lifetime</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(props.stats?.bonus_lifetime ?? 0))}</span></div><div class="flex items-center justify-between gap-3"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Omzet kiri</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(props.lifetimeRewards.summary.accumulated_left))}</span></div><div class="flex items-center justify-between gap-3"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Omzet kanan</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(unref(formatIDR)(props.lifetimeRewards.summary.accumulated_right))}</span></div></div></div><div class="rounded-2xl border border-gray-200 bg-white/70 p-3 dark:border-gray-800 dark:bg-gray-950/40"${_scopeId}><div class="flex items-center justify-between gap-3"${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>Promotion Reward</p><span class="rounded-full bg-primary-100 px-2.5 py-1 text-[11px] font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300"${_scopeId}>${ssrInterpolate(props.stats?.promo_active ?? 0)} promo aktif </span></div><div class="mt-3 space-y-2 text-sm"${_scopeId}><div class="flex items-center justify-between gap-3"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Poin</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(promotionRewardMetrics.value.points.toLocaleString("id-ID", { maximumFractionDigits: 2 }))}</span></div><div class="flex items-center justify-between gap-3"${_scopeId}><span class="text-gray-600 dark:text-gray-300"${_scopeId}>Omzet/BV</span><span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(promotionRewardMetrics.value.omzet.toLocaleString("id-ID", { maximumFractionDigits: 2 }))}</span></div></div></div><div class="rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300"${_scopeId}><p class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Rekomendasi</p><ul class="mt-1 list-disc space-y-1 pl-5"${_scopeId}><li${_scopeId}>Aktifkan network untuk unlock bonus lebih besar.</li><li${_scopeId}>Cek promo sebelum checkout agar lebih hemat.</li></ul></div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-gray-50/80 p-3 dark:border-gray-800 dark:bg-gray-900/40" }, [
                  createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                    createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Lifetime"),
                    createVNode("span", { class: "rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300" }, toDisplayString(props.lifetimeRewards.summary.eligible_count) + " siap klaim ", 1)
                  ]),
                  createVNode("div", { class: "mt-3 space-y-2 text-sm" }, [
                    createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                      createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Bonus lifetime"),
                      createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(props.stats?.bonus_lifetime ?? 0)), 1)
                    ]),
                    createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                      createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Omzet kiri"),
                      createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(props.lifetimeRewards.summary.accumulated_left)), 1)
                    ]),
                    createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                      createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Omzet kanan"),
                      createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(unref(formatIDR)(props.lifetimeRewards.summary.accumulated_right)), 1)
                    ])
                  ])
                ]),
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-3 dark:border-gray-800 dark:bg-gray-950/40" }, [
                  createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                    createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Promotion Reward"),
                    createVNode("span", { class: "rounded-full bg-primary-100 px-2.5 py-1 text-[11px] font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300" }, toDisplayString(props.stats?.promo_active ?? 0) + " promo aktif ", 1)
                  ]),
                  createVNode("div", { class: "mt-3 space-y-2 text-sm" }, [
                    createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                      createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Poin"),
                      createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(promotionRewardMetrics.value.points.toLocaleString("id-ID", { maximumFractionDigits: 2 })), 1)
                    ]),
                    createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                      createVNode("span", { class: "text-gray-600 dark:text-gray-300" }, "Omzet/BV"),
                      createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(promotionRewardMetrics.value.omzet.toLocaleString("id-ID", { maximumFractionDigits: 2 })), 1)
                    ])
                  ])
                ]),
                createVNode("div", { class: "rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, [
                  createVNode("p", { class: "font-semibold text-gray-900 dark:text-white" }, "Rekomendasi"),
                  createVNode("ul", { class: "mt-1 list-disc space-y-1 pl-5" }, [
                    createVNode("li", null, "Aktifkan network untuk unlock bonus lebih besar."),
                    createVNode("li", null, "Cek promo sebelum checkout agar lebih hemat.")
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
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardLifetimeCard.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "DashboardSecurityZone",
  __ssrInlineRender: true,
  props: {
    securitySummary: {}
  },
  setup(__props) {
    const { formatDate } = useDashboard();
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$8;
      const _component_UBadge = _sfc_main$b;
      const _component_UIcon = _sfc_main$9;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between"${_scopeId}><div${_scopeId}><p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Keamanan Akun</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"${_scopeId}> Lindungi akun dan data kamu. Gunakan menu Lock untuk pengaturan keamanan. </p><div class="mt-2 flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              label: `Status akun: ${__props.securitySummary?.account_status_label ?? "Prospek"}`,
              color: "neutral",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              label: __props.securitySummary?.email_verified ? "Email terverifikasi" : "Email belum terverifikasi",
              color: __props.securitySummary?.email_verified ? "success" : "warning",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(`</div></div>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-shield",
              class: "size-5 text-gray-500 dark:text-gray-300"
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between" }, [
                createVNode("div", null, [
                  createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Keamanan Akun"),
                  createVNode("p", { class: "mt-1 text-sm text-gray-500 dark:text-gray-400" }, " Lindungi akun dan data kamu. Gunakan menu Lock untuk pengaturan keamanan. "),
                  createVNode("div", { class: "mt-2 flex flex-wrap items-center gap-2" }, [
                    createVNode(_component_UBadge, {
                      label: `Status akun: ${__props.securitySummary?.account_status_label ?? "Prospek"}`,
                      color: "neutral",
                      variant: "soft",
                      class: "rounded-full"
                    }, null, 8, ["label"]),
                    createVNode(_component_UBadge, {
                      label: __props.securitySummary?.email_verified ? "Email terverifikasi" : "Email belum terverifikasi",
                      color: __props.securitySummary?.email_verified ? "success" : "warning",
                      variant: "soft",
                      class: "rounded-full"
                    }, null, 8, ["label", "color"])
                  ])
                ]),
                createVNode(_component_UIcon, {
                  name: "i-lucide-shield",
                  class: "size-5 text-gray-500 dark:text-gray-300"
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="mb-4 rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300"${_scopeId}><p class="font-semibold text-gray-900 dark:text-white"${_scopeId}>Status keamanan</p><ul class="mt-1 list-disc space-y-1 pl-5"${_scopeId}><li class="${ssrRenderClass(__props.securitySummary?.has_bank_account ? "text-emerald-600 dark:text-emerald-400" : "")}"${_scopeId}> Data rekening ${ssrInterpolate(__props.securitySummary?.has_bank_account ? "sudah lengkap" : "belum lengkap")}</li><li class="${ssrRenderClass(__props.securitySummary?.has_npwp ? "text-emerald-600 dark:text-emerald-400" : "")}"${_scopeId}> NPWP ${ssrInterpolate(__props.securitySummary?.has_npwp ? "sudah terdaftar" : "belum terdaftar")}</li><li${_scopeId}> Order terakhir: <span class="font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(__props.securitySummary?.last_order_at ? unref(formatDate)(__props.securitySummary.last_order_at) : "Belum ada order")}</span></li></ul></div>`);
          } else {
            return [
              createVNode("div", { class: "mb-4 rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, [
                createVNode("p", { class: "font-semibold text-gray-900 dark:text-white" }, "Status keamanan"),
                createVNode("ul", { class: "mt-1 list-disc space-y-1 pl-5" }, [
                  createVNode("li", {
                    class: __props.securitySummary?.has_bank_account ? "text-emerald-600 dark:text-emerald-400" : ""
                  }, " Data rekening " + toDisplayString(__props.securitySummary?.has_bank_account ? "sudah lengkap" : "belum lengkap"), 3),
                  createVNode("li", {
                    class: __props.securitySummary?.has_npwp ? "text-emerald-600 dark:text-emerald-400" : ""
                  }, " NPWP " + toDisplayString(__props.securitySummary?.has_npwp ? "sudah terdaftar" : "belum terdaftar"), 3),
                  createVNode("li", null, [
                    createTextVNode(" Order terakhir: "),
                    createVNode("span", { class: "font-semibold text-gray-900 dark:text-white" }, toDisplayString(__props.securitySummary?.last_order_at ? unref(formatDate)(__props.securitySummary.last_order_at) : "Belum ada order"), 1)
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
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/DashboardSecurityZone.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "DashboardHome",
  __ssrInlineRender: true,
  props: {
    customer: {},
    defaultAddress: {},
    stats: {},
    bonusTables: {},
    lifetimeRewards: {},
    networkProfile: {},
    networkStats: {},
    securitySummary: {}
  },
  emits: ["navigate"],
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}>`);
      _push(ssrRenderComponent(_sfc_main$7, {
        stats: __props.stats,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$6, {
        "default-address": __props.defaultAddress,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(`<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">`);
      _push(ssrRenderComponent(_sfc_main$5, {
        customer: __props.customer,
        "network-profile": __props.networkProfile,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$4, {
        "network-stats": __props.networkStats,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(`</div><div class="grid grid-cols-1 gap-4 lg:grid-cols-2">`);
      _push(ssrRenderComponent(_sfc_main$3, {
        customer: __props.customer,
        "network-profile": __props.networkProfile,
        onNavigate: ($event) => _ctx.$emit("navigate", $event)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$2, {
        stats: __props.stats,
        "bonus-tables": __props.bonusTables,
        "lifetime-rewards": __props.lifetimeRewards
      }, null, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_sfc_main$1, { "security-summary": __props.securitySummary }, null, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/DashboardHome.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
