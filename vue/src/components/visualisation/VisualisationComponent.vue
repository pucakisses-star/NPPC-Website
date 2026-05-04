<template>
  <section>
    <nav>
      <fieldset style="border:none; padding:0; margin:0;">
        <legend class="sr-only">Filter statistics by prisoner status</legend>
        <a-radio-group v-model:value="buttonFilter" class="visualisation-filter-cnt">
          <a-radio-button value="imprisonedOrExiled">In Custody or Exiled</a-radio-button>
          <a-radio-button value="">All Cases</a-radio-button>
          <a-radio-button value="inExile">In Exile</a-radio-button>
          <a-radio-button value="inCustody">In Custody</a-radio-button>
          <a-radio-button value="released">Released</a-radio-button>
          <a-radio-button value="awaitingTrial">Awaiting Trial</a-radio-button>
        </a-radio-group>
      </fieldset>
    </nav>
    <Suspense>
      <GraphComponent :records="filteredRecords"/>
    </Suspense>
    <Suspense>
      <NumbersComponent :records="filteredRecords"/>
    </Suspense>
    <Suspense>
      <StateMapComponent :records="filteredRecords"/>
    </Suspense>
  </section>
</template>
<script setup lang="ts">
import GraphComponent from "@/components/visualisation/GraphComponent.vue";
import NumbersComponent from "@/components/visualisation/NumbersComponent.vue";
import StateMapComponent from "@/components/visualisation/StateMapComponent.vue";
import {computed, ref} from "vue";
import {useFilter} from "@/composables/useFilter";
import useAirtable from "@/composables/useAirtable";
const {checkPrisonerFilter} = useFilter()
const { records, fetchRecords } = useAirtable();
await fetchRecords();

const buttonFilter = ref<string>('imprisonedOrExiled')

// Computed property to generate filtered records
const filteredRecords = computed(() => {
  return records.value.filter((record) => {
    return checkPrisonerFilter(record, buttonFilter)
  });
});

</script>



<style lang="scss">
.visualisation-filter-cnt {
  padding-top: 1rem !important
}
</style>
