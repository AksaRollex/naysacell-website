<script setup lang="ts">
import { h, ref } from "vue";
import { useDelete, useDownloadExcel } from "@/libs/hooks";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { currency } from "@/libs/utils";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);

const { delete: deleteTransaction } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("transaction_code", {
        header: "Kode TRX",
    }),
    column.accessor("transaction_number", {
        header: "No. Tujuan",
    }),
    column.accessor("transaction_date", {
        header: "Tanggal",
    }),
    column.accessor("transaction_product", {
        header: "Produk",
    }),
    column.accessor("transaction_message", {
        header: "Pesan",
    }),
    column.accessor("transaction_total", {
        header: "Total",
        cell: (cell) =>
            currency(cell.getValue(), {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }),
    }),
    column.accessor("transaction_status", {
        header: "Status",
        cell: (cell) => {
            const transaction_status = cell.getValue();
            let badgeClass = "";
            let displayStatus = "";

            switch (transaction_status) {
                case "processing":
                    badgeClass = "badge-light-warning";
                    displayStatus = "Proses";
                    break;
                case "success":
                    badgeClass = "badge-light-success";
                    displayStatus = "Berhasil";
                    break;
                case "pending":
                    badgeClass = "badge-light-primary";
                    displayStatus = "Menunggu";
                    break;
                case "cancelled":
                    badgeClass = "badge-light-danger";
                    displayStatus = "Dibatalkan";
                    break;
                default:
                    badgeClass = "badge-light-primary";
                    displayStatus = "Menunggu";
            }

            return h("div", [
                h("span", { class: `badge ${badgeClass}` }, transaction_status),
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
                            deleteTransaction(
                                `/master/delete-laporan/${cell.getValue()}`
                            ),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];

const { download: downloadExcel } = useDownloadExcel({});
</script>

<template>
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Daftar Laporan Prabayar</h2>
            <button
                class="btn btn-sm btn-danger"
                @click="downloadExcel('/master/transaction/download-excel')"
            >
                Unduh Excel
            </button>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-laporan-prabayar"
                url="/master/laporan"
                :payload="{ transaction_type: 'Prepaid' }"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
