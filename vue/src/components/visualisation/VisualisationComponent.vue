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

// Empty string is the "All Cases" option: checkPrisonerFilter treats a blank
// filter as no filter. The homepage statistics open on the whole archive
// rather than on the currently-held subset — the charts below are a portrait
// of who has been imprisoned, and defaulting to "In Custody or Exiled" made
// them read as a portrait of who is imprisoned today, which is a much smaller
// and differently-shaped group. The database page has its own filters and is
// unaffected; this component only ever mounts on #app-stats, on the homepage.
const buttonFilter = ref<string>('')

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
