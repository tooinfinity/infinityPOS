import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export type Language = 'en' | 'fr' | 'ar';

interface PageProps {
    language: Record<string, string>;
    locale: Language;
}

export function useLanguage() {
    const { locale: pageLocale, language } = usePage<{ props: PageProps }>()
        .props;
    const [locale, setLocale] = useState<Language>(pageLocale as Language);

    const updateLanguage = (newLocale: Language) => {
        if (newLocale === locale) return;

        setLocale(newLocale);

        router.post(
            '/locale',
            { language: newLocale },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const __ = (key: string, replace: Record<string, string | number> = {}) => {
        const translations = language as Record<string, string>;
        let translation = translations[key] || key;

        Object.entries(replace).forEach(([key, value]) => {
            translation = translation.replace(`:${key}`, String(value));
        });

        return translation;
    };

    return { locale, updateLanguage, __ };
}
