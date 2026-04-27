import { defineComponent, computed, unref, withCtx, createVNode, toDisplayString, openBlock, createBlock, createCommentVNode, useSSRContext } from "vue";
import { ssrRenderComponent, ssrInterpolate, ssrRenderAttr } from "vue/server-renderer";
import { Head } from "@inertiajs/vue3";
import { u as useStoreData } from "./useStoreData-DrTMI0On.js";
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "SeoHead",
  __ssrInlineRender: true,
  props: {
    title: {},
    description: {},
    canonical: {},
    robots: {},
    image: {},
    keywords: {}
  },
  setup(__props) {
    const props = __props;
    const { appName, seoMetaTitle, seoMetaDescription, seoOgImage, seoMetaKeywords } = useStoreData();
    const siteName = computed(() => appName.value);
    const pageTitle = computed(() => props.title ?? seoMetaTitle.value ?? siteName.value);
    const ogTitle = computed(() => `${pageTitle.value} | ${siteName.value}`);
    const resolvedDescription = computed(() => props.description ?? seoMetaDescription.value ?? null);
    const resolvedImage = computed(() => props.image ?? seoOgImage.value ?? null);
    const resolvedKeywords = computed(() => {
      const kw = props.keywords ?? seoMetaKeywords.value ?? [];
      return kw.length ? kw.join(", ") : null;
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(ssrRenderComponent(unref(Head), _attrs, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<title${_scopeId}>${ssrInterpolate(pageTitle.value)}</title>`);
            if (resolvedDescription.value) {
              _push2(`<meta name="description"${ssrRenderAttr("content", resolvedDescription.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (resolvedKeywords.value) {
              _push2(`<meta name="keywords"${ssrRenderAttr("content", resolvedKeywords.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta name="robots"${ssrRenderAttr("content", __props.robots ?? "index, follow")}${_scopeId}>`);
            if (__props.canonical) {
              _push2(`<link rel="canonical"${ssrRenderAttr("href", __props.canonical)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta property="og:type" content="website"${_scopeId}><meta property="og:site_name"${ssrRenderAttr("content", siteName.value)}${_scopeId}><meta property="og:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
            if (resolvedDescription.value) {
              _push2(`<meta property="og:description"${ssrRenderAttr("content", resolvedDescription.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.canonical) {
              _push2(`<meta property="og:url"${ssrRenderAttr("content", __props.canonical)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta property="og:locale" content="id_ID"${_scopeId}>`);
            if (resolvedImage.value) {
              _push2(`<meta property="og:image"${ssrRenderAttr("content", resolvedImage.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (resolvedImage.value) {
              _push2(`<meta property="og:image:width" content="1200"${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (resolvedImage.value) {
              _push2(`<meta property="og:image:height" content="630"${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta name="twitter:card"${ssrRenderAttr("content", resolvedImage.value ? "summary_large_image" : "summary")}${_scopeId}><meta name="twitter:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
            if (resolvedDescription.value) {
              _push2(`<meta name="twitter:description"${ssrRenderAttr("content", resolvedDescription.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (resolvedImage.value) {
              _push2(`<meta name="twitter:image"${ssrRenderAttr("content", resolvedImage.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
          } else {
            return [
              createVNode("title", null, toDisplayString(pageTitle.value), 1),
              resolvedDescription.value ? (openBlock(), createBlock("meta", {
                key: 0,
                name: "description",
                content: resolvedDescription.value
              }, null, 8, ["content"])) : createCommentVNode("", true),
              resolvedKeywords.value ? (openBlock(), createBlock("meta", {
                key: 1,
                name: "keywords",
                content: resolvedKeywords.value
              }, null, 8, ["content"])) : createCommentVNode("", true),
              createVNode("meta", {
                name: "robots",
                content: __props.robots ?? "index, follow"
              }, null, 8, ["content"]),
              __props.canonical ? (openBlock(), createBlock("link", {
                key: 2,
                rel: "canonical",
                href: __props.canonical
              }, null, 8, ["href"])) : createCommentVNode("", true),
              createVNode("meta", {
                property: "og:type",
                content: "website"
              }),
              createVNode("meta", {
                property: "og:site_name",
                content: siteName.value
              }, null, 8, ["content"]),
              createVNode("meta", {
                property: "og:title",
                content: ogTitle.value
              }, null, 8, ["content"]),
              resolvedDescription.value ? (openBlock(), createBlock("meta", {
                key: 3,
                property: "og:description",
                content: resolvedDescription.value
              }, null, 8, ["content"])) : createCommentVNode("", true),
              __props.canonical ? (openBlock(), createBlock("meta", {
                key: 4,
                property: "og:url",
                content: __props.canonical
              }, null, 8, ["content"])) : createCommentVNode("", true),
              createVNode("meta", {
                property: "og:locale",
                content: "id_ID"
              }),
              resolvedImage.value ? (openBlock(), createBlock("meta", {
                key: 5,
                property: "og:image",
                content: resolvedImage.value
              }, null, 8, ["content"])) : createCommentVNode("", true),
              resolvedImage.value ? (openBlock(), createBlock("meta", {
                key: 6,
                property: "og:image:width",
                content: "1200"
              })) : createCommentVNode("", true),
              resolvedImage.value ? (openBlock(), createBlock("meta", {
                key: 7,
                property: "og:image:height",
                content: "630"
              })) : createCommentVNode("", true),
              createVNode("meta", {
                name: "twitter:card",
                content: resolvedImage.value ? "summary_large_image" : "summary"
              }, null, 8, ["content"]),
              createVNode("meta", {
                name: "twitter:title",
                content: ogTitle.value
              }, null, 8, ["content"]),
              resolvedDescription.value ? (openBlock(), createBlock("meta", {
                key: 8,
                name: "twitter:description",
                content: resolvedDescription.value
              }, null, 8, ["content"])) : createCommentVNode("", true),
              resolvedImage.value ? (openBlock(), createBlock("meta", {
                key: 9,
                name: "twitter:image",
                content: resolvedImage.value
              }, null, 8, ["content"])) : createCommentVNode("", true)
            ];
          }
        }),
        _: 1
      }, _parent));
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/SeoHead.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as _
};
