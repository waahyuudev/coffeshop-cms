<script setup>
import EcommerceRevenueReport from '@/views/dashboards/ecommerce/EcommerceRevenueReport.vue'
import EcommerceStatistics from '@/views/dashboards/ecommerce/EcommerceStatistics.vue'

definePage({
  meta: {
    authenticatedOnly: true,
  },
})
</script>

<template>
  <VRow class="match-height">
    <!-- 👉 Ecommerce Transition -->
    <VCol cols="12">
      <EcommerceStatistics class="h-100" />
    </VCol>

    <!-- 👉 Revenue Report -->
    <VCol cols="12">
      <EcommerceRevenueReport />
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/libs/apex-chart.scss";
</style>

