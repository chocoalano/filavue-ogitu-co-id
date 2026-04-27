import { computed, unref, mergeProps, withCtx, renderSlot, useSSRContext, useSlots, openBlock, createBlock, createTextVNode, toDisplayString, createCommentVNode, createVNode, Fragment, renderList } from "vue";
import { ssrRenderComponent, ssrRenderSlot, ssrRenderClass, ssrInterpolate, ssrRenderList } from "vue/server-renderer";
import { Primitive, Slot } from "reka-ui";
import "@inertiajs/vue3";
import { t as tv, u as useAppConfig } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$3 } from "./Button-DLZCZWnW.js";
const theme$2 = {
  "base": "mt-8 pb-24 space-y-12"
};
const _sfc_main$2 = {
  __name: "PageBody",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false },
    class: { type: null, required: false }
  },
  setup(__props) {
    const props = __props;
    const appConfig = useAppConfig();
    const ui = computed(() => tv({ extend: tv(theme$2), ...appConfig.ui?.pageBody || {} }));
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Primitive), mergeProps({
        as: __props.as,
        class: ui.value({ class: props.class })
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            ssrRenderSlot(_ctx.$slots, "default", {}, null, _push2, _parent2, _scopeId);
          } else {
            return [
              renderSlot(_ctx.$slots, "default")
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/PageBody.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const theme$1 = {
  "slots": {
    "root": "relative border-b border-default py-8",
    "container": "",
    "wrapper": "flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4",
    "headline": "mb-2.5 text-sm font-semibold text-primary flex items-center gap-1.5",
    "title": "text-3xl sm:text-4xl text-pretty font-bold text-highlighted",
    "description": "text-lg text-pretty text-muted",
    "links": "flex flex-wrap items-center gap-1.5"
  },
  "variants": {
    "title": {
      "true": {
        "description": "mt-4"
      }
    }
  }
};
const _sfc_main$1 = {
  __name: "PageHeader",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false },
    headline: { type: String, required: false },
    title: { type: String, required: false },
    description: { type: String, required: false },
    links: { type: Array, required: false },
    class: { type: null, required: false },
    ui: { type: null, required: false }
  },
  setup(__props) {
    const props = __props;
    const slots = useSlots();
    const appConfig = useAppConfig();
    const ui = computed(() => tv({ extend: tv(theme$1), ...appConfig.ui?.pageHeader || {} })({
      title: !!props.title || !!slots.title
    }));
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Primitive), mergeProps({
        as: __props.as,
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] })
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (__props.headline || !!slots.headline) {
              _push2(`<div data-slot="headline" class="${ssrRenderClass(ui.value.headline({ class: props.ui?.headline }))}"${_scopeId}>`);
              ssrRenderSlot(_ctx.$slots, "headline", {}, () => {
                _push2(`${ssrInterpolate(__props.headline)}`);
              }, _push2, _parent2, _scopeId);
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div data-slot="container" class="${ssrRenderClass(ui.value.container({ class: props.ui?.container }))}"${_scopeId}><div data-slot="wrapper" class="${ssrRenderClass(ui.value.wrapper({ class: props.ui?.wrapper }))}"${_scopeId}>`);
            if (__props.title || !!slots.title) {
              _push2(`<h1 data-slot="title" class="${ssrRenderClass(ui.value.title({ class: props.ui?.title }))}"${_scopeId}>`);
              ssrRenderSlot(_ctx.$slots, "title", {}, () => {
                _push2(`${ssrInterpolate(__props.title)}`);
              }, _push2, _parent2, _scopeId);
              _push2(`</h1>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.links?.length || !!slots.links) {
              _push2(`<div data-slot="links" class="${ssrRenderClass(ui.value.links({ class: props.ui?.links }))}"${_scopeId}>`);
              ssrRenderSlot(_ctx.$slots, "links", {}, () => {
                _push2(`<!--[-->`);
                ssrRenderList(__props.links, (link, index) => {
                  _push2(ssrRenderComponent(_sfc_main$3, mergeProps({
                    key: index,
                    color: "neutral",
                    variant: "outline"
                  }, { ref_for: true }, link), null, _parent2, _scopeId));
                });
                _push2(`<!--]-->`);
              }, _push2, _parent2, _scopeId);
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
            if (__props.description || !!slots.description) {
              _push2(`<div data-slot="description" class="${ssrRenderClass(ui.value.description({ class: props.ui?.description }))}"${_scopeId}>`);
              ssrRenderSlot(_ctx.$slots, "description", {}, () => {
                _push2(`${ssrInterpolate(__props.description)}`);
              }, _push2, _parent2, _scopeId);
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            ssrRenderSlot(_ctx.$slots, "default", {}, null, _push2, _parent2, _scopeId);
            _push2(`</div>`);
          } else {
            return [
              __props.headline || !!slots.headline ? (openBlock(), createBlock("div", {
                key: 0,
                "data-slot": "headline",
                class: ui.value.headline({ class: props.ui?.headline })
              }, [
                renderSlot(_ctx.$slots, "headline", {}, () => [
                  createTextVNode(toDisplayString(__props.headline), 1)
                ])
              ], 2)) : createCommentVNode("", true),
              createVNode("div", {
                "data-slot": "container",
                class: ui.value.container({ class: props.ui?.container })
              }, [
                createVNode("div", {
                  "data-slot": "wrapper",
                  class: ui.value.wrapper({ class: props.ui?.wrapper })
                }, [
                  __props.title || !!slots.title ? (openBlock(), createBlock("h1", {
                    key: 0,
                    "data-slot": "title",
                    class: ui.value.title({ class: props.ui?.title })
                  }, [
                    renderSlot(_ctx.$slots, "title", {}, () => [
                      createTextVNode(toDisplayString(__props.title), 1)
                    ])
                  ], 2)) : createCommentVNode("", true),
                  __props.links?.length || !!slots.links ? (openBlock(), createBlock("div", {
                    key: 1,
                    "data-slot": "links",
                    class: ui.value.links({ class: props.ui?.links })
                  }, [
                    renderSlot(_ctx.$slots, "links", {}, () => [
                      (openBlock(true), createBlock(Fragment, null, renderList(__props.links, (link, index) => {
                        return openBlock(), createBlock(_sfc_main$3, mergeProps({
                          key: index,
                          color: "neutral",
                          variant: "outline"
                        }, { ref_for: true }, link), null, 16);
                      }), 128))
                    ])
                  ], 2)) : createCommentVNode("", true)
                ], 2),
                __props.description || !!slots.description ? (openBlock(), createBlock("div", {
                  key: 0,
                  "data-slot": "description",
                  class: ui.value.description({ class: props.ui?.description })
                }, [
                  renderSlot(_ctx.$slots, "description", {}, () => [
                    createTextVNode(toDisplayString(__props.description), 1)
                  ])
                ], 2)) : createCommentVNode("", true),
                renderSlot(_ctx.$slots, "default")
              ], 2)
            ];
          }
        }),
        _: 3
      }, _parent));
    };
  }
};
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/PageHeader.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const theme = {
  "slots": {
    "root": "flex flex-col lg:grid lg:grid-cols-10 lg:gap-10",
    "left": "lg:col-span-2",
    "center": "lg:col-span-8",
    "right": "lg:col-span-2 order-first lg:order-last"
  },
  "variants": {
    "left": {
      "true": ""
    },
    "right": {
      "true": ""
    }
  },
  "compoundVariants": [
    {
      "left": true,
      "right": true,
      "class": {
        "center": "lg:col-span-6"
      }
    },
    {
      "left": false,
      "right": false,
      "class": {
        "center": "lg:col-span-10"
      }
    }
  ]
};
const _sfc_main = {
  __name: "Page",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false },
    class: { type: null, required: false },
    ui: { type: null, required: false }
  },
  setup(__props) {
    const props = __props;
    const slots = useSlots();
    const appConfig = useAppConfig();
    const ui = computed(() => tv({ extend: tv(theme), ...appConfig.ui?.page || {} })({
      left: !!slots.left,
      right: !!slots.right
    }));
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Primitive), mergeProps({
        as: __props.as,
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] })
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (!!slots.left) {
              _push2(ssrRenderComponent(unref(Slot), {
                "data-slot": "left",
                class: ui.value.left({ class: props.ui?.left })
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    ssrRenderSlot(_ctx.$slots, "left", {}, null, _push3, _parent3, _scopeId2);
                  } else {
                    return [
                      renderSlot(_ctx.$slots, "left")
                    ];
                  }
                }),
                _: 3
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div data-slot="center" class="${ssrRenderClass(ui.value.center({ class: props.ui?.center }))}"${_scopeId}>`);
            ssrRenderSlot(_ctx.$slots, "default", {}, null, _push2, _parent2, _scopeId);
            _push2(`</div>`);
            if (!!slots.right) {
              _push2(ssrRenderComponent(unref(Slot), {
                "data-slot": "right",
                class: ui.value.right({ class: props.ui?.right })
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    ssrRenderSlot(_ctx.$slots, "right", {}, null, _push3, _parent3, _scopeId2);
                  } else {
                    return [
                      renderSlot(_ctx.$slots, "right")
                    ];
                  }
                }),
                _: 3
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              !!slots.left ? (openBlock(), createBlock(unref(Slot), {
                key: 0,
                "data-slot": "left",
                class: ui.value.left({ class: props.ui?.left })
              }, {
                default: withCtx(() => [
                  renderSlot(_ctx.$slots, "left")
                ]),
                _: 3
              }, 8, ["class"])) : createCommentVNode("", true),
              createVNode("div", {
                "data-slot": "center",
                class: ui.value.center({ class: props.ui?.center })
              }, [
                renderSlot(_ctx.$slots, "default")
              ], 2),
              !!slots.right ? (openBlock(), createBlock(unref(Slot), {
                key: 1,
                "data-slot": "right",
                class: ui.value.right({ class: props.ui?.right })
              }, {
                default: withCtx(() => [
                  renderSlot(_ctx.$slots, "right")
                ]),
                _: 3
              }, 8, ["class"])) : createCommentVNode("", true)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/Page.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as _,
  _sfc_main$1 as a,
  _sfc_main$2 as b
};
