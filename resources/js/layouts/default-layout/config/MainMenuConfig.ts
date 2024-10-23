import type { MenuItem } from "@/layouts/default-layout/config/types";
import { createPopperBase } from "@popperjs/core";

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

    // WEBSITE
    {
        route: "/dashboard/website",
        name: "website",
        pages: [
            // USER / MITRA
            {
                sectionTitle: "User / Mitra",
                route: "/admin",
                keenthemesIcon: "people",
                name: "mitra",
                sub: [
                    {
                        heading: "User Admin",
                        name: "mitra-user-admin",
                        route: "/user/user-admin",
                    },
                    {
                        heading: "User Mitra",
                        name: "mitra-user-mitra",
                        route: "/user/user-mitra",
                    },
                    {
                        heading: "Hak Akses",
                        name: "mitra-hak-akses",
                        route: "/user/hak-akses",
                    },
                ],
            },

            // MASTER
            {
                sectionTitle: "Master",
                route: "/master",
                name: "master",
                keenthemesIcon: "element-11",
                sub: [
                    {
                        heading: "Brand",
                        name: "master-brand",
                        route : "/master/brand",
                    },
                    {
                        heading: "Kode Operator",
                        name: "master-operator-code",
                        route : "/master/operator-code",
                    },
                ],
            },

            // PRODUK
            {
                sectionTitle: "Produk",
                route: "/produk",
                name: "produk",
                keenthemesIcon: "purchase",
                sub: [
                    {
                        heading: "Prabayar",
                        name: "produk-prabayar",
                        route : "/produk/prabayar",
                    },
                    {
                        heading: "Pascabayar",
                        name: "produk-pascabayar",
                        route : "/produk/pascabayar",
                    },
                ],
            },

            // PPOB
            {
                heading: "PPOB",
                name: "PPOB",
                keenthemesIcon: "lots-shopping",
                route : "/ppob",
            },

            // LAPORAN
            {
                sectionTitle: "Laporan",
                name: "laporan",
                keenthemesIcon: "archive",
                route: "/laporan",
                sub: [
                    {
                        heading: "Grafik Penjualan",
                        name: "laporan-grafik-penjualan",
                        route : "/laporan/grafik-penjualan",
                    },
                    {
                        heading: "Transaksi Prabayar",
                        name: "laporan-transaksi-prabayar",
                        route : "/laporan/transaksi-prabayar",
                    },
                    {
                        heading: "Transaksi Pascabayar",
                        name: "laporan-transaksi-pascabayar",
                        route : "/laporan/transaksi-pascabayar",
                    },
                ],
            },

            // ISI SALDO
            {
                sectionTitle: "Isi Saldo",
                route: "/isi-saldo",
                name: "isi-saldo",
                keenthemesIcon: "wallet",
                sub: [
                    {
                        heading: "Tarik Tiket",
                        name: "isi-saldo-tarik-tiket",
                        route : "/isi-saldo/tarik-tiket",
                    },
                    {
                        heading: "Histori Isi Saldo",
                        name: "isi-saldo-histori",
                        route : "/isi-saldo/histori",
                    },
                ],
            },

            // SETTING
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
