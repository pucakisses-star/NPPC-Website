<script lang="ts" setup async>
import {caseCardFields, prisonerCardFields} from "@/@types/prisonerCardFields";
import {PrisonerRecord, Prisoner} from "@/@types/types";
import {defineProps, ref} from 'vue';
import {prop} from "vue-class-component";

interface Props {
  record: PrisonerRecord
}

const props = defineProps<Props>()
const showCases = ref<boolean>(false)


const heading4 = 'border-b border-white border-dashed border-opacity-50 my-3 pb-2 text-lg uppercase font-bold text-left'
const heading5 = 'font-bold mt-4 mb-2 uppercase'
const textValue = 'italic'



const parseValueForOutput = (value: any): any => {

  if (typeof value === 'string' && value.includes('error')) {
    return null;
  }
  // If the value is an object and contains a key 'error', return null
  if (typeof value === 'object' && value.error) {
    return null;
  }

  if (typeof value === 'boolean') {
    return value ? 'Yes' : 'No';
  }

  if (Array.isArray(value)) {
    return value.join(', ');
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    const date = new Date(value);
    const day = date.getDate();
    const suffix = (day >= 11 && day <= 13) ? 'th' : ['th','st','nd','rd'][day % 10] || 'th';
    return `${date.toLocaleString('default', { month: 'short' })} ${day}${suffix} ${date.getFullYear()}`;
  }


  // If it's neither boolean nor array, return the value as is.
  return value;
};

