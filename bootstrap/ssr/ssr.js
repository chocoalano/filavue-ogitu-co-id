import { createSSRApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "vue/server-renderer";
async function resolvePageComponent(path, pages) {
  for (const p of Array.isArray(path) ? path : [path]) {
    const page = pages[p];
    if (typeof page === "undefined") {
      continue;
    }
    return typeof page === "function" ? page() : page;
  }
  throw new Error(`Page not found: ${path}`);
}
createServer(
  (page) => createInertiaApp({
    page,
    render: renderToString,
    title: (title) => `${title} — ${"ogitu.id"}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, /* @__PURE__ */ Object.assign({ "./Pages/Article/Index.vue": () => import("./assets/Index-Ca253dMt.js"), "./Pages/Article/Show.vue": () => import("./assets/Show-Cl5UDY3N.js"), "./Pages/Auth/Checkout/Index.vue": () => import("./assets/Index-D8zC4dy7.js"), "./Pages/Auth/Dashboard/Index.vue": () => import("./assets/Index-CvHFOR8t.js"), "./Pages/Auth/Dashboard/partials/Addresses.vue": () => import("./assets/Addresses-BkuYO7cN.js"), "./Pages/Auth/Dashboard/partials/Bonus.vue": () => import("./assets/Bonus-CYXjmb7P.js"), "./Pages/Auth/Dashboard/partials/DashboardHome.vue": () => import("./assets/DashboardHome-BrcGqI8R.js"), "./Pages/Auth/Dashboard/partials/DeleteAccount.vue": () => import("./assets/DeleteAccount-bU1gLfP_.js"), "./Pages/Auth/Dashboard/partials/FormAccount.vue": () => import("./assets/FormAccount-D3R2zJ6V.js"), "./Pages/Auth/Dashboard/partials/GenerationNetwork.vue": () => import("./assets/GenerationNetwork-Bw6j4zaF.js"), "./Pages/Auth/Dashboard/partials/Lifetime.vue": () => import("./assets/Lifetime-D0fqOugE.js"), "./Pages/Auth/Dashboard/partials/Mitra.vue": () => import("./assets/Mitra-C4xTaPsT.js"), "./Pages/Auth/Dashboard/partials/Network.vue": () => import("./assets/Network-BQh5QNec.js"), "./Pages/Auth/Dashboard/partials/Orders.vue": () => import("./assets/Orders-BGKWAUh7.js"), "./Pages/Auth/Dashboard/partials/Promo.vue": () => import("./assets/Promo-C4wHRI-n.js"), "./Pages/Auth/Dashboard/partials/Wallet.vue": () => import("./assets/Wallet--e_1z7w7.js"), "./Pages/Auth/Dashboard/partials/Zenner.vue": () => import("./assets/Zenner-DKRV35DM.js"), "./Pages/Auth/ForgotPassword.vue": () => import("./assets/ForgotPassword-vY2fgHOJ.js"), "./Pages/Auth/Login.vue": () => import("./assets/Login-C0Wy5LXB.js"), "./Pages/Auth/Register.vue": () => import("./assets/Register-CHpOqa2D.js"), "./Pages/Auth/ResetPassword.vue": () => import("./assets/ResetPassword-CIrYuu2h.js"), "./Pages/Auth/WaConfirmationPending.vue": () => import("./assets/WaConfirmationPending-1H8ts5g1.js"), "./Pages/Home.vue": () => import("./assets/Home-Cceqym3x.js"), "./Pages/Page/Show.vue": () => import("./assets/Show-CT1GFdjA.js"), "./Pages/Shop/Index.vue": () => import("./assets/Index-CQCnTNOU.js"), "./Pages/Shop/Show.vue": () => import("./assets/Show-B9K_oUUa.js") })),
    setup({ App, props, plugin }) {
      return createSSRApp({ render: () => h(App, props) }).use(plugin);
    }
  })
);
