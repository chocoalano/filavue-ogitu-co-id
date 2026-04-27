import { _ as _sfc_main$8 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$7 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$6 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$5 } from "./Form-Cnb1D1Z4.js";
import { _ as _sfc_main$4 } from "./Alert-BRMLOsUb.js";
import { _ as _sfc_main$3 } from "./Icon-Chcm7u9q.js";
import { defineComponent, computed, unref, withCtx, createVNode, createTextVNode, toDisplayString, useSSRContext } from "vue";
import { ssrRenderComponent, ssrInterpolate } from "vue/server-renderer";
import { a as _sfc_main$1 } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$2 } from "./SeoHead-CrFJik-H.js";
import { usePage, useForm, Link } from "@inertiajs/vue3";
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
  __name: "ForgotPassword",
  __ssrInlineRender: true,
  props: {
    seo: {}
  },
  setup(__props) {
    const page = usePage();
    const flashStatus = computed(() => page.props?.status);
    const firstError = computed(() => page.props.errors?.error);
    const form = useForm({
      username: "",
      telp: ""
    });
    function onSubmit() {
      form.post("/forgot-password", {
        preserveScroll: true
      });
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$3;
      const _component_UAlert = _sfc_main$4;
      const _component_UForm = _sfc_main$5;
      const _component_UFormField = _sfc_main$6;
      const _component_UInput = _sfc_main$7;
      const _component_UButton = _sfc_main$8;
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
        name: "i-lucide-message-circle",
        class: "size-6 text-primary-500"
      }, null, _parent));
      _push(`<h1 class="text-2xl font-bold text-slate-900 dark:text-white">Lupa Kata Sandi</h1></div><p class="text-sm text-slate-500 dark:text-slate-400"> Masukkan username dan nomor WhatsApp yang terdaftar untuk mendapatkan link reset kata sandi. </p></div>`);
      if (flashStatus.value) {
        _push(ssrRenderComponent(_component_UAlert, {
          class: "mb-6",
          color: "info",
          variant: "soft",
          icon: "i-lucide-info",
          title: flashStatus.value
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      if (firstError.value) {
        _push(ssrRenderComponent(_component_UAlert, {
          class: "mb-6",
          color: "error",
          variant: "soft",
          icon: "i-lucide-alert-triangle",
          title: "Gagal mengirim link",
          description: String(firstError.value)
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(ssrRenderComponent(_component_UForm, {
        state: unref(form),
        class: "space-y-5",
        onSubmit
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Username",
              name: "username",
              required: "",
              error: unref(form).errors.username
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).username,
                    "onUpdate:modelValue": ($event) => unref(form).username = $event,
                    placeholder: "Masukkan username Anda",
                    autocomplete: "username",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).username,
                      "onUpdate:modelValue": ($event) => unref(form).username = $event,
                      placeholder: "Masukkan username Anda",
                      autocomplete: "username",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Nomor WhatsApp",
              name: "telp",
              required: "",
              error: unref(form).errors.telp
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).telp,
                    "onUpdate:modelValue": ($event) => unref(form).telp = $event,
                    type: "tel",
                    placeholder: "Contoh: 08123456789",
                    autocomplete: "tel",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).telp,
                      "onUpdate:modelValue": ($event) => unref(form).telp = $event,
                      type: "tel",
                      placeholder: "Contoh: 08123456789",
                      autocomplete: "tel",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
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
              "leading-icon": "i-lucide-message-circle"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(unref(form).processing ? "Memproses…" : "Kirim via WhatsApp")}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(unref(form).processing ? "Memproses…" : "Kirim via WhatsApp"), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UFormField, {
                label: "Username",
                name: "username",
                required: "",
                error: unref(form).errors.username
              }, {
                default: withCtx(() => [
                  createVNode(_component_UInput, {
                    modelValue: unref(form).username,
                    "onUpdate:modelValue": ($event) => unref(form).username = $event,
                    placeholder: "Masukkan username Anda",
                    autocomplete: "username",
                    class: "w-full"
                  }, null, 8, ["modelValue", "onUpdate:modelValue"])
                ]),
                _: 1
              }, 8, ["error"]),
              createVNode(_component_UFormField, {
                label: "Nomor WhatsApp",
                name: "telp",
                required: "",
                error: unref(form).errors.telp
              }, {
                default: withCtx(() => [
                  createVNode(_component_UInput, {
                    modelValue: unref(form).telp,
                    "onUpdate:modelValue": ($event) => unref(form).telp = $event,
                    type: "tel",
                    placeholder: "Contoh: 08123456789",
                    autocomplete: "tel",
                    class: "w-full"
                  }, null, 8, ["modelValue", "onUpdate:modelValue"])
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
                "leading-icon": "i-lucide-message-circle"
              }, {
                default: withCtx(() => [
                  createTextVNode(toDisplayString(unref(form).processing ? "Memproses…" : "Kirim via WhatsApp"), 1)
                ]),
                _: 1
              }, 8, ["loading", "disabled"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="mt-6 text-center">`);
      _push(ssrRenderComponent(unref(Link), {
        href: "/login",
        class: "text-sm text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Kembali ke halaman masuk `);
          } else {
            return [
              createTextVNode(" Kembali ke halaman masuk ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></div></div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/ForgotPassword.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
