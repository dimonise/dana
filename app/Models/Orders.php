<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Orders extends Model
{
    use HasFactory;

    public function index() {
        return Orders::paginate(50);
    }
    public function getAll(): BelongsTo {
        return $this->belongsTo(Details::class, 'detail_id', 'id');
    }

}
