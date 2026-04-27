import { _ as _sfc_main$c } from "./Card-CvchAxCK.js";
import { ref, computed, watch, defineComponent, mergeProps, withCtx, createTextVNode, createVNode, useSSRContext, toDisplayString, openBlock, createBlock, Fragment, renderList, resolveComponent, onMounted, nextTick, onBeforeUnmount, unref } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList, ssrRenderClass, ssrRenderStyle } from "vue/server-renderer";
import { useForm, router } from "@inertiajs/vue3";
import { u as useToast } from "./useToast-CTuSIf9f.js";
import { _ as _sfc_main$8 } from "./Input-DFvIE7JC.js";
import { _ as _sfc_main$7 } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$b } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$a } from "./Alert-BRMLOsUb.js";
import { _ as _sfc_main$9 } from "./Modal-BB5lXp8z.js";
import { _ as _sfc_main$d } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$e } from "./Empty-20F1kDsa.js";
import "reka-ui";
import "@vueuse/core";
import "defu";
import "ufo";
import "./usePortal-EQErrF6h.js";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
function useNetworkPlacement(passiveMembers, currentCustomerId) {
  const toast = useToast();
  const showPlacementDialog = ref(false);
  const selectedUplineId = ref(null);
  const selectedPosition = ref(null);
  const selectedMember = ref(null);
  const memberSearchQuery = ref("");
  const placementForm = useForm({
    member_id: 0,
    upline_id: 0,
    position: ""
  });
  const filteredPassiveMembers = computed(() => {
    const query = memberSearchQuery.value.trim().toLowerCase();
    if (query === "") {
      return passiveMembers.value;
    }
    return passiveMembers.value.filter((member) => {
      const haystack = `${member.name} ${member.email} ${member.phone ?? ""} ${member.username ?? ""}`.toLowerCase();
      return haystack.includes(query);
    });
  });
  function applyPlacementContext(payload) {
    selectedUplineId.value = payload.uplineId;
    selectedPosition.value = payload.position;
    selectedMember.value = null;
    memberSearchQuery.value = "";
    showPlacementDialog.value = true;
  }
  function openPlacementDialog(payload) {
    if (!currentCustomerId.value) {
      toast.add({
        title: "Upline tidak valid",
        description: "Data customer login tidak tersedia.",
        color: "error",
        icon: "i-lucide-x-circle"
      });
      return;
    }
    if (passiveMembers.value.length === 0) {
      router.reload({
        only: ["passiveMembers"],
        onSuccess: () => {
          if (passiveMembers.value.length === 0) {
            toast.add({
              title: "Member pasif belum tersedia",
              description: "Tidak ada member status pasif yang bisa ditempatkan saat ini.",
              color: "warning",
              icon: "i-lucide-alert-circle"
            });
            return;
          }
          applyPlacementContext(payload);
        }
      });
      return;
    }
    applyPlacementContext(payload);
  }
  function closePlacementDialog() {
    showPlacementDialog.value = false;
    selectedUplineId.value = null;
    selectedPosition.value = null;
    selectedMember.value = null;
    memberSearchQuery.value = "";
    placementForm.reset();
  }
  function selectMember(member) {
    selectedMember.value = member;
  }
  function placeMemberToBinaryTree() {
    if (!selectedMember.value || !selectedPosition.value || !selectedUplineId.value) {
      toast.add({
        title: "Data belum lengkap",
        description: "Pilih member dan posisi placement terlebih dahulu.",
        color: "warning",
        icon: "i-lucide-alert-circle"
      });
      return;
    }
    placementForm.member_id = selectedMember.value.id;
    placementForm.upline_id = selectedUplineId.value;
    placementForm.position = selectedPosition.value;
    placementForm.post("/mlm/place-member", {
      preserveScroll: true,
      onSuccess: () => {
        toast.add({
          title: "Placement berhasil",
          description: `${selectedMember.value?.name} ditempatkan ke posisi ${selectedPosition.value === "left" ? "kiri" : "kanan"}.`,
          color: "success",
          icon: "i-lucide-check-circle-2"
        });
        closePlacementDialog();
        router.reload({
          only: ["binaryTree", "networkTreeStats", "activeMembers", "passiveMembers", "prospectMembers", "hasLeft", "hasRight"]
        });
      },
      onError: (errors) => {
        const errorMessage = typeof errors.error === "string" ? errors.error : "Gagal memproses placement member.";
        toast.add({
          title: "Placement gagal",
          description: errorMessage,
          color: "error",
          icon: "i-lucide-x-circle"
        });
      }
    });
  }
  return {
    showPlacementDialog,
    selectedUplineId,
    selectedPosition,
    selectedMember,
    memberSearchQuery,
    placementForm,
    filteredPassiveMembers,
    openPlacementDialog,
    closePlacementDialog,
    selectMember,
    placeMemberToBinaryTree
  };
}
function normalizeSearchText(value) {
  return value.toLowerCase().replace(/\s+/g, " ").trim();
}
function flattenTree(node) {
  if (!node) {
    return [];
  }
  return [
    {
      id: node.id,
      name: node.name,
      username: node.username,
      email: node.email ?? "-",
      package_name: node.package_name ?? "Member",
      level: node.level
    },
    ...flattenTree(node.left),
    ...flattenTree(node.right)
  ];
}
function collectNodeIds(node) {
  if (!node) {
    return [];
  }
  return [node.id, ...collectNodeIds(node.left), ...collectNodeIds(node.right)];
}
function resolveDefaultZoom() {
  if (typeof window === "undefined") {
    return 0.8;
  }
  return window.matchMedia("(max-width: 640px)").matches ? 0.5 : 0.8;
}
function useNetworkTree(binaryTree, networkTreeStats, currentCustomerId, networkTreeRootId) {
  const treeSearchQuery = ref("");
  const showTreeSearchResults = ref(false);
  const defaultZoom = resolveDefaultZoom();
  const zoom = ref(defaultZoom);
  const minZoom = 0.15;
  const maxZoom = 1.6;
  const zoomStep = 0.08;
  const collapsedIds = ref([]);
  const rootTree = computed(() => binaryTree.value ?? null);
  const currentTree = computed(() => rootTree.value);
  const isViewingMemberTree = computed(() => {
    const activeTreeRootId = networkTreeRootId.value;
    const authCustomerId = currentCustomerId.value;
    if (!activeTreeRootId || !authCustomerId) {
      return false;
    }
    return activeTreeRootId !== authCustomerId;
  });
  const selectedMemberForTree = computed(() => currentTree.value);
  const allMemberSearchData = computed(() => flattenTree(rootTree.value));
  const maxLoadedLevel = computed(() => {
    const levels = allMemberSearchData.value.map((item) => item.level);
    if (levels.length === 0) {
      return 0;
    }
    return Math.max(...levels);
  });
  const currentStats = computed(() => {
    if (!currentTree.value) {
      return {
        totalDownlines: 0,
        totalLeft: 0,
        totalRight: 0
      };
    }
    if (!isViewingMemberTree.value && networkTreeStats.value) {
      return {
        totalDownlines: networkTreeStats.value.total_downlines,
        totalLeft: networkTreeStats.value.left_count,
        totalRight: networkTreeStats.value.right_count
      };
    }
    return {
      totalDownlines: Math.max(0, Number(currentTree.value.total_left ?? 0) + Number(currentTree.value.total_right ?? 0)),
      totalLeft: Math.max(0, Number(currentTree.value.total_left ?? 0)),
      totalRight: Math.max(0, Number(currentTree.value.total_right ?? 0))
    };
  });
  const treeSearchResults = computed(() => {
    const rawQuery = normalizeSearchText(treeSearchQuery.value);
    const usernameQuery = rawQuery.replace(/^@+/, "");
    if (usernameQuery.length < 2) {
      return [];
    }
    return allMemberSearchData.value.filter((member) => {
      const haystack = normalizeSearchText(
        `${member.name} ${member.username} @${member.username} ${member.email} ${member.package_name}`
      );
      return haystack.includes(rawQuery) || haystack.includes(usernameQuery);
    }).slice(0, 12);
  });
  function toggleNode(memberId) {
    if (collapsedIds.value.includes(memberId)) {
      collapsedIds.value = collapsedIds.value.filter((id) => id !== memberId);
      return;
    }
    collapsedIds.value = [...collapsedIds.value, memberId];
  }
  function expandAll() {
    collapsedIds.value = [];
  }
  function collapseAll() {
    if (!currentTree.value) {
      collapsedIds.value = [];
      return;
    }
    collapsedIds.value = collectNodeIds(currentTree.value).filter((id) => id !== currentTree.value?.id);
  }
  watch(
    currentTree,
    (tree, previousTree) => {
      if (!tree) {
        collapsedIds.value = [];
        return;
      }
      if (previousTree && previousTree.id === tree.id) {
        return;
      }
      collapsedIds.value = [];
    },
    { immediate: true }
  );
  function handleZoomIn() {
    zoom.value = Math.min(maxZoom, Number((zoom.value + zoomStep).toFixed(2)));
  }
  function handleZoomOut() {
    zoom.value = Math.max(minZoom, Number((zoom.value - zoomStep).toFixed(2)));
  }
  function handleResetZoom() {
    zoom.value = defaultZoom;
  }
  function handleTreeSearchInput() {
    showTreeSearchResults.value = treeSearchQuery.value.trim().length >= 2;
  }
  function selectTreeSearchResult(memberId) {
    if (memberId <= 0) {
      return;
    }
    treeSearchQuery.value = "";
    showTreeSearchResults.value = false;
  }
  function handleTreeSearchBlur() {
    window.setTimeout(() => {
      showTreeSearchResults.value = false;
    }, 120);
  }
  return {
    treeSearchQuery,
    showTreeSearchResults,
    zoom,
    collapsedIds,
    rootTree,
    currentTree,
    isViewingMemberTree,
    selectedMemberForTree,
    allMemberSearchData,
    maxLoadedLevel,
    currentStats,
    treeSearchResults,
    toggleNode,
    expandAll,
    collapseAll,
    handleZoomIn,
    handleZoomOut,
    handleResetZoom,
    handleTreeSearchInput,
    selectTreeSearchResult,
    handleTreeSearchBlur
  };
}
const _sfc_main$6 = /* @__PURE__ */ defineComponent({
  __name: "NetworkHeaderControls",
  __ssrInlineRender: true,
  props: {
    isViewingMemberTree: { type: Boolean },
    selectedMemberForTree: { default: null },
    maxLoadedLevel: {},
    zoom: { default: 1 },
    treeSearchQuery: { default: "" },
    showTreeSearchResults: { type: Boolean, default: false },
    treeSearchResults: { default: () => [] }
  },
  emits: ["back", "update:treeSearchQuery", "searchInput", "searchFocus", "searchBlur", "selectSearchResult", "expandAll", "collapseAll", "zoomOut", "zoomIn", "resetZoom"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function handleTreeSearchModelUpdate(value) {
      emit("update:treeSearchQuery", String(value ?? ""));
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$7;
      const _component_UInput = _sfc_main$8;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between" }, _attrs))}><div class="flex items-center gap-2">`);
      if (__props.isViewingMemberTree) {
        _push(ssrRenderComponent(_component_UButton, {
          color: "neutral",
          variant: "outline",
          size: "xs",
          icon: "i-lucide-arrow-left",
          class: "rounded-xl",
          onClick: ($event) => emit("back")
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` Kembali `);
            } else {
              return [
                createTextVNode(" Kembali ")
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`<div><p class="text-xs font-semibold text-highlighted sm:text-sm">${ssrInterpolate(__props.isViewingMemberTree && __props.selectedMemberForTree ? `Jaringan ${__props.selectedMemberForTree.name}` : "Struktur Binary Tree MLM")}</p><p class="text-[11px] text-muted">${ssrInterpolate(__props.isViewingMemberTree && __props.selectedMemberForTree ? `@${__props.selectedMemberForTree.username}` : `Data model customer • depth termuat hingga level ${__props.maxLoadedLevel}`)}</p></div></div><div class="flex flex-wrap items-center gap-1.5"><div class="relative w-full sm:w-auto">`);
      _push(ssrRenderComponent(_component_UInput, {
        "model-value": __props.treeSearchQuery,
        icon: "i-lucide-search",
        placeholder: "Cari member berdasakan username/nama...",
        size: "xs",
        class: "w-full sm:w-52",
        "onUpdate:modelValue": handleTreeSearchModelUpdate,
        onInput: ($event) => emit("searchInput"),
        onFocus: ($event) => emit("searchFocus"),
        onBlur: ($event) => emit("searchBlur")
      }, null, _parent));
      if (__props.showTreeSearchResults) {
        _push(`<div class="absolute left-1/2 top-full z-20 mt-1 w-64 max-w-[calc(100vw-1rem)] -translate-x-1/2 overflow-hidden rounded-xl border border-default bg-default shadow-lg sm:left-auto sm:right-0 sm:max-w-none sm:translate-x-0">`);
        if (__props.treeSearchResults.length === 0) {
          _push(`<div class="p-3 text-sm text-muted"> Tidak ditemukan member. </div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`<!--[-->`);
        ssrRenderList(__props.treeSearchResults, (member) => {
          _push(`<button type="button" class="w-full border-b border-default px-3 py-2 text-left transition hover:bg-elevated/70 last:border-b-0"><p class="truncate text-sm font-medium text-highlighted">${ssrInterpolate(member.name)}</p><p class="truncate text-xs text-muted">@${ssrInterpolate(member.username)} • ${ssrInterpolate(member.package_name)}</p></button>`);
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><span class="inline-flex items-center rounded-lg border border-default px-2 py-1 text-[10px] font-semibold text-muted">${ssrInterpolate(Math.round(__props.zoom * 100))}% </span>`);
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        size: "xs",
        icon: "i-lucide-unfold-vertical",
        class: "rounded-lg",
        onClick: ($event) => emit("expandAll")
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<span class="hidden sm:inline"${_scopeId}>Expand</span>`);
          } else {
            return [
              createVNode("span", { class: "hidden sm:inline" }, "Expand")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        size: "xs",
        icon: "i-lucide-fold-vertical",
        class: "rounded-lg",
        onClick: ($event) => emit("collapseAll")
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<span class="hidden sm:inline"${_scopeId}>Collapse</span>`);
          } else {
            return [
              createVNode("span", { class: "hidden sm:inline" }, "Collapse")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        size: "xs",
        icon: "i-lucide-zoom-out",
        class: "rounded-lg",
        onClick: ($event) => emit("zoomOut")
      }, null, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "outline",
        size: "xs",
        icon: "i-lucide-zoom-in",
        class: "rounded-lg",
        onClick: ($event) => emit("zoomIn")
      }, null, _parent));
      _push(ssrRenderComponent(_component_UButton, {
        color: "neutral",
        variant: "ghost",
        size: "xs",
        icon: "i-lucide-rotate-ccw",
        class: "rounded-lg",
        onClick: ($event) => emit("resetZoom")
      }, null, _parent));
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkHeaderControls.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "NetworkPlacementModal",
  __ssrInlineRender: true,
  props: {
    open: { type: Boolean, default: false },
    selectedPosition: { default: null },
    selectedUplineId: { default: null },
    memberSearchQuery: { default: "" },
    filteredPassiveMembers: { default: () => [] },
    selectedMemberId: { default: null },
    processing: { type: Boolean, default: false }
  },
  emits: ["update:open", "update:memberSearchQuery", "selectMember", "submit", "cancel"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const modalOpen = computed({
      get: () => props.open,
      set: (value) => {
        emit("update:open", value);
        if (!value) {
          emit("cancel");
        }
      }
    });
    function handleMemberSearchModelUpdate(value) {
      emit("update:memberSearchQuery", String(value ?? ""));
    }
    function formatDate(value) {
      if (!value) {
        return "-";
      }
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return value;
      }
      return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      }).format(date);
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UModal = _sfc_main$9;
      const _component_UAlert = _sfc_main$a;
      const _component_UInput = _sfc_main$8;
      const _component_UBadge = _sfc_main$b;
      const _component_UButton = _sfc_main$7;
      _push(ssrRenderComponent(_component_UModal, mergeProps({
        open: modalOpen.value,
        "onUpdate:open": ($event) => modalOpen.value = $event,
        portal: true,
        title: "Tempatkan Member ke Binary Tree",
        description: __props.selectedPosition ? `Posisi terpilih: ${__props.selectedPosition === "left" ? "Kiri" : "Kanan"}` : "",
        ui: { overlay: "fixed inset-0 z-[9998] bg-black/50 backdrop-blur-sm", content: "fixed z-[9999] w-full max-w-2xl" }
      }, _attrs), {
        body: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UAlert, {
              color: "neutral",
              variant: "subtle",
              icon: "i-lucide-info",
              class: "rounded-2xl",
              title: `Upline Node ID: ${__props.selectedUplineId ?? "-"}`,
              description: "Pilih satu member pasif untuk placement pada node ini."
            }, null, _parent2, _scopeId));
            _push2(`<div class="mt-4 space-y-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UInput, {
              "model-value": __props.memberSearchQuery,
              icon: "i-lucide-search",
              placeholder: "Cari nama, email, atau telepon...",
              size: "sm",
              class: "w-full",
              "onUpdate:modelValue": handleMemberSearchModelUpdate
            }, null, _parent2, _scopeId));
            if (__props.filteredPassiveMembers.length === 0) {
              _push2(`<div class="rounded-2xl border border-dashed border-default p-6 text-center text-sm text-muted"${_scopeId}> Tidak ada member pasif yang cocok. </div>`);
            } else {
              _push2(`<div class="max-h-80 space-y-2 overflow-auto pr-1"${_scopeId}><!--[-->`);
              ssrRenderList(__props.filteredPassiveMembers, (member) => {
                _push2(`<button type="button" class="${ssrRenderClass([__props.selectedMemberId === member.id ? "border-primary bg-primary/10" : "border-default hover:bg-elevated/60", "w-full rounded-2xl border px-3 py-2 text-left transition"])}"${_scopeId}><div class="flex items-start justify-between gap-2"${_scopeId}><div class="min-w-0"${_scopeId}><p class="truncate text-sm font-semibold text-highlighted"${_scopeId}>${ssrInterpolate(member.name)}</p><p class="truncate text-xs text-muted"${_scopeId}>${ssrInterpolate(member.email)}</p><p class="truncate text-xs text-muted"${_scopeId}>${ssrInterpolate(member.phone ?? "-")}</p><p class="text-[11px] text-muted"${_scopeId}>Join: ${ssrInterpolate(formatDate(member.joined_at))}</p></div>`);
                _push2(ssrRenderComponent(_component_UBadge, {
                  color: member.has_purchase ? "success" : "neutral",
                  variant: member.has_purchase ? "soft" : "subtle",
                  size: "xs",
                  class: "rounded-full"
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`${ssrInterpolate(member.has_purchase ? "Purchase" : "No Purchase")}`);
                    } else {
                      return [
                        createTextVNode(toDisplayString(member.has_purchase ? "Purchase" : "No Purchase"), 1)
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
                _push2(`</div></button>`);
              });
              _push2(`<!--]--></div>`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode(_component_UAlert, {
                color: "neutral",
                variant: "subtle",
                icon: "i-lucide-info",
                class: "rounded-2xl",
                title: `Upline Node ID: ${__props.selectedUplineId ?? "-"}`,
                description: "Pilih satu member pasif untuk placement pada node ini."
              }, null, 8, ["title"]),
              createVNode("div", { class: "mt-4 space-y-3" }, [
                createVNode(_component_UInput, {
                  "model-value": __props.memberSearchQuery,
                  icon: "i-lucide-search",
                  placeholder: "Cari nama, email, atau telepon...",
                  size: "sm",
                  class: "w-full",
                  "onUpdate:modelValue": handleMemberSearchModelUpdate
                }, null, 8, ["model-value"]),
                __props.filteredPassiveMembers.length === 0 ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-dashed border-default p-6 text-center text-sm text-muted"
                }, " Tidak ada member pasif yang cocok. ")) : (openBlock(), createBlock("div", {
                  key: 1,
                  class: "max-h-80 space-y-2 overflow-auto pr-1"
                }, [
                  (openBlock(true), createBlock(Fragment, null, renderList(__props.filteredPassiveMembers, (member) => {
                    return openBlock(), createBlock("button", {
                      key: member.id,
                      type: "button",
                      class: ["w-full rounded-2xl border px-3 py-2 text-left transition", __props.selectedMemberId === member.id ? "border-primary bg-primary/10" : "border-default hover:bg-elevated/60"],
                      onClick: ($event) => emit("selectMember", member)
                    }, [
                      createVNode("div", { class: "flex items-start justify-between gap-2" }, [
                        createVNode("div", { class: "min-w-0" }, [
                          createVNode("p", { class: "truncate text-sm font-semibold text-highlighted" }, toDisplayString(member.name), 1),
                          createVNode("p", { class: "truncate text-xs text-muted" }, toDisplayString(member.email), 1),
                          createVNode("p", { class: "truncate text-xs text-muted" }, toDisplayString(member.phone ?? "-"), 1),
                          createVNode("p", { class: "text-[11px] text-muted" }, "Join: " + toDisplayString(formatDate(member.joined_at)), 1)
                        ]),
                        createVNode(_component_UBadge, {
                          color: member.has_purchase ? "success" : "neutral",
                          variant: member.has_purchase ? "soft" : "subtle",
                          size: "xs",
                          class: "rounded-full"
                        }, {
                          default: withCtx(() => [
                            createTextVNode(toDisplayString(member.has_purchase ? "Purchase" : "No Purchase"), 1)
                          ]),
                          _: 2
                        }, 1032, ["color", "variant"])
                      ])
                    ], 10, ["onClick"]);
                  }), 128))
                ]))
              ])
            ];
          }
        }),
        footer: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="flex w-full justify-end gap-2"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              color: "neutral",
              variant: "outline",
              class: "rounded-xl",
              disabled: __props.processing,
              onClick: ($event) => emit("cancel")
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
              loading: __props.processing,
              disabled: !__props.selectedMemberId || !__props.selectedPosition || __props.processing,
              onClick: ($event) => emit("submit")
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
              createVNode("div", { class: "flex w-full justify-end gap-2" }, [
                createVNode(_component_UButton, {
                  color: "neutral",
                  variant: "outline",
                  class: "rounded-xl",
                  disabled: __props.processing,
                  onClick: ($event) => emit("cancel")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Batal ")
                  ]),
                  _: 1
                }, 8, ["disabled", "onClick"]),
                createVNode(_component_UButton, {
                  color: "primary",
                  class: "rounded-xl",
                  loading: __props.processing,
                  disabled: !__props.selectedMemberId || !__props.selectedPosition || __props.processing,
                  onClick: ($event) => emit("submit")
                }, {
                  default: withCtx(() => [
                    createTextVNode(" Tempatkan ")
                  ]),
                  _: 1
                }, 8, ["loading", "disabled", "onClick"])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkPlacementModal.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "NetworkStatsCards",
  __ssrInlineRender: true,
  props: {
    stats: {}
  },
  setup(__props) {
    const props = __props;
    const statistics = computed(() => [
      {
        label: "Total Jaringan",
        value: props.stats.totalDownlines,
        icon: "i-lucide-users",
        color: "primary"
      },
      {
        label: "Kaki Kiri",
        value: props.stats.totalLeft,
        icon: "i-lucide-arrow-down-left",
        color: "blue"
      },
      {
        label: "Kaki Kanan",
        value: props.stats.totalRight,
        icon: "i-lucide-arrow-down-right",
        color: "emerald"
      }
    ]);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$c;
      const _component_UIcon = _sfc_main$d;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "grid grid-cols-1 gap-2 sm:grid-cols-3" }, _attrs))}><!--[-->`);
      ssrRenderList(statistics.value, (stat) => {
        _push(ssrRenderComponent(_component_UCard, {
          key: stat.label,
          class: "rounded-xl",
          ui: {
            body: "p-3"
          }
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`<div class="flex items-center justify-between"${_scopeId}><div class="min-w-0"${_scopeId}><p class="truncate text-[10px] font-semibold uppercase tracking-wider text-muted"${_scopeId}>${ssrInterpolate(stat.label)}</p><p class="mt-0.5 text-xl font-bold text-highlighted"${_scopeId}>${ssrInterpolate(stat.value.toLocaleString("id-ID"))}</p></div><div class="${ssrRenderClass([[
                stat.color === "primary" ? "bg-primary-50 text-primary-600 ring-primary-500/20" : stat.color === "blue" ? "bg-blue-50 text-blue-600 ring-blue-500/20" : "bg-emerald-50 text-emerald-600 ring-emerald-500/20"
              ], "rounded-lg p-1.5 ring-1 ring-inset"])}"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: stat.icon,
                class: "size-4"
              }, null, _parent2, _scopeId));
              _push2(`</div></div>`);
            } else {
              return [
                createVNode("div", { class: "flex items-center justify-between" }, [
                  createVNode("div", { class: "min-w-0" }, [
                    createVNode("p", { class: "truncate text-[10px] font-semibold uppercase tracking-wider text-muted" }, toDisplayString(stat.label), 1),
                    createVNode("p", { class: "mt-0.5 text-xl font-bold text-highlighted" }, toDisplayString(stat.value.toLocaleString("id-ID")), 1)
                  ]),
                  createVNode("div", {
                    class: ["rounded-lg p-1.5 ring-1 ring-inset", [
                      stat.color === "primary" ? "bg-primary-50 text-primary-600 ring-primary-500/20" : stat.color === "blue" ? "bg-blue-50 text-blue-600 ring-blue-500/20" : "bg-emerald-50 text-emerald-600 ring-emerald-500/20"
                    ]]
                  }, [
                    createVNode(_component_UIcon, {
                      name: stat.icon,
                      class: "size-4"
                    }, null, 8, ["name"])
                  ], 2)
                ])
              ];
            }
          }),
          _: 2
        }, _parent));
      });
      _push(`<!--]--></div>`);
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkStatsCards.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  ...{
    name: "NetworkTreeNode"
  },
  __name: "NetworkTreeNode",
  __ssrInlineRender: true,
  props: {
    node: {},
    depth: { default: 1 },
    maxDepth: { default: 5 },
    collapsedIds: { default: () => [] },
    allowPlacement: { type: Boolean, default: true }
  },
  emits: ["memberClick", "openPlacement", "toggleExpand"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const canRenderChildren = computed(() => props.depth < props.maxDepth);
    const hasChildNode = computed(() => Boolean(props.node.left || props.node.right || props.node.has_children));
    const isCollapsed = computed(() => props.collapsedIds.includes(props.node.id));
    const showChildren = computed(() => canRenderChildren.value && hasChildNode.value && !isCollapsed.value);
    const packageLabel = computed(() => props.node.package_name ?? "Member");
    const levelLabel = computed(() => `L${props.node.level}`);
    const isRightNode = computed(() => props.node.position === "right");
    const packageBadgeClass = computed(
      () => isRightNode.value ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30" : "bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-300 dark:ring-blue-500/30"
    );
    function getSlotLabel(position) {
      return position === "left" ? "+ Kiri" : "+ Kanan";
    }
    function getSlotCardClass(position) {
      return position === "right" ? "border-emerald-300/80 text-emerald-700 hover:bg-emerald-50/70 dark:border-emerald-500/35 dark:text-emerald-300 dark:hover:bg-emerald-500/10" : "border-blue-300/80 text-blue-700 hover:bg-blue-50/70 dark:border-blue-500/35 dark:text-blue-300 dark:hover:bg-blue-500/10";
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$7;
      const _component_NetworkTreeNode = resolveComponent("NetworkTreeNode", true);
      const _component_UCard = _sfc_main$c;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "flex flex-col items-center" }, _attrs))}><div class="relative space-y-1 text-center sm:space-y-1.5"><div class="absolute right-0 top-0 flex items-center gap-0.5">`);
      if (hasChildNode.value) {
        _push(ssrRenderComponent(_component_UButton, {
          size: "xs",
          color: "neutral",
          variant: "ghost",
          icon: isCollapsed.value ? "i-lucide-plus" : "i-lucide-minus",
          class: "rounded-md",
          onClick: ($event) => emit("toggleExpand", __props.node.id)
        }, null, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(ssrRenderComponent(_component_UButton, {
        size: "xs",
        color: "neutral",
        variant: "ghost",
        icon: "i-lucide-scan-search",
        class: "rounded-md",
        onClick: ($event) => emit("memberClick", __props.node.id)
      }, null, _parent));
      _push(`</div><div class="space-y-0.5 pr-10"><p class="truncate text-[11px] font-semibold text-highlighted sm:text-sm">${ssrInterpolate(__props.node.name)}</p><p class="truncate text-[10px] leading-tight text-muted sm:text-xs">${ssrInterpolate(__props.node.username)}</p><p class="text-[10px] text-muted sm:text-xs">${ssrInterpolate(levelLabel.value)}</p></div><div class="pt-0.5"><span class="${ssrRenderClass([packageBadgeClass.value, "inline-flex max-w-full items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-medium sm:text-[11px]"])}"><span class="truncate">${ssrInterpolate(packageLabel.value)}</span></span></div><div class="flex items-center justify-center gap-2.5 text-[10px] text-muted sm:text-[11px]"><span class="inline-flex items-center gap-1"><span class="size-1.5 rounded-full bg-blue-500"></span> L: ${ssrInterpolate(__props.node.total_left)}</span><span class="inline-flex items-center gap-1"><span class="size-1.5 rounded-full bg-emerald-500"></span> R: ${ssrInterpolate(__props.node.total_right)}</span></div></div>`);
      if (showChildren.value) {
        _push(`<div class="mt-2 w-full sm:mt-4"><div class="mx-auto h-2.5 w-px bg-gray-300 dark:bg-gray-700 sm:h-4"></div><div class="relative grid grid-cols-2 gap-1 px-0 sm:gap-5 sm:px-2"><div class="absolute left-[30%] right-[30%] top-0 h-px bg-gray-300 dark:bg-gray-700 sm:left-[26%] sm:right-[26%]"></div><div class="relative flex justify-center pt-2.5 sm:pt-4"><div class="absolute left-[58%] top-0 h-2.5 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700 sm:left-[55%] sm:h-4"></div>`);
        if (__props.node.left) {
          _push(ssrRenderComponent(_component_NetworkTreeNode, {
            node: __props.node.left,
            depth: __props.depth + 1,
            "max-depth": __props.maxDepth,
            "collapsed-ids": __props.collapsedIds,
            "allow-placement": __props.allowPlacement,
            onMemberClick: ($event) => emit("memberClick", $event),
            onOpenPlacement: ($event) => emit("openPlacement", $event),
            onToggleExpand: ($event) => emit("toggleExpand", $event)
          }, null, _parent));
        } else if (__props.allowPlacement) {
          _push(ssrRenderComponent(_component_UCard, {
            class: ["w-[7.7rem] rounded-xl border border-dashed bg-elevated/35 sm:w-44 sm:rounded-2xl", getSlotCardClass("left")],
            ui: { body: "p-0" }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<button type="button" class="flex w-full items-center justify-center px-2 py-3 text-[11px] font-medium transition-colors sm:py-4 sm:text-sm"${_scopeId}>${ssrInterpolate(getSlotLabel("left"))}</button>`);
              } else {
                return [
                  createVNode("button", {
                    type: "button",
                    class: "flex w-full items-center justify-center px-2 py-3 text-[11px] font-medium transition-colors sm:py-4 sm:text-sm",
                    onClick: ($event) => emit("openPlacement", { uplineId: __props.node.id, position: "left" })
                  }, toDisplayString(getSlotLabel("left")), 9, ["onClick"])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        _push(`</div><div class="relative flex justify-center pt-2.5 sm:pt-4"><div class="absolute left-[42%] top-0 h-2.5 w-px -translate-x-1/2 bg-gray-300 dark:bg-gray-700 sm:left-[45%] sm:h-4"></div>`);
        if (__props.node.right) {
          _push(ssrRenderComponent(_component_NetworkTreeNode, {
            node: __props.node.right,
            depth: __props.depth + 1,
            "max-depth": __props.maxDepth,
            "collapsed-ids": __props.collapsedIds,
            "allow-placement": __props.allowPlacement,
            onMemberClick: ($event) => emit("memberClick", $event),
            onOpenPlacement: ($event) => emit("openPlacement", $event),
            onToggleExpand: ($event) => emit("toggleExpand", $event)
          }, null, _parent));
        } else if (__props.allowPlacement) {
          _push(ssrRenderComponent(_component_UCard, {
            class: ["w-[7.7rem] rounded-xl border border-dashed bg-elevated/35 sm:w-44 sm:rounded-2xl", getSlotCardClass("right")],
            ui: { body: "p-0" }
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<button type="button" class="flex w-full items-center justify-center px-2 py-3 text-[11px] font-medium transition-colors sm:py-4 sm:text-sm"${_scopeId}>${ssrInterpolate(getSlotLabel("right"))}</button>`);
              } else {
                return [
                  createVNode("button", {
                    type: "button",
                    class: "flex w-full items-center justify-center px-2 py-3 text-[11px] font-medium transition-colors sm:py-4 sm:text-sm",
                    onClick: ($event) => emit("openPlacement", { uplineId: __props.node.id, position: "right" })
                  }, toDisplayString(getSlotLabel("right")), 9, ["onClick"])
                ];
              }
            }),
            _: 1
          }, _parent));
        } else {
          _push(`<!---->`);
        }
        _push(`</div></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkTreeNode.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "NetworkGojsTree",
  __ssrInlineRender: true,
  props: {
    node: {},
    collapsedIds: { default: () => [] },
    maxDepth: { default: 5 },
    allowPlacement: { type: Boolean, default: true },
    zoom: { default: 1 }
  },
  emits: ["memberClick", "openPlacement", "toggleExpand"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const diagramContainer = ref(null);
    const loadError = ref(null);
    let goLib = null;
    let diagram = null;
    let hasUnmounted = false;
    let goLoadPromise = null;
    function getNodeBorderColor(side) {
      return side === "right" ? "#22c55e" : "#3b82f6";
    }
    function getPackageFillColor(side) {
      return side === "right" ? "#ecfdf5" : "#eff6ff";
    }
    function getPackageBorderColor(side) {
      return side === "right" ? "#86efac" : "#93c5fd";
    }
    function getPackageTextColor(side) {
      return side === "right" ? "#15803d" : "#1d4ed8";
    }
    function loadGoJs() {
      if (typeof window === "undefined") {
        return Promise.reject(new Error("GoJS hanya tersedia di browser."));
      }
      if (window.go) {
        return Promise.resolve(window.go);
      }
      if (!goLoadPromise) {
        goLoadPromise = new Promise((resolve, reject) => {
          const script = document.createElement("script");
          script.src = "https://unpkg.com/gojs/release/go.js";
          script.async = true;
          script.onload = () => {
            if (window.go) {
              resolve(window.go);
              return;
            }
            reject(new Error("GoJS gagal dimuat dari CDN."));
          };
          script.onerror = () => reject(new Error("Gagal memuat GoJS dari CDN."));
          document.head.appendChild(script);
        });
      }
      return goLoadPromise;
    }
    function toGraphData(rootNode) {
      const nodes = [];
      const links = [];
      const collapsedIdSet = new Set(props.collapsedIds);
      const traverse = (node, parentKey, side, depth) => {
        const key = `member-${node.id}`;
        const isCollapsed = collapsedIdSet.has(node.id);
        const hasChildNode = Boolean(node.left || node.right || node.has_children);
        nodes.push({
          key,
          category: "member",
          memberId: node.id,
          uplineId: null,
          position: null,
          side,
          name: node.name,
          username: node.username,
          packageName: node.package_name ?? "Member",
          levelLabel: `L${node.level}`,
          totalLeft: node.total_left,
          totalRight: node.total_right,
          hasChildNode,
          isCollapsed,
          label: ""
        });
        if (parentKey) {
          links.push({ from: parentKey, to: key });
        }
        if (depth >= props.maxDepth || isCollapsed) {
          return;
        }
        const appendChild = (position) => {
          const child = position === "left" ? node.left : node.right;
          if (child) {
            traverse(child, key, position, depth + 1);
            return;
          }
          if (!props.allowPlacement) {
            return;
          }
          const slotKey = `slot-${node.id}-${position}`;
          const isRight = position === "right";
          nodes.push({
            key: slotKey,
            category: "placeholder",
            memberId: null,
            uplineId: node.id,
            position,
            side: isRight ? "right" : "left",
            name: "",
            username: "",
            packageName: "",
            levelLabel: "",
            totalLeft: 0,
            totalRight: 0,
            hasChildNode: false,
            isCollapsed: false,
            label: isRight ? "+ Kanan" : "+ Kiri"
          });
          links.push({ from: key, to: slotKey });
        };
        appendChild("left");
        appendChild("right");
      };
      traverse(rootNode, null, "root", 1);
      return { nodes, links };
    }
    function initDiagram(go) {
      if (!diagramContainer.value) {
        return;
      }
      const $ = go.GraphObject.make;
      diagram = $(
        go.Diagram,
        diagramContainer.value,
        {
          isReadOnly: true,
          allowMove: false,
          allowCopy: false,
          allowDelete: false,
          allowInsert: false,
          allowSelect: false,
          allowHorizontalScroll: true,
          allowVerticalScroll: true,
          minScale: 0.12,
          maxScale: 1.8,
          "animationManager.isEnabled": false,
          contentAlignment: go.Spot.TopCenter,
          padding: 6,
          layout: $(
            go.TreeLayout,
            {
              angle: 90,
              layerSpacing: 14,
              nodeSpacing: 10,
              compaction: go.TreeCompaction.Block,
              arrangement: go.TreeArrangement.FixedRoots
            }
          )
        }
      );
      diagram.toolManager.mouseWheelBehavior = go.ToolManager.WheelZoom;
      diagram.linkTemplate = $(
        go.Link,
        {
          routing: go.Routing.Orthogonal,
          corner: 6,
          selectable: false,
          layerName: "Background"
        },
        $(go.Shape, { stroke: "#cbd5e1", strokeWidth: 1.1 })
      );
      diagram.nodeTemplateMap.add(
        "member",
        $(
          go.Node,
          "Spot",
          {
            cursor: "pointer",
            click: (_event, obj) => {
              const memberId = obj?.data?.memberId;
              if (typeof memberId === "number") {
                emit("memberClick", memberId);
              }
            }
          },
          $(
            go.Panel,
            "Auto",
            $(go.Shape, "RoundedRectangle", {
              parameter1: 9,
              fill: "#ffffff",
              strokeWidth: 2,
              width: 120,
              minSize: new go.Size(120, 84)
            }, new go.Binding("stroke", "side", getNodeBorderColor)),
            $(
              go.Panel,
              "Vertical",
              {
                margin: 5,
                width: 108,
                defaultAlignment: go.Spot.Center
              },
              $(
                go.TextBlock,
                {
                  width: 106,
                  maxLines: 1,
                  overflow: go.TextOverflow.Ellipsis,
                  textAlign: "center",
                  font: "600 10px Inter, system-ui, sans-serif",
                  stroke: "#0f172a"
                },
                new go.Binding("text", "name")
              ),
              $(
                go.TextBlock,
                {
                  width: 106,
                  maxLines: 1,
                  overflow: go.TextOverflow.Ellipsis,
                  textAlign: "center",
                  margin: new go.Margin(1, 0, 0, 0),
                  font: "9px Inter, system-ui, sans-serif",
                  stroke: "#64748b"
                },
                new go.Binding("text", "username")
              ),
              $(
                go.TextBlock,
                {
                  margin: new go.Margin(0, 0, 3, 0),
                  font: "9px Inter, system-ui, sans-serif",
                  stroke: "#94a3b8"
                },
                new go.Binding("text", "levelLabel")
              ),
              $(
                go.Panel,
                "Auto",
                { margin: new go.Margin(0, 0, 3, 0) },
                $(go.Shape, "RoundedRectangle", {
                  parameter1: 6,
                  strokeWidth: 1
                }, new go.Binding("fill", "side", getPackageFillColor), new go.Binding("stroke", "side", getPackageBorderColor)),
                $(
                  go.TextBlock,
                  {
                    width: 84,
                    maxLines: 1,
                    overflow: go.TextOverflow.Ellipsis,
                    textAlign: "center",
                    margin: new go.Margin(2, 6, 2, 6),
                    font: "500 9px Inter, system-ui, sans-serif"
                  },
                  new go.Binding("stroke", "side", getPackageTextColor),
                  new go.Binding("text", "packageName")
                )
              ),
              $(
                go.Panel,
                "Horizontal",
                { defaultAlignment: go.Spot.Center },
                $(go.Shape, "Circle", { desiredSize: new go.Size(5, 5), fill: "#3b82f6", strokeWidth: 0 }),
                $(
                  go.TextBlock,
                  {
                    margin: new go.Margin(0, 3, 0, 2),
                    font: "9px Inter, system-ui, sans-serif",
                    stroke: "#64748b"
                  },
                  new go.Binding("text", "totalLeft", (value) => `L: ${value}`)
                ),
                $(go.Shape, "Circle", { desiredSize: new go.Size(5, 5), fill: "#22c55e", strokeWidth: 0 }),
                $(
                  go.TextBlock,
                  {
                    margin: new go.Margin(0, 0, 0, 2),
                    font: "9px Inter, system-ui, sans-serif",
                    stroke: "#64748b"
                  },
                  new go.Binding("text", "totalRight", (value) => `R: ${value}`)
                )
              )
            )
          ),
          $(
            go.Panel,
            "Auto",
            {
              alignment: new go.Spot(1, 0, -4, 4),
              cursor: "pointer",
              click: (event, obj) => {
                event.handled = true;
                const memberId = obj?.part?.data?.memberId;
                if (typeof memberId === "number") {
                  emit("toggleExpand", memberId);
                }
              }
            },
            $(go.Shape, "RoundedRectangle", {
              parameter1: 5,
              fill: "#f8fafc",
              stroke: "#cbd5e1",
              strokeWidth: 1
            }),
            $(
              go.TextBlock,
              {
                margin: 1,
                font: "700 9px Inter, system-ui, sans-serif",
                stroke: "#334155"
              },
              new go.Binding("text", "isCollapsed", (collapsed) => collapsed ? "+" : "−")
            ),
            new go.Binding("visible", "hasChildNode")
          )
        )
      );
      diagram.nodeTemplateMap.add(
        "placeholder",
        $(
          go.Node,
          "Auto",
          {
            cursor: "pointer",
            click: (_event, obj) => {
              const uplineId = obj?.data?.uplineId;
              const position = obj?.data?.position;
              if (typeof uplineId === "number" && (position === "left" || position === "right")) {
                emit("openPlacement", { uplineId, position });
              }
            }
          },
          $(go.Shape, "RoundedRectangle", {
            parameter1: 6,
            fill: "#f8fafc",
            strokeWidth: 1.2,
            strokeDashArray: [3, 3],
            width: 92,
            minSize: new go.Size(92, 30)
          }, new go.Binding("stroke", "side", getPackageBorderColor)),
          $(
            go.TextBlock,
            {
              margin: new go.Margin(6, 8, 6, 8),
              textAlign: "center",
              font: "500 10px Inter, system-ui, sans-serif"
            },
            new go.Binding("stroke", "side", getPackageTextColor),
            new go.Binding("text", "label")
          )
        )
      );
      diagram.model = new go.GraphLinksModel([], []);
    }
    function renderModel() {
      if (!diagram || !goLib) {
        return;
      }
      const graphData = toGraphData(props.node);
      diagram.model = new goLib.GraphLinksModel(graphData.nodes, graphData.links);
      diagram.scale = props.zoom;
      diagram.requestUpdate();
    }
    onMounted(async () => {
      try {
        goLib = await loadGoJs();
        if (hasUnmounted) {
          return;
        }
        await nextTick();
        initDiagram(goLib);
        renderModel();
      } catch (error) {
        loadError.value = error instanceof Error ? error.message : "GoJS gagal dimuat.";
      }
    });
    onBeforeUnmount(() => {
      hasUnmounted = true;
      if (diagram) {
        diagram.clear();
        diagram.div = null;
        diagram = null;
      }
    });
    watch(
      () => [props.node, props.collapsedIds, props.maxDepth, props.allowPlacement],
      () => {
        renderModel();
      }
    );
    watch(
      () => props.zoom,
      (nextZoom) => {
        if (!diagram) {
          return;
        }
        diagram.scale = nextZoom;
      }
    );
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "w-full" }, _attrs))}>`);
      if (loadError.value) {
        _push(`<div class="rounded-xl border border-warning/40 bg-warning/10 p-3 text-xs text-muted"><p class="font-medium text-warning">GoJS tidak bisa dimuat. Menampilkan fallback tree.</p><p class="mt-1">${ssrInterpolate(loadError.value)}</p></div>`);
      } else {
        _push(`<!---->`);
      }
      if (loadError.value) {
        _push(`<div class="overflow-x-auto"><div class="mx-auto w-fit px-1.5 py-2">`);
        _push(ssrRenderComponent(_sfc_main$3, {
          node: __props.node,
          "max-depth": __props.maxDepth,
          "collapsed-ids": __props.collapsedIds,
          "allow-placement": __props.allowPlacement,
          onMemberClick: ($event) => emit("memberClick", $event),
          onOpenPlacement: ($event) => emit("openPlacement", $event),
          onToggleExpand: ($event) => emit("toggleExpand", $event)
        }, null, _parent));
        _push(`</div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="h-[56vh] min-h-85 w-full rounded-xl border border-default/70 bg-default" style="${ssrRenderStyle(!loadError.value ? null : { display: "none" })}"></div></div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkGojsTree.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "NetworkTreePanel",
  __ssrInlineRender: true,
  props: {
    currentTree: { default: null },
    zoom: {},
    collapsedIds: { default: () => [] },
    maxDepth: { default: 5 },
    allowPlacement: { type: Boolean, default: true }
  },
  emits: ["memberClick", "openPlacement", "toggleExpand"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UEmpty = _sfc_main$e;
      if (__props.currentTree) {
        _push(ssrRenderComponent(_sfc_main$2, mergeProps({
          node: __props.currentTree,
          "collapsed-ids": __props.collapsedIds,
          "max-depth": __props.maxDepth,
          "allow-placement": __props.allowPlacement,
          zoom: __props.zoom,
          onMemberClick: ($event) => emit("memberClick", $event),
          onOpenPlacement: ($event) => emit("openPlacement", $event),
          onToggleExpand: ($event) => emit("toggleExpand", $event)
        }, _attrs), null, _parent));
      } else {
        _push(ssrRenderComponent(_component_UEmpty, mergeProps({
          icon: "i-lucide-network",
          title: "Jaringan belum tersedia",
          description: "Belum ada data node tree untuk akun ini.",
          ui: { root: "rounded-2xl py-12" }
        }, _attrs), null, _parent));
      }
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/dashboard/network/NetworkTreePanel.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "Network",
  __ssrInlineRender: true,
  props: {
    binaryTree: { default: null },
    networkTreeStats: { default: null },
    passiveMembers: { default: () => [] },
    currentCustomerId: { default: null },
    networkTreeRootId: { default: null }
  },
  setup(__props) {
    const props = __props;
    const binaryTree = computed(() => props.binaryTree ?? null);
    const networkTreeStats = computed(() => props.networkTreeStats ?? null);
    const passiveMembers = computed(() => props.passiveMembers ?? []);
    const currentCustomerId = computed(() => props.currentCustomerId ?? null);
    const networkTreeRootId = computed(() => props.networkTreeRootId ?? null);
    const {
      currentStats,
      isViewingMemberTree,
      selectedMemberForTree,
      maxLoadedLevel,
      treeSearchQuery,
      showTreeSearchResults,
      treeSearchResults,
      currentTree,
      zoom,
      collapsedIds,
      toggleNode,
      expandAll,
      collapseAll,
      handleZoomIn,
      handleZoomOut,
      handleResetZoom,
      handleTreeSearchInput,
      selectTreeSearchResult,
      handleTreeSearchBlur
    } = useNetworkTree(binaryTree, networkTreeStats, currentCustomerId, networkTreeRootId);
    const {
      showPlacementDialog,
      selectedUplineId,
      selectedPosition,
      selectedMember,
      memberSearchQuery,
      placementForm,
      filteredPassiveMembers,
      openPlacementDialog,
      closePlacementDialog,
      selectMember,
      placeMemberToBinaryTree
    } = useNetworkPlacement(passiveMembers, currentCustomerId);
    function updateTreeSearchQuery(value) {
      treeSearchQuery.value = value;
    }
    function visitNetworkTree(memberId) {
      const authCustomerId = currentCustomerId.value;
      const normalizedTreeRootId = memberId !== null && authCustomerId !== null && memberId !== authCustomerId ? memberId : null;
      if (normalizedTreeRootId === networkTreeRootId.value) {
        return;
      }
      router.get("/dashboard", {
        section: "network",
        ...normalizedTreeRootId !== null ? { network_root_id: normalizedTreeRootId } : {}
      }, {
        only: ["currentCustomerId", "passiveMembers", "binaryTree", "networkTreeStats", "networkTreeRootId"],
        preserveState: true,
        preserveScroll: true,
        replace: true
      });
    }
    function backToDefaultTree() {
      visitNetworkTree(null);
    }
    function focusToMember(memberId) {
      visitNetworkTree(memberId);
    }
    function handleSelectSearchResult(memberId) {
      selectTreeSearchResult(memberId);
      focusToMember(memberId);
    }
    function updatePlacementModalOpen(value) {
      showPlacementDialog.value = value;
    }
    function updatePlacementSearchQuery(value) {
      memberSearchQuery.value = value;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$c;
      _push(`<!--[--><div class="space-y-3 sm:space-y-4">`);
      _push(ssrRenderComponent(_sfc_main$4, { stats: unref(currentStats) }, null, _parent));
      _push(ssrRenderComponent(_component_UCard, { class: "overflow-hidden rounded-2xl" }, {
        header: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_sfc_main$6, {
              "is-viewing-member-tree": unref(isViewingMemberTree),
              "selected-member-for-tree": unref(selectedMemberForTree),
              "max-loaded-level": unref(maxLoadedLevel),
              zoom: unref(zoom),
              "tree-search-query": unref(treeSearchQuery),
              "show-tree-search-results": unref(showTreeSearchResults),
              "tree-search-results": unref(treeSearchResults),
              onBack: backToDefaultTree,
              "onUpdate:treeSearchQuery": updateTreeSearchQuery,
              onSearchInput: unref(handleTreeSearchInput),
              onSearchFocus: unref(handleTreeSearchInput),
              onSearchBlur: unref(handleTreeSearchBlur),
              onSelectSearchResult: handleSelectSearchResult,
              onExpandAll: unref(expandAll),
              onCollapseAll: unref(collapseAll),
              onZoomOut: unref(handleZoomOut),
              onZoomIn: unref(handleZoomIn),
              onResetZoom: unref(handleResetZoom)
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_sfc_main$6, {
                "is-viewing-member-tree": unref(isViewingMemberTree),
                "selected-member-for-tree": unref(selectedMemberForTree),
                "max-loaded-level": unref(maxLoadedLevel),
                zoom: unref(zoom),
                "tree-search-query": unref(treeSearchQuery),
                "show-tree-search-results": unref(showTreeSearchResults),
                "tree-search-results": unref(treeSearchResults),
                onBack: backToDefaultTree,
                "onUpdate:treeSearchQuery": updateTreeSearchQuery,
                onSearchInput: unref(handleTreeSearchInput),
                onSearchFocus: unref(handleTreeSearchInput),
                onSearchBlur: unref(handleTreeSearchBlur),
                onSelectSearchResult: handleSelectSearchResult,
                onExpandAll: unref(expandAll),
                onCollapseAll: unref(collapseAll),
                onZoomOut: unref(handleZoomOut),
                onZoomIn: unref(handleZoomIn),
                onResetZoom: unref(handleResetZoom)
              }, null, 8, ["is-viewing-member-tree", "selected-member-for-tree", "max-loaded-level", "zoom", "tree-search-query", "show-tree-search-results", "tree-search-results", "onSearchInput", "onSearchFocus", "onSearchBlur", "onExpandAll", "onCollapseAll", "onZoomOut", "onZoomIn", "onResetZoom"])
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-3"${_scopeId}>`);
            _push2(ssrRenderComponent(_sfc_main$1, {
              "current-tree": unref(currentTree),
              zoom: unref(zoom),
              "collapsed-ids": unref(collapsedIds),
              "max-depth": 5,
              onMemberClick: focusToMember,
              onOpenPlacement: unref(openPlacementDialog),
              onToggleExpand: unref(toggleNode)
            }, null, _parent2, _scopeId));
            _push2(`<div class="rounded-xl border border-default bg-elevated/20 px-3 py-2"${_scopeId}><div class="flex flex-col gap-1 text-[11px] text-muted sm:flex-row sm:items-center sm:justify-between"${_scopeId}><p${_scopeId}>Klik node member untuk fokus subtree.</p><p${_scopeId}>Placement hanya untuk member pasif.</p></div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-3" }, [
                createVNode(_sfc_main$1, {
                  "current-tree": unref(currentTree),
                  zoom: unref(zoom),
                  "collapsed-ids": unref(collapsedIds),
                  "max-depth": 5,
                  onMemberClick: focusToMember,
                  onOpenPlacement: unref(openPlacementDialog),
                  onToggleExpand: unref(toggleNode)
                }, null, 8, ["current-tree", "zoom", "collapsed-ids", "onOpenPlacement", "onToggleExpand"]),
                createVNode("div", { class: "rounded-xl border border-default bg-elevated/20 px-3 py-2" }, [
                  createVNode("div", { class: "flex flex-col gap-1 text-[11px] text-muted sm:flex-row sm:items-center sm:justify-between" }, [
                    createVNode("p", null, "Klik node member untuk fokus subtree."),
                    createVNode("p", null, "Placement hanya untuk member pasif.")
                  ])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_sfc_main$5, {
        open: unref(showPlacementDialog),
        "selected-position": unref(selectedPosition),
        "selected-upline-id": unref(selectedUplineId),
        "member-search-query": unref(memberSearchQuery),
        "filtered-passive-members": unref(filteredPassiveMembers),
        "selected-member-id": unref(selectedMember)?.id ?? null,
        processing: unref(placementForm).processing,
        "onUpdate:open": updatePlacementModalOpen,
        "onUpdate:memberSearchQuery": updatePlacementSearchQuery,
        onSelectMember: unref(selectMember),
        onSubmit: unref(placeMemberToBinaryTree),
        onCancel: unref(closePlacementDialog)
      }, null, _parent));
      _push(`<!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Auth/Dashboard/partials/Network.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
