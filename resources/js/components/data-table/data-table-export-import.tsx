import { useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import { Download, FileSpreadsheet, FileText, Printer, Upload } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import type { DataTableRoutes, DataTableQueryState } from '@/types/data-table';

interface ExportImportProps {
  routes: DataTableRoutes;
  currentQuery: Partial<DataTableQueryState>;
}

function buildExportUrl(exportRoute: string, type: string, query: Partial<DataTableQueryState>) {
  const params = new URLSearchParams();
  params.set('type', type);
  if (query.search) params.set('search', query.search);
  if (query.sort) params.set('sort', query.sort);
  if (query.order) params.set('order', query.order);
  if (query.filters) {
    Object.entries(query.filters).forEach(([key, values]) => {
      values.forEach((v) => params.append(`filters[${key}][]`, v));
    });
  }
  return `${route(exportRoute)}?${params.toString()}`;
}

export function DataTableExportImport({ routes, currentQuery }: ExportImportProps) {
  const [importOpen, setImportOpen] = useState(false);
  const [file, setFile] = useState<File | null>(null);
  const [importing, setImporting] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleExport = (type: 'xlsx' | 'csv' | 'pdf' | 'print') => {
    if (!routes.export) return;
    window.open(buildExportUrl(routes.export, type, currentQuery), '_blank');
  };

  const handleImport = () => {
    if (!file || !routes.import) return;
    setImporting(true);
    const formData = new FormData();
    formData.append('file', file);
    router.post(route(routes.import), formData, {
      forceFormData: true,
      onSuccess: () => {
        setImportOpen(false);
        setFile(null);
      },
      onFinish: () => setImporting(false),
    });
  };

  return (
    <div className="flex items-center gap-2">
      {routes.export && (
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="outline" size="sm" className="h-8">
              <Download className="mr-2 h-4 w-4" />
              Export
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuItem onClick={() => handleExport('xlsx')}>
              <FileSpreadsheet className="mr-2 h-4 w-4" /> Excel (.xlsx)
            </DropdownMenuItem>
            <DropdownMenuItem onClick={() => handleExport('csv')}>
              <FileText className="mr-2 h-4 w-4" /> CSV
            </DropdownMenuItem>
            <DropdownMenuItem onClick={() => handleExport('pdf')}>
              <FileText className="mr-2 h-4 w-4" /> PDF
            </DropdownMenuItem>
            <DropdownMenuItem onClick={() => handleExport('print')}>
              <Printer className="mr-2 h-4 w-4" /> Print
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      )}

      {routes.import && (
        <>
          <Button variant="outline" size="sm" className="h-8" onClick={() => setImportOpen(true)}>
            <Upload className="mr-2 h-4 w-4" />
            Import
          </Button>

          <Dialog open={importOpen} onOpenChange={setImportOpen}>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Import records</DialogTitle>
                <DialogDescription>Upload an .xlsx or .csv file matching the expected columns.</DialogDescription>
              </DialogHeader>
              <Input
                ref={fileInputRef}
                type="file"
                accept=".xlsx,.csv,.txt"
                onChange={(e) => setFile(e.target.files?.[0] ?? null)}
              />
              <DialogFooter>
                <Button variant="outline" onClick={() => setImportOpen(false)} disabled={importing}>
                  Cancel
                </Button>
                <Button onClick={handleImport} disabled={!file || importing}>
                  {importing ? 'Importing…' : 'Import'}
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </>
      )}
    </div>
  );
}
