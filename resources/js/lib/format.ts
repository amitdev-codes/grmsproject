export const formatDate = (date: string | Date | null | undefined): string => {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleDateString();
};
