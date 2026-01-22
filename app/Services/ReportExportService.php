<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class ReportExportService
{
    /**
     * Generate PDF Report.
     */
    public function generatePdf(array $data, string $start, string $end)
    {
        $pdf = Pdf::loadView('reports.pdf', array_merge($data, [
            'start' => $start,
            'end' => $end,
            'userName' => auth()->user()->name
        ]));
        
        return $pdf->download("Qirata_Report_{$start}_{$end}.pdf");
    }

    /**
     * Generate Excel Report using PhpSpreadsheet.
     */
    public function generateExcel(array $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $sheet->setTitle(__('Financial Report'));

        // Header Styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
        ];

        // Summary Section
        $sheet->setCellValue('A1', __('Report Summary'));
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        $sheet->setCellValue('A2', __('Total Income'));
        $sheet->setCellValue('B2', $data['totalIncome']);
        $sheet->setCellValue('A3', __('Total Expense'));
        $sheet->setCellValue('B3', $data['totalExpense']);
        $sheet->setCellValue('A4', __('Savings Rate'));
        $sheet->setCellValue('B4', $data['savingsRate'] . '%');

        // Transactions Table
        $sheet->setCellValue('A6', __('Recent Transactions'));
        $sheet->mergeCells('A6:D6');
        $sheet->getStyle('A6:D6')->applyFromArray($headerStyle);

        $sheet->setCellValue('A7', __('Date'));
        $sheet->setCellValue('B7', __('Type'));
        $sheet->setCellValue('C7', __('Category'));
        $sheet->setCellValue('D7', __('Amount'));

        $row = 8;
        foreach ($data['transactions'] as $tx) {
            $sheet->setCellValue("A{$row}", $tx['occurred_at']);
            $sheet->setCellValue("B{$row}", __($tx['type']));
            $sheet->setCellValue("C{$row}", $tx['category']);
            $sheet->setCellValue("D{$row}", (float) $tx['amount']);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, 'Financial_Report.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
