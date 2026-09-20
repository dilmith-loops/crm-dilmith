<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\PettyCashRequest;

class HodResolutionTest extends TestCase
{
    public function test_user_without_assigned_hod_returns_not_assigned(): void
    {
        $user = new User([
            'name' => 'Test User',
            'role' => 'Staff',
            'department' => 'Tech',
        ]);

        $this->assertNull($user->associated_hod);
        $this->assertEquals('Not Assigned', $user->hod_name);
    }

    public function test_user_with_assigned_manager_returns_manager_as_hod(): void
    {
        $manager = new User([
            'id' => 101,
            'name' => 'Manager Supervisor',
            'role' => 'Manager',
            'department' => 'Tech',
        ]);
        $manager->id = 101;

        $user = new User([
            'name' => 'Test User',
            'role' => 'Staff',
            'department' => 'Tech',
            'supervisor_id' => 101,
        ]);
        $user->setRelation('supervisor', $manager);

        $this->assertNotNull($user->associated_hod);
        $this->assertEquals(101, $user->associated_hod->id);
        $this->assertEquals('Manager Supervisor', $user->hod_name);
    }

    public function test_user_with_assigned_staff_returns_staff_as_hod(): void
    {
        $staffLead = new User([
            'id' => 102,
            'name' => 'Staff Lead',
            'role' => 'Staff',
            'department' => 'Creative',
        ]);
        $staffLead->id = 102;

        $user = new User([
            'name' => 'Junior Staff',
            'role' => 'Staff',
            'department' => 'Creative',
            'supervisor_id' => 102,
        ]);
        $user->setRelation('supervisor', $staffLead);

        $this->assertNotNull($user->associated_hod);
        $this->assertEquals(102, $user->associated_hod->id);
        $this->assertEquals('Staff Lead', $user->hod_name);
    }

    public function test_petty_cash_request_associated_hod_uses_request_hod_first(): void
    {
        $hodUser = new User([
            'id' => 201,
            'name' => 'Any Assigned HOD',
            'role' => 'Finance Admin',
        ]);
        $hodUser->id = 201;

        $request = new PettyCashRequest([
            'hod_id' => 201,
        ]);
        $request->setRelation('hod', $hodUser);

        $this->assertNotNull($request->associated_hod);
        $this->assertEquals('Any Assigned HOD', $request->associated_hod->name);
    }

    public function test_user_role_helpers_for_finance_and_management(): void
    {
        $mgmt = new User(['name' => 'Samantha', 'role' => 'Management']);
        $this->assertTrue($mgmt->isManagement());
        $this->assertFalse($mgmt->isFinanceAdmin());

        $finance = new User(['name' => 'Finance Person', 'role' => 'Finance Admin']);
        $this->assertFalse($finance->isManagement());
        $this->assertTrue($finance->isFinanceAdmin());

        $superAdmin = new User(['name' => 'Super Admin', 'role' => 'Super Admin']);
        $this->assertFalse($superAdmin->isManagement());
        $this->assertTrue($superAdmin->isFinanceAdmin());
    }
}
