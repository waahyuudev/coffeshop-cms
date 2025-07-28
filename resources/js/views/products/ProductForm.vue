<script setup>
import AppTextField from "@core/components/app-form-elements/AppTextField.vue"
import DialogCloseBtn from "@core/components/DialogCloseBtn.vue"

const props = defineProps({
  isDialogOpen: {
    type: Boolean,
    required: true,
  },
  defaultFormData: {
    type: Object,
    default: () => ({}),
  },
})


const emit = defineEmits([
  'update:isDialogOpen',
  'submitProduct',
])

const dropZoneRef = ref()
const { open, onChange } = useFileDialog({ accept: 'image/*' })

function onDrop(DroppedFiles) {
  DroppedFiles?.forEach(file => {
    if (file.type.slice(0, 6) !== 'image/') {
      alert('Only image files are allowed')

      return
    }
    fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? '',
    })
  })
}

onChange(selectedFiles => {
  if (!selectedFiles)
    return
  for (const file of selectedFiles) {
    fileData.value.push({
      file,
      url: useObjectUrl(file).value ?? '',
    })
  }
  imageError.value = false
})
useDropZone(dropZoneRef, onDrop)

const refForm = ref()
const fileData = ref([])

const formData = ref({
  name: '',
  description: '',
  category: undefined,
  price: undefined,
  isAvailable: undefined,
})

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
  if (isOpen && props.defaultFormData && Object.keys(props.defaultFormData).length > 0) {
    // Mode edit
    formData.value = {
      name: props.defaultFormData.name || '',
      description: props.defaultFormData.description || '',
      category: props.defaultFormData.category || '',
      price: props.defaultFormData.price || '',
      isAvailable: props.defaultFormData.is_available !== undefined ? !!props.defaultFormData.is_available : true,
    }

    if (props.defaultFormData.image_path) {
      fetchImageAsFile(props.defaultFormData.image_path).then(result => {
        if (result) {
          fileData.value = [result]
        } else {
          fileData.value = []
        }
      })
    } else {
      fileData.value = []
    }
  } else if (!isOpen) {
    // Reset saat dialog ditutup
    formData.value = {
      name: '',
      description: '',
      category: undefined,
      price: undefined,
      isAvailable: undefined,
    }
    fileData.value = []
  }
})

const rules = {
  name: value => !!value || 'Product name is required',
  price: value => value !== undefined && value !== '' || 'Price is required',
  category: value => !!value || 'Category is required',
  description: value => !!value || 'Description is required',
  img: value => !!value || 'Image is required',
}

const imageError = ref(false)

const onSubmit = () => {
  imageError.value = fileData.value.length === 0
  if (imageError.value) return

  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const form = new FormData()

      form.append('name', formData.value.name)
      form.append('description', formData.value.description ?? '')
      form.append('category', formData.value.category ?? '')
      form.append('price', formData.value.price ?? 0)
      form.append('is_available', formData.value.isAvailable ? 1 : 0)

      if (fileData.value.length > 0) {
        form.append('image_path', fileData.value[0].file)
      }

      // Jika ada ID, berarti mode update
      if (props.defaultFormData?.id) {
        form.append('id', props.defaultFormData.id)
      }

      emit('submitProduct', form) // Kirim FormData ke parent
      emit('update:isDialogOpen', false)

      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
        fileData.value = []
      })
    }
  })
}


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
    <VCard>
      <VCardTitle class="d-flex justify-center">
        {{ props.defaultFormData?.id ? 'Update Product' : 'Add Product' }}
      </VCardTitle>
      <VCardText>
        <VForm
          ref="refForm"
          @submit.prevent="onSubmit"
        >
          <VCard class="mb-6">
            <VCardText>
              <VRow>
                <VCol cols="12">
                  <AppTextField
                    v-model="formData.name"
                    :rules="[rules.name]"
                    label="Name"
                    placeholder="Cappuccino"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="formData.category"
                    :rules="[rules.category]"
                    label="Category"
                    placeholder="coffee"
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="formData.price"
                    :rules="[rules.price]"
                    label="Price"
                    placeholder="25000"
                    type="number"
                  />
                </VCol>
                <VCol>
                  <span class="mb-1">Description (optional)</span>
                  <VTextarea
                    v-model="formData.description"
                    placeholder="Espresso dengan steamed milk"
                    class="border rounded"
                  />
                </VCol>
              </VRow>
              <br>
              <div class="d-flex flex-raw align-center justify-space-between ">
                <span>In stock</span>
                <VSwitch
                  v-model="formData.isAvailable"
                  density="compact"
                />
              </div>
            </VCardText>
          </VCard>

          <!-- 👉 Media -->
          <VCard class="mb-6">
            <VCardItem>
              <template #title>
                Photo Product
              </template>
            </VCardItem>

            <VCardText>
              <div class="flex">
                <div class="w-full h-auto relative">
                  <div
                    ref="dropZoneRef"
                    class="cursor-pointer"
                    @click="() => open()"
                  >
                    <div
                      v-if="fileData.length === 0"
                      class="d-flex flex-column justify-center align-center gap-y-3 px-6 py-10 border-dashed drop-zone"
                    >
                      <IconBtn
                        variant="tonal"
                        class="rounded-sm"
                      >
                        <VIcon icon="tabler-upload" />
                      </IconBtn>
                      <div class="text-base text-high-emphasis font-weight-medium">
                        Drag and Drop Your Image Here.
                      </div>
                      <span class="text-disabled">or</span>

                      <VBtn variant="tonal">
                        Browse Images
                      </VBtn>
                    </div>

                    <div
                      v-else
                      class="d-flex justify-center align-center gap-3 pa-8 border-dashed drop-zone flex-wrap"
                    >
                      <VRow class="match-height">
                        <template
                          v-for="(item, index) in fileData"
                          :key="index"
                        >
                          <VCol cols="12">
                            <VCard
                              :ripple="false"
                              border
                            >
                              <VCardText
                                class="d-flex flex-column"
                                @click.stop
                              >
                                <VImg
                                  :src="item.url"
                                  height="150px"
                                  class="w-100 mx-auto"
                                  aspect-ratio="4/3"
                                  cover
                                />
                                <div class="mt-2">
                                  <span class="clamp-text text-wrap">
                                    {{ item.file.name }}
                                  </span>
                                  <span>
                                    {{ item.file.size / 1000 }} KB
                                  </span>
                                </div>
                              </VCardText>
                              <VSpacer />
                              <VCardActions>
                                <VBtn
                                  variant="outlined"
                                  block
                                  @click.stop="fileData.splice(index, 1)"
                                >
                                  Remove File
                                </VBtn>
                              </VCardActions>
                            </VCard>
                          </VCol>
                        </template>
                      </VRow>
                    </div>
                  </div>
                </div>
              </div>
              <div
                v-if="imageError"
                class="text-error text-sm mb-2"
              >
                Image is required
              </div>
            </VCardText>
          </VCard>
          <div class="d-flex justify-end flex-wrap gap-3">
            <VBtn
              variant="tonal"
              color="secondary"
              @click="closeDialog"
            >
              Close
            </VBtn>
            <VBtn type="submit">
              Save
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
