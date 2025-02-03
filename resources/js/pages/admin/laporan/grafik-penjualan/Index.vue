<template>
  <div style="width: 100%; height: 500px">
    <LineChart
      v-if="hasData"
      :transactionData="chartData"
    />
    <div v-else class="text-center py-4">
      {{ loadingMessage }}
    </div>
  </div>
</template>

<script>
import axios from "@/libs/axios";
import LineChart from "@/components/LineChart.vue";

export default {
  components: {
    LineChart,
  },
  data() {
    return {
      chartData: {
        labels: [],
        transactions: [],
        amounts: []
      },
      loadingMessage: 'Loading chart...'
    };
  },
  computed: {
    hasData() {
      return this.chartData.labels.length > 0 && 
             this.chartData.transactions.length > 0 && 
             this.chartData.amounts.length > 0;
    }
  },
  mounted() {
    this.fetchChartData();
  },
  methods: {
    async fetchChartData() {
      try {
        const response = await axios.get("/master/transaction/chart-data");
        console.log('Raw response:', response.data);
        
        if (!response.data || !response.data.labels || !response.data.labels.length) {
          this.loadingMessage = 'Data tidak ditemukan !';
          return;
        }
        
        this.chartData = response.data;
      } catch (error) {
        console.error("Error fetching chart data:", error);
        this.loadingMessage = 'Error loading chart data';
      }
    },
  },
};
</script>