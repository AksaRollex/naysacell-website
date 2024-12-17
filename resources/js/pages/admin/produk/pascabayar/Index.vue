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
    column.accessor("product_name", {
        header: "Nama Produk",
    }),
    column.accessor("product_category", {
        header: "Kategori",
    }),
    column.accessor("product_provider", {
        header: "Provider",
    }),
    column.accessor("product_transaction_admin", {
        header: "Biaya Admin",
        cell : (cell) => currency(cell.getValue() ?? 0),
    }),
    column.accessor("product_transaction_fee", {
        header: "Komisi",
        cell : (cell) => currency(cell.getValue() ?? 0),
    }),
];


</script>

<template>
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Daftar Produk Pascabayar</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-product-pascabayar"
                url="/master/product/pasca"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
