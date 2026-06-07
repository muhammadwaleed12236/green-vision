<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceNotification extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'attendance_date' => 'date',
        'dismissed_until' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Salesman::class, 'staff_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_or_user_id', 'id');
    }

    // Check if this notification should be shown
    public function shouldShow()
    {
        // If dismissed_until is in future, don't show
        if ($this->dismissed_until && now() < $this->dismissed_until) {
            return false;
        }

        // If attendance is already marked, don't show
        $attendance = StaffAttendence::where([
            'staff_id' => $this->staff_id,
            'attendence_date' => $this->attendance_date,
            'admin_or_user_id' => $this->admin_or_user_id,
        ])->first();

        return !$attendance;
    }
}
