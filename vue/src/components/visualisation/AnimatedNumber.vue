<script lang="ts" setup>
import { ref, watch, onMounted } from 'vue';

interface Props {
  value: number;
  duration?: number; // ms
}

const props = withDefaults(defineProps<Props>(), {
  duration: 800,
});

const displayed = ref(0);
let rafId: number | null = null;

const animateTo = (target: number) => {
  if (rafId !== null) cancelAnimationFrame(rafId);
  const start = displayed.value;
  const delta = target - start;
  if (delta === 0) return;
  const startTime = performance.now();
  const tick = (now: number) => {
    const t = Math.min((now - startTime) / props.duration, 1);
    // ease-out cubic — quick at first, slows as it approaches
    const eased = 1 - Math.pow(1 - t, 3);
    displayed.value = Math.round(start + delta * eased);
    if (t < 1) {
      rafId = requestAnimationFrame(tick);
    } else {
      rafId = null;
    }
  };
  rafId = requestAnimationFrame(tick);
};

onMounted(() => animateTo(props.value));

watch(() => props.value, (newVal) => animateTo(newVal));
</script>

<template>{{ displayed.toLocaleString() }}</template>
