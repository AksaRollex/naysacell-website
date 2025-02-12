<template>
  <div class="card">
    <div class="card-body">
      <div id="transactionChart" style="width: 100%; height: 500px"></div>
      <div v-if="!hasData" class="text-center py-4">
        {{ loadingMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, onMounted, ref, computed } from 'vue';
import ApexCharts from 'apexcharts';

export default defineComponent({
  name: 'TransactionChart',
  setup() {
    const chartData = ref({
      labels: [],
      transactions: [],
      amounts: []
    });
    const loadingMessage = ref('Loading chart...');
    let chart = null;

    const hasData = computed(() => {
      return chartData.value.labels.length > 0 && 
             chartData.value.transactions.length > 0 && 
             chartData.value.amounts.length > 0;
    });

    const initChart = () => {
      const options = {
        series: [
          {
            name: 'Jumlah Transaksi',
            type: 'area',
            data: chartData.value.transactions
          },
          {
            name: 'Jumlah Penghasilan',
            type: 'area',
            data: chartData.value.amounts
          }
        ],
        chart: {
          height: 500,
          type: 'area',
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
              reset: false
            }
          }
        },
        stroke: {
          width: [2, 2],
          curve: 'smooth'
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            inverseColors: false,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100, 100, 100]
          }
        },
        dataLabels: {
          enabled: false
        },
        markers: {
          size: 4,
          strokeWidth: 2,
          hover: {
            size: 6
          }
        },
        xaxis: {
          categories: chartData.value.labels,
          type: 'category',
          title: {
            text: 'Period'
          },
          labels: {
            formatter: function(value) {
              const date = new Date(value);
              return date.toLocaleString('id-ID', { month: 'long', year: 'numeric' });
            }
          },
          tickAmount: 12 // Show all months
        },
        yaxis: [
          {
            title: {
              text: 'Transactions',
            },
            labels: {
              formatter: (value) => { return Math.round(value) }
            },
            min: function(min) { return min - (min * 0.1) }, // Add 10% padding
            max: function(max) { return max + (max * 0.1) }
          },
          {
            opposite: true,
            title: {
              text: 'Amount (IDR)'
            },
            labels: {
              formatter: (value) => {
                return new Intl.NumberFormat('id-ID', {
                  style: 'currency',
                  currency: 'IDR',
                  minimumFractionDigits: 0
                }).format(value);
              }
            },
            min: function(min) { return min - (min * 0.1) }, // Add 10% padding
            max: function(max) { return max + (max * 0.1) }
          }
        ],
        tooltip: {
          shared: true,
          intersect: false,
          x: {
            formatter: function(value) {
              return new Date(value).toLocaleString('id-ID', { month: 'long', year: 'numeric' });
            }
          },
          y: {
            formatter: (value, { seriesIndex }) => {
              if (seriesIndex === 0) {
                return `${value} transactions`;
              }
              return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
              }).format(value);
            }
          }
        },
        colors: ['#3699FF', '#1BC5BD'],
        legend: {
          position: 'top',
          horizontalAlign: 'right'
        }
      };

      if (chart) {
        chart.destroy();
      }

      chart = new ApexCharts(document.querySelector("#transactionChart"), options);
      chart.render();
    };

    const fetchChartData = async () => {
      try {
        const response = await axios.get("/master/transaction/chart-data");
        console.log('Raw response:', response.data);
        
        if (!response.data || !response.data.labels || !response.data.labels.length) {
          loadingMessage.value = 'Data tidak ditemukan!';
          return;
        }
        
        chartData.value = response.data;
        initChart();
      } catch (error) {
        console.error("Error fetching chart data:", error);
        loadingMessage.value = 'Error loading chart data';
      }
    };

    onMounted(() => {
      fetchChartData();
    });

    return {
      hasData,
      loadingMessage
    };
  }
});
</script>