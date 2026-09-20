<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\PettyCashRequest;
use Illuminate\Support\Facades\Blade;

class PettyCashManagementButtonsTest extends TestCase
{
    private string $bladeTemplate = <<<'BLADE'
@if(in_array($pc->status, ['pending_hod', 'pending_settlement_hod']) && (auth()->user()->id === $pc->hod_id || auth()->user()->isFinanceAdmin()))
    <form action="/hod-approve"><button>Accept</button></form>
    <button onclick="openHodRejectModal({{ $pc->id }})">Reject</button>
@endif

@if(auth()->user()->hasAdminPrivileges())
    @if($pc->status === 'pending_management')
        <button onclick="openManagementApproveModal({{ $pc->id }})">Approve</button>
        <button onclick="openManagementRejectModal({{ $pc->id }})">Reject</button>
    @elseif(auth()->user()->isFinanceAdmin())
        @if(!in_array($pc->status, ['approved', 'settled', 'rejected_by_management']))
            <button onclick="openAdminApproveModal({{ $pc->id }})">Approve</button>
            @if(empty($pc->management_approved_at))
                <button onclick="openSendToManagementModal({{ $pc->id }})">To Management</button>
            @endif
        @endif
        @if(!in_array($pc->status, ['rejected_by_super_admin', 'settled', 'approved', 'rejected_by_management']))
            <button onclick="openAdminRejectModal({{ $pc->id }})">Reject</button>
        @endif
    @endif
@endif
BLADE;

    public function test_management_user_does_not_see_finance_admin_approve_and_to_management(): void
    {
        $mgmtUser = new User([
            'name' => 'Samantha',
            'role' => 'Management',
        ]);
        $mgmtUser->id = 50;
        $this->actingAs($mgmtUser);

        // 1. Pending Finance Approval request
        $reqFinance = new PettyCashRequest([
            'id' => 1,
            'status' => 'pending_super_admin',
            'hod_id' => 999,
            'total_amount' => 1000,
        ]);

        $rendered = Blade::render($this->bladeTemplate, ['pc' => $reqFinance]);

        $this->assertStringNotContainsString('openAdminApproveModal', $rendered);
        $this->assertStringNotContainsString('openSendToManagementModal', $rendered);
        $this->assertStringNotContainsString('To Management', $rendered);
        $this->assertStringNotContainsString('openAdminRejectModal', $rendered);
        $this->assertStringNotContainsString('Accept', $rendered);

        // 2. Pending Management request
        $reqMgmt = new PettyCashRequest([
            'id' => 2,
            'status' => 'pending_management',
            'hod_id' => 999,
            'total_amount' => 5000,
        ]);

        $renderedMgmt = Blade::render($this->bladeTemplate, ['pc' => $reqMgmt]);

        $this->assertStringNotContainsString('openAdminApproveModal', $renderedMgmt);
        $this->assertStringNotContainsString('openSendToManagementModal', $renderedMgmt);
        $this->assertStringContainsString('openManagementApproveModal', $renderedMgmt);
        $this->assertStringContainsString('openManagementRejectModal', $renderedMgmt);

        // 3. Pending HOD where Samantha is assigned HOD
        $reqHodAssigned = new PettyCashRequest([
            'id' => 3,
            'status' => 'pending_hod',
            'hod_id' => 50,
            'total_amount' => 500,
        ]);

        $renderedHodAssigned = Blade::render($this->bladeTemplate, ['pc' => $reqHodAssigned]);

        $this->assertStringContainsString('Accept', $renderedHodAssigned);
        $this->assertStringContainsString('openHodRejectModal', $renderedHodAssigned);
        $this->assertStringNotContainsString('openAdminApproveModal', $renderedHodAssigned);
        $this->assertStringNotContainsString('openSendToManagementModal', $renderedHodAssigned);
        $this->assertStringNotContainsString('openAdminRejectModal', $renderedHodAssigned);

        // 4. Pending HOD where Samantha is NOT assigned HOD
        $reqHodOther = new PettyCashRequest([
            'id' => 4,
            'status' => 'pending_hod',
            'hod_id' => 999,
            'total_amount' => 500,
        ]);

        $renderedHodOther = Blade::render($this->bladeTemplate, ['pc' => $reqHodOther]);

        $this->assertStringNotContainsString('Accept', $renderedHodOther);
        $this->assertStringNotContainsString('openHodRejectModal', $renderedHodOther);
        $this->assertStringNotContainsString('openAdminApproveModal', $renderedHodOther);
        $this->assertStringNotContainsString('openSendToManagementModal', $renderedHodOther);
    }

    public function test_finance_admin_sees_admin_approve_and_to_management(): void
    {
        $financeUser = new User([
            'name' => 'Finance Admin',
            'role' => 'Finance Admin',
        ]);
        $financeUser->id = 10;
        $this->actingAs($financeUser);

        $req = new PettyCashRequest([
            'id' => 5,
            'status' => 'pending_super_admin',
            'hod_id' => 999,
            'total_amount' => 1200,
        ]);

        $rendered = Blade::render($this->bladeTemplate, ['pc' => $req]);

        $this->assertStringContainsString('openAdminApproveModal', $rendered);
        $this->assertStringContainsString('openSendToManagementModal', $rendered);
        $this->assertStringContainsString('To Management', $rendered);
        $this->assertStringContainsString('openAdminRejectModal', $rendered);
    }
}
