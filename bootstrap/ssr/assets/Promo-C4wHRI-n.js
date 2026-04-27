import { useSlots, ref, computed, watch, unref, mergeProps, withCtx, createVNode, openBlock, createBlock, createCommentVNode, renderSlot, createTextVNode, toDisplayString, useSSRContext, defineComponent, createSlots } from "vue";
import { ssrRenderComponent, ssrRenderClass, ssrRenderSlot, ssrInterpolate, ssrRenderAttrs, ssrRenderList } from "vue/server-renderer";
import { Primitive } from "reka-ui";
import { pausableFilter, useMouseInElement } from "@vueuse/core";
import "@inertiajs/vue3";
import { t as tv, g as getSlotChildrenText, _ as _sfc_main$5, u as useAppConfig } from "./Icon-Chcm7u9q.js";
import { a as _sfc_main$6, _ as _sfc_main$a } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$9 } from "./SelectMenu-D4PFVS1f.js";
import { _ as _sfc_main$8 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$7 } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$c } from "./Skeleton-COFz1HE7.js";
import { _ as _sfc_main$b } from "./Card-CvchAxCK.js";
import "hookable";
import "defu";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "ufo";
import "./usePortal-EQErrF6h.js";
const theme = {
  "slots": {
    "root": "relative flex rounded-lg",
    "spotlight": "absolute inset-0 rounded-[inherit] pointer-events-none bg-default/90",
    "container": "relative flex flex-col flex-1 lg:grid gap-x-8 gap-y-4 p-4 sm:p-6",
    "wrapper": "flex flex-col flex-1 items-start",
    "header": "mb-4",
    "body": "flex-1",
    "footer": "pt-4 mt-auto",
    "leading": "inline-flex items-center mb-2.5",
    "leadingIcon": "size-5 shrink-0 text-primary",
    "title": "text-base text-pretty font-semibold text-highlighted",
    "description": "text-[15px] text-pretty"
  },
  "variants": {
    "orientation": {
      "horizontal": {
        "container": "lg:grid-cols-2 lg:items-center"
      },
      "vertical": {
        "container": ""
      }
    },
    "reverse": {
      "true": {
        "wrapper": "order-last"
      }
    },
    "variant": {
      "solid": {
        "root": "bg-inverted text-inverted",
        "title": "text-inverted",
        "description": "text-dimmed"
      },
      "outline": {
        "root": "bg-default ring ring-default",
        "description": "text-muted"
      },
      "soft": {
        "root": "bg-elevated/50",
        "description": "text-toned"
      },
      "subtle": {
        "root": "bg-elevated/50 ring ring-default",
        "description": "text-toned"
      },
      "ghost": {
        "description": "text-muted"
      },
      "naked": {
        "container": "p-0 sm:p-0",
        "description": "text-muted"
      }
    },
    "to": {
      "true": {
        "root": [
          "has-focus-visible:ring-2 has-focus-visible:ring-primary",
          "transition"
        ]
      }
    },
    "title": {
      "true": {
        "description": "mt-1"
      }
    },
    "highlight": {
      "true": {
        "root": "ring-2"
      }
    },
    "highlightColor": {
      "primary": "",
      "secondary": "",
      "success": "",
      "info": "",
      "warning": "",
      "error": "",
      "neutral": ""
    },
    "spotlight": {
      "true": {
        "root": "[--spotlight-size:400px] before:absolute before:-inset-px before:pointer-events-none before:rounded-[inherit] before:bg-[radial-gradient(var(--spotlight-size)_var(--spotlight-size)_at_calc(var(--spotlight-x,0px))_calc(var(--spotlight-y,0px)),var(--spotlight-color),transparent_70%)]"
      }
    },
    "spotlightColor": {
      "primary": "",
      "secondary": "",
      "success": "",
      "info": "",
      "warning": "",
      "error": "",
      "neutral": ""
    }
  },
  "compoundVariants": [
    {
      "variant": "solid",
      "to": true,
      "class": {
        "root": "hover:bg-inverted/90"
      }
    },
    {
      "variant": "outline",
      "to": true,
      "class": {
        "root": "hover:bg-elevated/50"
      }
    },
    {
      "variant": "soft",
      "to": true,
      "class": {
        "root": "hover:bg-elevated"
      }
    },
    {
      "variant": "subtle",
      "to": true,
      "class": {
        "root": "hover:bg-elevated"
      }
    },
    {
      "variant": "subtle",
      "to": true,
      "highlight": false,
      "class": {
        "root": "hover:ring-accented"
      }
    },
    {
      "variant": "ghost",
      "to": true,
      "class": {
        "root": "hover:bg-elevated/50"
      }
    },
    {
      "highlightColor": "primary",
      "highlight": true,
      "class": {
        "root": "ring-primary"
      }
    },
    {
      "highlightColor": "secondary",
      "highlight": true,
      "class": {
        "root": "ring-secondary"
      }
    },
    {
      "highlightColor": "success",
      "highlight": true,
      "class": {
        "root": "ring-success"
      }
    },
    {
      "highlightColor": "info",
      "highlight": true,
      "class": {
        "root": "ring-info"
      }
    },
    {
      "highlightColor": "warning",
      "highlight": true,
      "class": {
        "root": "ring-warning"
      }
    },
    {
      "highlightColor": "error",
      "highlight": true,
      "class": {
        "root": "ring-error"
      }
    },
    {
      "highlightColor": "neutral",
      "highlight": true,
      "class": {
        "root": "ring-inverted"
      }
    },
    {
      "spotlightColor": "primary",
      "spotlight": true,
      "class": {
        "root": "[--spotlight-color:var(--ui-primary)]"
      }
    },
    {
      "spotlightColor": "secondary",
      "spotlight": true,
      "class": {
        "root": "[--spotlight-color:var(--ui-secondary)]"
      }
    },
    {
      "spotlightColor": "success",
      "spotlight": true,
      "class": {
        "root": "[--spotlight-color:var(--ui-success)]"
      }
    },
    {
      "spotlightColor": "info",
      "spotlight": true,
      "class": {
        "root": "[--spotlight-color:var(--ui-info)]"
      }
    },
    {
      "spotlightColor": "warning",
      "spotlight": true,
      "class": {
        "root": "[--spotlight-color:var(--ui-warning)]"
      }
    },
    {
      "spotlightColor": "error",
      "spotlight": true,
      "class": {
        "root": "[--spotlight-color:var(--ui-error)]"
      }
    },
    {
      "spotlightColor": "neutral",
      "spotlight": true,
      "class": {
        "root": "[--spotlight-color:var(--ui-bg-inverted)]"
      }
    }
  ],
  "defaultVariants": {
    "variant": "outline",
    "highlightColor": "primary",
    "spotlightColor": "primary"
  }
};
const _sfc_main$4 = /* @__PURE__ */ Object.assign({ inheritAttrs: false }, {
  __name: "PageCard",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false },
    icon: { type: null, required: false },
    title: { type: String, required: false },
    description: { type: String, required: false },
    orientation: { type: null, required: false, default: "vertical" },
    reverse: { type: Boolean, required: false },
    highlight: { type: Boolean, required: false },
    highlightColor: { type: null, required: false },
    spotlight: { type: Boolean, required: false },
    spotlightColor: { type: null, required: false },
    variant: { type: null, required: false },
    to: { type: null, required: false },
    target: { type: [String, Object, null], required: false },
    onClick: { type: Function, required: false },
    class: { type: null, required: false },
    ui: { type: null, required: false }
  },
  setup(__props) {
    const props = __props;
    const slots = useSlots();
    const cardRef = ref();
    const motionControl = pausableFilter();
    const appConfig = useAppConfig();
    const { elementX, elementY } = useMouseInElement(cardRef, {
      eventFilter: motionControl.eventFilter
    });
    const spotlight = computed(() => props.spotlight && (elementX.value !== 0 || elementY.value !== 0));
    watch(() => props.spotlight, (value) => {
      if (value) {
        motionControl.resume();
      } else {
        motionControl.pause();
      }
    }, { immediate: true });
    const ui = computed(() => tv({ extend: tv(theme), ...appConfig.ui?.pageCard || {} })({
      orientation: props.orientation,
      reverse: props.reverse,
      variant: props.variant,
      to: !!props.to || !!props.onClick,
      title: !!props.title || !!slots.title,
      highlight: props.highlight,
      highlightColor: props.highlightColor,
      spotlight: spotlight.value,
      spotlightColor: props.spotlightColor
    }));
    const ariaLabel = computed(() => {
      const slotText = slots.title && getSlotChildrenText(slots.title());
      return (slotText || props.title || "Card link").trim();
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Primitive), mergeProps({
        ref_key: "cardRef",
        ref: cardRef,
        as: __props.as,
        "data-orientation": __props.orientation,
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] }),
        style: spotlight.value && { "--spotlight-x": `${unref(elementX)}px`, "--spotlight-y": `${unref(elementY)}px` },
        onClick: __props.onClick
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (props.spotlight) {
              _push2(`<div data-slot="spotlight" class="${ssrRenderClass(ui.value.spotlight({ class: props.ui?.spotlight }))}"${_scopeId}></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div data-slot="container" class="${ssrRenderClass(ui.value.container({ class: props.ui?.container }))}"${_scopeId}>`);
            if (!!slots.header || (__props.icon || !!slots.leading) || !!slots.body || (__props.title || !!slots.title) || (__props.description || !!slots.description) || !!slots.footer) {
              _push2(`<div data-slot="wrapper" class="${ssrRenderClass(ui.value.wrapper({ class: props.ui?.wrapper }))}"${_scopeId}>`);
              if (!!slots.header) {
                _push2(`<div data-slot="header" class="${ssrRenderClass(ui.value.header({ class: props.ui?.header }))}"${_scopeId}>`);
                ssrRenderSlot(_ctx.$slots, "header", {}, null, _push2, _parent2, _scopeId);
                _push2(`</div>`);
              } else {
                _push2(`<!---->`);
              }
              if (__props.icon || !!slots.leading) {
                _push2(`<div data-slot="leading" class="${ssrRenderClass(ui.value.leading({ class: props.ui?.leading }))}"${_scopeId}>`);
                ssrRenderSlot(_ctx.$slots, "leading", { ui: ui.value }, () => {
                  if (__props.icon) {
                    _push2(ssrRenderComponent(_sfc_main$5, {
                      name: __props.icon,
                      "data-slot": "leadingIcon",
                      class: ui.value.leadingIcon({ class: props.ui?.leadingIcon })
                    }, null, _parent2, _scopeId));
                  } else {
                    _push2(`<!---->`);
                  }
                }, _push2, _parent2, _scopeId);
                _push2(`</div>`);
              } else {
                _push2(`<!---->`);
              }
              if (!!slots.body || (__props.title || !!slots.title) || (__props.description || !!slots.description)) {
                _push2(`<div data-slot="body" class="${ssrRenderClass(ui.value.body({ class: props.ui?.body }))}"${_scopeId}>`);
                ssrRenderSlot(_ctx.$slots, "body", {}, () => {
                  if (__props.title || !!slots.title) {
                    _push2(`<div data-slot="title" class="${ssrRenderClass(ui.value.title({ class: props.ui?.title }))}"${_scopeId}>`);
                    ssrRenderSlot(_ctx.$slots, "title", {}, () => {
                      _push2(`${ssrInterpolate(__props.title)}`);
                    }, _push2, _parent2, _scopeId);
                    _push2(`</div>`);
                  } else {
                    _push2(`<!---->`);
                  }
                  if (__props.description || !!slots.description) {
                    _push2(`<div data-slot="description" class="${ssrRenderClass(ui.value.description({ class: props.ui?.description }))}"${_scopeId}>`);
                    ssrRenderSlot(_ctx.$slots, "description", {}, () => {
                      _push2(`${ssrInterpolate(__props.description)}`);
                    }, _push2, _parent2, _scopeId);
                    _push2(`</div>`);
                  } else {
                    _push2(`<!---->`);
                  }
                }, _push2, _parent2, _scopeId);
                _push2(`</div>`);
              } else {
                _push2(`<!---->`);
              }
              if (!!slots.footer) {
                _push2(`<div data-slot="footer" class="${ssrRenderClass(ui.value.footer({ class: props.ui?.footer }))}"${_scopeId}>`);
                ssrRenderSlot(_ctx.$slots, "footer", {}, null, _push2, _parent2, _scopeId);
                _push2(`</div>`);
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            ssrRenderSlot(_ctx.$slots, "default", {}, null, _push2, _parent2, _scopeId);
            _push2(`</div>`);
            if (__props.to) {
              _push2(ssrRenderComponent(_sfc_main$6, mergeProps({ "aria-label": ariaLabel.value }, { to: __props.to, target: __props.target, ..._ctx.$attrs }, {
                class: "focus:outline-none peer",
                raw: ""
              }), {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<span class="absolute inset-0" aria-hidden="true"${_scopeId2}></span>`);
                  } else {
                    return [
                      createVNode("span", {
                        class: "absolute inset-0",
                        "aria-hidden": "true"
                      })
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              props.spotlight ? (openBlock(), createBlock("div", {
                key: 0,
                "data-slot": "spotlight",
                class: ui.value.spotlight({ class: props.ui?.spotlight })
              }, null, 2)) : createCommentVNode("", true),
              createVNode("div", {
                "data-slot": "container",
                class: ui.value.container({ class: props.ui?.container })
              }, [
                !!slots.header || (__props.icon || !!slots.leading) || !!slots.body || (__props.title || !!slots.title) || (__props.description || !!slots.description) || !!slots.footer ? (openBlock(), createBlock("div", {
                  key: 0,
                  "data-slot": "wrapper",
                  class: ui.value.wrapper({ class: props.ui?.wrapper })
                }, [
                  !!slots.header ? (openBlock(), createBlock("div", {
                    key: 0,
                    "data-slot": "header",
                    class: ui.value.header({ class: props.ui?.header })
                  }, [
                    renderSlot(_ctx.$slots, "header")
                  ], 2)) : createCommentVNode("", true),
                  __props.icon || !!slots.leading ? (openBlock(), createBlock("div", {
                    key: 1,
                    "data-slot": "leading",
                    class: ui.value.leading({ class: props.ui?.leading })
                  }, [
                    renderSlot(_ctx.$slots, "leading", { ui: ui.value }, () => [
                      __props.icon ? (openBlock(), createBlock(_sfc_main$5, {
                        key: 0,
                        name: __props.icon,
                        "data-slot": "leadingIcon",
                        class: ui.value.leadingIcon({ class: props.ui?.leadingIcon })
                      }, null, 8, ["name", "class"])) : createCommentVNode("", true)
                    ])
                  ], 2)) : createCommentVNode("", true),
                  !!slots.body || (__props.title || !!slots.title) || (__props.description || !!slots.description) ? (openBlock(), createBlock("div", {
                    key: 2,
                    "data-slot": "body",
                    class: ui.value.body({ class: props.ui?.body })
                  }, [
                    renderSlot(_ctx.$slots, "body", {}, () => [
                      __props.title || !!slots.title ? (openBlock(), createBlock("div", {
                        key: 0,
                        "data-slot": "title",
                        class: ui.value.title({ class: props.ui?.title })
                      }, [
                        renderSlot(_ctx.$slots, "title", {}, () => [
                          createTextVNode(toDisplayString(__props.title), 1)
                        ])
                      ], 2)) : createCommentVNode("", true),
                      __props.description || !!slots.description ? (openBlock(), createBlock("div", {
                        key: 1,
                        "data-slot": "description",
                        class: ui.value.description({ class: props.ui?.description })
                      }, [
                        renderSlot(_ctx.$slots, "description", {}, () => [
                          createTextVNode(toDisplayString(__props.description), 1)
                        ])
                      ], 2)) : createCommentVNode("", true)
                    ])
                  ], 2)) : createCommentVNode("", true),
                  !!slots.footer ? (openBlock(), createBlock("div", {
                    key: 3,
                    "data-slot": "footer",
                    class: ui.value.footer({ class: props.ui?.footer })
                  }, [
                    renderSlot(_ctx.$slots, "footer")
                  ], 2)) : createCommentVNode("", true)
                ], 2)) : createCommentVNode("", true),
                renderSlot(_ctx.$slots, "default")
              ], 2),
              __props.to ? (openBlock(), createBlock(_sfc_main$6, mergeProps({
                key: 1,
                "aria-label": ariaLabel.value
              }, { to: __props.to, target: __props.target, ..._ctx.$attrs }, {
                class: "focus:outline-none peer",
                raw: ""
              }), {
                default: withCtx(() => [
                  createVNode("span", {
                    class: "absolute inset-0",
                    "aria-hidden": "true"
                  })
                ]),
                _: 1
              }, 16, ["aria-label"])) : createCommentVNode("", true)
            ];
          }
        }),
        _: 3
      }, _parent));
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/PageCard.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
function isExpired(expiresAt) {
  if (!expiresAt) {
    return false;
  }
  return Date.parse(expiresAt) < Date.now();
}
function useDashboardPromo(options) {
  const searchQuery = ref("");
  const selectedType = ref("all");
  const onlyAvailable = ref(true);
  const copiedCode = ref(null);
  const typeMeta = {
    voucher: {
      label: "Voucher",
      color: "primary",
      icon: "i-lucide-ticket",
      accentClass: "bg-primary",
      iconClass: "text-primary"
    },
    discount: {
      label: "Diskon",
      color: "success",
      icon: "i-lucide-percent",
      accentClass: "bg-emerald-500",
      iconClass: "text-emerald-500"
    },
    flash: {
      label: "Flash Sale",
      color: "warning",
      icon: "i-lucide-zap",
      accentClass: "bg-amber-500",
      iconClass: "text-amber-500"
    },
    shipping: {
      label: "Gratis Ongkir",
      color: "info",
      icon: "i-lucide-truck",
      accentClass: "bg-sky-500",
      iconClass: "text-sky-500"
    },
    bundle: {
      label: "Bundle",
      color: "neutral",
      icon: "i-lucide-package",
      accentClass: "bg-indigo-500",
      iconClass: "text-indigo-500"
    },
    member: {
      label: "Exclusive",
      color: "primary",
      icon: "i-lucide-crown",
      accentClass: "bg-fuchsia-500",
      iconClass: "text-fuchsia-500"
    }
  };
  const typeItems = computed(() => [
    { label: "Semua Tipe", value: "all", icon: "i-lucide-layers" },
    ...Object.entries(typeMeta).map(([value, meta]) => ({
      label: meta.label,
      value,
      icon: meta.icon
    }))
  ]);
  const selectedTypeIcon = computed(
    () => selectedType.value === "all" ? "i-lucide-filter" : typeMeta[selectedType.value].icon
  );
  const filteredPromos = computed(() => {
    let data = [...options.promos.value];
    if (selectedType.value !== "all") {
      data = data.filter((promo) => promo.type === selectedType.value);
    }
    const keyword = searchQuery.value.trim().toLowerCase();
    if (keyword !== "") {
      data = data.filter(
        (promo) => promo.title.toLowerCase().includes(keyword) || String(promo.code ?? "").toLowerCase().includes(keyword)
      );
    }
    if (onlyAvailable.value) {
      data = data.filter((promo) => !isExpired(promo.expires_at));
    }
    return data.sort((left, right) => Number(!!right.highlight) - Number(!!left.highlight));
  });
  function formatExpiry(expiresAt) {
    if (!expiresAt) {
      return "Selamanya";
    }
    const parsed = Date.parse(expiresAt);
    if (Number.isNaN(parsed)) {
      return expiresAt;
    }
    const date = new Date(parsed);
    return `Hingga ${date.toLocaleDateString("id-ID", { month: "short", day: "numeric" })}`;
  }
  let copiedTimer = null;
  async function copyCode(code) {
    if (!code || typeof navigator === "undefined" || !navigator.clipboard) {
      return;
    }
    await navigator.clipboard.writeText(code);
    copiedCode.value = code;
    if (copiedTimer) {
      clearTimeout(copiedTimer);
    }
    copiedTimer = setTimeout(() => {
      copiedCode.value = null;
    }, 2e3);
  }
  function resetFilters() {
    searchQuery.value = "";
    selectedType.value = "all";
    onlyAvailable.value = true;
  }
  return {
    searchQuery,
    selectedType,
    onlyAvailable,
    copiedCode,
    typeMeta,
    typeItems,
    selectedTypeIcon,
    filteredPromos,
    formatExpiry,
    copyCode,
    resetFilters
  };
}
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "PromoHeaderFilters",
  __ssrInlineRender: true,
  props: {
    filteredCount: {},
    searchQuery: {},
    selectedType: {},
    onlyAvailable: { type: Boolean },
    selectedTypeIcon: {},
    typeItems: {}
  },
  emits: ["update:searchQuery", "update:selectedType", "update:onlyAvailable"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function onSearchUpdate(value) {
      emit("update:searchQuery", String(value ?? ""));
    }
    function onTypeUpdate(value) {
      emit("update:selectedType", String(value ?? "all"));
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UBadge = _sfc_main$7;
      const _component_UInput = _sfc_main$8;
      const _component_USelectMenu = _sfc_main$9;
      const _component_UIcon = _sfc_main$5;
      const _component_UButton = _sfc_main$a;
      _push(`<section${ssrRenderAttrs(mergeProps({ class: "flex flex-col gap-6" }, _attrs))}><div class="flex items-end justify-between"><div><h2 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">Daftar Promo</h2><p class="text-neutral-500 text-sm">Kelola dan gunakan promo aktif Anda di sini.</p></div>`);
      _push(ssrRenderComponent(_component_UBadge, {
        variant: "subtle",
        size: "lg",
        class: "hidden sm:flex font-mono"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(props.filteredCount)} Aktif `);
          } else {
            return [
              createTextVNode(toDisplayString(props.filteredCount) + " Aktif ", 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="flex flex-col md:flex-row gap-3">`);
      _push(ssrRenderComponent(_component_UInput, {
        "model-value": props.searchQuery,
        icon: "i-lucide-search",
        placeholder: "Cari nama promo atau kode...",
        class: "flex-1",
        size: "md",
        "onUpdate:modelValue": onSearchUpdate
      }, null, _parent));
      _push(`<div class="flex gap-2">`);
      _push(ssrRenderComponent(_component_USelectMenu, {
        "model-value": props.selectedType,
        items: props.typeItems,
        "value-key": "value",
        class: "w-48",
        size: "md",
        "onUpdate:modelValue": onTypeUpdate
      }, {
        leading: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UIcon, {
              name: props.selectedTypeIcon
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UIcon, {
                name: props.selectedTypeIcon
              }, null, 8, ["name"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        variant: props.onlyAvailable ? "solid" : "outline",
        color: props.onlyAvailable ? "primary" : "neutral",
        icon: "i-lucide-clock-check",
        label: "Aktif",
        size: "md",
        onClick: ($event) => emit("update:onlyAvailable", !props.onlyAvailable)
      }, null, _parent));
      _push(`</div></div></section>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/promo/PromoHeaderFilters.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "PromoCardItem",
  __ssrInlineRender: true,
  props: {
    promo: {},
    meta: {},
    copiedCode: {},
    formatExpiry: { type: Function }
  },
  emits: ["copyCode"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$b;
      const _component_UBadge = _sfc_main$7;
      const _component_UIcon = _sfc_main$5;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "group relative transition-all duration-300 hover:-translate-y-1 overflow-visible",
        ui: {
          root: "ring-1 ring-neutral-200 dark:ring-neutral-800 shadow-none hover:shadow-xl hover:ring-primary-500/50",
          header: "p-0 overflow-hidden rounded-t-xl",
          body: "p-5"
        }
      }, _attrs), createSlots({
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="${ssrRenderClass([props.meta.accentClass, "h-2 w-full"])}"${_scopeId}></div>`);
          } else {
            return [
              createVNode("div", {
                class: ["h-2 w-full", props.meta.accentClass]
              }, null, 2)
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (props.promo.highlight) {
              _push2(`<div class="absolute -top-2 -right-2 z-10"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "primary",
                variant: "solid",
                size: "sm",
                class: "shadow-lg rounded-lg shadow-primary-500/20"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Populer `);
                  } else {
                    return [
                      createTextVNode(" Populer ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="space-y-4"${_scopeId}><div class="flex items-start justify-between gap-2"${_scopeId}><div class="flex items-center gap-2 p-2 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: props.meta.icon,
              class: ["size-5", props.meta.iconClass]
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              variant: "subtle",
              color: props.meta.color,
              size: "xs"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(props.meta.label)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(props.meta.label), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div${_scopeId}><h4 class="font-bold text-neutral-900 dark:text-white line-clamp-1 uppercase tracking-tight"${_scopeId}>${ssrInterpolate(props.promo.title)}</h4><p class="text-xs text-neutral-500 mt-1 line-clamp-2 leading-relaxed"${_scopeId}>${ssrInterpolate(props.promo.description)}</p></div><div class="flex items-center gap-2 bg-neutral-50 dark:bg-neutral-900 p-2 rounded-xl border border-neutral-100 dark:border-neutral-800"${_scopeId}><div class="flex-1 px-2"${_scopeId}><span class="text-[10px] text-neutral-400 font-bold uppercase block"${_scopeId}>Kode Promo</span><span class="font-mono font-bold text-sm tracking-widest"${_scopeId}>${ssrInterpolate(props.promo.code || "Otomatis")}</span></div>`);
            if (props.promo.code) {
              _push2(ssrRenderComponent(_component_UButton, {
                icon: props.copiedCode === props.promo.code ? "i-lucide-check" : "i-lucide-copy",
                color: props.copiedCode === props.promo.code ? "success" : "neutral",
                variant: "ghost",
                size: "sm",
                onClick: ($event) => emit("copyCode", props.promo.code)
              }, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800"${_scopeId}><div class="flex items-center gap-1.5 text-neutral-500"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-calendar",
              class: "size-3.5"
            }, null, _parent2, _scopeId));
            _push2(`<span class="text-[11px] font-medium"${_scopeId}>${ssrInterpolate(props.formatExpiry(props.promo.expires_at))}</span></div>`);
            if (props.promo.quota_left) {
              _push2(`<div class="text-[11px] font-bold text-primary-500"${_scopeId}> Sisa ${ssrInterpolate(props.promo.quota_left)}</div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div>`);
          } else {
            return [
              props.promo.highlight ? (openBlock(), createBlock("div", {
                key: 0,
                class: "absolute -top-2 -right-2 z-10"
              }, [
                createVNode(_component_UBadge, {
                  color: "primary",
                  variant: "solid",
                  size: "sm",
                  class: "shadow-lg rounded-lg shadow-primary-500/20"
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Populer ")
                  ]),
                  _: 1
                })
              ])) : createCommentVNode("", true),
              createVNode("div", { class: "space-y-4" }, [
                createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                  createVNode("div", { class: "flex items-center gap-2 p-2 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg" }, [
                    createVNode(_component_UIcon, {
                      name: props.meta.icon,
                      class: ["size-5", props.meta.iconClass]
                    }, null, 8, ["name", "class"])
                  ]),
                  createVNode(_component_UBadge, {
                    variant: "subtle",
                    color: props.meta.color,
                    size: "xs"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(toDisplayString(props.meta.label), 1)
                    ]),
                    _: 1
                  }, 8, ["color"])
                ]),
                createVNode("div", null, [
                  createVNode("h4", { class: "font-bold text-neutral-900 dark:text-white line-clamp-1 uppercase tracking-tight" }, toDisplayString(props.promo.title), 1),
                  createVNode("p", { class: "text-xs text-neutral-500 mt-1 line-clamp-2 leading-relaxed" }, toDisplayString(props.promo.description), 1)
                ]),
                createVNode("div", { class: "flex items-center gap-2 bg-neutral-50 dark:bg-neutral-900 p-2 rounded-xl border border-neutral-100 dark:border-neutral-800" }, [
                  createVNode("div", { class: "flex-1 px-2" }, [
                    createVNode("span", { class: "text-[10px] text-neutral-400 font-bold uppercase block" }, "Kode Promo"),
                    createVNode("span", { class: "font-mono font-bold text-sm tracking-widest" }, toDisplayString(props.promo.code || "Otomatis"), 1)
                  ]),
                  props.promo.code ? (openBlock(), createBlock(_component_UButton, {
                    key: 0,
                    icon: props.copiedCode === props.promo.code ? "i-lucide-check" : "i-lucide-copy",
                    color: props.copiedCode === props.promo.code ? "success" : "neutral",
                    variant: "ghost",
                    size: "sm",
                    onClick: ($event) => emit("copyCode", props.promo.code)
                  }, null, 8, ["icon", "color", "onClick"])) : createCommentVNode("", true)
                ]),
                createVNode("div", { class: "flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800" }, [
                  createVNode("div", { class: "flex items-center gap-1.5 text-neutral-500" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-calendar",
                      class: "size-3.5"
                    }),
                    createVNode("span", { class: "text-[11px] font-medium" }, toDisplayString(props.formatExpiry(props.promo.expires_at)), 1)
                  ]),
                  props.promo.quota_left ? (openBlock(), createBlock("div", {
                    key: 0,
                    class: "text-[11px] font-bold text-primary-500"
                  }, " Sisa " + toDisplayString(props.promo.quota_left), 1)) : createCommentVNode("", true)
                ])
              ])
            ];
          }
        }),
        _: 2
      }, [
        props.promo.to ? {
          name: "footer",
          fn: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(ssrRenderComponent(_component_UButton, {
                to: props.promo.to,
                block: "",
                color: "neutral",
                variant: "ghost",
                "trailing-icon": "i-lucide-arrow-right",
                size: "sm"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Gunakan Promo `);
                  } else {
                    return [
                      createTextVNode(" Gunakan Promo ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              return [
                createVNode(_component_UButton, {
                  to: props.promo.to,
                  block: "",
                  color: "neutral",
                  variant: "ghost",
                  "trailing-icon": "i-lucide-arrow-right",
                  size: "sm"
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Gunakan Promo ")
                  ]),
                  _: 1
                }, 8, ["to"])
              ];
            }
          }),
          key: "0"
        } : void 0
      ]), _parent));
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/promo/PromoCardItem.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "PromoCardsGrid",
  __ssrInlineRender: true,
  props: {
    loading: { type: Boolean, default: false },
    promos: {},
    copiedCode: {},
    typeMeta: {},
    formatExpiry: {}
  },
  emits: ["copyCode", "resetFilters"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_USkeleton = _sfc_main$c;
      const _component_UIcon = _sfc_main$5;
      const _component_UButton = _sfc_main$a;
      if (props.loading) {
        _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" }, _attrs))}><!--[-->`);
        ssrRenderList(6, (i) => {
          _push(ssrRenderComponent(_component_USkeleton, {
            key: i,
            class: "h-48 w-full rounded-xl"
          }, null, _parent));
        });
        _push(`<!--]--></div>`);
      } else if (props.promos.length === 0) {
        _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col items-center justify-center py-20 border-2 border-dashed border-neutral-200 dark:border-neutral-800 rounded-3xl" }, _attrs))}><div class="p-4 bg-neutral-100 dark:bg-neutral-900 rounded-full mb-4">`);
        _push(ssrRenderComponent(_component_UIcon, {
          name: "i-lucide-ticket-dash",
          class: "size-10 text-neutral-400"
        }, null, _parent));
        _push(`</div><h3 class="text-lg font-semibold">Tidak Ada Promo</h3><p class="text-neutral-500 max-w-xs text-center text-sm mt-1">Coba sesuaikan filter atau kata kunci pencarian Anda.</p>`);
        _push(ssrRenderComponent(_component_UButton, {
          label: "Lihat Semua",
          variant: "link",
          class: "mt-2",
          onClick: ($event) => emit("resetFilters")
        }, null, _parent));
        _push(`</div>`);
      } else {
        _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" }, _attrs))}><!--[-->`);
        ssrRenderList(props.promos, (promo) => {
          _push(ssrRenderComponent(_sfc_main$2, {
            key: promo.id,
            promo,
            meta: props.typeMeta[promo.type],
            "copied-code": props.copiedCode,
            "format-expiry": props.formatExpiry,
            onCopyCode: (code) => emit("copyCode", code)
          }, null, _parent));
        });
        _push(`<!--]--></div>`);
      }
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/promo/PromoCardsGrid.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Promo",
  __ssrInlineRender: true,
  props: {
    promos: { default: () => [] },
    loading: { type: Boolean, default: false }
  },
  setup(__props) {
    const props = __props;
    const {
      searchQuery,
      selectedType,
      onlyAvailable,
      copiedCode,
      typeMeta,
      typeItems,
      selectedTypeIcon,
      filteredPromos,
      formatExpiry,
      copyCode,
      resetFilters
    } = useDashboardPromo({
      promos: computed(() => props.promos)
    });
    function onSearchQueryChange(value) {
      searchQuery.value = value;
    }
    function onSelectedTypeChange(value) {
      selectedType.value = value;
    }
    function onOnlyAvailableChange(value) {
      onlyAvailable.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UPageCard = _sfc_main$4;
      _push(ssrRenderComponent(_component_UPageCard, mergeProps({ class: "space-y-8 p-1" }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$3, {
              "filtered-count": unref(filteredPromos).length,
              "search-query": unref(searchQuery),
              "selected-type": unref(selectedType),
              "only-available": unref(onlyAvailable),
              "selected-type-icon": unref(selectedTypeIcon),
              "type-items": unref(typeItems),
              "onUpdate:searchQuery": onSearchQueryChange,
              "onUpdate:selectedType": onSelectedTypeChange,
              "onUpdate:onlyAvailable": onOnlyAvailableChange
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_sfc_main$1, {
              loading: __props.loading,
              promos: unref(filteredPromos),
              "copied-code": unref(copiedCode),
              "type-meta": unref(typeMeta),
              "format-expiry": unref(formatExpiry),
              onCopyCode: unref(copyCode),
              onResetFilters: unref(resetFilters)
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$3, {
                "filtered-count": unref(filteredPromos).length,
                "search-query": unref(searchQuery),
                "selected-type": unref(selectedType),
                "only-available": unref(onlyAvailable),
                "selected-type-icon": unref(selectedTypeIcon),
                "type-items": unref(typeItems),
                "onUpdate:searchQuery": onSearchQueryChange,
                "onUpdate:selectedType": onSelectedTypeChange,
                "onUpdate:onlyAvailable": onOnlyAvailableChange
              }, null, 8, ["filtered-count", "search-query", "selected-type", "only-available", "selected-type-icon", "type-items"]),
              createVNode(_sfc_main$1, {
                loading: __props.loading,
                promos: unref(filteredPromos),
                "copied-code": unref(copiedCode),
                "type-meta": unref(typeMeta),
                "format-expiry": unref(formatExpiry),
                onCopyCode: unref(copyCode),
                onResetFilters: unref(resetFilters)
              }, null, 8, ["loading", "promos", "copied-code", "type-meta", "format-expiry", "onCopyCode", "onResetFilters"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Promo.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
