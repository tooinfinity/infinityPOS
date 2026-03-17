<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ReferenceCounter extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'last_value',
    ];
}
