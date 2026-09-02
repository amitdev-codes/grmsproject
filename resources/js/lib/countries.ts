export interface Country {
    name: string;
    iso2: string;
    dialCode: string;
}

/** Converts an ISO2 country code (e.g. "LS") into its flag emoji. */
export function isoToFlagEmoji(iso2: string): string {
    return iso2
        .toUpperCase()
        .replace(/./g, (char) =>
            String.fromCodePoint(127397 + char.charCodeAt(0)),
        );
}

/**
 * Curated country list, Lesotho and its Southern Africa neighbours first,
 * then other common countries alphabetically. Extend freely as needed —
 * this isn't meant to be an exhaustive ISO list.
 */
export const COUNTRIES: Country[] = [
    { name: 'Lesotho', iso2: 'LS', dialCode: '+266' },
    { name: 'South Africa', iso2: 'ZA', dialCode: '+27' },
    { name: 'Botswana', iso2: 'BW', dialCode: '+267' },
    { name: 'Namibia', iso2: 'NA', dialCode: '+264' },
    { name: 'Eswatini', iso2: 'SZ', dialCode: '+268' },
    { name: 'Zimbabwe', iso2: 'ZW', dialCode: '+263' },
    { name: 'Mozambique', iso2: 'MZ', dialCode: '+258' },
    { name: 'Zambia', iso2: 'ZM', dialCode: '+260' },
    { name: 'Malawi', iso2: 'MW', dialCode: '+265' },
    { name: 'Nigeria', iso2: 'NG', dialCode: '+234' },
    { name: 'Kenya', iso2: 'KE', dialCode: '+254' },
    { name: 'Ghana', iso2: 'GH', dialCode: '+233' },
    { name: 'Tanzania', iso2: 'TZ', dialCode: '+255' },
    { name: 'Uganda', iso2: 'UG', dialCode: '+256' },
    { name: 'Ethiopia', iso2: 'ET', dialCode: '+251' },
    { name: 'Egypt', iso2: 'EG', dialCode: '+20' },
    { name: 'Morocco', iso2: 'MA', dialCode: '+212' },
    { name: 'Nepal', iso2: 'NP', dialCode: '+977' },
    { name: 'India', iso2: 'IN', dialCode: '+91' },
    { name: 'Pakistan', iso2: 'PK', dialCode: '+92' },
    { name: 'Bangladesh', iso2: 'BD', dialCode: '+880' },
    { name: 'China', iso2: 'CN', dialCode: '+86' },
    { name: 'Japan', iso2: 'JP', dialCode: '+81' },
    { name: 'South Korea', iso2: 'KR', dialCode: '+82' },
    { name: 'Vietnam', iso2: 'VN', dialCode: '+84' },
    { name: 'Philippines', iso2: 'PH', dialCode: '+63' },
    { name: 'Indonesia', iso2: 'ID', dialCode: '+62' },
    { name: 'Malaysia', iso2: 'MY', dialCode: '+60' },
    { name: 'Singapore', iso2: 'SG', dialCode: '+65' },
    { name: 'Thailand', iso2: 'TH', dialCode: '+66' },
    { name: 'UAE', iso2: 'AE', dialCode: '+971' },
    { name: 'Saudi Arabia', iso2: 'SA', dialCode: '+966' },
    { name: 'Turkey', iso2: 'TR', dialCode: '+90' },
    { name: 'United Kingdom', iso2: 'GB', dialCode: '+44' },
    { name: 'Ireland', iso2: 'IE', dialCode: '+353' },
    { name: 'Germany', iso2: 'DE', dialCode: '+49' },
    { name: 'France', iso2: 'FR', dialCode: '+33' },
    { name: 'Netherlands', iso2: 'NL', dialCode: '+31' },
    { name: 'Belgium', iso2: 'BE', dialCode: '+32' },
    { name: 'Switzerland', iso2: 'CH', dialCode: '+41' },
    { name: 'Italy', iso2: 'IT', dialCode: '+39' },
    { name: 'Spain', iso2: 'ES', dialCode: '+34' },
    { name: 'Portugal', iso2: 'PT', dialCode: '+351' },
    { name: 'Sweden', iso2: 'SE', dialCode: '+46' },
    { name: 'Norway', iso2: 'NO', dialCode: '+47' },
    { name: 'Denmark', iso2: 'DK', dialCode: '+45' },
    { name: 'Russia', iso2: 'RU', dialCode: '+7' },
    { name: 'United States', iso2: 'US', dialCode: '+1' },
    { name: 'Canada', iso2: 'CA', dialCode: '+1' },
    { name: 'Mexico', iso2: 'MX', dialCode: '+52' },
    { name: 'Brazil', iso2: 'BR', dialCode: '+55' },
    { name: 'Argentina', iso2: 'AR', dialCode: '+54' },
    { name: 'Australia', iso2: 'AU', dialCode: '+61' },
    { name: 'New Zealand', iso2: 'NZ', dialCode: '+64' },
];
