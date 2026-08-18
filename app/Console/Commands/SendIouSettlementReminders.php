<?php

namespace App\Console\Commands;

use App\Models\PettyCashRequest;
use App\Models\User;
use App\Notifications\PettyCashNotification;
use Illuminate\Console\Command;

class SendIouSettlementReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'petty-cash:send-iou-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to staff and HODs for unsettled IOUs (72-hour settlement policy)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $systemUser = User::where('role', 'Super Admin')->first() ?? new User(['name' => 'Loops Finance System']);

        // Find active unsettled IOUs
        $unsettledIous = PettyCashRequest::where('is_iou', true)
            ->whereIn('status', ['iou_issued', 'pending_settlement'])
            ->whereNotNull('issued_at')
            ->get();

        $count = 0;
        foreach ($unsettledIous as $pettyCash) {
            if ($pettyCash->user) {
                $pettyCash->user->notify(new PettyCashNotification($pettyCash, 'iou_reminder', $systemUser));
                $count++;
            }
            if ($pettyCash->hod && $pettyCash->hod->id !== ($pettyCash->user->id ?? 0)) {
                $pettyCash->hod->notify(new PettyCashNotification($pettyCash, 'iou_reminder', $systemUser));
            }
        }

        $this->info("Sent {$count} IOU settlement reminder notification(s).");
        return Command::SUCCESS;
    }
}
