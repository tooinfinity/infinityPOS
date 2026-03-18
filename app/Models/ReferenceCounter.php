<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReferenceCounterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ReferenceCounter extends Model
{
    /** @use HasFactory<ReferenceCounterFactory> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'last_value',
    ];
}
