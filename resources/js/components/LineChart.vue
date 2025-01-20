<template>
    <div class="chart-container">
        <canvas ref="lineChart"></canvas>
    </div>
</template>

<script>
import Chart from "chart.js/auto";

export default {
    name: "LineChart",
    props: {
        transactionData: {
            type: Object,
            required: true,
            default: () => ({
                labels: [],
                transactions: [],
                amounts: [],
            }),
        },
    },
    data() {
        return {
            chart: null,
        };
    },
    mounted() {
        this.createChart();
    },
    methods: {
        createChart() {
            const { labels, transactions, amounts } = this.transactionData;

            if (!labels.length) {
                console.warn("No data available for chart");
                return;
            }

            const ctx = this.$refs.lineChart.getContext("2d");

            if (this.chart) {
                this.chart.destroy();
            }

            this.chart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Jumlah Transaksi",
                            data: transactions,
                            borderColor: "rgb(75, 192, 192)",
                            tension: 0.1,
                            yAxisID: "y",
                        },
                        {
                            label: "Total Penjualan (Rp)",
                            data: amounts,
                            borderColor: "rgb(255, 99, 132)",
                            tension: 0.1,
                            yAxisID: "y1",
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: "linear",
                            display: true,
                            position: "left",
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Jumlah Transaksi",
                            },
                        },
                        y1: {
                            type: "linear",
                            display: true,
                            position: "right",
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Total Penjualan (Rp)",
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: "Statistik Transaksi",
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || "";
                                    if (label) {
                                        label += ": ";
                                    }
                                    if (context.datasetIndex === 1) {
                                        label += new Intl.NumberFormat(
                                            "id-ID",
                                            {
                                                style: "currency",
                                                currency: "IDR",
                                            }
                                        ).format(context.raw);
                                    } else {
                                        label += context.raw;
                                    }
                                    return label;
                                },
                            },
                        },
                    },
                    elements: {
                        line: {
                            tension: 0.4, // Membuat garis lebih smooth
                            borderWidth: 2, // Membuat garis lebih tebal
                        },
                        point: {
                            radius: 1,
                            hitRadius: 10,
                            hoverRadius: 6,
                        },
                    },
                },
            });
        },
    },
    watch: {
        transactionData: {
            handler() {
                this.createChart();
            },
            deep: true,
        },
    },
    beforeUnmount() {
        if (this.chart) {
            this.chart.destroy();
        }
    },
};
</script>

<style scoped>
.chart-container {
    width: 100%;
    height: 400px;
}
</style>
