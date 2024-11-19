import {
    createRouter,
    createWebHistory,
    type RouteRecordRaw,
} from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useConfigStore } from "@/stores/config";
import NProgress from "nprogress";
import "nprogress/nprogress.css";

declare module "vue-router" {
    interface RouteMeta {
        pageTitle?: string;
        permission?: string;
    }
}

const routes: Array<RouteRecordRaw> = [
    {
        path: "/welcome",
        name: "welcome",
        component: () => import("@/pages/user/Index.vue"),
        meta: {
            pageTitle: "Landing Page",
        },
    },
    {
        path: "/",
        redirect: "/dashboard",
        component: () => import("@/layouts/default-layout/DefaultLayout.vue"),
        meta: {
            middleware: "auth",
        },
        children: [
            {
                path: "/dashboard",
                name: "dashboard",
                component: () => import("@/pages/admin/Dashboard.vue"),
                meta: {
                    pageTitle: "Dashboard",
                    breadcrumbs: ["Dashboard"],
                },
            },
            {
                path: "/profile",
                name: "admin-profile",
                component: () => import("@/pages/admin/profile/Index.vue"),
                meta: {
                    pageTitle: "Profile",
                    breadcrumbs: ["Profile"],
                },
            },
            {
                path: "/admin/setting",
                name: "admin-setting",
                component: () => import("@/pages/admin/setting/Index.vue"),
                meta: {
                    pageTitle: "Website Setting",
                    breadcrumbs: ["Website", "Setting"],
                },
            },

            // USER
            {
                path: "/user/hak-akses",
                name: "mitra-hak-akses",
                component: () =>
                    import("@/pages/admin/user/hak-akses/Index.vue"),
                meta: {
                    pageTitle: "Dafrar Role & Hak Akses",
                    breadcrumbs: ["Dashboard", "Role & Hak Akses"],
                },
            },
            {
                path: "/user/user-admin",
                name: "mitra-user-admin",
                component: () =>
                    import("@/pages/admin/user/user-admin/Index.vue"),
                meta: {
                    pageTitle: "Daftar Admin",
                    breadcrumbs: ["Dashboard", "User Admin"],
                },
            },
            {
                path: "/user/user-mitra",
                name: "mitra-user-mitra",
                component: () =>
                    import("@/pages/admin/user/user-mitra/Index.vue"),
                meta: {
                    pageTitle: "Daftar Mitra",
                    breadcrumbs: ["Dashboard", "Mitra"],
                },
            },
            {
                path: "/user/user",
                name: "mitra-user-user",
                component: () => import("@/pages/admin/user/user/Index.vue"),
                meta: {
                    pageTitle: "Daftar User",
                    breadcrumbs: ["Dashboard", "User"],
                },
            },
            // ISI SALDO
            {
                path: "/isi-saldo/histori",
                name: "isi-saldo-histori",
                component: () =>
                    import("@/pages/admin/isi-saldo/histori/Index.vue"),
                meta: {
                    pageTitle: "Histori Isi Saldo",
                    breadcrumbs: ["Admin", "Histori Isi Saldo"],
                },
            },
            {
                path: "/isi-saldo/tarik-tiket",
                name: "isi-saldo-tarik-tiket",
                component: () =>
                    import("@/pages/admin/isi-saldo/tarik-tiket/Index.vue"),
                meta: {
                    pageTitle: "Tarik Tiket",
                    breadcrumbs: ["Admin", "Tarik Tiket"],
                },
            },

            // LAPORAN

            {
                path: "/laporan/grafik-penjualan",
                name: "laporan-grafik-penjualan",
                component: () =>
                    import("@/pages/admin/laporan/grafik-penjualan/Index.vue"),
                meta: {
                    pageTitle: "Grafik Penjualan",
                    breadcrumbs: ["Admin", "Grafik Penjualan"],
                },
            },
            {
                path: "/laporan/transaksi-prabayar",
                name: "laporan-transaksi-prabayar",
                component: () =>
                    import(
                        "@/pages/admin/laporan/transaksi-prabayar/Index.vue"
                    ),
                meta: {
                    pageTitle: "Transaksi Prabayar",
                    breadcrumbs: ["Admin", "Transaksi Prabayar"],
                },
            },
            {
                path: "/laporan/transaksi-pascabayar",
                name: "laporan-transaksi-pascabayar",
                component: () =>
                    import(
                        "@/pages/admin/laporan/transaksi-pascabayar/Index.vue"
                    ),
                meta: {
                    pageTitle: "Transaksi Pascabayar",
                    breadcrumbs: ["Admin", "Transaksi Pascabayar"],
                },
            },

            // MASTER
            {
                path: "/master/brand",
                name: "master-brand",
                component: () =>
                    import("@/pages/admin/master/master-brand/Index.vue"),
                meta: {
                    pageTitle: "Brand",
                    breadcrumbs: ["Admin", "Brand"],
                },
            },
            {
                path: "/master/operator-code",
                name: "master-operator-code",
                component: () =>
                    import(
                        "@/pages/admin/master/master-operator-code/Index.vue"
                    ),
                meta: {
                    pageTitle: "Operator Code",
                    breadcrumbs: ["Admin", "Operator Code"],
                },
            },

            // PPOB
            {
                path: "/ppob",
                name: "ppob",
                component: () => import("@/pages/admin/ppob/Index.vue"),
                meta: {
                    pageTitle: "PPOB",
                    breadcrumbs: ["Admin", "PPOB"],
                },
            },

            // PRODUK

            {
                path: "/produk/prabayar",
                name: "produk-prabayar",
                component: () =>
                    import("@/pages/admin/produk/prabayar/Index.vue"),
                meta: {
                    pageTitle: "Produk Prabayar",
                    breadcrumbs: ["Produk", "Prabayar"],
                },
            },
            {
                path: "/produk/pascabayar",
                name: "produk-pascabayar",
                component: () =>
                    import("@/pages/admin/produk/pascabayar/Index.vue"),
                meta: {
                    pageTitle: "Produk Pascabayar",
                    breadcrumbs: ["Produk", "Pascabayar"],
                },
            },
        ],
    },

    // AUTH
    {
        path: "/",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/sign-in",
                name: "sign-in",
                component: () => import("@/pages/auth/sign-in/Index.vue"),
                meta: {
                    pageTitle: "Sign In",
                    middleware: "guest",
                },
            },
        ],
    },
    {
        path: "/",
        component: () => import("@/layouts/SystemLayout.vue"),
        children: [
            {
                // the 404 route, when none of the above matches
                path: "/404",
                name: "404",
                component: () => import("@/pages/errors/Error404.vue"),
                meta: {
                    pageTitle: "Error 404",
                },
            },
            {
                path: "/500",
                name: "500",
                component: () => import("@/pages/errors/Error500.vue"),
                meta: {
                    pageTitle: "Error 500",
                },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: "/404",
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior(to) {
        // If the route has a hash, scroll to the section with the specified ID; otherwise, scroll to the top of the page.
        if (to.hash) {
            return {
                el: to.hash,
                top: 80,
                behavior: "smooth",
            };
        } else {
            return {
                top: 0,
                left: 0,
                behavior: "smooth",
            };
        }
    },
});

router.beforeEach(async (to, from, next) => {
    if (to.name) {
        // Start the route progress bar.
        NProgress.start();
    }

    const authStore = useAuthStore();
    const configStore = useConfigStore();

    // current page view title
    if (to.meta.pageTitle) {
        document.title = `${to.meta.pageTitle} - ${
            import.meta.env.VITE_APP_NAME
        }`;
    } else {
        document.title = import.meta.env.VITE_APP_NAME as string;
    }

    // reset config to initial state
    configStore.resetLayoutConfig();

    // verify auth token before each page change
    if (!authStore.isAuthenticated) await authStore.verifyAuth();

    // before page access check if page requires authentication
    if (to.meta.middleware == "auth") {
        if (authStore.isAuthenticated) {
            if (
                to.meta.permission &&
                !authStore.user.permission.includes(to.meta.permission)
            ) {
                next({ name: "404" });
            } else if (to.meta.checkDetail == false) {
                next();
            }

            next();
        } else {
            next({ name: "sign-in" });
        }
    } else if (to.meta.middleware == "guest" && authStore.isAuthenticated) {
        next({ name: "dashboard" });
    } else {
        next();
    }
});

router.afterEach(() => {
    // Complete the animation of the route progress bar.
    NProgress.done();
});

export default router;
