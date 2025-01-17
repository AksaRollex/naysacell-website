<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const amount = ref("");
const loading = ref(false);
const currentBalance = ref(0);

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
        await axios.post("/auth/topup", formData);
        await fetchBalance();

        toast.success("Top up berhasil!", {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 3000,
        });
        amount.value = "";
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
    <div class="max-w-md mx-auto p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Top Up Saldo</h2>

        <div class="mb-6">
            <h6 class="text-gray-500 mb-2">Saldo Saat Ini:</h6>
            <h3 class="font-semibold">
                {{ formatCurrency(currentBalance) }}
            </h3>
        </div>

        <form @submit.prevent="handleTopup" class="space-y-4">
            <div>
                <h6 for="amount" class="block font-medium text-gray-500 mb-2">
                    Jumlah Top Up
                </h6>
                <div class="relative col-md-3">
                    <h6 class="absolute left-3 top-2">Rp</h6>
                    <input
                        id="amount"
                        v-model="amount"
                        type="number"
                        class="form-control form-control-solid"
                        placeholder="Masukkan jumlah"
                        min="1000"
                        max="10000000"
                        required
                    />
                </div>
                <h6 class="mt-4 mb-2 text-gray-500">
                    Minimal Rp 1.000 - Maksimal Rp 10.000.000
                </h6>
            </div>

            <button
                type="submit"
                class="btn btn-sm btn-primary ms-auto"
                :disabled="loading"
            >
                <span v-if="loading">Memproses...</span>
                <h7 class="mb-0" v-else>Top Up Sekarang</h7>
            </button>
        </form>
    </div>
</template>
