import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLanguage, type Language } from '@/hooks/use-language';

export default function LanguageSwitcher() {
    const { __, locale, updateLanguage } = useLanguage();

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
                    <SelectItem value="en">
                        <span>{__('English')}</span>
                    </SelectItem>
                    <SelectItem value="fr">
                        <span>{__('Français')}</span>
                    </SelectItem>
                    <SelectItem value="ar">
                        <span>{__('العربية')}</span>
                    </SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}
