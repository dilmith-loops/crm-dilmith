<?php
use App\Models\InvoiceItem;
use App\Models\EstimateItem;

$ii = InvoiceItem::whereNull('department')->get();
foreach ($ii as $item) {
    if (!$item->invoice) continue;
    $estItem = EstimateItem::where('quotation_id', $item->invoice->quotation_id)
        ->where('description', $item->description)
        ->where('amount', $item->amount)
        ->first();
    if ($estItem) {
        $item->update([
            'department' => $estItem->department,
            'revenue_category' => $estItem->revenue_category
        ]);
    }
}
