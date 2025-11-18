import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLanguage, validLocales, type Language } from '@/hooks/use-language';

export default function LanguageSwitcher() {
    const { __, locale, updateLanguage } = useLanguage();
    const localeLabels: Record<Language, string> = {
        en: __('English'),
        fr: __('Français'),
        ar: __('العربية'),
    };

    return (
        <Select
            value={locale}
            onValueChange={(value) => updateLanguage(value as Language)}
        >
            <SelectTrigger className="w-[180px]">
                <SelectValue placeholder={__('Select a language')} />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    <SelectLabel>{__('Languages')}</SelectLabel>
                    {validLocales.map((locale) => (
                        <SelectItem key={locale} value={locale}>
                            <span>{localeLabels[locale]}</span>
                        </SelectItem>
                    ))}
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}
