import { _ as _sfc_main$4 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$3 } from "./Checkbox-PZ4-MUog.js";
import { _ as _sfc_main$2 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$1 } from "./Card-CvchAxCK.js";
import { defineComponent, ref, mergeProps, withCtx, createVNode, createTextVNode, useSSRContext } from "vue";
import { ssrRenderComponent } from "vue/server-renderer";
import { router } from "@inertiajs/vue3";
import "defu";
import "reka-ui";
import "@vueuse/core";
import "ufo";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "DeleteAccount",
  __ssrInlineRender: true,
  setup(__props) {
    const confirmed = ref(false);
    function deleteAccount() {
      router.delete("/account");
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$1;
      const _component_UIcon = _sfc_main$2;
      const _component_UCheckbox = _sfc_main$3;
      const _component_UButton = _sfc_main$4;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center gap-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-user-x",
              class: "size-5 text-rose-500"
            }, null, _parent2, _scopeId));
            _push2(`<p class="text-base font-semibold text-gray-900 dark:text-white"${_scopeId}>Delete Account</p></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center gap-3" }, [
                createVNode(_component_UIcon, {
                  name: "i-lucide-user-x",
                  class: "size-5 text-rose-500"
                }),
                createVNode("p", { class: "text-base font-semibold text-gray-900 dark:text-white" }, "Delete Account")
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UCard, { class: "rounded-2xl border border-rose-200 bg-rose-50/60 dark:border-rose-900/50 dark:bg-rose-950/30" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex items-start gap-3"${_scopeId2}><div class="grid size-10 place-items-center rounded-xl bg-rose-100 dark:bg-rose-950/50"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-triangle-alert",
                    class: "size-5 text-rose-600 dark:text-rose-300"
                  }, null, _parent3, _scopeId2));
                  _push3(`</div><div class="min-w-0 space-y-1"${_scopeId2}><p class="text-sm font-semibold text-rose-800 dark:text-rose-200"${_scopeId2}>Peringatan</p><p class="text-xs text-rose-700 dark:text-rose-300"${_scopeId2}> Menghapus akun bersifat <strong${_scopeId2}>permanen</strong> dan tidak dapat dibatalkan. Semua data, termasuk pesanan, saldo wallet, dan bonus akan dihapus selamanya. </p></div></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex items-start gap-3" }, [
                      createVNode("div", { class: "grid size-10 place-items-center rounded-xl bg-rose-100 dark:bg-rose-950/50" }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-triangle-alert",
                          class: "size-5 text-rose-600 dark:text-rose-300"
                        })
                      ]),
                      createVNode("div", { class: "min-w-0 space-y-1" }, [
                        createVNode("p", { class: "text-sm font-semibold text-rose-800 dark:text-rose-200" }, "Peringatan"),
                        createVNode("p", { class: "text-xs text-rose-700 dark:text-rose-300" }, [
                          createTextVNode(" Menghapus akun bersifat "),
                          createVNode("strong", null, "permanen"),
                          createTextVNode(" dan tidak dapat dibatalkan. Semua data, termasuk pesanan, saldo wallet, dan bonus akan dihapus selamanya. ")
                        ])
                      ])
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UCheckbox, {
              modelValue: confirmed.value,
              "onUpdate:modelValue": ($event) => confirmed.value = $event,
              label: "Saya memahami bahwa tindakan ini permanen dan tidak dapat dibatalkan."
            }, null, _parent2, _scopeId));
            _push2(`<div class="flex justify-end"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              disabled: !confirmed.value,
              color: "error",
              variant: "solid",
              class: "rounded-xl",
              icon: "i-lucide-user-x",
              onClick: deleteAccount
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Hapus Akun Sekarang `);
                } else {
                  return [
                    createTextVNode(" Hapus Akun Sekarang ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode(_component_UCard, { class: "rounded-2xl border border-rose-200 bg-rose-50/60 dark:border-rose-900/50 dark:bg-rose-950/30" }, {
                  default: withCtx(() => [
                    createVNode("div", { class: "flex items-start gap-3" }, [
                      createVNode("div", { class: "grid size-10 place-items-center rounded-xl bg-rose-100 dark:bg-rose-950/50" }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-triangle-alert",
                          class: "size-5 text-rose-600 dark:text-rose-300"
                        })
                      ]),
                      createVNode("div", { class: "min-w-0 space-y-1" }, [
                        createVNode("p", { class: "text-sm font-semibold text-rose-800 dark:text-rose-200" }, "Peringatan"),
                        createVNode("p", { class: "text-xs text-rose-700 dark:text-rose-300" }, [
                          createTextVNode(" Menghapus akun bersifat "),
                          createVNode("strong", null, "permanen"),
                          createTextVNode(" dan tidak dapat dibatalkan. Semua data, termasuk pesanan, saldo wallet, dan bonus akan dihapus selamanya. ")
                        ])
                      ])
                    ])
                  ]),
                  _: 1
                }),
                createVNode(_component_UCheckbox, {
                  modelValue: confirmed.value,
                  "onUpdate:modelValue": ($event) => confirmed.value = $event,
                  label: "Saya memahami bahwa tindakan ini permanen dan tidak dapat dibatalkan."
                }, null, 8, ["modelValue", "onUpdate:modelValue"]),
                createVNode("div", { class: "flex justify-end" }, [
                  createVNode(_component_UButton, {
                    disabled: !confirmed.value,
                    color: "error",
                    variant: "solid",
                    class: "rounded-xl",
                    icon: "i-lucide-user-x",
                    onClick: deleteAccount
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Hapus Akun Sekarang ")
                    ]),
                    _: 1
                  }, 8, ["disabled"])
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
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/DeleteAccount.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
