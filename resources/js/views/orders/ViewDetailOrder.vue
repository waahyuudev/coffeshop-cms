<script setup>
import { VDataTable } from 'vuetify/labs/VDataTable'
import DialogCloseBtn from "@core/components/DialogCloseBtn.vue"

const props = defineProps({
  isDialogOpen: {
    type: Boolean,
    required: true,
  },
  detailOrderData: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits([
  'update:isDialogOpen',
])

const isConfirmDialogVisible = ref(false)

const headers = [
  {
    title: 'Product',
    key: 'productName',
  },
  {
    title: 'Price',
    key: 'price',
  },
  {
    title: 'Quantity',
    key: 'quantity',
  },
  {
    title: 'Total',
    key: 'total',
  },
]

const orderData = [
  {
    productName: 'OnePlus 7 Pro',
    productImage: '',
    subtitle: 'Storage: 128gb',
    price: 799,
    quantity: 1,
    total: 799,
  },
  {
    productName: 'Face Cream',
    productImage: '',
    subtitle: 'Gender: Women',
    price: 89,
    quantity: 1,
    total: 89,
  },
  {
    productName: 'Wooden Chair',
    productImage: '',
    subtitle: 'Material: Woodem',
    price: 289,
    quantity: 2,
    total: 578,
  },
  {
    productName: 'Nike Jorden',
    productImage: '',
    subtitle: 'Size: 8UK',
    price: 299,
    quantity: 2,
    total: 598,
  },
]


const handlerDialogModelValueUpdate = val => {
  emit('update:isDialogOpen', val)
}

const closeDialog = () => {
  emit('update:isDialogOpen', false)
}
</script>

<template>
  <VDialog
    :model-value="props.isDialogOpen"
    max-width="700"
    @update:model-value="handlerDialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="closeDialog" />

    <!-- Dialog Content -->
    <VCard>
      <VCardText>
        <div class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
          <div>
            <div class="d-flex gap-2 align-center mb-2 flex-wrap">
              <h4 class="text-h4">
                Order {{ props.detailOrderData.table_number }}#{{ props.detailOrderData.order_id }}
              </h4>
            </div>
            <div>
              <span class="text-body-1">
                {{ formatOrderDate(props.detailOrderData.created_at) }}
              </span>
            </div>
          </div>

          <VChip
            v-if="props.detailOrderData.status === 'served'"
            v-bind="'Served/Completed'"
            label
          />

          <VBtn
            v-else-if="props.detailOrderData.status === 'request'"
            variant="tonal"
            color="warning"
            @click="isConfirmDialogVisible = !isConfirmDialogVisible"
          >
            Mark Process
          </VBtn>

          <VBtn
            v-else-if="props.detailOrderData.status === 'ready'"
            variant="tonal"
            color="info"
            @click="isConfirmDialogVisible = !isConfirmDialogVisible"
          >
            Mark Served
          </VBtn>
        </div>


        <VCardItem>
          <template #title>
            <h5 class="text-h5">
              Order Details
            </h5>
          </template>
          <template #append>
            <VBtn variant="text">
              Edit
            </VBtn>
          </template>
        </VCardItem>

        <VDivider />
        <VDataTable
          :headers="headers"
          :items="props.detailOrderData.items"
          item-value="productName"
          class="text-no-wrap"
        >
          <template #item.productName="{ item }">
            <div class="d-flex gap-x-3">
              <!--              <VAvatar -->
              <!--                size="38" -->
              <!--                :image="item.productImage" -->
              <!--                :rounded="0" -->
              <!--              /> -->

              <div class="d-flex flex-column align-start">
                <span class="text-body-1 font-weight-medium">
                  {{ item.product.name }}
                </span>

                <span class="text-sm text-disabled">
                  {{ item.note }}
                </span>
              </div>
            </div>
          </template>

          <template #item.price="{ item }">
            <span>{{ formatPrice(item.total_price) }}</span>
          </template>

          <template #item.total="{ item }">
            <span class="text-h6 ">
              {{ formatPrice(item.subtotal_price) }}
            </span>
          </template>

          <template #item.quantity="{ item }">
            <span class="text-high-emphasis font-weight-medium">{{ item.quantity }}</span>
          </template>

          <template #bottom />
        </VDataTable>
      </VCardText>
      <VDivider />

      <VCardText>
        <div class="d-flex align-end flex-column">
          <table class="text-high-emphasis">
            <tbody>
              <tr>
                <td class="text-high-emphasis font-weight-medium">
                  Grand Total :
                </td>
                <td class="font-weight-medium">
                  {{ formatPrice(props.detailOrderData.total_price) }}
                </td>
              </tr>
              <tr>
                <td class="text-high-emphasis font-weight-medium">
                  Pembayaran :
                </td>
                <td class="font-weight-medium">
                  {{ props.detailOrderData.channel_payment.charAt(0).toUpperCase() + props.detailOrderData.channel_payment.slice(1) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>
