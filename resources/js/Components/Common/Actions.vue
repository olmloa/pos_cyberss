<script setup>
import { ref, watch } from 'vue';
import { DangerButton, DialogModal, SecondaryButton } from '@/Components/Jet';

const props = defineProps({
  row: { type: Object },
  record: { type: String },
  deleted: { type: Boolean },
  editRow: { type: Function },
  deleting: { type: Boolean },
  permission: { type: String },
  deleteRow: { type: Function },
});

const confirm = ref(false);

watch(
  () => props.deleted,
  value => {
    console.log('watch deleted', value);

    if (value == props.row.id) {
      confirm.value = false;
    }
  }
);

function deleteRecord(row) {
  props.deleteRow(row);
}
</script>

<template>
  <div class="text-mute flex items-center gap-4">
    <button v-if="editRow && $can('update-' + permission)" type="button" class="link" @click="editRow(row)">
      <Icon name="edit-o" />
    </button>
    <button v-if="deleteRow && $can('delete-' + permission)" type="button" @click="confirm = true">
      <Icon v-if="row.deleted_at" name="trash" class="link-red" />
      <Icon v-else name="trash-o" class="link-red" />
    </button>

    <!-- Delete Account Confirmation Modal -->
    <DialogModal :show="confirm" @close="confirm = false" max-width="sm" :backdrop="!deleting" :closeable="!deleting">
      <template #title>
        <span class="text-red-500"> {{ $t('Delete {x}', { x: record || $t('record') }) }}? </span>
      </template>

      <template #content>
        <p>
          {{ $t('Are you sure you want to delete the {record}?', { record: record || $t('record') }) }}
        </p>
        <div v-if="row.deleted_at" class="mt-4 font-extrabold text-red-500">
          <p>
            {{ $t('{x} will be deleted permanently from system.', { x: record || $t('Record') }) }}
          </p>
          <p class="mt-2">
            {{ $t('This action is not reversible!', { x: record || $t('Record') }) }}
          </p>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="confirm = false" :class="{ 'opacity-25': deleting }" :disabled="deleting">
          {{ $t('Cancel') }}
        </SecondaryButton>

        <DangerButton class="ms-3" @click="deleteRecord(row)" :class="{ 'opacity-25': deleting }" :disabled="deleting">
          {{ $t('Delete {x}', { x: record || $t('record') }) }}
        </DangerButton>
      </template>
    </DialogModal>
  </div>
</template>
