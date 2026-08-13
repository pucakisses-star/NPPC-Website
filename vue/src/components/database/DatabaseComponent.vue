
<script lang="ts" async setup>
import useAirtable from '../../composables/useAirtable';
import {ref, watch, computed, onMounted, onUnmounted, nextTick} from "vue";
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

// The URL and the filters stay in step both ways: /database/era/1980s opens
// with that era selected, and selecting an era rewrites the address bar to
// match. The path is the single source of truth for both directions, which is
// what makes the Back button work -- popstate gives us a new path and nothing
// else, so anything the path cannot express would be lost on the way back.
const FACET_KEYS = ['ideology', 'era', 'affiliation', 'state', 'race', 'gender'];

// The URL segment is matched against the real filter options rather than used
// as-is, because the option is "Black Panther Party" and the URL is
// "black-panther-party". Slugging both sides means a link works however it was
// written -- Anarchism, anarchism, ANARCHISM all land on the same page.
const slugifyFacet = (value: string): string =>
    (value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

const facetFromPath = (): { key: string, value: string } | null => {
  const parts = window.location.pathname.split('/').filter(Boolean);

  if (parts.length !== 3 || parts[0] !== 'database' || !FACET_KEYS.includes(parts[1])) return null;

  try {
    return { key: parts[1], value: decodeURIComponent(parts[2]) };
  } catch {
    // A malformed escape sequence in the URL is not worth throwing over.
    return { key: parts[1], value: parts[2] };
  }
};

// Resolves a URL segment to the actual option string, or null if no option
// matches -- a link to an ideology that has since been renamed should leave
// the page unfiltered rather than empty.
const matchFacetValue = (key: string, value: string): string | undefined => {
  const options: string[] = (filterFieldsObj as any)[key] ?? [];
  const wanted = slugifyFacet(value);

  return options.find(option => slugifyFacet(option) === wanted);
};

const applyFacetFromUrl = () => {
  const facet = facetFromPath();
  if (!facet) return;

  const match = matchFacetValue(facet.key, facet.value);
  if (!match) return;

  filterObject.value = { ...filterObject.value, [facet.key]: [match] };

  // Someone following a link to an era or an ideology wants to browse it, not
  // to see only the handful of those people still inside. The default status
  // filter would leave most of these links looking almost empty, so a deep
  // link opens on All Cases.
  buttonFilter.value = '';
};

// Runs during setup, after the top-level await on fetchRecords above, so the
// option lists are already populated and FiltersComponent is created with the
// selection in place.
applyFacetFromUrl();

const visibleCount = ref(20);
const loadMoreTrigger = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

// Minor cases (brief detentions, low-significance arrests) are hidden by
// default; the checkbox beside the results count includes them.
const includeMinor = ref(false);

// Searching a name is a search for a person, not a browse of the significant
// cases: somebody typing "Kyle Snyder" wants Kyle Snyder, and a record being
// classified minor is no reason to tell them he does not exist. So a name
// search turns the filter off for as long as it lasts, and the checkbox
// visibly ticks itself rather than the filter changing behind the user's back.
//
// A deliberate toggle always wins. Unticking during a search means "I really
// do want minor cases hidden", and is remembered for later searches too;
// ticking it by hand outside a search survives the search being cleared.
const minorAutoEnabled = ref(false);
const minorUserSet = ref(false);

// Fires only on a real click, not on the programmatic changes below.
const onIncludeMinorToggled = () => {
  minorUserSet.value = true;
  minorAutoEnabled.value = false;
};

watch(nameSearch, (value) => {
  // Trimmed, to match the name filter itself: whitespace is not a search.
  if (value.trim() !== '') {
    if (!minorUserSet.value && !includeMinor.value) {
      includeMinor.value = true;
      minorAutoEnabled.value = true;
    }
  } else if (minorAutoEnabled.value) {
    // Only ever undo what this did; a hand-ticked box is left alone.
    includeMinor.value = false;
    minorAutoEnabled.value = false;
  }
});

// Computed property to generate filtered records
const filteredRecords = computed(() => {
  return records.value.filter((record) => {
    if (!includeMinor.value && record['Minor Case']) return false;
    return checkPrisonerFilter(record, buttonFilter, cleanFilterObject, nameSearch)
  });
});

const visibleRecords = computed(() => {
  return filteredRecords.value.slice(0, visibleCount.value);
});

const hasMore = computed(() => {
  return visibleCount.value < filteredRecords.value.length;
});

// Reset visible count when filters change
watch([buttonFilter, cleanFilterObject, nameSearch, includeMinor], () => {
  visibleCount.value = 20;
});

// Auto-fallback to "All Cases" when the status filter is what emptied the
// results. Picking an era like 1700s while "In Custody or Exiled" is selected
// would otherwise show nothing, when the era itself has plenty of records --
// so the status filter drops away rather than the page looking empty.
// autoSwitchedFrom drives the notice, and is cleared as soon as the user
// changes the status filter themselves.
const autoSwitchedFrom = ref<string>('');

const statusFilterLabels: Record<string, string> = {
  imprisonedOrExiled: 'In Custody or Exiled',
  inExile: 'In Exile',
  inCustody: 'In Custody',
  released: 'Released',
  awaitingTrial: 'Awaiting Trial',
};

// Same filtering, but ignoring the status buttons -- tells us whether the
// other filters on their own would have matched anything.
const matchesWithoutStatus = computed(() => {
  const noStatus = ref('');
  return records.value.filter((record) => {
    if (!includeMinor.value && record['Minor Case']) return false;
    return checkPrisonerFilter(record, noStatus, cleanFilterObject, nameSearch);
  }).length;
});

watch([filteredRecords, matchesWithoutStatus], () => {
  if (
    buttonFilter.value !== '' &&
    filteredRecords.value.length === 0 &&
    matchesWithoutStatus.value > 0
  ) {
    autoSwitchedFrom.value = statusFilterLabels[buttonFilter.value] ?? buttonFilter.value;
    buttonFilter.value = '';
  }
});

// A deliberate change of the status filter dismisses the notice.
watch(buttonFilter, (value) => {
  if (value !== '') autoSwitchedFrom.value = '';
});

onMounted(() => {
  window.addEventListener('popstate', onPopState);

  observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && hasMore.value) {
      visibleCount.value += 20;
    }
  }, { rootMargin: '200px' });
});

