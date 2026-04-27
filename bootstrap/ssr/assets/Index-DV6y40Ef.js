import { a as _sfc_main$c, _ as _sfc_main$f, b as _sfc_main$g } from "./Page-ws952gfV.js";
import { useSlots, computed, unref, mergeProps, withCtx, createVNode, openBlock, createBlock, renderSlot, createCommentVNode, useSSRContext, ref, defineComponent, toDisplayString, isRef, createTextVNode, onMounted, onBeforeUnmount, watch, defineAsyncComponent, resolveDynamicComponent, toHandlers } from "vue";
import { ssrRenderComponent, ssrRenderClass, ssrRenderSlot, ssrRenderAttrs, ssrRenderList, ssrInterpolate, ssrRenderAttr, ssrRenderVNode } from "vue/server-renderer";
import { router, usePage } from "@inertiajs/vue3";
import { _ as _sfc_main$e } from "./SeoHead-CrFJik-H.js";
import { _ as _sfc_main$9 } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$b } from "./Card-CvchAxCK.js";
import { Primitive } from "reka-ui";
import { t as tv, u as useAppConfig, _ as _sfc_main$8 } from "./Icon-Chcm7u9q.js";
import { b as _export_sfc, c as _sfc_main$a, a as _sfc_main$d } from "./AppLayout-BFrtNV54.js";
import { u as useDashboard } from "./useDashboard-DR5F4MRN.js";
import { _ as _sfc_main$7 } from "./Button-DLZCZWnW.js";
import "./useStoreData-DrTMI0On.js";
import "hookable";
import "@vueuse/core";
import "defu";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "./useToast-CTuSIf9f.js";
import "./usePortal-EQErrF6h.js";
import "./Input-DFvIE7JC.js";
import "./Separator-CuyFd3xv.js";
import "reka-ui/namespaced";
import "./Checkbox-PZ4-MUog.js";
import "vaul-vue";
import "ufo";
const theme = {
  "slots": {
    "root": "hidden overflow-y-auto lg:block lg:max-h-[calc(100vh-var(--ui-header-height))] lg:sticky lg:top-(--ui-header-height) py-8 lg:ps-4 lg:-ms-4 lg:pe-6.5",
    "container": "relative",
    "top": "sticky -top-8 -mt-8 pointer-events-none z-[1]",
    "topHeader": "h-8 bg-default -mx-4 px-4",
    "topBody": "bg-default relative pointer-events-auto flex flex-col -mx-4 px-4",
    "topFooter": "h-8 bg-gradient-to-b from-default -mx-4 px-4"
  }
};
const _sfc_main$6 = {
  __name: "PageAside",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false, default: "aside" },
    class: { type: null, required: false },
    ui: { type: null, required: false }
  },
  setup(__props) {
    const props = __props;
    const slots = useSlots();
    const appConfig = useAppConfig();
    const ui = computed(() => tv({ extend: tv(theme), ...appConfig.ui?.pageAside || {} })());
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Primitive), mergeProps({
        as: __props.as,
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] })
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div data-slot="container" class="${ssrRenderClass(ui.value.container({ class: props.ui?.container }))}"${_scopeId}>`);
            if (!!slots.top) {
              _push2(`<div data-slot="top" class="${ssrRenderClass(ui.value.top({ class: props.ui?.top }))}"${_scopeId}><div data-slot="topHeader" class="${ssrRenderClass(ui.value.topHeader({ class: props.ui?.topHeader }))}"${_scopeId}></div><div data-slot="topBody" class="${ssrRenderClass(ui.value.topBody({ class: props.ui?.topBody }))}"${_scopeId}>`);
              ssrRenderSlot(_ctx.$slots, "top", {}, null, _push2, _parent2, _scopeId);
              _push2(`</div><div data-slot="topFooter" class="${ssrRenderClass(ui.value.topFooter({ class: props.ui?.topFooter }))}"${_scopeId}></div></div>`);
            } else {
              _push2(`<!---->`);
            }
            ssrRenderSlot(_ctx.$slots, "default", {}, null, _push2, _parent2, _scopeId);
            ssrRenderSlot(_ctx.$slots, "bottom", {}, null, _push2, _parent2, _scopeId);
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", {
                "data-slot": "container",
                class: ui.value.container({ class: props.ui?.container })
              }, [
                !!slots.top ? (openBlock(), createBlock("div", {
                  key: 0,
                  "data-slot": "top",
                  class: ui.value.top({ class: props.ui?.top })
                }, [
                  createVNode("div", {
                    "data-slot": "topHeader",
                    class: ui.value.topHeader({ class: props.ui?.topHeader })
                  }, null, 2),
                  createVNode("div", {
                    "data-slot": "topBody",
                    class: ui.value.topBody({ class: props.ui?.topBody })
                  }, [
                    renderSlot(_ctx.$slots, "top")
                  ], 2),
                  createVNode("div", {
                    "data-slot": "topFooter",
                    class: ui.value.topFooter({ class: props.ui?.topFooter })
                  }, null, 2)
                ], 2)) : createCommentVNode("", true),
                renderSlot(_ctx.$slots, "default"),
                renderSlot(_ctx.$slots, "bottom")
              ], 2)
            ];
          }
        }),
        _: 3
      }, _parent));
    };
  }
};
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/PageAside.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
function useDashboardAsideMenuState(options) {
  const mobileMenuOpen = ref(false);
  function openMobileMenu() {
    mobileMenuOpen.value = true;
  }
  function closeMobileMenu() {
    mobileMenuOpen.value = false;
  }
  function selectSection(section, closeAfterSelect = false) {
    options.onSelect(section);
    if (closeAfterSelect) {
      closeMobileMenu();
    }
  }
  return {
    mobileMenuOpen,
    openMobileMenu,
    closeMobileMenu,
    selectSection
  };
}
function useDashboardAsideLinks() {
  function isLabelLink(link) {
    return "type" in link && link.type === "label";
  }
  function isActionLink(link) {
    return !isLabelLink(link);
  }
  return {
    isLabelLink,
    isActionLink
  };
}
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "DashboardAsideMenuNav",
  __ssrInlineRender: true,
  props: {
    active: {},
    links: {},
    keyPrefix: { default: "aside" }
  },
  emits: ["select"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const { isLabelLink, isActionLink } = useDashboardAsideLinks();
    function onSelect(section) {
      emit("select", section);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$7;
      const _component_UIcon = _sfc_main$8;
      _push(`<nav${ssrRenderAttrs(mergeProps({ class: "space-y-1" }, _attrs))}><!--[-->`);
      ssrRenderList(props.links, (link, index) => {
        _push(`<!--[-->`);
        if (unref(isLabelLink)(link)) {
          _push(`<div class="pt-3 pb-1"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">${ssrInterpolate(link.label)}</p></div>`);
        } else if (unref(isActionLink)(link)) {
          _push(ssrRenderComponent(_component_UButton, {
            color: link.color ?? "neutral",
            variant: props.active === link.value ? "solid" : "ghost",
            class: "w-full justify-start rounded-xl",
            icon: link.icon,
            ui: { base: "w-full justify-start" },
            onClick: ($event) => onSelect(link.value)
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<span class="flex-1 text-left"${_scopeId}>${ssrInterpolate(link.label)}</span>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-chevron-right",
                  class: "size-4 opacity-60"
                }, null, _parent2, _scopeId));
              } else {
                return [
                  createVNode("span", { class: "flex-1 text-left" }, toDisplayString(link.label), 1),
                  createVNode(_component_UIcon, {
                    name: "i-lucide-chevron-right",
                    class: "size-4 opacity-60"
                  })
                ];
              }
            }),
            _: 2
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        _push(`<!--]-->`);
      });
      _push(`<!--]--></nav>`);
    };
  }
});
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/index/aside/DashboardAsideMenuNav.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "DashboardAsideMenuTrigger",
  __ssrInlineRender: true,
  props: {
    walletBalance: { default: 0 }
  },
  emits: ["open"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const { formatIDR } = useDashboard();
    function openMenu() {
      emit("open");
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$7;
      const _component_UBadge = _sfc_main$9;
      _push(ssrRenderComponent(_component_UButton, mergeProps({
        color: "neutral",
        variant: "outline",
        icon: "i-lucide-menu",
        class: "w-full justify-start rounded-xl",
        onClick: openMenu
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<span class="flex-1 text-left"${_scopeId}>Menu Akun</span>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              label: unref(formatIDR)(props.walletBalance ?? 0),
              color: "primary",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode("span", { class: "flex-1 text-left" }, "Menu Akun"),
              createVNode(_component_UBadge, {
                label: unref(formatIDR)(props.walletBalance ?? 0),
                color: "primary",
                variant: "soft",
                class: "rounded-full"
              }, null, 8, ["label"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/index/aside/DashboardAsideMenuTrigger.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = {};
function _sfc_ssrRender(_ctx, _push, _parent, _attrs) {
  const _component_UIcon = _sfc_main$8;
  _push(`<div${ssrRenderAttrs(mergeProps({ class: "rounded-2xl border border-gray-200 bg-white/70 p-3 text-xs text-gray-600 backdrop-blur dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300" }, _attrs))}><div class="flex items-start gap-2">`);
  _push(ssrRenderComponent(_component_UIcon, {
    name: "i-lucide-info",
    class: "mt-0.5 size-4 opacity-70"
  }, null, _parent));
  _push(`<div class="min-w-0"><p class="font-semibold text-gray-900 dark:text-white">Tips cepat</p><ul class="mt-1 list-disc pl-5 space-y-1"><li>Gunakan alamat default agar checkout lebih cepat.</li><li>Cek pesanan &quot;Menunggu&quot; agar tidak kedaluwarsa.</li><li>Pastikan saldo wallet cukup sebelum bayar.</li></ul></div></div></div>`);
}
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/index/aside/DashboardAsideTipsCard.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const DashboardAsideTipsCard = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["ssrRender", _sfc_ssrRender]]);
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "DashboardAsideMenu",
  __ssrInlineRender: true,
  props: {
    active: {},
    links: {},
    walletBalance: { default: 0 }
  },
  emits: ["update:active"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const { formatIDR } = useDashboard();
    const { mobileMenuOpen, openMobileMenu, selectSection } = useDashboardAsideMenuState({
      onSelect: (section) => emit("update:active", section)
    });
    function selectDesktopSection(section) {
      selectSection(section);
    }
    function selectMobileSection(section) {
      selectSection(section, true);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_USlideover = _sfc_main$a;
      const _component_UPageAside = _sfc_main$6;
      const _component_UCard = _sfc_main$b;
      const _component_UBadge = _sfc_main$9;
      _push(`<!--[--><div class="lg:hidden">`);
      _push(ssrRenderComponent(_sfc_main$4, {
        "wallet-balance": props.walletBalance,
        onOpen: unref(openMobileMenu)
      }, null, _parent));
      _push(ssrRenderComponent(_component_USlideover, {
        open: unref(mobileMenuOpen),
        "onUpdate:open": ($event) => isRef(mobileMenuOpen) ? mobileMenuOpen.value = $event : null,
        portal: true,
        side: "left",
        title: "Menu Akun",
        description: "Kelola akun & aktivitasmu",
        ui: { overlay: "z-[90]", content: "z-[100] w-full sm:max-w-sm" }
      }, {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$5, {
              active: props.active,
              links: props.links,
              "key-prefix": "mobile-aside",
              onSelect: selectMobileSection
            }, null, _parent2, _scopeId));
            _push2(`<div class="mt-5"${_scopeId}>`);
            _push2(ssrRenderComponent(DashboardAsideTipsCard, null, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode(_sfc_main$5, {
                active: props.active,
                links: props.links,
                "key-prefix": "mobile-aside",
                onSelect: selectMobileSection
              }, null, 8, ["active", "links"]),
              createVNode("div", { class: "mt-5" }, [
                createVNode(DashboardAsideTipsCard)
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_component_UPageAside, { class: "hidden lg:block lg:col-span-3" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UCard, { class: "rounded-2xl" }, {
              header: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex items-start justify-between"${_scopeId2}><div${_scopeId2}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId2}>Menu Akun</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId2}>Kelola akun &amp; aktivitasmu</p></div>`);
                  _push3(ssrRenderComponent(_component_UBadge, {
                    label: unref(formatIDR)(props.walletBalance ?? 0),
                    color: "primary",
                    variant: "soft",
                    class: "rounded-full"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex items-start justify-between" }, [
                      createVNode("div", null, [
                        createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Menu Akun"),
                        createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, "Kelola akun & aktivitasmu")
                      ]),
                      createVNode(_component_UBadge, {
                        label: unref(formatIDR)(props.walletBalance ?? 0),
                        color: "primary",
                        variant: "soft",
                        class: "rounded-full"
                      }, null, 8, ["label"])
                    ])
                  ];
                }
              }),
              footer: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(DashboardAsideTipsCard, null, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(DashboardAsideTipsCard)
                  ];
                }
              }),
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_sfc_main$5, {
                    active: props.active,
                    links: props.links,
                    "key-prefix": "desktop-aside",
                    onSelect: selectDesktopSection
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_sfc_main$5, {
                      active: props.active,
                      links: props.links,
                      "key-prefix": "desktop-aside",
                      onSelect: selectDesktopSection
                    }, null, 8, ["active", "links"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UCard, { class: "rounded-2xl" }, {
                header: withCtx(() => [
                  createVNode("div", { class: "flex items-start justify-between" }, [
                    createVNode("div", null, [
                      createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, "Menu Akun"),
                      createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, "Kelola akun & aktivitasmu")
                    ]),
                    createVNode(_component_UBadge, {
                      label: unref(formatIDR)(props.walletBalance ?? 0),
                      color: "primary",
                      variant: "soft",
                      class: "rounded-full"
                    }, null, 8, ["label"])
                  ])
                ]),
                footer: withCtx(() => [
                  createVNode(DashboardAsideTipsCard)
                ]),
                default: withCtx(() => [
                  createVNode(_sfc_main$5, {
                    active: props.active,
                    links: props.links,
                    "key-prefix": "desktop-aside",
                    onSelect: selectDesktopSection
                  }, null, 8, ["active", "links"])
                ]),
                _: 1
              })
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<!--]-->`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/index/DashboardAsideMenu.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "DashboardPageHeader",
  __ssrInlineRender: true,
  props: {
    customer: { default: null },
    networkProfile: { default: null },
    promoActive: { default: 0 }
  },
  emits: ["logout"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const { formatDate, formatPhoneDisplay, copyToClipboard } = useDashboard();
    const referralPath = computed(() => {
      const username = props.networkProfile?.username?.trim();
      if (!username) {
        return null;
      }
      return `/?username=${encodeURIComponent(username)}`;
    });
    const referralUrlLabel = computed(() => {
      if (!referralPath.value) {
        return "—";
      }
      if (typeof window === "undefined") {
        return referralPath.value;
      }
      return `${window.location.origin}${referralPath.value}`;
    });
    function handleLogout() {
      emit("logout");
    }
    function handleCopyReferral() {
      const username = props.networkProfile?.username ?? "";
      void copyToClipboard(username);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UPageHeader = _sfc_main$c;
      const _component_UIcon = _sfc_main$8;
      const _component_UBadge = _sfc_main$9;
      const _component_UButton = _sfc_main$7;
      _push(ssrRenderComponent(_component_UPageHeader, mergeProps({ class: "mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 pt-8" }, _attrs), {
        title: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center gap-3"${_scopeId}><div class="size-10 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 grid place-items-center"${_scopeId}>`);
            if (props.customer?.avatar_url) {
              _push2(`<img${ssrRenderAttr("src", props.customer.avatar_url)}${ssrRenderAttr("alt", props.customer?.name || "User")} class="h-full w-full object-cover"${_scopeId}>`);
            } else {
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-user",
                class: "size-5 text-gray-500 dark:text-gray-300"
              }, null, _parent2, _scopeId));
            }
            _push2(`</div><div class="min-w-0"${_scopeId}><p class="truncate text-xl font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(props.customer?.name || "Dashboard Akun")}</p><p class="truncate text-sm text-gray-600 dark:text-gray-300"${_scopeId}>${ssrInterpolate(props.customer?.email || "—")} `);
            if (props.customer?.phone) {
              _push2(`<span class="mx-2 text-gray-400"${_scopeId}>•</span>`);
            } else {
              _push2(`<!---->`);
            }
            if (props.customer?.phone) {
              _push2(`<span${_scopeId}>${ssrInterpolate(unref(formatPhoneDisplay)(props.customer?.phone))}</span>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</p></div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center gap-3" }, [
                createVNode("div", { class: "size-10 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 grid place-items-center" }, [
                  props.customer?.avatar_url ? (openBlock(), createBlock("img", {
                    key: 0,
                    src: props.customer.avatar_url,
                    alt: props.customer?.name || "User",
                    class: "h-full w-full object-cover"
                  }, null, 8, ["src", "alt"])) : (openBlock(), createBlock(_component_UIcon, {
                    key: 1,
                    name: "i-lucide-user",
                    class: "size-5 text-gray-500 dark:text-gray-300"
                  }))
                ]),
                createVNode("div", { class: "min-w-0" }, [
                  createVNode("p", { class: "truncate text-xl font-semibold text-gray-900 dark:text-white" }, toDisplayString(props.customer?.name || "Dashboard Akun"), 1),
                  createVNode("p", { class: "truncate text-sm text-gray-600 dark:text-gray-300" }, [
                    createTextVNode(toDisplayString(props.customer?.email || "—") + " ", 1),
                    props.customer?.phone ? (openBlock(), createBlock("span", {
                      key: 0,
                      class: "mx-2 text-gray-400"
                    }, "•")) : createCommentVNode("", true),
                    props.customer?.phone ? (openBlock(), createBlock("span", { key: 1 }, toDisplayString(unref(formatPhoneDisplay)(props.customer?.phone)), 1)) : createCommentVNode("", true)
                  ])
                ])
              ])
            ];
          }
        }),
        description: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-2"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              label: props.customer?.tier ? `Member ${props.customer.tier}` : "Member",
              color: "neutral",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              label: `Member sejak ${unref(formatDate)(props.customer?.member_since)}`,
              color: "neutral",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              label: props.promoActive ? `${props.promoActive} promo aktif` : "Tidak ada promo aktif",
              color: props.promoActive ? "primary" : "neutral",
              variant: "soft",
              class: "rounded-full"
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
            if (referralPath.value) {
              _push2(`<div class="flex flex-wrap items-center gap-2"${_scopeId}><span class="text-xs font-medium text-gray-500 dark:text-gray-400"${_scopeId}>Link Referral:</span><a${ssrRenderAttr("href", referralPath.value)} target="_blank" rel="noopener noreferrer" class="max-w-full truncate text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300 dark:hover:text-primary-200"${_scopeId}>${ssrInterpolate(referralUrlLabel.value)}</a>`);
              _push2(ssrRenderComponent(_component_UButton, {
                color: "neutral",
                variant: "ghost",
                size: "xs",
                icon: "i-lucide-copy",
                class: "rounded-lg",
                onClick: handleCopyReferral
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Copy `);
                  } else {
                    return [
                      createTextVNode(" Copy ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-2" }, [
                createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                  createVNode(_component_UBadge, {
                    label: props.customer?.tier ? `Member ${props.customer.tier}` : "Member",
                    color: "neutral",
                    variant: "soft",
                    class: "rounded-full"
                  }, null, 8, ["label"]),
                  createVNode(_component_UBadge, {
                    label: `Member sejak ${unref(formatDate)(props.customer?.member_since)}`,
                    color: "neutral",
                    variant: "soft",
                    class: "rounded-full"
                  }, null, 8, ["label"]),
                  createVNode(_component_UBadge, {
                    label: props.promoActive ? `${props.promoActive} promo aktif` : "Tidak ada promo aktif",
                    color: props.promoActive ? "primary" : "neutral",
                    variant: "soft",
                    class: "rounded-full"
                  }, null, 8, ["label", "color"])
                ]),
                referralPath.value ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "flex flex-wrap items-center gap-2"
                }, [
                  createVNode("span", { class: "text-xs font-medium text-gray-500 dark:text-gray-400" }, "Link Referral:"),
                  createVNode("a", {
                    href: referralPath.value,
                    target: "_blank",
                    rel: "noopener noreferrer",
                    class: "max-w-full truncate text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-300 dark:hover:text-primary-200"
                  }, toDisplayString(referralUrlLabel.value), 9, ["href"]),
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "ghost",
                    size: "xs",
                    icon: "i-lucide-copy",
                    class: "rounded-lg",
                    onClick: handleCopyReferral
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Copy ")
                    ]),
                    _: 1
                  })
                ])) : createCommentVNode("", true)
              ])
            ];
          }
        }),
        right: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              to: "/products",
              color: "primary",
              variant: "solid",
              class: "rounded-xl",
              icon: "i-lucide-shopping-bag"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Belanja `);
                } else {
                  return [
                    createTextVNode(" Belanja ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              to: "/cart",
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              icon: "i-lucide-shopping-cart"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Keranjang `);
                } else {
                  return [
                    createTextVNode(" Keranjang ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "ghost",
              class: "rounded-xl",
              icon: "i-lucide-log-out",
              onClick: handleLogout
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Keluar `);
                } else {
                  return [
                    createTextVNode(" Keluar ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                createVNode(_component_UButton, {
                  to: "/products",
                  color: "primary",
                  variant: "solid",
                  class: "rounded-xl",
                  icon: "i-lucide-shopping-bag"
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Belanja ")
                  ]),
                  _: 1
                }),
                createVNode(_component_UButton, {
                  to: "/cart",
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-xl",
                  icon: "i-lucide-shopping-cart"
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Keranjang ")
                  ]),
                  _: 1
                }),
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "ghost",
                  class: "rounded-xl",
                  icon: "i-lucide-log-out",
                  onClick: handleLogout
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Keluar ")
                  ]),
                  _: 1
                })
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/index/DashboardPageHeader.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const componentMap = {
  dashboard: defineAsyncComponent(() => import("./DashboardHome-DCMyyAs_.js")),
  form_account: defineAsyncComponent(() => import("./FormAccount-D3R2zJ6V.js")),
  orders: defineAsyncComponent(() => import("./Orders-BGKWAUh7.js")),
  promo: defineAsyncComponent(() => import("./Promo-C4wHRI-n.js")),
  wallet: defineAsyncComponent(() => import("./Wallet--e_1z7w7.js")),
  zenner: defineAsyncComponent(() => import("./Zenner-DKRV35DM.js")),
  mitra: defineAsyncComponent(() => import("./Mitra-C4xTaPsT.js")),
  network: defineAsyncComponent(() => import("./Network-BQh5QNec.js")),
  generation_network: defineAsyncComponent(() => import("./GenerationNetwork-Bw6j4zaF.js")),
  bonus: defineAsyncComponent(() => import("./Bonus-CYXjmb7P.js")),
  lifetime: defineAsyncComponent(() => import("./Lifetime-D0fqOugE.js")),
  addresses: defineAsyncComponent(() => import("./Addresses-BkuYO7cN.js"))
};
const dashboardPropKeys = [
  "customer",
  "defaultAddress",
  "stats",
  "bonusTables",
  "lifetimeRewards",
  "networkProfile",
  "networkStats",
  "securitySummary"
];
const addressPropKeys = [
  "addresses",
  "defaultAddress"
];
const formAccountPropKeys = ["customer", "defaultAddress"];
const ordersPropKeys = ["orders"];
const promoPropKeys = ["promos"];
const zennerPropKeys = ["zennerCategories", "zennerContents"];
const walletPropKeys = ["customer", "stats", "walletTransactions", "hasPendingWithdrawal", "midtrans", "waConfirmationUrl", "waGatewayHematMode"];
const bonusPropKeys = ["bonusStats", "bonusTables"];
const lifetimePropKeys = ["lifetimeRewards"];
const mitraPropKeys = ["currentCustomerId", "activeMembers", "passiveMembers", "prospectMembers", "hasLeft", "hasRight"];
const networkPropKeys = ["currentCustomerId", "passiveMembers", "binaryTree", "networkTreeStats", "networkTreeRootId"];
const generationNetworkPropKeys = ["generationNetwork"];
const mitraNetworkSections = /* @__PURE__ */ new Set(["zenner", "mitra", "network", "generation_network", "bonus", "lifetime"]);
function isDashboardSection(value) {
  return Object.prototype.hasOwnProperty.call(componentMap, value);
}
function resolveInitialSection(url) {
  const query = url.split("?")[1] ?? "";
  const section = new URLSearchParams(query).get("section");
  if (section && isDashboardSection(section)) {
    return section;
  }
  return "dashboard";
}
function useDashboardSections(props, initialUrl) {
  const canAccessMitraNetwork = computed(() => {
    const status = Number(props.value.customer?.status ?? 0);
    const hasPlacement = props.value.customer?.has_placement === true;
    return status === 3 && hasPlacement;
  });
  function isSectionAllowed(section) {
    if (!canAccessMitraNetwork.value && mitraNetworkSections.has(section)) {
      return false;
    }
    return true;
  }
  const initialSection = resolveInitialSection(initialUrl);
  const active = ref(isSectionAllowed(initialSection) ? initialSection : "dashboard");
  const currentComponent = computed(() => componentMap[active.value] ?? componentMap.dashboard);
  const currentComponentProps = computed(() => {
    switch (active.value) {
      case "dashboard":
        return {
          customer: props.value.customer,
          defaultAddress: props.value.defaultAddress,
          stats: props.value.stats,
          bonusTables: props.value.bonusTables,
          lifetimeRewards: props.value.lifetimeRewards,
          networkProfile: props.value.networkProfile,
          networkStats: props.value.networkStats,
          securitySummary: props.value.securitySummary
        };
      case "addresses":
        return {
          addresses: props.value.addresses
        };
      case "form_account":
        return {
          customer: props.value.customer,
          defaultAddress: props.value.defaultAddress
        };
      case "orders":
        return {
          orders: props.value.orders,
          midtrans: props.value.midtrans
        };
      case "wallet":
        return {
          customer: props.value.customer,
          transactions: props.value.walletTransactions,
          hasPendingWithdrawal: props.value.hasPendingWithdrawal,
          walletBalance: props.value.stats?.wallet_balance,
          midtrans: props.value.midtrans,
          waConfirmationUrl: props.value.waConfirmationUrl ?? null,
          waGatewayHematMode: props.value.waGatewayHematMode ?? false
        };
      case "bonus":
        return {
          bonusStats: props.value.bonusStats,
          bonusTables: props.value.bonusTables
        };
      case "lifetime":
        return {
          lifetimeRewards: props.value.lifetimeRewards
        };
      case "promo":
        return {
          promos: props.value.promos
        };
      case "zenner":
        return {
          categories: props.value.zennerCategories,
          contents: props.value.zennerContents
        };
      case "mitra":
        return {
          activeMembers: props.value.activeMembers,
          passiveMembers: props.value.passiveMembers,
          prospectMembers: props.value.prospectMembers,
          hasLeft: props.value.hasLeft,
          hasRight: props.value.hasRight,
          currentCustomerId: props.value.currentCustomerId
        };
      case "network":
        return {
          binaryTree: props.value.binaryTree,
          networkTreeStats: props.value.networkTreeStats,
          passiveMembers: props.value.passiveMembers,
          currentCustomerId: props.value.currentCustomerId,
          networkTreeRootId: props.value.networkTreeRootId ?? null
        };
      case "generation_network":
        return {
          generationNetwork: props.value.generationNetwork
        };
      default:
        return {};
    }
  });
  const currentComponentListeners = computed(() => {
    if (active.value !== "dashboard") {
      return {};
    }
    return {
      navigate: (value) => {
        if (isDashboardSection(value)) {
          setActive(value);
        }
      }
    };
  });
  const asideLinks = computed(() => {
    const accountLinks = [
      { label: "Akun", type: "label" },
      { label: "Info Pengguna", icon: "i-lucide-user", value: "dashboard" },
      { label: "Form Pengguna", icon: "i-lucide-form", value: "form_account" },
      { label: "Order", icon: "i-lucide-package-search", value: "orders" },
      { label: "Promo", icon: "i-lucide-ticket", value: "promo" },
      { label: "Wallet", icon: "i-lucide-wallet", value: "wallet" },
      { label: "Alamat", icon: "i-lucide-map-pin", value: "addresses" }
    ];
    if (!canAccessMitraNetwork.value) {
      return accountLinks;
    }
    return [
      ...accountLinks,
      { label: "Mitra & Network", type: "label" },
      { label: "Zenner", icon: "i-lucide-sparkles", value: "zenner" },
      { label: "Mitra", icon: "i-lucide-handshake", value: "mitra" },
      { label: "Network", icon: "i-lucide-network", value: "network" },
      { label: "Jaringan Generasi", icon: "i-lucide-users-round", value: "generation_network" },
      { label: "Bonus", icon: "i-lucide-coins", value: "bonus" },
      { label: "Lifetime", icon: "i-lucide-trophy", value: "lifetime" }
    ];
  });
  function getSectionOnlyProps(section) {
    switch (section) {
      case "dashboard":
        return dashboardPropKeys;
      case "addresses":
        return addressPropKeys;
      case "form_account":
        return formAccountPropKeys;
      case "orders":
        return ordersPropKeys;
      case "promo":
        return promoPropKeys;
      case "zenner":
        return zennerPropKeys;
      case "wallet":
        return walletPropKeys;
      case "bonus":
        return bonusPropKeys;
      case "lifetime":
        return lifetimePropKeys;
      case "mitra":
        return mitraPropKeys;
      case "network":
        return networkPropKeys;
      case "generation_network":
        return generationNetworkPropKeys;
      default:
        return [];
    }
  }
  function buildSectionQuery(section) {
    const query = { section };
    if (section === "orders") {
      const ordersPayload = props.value.orders;
      const page = Number(ordersPayload?.current_page ?? 1);
      const filters = ordersPayload?.filters;
      const search = String(filters?.q ?? "").trim();
      const status = String(filters?.status ?? "all").trim().toLowerCase();
      const sort = String(filters?.sort ?? "newest").trim().toLowerCase();
      if (page > 1) {
        query.orders_page = page;
      }
      if (search !== "") {
        query.orders_q = search;
      }
      if (status !== "" && status !== "all") {
        query.orders_status = status;
      }
      if (sort !== "" && sort !== "newest") {
        query.orders_sort = sort;
      }
      return query;
    }
    if (section === "wallet") {
      const walletPayload = props.value.walletTransactions;
      const page = Number(walletPayload?.current_page ?? 1);
      const filters = walletPayload?.filters;
      const search = (filters?.search ?? "").trim();
      const type = (filters?.type ?? "").trim();
      const status = (filters?.status ?? "").trim();
      if (page > 1) {
        query.wallet_page = page;
      }
      if (search !== "") {
        query.wallet_search = search;
      }
      if (type !== "" && type !== "all") {
        query.wallet_type = type;
      }
      if (status !== "" && status !== "all") {
        query.wallet_status = status;
      }
      return query;
    }
    if (section === "generation_network") {
      const generationPayload = props.value.generationNetwork;
      const page = Number(generationPayload?.current_page ?? 1);
      const level = Number(generationPayload?.filters?.level ?? 0);
      if (page > 1) {
        query.generation_page = page;
      }
      if (level > 0) {
        query.generation_level = level;
      }
    }
    return query;
  }
  function visitSection(section) {
    router.get("/dashboard", buildSectionQuery(section), {
      only: getSectionOnlyProps(section),
      preserveState: true,
      preserveScroll: true,
      replace: true
    });
  }
  function reloadDashboardSnapshot() {
    if (active.value !== "dashboard") {
      return;
    }
    router.reload({
      only: dashboardPropKeys
    });
  }
  function reloadAddressSnapshot() {
    if (active.value !== "addresses") {
      return;
    }
    router.reload({
      only: addressPropKeys
    });
  }
  function reloadFormAccountSnapshot() {
    if (active.value !== "form_account") {
      return;
    }
    router.reload({
      only: formAccountPropKeys
    });
  }
  function reloadMitraSnapshot() {
    if (active.value !== "mitra") {
      return;
    }
    router.reload({
      only: mitraPropKeys
    });
  }
  function reloadNetworkSnapshot() {
    if (active.value !== "network") {
      return;
    }
    router.reload({
      only: networkPropKeys
    });
  }
  function reloadGenerationNetworkSnapshot() {
    if (active.value !== "generation_network") {
      return;
    }
    router.reload({
      only: generationNetworkPropKeys
    });
  }
  function reloadPromoSnapshot() {
    if (active.value !== "promo") {
      return;
    }
    router.reload({
      only: promoPropKeys
    });
  }
  function reloadZennerSnapshot() {
    if (active.value !== "zenner") {
      return;
    }
    router.reload({
      only: zennerPropKeys
    });
  }
  function reloadWalletSnapshot() {
    if (active.value !== "wallet") {
      return;
    }
    router.reload({
      only: walletPropKeys
    });
  }
  function reloadBonusSnapshot() {
    if (active.value !== "bonus") {
      return;
    }
    router.reload({
      only: bonusPropKeys
    });
  }
  function reloadLifetimeSnapshot() {
    if (active.value !== "lifetime") {
      return;
    }
    router.reload({
      only: lifetimePropKeys
    });
  }
  function reloadOrdersSnapshot(page = 1) {
    if (active.value !== "orders") {
      return;
    }
    router.get("/dashboard", {
      section: "orders",
      orders_page: page
    }, {
      only: ordersPropKeys,
      preserveState: true,
      preserveScroll: true,
      replace: true
    });
  }
  let dashboardPollTimer;
  onMounted(() => {
    dashboardPollTimer = window.setInterval(() => {
      if (document.visibilityState !== "visible") {
        return;
      }
      reloadDashboardSnapshot();
      reloadAddressSnapshot();
      reloadPromoSnapshot();
      reloadZennerSnapshot();
      reloadMitraSnapshot();
      reloadNetworkSnapshot();
      reloadGenerationNetworkSnapshot();
      reloadWalletSnapshot();
      reloadBonusSnapshot();
      reloadLifetimeSnapshot();
    }, 3e4);
    if (active.value === "orders" && !props.value.orders) {
      reloadOrdersSnapshot(1);
    }
    if (active.value === "promo" && !props.value.promos) {
      reloadPromoSnapshot();
    }
    if (active.value === "form_account" && !props.value.customer) {
      reloadFormAccountSnapshot();
    }
    if (active.value === "zenner" && !props.value.zennerContents) {
      reloadZennerSnapshot();
    }
    if (active.value === "wallet" && !props.value.walletTransactions) {
      reloadWalletSnapshot();
    }
    if (active.value === "bonus" && (!props.value.bonusStats || !props.value.bonusTables)) {
      reloadBonusSnapshot();
    }
    if (active.value === "lifetime" && !props.value.lifetimeRewards) {
      reloadLifetimeSnapshot();
    }
    if (active.value === "network" && !props.value.binaryTree) {
      reloadNetworkSnapshot();
    }
    if (active.value === "generation_network" && !props.value.generationNetwork) {
      reloadGenerationNetworkSnapshot();
    }
  });
  onBeforeUnmount(() => {
    if (dashboardPollTimer !== void 0) {
      window.clearInterval(dashboardPollTimer);
    }
  });
  watch(active, (current, previous) => {
    if (current === previous) {
      return;
    }
    visitSection(current);
  });
  watch(canAccessMitraNetwork, (allowed) => {
    if (allowed) {
      return;
    }
    if (mitraNetworkSections.has(active.value)) {
      active.value = "dashboard";
    }
  });
  function setActive(section) {
    const targetSection = isSectionAllowed(section) ? section : "dashboard";
    if (targetSection === active.value) {
      visitSection(targetSection);
      return;
    }
    active.value = targetSection;
  }
  return {
    active,
    currentComponent,
    currentComponentProps,
    currentComponentListeners,
    asideLinks,
    setActive
  };
}
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$d },
  __name: "Index",
  __ssrInlineRender: true,
  setup(__props) {
    const page = usePage();
    const props = computed(() => page.props);
    const seo = computed(() => props.value.seo);
    const customer = computed(() => props.value.customer ?? null);
    const {
      active,
      currentComponent,
      currentComponentProps,
      currentComponentListeners,
      asideLinks,
      setActive
    } = useDashboardSections(props, page.url);
    function logout() {
      router.post("/logout");
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UPage = _sfc_main$f;
      const _component_UPageBody = _sfc_main$g;
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_sfc_main$e, {
        title: seo.value.title,
        description: seo.value.description,
        canonical: seo.value.canonical
      }, null, _parent));
      _push(ssrRenderComponent(_component_UPage, { class: "min-h-screen bg-gray-50/60 dark:bg-gray-950" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$1, {
              customer: customer.value,
              "network-profile": props.value.networkProfile,
              "promo-active": props.value.stats?.promo_active,
              onLogout: logout
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UPageBody, { class: "mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 pb-10" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="grid grid-cols-1 gap-6 lg:grid-cols-12"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_sfc_main$2, {
                    active: unref(active),
                    links: unref(asideLinks),
                    "wallet-balance": props.value.stats?.wallet_balance,
                    "onUpdate:active": unref(setActive)
                  }, null, _parent3, _scopeId2));
                  _push3(`<div class="lg:col-span-9"${_scopeId2}>`);
                  ssrRenderVNode(_push3, createVNode(resolveDynamicComponent(unref(currentComponent)), mergeProps({ key: unref(active) }, unref(currentComponentProps), toHandlers(unref(currentComponentListeners))), null), _parent3, _scopeId2);
                  _push3(`</div></div>`);
                } else {
                  return [
                    createVNode("div", { class: "grid grid-cols-1 gap-6 lg:grid-cols-12" }, [
                      createVNode(_sfc_main$2, {
                        active: unref(active),
                        links: unref(asideLinks),
                        "wallet-balance": props.value.stats?.wallet_balance,
                        "onUpdate:active": unref(setActive)
                      }, null, 8, ["active", "links", "wallet-balance", "onUpdate:active"]),
                      createVNode("div", { class: "lg:col-span-9" }, [
                        (openBlock(), createBlock(resolveDynamicComponent(unref(currentComponent)), mergeProps({ key: unref(active) }, unref(currentComponentProps), toHandlers(unref(currentComponentListeners))), null, 16))
                      ])
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$1, {
                customer: customer.value,
                "network-profile": props.value.networkProfile,
                "promo-active": props.value.stats?.promo_active,
                onLogout: logout
              }, null, 8, ["customer", "network-profile", "promo-active"]),
              createVNode(_component_UPageBody, { class: "mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 pb-10" }, {
                default: withCtx(() => [
                  createVNode("div", { class: "grid grid-cols-1 gap-6 lg:grid-cols-12" }, [
                    createVNode(_sfc_main$2, {
                      active: unref(active),
                      links: unref(asideLinks),
                      "wallet-balance": props.value.stats?.wallet_balance,
                      "onUpdate:active": unref(setActive)
                    }, null, 8, ["active", "links", "wallet-balance", "onUpdate:active"]),
                    createVNode("div", { class: "lg:col-span-9" }, [
                      (openBlock(), createBlock(resolveDynamicComponent(unref(currentComponent)), mergeProps({ key: unref(active) }, unref(currentComponentProps), toHandlers(unref(currentComponentListeners))), null, 16))
                    ])
                  ])
                ]),
                _: 1
              })
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/Index.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
