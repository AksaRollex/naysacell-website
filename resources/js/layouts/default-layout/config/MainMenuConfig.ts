import type { MenuItem } from "@/layouts/default-layout/config/types";

const MainMenuConfig: Array<MenuItem> = [
    {
        pages: [
            {
                heading: "Dashboard",
                name: "dashboard",
                route: "/dashboard",
            },
        ],
    },
    {
        route: "/dashboard/website",
        name: "website",
        pages: [
            {
                sectionTitle: "User",
                route: "/admin",
                keenthemesIcon: "people",
                name: "user",
                sub: [
                    {
                        heading: "List Admin",
                        name: "user-admin",
                        route: "/user/user-admin",
                    },
                    {
                        heading: "List User",
                        name: "user",
                        route: "/user/user",
                    },
                    {
                        heading: "List Role & Hak Akses",
                        name: "hak-akses",
                        route: "/user/hak-akses",
                    },
                ],
            },
            {
                sectionTitle: "Produk",
                route: "/produk",
                name: "produk",
                keenthemesIcon: "purchase",
                sub: [
                    {
                        heading: "Prabayar",
                        name: "produk-prabayar",
                        route: "/produk/prabayar",
                    },
                ],
            },
            {
                heading: "Histori",
                name: "histori",
                keenthemesIcon: "archive",
                route: "/histori",
            },
            {
                sectionTitle: "Laporan Transaksi",
                name: "laporan",
                keenthemesIcon: "archive",
                route: "/laporan",
                sub: [
                    {
                        heading: "Transaksi Prabayar",
                        name: "laporan-transaksi-prabayar",
                        route: "/laporan/transaksi-prabayar",
                    },
                    {
                        heading: "Transaksi Deposit",
                        name: "laporan-transaksi-deposit",
                        route: "/laporan/transaksi-deposit",
                    },
                    {
                        heading: "Transaksi Pesanan",
                        name: "laporan-transaksi-pesanan",
                        route: "/laporan/transaksi-pesanan",
                    },
                ],
            },
            {
                sectionTitle: "Saldo",
                route: "/isi-saldo",
                name: "isi-saldo",
                keenthemesIcon: "wallet",
                sub: [
                    {
                        heading: "Topup",
                        name: "isi-saldo-topup",
                        route: "/isi-saldo/topup",
                    },
                    {
                        heading: "Saldo Pengguna",
                        name: "isi-saldo-saldo-user",
                        route: "/isi-saldo/saldo-user",
                    },
                ],
            },
            {
                heading: "PPOB",
                name: "PPOB",
                keenthemesIcon: "lots-shopping",
                route: "/ppob",
            },
            {
                heading: "Pesanan",
                route: "/order",
                name: "order",
                keenthemesIcon: "handcart",
            },
            {
                heading: "Setting",
                route: "/admin/setting",
                name: "setting",
                keenthemesIcon: "setting-2",
            },
        ],
    },
];

export default MainMenuConfig;
