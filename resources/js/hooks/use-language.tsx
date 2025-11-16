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
    const validLocales: Language[] = ['en', 'fr', 'ar'];
    const initialLocale = validLocales.includes(pageLocale as Language)
        ? (pageLocale as Language)
        : 'en';
    const [locale, setLocale] = useState<Language>(initialLocale);

    const updateLanguage = (newLocale: Language) => {
        if (newLocale === locale) return;

        setLocale(newLocale);

        router.post(
            '/locale',
            { locale: newLocale },
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
