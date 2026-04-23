
<script lang="ts" async setup>
import useAirtable from '../../composables/useAirtable';
import {ref, watch, computed, onMounted, onUnmounted} from "vue";
import {PrisonerRecord} from "@/@types/types";
import CardComponent from "@/components/database/CardComponent.vue";
import FiltersComponent from "@/components/database/FiltersComponent.vue";
import DatabaseMap from "@/components/database/DatabaseMap.vue";
import {useFilter} from "@/composables/useFilter";
const { records, fetchRecords, filterFieldsObj } = useAirtable();
await fetchRecords();
const filterObject = ref<any>({});
const cleanFilterObject = ref<Record<any, any>>({});
const nameSearch = ref<string>('');
const buttonFilter = ref<string>('imprisonedOrExiled')

const {checkPrisonerFilter} = useFilter()



// Computed property to generate filtered records
const filteredRecords = computed(() => {
  return records.value.filter((record) => {
    return checkPrisonerFilter(record, buttonFilter, cleanFilterObject, nameSearch)
  });
});



const hasActiveFilters = computed(() => {
  if (nameSearch.value) return true;
  return Object.keys(cleanFilterObject.value).length > 0;
});

const clearFilters = () => {
  nameSearch.value = '';
  filterObject.value = {};
  cleanFilterObject.value = {};
  buttonFilter.value = 'imprisonedOrExiled';
  // Force FiltersComponent to reset by incrementing a key
  filterKey.value++;
};

const filterKey = ref(0);

watch(filterObject, (newValue, oldValue) => {
  const _filters: Record<string, string[]> = {}
  Object.keys(filterObject.value).forEach((key) => {
    const value = filterObject.value[key]
    if(value && value.length > 0) {
      _filters[key] = value
    }
  })

  cleanFilterObject.value = _filters
}, { deep: true });


</script>

<template>
  <section id="prisoners-page" class="bg-black text-white py-12">
    <div class="container mx-auto">

      <fieldset style="border:none; padding:0; margin:0;">
        <legend class="sr-only">Filter by prisoner status</legend>
        <a-radio-group v-model:value="buttonFilter">
          <a-radio-button value="imprisonedOrExiled">In Custody or Exiled</a-radio-button>
          <a-radio-button value="">All Cases</a-radio-button>
          <a-radio-button value="inExile">In Exile</a-radio-button>
          <a-radio-button value="inCustody">In Custody</a-radio-button>
          <a-radio-button value="released">Released</a-radio-button>
          <a-radio-button value="awaitingTrial">Awaiting Trial</a-radio-button>
        </a-radio-group>
      </fieldset>

      <label for="prisoner-search" class="sr-only">Search prisoners by name</label>
      <input type="search" id="prisoner-search" placeholder="Search by name" v-model="nameSearch" aria-label="Search prisoners by name"/>

      <div class="flex items-center gap-4 mb-12">
        <FiltersComponent class="flex-1" :key="filterKey" :filters="filterFieldsObj" v-model:model-value="filterObject"/>
        <button v-if="hasActiveFilters" @click="clearFilters" class="clear-filters-btn">Clear Filters</button>
      </div>
      <template v-for="record in filteredRecords" >
        <CardComponent v-if="!record['Status Under Review']" :record="record" :key="record.id" />
      </template>
    </div>
  </section>
</template>

<style scoped>
.clear-filters-btn {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.3);
  color: #fff;
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
  height: 37px;
}
.clear-filters-btn:hover {
  border-color: #fff;
  background: rgba(255,255,255,0.1);
}
</style>

