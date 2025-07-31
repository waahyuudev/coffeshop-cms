<script setup>
import { VDataTableServer } from 'vuetify/labs/VDataTable'
import { paginationMeta } from '@api-utils/paginationMeta'
import ViewDetailOrder from "@/views/orders/ViewDetailOrder.vue"
import ViewDetailProduct from "@/views/products/ViewDetailProduct.vue"

definePage({
  meta: {
    authenticatedOnly: true,
  },
})

const widgetData = ref([
  {
    title: 'Pending Payment',
    value: 56,
    icon: 'tabler-calendar-stats',
  },
  {
    title: 'Unfulfilled',
    value: 25,
    icon: 'tabler-circle-x',
  },
  {
    title: 'Completed',
    value: 12689,
    icon: 'tabler-checks',
  },
  {
    title: 'Refunded',
    value: 124,
    icon: 'tabler-wallet',
  },
])

const searchQuery = ref('')

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

// Data table Headers
const headers = [
  {
    title: 'Order',
    key: 'orderId',
  },
  {
    title: 'Date',
    key: 'createdAt',
  },
  {
    title: 'Customers',
    key: 'customerName',
  },
  {
    title: 'Payment',
    key: 'payment',
  },
  {
    title: 'Status',
    key: 'status',
  },
  {
    title: 'Method',
    key: 'channelPayment',
    sortable: false,
  },
  {
    title: 'Price',
    key: 'totalPrice',
    sortable: false,
  },
  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
]

const updateOptions = options => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const resolvePaymentStatus = status => {
  if (status === 'paid')
    return {
      text: 'Paid',
      color: 'success',
    }
  if (status === 'unpaid')
    return {
      text: 'Pending',
      color: 'warning',
    }
  if (status === 'cancelled')
    return {
      text: 'Cancelled',
      color: 'secondary',
    }
  if (status === 'failed')
    return {
      text: 'Failed',
      color: 'error',
    }
}

const resolveStatus = status => {
  if (status === 'served')
    return {
      text: 'Served',
      color: 'success',
    }
  if (status === 'request')
    return {
      text: 'Request',
      color: 'info',
    }
  if (status === 'process')
    return {
      text: 'Process',
      color: 'primary',
    }
  if (status === 'ready')
    return {
      text: 'Ready',
      color: 'info',
    }
  if (status === 'cancel')
    return {
      text: 'Cancelled',
      color: 'warning',
    }
}

const {
  data: ordersData,
  execute: fetchOrders,
} = await useApi(createUrl('/order/list', {
  query: {
    q: searchQuery,
    page,
    itemsPerPage,
    sortBy,
    orderBy,
  },
}))

const orders = computed(() => ordersData.value.data)
const totalOrder = computed(() => ordersData.value.total)
const isShowDialogDetailOrder = ref(false)
const selectedOrder = ref()

const showDialogDetailOrder = order => {
  selectedOrder.value = order

  isShowDialogDetailOrder.value = true
}

const doUpdateStatus = async status => {
  const id = selectedOrder.value.id

  await $api('/order/mark-status', { method: 'PUT', body: { status: status }, query: { id: id } })

  // refetch products
  fetchOrders()
}

// const orders = computed(() => [])
// const totalOrder = computed(() => 0)

// const deleteOrder = async id => {
//   await $api(`/apps/ecommerce/orders/${ id }`, { method: 'DELETE' })
//   fetchOrders()
// }
</script>

