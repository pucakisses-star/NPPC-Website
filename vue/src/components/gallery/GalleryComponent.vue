<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue';
import useAirtable from "@/composables/useAirtable";

const { records, fetchRecords } = useAirtable();
await fetchRecords();

// Only include prisoners whose record has an actual Photo URL — otherwise
// they show up as empty black boxes with a name label. The gallery is purely
// visual, so unphotographed entries don't belong here.
const hasPhoto = (p: any) =>
  typeof p?.Photo === 'string' && p.Photo.trim() !== '' && p.Photo !== 'undefined';

const displayedRecords = ref(records.value.filter(hasPhoto).slice(0, 50));
const carouselRef = ref<HTMLElement | null>(null);

// Recalculate displayedRecords when records updates
watch(records, () => {
  displayedRecords.value = records.value.filter(hasPhoto).slice(0, 50);
});

onMounted(() => {
  const carousel = carouselRef.value;
  if (carousel) {
    // Repeat the images to ensure continuous scrolling effect
    displayedRecords.value = [...displayedRecords.value, ...displayedRecords.value];
  }
});
</script>

<template>
  <div class="carousel overflow-hidden relative w-full py-12" ref="carouselRef">
    <div class="carousel-track">
      <div
          class="carousel-slide inline-block w-36 h-56 bg-cover bg-center rounded-lg mx-2 relative"
          v-for="(prisoner, index) in displayedRecords"
          :key="index"
          :style="{ backgroundImage: `url(${prisoner.Photo})` }"
          role="img"
          :aria-label="'Photo of ' + prisoner.name"
      >
        <div class="meta absolute left-0 right-0 bottom-0 pl-2 pr-2">
          <h2>{{prisoner.name}}</h2>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.carousel {
  .carousel-track {
    display: flex;
    animation: slide 40s linear infinite;

    @keyframes slide {
      from { transform: translateX(0); }
      to { transform: translateX(-50%); }
    }
  }

  .carousel-slide {
    flex: 0 0 auto;
  }
}


.carousel-slide .meta {
  z-index: 10;
  color:#FFF;

  * {
    color:#FFF;
    font-weight: lighter;
    text-align: center;
  }

  h2 {

  }

  h4 {
    font-size: 11px;
  }
}

.carousel-slide:after {
  position: absolute;
  top: 0px;
  right: 0;
  left: 0;
  bottom: 0;
  content: "";
  background: linear-gradient(0deg, #000000c2, transparent);
  border-radius: 8px;
  z-index: 1;
}
</style>
