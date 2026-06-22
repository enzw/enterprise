<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Auth;

class SalesOrderObserver
{
    /**
     * Handle the SalesOrder "created" event.
     */
    public function created(SalesOrder $salesOrder)
    {
        $this->log($salesOrder, 'created');
    }

    /**
     * Handle the SalesOrder "updating" event.
     */
    public function updating(SalesOrder $salesOrder)
    {
        $original = $salesOrder->getOriginal();
        $new = $salesOrder->getAttributes();
        $event = 'updated';
        if (array_key_exists('status', $salesOrder->getChanges())) {
            $event = $original['status'] . '_to_' . $salesOrder->status;
        }
        $this->log($salesOrder, $event, $original, $new);
    }

    protected function log(SalesOrder $model, string $event, $old = null, $new = null)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'event' => $event,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
        ]);
    }
}
