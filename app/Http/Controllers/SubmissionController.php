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

        $submissions = $query->orderBy('submissions_create_date', 'desc')->get();

        return view('submissions.index', compact('submissions', 'role'));
    }

    public function create()
    {
        $categories = Category::where('categories_status', 1)->get();
        return view('submissions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Sanitize rupiah-formatted amount
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

        // --- Cek Budget (Kondisi 4) ---
        $budget      = Budget::where('budgets_categories_uuid', $category->categories_uuid)
            ->where('budgets_period_year', $currentYear)
            ->first();

        $status       = 1;
        $rejectReason = null;

        if (!$budget) {
            $status       = 8; 
            $rejectReason = 'Budget untuk kategori "' . $category->categories_name . '" belum diatur untuk tahun ' . $currentYear . '.';
        } else {
            $availableBudget = $budget->budgets_total_budget - $budget->budgets_used_budget;
            if ($amount > $availableBudget) {
                $status       = 8;
                $rejectReason = 'Budget kategori tidak mencukupi. Tersedia: Rp ' . number_format($availableBudget, 0, ',', '.') . '.';
            } else {
                if (stripos($category->categories_name, 'po produk') !== false) {
                    $status = 5;
                } else {
                    $status = 3;
                }
            }
        }

        $count  = Submission::whereDate('submissions_date', date('Y-m-d'))->count();
        $subNum = 'SUB-' . date('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $submission = Submission::create([
                'submissions_category_uuid'       => $category->categories_uuid,
                'submissions_submissions_number'  => $subNum,
                'submissions_user_uuid'           => Session::get('users_uuid'),
                'submissions_date'                => date('Y-m-d'),
                'submissions_amount'              => $amount,
                'submissions_description'         => $request->description,
                'submissions_status'              => $status,
                'submissions_reject_reason'       => $rejectReason,
                'submissions_create_by'           => Session::get('users_uuid'),
            ]);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                    $path     = $file->storeAs('public/submissions', $filename);

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

            DB::commit();

            if ($status === 8) {
                return response()->json([
                    'status'   => false,
                    'message'  => 'Pengajuan dibuat namun langsung DITOLAK: ' . $rejectReason,
                    'redirect' => route('submissions.index')
                ]);
            }

            $statusLabels = [
                3 => 'Menunggu Approval SPV',
                5 => 'Menunggu Approval Direktur',
            ];

            return response()->json([
                'status'   => true,
                'message'  => 'Pengajuan berhasil dibuat. Status: ' . ($statusLabels[$status] ?? 'Submitted'),
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

        return view('submissions.show', compact('submission'));
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

        return view('submissions.history', compact('submissions', 'role'));
    }

    public function dashboard()
    {
        $role = Session::get('roles_code');
        $uuid = Session::get('users_uuid');

        $totalQuery = Submission::query();
        $processQuery = Submission::whereIn('submissions_status', [3, 4, 5, 6]);
        $successQuery = Submission::where('submissions_status', 7);
        $rejectQuery = Submission::where('submissions_status', 8);

        $recentSubmissionsQuery = Submission::with('category', 'user');

        if ($role === 'staff') {
            $totalQuery->where('submissions_user_uuid', $uuid);
            $processQuery->where('submissions_user_uuid', $uuid);
            $successQuery->where('submissions_user_uuid', $uuid);
            $rejectQuery->where('submissions_user_uuid', $uuid);
            $recentSubmissionsQuery->where('submissions_user_uuid', $uuid);
        }

        $stats = [
            'total' => $totalQuery->count(),
            'process' => $processQuery->count(),
            'success' => $successQuery->count(),
            'reject' => $rejectQuery->count(),
        ];

        $recentSubmissions = $recentSubmissionsQuery->orderBy('submissions_create_date', 'desc')->limit(5)->get();

        $recentActivityQuery = \App\Models\Approval::with('user', 'role', 'submission')
            ->orderBy('approvals_create_date', 'desc');

        if ($role === 'staff') {
            $recentActivityQuery->whereHas('submission', function ($q) use ($uuid) {
                $q->where('submissions_user_uuid', $uuid);
            });
        }

        $recentActivity = $recentActivityQuery->limit(5)->get();

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

        $filename = 'transaksi_pengajuan_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($submissions, $statusMap) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No. Pengajuan',
                'Tanggal',
                'Pengaju',
                'Kategori',
                'Nilai (Rp)',
                'Status'
            ], ';');

            foreach ($submissions as $sub) {
                fputcsv($file, [
                    $sub->submissions_submissions_number,
                    date('d/m/Y', strtotime($sub->submissions_date)),
                    $sub->user->users_user_name ?? '-',
                    $sub->category->categories_name ?? '-',
                    number_format($sub->submissions_amount, 0, ',', '.'),
                    $statusMap[$sub->submissions_status] ?? 'Unknown',
                ], ';');
            }

            fclose($file);
        };

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

        return $pdf->stream('pengajuan_' . $submission->submissions_submissions_number . '.pdf');
    }
}
