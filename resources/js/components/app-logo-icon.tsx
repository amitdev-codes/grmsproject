import type { SVGAttributes } from 'react';

/**
 * GRMS mark — a shield (protection / redressal) with a checkmark
 * (resolution) cut out of it. Renders in currentColor so it inherits
 * the sidebar-primary-foreground color set by AppLogo's icon tile.
 */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 40 40"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
        >
            <path
                d="M20 2.5L34.5 8v10.5c0 9.94-6.1 17.94-14.5 21-8.4-3.06-14.5-11.06-14.5-21V8L20 2.5Z"
                fill="currentColor"
                fillOpacity="0.18"
                stroke="currentColor"
                strokeWidth="2.25"
                strokeLinejoin="round"
            />
            <path
                d="M13.5 20.5L18 25l9-11"
                stroke="currentColor"
                strokeWidth="2.75"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
