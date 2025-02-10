<script lang="ts" setup>
import { ref, watch, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { valueEquals } from "element-plus";

const customerNo = ref("");
const customerName = ref("");
const searchQuery = ref("");
const selectedProduct = ref(null);
const quantity = ref(1);
const currentBalance = ref(0);

const products = ref([]);
const loading = ref(false);
const error = ref("");

const fetchBalance = async () => {
    try {
        const response = await axios.get("/auth/check-saldo");
        currentBalance.value = response.data.balance;
    } catch (error) {
        toast.error("Gagal mengambil data saldo", {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 3000,
        });
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};

const searchProducts = async () => {
    try {
        loading.value = true;
        error.value = "";

        let productProvider = null;
        const validPrefix = [
            // Telkomsel
            [
                "0811",
                "0812",
                "0813",
                "0821",
                "0822",
                "0823",
                "0851",
                "0852",
                "0853",
            ],
            // Indosat
            ["0814", "0815", "0816", "0855", "0856", "0857", "0858"],
            // XL
            ["0817", "0818", "0819", "0859", "0877", "0878"],
            // Axis
            ["0831", "0832", "0833", "0838"],
            // Three
            ["0895", "0896", "0897", "0898", "0899"],
            // Smartfren
            [
                "0881",
                "0882",
                "0883",
                "0884",
                "0885",
                "0886",
                "0887",
                "0888",
                "0889",
            ],
        ].flat();

        // Check if the number starts with any valid prefix
        const hasValidPrefix = validPrefix.some((prefix) =>
            customerNo.value.startsWith(prefix)
        );

        if (!hasValidPrefix && customerNo.value.length >= 4) {
            products.value = [];
            return;
        }

        // Telkomsel
        if (
            [
                "0811",
                "0812",
                "0813",
                "0821",
                "0822",
                "0823",
                "0851",
                "0852",
                "0853",
            ].some((prefix) => customerNo.value.startsWith(prefix))
        ) {
            productProvider = "telkomsel";
        }
        // Indosat
        else if (
            ["0814", "0815", "0816", "0855", "0856", "0857", "0858"].some(
                (prefix) => customerNo.value.startsWith(prefix)
            )
        ) {
            productProvider = "indosat";
        }
        // XL
        else if (
            ["0817", "0818", "0819", "0859", "0877", "0878"].some((prefix) =>
                customerNo.value.startsWith(prefix)
            )
        ) {
            productProvider = "xl";
        }
        // Axis
        else if (
            ["0831", "0832", "0833", "0838"].some((prefix) =>
                customerNo.value.startsWith(prefix)
            )
        ) {
            productProvider = "axis";
        }
        // Three
        else if (
            ["0895", "0896", "0897", "0898", "0899"].some((prefix) =>
                customerNo.value.startsWith(prefix)
            )
        ) {
            productProvider = "three";
        }
        // Smartfren
        else if (
            [
                "0881",
                "0882",
                "0883",
                "0884",
                "0885",
                "0886",
                "0887",
                "0888",
                "0889",
            ].some((prefix) => customerNo.value.startsWith(prefix))
        ) {
            productProvider = "smartfren";
        }

        const response = await axios.post("/master/product/prepaid", {
            search: searchQuery.value,
            product_category: "pulsa",
            product_provider: productProvider,
            page: 1,
            per: 10,
        });

        products.value = response.data.data;
    } catch (err) {
        error.value = "Failed to fetch products";
        console.error("Error fetching products:", err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    searchProducts();
    fetchBalance();
});

watch(searchQuery, (newValue) => {
    if (newValue.length >= 3) {
        searchProducts();
    }
});

watch(customerNo, (newValue) => {
    if (newValue) {
        searchProducts();
    }
});

const submitOrder = async () => {
    if (!selectedProduct.value || !customerNo.value || !customerName.value) {
        toast.error("Mohon lengkapi semua field yang diperlukan", {
            autoClose: 3000,
            position: toast.POSITION.TOP_RIGHT,
        });
        return;
    }

    try {
        loading.value = true;
        error.value = "";

        const payload = {
            product_id: selectedProduct.value.id,
            product_name: selectedProduct.value.product_name,
            product_price: selectedProduct.value.product_price,
            quantity: quantity.value,
            customer_no: customerNo.value,
            customer_name: customerName.value,
            user_id: 1,
        };

        const response = await axios.post("/auth/submit-product", payload);

        if (response.data.status === "success") {
            customerNo.value = "";
            customerName.value = "";
            searchQuery.value = "";
            selectedProduct.value = null;
            quantity.value = 1;
            await searchProducts();

            toast.success("Pesanan berhasil dikirim", {
                autoClose: 3000,
                position: toast.POSITION.TOP_RIGHT,
            });
        }
    } catch (err) {
        console.log("Error response:", err.response?.data);

        const errorData = err.response?.data;

        if (errorData?.message === "Saldo tidak mencukupi") {
            const errorMessage = `
                Saldo tidak mencukupi
                Saldo sekarang: Rp ${errorData.details.saldo_sekarang}
                Total pembelian: Rp ${errorData.details.total_pembelian}
                ${errorData.suggestion}
            `;

            toast.error(errorMessage, {
                autoClose: 5000,
                position: toast.POSITION.TOP_RIGHT,
            });
        } else if (errorData?.message === "User balance not found") {
            toast.error(
                "Kamu belum memiliki saldo, silahkan untuk isi saldo terlebih dahulu",
                {
                    autoClose: 3000,
                    position: toast.POSITION.TOP_RIGHT,
                }
            );
        } else {
            toast.error(errorData?.message || "Gagal mengirim pesanan", {
                autoClose: 3000,
                position: toast.POSITION.TOP_RIGHT,
            });
        }

        error.value = err.response?.data?.message || "Failed to submit order";
        console.error("Error submitting order:", err);
    } finally {
        loading.value = false;
    }
};

const handleCustomerNoInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    // Only allow numbers, remove any other characters
    input.value = input.value.replace(/[^0-9]/g, "");
    // Limit to a reasonable phone number length (15 digits)
    if (input.value.length > 14) {
        input.value = input.value.slice(0, 14);
    }
    customerNo.value = input.value;
};

const handleCustomerNameInput = (e: Event) => {
    const input = e.target as HTMLInputElement;
    // Only allow letters, numbers and single spaces
    let sanitizedValue = input.value
        .replace(/[^a-zA-Z0-9\s]/g, "") // Remove special characters
        .replace(/\s+/g, " ") // Replace multiple spaces with single space
        .replace(/^\s+/, ""); // Remove leading spaces

    // Limit to a reasonable name length (50 characters)
    if (sanitizedValue.length > 50) {
        sanitizedValue = sanitizedValue.slice(0, 50);
    }

    input.value = sanitizedValue;
    customerName.value = sanitizedValue;
};
</script>

<template>
    <div class="page-container">
        <div class="card dashboard-card shadow-sm">
            <!-- Customer Information Section -->
            <div class="customer-section">
                <div class="row justify-content-between">
                    <h2 class="mb-0">Pulsa Order</h2>
                    <h6 class="mb-0 mt-4">
                        Saldo Anda {{ formatCurrency(currentBalance) }}
                    </h6>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <h5 class="form-label">Nomor Customer</h5>
                        <input
                            type="text"
                            v-model="customerNo"
                            @input="handleCustomerNoInput"
                            placeholder="Masukkan Nomor Tujuan"
                            class="form-control form-control-solid"
                        />
                    </div>

                    <div class="form-group">
                        <h5 class="form-label">Nama Customer</h5>
                        <input
                            type="text"
                            @input="handleCustomerNameInput"
                            v-model="customerName"
                            placeholder="Masukkan Nomor Customer"
                            class="form-control form-control-solid"
                        />
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <div class="search-container">
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari..."
                        class="form-control form-control-solid"
                    />
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-section">
                <div v-if="loading" class="loading-state">
                    <span
                        v-if="loading"
                        class="spinner-border spinner-border-sm"
                        role="status"
                        aria-hidden="true"
                    ></span>
                </div>

                <div v-else-if="error" class="error-state">
                    {{ error }}
                </div>

                <div v-else-if="products.length === 0" class="empty-state">
                    Produk Tidak Ditemukan
                </div>

                <div v-else class="products-grid">
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="product-card"
                        :class="{
                            selected: selectedProduct?.id === product.id,
                        }"
                        @click="selectedProduct = product"
                    >
                        <div class="product-icon">
                            <span class="icon-placeholder">📱</span>
                        </div>
                        <div class="product-details">
                            <h6 class="mb-0">
                                {{ product.product_name }}
                            </h6>
                            <h6 class="product-price mt-1">
                                Rp {{ product.product_price.toLocaleString() }}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Section -->
            <div class="action-section" v-if="selectedProduct">
                <div class="selected-product-info">
                    <span class="selected-label">Produk Terpilih :</span>
                    <h6 class="mb-0 selected-name">
                        {{ selectedProduct.product_name }}
                    </h6>
                    <span class="selected-price">
                        {{ selectedProduct.product_price.toLocaleString() }}
                    </span>
                </div>
                <button
                    class="btn btn-sm btn-primary"
                    :disabled="loading || !selectedProduct"
                    @click="submitOrder"
                >
                    <!-- Show spinner when loading is true -->
                    <span
                        v-if="loading"
                        class="spinner-border spinner-border-sm"
                        role="status"
                        aria-hidden="true"
                    ></span>

                    <!-- Show text "Submit Order" when loading is false -->
                    <span v-else>Submit Order</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-container {
    min-height: 100vh;
    border-radius: 20px;
    margin-bottom: 20px;
}

.content-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
    border-radius: 1rem;
    background-color: #2d3035;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.dashboard-card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
    padding: 1rem;
}

