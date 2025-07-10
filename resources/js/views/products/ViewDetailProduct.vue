<script setup>
import DialogCloseBtn from "@core/components/DialogCloseBtn.vue"

const props = defineProps({
  isDialogOpen: {
    type: Boolean,
    required: true,
  },
  detailProductData: {
    type: Object,
    default: () => ({}),
  },
})


const emit = defineEmits([
  'update:isDialogOpen',
])

const refForm = ref()
const fileData = ref([])

const productData = ref({
  name: '',
  description: '',
  category: undefined,
  price: undefined,
  isAvailable: undefined,
})

const isCardDetailsVisible = ref(false)

const currentObjectUrl = ref(null)

const fetchImageAsFile = async imagePath => {
  try {
    const response = await fetch(`/storage/${imagePath}`)
    const blob = await response.blob()

    const fileName = imagePath.split('/').pop() || 'image.jpg'
    const file = new File([blob], fileName, { type: blob.type })

    // Clean up URL sebelumnya
    if (currentObjectUrl.value) {
      URL.revokeObjectURL(currentObjectUrl.value)
    }

    const url = URL.createObjectURL(blob)

    currentObjectUrl.value = url

    return {
      file,
      url,
    }
  } catch (error) {
    console.error('Gagal mengambil file gambar:', error)

    return null
  }
}

const cleanUpObjectUrl = () => {
  if (currentObjectUrl.value) {
    URL.revokeObjectURL(currentObjectUrl.value)
    currentObjectUrl.value = null
  }
}


watch(() => props.isDialogOpen, isOpen => {
  if (isOpen && props.detailProductData && Object.keys(props.detailProductData).length > 0) {

    // Mode edit
    productData.value = {
      name: props.detailProductData.name || '',
      description: props.detailProductData.description || '',
      category: props.detailProductData.category || '',
      price: props.detailProductData.price || '',
      isAvailable: props.detailProductData.is_available !== undefined ? !!props.detailProductData.is_available : true,
    }

    if (props.detailProductData.image_path) {
      fetchImageAsFile(props.detailProductData.image_path).then(result => {
        if (result) {
          fileData.value = [result]
        } else {
          fileData.value = []
        }
      })
    } else {
      fileData.value = []
    }
  }
})

const handlerDialogModelValueUpdate = val => {
  emit('update:isDialogOpen', val)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
    fileData.value = []
    cleanUpObjectUrl()
  })
}

const closeDialog = () => {
  emit('update:isDialogOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
    fileData.value = []
    cleanUpObjectUrl()
  })
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
</script>

<template>
  <VDialog
    :model-value="props.isDialogOpen"
    max-width="600"
    @update:model-value="handlerDialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="closeDialog" />

    <!-- Dialog Content -->
    <VCard title="Product Information">
      <VCardText>
        <VCard>
          <template
            v-for="(item, index) in fileData"
            :key="index"
          >
            <VImg
              :src="item.url"
              height="300"
              class="w-100 mx-auto"
              cover
            />
          </template>

          <VCardItem>
            <div class="d-flex justify-space-between flex-wrap">
              <div class="me-2 mb-2">
                <VCardTitle class="pa-0">
                  {{ productData.name }}
                </VCardTitle>
                <VCardSubtitle class="text-caption pa-0">
                  {{ productData.category }}
                </VCardSubtitle>
              </div>
              <VChip
                v-bind="resolveStatus(productData.isAvailable)"
                density="default"
                label
              />
            </div>
          </VCardItem>

          <VCardText style="font-weight: bold; font-size: 16px;">
            {{ formatPrice(productData.price) }}
          </VCardText>

          <VCardActions>
            <VBtn @click="isCardDetailsVisible = !isCardDetailsVisible">
              Details
            </VBtn>

            <VSpacer />

            <VBtn
              icon
              size="small"
              @click="isCardDetailsVisible = !isCardDetailsVisible"
            >
              <VIcon :icon="isCardDetailsVisible ? 'tabler-chevron-up' : 'tabler-chevron-down'" />
            </VBtn>
          </VCardActions>

          <VExpandTransition>
            <div v-show="isCardDetailsVisible">
              <VDivider />
              <VCardText>{{ productData.description }}</VCardText>
            </div>
          </VExpandTransition>
        </VCard>
      </VCardText>
    </VCard>
  </VDialog>
</template>
