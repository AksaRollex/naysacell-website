<script setup lang="ts">
import { h, ref, watch } from "vue";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { currency } from "@/libs/utils";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

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
    column.accessor("product_seller_price", {
        header: "HPP",
        cell: (cell) => currency(cell.getValue() ?? 0),
    }),
    column.accessor("product_buyer_price", {
        header: "Harga Jual",
        cell: (cell) => currency(cell.getValue() ?? 0),
    }),
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
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
            <h2 class="mb-0">Daftar Produk Prabayar</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-product-prepaid"
                url="/master/product/prepaid"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
