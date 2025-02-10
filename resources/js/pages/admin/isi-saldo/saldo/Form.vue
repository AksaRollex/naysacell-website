<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
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

const user = ref<User>({} as User);
const formRef = ref();

const numericPattern = /^[0-9]+$/;

const formSchema = Yup.object().shape({
    balance: Yup.string()
        .matches(numericPattern, "saldo hanya boleh berisi angka")
        .required("saldo harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-user-balance"));
    ApiService.get(`auth/edit-saldo/${props.selected}`)
        .then(({ data }) => {
            if (data && data.data) {
                user.value = data.data;
            }
        })
        .catch((err: any) => {
            toast.error(err.response?.data?.message || "Terjadi kesalahan");
        })
        .finally(() => {
            unblock(document.getElementById("form-user-balance"));
        });
}

function submit() {
    const formData = new FormData();
    formData.append("balance", user.value.balance);

    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-user-balance"));
    axios({
        method: "post",
        url: props.selected ? `/auth/update-saldo/${props.selected}` : "",
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
            unblock(document.getElementById("form-user-balance"));
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

const handleBalanceInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    let balance = input.value.replace(/[^0-9]/g, "");
    user.value.balance = balance;
    input.value = balance;
};
</script>

<template>
    <VForm
        class="form card mb-10"
        @submit="submit"
        :validation-schema="formSchema"
        id="form-user-balance"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <h2 class="mb-0">
                {{ selected ? "Edit" : "Tambah" }} Saldo Pengguna
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
                            Jumlah Saldo
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            @input="handleBalanceInput"
                            name="balance"
                            autocomplete="off"
                            v-model="user.balance"
                            placeholder="089"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="balance" />
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
