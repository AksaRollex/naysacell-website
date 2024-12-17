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
    column.accessor("provider_name", {
        header: "Nama Brand",
    }),
    column.accessor("provider_photo", {
        header: "Foto Brand",

        cell: (info) => {
            const photoPath = info.getValue();
            return h("img", {
                src: photoPath,
                alt: "Brand Logo",
                class: "img-fluid",
                style: "max-width: 100px; max-height: 50px; object-fit: contain;",
            });
        },
    }),
];
</script>

<template>
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Daftar Brand</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-brand"
                url="/master/master/brand"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
