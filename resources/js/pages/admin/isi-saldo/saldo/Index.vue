<script setup lang="ts">
import { ref } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { currency } from "@/libs/utils";

const column = createColumnHelper<User>();
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
    })
];

</script>

<template>
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
