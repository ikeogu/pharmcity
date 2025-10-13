<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    total: {
        type: Number,
        required: true
    },
    processing: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'process']);

const paymentMethod = ref('cash');
const amountReceived = ref(props.total);
const notes = ref('');

const change = computed(() => {
    if (paymentMethod.value === 'cash') {
        return Math.max(0, amountReceived.value - props.total);
    }
    return 0;
});

const canProcess = computed(() => {
    if (paymentMethod.value === 'cash') {
        return amountReceived.value >= props.total;
    }
    return true;
});

const processPayment = () => {
    if (!canProcess.value) {
        alert('Amount received is less than total');
        return;
    }

    emit('process', paymentMethod.value, notes.value);
};

const setExactAmount = () => {
    amountReceived.value = props.total;
};
</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-semibold">Complete Payment</h3>
                <button
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <!-- Total Amount -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Amount</p>
                    <p class="text-3xl font-bold text-blue-600">₦{{ total.toFixed(2) }}</p>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Method
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="method in ['cash', 'card', 'transfer', 'insurance']"
                            :key="method"
                            @click="paymentMethod = method"
                            :class="[
                                'px-4 py-3 rounded-lg border-2 font-medium capitalize transition-all',
                                paymentMethod === method
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            {{ method }}
                        </button>
                    </div>
                </div>

                <!-- Cash Payment Details -->
                <div v-if="paymentMethod === 'cash'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Amount Received
                        </label>
                        <div class="flex space-x-2">
                            <input
                                v-model.number="amountReceived"
                                type="number"
                                step="0.01"
                                min="0"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <button
                                @click="setExactAmount"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium"
                            >
                                Exact
                            </button>
                        </div>
                    </div>

                    <div v-if="change > 0" class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Change</p>
                        <p class="text-2xl font-bold text-green-600">₦{{ change.toFixed(2) }}</p>
                    </div>

                    <div v-if="amountReceived < total" class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <p class="text-sm text-red-600">
                            Insufficient amount. Short by ₦{{ (total - amountReceived).toFixed(2) }}
                        </p>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea
                        v-model="notes"
                        rows="3"
                        placeholder="Add any notes for this transaction..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    ></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 rounded-b-lg">
                <button
                    @click="$emit('close')"
                    :disabled="processing"
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 font-medium disabled:opacity-50"
                >
                    Cancel
                </button>
                <button
                    @click="processPayment"
                    :disabled="!canProcess || processing"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                    {{ processing ? 'Processing...' : 'Complete Payment' }}
                </button>
            </div>
        </div>
    </div>
</template>
