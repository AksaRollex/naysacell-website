<script lang="ts" setup>
import { ref, watch, onMounted } from "vue";
import axios from "@/libs/axios";

// PPOB Form data
const customerNo = ref("");
const customerName = ref("");
const searchQuery = ref("");
const selectedProduct = ref(null);
const quantity = ref(1);

// Products state
const products = ref([]);
const loading = ref(false);
const error = ref("");

// Search products
const searchProducts = async () => {
    try {
        loading.value = true;
        error.value = "";

        const response = await axios.post("/master/product/prepaid", {
            search: searchQuery.value,
            product_category: 'e-money',
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
});

watch(searchQuery, (newValue) => {
    if (newValue.length >= 3) {
        searchProducts();
    }
});

const submitOrder = async () => {
    if (!selectedProduct.value || !customerNo.value || !customerName.value) {
        error.value = "Please fill in all required fields";
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

            alert("Order submitted successfully");
        }
    } catch (err) {
        error.value = err.response?.data?.message || "Failed to submit order";
        console.error("Error submitting order:", err);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="page-container">
        <div class="content-wrapper">
            <!-- Customer Information Section -->
            <div class="customer-section">
                <h2 class="section-title">E - Money Order</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Customer Number</label>
                        <input
                            type="text"
                            v-model="customerNo"
                            placeholder="Enter customer number"
                            class="input-field"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Customer Name</label>
                        <input
                            type="text"
                            v-model="customerName"
                            placeholder="Enter customer name"
                            class="input-field"
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
                        placeholder="Search e-money products..."
                        class="search-input"
                    />
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-section">
                <div v-if="loading" class="loading-state">
                    Loading products...
                </div>
                
                <div v-else-if="error" class="error-state">
                    {{ error }}
                </div>
                
                <div v-else-if="products.length === 0" class="empty-state">
                    No E-Money products found
                </div>
                
                <div v-else class="products-grid">
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="product-card"
                        :class="{ selected: selectedProduct?.id === product.id }"
                        @click="selectedProduct = product"
                    >
                        <div class="product-icon">
                            <span class="icon-placeholder">📱</span>
                        </div>
                        <div class="product-details">
                            <h3 class="product-name">{{ product.product_name }}</h3>
                            <p class="product-price">
                                Rp {{ product.product_price.toLocaleString() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Section -->
            <div class="action-section" v-if="selectedProduct">
                <div class="selected-product-info">
                    <span class="selected-label">Selected:</span>
                    <span class="selected-name">{{ selectedProduct.product_name }}</span>
                    <span class="selected-price">Rp {{ selectedProduct.product_price.toLocaleString() }}</span>
                </div>
                <button
                    class="submit-button"
                    :disabled="loading || !selectedProduct"
                    @click="submitOrder"
                >
                    {{ loading ? "Processing..." : "Submit Order" }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-container {
    min-height: 100vh;
    background-color: #f5f7fa;
    border-radius: 20px;
    margin-bottom: 20px;
    padding: 2rem;
}

.content-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.section-title {
    color: #1a1a1a;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.customer-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #4a5568;
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
    background: #f5f7fa;
    padding: 1rem 0;
}

.search-container {
    max-width: 600px;
    margin: 0 auto;
}

.search-input {
    width: 100%;
    padding: 1rem 1.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    background: white;
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
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.product-card.selected {
    border-color: #4299e1;
    background-color: #ebf8ff;
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
    color: #2d3748;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.product-price {
    color: #4a5568;
    font-size: 0.875rem;
    font-weight: 500;
}

.action-section {
    position: sticky;
    bottom: 0;
    background: white;
    padding: 1rem 2rem;
    border-radius: 12px;
    box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
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
    color: #2d3748;
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
    color: #718096;
    background: white;
    border-radius: 12px;
    margin: 1rem 0;
}

.error-state {
    color: #e53e3e;
    background: #fff5f5;
}
</style>