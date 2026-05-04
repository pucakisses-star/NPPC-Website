<script lang="ts" setup>
import { computed, ref } from 'vue';
import { PrisonerRecord } from '@/@types/types';

interface Props {
  records: PrisonerRecord[];
}
const props = defineProps<Props>();

const stateNames: Record<string, string> = {
  AL: 'Alabama',        AK: 'Alaska',         AZ: 'Arizona',
  AR: 'Arkansas',       CA: 'California',     CO: 'Colorado',
  CT: 'Connecticut',    DE: 'Delaware',       DC: 'District of Columbia',
  FL: 'Florida',        GA: 'Georgia',        HI: 'Hawaii',
  ID: 'Idaho',          IL: 'Illinois',       IN: 'Indiana',
  IA: 'Iowa',           KS: 'Kansas',         KY: 'Kentucky',
  LA: 'Louisiana',      ME: 'Maine',          MD: 'Maryland',
  MA: 'Massachusetts',  MI: 'Michigan',       MN: 'Minnesota',
  MS: 'Mississippi',    MO: 'Missouri',       MT: 'Montana',
  NE: 'Nebraska',       NV: 'Nevada',         NH: 'New Hampshire',
  NJ: 'New Jersey',     NM: 'New Mexico',     NY: 'New York',
  NC: 'North Carolina', ND: 'North Dakota',   OH: 'Ohio',
  OK: 'Oklahoma',       OR: 'Oregon',         PA: 'Pennsylvania',
  RI: 'Rhode Island',   SC: 'South Carolina', SD: 'South Dakota',
  TN: 'Tennessee',      TX: 'Texas',          UT: 'Utah',
  VT: 'Vermont',        VA: 'Virginia',       WA: 'Washington',
  WV: 'West Virginia',  WI: 'Wisconsin',      WY: 'Wyoming',
};

// abbr, row, col (1-indexed); same layout as Intercept's Trial and Terror
const grid: Array<[string, number, number]> = [
  ['ME', 1, 11],
  ['WA', 2, 1], ['ID', 2, 2], ['MT', 2, 3], ['ND', 2, 4], ['MN', 2, 5], ['WI', 2, 6], ['MI', 2, 7], ['PA', 2, 8], ['VT', 2, 9], ['NH', 2, 10], ['MA', 2, 11],
  ['OR', 3, 1], ['NV', 3, 2], ['WY', 3, 3], ['SD', 3, 4], ['IA', 3, 5], ['IN', 3, 6], ['OH', 3, 7], ['MD', 3, 8], ['NY', 3, 9], ['CT', 3, 10], ['RI', 3, 11],
  ['CA', 4, 1], ['UT', 4, 2], ['CO', 4, 3], ['NE', 4, 4], ['MO', 4, 5], ['IL', 4, 6], ['KY', 4, 7], ['DC', 4, 8], ['DE', 4, 9], ['NJ', 4, 10],
  ['AZ', 5, 2], ['NM', 5, 3], ['KS', 5, 4], ['AR', 5, 5], ['TN', 5, 6], ['WV', 5, 7], ['VA', 5, 8], ['NC', 5, 9],
  ['OK', 6, 4], ['LA', 6, 5], ['MS', 6, 6], ['AL', 6, 7], ['GA', 6, 8], ['SC', 6, 9],
  ['AK', 7, 1], ['HI', 7, 2], ['TX', 7, 4], ['FL', 7, 10],
];

// Build a name → abbr lookup so records with full state names match
const nameToAbbr = computed(() => {
  const m: Record<string, string> = {};
  Object.entries(stateNames).forEach(([abbr, name]) => {
    m[name.toLowerCase()] = abbr;
    m[abbr.toLowerCase()] = abbr;
  });
  return m;
});

// Reactive counts per state, recomputed every time props.records changes
const counts = computed(() => {
  const c: Record<string, number> = {};
  Object.keys(stateNames).forEach(a => { c[a] = 0; });
  props.records.forEach(r => {
    const raw = (r as any).State;
    if (!raw) return;
    const list = Array.isArray(raw) ? raw : [raw];
    list.forEach((entry: string) => {
      if (!entry || typeof entry !== 'string') return;
      const abbr = nameToAbbr.value[entry.toLowerCase()];
      if (abbr) c[abbr]++;
    });
  });
  return c;
});

const total = computed(() => Object.values(counts.value).reduce((a, b) => a + b, 0));
const max = computed(() => Math.max(...Object.values(counts.value), 1));

