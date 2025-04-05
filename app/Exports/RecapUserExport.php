<?php

namespace App\Exports;

use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class RecapUserExport implements FromCollection, WithHeadings, WithEvents, WithDrawings
{
    use Exportable;

    private $images = [];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $request = request();

        $recap = Schedule::when($request->role_id == 1, function($q) use ($request) {
            $q->whereHas('classRoom.instantion', function ($q) use ($request) {
                $q->where('id', $request->instantion_id);
            });
        })
        ->where('role_id', $request->role_id)
        ->where('user_id', $request->userid)
        ->whereDate('date', '>=', $request->start)
        ->whereDate('date', '<=', $request->end)
        ->get();

        $mapped = $recap
        ->map(function ($e, $index) {

            $start = Carbon::parse($e->start);
            $end = Carbon::parse($e->finish);

            $image = '-';

            if($e->attendance){
                $checkin = Carbon::parse($e->attendance->created_at);
                $image = $e->attendance->image;
                if ($e->attendance->image) {
                    $imageData = @file_get_contents($e->attendance->image);
                    if ($imageData) {
                        $this->images[] = [
                            'data' => $imageData,
                            'coordinates' => 'A' . ($index + 2),
                            'row' => $index + 2
                        ];
                    }
                }
            }

            $instantion = '-';
            

            if($e->classRoom?->instantion?->name){
                $instantion = $e->classRoom->instantion->name . ' ' . $e->classRoom->name;
            }
            
            return [
                'image' => $image,
                'date' => $e->date,
                'class' => $instantion,
                'start' => $start->translatedFormat('H:i'),
                'finish' => $end->translatedFormat('H:i'),
                'duration' => $start->diffInHours($end),
                'check_in' => $e->attendance ? $checkin->translatedFormat('H:i') : 'Tidak Masuk',
            ];
        })
        ->values()
        ->all();        

        return collect($mapped);
    }

    public function headings(): array
    {
        return [
            'Gambar', 'Tanggal', 'Kelas', 'Jadwal Masuk', 'Jadwal Keluar', 'Total Jam', 'Waktu Absen'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Menebalkan baris judul
                // $sheet->getStyle('A1:E1')->getFont()->setBold(true);

                $sheet->getStyle('A1:G1')->applyFromArray([
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
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(25);

                // foreach ($this->images as $img) {
                //     $rowIndex = $img['row'];
                //     Log::info($rowIndex);
                //     // Mengatur tinggi baris sama dengan tinggi gambar plus sedikit padding
                //     $sheet->getRowDimension($rowIndex)->setRowHeight(60 + 2);
                // }

                $highestRow = $sheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row++) {

                    $sheet->getRowDimension($row)->setRowHeight(60 + 2);

                    $link = $sheet->getCell("A$row")->getValue();
                    if ($link != '-') {
                        $sheet->getCell("A$row")->setValue('');
                        // $sheet->getCell("A$row")->getHyperlink()->setUrl($link);
                        // $sheet->getCell("A$row")->getHyperlink()->setUrl($link);
                        // $sheet->getCell("A$row")->getHyperlink()->setTooltip('Klik untuk mengakses');
                        // $sheet->getStyle("A$row")->getFont()->setUnderline(true);
                        // $sheet->getStyle("A$row")->getFont()->getColor()->setRGB('0000FF'); // Warna biru
                    }

                    $sheet->getStyle("A$row:G$row")->applyFromArray([
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

                // $sheet->getStyle('B2:E' . $sheet->getHighestRow())->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
            },
        ];
    }

    public function drawings()
    {
        $drawings = [];
        
        foreach ($this->images as $img) {
            $imageResource = imagecreatefromstring($img['data']);
            if (!$imageResource) continue;

            $height = imagesy($imageResource);

            $drawing = new MemoryDrawing();
            $drawing->setName('Gambar Absen');
            $drawing->setDescription('Gambar Absen');
            $drawing->setImageResource($imageResource);
            $drawing->setHeight(300);
            $drawing->setWidth(100);
            $drawing->setRenderingFunction(MemoryDrawing::RENDERING_JPEG);
            $drawing->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
            $drawing->setCoordinates($img['coordinates']);
            $drawing->setOffsetX(35);
            $drawing->setOffsetY(5);
            
            $drawings[] = $drawing;
        }
        
        return $drawings;
    }
}
