<template>
    <div style="width: 100%; height: 500px">
      <LineChart
        v-if="chartData.length > 0"
        :chartData="chartData"
        :chartLabels="chartLabels"
      />
      <div v-else>Loading chart...</div>
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
        chartData: [],
        chartLabels: [],
      };
    },
    mounted() {
      this.fetchLaporanData();
    },
    methods: {
      async fetchLaporanData() {
        try {
          const response = await axios.post("/master/laporan", {
            transaction_type: null,
            search: null,
            page: 1,
            per: 20,
          });
  
          const data = response.data.data;
          this.chartData = data.map((item) => item.id );
          this.chartLabels = data.map(
            (item) => item.transaction_date || item.label
          );
        } catch (error) {
          console.error("Error fetching laporan data:", error);
        }
      },
    },
  };
  </script>