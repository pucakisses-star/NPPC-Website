import {ref, Ref} from 'vue'
import {PrisonerRecord} from "@/@types/types";
type TDataKeys = 'race' | 'gender' | 'ideology' | 'affiliation'
type TransformedData = Record<string, number | string>
type FormatConfig = { format: any, color: string, icon: string, label: string  }
type TDataValue = Record<string, Record<number, number>>


export function useChart(): {
    initialiseChartData: (records: PrisonerRecord[]) => void
    prepareData: (dataKey: TDataKeys) =>  {series: any, eras: any}
    transformData: (input: Record<string, any[]>) => TransformedData[]
    generateLabels: (series: any[]) => FormatConfig[]
} {


    let data: Record<TDataKeys, TDataValue> = {
        race: {},
        gender: {},
        ideology: {},
        affiliation: {}
    }


    function initialiseChartData(records: PrisonerRecord[]): void {

        data = {
            race: {},
            gender: {},
            ideology: {},
            affiliation: {}
        }

        records.forEach((record: PrisonerRecord) => {
            const yearsInPrison = record['Years Spent In Prison']
            const race = record.Race
            const gender = record.Gender
            const affiliation = record.Affiliation
            const ideology = record.Ideologies ? record.Ideologies[0] : null

            if(!yearsInPrison) return
            const years: Array<number> = []
            yearsInPrison.forEach((year: string) => {
                years.push(parseInt(year))
            })

            if(!years) return false
            setDataValue('race', race, years)
            setDataValue('gender', gender, years)
            setDataValue('affiliation', affiliation, years)
            if(ideology) setDataValue('ideology', ideology, years)
        })
    }


    const setDataValue = (key: TDataKeys, value: string, years: Array<number>) => {
        if(!value) return false
        if(typeof data[key][value] === 'undefined') data[key][value] = {}
        years.forEach((year: number) => {
            if(typeof data[key][value][year] === 'undefined') data[key][value][year] = 0
            data[key][value][year]++
        })
    }


    function transformData(input: Record<string, any[]>): TransformedData[] {
        const { series, eras } = input;
        return eras.map((year: any, index: any) => {
            const dataPoint: TransformedData = { year };
            series.forEach(({ name, data }) => {
                dataPoint[name] = data[index];
            });
            return dataPoint;
        });
    }

    function generateLabels(series: any[]): FormatConfig[] {
        return series.map((item, index) => ({
            format: item.name,
            color: `var(--vis-color${index})`,
            icon: 'bookmark-fill',
            label: item.name,
        }));
    }


    const prepareData = (dataKey: TDataKeys): {series: any, eras: any} => {
        const series: any = [];

        const erasUnsorted: Array<number> = []
        Object.keys(data[dataKey]).forEach((key: string) => {
            Object.keys(data[dataKey][key]).forEach((eraNumber: string) => {
                erasUnsorted.push(parseInt(eraNumber))
            })
        })

        const currentYear = new Date().getFullYear()
        const eras = erasUnsorted
            .filter((value, index, self) =>
                self.indexOf(value) === index && value >= 1994 && value <= currentYear)
            .sort((a, b) => a - b);


        Object.keys(data[dataKey]).forEach((key: string) => {
            const seriesSubData: Array<number> = []
            eras.forEach((era: number) => {
                const count = data[dataKey][key][era] ?? 0
                seriesSubData.push(count)
            })

            series.push({name: key, data: seriesSubData})
        })

        return { series, eras }
    }


    return { initialiseChartData, prepareData, generateLabels, transformData }
}
