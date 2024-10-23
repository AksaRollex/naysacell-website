<script setup lang="ts">
import { currency } from "@/libs/utils";

</script>

<template>
    <div class="col-xl-3 ">
        <div
            class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5 border-danger border-left-5 border-start d-block"
        >
          
            <div class="m-0 d-flex justify-content-between align-items-center">
                <div>
                    <span
                        class="text-danger fw-bolder d-block fs-2x lh-1 ls-n1 mb-1 cursor"
                    > {{ currency ( 0) }}
                    </span>
                    <span class="text-gray-500 fw-semibold fs-6 cursor"
                        >Saldo Anda</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>
