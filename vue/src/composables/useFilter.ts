import type { Ref } from 'vue'
import {PrisonerRecord} from "@/@types/types";

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
            const nameSearchLower = nameSearch.value.toLowerCase();
            const prisonerNameLower = prisoner.name.toLowerCase();
            const prisonerAKALower = prisoner.AKA?.toLowerCase();

            if (!prisonerNameLower.includes(nameSearchLower) && !prisonerAKALower?.includes(nameSearchLower)) {
                return false;
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
