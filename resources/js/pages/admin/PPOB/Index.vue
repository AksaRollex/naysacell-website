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
                        class="card h-100 dashboard-card border-start border-success border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-success-subtle p-3 rounded-3 me-3"
                            >
                                <i class="bi bi-phone text-success fs-4"></i>
                            </div>
                            <h5 class="card-title text-light mb-0">
                                Pulsa & Paket Data
                            </h5>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- PLN Card -->
            <div class="col-md-4">
                <router-link to="/ppob/pln" class="text-decoration-none">
                    <div
                        class="card h-100 dashboard-card border-start border-warning border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-warning-subtle p-3 rounded-3 me-3"
                            >
                                <i
                                    class="bi bi-lightning-charge text-warning fs-4"
                                ></i>
                            </div>
                            <h5 class="card-title text-light mb-0">PLN</h5>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- Dompet Elektronik Card -->
            <div class="col-md-4">
                <router-link
                    to="/ppob/dompetelektronik"
                    class="text-decoration-none"
                >
                    <div
                        class="card h-100 dashboard-card border-start border-secondary border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-secondary-subtle p-3 rounded-3 me-3"
                            >
                                <i
                                    class="bi bi-wallet2 text-secondary fs-4"
                                ></i>
                            </div>
                            <h5 class="card-title text-light mb-0">
                                Dompet Elektronik
                            </h5>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- BPJS Kesehatan Card -->
            <div class="col-md-4">
                <router-link to="/ppob/bpjs" class="text-decoration-none">
                    <div
                        class="card h-100 dashboard-card border-start border-danger border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-danger-subtle p-3 rounded-3 me-3"
                            >
                                <i
                                    class="bi bi-heart-pulse text-danger fs-4"
                                ></i>
                            </div>
                            <h5 class="card-title text-light mb-0">
                                BPJS Kesehatan
                            </h5>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- PDAM Card -->
            <div class="col-md-4">
                <router-link to="/ppob/pdam" class="text-decoration-none">
                    <div
                        class="card h-100 dashboard-card border-start border-primary border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-primary-subtle p-3 rounded-3 me-3"
                            >
                                <i class="bi bi-droplet text-primary fs-4"></i>
                            </div>
                            <h5 class="card-title text-light mb-0">PDAM</h5>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- Internet Card -->
            <div class="col-md-4">
                <router-link to="/ppob/internet" class="text-decoration-none">
                    <div
                        class="card h-100 dashboard-card border-start border-purple border-4"
                    >
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="card-icon bg-purple-subtle p-3 rounded-3 me-3"
                            >
                                <i class="bi bi-wifi text-purple fs-4"></i>
                            </div>
                            <h5 class="card-title text-light mb-0">Internet</h5>
                        </div>
                    </div>
                </router-link>
            </div>
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
    background-color: #808080;
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}

.dashboard-card:hover {
    transform: translateY(-5px);
}

.card-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff; /* Warna putih */
    backdrop-filter: blur(5px);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}
/* Custom color for purple cards */
.border-purple {
    border-color: #9461fb !important;
}

.text-purple {
    color: #9461fb !important;
}

.bg-purple-subtle {
    background-color: rgba(148, 97, 251, 0.1) !important;
}
</style>
