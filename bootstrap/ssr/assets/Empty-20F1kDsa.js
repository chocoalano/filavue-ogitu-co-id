import { useSlots, computed, unref, mergeProps, withCtx, openBlock, createBlock, renderSlot, createCommentVNode, createTextVNode, toDisplayString, Fragment, renderList, useSSRContext } from "vue";
import { ssrRenderComponent, ssrRenderClass, ssrRenderSlot, ssrInterpolate, ssrRenderList } from "vue/server-renderer";
import { Primitive } from "reka-ui";
import "@inertiajs/vue3";
import { t as tv, u as useAppConfig } from "./Icon-Chcm7u9q.js";
import { c as _sfc_main$1, _ as _sfc_main$2 } from "./Button-DLZCZWnW.js";
const theme = {
  "slots": {
    "root": "relative flex flex-col items-center justify-center gap-4 rounded-lg p-4 sm:p-6 lg:p-8 min-w-0",
    "header": "flex flex-col items-center gap-2 max-w-sm text-center",
    "avatar": "shrink-0 mb-2",
    "title": "text-highlighted text-pretty font-medium",
    "description": "text-balance text-center",
    "body": "flex flex-col items-center gap-4 max-w-sm",
    "actions": "flex flex-wrap justify-center gap-2 shrink-0",
    "footer": "flex flex-col items-center gap-2 max-w-sm"
  },
  "variants": {
    "size": {
      "xs": {
        "avatar": "size-8 text-base",
        "title": "text-sm",
        "description": "text-xs"
      },
      "sm": {
        "avatar": "size-9 text-lg",
        "title": "text-sm",
        "description": "text-xs"
      },
      "md": {
        "avatar": "size-10 text-xl",
        "title": "text-base",
        "description": "text-sm"
      },
      "lg": {
        "avatar": "size-11 text-[22px]",
        "title": "text-base",
        "description": "text-sm"
      },
      "xl": {
        "avatar": "size-12 text-2xl",
        "title": "text-lg",
        "description": "text-base"
      }
    },
    "variant": {
      "solid": {
        "root": "bg-inverted",
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
      "naked": {
        "description": "text-muted"
      }
    }
  },
  "defaultVariants": {
    "variant": "outline",
    "size": "md"
  }
};
const _sfc_main = {
  __name: "Empty",
  __ssrInlineRender: true,
  props: {
    as: { type: null, required: false },
    icon: { type: null, required: false },
    avatar: { type: Object, required: false },
    title: { type: String, required: false },
    description: { type: String, required: false },
    actions: { type: Array, required: false },
    variant: { type: null, required: false },
    size: { type: null, required: false },
    class: { type: null, required: false },
    ui: { type: null, required: false }
  },
  setup(__props) {
    const props = __props;
    const slots = useSlots();
    const appConfig = useAppConfig();
    const ui = computed(() => tv({ extend: tv(theme), ...appConfig.ui?.empty || {} })({
      variant: props.variant,
      size: props.size
    }));
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Primitive), mergeProps({
        as: __props.as,
        "data-slot": "root",
        class: ui.value.root({ class: [props.ui?.root, props.class] })
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (!!slots.header || (__props.icon || __props.avatar || !!slots.leading) || (__props.title || !!slots.title) || (__props.description || !!slots.description)) {
              _push2(`<div data-slot="header" class="${ssrRenderClass(ui.value.header({ class: props.ui?.header }))}"${_scopeId}>`);
              ssrRenderSlot(_ctx.$slots, "header", {}, () => {
                ssrRenderSlot(_ctx.$slots, "leading", { ui: ui.value }, () => {
                  if (__props.icon || __props.avatar) {
                    _push2(ssrRenderComponent(_sfc_main$1, mergeProps({ icon: __props.icon }, typeof __props.avatar === "object" ? __props.avatar : {}, {
                      "data-slot": "avatar",
                      class: ui.value.avatar({ class: props.ui?.avatar })
                    }), null, _parent2, _scopeId));
                  } else {
                    _push2(`<!---->`);
                  }
                }, _push2, _parent2, _scopeId);
                if (__props.title || !!slots.title) {
                  _push2(`<h2 data-slot="title" class="${ssrRenderClass(ui.value.title({ class: props.ui?.title }))}"${_scopeId}>`);
                  ssrRenderSlot(_ctx.$slots, "title", {}, () => {
                    _push2(`${ssrInterpolate(__props.title)}`);
                  }, _push2, _parent2, _scopeId);
                  _push2(`</h2>`);
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
            if (!!slots.body || (__props.actions?.length || !!slots.actions)) {
              _push2(`<div data-slot="body" class="${ssrRenderClass(ui.value.body({ class: props.ui?.body }))}"${_scopeId}>`);
              ssrRenderSlot(_ctx.$slots, "body", {}, () => {
                if (__props.actions?.length || !!slots.actions) {
                  _push2(`<div data-slot="actions" class="${ssrRenderClass(ui.value.actions({ class: props.ui?.actions }))}"${_scopeId}>`);
                  ssrRenderSlot(_ctx.$slots, "actions", {}, () => {
                    _push2(`<!--[-->`);
                    ssrRenderList(__props.actions, (action, index) => {
                      _push2(ssrRenderComponent(_sfc_main$2, mergeProps({
                        key: index,
                        size: __props.size
                      }, { ref_for: true }, action), null, _parent2, _scopeId));
                    });
                    _push2(`<!--]-->`);
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
          } else {
            return [
              !!slots.header || (__props.icon || __props.avatar || !!slots.leading) || (__props.title || !!slots.title) || (__props.description || !!slots.description) ? (openBlock(), createBlock("div", {
                key: 0,
                "data-slot": "header",
                class: ui.value.header({ class: props.ui?.header })
              }, [
                renderSlot(_ctx.$slots, "header", {}, () => [
                  renderSlot(_ctx.$slots, "leading", { ui: ui.value }, () => [
                    __props.icon || __props.avatar ? (openBlock(), createBlock(_sfc_main$1, mergeProps({
                      key: 0,
                      icon: __props.icon
                    }, typeof __props.avatar === "object" ? __props.avatar : {}, {
                      "data-slot": "avatar",
                      class: ui.value.avatar({ class: props.ui?.avatar })
                    }), null, 16, ["icon", "class"])) : createCommentVNode("", true)
                  ]),
                  __props.title || !!slots.title ? (openBlock(), createBlock("h2", {
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
              !!slots.body || (__props.actions?.length || !!slots.actions) ? (openBlock(), createBlock("div", {
                key: 1,
                "data-slot": "body",
                class: ui.value.body({ class: props.ui?.body })
              }, [
                renderSlot(_ctx.$slots, "body", {}, () => [
                  __props.actions?.length || !!slots.actions ? (openBlock(), createBlock("div", {
                    key: 0,
                    "data-slot": "actions",
                    class: ui.value.actions({ class: props.ui?.actions })
                  }, [
                    renderSlot(_ctx.$slots, "actions", {}, () => [
                      (openBlock(true), createBlock(Fragment, null, renderList(__props.actions, (action, index) => {
                        return openBlock(), createBlock(_sfc_main$2, mergeProps({
                          key: index,
                          size: __props.size
                        }, { ref_for: true }, action), null, 16, ["size"]);
                      }), 128))
                    ])
                  ], 2)) : createCommentVNode("", true)
                ])
              ], 2)) : createCommentVNode("", true),
              !!slots.footer ? (openBlock(), createBlock("div", {
                key: 2,
                "data-slot": "footer",
                class: ui.value.footer({ class: props.ui?.footer })
              }, [
                renderSlot(_ctx.$slots, "footer")
              ], 2)) : createCommentVNode("", true)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/components/Empty.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as _
};
