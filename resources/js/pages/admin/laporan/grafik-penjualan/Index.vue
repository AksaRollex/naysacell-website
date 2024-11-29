<template>
    <div style="width: 100%; height: 500px">
        <LineChart :chartData="data" :chartLabels="labels" />
    </div>
</template>

<script>
import LineChart from "../../../../components/LineChart.vue";

export default {
    components: {
        LineChart,
    },
    data() {
        return {
            data: [65, 59, 80, 81, 56, 55, 40],
            labels: [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
            ],
        };
    },
};
</script>
