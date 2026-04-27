import { defineComponent, mergeProps, useSSRContext, ref, withCtx, createVNode, unref, createTextVNode, toDisplayString, computed, watch } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrInterpolate } from "vue/server-renderer";
import { a as _sfc_main$a } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$3 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$9 } from "./Checkbox-PZ4-MUog.js";
import { _ as _sfc_main$8 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$7 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$6 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$5 } from "./Form-Cnb1D1Z4.js";
import { _ as _sfc_main$4 } from "./Alert-BRMLOsUb.js";
import { Link, usePage, useForm } from "@inertiajs/vue3";
import { _ as _sfc_main$b } from "./SeoHead-CrFJik-H.js";
import { u as useToast } from "./useToast-CTuSIf9f.js";
import "reka-ui";
import "@vueuse/core";
import "./usePortal-EQErrF6h.js";
import "./useStoreData-DrTMI0On.js";
import "./Separator-CuyFd3xv.js";
import "defu";
import "reka-ui/namespaced";
import "./Badge-DqskWDDq.js";
import "vaul-vue";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "ufo";
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "LoginInfoPanel",
  __ssrInlineRender: true,
  setup(__props) {
    const benefits = [
      {
        icon: "i-lucide-wallet",
        title: "E-Wallet & Bonus Otomatis",
        desc: "Saldo dan bonus langsung masuk ke e-wallet Anda secara real-time."
      },
      {
        icon: "i-lucide-users",
        title: "Jaringan Affiliasi Terstruktur",
        desc: "Kembangkan jaringan binary tree dan raih bonus pairing tanpa batas."
      },
      {
        icon: "i-lucide-tag",
        title: "Harga Eksklusif Member",
        desc: "Dapatkan diskon dan akses ke harga khusus untuk seluruh katalog produk."
      }
    ];
    const stats = [
      { value: "50K+", label: "Member Aktif" },
      { value: "200+", label: "Produk Unggulan" },
      { value: "99%", label: "Kepuasan Pelanggan" }
    ];
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$3;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "relative hidden overflow-hidden bg-linear-to-br from-primary via-primary-800 to-primary-500 lg:flex lg:flex-col lg:justify-between lg:p-12" }, _attrs))}><div class="pointer-events-none absolute inset-0"><div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/5"></div><div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-white/5"></div><div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/3"></div></div><div class="relative flex items-center gap-2">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-sparkles",
        class: "size-6 text-white/80"
      }, null, _parent));
      _push(`<span class="text-xl font-black tracking-tight text-white">ogitu.id</span></div><div class="relative space-y-8"><div class="space-y-4"><div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white/90 backdrop-blur"> Platform Jual/Beli &amp; E-Commerce Terpercaya </div><h1 class="text-4xl font-black leading-tight tracking-tight text-white"> Selamat Datang<br>Kembali! </h1><p class="text-lg text-white/75"> Masuk dan nikmati semua keuntungan eksklusif sebagai member ogitu.id. </p></div><div class="space-y-3"><!--[-->`);
      ssrRenderList(benefits, (benefit) => {
        _push(`<div class="flex items-start gap-3 rounded-2xl bg-white/10 p-4 backdrop-blur-sm">`);
        _push(ssrRenderComponent(_component_UIcon, {
          name: benefit.icon,
          class: "mt-0.5 size-5 shrink-0 text-white"
        }, null, _parent));
        _push(`<div><div class="font-semibold text-white">${ssrInterpolate(benefit.title)}</div><div class="text-sm text-white/70">${ssrInterpolate(benefit.desc)}</div></div></div>`);
      });
      _push(`<!--]--></div><div class="grid grid-cols-3 divide-x divide-white/20 rounded-2xl bg-white/10 backdrop-blur-sm"><!--[-->`);
      ssrRenderList(stats, (stat) => {
        _push(`<div class="flex flex-col items-center px-3 py-4"><span class="text-2xl font-black text-white">${ssrInterpolate(stat.value)}</span><span class="mt-0.5 text-xs text-white/60">${ssrInterpolate(stat.label)}</span></div>`);
      });
      _push(`<!--]--></div></div><div class="relative text-sm text-white/50"> © ${ssrInterpolate((/* @__PURE__ */ new Date()).getFullYear())} ogitu.id. Semua hak dilindungi. </div></div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/auth/LoginInfoPanel.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "LoginFormPanel",
  __ssrInlineRender: true,
  props: {
    form: {},
    validate: { type: Function },
    firstError: {},
    flashStatus: {}
  },
  emits: ["submit"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const showPassword = ref(false);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$3;
      const _component_UAlert = _sfc_main$4;
      const _component_UForm = _sfc_main$5;
      const _component_UFormField = _sfc_main$6;
      const _component_UInput = _sfc_main$7;
      const _component_UButton = _sfc_main$8;
      const _component_UCheckbox = _sfc_main$9;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex min-h-dvh flex-col items-center justify-center bg-white px-6 py-12 dark:bg-slate-950 lg:px-12" }, _attrs))}><div class="mb-8 flex items-center gap-2 lg:hidden">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-sparkles",
        class: "size-5 text-primary-500"
      }, null, _parent));
      _push(`<span class="text-lg font-black tracking-tight text-slate-900 dark:text-white">ogitu.id</span></div><div class="w-full max-w-md"><div class="mb-8"><div class="mb-2 flex items-center gap-2">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-log-in",
        class: "size-6 text-primary-500"
      }, null, _parent));
      _push(`<h1 class="text-2xl font-bold text-slate-900 dark:text-white">Masuk ke Akun</h1></div><p class="text-sm text-slate-500 dark:text-slate-400">Selamat datang kembali, member ogitu.id!</p></div>`);
      if (props.flashStatus) {
        _push(ssrRenderComponent(_component_UAlert, {
          class: "mb-6",
          color: "info",
          variant: "soft",
          icon: "i-lucide-info",
          title: props.flashStatus
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      if (props.firstError) {
        _push(ssrRenderComponent(_component_UAlert, {
          class: "mb-6",
          color: "error",
          variant: "soft",
          icon: "i-lucide-alert-triangle",
          title: "Gagal masuk",
          description: String(props.firstError)
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(ssrRenderComponent(_component_UForm, {
        state: props.form,
        validate: props.validate,
        class: "space-y-5",
        onSubmit: ($event) => emit("submit")
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Username",
              name: "username",
              required: "",
              error: props.form.errors.username
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.username,
                    "onUpdate:modelValue": ($event) => props.form.username = $event,
                    placeholder: "Masukkan username Anda",
                    autocomplete: "username",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: props.form.username,
                      "onUpdate:modelValue": ($event) => props.form.username = $event,
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
              label: "Kata Sandi",
              name: "password",
              required: "",
              error: props.form.errors.password
            }, {
              hint: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(unref(Link), {
                    href: "/forgot-password",
                    class: "text-xs text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400",
                    tabindex: "-1"
                  }, {
                    default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(` Lupa kata sandi? `);
                      } else {
                        return [
                          createTextVNode(" Lupa kata sandi? ")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(unref(Link), {
                      href: "/forgot-password",
                      class: "text-xs text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400",
                      tabindex: "-1"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Lupa kata sandi? ")
                      ]),
                      _: 1
                    })
                  ];
                }
              }),
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.password,
                    "onUpdate:modelValue": ($event) => props.form.password = $event,
                    id: "password",
                    type: showPassword.value ? "text" : "password",
                    placeholder: "Masukkan kata sandi",
                    autocomplete: "current-password",
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
                      modelValue: props.form.password,
                      "onUpdate:modelValue": ($event) => props.form.password = $event,
                      id: "password",
                      type: showPassword.value ? "text" : "password",
                      placeholder: "Masukkan kata sandi",
                      autocomplete: "current-password",
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
            _push2(ssrRenderComponent(_component_UFormField, { name: "remember" }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UCheckbox, {
                    modelValue: props.form.remember,
                    "onUpdate:modelValue": ($event) => props.form.remember = $event,
                    label: "Ingat saya selama 30 hari"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UCheckbox, {
                      modelValue: props.form.remember,
                      "onUpdate:modelValue": ($event) => props.form.remember = $event,
                      label: "Ingat saya selama 30 hari"
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
              loading: props.form.processing,
              disabled: props.form.processing,
              "leading-icon": "i-lucide-log-in"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(props.form.processing ? "Memproses…" : "Masuk ke Akun")}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(props.form.processing ? "Memproses…" : "Masuk ke Akun"), 1)
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
                error: props.form.errors.username
              }, {
                default: withCtx(() => [
                  createVNode(_component_UInput, {
                    modelValue: props.form.username,
                    "onUpdate:modelValue": ($event) => props.form.username = $event,
                    placeholder: "Masukkan username Anda",
                    autocomplete: "username",
                    class: "w-full"
                  }, null, 8, ["modelValue", "onUpdate:modelValue"])
                ]),
                _: 1
              }, 8, ["error"]),
              createVNode(_component_UFormField, {
                label: "Kata Sandi",
                name: "password",
                required: "",
                error: props.form.errors.password
              }, {
                hint: withCtx(() => [
                  createVNode(unref(Link), {
                    href: "/forgot-password",
                    class: "text-xs text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400",
                    tabindex: "-1"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Lupa kata sandi? ")
                    ]),
                    _: 1
                  })
                ]),
                default: withCtx(() => [
                  createVNode(_component_UInput, {
                    modelValue: props.form.password,
                    "onUpdate:modelValue": ($event) => props.form.password = $event,
                    id: "password",
                    type: showPassword.value ? "text" : "password",
                    placeholder: "Masukkan kata sandi",
                    autocomplete: "current-password",
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
              createVNode(_component_UFormField, { name: "remember" }, {
                default: withCtx(() => [
                  createVNode(_component_UCheckbox, {
                    modelValue: props.form.remember,
                    "onUpdate:modelValue": ($event) => props.form.remember = $event,
                    label: "Ingat saya selama 30 hari"
                  }, null, 8, ["modelValue", "onUpdate:modelValue"])
                ]),
                _: 1
              }),
              createVNode(_component_UButton, {
                type: "submit",
                block: "",
                size: "lg",
                color: "primary",
                loading: props.form.processing,
                disabled: props.form.processing,
                "leading-icon": "i-lucide-log-in"
              }, {
                default: withCtx(() => [
                  createTextVNode(toDisplayString(props.form.processing ? "Memproses…" : "Masuk ke Akun"), 1)
                ]),
                _: 1
              }, 8, ["loading", "disabled"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="mt-6 space-y-4 text-center"><div class="text-sm text-slate-500"> Belum punya akun? `);
      _push(ssrRenderComponent(unref(Link), {
        href: "/register",
        class: "font-bold text-primary-600 hover:text-primary-500"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Daftar sekarang `);
          } else {
            return [
              createTextVNode(" Daftar sekarang ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="flex flex-wrap items-center justify-center gap-4 text-xs text-slate-400"><span class="flex items-center gap-1.5">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-lock",
        class: "size-3.5"
      }, null, _parent));
      _push(` SSL Terenkripsi </span><span class="flex items-center gap-1.5">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-shield",
        class: "size-3.5"
      }, null, _parent));
      _push(` Data Aman </span><span class="flex items-center gap-1.5">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-clock",
        class: "size-3.5"
      }, null, _parent));
      _push(` Akses 24/7 </span></div></div></div></div>`);
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/auth/LoginFormPanel.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
function useLoginForm() {
  const page = usePage();
  const toast = useToast();
  const form = useForm({
    username: "",
    password: "",
    remember: false
  });
  const firstError = computed(() => Object.values(form.errors ?? {})[0]);
  const flashStatus = computed(() => page.props?.status);
  watch(() => form.username, () => form.clearErrors("username"));
  watch(() => form.password, () => form.clearErrors("password"));
  function validate(state) {
    const errors = [];
    if (!state.username?.trim()) {
      errors.push({ name: "username", message: "Username wajib diisi." });
    }
    if (!state.password) {
      errors.push({ name: "password", message: "Kata sandi wajib diisi." });
    }
    return errors;
  }
  function onSubmit() {
    form.post("/login", {
      preserveScroll: true,
      onFinish: () => form.reset("password"),
      onSuccess: () => {
        toast.add({
          title: "Berhasil masuk",
          description: "Selamat datang kembali, member ogitu.id!",
          color: "success"
        });
      }
    });
  }
  return {
    form,
    firstError,
    flashStatus,
    validate,
    onSubmit
  };
}
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$a },
  __name: "Login",
  __ssrInlineRender: true,
  props: {
    seo: {}
  },
  setup(__props) {
    const { form, validate, firstError, flashStatus, onSubmit } = useLoginForm();
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_sfc_main$b, {
        title: __props.seo.title,
        description: __props.seo.description,
        canonical: __props.seo.canonical
      }, null, _parent));
      _push(`<div class="grid min-h-dvh lg:grid-cols-2">`);
      _push(ssrRenderComponent(_sfc_main$2, null, null, _parent));
      _push(ssrRenderComponent(_sfc_main$1, {
        form: unref(form),
        validate: unref(validate),
        "first-error": unref(firstError),
        "flash-status": unref(flashStatus),
        onSubmit: unref(onSubmit)
      }, null, _parent));
      _push(`</div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Login.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