onUnmounted(() => {
  window.removeEventListener('popstate', onPopState);
  observer?.disconnect();
});

watch(loadMoreTrigger, (el) => {
  observer?.disconnect();
  if (el) observer?.observe(el);
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
  autoSwitchedFrom.value = '';
  // Clearing means back to defaults, including the remembered choice about
  // minor cases -- so the next name search auto-includes them again.
  includeMinor.value = false;
  minorAutoEnabled.value = false;
  minorUserSet.value = false;
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
  // immediate, because a deep link sets filterObject during setup -- before
  // this watcher exists. Without it the dropdown would show the era selected
  // and the list would ignore it, since cleanFilterObject is what the filter
  // actually reads. Harmless on a normal load, where it derives {} from {}.
}, { deep: true, immediate: true });

// --- keeping the address bar in step -------------------------------------
//
// Set while the filters are being changed BY the URL (a Back button press), so
// that reacting to the URL does not immediately push the URL again.
let applyingFromUrl = false;

/**
 * The path that represents the current filters, or /database when they cannot
 * be represented. The scheme holds exactly one facet with exactly one value,
 * so two eras, or an era and an ideology together, have no honest URL -- and
 * an address bar that names only half the filtering is worse than one that
 * claims nothing. Status buttons and the name search are deliberately outside
 * the scheme and never affect the path.
 */
const pathForFilters = (): string => {
  const active = Object.entries(cleanFilterObject.value)
      .filter(([key, values]) => FACET_KEYS.includes(key) && Array.isArray(values) && values.length > 0);

  if (active.length !== 1) return '/database';

  const [key, values] = active[0] as [string, string[]];

  if (values.length !== 1) return '/database';

  return `/database/${key}/${slugifyFacet(values[0])}`;
};

