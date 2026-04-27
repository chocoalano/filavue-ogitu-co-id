import { reactive, computed, unref, isRef, ref, toRef, inject, mergeProps, createVNode, resolveDynamicComponent, useSSRContext } from "vue";
import { createHooks } from "hookable";
import { createSharedComposable, useColorMode as useColorMode$1 } from "@vueuse/core";
import { defu } from "defu";
import { isEqual } from "ohash/utils";
import { createTV } from "tailwind-variants";
import { ssrRenderComponent, ssrRenderVNode } from "vue/server-renderer";
import { Icon } from "@iconify/vue";
const appConfig = { "ui": { "colors": { "primary": "zinc", "secondary": "blue", "success": "green", "info": "blue", "warning": "yellow", "error": "red", "neutral": "slate" }, "icons": { "arrowDown": "i-lucide-arrow-down", "arrowLeft": "i-lucide-arrow-left", "arrowRight": "i-lucide-arrow-right", "arrowUp": "i-lucide-arrow-up", "caution": "i-lucide-circle-alert", "check": "i-lucide-check", "chevronDoubleLeft": "i-lucide-chevrons-left", "chevronDoubleRight": "i-lucide-chevrons-right", "chevronDown": "i-lucide-chevron-down", "chevronLeft": "i-lucide-chevron-left", "chevronRight": "i-lucide-chevron-right", "chevronUp": "i-lucide-chevron-up", "close": "i-lucide-x", "copy": "i-lucide-copy", "copyCheck": "i-lucide-copy-check", "dark": "i-lucide-moon", "drag": "i-lucide-grip-vertical", "ellipsis": "i-lucide-ellipsis", "error": "i-lucide-circle-x", "external": "i-lucide-arrow-up-right", "eye": "i-lucide-eye", "eyeOff": "i-lucide-eye-off", "file": "i-lucide-file", "folder": "i-lucide-folder", "folderOpen": "i-lucide-folder-open", "hash": "i-lucide-hash", "info": "i-lucide-info", "light": "i-lucide-sun", "loading": "i-lucide-loader-circle", "menu": "i-lucide-menu", "minus": "i-lucide-minus", "panelClose": "i-lucide-panel-left-close", "panelOpen": "i-lucide-panel-left-open", "plus": "i-lucide-plus", "reload": "i-lucide-rotate-ccw", "search": "i-lucide-search", "stop": "i-lucide-square", "success": "i-lucide-circle-check", "system": "i-lucide-monitor", "tip": "i-lucide-lightbulb", "upload": "i-lucide-upload", "warning": "i-lucide-triangle-alert" }, "tv": { "twMergeConfig": {} } }, "colorMode": true };
const _appConfig = reactive(appConfig);
const useAppConfig = () => _appConfig;
// @__NO_SIDE_EFFECTS__
function defineLocale(options) {
  return defu(options, { dir: "ltr" });
}
function omit(data, keys) {
  const result = { ...data };
  for (const key of keys) {
    delete result[key];
  }
  return result;
}
function get(object, path, defaultValue) {
  if (typeof path === "string") {
    path = path.split(".").map((key) => {
      const numKey = Number(key);
      return Number.isNaN(numKey) ? key : numKey;
    });
  }
  let result = object;
  for (const key of path) {
    if (result === void 0 || result === null) {
      return defaultValue;
    }
    result = result[key];
  }
  return result !== void 0 ? result : defaultValue;
}
function looseToNumber(val) {
  const n = Number.parseFloat(val);
  return Number.isNaN(n) ? val : n;
}
function compare(value, currentValue, comparator) {
  if (value === void 0 || currentValue === void 0) {
    return false;
  }
  if (typeof value === "string") {
    return value === currentValue;
  }
  if (typeof comparator === "function") {
    return comparator(value, currentValue);
  }
  if (typeof comparator === "string") {
    return get(value, comparator) === get(currentValue, comparator);
  }
  return isEqual(value, currentValue);
}
function isEmpty(value) {
  if (value == null) {
    return true;
  }
  if (typeof value === "boolean" || typeof value === "number") {
    return false;
  }
  if (typeof value === "string") {
    return value.trim().length === 0;
  }
  if (Array.isArray(value)) {
    return value.length === 0;
  }
  if (value instanceof Map || value instanceof Set) {
    return value.size === 0;
  }
  if (value instanceof Date || value instanceof RegExp || typeof value === "function") {
    return false;
  }
  if (typeof value === "object") {
    for (const _ in value) {
      if (Object.prototype.hasOwnProperty.call(value, _)) {
        return false;
      }
    }
    return true;
  }
  return false;
}
function getDisplayValue(items, value, options = {}) {
  const { valueKey, labelKey, by } = options;
  const foundItem = items.find((item) => {
    const itemValue = typeof item === "object" && item !== null && valueKey ? get(item, valueKey) : item;
    return compare(itemValue, value, by);
  });
  if (isEmpty(value) && foundItem) {
    return labelKey ? get(foundItem, labelKey) : void 0;
  }
  if (isEmpty(value)) {
    return void 0;
  }
  const source = foundItem ?? value;
  if (source === null || source === void 0) {
    return void 0;
  }
  if (typeof source === "object") {
    return labelKey ? get(source, labelKey) : void 0;
  }
  return String(source);
}
function isArrayOfArray(item) {
  return Array.isArray(item[0]);
}
function mergeClasses(appConfigClass, propClass) {
  if (!appConfigClass && !propClass) {
    return "";
  }
  return [
    ...Array.isArray(appConfigClass) ? appConfigClass : [appConfigClass],
    propClass
  ].filter(Boolean);
}
function getSlotChildrenText(children) {
  return children.map((node) => {
    if (!node.children || typeof node.children === "string") return node.children || "";
    else if (Array.isArray(node.children)) return getSlotChildrenText(node.children);
    else if (node.children.default) return getSlotChildrenText(node.children.default());
  }).join("");
}
function buildTranslator(locale) {
  return (path, option) => translate(path, option, unref(locale));
}
function translate(path, option, locale) {
  const prop = get(locale, `messages.${path}`, path);
  return prop.replace(
    /\{(\w+)\}/g,
    (_, key) => `${option?.[key] ?? `{${key}}`}`
  );
}
function buildLocaleContext(locale) {
  const lang = computed(() => unref(locale).name);
  const code = computed(() => unref(locale).code);
  const dir = computed(() => unref(locale).dir);
  const localeRef = isRef(locale) ? locale : ref(locale);
  return {
    lang,
    code,
    dir,
    locale: localeRef,
    t: buildTranslator(locale)
  };
}
const en = /* @__PURE__ */ defineLocale({
  name: "English",
  code: "en",
  messages: {
    alert: {
      close: "Close"
    },
    authForm: {
      hidePassword: "Hide password",
      showPassword: "Show password",
      submit: "Continue"
    },
    banner: {
      close: "Close"
    },
    calendar: {
      nextMonth: "Next month",
      nextYear: "Next year",
      prevMonth: "Previous month",
      prevYear: "Previous year"
    },
    carousel: {
      dots: "Choose slide to display",
      goto: "Go to slide {slide}",
      next: "Next",
      prev: "Prev"
    },
    chatPrompt: {
      placeholder: "Type your message here…"
    },
    chatPromptSubmit: {
      label: "Send prompt"
    },
    colorMode: {
      dark: "Dark",
      light: "Light",
      switchToDark: "Switch to dark mode",
      switchToLight: "Switch to light mode",
      system: "System"
    },
    commandPalette: {
      back: "Back",
      close: "Close",
      noData: "No data",
      noMatch: "No matching data",
      placeholder: "Type a command or search…"
    },
    contentSearch: {
      links: "Links",
      theme: "Theme"
    },
    contentSearchButton: {
      label: "Search…"
    },
    contentToc: {
      title: "On this page"
    },
    dashboardSearch: {
      theme: "Theme"
    },
    dashboardSearchButton: {
      label: "Search…"
    },
    dashboardSidebarCollapse: {
      collapse: "Collapse sidebar",
      expand: "Expand sidebar"
    },
    dashboardSidebarToggle: {
      close: "Close sidebar",
      open: "Open sidebar"
    },
    error: {
      clear: "Back to home"
    },
    fileUpload: {
      removeFile: "Remove {filename}"
    },
    header: {
      close: "Close menu",
      open: "Open menu"
    },
    inputMenu: {
      create: 'Create "{label}"',
      noData: "No data",
      noMatch: "No matching data"
    },
    inputNumber: {
      decrement: "Decrement",
      increment: "Increment"
    },
    modal: {
      close: "Close"
    },
    pricingTable: {
      caption: "Pricing plan comparison"
    },
    prose: {
      codeCollapse: {
        closeText: "Collapse",
        name: "code",
        openText: "Expand"
      },
      collapsible: {
        closeText: "Hide",
        name: "properties",
        openText: "Show"
      },
      pre: {
        copy: "Copy code to clipboard"
      }
    },
    selectMenu: {
      create: 'Create "{label}"',
      noData: "No data",
      noMatch: "No matching data",
      search: "Search…"
    },
    slideover: {
      close: "Close"
    },
    table: {
      noData: "No data"
    },
    toast: {
      close: "Close"
    }
  }
});
const localeContextInjectionKey = /* @__PURE__ */ Symbol.for("nuxt-ui.locale-context");
const _useLocale = (localeOverrides) => {
  const locale = localeOverrides || toRef(inject(localeContextInjectionKey, en));
  return buildLocaleContext(computed(() => locale.value || en));
};
const useLocale = createSharedComposable(_useLocale);
const useColorMode = () => {
  if (!appConfig.colorMode) {
    return {
      forced: true
    };
  }
  const { store, system } = useColorMode$1();
  return {
    get preference() {
      return store.value === "auto" ? "system" : store.value;
    },
    set preference(value) {
      store.value = value === "system" ? "auto" : value;
    },
    get value() {
      return store.value === "auto" ? system.value : store.value;
    },
    forced: false
  };
};
const state = {};
const useState = (key, init) => {
  if (state[key]) {
    return state[key];
  }
  const value = ref(init());
  state[key] = value;
  return value;
};
createHooks();
const appConfigTv = appConfig;
const tv = /* @__PURE__ */ createTV(appConfigTv.ui?.tv);
const _sfc_main = {
  __name: "Icon",
  __ssrInlineRender: true,
  props: {
    name: { type: null, required: true }
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      if (typeof __props.name === "string") {
        _push(ssrRenderComponent(unref(Icon), mergeProps({
          icon: __props.name.replace(/^i-/, "")
        }, _attrs), null, _parent));
      } else {
        ssrRenderVNode(_push, createVNode(resolveDynamicComponent(__props.name), _attrs, null), _parent);
      }
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("node_modules/@nuxt/ui/dist/runtime/vue/components/Icon.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as _,
  useLocale as a,
  get as b,
  getDisplayValue as c,
  compare as d,
  localeContextInjectionKey as e,
  useColorMode as f,
  getSlotChildrenText as g,
  useState as h,
  isArrayOfArray as i,
  looseToNumber as l,
  mergeClasses as m,
  omit as o,
  tv as t,
  useAppConfig as u
};
