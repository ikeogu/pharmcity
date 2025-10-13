<script setup>
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const emit = defineEmits(['add-to-cart']);

const searchQuery = ref('');
const drugs = ref([]);
const loading = ref(false);
const showResults = ref(false);

const searchDrugs = debounce(async () => {
    if (searchQuery.value.length < 2) {
        drugs.value = [];
        return;
    }

    loading.value = true;

    try {
        const response = await axios.get('/pos/drugs/search', {
            params: { query: searchQuery.value }
        });
        drugs.value = response.data.drugs;
        showResults.value = true;
    } catch (error) {
        console.error('Search failed:', error);
    } finally {
        loading.value = false;
    }
}, 300);

watch(searchQuery, () => {
    searchDrugs();
});

const selectDrug = (drug) => {
    emit('add-to-cart', drug);
    searchQuery.value = '';
    drugs.value = [];
    showResults.value = false;
};

const getStockStatusClass = (quantity) => {
    if (quantity === 0) return 'text-red-600';
    if (quantity < 10) return 'text-orange-600';
    return 'text-green-600';
};
</script>

<template>
    <div class="relative">
        <div class="relative">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by name, generic name, or scan barcode..."
                class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @focus="showResults = true"
            />
            <svg
                v-if="loading"
                class="animate-spin h-5 w-5 text-gray-400 absolute right-3 top-4"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg
                v-else
                class="h-5 w-5 text-gray-400 absolute right-3 top-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <!-- Search Results Dropdown -->
        <div
            v-if="showResults && drugs.length > 0"
            class="absolute z-10 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto"
        >
            <div
                v-for="drug in drugs"
                :key="drug.id"
                @click="selectDrug(drug)"
                class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0"
            >
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">{{ drug.name }}</h4>
                        <p class="text-sm text-gray-600">{{ drug.generic_name }}</p>
                        <div class="flex items-center mt-1 space-x-4 text-xs">
                            <span class="text-gray-500">{{ drug.category?.name }}</span>
                            <span :class="getStockStatusClass(drug.quantity)">
                                Stock: {{ drug.quantity }}
                            </span>
                            <span v-if="drug.requires_prescription" class="text-amber-600">
                                ℞ Prescription Required
                            </span>
                        </div>
                    </div>
                    <div class="text-right ml-4">
                        <p class="font-bold text-blue-600">₦{{ parseFloat(drug.selling_price).toFixed(2) }}</p>
                        <p v-if="drug.batches?.length" class="text-xs text-gray-500">
                            Exp: {{ new Date(drug.batches[0].expiry_date).toLocaleDateString() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showResults && searchQuery.length >= 2 && drugs.length === 0 && !loading"
            class="absolute z-10 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-center text-gray-500"
        >
            No products found
        </div>
    </div>
</template>
