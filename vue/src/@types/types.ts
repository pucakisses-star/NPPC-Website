
export interface PrisonerRecord extends Prisoner {
    visible: boolean
    cases: CaseFields[]
}

export interface PrisonerFilters {
    ideology: Array<string>
    affiliation: Array<string>
    state: Array<string>
    race: Array<string>
    era: Array<string>
    gender: Array<string>
}

export interface Prisoner {
    id: string;
    Twitter?: any
    Website?: any
    Facebook?: any
    Instagram?: any
    Affiliation: any
    latitude: number
    longitude: number
    'Years Spent In Prison': Array<string>
    Year?: number
    imprisonedFor: number
    calculatedPunishment: string
    inExileFor: number
    Era: any
    State: any
    Race?: any
    Gender?: any
    released?: any
    awaitingTrial?: any
    inCustody?: any
    inExile?: any
    "Age"?: any
    "Birthdate"?: any
    "Imprisoned or Exiled"?: any
    imprisonedOrExiled?: any
    "Released"?: any
    "Awaiting Trial"?: any
    "In Custody"?: any
    "In Exile"?: any
    AKA?: string;
    inmateNumber?: string;
    Ideologies: string[];
    Description: string;
    Photo: string;
    cases: CaseFields[];
    name: string;
}



export interface Case {
    id: string;
    createdTime: string;
    fields: CaseFields;
}
export interface CaseFields {
    Indicted: string;
    Convicted: string;
    "Sentenced Date": string;
    "Release Date": string;
    Charges: string[];
    Prosecutor: string;
    Judge: string;
    Plead: string;
    Sentence: string;
    "Institution name": string[];
    "Institution city": string[];
    "Institution state": string;
    "Institution security": string[];
    "Arrest Date"?: string | null;
    "Incarceration Date": string;
    "Mailing address"?: string;
    "Physical address"?: string;
}



export interface Institution {
    id: string;
    createdTime: string;
}

