import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { useLanguage } from '@/hooks/use-language';
import { cn } from '@/lib/utils';
import axios from 'axios';
import {
    Check,
    ChevronsUpDown,
    Loader2,
    User,
    UserPlus,
    X,
} from 'lucide-react';
import { useEffect, useState } from 'react';

interface Customer {
    id: number;
    name: string;
    phone?: string | null;
    email?: string | null;
}

interface CustomerSelectorProps {
    selectedCustomer: Customer | null;
    onCustomerChange: (customer: Customer | null) => void;
    required?: boolean;
}

export function CustomerSelector({
    selectedCustomer,
    onCustomerChange,
    required = false,
}: CustomerSelectorProps) {
    const { __ } = useLanguage();
    const [open, setOpen] = useState(false);
    const [customers, setCustomers] = useState<Customer[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');

    useEffect(() => {
        if (open && customers.length === 0) {
            loadCustomers();
        }
    }, [open, customers.length]);

    const loadCustomers = async () => {
        setIsLoading(true);
        try {
            // Load active customers
            const response = await axios.get('/api/clients', {
                params: { per_page: 100, is_active: true },
            });
            setCustomers(response.data.data);
        } catch (error) {
            console.error('Failed to load customers:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const filteredCustomers = customers.filter(
        (customer) =>
            customer.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            customer.phone?.includes(searchQuery) ||
            customer.email?.toLowerCase().includes(searchQuery.toLowerCase()),
    );

    return (
        <div className="flex items-center gap-2">
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        role="combobox"
                        aria-expanded={open}
                        className={cn(
                            'flex-1 justify-between',
                            !selectedCustomer && 'text-muted-foreground',
                        )}
                    >
                        <div className="flex items-center gap-2 overflow-hidden">
                            <User className="h-4 w-4 flex-shrink-0" />
                            <span className="truncate">
                                {selectedCustomer
                                    ? selectedCustomer.name
                                    : __('Select customer')}
                            </span>
                        </div>
                        <ChevronsUpDown className="ml-2 h-4 w-4 flex-shrink-0 opacity-50" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-[400px] p-0" align="start">
                    <Command>
                        <CommandInput
                            placeholder={__('Search customer...')}
                            value={searchQuery}
                            onValueChange={setSearchQuery}
                        />
                        <CommandList>
                            {isLoading ? (
                                <div className="flex items-center justify-center py-6">
                                    <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
                                </div>
                            ) : (
                                <>
                                    <CommandEmpty>
                                        {__('No customer found.')}
                                    </CommandEmpty>
                                    <CommandGroup>
                                        {filteredCustomers.map((customer) => (
                                            <CommandItem
                                                key={customer.id}
                                                value={customer.name}
                                                onSelect={() => {
                                                    onCustomerChange(
                                                        customer.id ===
                                                            selectedCustomer?.id
                                                            ? null
                                                            : customer,
                                                    );
                                                    setOpen(false);
                                                }}
                                            >
                                                <Check
                                                    className={cn(
                                                        'mr-2 h-4 w-4',
                                                        selectedCustomer?.id ===
                                                            customer.id
                                                            ? 'opacity-100'
                                                            : 'opacity-0',
                                                    )}
                                                />
                                                <div className="flex-1">
                                                    <div className="font-medium">
                                                        {customer.name}
                                                    </div>
                                                    {(customer.phone ||
                                                        customer.email) && (
                                                        <div className="text-xs text-muted-foreground">
                                                            {customer.phone ||
                                                                customer.email}
                                                        </div>
                                                    )}
                                                </div>
                                            </CommandItem>
                                        ))}
                                    </CommandGroup>
                                </>
                            )}
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>

            {selectedCustomer && !required && (
                <Button
                    variant="ghost"
                    size="icon"
                    onClick={() => onCustomerChange(null)}
                    className="flex-shrink-0"
                >
                    <X className="h-4 w-4" />
                </Button>
            )}
        </div>
    );
}

// Quick Add Customer Modal
interface QuickAddCustomerModalProps {
    isOpen: boolean;
    onClose: () => void;
    onCustomerAdded: (customer: Customer) => void;
}

export function QuickAddCustomerModal({
    isOpen,
    onClose,
    onCustomerAdded,
}: QuickAddCustomerModalProps) {
    const { __ } = useLanguage();
    const [name, setName] = useState('');
    const [phone, setPhone] = useState('');
    const [email, setEmail] = useState('');
    const [isProcessing, setIsProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const handleSubmit = async () => {
        // Validation
        const newErrors: Record<string, string> = {};
        if (!name.trim()) {
            newErrors.name = __('Customer name is required');
        }

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setIsProcessing(true);

        try {
            const response = await axios.post('/api/clients', {
                name: name.trim(),
                phone: phone.trim() || null,
                email: email.trim() || null,
                is_active: true,
            });

            const newCustomer = response.data.data;
            onCustomerAdded(newCustomer);

            // Reset form
            setName('');
            setPhone('');
            setEmail('');
            setErrors({});
            onClose();
        } catch (error: unknown) {
            console.error('Failed to add customer:', error);
            const apiError = error as {
                response?: { data?: { errors?: Record<string, string[]> } };
            };
            if (apiError.response?.data?.errors) {
                setErrors(apiError.response.data.errors);
            } else {
                alert(
                    error.response?.data?.message ||
                        __('Failed to add customer. Please try again.'),
                );
            }
        } finally {
            setIsProcessing(false);
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-xl font-semibold">
                        <UserPlus className="h-5 w-5" />
                        {__('Quick Add Customer')}
                    </DialogTitle>
                    <DialogDescription>
                        {__(
                            'Add a new customer quickly. You can add more details later.',
                        )}
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4 py-4">
                    {/* Name */}
                    <div className="space-y-2">
                        <label
                            htmlFor="customer-name"
                            className="text-sm font-medium"
                        >
                            {__('Name')}{' '}
                            <span className="text-destructive">*</span>
                        </label>
                        <input
                            id="customer-name"
                            type="text"
                            value={name}
                            onChange={(e) => {
                                setName(e.target.value);
                                setErrors((prev) => ({ ...prev, name: '' }));
                            }}
                            placeholder={__('Customer name')}
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            disabled={isProcessing}
                            autoFocus
                        />
                        {errors.name && (
                            <p className="text-sm text-destructive">
                                {errors.name}
                            </p>
                        )}
                    </div>

                    {/* Phone */}
                    <div className="space-y-2">
                        <label
                            htmlFor="customer-phone"
                            className="text-sm font-medium"
                        >
                            {__('Phone')}{' '}
                            <span className="text-xs text-muted-foreground">
                                ({__('optional')})
                            </span>
                        </label>
                        <input
                            id="customer-phone"
                            type="tel"
                            value={phone}
                            onChange={(e) => {
                                setPhone(e.target.value);
                                setErrors((prev) => ({ ...prev, phone: '' }));
                            }}
                            placeholder={__('Phone number')}
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            disabled={isProcessing}
                        />
                        {errors.phone && (
                            <p className="text-sm text-destructive">
                                {errors.phone}
                            </p>
                        )}
                    </div>

                    {/* Email */}
                    <div className="space-y-2">
                        <label
                            htmlFor="customer-email"
                            className="text-sm font-medium"
                        >
                            {__('Email')}{' '}
                            <span className="text-xs text-muted-foreground">
                                ({__('optional')})
                            </span>
                        </label>
                        <input
                            id="customer-email"
                            type="email"
                            value={email}
                            onChange={(e) => {
                                setEmail(e.target.value);
                                setErrors((prev) => ({ ...prev, email: '' }));
                            }}
                            placeholder={__('Email address')}
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            disabled={isProcessing}
                        />
                        {errors.email && (
                            <p className="text-sm text-destructive">
                                {errors.email}
                            </p>
                        )}
                    </div>
                </div>

                {/* Action Buttons */}
                <div className="flex gap-3">
                    <Button
                        variant="outline"
                        onClick={onClose}
                        disabled={isProcessing}
                        className="flex-1"
                    >
                        {__('Cancel')}
                    </Button>
                    <Button
                        onClick={handleSubmit}
                        disabled={isProcessing}
                        className="flex-1 gap-2"
                    >
                        {isProcessing ? (
                            <>
                                <Loader2 className="h-4 w-4 animate-spin" />
                                {__('Adding...')}
                            </>
                        ) : (
                            <>
                                <UserPlus className="h-4 w-4" />
                                {__('Add Customer')}
                            </>
                        )}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
