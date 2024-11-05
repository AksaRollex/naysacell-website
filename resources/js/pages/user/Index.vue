<template>
    <div class="w-100">
        <!-- Header -->
        <header>
            <nav
                class="navbar navbar-dark border-bottom"
                style="background-color: #18181b"
            >
                <div class="container-fluid px-3">
                    <a class="navbar-brand d-flex align-items-center px-3">
                        <img
                            src="../../../../public/media/logo.png"
                            alt="logo"
                            width="40"
                            height="40"
                        />
                        <span class="fs-2 ms-4 fw-bold">NAYSA CELL</span>
                    </a>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <div id="main">
            <!-- Welcome Section -->
            <section
                id="Welcome"
                class="min-vh-100 d-flex align-items-center"
                style="
                    background-image: url('/media/background.jpg');
                    background-size: cover;
                    background-position: center;
                "
            >
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-10 p-5">
                            <h1
                                id="WelcomeText"
                                class="text-white"
                                style="
                                    font-weight: bold;
                                    font-family: 'Segoe UI', Tahoma, Geneva,
                                        Verdana, sans-serif;
                                "
                            >
                                Selamat Datang Di NAYSA CELL
                            </h1>
                            <h1 class="text-white lead" style="font-family: Arial, Helvetica, sans-serif; line-height: 2rem;">
                                Silahkan Download Aplikasi Kami Untuk Kalian
                                Yang Ingin Topup PPOB Dan Sebagainya
                            </h1>
                        </div>
                    </div>
                </div>
            </section>

            <!-- UI Application Section -->
            <section id="UIApplication" class="py-20">
                <div class="text-center mb-5">
                    <h1 class="text-white fw-bold">Aplikasi Kami</h1>
                </div>
                <div class="container">
                    <div class="row g-4 justify-content-center">
                        <div
                            class="col-lg-3"
                            v-for="(app, index) in applications"
                            :key="index"
                        >
                            <div class="card h-100 shadow-sm mx-5">
                                <img
                                    :src="app.image"
                                    class="card-img-top"
                                    :alt="app.title"
                                />
                                <div class="card-body">
                                    <p class="card-text text-center fw-bold">
                                        {{ app.title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <Footer />
</template>
<script>
import Footer from "./component/Footer.vue";
export default {
    components: {
        Footer,
    },

    data() {
        return {
            applications: [
                {
                    title: "Halaman Utama NAYSA CELL",
                    image: "/media/homePage.png",
                },
                {
                    title: "Halaman Masuk NAYSA CELL",
                    image: "/media/loginPage.png",
                },
                {
                    title: "Halaman Daftar NAYSA CELL",
                    image: "/media/registerPage.png",
                },
            ],
        };
    },
};
</script>
<style>
body {
    font-family: "font-sans", sans-serif;
}
#WelcomeText {
    font-size: 6rem;
}

@media (max-width: 768px) {
    #WelcomeText {
        font-size: 3.3rem;
    }
}

#UIApplication {
    position: relative;
    background-color: #262626;
    width: 100%;
    height: 100vh;

    @media screen {
        @media (min-width: 768px) {
            height: 100%;
            width: 100%;
            padding: 20px;
        }

        @media (max-width: 768px) {
            height: 100%;
            width: 100%;
            padding: 20px;
        }
    }
}

#UIApplication::before {
    content: "";
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 40px;
    box-shadow: 0 -30px 20px 20px #262626;
}
</style>
