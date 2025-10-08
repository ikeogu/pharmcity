<template>
  <AppLayout title="Add New Drug">
    <div class="max-w-5xl mx-auto py-10">
      <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Add New Drug</h2>

        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Info -->
            <div>
              <Label for="drug_name" value="Drug Name" />
              <Input id="drug_name" v-model="form.drug_name" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.drug_name" />
            </div>

            <div>
              <Label for="generic_name" value="Generic Name" />
              <Input id="generic_name" v-model="form.generic_name" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.generic_name" />
            </div>

            <div>
              <Label for="drug_group_class" value="Drug Group/Class" />
              <Input id="drug_group_class" v-model="form.drug_group_class" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.drug_group_class" />
            </div>

            <div>
              <Label for="drug_dose" value="Drug Dose" />
              <Input id="drug_dose" v-model="form.drug_dose" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.drug_dose" />
            </div>

            <div>
              <Label for="drug_company" value="Drug Company" />
              <Input id="drug_company" v-model="form.drug_company" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.drug_company" />
            </div>

            <div>
              <Label for="drug_country" value="Country" />
              <Input id="drug_country" v-model="form.drug_country" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.drug_country" />
            </div>

            <!-- Dose & Route -->
            <div>
              <Label for="dose_unit" value="Dose Unit" />
              <select id="dose_unit" v-model="form.dose_unit" class="input-select">
                <option disabled value="">Select Unit</option>
                <option v-for="unit in doseUnits" :key="unit" :value="unit">{{ unit }}</option>
              </select>
              <InputError :message="form.errors.dose_unit" />
            </div>

            <div>
              <Label for="drug_route" value="Drug Route" />
              <select id="drug_route" v-model="form.drug_route" class="input-select">
                <option disabled value="">Select Route</option>
                <option v-for="route in drugRoutes" :key="route" :value="route">{{ route }}</option>
              </select>
              <InputError :message="form.errors.drug_route" />
            </div>

            <div>
              <Label for="min_daily_dose" value="Min Daily Dose" />
              <Input id="min_daily_dose" v-model="form.min_daily_dose" type="number" class="mt-1 block w-full" />
              <InputError :message="form.errors.min_daily_dose" />
            </div>

            <div>
              <Label for="max_daily_dose" value="Max Daily Dose" />
              <Input id="max_daily_dose" v-model="form.max_daily_dose" type="number" class="mt-1 block w-full" />
              <InputError :message="form.errors.max_daily_dose" />
            </div>

            <!-- Pricing and Stock -->
            <div>
              <Label for="purchase_price" value="Purchase Price" />
              <Input id="purchase_price" v-model="form.purchase_price" type="number" class="mt-1 block w-full" />
              <InputError :message="form.errors.purchase_price" />
            </div>

            <div>
              <Label for="batch_number" value="Batch Number" />
              <Input id="batch_number" v-model="form.batch_number" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.batch_number" />
            </div>

            <div>
              <Label for="expiry_date" value="Expiry Date" />
              <Input id="expiry_date" v-model="form.expiry_date" type="date" class="mt-1 block w-full" />
              <InputError :message="form.errors.expiry_date" />
            </div>

            <div>
              <Label for="total_sachets_in_stock" value="Total Sachets In Stock" />
              <Input id="total_sachets_in_stock" v-model="form.total_sachets_in_stock" type="number" class="mt-1 block w-full" />
              <InputError :message="form.errors.total_sachets_in_stock" />
            </div>

            <div>
              <Label for="packaging" value="Packaging" />
              <select id="packaging" v-model="form.packaging" class="input-select">
                <option v-for="item in packagingOptions" :key="item" :value="item">{{ item }}</option>
              </select>
              <InputError :message="form.errors.packaging" />
            </div>

            <div>
              <Label for="form_of_items_in_package" value="Form of Items in Package" />
              <select id="form_of_items_in_package" v-model="form.form_of_items_in_package" class="input-select">
                <option v-for="item in itemForms" :key="item" :value="item">{{ item }}</option>
              </select>
              <InputError :message="form.errors.form_of_items_in_package" />
            </div>

            <div>
              <Label for="cards_per_sachet" value="Cards per Sachet" />
              <Input id="cards_per_sachet" v-model="form.cards_per_sachet" type="number" class="mt-1 block w-full" />
              <InputError :message="form.errors.cards_per_sachet" />
            </div>

            <div>
              <Label for="sell_the_drug_as" value="Sell Drug As" />
              <select id="sell_the_drug_as" v-model="form.sell_the_drug_as" class="input-select">
                <option v-for="item in sellAsOptions" :key="item" :value="item">{{ item }}</option>
              </select>
              <InputError :message="form.errors.sell_the_drug_as" />
            </div>

            <div>
              <Label for="price_per_sachet" value="Price per Sachet" />
              <Input id="price_per_sachet" v-model="form.price_per_sachet" type="number" class="mt-1 block w-full" />
              <InputError :message="form.errors.price_per_sachet" />
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
              <Label for="drug_description" value="Drug Description" />
              <textarea id="drug_description" v-model="form.drug_description" class="textarea"></textarea>
              <InputError :message="form.errors.drug_description" />
            </div>

            <div class="md:col-span-2">
              <Label for="drug_composition" value="Drug Composition" />
              <textarea id="drug_composition" v-model="form.drug_composition" class="textarea"></textarea>
              <InputError :message="form.errors.drug_composition" />
            </div>
          </div>

          <!-- Submit -->
          <div class="mt-6 flex justify-end">
            <Button type="submit" :disabled="form.processing">Save Drug</Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import Input from '@/Components/TextInput.vue'
import Label from '@/Components/InputLabel.vue'
import Button from '@/Components/PrimaryButton.vue'
import InputError from '@/Components/InputError.vue'

const doseUnits = ['mls', 'mg', 'g', 'mcg', 'IU', 'drops', 'units']
const drugRoutes = ['Tablet', 'Syrup', 'Intravenous', 'Intramuscular', 'Subcutaneous', 'Topical', 'Nasal', 'Ocular', 'Vaginal', 'Rectal', 'Otic', 'PO', 'other']
const packagingOptions = ['sachet', 'bottle', 'box', 'blister', 'tube', 'vial', 'ampoule']
const itemForms = ['card', 'tablet', 'capsule', 'strip', 'piece', 'infusion','topical','syrup']
const sellAsOptions = ['sachet', 'card', 'tablet', 'bottle', 'piece', 'packet', 'ampoule','caplet', 'capsule','jar']

const form = useForm({
  drug_name: '',
  generic_name: '',
  drug_group_class: '',
  drug_dose: '',
  drug_company: '',
  drug_country: '',
  dose_unit: '',
  drug_route: '',
  min_daily_dose: '',
  max_daily_dose: '',
  purchase_price: '',
  batch_number: '',
  expiry_date: '',
  total_sachets_in_stock: '',
  packaging: '',
  form_of_items_in_package: '',
  cards_per_sachet: '',
  sell_the_drug_as: '',
  price_per_sachet: '',
  drug_description: '',
  drug_composition: '',
})

const submit = () => {
  form.post(route('drugs.store'))
}
</script>

<style scoped>
.input-select {
  @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500;
}
.textarea {
  @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500;
}
</style>
