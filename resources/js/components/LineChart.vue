<!-- LineChart.vue -->
<template>
    <div class="chart-container">
        <canvas ref="lineChart"></canvas>
    </div>
</template>

<script>
import { Chart } from 'chart.js';

export default {
    name: "LineChart",
    props: {
        chartData: {
            type: Array,
            required: true,
        },
        chartLabels: {
            type: Array,
            required: true,
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
            const ctx = this.$refs.lineChart.getContext("2d");

            this.chart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: this.chartLabels,
                    datasets: [
                        {
                            label: "Data Line Chart",
                            data: this.chartData,
                            fill: false,
                            borderColor: "rgb(75, 192, 192)",
                            tension: 0.1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: "Line Chart Example",
                        },
                    },
                },
            });
        },
    },
    watch: {
        chartData: {
            handler() {
                if (this.chart) {
                    this.chart.destroy();
                    this.createChart();
                }
            },
            deep: true,
        },
    },
    beforeDestroy() {
        if (this.chart) {
            this.chart.destroy();
        }
    },
};
</script>

<style scoped>
.chart-container {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}
</style>
