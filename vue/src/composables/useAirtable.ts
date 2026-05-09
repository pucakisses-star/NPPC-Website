import { Ref, ref, watchEffect } from 'vue';
import axios from 'axios';
import { Case, CaseFields, Institution, Prisoner, PrisonerFilters, PrisonerRecord } from "@/@types/types";

export default function useAirtable() {
    const filterFieldsObj: PrisonerFilters = {
        ideology: [],
        era: [],
        affiliation: [],
        state: [],
        race: [],
        gender: []
    }

    // Aggregates of all records, aggregated
    const aggregateCounts: Record<string, Record<string, number>> = {
        Era: {},
        Race: {},
        Gender: {},
    }

    const sumCounts: Record<string, number> = {
        individualsNotReleased: 0,
        individualsInExile: 0,
        individualsImprisonedOrExiled: 0,
        individualsImprisoned: 0,
        accumulatedDaysImprisoned: 0,
        accumulatedDaysInExile: 0,
    }

    const records: Ref<PrisonerRecord[]> = ref([]);
    const filterFields: Ref<PrisonerFilters> = ref(filterFieldsObj)

    const sortAggregateCounts = () => {
        Object.keys(aggregateCounts).forEach(key => {
            aggregateCounts[key] = Object.entries(aggregateCounts[key])
                .sort((a, b) => b[1] - a[1])
                .reduce((acc, [k, v]) => ({ ...acc, [k]: v }), {});
        });
    };



    // Function to update filter arrays, remove duplicates, and sort
    const updateFilterArray = (array: string[], value: string | string[]) => {
        if (Array.isArray(value)) {
            array.push(...value);
        } else {
            array.push(value);
        }
        // Remove duplicates
        const uniqueArray = Array.from(new Set(array));
        // Sort alphabetically
        return uniqueArray.sort();
    };

    // Era values are decade strings like "1700s" through "2020s". Sort
    // them newest-first (2020s → 1700s) so the filter dropdown reads in
    // reverse chronological order. Strings without a 4-digit year fall
    // through to the bottom in their natural alphabetical order.
    const sortErasReverseChronological = (eras: string[]): string[] => {
        const yearOf = (s: string): number => {
            const m = s.match(/\d{4}/);
            return m ? parseInt(m[0], 10) : -1;
        };
        return [...eras].sort((a, b) => {
            const ya = yearOf(a), yb = yearOf(b);
            if (ya !== yb) return yb - ya;        // newer year first
            return a.localeCompare(b);            // ties: alphabetical
        });
    };

    const processAggregate = (key: keyof Prisoner, prisoner: Prisoner): void => {
        const value = prisoner[key]
        if(!value) return
        if(!aggregateCounts[key]) aggregateCounts[key] = {}

        if(Array.isArray(value)) {
            value.forEach((val) => {
                if(!aggregateCounts[key][val]) aggregateCounts[key][val] = 0
                aggregateCounts[key][val]++
            })
        } else {
            if(!aggregateCounts[key][value]) aggregateCounts[key][value] = 0
            aggregateCounts[key][value]++
        }
    }

    const fetchRecords = async () => {
        let prisoners: Prisoner[];
        try {
            const req = await axios.get(`/api/prisoners`)
            prisoners = req.data
        } catch (error) {
            console.error('Failed to fetch prisoner data:', error)
            return
        }

        if (!prisoners.length) {
            return
        }

        prisoners.forEach((prisoner: Prisoner) => {
            filterFieldsObj.ideology = updateFilterArray(filterFieldsObj.ideology, prisoner.Ideologies ?? []);
            filterFieldsObj.affiliation = updateFilterArray(filterFieldsObj.affiliation, prisoner.Affiliation ?? []);
            filterFieldsObj.era = sortErasReverseChronological(updateFilterArray(filterFieldsObj.era, prisoner.Era ?? ''));
            filterFieldsObj.state = updateFilterArray(filterFieldsObj.state, prisoner.State ?? '');
            filterFieldsObj.race = updateFilterArray(filterFieldsObj.race, prisoner.Race ?? '');
            filterFieldsObj.gender = updateFilterArray(filterFieldsObj.gender, prisoner.Gender ?? '');


            // Aggregates
            processAggregate('Gender', prisoner)
            processAggregate('Race', prisoner)
            processAggregate('Era', prisoner)

            // Sums
            if(prisoner['In Exile']) sumCounts.individualsInExile++
            if(prisoner['In Custody']) sumCounts.individualsImprisoned++
            if(prisoner['Imprisoned or Exiled'] === 'T') sumCounts.individualsImprisonedOrExiled++
            if(prisoner.imprisonedFor) sumCounts.accumulatedDaysImprisoned += prisoner.imprisonedFor
            if(prisoner.inExileFor) sumCounts.accumulatedDaysInExile += prisoner.inExileFor
            if(!prisoner.Released) sumCounts.individualsNotReleased++


            // Parse prisoner
            const prisonerId: string = prisoner.id
            const prisonerRecord: PrisonerRecord = {
                ...prisoner,
                imprisonedOrExiled: prisoner['Imprisoned or Exiled'] === 'T',
                visible: true
            }

            const mainCase = prisoner.cases[0]
            if (mainCase) {
                prisonerRecord.released = prisoner["Released"]
                prisonerRecord.awaitingTrial = prisoner["Awaiting Trial"]
                prisonerRecord.inCustody = prisoner["In Custody"]
                prisonerRecord.inExile = prisoner['In Exile']
            }


            records.value.push(prisonerRecord)
        })

        sortAggregateCounts()
    }


    return { records, fetchRecords, filterFieldsObj, aggregateCounts, sumCounts };
}
