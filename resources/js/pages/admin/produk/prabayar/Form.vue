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

const numericPattern = /^[0-9]+$/;
const alphanumericPattern = /^[a-zA-Z0-9]+$/;

const formSchema = Yup.object().shape({
    product_name: Yup.string()
        .required("Nama Produk harus diisi ")
        .matches(
            alphanumericPattern,
            "Nama Produk hanya boleh berisi huruf dan angka"
        ),
    product_desc: Yup.string()
        .required("Deskripsi Produk harus diisi ")
        .matches(alphanumericPattern, "Deskripsi produk hanya boleh berisi huruf dan angka"),
    product_price: Yup.string()
        .required("Harga harus diisi")
        .matches(numericPattern, "Harga hanya boleh berisi angka"),
    product_sku: Yup.string()
        .required("Kode SKU harus diisi")
        .matches(alphanumericPattern, "SKU hanya boleh berisi huruf dan angka"),
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
    { id: 6, text: "Telkomsel" },
    { id: 7, text: "Dana" },
    { id: 8, text: "Gopay" },
    { id: 9, text: "OVO" },
    { id: 10, text: "Shopeepay" },
];

const category = [
    {
        id: "Pulsa",
        text: "Pulsa",
    },
    {
        id: "E-Money",
        text: "E-Money",
    },
    {
        id: "Data",
        text: "Data",
    },
];

// Computed property untuk provider yang telah difilter
const filteredProviders = computed(() => {
    const eMoneyProviders = ["Dana", "Gopay", "OVO", "Shopeepay"];

    if (data.value.product_category === "E-Money") {
        return provider
            .filter((p) => eMoneyProviders.includes(p.text))
            .map((item) => ({
                id: item.text,
                text: item.text,
            }));
    } else {
        return provider
            .filter((p) => !eMoneyProviders.includes(p.text))
            .map((item) => ({
                id: item.text,
                text: item.text,
            }));
    }
});

// Watch untuk me-reset provider ketika kategori berubah
watch(
    () => data.value.product_category,
    (newCategory) => {
        // Reset provider selection when category changes
        data.value.product_provider = "";
    }
);

const handlePriceInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    input.value = input.value.replace(/[^0-9]/g, "");
    data.value.product_price = input.value;
};
const handleNameInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    input.value = input.value.replace(/[^a-zA-Z0-9]/g, "");
    data.value.product_name = input.value;
};

const handleDescInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    input.value = input.value.replace(/[^a-zA-Z0-9]/g, "");
    data.value.product_desc = input.value;
};

const handleSkuInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    input.value = input.value.replace(/[^a-zA-Z0-9]/g, "");
    data.value.product_sku = input.value;
};
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
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Nama Produk
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="product_name"
                            autocomplete="off"
                            @input="handleNameInput"
                            v-model="data.product_name"
                            placeholder="Masukkan Nama Produk"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="product_name" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Deskripsi Produk
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="product_desc"
                            @input="handleDescInput"
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
                </div>
                <div class="col-md-4">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Harga Jual
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="product_price"
                            autocomplete="off"
                            @input="handlePriceInput"
                            v-model="data.product_price"
                            placeholder="Masukkan Harga Jual"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="product_price" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Produk SKU
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="product_sku"
                            @input="handleSkuInput"
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
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold fs-6 required">
                        Kategori
                    </label>
                    <Field name="product_category" v-slot="{ field }">
                        <select2
                            v-bind="field"
                            placeholder="Pilih Kategori"
                            class="form-select-solid"
                            :options="category"
                            v-model="data.product_category"
                        >
                        </select2>
                    </Field>
                    <div class="fv-plugins-message-container">
                        <div class="fv-help-block">
                            <ErrorMessage name="product_category" />
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold fs-6 required">
                        Provider
                    </label>
                    <Field name="product_provider" v-slot="{ field }">
                        <select2
                            v-bind="field"
                            placeholder="Pilih Provider"
                            class="form-select-solid"
                            :options="filteredProviders"
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
            </div>
        </div>
        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary btn-sm ms-auto">
                Simpan
            </button>
        </div>
    </VForm>
</template>
