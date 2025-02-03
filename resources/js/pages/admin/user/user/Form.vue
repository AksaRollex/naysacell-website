<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { User, Role } from "@/types";
import ApiService from "@/core/services/ApiService";
import { useRole } from "@/services/useRole";

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
const namePattern = /^[a-zA-Z0-9 ]+$/;

const formSchema = Yup.object().shape({
    name: Yup.string()
        .required("Nama harus diisi")
        .matches(namePattern, "Nama hanya boleh berisi huruf, angka dan spasi"),
    email: Yup.string()
        .email("Email harus valid")
        .required("Email harus diisi"),
    password: Yup.string().min(8, "Minimal 8 karakter").nullable(),
    passwordConfirmation: Yup.string()
        .oneOf([Yup.ref("password")], "Konfirmasi password harus sama")
        .nullable(),
    phone: Yup.string()
        .matches(numericPattern, "telepon hanya boleh berisi angka")
        .matches(/^08\d{8,12}$/, "Nomor telepon harus dimulai dengan 08")
        .required("Nomor Telepon harus diisi")
        .min(10, "No telepon minimal 10 digit")
        .max(14, "No telepon maksimal 14 digit"),
    role_id: Yup.string().required("Pilih role"),
    address: Yup.string().required("Alamat harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-user"));
    ApiService.get("master/users", props.selected)
        .then(({ data }) => {
            user.value = data.user;
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
    formData.append("name", user.value.name);
    formData.append("email", user.value.email);
    formData.append("phone", user.value.phone);
    formData.append("role_id", user.value.role_id);
    formData.append("address", user.value.address);

    if (user.value?.password) {
        formData.append("password", user.value.password);
        formData.append(
            "password_confirmation",
            user.value.passwordConfirmation
        );
    }

    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-user"));
    axios({
        method: "post",
        url: props.selected
            ? `/master/users/${props.selected}`
            : "/master/users/store",
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

const role = useRole();
const roles = computed(() =>
    role.data.value?.map((item: Role) => ({
        id: item.id,
        text: item.full_name,
    }))
);

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

const handlePhoneInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    let phone = input.value.replace(/[^0-9]/g, "");

    // Jika input kosong, set nilai default "08"
    if (phone === "") {
        phone = "08";
    }

    // Jika user menghapus angka dan panjang < 2, pastikan tetap dimulai dengan "08"
    if (phone.length < 2) {
        phone = "08";
    }
    // Jika angka pertama bukan 0, tambahkan 08 di depan
    else if (!phone.startsWith("0")) {
        phone = "08";
    }
    // Jika dimulai dengan 0 tapi angka kedua bukan 8, perbaiki menjadi 08
    else if (phone.startsWith("0") && phone[1] !== "8") {
        phone = "08" + phone.slice(2);
    }

    // Batasi panjang nomor telepon
    if (phone.length > 14) {
        phone = phone.substring(0, 14);
    }

    user.value.phone = phone;
    input.value = phone;

    const newPosition = Math.min(input.selectionStart || 0, phone.length);
    input.setSelectionRange(newPosition, newPosition);
};

const handleNameInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    // Hanya izinkan huruf, angka, dan spasi
    input.value = input.value.replace(/[^a-zA-Z0-9 ]/g, "");
};
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
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} User</h2>
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
                            name="name"
                            @input="handleNameInput"
                            autocomplete="off"
                            v-model="user.name"
                            placeholder="Masukkan Nama"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="name" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Email
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="email"
                            autocomplete="off"
                            v-model="user.email"
                            placeholder="Masukkan Email"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="email" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Alamat
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="address"
                            autocomplete="off"
                            v-model="user.address"
                            placeholder="Masukkan Alamat"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="address" />
                            </div>
                        </div>
                    </div>
                </div>
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
                            v-model="user.password"
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
                            v-model="user.passwordConfirmation"
                            placeholder="Konfirmasi password"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="passwordConfirmation" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Role
                        </label>
                        <Field
                            name="role_id"
                            type="hidden"
                            v-model="user.role_id"
                        >
                            <select2
                                placeholder="Pilih role"
                                class="form-select-solid"
                                :options="roles"
                                name="role_id"
                                v-model="user.role_id"
                            >
                            </select2>
                        </Field>
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="role_id" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="fv-row mb-7">
                        <label class="form-label fw-bold fs-6 required">
                            Nomor Telepon
                        </label>
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            @input="handlePhoneInput"
                            name="phone"
                            autocomplete="off"
                            v-model="user.phone"
                            placeholder="089"
                            maxlength="14"
                            minlength="8"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="phone" />
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
