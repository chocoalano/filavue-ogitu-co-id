import { _ as _sfc_main$9 } from "./Card-CvchAxCK.js";
import { ref, computed, watch, defineComponent, mergeProps, withCtx, createVNode, createTextVNode, toDisplayString, useSSRContext, openBlock, createBlock, Fragment, renderList, createCommentVNode, unref, isRef } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList, ssrRenderClass } from "vue/server-renderer";
import { useForm } from "@inertiajs/vue3";
import { u as useToast } from "./useToast-CTuSIf9f.js";
import { _ as _sfc_main$8 } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$7 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$b } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$a } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$c } from "./Empty-20F1kDsa.js";
import { _ as _sfc_main$d } from "./Modal-BB5lXp8z.js";
import { _ as _sfc_main$e } from "./Alert-BRMLOsUb.js";
import "reka-ui";
import "hookable";
import "@vueuse/core";
import "defu";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "ufo";
import "./usePortal-EQErrF6h.js";
function useDashboardMitra(options) {
  const toast = useToast();
  const activeTab = ref("active");
  const q = ref("");
  const counts = computed(() => ({
    active: options.activeMembers.value.length,
    passive: options.passiveMembers.value.length,
    prospect: options.prospectMembers.value.length
  }));
  const totalMembers = computed(() => counts.value.active + counts.value.passive + counts.value.prospect);
  const tabs = computed(() => [
    { value: "active", label: "Aktif", icon: "i-lucide-check-circle-2", count: counts.value.active },
    { value: "passive", label: "Pasif", icon: "i-lucide-clock", count: counts.value.passive },
    { value: "prospect", label: "Prospek", icon: "i-lucide-user-plus", count: counts.value.prospect }
  ]);
  const membersByTab = computed(() => {
    if (activeTab.value === "active") {
      return options.activeMembers.value;
    }
    if (activeTab.value === "passive") {
      return options.passiveMembers.value;
    }
    return options.prospectMembers.value;
  });
  const filteredMembers = computed(() => {
    const keyword = q.value.trim().toLowerCase();
    const arr = [...membersByTab.value];
    if (!keyword) {
      return arr;
    }
    return arr.filter((member) => {
      const hay = [
        member.name,
        member.username ?? "",
        member.email ?? "",
        member.phone ?? "",
        member.package_name ?? "",
        member.position ?? "",
        String(member.level ?? ""),
        String(member.total_left ?? ""),
        String(member.total_right ?? "")
      ].join(" ").toLowerCase();
      return hay.includes(keyword);
    });
  });
  const hintText = computed(() => {
    if (activeTab.value === "active") {
      return "Sudah ditempatkan di binary tree.";
    }
    if (activeTab.value === "passive") {
      return "Belum ditempatkan, tapi sudah memiliki pembelian.";
    }
    return "Member baru, belum ditempatkan dan belum pembelian.";
  });
  const isDetailOpen = ref(false);
  const detailMember = ref(null);
  const showPlacementDialog = ref(false);
  const selectedMember = ref(null);
  const selectedPosition = ref(null);
  const placementForm = useForm({
    member_id: 0,
    upline_id: 0,
    position: ""
  });
  const hasLeft = computed(() => options.hasLeft.value);
  const hasRight = computed(() => options.hasRight.value);
  const formatDate = (dateString) => {
    if (!dateString) {
      return "-";
    }
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) {
      return dateString;
    }
    return date.toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
  };
  const formatCurrency = (amount) => new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
  const getPositionBadge = (position) => {
    if (!position) {
      return { color: "neutral", variant: "subtle", text: "Belum Ditempatkan" };
    }
    return {
      color: position === "left" ? "primary" : "info",
      variant: "soft",
      text: position === "left" ? "Kiri" : "Kanan"
    };
  };
  const tabStatusBadge = (tab) => {
    if (tab === "active") {
      return { color: "success", icon: "i-lucide-check-circle-2", text: "Aktif" };
    }
    if (tab === "passive") {
      return { color: "neutral", icon: "i-lucide-clock", text: "Pasif" };
    }
    return { color: "warning", icon: "i-lucide-user-plus", text: "Prospek" };
  };
  function memberState(member) {
    const hasPlacement = member.has_placement === true || !!member.position;
    const hasPurchase = member.has_purchase === true;
    if (hasPlacement) {
      return { key: "active", ...tabStatusBadge("active") };
    }
    if (hasPurchase) {
      return { key: "passive", ...tabStatusBadge("passive") };
    }
    return { key: "prospect", ...tabStatusBadge("prospect") };
  }
  function openDetail(member) {
    detailMember.value = member;
    isDetailOpen.value = true;
  }
  function closeDetail() {
    isDetailOpen.value = false;
  }
  function openPlacementDialog(member) {
    if (!options.currentCustomerId.value) {
      toast?.add?.({
        title: "Gagal membuka modal",
        description: "Data upline tidak ditemukan. Muat ulang halaman dan coba lagi.",
        color: "error",
        icon: "i-lucide-x-circle"
      });
      return;
    }
    selectedMember.value = member;
    selectedPosition.value = null;
    placementForm.member_id = member.id;
    placementForm.upline_id = options.currentCustomerId.value;
    placementForm.position = "";
    showPlacementDialog.value = true;
  }
  const closePlacementDialog = () => {
    showPlacementDialog.value = false;
    selectedMember.value = null;
    selectedPosition.value = null;
    placementForm.reset();
  };
  const placeToBinaryTree = () => {
    if (!selectedMember.value || !selectedPosition.value) {
      toast?.add?.({ title: "Pilih posisi terlebih dahulu", color: "warning", icon: "i-lucide-alert-circle" });
      return;
    }
    if (!placementForm.upline_id) {
      toast?.add?.({
        title: "Gagal",
        description: "Data upline tidak valid.",
        color: "error",
        icon: "i-lucide-x-circle"
      });
      return;
    }
    placementForm.position = selectedPosition.value;
    placementForm.post("/mlm/place-member", {
      onSuccess: () => {
        toast?.add?.({
          title: "Berhasil",
          description: `${selectedMember.value?.name} ditempatkan di posisi ${selectedPosition.value === "left" ? "Kiri" : "Kanan"}`,
          color: "success",
          icon: "i-lucide-check-circle-2"
        });
        closePlacementDialog();
      },
      onError: (errors) => {
        toast?.add?.({
          title: "Gagal",
          description: errors?.error || "Gagal menempatkan member ke binary tree",
          color: "error",
          icon: "i-lucide-x-circle"
        });
      }
    });
  };
  function openPlacementFromDetail() {
    if (!detailMember.value) {
      return;
    }
    const selected = detailMember.value;
    closeDetail();
    openPlacementDialog(selected);
  }
  watch(isDetailOpen, (open) => {
    if (!open) {
      detailMember.value = null;
    }
  });
  return {
    activeTab,
    q,
    counts,
    totalMembers,
    tabs,
    filteredMembers,
    hintText,
    formatDate,
    formatCurrency,
    getPositionBadge,
    tabStatusBadge,
    memberState,
    isDetailOpen,
    detailMember,
    openDetail,
    closeDetail,
    showPlacementDialog,
    selectedMember,
    selectedPosition,
    placementForm,
    hasLeft,
    hasRight,
    openPlacementDialog,
    closePlacementDialog,
    placeToBinaryTree,
    openPlacementFromDetail
  };
}
const _sfc_main$6 = /* @__PURE__ */ defineComponent({
  __name: "MitraHeaderPanel",
  __ssrInlineRender: true,
  props: {
    totalMembers: {},
    activeTab: {},
    tabBadge: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$7;
      const _component_UBadge = _sfc_main$8;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between" }, _attrs))}><div class="flex items-start gap-3"><div class="flex size-10 items-center justify-center rounded-2xl border border-default bg-elevated/60">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-network",
        class: "size-5 text-primary"
      }, null, _parent));
      _push(`</div><div class="min-w-0"><p class="text-base font-semibold text-highlighted">Jaringan Member</p><p class="mt-0.5 text-sm text-muted">Kelola dan lihat member dalam jaringan Anda</p></div></div><div class="flex items-center gap-2">`);
      _push(ssrRenderComponent(_component_UBadge, {
        color: "neutral",
        variant: "subtle",
        class: "rounded-2xl"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-users",
              class: "mr-1 size-3.5"
            }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(__props.totalMembers)}`);
          } else {
            return [
              createVNode(_component_UIcon, {
                name: "i-lucide-users",
                class: "mr-1 size-3.5"
              }),
              createTextVNode(" " + toDisplayString(__props.totalMembers), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UBadge, {
        color: __props.activeTab === "active" ? "success" : __props.activeTab === "passive" ? "neutral" : "warning",
        variant: "soft",
        class: "rounded-2xl hidden sm:inline-flex"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UIcon, {
              name: __props.tabBadge.icon,
              class: "mr-1 size-3.5"
            }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(__props.tabBadge.text)}`);
          } else {
            return [
              createVNode(_component_UIcon, {
                name: __props.tabBadge.icon,
                class: "mr-1 size-3.5"
              }, null, 8, ["name"]),
              createTextVNode(" " + toDisplayString(__props.tabBadge.text), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/mitra/MitraHeaderPanel.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "MitraFilterTabsCard",
  __ssrInlineRender: true,
  props: {
    tabs: {},
    activeTab: {},
    hintText: {},
    searchQuery: {}
  },
  emits: ["update:activeTab", "update:searchQuery"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function onSearchUpdate(value) {
      emit("update:searchQuery", String(value ?? ""));
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      const _component_UIcon = _sfc_main$7;
      const _component_UBadge = _sfc_main$8;
      const _component_UInput = _sfc_main$b;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "rounded-2xl",
        ui: { body: "p-2" }
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="grid grid-cols-3 gap-2"${_scopeId}><!--[-->`);
            ssrRenderList(props.tabs, (tab) => {
              _push2(ssrRenderComponent(_component_UButton, {
                key: tab.value,
                block: "",
                color: "neutral",
                variant: "ghost",
                class: ["rounded-xl py-2.5 px-2.5", props.activeTab === tab.value ? "bg-white dark:bg-gray-950 ring-1 ring-black/5 dark:ring-white/5" : "hover:bg-white/70 dark:hover:bg-white/10"],
                onClick: ($event) => emit("update:activeTab", tab.value)
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<div class="flex items-center justify-center gap-2"${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_UIcon, {
                      name: tab.icon,
                      class: ["size-4", props.activeTab === tab.value ? "text-primary" : "text-muted"]
                    }, null, _parent3, _scopeId2));
                    _push3(`<span class="text-xs sm:text-sm font-semibold text-highlighted"${_scopeId2}>${ssrInterpolate(tab.label)}</span>`);
                    _push3(ssrRenderComponent(_component_UBadge, {
                      color: "neutral",
                      variant: "subtle",
                      size: "xs",
                      class: "rounded-xl"
                    }, {
                      default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                        if (_push4) {
                          _push4(`${ssrInterpolate(tab.count)}`);
                        } else {
                          return [
                            createTextVNode(toDisplayString(tab.count), 1)
                          ];
                        }
                      }),
                      _: 2
                    }, _parent3, _scopeId2));
                    _push3(`</div>`);
                  } else {
                    return [
                      createVNode("div", { class: "flex items-center justify-center gap-2" }, [
                        createVNode(_component_UIcon, {
                          name: tab.icon,
                          class: ["size-4", props.activeTab === tab.value ? "text-primary" : "text-muted"]
                        }, null, 8, ["name", "class"]),
                        createVNode("span", { class: "text-xs sm:text-sm font-semibold text-highlighted" }, toDisplayString(tab.label), 1),
                        createVNode(_component_UBadge, {
                          color: "neutral",
                          variant: "subtle",
                          size: "xs",
                          class: "rounded-xl"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(tab.count), 1)
                          ]),
                          _: 2
                        }, 1024)
                      ])
                    ];
                  }
                }),
                _: 2
              }, _parent2, _scopeId));
            });
            _push2(`<!--]--></div><div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"${_scopeId}><p class="text-xs text-muted"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-info",
              class: "mr-1 inline-block size-3.5"
            }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(props.hintText)}</p>`);
            _push2(ssrRenderComponent(_component_UInput, {
              "model-value": props.searchQuery,
              icon: "i-lucide-search",
              placeholder: "Cari nama, username, email, telepon...",
              size: "sm",
              class: "w-full sm:w-80",
              "onUpdate:modelValue": onSearchUpdate
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "grid grid-cols-3 gap-2" }, [
                (openBlock(true), createBlock(Fragment, null, renderList(props.tabs, (tab) => {
                  return openBlock(), createBlock(_component_UButton, {
                    key: tab.value,
                    block: "",
                    color: "neutral",
                    variant: "ghost",
                    class: ["rounded-xl py-2.5 px-2.5", props.activeTab === tab.value ? "bg-white dark:bg-gray-950 ring-1 ring-black/5 dark:ring-white/5" : "hover:bg-white/70 dark:hover:bg-white/10"],
                    onClick: ($event) => emit("update:activeTab", tab.value)
                  }, {
                    default: withCtx(() => [
                      createVNode("div", { class: "flex items-center justify-center gap-2" }, [
                        createVNode(_component_UIcon, {
                          name: tab.icon,
                          class: ["size-4", props.activeTab === tab.value ? "text-primary" : "text-muted"]
                        }, null, 8, ["name", "class"]),
                        createVNode("span", { class: "text-xs sm:text-sm font-semibold text-highlighted" }, toDisplayString(tab.label), 1),
                        createVNode(_component_UBadge, {
                          color: "neutral",
                          variant: "subtle",
                          size: "xs",
                          class: "rounded-xl"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(tab.count), 1)
                          ]),
                          _: 2
                        }, 1024)
                      ])
                    ]),
                    _: 2
                  }, 1032, ["class", "onClick"]);
                }), 128))
              ]),
              createVNode("div", { class: "mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode("p", { class: "text-xs text-muted" }, [
                  createVNode(_component_UIcon, {
                    name: "i-lucide-info",
                    class: "mr-1 inline-block size-3.5"
                  }),
                  createTextVNode(" " + toDisplayString(props.hintText), 1)
                ]),
                createVNode(_component_UInput, {
                  "model-value": props.searchQuery,
                  icon: "i-lucide-search",
                  placeholder: "Cari nama, username, email, telepon...",
                  size: "sm",
                  class: "w-full sm:w-80",
                  "onUpdate:modelValue": onSearchUpdate
                }, null, 8, ["model-value"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/mitra/MitraFilterTabsCard.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "MitraMemberCard",
  __ssrInlineRender: true,
  props: {
    member: {},
    activeTab: {},
    hasLeft: { type: Boolean },
    hasRight: { type: Boolean },
    formatDate: { type: Function },
    formatCurrency: { type: Function },
    getPositionBadge: { type: Function },
    tabStatusBadge: { type: Function }
  },
  emits: ["openDetail", "placeMember"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$9;
      const _component_UBadge = _sfc_main$8;
      const _component_UIcon = _sfc_main$7;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UCard, mergeProps({
        class: "rounded-2xl",
        ui: { root: "hover:bg-elevated/25 transition-colors" }
      }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex items-start justify-between gap-3"${_scopeId}><div class="min-w-0 flex-1"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}><p class="truncate text-sm sm:text-base font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(props.member.name)} <span class="text-sm font-normal text-muted"${_scopeId}>(@${ssrInterpolate(props.member.username)})</span></p>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: props.getPositionBadge(props.member.position).color,
              variant: props.getPositionBadge(props.member.position).variant,
              class: "rounded-2xl"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`${ssrInterpolate(props.getPositionBadge(props.member.position).text)}`);
                } else {
                  return [
                    createTextVNode(toDisplayString(props.getPositionBadge(props.member.position).text), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (props.member.level) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "neutral",
                variant: "subtle",
                class: "rounded-2xl"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Level ${ssrInterpolate(props.member.level)}`);
                  } else {
                    return [
                      createTextVNode(" Level " + toDisplayString(props.member.level), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><div class="mt-2 grid gap-1 text-xs sm:text-sm text-muted"${_scopeId}><div class="flex flex-wrap items-center gap-x-4 gap-y-1"${_scopeId}><span class="inline-flex items-center gap-1.5 min-w-0"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-mail",
              class: "size-4"
            }, null, _parent2, _scopeId));
            _push2(`<span class="truncate"${_scopeId}>${ssrInterpolate(props.member.email)}</span></span>`);
            if (props.member.phone) {
              _push2(`<span class="inline-flex items-center gap-1.5"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-phone",
                class: "size-4"
              }, null, _parent2, _scopeId));
              _push2(`<span class="truncate"${_scopeId}>${ssrInterpolate(props.member.phone)}</span></span>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><div class="flex flex-wrap items-center gap-x-4 gap-y-1"${_scopeId}>`);
            if (props.member.package_name) {
              _push2(`<span class="inline-flex items-center gap-1.5"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-badge",
                class: "size-4"
              }, null, _parent2, _scopeId));
              _push2(`<span class="text-primary font-semibold"${_scopeId}>Paket: ${ssrInterpolate(props.member.package_name)}</span></span>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<span class="inline-flex items-center gap-1.5"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-calendar",
              class: "size-4"
            }, null, _parent2, _scopeId));
            _push2(` Bergabung: ${ssrInterpolate(props.formatDate(props.member.joined_at))}</span></div>`);
            if ((props.member.total_left ?? 0) > 0 || (props.member.total_right ?? 0) > 0) {
              _push2(`<div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "info",
                variant: "subtle",
                class: "rounded-xl"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`Kiri: ${ssrInterpolate(props.member.total_left ?? 0)}`);
                  } else {
                    return [
                      createTextVNode("Kiri: " + toDisplayString(props.member.total_left ?? 0), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "success",
                variant: "subtle",
                class: "rounded-xl"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`Kanan: ${ssrInterpolate(props.member.total_right ?? 0)}`);
                  } else {
                    return [
                      createTextVNode("Kanan: " + toDisplayString(props.member.total_right ?? 0), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div><div class="shrink-0 flex flex-col items-end gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UBadge, {
              color: props.tabStatusBadge(props.activeTab).color,
              variant: "soft",
              class: "rounded-2xl"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: props.tabStatusBadge(props.activeTab).icon,
                    class: "mr-1 size-3.5"
                  }, null, _parent3, _scopeId2));
                  _push3(` ${ssrInterpolate(props.tabStatusBadge(props.activeTab).text)}`);
                } else {
                  return [
                    createVNode(_component_UIcon, {
                      name: props.tabStatusBadge(props.activeTab).icon,
                      class: "mr-1 size-3.5"
                    }, null, 8, ["name"]),
                    createTextVNode(" " + toDisplayString(props.tabStatusBadge(props.activeTab).text), 1)
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`<div class="${ssrRenderClass([props.activeTab === "active" ? "text-emerald-600 dark:text-emerald-400" : props.activeTab === "passive" ? "text-orange-600 dark:text-orange-400" : "text-muted", "text-xs sm:text-sm font-semibold tabular-nums"])}"${_scopeId}> Omzet: ${ssrInterpolate(props.formatCurrency(props.member.omzet ?? 0))}</div><div class="flex items-center gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              size: "xs",
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              icon: "i-lucide-eye",
              onClick: ($event) => emit("openDetail", props.member)
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Detail `);
                } else {
                  return [
                    createTextVNode(" Detail ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (props.activeTab === "passive" && (!props.hasLeft || !props.hasRight)) {
              _push2(ssrRenderComponent(_component_UButton, {
                size: "xs",
                color: "primary",
                variant: "soft",
                class: "rounded-xl",
                icon: "i-lucide-git-branch",
                onClick: ($event) => emit("placeMember", props.member)
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Tempatkan `);
                  } else {
                    return [
                      createTextVNode(" Tempatkan ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div><div class="flex flex-wrap items-center justify-end gap-1.5"${_scopeId}>`);
            if (props.activeTab === "passive" && props.member.has_purchase) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "primary",
                variant: "subtle",
                class: "rounded-xl"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Sudah Belanja `);
                  } else {
                    return [
                      createTextVNode(" Sudah Belanja ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            if (props.activeTab === "prospect" && !props.member.has_purchase) {
              _push2(ssrRenderComponent(_component_UBadge, {
                color: "neutral",
                variant: "subtle",
                class: "rounded-xl"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Belum Belanja `);
                  } else {
                    return [
                      createTextVNode(" Belum Belanja ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "flex items-start justify-between gap-3" }, [
                createVNode("div", { class: "min-w-0 flex-1" }, [
                  createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                    createVNode("p", { class: "truncate text-sm sm:text-base font-semibold text-highlighted" }, [
                      createTextVNode(toDisplayString(props.member.name) + " ", 1),
                      createVNode("span", { class: "text-sm font-normal text-muted" }, "(@" + toDisplayString(props.member.username) + ")", 1)
                    ]),
                    createVNode(_component_UBadge, {
                      color: props.getPositionBadge(props.member.position).color,
                      variant: props.getPositionBadge(props.member.position).variant,
                      class: "rounded-2xl"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(toDisplayString(props.getPositionBadge(props.member.position).text), 1)
                      ]),
                      _: 1
                    }, 8, ["color", "variant"]),
                    props.member.level ? (openBlock(), createBlock(_component_UBadge, {
                      key: 0,
                      color: "neutral",
                      variant: "subtle",
                      class: "rounded-2xl"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Level " + toDisplayString(props.member.level), 1)
                      ]),
                      _: 1
                    })) : createCommentVNode("", true)
                  ]),
                  createVNode("div", { class: "mt-2 grid gap-1 text-xs sm:text-sm text-muted" }, [
                    createVNode("div", { class: "flex flex-wrap items-center gap-x-4 gap-y-1" }, [
                      createVNode("span", { class: "inline-flex items-center gap-1.5 min-w-0" }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-mail",
                          class: "size-4"
                        }),
                        createVNode("span", { class: "truncate" }, toDisplayString(props.member.email), 1)
                      ]),
                      props.member.phone ? (openBlock(), createBlock("span", {
                        key: 0,
                        class: "inline-flex items-center gap-1.5"
                      }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-phone",
                          class: "size-4"
                        }),
                        createVNode("span", { class: "truncate" }, toDisplayString(props.member.phone), 1)
                      ])) : createCommentVNode("", true)
                    ]),
                    createVNode("div", { class: "flex flex-wrap items-center gap-x-4 gap-y-1" }, [
                      props.member.package_name ? (openBlock(), createBlock("span", {
                        key: 0,
                        class: "inline-flex items-center gap-1.5"
                      }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-badge",
                          class: "size-4"
                        }),
                        createVNode("span", { class: "text-primary font-semibold" }, "Paket: " + toDisplayString(props.member.package_name), 1)
                      ])) : createCommentVNode("", true),
                      createVNode("span", { class: "inline-flex items-center gap-1.5" }, [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-calendar",
                          class: "size-4"
                        }),
                        createTextVNode(" Bergabung: " + toDisplayString(props.formatDate(props.member.joined_at)), 1)
                      ])
                    ]),
                    (props.member.total_left ?? 0) > 0 || (props.member.total_right ?? 0) > 0 ? (openBlock(), createBlock("div", {
                      key: 0,
                      class: "flex flex-wrap items-center gap-x-2 gap-y-1 text-xs"
                    }, [
                      createVNode(_component_UBadge, {
                        color: "info",
                        variant: "subtle",
                        class: "rounded-xl"
                      }, {
                        default: withCtx(() => [
                          createTextVNode("Kiri: " + toDisplayString(props.member.total_left ?? 0), 1)
                        ]),
                        _: 1
                      }),
                      createVNode(_component_UBadge, {
                        color: "success",
                        variant: "subtle",
                        class: "rounded-xl"
                      }, {
                        default: withCtx(() => [
                          createTextVNode("Kanan: " + toDisplayString(props.member.total_right ?? 0), 1)
                        ]),
                        _: 1
                      })
                    ])) : createCommentVNode("", true)
                  ])
                ]),
                createVNode("div", { class: "shrink-0 flex flex-col items-end gap-2" }, [
                  createVNode(_component_UBadge, {
                    color: props.tabStatusBadge(props.activeTab).color,
                    variant: "soft",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UIcon, {
                        name: props.tabStatusBadge(props.activeTab).icon,
                        class: "mr-1 size-3.5"
                      }, null, 8, ["name"]),
                      createTextVNode(" " + toDisplayString(props.tabStatusBadge(props.activeTab).text), 1)
                    ]),
                    _: 1
                  }, 8, ["color"]),
                  createVNode("div", {
                    class: ["text-xs sm:text-sm font-semibold tabular-nums", props.activeTab === "active" ? "text-emerald-600 dark:text-emerald-400" : props.activeTab === "passive" ? "text-orange-600 dark:text-orange-400" : "text-muted"]
                  }, " Omzet: " + toDisplayString(props.formatCurrency(props.member.omzet ?? 0)), 3),
                  createVNode("div", { class: "flex items-center gap-2" }, [
                    createVNode(_component_UButton, {
                      size: "xs",
                      color: "neutral",
                      variant: "outline",
                      class: "rounded-xl",
                      icon: "i-lucide-eye",
                      onClick: ($event) => emit("openDetail", props.member)
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Detail ")
                      ]),
                      _: 1
                    }, 8, ["onClick"]),
                    props.activeTab === "passive" && (!props.hasLeft || !props.hasRight) ? (openBlock(), createBlock(_component_UButton, {
                      key: 0,
                      size: "xs",
                      color: "primary",
                      variant: "soft",
                      class: "rounded-xl",
                      icon: "i-lucide-git-branch",
                      onClick: ($event) => emit("placeMember", props.member)
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Tempatkan ")
                      ]),
                      _: 1
                    }, 8, ["onClick"])) : createCommentVNode("", true)
                  ]),
                  createVNode("div", { class: "flex flex-wrap items-center justify-end gap-1.5" }, [
                    props.activeTab === "passive" && props.member.has_purchase ? (openBlock(), createBlock(_component_UBadge, {
                      key: 0,
                      color: "primary",
                      variant: "subtle",
                      class: "rounded-xl"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Sudah Belanja ")
                      ]),
                      _: 1
                    })) : createCommentVNode("", true),
                    props.activeTab === "prospect" && !props.member.has_purchase ? (openBlock(), createBlock(_component_UBadge, {
                      key: 1,
                      color: "neutral",
                      variant: "subtle",
                      class: "rounded-xl"
                    }, {
                      default: withCtx(() => [
                        createTextVNode(" Belum Belanja ")
                      ]),
                      _: 1
                    })) : createCommentVNode("", true)
                  ])
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
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/mitra/MitraMemberCard.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "MitraMembersList",
  __ssrInlineRender: true,
  props: {
    members: {},
    activeTab: {},
    hasLeft: { type: Boolean },
    hasRight: { type: Boolean },
    formatDate: { type: Function },
    formatCurrency: { type: Function },
    getPositionBadge: { type: Function },
    tabStatusBadge: { type: Function }
  },
  emits: ["openDetail", "placeMember"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function emptyTitle(tab) {
      if (tab === "active") {
        return "Belum ada member aktif";
      }
      if (tab === "passive") {
        return "Belum ada member pasif";
      }
      return "Belum ada member prospek";
    }
    function emptyDescription(tab) {
      if (tab === "active") {
        return "Member aktif adalah member yang sudah ditempatkan di binary tree.";
      }
      if (tab === "passive") {
        return "Member pasif adalah member yang belum ditempatkan di binary tree tapi sudah memiliki pembelian/order.";
      }
      return "Member prospek adalah member yang baru mendaftar, belum ditempatkan di binary tree dan belum memiliki pembelian/order.";
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UEmpty = _sfc_main$c;
      if (props.members.length === 0) {
        _push(ssrRenderComponent(_component_UEmpty, mergeProps({
          icon: props.tabStatusBadge(props.activeTab).icon,
          title: emptyTitle(props.activeTab),
          description: emptyDescription(props.activeTab),
          variant: "outline",
          size: "lg",
          ui: { root: "rounded-2xl py-12" }
        }, _attrs), null, _parent));
      } else {
        _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid gap-3" }, _attrs))}><!--[-->`);
        ssrRenderList(props.members, (member) => {
          _push(ssrRenderComponent(_sfc_main$4, {
            key: member.id,
            member,
            "active-tab": props.activeTab,
            "has-left": props.hasLeft,
            "has-right": props.hasRight,
            "format-date": props.formatDate,
            "format-currency": props.formatCurrency,
            "get-position-badge": props.getPositionBadge,
            "tab-status-badge": props.tabStatusBadge,
            onOpenDetail: (value) => emit("openDetail", value),
            onPlaceMember: (value) => emit("placeMember", value)
          }, null, _parent));
        });
        _push(`<!--]--></div>`);
      }
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/mitra/MitraMembersList.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "MitraDetailModal",
  __ssrInlineRender: true,
  props: {
    open: { type: Boolean },
    detailMember: { default: null },
    activeTab: {},
    hasLeft: { type: Boolean },
    hasRight: { type: Boolean },
    formatDate: {},
    formatCurrency: {},
    getPositionBadge: {},
    tabStatusBadge: {},
    memberState: {}
  },
  emits: ["update:open", "close", "place-member"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function closeModal() {
      emit("close");
      emit("update:open", false);
    }
    function placeMember() {
      emit("place-member");
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$d;
      const _component_UBadge = _sfc_main$8;
      const _component_UIcon = _sfc_main$7;
      const _component_UCard = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: __props.open,
        title: __props.detailMember ? `Detail Member: ${__props.detailMember.name}` : "Detail Member",
        description: "Informasi lengkap member dalam jaringan.",
        scrollable: "",
        ui: { overlay: "fixed inset-0 z-[9998] bg-black/50 backdrop-blur-sm", content: "fixed z-[9999] w-full max-w-3xl" },
        "onUpdate:open": (value) => emit("update:open", value)
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (__props.detailMember) {
              _push2(`<div class="space-y-4"${_scopeId}><div class="flex flex-wrap items-center gap-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UBadge, {
                color: __props.memberState(__props.detailMember).color,
                variant: "soft",
                class: "rounded-2xl"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(ssrRenderComponent(_component_UIcon, {
                      name: __props.memberState(__props.detailMember).icon,
                      class: "mr-1 size-3.5"
                    }, null, _parent3, _scopeId2));
                    _push3(` ${ssrInterpolate(__props.memberState(__props.detailMember).text)}`);
                  } else {
                    return [
                      createVNode(_component_UIcon, {
                        name: __props.memberState(__props.detailMember).icon,
                        class: "mr-1 size-3.5"
                      }, null, 8, ["name"]),
                      createTextVNode(" " + toDisplayString(__props.memberState(__props.detailMember).text), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UBadge, {
                color: __props.getPositionBadge(__props.detailMember.position).color,
                variant: __props.getPositionBadge(__props.detailMember.position).variant,
                class: "rounded-2xl"
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`${ssrInterpolate(__props.getPositionBadge(__props.detailMember.position).text)}`);
                  } else {
                    return [
                      createTextVNode(toDisplayString(__props.getPositionBadge(__props.detailMember.position).text), 1)
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              if (__props.detailMember.level) {
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: "neutral",
                  variant: "subtle",
                  class: "rounded-2xl"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(` Level ${ssrInterpolate(__props.detailMember.level)}`);
                    } else {
                      return [
                        createTextVNode(" Level " + toDisplayString(__props.detailMember.level), 1)
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              if (__props.detailMember.package_name) {
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: "primary",
                  variant: "subtle",
                  class: "rounded-2xl"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(ssrRenderComponent(_component_UIcon, {
                        name: "i-lucide-badge",
                        class: "mr-1 size-3.5"
                      }, null, _parent3, _scopeId2));
                      _push3(` ${ssrInterpolate(__props.detailMember.package_name)}`);
                    } else {
                      return [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-badge",
                          class: "mr-1 size-3.5"
                        }),
                        createTextVNode(" " + toDisplayString(__props.detailMember.package_name), 1)
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              if (__props.detailMember.has_purchase) {
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: "primary",
                  variant: "soft",
                  class: "rounded-2xl"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(ssrRenderComponent(_component_UIcon, {
                        name: "i-lucide-shopping-bag",
                        class: "mr-1 size-3.5"
                      }, null, _parent3, _scopeId2));
                      _push3(` Sudah Belanja `);
                    } else {
                      return [
                        createVNode(_component_UIcon, {
                          name: "i-lucide-shopping-bag",
                          class: "mr-1 size-3.5"
                        }),
                        createTextVNode(" Sudah Belanja ")
                      ];
                    }
                  }),
                  _: 1
                }, _parent2, _scopeId));
              } else {
                _push2(`<!---->`);
              }
              _push2(`</div><div class="grid gap-3 sm:grid-cols-2"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UCard, {
                class: "rounded-2xl",
                ui: { root: "border border-default bg-elevated/20", body: "p-3" }
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<p class="text-xs font-bold uppercase tracking-wider text-muted"${_scopeId2}>Identitas</p><div class="mt-2 space-y-1.5 text-sm"${_scopeId2}><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Nama</span><span class="font-semibold text-highlighted"${_scopeId2}>${ssrInterpolate(__props.detailMember.name)}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Username</span><span class="font-semibold text-highlighted"${_scopeId2}>@${ssrInterpolate(__props.detailMember.username)}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Bergabung</span><span class="font-semibold text-highlighted"${_scopeId2}>${ssrInterpolate(__props.formatDate(__props.detailMember.joined_at))}</span></p></div>`);
                  } else {
                    return [
                      createVNode("p", { class: "text-xs font-bold uppercase tracking-wider text-muted" }, "Identitas"),
                      createVNode("div", { class: "mt-2 space-y-1.5 text-sm" }, [
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Nama"),
                          createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(__props.detailMember.name), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Username"),
                          createVNode("span", { class: "font-semibold text-highlighted" }, "@" + toDisplayString(__props.detailMember.username), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Bergabung"),
                          createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(__props.formatDate(__props.detailMember.joined_at)), 1)
                        ])
                      ])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(ssrRenderComponent(_component_UCard, {
                class: "rounded-2xl",
                ui: { root: "border border-default bg-elevated/20", body: "p-3" }
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<p class="text-xs font-bold uppercase tracking-wider text-muted"${_scopeId2}>Kontak</p><div class="mt-2 space-y-1.5 text-sm"${_scopeId2}><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Email</span><span class="font-semibold text-highlighted truncate"${_scopeId2}>${ssrInterpolate(__props.detailMember.email)}</span></p><p class="flex items-center justify-between gap-3"${_scopeId2}><span class="text-muted"${_scopeId2}>Telepon</span><span class="font-semibold text-highlighted"${_scopeId2}>${ssrInterpolate(__props.detailMember.phone ?? "-")}</span></p></div>`);
                  } else {
                    return [
                      createVNode("p", { class: "text-xs font-bold uppercase tracking-wider text-muted" }, "Kontak"),
                      createVNode("div", { class: "mt-2 space-y-1.5 text-sm" }, [
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Email"),
                          createVNode("span", { class: "font-semibold text-highlighted truncate" }, toDisplayString(__props.detailMember.email), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Telepon"),
                          createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(__props.detailMember.phone ?? "-"), 1)
                        ])
                      ])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
              _push2(ssrRenderComponent(_component_UCard, {
                class: "rounded-2xl",
                ui: { root: "border border-default bg-elevated/20", body: "p-3" }
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<div class="flex items-center justify-between gap-3"${_scopeId2}><p class="text-xs font-bold uppercase tracking-wider text-muted"${_scopeId2}>Ringkasan</p><p class="text-sm font-black tabular-nums text-primary"${_scopeId2}>${ssrInterpolate(__props.formatCurrency(__props.detailMember.omzet ?? 0))}</p></div><div class="mt-3 grid gap-2 sm:grid-cols-3"${_scopeId2}><div class="rounded-xl border border-default bg-elevated/10 px-3 py-2"${_scopeId2}><p class="text-[10px] font-extrabold uppercase tracking-wider text-muted"${_scopeId2}>Downline kiri</p><p class="mt-0.5 text-sm font-bold tabular-nums text-highlighted"${_scopeId2}>${ssrInterpolate(__props.detailMember.total_left ?? 0)}</p></div><div class="rounded-xl border border-default bg-elevated/10 px-3 py-2"${_scopeId2}><p class="text-[10px] font-extrabold uppercase tracking-wider text-muted"${_scopeId2}>Downline kanan</p><p class="mt-0.5 text-sm font-bold tabular-nums text-highlighted"${_scopeId2}>${ssrInterpolate(__props.detailMember.total_right ?? 0)}</p></div><div class="rounded-xl border border-default bg-elevated/10 px-3 py-2"${_scopeId2}><p class="text-[10px] font-extrabold uppercase tracking-wider text-muted"${_scopeId2}>Posisi</p><p class="mt-0.5 text-sm font-bold text-highlighted"${_scopeId2}>${ssrInterpolate(__props.detailMember.position ? __props.detailMember.position === "left" ? "Kiri" : "Kanan" : "Belum")}</p></div></div>`);
                  } else {
                    return [
                      createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                        createVNode("p", { class: "text-xs font-bold uppercase tracking-wider text-muted" }, "Ringkasan"),
                        createVNode("p", { class: "text-sm font-black tabular-nums text-primary" }, toDisplayString(__props.formatCurrency(__props.detailMember.omzet ?? 0)), 1)
                      ]),
                      createVNode("div", { class: "mt-3 grid gap-2 sm:grid-cols-3" }, [
                        createVNode("div", { class: "rounded-xl border border-default bg-elevated/10 px-3 py-2" }, [
                          createVNode("p", { class: "text-[10px] font-extrabold uppercase tracking-wider text-muted" }, "Downline kiri"),
                          createVNode("p", { class: "mt-0.5 text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(__props.detailMember.total_left ?? 0), 1)
                        ]),
                        createVNode("div", { class: "rounded-xl border border-default bg-elevated/10 px-3 py-2" }, [
                          createVNode("p", { class: "text-[10px] font-extrabold uppercase tracking-wider text-muted" }, "Downline kanan"),
                          createVNode("p", { class: "mt-0.5 text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(__props.detailMember.total_right ?? 0), 1)
                        ]),
                        createVNode("div", { class: "rounded-xl border border-default bg-elevated/10 px-3 py-2" }, [
                          createVNode("p", { class: "text-[10px] font-extrabold uppercase tracking-wider text-muted" }, "Posisi"),
                          createVNode("p", { class: "mt-0.5 text-sm font-bold text-highlighted" }, toDisplayString(__props.detailMember.position ? __props.detailMember.position === "left" ? "Kiri" : "Kanan" : "Belum"), 1)
                        ])
                      ])
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
              _push2(`</div>`);
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              __props.detailMember ? (openBlock(), createBlock("div", {
                key: 0,
                class: "space-y-4"
              }, [
                createVNode("div", { class: "flex flex-wrap items-center gap-2" }, [
                  createVNode(_component_UBadge, {
                    color: __props.memberState(__props.detailMember).color,
                    variant: "soft",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UIcon, {
                        name: __props.memberState(__props.detailMember).icon,
                        class: "mr-1 size-3.5"
                      }, null, 8, ["name"]),
                      createTextVNode(" " + toDisplayString(__props.memberState(__props.detailMember).text), 1)
                    ]),
                    _: 1
                  }, 8, ["color"]),
                  createVNode(_component_UBadge, {
                    color: __props.getPositionBadge(__props.detailMember.position).color,
                    variant: __props.getPositionBadge(__props.detailMember.position).variant,
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(toDisplayString(__props.getPositionBadge(__props.detailMember.position).text), 1)
                    ]),
                    _: 1
                  }, 8, ["color", "variant"]),
                  __props.detailMember.level ? (openBlock(), createBlock(_component_UBadge, {
                    key: 0,
                    color: "neutral",
                    variant: "subtle",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Level " + toDisplayString(__props.detailMember.level), 1)
                    ]),
                    _: 1
                  })) : createCommentVNode("", true),
                  __props.detailMember.package_name ? (openBlock(), createBlock(_component_UBadge, {
                    key: 1,
                    color: "primary",
                    variant: "subtle",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-badge",
                        class: "mr-1 size-3.5"
                      }),
                      createTextVNode(" " + toDisplayString(__props.detailMember.package_name), 1)
                    ]),
                    _: 1
                  })) : createCommentVNode("", true),
                  __props.detailMember.has_purchase ? (openBlock(), createBlock(_component_UBadge, {
                    key: 2,
                    color: "primary",
                    variant: "soft",
                    class: "rounded-2xl"
                  }, {
                    default: withCtx(() => [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-shopping-bag",
                        class: "mr-1 size-3.5"
                      }),
                      createTextVNode(" Sudah Belanja ")
                    ]),
                    _: 1
                  })) : createCommentVNode("", true)
                ]),
                createVNode("div", { class: "grid gap-3 sm:grid-cols-2" }, [
                  createVNode(_component_UCard, {
                    class: "rounded-2xl",
                    ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                  }, {
                    default: withCtx(() => [
                      createVNode("p", { class: "text-xs font-bold uppercase tracking-wider text-muted" }, "Identitas"),
                      createVNode("div", { class: "mt-2 space-y-1.5 text-sm" }, [
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Nama"),
                          createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(__props.detailMember.name), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Username"),
                          createVNode("span", { class: "font-semibold text-highlighted" }, "@" + toDisplayString(__props.detailMember.username), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Bergabung"),
                          createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(__props.formatDate(__props.detailMember.joined_at)), 1)
                        ])
                      ])
                    ]),
                    _: 1
                  }),
                  createVNode(_component_UCard, {
                    class: "rounded-2xl",
                    ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                  }, {
                    default: withCtx(() => [
                      createVNode("p", { class: "text-xs font-bold uppercase tracking-wider text-muted" }, "Kontak"),
                      createVNode("div", { class: "mt-2 space-y-1.5 text-sm" }, [
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Email"),
                          createVNode("span", { class: "font-semibold text-highlighted truncate" }, toDisplayString(__props.detailMember.email), 1)
                        ]),
                        createVNode("p", { class: "flex items-center justify-between gap-3" }, [
                          createVNode("span", { class: "text-muted" }, "Telepon"),
                          createVNode("span", { class: "font-semibold text-highlighted" }, toDisplayString(__props.detailMember.phone ?? "-"), 1)
                        ])
                      ])
                    ]),
                    _: 1
                  })
                ]),
                createVNode(_component_UCard, {
                  class: "rounded-2xl",
                  ui: { root: "border border-default bg-elevated/20", body: "p-3" }
                }, {
                  default: withCtx(() => [
                    createVNode("div", { class: "flex items-center justify-between gap-3" }, [
                      createVNode("p", { class: "text-xs font-bold uppercase tracking-wider text-muted" }, "Ringkasan"),
                      createVNode("p", { class: "text-sm font-black tabular-nums text-primary" }, toDisplayString(__props.formatCurrency(__props.detailMember.omzet ?? 0)), 1)
                    ]),
                    createVNode("div", { class: "mt-3 grid gap-2 sm:grid-cols-3" }, [
                      createVNode("div", { class: "rounded-xl border border-default bg-elevated/10 px-3 py-2" }, [
                        createVNode("p", { class: "text-[10px] font-extrabold uppercase tracking-wider text-muted" }, "Downline kiri"),
                        createVNode("p", { class: "mt-0.5 text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(__props.detailMember.total_left ?? 0), 1)
                      ]),
                      createVNode("div", { class: "rounded-xl border border-default bg-elevated/10 px-3 py-2" }, [
                        createVNode("p", { class: "text-[10px] font-extrabold uppercase tracking-wider text-muted" }, "Downline kanan"),
                        createVNode("p", { class: "mt-0.5 text-sm font-bold tabular-nums text-highlighted" }, toDisplayString(__props.detailMember.total_right ?? 0), 1)
                      ]),
                      createVNode("div", { class: "rounded-xl border border-default bg-elevated/10 px-3 py-2" }, [
                        createVNode("p", { class: "text-[10px] font-extrabold uppercase tracking-wider text-muted" }, "Posisi"),
                        createVNode("p", { class: "mt-0.5 text-sm font-bold text-highlighted" }, toDisplayString(__props.detailMember.position ? __props.detailMember.position === "left" ? "Kiri" : "Kanan" : "Belum"), 1)
                      ])
                    ])
                  ]),
                  _: 1
                })
              ])) : createCommentVNode("", true)
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              onClick: closeModal
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tutup `);
                } else {
                  return [
                    createTextVNode(" Tutup ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            if (__props.detailMember && __props.activeTab === "passive" && (!__props.hasLeft || !__props.hasRight)) {
              _push2(ssrRenderComponent(_component_UButton, {
                color: "primary",
                variant: "soft",
                class: "rounded-xl",
                icon: "i-lucide-git-branch",
                onClick: placeMember
              }, {
                default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(` Tempatkan Member `);
                  } else {
                    return [
                      createTextVNode(" Tempatkan Member ")
                    ];
                  }
                }),
                _: 1
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-xl",
                  onClick: closeModal
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Tutup ")
                  ]),
                  _: 1
                }),
                __props.detailMember && __props.activeTab === "passive" && (!__props.hasLeft || !__props.hasRight) ? (openBlock(), createBlock(_component_UButton, {
                  key: 0,
                  color: "primary",
                  variant: "soft",
                  class: "rounded-xl",
                  icon: "i-lucide-git-branch",
                  onClick: placeMember
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Tempatkan Member ")
                  ]),
                  _: 1
                })) : createCommentVNode("", true)
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/mitra/MitraDetailModal.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "MitraPlacementModal",
  __ssrInlineRender: true,
  props: {
    open: { type: Boolean },
    selectedMember: { default: null },
    selectedPosition: { default: null },
    hasLeft: { type: Boolean },
    hasRight: { type: Boolean },
    processing: { type: Boolean },
    uplineId: {}
  },
  emits: ["update:open", "update:selectedPosition", "close", "submit"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function closeModal() {
      emit("close");
      emit("update:open", false);
    }
    function submitPlacement() {
      emit("submit");
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$d;
      const _component_UAlert = _sfc_main$e;
      const _component_UButton = _sfc_main$a;
      const _component_UIcon = _sfc_main$7;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: __props.open,
        title: "Tempatkan Member ke Binary Tree",
        description: __props.selectedMember ? `Pilih posisi untuk menempatkan ${__props.selectedMember.name} di jaringan binary tree Anda.` : "",
        ui: { overlay: "fixed inset-0 z-[9998] bg-black/50 backdrop-blur-sm", content: "fixed z-[9999] w-full max-w-lg" },
        "onUpdate:open": (value) => emit("update:open", value)
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (__props.selectedMember) {
              _push2(ssrRenderComponent(_component_UAlert, {
                icon: "i-lucide-info",
                color: "neutral",
                variant: "subtle",
                class: "rounded-2xl",
                title: `Menempatkan: ${__props.selectedMember.name}`,
                description: `Upline ID: ${__props.uplineId}`
              }, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="mt-4 grid grid-cols-2 gap-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              block: "",
              size: "lg",
              color: "neutral",
              variant: "outline",
              class: "rounded-2xl py-6",
              disabled: __props.hasLeft,
              ui: { base: __props.selectedPosition === "left" ? "ring-2 ring-primary" : "" },
              onClick: ($event) => !__props.hasLeft && emit("update:selectedPosition", "left")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col items-center gap-2"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-git-branch",
                    class: "size-7 rotate-90"
                  }, null, _parent3, _scopeId2));
                  _push3(`<div class="text-sm font-semibold"${_scopeId2}>Posisi Kiri</div><div class="text-xs text-muted"${_scopeId2}>${ssrInterpolate(__props.hasLeft ? "Sudah Terisi" : "Left Position")}</div></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col items-center gap-2" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-git-branch",
                        class: "size-7 rotate-90"
                      }),
                      createVNode("div", { class: "text-sm font-semibold" }, "Posisi Kiri"),
                      createVNode("div", { class: "text-xs text-muted" }, toDisplayString(__props.hasLeft ? "Sudah Terisi" : "Left Position"), 1)
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              block: "",
              size: "lg",
              color: "neutral",
              variant: "outline",
              class: "rounded-2xl py-6",
              disabled: __props.hasRight,
              ui: { base: __props.selectedPosition === "right" ? "ring-2 ring-primary" : "" },
              onClick: ($event) => !__props.hasRight && emit("update:selectedPosition", "right")
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(`<div class="flex flex-col items-center gap-2"${_scopeId2}>`);
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-git-branch",
                    class: "size-7 -rotate-90"
                  }, null, _parent3, _scopeId2));
                  _push3(`<div class="text-sm font-semibold"${_scopeId2}>Posisi Kanan</div><div class="text-xs text-muted"${_scopeId2}>${ssrInterpolate(__props.hasRight ? "Sudah Terisi" : "Right Position")}</div></div>`);
                } else {
                  return [
                    createVNode("div", { class: "flex flex-col items-center gap-2" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-git-branch",
                        class: "size-7 -rotate-90"
                      }),
                      createVNode("div", { class: "text-sm font-semibold" }, "Posisi Kanan"),
                      createVNode("div", { class: "text-xs text-muted" }, toDisplayString(__props.hasRight ? "Sudah Terisi" : "Right Position"), 1)
                    ])
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            if (__props.selectedPosition) {
              _push2(ssrRenderComponent(_component_UAlert, {
                class: "mt-4 rounded-2xl",
                color: "primary",
                variant: "soft",
                icon: "i-lucide-check",
                title: `Posisi terpilih: ${__props.selectedPosition === "left" ? "Kiri" : "Kanan"}`,
                description: "Klik tombol Tempatkan untuk menyimpan perubahan."
              }, null, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              __props.selectedMember ? (openBlock(), createBlock(_component_UAlert, {
                key: 0,
                icon: "i-lucide-info",
                color: "neutral",
                variant: "subtle",
                class: "rounded-2xl",
                title: `Menempatkan: ${__props.selectedMember.name}`,
                description: `Upline ID: ${__props.uplineId}`
              }, null, 8, ["title", "description"])) : createCommentVNode("", true),
              createVNode("div", { class: "mt-4 grid grid-cols-2 gap-3" }, [
                createVNode(_component_UButton, {
                  block: "",
                  size: "lg",
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-2xl py-6",
                  disabled: __props.hasLeft,
                  ui: { base: __props.selectedPosition === "left" ? "ring-2 ring-primary" : "" },
                  onClick: ($event) => !__props.hasLeft && emit("update:selectedPosition", "left")
                }, {
                  default: withCtx(() => [
                    createVNode("div", { class: "flex flex-col items-center gap-2" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-git-branch",
                        class: "size-7 rotate-90"
                      }),
                      createVNode("div", { class: "text-sm font-semibold" }, "Posisi Kiri"),
                      createVNode("div", { class: "text-xs text-muted" }, toDisplayString(__props.hasLeft ? "Sudah Terisi" : "Left Position"), 1)
                    ])
                  ]),
                  _: 1
                }, 8, ["disabled", "ui", "onClick"]),
                createVNode(_component_UButton, {
                  block: "",
                  size: "lg",
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-2xl py-6",
                  disabled: __props.hasRight,
                  ui: { base: __props.selectedPosition === "right" ? "ring-2 ring-primary" : "" },
                  onClick: ($event) => !__props.hasRight && emit("update:selectedPosition", "right")
                }, {
                  default: withCtx(() => [
                    createVNode("div", { class: "flex flex-col items-center gap-2" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-git-branch",
                        class: "size-7 -rotate-90"
                      }),
                      createVNode("div", { class: "text-sm font-semibold" }, "Posisi Kanan"),
                      createVNode("div", { class: "text-xs text-muted" }, toDisplayString(__props.hasRight ? "Sudah Terisi" : "Right Position"), 1)
                    ])
                  ]),
                  _: 1
                }, 8, ["disabled", "ui", "onClick"])
              ]),
              __props.selectedPosition ? (openBlock(), createBlock(_component_UAlert, {
                key: 1,
                class: "mt-4 rounded-2xl",
                color: "primary",
                variant: "soft",
                icon: "i-lucide-check",
                title: `Posisi terpilih: ${__props.selectedPosition === "left" ? "Kiri" : "Kanan"}`,
                description: "Klik tombol Tempatkan untuk menyimpan perubahan."
              }, null, 8, ["title"])) : createCommentVNode("", true)
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex justify-end gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              disabled: __props.processing,
              onClick: closeModal
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Batal `);
                } else {
                  return [
                    createTextVNode(" Batal ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_UButton, {
              color: "primary",
              class: "rounded-xl",
              disabled: !__props.selectedPosition || __props.processing,
              loading: __props.processing,
              onClick: submitPlacement
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Tempatkan `);
                } else {
                  return [
                    createTextVNode(" Tempatkan ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "flex justify-end gap-2" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-xl",
                  disabled: __props.processing,
                  onClick: closeModal
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Batal ")
                  ]),
                  _: 1
                }, 8, ["disabled"]),
                createVNode(_component_UButton, {
                  color: "primary",
                  class: "rounded-xl",
                  disabled: !__props.selectedPosition || __props.processing,
                  loading: __props.processing,
                  onClick: submitPlacement
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Tempatkan ")
                  ]),
                  _: 1
                }, 8, ["disabled", "loading"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/mitra/MitraPlacementModal.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Mitra",
  __ssrInlineRender: true,
  props: {
    activeMembers: { default: () => [] },
    passiveMembers: { default: () => [] },
    prospectMembers: { default: () => [] },
    hasLeft: { type: Boolean, default: false },
    hasRight: { type: Boolean, default: false },
    currentCustomerId: { default: null }
  },
  setup(__props) {
    const props = __props;
    const {
      activeTab,
      q,
      totalMembers,
      tabs,
      filteredMembers,
      hintText,
      formatDate,
      formatCurrency,
      getPositionBadge,
      tabStatusBadge,
      memberState,
      isDetailOpen,
      detailMember,
      openDetail,
      closeDetail,
      showPlacementDialog,
      selectedMember,
      selectedPosition,
      placementForm,
      hasLeft,
      hasRight,
      openPlacementDialog,
      closePlacementDialog,
      placeToBinaryTree,
      openPlacementFromDetail
    } = useDashboardMitra({
      activeMembers: computed(() => props.activeMembers),
      passiveMembers: computed(() => props.passiveMembers),
      prospectMembers: computed(() => props.prospectMembers),
      hasLeft: computed(() => props.hasLeft),
      hasRight: computed(() => props.hasRight),
      currentCustomerId: computed(() => props.currentCustomerId)
    });
    function onSelectedPositionChange(value) {
      selectedPosition.value = value;
    }
    function onActiveTabChange(value) {
      activeTab.value = value;
    }
    function onSearchQueryChange(value) {
      q.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$9;
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_component_UCard, { class: "rounded-3xl overflow-hidden" }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$6, {
              "total-members": unref(totalMembers),
              "active-tab": unref(activeTab),
              "tab-badge": unref(tabStatusBadge)(unref(activeTab))
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$6, {
                "total-members": unref(totalMembers),
                "active-tab": unref(activeTab),
                "tab-badge": unref(tabStatusBadge)(unref(activeTab))
              }, null, 8, ["total-members", "active-tab", "tab-badge"])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}>`);
            _push2(ssrRenderComponent(_sfc_main$5, {
              tabs: unref(tabs),
              "active-tab": unref(activeTab),
              "hint-text": unref(hintText),
              "search-query": unref(q),
              "onUpdate:activeTab": onActiveTabChange,
              "onUpdate:searchQuery": onSearchQueryChange
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_sfc_main$3, {
              members: unref(filteredMembers),
              "active-tab": unref(activeTab),
              "has-left": unref(hasLeft),
              "has-right": unref(hasRight),
              "format-date": unref(formatDate),
              "format-currency": unref(formatCurrency),
              "get-position-badge": unref(getPositionBadge),
              "tab-status-badge": unref(tabStatusBadge),
              onOpenDetail: unref(openDetail),
              onPlaceMember: unref(openPlacementDialog)
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode(_sfc_main$5, {
                  tabs: unref(tabs),
                  "active-tab": unref(activeTab),
                  "hint-text": unref(hintText),
                  "search-query": unref(q),
                  "onUpdate:activeTab": onActiveTabChange,
                  "onUpdate:searchQuery": onSearchQueryChange
                }, null, 8, ["tabs", "active-tab", "hint-text", "search-query"]),
                createVNode(_sfc_main$3, {
                  members: unref(filteredMembers),
                  "active-tab": unref(activeTab),
                  "has-left": unref(hasLeft),
                  "has-right": unref(hasRight),
                  "format-date": unref(formatDate),
                  "format-currency": unref(formatCurrency),
                  "get-position-badge": unref(getPositionBadge),
                  "tab-status-badge": unref(tabStatusBadge),
                  onOpenDetail: unref(openDetail),
                  onPlaceMember: unref(openPlacementDialog)
                }, null, 8, ["members", "active-tab", "has-left", "has-right", "format-date", "format-currency", "get-position-badge", "tab-status-badge", "onOpenDetail", "onPlaceMember"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_sfc_main$2, {
        open: unref(isDetailOpen),
        "onUpdate:open": ($event) => isRef(isDetailOpen) ? isDetailOpen.value = $event : null,
        "detail-member": unref(detailMember),
        "active-tab": unref(activeTab),
        "has-left": unref(hasLeft),
        "has-right": unref(hasRight),
        "format-date": unref(formatDate),
        "format-currency": unref(formatCurrency),
        "get-position-badge": unref(getPositionBadge),
        "tab-status-badge": unref(tabStatusBadge),
        "member-state": unref(memberState),
        onClose: unref(closeDetail),
        onPlaceMember: unref(openPlacementFromDetail)
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$1, {
        open: unref(showPlacementDialog),
        "onUpdate:open": ($event) => isRef(showPlacementDialog) ? showPlacementDialog.value = $event : null,
        "selected-member": unref(selectedMember),
        "selected-position": unref(selectedPosition),
        "has-left": unref(hasLeft),
        "has-right": unref(hasRight),
        processing: unref(placementForm).processing,
        "upline-id": unref(placementForm).upline_id,
        "onUpdate:selectedPosition": onSelectedPositionChange,
        onClose: unref(closePlacementDialog),
        onSubmit: unref(placeToBinaryTree)
      }, null, _parent));
      _push(`<!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Mitra.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
