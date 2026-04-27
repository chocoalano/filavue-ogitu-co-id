import { _ as _sfc_main$b } from "./Separator-CuyFd3xv.js";
import { _ as _sfc_main$7 } from "./Card-CvchAxCK.js";
import { _ as _sfc_main$8 } from "./Button-DLZCZWnW.js";
import { computed, defineComponent, mergeProps, withCtx, createTextVNode, toDisplayString, useSSRContext, createVNode, resolveDynamicComponent, openBlock, createBlock, Fragment, renderList, unref } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrInterpolate, ssrRenderList, ssrRenderVNode, ssrRenderAttr } from "vue/server-renderer";
import { Head } from "@inertiajs/vue3";
import { b as _export_sfc, a as _sfc_main$9 } from "./AppLayout-BFrtNV54.js";
import { _ as _sfc_main$a } from "./SeoHead-CrFJik-H.js";
import { _ as _sfc_main$5 } from "./Badge-DqskWDDq.js";
import { _ as _sfc_main$4 } from "./Breadcrumb-EXGzoFVp.js";
import { _ as _sfc_main$6 } from "./Icon-Chcm7u9q.js";
import "reka-ui";
import "@vueuse/core";
import "defu";
import "ufo";
import "./useToast-CTuSIf9f.js";
import "./usePortal-EQErrF6h.js";
import "./Input-DFvIE7JC.js";
import "./useStoreData-DrTMI0On.js";
import "reka-ui/namespaced";
import "./Checkbox-PZ4-MUog.js";
import "vaul-vue";
import "hookable";
import "ohash/utils";
import "tailwind-variants";
import "@iconify/vue";
function useArticleDetail(article, seo) {
  const publishedDate = computed(() => {
    if (!article.published_at) {
      return null;
    }
    return new Intl.DateTimeFormat("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric"
    }).format(new Date(article.published_at));
  });
  const updatedDate = computed(() => {
    if (!article.updated_at) {
      return null;
    }
    return new Intl.DateTimeFormat("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit"
    }).format(new Date(article.updated_at));
  });
  const readTimeLabel = computed(() => `${article.read_time_minutes} menit baca`);
  const jsonLdScripts = computed(() => {
    const list = seo.structured_data ?? [];
    return list.map((item) => JSON.stringify(item));
  });
  const breadcrumbItems = computed(() => [
    { label: "Home", icon: "i-lucide-home", to: "/" },
    { label: "Artikel", to: "/articles" },
    { label: article.title }
  ]);
  function headingTag(level) {
    if (level <= 1) {
      return "h1";
    }
    if (level >= 6) {
      return "h6";
    }
    return `h${level}`;
  }
  return {
    publishedDate,
    updatedDate,
    readTimeLabel,
    jsonLdScripts,
    breadcrumbItems,
    headingTag
  };
}
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "ArticleDetailHeader",
  __ssrInlineRender: true,
  props: {
    article: {},
    breadcrumbItems: {},
    publishedDate: {},
    updatedDate: {},
    readTimeLabel: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UBreadcrumb = _sfc_main$4;
      const _component_UBadge = _sfc_main$5;
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "space-y-4" }, _attrs))}>`);
      _push(ssrRenderComponent(_component_UBreadcrumb, { items: __props.breadcrumbItems }, null, _parent));
      _push(`<div class="space-y-5"><div class="flex flex-wrap items-center gap-2">`);
      _push(ssrRenderComponent(_component_UBadge, {
        color: "primary",
        variant: "soft",
        size: "sm",
        class: "rounded-full"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Artikel `);
          } else {
            return [
              createTextVNode(" Artikel ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_component_UBadge, {
        color: "neutral",
        variant: "subtle",
        size: "sm",
        class: "rounded-full"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`${ssrInterpolate(__props.readTimeLabel)}`);
          } else {
            return [
              createTextVNode(toDisplayString(__props.readTimeLabel), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      if (__props.publishedDate) {
        _push(ssrRenderComponent(_component_UBadge, {
          color: "neutral",
          variant: "subtle",
          size: "sm",
          class: "rounded-full"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(__props.publishedDate)}`);
            } else {
              return [
                createTextVNode(toDisplayString(__props.publishedDate), 1)
              ];
            }
          }),
          _: 1
        }, _parent));
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div class="space-y-2"><h1 class="text-2xl font-bold leading-tight text-highlighted sm:text-4xl">${ssrInterpolate(__props.article.title)}</h1><p class="max-w-3xl text-sm leading-relaxed text-muted sm:text-base">${ssrInterpolate(__props.article.excerpt)}</p></div>`);
      if (__props.article.tags.length > 0) {
        _push(`<div class="flex flex-wrap gap-2"><!--[-->`);
        ssrRenderList(__props.article.tags, (tag) => {
          _push(ssrRenderComponent(_component_UBadge, {
            key: `${__props.article.id}-${tag}`,
            color: "primary",
            variant: "subtle",
            class: "rounded-full"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(tag)}`);
              } else {
                return [
                  createTextVNode(toDisplayString(tag), 1)
                ];
              }
            }),
            _: 2
          }, _parent));
        });
        _push(`<!--]--></div>`);
      } else {
        _push(`<!---->`);
      }
      if (__props.updatedDate) {
        _push(`<p class="text-xs text-muted"> Terakhir diperbarui ${ssrInterpolate(__props.updatedDate)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleDetailHeader.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "ArticleContentRenderer",
  __ssrInlineRender: true,
  props: {
    blocks: {}
  },
  setup(__props) {
    const props = __props;
    function headingTag(level) {
      if (level <= 1) {
        return "h1";
      }
      if (level >= 6) {
        return "h6";
      }
      return `h${level}`;
    }
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UIcon = _sfc_main$6;
      _push(`<article${ssrRenderAttrs(mergeProps({ class: "article-content" }, _attrs))} data-v-a9336f4f><!--[-->`);
      ssrRenderList(props.blocks, (block, index) => {
        _push(`<!--[-->`);
        if (block.type === "heading") {
          ssrRenderVNode(_push, createVNode(resolveDynamicComponent(headingTag(block.level)), {
            class: ["article-heading", `article-heading--h${block.level}`]
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`${ssrInterpolate(block.text)}`);
              } else {
                return [
                  createTextVNode(toDisplayString(block.text), 1)
                ];
              }
            }),
            _: 2
          }), _parent);
        } else if (block.type === "rich_text") {
          _push(`<div class="article-rich-text" data-v-a9336f4f>${block.html ?? ""}</div>`);
        } else if (block.type === "image") {
          _push(`<figure class="article-figure" data-v-a9336f4f><div class="article-figure__media" data-v-a9336f4f>`);
          if (block.url) {
            _push(`<img${ssrRenderAttr("src", block.url)}${ssrRenderAttr("alt", block.alt || "Gambar artikel")} class="article-figure__img" loading="lazy" data-v-a9336f4f>`);
          } else {
            _push(`<div class="article-figure__placeholder" data-v-a9336f4f>`);
            _push(ssrRenderComponent(_component_UIcon, {
              name: "i-lucide-image-off",
              class: "size-7"
            }, null, _parent));
            _push(`</div>`);
          }
          _push(`</div>`);
          if (block.caption) {
            _push(`<figcaption class="article-figure__caption" data-v-a9336f4f>${ssrInterpolate(block.caption)}</figcaption>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</figure>`);
        } else if (block.type === "list") {
          ssrRenderVNode(_push, createVNode(resolveDynamicComponent(block.ordered ? "ol" : "ul"), {
            class: block.ordered ? "article-list article-list--ordered" : "article-list article-list--unordered"
          }, {
            default: withCtx((_, _push2, _parent2, _scopeId) => {
              if (_push2) {
                _push2(`<!--[-->`);
                ssrRenderList(block.items, (item, itemIndex) => {
                  _push2(`<li class="article-list__item" data-v-a9336f4f${_scopeId}>${item ?? ""}</li>`);
                });
                _push2(`<!--]-->`);
              } else {
                return [
                  (openBlock(true), createBlock(Fragment, null, renderList(block.items, (item, itemIndex) => {
                    return openBlock(), createBlock("li", {
                      key: `item-${index}-${itemIndex}`,
                      class: "article-list__item",
                      innerHTML: item
                    }, null, 8, ["innerHTML"]);
                  }), 128))
                ];
              }
            }),
            _: 2
          }), _parent);
        } else if (block.type === "quote") {
          _push(`<blockquote class="article-blockquote" data-v-a9336f4f><p data-v-a9336f4f>${ssrInterpolate(block.quote)}</p>`);
          if (block.cite) {
            _push(`<cite class="article-blockquote__cite" data-v-a9336f4f> — ${ssrInterpolate(block.cite)}</cite>`);
          } else {
            _push(`<!---->`);
          }
          _push(`</blockquote>`);
        } else if (block.type === "divider") {
          _push(`<hr class="article-divider" data-v-a9336f4f>`);
        } else {
          _push(`<details class="article-unknown" data-v-a9336f4f><summary class="article-unknown__summary" data-v-a9336f4f> Blok tidak dikenal: ${ssrInterpolate(block.block_type)}</summary><pre class="article-unknown__pre" data-v-a9336f4f>${ssrInterpolate(JSON.stringify(block.data, null, 2))}</pre></details>`);
        }
        _push(`<!--]-->`);
      });
      _push(`<!--]-->`);
      if (props.blocks.length === 0) {
        _push(`<div class="article-empty" data-v-a9336f4f> Konten artikel belum tersedia. </div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</article>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleContentRenderer.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const ArticleContentRenderer = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["__scopeId", "data-v-a9336f4f"]]);
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "ArticleRelatedPosts",
  __ssrInlineRender: true,
  props: {
    relatedArticles: {}
  },
  setup(__props) {
    const props = __props;
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UCard = _sfc_main$7;
      const _component_UButton = _sfc_main$8;
      const _component_UIcon = _sfc_main$6;
      _push(ssrRenderComponent(_component_UCard, mergeProps({ class: "rounded-2xl dark:bg-gray-950/80" }, _attrs), {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-4"${_scopeId}><div class="flex items-center justify-between gap-2"${_scopeId}><h2 class="text-lg font-semibold text-highlighted sm:text-xl"${_scopeId}>Artikel Terkait</h2>`);
            _push2(ssrRenderComponent(_component_UButton, {
              to: "/articles",
              color: "neutral",
              variant: "outline",
              size: "sm",
              "trailing-icon": "i-lucide-arrow-right"
            }, {
              default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                if (_push3) {
                  _push3(` Semua Artikel `);
                } else {
                  return [
                    createTextVNode(" Semua Artikel ")
                  ];
                }
              }),
              _: 1
            }, _parent2, _scopeId));
            _push2(`</div>`);
            if (props.relatedArticles.length === 0) {
              _push2(`<div class="rounded-2xl border border-dashed border-default p-6 text-sm text-muted"${_scopeId}> Belum ada artikel terkait. </div>`);
            } else {
              _push2(`<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"${_scopeId}><!--[-->`);
              ssrRenderList(props.relatedArticles, (article) => {
                _push2(ssrRenderComponent(_component_UCard, {
                  key: article.id,
                  class: "overflow-hidden rounded-2xl",
                  ui: { body: "p-0" }
                }, {
                  default: withCtx((_2, _push3, _parent3, _scopeId2) => {
                    if (_push3) {
                      _push3(`<div class="h-36 overflow-hidden bg-elevated"${_scopeId2}>`);
                      if (article.banner_image || article.cover_image) {
                        _push3(`<img${ssrRenderAttr("src", article.banner_image || article.cover_image || "")}${ssrRenderAttr("alt", article.title)} class="h-full w-full object-cover" loading="lazy"${_scopeId2}>`);
                      } else {
                        _push3(`<div class="grid h-full w-full place-items-center text-muted"${_scopeId2}>`);
                        _push3(ssrRenderComponent(_component_UIcon, {
                          name: "i-lucide-image-off",
                          class: "size-6"
                        }, null, _parent3, _scopeId2));
                        _push3(`</div>`);
                      }
                      _push3(`</div><div class="space-y-3 p-3"${_scopeId2}><h3 class="line-clamp-2 text-sm font-semibold leading-snug text-highlighted"${_scopeId2}>${ssrInterpolate(article.title)}</h3><p class="line-clamp-2 text-xs text-muted"${_scopeId2}>${ssrInterpolate(article.excerpt)}</p>`);
                      _push3(ssrRenderComponent(_component_UButton, {
                        to: article.url,
                        size: "xs",
                        color: "primary",
                        variant: "outline",
                        "trailing-icon": "i-lucide-arrow-right"
                      }, {
                        default: withCtx((_3, _push4, _parent4, _scopeId3) => {
                          if (_push4) {
                            _push4(` Baca `);
                          } else {
                            return [
                              createTextVNode(" Baca ")
                            ];
                          }
                        }),
                        _: 2
                      }, _parent3, _scopeId2));
                      _push3(`</div>`);
                    } else {
                      return [
                        createVNode("div", { class: "h-36 overflow-hidden bg-elevated" }, [
                          article.banner_image || article.cover_image ? (openBlock(), createBlock("img", {
                            key: 0,
                            src: article.banner_image || article.cover_image || "",
                            alt: article.title,
                            class: "h-full w-full object-cover",
                            loading: "lazy"
                          }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                            key: 1,
                            class: "grid h-full w-full place-items-center text-muted"
                          }, [
                            createVNode(_component_UIcon, {
                              name: "i-lucide-image-off",
                              class: "size-6"
                            })
                          ]))
                        ]),
                        createVNode("div", { class: "space-y-3 p-3" }, [
                          createVNode("h3", { class: "line-clamp-2 text-sm font-semibold leading-snug text-highlighted" }, toDisplayString(article.title), 1),
                          createVNode("p", { class: "line-clamp-2 text-xs text-muted" }, toDisplayString(article.excerpt), 1),
                          createVNode(_component_UButton, {
                            to: article.url,
                            size: "xs",
                            color: "primary",
                            variant: "outline",
                            "trailing-icon": "i-lucide-arrow-right"
                          }, {
                            default: withCtx(() => [
                              createTextVNode(" Baca ")
                            ]),
                            _: 1
                          }, 8, ["to"])
                        ])
                      ];
                    }
                  }),
                  _: 2
                }, _parent2, _scopeId));
              });
              _push2(`<!--]--></div>`);
            }
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-4" }, [
                createVNode("div", { class: "flex items-center justify-between gap-2" }, [
                  createVNode("h2", { class: "text-lg font-semibold text-highlighted sm:text-xl" }, "Artikel Terkait"),
                  createVNode(_component_UButton, {
                    to: "/articles",
                    color: "neutral",
                    variant: "outline",
                    size: "sm",
                    "trailing-icon": "i-lucide-arrow-right"
                  }, {
                    default: withCtx(() => [
                      createTextVNode(" Semua Artikel ")
                    ]),
                    _: 1
                  })
                ]),
                props.relatedArticles.length === 0 ? (openBlock(), createBlock("div", {
                  key: 0,
                  class: "rounded-2xl border border-dashed border-default p-6 text-sm text-muted"
                }, " Belum ada artikel terkait. ")) : (openBlock(), createBlock("div", {
                  key: 1,
                  class: "grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                }, [
                  (openBlock(true), createBlock(Fragment, null, renderList(props.relatedArticles, (article) => {
                    return openBlock(), createBlock(_component_UCard, {
                      key: article.id,
                      class: "overflow-hidden rounded-2xl",
                      ui: { body: "p-0" }
                    }, {
                      default: withCtx(() => [
                        createVNode("div", { class: "h-36 overflow-hidden bg-elevated" }, [
                          article.banner_image || article.cover_image ? (openBlock(), createBlock("img", {
                            key: 0,
                            src: article.banner_image || article.cover_image || "",
                            alt: article.title,
                            class: "h-full w-full object-cover",
                            loading: "lazy"
                          }, null, 8, ["src", "alt"])) : (openBlock(), createBlock("div", {
                            key: 1,
                            class: "grid h-full w-full place-items-center text-muted"
                          }, [
                            createVNode(_component_UIcon, {
                              name: "i-lucide-image-off",
                              class: "size-6"
                            })
                          ]))
                        ]),
                        createVNode("div", { class: "space-y-3 p-3" }, [
                          createVNode("h3", { class: "line-clamp-2 text-sm font-semibold leading-snug text-highlighted" }, toDisplayString(article.title), 1),
                          createVNode("p", { class: "line-clamp-2 text-xs text-muted" }, toDisplayString(article.excerpt), 1),
                          createVNode(_component_UButton, {
                            to: article.url,
                            size: "xs",
                            color: "primary",
                            variant: "outline",
                            "trailing-icon": "i-lucide-arrow-right"
                          }, {
                            default: withCtx(() => [
                              createTextVNode(" Baca ")
                            ]),
                            _: 1
                          }, 8, ["to"])
                        ])
                      ]),
                      _: 2
                    }, 1024);
                  }), 128))
                ]))
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/article/ArticleRelatedPosts.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$9 },
  __name: "Show",
  __ssrInlineRender: true,
  props: {
    seo: {},
    article: {},
    relatedArticles: {}
  },
  setup(__props) {
    const props = __props;
    const { publishedDate, updatedDate, readTimeLabel, jsonLdScripts, breadcrumbItems } = useArticleDetail(
      props.article,
      props.seo
    );
    const heroImage = computed(() => props.article.banner_image || props.article.cover_image || "");
    const hasHeroImage = computed(() => heroImage.value !== "");
    return (_ctx, _push, _parent, _attrs) => {
      const _component_UButton = _sfc_main$8;
      const _component_UCard = _sfc_main$7;
      const _component_USeparator = _sfc_main$b;
      _push(`<!--[-->`);
      _push(ssrRenderComponent(_sfc_main$a, {
        title: props.seo.title,
        description: props.seo.description,
        canonical: props.seo.canonical,
        robots: props.seo.robots,
        image: props.seo.image ?? void 0
      }, null, _parent));
      _push(ssrRenderComponent(unref(Head), null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<!--[-->`);
            ssrRenderList(unref(jsonLdScripts), (script, index) => {
              ssrRenderVNode(_push2, createVNode(resolveDynamicComponent("script"), {
                key: `article-show-ld-${index}`,
                type: "application/ld+json"
              }, null), _parent2, _scopeId);
            });
            _push2(`<!--]-->`);
          } else {
            return [
              (openBlock(true), createBlock(Fragment, null, renderList(unref(jsonLdScripts), (script, index) => {
                return openBlock(), createBlock(resolveDynamicComponent("script"), {
                  key: `article-show-ld-${index}`,
                  type: "application/ld+json",
                  innerHTML: script
                }, null, 8, ["innerHTML"]);
              }), 128))
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="min-h-screen bg-gray-50 dark:bg-gray-950"><div class="relative isolate">`);
      if (hasHeroImage.value) {
        _push(`<div class="absolute inset-0 -z-10"><img${ssrRenderAttr("src", heroImage.value)}${ssrRenderAttr("alt", props.article.title)} class="h-full w-full object-cover"><div class="absolute inset-0 bg-linear-to-b from-black/55 via-black/35 to-gray-50 dark:to-gray-950"></div></div>`);
      } else {
        _push(`<div class="absolute inset-0 -z-10"><div class="h-full w-full bg-linear-to-b from-gray-900/10 via-gray-50 to-gray-50 dark:from-white/5 dark:via-gray-950 dark:to-gray-950"></div></div>`);
      }
      _push(`<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="flex items-center justify-between py-6">`);
      _push(ssrRenderComponent(_component_UButton, {
        to: "/articles",
        color: "neutral",
        variant: "outline",
        icon: "i-lucide-arrow-left",
        size: "sm",
        class: "rounded-xl bg-white/80 backdrop-blur dark:bg-gray-950/60"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Kembali ke Artikel `);
          } else {
            return [
              createTextVNode(" Kembali ke Artikel ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`</div><div class="pb-10 pt-4 sm:pb-14 sm:pt-6"><div class="max-w-3xl"><div class="flex flex-wrap items-center gap-2"><span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur dark:border-white/10">${ssrInterpolate(unref(publishedDate))}</span><span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur dark:border-white/10">${ssrInterpolate(unref(readTimeLabel))}</span>`);
      if (unref(updatedDate)) {
        _push(`<span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 backdrop-blur dark:border-white/10"> Updated: ${ssrInterpolate(unref(updatedDate))}</span>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">${ssrInterpolate(props.article.title)}</h1>`);
      if (props.seo?.description) {
        _push(`<p class="mt-4 max-w-2xl text-sm leading-6 text-white/85 sm:text-base">${ssrInterpolate(props.seo.description)}</p>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div></div><div class="pointer-events-none h-8 bg-linear-to-b from-transparent to-gray-50 dark:to-gray-950"></div></div><div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">`);
      _push(ssrRenderComponent(_component_UCard, {
        class: "-mt-10 rounded-3xl backdrop-blur dark:bg-gray-950/65",
        ui: { body: "p-5 sm:p-8" }
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="space-y-8"${_scopeId}>`);
            _push2(ssrRenderComponent(_sfc_main$3, {
              article: props.article,
              "breadcrumb-items": unref(breadcrumbItems),
              "published-date": unref(publishedDate),
              "updated-date": unref(updatedDate),
              "read-time-label": unref(readTimeLabel)
            }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(_component_USeparator, { class: "dark:border-white/10" }, null, _parent2, _scopeId));
            _push2(ssrRenderComponent(ArticleContentRenderer, {
              blocks: props.article.blocks
            }, null, _parent2, _scopeId));
            _push2(`</div>`);
          } else {
            return [
              createVNode("div", { class: "space-y-8" }, [
                createVNode(_sfc_main$3, {
                  article: props.article,
                  "breadcrumb-items": unref(breadcrumbItems),
                  "published-date": unref(publishedDate),
                  "updated-date": unref(updatedDate),
                  "read-time-label": unref(readTimeLabel)
                }, null, 8, ["article", "breadcrumb-items", "published-date", "updated-date", "read-time-label"]),
                createVNode(_component_USeparator, { class: "dark:border-white/10" }),
                createVNode(ArticleContentRenderer, {
                  blocks: props.article.blocks
                }, null, 8, ["blocks"])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<div class="mt-10">`);
      _push(ssrRenderComponent(_sfc_main$1, {
        "related-articles": props.relatedArticles
      }, null, _parent));
      _push(`</div></div></div><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Article/Show.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
