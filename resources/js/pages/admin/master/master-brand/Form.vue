<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { User } from "@/types";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const data = ref<User>({} as User);
const product_photo = ref<any>([]);
const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
const formRef = ref();

const formSchema = Yup.object().shape({
    product_name: Yup.string().required("nama produk harus diisi"),
    product_photo : Yup.string().required("foto produk harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-brand"));
    ApiService.get("/master/master/brand/get", props.selected)
        .then(({ data }) => {
            data.value = data.data;
            product_photo.value = data.data.product_photo
                ? ["/storage/" + data.data.product_photo]
                : [];
        })
        .catch((err: any) => {
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-user"));
        });
}

function submit() {
    const formData = new FormData();
    formData.append("provider_name", data.value.provider_name);
    formData.append("provider_photo",  data.value.provider_photo);

    if (product_photo.value.length) {
        formData.append("product_photo", product_photo.value[0].file);
    }
    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-user"));
    axios({
        method: "post",
        url: props.selected
            ? `/master/master/brand/update/${props.selected}`
            : "/master/master/brand/store",
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
            unblock(document.getElementById("form-user"));
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
</script>

<template>
    <VForm
        class="form card mb-10"
        @submit="submit"
        :validation-schema="formSchema"
        id="form-user"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Brand</h2>
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
                <div class="col-md-6">
                    
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Nama
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="provider_name"
                            autocomplete="off"
                            v-model="data.provider_name"
                            placeholder="Masukkan Nama"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="provider_name" />
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-6">
                    
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">
                            Foto Brand
                        </label>
                        <!--begin::Input-->
                        <file-upload
                            :files="product_photo"
                            :accepted-file-types="fileTypes"
                            required
                            v-on:updatefiles="(file) => (product_photo = file)"
                        ></file-upload>
                        <!--end::Input-->
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="product_photo" />
                            </div>
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
