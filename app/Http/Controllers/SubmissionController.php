<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\Category;
use App\Models\Budget;
use App\Models\DocumentSubmission;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Approval;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubmissionNotification;
use App\Models\User;

class SubmissionController extends Controller
{
    public function index()
    {
        $role  = Session::get('roles_code');
        $uuid  = Session::get('users_uuid');
        $query = Submission::with('category', 'user');

        switch ($role) {
            case 'staff':
                $query->where('submissions_user_uuid', $uuid);
                break;
            case 'spv':
                $query->where('submissions_status', 3);
                break;
            case 'manager':
                $query->where('submissions_status', 4);
                break;
            case 'direktur':
                $query->where('submissions_status', 5);
                break;
            case 'finance':
                $query->where('submissions_status', 6);
                break;
        }

        $submissions = $query->orderBy('submissions_id', 'desc')->get();
        logActivity('VIEW_SUBMISSIONS', 'Melihat daftar pengajuan transaksi');
        return view('submissions.index', compact('submissions', 'role'));
    }

    public function create()
    {
        $roleCode   = Session::get('roles_code');
        if ($roleCode !== 'staff') {
            return view('page-403');
        }
        $categories = Category::where('categories_status', 1)->get();
        $subNum = $this->generateSubmissionNumber();
        logActivity('VIEW_CREATE_SUBMISSION', 'Melihat halaman form pengajuan baru');
        return view('submissions.create', compact('categories', 'subNum'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'amount' => sanitize_rupiah($request->amount)
        ]);

        $request->validate([
            'categories_uuid' => 'required|exists:categories,categories_uuid',
            'amount'          => 'required|numeric|min:1',
            'description'     => 'required|string',
            'documents'       => 'required|array|min:1',
            'documents.*'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'categories_uuid.required' => 'Kategori wajib dipilih.',
            'categories_uuid.exists'   => 'Kategori tidak valid.',
            'amount.required'          => 'Nilai pengajuan wajib diisi.',
            'amount.min'               => 'Nilai pengajuan minimal Rp 1.',
            'description.required'     => 'Deskripsi wajib diisi.',
            'documents.required'       => 'Minimal harus ada 1 file yang diunggah.',
            'documents.min'            => 'Minimal harus ada 1 file yang diunggah.',
            'documents.*.required'     => 'File dokumen wajib dipilih.',
            'documents.*.mimes'        => 'Dokumen harus berupa PDF, JPG, JPEG, atau PNG.',
            'documents.*.max'          => 'Ukuran dokumen maksimal 5 MB.',
        ]);

        $category    = Category::where('categories_uuid', $request->categories_uuid)->first();
        $amount      = (float) $request->amount;
        $currentYear = date('Y');

        $budget      = Budget::where('budgets_categories_uuid', $category->categories_uuid)
            ->where('budgets_period_year', $currentYear)
            ->first();

        $status       = 1;
        $rejectReason = null;
        $normalizedCategoryName = strtolower($category->categories_name);
        $normalizedCategoryName = preg_replace('/[^a-z0-9]+/', ' ', $normalizedCategoryName);
        $normalizedCategoryName = trim(preg_replace('/\s+/', ' ', $normalizedCategoryName));
        $isPoProduk = strpos($normalizedCategoryName, 'po produk') !== false;
        if ($isPoProduk) {
            $status = 5;
        } elseif ($amount <= 5000000) {
            if (!$budget) {
                $status       = 8;
                $rejectReason = 'Budget untuk kategori "' . $category->categories_name . '" belum diatur untuk tahun ' . $currentYear . '.';
            } else {
                $availableBudget = $budget->budgets_total_budget - $budget->budgets_used_budget;
                if ($amount > $availableBudget) {
                    $status       = 8;
                    $rejectReason = 'Budget kategori tidak mencukupi. Tersedia: Rp ' . number_format($availableBudget, 0, ',', '.') . '.';
                } else {
                    $status = 3;
                }
            }
        } elseif ($amount <= 10000000) {
            if (!$budget) {
                $status       = 8;
                $rejectReason = 'Budget untuk kategori "' . $category->categories_name . '" belum diatur untuk tahun ' . $currentYear . '.';
            } else {
                $availableBudget = $budget->budgets_total_budget - $budget->budgets_used_budget;
                if ($amount > $availableBudget) {
                    $status       = 8;
                    $rejectReason = 'Budget kategori tidak mencukupi. Tersedia: Rp ' . number_format($availableBudget, 0, ',', '.') . '.';
                } else {
                    $status = 4;
                }
            }
        } else {
            $status = 5;
        }


        DB::beginTransaction();
        try {
            // if($status !== 8) {
                
            // }
            $submission = Submission::create([
                    'submissions_category_uuid'       => $category->categories_uuid,
                    'submissions_submissions_number'  => $request->submission_number,
                    'submissions_user_uuid'           => Session::get('users_uuid'),
                    'submissions_date'                => $request->submissions_date,
                    'submissions_amount'              => $amount,
                    'submissions_description'         => $request->description,
                    'submissions_status'              => $status,
                    'submissions_create_by'           => Session::get('users_uuid'),
                ]);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                    $path     = $file->storeAs('submissions', $filename, 'public');

                    DocumentSubmission::create([
                        'document_submission_submission_uuid' => $submission->submissions_uuid,
                        'document_submission_file_name'       => $file->getClientOriginalName(),
                        'document_submission_file_path'       => str_replace('public/', '', $path),
                        'document_submission_file_size'       => $file->getSize(),
                        'document_submission_file_type'       => $file->getClientMimeType(),
                        'document_submission_create_by'       => Session::get('users_uuid')
                    ]);
                }
            }
            $userUuid    = Session::get('users_uuid');
            $roleUuid    = Session::get('users_roles_uuid');
            Approval::create([
                'approvals_submissions_uuid' => $submission->submissions_uuid,
                'approvals_user_uuid'        => $userUuid,
                'approvals_roles_uuid'       => $roleUuid,
                'approvals_step'             => 1,
                'approvals_notes'            => "Pengajuan transaksi pengeluaran dengan nomor pengajuan :".$request->submission_number,
                'approvals_action_date'      => now(),
                'approvals_status'           => 1,
                'approvals_create_by'        => $userUuid
            ]);

