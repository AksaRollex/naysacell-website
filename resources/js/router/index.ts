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
    },
    {
        path: "/",
        redirect: "/welcome",
        component: () => import("@/layouts/default-layout/DefaultLayout.vue"),
        meta: {
            middleware: "auth",
        },
        children: [
            {
                path: "/dashboard",
                name: "dashboard",
                component: () => import("@/pages/admin/Dashboard.vue"),
            },
            {
                path: "/profile",
                name: "admin-profile",
                component: () => import("@/pages/admin/profile/Index.vue"),
            },
            {
                path: "/admin/setting",
                name: "admin-setting",
                component: () => import("@/pages/admin/setting/Index.vue"),
            },

            {
                path: "/user/hak-akses",
                name: "mitra-hak-akses",
                component: () =>
                    import("@/pages/admin/user/hak-akses/Index.vue"),
            },
            {
                path: "/user/user-admin",
                name: "user-admin",
                component: () =>
                    import("@/pages/admin/user/user-admin/Index.vue"),
            },
            {
                path: "/user/user-mitra",
                name: "user-mitra",
                component: () =>
                    import("@/pages/admin/user/user-mitra/Index.vue"),
            },
            {
                path: "/user/user",
                name: "user-user",
                component: () => import("@/pages/admin/user/user/Index.vue"),
            },
            {
                path: "/isi-saldo/histori",
                name: "isi-saldo-histori",
                component: () =>
                    import("@/pages/admin/laporan/isi-saldo/Index.vue"),
            },
            {
                path: "/isi-saldo/topup",
                name: "isi-saldo-topup",
                component: () =>
                    import("@/pages/admin/isi-saldo/topup/Index.vue"),
            },
            {
                path: "/isi-saldo/saldo-user",
                name: "isi-saldo-saldo-user",
                component: () =>
                    import("@/pages/admin/isi-saldo/saldo/Index.vue"),
            },
            {
                path: "/laporan/grafik-penjualan",
                name: "laporan-grafik-penjualan",
                component: () =>
                    import("@/pages/admin/laporan/grafik-penjualan/Index.vue"),
            },
            {
                path: "/laporan/transaksi-prabayar",
                name: "laporan-transaksi-prabayar",
                component: () =>
                    import(
                        "@/pages/admin/laporan/transaksi-prabayar/Index.vue"
                    ),
            },
            {
                path: "/laporan/transaksi-pascabayar",
                name: "laporan-transaksi-pascabayar",
                component: () =>
                    import(
                        "@/pages/admin/laporan/transaksi-pascabayar/Index.vue"
                    ),
            },
            {
                path: "/laporan/transaksi-deposit",
                name: "laporan-transaksi-deposit",
                component: () =>
                    import("@/pages/admin/laporan/transaksi-deposit/Index.vue"),
            },
            {
                path: "/laporan/transaksi-semua",
                name: "laporan-transaksi-semua",
                component: () =>
                    import("@/pages/admin/laporan/semua-transaksi/Index.vue"),
            },
            {
                path: "/master/brand",
                name: "master-brand",
                component: () =>
                    import("@/pages/admin/master/master-brand/Index.vue"),
            },
            {
                path: "/master/operator-code",
                name: "master-operator-code",
                component: () =>
                    import(
                        "@/pages/admin/master/master-operator-code/Index.vue"
                    ),
            },

            {
                path: "/ppob",
                name: "ppob",
                component: () => import("@/pages/admin/ppob/Index.vue"),
            },
            {
                path: "/ppob/pulsapaketdata",
                name: "ppob-pulsapaketdata",
                component: () =>
                    import("@/pages/admin/ppob/tabs/PulsaPaketData.vue"),
            },
            {
                path: "/ppob/pln",
                name: "ppob-pln",
                component: () => import("@/pages/admin/ppob/tabs/PLN.vue"),
            },
            {
                path: "/ppob/pdam",
                name: "ppob-pdam",
                component: () => import("@/pages/admin/ppob/tabs/PDAM.vue"),
            },
            {
                path: "/ppob/dompetelektronik",
                name: "ppob-dompetelektronik",
                component: () =>
                    import("@/pages/admin/ppob/tabs/DompetElektronik.vue"),
            },
            {
                path: "/ppob/bpjs",
                name: "ppob-bpjs",
                component: () => import("@/pages/admin/ppob/tabs/BPJS.vue"),
            },
            {
                path: "/ppob/internet",
                name: "ppob-internet",
                component: () => import("@/pages/admin/ppob/tabs/Internet.vue"),
            },

            {
                path: "/produk/prabayar",
                name: "produk-prabayar",
                component: () =>
                    import("@/pages/admin/produk/prabayar/Index.vue"),
            },
            {
                path: "/produk/pascabayar",
                name: "produk-pascabayar",
                component: () =>
                    import("@/pages/admin/produk/pascabayar/Index.vue"),
            },
            {
                path: "/histori",
                name: "histori",
                component: () => import("@/pages/user/histori/Index.vue"),
            },
            {
                path: "/order",
                name: "order",
                component: () => import("@/pages/admin/pesanan/Index.vue"),
            },
        ],
    },

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
                },
            },
        ],
    },
    {
        path: "/password-reset",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/password-reset",
                name: "password-reset",
                component: () =>
                    import("@/pages/auth/sign-in/tabs/ResetPassword.vue"),
                meta: {
                    pageTitle: "Reset Password",
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
        NProgress.start();
    }

    const authStore = useAuthStore();
    const configStore = useConfigStore();

    if (to.meta.pageTitle) {
        document.title = `${to.meta.pageTitle} - ${
            import.meta.env.VITE_APP_NAME
        }`;
    } else {
        document.title = import.meta.env.VITE_APP_NAME as string;
    }

    configStore.resetLayoutConfig();

    if (!authStore.isAuthenticated) await authStore.verifyAuth();

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
            next({ name: "welcome" });
        }
    } else if (to.meta.middleware == "guest" && authStore.isAuthenticated) {
        next({ name: "welcome" });
    } else {
        next();
    }
});

router.afterEach(() => {
    NProgress.done();
});

export default router;
