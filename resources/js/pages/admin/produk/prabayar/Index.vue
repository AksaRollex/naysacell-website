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
    column.accessor("product_name", {
        header: "Nama Produk",
    }),
    column.accessor("product_provider", {
        header: "Provider",
    }),
    column.accessor("product_category", {
        header: "Kategori",
    }),
    column.accessor("product_seller_price", {
        header: "Seller Price",
    }),
    column.accessor("product_buyer_price", {
        header: "Buyer Price",
    }),
    column.accessor("uuid", {
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
                url="/master/prepaid"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
