import { ref, computed, reactive, watch, defineComponent, mergeProps, withCtx, unref, createVNode, withDirectives, openBlock, createBlock, Fragment, renderList, toDisplayString, createCommentVNode, createTextVNode, useSSRContext } from "vue";
import { ssrRenderComponent, ssrGetDirectiveProps, ssrRenderList, ssrInterpolate, ssrRenderAttrs } from "vue/server-renderer";
import { _ as _sfc_main$9 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$8 } from "./Textarea-DFfHqaZw.js";
import { _ as _sfc_main$7 } from "./Separator-CuyFd3xv.js";
import { _ as _sfc_main$6 } from "./SelectMenu-D4PFVS1f.js";
import { _ as _sfc_main$5 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$4 } from "./FormField-Dpedw1-R.js";
import { _ as _sfc_main$3 } from "./Alert-BRMLOsUb.js";
import { _ as _sfc_main$a } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$2 } from "./Card-CvchAxCK.js";
import { vMaska } from "maska/vue";
import { usePage, router } from "@inertiajs/vue3";
import { u as useToast } from "./useToast-CTuSIf9f.js";
import { u as useDashboard } from "./useDashboard-DR5F4MRN.js";
import "defu";
import "reka-ui";
import "./Icon-Chcm7u9q.js";
import "hookable";
import "@vueuse/core";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "ufo";
import "./usePortal-EQErrF6h.js";
function normalizeGender(value) {
  const normalized = (value ?? "").trim().toUpperCase();
  if (["L", "LAKI-LAKI", "MALE", "M"].includes(normalized)) {
    return "L";
  }
  if (["P", "PEREMPUAN", "FEMALE", "F"].includes(normalized)) {
    return "P";
  }
  return "L";
}
function normalizeNpwpGender(value) {
  const raw = typeof value === "number" ? String(value) : (value ?? "").trim();
  if (raw === "1" || raw.toUpperCase() === "L") {
    return "1";
  }
  if (raw === "2" || raw.toUpperCase() === "P") {
    return "2";
  }
  return "";
}
function normalizeNpwpFlag(value) {
  const normalized = (value ?? "").trim().toUpperCase();
  if (normalized === "Y" || normalized === "N") {
    return normalized;
  }
  return "";
}
function toErrorMessage(value) {
  if (Array.isArray(value)) {
    return value.map((item) => String(item)).join(" ");
  }
  return value ? String(value) : "";
}
function firstErrorMessage(errors) {
  const first = Object.values(errors).find((value) => value !== void 0);
  if (Array.isArray(first)) {
    return first[0] ?? "Gagal memperbarui profil akun.";
  }
  return first ?? "Gagal memperbarui profil akun.";
}
function useDashboardAccountForm(options) {
  const toast = useToast();
  const page = usePage();
  const submitting = ref(false);
  const errors = ref({});
  const genderItems = computed(() => [
    { label: "Laki-laki", value: "L" },
    { label: "Perempuan", value: "P" }
  ]);
  const npwpGenderItems = computed(() => [
    { label: "Laki-laki", value: "1" },
    { label: "Perempuan", value: "2" }
  ]);
  const yesNoItems = computed(() => [
    { label: "Ya", value: "Y" },
    { label: "Tidak", value: "N" }
  ]);
  const form = reactive({
    username: "",
    name: "",
    nik: "",
    gender: "L",
    email: "",
    phone: "",
    bank_name: "",
    bank_account: "",
    npwp_nama: "",
    npwp_number: "",
    npwp_jk: "",
    npwp_date: "",
    npwp_alamat: "",
    npwp_menikah: "",
    npwp_anak: "",
    npwp_kerja: "",
    npwp_office: ""
  });
  watch(
    options.customer,
    (customer) => {
      form.username = customer?.username ?? "";
      form.name = customer?.name ?? "";
      form.nik = customer?.nik ?? "";
      form.gender = normalizeGender(customer?.gender);
      form.email = customer?.email ?? "";
      form.phone = customer?.phone ?? "";
      form.bank_name = customer?.bank_name ?? "";
      form.bank_account = customer?.bank_account ?? "";
      form.npwp_nama = customer?.npwp?.nama ?? "";
      form.npwp_number = customer?.npwp?.npwp ?? "";
      form.npwp_jk = normalizeNpwpGender(customer?.npwp?.jk);
      form.npwp_date = customer?.npwp?.npwp_date ?? "";
      form.npwp_alamat = customer?.npwp?.alamat ?? "";
      form.npwp_menikah = normalizeNpwpFlag(customer?.npwp?.menikah);
      form.npwp_anak = customer?.npwp?.anak ?? "";
      form.npwp_kerja = normalizeNpwpFlag(customer?.npwp?.kerja);
      form.npwp_office = customer?.npwp?.office ?? "";
    },
    { immediate: true, once: true }
  );
  function clearErrors() {
    errors.value = {};
  }
  function resetToCurrentCustomerData() {
    const customer = options.customer.value;
    form.username = customer?.username ?? "";
    form.name = customer?.name ?? "";
    form.nik = customer?.nik ?? "";
    form.gender = normalizeGender(customer?.gender);
    form.email = customer?.email ?? "";
    form.phone = customer?.phone ?? "";
    form.bank_name = customer?.bank_name ?? "";
    form.bank_account = customer?.bank_account ?? "";
    form.npwp_nama = customer?.npwp?.nama ?? "";
    form.npwp_number = customer?.npwp?.npwp ?? "";
    form.npwp_jk = normalizeNpwpGender(customer?.npwp?.jk);
    form.npwp_date = customer?.npwp?.npwp_date ?? "";
    form.npwp_alamat = customer?.npwp?.alamat ?? "";
    form.npwp_menikah = normalizeNpwpFlag(customer?.npwp?.menikah);
    form.npwp_anak = customer?.npwp?.anak ?? "";
    form.npwp_kerja = normalizeNpwpFlag(customer?.npwp?.kerja);
    form.npwp_office = customer?.npwp?.office ?? "";
    clearErrors();
  }
  function validate() {
    const validationErrors = {};
    form.username.trim();
    const name = form.name.trim();
    form.nik.replace(/\D/g, "");
    const gender = form.gender.trim().toUpperCase();
    form.email.trim();
    form.phone.replace(/\s+/g, "");
    const bankName = form.bank_name.trim();
    const bankAccount = form.bank_account.replace(/\D/g, "");
    const npwpNama = form.npwp_nama.trim();
    const npwpNumber = form.npwp_number.trim();
    const npwpJk = form.npwp_jk.trim();
    const npwpDate = form.npwp_date.trim();
    const npwpAlamat = form.npwp_alamat.trim();
    const npwpMenikah = form.npwp_menikah.trim().toUpperCase();
    const npwpAnak = form.npwp_anak.trim();
    const npwpKerja = form.npwp_kerja.trim().toUpperCase();
    const npwpOffice = form.npwp_office.trim();
    if (name.length === 0) {
      validationErrors.name = "Nama lengkap wajib diisi.";
    }
    if (!["L", "P"].includes(gender)) {
      validationErrors.gender = "Jenis kelamin wajib dipilih.";
    }
    if (bankName.length === 0) {
      validationErrors.bank_name = "Nama bank wajib diisi.";
    }
    if (!/^\d{5,50}$/.test(bankAccount)) {
      validationErrors.bank_account = "Nomor rekening harus 5-50 digit angka.";
    }
    if (npwpNumber.length > 0 && !/^[0-9.\-]{10,30}$/.test(npwpNumber)) {
      validationErrors.npwp_number = "Nomor NPWP tidak valid.";
    }
    if (npwpJk.length > 0 && !["1", "2"].includes(npwpJk)) {
      validationErrors.npwp_jk = "Jenis kelamin NPWP tidak valid.";
    }
    if (npwpDate.length > 0 && Number.isNaN(Date.parse(npwpDate))) {
      validationErrors.npwp_date = "Tanggal NPWP tidak valid.";
    }
    if (npwpMenikah.length > 0 && !["Y", "N"].includes(npwpMenikah)) {
      validationErrors.npwp_menikah = "Status pernikahan NPWP tidak valid.";
    }
    if (npwpKerja.length > 0 && !["Y", "N"].includes(npwpKerja)) {
      validationErrors.npwp_kerja = "Status kerja NPWP tidak valid.";
    }
    if (npwpAnak.length > 0 && !/^\d{1,2}$/.test(npwpAnak)) {
      validationErrors.npwp_anak = "Jumlah anak NPWP harus berupa angka.";
    }
    if (npwpNama.length > 255) {
      validationErrors.npwp_nama = "Nama NPWP maksimal 255 karakter.";
    }
    if (npwpAlamat.length > 1e3) {
      validationErrors.npwp_alamat = "Alamat NPWP maksimal 1000 karakter.";
    }
    if (npwpOffice.length > 255) {
      validationErrors.npwp_office = "Nama kantor maksimal 255 karakter.";
    }
    errors.value = validationErrors;
    return Object.keys(validationErrors).length === 0;
  }
  function normalizeErrors(serverErrors) {
    errors.value = Object.fromEntries(
      Object.entries(serverErrors ?? {}).map(([field, message]) => [field, toErrorMessage(message)]).filter((entry) => entry[1].length > 0)
    );
  }
  function submit() {
    clearErrors();
    if (!validate()) {
      return;
    }
    submitting.value = true;
    router.post("/dashboard/account/profile", {
      _token: page.props.csrf_token,
      username: form.username.trim().toLowerCase(),
      name: form.name.trim(),
      nik: form.nik.replace(/\D/g, ""),
      gender: form.gender.trim().toUpperCase(),
      email: form.email.trim().toLowerCase(),
      phone: form.phone.replace(/\s+/g, ""),
      bank_name: form.bank_name.trim(),
      bank_account: form.bank_account.replace(/\D/g, ""),
      npwp_nama: form.npwp_nama.trim(),
      npwp_number: form.npwp_number.trim(),
      npwp_jk: form.npwp_jk ? Number(form.npwp_jk) : null,
      npwp_date: form.npwp_date.trim(),
      npwp_alamat: form.npwp_alamat.trim(),
      npwp_menikah: form.npwp_menikah.trim().toUpperCase(),
      npwp_anak: form.npwp_anak.trim(),
      npwp_kerja: form.npwp_kerja.trim().toUpperCase(),
      npwp_office: form.npwp_office.trim()
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        const flashMessage = page.props.flash?.account?.message;
        const message = typeof flashMessage === "string" && flashMessage.length > 0 ? flashMessage : "Profil akun berhasil diperbarui.";
        toast.add({
          title: "Berhasil",
          description: message,
          color: "success"
        });
      },
      onError: (serverErrors) => {
        normalizeErrors(serverErrors);
        toast.add({
          title: "Gagal",
          description: firstErrorMessage(serverErrors),
          color: "error"
        });
      },
      onFinish: () => {
        submitting.value = false;
      }
    });
  }
  return {
    form,
    errors,
    submitting,
    genderItems,
    npwpGenderItems,
    yesNoItems,
    submit,
    resetToCurrentCustomerData
  };
}
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "DashboardAccountFormCard",
  __ssrInlineRender: true,
  props: {
    customer: { default: null },
    defaultAddress: { default: null }
  },
  setup(__props) {
    const props = __props;
    const customerName = computed(() => props.customer?.name?.trim() || "—");
    const { digitsOnly, extractPhoneLocalPart } = useDashboard();
    const hasCompleteDefaultAddress = computed(() => {
      const address = props.defaultAddress;
      if (!address) {
        return false;
      }
      const requiredFields = [
        address.recipient_name,
        address.phone,
        address.address_line,
        address.city,
        address.province,
        address.postal_code
      ];
      return requiredFields.every((value) => {
        const normalized = value.trim();
        return normalized.length > 0 && !["-", "—"].includes(normalized);
      });
    });
    const {
      form,
      errors,
      submitting,
      genderItems,
      npwpGenderItems,
      yesNoItems,
      submit,
      resetToCurrentCustomerData
    } = useDashboardAccountForm({
      customer: computed(() => props.customer)
    });
    const numericNik = computed(() => digitsOnly(form.nik));
    const phoneLocalPart = computed(() => extractPhoneLocalPart(form.phone));
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$2;
      const _component_UBadge = _sfc_main$a;
      const _component_UAlert = _sfc_main$3;
      const _component_UFormField = _sfc_main$4;
      const _component_UInput = _sfc_main$5;
      const _component_USelectMenu = _sfc_main$6;
      const _component_USeparator = _sfc_main$7;
      const _component_UTextarea = _sfc_main$8;
      const _component_UButton = _sfc_main$9;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800" }, _attrs), {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-center justify-between"${_scopeId}><div${_scopeId}><h3 class="text-base font-bold text-gray-900 dark:text-white"${_scopeId}>Pengaturan Akun</h3><p class="mt-1 text-xs text-gray-500 dark:text-gray-400"${_scopeId}> Pastikan data profil dan rekening sudah benar untuk kelancaran penarikan dana. </p></div>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: "primary",
              variant: "subtle",
              class: "rounded-full px-3"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Profil Publik `);
                } else {
                  return [
                    createTextVNode(" Profil Publik ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-center justify-between" }, [
                createVNode("div", null, [
                  createVNode("h3", { class: "text-base font-bold text-gray-900 dark:text-white" }, "Pengaturan Akun"),
                  createVNode("p", { class: "mt-1 text-xs text-gray-500 dark:text-gray-400" }, " Pastikan data profil dan rekening sudah benar untuk kelancaran penarikan dana. ")
                ]),
                createVNode(_component_UBadge, {
                  color: "primary",
                  variant: "subtle",
                  class: "rounded-full px-3"
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Profil Publik ")
                  ]),
                  _: 1
                })
              ])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-6"${_scopeId}>`);
            if (!hasCompleteDefaultAddress.value) {
              _push2(ssrRenderComponent(_component_UAlert, {
                color: "warning",
                variant: "subtle",
                icon: "i-lucide-map-pin-off",
                title: "Default Alamat Lengkap Belum Tersedia",
                description: "Silakan atur alamat utama Anda di menu Alamat agar data pengiriman dan dokumen akun bisa tervalidasi.",
                ui: { title: "font-bold" }
              }, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(ssrRenderComponent(_component_UAlert, {
              color: "warning",
              variant: "subtle",
              icon: "i-lucide-shield-check",
              title: "Verifikasi Rekening",
              description: `Nama pemilik rekening harus sesuai dengan nama terdaftar: ${customerName.value}. Ketidaksesuaian dapat menyebabkan pembatalan otomatis pada proses withdrawal.`,
              ui: { title: "font-bold" }
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UAlert, {
              color: "info",
              variant: "subtle",
              icon: "i-lucide-lock",
              title: "Kolom Terkunci",
              description: "Username, NIK, Email, dan No. Telepon tidak dapat diubah sendiri. Selain itu, NIK, Email, dan No. Telepon hanya boleh dipakai maksimal 7 akun. Hubungi admin jika perlu perubahan atau jika data Anda dipakai terlalu banyak akun.",
              ui: { title: "font-bold" }
            }, null, _parent2, _scopeId));
            _push2(`<div class="grid grid-cols-1 gap-5 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Username",
              required: "",
              error: unref(errors).username,
              help: "Username tidak dapat diubah. Hubungi admin untuk perubahan."
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).username,
                    "onUpdate:modelValue": ($event) => unref(form).username = $event,
                    readonly: "",
                    icon: "i-lucide-lock",
                    class: "w-full opacity-70 cursor-not-allowed"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).username,
                      "onUpdate:modelValue": ($event) => unref(form).username = $event,
                      readonly: "",
                      icon: "i-lucide-lock",
                      class: "w-full opacity-70 cursor-not-allowed"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Nama Lengkap",
              required: "",
              error: unref(errors).name,
              help: "Harus sesuai dengan nama yang tertera di KTP/Buku Tabungan."
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).name,
                    "onUpdate:modelValue": ($event) => unref(form).name = $event,
                    placeholder: "Input nama lengkap Anda",
                    icon: "i-lucide-user",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).name,
                      "onUpdate:modelValue": ($event) => unref(form).name = $event,
                      placeholder: "Input nama lengkap Anda",
                      icon: "i-lucide-user",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "NIK",
              required: "",
              error: unref(errors).nik,
              help: "NIK tidak dapat diubah dari sini. Satu NIK hanya bisa dipakai maksimal 7 akun."
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    "model-value": numericNik.value,
                    readonly: "",
                    icon: "i-lucide-lock",
                    class: "w-full opacity-70 cursor-not-allowed"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      "model-value": numericNik.value,
                      readonly: "",
                      icon: "i-lucide-lock",
                      class: "w-full opacity-70 cursor-not-allowed"
                    }, null, 8, ["model-value"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Jenis Kelamin",
              required: "",
              error: unref(errors).gender
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: unref(form).gender,
                    "onUpdate:modelValue": ($event) => unref(form).gender = $event,
                    items: unref(genderItems),
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih jenis kelamin",
                    icon: "i-lucide-venus-and-mars",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      modelValue: unref(form).gender,
                      "onUpdate:modelValue": ($event) => unref(form).gender = $event,
                      items: unref(genderItems),
                      "value-key": "value",
                      "label-key": "label",
                      placeholder: "Pilih jenis kelamin",
                      icon: "i-lucide-venus-and-mars",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Email",
              required: "",
              error: unref(errors).email,
              help: "Email tidak dapat diubah dari sini. Satu email hanya bisa dipakai maksimal 7 akun."
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).email,
                    "onUpdate:modelValue": ($event) => unref(form).email = $event,
                    type: "email",
                    icon: "i-lucide-lock",
                    class: "w-full opacity-70 cursor-not-allowed"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).email,
                      "onUpdate:modelValue": ($event) => unref(form).email = $event,
                      type: "email",
                      icon: "i-lucide-lock",
                      class: "w-full opacity-70 cursor-not-allowed"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "WhatsApp / No. Telepon",
              required: "",
              error: unref(errors).phone,
              help: "Nomor telepon tidak dapat diubah dari sini. Satu nomor HP/WhatsApp hanya bisa dipakai maksimal 7 akun."
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    "model-value": phoneLocalPart.value,
                    readonly: "",
                    inputmode: "numeric",
                    class: "w-full opacity-70 cursor-not-allowed"
                  }, {
                    leading: withCtx((_3, _push4, _parent4, _scopeId3) => {
                      if (_push4) {
                        _push4(`<span class="text-xs font-semibold text-gray-500 dark:text-gray-400"${_scopeId3}>+62</span>`);
                      } else {
                        return [
                          createVNode("span", { class: "text-xs font-semibold text-gray-500 dark:text-gray-400" }, "+62")
                        ];
                      }
                    }),
                    _: 1
                  }, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      "model-value": phoneLocalPart.value,
                      readonly: "",
                      inputmode: "numeric",
                      class: "w-full opacity-70 cursor-not-allowed"
                    }, {
                      leading: withCtx(() => [
                        createVNode("span", { class: "text-xs font-semibold text-gray-500 dark:text-gray-400" }, "+62")
                      ]),
                      _: 1
                    }, 8, ["model-value"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_USeparator, {
              label: "Informasi Rekening Bank",
              ui: { label: "text-xs font-bold uppercase tracking-widest text-gray-400" }
            }, null, _parent2, _scopeId));
            _push2(`<div class="grid grid-cols-1 gap-5 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Bank Utama",
              required: "",
              error: unref(errors).bank_name,
              help: "Pilih atau tuliskan nama bank Anda."
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).bank_name,
                    "onUpdate:modelValue": ($event) => unref(form).bank_name = $event,
                    placeholder: "BCA / Mandiri / BRI",
                    icon: "i-lucide-landmark",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).bank_name,
                      "onUpdate:modelValue": ($event) => unref(form).bank_name = $event,
                      placeholder: "BCA / Mandiri / BRI",
                      icon: "i-lucide-landmark",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Nomor Rekening",
              required: "",
              error: unref(errors).bank_account,
              help: "Pastikan digit nomor rekening sudah tepat tanpa tanda baca."
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).bank_account,
                    "onUpdate:modelValue": ($event) => unref(form).bank_account = $event,
                    placeholder: "Contoh: 712345678",
                    icon: "i-lucide-credit-card",
                    class: "w-full",
                    inputmode: "numeric",
                    ui: { base: "font-mono" },
                    maxlength: 50,
                    minlength: 5,
                    autofocus: false,
                    onInput: ($event) => unref(form).bank_account = unref(digitsOnly)(unref(form).bank_account)
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).bank_account,
                      "onUpdate:modelValue": ($event) => unref(form).bank_account = $event,
                      placeholder: "Contoh: 712345678",
                      icon: "i-lucide-credit-card",
                      class: "w-full",
                      inputmode: "numeric",
                      ui: { base: "font-mono" },
                      maxlength: 50,
                      minlength: 5,
                      autofocus: false,
                      onInput: ($event) => unref(form).bank_account = unref(digitsOnly)(unref(form).bank_account)
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "onInput"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_USeparator, {
              label: "Data NPWP (Opsional)",
              ui: { label: "text-xs font-bold uppercase tracking-widest text-gray-400" }
            }, null, _parent2, _scopeId));
            _push2(`<div class="grid grid-cols-1 gap-5 sm:grid-cols-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Nama pada NPWP",
              error: unref(errors).npwp_nama
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).npwp_nama,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_nama = $event,
                    placeholder: "Nama sesuai NPWP",
                    icon: "i-lucide-user-round-search",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).npwp_nama,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_nama = $event,
                      placeholder: "Nama sesuai NPWP",
                      icon: "i-lucide-user-round-search",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Nomor NPWP",
              error: unref(errors).npwp_number
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, mergeProps({
                    modelValue: unref(form).npwp_number,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_number = $event,
                    placeholder: "00.000.000.0-000.000",
                    icon: "i-lucide-file-badge",
                    class: "w-full"
                  }, ssrGetDirectiveProps(_ctx, unref(vMaska), "##.###.###.#-###.###")), null, _parent3, _scopeId2));
                } else {
                  return [
                    withDirectives(createVNode(_component_UInput, {
                      modelValue: unref(form).npwp_number,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_number = $event,
                      placeholder: "00.000.000.0-000.000",
                      icon: "i-lucide-file-badge",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"]), [
                      [unref(vMaska), "##.###.###.#-###.###"]
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Jenis Kelamin NPWP",
              error: unref(errors).npwp_jk
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: unref(form).npwp_jk,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_jk = $event,
                    items: unref(npwpGenderItems),
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih jenis kelamin",
                    icon: "i-lucide-venus-and-mars",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      modelValue: unref(form).npwp_jk,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_jk = $event,
                      items: unref(npwpGenderItems),
                      "value-key": "value",
                      "label-key": "label",
                      placeholder: "Pilih jenis kelamin",
                      icon: "i-lucide-venus-and-mars",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Tanggal NPWP",
              error: unref(errors).npwp_date
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).npwp_date,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_date = $event,
                    type: "date",
                    icon: "i-lucide-calendar-days",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).npwp_date,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_date = $event,
                      type: "date",
                      icon: "i-lucide-calendar-days",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Status Menikah",
              error: unref(errors).npwp_menikah
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: unref(form).npwp_menikah,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_menikah = $event,
                    items: unref(yesNoItems),
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih status",
                    icon: "i-lucide-heart",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      modelValue: unref(form).npwp_menikah,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_menikah = $event,
                      items: unref(yesNoItems),
                      "value-key": "value",
                      "label-key": "label",
                      placeholder: "Pilih status",
                      icon: "i-lucide-heart",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Status Bekerja",
              error: unref(errors).npwp_kerja
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_USelectMenu, {
                    modelValue: unref(form).npwp_kerja,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_kerja = $event,
                    items: unref(yesNoItems),
                    "value-key": "value",
                    "label-key": "label",
                    placeholder: "Pilih status",
                    icon: "i-lucide-briefcase-business",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_USelectMenu, {
                      modelValue: unref(form).npwp_kerja,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_kerja = $event,
                      items: unref(yesNoItems),
                      "value-key": "value",
                      "label-key": "label",
                      placeholder: "Pilih status",
                      icon: "i-lucide-briefcase-business",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Jumlah Anak",
              error: unref(errors).npwp_anak
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).npwp_anak,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_anak = $event,
                    inputmode: "numeric",
                    placeholder: "Contoh: 0",
                    icon: "i-lucide-baby",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).npwp_anak,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_anak = $event,
                      inputmode: "numeric",
                      placeholder: "Contoh: 0",
                      icon: "i-lucide-baby",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Nama Kantor",
              error: unref(errors).npwp_office
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UInput, {
                    modelValue: unref(form).npwp_office,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_office = $event,
                    placeholder: "Nama tempat kerja",
                    icon: "i-lucide-building-2",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UInput, {
                      modelValue: unref(form).npwp_office,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_office = $event,
                      placeholder: "Nama tempat kerja",
                      icon: "i-lucide-building-2",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            _push2(ssrRenderComponent(_component_UFormField, {
              label: "Alamat NPWP",
              error: unref(errors).npwp_alamat
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UTextarea, {
                    modelValue: unref(form).npwp_alamat,
                    "onUpdate:modelValue": ($event) => unref(form).npwp_alamat = $event,
                    rows: 3,
                    placeholder: "Alamat sesuai dokumen NPWP",
                    class: "w-full"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UTextarea, {
                      modelValue: unref(form).npwp_alamat,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_alamat = $event,
                      rows: 3,
                      placeholder: "Alamat sesuai dokumen NPWP",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (Object.keys(unref(errors)).length) {
              _push2(ssrRenderComponent(_component_UAlert, {
                color: "error",
                variant: "subtle",
                icon: "i-lucide-alert-circle",
                title: "Terdapat Kesalahan Input"
              }, {
                description: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<ul class="list-disc pl-4 mt-2 space-y-1"${_scopeId2}><!--[-->`);
                    ssrRenderList(unref(errors), (message, key) => {
                      _push3(`<li${_scopeId2}>${ssrInterpolate(message)}</li>`);
                    });
                    _push3(`<!--]--></ul>`);
                  } else {
                    return [
                      createVNode("ul", { class: "list-disc pl-4 mt-2 space-y-1" }, [
                        (openBlock(true), createBlock(Fragment, null, renderList(unref(errors), (message, key) => {
                          return openBlock(), createBlock("li", { key }, toDisplayString(message), 1);
                        }), 128))
                      ])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end pt-4 border-t border-gray-100 dark:border-gray-800"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "ghost",
              class: "rounded-xl px-6",
              disabled: unref(submitting),
              onClick: unref(resetToCurrentCustomerData),
              label: "Batalkan"
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              variant: "solid",
              class: "rounded-xl px-8 shadow-lg shadow-primary-500/20",
              loading: unref(submitting),
              onClick: unref(submit),
              label: "Simpan Data Akun"
            }, null, _parent2, _scopeId));
            _push2(`</div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-6" }, [
                !hasCompleteDefaultAddress.value ? (openBlock(), createBlock(_component_UAlert, {
                  key: 0,
                  color: "warning",
                  variant: "subtle",
                  icon: "i-lucide-map-pin-off",
                  title: "Default Alamat Lengkap Belum Tersedia",
                  description: "Silakan atur alamat utama Anda di menu Alamat agar data pengiriman dan dokumen akun bisa tervalidasi.",
                  ui: { title: "font-bold" }
                })) : createCommentVNode("", true),
                createVNode(_component_UAlert, {
                  color: "warning",
                  variant: "subtle",
                  icon: "i-lucide-shield-check",
                  title: "Verifikasi Rekening",
                  description: `Nama pemilik rekening harus sesuai dengan nama terdaftar: ${customerName.value}. Ketidaksesuaian dapat menyebabkan pembatalan otomatis pada proses withdrawal.`,
                  ui: { title: "font-bold" }
                }, null, 8, ["description"]),
                createVNode(_component_UAlert, {
                  color: "info",
                  variant: "subtle",
                  icon: "i-lucide-lock",
                  title: "Kolom Terkunci",
                  description: "Username, NIK, Email, dan No. Telepon tidak dapat diubah sendiri. Selain itu, NIK, Email, dan No. Telepon hanya boleh dipakai maksimal 7 akun. Hubungi admin jika perlu perubahan atau jika data Anda dipakai terlalu banyak akun.",
                  ui: { title: "font-bold" }
                }),
                createVNode("div", { class: "grid grid-cols-1 gap-5 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Username",
                    required: "",
                    error: unref(errors).username,
                    help: "Username tidak dapat diubah. Hubungi admin untuk perubahan."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).username,
                        "onUpdate:modelValue": ($event) => unref(form).username = $event,
                        readonly: "",
                        icon: "i-lucide-lock",
                        class: "w-full opacity-70 cursor-not-allowed"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Nama Lengkap",
                    required: "",
                    error: unref(errors).name,
                    help: "Harus sesuai dengan nama yang tertera di KTP/Buku Tabungan."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).name,
                        "onUpdate:modelValue": ($event) => unref(form).name = $event,
                        placeholder: "Input nama lengkap Anda",
                        icon: "i-lucide-user",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "NIK",
                    required: "",
                    error: unref(errors).nik,
                    help: "NIK tidak dapat diubah dari sini. Satu NIK hanya bisa dipakai maksimal 7 akun."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        "model-value": numericNik.value,
                        readonly: "",
                        icon: "i-lucide-lock",
                        class: "w-full opacity-70 cursor-not-allowed"
                      }, null, 8, ["model-value"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Jenis Kelamin",
                    required: "",
                    error: unref(errors).gender
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelectMenu, {
                        modelValue: unref(form).gender,
                        "onUpdate:modelValue": ($event) => unref(form).gender = $event,
                        items: unref(genderItems),
                        "value-key": "value",
                        "label-key": "label",
                        placeholder: "Pilih jenis kelamin",
                        icon: "i-lucide-venus-and-mars",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Email",
                    required: "",
                    error: unref(errors).email,
                    help: "Email tidak dapat diubah dari sini. Satu email hanya bisa dipakai maksimal 7 akun."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).email,
                        "onUpdate:modelValue": ($event) => unref(form).email = $event,
                        type: "email",
                        icon: "i-lucide-lock",
                        class: "w-full opacity-70 cursor-not-allowed"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "WhatsApp / No. Telepon",
                    required: "",
                    error: unref(errors).phone,
                    help: "Nomor telepon tidak dapat diubah dari sini. Satu nomor HP/WhatsApp hanya bisa dipakai maksimal 7 akun."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        "model-value": phoneLocalPart.value,
                        readonly: "",
                        inputmode: "numeric",
                        class: "w-full opacity-70 cursor-not-allowed"
                      }, {
                        leading: withCtx(() => [
                          createVNode("span", { class: "text-xs font-semibold text-gray-500 dark:text-gray-400" }, "+62")
                        ]),
                        _: 1
                      }, 8, ["model-value"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode(_component_USeparator, {
                  label: "Informasi Rekening Bank",
                  ui: { label: "text-xs font-bold uppercase tracking-widest text-gray-400" }
                }),
                createVNode("div", { class: "grid grid-cols-1 gap-5 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Bank Utama",
                    required: "",
                    error: unref(errors).bank_name,
                    help: "Pilih atau tuliskan nama bank Anda."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).bank_name,
                        "onUpdate:modelValue": ($event) => unref(form).bank_name = $event,
                        placeholder: "BCA / Mandiri / BRI",
                        icon: "i-lucide-landmark",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Nomor Rekening",
                    required: "",
                    error: unref(errors).bank_account,
                    help: "Pastikan digit nomor rekening sudah tepat tanpa tanda baca."
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).bank_account,
                        "onUpdate:modelValue": ($event) => unref(form).bank_account = $event,
                        placeholder: "Contoh: 712345678",
                        icon: "i-lucide-credit-card",
                        class: "w-full",
                        inputmode: "numeric",
                        ui: { base: "font-mono" },
                        maxlength: 50,
                        minlength: 5,
                        autofocus: false,
                        onInput: ($event) => unref(form).bank_account = unref(digitsOnly)(unref(form).bank_account)
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "onInput"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode(_component_USeparator, {
                  label: "Data NPWP (Opsional)",
                  ui: { label: "text-xs font-bold uppercase tracking-widest text-gray-400" }
                }),
                createVNode("div", { class: "grid grid-cols-1 gap-5 sm:grid-cols-2" }, [
                  createVNode(_component_UFormField, {
                    label: "Nama pada NPWP",
                    error: unref(errors).npwp_nama
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).npwp_nama,
                        "onUpdate:modelValue": ($event) => unref(form).npwp_nama = $event,
                        placeholder: "Nama sesuai NPWP",
                        icon: "i-lucide-user-round-search",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Nomor NPWP",
                    error: unref(errors).npwp_number
                  }, {
                    default: withCtx(() => [
                      withDirectives(createVNode(_component_UInput, {
                        modelValue: unref(form).npwp_number,
                        "onUpdate:modelValue": ($event) => unref(form).npwp_number = $event,
                        placeholder: "00.000.000.0-000.000",
                        icon: "i-lucide-file-badge",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"]), [
                        [unref(vMaska), "##.###.###.#-###.###"]
                      ])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Jenis Kelamin NPWP",
                    error: unref(errors).npwp_jk
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelectMenu, {
                        modelValue: unref(form).npwp_jk,
                        "onUpdate:modelValue": ($event) => unref(form).npwp_jk = $event,
                        items: unref(npwpGenderItems),
                        "value-key": "value",
                        "label-key": "label",
                        placeholder: "Pilih jenis kelamin",
                        icon: "i-lucide-venus-and-mars",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Tanggal NPWP",
                    error: unref(errors).npwp_date
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).npwp_date,
                        "onUpdate:modelValue": ($event) => unref(form).npwp_date = $event,
                        type: "date",
                        icon: "i-lucide-calendar-days",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Status Menikah",
                    error: unref(errors).npwp_menikah
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelectMenu, {
                        modelValue: unref(form).npwp_menikah,
                        "onUpdate:modelValue": ($event) => unref(form).npwp_menikah = $event,
                        items: unref(yesNoItems),
                        "value-key": "value",
                        "label-key": "label",
                        placeholder: "Pilih status",
                        icon: "i-lucide-heart",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Status Bekerja",
                    error: unref(errors).npwp_kerja
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_USelectMenu, {
                        modelValue: unref(form).npwp_kerja,
                        "onUpdate:modelValue": ($event) => unref(form).npwp_kerja = $event,
                        items: unref(yesNoItems),
                        "value-key": "value",
                        "label-key": "label",
                        placeholder: "Pilih status",
                        icon: "i-lucide-briefcase-business",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue", "items"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Jumlah Anak",
                    error: unref(errors).npwp_anak
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).npwp_anak,
                        "onUpdate:modelValue": ($event) => unref(form).npwp_anak = $event,
                        inputmode: "numeric",
                        placeholder: "Contoh: 0",
                        icon: "i-lucide-baby",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"]),
                  createVNode(_component_UFormField, {
                    label: "Nama Kantor",
                    error: unref(errors).npwp_office
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UInput, {
                        modelValue: unref(form).npwp_office,
                        "onUpdate:modelValue": ($event) => unref(form).npwp_office = $event,
                        placeholder: "Nama tempat kerja",
                        icon: "i-lucide-building-2",
                        class: "w-full"
                      }, null, 8, ["modelValue", "onUpdate:modelValue"])
                    ]),
                    _: 1
                  }, 8, ["error"])
                ]),
                createVNode(_component_UFormField, {
                  label: "Alamat NPWP",
                  error: unref(errors).npwp_alamat
                }, {
                  default: withCtx(() => [
                    createVNode(_component_UTextarea, {
                      modelValue: unref(form).npwp_alamat,
                      "onUpdate:modelValue": ($event) => unref(form).npwp_alamat = $event,
                      rows: 3,
                      placeholder: "Alamat sesuai dokumen NPWP",
                      class: "w-full"
                    }, null, 8, ["modelValue", "onUpdate:modelValue"])
                  ]),
                  _: 1
                }, 8, ["error"]),
                Object.keys(unref(errors)).length ? (openBlock(), createBlock(_component_UAlert, {
                  key: 1,
                  color: "error",
                  variant: "subtle",
                  icon: "i-lucide-alert-circle",
                  title: "Terdapat Kesalahan Input"
                }, {
                  description: withCtx(() => [
                    createVNode("ul", { class: "list-disc pl-4 mt-2 space-y-1" }, [
                      (openBlock(true), createBlock(Fragment, null, renderList(unref(errors), (message, key) => {
                        return openBlock(), createBlock("li", { key }, toDisplayString(message), 1);
                      }), 128))
                    ])
                  ]),
                  _: 1
                })) : createCommentVNode("", true),
                createVNode("div", { class: "flex flex-col-reverse gap-3 sm:flex-row sm:justify-end pt-4 border-t border-gray-100 dark:border-gray-800" }, [
                  createVNode(_component_UButton, {
                    color: "neutral",
                    variant: "ghost",
                    class: "rounded-xl px-6",
                    disabled: unref(submitting),
                    onClick: unref(resetToCurrentCustomerData),
                    label: "Batalkan"
                  }, null, 8, ["disabled", "onClick"]),
                  createVNode(_component_UButton, {
                    color: "primary",
                    variant: "solid",
                    class: "rounded-xl px-8 shadow-lg shadow-primary-500/20",
                    loading: unref(submitting),
                    onClick: unref(submit),
                    label: "Simpan Data Akun"
                  }, null, 8, ["loading", "onClick"])
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
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/account/DashboardAccountFormCard.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "FormAccount",
  __ssrInlineRender: true,
  props: {
    customer: {},
    defaultAddress: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-6" }, _attrs))}>`);
      _push(ssrRenderComponent(_sfc_main$1, {
        customer: __props.customer,
        "default-address": __props.defaultAddress
      }, null, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/FormAccount.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
