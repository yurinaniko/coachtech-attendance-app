<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdminUser;


class ApprovalLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'admin_user_id',
        'stamp_correction_request_id',
        'approved_at',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
