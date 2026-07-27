import type { Ref } from 'vue'
import {PrisonerRecord} from "@/@types/types";

/**
 * Lower-case and strip diacritics, so searching "Maria Cueto" matches
 * "María Cueto" (and typing the accents still matches the plain spelling).
 * NFD splits accented characters into base letter + combining mark, which the
 * regex then removes.
 */
const fold = (value?: string | null): string =>
    (value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/\s+/g, ' ');

export function useFilter(): {
    checkPrisonerFilter: (prisoner: PrisonerRecord, buttonFilter: Ref<string>, cleanFilterObject?: Record<any, any>, nameSearch?: Ref<string>) => boolean
} {


    const fieldFiltersRel: Record<string, keyof PrisonerRecord> = {
        ideology: 'Ideologies',
        affiliation: 'Affiliation',
        era: 'Era',
        state: 'State',
        race: 'Race',
        gender: 'Gender'
    }


    const checkFilterValues = (filterValues: string[], prisonerValue: string | string[]): boolean => {
        // Convert everything to lowercase to make the comparison case-insensitive (optional)
        const lowerFilterValues = filterValues.map(value => value.toLowerCase());

        if (typeof prisonerValue === 'string') {
            // If prisonerValue is a string, we check if any filterValue matches prisonerValue
            return lowerFilterValues.includes(prisonerValue.toLowerCase());
        } else if (Array.isArray(prisonerValue)) {
            // If prisonerValue is an array, we check if any filterValue is included in prisonerValue
            const lowerPrisonerValue = prisonerValue.map(value => value.toLowerCase());
            return lowerFilterValues.some(value => lowerPrisonerValue.includes(value));
        }

        return false; // Fallback case, shouldn't be reached
    };

    const checkPrisonerFilter = (prisoner: PrisonerRecord, buttonFilter: Ref<string>, cleanFilterObject?: Record<any, any>, nameSearch?: Ref<string>): boolean => {
        if(buttonFilter.value && !prisoner[buttonFilter.value]) {
            return false
        }

        if(nameSearch && nameSearch.value) {
            // Every word must appear somewhere in the name or AKA, so
            // "Joseph Smith" matches "Joseph William Smith" and word order
            // does not matter.
            const words = fold(nameSearch.value).split(' ').filter(Boolean);
            if (words.length) {
                const haystack = fold(prisoner.name) + ' ' + fold(prisoner.AKA);

                if (!words.every(word => haystack.includes(word))) {
                    return false;
                }
            }
        }

        if(!cleanFilterObject || !cleanFilterObject.value) return true

        const keys = Object.keys(cleanFilterObject.value)
        for (const key of keys) {
            const field = fieldFiltersRel[key]
            // @ts-ignore
            const prisonerValue: string|Array<string> = prisoner[field]
            const filterValues = cleanFilterObject.value[key]

            if(!filterValues || !filterValues.length) continue
            if(!prisonerValue) return false

            const matchesFilter = checkFilterValues(filterValues, prisonerValue)
            if(!matchesFilter) return false
        }

        return true
    }
    return { checkPrisonerFilter }
}
