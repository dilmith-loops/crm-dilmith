<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogsActivity;

class UserController extends Controller
{
    use LogsActivity;


    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $users = User::all();
        return view('users.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:Super Admin,Management,HOD,Manager,Staff',
            'supervisor_id' => 'nullable|exists:users,id',
            'department' => 'nullable|string|in:Creative,Digital,Tech,AM,BD,PM,Corporate',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'supervisor_id' => $request->supervisor_id,
            'department' => $request->department,
        ]);

        $this->logAction("Created user: {$user->name}", $user);


        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $users = User::where('id', '!=', $user->id)->get();
        return view('users.edit', compact('user', 'users'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:Super Admin,Management,HOD,Manager,Staff',
            'supervisor_id' => 'nullable|exists:users,id',
            'department' => 'nullable|string|in:Creative,Digital,Tech,AM,BD,PM,Corporate',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->supervisor_id = $request->supervisor_id;
        $user->department = $request->department;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        $this->logAction("Updated user: {$user->name}", $user);


        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete yourself.');
        }

        $name = $user->name;
        $user->delete();
        $this->logAction("Deleted user: {$name}");

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_import_sample.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Role', 'Supervisor Email', 'Department', 'Password']);
            fputcsv($file, ['John Doe', 'john@example.com', 'Manager', 'jane@example.com', 'creative', 'password123']);
            fputcsv($file, ['Jane Smith', 'jane@example.com', 'Super Admin', '', '', 'secret']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        // Skip header row
        fgetcsv($handle);

        $successCount = 0;
        $errors = [];
        $rowNumber = 1; // Header is 0, but logical row 1 for user is row 2

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;
            // Expecting: Name, Email, Role, Supervisor Email, Department, Password
            if (count($data) < 3) {
                $errors[] = "Row $rowNumber: Insufficient data.";
                continue;
            }

            $name = $data[0] ?? null;
            $email = $data[1] ?? null;
            $role = $data[2] ?? 'Manager'; // Default
            $supervisorEmail = $data[3] ?? null;
            $department = $data[4] ?? null;
            $password = $data[5] ?? 'password';

            if (!$name || !$email) {
                $errors[] = "Row $rowNumber: Name or Email missing.";
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $errors[] = "Row $rowNumber: Email $email already exists.";
                continue;
            }

            // Lookup Supervisor
            $supervisorId = null;
            if ($supervisorEmail) {
                $supervisor = User::where('email', $supervisorEmail)->first();
                if ($supervisor) {
                    $supervisorId = $supervisor->id;
                } else {
                    $errors[] = "Row $rowNumber: Supervisor email ($supervisorEmail) not found. Created without supervisor.";
                }
            }

            try {
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'supervisor_id' => $supervisorId,
                    'department' => $department, // Use casing from CSV, matching validation
                    'password' => Hash::make($password),
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Row $rowNumber: Failed to create user. " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "Imported $successCount users successfully.";
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " errors occurred.";
            return redirect()->route('users.index')->with('success', $message)->with('import_errors', $errors);
        }

        return redirect()->route('users.index')->with('success', $message);
    }
}