const colorFor = (n: number): string => {
  if (n <= 0) return '#14141a';
  // Log-scale so single-defendant states are visible
  const t = Math.min(1, Math.log(n + 1) / Math.log(max.value + 1));
  // 3-stop interpolation: dark → site brand blue → magenta
  let r: number, g: number, b: number;
  if (t < 0.5) {
    const u = t / 0.5;
    r = Math.round(0x14 + u * (0x56 - 0x14));
    g = Math.round(0x14 + u * (0x60 - 0x14));
    b = Math.round(0x1a + u * (0xfe - 0x1a));
  } else {
    const u = (t - 0.5) / 0.5;
    r = Math.round(0x56 + u * (0xc5 - 0x56));
    g = Math.round(0x60 + u * (0x27 - 0x60));
    b = Math.round(0xfe + u * (0x9a - 0xfe));
  }
  const hex = (v: number) => v.toString(16).padStart(2, '0');
  return `#${hex(r)}${hex(g)}${hex(b)}`;
};

// Tooltip state
const tipVisible = ref(false);
const tipName = ref('');
const tipCount = ref(0);
const tipPct = ref(0);
const tipX = ref(0);
const tipY = ref(0);

const showTip = (abbr: string, e: MouseEvent) => {
  tipName.value = stateNames[abbr];
  tipCount.value = counts.value[abbr];
  tipPct.value = total.value > 0 ? Math.round((tipCount.value / total.value) * 100) : 0;
  tipVisible.value = true;
  moveTip(e);
};

const moveTip = (e: MouseEvent) => {
  const offset = 14;
  let x = e.clientX + offset;
  let y = e.clientY + offset;
  if (x + 240 > window.innerWidth) x = e.clientX - 240 - offset;
  if (y + 80 > window.innerHeight) y = e.clientY - 80 - offset;
  tipX.value = x;
  tipY.value = y;
};

const hideTip = () => { tipVisible.value = false; };
</script>

<template>
  <section id="state-map-component">
    <div class="state-map-inner">
      <h2 class="state-map-title">All Prosecutions by State</h2>
      <p class="state-map-sub">Hover any state for the count and share of total documented cases.</p>

      <div class="state-map-grid">
        <a v-for="[abbr, row, col] in grid"
           :key="abbr"
           :href="`/database?state=${encodeURIComponent(stateNames[abbr])}`"
           class="state-cell"
           :style="{ gridRow: row, gridColumn: col, background: colorFor(counts[abbr]) }"
           @mouseenter="(e) => showTip(abbr, e)"
           @mousemove="moveTip"
           @mouseleave="hideTip">
          <span class="state-abbr">{{ abbr }}</span>
        </a>
      </div>

      <div class="state-map-legend">
        <span>1</span>
        <div class="legend-bar"></div>
        <span>{{ max }}</span>
      </div>
    </div>

    <transition name="fade">
      <div v-if="tipVisible" class="state-map-tooltip" :style="{ left: tipX + 'px', top: tipY + 'px' }">
        <div class="tt-name">{{ tipName }}</div>
        <div class="tt-pct">{{ tipPct > 0 ? tipPct : '<1' }}% of all defendants</div>
        <div class="tt-count">{{ tipCount }} defendant{{ tipCount === 1 ? '' : 's' }}</div>
      </div>
    </transition>
  </section>
</template>

<style lang="scss" scoped>
#state-map-component {
  background: #000;
  padding: 96px 24px 80px;
}
.state-map-inner {
  max-width: 1100px;
  margin: 0 auto;
}
.state-map-title {
  text-align: center;
  font-size: 13px;
  font-weight: 800;
  color: #fff;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  margin: 0 0 8px;
}
.state-map-sub {
  text-align: center;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.4);
  margin: 0 0 40px;
}
.state-map-grid {
  display: grid;
  grid-template-rows: repeat(7, 64px);
  grid-template-columns: repeat(11, 1fr);
  gap: 4px;
  max-width: 880px;
  margin: 0 auto;
}
.state-cell {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  border: 1px solid rgba(255, 255, 255, 0.08);
  cursor: pointer;
  // Smooth morph when filters change
  transition: background-color 0.45s ease, transform 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease;
}
.state-cell:hover {
  transform: scale(1.05);
  border-color: rgba(255, 255, 255, 0.5);
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.15);
  z-index: 2;
}
.state-abbr {
  font-size: 12px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.85);
  letter-spacing: 0.04em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.7);
}

.state-map-legend {
  display: flex;
  align-items: center;
  gap: 12px;
  max-width: 320px;
  margin: 28px auto 0;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.55);
}
.legend-bar {
  flex: 1;
  height: 6px;
  background: linear-gradient(90deg, #14141a 0%, #2c3a8c 25%, #5660fe 50%, #8d44d6 75%, #c5279a 100%);
  border-radius: 2px;
}

.state-map-tooltip {
  position: fixed;
  z-index: 1000;
  background: #ffffff;
  color: #0a0a0a;
  padding: 10px 14px;
  border-radius: 4px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
  font-size: 13px;
  line-height: 1.5;
  pointer-events: none;
  max-width: 220px;

  .tt-name { font-weight: 800; margin-bottom: 4px; }
  .tt-pct, .tt-count { color: rgba(0, 0, 0, 0.7); }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.12s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 760px) {
  .state-map-grid { grid-template-rows: repeat(7, 44px); }
  .state-abbr { font-size: 10px; }
}
</style>
