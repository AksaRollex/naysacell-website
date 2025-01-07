<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    protected $fillable = ['email', 'otp', 'otp_expired_at'];
}