watch(cleanFilterObject, () => {
  if (applyingFromUrl) return;

  const next = pathForFilters();

  // Nothing to say. Also the case on first load, where the path already
  // matches the filters a deep link just applied.
  if (next === window.location.pathname) return;

  window.history.pushState({ nppcDatabaseFacet: true }, '', next + window.location.search);
}, { deep: true });

/**
 * Back and Forward. The path is all popstate gives us, so the filters are
 * rebuilt from it rather than from any remembered state: an unrepresentable
 * combination was never pushed, so there is nothing to restore.
 */
const onPopState = () => {
  applyingFromUrl = true;

  const facet = facetFromPath();
  const match = facet ? matchFacetValue(facet.key, facet.value) : undefined;

  filterObject.value = match && facet ? { [facet.key]: [match] } : {};

  if (match) buttonFilter.value = '';

  // Remount FiltersComponent so its dropdowns re-seed from the new value --
  // set after filterObject, so the fresh instance sees the new selection.
  filterKey.value++;

  // Released on the next tick, once the cleanFilterObject watcher above has
  // run for this change and declined to push it back.
  nextTick(() => { applyingFromUrl = false; });
};


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
      <div v-if="autoSwitchedFrom" class="auto-switch-notice" role="status" aria-live="polite">
        No results for “{{ autoSwitchedFrom }}” with these filters — showing <strong>All Cases</strong> instead.
      </div>
      <div class="results-row">
        <div class="results-count" v-if="filteredRecords.length">{{ filteredRecords.length }} results</div>
        <label class="include-minor">
          <input type="checkbox" v-model="includeMinor" @change="onIncludeMinorToggled" />
          <span>Include minor cases</span>
          <span v-if="minorAutoEnabled" class="include-minor-auto">on for name search</span>
        </label>
      </div>
      <template v-for="record in visibleRecords" >
        <CardComponent v-if="!record['Status Under Review']" :record="record" :key="record.id" />
      </template>
      <div v-if="hasMore" ref="loadMoreTrigger" class="load-more-indicator" role="status" aria-live="polite">
        <span class="load-more-spinner" aria-hidden="true"></span>
        <span>Loading more prisoners…</span>
      </div>
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
.auto-switch-notice {
  margin-bottom: 12px;
  padding: 10px 14px;
  border: 1px solid rgba(255,255,255,0.18);
  border-left: 3px solid rgba(255,255,255,0.55);
  border-radius: 4px;
  font-size: 14px;
  line-height: 1.5;
  color: rgba(255,255,255,0.72);
  background: rgba(255,255,255,0.04);
}
.auto-switch-notice strong { color: #fff; font-weight: 700; }

.results-row {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  margin-bottom: 16px;
  gap: 12px;
}
.results-count {
  grid-column: 2;
  text-align: center;
  font-size: 14px;
  color: rgba(255,255,255,0.4);
}
.include-minor {
  grid-column: 3;
  justify-self: end;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: rgba(255,255,255,0.6);
  cursor: pointer;
  user-select: none;
}
.include-minor input {
  width: 15px;
  height: 15px;
  accent-color: var(--accent, #4f46e5);
  cursor: pointer;
}
/* Says why the box ticked itself, without a banner for every keystroke. */
.include-minor-auto {
  font-size: 12px;
  font-style: italic;
  color: rgba(255,255,255,0.45);
  white-space: nowrap;
}
@media (max-width: 600px) {
  .results-row { grid-template-columns: 1fr auto; }
  .results-count { grid-column: 1; text-align: left; }
  .include-minor { grid-column: 2; }
}
.load-more-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 32px 16px;
  color: rgba(255, 255, 255, 0.55);
  font-size: 14px;
  font-weight: 600;
}
.load-more-spinner {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid rgba(255, 255, 255, 0.15);
  border-top-color: #fff;
  animation: load-more-spin 0.8s linear infinite;
}
@keyframes load-more-spin {
  to { transform: rotate(360deg); }
}
@media (prefers-reduced-motion: reduce) {
  .load-more-spinner { animation-duration: 2.4s; }
}
</style>

