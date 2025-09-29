<template>
  <Head title="Reports" />

  <AppLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">
          Admin Reports
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
          View job bookings and conversion funnel analytics
        </p>
      </div>

      <!-- Debug Card -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">System Information</h2>
        <div class="space-y-2">
          <p><strong>User:</strong> {{ user.name }} ({{ user.role }})</p>
          <p><strong>Is Admin:</strong> {{ user.is_admin ? 'Yes' : 'No' }}</p>
          <p><strong>Available Markets:</strong> {{ markets.length }}</p>
          <ul class="list-disc pl-6">
            <li v-for="market in markets" :key="market.id">
              {{ market.name }} ({{ market.code }})
            </li>
          </ul>
        </div>
      </div>

      <!-- API Test Card -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">API Test</h2>
        <div class="space-y-4">
          <div class="flex space-x-4">
            <button
              @click="testJobBookings"
              :disabled="loading"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            >
              {{ loading ? 'Testing...' : 'Test Job Bookings API' }}
            </button>
            <button
              @click="testConversionFunnel"
              :disabled="loading"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50"
            >
              {{ loading ? 'Testing...' : 'Test Conversion Funnel API' }}
            </button>
          </div>
          
          <div v-if="apiResponse" class="mt-4">
            <h3 class="font-semibold mb-2">API Response:</h3>
            <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded text-sm overflow-auto max-h-64">{{ JSON.stringify(apiResponse, null, 2) }}</pre>
          </div>
          
          <div v-if="error" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <strong>Error:</strong> {{ error }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

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

const loading = ref(false);
const apiResponse = ref<any>(null);
const error = ref<string | null>(null);

const testJobBookings = async () => {
  loading.value = true;
  error.value = null;
  apiResponse.value = null;

  try {
    const params = new URLSearchParams({
      start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      end_date: new Date().toISOString().split('T')[0],
    });

    props.markets.forEach(market => {
      params.append('market_ids[]', market.id.toString());
    });

    const response = await fetch(`/api/reports/job-bookings?${params}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
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
    loading.value = false;
  }
};

const testConversionFunnel = async () => {
  loading.value = true;
  error.value = null;
  apiResponse.value = null;

  try {
    const params = new URLSearchParams({
      start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      end_date: new Date().toISOString().split('T')[0],
    });

    props.markets.forEach(market => {
      params.append('market_ids[]', market.id.toString());
    });

    const response = await fetch(`/api/reports/conversion-funnel?${params}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
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
    loading.value = false;
  }
};
</script>