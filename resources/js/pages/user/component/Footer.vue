<template>
    <footer class=" py-5" style="background-color: #18181B;">
        <div class="container">
            <div class="row align-items-center">
                <div
                    class="col-lg-8 mb-4 mb-lg-0 d-flex flex-column flex-md-row align-items-start align-items-md-center"
                >
                    <img
                        src="../../../../../public/media/logo.png"
                        alt="Logo"
                        class="mb-3 mb-md-0 me-md-3"
                        style="width: 8rem; height: auto"
                    />
                    <div class="max-w-md">
                        <p class="text-light-50 text-justify text-md-start" style="font-family : Arial, Helvetica, sans-serif">
                            Top up cepat dan mudah dengan NAYSA CELL! Nikmati
                            berbagai layanan PPOB, mulai dari pulsa, token
                            listrik, hingga pembayaran tagihan, semua dalam satu
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
                    <a href="#" class="d-inline-block">
                        <img
                            src="../../../../../public/media/googleplay.png"
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
};
</script>

<style>
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
