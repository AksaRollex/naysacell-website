<script setup lang="ts">
import { h, ref, watch } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");

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
    column.accessor("transaction_type", {
        header: "Tipe",
    }),
    column.accessor("transaction_date", {
        header: "Tanggal",
    }),
    column.accessor("transaction_message", {
        header: "Pesan",
    }),
    column.accessor("transaction_user_id", {
        header: "User ID",
    }),
    column.accessor("transaction_status", {
        header: "Status",
    }),
    column.accessor("transaction_total", {
        header: "Total",
    }),
];

const refresh = () => paginateRef.value.refetch();
</script>

<template>
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Histori Transaksi</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-laporan-histori"
                url="/auth/histori"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
