<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Mom;
use App\Models\MomDetail;
use App\Models\MomApproval;

use App\Http\Traits\SettingTrait;
use Illuminate\Support\Facades\Session;
use App\Helpers\MomNumberHelper;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SingleMomExport;

use Carbon\Carbon;

/**
 * Class MomController
 * 
 * Controller to handle Meeting of Minutes (MOM) related operations.
 * Provides methods to list, create, show, edit, and upload MOMs.
 */
class MomController extends Controller
{
    use SettingTrait;

    /**
     * Status array mapping MOM statuses to bootstrap badge classes.
     *
     * @var array
     */
    public $status_arr = [
        'draft'         => 'secondary',
        'submitted'     => 'info',
        'ongoing'       => 'warning',
        'completed'     => 'success',
    ];

    /**
     * Display a paginated list of MOMs with optional search filtering and role-based access.
     *
     * @param Request $request HTTP request object containing search query.
     * @return \Illuminate\View\View View displaying the list of MOMs.
     */
    public function index(Request $request) {
        // Trim and get search query parameter
        $search = trim($request->get('search'));

        // Clear any existing MOM data in session
        Session::forget('mom_data');

        // Query MOMs ordered by mom_number descending with related user
        $moms = Mom::orderBy('mom_number', 'DESC')
            ->with('user', 'type')
            // Apply search filters on multiple fields and related models
            ->when(!empty($search), function($query) use($search) {
                $query->where('mom_number', 'LIKE', '%'.$search.'%')
                    ->orWhere('agenda', 'LIKE', '%'.$search.'%')
                    ->orWhere('meeting_date', 'LIKE', '%'.$search.'%')
                    ->orWhere('status', 'LIKE', '%'.$search.'%')
                    ->orWhereHas('user', function($qry) use($search) {
                        $qry->where('name', 'LIKE', '%'.$search.'%');
                    })
                    ->orWhereHas('type', function($qry) use($search) {
                        $qry->where('type', 'LIKE', '%'.$search.'%');
                    });
            })
            // Restrict query for non-superadmin users to their own MOMs or participation
            ->when(!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('admin'), function($query) {
                $query->where(function($qry) {
                    $qry->whereHas('participants', function($qry1) {
                        $qry1->where('id', auth()->user()->id);
                    })
                    ->orWhere('user_id', auth()->user()->id);
                });
            })
            // Paginate results with configurable items per page
            ->paginate($this->getDataPerPage())
            // Append query parameters to pagination links
            ->appends(request()->query());

        // Return the MOMs index view with search, MOMs data, and status array
        return view('pages.moms.index')->with([
            'search' => $search,
            'moms' => $moms,
            'status_arr' => $this->status_arr,
        ]);
    }

    /**
     * Show the form for creating a new MOM.
     * If no MOM data in session, create a new draft MOM and store in session.
     *
     * @return \Illuminate\View\View View displaying the create MOM form.
     */
    public function create() {
        // Get MOM data from session
        $mom_data = Session::get('mom_data');
        if(empty($mom_data)) {
            // Create new draft MOM with default values
            $mom = Mom::create([
                'mom_type_id' => NULL,
                'user_id' => auth()->user()->id,
                'mom_number' => MomNumberHelper::generateMomNumber(),
                'agenda' => '',
                'meeting_date' => date('Y-m-d'),
                'status' => 'draft',
            ]);

            // Store MOM data in session
            Session::put('mom_data', $mom);
    
            // Log the creation activity
            activity('created')
                ->performedOn($mom)
                ->log(':causer.name has created a new mom :subject.mom_number');
        } else {
            $mom = $mom_data;
        }

        // Return the create MOM view with MOM data
        return view('pages.moms.create')->with([
            'mom' => $mom
        ]);
    }

    /**
     * Display the specified MOM details.
     *
     * @param string $id Encrypted MOM ID.
     * @return \Illuminate\View\View View displaying the MOM details.
     */
    public function show($id) {
        $mom = Mom::findOrFail(decrypt($id));

        // Return the show MOM view with MOM data and status array
        return view('pages.moms.show')->with([
            'mom' => $mom,
            'status_arr' => $this->status_arr,
        ]);

    }

    /**
     * Show the form for editing the specified MOM.
     *
     * @param string $id Encrypted MOM ID.
     * @return \Illuminate\View\View View displaying the edit MOM form.
     */
    public function edit($id) {
        $mom = Mom::findOrFail(decrypt($id));

        // Return the edit MOM view with MOM data
        return view('pages.moms.edit')->with([
            'mom' => $mom
        ]);
    }

    /**
     * Show the upload form for MOMs.
     *
     * @return \Illuminate\View\View View displaying the MOM upload form.
     */
    public function upload() {
        // Return the MOM upload view
        return view('pages.moms.upload');
    }

    public function topic($id) {
        $detail = MomDetail::findOrFail(decrypt($id));

        return view('pages.moms.topic')->with([
            'detail' => $detail,
            'status_arr' => $this->status_arr
        ]);
    }

    public function printPDF($id) {
        $mom = Mom::findOrFail(decrypt($id));
        $status_arr = [
            'open' => 'secondary',
            'overdue' => 'danger',
            'extended' => 'warning',
            'on-time' => 'success',
        ];

        $approval_dates = MomApproval::select(DB::raw('DATE(created_at) as date'))
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->where('mom_id', $mom->id)
            ->get();

        $approval_data = [];
        foreach($approval_dates as $data) {
            $approvals = MomApproval::with('user')
                ->orderBy('created_at', 'DESC')
                ->where('mom_id', $mom->id)
                ->where(DB::raw('DATE(created_at)'), $data->date)
                ->get();
            
            $approval_data[$data->date] = $approvals;
        }

        $pdf = PDF::loadView('pages.moms.pdf', [
            'mom' => $mom,
            'status_arr' => $status_arr,
            'approval_data' => $approval_data,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('weekly-activity-report-'.$mom->mom_number.'-'.time().'.pdf');
    }

    public static function checkDaysExtended($detail) {
        $status = 'open';

        if ($detail->status != 'completed') {
            $currentDate = Carbon::today();
            $targetDate = Carbon::parse($detail->target_date);
  
            if ($targetDate->lt($currentDate)) {
                // Target date is **before** current date (past due)
                $status = 'overdue';
            } elseif ($targetDate->gt($currentDate)) {
                // Target date is **after** current date (still time left)
                $status = 'open';
            } else { 
                // Target date is **today**
                if(!empty($detail->actions->count())) {
                    $status = 'open';
                }
            }
        } else {
            // Compute days completed
            $completedDate = Carbon::parse($detail->completed_date);
            $targetDate = Carbon::parse($detail->target_date);

            $daysToComplete = $completedDate->diffInDays($targetDate, false); // false = returns negative if completed before target

            $days_completed = $daysToComplete;

            // check if completed beyond target date
            if($daysToComplete > 0) {
                $status = 'on-time';
            } elseif($daysToComplete < 0) {
                $status = 'extended';
            }
        }

        return $status;
    }
    
    public function exportExcel($id) {
        $mom = Mom::findOrFail(decrypt($id));

        return Excel::download(new SingleMomExport($mom->id), 'mom-'.$mom->mom_number.'-'.time().'.xlsx');
    }
}
