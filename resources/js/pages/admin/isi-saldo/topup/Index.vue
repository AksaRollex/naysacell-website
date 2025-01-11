<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from '@/libs/axios';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const amount = ref('');
const loading = ref(false);
const currentBalance = ref(0);

const fetchBalance = async () => {
  try {
    const response = await axios.get('/auth/check-saldo');
    currentBalance.value = response.data.balance;
  } catch (error: any) {
    toast.error('Gagal mengambil data saldo', {
      position: toast.POSITION.TOP_RIGHT,
      autoClose: 3000
    });
  }
};

// Panggil fetchBalance saat komponen dimount
onMounted(() => {
  fetchBalance();
});

const validateAmount = (value: string): boolean => {
  const numValue = Number(value);
  return numValue >= 1000 && numValue <= 10000000;
};

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(value);
};

const handleTopup = async () => {
  if (!validateAmount(amount.value)) {
    toast.error('Jumlah harus antara Rp 1.000 - Rp 10.000.000', {
      position: toast.POSITION.TOP_RIGHT,
      autoClose: 3000
    });
    return;
  }

  loading.value = true;
  const formData = new FormData();
  formData.append('amount', amount.value);

  try {
    await axios.post('/auth/topup', formData);
    await fetchBalance();
    
    toast.success('Top up berhasil!', {
      position: toast.POSITION.TOP_RIGHT,
      autoClose: 3000
    });
    amount.value = '';
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Terjadi kesalahan saat top up', {
      position: toast.POSITION.TOP_RIGHT,
      autoClose: 3000
    });
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="max-w-md mx-auto  p-6  rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Top Up Saldo</h2>
    
    <div class="mb-6">
      <p class="text-gray-600 mb-2">Saldo Saat Ini:</p>
      <p class="text-xl font-semibold text-gray-800">
        {{ formatCurrency(currentBalance) }}
      </p>
    </div>

    <form @submit.prevent="handleTopup" class="space-y-4">
      <div>
        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
          Jumlah Top Up
        </label>
        <div class="relative">
          <span class="absolute left-3 top-2 text-gray-500">Rp</span>
          <input
            id="amount"
            v-model="amount"
            type="number"
            class=" w-full p-2 mx-2 max-w-md border w-50 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            placeholder="Masukkan jumlah"
            min="1000"
            max="10000000"
            required
          />
        </div>
        <p class="mt-1 text-sm text-gray-500">
          Minimal Rp 1.000 - Maksimal Rp 10.000.000
        </p>
      </div>

      <button
        type="submit"
        class="w-full bg-blue-600 rounded-lg text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="loading"
      >
        <span v-if="loading">Memproses...</span>
        <span v-else>Top Up Sekarang</span>
      </button>
    </form>
  </div>
</template>