<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelPrintLog extends Model
{
    protected $fillable = [
        'label_type',
        'reference_id',
        'copies',
        'printed_by',
    ];

    protected $casts = [
        'copies' => 'integer',
    ];

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
