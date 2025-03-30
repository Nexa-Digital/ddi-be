<?php

namespace App\Exports;

use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecapExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{

    use Exportable;

    public function collection()
    {
        // Disini kita panggil fungsi getRecapAll dan kirimkan request yang diperlukan
        $request = request();
        $recap = Schedule::when($request->role_id == 1, function($q) use ($request) {
            $q->whereHas('classRoom.instantion', function ($q) use ($request) {
                $q->where('id', $request->instantion_id);
            });
        })
        ->where('role_id', $request->role_id)
        ->whereDate('date', '>=', $request->start)
        ->whereDate('date', '<=', $request->finish)
        ->get();

        Log::info('sf');

        $grouped = $recap
        ->map(function ($e) {
            $start = Carbon::parse($e->start);
            $end = Carbon::parse($e->finish);
            $duration = $start->diffInHours($end);

            return [
                'user' => $e->user->name,
                'date' => $e->date,
                'duration' => $duration,
                'attendance' => $e->attendance,
            ];
        })
        ->groupBy('user')
        ->map(function ($e, $name) {
            $totalHour = $e->sum(fn($n) => $n['duration']) ?? 0; 
            $paidHour = $e->filter(fn($n) => $n['attendance'])->sum(fn($n) => $n['duration']) ?? 0;
            $absentHour = $e->filter(fn($n) => !$n['attendance'])->sum(fn($n) => $n['duration']) ?? 0;

            return [
                'name' => $name,
                'absent_hour' => round($absentHour, 0),
                'total_hour' => round($totalHour, 0),
                'paid_hour' => round($paidHour, 0),
            ];
        })
        ->values()
        ->all();

        return collect($grouped);
    }

    public function headings(): array
    {
        return [
            'Nama', 'Tidak Hadir', 'Hadir', 'Total Jam', 'Dibayar'
        ];
    }

    public function map($row): array
    {
        return [
            $row['name'], 
            $row['absent_hour'] == 0 ? '0' : $row['absent_hour'], 
            $row['paid_hour'] == 0 ? '0' : $row['paid_hour'],
            $row['total_hour'] == 0 ? '0' : $row['total_hour'], 
            $row['paid_hour'] == 0 ? '0' : $row['paid_hour']
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Menebalkan baris judul
                // $sheet->getStyle('A1:E1')->getFont()->setBold(true);

                $sheet->getStyle('A1:E1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E8B57'], 
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);

                $highestRow = $sheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getStyle("A$row:E$row")->applyFromArray([
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'], // Warna garis hitam
                            ],
                        ],
                    ]);
                }

                $sheet->getStyle('B2:E' . $sheet->getHighestRow())->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
            },
        ];
    }
}