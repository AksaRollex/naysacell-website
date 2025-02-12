<template>
    <main class="dashboard-container">
        <div class="row g-5">
            <div
                v-for="(card, index) in dashboardCards"
                :key="index"
                class="col-xl-3"
            >
                <div class="card h-100 shadow-sm hover-elevate-up">
                    <div class="card-body">
                        <div class="d-flex flex-column">
                            <div class="symbol symbol-60px mb-6">
                                <span
                                    :class="`symbol-label bg-${card.color} bg-opacity-10 rounded-3`"
                                >
                                    <i
                                        :class="`ki-duotone ki-${card.icon} text-${card.color} fs-1`"
                                    >
                                        <span
                                            v-for="n in 4"
                                            :key="n"
                                            :class="`path${n}`"
                                        ></span>
                                    </i>
                                </span>
                            </div>
                            <div class="d-flex flex-column mb-2">
                                <span
                                    class="text-gray-600 fw-semibold fs-7 mb-1"
                                    >{{ card.title }}</span
                                >
                                <div class="d-flex align-items-center">
                                    <span
                                        :class="`badge badge-light-${card.color} fs-base`"
                                    >
                                        <i
                                            class="ki-duotone ki-arrow-up fs-7 text-success ms-n1"
                                        ></i>
                                        {{ card.value }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-5 mb-5">
            <div class="card-body">
                <div
                    id="transactionChart"
                    style="width: 100%; height: 0px"
                ></div>
                <div v-if="!hasChartData" class="text-center py-4">
                    <span
                        v-if="loading"
                        class="spinner-border spinner-border-sm border-primary me-2"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    <!-- {{ loadingMessage }} -->
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import ApexCharts from "apexcharts";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";

const chartData = ref({ labels: [], transactions: [], amounts: [] });
const loading = ref(true);
const loadingMessage = ref("Loading chart...");
const currentBalance = ref(0);
const currentUsers = ref([]);
const currentOrders = ref([]);
let chart = null;

const hasChartData = computed(() => {
    const { labels, transactions, amounts } = chartData.value;
    return labels.length > 0 && transactions.length > 0 && amounts.length > 0;
});

const totalIncome = computed(() => {
    return chartData.value.amounts.reduce((sum, amount) => sum + amount, 0);
});

const dashboardCards = computed(() => [
    {
        title: "Saldo Anda",
        value: formatCurrency(currentBalance.value),
        color: "primary",
        icon: "wallet",
    },
    {
        title: "Total Pendapatan",
        value: formatCurrency(totalIncome.value),
        color: "success",
        icon: "dollar",
    },
    {
        title: "Pengguna",
        value: currentUsers.value.length,
        color: "warning",
        icon: "people",
    },
    {
        title: "Pesanan",
        value: currentOrders.value.length,
        color: "info",
        icon: "purchase",
    },
]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

const initChart = () => {
    const options = {
        series: [
            {
                name: "Jumlah Transaksi",
                type: "area",
                data: chartData.value.transactions,
            },
            {
                name: "Jumlah Penghasilan",
                type: "area",
                data: chartData.value.amounts,
            },
        ],
        chart: {
            height: 500,
            type: "area",
            stacked: false,
            toolbar: {
                show: true,
                tools: {
                    download: false,
                    selection: true,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false,
                },
            },
        },
        stroke: {
            width: [2, 2],
            curve: "smooth",
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100, 100, 100],
            },
        },
        dataLabels: { enabled: false },
        markers: {
            size: 4,
            strokeWidth: 2,
            hover: { size: 6 },
        },
        xaxis: {
            categories: chartData.value.labels,
            type: "category",
            title: { text: "Period" },
            labels: {
                formatter: (value) =>
                    new Date(value).toLocaleString("id-ID", {
                        month: "long",
                        year: "numeric",
                    }),
            },
            tickAmount: 12,
        },
        yaxis: [
            {
                title: { text: "Jumlah Transaksi" },
                labels: {
                    formatter: (value) => Math.round(value),
                },
                min: (min) => min - min * 0.1,
                max: (max) => max + max * 0.1,
            },
            {
                opposite: true,
                title: { text: "Jumlah Penghasilan (IDR)" },
                labels: {
                    formatter: (value) => formatCurrency(value),
                },
                min: (min) => min - min * 0.1,
                max: (max) => max + max * 0.1,
            },
        ],
        tooltip: {
            shared: true,
            intersect: false,
            x: {
                formatter: (value) =>
                    new Date(value).toLocaleString("id-ID", {
                        month: "long",
                        year: "numeric",
                    }),
            },
            y: {
                formatter: (value, { seriesIndex }) => {
                    return seriesIndex === 0
                        ? `${value} transactions`
                        : formatCurrency(value);
                },
            },
        },
        colors: ["#3699FF", "#1BC5BD"],
        legend: {
            position: "top",
            horizontalAlign: "right",
        },
    };

    if (chart) {
        chart.destroy();
    }

    chart = new ApexCharts(
        document.querySelector("#transactionChart"),
        options
    );
    chart.render();
};

const fetchData = async (endpoint, errorMessage) => {
    try {
        const response = await axios.get(endpoint);
        return response.data;
    } catch (error) {
        toast.error(errorMessage, {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 3000,
        });
        console.error(`Error fetching ${endpoint}:`, error);
        return null;
    }
};

const fetchOrder = async (endpoint, errorMessage) => {
    try {
        const response = await axios.post(endpoint);
        return response.data;
    } catch (error) {
        toast.error(errorMessage, {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 3000,
        });
        console.error(`Error fetching ${endpoint}:`, error);
        return null;
    }
};

const fetchChartData = async () => {
    try {
        const response = await axios.get("/master/transaction/chart-data");
        if (!response.data?.labels?.length) {
            loadingMessage.value = "Data tidak ditemukan!";
            return;
        }

        if (!Array.isArray(response.data.amounts)) {
            response.data.amounts = [];
        }

        chartData.value = response.data;
        initChart();
    } catch (error) {
        console.error("Error fetching chart data:", error);
        loadingMessage.value = "Error loading chart data";
        chartData.value.amounts = [];
    }
};

onMounted(async () => {
    const [balanceData, userData, orderData] = await Promise.all([
        fetchData("/auth/check-saldo", "Gagal mengambil data saldo"),
        fetchData("/master/users", "Gagal mengambil data user"),
        fetchOrder("/master/order", "Gagal mengambil data pesanan"),
    ]);

    if (balanceData) currentBalance.value = balanceData.balance;
    if (userData) currentUsers.value = userData.data;
    if (orderData) currentOrders.value = orderData.data;

    fetchChartData();
});
</script>

<style>
.hover-elevate-up {
    transition: transform 0.3s ease;
}

.hover-elevate-up:hover {
    transform: translateY(-5px);
}
</style>
