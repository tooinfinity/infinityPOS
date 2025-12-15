import SettingController from '@/actions/App/Http/Controllers/SettingController';
import SettingCard from '@/components/settings/setting-card';
import AppLayout from '@/layouts/app-layout';
import { Setting, SettingsPageProps, SettingValue } from '@/types/settings';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ groupedSettings }: SettingsPageProps) {
    const [activeTab, setActiveTab] = useState(
        Object.keys(groupedSettings)[0] || 'general',
    );

    const handleUpdate = (key: string, value: SettingValue) => {
        router.put(
            SettingController.update.url(),
            {
                settings: [{ key, value }],
            },
            {
                preserveScroll: true,
            },
        );
    };

    const groups = Object.keys(groupedSettings);

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Settings', href: SettingController.index().url },
            ]}
        >
            <Head title="Settings" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div role="tablist" className="tabs tabs-boxed">
                            {groups.map((group) => (
                                <a
                                    key={group}
                                    role="tab"
                                    className={`tab ${activeTab === group ? 'tab-active' : ''}`}
                                    onClick={() => setActiveTab(group)}
                                >
                                    {group.charAt(0).toUpperCase() +
                                        group.slice(1)}
                                </a>
                            ))}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {groupedSettings[activeTab] &&
                            Object.values(groupedSettings[activeTab]).map(
                                (setting: Setting) => (
                                    <SettingCard
                                        key={setting.key}
                                        setting={setting}
                                        onUpdate={handleUpdate}
                                    />
                                ),
                            )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
