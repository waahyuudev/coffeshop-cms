<script setup>
import { VDataTableServer } from 'vuetify/labs/VDataTable'
import { paginationMeta } from '@api-utils/paginationMeta'
import { resolveImgUrl } from "@/utils/image-utils"
import ProductForm from "@/views/products/ProductForm.vue"
import DialogConfirmationDelete from "@/components/dialogs/DialogConfirmationDelete.vue"
import ViewDetailProduct from "@/views/products/ViewDetailProduct.vue"

definePage({
  meta: {
    authenticatedOnly: true,
  },
})

const widgetData = ref([
  {
    title: 'In-Store Sales',
    value: '$5,345.43',
    icon: 'tabler-home',
    desc: '5k orders',
    change: 5.7,
  },
  {
    title: 'Website Sales',
    value: '$674,347.12',
    icon: 'tabler-device-laptop',
    desc: '21k orders',
    change: 12.4,
  },
  {
    title: 'Discount',
    value: '$14,235.12',
    icon: 'tabler-gift',
    desc: '6k orders',
  },
  {
    title: 'Affiliate',
    value: '$8,345.23',
    icon: 'tabler-wallet',
    desc: '150 orders',
    change: -3.5,
  },
])

const headers = [
  {
    title: 'Product',
    key: 'product',
  },
  {
    title: 'Category',
    key: 'category',
  },
  {
    title: 'Price',
    key: 'price',
  },
  {
    title: 'Status',
    key: 'status',
  },
  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
]

const selectedStatus = ref()
const selectedCategory = ref()
const searchQuery = ref('')

const status = ref([
  {
    title: 'In Stock',
    value: 1,
  },
  {
    title: 'Out Of Stock',
    value: 0,
  },
])

const categories = ref([
  {
    title: 'Food',
    value: 'food',
  },
  {
    title: 'Coffee',
    value: 'coffee',
  },
])

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const updateOptions = options => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const resolveCategory = category => {
  if (category === 'coffee')
    return {
      color: 'success',
      icon: 'tabler-coffee',
    }
  if (category === 'non-coffee')
    return {
      color: 'info',
      icon: 'tabler-mug',
    }
  if (category === 'food')
    return {
      color: 'warning',
      icon: 'tabler-bowl',
    }
}

const resolveStatus = statusMsg => {
  if (statusMsg)
    return {
      text: 'In Stock',
      color: 'success',
    }
  else 
    return {
      text: 'Out Of Stocks',
      color: 'error',
    } 
  
}

const isShowDialogDetailProduct = ref(false)
const isFormAddOrUpdateVisible = ref(false)
const isDialogVisibleDelete = ref(false)
const selectedProduct = ref()
const itemId = ref()

