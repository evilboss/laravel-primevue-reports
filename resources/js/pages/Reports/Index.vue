<template>
  <Head title="Reports" />

  <AppLayout>
    <div class="flex flex-col gap-8">
      <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">
              Admin Reports
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              View job bookings and conversion funnel analytics
            </p>
          </div>
        </div>

        <!-- Debug Info -->
        <Card class="p-6">
          <h3 class="text-lg font-medium mb-4">Debug Information</h3>
          <div class="space-y-2">
            <p><strong>Total Markets:</strong> {{ markets.length }}</p>
            <p><strong>User Role:</strong> {{ user.role }}</p>
            <p><strong>Is Admin:</strong> {{ user.is_admin ? 'Yes' : 'No' }}</p>
          </div>
        </Card>

        <!-- Simple Filters -->
        <Card class="p-6">
          <h3 class="text-lg font-medium mb-4">Filters</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Markets ({{ selectedMarkets.length }} selected)
              </label>
              <div class="text-sm text-gray-600">
                Available: {{ markets.map(m => m.name).join(', ') }}
              </div>
            </div>
            
            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Start Date
              </label>
              <input 
                v-model="startDateString" 
                type="date" 
                class="p-2 border rounded"
              />
            </div>
            
            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                End Date
              </label>
              <input 
                v-model="endDateString" 
                type="date" 
                class="p-2 border rounded"
              />
            </div>
          </div>
          
          <div class="mt-4">
            <Button 
              @click="testApiEndpoint"
              :loading="testing"
              class="mr-4"
            >
              Test Job Bookings API
            </Button>
            <Button 
              @click="testConversionFunnel"
              :loading="testingFunnel"
            >
              Test Conversion Funnel API
            </Button>
          </div>
        </Card>

        <!-- API Response Display -->
        <Card v-if="apiResponse" class="p-6">
          <h3 class="text-lg font-medium mb-4">API Response</h3>
          <pre class="bg-gray-100 dark:bg-gray-800 p-4 rounded overflow-auto text-sm">{{ JSON.stringify(apiResponse, null, 2) }}</pre>
        </Card>

        <!-- Error Display -->
        <Card v-if="error" class="p-6 border-red-200 bg-red-50">
          <h3 class="text-lg font-medium text-red-800 mb-2">Error</h3>
          <p class="text-red-600">{{ error }}</p>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

interface Market {
  id: number;
  name: string;
  code: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  is_admin: boolean;
}

interface Props {
  markets: Market[];
  user: User;
}

const props = defineProps<Props>();

// Reactive data
const selectedMarkets = ref<number[]>(props.markets.map(m => m.id));
const startDateString = ref(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
const endDateString = ref(new Date().toISOString().split('T')[0]);

// Testing states
const testing = ref(false);
const testingFunnel = ref(false);
const apiResponse = ref<any>(null);
const error = ref<string | null>(null);

const testApiEndpoint = async () => {
  testing.value = true;
  apiResponse.value = null;
  error.value = null;

  try {
    const params = new URLSearchParams({
      start_date: startDateString.value,
      end_date: endDateString.value,
    });

    selectedMarkets.value.forEach(marketId => {
      params.append('market_ids[]', marketId.toString());
    });

    const response = await fetch(`/api/reports/job-bookings?${params}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    const result = await response.json();
    apiResponse.value = result;
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${result.message || 'Unknown error'}`);
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred';
  } finally {
    testing.value = false;
  }
};

const testConversionFunnel = async () => {
  testingFunnel.value = true;
  apiResponse.value = null;
  error.value = null;

  try {
    const params = new URLSearchParams({
      start_date: startDateString.value,
      end_date: endDateString.value,
    });

    selectedMarkets.value.forEach(marketId => {
      params.append('market_ids[]', marketId.toString());
    });

    const response = await fetch(`/api/reports/conversion-funnel?${params}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    const result = await response.json();
    apiResponse.value = result;
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${result.message || 'Unknown error'}`);
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'An error occurred';
  } finally {
    testingFunnel.value = false;
  }
};
</script>