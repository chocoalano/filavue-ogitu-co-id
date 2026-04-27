import { t as tv, b as get, _ as _sfc_main$3, u as useAppConfig } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$a } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$5 } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$4, c as _sfc_main$7 } from "./Button-DLZCZWnW.js";
import { useSlots, computed, unref, mergeProps, withCtx, renderSlot, openBlock, createBlock, createCommentVNode, createTextVNode, toDisplayString, createVNode, Fragment, renderList, useSSRContext, defineComponent, resolveDynamicComponent } from "vue";
import { ssrRenderComponent, ssrRenderList, ssrRenderSlot, ssrRenderClass, ssrInterpolate, ssrRenderAttrs, ssrRenderAttr, ssrRenderVNode } from "vue/server-renderer";
import { Head } from "@inertiajs/vue3";
import { a as _sfc_main$8 } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$9 } from "./SeoHead-CrFJik-H.js";
import { useForwardPropsEmits, AccordionRoot, AccordionItem, AccordionHeader, AccordionTrigger, AccordionContent } from "reka-ui";
import { reactivePick } from "@vueuse/core";
import { _ as _sfc_main$6 } from "./Separator-CuyFd3xv.js";
import "hookable";
import "defu";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "ufo";
import "./useToast-CTuSIf9f.js";
import "./usePortal-EQErrF6h.js";
import "./Input-DFvIE7JC.js";
import "./useStoreData-DrTMI0On.js";
import "reka-ui/namespaced";
import "./Checkbox-PZ4-MUog.js";
import "vaul-vue";
const theme = {
  "slots": {
    "root": "w-full",
    "item": "border-b border-default last:border-b-0",
    "header": "flex",
    "trigger": "group flex-1 flex items-center gap-1.5 font-medium text-sm py-3.5 focus-visible:outline-primary min-w-0",
    "content": "data-[state=open]:animate-[accordion-down_200ms_ease-out] data-[state=closed]:animate-[accordion-up_200ms_ease-out] overflow-hidden focus:outline-none",
    "body": "text-sm pb-3.5",
    "leadingIcon": "shrink-0 size-5",
    "trailingIcon": "shrink-0 size-5 ms-auto group-data-[state=open]:rotate-180 transition-transform duration-200",
    "label": "text-start break-words"
  },
  "variants": {
    "disabled": {
      "true": {
        "trigger": "cursor-not-allowed opacity-75"
      }
    }
  }
};
const _sfc_main$2 = {
  __name: "Accordion",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false },
    items: { type: Array, required: false },
    trailingIcon: { type: null, required: false },
    valueKey: { type: null, required: false, default: "value" },
    labelKey: { type: null, required: false, default: "label" },
    class: { type: null, required: false },
    ui: { type: null, required: false },
    collapsible: { type: Boolean, required: false, default: true },
    defaultValue: { type: null, required: false },
    modelValue: { type: null, required: false },
    type: { type: String, required: false, default: "single" },
    disabled: { type: Boolean, required: false },
    unmountOnHide: { type: Boolean, required: false, default: true }
  },
  emits: ["update:modelValue"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emits = __emit;
    const slots = useSlots();
    const appConfig = useAppConfig();
    const rootProps = useForwardPropsEmits(reactivePick(props, "as", "collapsible", "defaultValue", "disabled", "modelValue", "unmountOnHide"), emits);
    const ui = computed(() => tv({ extend: tv(theme), ...appConfig.ui?.accordion || {} })({
      disabled: props.disabled
    }));
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(AccordionRoot), mergeProps(unref(rootProps), {
        type: __props.type,
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] })
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<!--[-->`);
            ssrRenderList(props.items, (item, index) => {
              _push2(ssrRenderComponent(unref(AccordionItem), {
                key: index,
                value: unref(get)(item, props.valueKey) ?? String(index),
                disabled: item.disabled,
                "data-slot": "item",
                class: ui.value.item({ class: [props.ui?.item, item.ui?.item, item.class] })
              }, {
                default: withCtx(({ open }, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(unref(AccordionHeader), {
                      as: "div",
                      "data-slot": "header",
                      class: ui.value.header({ class: [props.ui?.header, item.ui?.header] })
                    }, {
                      default: withCtx((_2, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(ssrRenderComponent(unref(AccordionTrigger), {
                            "data-slot": "trigger",
                            class: ui.value.trigger({ class: [props.ui?.trigger, item.ui?.trigger], disabled: item.disabled })
                          }, {
                            default: withCtx((_3, _push5, _parent5, _scopeId4) => {
                              if (_push5) {
                                ssrRenderSlot(_ctx.$slots, "leading", {
                                  item,
                                  index,
                                  open,
                                  ui: ui.value
                                }, () => {
                                  if (item.icon) {
                                    _push5(ssrRenderComponent(_sfc_main$3, {
                                      name: item.icon,
                                      "data-slot": "leadingIcon",
                                      class: ui.value.leadingIcon({ class: [props.ui?.leadingIcon, item?.ui?.leadingIcon] })
                                    }, null, _parent5, _scopeId4));
                                  } else {
                                    _push5(`<!---->`);
                                  }
                                }, _push5, _parent5, _scopeId4);
                                if (unref(get)(item, props.labelKey) || !!slots.default) {
                                  _push5(`<span data-slot="label" class="${ssrRenderClass(ui.value.label({ class: [props.ui?.label, item.ui?.label] }))}"${_scopeId4}>`);
                                  ssrRenderSlot(_ctx.$slots, "default", {
                                    item,
                                    index,
                                    open
                                  }, () => {
                                    _push5(`${ssrInterpolate(unref(get)(item, props.labelKey))}`);
                                  }, _push5, _parent5, _scopeId4);
                                  _push5(`</span>`);
                                } else {
                                  _push5(`<!---->`);
                                }
                                ssrRenderSlot(_ctx.$slots, "trailing", {
                                  item,
                                  index,
                                  open,
                                  ui: ui.value
                                }, () => {
                                  _push5(ssrRenderComponent(_sfc_main$3, {
                                    name: item.trailingIcon || __props.trailingIcon || unref(appConfig).ui.icons.chevronDown,
                                    "data-slot": "trailingIcon",
                                    class: ui.value.trailingIcon({ class: [props.ui?.trailingIcon, item.ui?.trailingIcon] })
                                  }, null, _parent5, _scopeId4));
                                }, _push5, _parent5, _scopeId4);
                              } else {
                                return [
                                  renderSlot(_ctx.$slots, "leading", {
                                    item,
                                    index,
                                    open,
                                    ui: ui.value
                                  }, () => [
                                    item.icon ? (openBlock(), createBlock(_sfc_main$3, {
                                      key: 0,
                                      name: item.icon,
                                      "data-slot": "leadingIcon",
                                      class: ui.value.leadingIcon({ class: [props.ui?.leadingIcon, item?.ui?.leadingIcon] })
                                    }, null, 8, ["name", "class"])) : createCommentVNode("", true)
                                  ]),
                                  unref(get)(item, props.labelKey) || !!slots.default ? (openBlock(), createBlock("span", {
                                    key: 0,
                                    "data-slot": "label",
                                    class: ui.value.label({ class: [props.ui?.label, item.ui?.label] })
                                  }, [
                                    renderSlot(_ctx.$slots, "default", {
                                      item,
                                      index,
                                      open
                                    }, () => [
                                      createTextVNode(toDisplayString(unref(get)(item, props.labelKey)), 1)
                                    ])
                                  ], 2)) : createCommentVNode("", true),
                                  renderSlot(_ctx.$slots, "trailing", {
                                    item,
                                    index,
                                    open,
                                    ui: ui.value
                                  }, () => [
                                    createVNode(_sfc_main$3, {
                                      name: item.trailingIcon || __props.trailingIcon || unref(appConfig).ui.icons.chevronDown,
                                      "data-slot": "trailingIcon",
                                      class: ui.value.trailingIcon({ class: [props.ui?.trailingIcon, item.ui?.trailingIcon] })
                                    }, null, 8, ["name", "class"])
                                  ])
                                ];
                              }
                            }),
                            _: 2
                          }, _parent4, _scopeId3));
                        } else {
                          return [
                            createVNode(unref(AccordionTrigger), {
                              "data-slot": "trigger",
                              class: ui.value.trigger({ class: [props.ui?.trigger, item.ui?.trigger], disabled: item.disabled })
                            }, {
                              default: withCtx(() => [
                                renderSlot(_ctx.$slots, "leading", {
                                  item,
                                  index,
                                  open,
                                  ui: ui.value
                                }, () => [
                                  item.icon ? (openBlock(), createBlock(_sfc_main$3, {
                                    key: 0,
                                    name: item.icon,
                                    "data-slot": "leadingIcon",
                                    class: ui.value.leadingIcon({ class: [props.ui?.leadingIcon, item?.ui?.leadingIcon] })
                                  }, null, 8, ["name", "class"])) : createCommentVNode("", true)
                                ]),
                                unref(get)(item, props.labelKey) || !!slots.default ? (openBlock(), createBlock("span", {
                                  key: 0,
                                  "data-slot": "label",
                                  class: ui.value.label({ class: [props.ui?.label, item.ui?.label] })
                                }, [
                                  renderSlot(_ctx.$slots, "default", {
                                    item,
                                    index,
                                    open
                                  }, () => [
                                    createTextVNode(toDisplayString(unref(get)(item, props.labelKey)), 1)
                                  ])
                                ], 2)) : createCommentVNode("", true),
                                renderSlot(_ctx.$slots, "trailing", {
                                  item,
                                  index,
                                  open,
                                  ui: ui.value
                                }, () => [
                                  createVNode(_sfc_main$3, {
                                    name: item.trailingIcon || __props.trailingIcon || unref(appConfig).ui.icons.chevronDown,
                                    "data-slot": "trailingIcon",
                                    class: ui.value.trailingIcon({ class: [props.ui?.trailingIcon, item.ui?.trailingIcon] })
                                  }, null, 8, ["name", "class"])
                                ])
                              ]),
                              _: 2
                            }, 1032, ["class"])
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                    if (item.content || !!slots.content || item.slot && !!slots[item.slot] || !!slots.body || item.slot && !!slots[`${item.slot}-body`]) {
                      _push3(ssrRenderComponent(unref(AccordionContent), {
                        "data-slot": "content",
                        class: ui.value.content({ class: [props.ui?.content, item.ui?.content] })
                      }, {
                        default: withCtx((_2, _push4, _parent4, _scopeId3) => {
                          if (_push4) {
                            ssrRenderSlot(_ctx.$slots, item.slot || "content", {
                              item,
                              index,
                              open,
                              ui: ui.value
                            }, () => {
                              _push4(`<div data-slot="body" class="${ssrRenderClass(ui.value.body({ class: [props.ui?.body, item.ui?.body] }))}"${_scopeId3}>`);
                              ssrRenderSlot(_ctx.$slots, item.slot ? `${item.slot}-body` : "body", {
                                item,
                                index,
                                open,
                                ui: ui.value
                              }, () => {
                                _push4(`${ssrInterpolate(item.content)}`);
                              }, _push4, _parent4, _scopeId3);
                              _push4(`</div>`);
                            }, _push4, _parent4, _scopeId3);
                          } else {
                            return [
                              renderSlot(_ctx.$slots, item.slot || "content", {
                                item,
                                index,
                                open,
                                ui: ui.value
                              }, () => [
                                createVNode("div", {
                                  "data-slot": "body",
                                  class: ui.value.body({ class: [props.ui?.body, item.ui?.body] })
                                }, [
                                  renderSlot(_ctx.$slots, item.slot ? `${item.slot}-body` : "body", {
                                    item,
                                    index,
                                    open,
                                    ui: ui.value
                                  }, () => [
                                    createTextVNode(toDisplayString(item.content), 1)
                                  ])
                                ], 2)
                              ])
                            ];
                          }
                        }),
                        _: 2
                      }, _parent3, _scopeId2));
                    } else {
                      _push3(`<!---->`);
                    }
                  } else {
                    return [
                      createVNode(unref(AccordionHeader), {
                        as: "div",
                        "data-slot": "header",
                        class: ui.value.header({ class: [props.ui?.header, item.ui?.header] })
                      }, {
                        default: withCtx(() => [
                          createVNode(unref(AccordionTrigger), {
                            "data-slot": "trigger",
                            class: ui.value.trigger({ class: [props.ui?.trigger, item.ui?.trigger], disabled: item.disabled })
                          }, {
                            default: withCtx(() => [
                              renderSlot(_ctx.$slots, "leading", {
                                item,
                                index,
                                open,
                                ui: ui.value
                              }, () => [
                                item.icon ? (openBlock(), createBlock(_sfc_main$3, {
                                  key: 0,
                                  name: item.icon,
                                  "data-slot": "leadingIcon",
                                  class: ui.value.leadingIcon({ class: [props.ui?.leadingIcon, item?.ui?.leadingIcon] })
                                }, null, 8, ["name", "class"])) : createCommentVNode("", true)
                              ]),
                              unref(get)(item, props.labelKey) || !!slots.default ? (openBlock(), createBlock("span", {
                                key: 0,
                                "data-slot": "label",
                                class: ui.value.label({ class: [props.ui?.label, item.ui?.label] })
                              }, [
                                renderSlot(_ctx.$slots, "default", {
                                  item,
                                  index,
                                  open
                                }, () => [
                                  createTextVNode(toDisplayString(unref(get)(item, props.labelKey)), 1)
                                ])
                              ], 2)) : createCommentVNode("", true),
                              renderSlot(_ctx.$slots, "trailing", {
                                item,
                                index,
                                open,
                                ui: ui.value
                              }, () => [
                                createVNode(_sfc_main$3, {
                                  name: item.trailingIcon || __props.trailingIcon || unref(appConfig).ui.icons.chevronDown,
                                  "data-slot": "trailingIcon",
                                  class: ui.value.trailingIcon({ class: [props.ui?.trailingIcon, item.ui?.trailingIcon] })
                                }, null, 8, ["name", "class"])
                              ])
                            ]),
                            _: 2
                          }, 1032, ["class"])
                        ]),
                        _: 2
                      }, 1032, ["class"]),
                      item.content || !!slots.content || item.slot && !!slots[item.slot] || !!slots.body || item.slot && !!slots[`${item.slot}-body`] ? (openBlock(), createBlock(unref(AccordionContent), {
                        key: 0,
                        "data-slot": "content",
                        class: ui.value.content({ class: [props.ui?.content, item.ui?.content] })
                      }, {
                        default: withCtx(() => [
                          renderSlot(_ctx.$slots, item.slot || "content", {
                            item,
                            index,
                            open,
                            ui: ui.value
                          }, () => [
                            createVNode("div", {
                              "data-slot": "body",
                              class: ui.value.body({ class: [props.ui?.body, item.ui?.body] })
                            }, [
                              renderSlot(_ctx.$slots, item.slot ? `${item.slot}-body` : "body", {
                                item,
                                index,
                                open,
                                ui: ui.value
                              }, () => [
                                createTextVNode(toDisplayString(item.content), 1)
                              ])
                            ], 2)
                          ])
                        ]),
                        _: 2
                      }, 1032, ["class"])) : createCommentVNode("", true)
                    ];
                  }
                }),
                _: 2
              }, _parent2, _scopeId));
            });
            _push2(`<!--]-->`);
          } else {
            return [
              (openBlock(true), createBlock(Fragment, null, renderList(props.items, (item, index) => {
                return openBlock(), createBlock(unref(AccordionItem), {
                  key: index,
                  value: unref(get)(item, props.valueKey) ?? String(index),
                  disabled: item.disabled,
                  "data-slot": "item",
                  class: ui.value.item({ class: [props.ui?.item, item.ui?.item, item.class] })
                }, {
                  default: withCtx(({ open }) => [
                    createVNode(unref(AccordionHeader), {
                      as: "div",
                      "data-slot": "header",
                      class: ui.value.header({ class: [props.ui?.header, item.ui?.header] })
                    }, {
                      default: withCtx(() => [
                        createVNode(unref(AccordionTrigger), {
                          "data-slot": "trigger",
                          class: ui.value.trigger({ class: [props.ui?.trigger, item.ui?.trigger], disabled: item.disabled })
                        }, {
                          default: withCtx(() => [
                            renderSlot(_ctx.$slots, "leading", {
                              item,
                              index,
                              open,
                              ui: ui.value
                            }, () => [
                              item.icon ? (openBlock(), createBlock(_sfc_main$3, {
                                key: 0,
                                name: item.icon,
                                "data-slot": "leadingIcon",
                                class: ui.value.leadingIcon({ class: [props.ui?.leadingIcon, item?.ui?.leadingIcon] })
                              }, null, 8, ["name", "class"])) : createCommentVNode("", true)
                            ]),
                            unref(get)(item, props.labelKey) || !!slots.default ? (openBlock(), createBlock("span", {
                              key: 0,
                              "data-slot": "label",
                              class: ui.value.label({ class: [props.ui?.label, item.ui?.label] })
                            }, [
                              renderSlot(_ctx.$slots, "default", {
                                item,
                                index,
                                open
                              }, () => [
                                createTextVNode(toDisplayString(unref(get)(item, props.labelKey)), 1)
                              ])
                            ], 2)) : createCommentVNode("", true),
                            renderSlot(_ctx.$slots, "trailing", {
                              item,
                              index,
                              open,
                              ui: ui.value
                            }, () => [
                              createVNode(_sfc_main$3, {
                                name: item.trailingIcon || __props.trailingIcon || unref(appConfig).ui.icons.chevronDown,
                                "data-slot": "trailingIcon",
                                class: ui.value.trailingIcon({ class: [props.ui?.trailingIcon, item.ui?.trailingIcon] })
                              }, null, 8, ["name", "class"])
                            ])
                          ]),
                          _: 2
                        }, 1032, ["class"])
                      ]),
                      _: 2
                    }, 1032, ["class"]),
                    item.content || !!slots.content || item.slot && !!slots[item.slot] || !!slots.body || item.slot && !!slots[`${item.slot}-body`] ? (openBlock(), createBlock(unref(AccordionContent), {
                      key: 0,
                      "data-slot": "content",
                      class: ui.value.content({ class: [props.ui?.content, item.ui?.content] })
                    }, {
                      default: withCtx(() => [
                        renderSlot(_ctx.$slots, item.slot || "content", {
                          item,
                          index,
                          open,
                          ui: ui.value
                        }, () => [
                          createVNode("div", {
                            "data-slot": "body",
                            class: ui.value.body({ class: [props.ui?.body, item.ui?.body] })
                          }, [
                            renderSlot(_ctx.$slots, item.slot ? `${item.slot}-body` : "body", {
                              item,
                              index,
                              open,
                              ui: ui.value
                            }, () => [
                              createTextVNode(toDisplayString(item.content), 1)
                            ])
                          ], 2)
                        ])
                      ]),
                      _: 2
                    }, 1032, ["class"])) : createCommentVNode("", true)
                  ]),
                  _: 2
                }, 1032, ["value", "disabled", "class"]);
              }), 128))
            ];
          }
        }),
        _: 3
      }, _parent));
    };
  }
};
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/Accordion.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "PageBlockRenderer",
  __ssrInlineRender: true,
  props: {
    blocks: {}
  },
  setup(__props) {
    const props = __props;
    const spacerClassMap = {
      sm: "h-4",
      md: "h-8",
      lg: "h-14",
      xl: "h-20"
    };
    function heroData(data) {
      const align = `${data.align ?? "left"}` === "center" ? "center" : "left";
      const variant = `${data.variant ?? "image-right"}`;
      const normalizedVariant = variant === "image-left" || variant === "image-bg" || variant === "text-only" ? variant : "image-right";
      return {
        headline: `${data.headline ?? ""}`,
        subheadline: `${data.subheadline ?? ""}`,
        primary_cta_label: `${data.primary_cta_label ?? ""}`,
        primary_cta_url: `${data.primary_cta_url ?? ""}`,
        secondary_cta_label: `${data.secondary_cta_label ?? ""}`,
        secondary_cta_url: `${data.secondary_cta_url ?? ""}`,
        align,
        variant: normalizedVariant,
        image: typeof data.image === "string" && data.image.trim() !== "" ? data.image : null
      };
    }
    function sectionRichData(data) {
      const rawContainer = `${data.container ?? "lg"}`;
      const container = rawContainer === "sm" || rawContainer === "md" || rawContainer === "xl" ? rawContainer : "lg";
      return {
        title: `${data.title ?? ""}`,
        content: `${data.content ?? ""}`,
        container,
        with_divider: Boolean(data.with_divider)
      };
    }
    function featuresData(data) {
      const rawColumns = Number(data.columns ?? 3);
      const columns = Number.isFinite(rawColumns) ? Math.min(4, Math.max(2, rawColumns)) : 3;
      const items = Array.isArray(data.items) ? data.items : [];
      return {
        title: `${data.title ?? ""}`,
        subtitle: `${data.subtitle ?? ""}`,
        columns,
        iconed: Boolean(data.iconed),
        carded: Boolean(data.carded),
        items: items.map((item) => {
          const row = item ?? {};
          return {
            title: `${row.title ?? ""}`,
            icon: `${row.icon ?? ""}`,
            description: `${row.description ?? ""}`
          };
        }).filter((item) => item.title !== "" || item.description !== "")
      };
    }
    function ctaData(data) {
      const rawStyle = `${data.style ?? "primary"}`;
      const style = rawStyle === "secondary" || rawStyle === "outline" ? rawStyle : "primary";
      return {
        title: `${data.title ?? ""}`,
        description: `${data.description ?? ""}`,
        button_label: `${data.button_label ?? ""}`,
        button_url: `${data.button_url ?? ""}`,
        style,
        accent: `${data.accent ?? ""}`
      };
    }
    function faqData(data) {
      const items = Array.isArray(data.items) ? data.items : [];
      return {
        title: `${data.title ?? ""}`,
        items: items.map((item) => {
          const row = item ?? {};
          return {
            q: `${row.q ?? ""}`,
            a: `${row.a ?? ""}`
          };
        }).filter((item) => item.q !== "" || item.a !== "")
      };
    }
    function faqAccordionItems(data) {
      return faqData(data).items.map((item, index) => ({
        label: item.q || `Pertanyaan ${index + 1}`,
        content: item.a,
        value: `faq-${index}`
      }));
    }
    function testimonialsData(data) {
      const items = Array.isArray(data.items) ? data.items : [];
      return {
        title: `${data.title ?? ""}`,
        items: items.map((item) => {
          const row = item ?? {};
          return {
            name: `${row.name ?? ""}`,
            role: `${row.role ?? ""}`,
            quote: `${row.quote ?? ""}`,
            avatar: typeof row.avatar === "string" && row.avatar.trim() !== "" ? row.avatar : null
          };
        }).filter((item) => item.name !== "" || item.quote !== "")
      };
    }
    function testimonialInitial(name) {
      const normalizedName = name.trim();
      if (normalizedName === "") {
        return void 0;
      }
      return normalizedName.charAt(0).toUpperCase();
    }
    function spacerData(data) {
      const rawSize = `${data.size ?? "md"}`;
      const size = rawSize === "sm" || rawSize === "lg" || rawSize === "xl" ? rawSize : "md";
      return { size };
    }
    function customHtmlData(data) {
      return {
        html: `${data.html ?? ""}`,
        meta: typeof data.meta === "object" && data.meta !== null ? data.meta : {}
      };
    }
    function headingData(data) {
      const rawLevel = Number(data.level ?? 2);
      const normalizedLevel = Number.isFinite(rawLevel) ? Math.min(6, Math.max(1, Math.round(rawLevel))) : 2;
      return {
        level: normalizedLevel,
        text: `${data.text ?? ""}` || `${data.content ?? ""}`
      };
    }
    function headingTag(level) {
      return `h${level}`;
    }
    function richTextData(data) {
      return {
        content: `${data.content ?? ""}` || `${data.text ?? ""}`
      };
    }
    function imageData(data) {
      return {
        url: typeof data.url === "string" && data.url.trim() !== "" ? data.url : null,
        alt: `${data.alt ?? ""}`,
        caption: `${data.caption ?? ""}`
      };
    }
    function listData(data) {
      const items = Array.isArray(data.items) ? data.items : [];
      return {
        ordered: Boolean(data.ordered),
        items: items.map((item) => `${item ?? ""}`.trim()).filter((item) => item !== "")
      };
    }
    function quoteData(data) {
      return {
        quote: `${data.quote ?? ""}` || `${data.text ?? ""}`,
        cite: `${data.cite ?? ""}`
      };
    }
    function featureGridClass(columns) {
      if (columns === 2) {
        return "md:grid-cols-2";
      }
      if (columns === 4) {
        return "md:grid-cols-2 xl:grid-cols-4";
      }
      return "md:grid-cols-2 xl:grid-cols-3";
    }
    function heroTextAlignClass(align) {
      return align === "center" ? "text-center items-center" : "text-left items-start";
    }
    function heroLayoutClass(variant) {
      if (variant === "image-left") {
        return "lg:flex-row-reverse";
      }
      if (variant === "image-right") {
        return "lg:flex-row";
      }
      return "";
    }
    function ctaColor(style) {
      if (style === "secondary") {
        return "secondary";
      }
      if (style === "outline") {
        return "neutral";
      }
      return "primary";
    }
    function ctaVariant(style) {
      return style === "outline" ? "outline" : "solid";
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$4;
      const _component_UCard = _sfc_main$5;
      const _component_USeparator = _sfc_main$6;
      const _component_UIcon = _sfc_main$3;
      const _component_UAccordion = _sfc_main$2;
      const _component_UAvatar = _sfc_main$7;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}><!--[-->`);
      ssrRenderList(props.blocks, (block, index) => {
        _push(`<!--[-->`);
        if (block.type === "hero") {
          _push(`<section class="relative overflow-hidden rounded-3xl border border-default/80 bg-linear-to-br from-primary-50/70 via-white to-cyan-50/40 p-6 dark:from-primary-950/50 dark:via-gray-950 dark:to-cyan-950/30 sm:p-8">`);
          if (heroData(block.data).variant === "image-bg" && heroData(block.data).image) {
            _push(`<div class="absolute inset-0"><img${ssrRenderAttr("src", heroData(block.data).image ?? "")}${ssrRenderAttr("alt", heroData(block.data).headline || "Hero image")} class="h-full w-full object-cover opacity-20"></div>`);
          } else {
            _push(`<!---->`);
          }
          _push(`<div class="${ssrRenderClass([heroLayoutClass(heroData(block.data).variant), "relative flex flex-col gap-6"])}"><div class="${ssrRenderClass([heroTextAlignClass(heroData(block.data).align), "flex-1 space-y-4"])}"><h2 class="text-2xl font-bold leading-tight text-highlighted sm:text-4xl">${ssrInterpolate(heroData(block.data).headline || "Hero")}</h2>`);
          if (heroData(block.data).subheadline) {
            _push(`<p class="max-w-2xl text-sm leading-relaxed text-muted sm:text-base">${ssrInterpolate(heroData(block.data).subheadline)}</p>`);
          } else {
            _push(`<!---->`);
          }
          _push(`<div class="${ssrRenderClass([heroData(block.data).align === "center" ? "justify-center" : "", "flex flex-wrap gap-2"])}">`);
          if (heroData(block.data).primary_cta_label) {
            _push(ssrRenderComponent(_component_UButton, {
              to: heroData(block.data).primary_cta_url || void 0,
              color: "primary",
              variant: "solid",
              "trailing-icon": "i-lucide-arrow-right"
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(`${ssrInterpolate(heroData(block.data).primary_cta_label)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(heroData(block.data).primary_cta_label), 1)
                  ];
                }
              }),
              _: 2
            }, _parent));
          } else {
            _push(`<!---->`);
          }
          if (heroData(block.data).secondary_cta_label) {
            _push(ssrRenderComponent(_component_UButton, {
              to: heroData(block.data).secondary_cta_url || void 0,
              color: "neutral",
              variant: "outline"
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(`${ssrInterpolate(heroData(block.data).secondary_cta_label)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(heroData(block.data).secondary_cta_label), 1)
                  ];
                }
              }),
              _: 2
            }, _parent));
          } else {
            _push(`<!---->`);
          }
          _push(`</div></div>`);
          if (heroData(block.data).image && heroData(block.data).variant !== "text-only" && heroData(block.data).variant !== "image-bg") {
            _push(`<div class="w-full lg:w-2/5"><div class="overflow-hidden rounded-2xl border border-default/80 bg-elevated"><img${ssrRenderAttr("src", heroData(block.data).image ?? "")}${ssrRenderAttr("alt", heroData(block.data).headline || "Hero image")} class="h-full w-full object-cover"></div></div>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div></section>`);
        } else if (block.type === "section_rich") {
          _push(`<section class="space-y-4">`);
          if (sectionRichData(block.data).title) {
            _push(`<h2 class="text-xl font-semibold text-highlighted sm:text-2xl">${ssrInterpolate(sectionRichData(block.data).title)}</h2>`);
          } else {
            _push(`<!---->`);
          }
          _push(ssrRenderComponent(_component_UCard, {
            class: "rounded-3xl border border-default/80",
            ui: { body: "p-5 sm:p-8" }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<article class="prose prose-gray max-w-none dark:prose-invert"${_scopeId}>${sectionRichData(block.data).content ?? ""}</article>`);
              } else {
                return [
                  createVNode("article", {
                    class: "prose prose-gray max-w-none dark:prose-invert",
                    innerHTML: sectionRichData(block.data).content
                  }, null, 8, ["innerHTML"])
                ];
              }
            }),
            _: 2
          }, _parent));
          if (sectionRichData(block.data).with_divider) {
            _push(ssrRenderComponent(_component_USeparator, null, null, _parent));
          } else {
            _push(`<!---->`);
          }
          _push(`</section>`);
        } else if (block.type === "heading") {
          _push(`<section>`);
          ssrRenderVNode(_push, createVNode(resolveDynamicComponent(headingTag(headingData(block.data).level)), { class: "text-xl font-semibold leading-tight text-highlighted sm:text-2xl" }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(headingData(block.data).text)}`);
              } else {
                return [
                  createTextVNode(toDisplayString(headingData(block.data).text), 1)
                ];
              }
            }),
            _: 2
          }), _parent);
          _push(`</section>`);
        } else if (block.type === "rich_text" || block.type === "paragraph" || block.type === "richtext") {
          _push(`<section>`);
          _push(ssrRenderComponent(_component_UCard, {
            class: "rounded-3xl border border-default/80",
            ui: { body: "p-5 sm:p-8" }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<article class="prose prose-gray max-w-none dark:prose-invert"${_scopeId}>${richTextData(block.data).content ?? ""}</article>`);
              } else {
                return [
                  createVNode("article", {
                    class: "prose prose-gray max-w-none dark:prose-invert",
                    innerHTML: richTextData(block.data).content
                  }, null, 8, ["innerHTML"])
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`</section>`);
        } else if (block.type === "image") {
          _push(`<figure class="space-y-2">`);
          _push(ssrRenderComponent(_component_UCard, {
            class: "overflow-hidden rounded-2xl border border-default/80",
            ui: { body: "p-0" }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                if (imageData(block.data).url) {
                  _push2(`<img${ssrRenderAttr("src", imageData(block.data).url ?? "")}${ssrRenderAttr("alt", imageData(block.data).alt || "Image")} class="h-full w-full object-cover"${_scopeId}>`);
                } else {
                  _push2(`<div class="grid h-48 place-items-center bg-elevated"${_scopeId}>`);
                  _push2(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-image-off",
                    class: "size-7 text-muted"
                  }, null, _parent2, _scopeId));
                  _push2(`</div>`);
                }
              } else {
                return [
                  imageData(block.data).url ? (openBlock(), createBlock("img", {
                    key: 0,
                    src: imageData(block.data).url ?? "",
                    alt: imageData(block.data).alt || "Image",
                    class: "h-full w-full object-cover"
                  }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                    key: 1,
                    class: "grid h-48 place-items-center bg-elevated"
                  }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-image-off",
                      class: "size-7 text-muted"
                    })
                  ]))
                ];
              }
            }),
            _: 2
          }, _parent));
          if (imageData(block.data).caption) {
            _push(`<figcaption class="text-sm text-muted">${ssrInterpolate(imageData(block.data).caption)}</figcaption>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</figure>`);
        } else if (block.type === "list") {
          _push(`<section>`);
          _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl border border-default/80" }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                ssrRenderVNode(_push2, createVNode(resolveDynamicComponent(listData(block.data).ordered ? "ol" : "ul"), { class: "space-y-2 pl-5 text-sm text-muted" }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`<!--[-->`);
                      ssrRenderList(listData(block.data).items, (item, itemIndex) => {
                        _push3(`<li${_scopeId2}>${item ?? ""}</li>`);
                      });
                      _push3(`<!--]-->`);
                    } else {
                      return [
                        (openBlock(true), createBlock(Fragment, null, renderList(listData(block.data).items, (item, itemIndex) => {
                          return openBlock(), createBlock("li", {
                            key: `legacy-list-${index}-${itemIndex}`,
                            innerHTML: item
                          }, null, 8, ["innerHTML"]);
                        }), 128))
                      ];
                    }
                  }),
                  _: 2
                }), _parent2, _scopeId);
              } else {
                return [
                  (openBlock(), createBlock(resolveDynamicComponent(listData(block.data).ordered ? "ol" : "ul"), { class: "space-y-2 pl-5 text-sm text-muted" }, {
                    default: withCtx(() => [
                      (openBlock(true), createBlock(Fragment, null, renderList(listData(block.data).items, (item, itemIndex) => {
                        return openBlock(), createBlock("li", {
                          key: `legacy-list-${index}-${itemIndex}`,
                          innerHTML: item
                        }, null, 8, ["innerHTML"]);
                      }), 128))
                    ]),
                    _: 2
                  }, 1024))
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`</section>`);
        } else if (block.type === "quote") {
          _push(`<section>`);
          _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl border border-default/80" }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<blockquote class="border-l-4 border-primary pl-4"${_scopeId}><p class="text-sm text-highlighted"${_scopeId}>${ssrInterpolate(quoteData(block.data).quote)}</p>`);
                if (quoteData(block.data).cite) {
                  _push2(`<cite class="mt-2 block text-xs not-italic text-muted"${_scopeId}> — ${ssrInterpolate(quoteData(block.data).cite)}</cite>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</blockquote>`);
              } else {
                return [
                  createVNode("blockquote", { class: "border-l-4 border-primary pl-4" }, [
                    createVNode("p", { class: "text-sm text-highlighted" }, toDisplayString(quoteData(block.data).quote), 1),
                    quoteData(block.data).cite ? (openBlock(), createBlock("cite", {
                      key: 0,
                      class: "mt-2 block text-xs not-italic text-muted"
                    }, " — " + toDisplayString(quoteData(block.data).cite), 1)) : createCommentVNode("", true)
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`</section>`);
        } else if (block.type === "features" || block.type === "features_grid") {
          _push(`<section class="space-y-4"><div class="space-y-2">`);
          if (featuresData(block.data).title) {
            _push(`<h2 class="text-xl font-semibold text-highlighted sm:text-2xl">${ssrInterpolate(featuresData(block.data).title)}</h2>`);
          } else {
            _push(`<!---->`);
          }
          if (featuresData(block.data).subtitle) {
            _push(`<p class="text-sm text-muted">${ssrInterpolate(featuresData(block.data).subtitle)}</p>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</div><div class="${ssrRenderClass([featureGridClass(featuresData(block.data).columns), "grid gap-3"])}"><!--[-->`);
          ssrRenderList(featuresData(block.data).items, (item, itemIndex) => {
            _push(ssrRenderComponent(_component_UCard, {
              key: `feature-item-${index}-${itemIndex}`,
              class: "rounded-2xl border border-default/80"
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(`<div class="space-y-2"${_scopeId}><div class="flex items-center gap-2"${_scopeId}>`);
                  if (featuresData(block.data).iconed) {
                    _push2(ssrRenderComponent(_component_UIcon, {
                      name: item.icon || "i-lucide-star",
                      class: "size-4 text-primary"
                    }, null, _parent2, _scopeId));
                  } else {
                    _push2(`<!---->`);
                  }
                  _push2(`<h3 class="text-sm font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(item.title)}</h3></div><p class="text-sm text-muted"${_scopeId}>${ssrInterpolate(item.description)}</p></div>`);
                } else {
                  return [
                    createVNode("div", { class: "space-y-2" }, [
                      createVNode("div", { class: "flex items-center gap-2" }, [
                        featuresData(block.data).iconed ? (openBlock(), createBlock(_component_UIcon, {
                          key: 0,
                          name: item.icon || "i-lucide-star",
                          class: "size-4 text-primary"
                        }, null, 8, ["name"])) : createCommentVNode("", true),
                        createVNode("h3", { class: "text-sm font-semibold text-highlighted" }, toDisplayString(item.title), 1)
                      ]),
                      createVNode("p", { class: "text-sm text-muted" }, toDisplayString(item.description), 1)
                    ])
                  ];
                }
              }),
              _: 2
            }, _parent));
          });
          _push(`<!--]--></div></section>`);
        } else if (block.type === "cta") {
          _push(`<section>`);
          _push(ssrRenderComponent(_component_UCard, { class: "rounded-3xl border border-default/80 bg-linear-to-r from-primary-50/60 to-cyan-50/40 dark:from-primary-950/30 dark:to-cyan-950/30" }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><div class="space-y-1"${_scopeId}><h2 class="text-xl font-semibold text-highlighted sm:text-2xl"${_scopeId}>${ssrInterpolate(ctaData(block.data).title || "Call to Action")}</h2>`);
                if (ctaData(block.data).description) {
                  _push2(`<p class="text-sm text-muted"${_scopeId}>${ssrInterpolate(ctaData(block.data).description)}</p>`);
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div>`);
                if (ctaData(block.data).button_label) {
                  _push2(ssrRenderComponent(_component_UButton, {
                    to: ctaData(block.data).button_url || void 0,
                    color: ctaColor(ctaData(block.data).style),
                    variant: ctaVariant(ctaData(block.data).style),
                    "trailing-icon": "i-lucide-arrow-right"
                  }, {
                    default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                      if (_push3) {
                        _push3(`${ssrInterpolate(ctaData(block.data).button_label)}`);
                      } else {
                        return [
                          createTextVNode(toDisplayString(ctaData(block.data).button_label), 1)
                        ];
                      }
                    }),
                    _: 2
                  }, _parent2, _scopeId));
                } else {
                  _push2(`<!---->`);
                }
                _push2(`</div>`);
              } else {
                return [
                  createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" }, [
                    createVNode("div", { class: "space-y-1" }, [
                      createVNode("h2", { class: "text-xl font-semibold text-highlighted sm:text-2xl" }, toDisplayString(ctaData(block.data).title || "Call to Action"), 1),
                      ctaData(block.data).description ? (openBlock(), createBlock("p", {
                        key: 0,
                        class: "text-sm text-muted"
                      }, toDisplayString(ctaData(block.data).description), 1)) : createCommentVNode("", true)
                    ]),
                    ctaData(block.data).button_label ? (openBlock(), createBlock(_component_UButton, {
                      key: 0,
                      to: ctaData(block.data).button_url || void 0,
                      color: ctaColor(ctaData(block.data).style),
                      variant: ctaVariant(ctaData(block.data).style),
                      "trailing-icon": "i-lucide-arrow-right"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(toDisplayString(ctaData(block.data).button_label), 1)
                      ]),
                      _: 2
                    }, 1032, ["to", "color", "variant"])) : createCommentVNode("", true)
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`</section>`);
        } else if (block.type === "faq") {
          _push(`<section class="space-y-3">`);
          if (faqData(block.data).title) {
            _push(`<h2 class="text-xl font-semibold text-highlighted sm:text-2xl">${ssrInterpolate(faqData(block.data).title)}</h2>`);
          } else {
            _push(`<!---->`);
          }
          _push(ssrRenderComponent(_component_UAccordion, {
            items: faqAccordionItems(block.data),
            type: "multiple",
            collapsible: "",
            ui: {
              item: "rounded-2xl border border-default/80 px-3 py-1",
              trigger: "text-sm font-semibold text-highlighted",
              content: "pt-1"
            }
          }, {
            body: withCtx(({ item }, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<p class="whitespace-pre-line text-sm leading-relaxed text-muted"${_scopeId}>${ssrInterpolate(item.content)}</p>`);
              } else {
                return [
                  createVNode("p", { class: "whitespace-pre-line text-sm leading-relaxed text-muted" }, toDisplayString(item.content), 1)
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`</section>`);
        } else if (block.type === "testimonials" || block.type === "testimonial") {
          _push(`<section class="space-y-4">`);
          if (testimonialsData(block.data).title) {
            _push(`<h2 class="text-xl font-semibold text-highlighted sm:text-2xl">${ssrInterpolate(testimonialsData(block.data).title)}</h2>`);
          } else {
            _push(`<!---->`);
          }
          _push(`<div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"><!--[-->`);
          ssrRenderList(testimonialsData(block.data).items, (item, itemIndex) => {
            _push(ssrRenderComponent(_component_UCard, {
              key: `testimonial-item-${index}-${itemIndex}`,
              class: "rounded-2xl border border-default/80"
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(`<div class="space-y-3"${_scopeId}><div class="flex items-center gap-3"${_scopeId}>`);
                  _push2(ssrRenderComponent(_component_UAvatar, {
                    src: item.avatar ?? void 0,
                    alt: item.name || "Avatar",
                    text: testimonialInitial(item.name),
                    icon: "i-lucide-user",
                    size: "lg"
                  }, null, _parent2, _scopeId));
                  _push2(`<div${_scopeId}><p class="text-sm font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(item.name || "Anonymous")}</p>`);
                  if (item.role) {
                    _push2(`<p class="text-xs text-muted"${_scopeId}>${ssrInterpolate(item.role)}</p>`);
                  } else {
                    _push2(`<!---->`);
                  }
                  _push2(`</div></div><p class="text-sm leading-relaxed text-muted"${_scopeId}>&quot;${ssrInterpolate(item.quote)}&quot;</p></div>`);
                } else {
                  return [
                    createVNode("div", { class: "space-y-3" }, [
                      createVNode("div", { class: "flex items-center gap-3" }, [
                        createVNode(_component_UAvatar, {
                          src: item.avatar ?? void 0,
                          alt: item.name || "Avatar",
                          text: testimonialInitial(item.name),
                          icon: "i-lucide-user",
                          size: "lg"
                        }, null, 8, ["src", "alt", "text"]),
                        createVNode("div", null, [
                          createVNode("p", { class: "text-sm font-semibold text-highlighted" }, toDisplayString(item.name || "Anonymous"), 1),
                          item.role ? (openBlock(), createBlock("p", {
                            key: 0,
                            class: "text-xs text-muted"
                          }, toDisplayString(item.role), 1)) : createCommentVNode("", true)
                        ])
                      ]),
                      createVNode("p", { class: "text-sm leading-relaxed text-muted" }, '"' + toDisplayString(item.quote) + '"', 1)
                    ])
                  ];
                }
              }),
              _: 2
            }, _parent));
          });
          _push(`<!--]--></div></section>`);
        } else if (block.type === "divider") {
          _push(ssrRenderComponent(_component_USeparator, null, null, _parent));
        } else if (block.type === "spacer") {
          _push(`<div class="${ssrRenderClass(spacerClassMap[spacerData(block.data).size])}"></div>`);
        } else if (block.type === "custom_html") {
          _push(`<section class="space-y-3">`);
          _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl border border-warning/40 bg-warning/5" }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<div class="flex items-center gap-2 text-sm text-warning"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-triangle-alert",
                  class: "size-4"
                }, null, _parent2, _scopeId));
                _push2(`<span${_scopeId}>Custom HTML</span></div>`);
              } else {
                return [
                  createVNode("div", { class: "flex items-center gap-2 text-sm text-warning" }, [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-triangle-alert",
                      class: "size-4"
                    }),
                    createVNode("span", null, "Custom HTML")
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`<div class="prose prose-gray max-w-none dark:prose-invert">${customHtmlData(block.data).html ?? ""}</div></section>`);
        } else {
          _push(`<section>`);
          _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl border border-default/80" }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<p class="text-sm text-muted"${_scopeId}> Blok <span class="font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(block.type)}</span> belum didukung di storefront. </p>`);
              } else {
                return [
                  createVNode("p", { class: "text-sm text-muted" }, [
                    createTextVNode(" Blok "),
                    createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(block.type), 1),
                    createTextVNode(" belum didukung di storefront. ")
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
          _push(`</section>`);
        }
        _push(`<!--]-->`);
      });
      _push(`<!--]--></div>`);
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/page/PageBlockRenderer.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$8 },
  __name: "Show",
  __ssrInlineRender: true,
  props: {
    seo: {},
    page: {}
  },
  setup(__props) {
    const props = __props;
    const structuredDataScripts = computed(() => {
      const payload = props.seo.structured_data ?? [];
      return payload.map((item) => JSON.stringify(item));
    });
    const hasBlocks = computed(() => props.page.blocks.length > 0);
    const hasFallbackContent = computed(() => props.page.content_html.trim() !== "");
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$4;
      const _component_UCard = _sfc_main$5;
      const _component_UBadge = _sfc_main$a;
      const _component_UIcon = _sfc_main$3;
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_sfc_main$9, {
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
                key: `page-show-ld-${index}`,
                type: "application/ld+json"
              }, null), _parent2, _scopeId);
            });
            _push2(`<!--]-->`);
          } else {
            return [
              (openBlock(true), createBlock(Fragment, null, renderList(structuredDataScripts.value, (script, index) => {
                return openBlock(), createBlock(resolveDynamicComponent("script"), {
                  key: `page-show-ld-${index}`,
                  type: "application/ld+json",
                  innerHTML: script
                }, null, 8, ["innerHTML"]);
              }), 128))
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="min-h-screen bg-gray-50/60 py-8 transition-colors duration-300 dark:bg-gray-950"><div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">`);
      _push(ssrRenderComponent(_component_UButton, {
        to: "/",
        color: "neutral",
        variant: "outline",
        icon: "i-lucide-arrow-left",
        size: "sm",
        class: "w-fit"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Kembali ke Beranda `);
          } else {
            return [
              createTextVNode(" Kembali ke Beranda ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-3xl border border-default/80 bg-linear-to-br from-primary-50/60 via-white to-cyan-50/40 dark:from-primary-950/40 dark:via-gray-950 dark:to-cyan-950/20" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "primary",
              variant: "soft",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Halaman `);
                } else {
                  return [
                    createTextVNode(" Halaman ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "neutral",
              variant: "subtle",
              class: "rounded-full"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Template: ${ssrInterpolate(props.page.template)}`);
                } else {
                  return [
                    createTextVNode(" Template: " + toDisplayString(props.page.template), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (props.page.published_label) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "neutral",
                variant: "subtle",
                class: "rounded-full"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Update ${ssrInterpolate(props.page.published_label)}`);
                  } else {
                    return [
                      createTextVNode(" Update " + toDisplayString(props.page.published_label), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><h1 class="text-2xl font-bold leading-tight text-highlighted sm:text-4xl"${_scopeId}>${ssrInterpolate(props.page.title)}</h1><p class="max-w-3xl text-sm leading-relaxed text-muted sm:text-base"${_scopeId}>${ssrInterpolate(props.page.excerpt)}</p></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                  createVNode(_component_UBadge, {
                    color: "primary",
                    variant: "soft",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Halaman ")
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UBadge, {
                    color: "neutral",
                    variant: "subtle",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Template: " + toDisplayString(props.page.template), 1)
                    ]),
                    _: 1
                  }),
                  props.page.published_label ? (openBlock(), createBlock(_component_UBadge, {
                    key: 0,
                    color: "neutral",
                    variant: "subtle",
                    class: "rounded-full"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Update " + toDisplayString(props.page.published_label), 1)
                    ]),
                    _: 1
                  })) : createCommentVNode("", true)
                ]),
                createVNode("h1", { class: "text-2xl font-bold leading-tight text-highlighted sm:text-4xl" }, toDisplayString(props.page.title), 1),
                createVNode("p", { class: "max-w-3xl text-sm leading-relaxed text-muted sm:text-base" }, toDisplayString(props.page.excerpt), 1)
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      if (hasBlocks.value) {
        _push(ssrRenderComponent(_sfc_main$1, {
          blocks: props.page.blocks
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      if (!hasBlocks.value && hasFallbackContent.value) {
        _push(ssrRenderComponent(_component_UCard, {
          class: "rounded-3xl border border-default/80",
          ui: { body: "p-5 sm:p-8" }
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<article class="prose prose-gray max-w-none dark:prose-invert"${_scopeId}>${props.page.content_html ?? ""}</article>`);
            } else {
              return [
                createVNode("article", {
                  class: "prose prose-gray max-w-none dark:prose-invert",
                  innerHTML: props.page.content_html
                }, null, 8, ["innerHTML"])
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      if (!hasBlocks.value && !hasFallbackContent.value) {
        _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl border border-dashed border-default text-center" }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<div class="py-8"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-file-text",
                class: "mx-auto mb-3 size-8 text-muted"
              }, null, _parent2, _scopeId));
              _push2(`<p class="text-sm text-muted"${_scopeId}>Konten halaman belum tersedia.</p></div>`);
            } else {
              return [
                createVNode("div", { class: "py-8" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-file-text",
                    class: "mx-auto mb-3 size-8 text-muted"
                  }),
                  createVNode("p", { class: "text-sm text-muted" }, "Konten halaman belum tersedia.")
                ])
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Page/Show.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