.section-title {
    color: #f5f7fa;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.customer-section {
    border-radius: 12px;
}

.form-grid {
    display: grid;
    margin-top: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.input-field {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.input-field:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.search-section {
    position: sticky;
    top: 0;
    z-index: 10;
    padding: 1rem 0;
}

.search-container {
    max-width: 100%;
    margin: 0 auto;
}

.search-input {
    width: 100%;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-size: 1rem;
    /* background: white; */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.products-section {
    flex: 1;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
    padding: 1rem 0;
}

.product-card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
    border-radius: 1rem;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.product-card.selected {
    border: 1px;
    background-color: rgba(52, 50, 50, 0.1);
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
}

.product-icon {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}

.icon-placeholder {
    font-size: 2rem;
}

.product-details {
    text-align: center;
}

.product-name {
    font-weight: 600;
    color: #ebf8ff;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.product-price {
    color: #138ee9;
    font-size: 0.875rem;
    font-weight: 500;
}

.action-section {
    position: sticky;
    bottom: 0;
    padding: 1rem 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selected-product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.selected-label {
    font-size: 0.875rem;
    color: #718096;
}

.selected-name {
    font-weight: 600;
}

.selected-price {
    font-weight: 600;
    color: #4299e1;
}

.submit-button {
    padding: 0.75rem 2rem;
    background-color: #4299e1;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}

.submit-button:hover:not(:disabled) {
    background-color: #3182ce;
}

.submit-button:disabled {
    background-color: #cbd5e0;
    cursor: not-allowed;
}

.loading-state,
.error-state,
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #e53e3e;
    background-color: rgba(52, 50, 50, 0.1);
    border-radius: 12px;
    margin: 1rem 0;
}

.error-state {
    color: #e53e3e;
    background-color: rgba(52, 50, 50, 0.1);
}
</style>
