<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

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

watch(openForm, (val) => {
    if (!val) {
        selected.value = "";
    }
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form
        :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />

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
