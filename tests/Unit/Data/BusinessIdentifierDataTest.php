<?php

declare(strict_types=1);

use App\Data\BusinessIdentifierData;
use App\Models\BusinessIdentifier;

it('transforms a business identifier model into BusinessIdentifierData', function () {

    /** @var BusinessIdentifier $identifier */
    $identifier = BusinessIdentifier::factory()->create([
        'article' => 'ART-123',
        'nif' => 'NIF-456',
        'nis' => 'NIS-789',
        'rc' => 'RC-111',
        'rib' => 'RIB-222',
    ]);

    $data = BusinessIdentifierData::fromModel($identifier);

    expect($data)
        ->toBeInstanceOf(BusinessIdentifierData::class)
        ->id->toBe($identifier->id)
        ->article->toBe('ART-123')
        ->nif->toBe('NIF-456')
        ->nis->toBe('NIS-789')
        ->rc->toBe('RC-111')
        ->rib->toBe('RIB-222')
        ->and($data->created_at->toDateTimeString())
        ->toBe($identifier->created_at->toDateTimeString())
        ->and($data->updated_at->toDateTimeString())
        ->toBe($identifier->updated_at->toDateTimeString());
});
