<script setup lang="ts">
import { ref, h, watch } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import Form from "./Form.vue";
import { currency } from "@/libs/utils";

const column = createColumnHelper<User>();
const openForm = ref<boolean>(false);
const selected = ref<string>("");
    const paginateRef = ref<any>(null);

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("user_name", {
        header: "Nama User",
    }),
    column.accessor("balance", {
        header: "Saldo",
        cell: (cell) => currency(cell.getValue() ?? 0),
    }),
    column.accessor("id", {
        header : "Aksi",
        cell : ( cell ) =>
            h("div", { class : "d-flex  gap-2"}, [
            h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" })
                ),
            ])
    })
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
            <h2 class="mb-0">Saldo Pengguna</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-laporan-pascabayar"
                url="/auth/saldo-user"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
