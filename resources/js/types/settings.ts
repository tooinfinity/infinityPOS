export enum SettingType {
    STRING = 'string',
    INTEGER = 'integer',
    BOOLEAN = 'boolean',
    JSON = 'json',
    ARRAY = 'array',
    FLOAT = 'float',
}

export type SettingValue =
    | string
    | number
    | boolean
    | { [key: string]: SettingValue }
    | SettingValue[]
    | null;

export interface Setting {
    id: number;
    key: string;
    value: SettingValue;
    type: SettingType;
    group: string;
    description?: string;
    is_public: boolean;
    created_at: string;
    updated_at: string;
}

export interface SettingsPageProps {
    groupedSettings: Record<string, Record<string, Setting>>;
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
}
