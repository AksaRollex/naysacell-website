<script setup lang="ts">
import { ref } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("user_name", {
        header: "Nama User",
    }),
    column.accessor("amount", {
        header: "Jumlah Deposit",
    }),
    column.accessor("status", {
        header: "Status Transaksi",
    }),
];

const refresh = () => paginateRef.value.refetch();
</script>

<template>
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Daftar Laporan Deposit</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-laporan-pascabayar"
                url="/auth/histori-deposit"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
