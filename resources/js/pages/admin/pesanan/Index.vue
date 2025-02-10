<script setup lang="ts">
import { h, ref, watch } from "vue";
import Form from "./Form.vue";
import { useDelete } from "@/libs/hooks";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";
import { currency } from "@/libs/utils";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteProduct } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("user.name", {
        header: "Nama Customer",
    }),
    column.accessor("product.product_name", {
        header: "Nama Produk",
    }),
    column.accessor("customer_no", {
        header: "Nomor Customer",
    }),
    column.accessor("product.product_price", {
        header: "Harga",
        cell: (cell) => currency(cell.getValue() ?? 0),
    }),
    column.accessor("transaction_model.order_status", {
        header: "Status",
        cell: (cell) => {
            const order_status = cell.getValue();
            let badgeClass = "";
            let displayStatus = "";

            switch (order_status) {
                case "processing":
                    badgeClass = "badge-light-warning";
                    displayStatus = "Proses";
                    break;
                case "success":
                    badgeClass = "badge-light-success";
                    displayStatus = "Berhasil";
                    break;
                case "pending":
                    badgeClass = "badge-light-primary";
                    displayStatus = "Menunggu";
                    break;
                case "cancelled":
                    badgeClass = "badge-light-danger";
                    displayStatus = "Dibatalkan";
                    break;
                default:
                    badgeClass = "badge-light-primary";
                    displayStatus = "Menunggu";
                    break;
            }

            return h("div", [
                h("span", { class: `badge ${badgeClass}` }, displayStatus),
            ]);
        },
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
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () =>
                            deleteProduct(
                                `/master/order/delete/${cell.getValue()}`
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
            <h2 class="mb-0">Daftar Pesanan</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-order"
                url="/master/order"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
