<?php
$user = \App\Models\User::where('role', 'Manager')->first();
if (!$user) {
    echo "No managers found.\n";
    exit;
}

echo "Testing for Manager: " . $user->name . " (ID: " . $user->id . ")\n";

$request = new \Illuminate\Http\Request([
    'month' => 'all',
    'brand' => 'all',
    'manager' => 'all',
    'department' => 'all',
    'stage' => 'all',
    'customer' => 'all'
]);

// Simulate login
auth()->login($user);

// Run the dashboard logic
app()->make(\Illuminate\Http\Request::class)->merge($request->all());
$controller = app()->make(\App\Http\Controllers\DashboardController::class);
$response = $controller->index(request());
$viewData = $response->getData();

echo "Pending Payments: " . $viewData['pendingPayments'] . "\n";
echo "Ongoing Deals Count: " . $viewData['ongoingDealsCount'] . "\n";
echo "Ongoing Deals Value: " . $viewData['ongoingDealsValue'] . "\n";
echo "Deals Progress:\n";
print_r($viewData['dealsProgress']);
echo "Manager Customers (Filter):\n";
print_r($viewData['managerCustomers']);
