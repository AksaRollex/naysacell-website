<script setup lang="ts">
import { ref, h } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { useDownloadExcel } from "@/libs/hooks";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const openForm = ref<boolean>(false);

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("deposit_code", {
        header: "Kode TRX",
    }),
    column.accessor("user_name", {
        header: "Nama User",
    }),
    column.accessor("user_number", {
        header: "No. User",
    }),
    column.accessor("amount", {
        header: "Jumlah Deposit",
    }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
            let badgeClass = "";

            switch (status) {
                case "success":
                    badgeClass = "badge-light-success";
                    break;
                case "pending":
                    badgeClass = "badge-light-warning";
                    break;
                case "failed":
                    badgeClass = "badge-light-danger";
                    break;
                default:
                    badgeClass = "badge-light-primary";
            }

            return h("div", [
                h("span", { class: `badge ${badgeClass}` }, status),
            ]);
        },
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
