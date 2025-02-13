<template>
    <div class="w-100">
        <!-- Main Content -->
        <div id="main">
            <!-- Welcome Section -->
            <section
                id="Welcome"
                class="min-vh-100 d-flex align-items-center justify-content-center"
                style="
                    background-image: url('/media/products_provider/background.jpg');
                    background-size: cover;
                    background-position: center;
                "
            >
                <!-- Konten Teks -->
                <div
                    class="container d-flex flex-row align-items-center justify-content-center"
                >
                    <div class="row w-100">
                        <div
                            class=" col-md-12 p-5 text-white text-center text-lg-start"
                        >
                            <img
                                src="../../../../public/media/logo-nc.png"
                                alt="Naysa Cell Logo"
                                class="img-fluid"
                                style="max-width: 170px"
                            />
                            <h1
                                id="WelcomeText"
                                style="
                                    color: #f1f5f9;
                                    font-weight: bold;
                                    font-family: 'Segoe UI', Tahoma, Geneva,
                                        Verdana, sans-serif;
                                "
                            >
                                Selamat Datang Di NAYSA CELL
                            </h1>
                            <p
                                class="lead"
                                style="
                                    font-family: Arial, Helvetica, sans-serif;
                                    line-height: 2rem;
                                "
                            >
                                Silahkan Download Aplikasi Kami Untuk Kalian
                                Yang Ingin Topup Pulsa, Paket Data, Uang
                                Elektronik, Dan Game.
                            </p>
                            <a
                                @click="downloadApp"
                                href="#"
                                class="d-inline-block"
                                style="cursor: pointer"
                            >
                                <img
                                    src="../../../../public/media/download-button.png"
                                    alt="Get it on Google Play"
                                    style="width: 15rem; height: auto"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
<script>
export default {
    name: "Footer",
    methods: {
        downloadApp(e) {
            e.preventDefault();
            try {
                const downloadLink = document.createElement("a");
                downloadLink.href = "/media/NAYSACELL.apk";
                downloadLink.download = "NAYSACELL.apk";
                downloadLink.click();

                this.$toast?.success("Download dimulai...", {
                    position: "bottom-right",
                    timeout: 3000,
                });
            } catch (error) {
                console.error("Download failed:", error);
                this.$toast?.error("Download gagal. Silakan coba lagi nanti.", {
                    position: "bottom-right",
                    timeout: 5000,
                });
            }
        },
    },
};
</script>
<style>
body {
    font-family: "font-sans", sans-serif;
}

#WelcomeText {
    font-size: 5rem;
}

@media (max-width: 768px) {
    #WelcomeText {
        font-size: 2rem;
    }
}

#Welcome {
    position: relative;
    width: 100%;
    min-height: 100vh;
    overflow: hidden;
}

#Welcome::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url("/media/products_provider/background.jpg");
    background-size: cover;
    background-position: center;
    transform: scaleY(-1);
    z-index: -1;
}
</style>
