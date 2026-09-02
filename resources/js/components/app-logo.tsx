import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="brand-gradient flex aspect-square size-9 items-center justify-center rounded-md text-white shadow-sm">
                <AppLogoIcon className="size-5" />
            </div>
            <div className="ml-1 grid flex-1 text-left leading-tight">
                <span className="truncate text-sm font-bold">
                    Grievance Redressal
                </span>
                <span className="truncate text-sm font-bold">
                    Management System
                </span>
            </div>
        </>
    );
}
