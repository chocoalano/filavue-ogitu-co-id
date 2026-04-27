import { defineComponent, computed, ref, mergeProps, withCtx, createTextVNode, createVNode, toDisplayString, openBlock, createBlock, createCommentVNode, withModifiers, useSSRContext, unref, Fragment, renderList } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderAttr, ssrRenderClass, ssrRenderStyle, ssrInterpolate, ssrRenderList } from "vue/server-renderer";
import { u as useCategories, a as _sfc_main$b } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$a } from "./Button-DLZCZWnW.js";
import { _ as _sfc_main$9 } from "./Icon-Chcm7u9q.js";
import { _ as _sfc_main$8 } from "./Carousel-MVkbrILq.js";
import { Link, usePage } from "@inertiajs/vue3";
import { P as ProductCard } from "./ProductCard-CYhvI4HJ.js";
import { u as useStoreData } from "./useStoreData-DrTMI0On.js";
import { _ as _sfc_main$c } from "./SeoHead-CrFJik-H.js";
import "reka-ui";
import "@vueuse/core";
import "./useToast-CTuSIf9f.js";
import "./usePortal-EQErrF6h.js";
import "./Input-DFvIE7JC.js";
import "./Separator-CuyFd3xv.js";
import "defu";
import "reka-ui/namespaced";
import "./Checkbox-PZ4-MUog.js";
import "./Badge-DqskWDDq.js";
import "vaul-vue";
import "ufo";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
import "embla-carousel-vue";
const _sfc_main$7 = /* @__PURE__ */ defineComponent({
  __name: "CarouselHero",
  __ssrInlineRender: true,
  props: {
    banners: { default: () => [] }
  },
  setup(__props) {
    const props = __props;
    const placeholders = [
      {
        id: 1,
        name: "Organic & Natural Wellness",
        description: "Pilihan produk herba dan suplemen alami terbaik untuk menjaga kesehatan tubuh dan pikiran Anda setiap hari.",
        image: "https://images.unsplash.com/photo-1523473827533-2a64d0f291f0?auto=format&fit=crop&w=2400&q=80",
        slug: "/shop/herba-care",
        type: "bundle",
        code: "NATURAL25",
        discount: 25
      },
      {
        id: 2,
        name: "Radiant Beauty Selection",
        description: "Rawat diri Anda dengan koleksi skincare dan kosmetik premium. Pancarkan kecantikan alami Anda bersama kami.",
        image: "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=2400&q=80",
        slug: "/shop/beauty-care",
        type: "new",
        code: "BEAUTY10"
      },
      {
        id: 3,
        name: "Therapeutic Health Gear",
        description: "Dukung proses pemulihan dan kenyamanan Anda dengan alat terapi berkualitas tinggi dan teknologi terkini.",
        image: "https://images.unsplash.com/photo-1580281658628-48f1cf6b62f0?auto=format&fit=crop&w=2400&q=80",
        slug: "/shop/health-therapy",
        type: "flash_sale",
        code: "HEALTH70",
        discount: 70
      }
    ];
    const items = computed(() => props.banners?.length ? props.banners : placeholders);
    const activeIndex = ref(0);
    const copiedCode = ref(null);
    const typeBadge = {
      bundle: { label: "Bundle Hemat", icon: "i-lucide-package", gradient: "from-indigo-500 to-violet-600" },
      flash_sale: { label: "Flash Sale", icon: "i-lucide-zap", gradient: "from-orange-500 to-red-600" },
      discount: { label: "Diskon Spesial", icon: "i-lucide-percent", gradient: "from-emerald-500 to-teal-600" },
      new: { label: "Produk Baru", icon: "i-lucide-sparkles", gradient: "from-sky-500 to-blue-600" }
    };
    const getBadge = (type) => typeBadge[typeof type === "string" ? type : ""] ?? { label: "Promo", icon: "i-lucide-tag", gradient: "from-pink-500 to-rose-600" };
    const bgGradients = [
      "from-indigo-50/60 via-white to-sky-50/60 dark:from-indigo-950/25 dark:via-primary-950 dark:to-sky-950/25",
      "from-rose-50/60 via-white to-amber-50/60 dark:from-rose-950/25 dark:via-primary-950 dark:to-amber-950/25",
      "from-emerald-50/60 via-white to-teal-50/60 dark:from-emerald-950/25 dark:via-primary-950 dark:to-teal-950/25"
    ];
    const splitTitle = (title) => {
      const safeTitle = typeof title === "string" && title.trim() !== "" ? title.trim() : "Promo Spesial";
      const words = safeTitle.split(/\s+/).filter(Boolean);
      if (words.length <= 1) return { first: safeTitle, last: "" };
      return { first: words.slice(0, -1).join(" "), last: words.at(-1) };
    };
    const hrefOf = (slug) => {
      if (typeof slug !== "string") return "/shop";
      const normalizedSlug = slug.trim();
      if (normalizedSlug === "") return "/shop";
      const lowerSlug = normalizedSlug.toLowerCase();
      if (["notfound", "not-found", "undefined", "null"].includes(lowerSlug)) return "/shop";
      if (normalizedSlug.startsWith("http://") || normalizedSlug.startsWith("https://") || normalizedSlug.startsWith("//")) {
        return normalizedSlug;
      }
      if (normalizedSlug.startsWith("?") || normalizedSlug.startsWith("#")) {
        return `/shop${normalizedSlug}`;
      }
      if (normalizedSlug === "shop" || normalizedSlug === "shop/") {
        return "/shop";
      }
      if (normalizedSlug.startsWith("/")) {
        return normalizedSlug;
      }
      if (normalizedSlug.startsWith("shop/")) {
        return `/${normalizedSlug}`;
      }
      return `/shop/${normalizedSlug.replace(/^\/+/, "")}`;
    };
    const copyPromo = async (code) => {
      if (!code) return;
      try {
        await navigator.clipboard.writeText(code);
        copiedCode.value = code;
        window.setTimeout(() => {
          if (copiedCode.value === code) copiedCode.value = null;
        }, 1600);
      } catch {
        const el = document.createElement("textarea");
        el.value = code;
        el.style.position = "fixed";
        el.style.left = "-9999px";
        document.body.appendChild(el);
        el.select();
        document.execCommand("copy");
        document.body.removeChild(el);
        copiedCode.value = code;
        window.setTimeout(() => {
          if (copiedCode.value === code) copiedCode.value = null;
        }, 1600);
      }
    };
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCarousel = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(`<section${ssrRenderAttrs(mergeProps({
        class: "relative w-full overflow-hidden h-svh supports-[height:100dvh]:h-dvh",
        role: "region",
        "aria-label": "Promotional banners"
      }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UCarousel, {
        items: items.value,
        loop: "",
        arrows: "",
        dots: "",
        fade: "",
        autoplay: { delay: 8e3 },
        onSelect: (i) => activeIndex.value = i,
        ui: {
          root: "relative w-full h-full group",
          viewport: "overflow-hidden h-full",
          container: "ms-0 h-full",
          item: "basis-full ps-0 h-full",
          arrows: "absolute inset-0 z-30 pointer-events-none",
          prev: "pointer-events-auto absolute left-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity",
          next: "pointer-events-auto absolute right-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity",
          dots: "absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white/45 dark:bg-black/20 backdrop-blur-md border border-primary-200/60 dark:border-white/10 ring-1 ring-black/5 dark:ring-white/5",
          dot: "cursor-pointer size-1.5 rounded-full bg-primary-400/80 dark:bg-primary-600/80 transition-all duration-300 data-[state=active]:bg-primary-600 dark:data-[state=active]:bg-primary-500 data-[state=active]:w-7"
        }
      }, {
        default: withCtx(({ item, index }, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="relative w-full h-full"${_scopeId}><div class="absolute inset-0"${_scopeId}>`);
            if (item.image) {
              _push2(`<img${ssrRenderAttr("src", item.image)}${ssrRenderAttr("alt", item.name)} class="size-full object-cover scale-[1.01] transition-transform duration-[12s] ease-out motion-reduce:transition-none group-hover:scale-[1.06]" loading="lazy" decoding="async"${_scopeId}>`);
            } else {
              _push2(`<div class="${ssrRenderClass(`size-full bg-linear-to-br ${bgGradients[index % bgGradients.length]}`)}"${_scopeId}><div class="absolute inset-0 overflow-hidden opacity-35 dark:opacity-20 pointer-events-none"${_scopeId}><div class="absolute -left-[10%] -top-[10%] size-[52%] rounded-full bg-primary-400/25 blur-3xl animate-pulse motion-reduce:animate-none"${_scopeId}></div><div class="absolute -right-[10%] -bottom-[10%] size-[52%] rounded-full bg-indigo-400/25 blur-3xl animate-pulse motion-reduce:animate-none" style="${ssrRenderStyle({ "animation-delay": "1.8s" })}"${_scopeId}></div><div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-[62%] rounded-full border border-primary-500/10 animate-[spin_22s_linear_infinite] motion-reduce:animate-none"${_scopeId}></div></div></div>`);
            }
            _push2(`<div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_22%_18%,rgba(99,102,241,0.10),transparent_20%)] dark:bg-[radial-gradient(circle_at_22%_18%,rgba(99,102,241,0.08),transparent_25%)]"${_scopeId}></div><div class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/0 via-black/0 to-black/0 dark:from-black/25 dark:via-black/10 dark:to-black/0"${_scopeId}></div><div class="pointer-events-none absolute inset-0 shadow-[inset_0_0_0_9999px_rgba(0,0,0,0.03)] dark:shadow-[inset_0_0_0_9999px_rgba(0,0,0,0.10)]"${_scopeId}></div><div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-linear-to-t from-white/22 to-transparent dark:from-primary-950/18"${_scopeId}></div></div><div class="relative z-10 h-full"${_scopeId}><div class="mx-auto h-full w-full max-w-screen-2xl px-4 sm:px-10 lg:px-20"${_scopeId}><div class="grid h-full items-end lg:items-center lg:grid-cols-12 pb-8 sm:pb-10"${_scopeId}><div class="lg:col-span-7"${_scopeId}><div class="relative w-full max-w-[92vw] sm:max-w-2xl lg:max-w-3xl mx-auto lg:mx-0 rounded-2xl sm:rounded-3xl border border-white/35 dark:border-white/10 bg-white/20 dark:bg-white/4 backdrop-blur-md sm:backdrop-blur-sm shadow-[0_18px_70px_-35px_rgba(0,0,0,0.40)] p-4 sm:p-6 lg:p-9 overflow-hidden"${_scopeId}><div class="pointer-events-none absolute inset-0 bg-linear-to-br from-white/14 via-white/6 to-transparent dark:from-white/8 dark:via-transparent dark:to-transparent"${_scopeId}></div><div class="pointer-events-none absolute -top-20 -right-20 size-64 rounded-full bg-white/14 blur-3xl dark:bg-white/5"${_scopeId}></div><div class="pointer-events-none absolute -bottom-24 -left-24 size-72 rounded-full bg-primary-500/10 blur-3xl"${_scopeId}></div><div class="relative flex flex-col gap-4 sm:gap-6 animate-in slide-in-from-bottom-6 duration-700 ease-out"${_scopeId}><div class="flex flex-wrap items-center gap-2 sm:gap-2.5"${_scopeId}><div class="${ssrRenderClass(`inline-flex items-center gap-2 rounded-2xl bg-linear-to-r ${getBadge(item.type).gradient} px-3 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-widest text-white shadow-lg`)}"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: getBadge(item.type).icon,
              class: "size-4"
            }, null, _parent2, _scopeId));
            _push2(` ${ssrInterpolate(getBadge(item.type).label)}</div>`);
            if (item.discount) {
              _push2(`<div class="inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-3 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-widest text-white shadow-sm ring-1 ring-amber-600/25"${_scopeId}>`);
              _push2(ssrRenderComponent(_component_UIcon, {
                name: "i-lucide-tags",
                class: "size-4"
              }, null, _parent2, _scopeId));
              _push2(` Diskon ${ssrInterpolate(item.discount)}% </div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="hidden sm:flex items-center gap-2 text-[12px] font-semibold text-primary-700/80 dark:text-primary-200/80"${_scopeId}><span class="inline-flex items-center gap-1 rounded-xl bg-white/28 dark:bg-white/4 px-3 py-1 border border-white/30 dark:border-white/10"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-shield-check",
              class: "size-4"
            }, null, _parent2, _scopeId));
            _push2(` Original </span><span class="inline-flex items-center gap-1 rounded-xl bg-white/28 dark:bg-white/4 px-3 py-1 border border-white/30 dark:border-white/10"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-truck",
              class: "size-4"
            }, null, _parent2, _scopeId));
            _push2(` Fast Delivery </span></div></div><div class="space-y-2"${_scopeId}><h2 class="text-2xl sm:text-5xl lg:text-6xl font-black tracking-tight text-primary-950 dark:text-white leading-[1.05]"${_scopeId}>${ssrInterpolate(splitTitle(item.name).first)} `);
            if (splitTitle(item.name).last) {
              _push2(`<span class="block text-primary-600 dark:text-primary-400 italic"${_scopeId}>${ssrInterpolate(splitTitle(item.name).last)}</span>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</h2><p class="max-w-2xl text-sm sm:text-lg font-semibold text-primary-800/90 dark:text-primary-200 leading-relaxed line-clamp-3 sm:line-clamp-none"${_scopeId}>${ssrInterpolate(item.description)}</p></div><div class="grid grid-cols-1 sm:flex sm:flex-row sm:items-center gap-3 pt-1"${_scopeId}>`);
            _push2(ssrRenderComponent(_component_UButton, {
              to: hrefOf(item.slug),
              size: "xl",
              class: "rounded-2xl px-8 py-2 font-bold group"
            }, {
              trailing: withCtx((_, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(ssrRenderComponent(_component_UIcon, {
                    name: "i-lucide-arrow-right",
                    class: "size-5 transition-transform duration-200 group-hover:translate-x-1"
                  }, null, _parent3, _scopeId2));
                } else {
                  return [
                    createVNode(_component_UIcon, {
                      name: "i-lucide-arrow-right",
                      class: "size-5 transition-transform duration-200 group-hover:translate-x-1"
                    })
                  ];
                }
              }),
              default: withCtx((_, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Belanja Sekarang `);
                } else {
                  return [
                    createTextVNode(" Belanja Sekarang ")
                  ];
                }
              }),
              _: 2
            }, _parent2, _scopeId));
            if (item.code) {
              _push2(ssrRenderComponent(_component_UButton, {
                variant: "outline",
                color: "neutral",
                size: "xl",
                class: "rounded-2xl px-6 py-2 font-bold justify-between",
                "aria-label": `Copy promo code ${item.code}`,
                onClick: ($event) => copyPromo(item.code)
              }, {
                default: withCtx((_, _push3, _parent3, _scopeId2) => {
                  if (_push3) {
                    _push3(`<span class="inline-flex items-center gap-2 min-w-0"${_scopeId2}>`);
                    _push3(ssrRenderComponent(_component_UIcon, {
                      name: copiedCode.value === item.code ? "i-lucide-check" : "i-lucide-copy",
                      class: "size-4"
                    }, null, _parent3, _scopeId2));
                    _push3(`<span class="truncate"${_scopeId2}>${ssrInterpolate(item.code)}</span></span><span class="text-[11px] font-extrabold uppercase tracking-widest opacity-70" aria-live="polite"${_scopeId2}>${ssrInterpolate(copiedCode.value === item.code ? "Copied" : "Copy")}</span>`);
                  } else {
                    return [
                      createVNode("span", { class: "inline-flex items-center gap-2 min-w-0" }, [
                        createVNode(_component_UIcon, {
                          name: copiedCode.value === item.code ? "i-lucide-check" : "i-lucide-copy",
                          class: "size-4"
                        }, null, 8, ["name"]),
                        createVNode("span", { class: "truncate" }, toDisplayString(item.code), 1)
                      ]),
                      createVNode("span", {
                        class: "text-[11px] font-extrabold uppercase tracking-widest opacity-70",
                        "aria-live": "polite"
                      }, toDisplayString(copiedCode.value === item.code ? "Copied" : "Copy"), 1)
                    ];
                  }
                }),
                _: 2
              }, _parent2, _scopeId));
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div></div></div><div class="hidden lg:flex lg:col-span-5 justify-end items-center"${_scopeId}><div class="flex items-center gap-6"${_scopeId}><div class="flex flex-col items-end"${_scopeId}><span class="text-[52px] font-black text-primary-900/10 dark:text-white/10 leading-none tabular-nums"${_scopeId}>${ssrInterpolate(String(activeIndex.value + 1).padStart(2, "0"))}</span><div class="h-1 w-16 bg-primary-200/70 dark:bg-white/10 rounded-full overflow-hidden"${_scopeId}><div class="h-full bg-primary-600 transition-all duration-300 ease-out" style="${ssrRenderStyle({ width: `${(activeIndex.value + 1) / items.value.length * 100}%` })}"${_scopeId}></div></div></div><div class="h-12 w-px bg-primary-200/80 dark:bg-white/10"${_scopeId}></div><div class="text-sm font-extrabold text-primary-500 uppercase tracking-widest"${_scopeId}> Explore Items </div></div></div></div></div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "relative w-full h-full" }, [
                createVNode("div", { class: "absolute inset-0" }, [
                  item.image ? (openBlock(), createBlock("img", {
                    key: 0,
                    src: item.image,
                    alt: item.name,
                    class: "size-full object-cover scale-[1.01] transition-transform duration-[12s] ease-out motion-reduce:transition-none group-hover:scale-[1.06]",
                    loading: "lazy",
                    decoding: "async"
                  }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                    key: 1,
                    class: `size-full bg-linear-to-br ${bgGradients[index % bgGradients.length]}`
                  }, [
                    createVNode("div", { class: "absolute inset-0 overflow-hidden opacity-35 dark:opacity-20 pointer-events-none" }, [
                      createVNode("div", { class: "absolute -left-[10%] -top-[10%] size-[52%] rounded-full bg-primary-400/25 blur-3xl animate-pulse motion-reduce:animate-none" }),
                      createVNode("div", {
                        class: "absolute -right-[10%] -bottom-[10%] size-[52%] rounded-full bg-indigo-400/25 blur-3xl animate-pulse motion-reduce:animate-none",
                        style: { "animation-delay": "1.8s" }
                      }),
                      createVNode("div", { class: "absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-[62%] rounded-full border border-primary-500/10 animate-[spin_22s_linear_infinite] motion-reduce:animate-none" })
                    ])
                  ], 2)),
                  createVNode("div", { class: "pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_22%_18%,rgba(99,102,241,0.10),transparent_20%)] dark:bg-[radial-gradient(circle_at_22%_18%,rgba(99,102,241,0.08),transparent_25%)]" }),
                  createVNode("div", { class: "pointer-events-none absolute inset-0 bg-linear-to-t from-black/0 via-black/0 to-black/0 dark:from-black/25 dark:via-black/10 dark:to-black/0" }),
                  createVNode("div", { class: "pointer-events-none absolute inset-0 shadow-[inset_0_0_0_9999px_rgba(0,0,0,0.03)] dark:shadow-[inset_0_0_0_9999px_rgba(0,0,0,0.10)]" }),
                  createVNode("div", { class: "pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-linear-to-t from-white/22 to-transparent dark:from-primary-950/18" })
                ]),
                createVNode("div", { class: "relative z-10 h-full" }, [
                  createVNode("div", { class: "mx-auto h-full w-full max-w-screen-2xl px-4 sm:px-10 lg:px-20" }, [
                    createVNode("div", { class: "grid h-full items-end lg:items-center lg:grid-cols-12 pb-8 sm:pb-10" }, [
                      createVNode("div", { class: "lg:col-span-7" }, [
                        createVNode("div", { class: "relative w-full max-w-[92vw] sm:max-w-2xl lg:max-w-3xl mx-auto lg:mx-0 rounded-2xl sm:rounded-3xl border border-white/35 dark:border-white/10 bg-white/20 dark:bg-white/4 backdrop-blur-md sm:backdrop-blur-sm shadow-[0_18px_70px_-35px_rgba(0,0,0,0.40)] p-4 sm:p-6 lg:p-9 overflow-hidden" }, [
                          createVNode("div", { class: "pointer-events-none absolute inset-0 bg-linear-to-br from-white/14 via-white/6 to-transparent dark:from-white/8 dark:via-transparent dark:to-transparent" }),
                          createVNode("div", { class: "pointer-events-none absolute -top-20 -right-20 size-64 rounded-full bg-white/14 blur-3xl dark:bg-white/5" }),
                          createVNode("div", { class: "pointer-events-none absolute -bottom-24 -left-24 size-72 rounded-full bg-primary-500/10 blur-3xl" }),
                          createVNode("div", { class: "relative flex flex-col gap-4 sm:gap-6 animate-in slide-in-from-bottom-6 duration-700 ease-out" }, [
                            createVNode("div", { class: "flex flex-wrap items-center gap-2 sm:gap-2.5" }, [
                              createVNode("div", {
                                class: `inline-flex items-center gap-2 rounded-2xl bg-linear-to-r ${getBadge(item.type).gradient} px-3 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-widest text-white shadow-lg`
                              }, [
                                createVNode(_component_UIcon, {
                                  name: getBadge(item.type).icon,
                                  class: "size-4"
                                }, null, 8, ["name"]),
                                createTextVNode(" " + toDisplayString(getBadge(item.type).label), 1)
                              ], 2),
                              item.discount ? (openBlock(), createBlock("div", {
                                key: 0,
                                class: "inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-3 sm:px-4 py-1.5 sm:py-2 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-widest text-white shadow-sm ring-1 ring-amber-600/25"
                              }, [
                                createVNode(_component_UIcon, {
                                  name: "i-lucide-tags",
                                  class: "size-4"
                                }),
                                createTextVNode(" Diskon " + toDisplayString(item.discount) + "% ", 1)
                              ])) : createCommentVNode("", true),
                              createVNode("div", { class: "hidden sm:flex items-center gap-2 text-[12px] font-semibold text-primary-700/80 dark:text-primary-200/80" }, [
                                createVNode("span", { class: "inline-flex items-center gap-1 rounded-xl bg-white/28 dark:bg-white/4 px-3 py-1 border border-white/30 dark:border-white/10" }, [
                                  createVNode(_component_UIcon, {
                                    name: "i-lucide-shield-check",
                                    class: "size-4"
                                  }),
                                  createTextVNode(" Original ")
                                ]),
                                createVNode("span", { class: "inline-flex items-center gap-1 rounded-xl bg-white/28 dark:bg-white/4 px-3 py-1 border border-white/30 dark:border-white/10" }, [
                                  createVNode(_component_UIcon, {
                                    name: "i-lucide-truck",
                                    class: "size-4"
                                  }),
                                  createTextVNode(" Fast Delivery ")
                                ])
                              ])
                            ]),
                            createVNode("div", { class: "space-y-2" }, [
                              createVNode("h2", { class: "text-2xl sm:text-5xl lg:text-6xl font-black tracking-tight text-primary-950 dark:text-white leading-[1.05]" }, [
                                createTextVNode(toDisplayString(splitTitle(item.name).first) + " ", 1),
                                splitTitle(item.name).last ? (openBlock(), createBlock("span", {
                                  key: 0,
                                  class: "block text-primary-600 dark:text-primary-400 italic"
                                }, toDisplayString(splitTitle(item.name).last), 1)) : createCommentVNode("", true)
                              ]),
                              createVNode("p", { class: "max-w-2xl text-sm sm:text-lg font-semibold text-primary-800/90 dark:text-primary-200 leading-relaxed line-clamp-3 sm:line-clamp-none" }, toDisplayString(item.description), 1)
                            ]),
                            createVNode("div", { class: "grid grid-cols-1 sm:flex sm:flex-row sm:items-center gap-3 pt-1" }, [
                              createVNode(_component_UButton, {
                                to: hrefOf(item.slug),
                                size: "xl",
                                class: "rounded-2xl px-8 py-2 font-bold group"
                              }, {
                                trailing: withCtx(() => [
                                  createVNode(_component_UIcon, {
                                    name: "i-lucide-arrow-right",
                                    class: "size-5 transition-transform duration-200 group-hover:translate-x-1"
                                  })
                                ]),
                                default: withCtx(() => [
                                  createTextVNode(" Belanja Sekarang ")
                                ]),
                                _: 1
                              }, 8, ["to"]),
                              item.code ? (openBlock(), createBlock(_component_UButton, {
                                key: 0,
                                variant: "outline",
                                color: "neutral",
                                size: "xl",
                                class: "rounded-2xl px-6 py-2 font-bold justify-between",
                                "aria-label": `Copy promo code ${item.code}`,
                                onClick: withModifiers(($event) => copyPromo(item.code), ["prevent"])
                              }, {
                                default: withCtx(() => [
                                  createVNode("span", { class: "inline-flex items-center gap-2 min-w-0" }, [
                                    createVNode(_component_UIcon, {
                                      name: copiedCode.value === item.code ? "i-lucide-check" : "i-lucide-copy",
                                      class: "size-4"
                                    }, null, 8, ["name"]),
                                    createVNode("span", { class: "truncate" }, toDisplayString(item.code), 1)
                                  ]),
                                  createVNode("span", {
                                    class: "text-[11px] font-extrabold uppercase tracking-widest opacity-70",
                                    "aria-live": "polite"
                                  }, toDisplayString(copiedCode.value === item.code ? "Copied" : "Copy"), 1)
                                ]),
                                _: 2
                              }, 1032, ["aria-label", "onClick"])) : createCommentVNode("", true)
                            ])
                          ])
                        ])
                      ]),
                      createVNode("div", { class: "hidden lg:flex lg:col-span-5 justify-end items-center" }, [
                        createVNode("div", { class: "flex items-center gap-6" }, [
                          createVNode("div", { class: "flex flex-col items-end" }, [
                            createVNode("span", { class: "text-[52px] font-black text-primary-900/10 dark:text-white/10 leading-none tabular-nums" }, toDisplayString(String(activeIndex.value + 1).padStart(2, "0")), 1),
                            createVNode("div", { class: "h-1 w-16 bg-primary-200/70 dark:bg-white/10 rounded-full overflow-hidden" }, [
                              createVNode("div", {
                                class: "h-full bg-primary-600 transition-all duration-300 ease-out",
                                style: { width: `${(activeIndex.value + 1) / items.value.length * 100}%` }
                              }, null, 4)
                            ])
                          ]),
                          createVNode("div", { class: "h-12 w-px bg-primary-200/80 dark:bg-white/10" }),
                          createVNode("div", { class: "text-sm font-extrabold text-primary-500 uppercase tracking-widest" }, " Explore Items ")
                        ])
                      ])
                    ])
                  ])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</section>`);
    };
  }
});
const _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/home/CarouselHero.vue");
  return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
const _sfc_main$6 = /* @__PURE__ */ defineComponent({
  __name: "CategoryGrid",
  __ssrInlineRender: true,
  props: {
    categories: {},
    maxItems: {},
    hideBackground: { type: Boolean }
  },
  setup(__props) {
    const props = __props;
    const { getCategoryIcon, getCategoryGradient } = useCategories();
    const shown = computed(() => {
      const arr = props.categories ?? [];
      const max = typeof props.maxItems === "number" ? props.maxItems : arr.length;
      return arr.slice(0, Math.max(0, max));
    });
    const gridClass = computed(() => {
      const n = shown.value.length;
      if (n <= 1) return "grid grid-cols-1 gap-4";
      if (n === 2) return "grid grid-cols-2 gap-4";
      if (n === 3) return "grid grid-cols-2 sm:grid-cols-3 gap-4";
      if (n === 4) return "grid grid-cols-2 sm:grid-cols-4 gap-4";
      return "grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4 lg:gap-5";
    });
    const getCategoryLink = (slug) => `/shop?category=${encodeURIComponent(slug)}`;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      if (shown.value.length) {
        _push(`<section${ssrRenderAttrs(mergeProps({
          class: ["relative py-16 sm:py-20", { "overflow-hidden": !__props.hideBackground }]
        }, _attrs))}>`);
        if (!__props.hideBackground) {
          _push(`<div class="pointer-events-none absolute inset-0 -z-10"><div class="absolute inset-0 bg-linear-to-br from-indigo-50/70 via-white to-violet-50/50 dark:from-indigo-950/40 dark:via-gray-950 dark:to-purple-950/30"></div><div class="absolute -top-32 -right-16 h-96 w-96 rounded-full bg-violet-300/30 blur-3xl dark:bg-violet-700/15"></div><div class="absolute -bottom-24 -left-16 h-80 w-80 rounded-full bg-blue-300/30 blur-3xl dark:bg-blue-700/15"></div><div class="absolute top-1/2 left-1/2 h-125 w-125 -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-100/20 blur-3xl dark:bg-indigo-900/10"></div><div class="absolute inset-0 bg-[radial-gradient(circle,#6366f125_1px,transparent_1px)] bg-size-[28px_28px] dark:bg-[radial-gradient(circle,#6366f115_1px,transparent_1px)]"></div></div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`<div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div class="mb-10 flex items-end justify-between gap-4"><div class="min-w-0"><div class="mb-3 inline-flex items-center gap-1.5 rounded-full border border-indigo-200/70 bg-indigo-50/90 px-3 py-1 text-xs font-medium text-indigo-600 backdrop-blur-sm dark:border-indigo-700/40 dark:bg-indigo-950/60 dark:text-indigo-400">`);
        _push(ssrRenderComponent(_component_UIcon, {
          name: "i-lucide-layout-grid",
          class: "size-3.5"
        }, null, _parent));
        _push(` Semua Kategori </div><h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl"> Belanja Berdasarkan Kategori </h2><p class="mt-2 text-sm text-gray-500 dark:text-gray-400"> Temukan produk favorit dari berbagai kategori pilihan </p></div>`);
        _push(ssrRenderComponent(_component_UButton, {
          to: "/shop",
          variant: "ghost",
          color: "neutral",
          "trailing-icon": "i-lucide-arrow-right",
          class: "hidden sm:flex"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` Lihat Semua `);
            } else {
              return [
                createTextVNode(" Lihat Semua ")
              ];
            }
          }),
          _: 1
        }, _parent));
        _push(`</div><div class="${ssrRenderClass(gridClass.value)}"><!--[-->`);
        ssrRenderList(shown.value, (cat, idx) => {
          _push(ssrRenderComponent(unref(Link), {
            key: cat.id ?? cat.slug,
            class: "group relative block aspect-[4/4.8] overflow-hidden rounded-3xl border border-gray-200/60 bg-white/80 shadow-sm shadow-gray-100 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-gray-200/70 dark:border-white/10 dark:bg-white/5 dark:shadow-none dark:hover:border-white/15 dark:hover:shadow-black/40",
            href: getCategoryLink(cat.slug)
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                if (cat.image) {
                  _push2(`<img${ssrRenderAttr("src", cat.image)}${ssrRenderAttr("alt", cat.name)} class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"${_scopeId}>`);
                } else {
                  _push2(`<div class="${ssrRenderClass([
                    "absolute inset-0 grid place-items-center bg-linear-to-br text-white",
                    unref(getCategoryGradient)(cat.slug, idx)
                  ])}"${_scopeId}>`);
                  _push2(ssrRenderComponent(_component_UIcon, {
                    name: unref(getCategoryIcon)(cat.slug),
                    class: "size-10 drop-shadow-lg"
                  }, null, _parent2, _scopeId));
                  _push2(`</div>`);
                }
                _push2(`<div class="absolute inset-0 bg-linear-to-t from-black/85 via-black/35 to-black/10 transition-opacity duration-300 group-hover:from-black/75 group-hover:via-black/30 group-hover:to-transparent"${_scopeId}></div><div class="absolute inset-x-0 bottom-0 p-4"${_scopeId}><p class="line-clamp-2 text-sm font-semibold text-white drop-shadow-md"${_scopeId}>${ssrInterpolate(cat.name)}</p><div class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-0.5 text-[11px] font-medium text-gray-700 backdrop-blur-sm"${_scopeId}>`);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-package-2",
                  class: "size-3 shrink-0"
                }, null, _parent2, _scopeId));
                _push2(` ${ssrInterpolate((cat.productCount ?? 0).toLocaleString("id-ID"))} produk </div></div><div class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-black/35 px-2 py-1 text-[11px] font-medium text-white opacity-0 backdrop-blur-sm transition-opacity duration-300 group-hover:opacity-100"${_scopeId}> Lihat `);
                _push2(ssrRenderComponent(_component_UIcon, {
                  name: "i-lucide-arrow-right",
                  class: "size-3.5"
                }, null, _parent2, _scopeId));
                _push2(`</div>`);
              } else {
                return [
                  cat.image ? (openBlock(), createBlock("img", {
                    key: 0,
                    src: cat.image,
                    alt: cat.name,
                    class: "absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                  }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                    key: 1,
                    class: [
                      "absolute inset-0 grid place-items-center bg-linear-to-br text-white",
                      unref(getCategoryGradient)(cat.slug, idx)
                    ]
                  }, [
                    createVNode(_component_UIcon, {
                      name: unref(getCategoryIcon)(cat.slug),
                      class: "size-10 drop-shadow-lg"
                    }, null, 8, ["name"])
                  ], 2)),
                  createVNode("div", { class: "absolute inset-0 bg-linear-to-t from-black/85 via-black/35 to-black/10 transition-opacity duration-300 group-hover:from-black/75 group-hover:via-black/30 group-hover:to-transparent" }),
                  createVNode("div", { class: "absolute inset-x-0 bottom-0 p-4" }, [
                    createVNode("p", { class: "line-clamp-2 text-sm font-semibold text-white drop-shadow-md" }, toDisplayString(cat.name), 1),
                    createVNode("div", { class: "mt-2 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-0.5 text-[11px] font-medium text-gray-700 backdrop-blur-sm" }, [
                      createVNode(_component_UIcon, {
                        name: "i-lucide-package-2",
                        class: "size-3 shrink-0"
                      }),
                      createTextVNode(" " + toDisplayString((cat.productCount ?? 0).toLocaleString("id-ID")) + " produk ", 1)
                    ])
                  ]),
                  createVNode("div", { class: "absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-black/35 px-2 py-1 text-[11px] font-medium text-white opacity-0 backdrop-blur-sm transition-opacity duration-300 group-hover:opacity-100" }, [
                    createTextVNode(" Lihat "),
                    createVNode(_component_UIcon, {
                      name: "i-lucide-arrow-right",
                      class: "size-3.5"
                    })
                  ])
                ];
              }
            }),
            _: 2
          }, _parent));
        });
        _push(`<!--]--></div><div class="mt-8 sm:hidden">`);
        _push(ssrRenderComponent(_component_UButton, {
          to: "/shop",
          color: "neutral",
          variant: "outline",
          "trailing-icon": "i-lucide-arrow-right",
          block: "",
          class: "rounded-2xl p-3"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(` Lihat Semua `);
            } else {
              return [
                createTextVNode(" Lihat Semua ")
              ];
            }
          }),
          _: 1
        }, _parent));
        _push(`</div></div></section>`);
      } else {
        _push(`<!---->`);
      }
    };
  }
});
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/home/CategoryGrid.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "FeaturedProducts",
  __ssrInlineRender: true,
  props: {
    products: {},
    hideBackground: { type: Boolean }
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(`<section${ssrRenderAttrs(mergeProps({
        class: ["relative pb-16 pt-2 sm:pb-20 sm:pt-4", { "overflow-hidden": !__props.hideBackground }]
      }, _attrs))}>`);
      if (!__props.hideBackground) {
        _push(`<div class="pointer-events-none absolute inset-0 -z-10"><div class="absolute inset-0 bg-linear-to-tl from-indigo-50/70 via-white to-violet-50/50 dark:from-indigo-950/40 dark:via-gray-950 dark:to-purple-950/30"></div><div class="absolute -top-32 -left-16 h-96 w-96 rounded-full bg-indigo-300/25 blur-3xl dark:bg-indigo-700/15"></div><div class="absolute -bottom-24 -right-16 h-80 w-80 rounded-full bg-violet-300/25 blur-3xl dark:bg-violet-700/15"></div><div class="absolute inset-0 bg-[radial-gradient(circle,#6366f125_1px,transparent_1px)] bg-size-[28px_28px] dark:bg-[radial-gradient(circle,#6366f115_1px,transparent_1px)]"></div></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="mx-auto mb-10 max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div class="border-t border-indigo-100/60 dark:border-white/5"></div></div><div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div class="mb-8 flex items-end justify-between gap-4 sm:mb-10"><div class="min-w-0"><div class="mb-3 inline-flex items-center gap-1.5 rounded-full border border-indigo-200/70 bg-indigo-50/90 px-3 py-1 text-xs font-medium text-indigo-600 backdrop-blur-sm dark:border-indigo-700/40 dark:bg-indigo-950/60 dark:text-indigo-400">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-trending-up",
        class: "size-3.5"
      }, null, _parent));
      _push(` Terlaris Bulan Ini </div><h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white"> Produk Unggulan </h2><p class="mt-2 text-sm text-gray-500 dark:text-gray-400"> Produk terlaris pilihan pelanggan kami </p></div>`);
      _push(ssrRenderComponent(_component_UButton, {
        to: "/shop",
        variant: "ghost",
        color: "neutral",
        "trailing-icon": "i-lucide-arrow-right",
        class: "hidden shrink-0 sm:flex"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Lihat Semua `);
          } else {
            return [
              createTextVNode(" Lihat Semua ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div>`);
      if (__props.products === void 0) {
        _push(`<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4"><!--[-->`);
        ssrRenderList(8, (i) => {
          _push(`<div class="overflow-hidden rounded-2xl border border-gray-200/50 bg-white/60 sm:rounded-3xl dark:border-white/8 dark:bg-white/5"><div class="aspect-square animate-pulse bg-gray-200/80 dark:bg-white/10"></div><div class="space-y-2 p-2.5 sm:space-y-3 sm:p-4"><div class="h-2.5 w-20 animate-pulse rounded-full bg-gray-200 sm:h-3 sm:w-24 dark:bg-white/10"></div><div class="h-3 animate-pulse rounded-full bg-gray-200 sm:h-4 dark:bg-white/10"></div><div class="h-3 w-3/4 animate-pulse rounded-full bg-gray-200 sm:h-4 dark:bg-white/10"></div><div class="h-4 w-1/2 animate-pulse rounded-full bg-gray-200 sm:h-5 dark:bg-white/10"></div><div class="mt-1 h-8 animate-pulse rounded-xl bg-gray-200 dark:bg-white/10"></div></div></div>`);
        });
        _push(`<!--]--></div>`);
      } else if (__props.products.length) {
        _push(`<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4"><!--[-->`);
        ssrRenderList(__props.products, (product) => {
          _push(ssrRenderComponent(ProductCard, {
            key: product.id,
            product,
            class: "h-full"
          }, null, _parent));
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="mt-8 sm:hidden">`);
      _push(ssrRenderComponent(_component_UButton, {
        to: "/shop",
        color: "neutral",
        variant: "outline",
        "trailing-icon": "i-lucide-arrow-right",
        class: "rounded-2xl p-3",
        block: ""
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Lihat Semua Produk `);
          } else {
            return [
              createTextVNode(" Lihat Semua Produk ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></div></section>`);
    };
  }
});
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/home/FeaturedProducts.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "ProductCTA",
  __ssrInlineRender: true,
  setup(__props) {
    const { homeCta } = useStoreData();
    const hasSecondaryCta = computed(() => !!homeCta.value.secondary_cta_label);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(`<section${ssrRenderAttrs(mergeProps({ class: "py-16 sm:py-24 bg-white dark:bg-slate-950 transition-colors duration-500" }, _attrs))}><div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div class="relative overflow-hidden rounded-3xl bg-slate-50 border border-slate-200 shadow-xl dark:bg-slate-900 dark:border-white/5 dark:shadow-2xl"><div class="absolute inset-0 pointer-events-none overflow-hidden opacity-30 dark:opacity-40"><div class="absolute -left-24 -top-24 size-96 rounded-full bg-primary-500/20 blur-[100px] animate-pulse dark:bg-primary-600/30"></div><div class="absolute -right-24 -bottom-24 size-96 rounded-full bg-blue-500/10 blur-[100px] animate-pulse dark:bg-blue-600/20" style="${ssrRenderStyle({ "animation-delay": "2s" })}"></div></div><div class="relative flex flex-col items-center lg:flex-row lg:items-center"><div class="z-10 w-full p-8 sm:p-12 lg:w-3/5 lg:p-20 text-center lg:text-left"><div class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-primary-600 backdrop-blur-md border border-primary-100 mb-6 dark:bg-white/10 dark:text-primary-400 dark:border-white/10">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-sparkles",
        class: "size-3.5"
      }, null, _parent));
      _push(` ${ssrInterpolate(unref(homeCta).badge_label)}</div><h2 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white sm:text-6xl leading-[1.1]">${ssrInterpolate(unref(homeCta).heading_main)} <br class="hidden sm:block"><span class="text-transparent bg-clip-text bg-linear-to-r from-primary-600 to-primary-600 dark:from-primary-400 dark:to-blue-400 italic">${ssrInterpolate(unref(homeCta).heading_gradient)}</span></h2><p class="mt-6 max-w-xl text-lg text-slate-600 dark:text-gray-400 leading-relaxed mx-auto lg:mx-0">${ssrInterpolate(unref(homeCta).description)}</p>`);
      if (unref(homeCta).features.length) {
        _push(`<div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 border-t border-slate-200 dark:border-white/10 pt-10"><!--[-->`);
        ssrRenderList(unref(homeCta).features, (f) => {
          _push(`<div class="flex flex-col items-center lg:items-start">`);
          _push(ssrRenderComponent(_component_UIcon, {
            name: f.icon,
            class: "size-6 text-primary-600 dark:text-primary-500 mb-3"
          }, null, _parent));
          _push(`<h3 class="font-bold text-slate-900 dark:text-white text-sm">${ssrInterpolate(f.label)}</h3><p class="text-xs text-slate-500 dark:text-gray-500 mt-1">${ssrInterpolate(f.description)}</p></div>`);
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="mt-12 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">`);
      _push(ssrRenderComponent(_component_UButton, {
        to: unref(homeCta).primary_cta_url,
        size: "xl",
        class: "w-full sm:w-auto rounded-2xl px-10 py-4 font-bold shadow-xl shadow-primary-500/20"
      }, {
        trailing: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-arrow-right",
              class: "size-5"
            }, null, _parent2, _scopeId));
          } else {
            return [
              createVNode(_component_UIcon, {
                name: "i-lucide-arrow-right",
                class: "size-5"
              })
            ];
          }
        }),
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(unref(homeCta).primary_cta_label)} `);
          } else {
            return [
              createTextVNode(toDisplayString(unref(homeCta).primary_cta_label) + " ", 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      if (hasSecondaryCta.value) {
        _push(ssrRenderComponent(_component_UButton, {
          to: unref(homeCta).secondary_cta_url || void 0,
          variant: "ghost",
          color: "neutral",
          size: "xl",
          class: "text-slate-700 hover:bg-slate-200/50 dark:text-white dark:hover:bg-white/5 rounded-2xl px-8"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(unref(homeCta).secondary_cta_label)}`);
            } else {
              return [
                createTextVNode(toDisplayString(unref(homeCta).secondary_cta_label), 1)
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div><div class="relative w-full lg:w-2/5 p-8 lg:p-0 flex items-center justify-center"><div class="relative size-72 sm:size-96 lg:size-112.5"><div class="absolute top-[10%] -left-[5%] z-20 w-48 rounded-2xl bg-white/70 p-4 shadow-xl backdrop-blur-xl border border-white dark:bg-white/10 dark:border-white/20 animate-in fade-in slide-in-from-bottom-10 duration-700"><div class="flex items-center gap-3"><div class="size-10 rounded-xl bg-primary-100 dark:bg-primary-500/20 grid place-items-center">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-shield-check",
        class: "size-5 text-primary-600 dark:text-primary-400"
      }, null, _parent));
      _push(`</div><div><p class="text-[10px] uppercase font-bold text-slate-500 dark:text-gray-400">${ssrInterpolate(unref(homeCta).floating_badge1_label)}</p><p class="text-xs font-bold text-slate-900 dark:text-white">${ssrInterpolate(unref(homeCta).floating_badge1_value)}</p></div></div></div><div class="absolute bottom-[10%] -right-[5%] z-20 w-48 rounded-2xl bg-primary-600 p-4 shadow-2xl border border-white/20 animate-in fade-in slide-in-from-bottom-20 duration-1000"><div class="flex items-center gap-3"><div class="size-10 rounded-xl bg-white/20 grid place-items-center">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-trending-up",
        class: "size-5 text-white"
      }, null, _parent));
      _push(`</div><div><p class="text-[10px] uppercase font-bold text-primary-100">${ssrInterpolate(unref(homeCta).floating_badge2_label)}</p><p class="text-xs font-bold text-white">${ssrInterpolate(unref(homeCta).floating_badge2_value)}</p></div></div></div><div class="absolute inset-0 grid place-items-center"><div class="size-64 sm:size-80 rounded-full border border-slate-200 dark:border-white/10 animate-[spin_10s_linear_infinite]"></div><div class="absolute size-48 sm:size-64 rounded-full border border-primary-500/30 dark:border-primary-500/20 animate-[spin_15s_linear_infinite_reverse]"></div><div class="absolute size-32 rounded-3xl bg-primary-500/10 rotate-45 animate-pulse dark:bg-primary-500/10"></div></div></div></div></div></div></div></section>`);
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/home/ProductCTA.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "AffiliateCTA",
  __ssrInlineRender: true,
  setup(__props) {
    const { affiliateCta } = useStoreData();
    const hasSecondaryCta = computed(() => !!affiliateCta.value.secondary_cta_label && !!affiliateCta.value.secondary_cta_url);
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$9;
      const _component_UButton = _sfc_main$a;
      _push(`<section${ssrRenderAttrs(mergeProps({ class: "py-16 sm:py-24 bg-white dark:bg-gray-950 overflow-hidden" }, _attrs))}><div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div class="lg:flex lg:items-center lg:gap-16"><div class="relative mb-12 lg:mb-0 lg:w-1/2"><div class="absolute -left-12 -top-12 size-64 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-3xl opacity-50"></div><div class="relative z-10 grid grid-cols-2 gap-4"><div class="col-span-2 rounded-3xl bg-gray-50 dark:bg-white/5 p-8 border border-gray-100 dark:border-white/5 shadow-sm transform transition hover:-translate-y-1 duration-300"><div class="flex items-center gap-4 mb-4"><div class="size-12 rounded-2xl bg-primary-600 text-white grid place-items-center">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-wallet",
        class: "size-6"
      }, null, _parent));
      _push(`</div><h3 class="text-xl font-bold text-gray-900 dark:text-white">${ssrInterpolate(unref(affiliateCta).stat1_title)}</h3></div><p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">${ssrInterpolate(unref(affiliateCta).stat1_description)}</p></div><div class="rounded-3xl bg-primary-600 p-8 text-white shadow-xl transform transition hover:-translate-y-1 duration-300">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-trending-up",
        class: "size-8 mb-4 opactiy-80"
      }, null, _parent));
      _push(`<p class="text-3xl font-black">${ssrInterpolate(unref(affiliateCta).stat2_value)}</p><p class="text-xs font-bold uppercase tracking-wider text-primary-100 mt-1">${ssrInterpolate(unref(affiliateCta).stat2_label)}</p></div><div class="rounded-3xl bg-indigo-600 p-8 text-white shadow-xl transform transition hover:-translate-y-1 duration-300">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-globe",
        class: "size-8 mb-4 opactiy-80"
      }, null, _parent));
      _push(`<p class="text-3xl font-black">${ssrInterpolate(unref(affiliateCta).stat3_value)}</p><p class="text-xs font-bold uppercase tracking-wider text-indigo-100 mt-1">${ssrInterpolate(unref(affiliateCta).stat3_label)}</p></div></div><div class="absolute -right-6 bottom-12 z-20 hidden sm:flex items-center gap-3 rounded-2xl bg-white dark:bg-gray-800 p-4 shadow-2xl border border-gray-100 dark:border-white/10 ring-8 ring-white dark:ring-gray-950"><div class="size-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 grid place-items-center">`);
      _push(ssrRenderComponent(_component_UIcon, {
        name: "i-lucide-car",
        class: "size-6 text-orange-600"
      }, null, _parent));
      _push(`</div><div><p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase">${ssrInterpolate(unref(affiliateCta).floating_label)}</p><p class="text-sm font-bold text-gray-900 dark:text-white">${ssrInterpolate(unref(affiliateCta).floating_value)}</p></div></div></div><div class="lg:w-1/2"><div class="max-w-xl"><p class="text-sm font-bold uppercase tracking-widest text-primary-600 dark:text-primary-500 mb-4">${ssrInterpolate(unref(affiliateCta).badge_label)}</p><h2 class="text-4xl font-black tracking-tight text-gray-900 dark:text-white sm:text-6xl leading-[1.1]">${ssrInterpolate(unref(affiliateCta).heading_main)} `);
      if (unref(affiliateCta).heading_sub) {
        _push(`<!--[--><br>${ssrInterpolate(unref(affiliateCta).heading_sub)}<!--]-->`);
      } else {
        _push(`<!---->`);
      }
      _push(`</h2><p class="mt-6 text-lg text-gray-600 dark:text-gray-400 leading-relaxed">${ssrInterpolate(unref(affiliateCta).description)}</p>`);
      if (unref(affiliateCta).benefits.length) {
        _push(`<div class="mt-10 space-y-6"><!--[-->`);
        ssrRenderList(unref(affiliateCta).benefits, (b) => {
          _push(`<div class="flex items-start gap-4"><div class="mt-1 grid size-8 shrink-0 place-items-center rounded-lg bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-500">`);
          _push(ssrRenderComponent(_component_UIcon, {
            name: b.icon,
            class: "size-4.5"
          }, null, _parent));
          _push(`</div><div><h4 class="font-bold text-gray-900 dark:text-white">${ssrInterpolate(b.label)} - ${ssrInterpolate(b.value)}</h4><p class="text-sm text-gray-500 dark:text-gray-500 mt-0.5">${ssrInterpolate(b.description)}</p></div></div>`);
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="mt-12 flex flex-col sm:flex-row items-center gap-4">`);
      _push(ssrRenderComponent(_component_UButton, {
        to: unref(affiliateCta).primary_cta_url,
        size: "xl",
        block: "",
        class: "sm:w-auto rounded-2xl px-12 py-4 font-bold shadow-xl shadow-primary-500/10"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(unref(affiliateCta).primary_cta_label)}`);
          } else {
            return [
              createTextVNode(toDisplayString(unref(affiliateCta).primary_cta_label), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      if (hasSecondaryCta.value) {
        _push(ssrRenderComponent(_component_UButton, {
          to: unref(affiliateCta).secondary_cta_url,
          variant: "outline",
          color: "neutral",
          size: "xl",
          block: "",
          class: "sm:w-auto rounded-2xl px-10 border-gray-200 dark:border-white/10"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(unref(affiliateCta).secondary_cta_label)}`);
            } else {
              return [
                createTextVNode(toDisplayString(unref(affiliateCta).secondary_cta_label), 1)
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div>`);
      if (unref(affiliateCta).footer_note) {
        _push(`<p class="mt-6 text-center sm:text-left text-xs text-gray-400 dark:text-gray-500">${ssrInterpolate(unref(affiliateCta).footer_note)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div></div></div></section>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/home/AffiliateCTA.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "TestimonialSection",
  __ssrInlineRender: true,
  props: {
    testimonials: {}
  },
  setup(__props) {
    const placeholders = [
      { id: 1, name: "Sarah Wijaya", role: "Fashion Enthusiast", rating: 5, text: "Kualitas produknya luar biasa! Pengiriman cepat dan packaging sangat rapi. Pasti akan belanja lagi di sini." },
      { id: 2, name: "Budi Santoso", role: "Tech Reviewer", rating: 5, text: "Harga competitive dengan produk original. Customer service sangat responsif dan membantu." },
      { id: 3, name: "Anita Putri", role: "Beauty Blogger", rating: 4, text: "Koleksi skincare-nya lengkap banget! Semua produk original dan ada garansi. Sangat direkomendasikan." },
      { id: 4, name: "Reza Mahendra", role: "Athlete", rating: 5, text: "Sepatu olahraga yang saya beli kualitasnya top. Proses belanja mudah dan pengirimannya on time." },
      { id: 5, name: "Dewi Lestari", role: "Interior Designer", rating: 5, text: "Produk home decor-nya cantik-cantik dan berkualitas premium. Harga juga sangat worth it!" },
      { id: 6, name: "Fikri Rahman", role: "Gadget Lover", rating: 4, text: "Banyak pilihan gadget dengan harga lebih murah dari toko lain. Garansi resmi juga tersedia." }
    ];
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCarousel = _sfc_main$8;
      const _component_UIcon = _sfc_main$9;
      _push(`<section${ssrRenderAttrs(mergeProps({ class: "py-16 sm:py-20 bg-gray-50/50 dark:bg-gray-900/30" }, _attrs))}><div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div class="text-center mb-12"><h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl"> Kata Pelanggan Kami </h2><p class="mt-2 text-sm text-gray-500 dark:text-gray-400"> Ribuan pelanggan puas berbelanja bersama kami </p></div>`);
      _push(ssrRenderComponent(_component_UCarousel, {
        items: __props.testimonials ?? placeholders,
        loop: "",
        autoplay: { delay: 4e3 },
        ui: {
          item: "basis-[90%] sm:basis-1/2 lg:basis-1/3"
        }
      }, {
        default: withCtx(({ item }, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="h-full rounded-2xl border border-gray-200/60 bg-white p-6 transition-all duration-300 hover:shadow-lg hover:shadow-gray-200/50 dark:border-white/5 dark:bg-gray-900/80 dark:hover:shadow-gray-950/50"${_scopeId}><div class="flex items-center gap-0.5 mb-4"${_scopeId}><!--[-->`);
            ssrRenderList(5, (i) => {
              _push2(ssrRenderComponent(_component_UIcon, {
                key: i,
                name: "i-lucide-star",
                class: [i <= item.rating ? "text-amber-400" : "text-gray-200 dark:text-gray-700", "size-4"]
              }, null, _parent2, _scopeId));
            });
            _push2(`<!--]--></div><p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 mb-6 line-clamp-4"${_scopeId}> &quot;${ssrInterpolate(item.text)}&quot; </p><div class="flex items-center gap-3 mt-auto"${_scopeId}><div class="grid size-10 place-items-center rounded-full bg-linear-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 text-sm font-bold text-gray-600 dark:text-gray-300"${_scopeId}>${ssrInterpolate(item.name.charAt(0))}</div><div${_scopeId}><p class="text-sm font-semibold text-gray-900 dark:text-white"${_scopeId}>${ssrInterpolate(item.name)}</p>`);
            if (item.role) {
              _push2(`<p class="text-xs text-gray-400 dark:text-gray-500"${_scopeId}>${ssrInterpolate(item.role)}</p>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "h-full rounded-2xl border border-gray-200/60 bg-white p-6 transition-all duration-300 hover:shadow-lg hover:shadow-gray-200/50 dark:border-white/5 dark:bg-gray-900/80 dark:hover:shadow-gray-950/50" }, [
                createVNode("div", { class: "flex items-center gap-0.5 mb-4" }, [
                  (openBlock(), createBlock(Fragment, null, renderList(5, (i) => {
                    return createVNode(_component_UIcon, {
                      key: i,
                      name: "i-lucide-star",
                      class: [i <= item.rating ? "text-amber-400" : "text-gray-200 dark:text-gray-700", "size-4"]
                    }, null, 8, ["class"]);
                  }), 64))
                ]),
                createVNode("p", { class: "text-sm leading-relaxed text-gray-600 dark:text-gray-300 mb-6 line-clamp-4" }, ' "' + toDisplayString(item.text) + '" ', 1),
                createVNode("div", { class: "flex items-center gap-3 mt-auto" }, [
                  createVNode("div", { class: "grid size-10 place-items-center rounded-full bg-linear-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 text-sm font-bold text-gray-600 dark:text-gray-300" }, toDisplayString(item.name.charAt(0)), 1),
                  createVNode("div", null, [
                    createVNode("p", { class: "text-sm font-semibold text-gray-900 dark:text-white" }, toDisplayString(item.name), 1),
                    item.role ? (openBlock(), createBlock("p", {
                      key: 0,
                      class: "text-xs text-gray-400 dark:text-gray-500"
                    }, toDisplayString(item.role), 1)) : createCommentVNode("", true)
                  ])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div></section>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/home/TestimonialSection.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "FeatureHighlights",
  __ssrInlineRender: true,
  setup(__props) {
    const { featureHighlights } = useStoreData();
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$9;
      if (unref(featureHighlights).length) {
        _push(`<section${ssrRenderAttrs(mergeProps({ class: "border-y border-gray-200/60 bg-white py-10 dark:border-white/5 dark:bg-gray-950/50" }, _attrs))}><div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div class="grid grid-cols-2 gap-6 sm:gap-8 lg:grid-cols-4"><!--[-->`);
        ssrRenderList(unref(featureHighlights), (feat) => {
          _push(`<div class="flex items-start gap-4 sm:flex-col sm:items-center sm:text-center"><div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gray-100 text-gray-600 transition-colors dark:bg-white/5 dark:text-gray-400">`);
          _push(ssrRenderComponent(_component_UIcon, {
            name: feat.icon,
            class: "size-5"
          }, null, _parent));
          _push(`</div><div><h3 class="text-sm font-semibold text-gray-900 dark:text-white">${ssrInterpolate(feat.title)}</h3><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">${ssrInterpolate(feat.description)}</p></div></div>`);
        });
        _push(`<!--]--></div></div></section>`);
      } else {
        _push(`<!---->`);
      }
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/home/FeatureHighlights.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$b },
  __name: "Home",
  __ssrInlineRender: true,
  props: {
    heroBanners: {},
    featuredProducts: {},
    brands: {},
    testimonials: {}
  },
  setup(__props) {
    const page = usePage();
    const categories = computed(() => page.props.categories);
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(_attrs)}>`);
      _push(ssrRenderComponent(_sfc_main$c, null, null, _parent));
      _push(ssrRenderComponent(_sfc_main$7, {
        banners: __props.heroBanners ?? []
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$1, null, null, _parent));
      _push(`<div class="relative overflow-hidden"><div class="pointer-events-none absolute inset-0 -z-10"><div class="absolute inset-0 bg-linear-to-b from-indigo-50/80 via-white/90 to-violet-50/60 dark:from-indigo-950/50 dark:via-gray-950 dark:to-purple-950/40"></div><div class="absolute -top-24 -right-20 h-96 w-96 rounded-full bg-violet-300/30 blur-3xl dark:bg-violet-700/20"></div><div class="absolute top-1/3 -left-20 h-80 w-80 rounded-full bg-blue-300/25 blur-3xl dark:bg-blue-700/15"></div><div class="absolute top-2/3 -right-16 h-72 w-72 rounded-full bg-indigo-300/25 blur-3xl dark:bg-indigo-700/15"></div><div class="absolute -bottom-20 left-1/4 h-80 w-80 rounded-full bg-purple-300/20 blur-3xl dark:bg-purple-700/12"></div><div class="absolute inset-0 bg-[radial-gradient(circle,#6366f120_1px,transparent_1px)] bg-size-[28px_28px]"></div></div>`);
      _push(ssrRenderComponent(_sfc_main$6, {
        categories: categories.value ?? [],
        "hide-background": true
      }, null, _parent));
      _push(ssrRenderComponent(_sfc_main$5, {
        products: __props.featuredProducts,
        "hide-background": true
      }, null, _parent));
      _push(`</div>`);
      _push(ssrRenderComponent(_sfc_main$4, null, null, _parent));
      _push(ssrRenderComponent(_sfc_main$3, null, null, _parent));
      _push(ssrRenderComponent(_sfc_main$2, { testimonials: __props.testimonials }, null, _parent));
      _push(`</div>`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Home.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
