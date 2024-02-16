<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    public function getAttachAttribute()
    {
        if ($this->fileAttachFile) {
            return $this->fileAttachFile->FileUrl;
        }
        return asset('assets/images/no-image.jpg');
    }

    public function fileAttachFile()
    {
        return $this->hasOne(FileManager::class, 'id', 'attach_id')->select('id', 'folder_name', 'file_name', 'origin_type', 'origin_id');
    }

    public function getInvoiceAttribute()
    {
        if ($this->fileAttachInvoice) {
            return $this->fileAttachInvoice->FileUrl;
        }
        return asset('assets/images/no-image.jpg');
    }

    public function fileAttachInvoice()
    {
        return $this->hasOne(FileManager::class, 'id', 'invoice_id')->select('id', 'folder_name', 'file_name', 'origin_type', 'origin_id');
    }
}
