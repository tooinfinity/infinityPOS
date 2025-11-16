import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLanguage } from '@/hooks/use-language';

export default function LanguageSwitcher() {
    const { locale, updateLanguage } = useLanguage();

    return (
        <Select defaultValue={locale as string} onValueChange={updateLanguage}>
            <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Select a language" />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    <SelectLabel>Languages</SelectLabel>
                    <SelectItem value="en">English</SelectItem>
                    <SelectItem value="fr">Français</SelectItem>
                    <SelectItem value="ar">العربية</SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}
