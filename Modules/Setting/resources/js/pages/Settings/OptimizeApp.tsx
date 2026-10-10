import IndexLayout from '@/components/index-layout';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/react';
import { AlertTriangle, CheckCircle2, Play, Settings2 } from 'lucide-react';
import { useState } from 'react';

interface CommandInfo {
    label: string;
    description: string;
}

interface Result {
    command: string;
    output: string;
    successful: boolean;
}

interface Props {
    commands: Record<string, CommandInfo>;
    result: Result | null;
}

export default function OptimizeApp({ commands, result }: Props) {
    const [processing, setProcessing] = useState(false);

    const run = (command: string) => {
        router.post(
            route('settings.optimize.run'),
            { command },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <IndexLayout
            title="Optimize Application"
            breadcrumbs={[
                { label: 'Settings', icon: Settings2 },
                { label: 'Optimize Application' },
            ]}
        >
            <div className="space-y-6">
                <Alert>
                    <AlertTriangle className="size-4" />
                    <AlertTitle>Run commands carefully</AlertTitle>
                    <AlertDescription>
                        These fixed Artisan commands run on the application
                        server. Avoid changing route or configuration caches
                        while deployments or configuration edits are in
                        progress.
                    </AlertDescription>
                </Alert>

                {result && (
                    <Alert
                        variant={result.successful ? 'default' : 'destructive'}
                    >
                        {result.successful ? (
                            <CheckCircle2 className="size-4" />
                        ) : (
                            <AlertTriangle className="size-4" />
                        )}
                        <AlertTitle>
                            {result.command}{' '}
                            {result.successful ? 'completed' : 'failed'}
                        </AlertTitle>
                        <AlertDescription>
                            <pre className="mt-2 max-h-72 overflow-auto text-xs whitespace-pre-wrap">
                                {result.output ||
                                    'Command finished with no output.'}
                            </pre>
                        </AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-4 md:grid-cols-2">
                    {Object.entries(commands).map(([command, info]) => (
                        <Card key={command}>
                            <CardHeader>
                                <CardTitle>{info.label}</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <p className="text-sm text-muted-foreground">
                                    {info.description}
                                </p>
                                <code className="block rounded bg-muted p-2 text-xs">
                                    php artisan {command}
                                </code>
                                <Button
                                    type="button"
                                    onClick={() => run(command)}
                                    disabled={processing}
                                >
                                    <Play className="mr-2 size-4" />
                                    Run command
                                </Button>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </IndexLayout>
    );
}