            DB::commit();

            if ($status === 8) {
                logActivity('CREATE_SUBMISSION_FAILED', "Gagal membuat pengajuan transaksi pengeluaran karena: {$rejectReason}");
                // return response()->json([
                //     'status'   => false,
                //     'message'  => 'Pengajuan gagal dibuat ,dengan alasan : ' . $rejectReason,
                //     'redirect' => route('submissions.index')
                // ]);
                Approval::create([
                    'approvals_submissions_uuid' => $submission->submissions_uuid,
                    'approvals_user_uuid'        => $userUuid,
                    'approvals_roles_uuid'       => $roleUuid,
                    'approvals_step'             => 1,
                    'approvals_notes'            => $rejectReason,
                    'approvals_action_date'      => now(),
                    'approvals_status'           => 0,
                    'approvals_create_by'        => $userUuid
                ]);

                try {
                    $staff = User::where('users_uuid', $userUuid)->first();
                    if ($staff) {
                        Mail::to($staff->users_email)->queue(new SubmissionNotification(
                            'Pengajuan Ditolak - Budget Tidak Mencukupi',
                            'Halo ' . $staff->users_user_name . ',',
                            ['Pengajuan Anda tidak dapat dilanjutkan karena ditolak otomatis oleh sistem.', 'Alasan: ' . $rejectReason],
                            $submission
                        ));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send email notification (Store Reject): ' . $e->getMessage());
                }
            }
            else{            
                logActivity('CREATE_SUBMISSION', "Membuat pengajuan transaksi pengeluaran {$submission->submissions_submissions_number} senilai Rp " . number_format($amount, 0, ',', '.'));
                
                try {
                    $staff = User::where('users_uuid', $userUuid)->first();
                    if ($staff) {
                        $tierInfo = '';
                        if ($status == 3) $tierInfo = 'SPV';
                        if ($status == 4) $tierInfo = 'Manager';
                        if ($status == 5) $tierInfo = 'Direktur';
                        
                        Mail::to($staff->users_email)->queue(new SubmissionNotification(
                            'Pengajuan Berhasil Dibuat',
                            'Halo ' . $staff->users_user_name . ',',
                            ['Pengajuan Anda berhasil diajukan dengan nomor: ' . $submission->submissions_submissions_number . '.', 'Saat ini sedang menunggu approval ' . $tierInfo . '.'],
                            $submission
                        ));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send email notification (Store Success): ' . $e->getMessage());
                }
            }
            return response()->json([
                'status'   => true,
                'message'  => 'Pengajuan berhasil transaksi pengeluran berhasil dibuat',
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

    public function show($id)
    {
        $submission = Submission::with([
            'category',
            'user',
            'documents',
            'approvals.user',
            'approvals.role'
        ])
            ->where('submissions_uuid', $id)
            ->orWhere('submissions_id', $id)
            ->firstOrFail();

        logActivity('VIEW_SUBMISSION_DETAILS', "Melihat detail pengajuan transaksi {$submission->submissions_submissions_number}");

        // Hitung sisa budget kategori untuk indikator saldo di halaman Finance
        $budgetAvailable = null;
        $roleCode = Session::get('roles_code');
        if ($roleCode === 'finance' && $submission->submissions_status == 6) {
            $currentYear = date('Y', strtotime($submission->submissions_date));
            $budget = Budget::where('budgets_categories_uuid', $submission->submissions_category_uuid)
                ->where('budgets_period_year', $currentYear)
                ->first();

            if ($budget) {
                $budgetAvailable = $budget->budgets_total_budget - $budget->budgets_used_budget;
            } else {
                $budgetAvailable = 0; // Budget belum diatur = 0
            }
        }

        return view('submissions.show', compact('submission', 'budgetAvailable'));
    }

    public function history()
    {
        $role = Session::get('roles_code');
        $uuid = Session::get('users_uuid');

        if ($role === 'staff') {
            return redirect()->route('submissions.index');
        }

        $query = Submission::with('category', 'user');

        if (in_array($role, ['spv', 'manager', 'direktur'])) {
            $query->whereHas('approvals', function ($q) use ($uuid) {
                $q->where('approvals_user_uuid', $uuid);
            });
        } elseif ($role === 'finance') {
            $query->where(function ($q) use ($uuid) {
                $q->whereHas('payment', function ($pq) use ($uuid) {
                    $pq->where('payments_finance_user_uuid', $uuid);
                })->orWhere(function ($oq) use ($uuid) {
                    $oq->where('submissions_status', 8)->where('submissions_update_by', $uuid);
                });
            });
        }

        $submissions = $query->orderBy('submissions_update_date', 'desc')->get();
        logActivity('VIEW_SUBMISSION_HISTORY', 'Melihat riwayat pengajuan transaksi');
        return view('submissions.history', compact('submissions', 'role'));
    }

    public function approve(Request $request, $id)
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

            if ($action === 'reject') {
                $nextStatus = 8;
            } else {
                if (in_array($currentStatus, [3, 4, 5])) {
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
                'approvals_status'           => ($action === 'approve') ? 2 : 0,
                'approvals_create_by'        => $userUuid
            ]);

            $submission->update([
                'submissions_status'        => $nextStatus,
                'submissions_update_by'     => $userUuid,
                'submissions_reject_reason' => ($action === 'reject') ? $notes : $submission->submissions_reject_reason
            ]);

            DB::commit();

            $actionLabelLog = $action === 'approve' ? 'menyetujui' : 'menolak';
            logActivity('APPROVE_SUBMISSION', "User {$actionLabelLog} pengajuan {$submission->submissions_submissions_number} pada step {$step}");

            try {
                // Email ke staff pemilik
                $staff = User::where('users_uuid', $submission->submissions_user_uuid)->first();
                if ($staff) {
                    $tierName = strtoupper($roleCode);
                    if ($action === 'approve') {
                        $nextTierInfo = 'lanjut ke tahap berikutnya';
                        if ($nextStatus == 6) {
                            $nextTierInfo = 'menunggu proses Finance';
                        }
                        Mail::to($staff->users_email)->queue(new SubmissionNotification(
                            'Pengajuan Disetujui',
                            'Halo ' . $staff->users_user_name . ',',
                            ['Pengajuan Anda telah disetujui oleh ' . $tierName . ', ' . $nextTierInfo . '.'],
                            $submission
                        ));
                    } else {
                        Mail::to($staff->users_email)->queue(new SubmissionNotification(
                            'Pengajuan Ditolak',
                            'Halo ' . $staff->users_user_name . ',',
                            ['Pengajuan Anda ditolak oleh ' . $tierName . '.', 'Alasan: ' . ($notes ?: '-')],
                            $submission
                        ));
                    }
                }

                // Jika lanjut ke Finance, kirim notifikasi ke tim Finance
                if ($action === 'approve' && $nextStatus == 6) {
                    $finances = User::whereHas('role', function($q){
                        $q->where('roles_code', 'finance');
                    })->where('users_status', 1)->get();
                    
                    foreach($finances as $finance) {
                        Mail::to($finance->users_email)->queue(new SubmissionNotification(
                            'Menunggu Pembayaran',
                            'Halo Tim Finance,',
                            ['Terdapat pengajuan baru (No: ' . $submission->submissions_submissions_number . ') yang siap diproses untuk pembayaran.'],
                            $submission
                        ));
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send email notification (Approve): ' . $e->getMessage());
            }

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

    public function payment(Request $request, $id)
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
        $roleUuid    = Session::get('users_roles_uuid');
        $step        = Approval::where('approvals_submissions_uuid', $submission->submissions_uuid)->count() + 1;
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

                 Approval::create([
                    'approvals_submissions_uuid' => $submission->submissions_uuid,
                    'approvals_user_uuid'        => $userUuid,
                    'approvals_roles_uuid'       => $roleUuid,
                    'approvals_step'             => $step,
                    'approvals_notes'            => $notes ?: 'Ditolak oleh Finance: Saldo tidak mencukupi.',
                    'approvals_action_date'      => now(),
                    'approvals_status'           => 0,
                    'approvals_create_by'        => $userUuid
                ]);

                DB::commit();
                logActivity('REJECT_PAYMENT', "Finance menolak pembayaran untuk pengajuan {$submission->submissions_submissions_number}");
                
                try {
                    $staff = User::where('users_uuid', $submission->submissions_user_uuid)->first();
                    if ($staff) {
                        Mail::to($staff->users_email)->queue(new SubmissionNotification(
                            'Pengajuan Ditolak oleh Finance',
                            'Halo ' . $staff->users_user_name . ',',
                            ['Pengajuan Anda ditolak oleh Finance karena saldo tidak mencukupi.', 'Alasan: ' . ($notes ?: '-')],
                            $submission
                        ));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send email notification (Payment Reject): ' . $e->getMessage());
                }
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
            Approval::create([
                'approvals_submissions_uuid' => $submission->submissions_uuid,
                'approvals_user_uuid'        => $userUuid,
                'approvals_roles_uuid'       => $roleUuid,
                'approvals_step'             => $step,
                'approvals_notes'            => $notes,
                'approvals_action_date'      => now(),
                'approvals_status'           => ($action !== 'reject') ? 3 : 0,
                'approvals_create_by'        => $userUuid
            ]);

            DB::commit();
            logActivity('PROCESS_PAYMENT', "Finance memproses pembayaran untuk pengajuan {$submission->submissions_submissions_number} senilai Rp " . number_format($amount, 0, ',', '.'));

            try {
                $staff = User::where('users_uuid', $submission->submissions_user_uuid)->first();
                if ($staff) {
                    Mail::to($staff->users_email)->queue(new SubmissionNotification(
                        'Pembayaran Pengajuan Telah Selesai',
                        'Halo ' . $staff->users_user_name . ',',
                        ['Pengajuan Anda telah dibayar oleh Finance pada tanggal ' . date('d/m/Y') . '.'],
                        $submission
                    ));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send email notification (Payment Success): ' . $e->getMessage());
            }

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

    public function dashboard()
    {
        $is_admin = Session::get('users_is_admin');
        $userUuid = Session::get('users_uuid');
        $role_code = Session::get('roles_code');
        $totalQuery = Submission::query();
        $processQuery = Submission::where('submissions_user_uuid', $userUuid)->whereIn('submissions_status', [3, 4, 5, 6]);
        $successQuery = Submission::where('submissions_user_uuid', $userUuid)->where('submissions_status', 7);
        $rejectQuery = Submission::where('submissions_user_uuid', $userUuid)->where('submissions_status', 8);


        $recentSubmissionsQuery = [];

        if($role_code === 'spv') {
            $recentSubmissionsQuery = Submission::where('submissions_status', 3)->with('category', 'user');
        } else if($role_code == 'manager') {
            $recentSubmissionsQuery = Submission::where('submissions_status', 4)->with('category', 'user');
        } else if($role_code == 'finance') {
            $recentSubmissionsQuery = Submission::where('submissions_status', 6)->with('category', 'user');
        } else {
            $recentSubmissionsQuery = Submission::where('submissions_user_uuid', $userUuid)->with('category', 'user');
        }
        $stats = [
            'total' => $totalQuery->count(),
            'process' => $processQuery->count(),
            'success' => $successQuery->count(),
            'reject' => $rejectQuery->count(),
        ];

        $recentSubmissions = $recentSubmissionsQuery->orderBy('submissions_create_date', 'desc')->limit(5)->get();

        $recentActivityQuery = \App\Models\UserActivity::where('user_activity_user_uuid',$userUuid)
                                ->orderBy('user_activity_create_date', 'desc');

        $recentActivity = $recentActivityQuery->limit(5)->get();
        logActivity('VIEW_DASHBOARD', 'Melihat halaman dashboard');
        return view('dashboard', compact('stats', 'recentSubmissions', 'recentActivity'));
    }

    public function exportExcel()
    {
        $role  = Session::get('roles_code');
        $uuid  = Session::get('users_uuid');
        $query = Submission::with('category', 'user');

        switch ($role) {
            case 'staff':
                $query->where('submissions_user_uuid', $uuid);
                break;
            case 'spv':
                $query->where('submissions_status', 3);
                break;
            case 'manager':
                $query->where('submissions_status', 4);
                break;
            case 'direktur':
                $query->where('submissions_status', 5);
                break;
            case 'finance':
                $query->where('submissions_status', 6);
                break;
        }

        $submissions = $query->orderBy('submissions_create_date', 'desc')->get();

        $statusMap = [
            1 => 'Draft',
            2 => 'Submitted',
            3 => 'Waiting SPV Approval',
            4 => 'Waiting Manager Approval',
            5 => 'Waiting Director Approval',
            6 => 'Waiting Finance',
            7 => 'Paid',
            8 => 'Rejected',
        ];

        $filename = 'transaksi_pengajuan_' . date('Ymd_His') . '.xls';

        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($submissions, $statusMap) {
            $output = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            $output .= '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Pengajuan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
            $output .= '<body>';
            $output .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
            $output .= '<thead>';
            $output .= '<tr style="background-color: #435ebe; color: #ffffff; font-weight: bold;">';
            $output .= '<th style="border: 1px solid #000;">No</th>';
            $output .= '<th style="border: 1px solid #000;">No. Pengajuan</th>';
            $output .= '<th style="border: 1px solid #000;">Tanggal</th>';
            $output .= '<th style="border: 1px solid #000;">Pengaju</th>';
            $output .= '<th style="border: 1px solid #000;">Kategori</th>';
            $output .= '<th style="border: 1px solid #000;">Nilai (Rp)</th>';
            $output .= '<th style="border: 1px solid #000;">Status</th>';
            $output .= '</tr>';
            $output .= '</thead>';
            $output .= '<tbody>';

            $no = 1;
            foreach ($submissions as $sub) {
                $output .= '<tr>';
                $output .= '<td style="border: 1px solid #000; text-align: center;">' . $no++ . '</td>';
                $output .= '<td style="border: 1px solid #000;">' . htmlspecialchars($sub->submissions_submissions_number) . '</td>';
                $output .= '<td style="border: 1px solid #000;">' . date('d/m/Y', strtotime($sub->submissions_date)) . '</td>';
                $output .= '<td style="border: 1px solid #000;">' . htmlspecialchars($sub->user->users_user_name ?? '-') . '</td>';
                $output .= '<td style="border: 1px solid #000;">' . htmlspecialchars($sub->category->categories_name ?? '-') . '</td>';
                $output .= '<td style="border: 1px solid #000; text-align: right;">' . number_format($sub->submissions_amount, 0, ',', '.') . '</td>';
                $output .= '<td style="border: 1px solid #000;">' . ($statusMap[$sub->submissions_status] ?? 'Unknown') . '</td>';
                $output .= '</tr>';
            }

            $output .= '</tbody>';
            $output .= '</table>';
            $output .= '</body></html>';

            echo $output;
        };

        logActivity('EXPORT_EXCEL_SUBMISSIONS', 'Mengekspor daftar pengajuan transaksi ke Excel');

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf($id)
    {
        $submission = Submission::with([
            'category',
            'user',
            'documents.creator',
            'approvals.user',
            'approvals.role',
            'payment'
        ])
            ->where('submissions_uuid', $id)
            ->orWhere('submissions_id', $id)
            ->firstOrFail();

        if ($submission->submissions_status != 7) {
            abort(403, 'Export PDF hanya tersedia untuk pengajuan berstatus Paid.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('submissions.pdf', compact('submission'));
        $pdf->setPaper('A4', 'portrait');

        logActivity('EXPORT_PDF_SUBMISSION', "Mengekspor bukti pengajuan transaksi {$submission->submissions_submissions_number} ke PDF");

        return $pdf->stream('pengajuan_' . $submission->submissions_submissions_number . '.pdf');
    }

    private function generateSubmissionNumber()
    {
        $prefix = 'SUB-' . date('Ymd') . '-';
        $lastSubmission = Submission::where('submissions_submissions_number', 'like', $prefix . '%')
            ->orderByDesc('submissions_submissions_number')
            ->first();
        if ($lastSubmission) {
            $lastNumber = (int) substr($lastSubmission->submissions_submissions_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        $subNum = $prefix . $nextNumber;
        return $subNum;
    }
}
