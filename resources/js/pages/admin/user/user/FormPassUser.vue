<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { ref } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";

const emit = defineEmits(["close", "refresh"]);

const props = defineProps({
    selected: {
        type: String,
        required: true,
    },
});

const formRef = ref();
const password = ref("");
const passwordConfirmation = ref("");

const formSchema = Yup.object().shape({
    password: Yup.string().min(8, "Minimal 8 karakter").nullable(),
    password_confirmation: Yup.string()
        .oneOf([Yup.ref("password")], "Konfirmasi password harus sama")
        .nullable(),
});

function submit() {
    const formData = new FormData();
    formData.append("password", password.value);
    formData.append("password_confirmation", passwordConfirmation.value);

    block(document.getElementById("form-password"));

    axios({
        method: "post",
        url: `/api/auth/form-password`,
        data: {
            uuid: props.selected,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
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
            unblock(document.getElementById("form-password"));
        });
}
</script>

<template>
    <VForm
        class="form card mb-10"
        @submit="submit"
        :validation-schema="formSchema"
        id="form-password"
        ref="formRef"
    >
        <div class="card-header align-items-center">
            <h2 class="mb-0">Rubah Password User</h2>
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
                        <label class="form-label fw-bold fs-6">
                            Password
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="password"
                            name="password"
                            autocomplete="off"
                            v-model="password"
                            placeholder="Masukkan password"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="password" />
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-6">
                    
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6">
                            Konfirmasi Password
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="password"
                            name="passwordConfirmation"
                            autocomplete="off"
                            v-model="passwordConfirmation"
                            placeholder="Konfirmasi password"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="passwordConfirmation" />
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