<template>
  <div>
    <!--
      <VCard class="mb-6">
      &lt;!&ndash; 👉 Widgets  &ndash;&gt;
      <VCardText>
      <VRow>
      <template
      v-for="(data, id) in widgetData"
      :key="id"
      >
      <VCol
      cols="12"
      sm="6"
      md="3"
      class="px-6"
      >
      <div
      class="d-flex justify-space-between"
      :class="$vuetify.display.xs
      ? 'product-widget'
      : $vuetify.display.sm
      ? id < 2 ? 'product-widget' : ''
      : ''"
      >
      <div class="d-flex flex-column gap-y-1">
      <h4 class="text-h4">
      {{ data.value }}
      </h4>

      <h6 class="text-h6">
      {{ data.title }}
      </h6>
      </div>

      <VAvatar
      variant="tonal"
      rounded
      size="38"
      >
      <VIcon
      :icon="data.icon"
      size="28"
      />
      </VAvatar>
      </div>
      </VCol>
      <VDivider
      v-if="$vuetify.display.mdAndUp ? id !== widgetData.length - 1
      : $vuetify.display.smAndUp ? id % 2 === 0
      : false"
      vertical
      inset
      length="55"
      />
      </template>
      </VRow>
      </VCardText>
      </VCard>
    -->

    <VCard>
      <!-- 👉 Filters -->
      <VCardText>
        <div class="d-flex justify-sm-space-between justify-start flex-wrap gap-4">
          <VTextField
            v-model="searchQuery"
            density="compact"
            placeholder="Serach Order"
            style=" max-inline-size: 200px; min-inline-size: 200px;"
          />

          <div class="d-flex gap-x-4 align-center">
            <AppSelect
              v-model="itemsPerPage"
              style="min-inline-size: 6.25rem;"
              :items="[5, 10, 20, 50, 100]"
            />
            <VBtn
              variant="tonal"
              color="secondary"
              prepend-icon="tabler-screen-share"
              text="Export"
              append-icon="tabler-chevron-down"
            />
          </div>
        </div>
      </VCardText>

      <VDivider />

      <!-- 👉 Order Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :headers="headers"
        :items="orders"
        :items-length="totalOrder"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- Order ID -->
        <template #item.orderId="{ item }">
          <span
            class="text-primary font-weight-medium cursor-pointer text-decoration-underline"
            @click="showDialogDetailOrder(item)"
          >
            {{ item.table_number }}#{{ item.order_id }}
          </span>
        </template>

        <!-- Date -->
        <template #item.createdAt="{ item }">
          {{ formatOrderDate(item.created_at) }}
        </template>

        <!-- Customers  -->
        <template #item.customerName="{ item }">
          <div class="d-flex align-center">
            <div class="d-flex flex-column"> 
              <div class="text-body-1 font-weight-medium"> 
                <RouterLink
                  :to="{ name: 'orders', params: { id: item.id } }"
                  class="text-link"
                >
                  {{ item.customer_name }}
                </RouterLink> 
              </div>
            </div> 
          </div> 
        </template> 

        <!-- Status -->
        <template #item.payment="{ item }">
          <li
            :class="`text-${resolvePaymentStatus(item.payment_status)?.color}`"
            class="font-weight-medium"
          >
            {{ resolvePaymentStatus(item.payment_status)?.text }}
          </li>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip
            v-bind="resolveStatus(item.status)"
            label
          />
        </template>

        <!--   Method -->
        <template #item.channelPayment="{ item }">
          {{ item.channel_payment.charAt(0).toUpperCase() + item.channel_payment.slice(1) }}
        </template>

        <!--   Price -->
        <template #item.totalPrice="{ item }">
          {{ formatPrice(item.total_price) }}
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn>
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem value="view">
                  View
                </VListItem>
                <VListItem value="delete">
                  Delete
                </VListItem>
              </VList>
            </VMenu>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <VDivider />

          <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
            <p class="text-sm text-disabled mb-0">
              {{ paginationMeta({ page, itemsPerPage }, totalOrder) }}
            </p>

            <VPagination
              v-model="page"
              :length="Math.ceil(totalOrder / itemsPerPage)"
              :total-visible="$vuetify.display.xs ? 1 : Math.min(Math.ceil(totalOrder / itemsPerPage), 5)"
            >
              <template #prev="slotProps">
                <VBtn
                  variant="tonal"
                  color="default"
                  v-bind="slotProps"
                  :icon="false"
                >
                  Previous
                </VBtn>
              </template>

              <template #next="slotProps">
                <VBtn
                  variant="tonal"
                  color="default"
                  v-bind="slotProps"
                  :icon="false"
                >
                  Next
                </VBtn>
              </template>
            </VPagination>
          </div>
        </template>
      </VDataTableServer>
    </VCard>
  </div>

  <!--  Dialog for showing detail product -->
  <ViewDetailOrder
    v-model:isDialogOpen="isShowDialogDetailOrder"
    :detail-order-data="selectedOrder"
    @update-status="doUpdateStatus"
  />
</template>

<style lang="scss" scoped>
.customer-title:hover{
  color: rgba(var(--v-theme-primary)) !important;
}

.product-widget{
  border-block-end: 1px solid rgba(var(--v-theme-on-surface), var(--v-border-opacity));
  padding-block-end: 1rem;
}
</style>
