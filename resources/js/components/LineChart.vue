<template>
    <div class="chart-container">
      <canvas ref="lineChart"></canvas>
    </div>
  </template>
  
  <script>
  import Chart from 'chart.js/auto';
  
  export default {
    name: "LineChart",
    props: {
      chartData: {
        type: Array,
        default: () => []
      },
      chartLabels: {
        type: Array,
        default: () => []
      }
    },
    data() {
      return {
        chart: null
      };
    },
    mounted() {
      this.createChart();
    },
    methods: {
      createChart() {
        if (this.chartData.length === 0 || this.chartLabels.length === 0) {
          console.warn('No data available for chart');
          return;
        }
  
        const ctx = this.$refs.lineChart.getContext("2d");
  
        if (this.chart) {
          this.chart.destroy();
        }
  
        this.chart = new Chart(ctx, {
          type: "line",
          data: {
            labels: this.chartLabels,
            datasets: [
              {
                label: "Produk Prabayar",
                data: this.chartData,
                fill: false,
                borderColor: "rgb(255, 99, 132)",
                borderWidth: 2,
                tension: 0.1
              },
              {
                label: "Produk Pascabayar",
                data: this.chartData,
                fill: false,
                borderColor: "rgb(54, 162, 235)",
                borderWidth: 2,
                tension: 0.1
              },
              {
                label: "Produk BPJS Kesehatan",
                data: this.chartData,
                fill: false,
                borderColor: "rgb(255, 205, 86)",
                borderWidth: 2,
                tension: 0.1
              },
              {
                label: "Produk Internet",
                data: this.chartData,
                fill: false,
                borderColor: "rgb(75, 192, 192)",
                borderWidth: 2,
                tension: 0.1
              },
              {
                label: "Produk E-Money",
                data: this.chartData,
                fill: false,
                borderColor: "rgb(153, 102, 255)",
                borderWidth: 2,
                tension: 0.1
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              x: {
                type: 'category',
                grid: {
                  display: false
                }
              },
              y: {
                beginAtZero: true,
                grid: {
                  display: false
                }
              }
            },
            plugins: {
              title: {
                display: true,
                text: "Grafik Penjualan"
              }
            }
          }
        });
      }
    },
    watch: {
      chartData: {
        handler() {
          this.createChart();
        },
        deep: true
      }
    },
    beforeUnmount() {
      if (this.chart) {
        this.chart.destroy();
      }
    }
  };
  </script>
  
  <style scoped>
  .chart-container {
    width: 100%;
    height: 400px;
  }
  </style>