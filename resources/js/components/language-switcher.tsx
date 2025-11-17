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
    const { locale, updateLanguage } = useLanguage();

    return (
        <Select
            value={locale}
            onValueChange={(value) => updateLanguage(value as Language)}
        >
            <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Select a language" />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    <SelectLabel>Languages</SelectLabel>
                    <SelectItem value="en">
                        <span>English</span>
                    </SelectItem>
                    <SelectItem value="fr">
                        <span>Français</span>
                    </SelectItem>
                    <SelectItem value="ar">
                        <span>العربية</span>
                    </SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}