const {
  data: productsData,
  execute: fetchProducts,
} = await useApi(createUrl('/product/list', {
  query: {
    q: searchQuery,
    isAvailable: selectedStatus,
    category: selectedCategory,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const products = computed(() => productsData.value.data)
const totalProduct = computed(() => productsData.value.total)

const showDialogDelete = id => {
  itemId.value = id

  isDialogVisibleDelete.value = true
}

const showDetailProduct = product => {
  selectedProduct.value = product

  isShowDialogDetailProduct.value = true
}

const showFormProduct = (product = {}) => {
  selectedProduct.value = product

  isFormAddOrUpdateVisible.value = true
}

const deleteProduct = async id => {
  await $api('/product/delete', { method: 'DELETE', query: { id: id } })

  // refetch products
  fetchProducts()
}

const handleSaveOrUpdate = async formData => {
  try {
    if (formData.get('id')) {
      await $api('/product/update', {
        method: 'POST',
        query: { id: formData.get('id') },
        body: formData,
        onResponseError({ response }) {
          console.log(response)
        },
      })
    } else {
      await $api('/product/add', {
        method: 'POST',
        body: formData,
        onResponseError({ response }) {
          console.log(response)
        },
      })
    }

    fetchProducts()
  } catch (err) {
    console.error('Error:', err)
  }
}
</script>

<template>
  <div>
    <!-- 👉 widgets -->
    <!--
      <VCard class="mb-6">
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
      <div class="text-body-1 font-weight-medium text-capitalize">
      {{ data.title }}
      </div>

      <h4 class="text-h4 my-1">
      {{ data.value }}
      </h4>

      <div class="d-flex">
      <div class="me-2 text-disabled text-no-wrap">
      {{ data.desc }}
      </div>

      <VChip
      v-if="data.change"
      label
      :color="data.change > 0 ? 'success' : 'error'"
      >
      {{ prefixWithPlus(data.change) }}%
      </VChip>
      </div>
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
      length="95"
      />
      </template>
      </VRow>
      </VCardText>
      </VCard>
    -->

    <!-- 👉 products -->
    <VCard
      title="Filters"
      class="mb-6"
    >
      <VCardText>
        <VRow>
          <!-- 👉 Select Status -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedStatus"
              placeholder="Status"
              :items="status"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <!-- 👉 Select Category -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedCategory"
              placeholder="Category"
              :items="categories"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider class="my-4" />

      <div class="d-flex flex-wrap gap-4 mx-5">
        <div class="d-flex align-center">
          <!-- 👉 Search  -->
          <AppTextField
            v-model="searchQuery"
            placeholder="Search Product"
            density="compact"
            style="inline-size: 200px;"
            class="me-3"
          />
        </div>

        <VSpacer />
        <div class="d-flex gap-4 flex-wrap align-center">
          <AppSelect
            v-model="itemsPerPage"
            :items="[5, 10, 20, 25, 50]"
          />

          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="showFormProduct({})"
          >
            Add Product
          </VBtn>
        </div>
      </div>

      <VDivider class="mt-4" />

      <!-- 👉 Datatable  -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :headers="headers"
        show-select
        :items="products"
        :items-length="totalProduct"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- product  -->
        <template #item.product="{ item }">
          <div class="d-flex align-center gap-x-2">
            <VAvatar
              v-if="item.image_path"
              size="38"
              rounded
              variant="tonal"
              :image="resolveImgUrl(item.image_path)"
            />
            <div class="d-flex flex-column">
              <span class="text-body-1 font-weight-medium">{{ item.name }}</span>
            </div>
          </div>
        </template>

        <!-- price  -->
        <template #item.price="{ item }">
          <span class="text-body-1 font-weight-medium">{{ formatPrice(item.price) }}</span>
        </template>

        <!-- category -->
        <template #item.category="{ item }">
          <VAvatar
            size="30"
            variant="tonal"
            :color="resolveCategory(item.category)?.color"
            class="me-2"
          >
            <VIcon
              :icon="resolveCategory(item.category)?.icon"
              size="18"
            />
          </VAvatar>
          <span class="text-body-1 font-weight-medium">{{ item.category }}</span>
        </template>

        <!-- status -->
        <template #item.status="{ item }">
          <VChip
            v-bind="resolveStatus(item.is_available)"
            density="default"
            label
          />
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn>
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem
                  value="detail"
                  prepend-icon="tabler-eye"
                  @click="showDetailProduct(item)"
                >
                  Detail
                </VListItem>

                <VListItem
                  value="edit"
                  prepend-icon="tabler-edit"
                  @click="showFormProduct(item)"
                >
                  Edit
                </VListItem>

                <VListItem
                  value="delete"
                  prepend-icon="tabler-trash"
                  @click="showDialogDelete(item.id)"
                >
                  Delete
                </VListItem>
              </VList>
            </VMenu>
          </IconBtn>
        </template>

        <template #bottom>
          <VDivider />

          <div class="d-flex align-center justify-space-between flex-wrap gap-3 pa-5 pt-3">
            <p class="text-sm text-medium-emphasis mb-0">
              {{ paginationMeta({ page, itemsPerPage }, totalProduct) }}
            </p>

            <VPagination
              v-model="page"
              :length="Math.min(Math.ceil(totalProduct / itemsPerPage), 5)"
              :total-visible="$vuetify.display.xs ? 1 : Math.min(Math.ceil(totalProduct / itemsPerPage), 5)"
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
  <ViewDetailProduct
    v-model:isDialogOpen="isShowDialogDetailProduct"
    :detail-product-data="selectedProduct"
  />

  <!--  Dialog for add and update product -->
  <ProductForm
    v-model:isDialogOpen="isFormAddOrUpdateVisible"
    :default-form-data="selectedProduct"
    @submit-product="handleSaveOrUpdate"
  />

  <!--  Dialog for delete product -->
  <DialogConfirmationDelete
    v-model:isDialogOpen="isDialogVisibleDelete"
    :item-id="itemId"
    @delete="deleteProduct"
  />
</template>

<style lang="scss" scoped>
.product-widget{
  border-block-end: 1px solid rgba(var(--v-theme-on-surface), var(--v-border-opacity));
  padding-block-end: 1rem;
}
</style>
