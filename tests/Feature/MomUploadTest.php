<?php

namespace Tests\Feature;

use App\Livewire\Moms\Upload;
use App\Models\Mom;
use App\Models\MomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Tests\TestCase;

class MomUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $responsible;
    protected MomType $type;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->user = User::factory()->create();
        $this->responsible = User::factory()->create(['name' => 'John Doe']);
        $this->type = MomType::factory()->create(['type' => 'MID MONTH MEETING']);
    }

    /**
     * Build a real xlsx upload from row arrays keyed by the template headers.
     */
    private function makeUploadFile(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'mom_upload_test') . '.xlsx';

        $writer = SimpleExcelWriter::create($path);
        foreach ($rows as $row) {
            $writer->addRow($row);
        }
        $writer->close();

        $file = UploadedFile::fake()->createWithContent('mom.xlsx', file_get_contents($path));
        unlink($path);

        return $file;
    }

    private function row(array $overrides = []): array
    {
        return array_merge([
            'CODE' => '',
            'DATE OF MEETING' => '',
            'TYPE' => '',
            'AGENDA' => '',
            'TOPIC' => '',
            'NEXT STEP' => '',
            'TARGET DATE' => '',
            'RESPONSIBLE' => '',
            'ACTION PLAN' => '',
            'STATUS' => '',
            'DAYS COMPLETED' => '',
            'REMARKS' => '',
        ], $overrides);
    }

    /** @test */
    public function check_data_groups_rows_by_code(): void
    {
        $file = $this->makeUploadFile([
            $this->row([
                'CODE' => 'MOM-20260101-0001',
                'DATE OF MEETING' => '2026-01-01',
                'TYPE' => 'MID MONTH MEETING',
                'AGENDA' => 'Agenda A',
                'TOPIC' => 'Topic 1',
                'NEXT STEP' => 'Step 1',
                'TARGET DATE' => '2026-01-15',
                'RESPONSIBLE' => 'John Doe',
                'STATUS' => 'OPEN',
            ]),
            $this->row([
                'CODE' => 'MOM-20260102-0001',
                'DATE OF MEETING' => '2026-01-02',
                'TYPE' => 'MID MONTH MEETING',
                'AGENDA' => 'Agenda B',
                'TOPIC' => 'Topic B1',
                'RESPONSIBLE' => 'John Doe',
            ]),
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(Upload::class)
            ->set('upload_file', $file)
            ->call('checkData')
            ->assertHasNoErrors();

        $mom_data = $component->get('mom_data');

        $this->assertCount(2, $mom_data);
        $this->assertSame('Agenda A', $mom_data['MOM-20260101-0001']['header']['agenda']);
        $this->assertSame($this->type->id, $mom_data['MOM-20260101-0001']['header']['type_id']);
        $this->assertSame('2026-01-01', $mom_data['MOM-20260101-0001']['header']['meeting_date']);
        $this->assertSame($this->responsible->id, $mom_data['MOM-20260101-0001']['topics'][0]['responsible_id']);
        $this->assertCount(1, $mom_data['MOM-20260102-0001']['topics']);
    }

    /** @test */
    public function continuation_rows_with_blank_code_attach_to_previous_mom(): void
    {
        $file = $this->makeUploadFile([
            $this->row([
                'CODE' => 'MOM-20260101-0001',
                'DATE OF MEETING' => '2026-01-01',
                'TYPE' => 'MID MONTH MEETING',
                'AGENDA' => 'Agenda A',
                'TOPIC' => 'Topic 1',
                'RESPONSIBLE' => 'John Doe',
            ]),
            $this->row([
                'TOPIC' => 'Topic 2',
                'TARGET DATE' => '2026-01-20',
            ]),
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(Upload::class)
            ->set('upload_file', $file)
            ->call('checkData')
            ->assertHasNoErrors();

        $mom_data = $component->get('mom_data');

        $this->assertCount(1, $mom_data);
        $this->assertCount(2, $mom_data['MOM-20260101-0001']['topics']);
        $this->assertSame('Agenda A', $mom_data['MOM-20260101-0001']['header']['agenda']);
        $this->assertSame('Topic 2', $mom_data['MOM-20260101-0001']['topics'][1]['topic']);
    }

    /** @test */
    public function save_mom_creates_mom_with_details_and_participants(): void
    {
        $file = $this->makeUploadFile([
            $this->row([
                'CODE' => 'MOM-20260101-0001',
                'DATE OF MEETING' => '2026-01-01',
                'TYPE' => 'MID MONTH MEETING',
                'AGENDA' => 'Agenda A',
                'TOPIC' => 'Topic 1',
                'NEXT STEP' => 'Step 1',
                'TARGET DATE' => '2026-01-15',
                'RESPONSIBLE' => 'John Doe',
            ]),
            $this->row([
                'TOPIC' => 'Topic 2',
                'RESPONSIBLE' => 'John Doe',
            ]),
        ]);

        Livewire::actingAs($this->user)
            ->test(Upload::class)
            ->set('upload_file', $file)
            ->call('checkData')
            ->assertHasNoErrors()
            ->call('saveMom')
            ->assertRedirect(route('mom.index'));

        $mom = Mom::where('mom_number', 'MOM-20260101-0001')->first();

        $this->assertNotNull($mom);
        $this->assertSame($this->type->id, $mom->mom_type_id);
        $this->assertSame('Agenda A', $mom->agenda);
        $this->assertCount(2, $mom->details);
        $this->assertCount(1, $mom->participants);
        $this->assertTrue($mom->details->first()->responsibles->contains($this->responsible->id));
    }

    /** @test */
    public function empty_dates_default_to_today_instead_of_1970(): void
    {
        $file = $this->makeUploadFile([
            $this->row([
                'CODE' => 'MOM-20260101-0001',
                'TYPE' => 'MID MONTH MEETING',
                'AGENDA' => 'Agenda A',
                'TOPIC' => 'Topic 1',
                'RESPONSIBLE' => 'John Doe',
            ]),
        ]);

        Livewire::actingAs($this->user)
            ->test(Upload::class)
            ->set('upload_file', $file)
            ->call('checkData')
            ->call('saveMom');

        $mom = Mom::where('mom_number', 'MOM-20260101-0001')->first();

        $this->assertNotNull($mom);
        $this->assertSame(date('Y-m-d'), $mom->meeting_date);
        $this->assertSame(date('Y-m-d'), $mom->details->first()->target_date->format('Y-m-d'));
    }

    /** @test */
    public function rows_without_code_and_topic_are_skipped(): void
    {
        $file = $this->makeUploadFile([
            $this->row(['TYPE' => 'MID MONTH MEETING']),
            $this->row(['TYPE' => 'BUDGET REVIEW - TMG']),
        ]);

        Livewire::actingAs($this->user)
            ->test(Upload::class)
            ->set('upload_file', $file)
            ->call('checkData')
            ->assertHasErrors('upload_file')
            ->assertSet('mom_data', []);
    }

    /** @test */
    public function existing_mom_numbers_are_not_duplicated(): void
    {
        Mom::factory()->create(['mom_number' => 'MOM-20260101-0001']);

        $file = $this->makeUploadFile([
            $this->row([
                'CODE' => 'MOM-20260101-0001',
                'TYPE' => 'MID MONTH MEETING',
                'AGENDA' => 'Agenda A',
                'TOPIC' => 'Topic 1',
            ]),
        ]);

        Livewire::actingAs($this->user)
            ->test(Upload::class)
            ->set('upload_file', $file)
            ->call('checkData')
            ->call('saveMom');

        $this->assertSame(1, Mom::where('mom_number', 'MOM-20260101-0001')->count());
    }
}
