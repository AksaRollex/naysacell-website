<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { computed } from "vue";
import ApiService from "@/core/services/ApiService";

const data = ref({
    order_status: "",
});

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const formRef = ref();

const formSchema = Yup.object().shape({
    order_status: Yup.string().required("Status Pesanan harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-product"));
    ApiService.get("master/order/get", props.selected)
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
    formData.append("order_status", String(data.value.order_status));

    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-data"));
    axios({
        method: "post",
        url: props.selected ? `/master/order/update/${props.selected}` : "",
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

const status = [
    {
        id: "Pending",
        text: "Pending",
    },
    {
        id: "success",
        text: "Sukses",
    },
    {
        id: "Cancelled",
        text: "Dibatalkan",
    },
    {
        id: "Processing",
        text: "Proses",
    },
];

const statuses = computed(() =>
    status.map((item: any) => ({
        id: item.id,
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
            <h2 class="mb-0">
                {{ selected ? "Rubah" : "Tambah" }} Status Pesanan
            </h2>
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
                <div class="col-md-12">
                    
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Status
                        </label>
                        <Field name="order_status" v-slot="{ field }">
                            <select2
                                v-bind="field"
                                placeholder="Pilih Status"
                                class="form-select-solid"
                                :options="statuses"
                                v-model="data.order_status"
                            >
                            </select2>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="order_status" />
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
