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

const { delete : deleteTransaction } = useDelete({
    onSuccess : () => paginateRef.value.refetch(),
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
    column.accessor("transaction_message", {
        header: "Pesan",
    }),
    column.accessor("transaction_status", {
        header: "Status",
    }),
    column.accessor("transaction_total", {
        header: "Total",
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
            <h2 class="mb-0">Daftar Laporan Prabayar</h2>
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
