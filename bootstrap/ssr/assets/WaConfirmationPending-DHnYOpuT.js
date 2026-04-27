import { _ as _sfc_main$5 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$4 } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$3 } from "./Alert-BRMLOsUb.js";
import { _ as _sfc_main$2 } from "./Icon-Chcm7u9q.js";
import { defineComponent, mergeProps, withCtx, createVNode, createTextVNode, unref, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate } from "vue/server-renderer";
import { a as _sfc_main$1 } from "./AppLayout-BFrtNV54.js";
import { Link } from "@inertiajs/vue3";
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
import "./Input-DFvIE7JC.js";
import "./useStoreData-DrTMI0On.js";
import "./Separator-CuyFd3xv.js";
import "reka-ui/namespaced";
import "./Checkbox-PZ4-MUog.js";
import "./Badge-DqskWDDq.js";
import "vaul-vue";
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$1 },
  __name: "WaConfirmationPending",
  __ssrInlineRender: true,
  props: {
    username: {},
    maskedPhone: {},
    waUrl: {},
    confirmUrl: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$2;
      const _component_UAlert = _sfc_main$3;
      const _component_UCard = _sfc_main$4;
      const _component_UButton = _sfc_main$5;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex min-h-dvh items-center justify-center bg-gray-50 px-4 py-16 dark:bg-gray-950" }, _attrs))}><div class="w-full max-w-md space-y-6"><div class="flex flex-col items-center gap-3 text-center"><div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-message-circle-warning",
        class: "h-8 w-8 text-amber-600 dark:text-amber-400"
      }, null, _parent));
      _push(`</div><div><h1 class="text-xl font-bold text-gray-900 dark:text-white"> Konfirmasi WhatsApp Diperlukan </h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400"> Halo <span class="font-semibold text-gray-700 dark:text-gray-300">${ssrInterpolate(__props.username)}</span></p></div></div>`);
      _push(ssrRenderComponent(_component_UAlert, {
        color: "warning",
        variant: "soft",
        icon: "i-lucide-triangle-alert",
        title: "Nomor WhatsApp belum terdeteksi",
        description: `Nomor ${__props.maskedPhone} belum terdeteksi mengirim pesan ke gateway kami. Ikuti langkah di bawah untuk menyelesaikan konfirmasi.`
      }, null, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}><p class="text-sm font-semibold text-gray-700 dark:text-gray-300"${_scopeId}> Cara konfirmasi nomor WhatsApp: </p><ol class="space-y-3"${_scopeId}><li class="flex gap-3"${_scopeId}><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300"${_scopeId}> 1 </span><span class="text-sm text-gray-600 dark:text-gray-400"${_scopeId}> Klik tombol <span class="font-semibold text-gray-800 dark:text-gray-200"${_scopeId}>&quot;Buka WhatsApp&quot;</span> di bawah. Pesan sudah terisi otomatis — jangan ubah isinya. </span></li><li class="flex gap-3"${_scopeId}><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300"${_scopeId}> 2 </span><span class="text-sm text-gray-600 dark:text-gray-400"${_scopeId}><span class="font-semibold text-gray-800 dark:text-gray-200"${_scopeId}>Kirim pesan</span> tersebut ke nomor gateway kami. Langkah ini wajib agar sistem mengenali nomor Anda. </span></li><li class="flex gap-3"${_scopeId}><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300"${_scopeId}> 3 </span><span class="text-sm text-gray-600 dark:text-gray-400"${_scopeId}> Setelah pesan terkirim, <span class="font-semibold text-gray-800 dark:text-gray-200"${_scopeId}>klik link konfirmasi</span> yang ada di dalam pesan tersebut untuk menyelesaikan proses. </span></li></ol></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode("p", { class: "text-sm font-semibold text-gray-700 dark:text-gray-300" }, " Cara konfirmasi nomor WhatsApp: "),
                createVNode("ol", { class: "space-y-3" }, [
                  createVNode("li", { class: "flex gap-3" }, [
                    createVNode("span", { class: "flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300" }, " 1 "),
                    createVNode("span", { class: "text-sm text-gray-600 dark:text-gray-400" }, [
                      createTextVNode(" Klik tombol "),
                      createVNode("span", { class: "font-semibold text-gray-800 dark:text-gray-200" }, '"Buka WhatsApp"'),
                      createTextVNode(" di bawah. Pesan sudah terisi otomatis — jangan ubah isinya. ")
                    ])
                  ]),
                  createVNode("li", { class: "flex gap-3" }, [
                    createVNode("span", { class: "flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300" }, " 2 "),
                    createVNode("span", { class: "text-sm text-gray-600 dark:text-gray-400" }, [
                      createVNode("span", { class: "font-semibold text-gray-800 dark:text-gray-200" }, "Kirim pesan"),
                      createTextVNode(" tersebut ke nomor gateway kami. Langkah ini wajib agar sistem mengenali nomor Anda. ")
                    ])
                  ]),
                  createVNode("li", { class: "flex gap-3" }, [
                    createVNode("span", { class: "flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300" }, " 3 "),
                    createVNode("span", { class: "text-sm text-gray-600 dark:text-gray-400" }, [
                      createTextVNode(" Setelah pesan terkirim, "),
                      createVNode("span", { class: "font-semibold text-gray-800 dark:text-gray-200" }, "klik link konfirmasi"),
                      createTextVNode(" yang ada di dalam pesan tersebut untuk menyelesaikan proses. ")
                    ])
                  ])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="space-y-3">`);
      if (__props.waUrl) {
        _push(ssrRenderComponent(_component_UButton, {
          to: __props.waUrl,
          target: "_blank",
          color: "success",
          size: "lg",
          icon: "i-lucide-message-circle",
          class: "w-full justify-center"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` Buka WhatsApp &amp; Kirim Pesan `);
            } else {
              return [
                createTextVNode(" Buka WhatsApp & Kirim Pesan ")
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(ssrRenderComponent(_component_UAlert, {
          color: "error",
          variant: "soft",
          icon: "i-lucide-wifi-off",
          title: "Gateway WhatsApp belum dikonfigurasi",
          description: "Hubungi admin untuk bantuan konfirmasi manual."
        }, null, _parent));
      }
      _push(`<div class="flex items-center justify-center gap-1 pt-1 text-sm text-gray-500 dark:text-gray-400">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-info",
        class: "h-4 w-4 shrink-0"
      }, null, _parent));
      _push(`<span>Setelah kirim pesan, klik link di dalam pesan WA Anda.</span></div></div>`);
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-2xl ring-1 ring-amber-200 bg-amber-50/50 dark:ring-amber-800/40 dark:bg-amber-950/20" }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex gap-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-headset",
              class: "h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5"
            }, null, _parent2, _scopeId));
            _push2(`<div class="space-y-1"${_scopeId}><p class="text-sm font-semibold text-amber-800 dark:text-amber-200"${_scopeId}> Sudah kirim pesan tapi masih tidak terdeteksi? </p><p class="text-xs text-amber-700 dark:text-amber-300"${_scopeId}> Hubungi admin untuk konfirmasi manual. Admin dapat mengaktifkan akun Anda langsung melalui panel kontrol. </p></div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex gap-3" }, [
                createVNode(_component_UIcon, {
                  name: "i-lucide-headset",
                  class: "h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5"
                }),
                createVNode("div", { class: "space-y-1" }, [
                  createVNode("p", { class: "text-sm font-semibold text-amber-800 dark:text-amber-200" }, " Sudah kirim pesan tapi masih tidak terdeteksi? "),
                  createVNode("p", { class: "text-xs text-amber-700 dark:text-amber-300" }, " Hubungi admin untuk konfirmasi manual. Admin dapat mengaktifkan akun Anda langsung melalui panel kontrol. ")
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="text-center">`);
      _push(ssrRenderComponent(unref(Link), {
        href: "/login",
        class: "text-sm text-primary-600 hover:text-primary-700 hover:underline dark:text-primary-400"
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
      _push(`</div></div></div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/WaConfirmationPending.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
