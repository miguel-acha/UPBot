<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffContact extends Model
{
    protected $fillable = ['department_id','role','full_name','email','phone','office_hours'];
}
