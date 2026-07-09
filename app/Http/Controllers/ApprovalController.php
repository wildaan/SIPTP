<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\Approval;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes'  => 'nullable|string|max:500'
        ]);

        $submission  = Submission::where('submissions_uuid', $id)->firstOrFail();
        $userUuid    = Session::get('users_uuid');
        $roleUuid    = Session::get('users_roles_uuid');
        $roleCode    = Session::get('roles_code');
        $action      = $request->action;
        $notes       = $request->notes;
        $currentStatus = $submission->submissions_status;

        $allowed = [
            'spv'      => 3,
            'manager'  => 4,
            'direktur' => 5,
        ];

        if (!isset($allowed[$roleCode]) || $currentStatus != $allowed[$roleCode]) {
            return response()->json([
                'status'  => false,
                'message' => 'Anda tidak memiliki hak untuk memproses pengajuan ini pada tahap ini.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $step       = Approval::where('approvals_submissions_uuid', $submission->submissions_uuid)->count() + 1;
            $nextStatus = $currentStatus;
            $amount     = $submission->submissions_amount;

            if ($action === 'reject') {
                $nextStatus = 8;
            } else {
                if ($currentStatus == 3) { // SPV approve
                    $nextStatus = ($amount > 5000000) ? 4 : 6;
                } elseif ($currentStatus == 4) { // Manager approve
                    $nextStatus = ($amount > 10000000) ? 5 : 6;
                } elseif ($currentStatus == 5) { // Direktur approve
                    $nextStatus = 6;
                }
            }

            Approval::create([
                'approvals_submissions_uuid' => $submission->submissions_uuid,
                'approvals_user_uuid'        => $userUuid,
                'approvals_roles_uuid'       => $roleUuid,
                'approvals_step'             => $step,
                'approvals_notes'            => $notes,
                'approvals_action_date'      => now(),
                'approvals_status'           => ($action === 'approve') ? 1 : 2,
                'approvals_create_by'        => $userUuid
            ]);

            $submission->update([
                'submissions_status'        => $nextStatus,
                'submissions_update_by'     => $userUuid,
                'submissions_reject_reason' => ($action === 'reject') ? $notes : $submission->submissions_reject_reason
            ]);

            DB::commit();

            $actionLabel = $action === 'approve' ? 'disetujui' : 'ditolak';
            return response()->json([
                'status'   => true,
                'message'  => "Pengajuan berhasil {$actionLabel}.",
                'redirect' => route('submissions.index')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
