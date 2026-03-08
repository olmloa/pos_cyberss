<script setup>
import { TemplateOne } from '@/Components/Templates';

defineProps(['sale', 'custom_fields']);
</script>

<template>
  <Head>
    <title>{{ $t('Sale') }}</title>
  </Head>

  <div class="p-6">
    <div class="relative mx-auto w-full max-w-3xl rounded-lg border border-gray-200 dark:border-gray-700 print:border-0">
      <TemplateOne :record="sale" type="sale" :custom_fields="custom_fields" :xfetch="true" />
    </div>
  </div>
</template>
