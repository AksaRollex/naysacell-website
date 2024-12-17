<script setup lang="ts">
import { h, ref, watch } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("name_operator", {
        header: "Nama Provider",
    }),
    column.accessor("code", {
        header: "Kode Operator",
    }),
];
</script>

<template>
    <div class="card">
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-code-operator"
                url="/master/master/operator"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
