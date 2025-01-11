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
                    // {
                    //     heading: "List Mitra",
                    //     name: "user-mitra",
                    //     route: "/user/user-mitra",
                    // },
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

            // // MASTER
            // {
            //     sectionTitle: "Master",
            //     route: "/master",
            //     name: "master",
            //     keenthemesIcon: "element-11",
            //     sub: [
            //         {
            //             heading: "Brand",
            //             name: "master-brand",
            //             route: "/master/brand",
            //         },
            //         // {
            //         //     heading: "Kode Operator",
            //         //     name: "master-operator-code",
            //         //     route: "/master/operator-code",
            //         // },
            //     ],
            // },

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
                        route: "/produk/prabayar",
                    },
                    // {
                    //     heading: "Pascabayar",
                    //     name: "produk-pascabayar",
                    //     route: "/produk/pascabayar",
                    // },
                ],
            },

            // PPOB
            {
                heading: "PPOB",
                name: "PPOB",
                keenthemesIcon: "lots-shopping",
                route: "/ppob",
            },

            // HISTORI
            {
                heading: "Histori",
                name: "histori",
                keenthemesIcon: "archive",
                route: "/histori",
            },

            // LAPORAN
            {
                sectionTitle: "Laporan Transaksi",
                name: "laporan",
                keenthemesIcon: "archive",
                route: "/laporan",
                sub: [
                    {
                        heading: "Grafik Penjualan",
                        name: "laporan-grafik-penjualan",
                        route: "/laporan/grafik-penjualan",
                    },
                    {
                        heading: "Transaksi Prabayar",
                        name: "laporan-transaksi-prabayar",
                        route: "/laporan/transaksi-prabayar",
                    },
                    // {
                    //     heading: "Transaksi Pascabayar",
                    //     name: "laporan-transaksi-pascabayar",
                    //     route: "/laporan/transaksi-pascabayar",
                    // },
                    {
                        heading: "Transaksi Deposit",
                        name: "laporan-transaksi-deposit",
                        route: "/laporan/transaksi-deposit",
                    },
                    // {
                    //     heading: "Semua Transaksi",
                    //     name: "laporan-semua-transaksi",
                    //     route: "/laporan/transaksi-semua",
                    // },
                    {
                        heading: "Transaksi Pesanan",
                        name: "laporan-transaksi-pesanan",
                        route: "/laporan/transaksi-pesanan",
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
            // PESANAN
            {
                heading: "Pesanan",
                route: "/order",
                name: "order",
                keenthemesIcon: "handcart",
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
