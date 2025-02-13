<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { Form as VForm, Field } from "vee-validate";
import * as Yup from "yup";

interface MidtransResult {
    status_code: string;
    status_message: string;
    transaction_id: string;
    order_id: string;
    gross_amount: string;
    payment_type: string;
    transaction_time: string;
    transaction_status: string;
    fraud_status?: string;
    payment_code?: string;
    pdf_url?: string;
    finish_redirect_url?: string;
}

const numericPattern = /^[0-9]+$/;

const formSchema = Yup.object().shape({
    amount: Yup.string()
        .required("Nominal wajib diisi")
        .matches(numericPattern, "Nominal harus berupa angka")
        .test(
            "min-max",
            "Jumlah harus antara Rp 1.000 - Rp 10.000.000",
            (value) => {
                const numValue = Number(value);
                return numValue >= 1000 && numValue <= 10000000;
            }
        ),
});

const amount = ref("");
const loading = ref(false);
const currentBalance = ref(0);

declare const snap: any;

const handlePhoneInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    input.value = input.value.replace(/[^0-9]/g, "");
};

const fetchBalance = async () => {
    try {
        const response = await axios.get("/auth/check-saldo");
        currentBalance.value = response.data.balance;
    } catch (error: any) {
        toast.error("Gagal mengambil data saldo", {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 3000,
        });
    }
};

onMounted(() => {
    fetchBalance();

    const loadMidtransScript = new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
        script.setAttribute(
            "data-client-key",
            import.meta.env.VITE_MIDTRANS_CLIENT_KEY
        );
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });

    loadMidtransScript
        .then(() => {
            console.log("Midtrans script loaded successfully");
        })
        .catch((error) => {
            console.error("Failed to load Midtrans script:", error);
            toast.error("Gagal memuat sistem pembayaran");
        });
});

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const handleTopup = async () => {
    loading.value = true;

    try {
        if (typeof snap === "undefined") {
            throw new Error("Sistem pembayaran belum siap");
        }

        const formData = new FormData();
        formData.append("amount", amount.value);

        const response = await axios.post("/auth/topup", formData);
        const { snap_token, transaction } = response.data;
        console.log("Response:", response.data);

        if (!snap_token) {
            throw new Error("Token pembayaran tidak valid");
        }

        console.log("Starting payment with token:", snap_token);

        snap.pay(snap_token, {
            onSuccess: async (result: MidtransResult) => {
                console.log("Payment success with details:", result);
                await axios
                    .post("/midtrans-callback", result, transaction)
                    .then((response) => {
                        console.log("Callback response:", response);
                    })
                    .catch((error) => {
                        console.error("Error posting callback:", error);
                    });

                await fetchBalance();
                toast.success("Top up berhasil!");
            },
            onPending: (result: any) => {
                console.log("Payment pending:", result);
                toast.info(
                    "Menunggu pembayaran. Silahkan selesaikan pembayaran Anda"
                );
            },
            onError: (result: any) => {
                console.error("Payment error:", result);
                toast.error(
                    "Pembayaran gagal: " +
                        (result?.message || "Terjadi kesalahan")
                );
            },
            onClose: () => {
                if (loading.value) {
                    toast.info("Pembayaran dibatalkan");
                }
            },
        });
    } catch (error: any) {
        console.error("Topup error:", error);
        toast.error(
            error.response?.data?.message ||
                error.message ||
                "Terjadi kesalahan saat top up"
        );
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <VForm
        class="card mb-10"
        @submit="handleTopup"
        :validation-schema="formSchema"
    >
        <div class="page-container">
            <div class="card dashboard-card shadow-sm">
                <div class="customer-section">
                    <div class="row justify-content-between">
                        <h2 class="mb-0">Topup</h2>
                        <h6 class="mb-0 mt-4">
                            Saldo Anda Saat Ini :
                            {{ formatCurrency(currentBalance) }}
                        </h6>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <h5 class="form-label">Nominal</h5>
                            <div
                                class="d-flex align-items-center col-md-12"
                                style="flex-direction: row"
                            >
                                <Field
                                    name="amount"
                                    v-model="amount"
                                    type="text"
                                    class="form-control form-control-solid"
                                    placeholder="Masukkan Jumlah Nominal"
                                    @input="handlePhoneInput"
                                />
                            </div>
                            <h6 class="mt-4 text-gray-500">
                                Minimal Rp 1.000 - Maksimal Rp 10.000.000
                            </h6>
                        </div>
                        <div class="mb-3">
                            <ErrorMessage
                                name="amount"
                                class="text-danger"
                                style="font-size: 13px"
                            />
                        </div>
                    </div>
                </div>
                <button
                    type="submit"
                    class="btn btn-sm btn-primary col-md-12 text-center"
                    :disabled="loading"
                    style="text-align: left; display: inline-block"
                >
                    <span
                        v-if="loading"
                        class="spinner-border spinner-border-sm"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    <h7 class="mb-0" v-else>Top Up Sekarang</h7>
                </button>
            </div>
        </div>
    </VForm>
</template>

<style scoped>
.page-container {
    border-radius: 20px;
}

.dashboard-card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
    padding: 1rem;
}

.customer-section {
    border-radius: 12px;
}

.form-grid {
    flex-direction: column;
    margin-top: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.text-danger {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
