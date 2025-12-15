import { Setting, SettingType, SettingValue } from '@/types/settings';
import { useState } from 'react';

interface Props {
    setting: Setting;
    onUpdate: (key: string, value: SettingValue) => void;
}

export default function SettingCard({ setting, onUpdate }: Props) {
    const [isEditing, setIsEditing] = useState(false);
    const [value, setValue] = useState<SettingValue>(setting.value);

    const handleSave = () => {
        onUpdate(setting.key, value);
        setIsEditing(false);
    };

    const handleCancel = () => {
        setValue(setting.value);
        setIsEditing(false);
    };

    const renderInput = () => {
        switch (setting.type) {
            case SettingType.BOOLEAN:
                return (
                    <input
                        type="checkbox"
                        className="toggle toggle-primary"
                        checked={Boolean(value)}
                        onChange={(e) => setValue(e.target.checked)}
                    />
                );
            case SettingType.INTEGER:
            case SettingType.FLOAT:
                return (
                    <input
                        type="number"
                        className="input input-bordered w-full"
                        value={value as number}
                        onChange={(e) => setValue(e.target.value)}
                    />
                );
            case SettingType.JSON:
            case SettingType.ARRAY:
                return (
                    <textarea
                        className="textarea textarea-bordered w-full font-mono"
                        value={
                            typeof value === 'string'
                                ? value
                                : JSON.stringify(value, null, 2)
                        }
                        onChange={(e) => setValue(e.target.value)}
                    />
                );
            default:
                return (
                    <input
                        type="text"
                        className="input input-bordered w-full"
                        value={value as string}
                        onChange={(e) => setValue(e.target.value)}
                    />
                );
        }
    };

    return (
        <div className="card bg-base-100 border-base-200 border shadow-sm">
            <div className="card-body p-4">
                <div className="flex items-start justify-between">
                    <div>
                        <h3 className="card-title text-base">{setting.key}</h3>
                        <p className="text-base-content/70 text-xs">
                            {setting.description || setting.group}
                        </p>
                        <div className="badge badge-ghost badge-sm mt-1">
                            {setting.type}
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {isEditing ? (
                            <>
                                <button
                                    onClick={handleSave}
                                    className="btn btn-xs btn-primary"
                                >
                                    Save
                                </button>
                                <button
                                    onClick={handleCancel}
                                    className="btn btn-xs btn-ghost"
                                >
                                    Cancel
                                </button>
                            </>
                        ) : (
                            <button
                                onClick={() => setIsEditing(true)}
                                className="btn btn-xs btn-ghost"
                            >
                                Edit
                            </button>
                        )}
                    </div>
                </div>
                <div className="mt-4">
                    {isEditing ? (
                        renderInput()
                    ) : (
                        <div className="font-medium">
                            {setting.type === SettingType.BOOLEAN ? (
                                <input
                                    type="checkbox"
                                    className="toggle toggle-sm"
                                    checked={Boolean(setting.value)}
                                    disabled
                                />
                            ) : (
                                <span className="break-all">
                                    {typeof setting.value === 'object'
                                        ? JSON.stringify(setting.value)
                                        : String(setting.value)}
                                </span>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
