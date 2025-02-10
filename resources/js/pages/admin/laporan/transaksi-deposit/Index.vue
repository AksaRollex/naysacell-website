<script setup lang="ts">
import { ref, h } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { useDelete, useDownloadExcel } from "@/libs/hooks";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);

const formatIDR = (value: number) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const { delete: deleteDeposit } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("user.name", {
        header: "Nama User",
    }),
    column.accessor("deposit_code", {
        header: "Kode TRX",
    }),
    column.accessor("user.phone", {
        header: "Nomor",
    }),
    column.accessor("amount", {
        header: "Jumlah Deposit",
        cell: (cell) => {
            const amount = cell.getValue();
            return h("div", formatIDR(amount));
        },
    }),
    column.accessor("payment_type", {
        header : "Metode Pembayaran",
    }),
    column.accessor("paid_at", {
        header : "Tanggal Pembayaran",
    }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
            let badgeClass = "";
            let displayStatus = "";

            switch (status) {
                case "success":
                    badgeClass = "badge-light-success";
                    displayStatus = "Berhasil";
                    break;
                case "pending":
                    badgeClass = "badge-light-primary";
                    displayStatus = "Menunggu";
                    break;
                case "failed":
                    badgeClass = "badge-light-danger";
                    displayStatus = "Gagal";
                    break;
                default:
                    badgeClass = "badge-light-primary";
                    displayStatus = "Menunggu";
                    break;
            }

            return h("div", [
                h("span", { class: `badge ${badgeClass}` }, displayStatus),
            ]);
        },
    }),
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () =>
                            deleteDeposit(
                                `/master/delete-laporan-deposit/${cell.getValue()}`
                            ),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];

const { download: downloadExcelDeposit } = useDownloadExcel();
</script>

<template>
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Daftar Laporan Deposit</h2>
            <button
                class="btn btn-sm btn-danger"
                @click="downloadExcelDeposit('/master/deposit/download-excel')"
            >
                Unduh Excel
            </button>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-laporan-deposit"
                url="/auth/histori-deposit-web"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
