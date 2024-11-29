<template>
    <form @submit.prevent="handleSubmit" class="w-100 w-sm-auto">
        <input
            type="search"
            class="form-control form-control-solid"
            :placeholder="placeholder"
            v-model="searchValue"
            v-debounce="onSearch"
        />
    </form>
</template>

<script>
import { ref, defineComponent } from "vue";

export default defineComponent({
    name: "SearchInput",
    props: {
        // Initial search value
        modelValue: {
            type: String,
            default: "",
        },
        // Custom placeholder text
        placeholder: {
            type: String,
            default: "Cari ...",
        },
        // Debounce delay in milliseconds
        debounceDelay: {
            type: Number,
            default: 300,
        },
    },
    emits: ["update:modelValue", "search"],

    setup(props, { emit }) {
        const searchValue = ref(props.modelValue);

        const handleSubmit = () => {
            emit("search", searchValue.value);
        };

        const onSearch = () => {
            emit("update:modelValue", searchValue.value);
            emit("search", searchValue.value);
        };

        return {
            searchValue,
            handleSubmit,
            onSearch,
        };
    },

    watch: {
        modelValue(newValue) {
            this.searchValue = newValue;
        },
    },
});
</script>
