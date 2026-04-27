import { defineComponent, mergeProps, useSSRContext, ref, withCtx, createVNode, createTextVNode, toDisplayString, unref, openBlock, createBlock, createCommentVNode, computed, watch } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrInterpolate } from "vue/server-renderer";
import { a as _sfc_main$b } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$3 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$a } from "./Checkbox-PZ4-MUog.js";
import { _ as _sfc_main$9 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$8 } from "./Select-Dl3Zaa-r.js";
import { _ as _sfc_main$7 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$6 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$5 } from "./Form-Cnb1D1Z4.js";
import { _ as _sfc_main$4 } from "./Alert-BRMLOsUb.js";
import { Link, useForm } from "@inertiajs/vue3";
import { _ as _sfc_main$c } from "./SeoHead-CrFJik-H.js";
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
  __name: "RegisterInfoPanel",
  __ssrInlineRender: true,
  setup(__props) {
    const benefits = [
      {
        icon: "i-lucide-badge-check",
        title: "Akses khusus member",
        description: "Katalog & fitur yang dirancang untuk pertumbuhan Anda."
      },
      {
        icon: "i-lucide-users",
        title: "Komunitas & dukungan",
        description: "Lebih mudah mulai karena ada arahan dan support."
      },
      {
        icon: "i-lucide-shield-check",
        title: "Data rapi & terorganisir",
        description: "Kelola akun Anda dengan lebih nyaman dan aman."
      }
    ];
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$3;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "relative hidden overflow-hidden bg-linear-to-br from-primary via-primary-800 to-primary-500 lg:flex lg:flex-col lg:justify-between lg:p-12" }, _attrs))}><div class="pointer-events-none absolute inset-0"><div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/5"></div><div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-white/5"></div><div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/3"></div></div><div class="relative flex items-center gap-2">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-sparkles",
        class: "size-6 text-white/80"
      }, null, _parent));
      _push(`<span class="text-xl font-black tracking-tight text-white">ogitu.id</span></div><div class="relative space-y-8"><div class="space-y-4"><div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white/90 backdrop-blur"> Gratis daftar • Mulai lebih cepat </div><h1 class="text-4xl font-black leading-tight tracking-tight text-white"> Jadi Member <br> ogitu.id hari ini. </h1><p class="text-lg text-white/75"> Dapatkan akses member untuk memulai, lebih mudah, lebih rapi, dan siap dikembangkan. </p></div><div class="space-y-3"><!--[-->`);
      ssrRenderList(benefits, (benefit) => {
        _push(`<div class="flex items-start gap-3 rounded-2xl bg-white/10 p-4 backdrop-blur-sm">`);
        _push(ssrRenderComponent(_component_UIcon, {
          name: benefit.icon,
          class: "mt-0.5 size-5 shrink-0 text-white"
        }, null, _parent));
        _push(`<div><div class="font-semibold text-white">${ssrInterpolate(benefit.title)}</div><div class="text-sm text-white/70">${ssrInterpolate(benefit.description)}</div></div></div>`);
      });
      _push(`<!--]--></div></div><div class="relative text-sm text-white/50"> © ${ssrInterpolate((/* @__PURE__ */ new Date()).getFullYear())} ogitu.id. Semua hak dilindungi. </div></div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/auth/RegisterInfoPanel.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "RegisterFormPanel",
  __ssrInlineRender: true,
  props: {
    form: {},
    validate: { type: Function },
    firstError: {},
    isReferralReadonly: { type: Boolean },
    debugMode: { type: Boolean },
    onAutofillDebugForm: { type: Function },
    onResetRegisterForm: { type: Function }
  },
  emits: ["submit"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const genderOptions = [
      { label: "Laki-laki", value: "L" },
      { label: "Perempuan", value: "P" }
    ];
    const showPassword = ref(false);
    const showPasswordConfirmation = ref(false);
    function handleAutofillDebugForm() {
      props.onAutofillDebugForm();
    }
    function handleResetRegisterForm() {
      props.onResetRegisterForm();
      showPassword.value = false;
      showPasswordConfirmation.value = false;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$3;
      const _component_UAlert = _sfc_main$4;
      const _component_UForm = _sfc_main$5;
      const _component_UFormField = _sfc_main$6;
      const _component_UInput = _sfc_main$7;
      const _component_USelect = _sfc_main$8;
      const _component_UButton = _sfc_main$9;
      const _component_UCheckbox = _sfc_main$a;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex min-h-dvh flex-col items-center justify-center bg-white px-6 py-12 dark:bg-slate-950 lg:px-12" }, _attrs))}><div class="mb-8 flex items-center gap-2 lg:hidden">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-sparkles",
        class: "size-5 text-primary-500"
      }, null, _parent));
      _push(`<span class="text-lg font-black tracking-tight text-slate-900 dark:text-white">ogitu.id</span></div><div class="w-full max-w-2xl"><div class="mb-8"><div class="mb-2 flex items-center gap-2">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-user-plus",
        class: "size-6 text-primary-500"
      }, null, _parent));
      _push(`<h1 class="text-2xl font-bold text-slate-900 dark:text-white">Pendaftaran Member</h1></div><p class="text-sm text-slate-500 dark:text-slate-400">Isi data berikut untuk membuat akun baru.</p></div>`);
      if (__props.firstError) {
        _push(ssrRenderComponent(_component_UAlert, {
          class: "mb-6",
          color: "error",
          variant: "soft",
          icon: "i-lucide-alert-triangle",
          title: "Pendaftaran gagal",
          description: String(__props.firstError)
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(ssrRenderComponent(_component_UForm, {
        state: props.form,
        validate: props.validate,
        onSubmit: ($event) => emit("submit"),
        class: "space-y-6"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}><p class="text-xs font-semibold uppercase tracking-widest text-slate-400"${_scopeId}>Data Diri</p><div class="grid grid-cols-1 gap-4 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Nama Lengkap",
              name: "name",
              required: "",
              error: props.form.errors.name
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.name,
                    "onUpdate:modelValue": ($event) => props.form.name = $event,
                    placeholder: "Nama sesuai KTP",
                    autocomplete: "name",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: props.form.name,
                      "onUpdate:modelValue": ($event) => props.form.name = $event,
                      placeholder: "Nama sesuai KTP",
                      autocomplete: "name",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
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
                    placeholder: "Contoh: ogitu.id_partner",
                    autocomplete: "username",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: props.form.username,
                      "onUpdate:modelValue": ($event) => props.form.username = $event,
                      placeholder: "Contoh: ogitu.id_partner",
                      autocomplete: "username",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="grid grid-cols-1 gap-4 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Email",
              name: "email",
              required: "",
              error: props.form.errors.email
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.email,
                    "onUpdate:modelValue": ($event) => props.form.email = $event,
                    type: "email",
                    placeholder: "email@contoh.com",
                    autocomplete: "email",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: props.form.email,
                      "onUpdate:modelValue": ($event) => props.form.email = $event,
                      type: "email",
                      placeholder: "email@contoh.com",
                      autocomplete: "email",
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
              error: props.form.errors.telp
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.telp,
                    "onUpdate:modelValue": ($event) => props.form.telp = $event,
                    type: "tel",
                    placeholder: "08xxxxxxxxxx",
                    autocomplete: "tel",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: props.form.telp,
                      "onUpdate:modelValue": ($event) => props.form.telp = $event,
                      type: "tel",
                      placeholder: "08xxxxxxxxxx",
                      autocomplete: "tel",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="grid grid-cols-1 gap-4 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "NIK (opsional)",
              name: "nik",
              error: props.form.errors.nik
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.nik,
                    "onUpdate:modelValue": ($event) => props.form.nik = $event,
                    placeholder: "16 digit",
                    autocomplete: "off",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: props.form.nik,
                      "onUpdate:modelValue": ($event) => props.form.nik = $event,
                      placeholder: "16 digit",
                      autocomplete: "off",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Jenis Kelamin",
              name: "gender",
              required: "",
              error: props.form.errors.gender
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelect, {
                    modelValue: props.form.gender,
                    "onUpdate:modelValue": ($event) => props.form.gender = $event,
                    items: genderOptions,
                    placeholder: "Pilih jenis kelamin",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelect, {
                      modelValue: props.form.gender,
                      "onUpdate:modelValue": ($event) => props.form.gender = $event,
                      items: genderOptions,
                      placeholder: "Pilih jenis kelamin",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="grid grid-cols-1 gap-4 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Alamat (opsional)",
              name: "alamat",
              error: props.form.errors.alamat
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.alamat,
                    "onUpdate:modelValue": ($event) => props.form.alamat = $event,
                    placeholder: "Alamat lengkap untuk pengiriman",
                    autocomplete: "street-address",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: props.form.alamat,
                      "onUpdate:modelValue": ($event) => props.form.alamat = $event,
                      placeholder: "Alamat lengkap untuk pengiriman",
                      autocomplete: "street-address",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Username Referral (opsional)",
              name: "referral_username",
              error: props.form.errors.referral_username ?? props.form.errors.referral_code
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.referral_username,
                    "onUpdate:modelValue": ($event) => props.form.referral_username = $event,
                    placeholder: props.isReferralReadonly ? "Username referral dari link tidak bisa diubah" : "Jika ada, masukkan username sponsor di sini",
                    autocomplete: "off",
                    autocapitalize: "none",
                    spellcheck: "false",
                    readonly: props.isReferralReadonly,
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: props.form.referral_username,
                      "onUpdate:modelValue": ($event) => props.form.referral_username = $event,
                      placeholder: props.isReferralReadonly ? "Username referral dari link tidak bisa diubah" : "Jika ada, masukkan username sponsor di sini",
                      autocomplete: "off",
                      autocapitalize: "none",
                      spellcheck: "false",
                      readonly: props.isReferralReadonly,
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder", "readonly"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div><div class="space-y-4"${_scopeId}><p class="text-xs font-semibold uppercase tracking-widest text-slate-400"${_scopeId}>Keamanan Akun</p><div class="grid grid-cols-1 gap-4 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Kata Sandi",
              name: "password",
              required: "",
              error: props.form.errors.password
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.password,
                    "onUpdate:modelValue": ($event) => props.form.password = $event,
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
                      modelValue: props.form.password,
                      "onUpdate:modelValue": ($event) => props.form.password = $event,
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
              error: props.form.errors.password_confirmation
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: props.form.password_confirmation,
                    "onUpdate:modelValue": ($event) => props.form.password_confirmation = $event,
                    id: "password_confirmation",
                    type: showPasswordConfirmation.value ? "text" : "password",
                    placeholder: "Ulangi kata sandi",
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
                          "aria-label": showPasswordConfirmation.value ? "Sembunyikan konfirmasi kata sandi" : "Tampilkan konfirmasi kata sandi",
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
                            "aria-label": showPasswordConfirmation.value ? "Sembunyikan konfirmasi kata sandi" : "Tampilkan konfirmasi kata sandi",
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
                      modelValue: props.form.password_confirmation,
                      "onUpdate:modelValue": ($event) => props.form.password_confirmation = $event,
                      id: "password_confirmation",
                      type: showPasswordConfirmation.value ? "text" : "password",
                      placeholder: "Ulangi kata sandi",
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
                          "aria-label": showPasswordConfirmation.value ? "Sembunyikan konfirmasi kata sandi" : "Tampilkan konfirmasi kata sandi",
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
            _push2(`</div></div><div class="space-y-4"${_scopeId}>`);
            if (props.debugMode) {
              _push2(`<div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/40"${_scopeId}><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><div${_scopeId}><p class="text-sm font-semibold text-amber-900 dark:text-amber-100"${_scopeId}>Debug Tools</p><p class="text-xs text-amber-700 dark:text-amber-300"${_scopeId}> Autofill mengisi data contoh. Reset mengembalikan form ke nilai awal. </p></div><div class="flex flex-col gap-2 sm:flex-row"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UButton, {
                type: "button",
                color: "warning",
                variant: "soft",
                icon: "i-lucide-wand-sparkles",
                disabled: props.form.processing,
                onClick: handleAutofillDebugForm
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Autofill Debug `);
                  } else {
                    return [
                      createTextVNode(" Autofill Debug ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UButton, {
                type: "button",
                color: "neutral",
                variant: "outline",
                icon: "i-lucide-rotate-ccw",
                disabled: props.form.processing,
                onClick: handleResetRegisterForm
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Reset Form `);
                  } else {
                    return [
                      createTextVNode(" Reset Form ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div></div></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(ssrRenderComponent(_component_UFormField, {
              name: "terms",
              error: props.form.errors.terms
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UCheckbox, {
                    modelValue: props.form.terms,
                    "onUpdate:modelValue": ($event) => props.form.terms = $event,
                    label: "Saya setuju dengan Syarat & Ketentuan",
                    description: props.form.errors.terms ? void 0 : "Wajib dicentang untuk melanjutkan."
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UCheckbox, {
                      modelValue: props.form.terms,
                      "onUpdate:modelValue": ($event) => props.form.terms = $event,
                      label: "Saya setuju dengan Syarat & Ketentuan",
                      description: props.form.errors.terms ? void 0 : "Wajib dicentang untuk melanjutkan."
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "description"])
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
              "leading-icon": "i-lucide-user-plus"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(props.form.processing ? "Sedang membuat akun…" : "Daftar Sekarang")}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(props.form.processing ? "Sedang membuat akun…" : "Daftar Sekarang"), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div><div class="space-y-3 text-center"${_scopeId}><p class="text-xs text-slate-500"${_scopeId}> Dengan mendaftar, Anda menyetujui <a href="#" class="font-semibold text-primary-600 hover:text-primary-500"${_scopeId}>Syarat &amp; Ketentuan</a>. </p><div class="text-sm text-slate-500"${_scopeId}> Sudah punya akun? `);
            _push2(ssrRenderComponent(unref(Link), {
              href: "/login",
              class: "font-bold text-primary-600 hover:text-primary-500"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`Masuk`);
                } else {
                  return [
                    createTextVNode("Masuk")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode("p", { class: "text-xs font-semibold uppercase tracking-widest text-slate-400" }, "Data Diri"),
                createVNode("div", { class: "grid grid-cols-1 gap-4 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Nama Lengkap",
                    name: "name",
                    required: "",
                    error: props.form.errors.name
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: props.form.name,
                        "onUpdate:modelValue": ($event) => props.form.name = $event,
                        placeholder: "Nama sesuai KTP",
                        autocomplete: "name",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
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
                        placeholder: "Contoh: ogitu.id_partner",
                        autocomplete: "username",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode("div", { class: "grid grid-cols-1 gap-4 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Email",
                    name: "email",
                    required: "",
                    error: props.form.errors.email
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: props.form.email,
                        "onUpdate:modelValue": ($event) => props.form.email = $event,
                        type: "email",
                        placeholder: "email@contoh.com",
                        autocomplete: "email",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Nomor WhatsApp",
                    name: "telp",
                    required: "",
                    error: props.form.errors.telp
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: props.form.telp,
                        "onUpdate:modelValue": ($event) => props.form.telp = $event,
                        type: "tel",
                        placeholder: "08xxxxxxxxxx",
                        autocomplete: "tel",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode("div", { class: "grid grid-cols-1 gap-4 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "NIK (opsional)",
                    name: "nik",
                    error: props.form.errors.nik
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: props.form.nik,
                        "onUpdate:modelValue": ($event) => props.form.nik = $event,
                        placeholder: "16 digit",
                        autocomplete: "off",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Jenis Kelamin",
                    name: "gender",
                    required: "",
                    error: props.form.errors.gender
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelect, {
                        modelValue: props.form.gender,
                        "onUpdate:modelValue": ($event) => props.form.gender = $event,
                        items: genderOptions,
                        placeholder: "Pilih jenis kelamin",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode("div", { class: "grid grid-cols-1 gap-4 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Alamat (opsional)",
                    name: "alamat",
                    error: props.form.errors.alamat
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: props.form.alamat,
                        "onUpdate:modelValue": ($event) => props.form.alamat = $event,
                        placeholder: "Alamat lengkap untuk pengiriman",
                        autocomplete: "street-address",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Username Referral (opsional)",
                    name: "referral_username",
                    error: props.form.errors.referral_username ?? props.form.errors.referral_code
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: props.form.referral_username,
                        "onUpdate:modelValue": ($event) => props.form.referral_username = $event,
                        placeholder: props.isReferralReadonly ? "Username referral dari link tidak bisa diubah" : "Jika ada, masukkan username sponsor di sini",
                        autocomplete: "off",
                        autocapitalize: "none",
                        spellcheck: "false",
                        readonly: props.isReferralReadonly,
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder", "readonly"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ])
              ]),
              createVNode("div", { class: "space-y-4" }, [
                createVNode("p", { class: "text-xs font-semibold uppercase tracking-widest text-slate-400" }, "Keamanan Akun"),
                createVNode("div", { class: "grid grid-cols-1 gap-4 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Kata Sandi",
                    name: "password",
                    required: "",
                    error: props.form.errors.password
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: props.form.password,
                        "onUpdate:modelValue": ($event) => props.form.password = $event,
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
                    error: props.form.errors.password_confirmation
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: props.form.password_confirmation,
                        "onUpdate:modelValue": ($event) => props.form.password_confirmation = $event,
                        id: "password_confirmation",
                        type: showPasswordConfirmation.value ? "text" : "password",
                        placeholder: "Ulangi kata sandi",
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
                            "aria-label": showPasswordConfirmation.value ? "Sembunyikan konfirmasi kata sandi" : "Tampilkan konfirmasi kata sandi",
                            "aria-pressed": showPasswordConfirmation.value,
                            "aria-controls": "password_confirmation",
                            onClick: ($event) => showPasswordConfirmation.value = !showPasswordConfirmation.value
                          }, null, 8, ["icon", "aria-label", "aria-pressed", "onClick"])
                        ]),
                        _: 1
                      }, 8, ["modelValue", "onUpdate:modelValue", "type"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ])
              ]),
              createVNode("div", { class: "space-y-4" }, [
                props.debugMode ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/40"
                }, [
                  createVNode("div", { class: "flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" }, [
                    createVNode("div", null, [
                      createVNode("p", { class: "text-sm font-semibold text-amber-900 dark:text-amber-100" }, "Debug Tools"),
                      createVNode("p", { class: "text-xs text-amber-700 dark:text-amber-300" }, " Autofill mengisi data contoh. Reset mengembalikan form ke nilai awal. ")
                    ]),
                    createVNode("div", { class: "flex flex-col gap-2 sm:flex-row" }, [
                      createVNode(_component_UButton, {
                        type: "button",
                        color: "warning",
                        variant: "soft",
                        icon: "i-lucide-wand-sparkles",
                        disabled: props.form.processing,
                        onClick: handleAutofillDebugForm
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" Autofill Debug ")
                        ]),
                        _: 1
                      }, 8, ["disabled"]),
                      createVNode(_component_UButton, {
                        type: "button",
                        color: "neutral",
                        variant: "outline",
                        icon: "i-lucide-rotate-ccw",
                        disabled: props.form.processing,
                        onClick: handleResetRegisterForm
                      }, {
                        default: withCtx(() => [
                          createTextVNode(" Reset Form ")
                        ]),
                        _: 1
                      }, 8, ["disabled"])
                    ])
                  ])
                ])) : createCommentVNode("", true),
                createVNode(_component_UFormField, {
                  name: "terms",
                  error: props.form.errors.terms
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UCheckbox, {
                      modelValue: props.form.terms,
                      "onUpdate:modelValue": ($event) => props.form.terms = $event,
                      label: "Saya setuju dengan Syarat & Ketentuan",
                      description: props.form.errors.terms ? void 0 : "Wajib dicentang untuk melanjutkan."
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "description"])
                  ]),
                  _: 1
                }, 8, ["error"]),
                createVNode(_component_UButton, {
                  type: "submit",
                  block: "",
                  size: "lg",
                  color: "primary",
                  loading: props.form.processing,
                  disabled: props.form.processing,
                  "leading-icon": "i-lucide-user-plus"
                }, {
                  default: withCtx(() => [
                    createTextVNode(toDisplayString(props.form.processing ? "Sedang membuat akun…" : "Daftar Sekarang"), 1)
                  ]),
                  _: 1
                }, 8, ["loading", "disabled"])
              ]),
              createVNode("div", { class: "space-y-3 text-center" }, [
                createVNode("p", { class: "text-xs text-slate-500" }, [
                  createTextVNode(" Dengan mendaftar, Anda menyetujui "),
                  createVNode("a", {
                    href: "#",
                    class: "font-semibold text-primary-600 hover:text-primary-500"
                  }, "Syarat & Ketentuan"),
                  createTextVNode(". ")
                ]),
                createVNode("div", { class: "text-sm text-slate-500" }, [
                  createTextVNode(" Sudah punya akun? "),
                  createVNode(unref(Link), {
                    href: "/login",
                    class: "font-bold text-primary-600 hover:text-primary-500"
                  }, {
                    default: withCtx(() => [
                      createTextVNode("Masuk")
                    ]),
                    _: 1
                  })
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/auth/RegisterFormPanel.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
function useRegisterForm(referralCode, referralUsername) {
  const toast = useToast();
  const initialState = {
    name: "",
    username: "",
    email: "",
    telp: "",
    nik: "",
    gender: "",
    alamat: "",
    referral_username: referralUsername ?? "",
    referral_code: referralCode ?? "",
    password: "",
    password_confirmation: "",
    terms: false
  };
  const form = useForm({ ...initialState });
  const firstError = computed(() => Object.values(form.errors ?? {})[0]);
  const watchedFields = [
    "name",
    "username",
    "email",
    "telp",
    "gender",
    "referral_username",
    "referral_code",
    "password",
    "password_confirmation",
    "terms"
  ];
  for (const field of watchedFields) {
    watch(() => form[field], () => form.clearErrors(field));
  }
  function validate(state) {
    const errors = [];
    const email = String(state.email ?? "").trim();
    const telp = String(state.telp ?? "").replace(/\s+/g, "");
    const username = String(state.username ?? "").trim();
    const referralUsername2 = String(state.referral_username ?? "").trim().replace(/^@+/, "");
    const password = String(state.password ?? "");
    const passwordConfirmation = String(state.password_confirmation ?? "");
    if (!String(state.name ?? "").trim()) {
      errors.push({ name: "name", message: "Nama wajib diisi." });
    }
    if (!username) {
      errors.push({ name: "username", message: "Username wajib diisi." });
    } else if (!/^[a-zA-Z0-9_.]{3,30}$/.test(username)) {
      errors.push({
        name: "username",
        message: "Username minimal 3 karakter (huruf/angka/underscore/titik)."
      });
    }
    if (!email) {
      errors.push({ name: "email", message: "Email wajib diisi." });
    } else if (!/^\S+@\S+\.\S+$/.test(email)) {
      errors.push({ name: "email", message: "Format email tidak valid." });
    }
    if (!telp) {
      errors.push({ name: "telp", message: "Nomor WhatsApp wajib diisi." });
    } else if (!/^[0-9+]{8,16}$/.test(telp)) {
      errors.push({ name: "telp", message: "Nomor WhatsApp tidak valid." });
    }
    if (state.gender !== "L" && state.gender !== "P") {
      errors.push({ name: "gender", message: "Silakan pilih jenis kelamin." });
    }
    if (referralUsername2 && !/^[a-zA-Z0-9_.]{3,30}$/.test(referralUsername2)) {
      errors.push({
        name: "referral_username",
        message: "Username referral tidak valid."
      });
    }
    if (!password || password.length < 8) {
      errors.push({ name: "password", message: "Kata sandi minimal 8 karakter." });
    }
    if (password !== passwordConfirmation) {
      errors.push({ name: "password_confirmation", message: "Konfirmasi kata sandi tidak cocok." });
    }
    if (!state.terms) {
      errors.push({ name: "terms", message: "Anda harus menyetujui Syarat & Ketentuan." });
    }
    return errors;
  }
  function onSubmit() {
    form.clearErrors();
    form.transform((data) => ({
      ...data,
      username: data.username.trim().toLowerCase(),
      email: data.email.trim().toLowerCase(),
      telp: data.telp.replace(/\s+/g, ""),
      referral_username: data.referral_username.trim().replace(/^@+/, "").toLowerCase()
    })).post("/register", {
      preserveScroll: true,
      onError: () => {
        toast.add({
          title: "Pendaftaran gagal",
          description: String(firstError.value ?? "Periksa kembali data yang Anda isi."),
          color: "error"
        });
      },
      onSuccess: () => {
        toast.add({
          title: "Berhasil!",
          description: "Akun Anda berhasil dibuat. Selamat bergabung!",
          color: "success"
        });
      },
      onFinish: () => form.reset("password", "password_confirmation")
    });
  }
  function autofillDebugForm() {
    const timestampSeed = Date.now().toString().slice(-6);
    form.clearErrors();
    form.name = `Debug Member ${timestampSeed}`;
    form.username = `debug.member.${timestampSeed}`;
    form.email = `debug.member.${timestampSeed}@example.test`;
    form.telp = `08123${timestampSeed}`;
    form.nik = "3276010101010001";
    form.gender = "L";
    form.alamat = "Jl. Debug Mode No. 1, Jakarta";
    form.password = "secret12345";
    form.password_confirmation = "secret12345";
    form.terms = true;
  }
  function resetRegisterForm() {
    form.clearErrors();
    form.reset();
  }
  return {
    form,
    firstError,
    validate,
    onSubmit,
    autofillDebugForm,
    resetRegisterForm
  };
}
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$b },
  __name: "Register",
  __ssrInlineRender: true,
  props: {
    seo: {},
    referralCode: {},
    referralUsername: {},
    debugMode: { type: Boolean }
  },
  setup(__props) {
    const props = __props;
    const { form, validate, firstError, onSubmit, autofillDebugForm, resetRegisterForm } = useRegisterForm(
      props.referralCode,
      props.referralUsername
    );
    const isReferralReadonly = Boolean(props.referralUsername ?? props.referralCode);
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_sfc_main$c, {
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
        "is-referral-readonly": unref(isReferralReadonly),
        "debug-mode": props.debugMode,
        "on-autofill-debug-form": unref(autofillDebugForm),
        "on-reset-register-form": unref(resetRegisterForm),
        onSubmit: unref(onSubmit)
      }, null, _parent));
      _push(`</div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Register.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
