<?php

namespace App\Http\Controllers;

use App\Models\PettyCashRequest;
use App\Models\PettyCashItem;
use App\Models\PettyCashProof;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Deal;
use App\Notifications\PettyCashNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PettyCashController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $request->input('scope', 'my_requests');
        $query = PettyCashRequest::with(['user', 'hod', 'items.category', 'proofs']);

        if ($scope === 'my_requests') {
            // Show only the logged-in user's own requested petty cash requests
            $query->where('user_id', $user->id);
        } elseif ($scope === 'approvals') {
            if ($user->hasRole('super_admin') || $user->role === 'Management') {
                $query->whereIn('status', ['pending_hod', 'pending_super_admin']);
            } elseif ($user->role === 'HOD') {
                $query->where('hod_id', $user->id)->where('status', 'pending_hod');
            } else {
                $query->where('user_id', $user->id);
            }
        } elseif ($scope === 'all_team') {
            if ($user->role === 'Staff') {
                $query->where('user_id', $user->id);
            } elseif ($user->role === 'HOD') {
                $query->where(function ($q) use ($user) {
                    $q->where('hod_id', $user->id)
                      ->orWhere('department', $user->department)
                      ->orWhere('user_id', $user->id);
                });
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $pettyCashes = $query->orderBy('created_at', 'desc')->get();

        // Calculate counts for tabs
        $myRequestsCount = PettyCashRequest::where('user_id', $user->id)->count();
        $pendingApprovalsCount = 0;
        if ($user->hasRole('super_admin') || $user->role === 'Management') {
            $pendingApprovalsCount = PettyCashRequest::whereIn('status', ['pending_hod', 'pending_super_admin'])->count();
        } elseif ($user->role === 'HOD') {
            $pendingApprovalsCount = PettyCashRequest::where('hod_id', $user->id)->where('status', 'pending_hod')->count();
        }

        // Data for modals / dropdowns
        $expenseCategories = ExpenseCategory::where('status', 'active')->orderBy('name')->get();
        $hods = User::where('role', 'HOD');
        if ($user->department) {
            $hods->where('department', $user->department);
        }
        $hods = $hods->get();
        if ($hods->isEmpty()) {
            $hods = User::where('role', 'HOD')->get();
        }

        // Job Numbers for user's department
        $jobQuery = Deal::whereNotNull('job_number');
        if ($user->department) {
            $jobQuery->where(function ($q) use ($user) {
                $q->whereJsonContains('department_split', [['department' => $user->department]])
                  ->orWhereHas('owner', function ($oq) use ($user) {
                      $oq->where('department', $user->department);
                  });
            });
        }
        $jobs = $jobQuery->orderBy('job_number', 'desc')->pluck('job_number', 'job_number');

        return view('petty-cash.index', compact('pettyCashes', 'expenseCategories', 'hods', 'jobs', 'scope', 'myRequestsCount', 'pendingApprovalsCount'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'hod_id' => 'required|exists:users,id',
            'job_number' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.expense_category_id' => 'required|exists:expense_categories,id',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.description' => 'nullable|string',
            'proofs' => 'nullable|array',
            'proofs.*' => 'file|mimes:jpeg,png,jpg,pdf,doc,docx|max:10240',
        ]);

        $totalAmount = 0;
        $isIou = false;
        foreach ($request->items as $item) {
            $totalAmount += (float)$item['amount'];
            $category = ExpenseCategory::find($item['expense_category_id']);
            if ($category && stripos($category->name, 'IOU') !== false) {
                $isIou = true;
            }
        }

        $pettyCash = PettyCashRequest::create([
            'reference_number' => PettyCashRequest::generateReferenceNumber(),
            'user_id' => $user->id,
            'hod_id' => $request->hod_id,
            'department' => $user->department ?: 'General',
            'job_number' => $request->job_number,
            'total_amount' => $totalAmount,
            'is_iou' => $isIou,
            'status' => 'pending_hod',
        ]);

        // Save Items
        foreach ($request->items as $itemData) {
            PettyCashItem::create([
                'petty_cash_request_id' => $pettyCash->id,
                'expense_category_id' => $itemData['expense_category_id'],
                'amount' => $itemData['amount'],
                'description' => $itemData['description'] ?? null,
            ]);
        }

        // Handle Proof File Uploads
        if ($request->hasFile('proofs')) {
            foreach ($request->file('proofs') as $file) {
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $destinationPath = public_path('uploads/petty_cash_proofs');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
                $filePath = 'uploads/petty_cash_proofs/' . $filename;

                PettyCashProof::create([
                    'petty_cash_request_id' => $pettyCash->id,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        // Notify HOD
        $hod = User::find($request->hod_id);
        if ($hod) {
            $hod->notify(new PettyCashNotification($pettyCash, 'submitted', $user));
        }

        return redirect()->back()->with('success', 'Petty Cash request submitted successfully and sent to HOD for approval.');
    }

    public function show(PettyCashRequest $pettyCash)
    {
        $pettyCash->load(['user', 'hod', 'items.category', 'proofs']);
        return response()->json([
            'success' => true,
            'pettyCash' => $pettyCash
        ]);
    }

    public function hodApprove(PettyCashRequest $pettyCash)
    {
        $user = auth()->user();

        // Ensure user is assigned HOD or Super Admin
        if ($user->id !== $pettyCash->hod_id && !$user->hasRole('super_admin') && $user->role !== 'HOD') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $pettyCash->update([
            'status' => 'pending_super_admin',
        ]);

        // Notify Super Admins
        $superAdmins = User::where('role', 'Super Admin')->get();
        Notification::send($superAdmins, new PettyCashNotification($pettyCash, 'hod_approved', $user));

        return redirect()->back()->with('success', 'Petty Cash request approved and forwarded to Super Admin.');
    }

    public function hodReject(Request $request, PettyCashRequest $pettyCash)
    {
        $user = auth()->user();

        if ($user->id !== $pettyCash->hod_id && !$user->hasRole('super_admin') && $user->role !== 'HOD') {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'hod_rejection_note' => 'required|string',
        ]);

        $pettyCash->update([
            'status' => 'rejected_by_hod',
            'hod_rejection_note' => $request->hod_rejection_note,
        ]);

        // Notify Staff user
        if ($pettyCash->user) {
            $pettyCash->user->notify(new PettyCashNotification($pettyCash, 'hod_rejected', $user, $request->hod_rejection_note));
        }

        return redirect()->back()->with('success', 'Petty Cash request rejected. Staff has been notified.');
    }

    public function adminApprove(Request $request, PettyCashRequest $pettyCash)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['Super Admin', 'Management'])) {
            return redirect()->back()->with('error', 'Unauthorized action. Only Super Admin or Management can perform this action.');
        }

        $isIOU = $pettyCash->isIOU();

        // Validate mandatory signature for IOU
        if ($isIOU && (!$request->filled('signature') || !str_starts_with($request->signature, 'data:image/png;base64,'))) {
            return redirect()->back()->with('error', 'A signature from the requested person is MANDATORY for IOU approvals and settlements.');
        }

        $savedSignaturePath = null;
        if ($request->filled('signature') && str_starts_with($request->signature, 'data:image/png;base64,')) {
            $imageParts = explode(';base64,', $request->signature);
            if (isset($imageParts[1])) {
                $imageBase64 = base64_decode($imageParts[1]);
                $filename = 'sig_' . $pettyCash->id . '_' . time() . '.png';
                $destinationPath = public_path('uploads/petty_cash_signatures');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                file_put_contents($destinationPath . '/' . $filename, $imageBase64);
                $savedSignaturePath = 'uploads/petty_cash_signatures/' . $filename;
            }
        }

        if ($pettyCash->status === 'pending_settlement') {
            // Approval of IOU settlement
            $pettyCash->update([
                'status' => 'settled',
                'settlement_signature_path' => $savedSignaturePath ?: $pettyCash->settlement_signature_path,
                'settled_at' => now(),
            ]);

            if ($pettyCash->user) {
                $pettyCash->user->notify(new PettyCashNotification($pettyCash, 'admin_approved', $user));
            }
            return redirect()->back()->with('success', 'IOU Settlement has been APPROVED and marked as SETTLED.');
        }

        // Initial Super Admin approval (or money handed over for IOU)
        $newStatus = $isIOU ? 'iou_issued' : 'approved';

        $pettyCash->update([
            'status' => $newStatus,
            'signature_path' => $savedSignaturePath ?: $pettyCash->signature_path,
        ]);

        // Notify Staff & HOD
        if ($pettyCash->user) {
            $pettyCash->user->notify(new PettyCashNotification($pettyCash, 'admin_approved', $user));
        }
        if ($pettyCash->hod && $pettyCash->hod->id !== $user->id) {
            $pettyCash->hod->notify(new PettyCashNotification($pettyCash, 'admin_approved', $user));
        }

        $msg = $isIOU ? 'IOU Request APPROVED & Money Handed Over (Status: Unsettled IOU).' : 'Petty Cash request APPROVED successfully.';
        return redirect()->back()->with('success', $msg);
    }

    public function adminReject(Request $request, PettyCashRequest $pettyCash)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['Super Admin', 'Management'])) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'admin_rejection_note' => 'required|string',
        ]);

        $pettyCash->update([
            'status' => 'rejected_by_super_admin',
            'admin_rejection_note' => $request->admin_rejection_note,
        ]);

        // Notify BOTH Staff & HOD
        if ($pettyCash->user) {
            $pettyCash->user->notify(new PettyCashNotification($pettyCash, 'admin_rejected', $user, $request->admin_rejection_note));
        }
        if ($pettyCash->hod && $pettyCash->hod->id !== $user->id) {
            $pettyCash->hod->notify(new PettyCashNotification($pettyCash, 'admin_rejected', $user, $request->admin_rejection_note));
        }

        return redirect()->back()->with('success', 'Petty Cash request rejected by Super Admin. Staff and HOD have been notified.');
    }

    public function settleIOU(Request $request, PettyCashRequest $pettyCash)
    {
        $user = auth()->user();

        if ($user->id !== $pettyCash->user_id && !$user->hasRole('super_admin')) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'proofs' => 'nullable|array',
            'proofs.*' => 'file|mimes:jpeg,png,jpg,pdf,doc,docx|max:10240',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:petty_cash_items,id',
            'items.*.amount' => 'required|numeric|min:0.01',
        ]);

        // Update items/amounts if submitted
        if ($request->has('items')) {
            $totalAmount = 0;
            foreach ($request->items as $itemData) {
                $item = PettyCashItem::find($itemData['id']);
                if ($item) {
                    $item->update([
                        'amount' => $itemData['amount'],
                        'description' => $itemData['description'] ?? $item->description,
                    ]);
                    $totalAmount += (float)$itemData['amount'];
                }
            }
            if ($totalAmount > 0) {
                $pettyCash->update(['total_amount' => $totalAmount]);
            }
        }

        // Upload settlement proofs
        if ($request->hasFile('proofs')) {
            foreach ($request->file('proofs') as $file) {
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $destinationPath = public_path('uploads/petty_cash_proofs');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
                $filePath = 'uploads/petty_cash_proofs/' . $filename;

                PettyCashProof::create([
                    'petty_cash_request_id' => $pettyCash->id,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        $pettyCash->update([
            'status' => 'pending_settlement',
        ]);

        // Notify Super Admins
        $superAdmins = User::where('role', 'Super Admin')->get();
        Notification::send($superAdmins, new PettyCashNotification($pettyCash, 'submitted', $user));

        return redirect()->back()->with('success', 'IOU Settlement details and proofs submitted successfully. Pending Super Admin final approval.');
    }

    public function reappeal(Request $request, PettyCashRequest $pettyCash)
    {
        $user = auth()->user();

        // Staff or HOD can re-appeal
        if ($user->id !== $pettyCash->user_id && $user->id !== $pettyCash->hod_id && !$user->hasRole('super_admin')) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'hod_id' => 'required|exists:users,id',
            'job_number' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.expense_category_id' => 'required|exists:expense_categories,id',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.description' => 'nullable|string',
            'proofs' => 'nullable|array',
            'proofs.*' => 'file|mimes:jpeg,png,jpg,pdf,doc,docx|max:10240',
        ]);

        $totalAmount = 0;
        $isIou = false;
        foreach ($request->items as $item) {
            $totalAmount += (float)$item['amount'];
            $category = ExpenseCategory::find($item['expense_category_id']);
            if ($category && stripos($category->name, 'IOU') !== false) {
                $isIou = true;
            }
        }

        // Determine new status:
        // If rejected by HOD, resubmit to HOD -> pending_hod
        // If rejected by Super Admin and re-appealed by HOD, send to Super Admin -> pending_super_admin
        // If re-appealed by Staff, send back to HOD -> pending_hod
        $newStatus = ($user->id === $pettyCash->hod_id && $pettyCash->status === 'rejected_by_super_admin') 
                     ? 'pending_super_admin' 
                     : 'pending_hod';

        $pettyCash->update([
            'hod_id' => $request->hod_id,
            'job_number' => $request->job_number,
            'total_amount' => $totalAmount,
            'is_iou' => $isIou,
            'status' => $newStatus,
            'reappeal_count' => $pettyCash->reappeal_count + 1,
        ]);

        // Delete existing items and recreate
        $pettyCash->items()->delete();
        foreach ($request->items as $itemData) {
            PettyCashItem::create([
                'petty_cash_request_id' => $pettyCash->id,
                'expense_category_id' => $itemData['expense_category_id'],
                'amount' => $itemData['amount'],
                'description' => $itemData['description'] ?? null,
            ]);
        }

        // Add additional Proof File Uploads if provided
        if ($request->hasFile('proofs')) {
            foreach ($request->file('proofs') as $file) {
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $destinationPath = public_path('uploads/petty_cash_proofs');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
                $filePath = 'uploads/petty_cash_proofs/' . $filename;

                PettyCashProof::create([
                    'petty_cash_request_id' => $pettyCash->id,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        // Notify HOD or Super Admin based on new status
        if ($newStatus === 'pending_super_admin') {
            $superAdmins = User::where('role', 'Super Admin')->get();
            Notification::send($superAdmins, new PettyCashNotification($pettyCash, 'reappealed', $user));
        } else {
            $hod = User::find($request->hod_id);
            if ($hod) {
                $hod->notify(new PettyCashNotification($pettyCash, 'reappealed', $user));
            }
        }

        return redirect()->back()->with('success', 'Petty Cash request re-appealed and resubmitted successfully.');
    }
}
