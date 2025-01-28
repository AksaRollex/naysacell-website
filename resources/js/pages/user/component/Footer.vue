<template>
    <footer class="py-5" style="background-color: #18181b">
        <div class="container">
            <div class="row align-items-center">
                <div
                    class="col-lg-8 mb-4 mb-lg-0 d-flex flex-column flex-md-row align-items-start align-items-md-center"
                >
                    <img
                        src="../../../../../public/media/logo-nc.png"
                        alt="Logo"
                        class="mb-3 mb-md-0 me-md-3"
                        style="width: 7rem; height: auto"
                    />
                    <div class="max-w-md">
                        <p
                            class="text-light-50 text-justify text-md-start"
                            style="font-family: Arial, Helvetica, sans-serif"
                        >
                            Top up cepat dan mudah dengan NAYSA CELL! Nikmati
                            berbagai layanan, mulai dari pulsa, paket data, uang
                            elektrinik, hingga topup game, semua dalam satu
                            aplikasi. Hemat waktu dan transaksi aman hanya
                            dengan beberapa klik. Unduh sekarang dan nikmati
                            kemudahan bertransaksi!
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end" id="download">
                    <h3 class="text-white mb-3">
                        Download NAYSA CELL Sekarang
                    </h3>
                    <a
                        @click="downloadApp"
                        href="#"
                        class="d-inline-block"
                        style="cursor: pointer"
                    >
                        <img
                            src="../../../../../public/media/click-here.png"
                            alt="Get it on Google Play"
                            style="width: 12rem; height: auto"
                        />
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>
export default {
    name: "Footer",
    methods: {
        downloadApp(e) {
            e.preventDefault();
            try {
                // Gunakan browser's native download behavior
                const downloadLink = document.createElement("a");
                downloadLink.href = "/media/NAYSACELL.apk";
                downloadLink.download = "NAYSACELL.apk";
                downloadLink.click();

                // Optional: Tampilkan notifikasi sukses
                this.$toast?.success("Download dimulai...", {
                    position: "bottom-right",
                    timeout: 3000,
                });
            } catch (error) {
                console.error("Download failed:", error);
                // Notifikasi error
                this.$toast?.error("Download gagal. Silakan coba lagi nanti.", {
                    position: "bottom-right",
                    timeout: 5000,
                });
            }
        },
    },
};
</script>

<style scoped>
@media (max-width: 768px) {
    footer {
        padding: 2rem;
    }
    footer img.logo {
        width: 6rem;
        margin-bottom: 1rem;
    }
    footer p {
        padding: 0 1rem;
    }
}

#download {
    @media (max-width: 768px) {
        margin-left: 1rem;
    }
}
</style>
