<template>
  <div id="filters">
   <section class="grid grid-cols-2 md:grid-cols-2 gap-2 w-full">
      <div v-for="(options, key) in filters" :key="key">
       <a-select
           class="w-full"
           mode="multiple"
           :value="selectedFilters[key] ?? []"
           :options="formatOptions(options)"
           :placeholder="key"
           @change="updateSelectedFilters(key, $event)"
       ></a-select>
     </div>
   </section>
  </div>
</template>

<script lang="ts" setup>
import { Select as ASelect } from 'ant-design-vue';
import {PrisonerFilters} from "@/@types/types";
import { ref, defineProps, defineEmits, watch } from 'vue';

const props = defineProps<{ filters: PrisonerFilters, modelValue: PrisonerFilters }>()

// Seeded from the parent rather than starting empty, so a filter the parent
// has already set -- a /database/era/1980s deep link -- shows as a selected
// tag in the dropdown instead of the results being filtered by something the
// UI does not admit to. The parent remounts this component on Clear Filters
// (via :key), at which point modelValue is {} and this seeds empty again.
const selectedFilters = ref<Record<string, string[]>>({ ...(props.modelValue ?? {}) });

const emit = defineEmits<{
  (event: 'update:modelValue', value: any): void;
}>();


watch(selectedFilters, (newVal, oldVal) => {
  emit('update:modelValue', newVal);
}, { deep: true });

// Convert array to Antd select options
const formatOptions = (array: string[]) => array
    .filter(item => item) // Remove falsy values like empty strings
    .map(item => ({ label: item, value: item }));

const updateSelectedFilters = (key: string, selectedOptions: string[]) => {
  console.log('Filters updated')
  selectedFilters.value[key] = selectedOptions;
};
</script>

<style lang="scss">
#filters {

  .ant-select-item-option-selected:not(.ant-select-item-option-disabled) {
    background: #000;
    color: #FFF;
  }

  .ant-select-selection-placeholder {
    color: #000;
    text-align: left;
    text-transform: capitalize;
  }

  .ant-select-selector {
    background: #FFF;
    border: none;
    border-radius: 0;
    min-height: 37px;
    outline: none;

    .ant-select-selection-search-input {
      color: #000;
      caret-color: #000;
    }
  }


  .ant-select-selection-item-remove {
    color: #FFF;
    margin-left: 4px;
    font-size: 12px;
    position: relative;
    top: -2px;
  }

  .ant-select-selection-item {
    background: #000;
    color: #fff;
    border: none;
    border-radius: 0;
    padding: 0 5px;
    height: 25px;
    line-height: 25px;
    margin: 0 2px 0 0;
    font-size: 12px;

  }

  .ant-select-item-option-active, .ant-select-item-option-selected {
    background: #000;
    color:#FFF;
  }

// Options - white background with black text
  .ant-select-dropdown {
    .ant-select-item {
      background-color: white;
      color: black;

    // On hover - invert colors
      &:hover {
        background-color: black;
        color: white;
      }
    }
  }
}
</style>
