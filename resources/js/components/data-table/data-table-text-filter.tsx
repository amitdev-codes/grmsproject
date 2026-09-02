import { useEffect, useState } from 'react';
import { Input } from '@/components/ui/input';

interface DataTableTextFilterProps {
  title: string;
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
}

/** A single-column, debounced text filter — for free-text columns like email/name
 *  where a faceted (fixed-option) dropdown doesn't make sense. */
export function DataTableTextFilter({ title, value, onChange, placeholder }: DataTableTextFilterProps) {
  const [local, setLocal] = useState(value);

  // keep in sync if filters get reset externally (e.g. "Reset" button)
  useEffect(() => setLocal(value), [value]);

  useEffect(() => {
    const handle = setTimeout(() => {
      if (local !== value) onChange(local);
    }, 400);
    return () => clearTimeout(handle);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [local]);

  return (
    <Input
      placeholder={placeholder ?? `Filter ${title.toLowerCase()}…`}
      value={local}
      onChange={(e) => setLocal(e.target.value)}
      className="h-8 w-[150px]"
    />
  );
}
