<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountingMappingSettingAutoMigration extends Model
{
    use HasFactory;

    protected $fillable = ['name','type','status','payment_status','method','active','created_by'];
  
   
}