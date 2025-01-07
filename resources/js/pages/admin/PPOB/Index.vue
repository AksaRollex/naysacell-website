<template>
    <div class="container mt-4">
        <div class="row g-4">
            <!-- Pulsa & Paket Data Card -->
            <div class="col-md-4">
                <router-link
                    to="/ppob/pulsapaketdata"
                    class="text-decoration-none"
                >
                    <div
                        class="card h-100 dashboard-card border-start border-primary border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-primary-light p-3 rounded-3 me-3"
                            >
                                <i class="bi bi-phone text-primary fs-4"></i>
                            </div>
                            <h5 class="card-title text-white mb-0">
                                Pulsa & Paket Data
                            </h5>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- PLN Card -->
            <!-- <div class="col-md-4">
                <router-link to="/ppob/pln" class="text-decoration-none">
                    <div
                        class="card h-100 dashboard-card border-start border-warning border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-warning-light p-3 rounded-3 me-3"
                            >
                                <i
                                    class="bi bi-lightning-charge text-warning fs-4"
                                ></i>
                            </div>
                            <h5 class="card-title text-white mb-0">PLN</h5>
                        </div>
                    </div>
                </router-link>
            </div> -->

            <!-- Dompet Elektronik Card -->
            <div class="col-md-4">
                <router-link
                    to="/ppob/dompetelektronik"
                    class="text-decoration-none"
                >
                    <div
                        class="card h-100 dashboard-card border-start border-success border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-success-light p-3 rounded-3 me-3"
                            >
                                <i class="bi bi-wallet2 text-success fs-4"></i>
                            </div>
                            <h5 class="card-title text-white mb-0">
                                Dompet Elektronik
                            </h5>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- BPJS Kesehatan Card -->
            <!-- <div class="col-md-4">
                <router-link to="/ppob/bpjs" class="text-decoration-none">
                    <div
                        class="card h-100 dashboard-card border-start border-danger border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-danger-light p-3 rounded-3 me-3"
                            >
                                <i
                                    class="bi bi-heart-pulse text-danger fs-4"
                                ></i>
                            </div>
                            <h5 class="card-title text-white mb-0">
                                BPJS Kesehatan
                            </h5>
                        </div>
                    </div>
                </router-link>
            </div> -->

            <!-- PDAM Card -->
            <!-- <div class="col-md-4">
                <router-link to="/ppob/pdam" class="text-decoration-none">
                    <div
                        class="card h-100 dashboard-card border-start border-info border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-info-light p-3 rounded-3 me-3"
                            >
                                <i class="bi bi-droplet text-info fs-4"></i>
                            </div>
                            <h5 class="card-title text-white mb-0">PDAM</h5>
                        </div>
                    </div>
                </router-link>
            </div> -->
        </div>
    </div>
</template>

<script>
export default {
    name: "DashboardCards",
};
</script>

<style scoped>
.dashboard-card {
    background-color: #262626;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
    border: 1px solid #e0e0e0;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.card-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
    border-radius: 8px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

/* Custom color classes */
.border-purple {
    border-color: #9461fb !important;
}

.text-purple {
    color: #9461fb !important;
}

.bg-purple-light {
    background-color: rgba(148, 97, 251, 0.1) !important;
}

.border-info {
    border-color: #0dcaf0 !important;
}

.text-info {
    color: #0dcaf0 !important;
}

.bg-info-light {
    background-color: rgba(13, 202, 240, 0.1) !important;
}

/* Light background colors for card icons */
.bg-primary-light {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.bg-warning-light {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-danger-light {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
.bg-success-light {
    background-color: rgba(44, 144, 39, 0.1) !important;
}

.bg-secondary-light {
    background-color: rgba(108, 117, 125, 0.1) !important;
}
</style>
