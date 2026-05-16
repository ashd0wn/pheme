/* eslint-disable no-undef */

import {GlobalPermission, StationPermission} from "~/acl.ts";

let globalProps: PhemeConstants;

export function setGlobalProps(newGlobalProps: PhemeConstants): void {
    globalProps = newGlobalProps;
}

export interface PhemeStationConstants {
    id: number | null,
    name: string | null,
    isEnabled: boolean | null,
    shortName: string | null,
    timezone: string | null,
    offlineText: string | null,
}

export interface PhemeUserConstants {
    id: number | null,
    displayName: string | null,
    globalPermissions: GlobalPermission[],
    stationPermissions: {
        [key: number]: StationPermission[]
    }
}

export interface PhemeConstants {
    locale: string,
    localeShort: string,
    localeWithDashes: string,
    timeConfig: object,
    apiCsrf: string | null,
    enableAdvancedFeatures: boolean,
    panelProps: object | null,
    sidebarProps: object | null,
    componentProps: object | null,
    user: PhemeUserConstants | null,
    station: PhemeStationConstants | null,
}

export function usePheme(): PhemeConstants {
    return globalProps;
}

export function usePhemeUser(): PhemeUserConstants {
    const {user} = usePheme();

    return (user !== null) ? user : {
        id: null,
        displayName: null,
        globalPermissions: [],
        stationPermissions: {}
    };
}

export function usePhemeStation(): PhemeStationConstants {
    const {station} = usePheme();

    return (station !== null) ? station : {
        id: null,
        name: null,
        isEnabled: null,
        shortName: null,
        timezone: null,
        offlineText: null
    };
}
