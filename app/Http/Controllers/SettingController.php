<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SeniorManager;
use App\Models\SystemCurrency;
use App\Models\StandardTerm;
use App\Models\Target;
use App\Models\User;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class SettingController extends Controller
{

    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $managers = SeniorManager::all();
        $terms = StandardTerm::all();
        $currencies = SystemCurrency::all();
        $expenseCategories = ExpenseCategory::all();
        
        $departmentTargets = Target::where('type', 'department')->get()->keyBy('department');
        $userTargets = Target::where('type', 'user')->get()->keyBy('user_id');
        $users = User::all();

        return view('settings.index', compact('settings', 'managers', 'terms', 'currencies', 'expenseCategories', 'departmentTargets', 'userTargets', 'users'));
    }

    public function updateDepartmentTargets(Request $request)
    {
        $request->validate([
            'targets' => 'array',
            'targets.*' => 'numeric|min:0'
        ]);

        if ($request->has('targets')) {
            foreach ($request->targets as $department => $amount) {
                Target::updateOrCreate(
                    ['type' => 'department', 'department' => $department],
                    ['target_amount' => $amount]
                );
            }
        }

        return redirect()->route('settings.index')->with('success', 'Department targets updated successfully.');
    }

    public function updateUserTargets(Request $request)
    {
        $request->validate([
            'targets' => 'array',
            'targets.*' => 'numeric|min:0'
        ]);

        if ($request->has('targets')) {
            foreach ($request->targets as $userId => $amount) {
                Target::updateOrCreate(
                    ['type' => 'user', 'user_id' => $userId],
                    ['target_amount' => $amount]
                );
            }
        }

        return redirect()->route('settings.index')->with('success', 'User targets updated successfully.');
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'General settings updated successfully.');
    }

    public function updateTax(Request $request)
    {
        $request->validate([
            'sscl_rate' => 'required|numeric|min:0',
            'vat_rate' => 'required|numeric|min:0',
        ]);

        Setting::set('sscl_rate', $request->sscl_rate, 'tax');
        Setting::set('vat_rate', $request->vat_rate, 'tax');

        return redirect()->route('settings.index')->with('success', 'Tax settings updated successfully.');
    }

    public function storeManager(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
        ]);

        SeniorManager::create($request->only('name', 'designation'));

        return redirect()->route('settings.index')->with('success', 'Senior manager added successfully.');
    }

    public function destroyManager(SeniorManager $manager)
    {
        try {
            $manager->delete();
            return redirect()->route('settings.index')->with('success', 'Senior manager removed successfully.');
        } catch (\Exception $e) {
            return redirect()->route('settings.index')->with('error', 'Cannot delete manager. It may be in use.');
        }
    }

    public function updateManager(Request $request, SeniorManager $manager)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
        ]);

        $manager->update($request->only('name', 'designation'));

        return redirect()->route('settings.index')->with('success', 'Senior manager updated successfully.');
    }

    public function storeTerm(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        StandardTerm::create($request->only('content'));

        return redirect()->route('settings.index')->with('success', 'Standard term added successfully.');
    }

    public function destroyTerm(StandardTerm $term)
    {
        try {
            $term->delete();
            return redirect()->route('settings.index')->with('success', 'Standard term removed successfully.');
        } catch (\Exception $e) {
            return redirect()->route('settings.index')->with('error', 'Cannot delete term. It may be in use.');
        }
    }

    public function updateTerm(Request $request, StandardTerm $term)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $term->update($request->only('content'));

        return redirect()->route('settings.index')->with('success', 'Standard term updated successfully.');
    }
    public function storeCurrency(Request $request)
    {
        // Role Check (Super Admin only)
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'code' => 'required|string|max:3|unique:system_currencies,code',
            'name' => 'nullable|string|max:255',
            'symbol' => 'nullable|string|max:10',
        ]);

        \App\Models\SystemCurrency::create($request->only('code', 'name', 'symbol'));

        return redirect()->route('settings.index')->with('success', 'Currency added successfully.');
    }

    public function destroyCurrency(\App\Models\SystemCurrency $currency)
    {
        // Debugging
        // dd('Arrived at destroyCurrency', $currency);

        // Role Check (Super Admin only)
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $currency->delete();
            return redirect()->route('settings.index')->with('success', 'Currency removed successfully.');
        } catch (\Exception $e) {
            return redirect()->route('settings.index')->with('error', 'Cannot delete currency. It may be in use.');
        }
    }

    public function updateCurrency(Request $request, \App\Models\SystemCurrency $currency)
    {
        // Role Check (Super Admin only)
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'code' => 'required|string|max:3|unique:system_currencies,code,' . $currency->id,
            'name' => 'nullable|string|max:255',
            'symbol' => 'nullable|string|max:10',
        ]);

        $currency->update($request->only('code', 'name', 'symbol'));

        return redirect()->route('settings.index')->with('success', 'Currency updated successfully.');
    }

    public function updateMaintenance(Request $request)
    {
        // Role Check (Super Admin only)
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'maintenance_mode' => 'required|in:0,1',
        ]);

        Setting::set('maintenance_mode', $request->maintenance_mode, 'system');

        return redirect()->route('settings.index')->with('success', 'Maintenance mode status updated successfully.');
    }

    public function storeExpenseCategory(Request $request)
    {
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('settings.index')->with('success', 'Expense category created successfully.');
    }

    public function updateExpenseCategory(Request $request, ExpenseCategory $expenseCategory)
    {
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $expenseCategory->update($request->only('name', 'description', 'status'));

        return redirect()->route('settings.index')->with('success', 'Expense category updated successfully.');
    }

    public function destroyExpenseCategory(ExpenseCategory $expenseCategory)
    {
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $expenseCategory->delete();
            return redirect()->route('settings.index')->with('success', 'Expense category removed successfully.');
        } catch (\Exception $e) {
            return redirect()->route('settings.index')->with('error', 'Cannot delete expense category.');
        }
    }
}