const prisonerLinks: Array<{image: string, key: keyof Prisoner}> = [
  {image: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64H503.9c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 20.9 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>', key: 'Website'},
  {image: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>', key: 'Twitter'},
  {image: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg>', key: 'Facebook'},
]


const mainCase = props.record.cases[0]
</script>

<template>
  <div v-if="record.visible" class="prisoner border-2 border-white mb-12 p-4 md:p-8" :class="`has-maincase-${mainCase ? 'yes' : 'no'}`" :key="record.name">
    <!-- Header Section -->
    <header class="flex justify-between font-bold border-b-2  border-opacity-50 border-white mb-4 pb-2">
      <div class="flex justify-start">
        <h2 class="">
          <a :href="'/prisoner/' + (record.slug || record.id)" class="text-xl md:text-3xl" style="color:#fff; text-decoration:none; border-bottom:1px solid rgba(255,255,255,0.2);">{{ record.name ? (record.AKA ? `${record.AKA} (${record.name})` : record.name) : '' }}</a>
        </h2>
      </div>
      <div class="meta flex justify-end">
        <div v-if="record.inmateNumber" class="inmate text-xl md:text-3xl">#{{ record.inmateNumber }}</div>
        <span v-if="mainCase && !showCases" @click="showCases = true" class="ml-4 cursor-pointer text-sm mt-2">Show More <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M137.4 374.6c12.5 12.5 32.8 12.5 45.3 0l128-128c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8L32 192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l128 128z"/></svg></span>
        <span v-if="mainCase && showCases" @click="showCases = false" class="ml-4 cursor-pointer text-sm mt-2">Hide <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M182.6 137.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-9.2 9.2-11.9 22.9-6.9 34.9s16.6 19.8 29.6 19.8H288c12.9 0 24.6-7.8 29.6-19.8s2.2-25.7-6.9-34.9l-128-128z"/></svg></span>
      </div>
    </header>

    <section class="flex justify-between mb-4">
      <section class="flex justify-start">
        <template v-for="link in prisonerLinks" :key="link.key"><a v-if="record[link.key]" :href="record[link.key]" target="_blank" rel="noopener" class="text-lg" :aria-label="link.key + ' link for ' + record.name"><span class="link-img" v-html="link.image" aria-hidden="true"></span></a></template>
      </section>
      <div>
        <span :class="heading5" v-if="mainCase && mainCase['Incarceration Date']">Incarcerated: {{mainCase["Incarceration Date"]}}</span>
      </div>
    </section>



    <main class="block md:flex">
      <section class="image w-full md:w-1/4">
        <img class="w-full h-auto" :src="record.Photo ? record.Photo : '/images/no-image-available.svg'" :alt="record.Photo ? 'Photo of ' + record.name : 'No image available'" />
        <section class="mt-4 flex justify-start flex-wrap" v-if="record.Ideologies && record.Ideologies.length > 0">
          <template v-for="ideology in record.Ideologies" :key="ideology"><span class="tagg">{{ideology}}</span></template>
        </section>
      </section>

      <section class="info w-full px-4 md:w-3/4">

        <div v-if="record.Description" class="text-left" style="white-space:pre-line;">
          <p>{{ record.Description }}</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 text-left">
          <template v-for="field in prisonerCardFields" :key="field.title">
            <div v-if="record[field.fieldKey] != null && record[field.fieldKey] !== '' && !(Array.isArray(record[field.fieldKey]) && record[field.fieldKey].length === 0)" class="mb-4">
              <div :class="heading5">{{field.title}}</div>
              <div :class="textValue">{{parseValueForOutput(record[field.fieldKey]) ?? ''}}</div>
              <div v-if="field.title === 'Age'">
                <span class="text-sm relative" style="top: -25px;left: 22px;;">{{ record['Death date'] ? 'Deceased' : '' }}</span>
              </div>
            </div>
          </template>
        </div>
      </section>
    </main>

    <div class="currentImprisonment" v-if="record.calculatedPunishment" v-html="record.calculatedPunishment"></div>

    <transition name="slide-fade">
      <section id="cases" class="mt-8" v-if="showCases">
        <template v-for="criminalCase in record.cases">
          <div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 text-left">
              <template v-for="field in caseCardFields">
                <div v-if="criminalCase[field.fieldKey] != null && criminalCase[field.fieldKey] !== '' && !(Array.isArray(criminalCase[field.fieldKey]) && criminalCase[field.fieldKey].length === 0)" :key="field.title" class="mb-4">
                  <div :class="heading5">{{field.title}}</div>
                  <div :class="textValue" v-if="field.asTags">
                    <span class="tagg" v-for="i in criminalCase[field.fieldKey]">{{i}}</span>
                  </div>
                  <div :class="textValue" v-else>{{parseValueForOutput(criminalCase[field.fieldKey]) ?? '-'}}</div>
                </div>
                <div v-else-if="field.separator" class="border-b-2 border-white col-span-full mb-4 mt-4"></div>
              </template>
            </div>
          </div>
        </template>

        <section v-if="mainCase" class="flex justify-between mt-6">
          <div class="text-left" v-if="mainCase['Mailing address']">
            <div :class="heading5">Mailing Address</div>
            <div :class="textValue">{{parseValueForOutput(mainCase['Mailing address']) ?? '-'}}</div>
          </div>
          <div class="text-right" v-if="mainCase['Physical address']">
            <div :class="heading5">Physical Address</div>
            <div :class="textValue">{{parseValueForOutput(mainCase['Physical address']) ?? '-'}}</div>
          </div>
        </section>
      </section>
    </transition>
  </div>
</template>

<style lang="scss">
.tagg {
  background: #95999e;
  text-transform: uppercase;
  color: #FFF;
  border-radius: 4px;
  padding: 0 5px;
  line-height: 18px;
  height: 18px;
  font-size: 12px;
  margin: 0 3px 6px;
}

.meta svg {
  display:inline;
  fill:#FFF;
  height: 15px;
}

.currentImprisonment {
  margin-top: 1rem;
  padding-top: 1.5rem;
  padding-bottom: 1.5rem;
  border-top: 2px solid #FFF;
  border-bottom: 2px solid #FFF;
  text-align: center;
  text-transform: uppercase;
  font-size: 23px;
}

.slide-fade-enter-active, .slide-fade-leave-active {
  transition: opacity .3s, transform .3s;
}
.slide-fade-enter, .slide-fade-leave-to /* .slide-fade-leave-active in <2.1.8 */ {
  opacity: 0;
  transform: translateY(1em);
}


.link-img {

  svg {
    height: 18px;
    margin-right: 4px;
    fill: #FFF;
    width: auto;
    stroke: #FFF;
  }
}
</style>
