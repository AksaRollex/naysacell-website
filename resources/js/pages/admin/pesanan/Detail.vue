<script setup lang="ts">
import ApiService from "@/core/services/ApiService";
import { block, unblock } from "@/libs/utils";
import { computed } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import { toast } from "vue3-toastify";

const route = useRoute();
const data = ref({});
const loading = ref(false);

const orderId = computed(() => route.params.id as string);  

async function getData() {
    if (!orderId.value) {
        toast.error('ID Order tidak ditemukan');
        return;
    }

    loading.value = true;
    const formElement = document.getElementById("form-product-detail");
    
    try {
        if (formElement) block(formElement);
        const response = await ApiService.get(`master/order/get/${orderId.value}`);
        data.value = response.data.data;
    } catch (err: any) {
        console.error('Error:', err);
        toast.error(err.response?.data?.message || 'Gagal mengambil data');
    } finally {
        if (formElement) unblock(formElement);
        loading.value = false;
    }
}

onMounted(() => {
    getData();
});
</script>

<template>
    <div id="form-product-detail">
        <div v-if="loading">Loading...</div>
        <div v-else>
            <!-- Untuk debugging -->
            <pre>{{ data }}</pre>
        </div>
    </div>
</template>