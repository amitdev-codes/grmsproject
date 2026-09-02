import { Check, ChevronsUpDown } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { COUNTRIES, isoToFlagEmoji  } from '@/lib/countries';
import type {Country} from '@/lib/countries';
import { cn } from '@/lib/utils';
import { FieldWrapper } from './field-wrapper';

interface PhoneFieldProps {
    id: string;
    label: string;
    /** Combined value, e.g. "+266 58123456". */
    value: string;
    onChange: (value: string) => void;
    error?: string;
    required?: boolean;
    /** ISO2 of the initially-selected country. Defaults to Lesotho ("LS"). */
    defaultCountry?: string;
    placeholder?: string;
    disabled?: boolean;
}

function parseValue(value: string, fallback: Country) {
    const match = COUNTRIES.filter((c) => value.startsWith(c.dialCode)).sort(
        (a, b) => b.dialCode.length - a.dialCode.length,
    )[0];

    if (match) {
        return {
            country: match,
            number: value.slice(match.dialCode.length).trim(),
        };
    }

    return { country: fallback, number: value.trim() };
}

export function PhoneField({
    id,
    label,
    value,
    onChange,
    error,
    required,
    defaultCountry = 'LS',
    placeholder = 'e.g. 5812 3456',
    disabled,
}: PhoneFieldProps) {
    const fallbackCountry =
        COUNTRIES.find((c) => c.iso2 === defaultCountry) ?? COUNTRIES[0];

    // Parsed once on mount so editing an existing record pre-selects the right country.
    const initial = useMemo(
        () => parseValue(value, fallbackCountry),
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [],
    );

    const [country, setCountry] = useState<Country>(initial.country);
    const [number, setNumber] = useState(initial.number);
    const [open, setOpen] = useState(false);

    const emit = (nextCountry: Country, nextNumber: string) => {
        const digits = nextNumber.replace(/\D/g, '');
        onChange(digits ? `${nextCountry.dialCode} ${digits}` : '');
    };

    return (
        <FieldWrapper id={id} label={label} error={error} required={required}>
            <div className="flex gap-2">
                <Popover open={open} onOpenChange={setOpen}>
                    <PopoverTrigger asChild>
                        <Button
                            type="button"
                            variant="outline"
                            role="combobox"
                            aria-expanded={open}
                            disabled={disabled}
                            className="w-26 shrink-0 justify-between px-2"
                        >
                            <span className="flex items-center gap-1.5 truncate">
                                <span>{isoToFlagEmoji(country.iso2)}</span>
                                <span className="text-sm">
                                    {country.dialCode}
                                </span>
                            </span>
                            <ChevronsUpDown className="size-4 shrink-0 opacity-50" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-60 p-0" align="start">
                        {/* Search box lives inside the dropdown itself — select2-style, no separate input outside */}
                        <Command>
                            <CommandInput placeholder="Search country…" />
                            <CommandList>
                                <CommandEmpty>No country found.</CommandEmpty>
                                <CommandGroup>
                                    {COUNTRIES.map((c) => (
                                        <CommandItem
                                            key={c.iso2}
                                            value={`${c.name} ${c.dialCode}`}
                                            onSelect={() => {
                                                setCountry(c);
                                                setOpen(false);
                                                emit(c, number);
                                            }}
                                        >
                                            <Check
                                                className={cn(
                                                    'mr-2 size-4',
                                                    country.iso2 === c.iso2
                                                        ? 'opacity-100'
                                                        : 'opacity-0',
                                                )}
                                            />
                                            <span className="mr-2">
                                                {isoToFlagEmoji(c.iso2)}
                                            </span>
                                            <span className="flex-1 truncate">
                                                {c.name}
                                            </span>
                                            <span className="text-muted-foreground">
                                                {c.dialCode}
                                            </span>
                                        </CommandItem>
                                    ))}
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>

                <div className="flex-1">
                    <Input
                        id={id}
                        type="tel"
                        inputMode="numeric"
                        required={required}
                        value={number}
                        placeholder={placeholder}
                        disabled={disabled}
                        aria-invalid={!!error}
                        onChange={(e) => {
                            // Strip non-digits and hard-cap at 10 as the user types —
                            // "too many" is prevented here; "too few" is caught by
                            // rules.phoneDigits(10) on submit.
                            const digitsOnly = e.target.value
                                .replace(/\D/g, '')
                                .slice(0, 10);
                            setNumber(digitsOnly);
                            emit(country, digitsOnly);
                        }}
                    />
                    <p className="mt-1 text-xs text-muted-foreground">
                        {number.length}/10 digits
                    </p>
                </div>
            </div>
        </FieldWrapper>
    );
}
