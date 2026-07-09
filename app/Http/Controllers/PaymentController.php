<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\Payment;
use App\Models\Budget;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'action'         => 'required|in:pay,reject',
            'payment_method' => 'required_if:action,pay|integer',
            'notes'          => 'nullable|string|max:500'
        ]);

        $submission = Submission::with('category')->where('submissions_uuid', $id)->firstOrFail();
        $userUuid   = Session::get('users_uuid');
        $roleCode   = Session::get('roles_code');
        $action     = $request->action;
        $notes      = $request->notes;

        if ($roleCode !== 'finance' || $submission->submissions_status != 6) {
            return response()->json([
                'status'  => false,
                'message' => 'Anda tidak memiliki hak untuk memproses pembayaran ini.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            if ($action === 'reject') {
                $submission->update([
                    'submissions_status'        => 8,
                    'submissions_update_by'     => $userUuid,
                    'submissions_reject_reason' => $notes ?: 'Ditolak oleh Finance: Saldo tidak mencukupi.'
                ]);

                DB::commit();
                return response()->json([
                    'status'   => true,
                    'message'  => 'Pengajuan telah ditolak (saldo tidak mencukupi).',
                    'redirect' => route('submissions.index')
                ]);
            }

            $amount      = $submission->submissions_amount;
            $currentYear = date('Y', strtotime($submission->submissions_date));

            $budget = Budget::where('budgets_categories_uuid', $submission->submissions_category_uuid)
                ->where('budgets_period_year', $currentYear)
                ->lockForUpdate() // race condition
                ->first();

            if (!$budget) {
                throw new \Exception("Budget untuk kategori ini tidak ditemukan.");
            }

            $available = $budget->budgets_total_budget - $budget->budgets_used_budget;
            if ($amount > $available) {
                throw new \Exception(
                    "Saldo budget tidak mencukupi saat ini. " .
                    "Tersedia: Rp " . number_format($available, 0, ',', '.') .
                    " | Dibutuhkan: Rp " . number_format($amount, 0, ',', '.')
                );
            }

            $budget->update([
                'budgets_used_budget' => $budget->budgets_used_budget + $amount,
                'budgets_update_by'   => $userUuid
            ]);

            Payment::create([
                'payments_submissions_uuid'  => $submission->submissions_uuid,
                'payments_finance_user_uuid' => $userUuid,
                'payments_date'              => now()->toDateString(),
                'payments_amount_paid'       => $amount,
                'payments_method'            => $request->payment_method ?: 1,
                'payments_notes'             => $notes,
                'payments_status'            => 1,
                'payments_create_by'         => $userUuid
            ]);

            $submission->update([
                'submissions_status'    => 7,
                'submissions_update_by' => $userUuid
            ]);

            DB::commit();

            return response()->json([
                'status'   => true,
                'message'  => 'Pembayaran berhasil diproses. Status pengajuan menjadi Paid.',
                'redirect' => route('submissions.index')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memproses: ' . $e->getMessage()
            ], 422);
        }
    }
}
