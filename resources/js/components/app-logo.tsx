export default function AppLogo() {
    return (
        <>
            <div className="flex items-center gap-2">
                <img
                    src="/logo.png"
                    alt="Roads Directorate of Lesotho"
                    className="size-10 shrink-0 rounded-md object-contain"
                    loading="eager"
                />
                <div className="grid flex-1 text-left leading-tight">
                    <span className="truncate text-sm font-bold">
                        Grievance Redressal
                    </span>
                    <span className="truncate text-sm font-bold">
                        Management System
                    </span>
                </div>
            </div>
        </>
    );
}