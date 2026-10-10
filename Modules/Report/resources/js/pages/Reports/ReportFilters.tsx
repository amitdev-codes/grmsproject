import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';

export interface ReportFilterOptions {
    districts: { id: number; name: string }[];
    divisions: { id: number; name: string }[];
    categories: { id: number; name: string }[];
    statuses: { value: string; label: string }[];
}

export interface ReportFilterValues {
    district_id: string;
    division_id: string;
    category_id: string;
    status: string;
    group_by?: string;
}

export function ReportFilters({
    options,
    filters,
    groupingOptions,
}: {
    options: ReportFilterOptions;
    filters: ReportFilterValues;
    groupingOptions?: { value: string; label: string }[];
}) {
    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        const params = Object.fromEntries(
            [...formData.entries()].filter(([, value]) => value !== ''),
        );

        router.get(window.location.pathname, params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <form
            onSubmit={submit}
            className="grid gap-3 rounded-lg border bg-card p-4 sm:grid-cols-2 lg:grid-cols-5"
        >
            <label className="space-y-1 text-sm">
                <span className="font-medium">District</span>
                <select
                    name="district_id"
                    defaultValue={filters.district_id}
                    className="h-10 w-full rounded-md border bg-background px-3"
                >
                    <option value="">All districts</option>
                    {options.districts.map((district) => (
                        <option key={district.id} value={district.id}>
                            {district.name}
                        </option>
                    ))}
                </select>
            </label>
            <label className="space-y-1 text-sm">
                <span className="font-medium">Division</span>
                <select
                    name="division_id"
                    defaultValue={filters.division_id}
                    className="h-10 w-full rounded-md border bg-background px-3"
                >
                    <option value="">All divisions</option>
                    {options.divisions.map((division) => (
                        <option key={division.id} value={division.id}>
                            {division.name}
                        </option>
                    ))}
                </select>
            </label>
            <label className="space-y-1 text-sm">
                <span className="font-medium">Category type</span>
                <select
                    name="category_id"
                    defaultValue={filters.category_id}
                    className="h-10 w-full rounded-md border bg-background px-3"
                >
                    <option value="">All categories</option>
                    {options.categories.map((category) => (
                        <option key={category.id} value={category.id}>
                            {category.name}
                        </option>
                    ))}
                </select>
            </label>
            <label className="space-y-1 text-sm">
                <span className="font-medium">Status</span>
                <select
                    name="status"
                    defaultValue={filters.status}
                    className="h-10 w-full rounded-md border bg-background px-3"
                >
                    <option value="">All statuses</option>
                    {options.statuses.map((status) => (
                        <option key={status.value} value={status.value}>
                            {status.label}
                        </option>
                    ))}
                </select>
            </label>
            {groupingOptions && (
                <label className="space-y-1 text-sm">
                    <span className="font-medium">Group by</span>
                    <select
                        name="group_by"
                        defaultValue={filters.group_by ?? 'year'}
                        className="h-10 w-full rounded-md border bg-background px-3"
                    >
                        {groupingOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </label>
            )}
            <div className="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
                <Button type="submit">Apply filters</Button>
                <Button
                    type="button"
                    variant="outline"
                    onClick={() =>
                        router.get(
                            window.location.pathname,
                            {},
                            {
                                preserveScroll: true,
                                replace: true,
                            },
                        )
                    }
                >
                    Clear
                </Button>
            </div>
        </form>
    );
}
