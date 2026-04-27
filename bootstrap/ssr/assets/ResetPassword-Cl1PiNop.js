import { _ as _sfc_main$7 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$6 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$5 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$4 } from "./Form-Cnb1D1Z4.js";
import { _ as _sfc_main$3 } from "./Icon-Chcm7u9q.js";
import { defineComponent, ref, unref, withCtx, createVNode, createTextVNode, toDisplayString, useSSRContext } from "vue";
import { ssrRenderComponent, ssrInterpolate } from "vue/server-renderer";
import { a as _sfc_main$1 } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$2 } from "./SeoHead-CrFJik-H.js";
import { useForm } from "@inertiajs/vue3";
import "defu";
import "reka-ui";
import "@vueuse/core";
import "ufo";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "./useToast-CTuSIf9f.js";
import "./usePortal-EQErrF6h.js";
import "./useStoreData-DrTMI0On.js";
import "./Separator-CuyFd3xv.js";
import "reka-ui/namespaced";
import "./Checkbox-PZ4-MUog.js";
import "./Badge-DqskWDDq.js";
import "vaul-vue";
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$1 },
  __name: "ResetPassword",
  __ssrInlineRender: true,
  props: {
    resetUrl: {},
    seo: {}
  },
  setup(__props) {
    const props = __props;
    const showPassword = ref(false);
    const showPasswordConfirmation = ref(false);
    const form = useForm({
      password: "",
      password_confirmation: ""
    });
    function onSubmit() {
      form.post(props.resetUrl, {
        preserveScroll: true,
        onFinish: () => form.reset("password", "password_confirmation")
      });
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$3;
      const _component_UForm = _sfc_main$4;
      const _component_UFormField = _sfc_main$5;
      const _component_UInput = _sfc_main$6;
      const _component_UButton = _sfc_main$7;
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_sfc_main$2, {
        title: __props.seo.title,
        description: __props.seo.description,
        canonical: __props.seo.canonical
      }, null, _parent));
      _push(`<div class="flex min-h-dvh flex-col items-center justify-center bg-white px-6 py-12 dark:bg-slate-950 lg:px-12"><div class="mb-8 flex items-center gap-2 lg:hidden">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-sparkles",
        class: "size-5 text-primary-500"
      }, null, _parent));
      _push(`<span class="text-lg font-black tracking-tight text-slate-900 dark:text-white">ogitu.id</span></div><div class="w-full max-w-md"><div class="mb-8"><div class="mb-2 flex items-center gap-2">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-lock-keyhole",
        class: "size-6 text-primary-500"
      }, null, _parent));
      _push(`<h1 class="text-2xl font-bold text-slate-900 dark:text-white">Buat Kata Sandi Baru</h1></div><p class="text-sm text-slate-500 dark:text-slate-400"> Masukkan kata sandi baru untuk akun Anda. Gunakan kombinasi huruf, angka, dan simbol agar lebih aman. </p></div>`);
      _push(ssrRenderComponent(_component_UForm, {
        state: unref(form),
        class: "space-y-5",
        onSubmit
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Kata Sandi Baru",
              name: "password",
              required: "",
              error: unref(form).errors.password
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).password,
                    "onUpdate:modelValue": ($event) => unref(form).password = $event,
                    id: "password",
                    type: showPassword.value ? "text" : "password",
                    placeholder: "Minimal 8 karakter",
                    autocomplete: "new-password",
                    class: "w-full",
                    ui: { trailing: "pe-1" }
                  }, {
                    trailing: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(ssrRenderComponent(_component_UButton, {
                          color: "neutral",
                          type: "button",
                          variant: "link",
                          size: "sm",
                          icon: showPassword.value ? "i-lucide-eye-off" : "i-lucide-eye",
                          "aria-label": showPassword.value ? "Sembunyikan kata sandi" : "Tampilkan kata sandi",
                          "aria-pressed": showPassword.value,
                          "aria-controls": "password",
                          onClick: ($event) => showPassword.value = !showPassword.value
                        }, null, _parent4, _scopeId3));
                      } else {
                        return [
                          createVNode(_component_UButton, {
                            color: "neutral",
                            type: "button",
                            variant: "link",
                            size: "sm",
                            icon: showPassword.value ? "i-lucide-eye-off" : "i-lucide-eye",
                            "aria-label": showPassword.value ? "Sembunyikan kata sandi" : "Tampilkan kata sandi",
                            "aria-pressed": showPassword.value,
                            "aria-controls": "password",
                            onClick: ($event) => showPassword.value = !showPassword.value
                          }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).password,
                      "onUpdate:modelValue": ($event) => unref(form).password = $event,
                      id: "password",
                      type: showPassword.value ? "text" : "password",
                      placeholder: "Minimal 8 karakter",
                      autocomplete: "new-password",
                      class: "w-full",
                      ui: { trailing: "pe-1" }
                    }, {
                      trailing: withCtx(() => [
                        createVNode(_component_UButton, {
                          color: "neutral",
                          type: "button",
                          variant: "link",
                          size: "sm",
                          icon: showPassword.value ? "i-lucide-eye-off" : "i-lucide-eye",
                          "aria-label": showPassword.value ? "Sembunyikan kata sandi" : "Tampilkan kata sandi",
                          "aria-pressed": showPassword.value,
                          "aria-controls": "password",
                          onClick: ($event) => showPassword.value = !showPassword.value
                        }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                      ]),
                      _: 1
                    }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Konfirmasi Kata Sandi",
              name: "password_confirmation",
              required: "",
              error: unref(form).errors.password_confirmation
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).password_confirmation,
                    "onUpdate:modelValue": ($event) => unref(form).password_confirmation = $event,
                    id: "password_confirmation",
                    type: showPasswordConfirmation.value ? "text" : "password",
                    placeholder: "Ulangi kata sandi baru",
                    autocomplete: "new-password",
                    class: "w-full",
                    ui: { trailing: "pe-1" }
                  }, {
                    trailing: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(ssrRenderComponent(_component_UButton, {
                          color: "neutral",
                          type: "button",
                          variant: "link",
                          size: "sm",
                          icon: showPasswordConfirmation.value ? "i-lucide-eye-off" : "i-lucide-eye",
                          "aria-label": showPasswordConfirmation.value ? "Sembunyikan konfirmasi" : "Tampilkan konfirmasi",
                          "aria-pressed": showPasswordConfirmation.value,
                          "aria-controls": "password_confirmation",
                          onClick: ($event) => showPasswordConfirmation.value = !showPasswordConfirmation.value
                        }, null, _parent4, _scopeId3));
                      } else {
                        return [
                          createVNode(_component_UButton, {
                            color: "neutral",
                            type: "button",
                            variant: "link",
                            size: "sm",
                            icon: showPasswordConfirmation.value ? "i-lucide-eye-off" : "i-lucide-eye",
                            "aria-label": showPasswordConfirmation.value ? "Sembunyikan konfirmasi" : "Tampilkan konfirmasi",
                            "aria-pressed": showPasswordConfirmation.value,
                            "aria-controls": "password_confirmation",
                            onClick: ($event) => showPasswordConfirmation.value = !showPasswordConfirmation.value
                          }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).password_confirmation,
                      "onUpdate:modelValue": ($event) => unref(form).password_confirmation = $event,
                      id: "password_confirmation",
                      type: showPasswordConfirmation.value ? "text" : "password",
                      placeholder: "Ulangi kata sandi baru",
                      autocomplete: "new-password",
                      class: "w-full",
                      ui: { trailing: "pe-1" }
                    }, {
                      trailing: withCtx(() => [
                        createVNode(_component_UButton, {
                          color: "neutral",
                          type: "button",
                          variant: "link",
                          size: "sm",
                          icon: showPasswordConfirmation.value ? "i-lucide-eye-off" : "i-lucide-eye",
                          "aria-label": showPasswordConfirmation.value ? "Sembunyikan konfirmasi" : "Tampilkan konfirmasi",
                          "aria-pressed": showPasswordConfirmation.value,
                          "aria-controls": "password_confirmation",
                          onClick: ($event) => showPasswordConfirmation.value = !showPasswordConfirmation.value
                        }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                      ]),
                      _: 1
                    }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              type: "submit",
              block: "",
              size: "lg",
              color: "primary",
              loading: unref(form).processing,
              disabled: unref(form).processing,
              "leading-icon": "i-lucide-lock-keyhole"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(unref(form).processing ? "Menyimpan…" : "Simpan Kata Sandi Baru")}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(unref(form).processing ? "Menyimpan…" : "Simpan Kata Sandi Baru"), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UFormField, {
                label: "Kata Sandi Baru",
                name: "password",
                required: "",
                error: unref(form).errors.password
              }, {
                default: withCtx(() => [
                  createVNode(_component_UInput, {
                    modelValue: unref(form).password,
                    "onUpdate:modelValue": ($event) => unref(form).password = $event,
                    id: "password",
                    type: showPassword.value ? "text" : "password",
                    placeholder: "Minimal 8 karakter",
                    autocomplete: "new-password",
                    class: "w-full",
                    ui: { trailing: "pe-1" }
                  }, {
                    trailing: withCtx(() => [
                      createVNode(_component_UButton, {
                        color: "neutral",
                        type: "button",
                        variant: "link",
                        size: "sm",
                        icon: showPassword.value ? "i-lucide-eye-off" : "i-lucide-eye",
                        "aria-label": showPassword.value ? "Sembunyikan kata sandi" : "Tampilkan kata sandi",
                        "aria-pressed": showPassword.value,
                        "aria-controls": "password",
                        onClick: ($event) => showPassword.value = !showPassword.value
                      }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                    ]),
                    _: 1
                  }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                ]),
                _: 1
              }, 8, ["error"]),
              createVNode(_component_UFormField, {
                label: "Konfirmasi Kata Sandi",
                name: "password_confirmation",
                required: "",
                error: unref(form).errors.password_confirmation
              }, {
                default: withCtx(() => [
                  createVNode(_component_UInput, {
                    modelValue: unref(form).password_confirmation,
                    "onUpdate:modelValue": ($event) => unref(form).password_confirmation = $event,
                    id: "password_confirmation",
                    type: showPasswordConfirmation.value ? "text" : "password",
                    placeholder: "Ulangi kata sandi baru",
                    autocomplete: "new-password",
                    class: "w-full",
                    ui: { trailing: "pe-1" }
                  }, {
                    trailing: withCtx(() => [
                      createVNode(_component_UButton, {
                        color: "neutral",
                        type: "button",
                        variant: "link",
                        size: "sm",
                        icon: showPasswordConfirmation.value ? "i-lucide-eye-off" : "i-lucide-eye",
                        "aria-label": showPasswordConfirmation.value ? "Sembunyikan konfirmasi" : "Tampilkan konfirmasi",
                        "aria-pressed": showPasswordConfirmation.value,
                        "aria-controls": "password_confirmation",
                        onClick: ($event) => showPasswordConfirmation.value = !showPasswordConfirmation.value
                      }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                    ]),
                    _: 1
                  }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                ]),
                _: 1
              }, 8, ["error"]),
              createVNode(_component_UButton, {
                type: "submit",
                block: "",
                size: "lg",
                color: "primary",
                loading: unref(form).processing,
                disabled: unref(form).processing,
                "leading-icon": "i-lucide-lock-keyhole"
              }, {
                default: withCtx(() => [
                  createTextVNode(toDisplayString(unref(form).processing ? "Menyimpan…" : "Simpan Kata Sandi Baru"), 1)
                ]),
                _: 1
              }, 8, ["loading", "disabled"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/ResetPassword.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
