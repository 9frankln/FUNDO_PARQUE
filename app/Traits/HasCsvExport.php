<?php

namespace App\Traits;

/**
 * Provides a reusable CSV export stream download.
 *
 * Usage:
 *   return $this->streamCsv('animales', ['Arete', 'Nombre'], $query->cursor(), fn ($row) => [$row->arete, $row->nombre]);
 */
trait HasCsvExport
{
    /**
     * Stream a CSV download with UTF-8 BOM for Excel compatibility.
     *
     * @param  string  $prefix  File name prefix (e.g. 'animales', 'finanzas')
     * @param  array  $headers  Column header labels
     * @param  iterable  $rows  Rows to export (cursor, collection, array)
     * @param  callable  $mapper  fn($row): array — maps each row to CSV columns
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function streamCsv(string $prefix, array $headers, iterable $rows, callable $mapper)
    {
        return response()->streamDownload(function () use ($headers, $rows, $mapper): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $mapper($row));
            }

            fclose($output);
        }, $prefix.'_'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
