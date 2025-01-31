<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const amount = ref("");
const loading = ref(false);
const currentBalance = ref(0);

// Declare midtrans snap as external
declare const snap: any;

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
    // Load Midtrans Snap JS
    const script = document.createElement("script");
    script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
    // Ganti URL di atas dengan https://app.midtrans.com/snap/snap.js untuk production
    script.setAttribute("data-client-key", "YOUR-CLIENT-KEY");
    document.head.appendChild(script);
});

const validateAmount = (value: string): boolean => {
    const numValue = Number(value);
    return numValue >= 1000 && numValue <= 10000000;
};

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const handleTopup = async () => {
    if (!validateAmount(amount.value)) {
        toast.error("Jumlah harus antara Rp 1.000 - Rp 10.000.000", {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 3000,
        });
        return;
    }

    loading.value = true;
    const formData = new FormData();
    formData.append("amount", amount.value);

    try {
        // Request snap token dari backend
        const response = await axios.post("/auth/topup", formData);
        const { snap_token } = response.data;

        // Tampilkan popup pembayaran Midtrans
        snap.pay(snap_token, {
            onSuccess: async (result: any) => {
                await fetchBalance();
                toast.success("Top up berhasil!", {
                    position: toast.POSITION.TOP_RIGHT,
                    autoClose: 3000,
                });
                amount.value = "";
            },
            onPending: (result: any) => {
                toast.info("Menunggu pembayaran", {
                    position: toast.POSITION.TOP_RIGHT,
                    autoClose: 3000,
                });
            },
            onError: (result: any) => {
                toast.error("Pembayaran gagal", {
                    position: toast.POSITION.TOP_RIGHT,
                    autoClose: 3000,
                });
            },
            onClose: () => {
                toast.info("Pembayaran dibatalkan", {
                    position: toast.POSITION.TOP_RIGHT,
                    autoClose: 3000,
                });
            }
        });
    } catch (error: any) {
        toast.error(
            error.response?.data?.message || "Terjadi kesalahan saat top up",
            {
                position: toast.POSITION.TOP_RIGHT,
                autoClose: 3000,
            }
        );
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="page-container">
        <div class="card dashboard-card shadow-sm">
            <form @submit.prevent="handleTopup">
                <div class="customer-section">
                    <div class="row justify-content-between">
                        <h2 class="mb-0">Topup</h2>
                        <h6 class="mb-0 mt-4">
                            Saldo Anda Saat Ini : {{ formatCurrency(currentBalance) }}
                        </h6>
                    </div>
                    <div class="form-grid">
                        <input
                            id="amount"
                            v-model="amount"
                            type="number"
                            class="form-control form-control-solid"
                            placeholder="Masukkan Jumlah Nominal"
                            min="1000"
                            max="10000000"
                            required
                        />
                        <h6 class="mt-4 mb-2 text-gray-500">
                            Minimal Rp 1.000 - Maksimal Rp 10.000.000
                        </h6>
                    </div>
                </div>
                <button
                    type="submit"
                    class="btn btn-sm btn-primary mt-4 ms-auto"
                    :disabled="loading"
                >
                    <span v-if="loading">Memproses...</span>
                    <h7 class="mb-0" v-else>Top Up Sekarang</h7>
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped>
.page-container {
    min-height: 100vh;
    border-radius: 20px;
    margin-bottom: 20px;
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
    display: grid;
    margin-top: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
}
</style>
