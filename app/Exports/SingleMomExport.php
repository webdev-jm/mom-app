<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Illuminate\Support\Collection;

use App\Models\MomDetail;

class SingleMomExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithChunkReading, WithStrictNullComparison
{
    public function __construct(public int $mom_id) {}

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function backgroundColor()
    {
        return null;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'Minutes of Meeting App',
            'lastModifiedBy' => 'MoM App',
            'title'          => 'MoM Export',
            'description'    => 'Minutes of meeting Topics List',
            'subject'        => 'Minutes of Meeting Topics',
            'keywords'       => 'MoM topics,export,spreadsheet',
            'category'       => 'Topics',
            'manager'        => 'MoM Application',
            'company'        => 'BEVI',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 15],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'E7FDEC'],
                ],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'ddfffd'],
                ],
            ],
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $header = [
            'MOM NUMBER',
            'MEETING DATE',
            'AGENDA',
            'REMARKS',
            'TOPIC',
            'NEXT STEP',
            'TARGET DATE',
            'RESPONSIBLE',
            'ACTION TAKEN',
            'REMARKS',
            'STATUS',
            'DAYS COMPLETED',
        ];

        $data = [];

        $details = MomDetail::with(['responsibles', 'actions', 'mom'])
            ->where('mom_id', $this->mom_id)
            ->get();

        foreach ($details as $item) {
            $data[] = [
                $item->mom->mom_number,
                $item->mom->meeting_date,
                $item->mom->agenda,
                $item->mom->remarks,
                $item->topic,
                $item->next_step,
                $item->target_date,
                $item->responsibles->first()->name ?? '-',
                $item->actions->first()->action_taken ?? '-',
                $item->actions->first()->remarks ?? '-',
                $item->status,
                $item->completed_date,
            ];
        }

        return new Collection([
            ['MoM - Minutes of Meeting App'],
            ['TOPICS LIST'],
            $header,
            $data,
        ]);
    }
}
