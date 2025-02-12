<template>
    <VForm class="card mb-10" @submit="submit" :validation-schema="formSchema">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Konfigurasi Website</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    
                    <div class="fv-row mb-8">
                        <label class="form-label fw-bold fs-6 required"
                            >Nama Aplikasi</label
                        >
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="app"
                            autocomplete="off"
                            @input="handleNameInput"
                            v-model="formData.app"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="app" />
                            </div>
                        </div>
                    </div>
                    

                    
                    <div class="fv-row mb-8">
                        <label class="form-label fw-bold fs-6 required"
                            >Deskripsi</label
                        >
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="textarea"
                            name="description"
                            autocomplete="off"
                            v-model="formData.description"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="description" />
                            </div>
                        </div>
                    </div>
                    

                    
                    <div class="fv-row mb-8">
                        <label class="form-label fw-bold fs-6 required"
                            >Alamat</label
                        >
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="alamat"
                            autocomplete="off"
                            v-model="formData.alamat"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="alamat" />
                            </div>
                        </div>
                    </div>
                    

                    
                    <div class="fv-row mb-8">
                        <label class="form-label fw-bold fs-6 required"
                            >Telepon</label
                        >
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            @input="handlePhoneInput"
                            v-model="formData.telepon"
                            name="telepon"
                            maxlength="14"
                            autocomplete="off"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="telepon" />
                            </div>
                        </div>
                    </div>
                    

                    
                    <div class="fv-row mb-8">
                        <label class="form-label fw-bold fs-6 required"
                            >Email</label
                        >
                        <Field
                            class="form-control form-control-lg form-control-solid"
                            type="text"
                            name="email"
                            autocomplete="off"
                            v-model="formData.email"
                        />
                        <div class="fv-plugins-message-container">
                            <div class="fv-help-block">
                                <ErrorMessage name="email" />
                            </div>
                        </div>
                    </div>
                    
                </div>

                <div class="col-12 d-md-none">
                    <div
                        class="border border-bottom border-gray mt-8 mb-12"
                    ></div>
                </div>

                <div class="col-md-6">
                    <div class="fv-row mb-8">
                        <!--begin::Label-->
                        <label class="form-label fw-bold required">Logo</label>
                        <!--end::Label-->

                        <!--begin::Input-->
                        <file-upload
                            v-bind:files="files.logo"
                            :accepted-file-types="fileTypes"
                            required
                            v-on:updatefiles="(file) => (files.logo = file)"
                        ></file-upload>
                        <!--end::Input-->
                    </div>

                    <div class="fv-row mb-8">
                        <!--begin::Label-->
                        <label class="form-label fw-bold required"
                            >Background Login</label
                        >
                        <!--end::Label-->

                        <!--begin::Input-->
                        <file-upload
                            v-bind:files="files.bgAuth"
                            :accepted-file-types="fileTypes"
                            required
                            v-on:updatefiles="(file) => (files.bgAuth = file)"
                        ></file-upload>
                        <!--end::Input-->
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

<script lang="ts">
import { block, unblock } from "@/libs/utils";
import { defineComponent, ref } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { useSetting } from "@/services";
import type { Setting } from "@/types";

const numericPattern = /^[0-9]+$/;
export default defineComponent({
    props: {
        selected: {
            type: String,
            default: null,
        },
    },

    setup() {
        const setting = useSetting();
        const formData = ref<Setting>({ ...setting.data?.value });

        const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
        const files = ref({
            logo: setting.data?.value?.logo ? [setting.data.value.logo] : [],
            bgAuth: setting.data?.value?.bg_auth
                ? [setting.data.value.bg_auth]
                : [],
        });

        const formSchema = Yup.object().shape({
            app: Yup.string()
                .required("Nama aplikasi wajib diisi")
                .matches(
                    /^[a-zA-Z0-9\s]+$/,
                    "Nama aplikasi hanya boleh berisi huruf, angka dan spasi"
                ),
            description: Yup.string().required("Deskripsi wajib diisi"),
            alamat: Yup.string().required("Alamat wajib diisi"),
            email: Yup.string()
                .required("Email wajib diisi")
                .email("Email harus valid"),
            telepon: Yup.string()
                .required("Telepon wajib diisi")
                .matches(numericPattern, "telepon hanya boleh berisi angka")
                .max(14, "No telepon maksimal 14 digit"),
        });

        // Ensure that formData.telepon is part of the reactive data
        const handlePhoneInput = (e: Event) => {
            const input = e.target as HTMLInputElement;
            const cursorPosition = input.selectionStart;

            // Remove non-numeric characters
            let phoneNumber = input.value.replace(/[^0-9]/g, "");

            // Limit to 14 digits
            if (phoneNumber.length > 14) {
                phoneNumber = phoneNumber.substring(0, 14);
            }

            // Update both the form data and input value
            formData.value.telepon = phoneNumber;
            input.value = phoneNumber;

            // Restore cursor position
            const newPosition = Math.min(
                cursorPosition || 0,
                phoneNumber.length
            );
            input.setSelectionRange(newPosition, newPosition);
        };

        const handleNameInput = (e: Event) => {
            const input = e.target as HTMLInputElement;
            // Hanya izinkan huruf, angka, dan spasi
            input.value = input.value.replace(/[^a-zA-Z0-9 ]/g, "");
        };

        return {
            setting,
            formData,
            formSchema,
            fileTypes,
            files,
            handlePhoneInput,
            handleNameInput,
        };
    },

    methods: {
        submit() {
            const data = new FormData(this.$el);

            if (this.files.logo[0]?.file) {
                data.append("logo", this.files.logo[0].file);
            }
            if (this.files.bgAuth[0]?.file) {
                data.append("bg_auth", this.files.bgAuth[0].file);
            }

            block(this.$el);
            axios
                .post("/setting/update", data)
                .then((res) => {
                    toast.success(res.data.message);
                    this.setting.refetch();
                })
                .catch((err) => {
                    toast.error(err.response.data.message);
                })
                .finally(() => {
                    unblock(this.$el);
                });
        },
    },
    watch: {
        setting: {
            handler(setting) {
                this.formData = setting.data.value;

                this.files.logo = setting.data.value.logo
                    ? [setting.data.value.logo]
                    : [];
                this.files.bgAuth = setting.data.value.bg_auth
                    ? [setting.data.value.bg_auth]
                    : [];
            },
            deep: true,
        },
    },
});
</script>
