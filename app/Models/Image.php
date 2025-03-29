<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'path',
        'original_filename',
        'mime_type',
        'size',
        'alt_text',
        'order',
        'is_primary'
    ];

    protected $casts = [
        'size' => 'integer',
        'order' => 'integer',
        'is_primary' => 'boolean'
    ];

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
