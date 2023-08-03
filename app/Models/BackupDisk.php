<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupDisk extends Model
{
    use CrudTrait;
    use SoftDeletes;

    protected $table = 'backup_disks';
    protected $fillable = ['id', 'name', 'size_gb', 'local'];

    public function backups(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Backup', 'backups_backup_disks', 'backup_id', 'backup_disk_id');
    }
}