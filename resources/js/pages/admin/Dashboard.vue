<template>
    <main class="dashboard-container">
        <!-- Modern Statistics Cards -->
        <div class="row g-5">
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 shadow-sm hover-elevate-up">
                    <div class="card-body">
                        <div class="d-flex flex-column">
                            <!-- Icon with Gradient Background -->
                            <div class="symbol symbol-60px mb-6">
                                <span
                                    class="symbol-label bg-primary bg-opacity-10 rounded-3"
                                >
                                    <i
                                        class="ki-duotone ki-wallet text-primary fs-1"
                                    >
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                </span>
                            </div>

                            <!-- Statistics -->
                            <div class="d-flex flex-column mb-2">
                                <span
                                    class="text-gray-600 fw-semibold fs-7 mb-1"
                                    >Saldo</span
                                >
                                <div class="d-flex align-items-center">
                                    <span
                                        class="badge badge-light-primary fs-base"
                                    >
                                        <i
                                            class="ki-duotone ki-arrow-up fs-7 text-success ms-n1"
                                        ></i>
                                        {{ formatCurrency(currentBalance) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <!-- <div class="progress h-3px bg-light-primary">
                  <div class="progress-bar bg-primary" role="progressbar" :style="{ width: '76%' }"></div>
                </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";

const currentBalance = ref(0);
const fetchBalance = async () => {
    try {
        const response = await axios.get("/auth/check-saldo");
        currentBalance.value = response.data.balance;
    } catch (error) {
        toast.error("Gagal mengambil data saldo", {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 3000,
        });
        console.error("Error fetching balance:", error);
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

onMounted(() => {
    fetchBalance();
});
</script>

<style>
.hover-elevate-up {
    transition: transform 0.3s ease;
}

.hover-elevate-up:hover {
    transform: translateY(-5px);
}

.timeline-label {
    position: relative;
    padding-left: 25px;
}

.timeline-label::before {
    content: "";
    position: absolute;
    left: 13px;
    width: 2px;
    top: 5px;
    bottom: 5px;
    background-color: #e4e6ef;
}

.timeline-badge {
    position: absolute;
    left: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 2px solid #e4e6ef;
    z-index: 1;
}
</style>
