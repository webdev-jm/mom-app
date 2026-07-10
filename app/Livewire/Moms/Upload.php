<?php

namespace App\Livewire\Moms;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Helpers\MomNumberHelper;
use App\Models\User;
use App\Models\Mom;
use App\Models\MomType;
use App\Models\MomDetail;

class Upload extends Component
{
    use WithFileUploads;

    public $upload_file;
    public $mom_data = [];

    protected $user_lookup;
    protected $type_lookup;
    protected $last_code = '';

    public function render()
    {
        return view('livewire.moms.upload');
    }

    public function checkData(): void
    {
        $this->validate([
            'upload_file' => [
                'required',
                'file',
                'mimes:csv,xlsx,txt'
            ]
        ]);

        $path = $this->upload_file->store('mom-uploads', 'local');
        $fullPath = Storage::disk('local')->path($path);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $this->user_lookup = User::query()->get(['id', 'name'])
            ->keyBy(fn(User $user) => mb_strtoupper(trim($user->name)));
        $this->type_lookup = MomType::query()->get()
            ->keyBy(fn(MomType $type) => mb_strtoupper(trim($type->type)));

        $this->mom_data = [];
        $this->last_code = '';

        SimpleExcelReader::create($fullPath, $extension === 'txt' ? 'csv' : '')
            ->getRows()
            ->each(function(array $row) {
                $this->processRow($row);
            });

        if (empty($this->mom_data)) {
            $this->addError('upload_file', __('No valid rows were found in the uploaded file.'));
        }
    }

    private function processRow(array $row): void
    {
        $code = trim((string) ($row['CODE'] ?? ''));
        $topic = trim((string) ($row['TOPIC'] ?? ''));

        if ($code === '' && $topic === '') {
            return;
        }

        if ($code === '') {
            // Blank CODE (e.g. merged cells): the row belongs to the previous MOM
            $code = $this->last_code;
            if ($code === '') {
                return;
            }
        } else {
            $this->last_code = $code;
        }

        $type_name = trim((string) ($row['TYPE'] ?? ''));
        $type = $type_name !== '' ? ($this->type_lookup[mb_strtoupper($type_name)] ?? null) : null;

        $header = $this->mom_data[$code]['header'] ?? [
            'meeting_date' => null,
            'type' => null,
            'type_id' => null,
            'agenda' => null,
        ];

        $header['meeting_date'] = $header['meeting_date'] ?: $this->formatDate($row['DATE OF MEETING'] ?? null);
        $header['type'] = $header['type'] ?: ($type->type ?? ($type_name !== '' ? $type_name : null));
        $header['type_id'] = $header['type_id'] ?: ($type->id ?? null);
        $header['agenda'] = $header['agenda'] ?: (trim((string) ($row['AGENDA'] ?? '')) ?: null);

        $this->mom_data[$code]['header'] = $header;
        $this->mom_data[$code]['topics'] = $this->mom_data[$code]['topics'] ?? [];

        if ($topic === '') {
            return;
        }

        $responsible_name = trim((string) ($row['RESPONSIBLE'] ?? ''));
        $responsible = $responsible_name !== '' ? ($this->user_lookup[mb_strtoupper($responsible_name)] ?? null) : null;

        $this->mom_data[$code]['topics'][] = [
            'topic' => $topic,
            'next_step' => trim((string) ($row['NEXT STEP'] ?? '')),
            'target_date' => $this->formatDate($row['TARGET DATE'] ?? null),
            'responsible' => $responsible->name ?? $responsible_name,
            'responsible_id' => $responsible->id ?? null,
            'action_plan' => trim((string) ($row['ACTION PLAN'] ?? '')),
            'status' => trim((string) ($row['STATUS'] ?? '')),
            'days_completed' => trim((string) ($row['DAYS COMPLETED'] ?? '')),
            'remarks' => trim((string) ($row['REMARKS'] ?? '')),
        ];
    }

    private function formatDate($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    public function saveMom()
    {
        if (empty($this->mom_data)) {
            return;
        }

        DB::connection(Session::get('db_connection', 'mysql'))->transaction(function() {
            foreach($this->mom_data as $mom_number => $mom_val) {

                // check if already exists
                $exists = Mom::where('mom_number', $mom_number)->exists();
                if($exists) {
                    continue;
                }

                $mom = Mom::create([
                    'mom_type_id' => $mom_val['header']['type_id'] ?? NULL,
                    'user_id' => auth()->id(),
                    'mom_number' => MomNumberHelper::generateMomNumber($mom_number),
                    'agenda' => $mom_val['header']['agenda'] ?? '',
                    'meeting_date' => $mom_val['header']['meeting_date'] ?? date('Y-m-d'),
                    'status' => 'draft',
                ]);

                $attendees_ids = [];
                foreach($mom_val['topics'] ?? [] as $topic) {
                    $mom_detail = MomDetail::create([
                        'mom_id' => $mom->id,
                        'topic' => $topic['topic'],
                        'next_step' => $topic['next_step'],
                        'target_date' => $topic['target_date'] ?? date('Y-m-d'),
                        'completed_date' => NULL,
                        'remarks' => $topic['remarks'],
                        'status' => 'open',
                    ]);

                    if(!empty($topic['responsible_id'])) {
                        $mom_detail->responsibles()->sync([$topic['responsible_id']]);

                        $attendees_ids[] = $topic['responsible_id'];
                    }
                }

                $mom->participants()->sync(array_unique($attendees_ids));
            }
        });

        return redirect()->route('mom.index')->with([
            'message_success' => __('adminlte::moms.mom_uploaded')
        ]);
    }
}
