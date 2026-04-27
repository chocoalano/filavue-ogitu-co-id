import { useSlots, computed, unref, mergeProps, withCtx, renderSlot, createVNode, openBlock, createBlock, createCommentVNode, Fragment, renderList, useSSRContext } from "vue";
import { ssrRenderComponent, ssrRenderSlot, ssrRenderList } from "vue/server-renderer";
import { useForwardPropsEmits, PaginationRoot, PaginationList, PaginationFirst, PaginationPrev, PaginationListItem, PaginationEllipsis, PaginationNext, PaginationLast } from "reka-ui";
import { reactivePick } from "@vueuse/core";
import "@inertiajs/vue3";
import { a as useLocale, t as tv, u as useAppConfig } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$1 } from "./Button-DLZCZWnW.js";
const theme = {
  "slots": {
    "root": "",
    "list": "flex items-center gap-1",
    "ellipsis": "pointer-events-none",
    "label": "min-w-5 text-center",
    "first": "",
    "prev": "",
    "item": "",
    "next": "",
    "last": ""
  }
};
const _sfc_main = {
  __name: "Pagination",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false },
    firstIcon: { type: null, required: false },
    prevIcon: { type: null, required: false },
    nextIcon: { type: null, required: false },
    lastIcon: { type: null, required: false },
    ellipsisIcon: { type: null, required: false },
    color: { type: null, required: false, default: "neutral" },
    variant: { type: null, required: false, default: "outline" },
    activeColor: { type: null, required: false, default: "primary" },
    activeVariant: { type: null, required: false, default: "solid" },
    showControls: { type: Boolean, required: false, default: true },
    size: { type: null, required: false },
    to: { type: Function, required: false },
    class: { type: null, required: false },
    ui: { type: null, required: false },
    defaultPage: { type: Number, required: false },
    disabled: { type: Boolean, required: false },
    itemsPerPage: { type: Number, required: false, default: 10 },
    page: { type: Number, required: false },
    showEdges: { type: Boolean, required: false, default: false },
    siblingCount: { type: Number, required: false, default: 2 },
    total: { type: Number, required: false, default: 0 }
  },
  emits: ["update:page"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emits = __emit;
    const slots = useSlots();
    const { dir } = useLocale();
    const appConfig = useAppConfig();
    const rootProps = useForwardPropsEmits(reactivePick(props, "as", "defaultPage", "disabled", "itemsPerPage", "page", "showEdges", "siblingCount", "total"), emits);
    const firstIcon = computed(() => props.firstIcon || (dir.value === "rtl" ? appConfig.ui.icons.chevronDoubleRight : appConfig.ui.icons.chevronDoubleLeft));
    const prevIcon = computed(() => props.prevIcon || (dir.value === "rtl" ? appConfig.ui.icons.chevronRight : appConfig.ui.icons.chevronLeft));
    const nextIcon = computed(() => props.nextIcon || (dir.value === "rtl" ? appConfig.ui.icons.chevronLeft : appConfig.ui.icons.chevronRight));
    const lastIcon = computed(() => props.lastIcon || (dir.value === "rtl" ? appConfig.ui.icons.chevronDoubleLeft : appConfig.ui.icons.chevronDoubleRight));
    const ui = computed(() => tv({ extend: tv(theme), ...appConfig.ui?.pagination || {} })());
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(PaginationRoot), mergeProps(unref(rootProps), {
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] })
      }, _attrs), {
        default: withCtx(({ page, pageCount }, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(unref(PaginationList), {
              "data-slot": "list",
              class: ui.value.list({ class: props.ui?.list })
            }, {
              default: withCtx(({ items }, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  if (__props.showControls || !!slots.first) {
                    _push3(ssrRenderComponent(unref(PaginationFirst), {
                      "as-child": "",
                      "data-slot": "first",
                      class: ui.value.first({ class: props.ui?.first })
                    }, {
                      default: withCtx((_, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          ssrRenderSlot(_ctx.$slots, "first", {}, () => {
                            _push4(ssrRenderComponent(_sfc_main$1, {
                              color: __props.color,
                              variant: __props.variant,
                              size: __props.size,
                              icon: firstIcon.value,
                              to: __props.to?.(1)
                            }, null, _parent4, _scopeId3));
                          }, _push4, _parent4, _scopeId3);
                        } else {
                          return [
                            renderSlot(_ctx.$slots, "first", {}, () => [
                              createVNode(_sfc_main$1, {
                                color: __props.color,
                                variant: __props.variant,
                                size: __props.size,
                                icon: firstIcon.value,
                                to: __props.to?.(1)
                              }, null, 8, ["color", "variant", "size", "icon", "to"])
                            ])
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                  } else {
                    _push3(`<!---->`);
                  }
                  if (__props.showControls || !!slots.prev) {
                    _push3(ssrRenderComponent(unref(PaginationPrev), {
                      "as-child": "",
                      "data-slot": "prev",
                      class: ui.value.prev({ class: props.ui?.prev })
                    }, {
                      default: withCtx((_, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          ssrRenderSlot(_ctx.$slots, "prev", {}, () => {
                            _push4(ssrRenderComponent(_sfc_main$1, {
                              color: __props.color,
                              variant: __props.variant,
                              size: __props.size,
                              icon: prevIcon.value,
                              to: page > 1 ? __props.to?.(page - 1) : void 0
                            }, null, _parent4, _scopeId3));
                          }, _push4, _parent4, _scopeId3);
                        } else {
                          return [
                            renderSlot(_ctx.$slots, "prev", {}, () => [
                              createVNode(_sfc_main$1, {
                                color: __props.color,
                                variant: __props.variant,
                                size: __props.size,
                                icon: prevIcon.value,
                                to: page > 1 ? __props.to?.(page - 1) : void 0
                              }, null, 8, ["color", "variant", "size", "icon", "to"])
                            ])
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                  } else {
                    _push3(`<!---->`);
                  }
                  _push3(`<!--[-->`);
                  ssrRenderList(items, (item, index) => {
                    _push3(`<!--[-->`);
                    if (item.type === "page") {
                      _push3(ssrRenderComponent(unref(PaginationListItem), {
                        "as-child": "",
                        value: item.value,
                        "data-slot": "item",
                        class: ui.value.item({ class: props.ui?.item })
                      }, {
                        default: withCtx((_, _push4, _parent4, _scopeId3) => {
                          if (_push4) {
                            ssrRenderSlot(_ctx.$slots, "item", mergeProps({ ref_for: true }, { item, index, page, pageCount }), () => {
                              _push4(ssrRenderComponent(_sfc_main$1, {
                                color: page === item.value ? __props.activeColor : __props.color,
                                variant: page === item.value ? __props.activeVariant : __props.variant,
                                size: __props.size,
                                label: String(item.value),
                                ui: { label: ui.value.label() },
                                to: __props.to?.(item.value),
                                square: ""
                              }, null, _parent4, _scopeId3));
                            }, _push4, _parent4, _scopeId3);
                          } else {
                            return [
                              renderSlot(_ctx.$slots, "item", mergeProps({ ref_for: true }, { item, index, page, pageCount }), () => [
                                createVNode(_sfc_main$1, {
                                  color: page === item.value ? __props.activeColor : __props.color,
                                  variant: page === item.value ? __props.activeVariant : __props.variant,
                                  size: __props.size,
                                  label: String(item.value),
                                  ui: { label: ui.value.label() },
                                  to: __props.to?.(item.value),
                                  square: ""
                                }, null, 8, ["color", "variant", "size", "label", "ui", "to"])
                              ])
                            ];
                          }
                        }),
                        _: 2
                      }, _parent3, _scopeId2));
                    } else {
                      _push3(ssrRenderComponent(unref(PaginationEllipsis), {
                        "as-child": "",
                        "data-slot": "ellipsis",
                        class: ui.value.ellipsis({ class: props.ui?.ellipsis })
                      }, {
                        default: withCtx((_, _push4, _parent4, _scopeId3) => {
                          if (_push4) {
                            ssrRenderSlot(_ctx.$slots, "ellipsis", { ui: ui.value }, () => {
                              _push4(ssrRenderComponent(_sfc_main$1, {
                                as: "div",
                                color: __props.color,
                                variant: __props.variant,
                                size: __props.size,
                                icon: __props.ellipsisIcon || unref(appConfig).ui.icons.ellipsis
                              }, null, _parent4, _scopeId3));
                            }, _push4, _parent4, _scopeId3);
                          } else {
                            return [
                              renderSlot(_ctx.$slots, "ellipsis", { ui: ui.value }, () => [
                                createVNode(_sfc_main$1, {
                                  as: "div",
                                  color: __props.color,
                                  variant: __props.variant,
                                  size: __props.size,
                                  icon: __props.ellipsisIcon || unref(appConfig).ui.icons.ellipsis
                                }, null, 8, ["color", "variant", "size", "icon"])
                              ])
                            ];
                          }
                        }),
                        _: 2
                      }, _parent3, _scopeId2));
                    }
                    _push3(`<!--]-->`);
                  });
                  _push3(`<!--]-->`);
                  if (__props.showControls || !!slots.next) {
                    _push3(ssrRenderComponent(unref(PaginationNext), {
                      "as-child": "",
                      "data-slot": "next",
                      class: ui.value.next({ class: props.ui?.next })
                    }, {
                      default: withCtx((_, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          ssrRenderSlot(_ctx.$slots, "next", {}, () => {
                            _push4(ssrRenderComponent(_sfc_main$1, {
                              color: __props.color,
                              variant: __props.variant,
                              size: __props.size,
                              icon: nextIcon.value,
                              to: page < pageCount ? __props.to?.(page + 1) : void 0
                            }, null, _parent4, _scopeId3));
                          }, _push4, _parent4, _scopeId3);
                        } else {
                          return [
                            renderSlot(_ctx.$slots, "next", {}, () => [
                              createVNode(_sfc_main$1, {
                                color: __props.color,
                                variant: __props.variant,
                                size: __props.size,
                                icon: nextIcon.value,
                                to: page < pageCount ? __props.to?.(page + 1) : void 0
                              }, null, 8, ["color", "variant", "size", "icon", "to"])
                            ])
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                  } else {
                    _push3(`<!---->`);
                  }
                  if (__props.showControls || !!slots.last) {
                    _push3(ssrRenderComponent(unref(PaginationLast), {
                      "as-child": "",
                      "data-slot": "last",
                      class: ui.value.last({ class: props.ui?.last })
                    }, {
                      default: withCtx((_, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          ssrRenderSlot(_ctx.$slots, "last", {}, () => {
                            _push4(ssrRenderComponent(_sfc_main$1, {
                              color: __props.color,
                              variant: __props.variant,
                              size: __props.size,
                              icon: lastIcon.value,
                              to: __props.to?.(pageCount)
                            }, null, _parent4, _scopeId3));
                          }, _push4, _parent4, _scopeId3);
                        } else {
                          return [
                            renderSlot(_ctx.$slots, "last", {}, () => [
                              createVNode(_sfc_main$1, {
                                color: __props.color,
                                variant: __props.variant,
                                size: __props.size,
                                icon: lastIcon.value,
                                to: __props.to?.(pageCount)
                              }, null, 8, ["color", "variant", "size", "icon", "to"])
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
                    __props.showControls || !!slots.first ? (openBlock(), createBlock(unref(PaginationFirst), {
                      key: 0,
                      "as-child": "",
                      "data-slot": "first",
                      class: ui.value.first({ class: props.ui?.first })
                    }, {
                      default: withCtx(() => [
                        renderSlot(_ctx.$slots, "first", {}, () => [
                          createVNode(_sfc_main$1, {
                            color: __props.color,
                            variant: __props.variant,
                            size: __props.size,
                            icon: firstIcon.value,
                            to: __props.to?.(1)
                          }, null, 8, ["color", "variant", "size", "icon", "to"])
                        ])
                      ]),
                      _: 3
                    }, 8, ["class"])) : createCommentVNode("", true),
                    __props.showControls || !!slots.prev ? (openBlock(), createBlock(unref(PaginationPrev), {
                      key: 1,
                      "as-child": "",
                      "data-slot": "prev",
                      class: ui.value.prev({ class: props.ui?.prev })
                    }, {
                      default: withCtx(() => [
                        renderSlot(_ctx.$slots, "prev", {}, () => [
                          createVNode(_sfc_main$1, {
                            color: __props.color,
                            variant: __props.variant,
                            size: __props.size,
                            icon: prevIcon.value,
                            to: page > 1 ? __props.to?.(page - 1) : void 0
                          }, null, 8, ["color", "variant", "size", "icon", "to"])
                        ])
                      ]),
                      _: 2
                    }, 1032, ["class"])) : createCommentVNode("", true),
                    (openBlock(true), createBlock(Fragment, null, renderList(items, (item, index) => {
                      return openBlock(), createBlock(Fragment, { key: index }, [
                        item.type === "page" ? (openBlock(), createBlock(unref(PaginationListItem), {
                          key: 0,
                          "as-child": "",
                          value: item.value,
                          "data-slot": "item",
                          class: ui.value.item({ class: props.ui?.item })
                        }, {
                          default: withCtx(() => [
                            renderSlot(_ctx.$slots, "item", mergeProps({ ref_for: true }, { item, index, page, pageCount }), () => [
                              createVNode(_sfc_main$1, {
                                color: page === item.value ? __props.activeColor : __props.color,
                                variant: page === item.value ? __props.activeVariant : __props.variant,
                                size: __props.size,
                                label: String(item.value),
                                ui: { label: ui.value.label() },
                                to: __props.to?.(item.value),
                                square: ""
                              }, null, 8, ["color", "variant", "size", "label", "ui", "to"])
                            ])
                          ]),
                          _: 2
                        }, 1032, ["value", "class"])) : (openBlock(), createBlock(unref(PaginationEllipsis), {
                          key: 1,
                          "as-child": "",
                          "data-slot": "ellipsis",
                          class: ui.value.ellipsis({ class: props.ui?.ellipsis })
                        }, {
                          default: withCtx(() => [
                            renderSlot(_ctx.$slots, "ellipsis", { ui: ui.value }, () => [
                              createVNode(_sfc_main$1, {
                                as: "div",
                                color: __props.color,
                                variant: __props.variant,
                                size: __props.size,
                                icon: __props.ellipsisIcon || unref(appConfig).ui.icons.ellipsis
                              }, null, 8, ["color", "variant", "size", "icon"])
                            ])
                          ]),
                          _: 3
                        }, 8, ["class"]))
                      ], 64);
                    }), 128)),
                    __props.showControls || !!slots.next ? (openBlock(), createBlock(unref(PaginationNext), {
                      key: 2,
                      "as-child": "",
                      "data-slot": "next",
                      class: ui.value.next({ class: props.ui?.next })
                    }, {
                      default: withCtx(() => [
                        renderSlot(_ctx.$slots, "next", {}, () => [
                          createVNode(_sfc_main$1, {
                            color: __props.color,
                            variant: __props.variant,
                            size: __props.size,
                            icon: nextIcon.value,
                            to: page < pageCount ? __props.to?.(page + 1) : void 0
                          }, null, 8, ["color", "variant", "size", "icon", "to"])
                        ])
                      ]),
                      _: 2
                    }, 1032, ["class"])) : createCommentVNode("", true),
                    __props.showControls || !!slots.last ? (openBlock(), createBlock(unref(PaginationLast), {
                      key: 3,
                      "as-child": "",
                      "data-slot": "last",
                      class: ui.value.last({ class: props.ui?.last })
                    }, {
                      default: withCtx(() => [
                        renderSlot(_ctx.$slots, "last", {}, () => [
                          createVNode(_sfc_main$1, {
                            color: __props.color,
                            variant: __props.variant,
                            size: __props.size,
                            icon: lastIcon.value,
                            to: __props.to?.(pageCount)
                          }, null, 8, ["color", "variant", "size", "icon", "to"])
                        ])
                      ]),
                      _: 2
                    }, 1032, ["class"])) : createCommentVNode("", true)
                  ];
                }
              }),
              _: 2
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(unref(PaginationList), {
                "data-slot": "list",
                class: ui.value.list({ class: props.ui?.list })
              }, {
                default: withCtx(({ items }) => [
                  __props.showControls || !!slots.first ? (openBlock(), createBlock(unref(PaginationFirst), {
                    key: 0,
                    "as-child": "",
                    "data-slot": "first",
                    class: ui.value.first({ class: props.ui?.first })
                  }, {
                    default: withCtx(() => [
                      renderSlot(_ctx.$slots, "first", {}, () => [
                        createVNode(_sfc_main$1, {
                          color: __props.color,
                          variant: __props.variant,
                          size: __props.size,
                          icon: firstIcon.value,
                          to: __props.to?.(1)
                        }, null, 8, ["color", "variant", "size", "icon", "to"])
                      ])
                    ]),
                    _: 3
                  }, 8, ["class"])) : createCommentVNode("", true),
                  __props.showControls || !!slots.prev ? (openBlock(), createBlock(unref(PaginationPrev), {
                    key: 1,
                    "as-child": "",
                    "data-slot": "prev",
                    class: ui.value.prev({ class: props.ui?.prev })
                  }, {
                    default: withCtx(() => [
                      renderSlot(_ctx.$slots, "prev", {}, () => [
                        createVNode(_sfc_main$1, {
                          color: __props.color,
                          variant: __props.variant,
                          size: __props.size,
                          icon: prevIcon.value,
                          to: page > 1 ? __props.to?.(page - 1) : void 0
                        }, null, 8, ["color", "variant", "size", "icon", "to"])
                      ])
                    ]),
                    _: 2
                  }, 1032, ["class"])) : createCommentVNode("", true),
                  (openBlock(true), createBlock(Fragment, null, renderList(items, (item, index) => {
                    return openBlock(), createBlock(Fragment, { key: index }, [
                      item.type === "page" ? (openBlock(), createBlock(unref(PaginationListItem), {
                        key: 0,
                        "as-child": "",
                        value: item.value,
                        "data-slot": "item",
                        class: ui.value.item({ class: props.ui?.item })
                      }, {
                        default: withCtx(() => [
                          renderSlot(_ctx.$slots, "item", mergeProps({ ref_for: true }, { item, index, page, pageCount }), () => [
                            createVNode(_sfc_main$1, {
                              color: page === item.value ? __props.activeColor : __props.color,
                              variant: page === item.value ? __props.activeVariant : __props.variant,
                              size: __props.size,
                              label: String(item.value),
                              ui: { label: ui.value.label() },
                              to: __props.to?.(item.value),
                              square: ""
                            }, null, 8, ["color", "variant", "size", "label", "ui", "to"])
                          ])
                        ]),
                        _: 2
                      }, 1032, ["value", "class"])) : (openBlock(), createBlock(unref(PaginationEllipsis), {
                        key: 1,
                        "as-child": "",
                        "data-slot": "ellipsis",
                        class: ui.value.ellipsis({ class: props.ui?.ellipsis })
                      }, {
                        default: withCtx(() => [
                          renderSlot(_ctx.$slots, "ellipsis", { ui: ui.value }, () => [
                            createVNode(_sfc_main$1, {
                              as: "div",
                              color: __props.color,
                              variant: __props.variant,
                              size: __props.size,
                              icon: __props.ellipsisIcon || unref(appConfig).ui.icons.ellipsis
                            }, null, 8, ["color", "variant", "size", "icon"])
                          ])
                        ]),
                        _: 3
                      }, 8, ["class"]))
                    ], 64);
                  }), 128)),
                  __props.showControls || !!slots.next ? (openBlock(), createBlock(unref(PaginationNext), {
                    key: 2,
                    "as-child": "",
                    "data-slot": "next",
                    class: ui.value.next({ class: props.ui?.next })
                  }, {
                    default: withCtx(() => [
                      renderSlot(_ctx.$slots, "next", {}, () => [
                        createVNode(_sfc_main$1, {
                          color: __props.color,
                          variant: __props.variant,
                          size: __props.size,
                          icon: nextIcon.value,
                          to: page < pageCount ? __props.to?.(page + 1) : void 0
                        }, null, 8, ["color", "variant", "size", "icon", "to"])
                      ])
                    ]),
                    _: 2
                  }, 1032, ["class"])) : createCommentVNode("", true),
                  __props.showControls || !!slots.last ? (openBlock(), createBlock(unref(PaginationLast), {
                    key: 3,
                    "as-child": "",
                    "data-slot": "last",
                    class: ui.value.last({ class: props.ui?.last })
                  }, {
                    default: withCtx(() => [
                      renderSlot(_ctx.$slots, "last", {}, () => [
                        createVNode(_sfc_main$1, {
                          color: __props.color,
                          variant: __props.variant,
                          size: __props.size,
                          icon: lastIcon.value,
                          to: __props.to?.(pageCount)
                        }, null, 8, ["color", "variant", "size", "icon", "to"])
                      ])
                    ]),
                    _: 2
                  }, 1032, ["class"])) : createCommentVNode("", true)
                ]),
                _: 2
              }, 1032, ["class"])
            ];
          }
        }),
        _: 3
      }, _parent));
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/Pagination.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as _
};
