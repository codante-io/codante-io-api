<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{   
    use CrudTrait;
    use SoftDeletes;

    protected $table = 'backups';
    protected $fillable = ['id', 'name', 'size_gb', 'number_of_files', 'created_at', 'details'];

    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Challenge', 'backup_challenge', 'backup_id', 'challenge_id');
    }

    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Workshop', 'backup_workshop', 'backup_id', 'workshop_id');
    }

    public function disks(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\BackupDisk', 'backups_backup_disk', 'backup_id', 'backup_disk_id');
    }
}
