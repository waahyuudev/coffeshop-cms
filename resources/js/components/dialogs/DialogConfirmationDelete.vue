<script setup>
const props = defineProps({
  isDialogOpen: {
    type: Boolean,
    required: true,
  },
  itemId: {
    type: Number,
    required: false,
  },
})

const emit = defineEmits([
  'update:isDialogOpen',
  'delete',
])

const closeDialog = () => {
  emit('update:isDialogOpen', false)
}

const deleteItem = () => {
  emit('delete', props.itemId)
  console.log(`item delete ${props.itemId}`)
  closeDialog()
}

const handleDialogModelValueUpdate = val => {
  emit('update:isDialogOpen', val)
}
</script>

<template>
  <VDialog
    persistent
    class="v-dialog-sm"
    :model-value="props.isDialogOpen"
    @update:model-value="handleDialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="closeDialog" />

    <!-- Dialog Content -->
    <VCard title="Are you sure you want to delete this item?">
      <VCardText>
        Deleting this item means you will permanently lose the associated information.
      </VCardText>

      <VCardText class="d-flex justify-end gap-3 flex-wrap">
        <VBtn
          color="error"
          variant="tonal"
          @click="closeDialog"
        >
          Cancel
        </VBtn>
        <VBtn @click="deleteItem">
          Yes, Delete
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

