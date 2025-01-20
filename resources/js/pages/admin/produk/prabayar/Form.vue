<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { User } from "@/types";
import ApiService from "@/core/services/ApiService";
import { computed } from "vue";

const data = ref({} as User);

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const formRef = ref();

const formSchema = Yup.object().shape({
    product_name: Yup.string().required("Nama Produk harus diisi "),
    product_desc: Yup.string().required("Deskripsi Produk harus diisi "),
    product_price: Yup.string().required("Harga harus diisi "),
    product_sku: Yup.string().required("Kode SKU harus diisi "),
    product_category: Yup.string().required("Kategori Produk harus diisi "),
    product_provider: Yup.string().required("Provider Produk harus diisi "),
});

function getEdit() {
    block(document.getElementById("form-product"));
    ApiService.get("master/product/prepaid/get-pbb", props.selected)
        .then((response) => {
            data.value = response.data.data;
        })
        .catch((err: any) => {
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-data"));
        });
}

function submit() {
    const formData = new FormData();
    formData.append("product_name", data.value.product_name);
    formData.append("product_desc", data.value.product_desc);
    formData.append("product_category", data.value.product_category);
    formData.append("product_provider", data.value.product_provider);
    formData.append("product_price", data.value.product_price);
    formData.append("product_sku", data.value.product_sku);

    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-data"));
    axios({
        method: "post",
        url: props.selected
            ? `/master/product/prepaid/update-pbb/${props.selected}`
            : "/master/product/prepaid/store-pbb",
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            toast.success("Data berhasil disimpan");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-data"));
        });
}

onMounted(async () => {
    if (props.selected) {
        getEdit();
    }
});

watch(
    () => props.selected,
    () => {
        if (props.selected) {
            getEdit();
        }
    }
);

const provider = [
    { id: 1, text: "Indosat" },
    { id: 2, text: "XL" },
    { id: 3, text: "Smartfren" },
    { id: 4, text: "Three" },
    { id: 5, text: "Axis" },
];

const providers = computed(() =>
    provider.map((item: any) => ({
        id: item.text,
        text: item.text,
    }))
);
</script>

<template>
    <VForm
        class="form card mb-10"
        @submit="submit"
        :validation-schema="formSchema"
        id="form-data"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Rubah" : "Tambah" }} Produk</h2>
            <button
                type="button"
                class="btn btn-sm btn-light-danger ms-auto"
                @click="emit('close')"
            >
                Batal
                <i class="la la-times-circle p-0"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Nama Produk
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="product_name"
                            autocomplete="off"
                            v-model="data.product_name"
                            placeholder="Masukkan Nama Produk"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="product_name" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Deskripsi Produk
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="product_desc"
                            autocomplete="off"
                            v-model="data.product_desc"
                            placeholder="Masukkan Deskripsi Produk"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="product_desc" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Harga Jual
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="number"
                            name="product_price"
                            autocomplete="off"
                            v-model="data.product_price"
                            placeholder="Masukkan Harga Jual"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="product_price" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Produk SKU
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="product_sku"
                            autocomplete="off"
                            v-model="data.product_sku"
                            placeholder="Masukkan Kode SKU Produk"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="product_sku" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <!--begin::Input group-->
                <div class="fv-row mb-7">
                    <label class="form-label fw-bold fs-6 required">
                        Provider
                    </label>
                    <Field name="product_provider" v-slot="{ field }">
                        <select2
                            v-bind="field"
                            placeholder="Pilih provider"
                            class="form-select-solid"
                            :options="providers"
                            v-model="data.product_provider"
                        >
                        </select2>
                    </Field>
                    <div class="fv-plugins-message-container">
                        <div class="fv-help-block">
                            <ErrorMessage name="product_provider" />
                        </div>
                    </div>
                </div>
                <!--end::Input group-->
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Produk Kategori
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="product_category"
                            autocomplete="off"
                            v-model="data.product_category"
                            placeholder="Masukkan Kategori Barang"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="product_category" />
                            </div>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
            </div>
        </div>
        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary btn-sm ms-auto">
                Simpan
            </button>
        </div>
    </VForm>
</template>
