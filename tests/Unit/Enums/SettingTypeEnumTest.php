<?php

declare(strict_types=1);

use App\Enums\SettingTypeEnum;

it('return all setting types', function (): void {
    expect(SettingTypeEnum::cases())->toBeArray();
});

it('setting type label', function (): void {
    $value1 = 'String';
    $value2 = 'Number';
    $value3 = 'Boolean';
    $value4 = 'JSON';
    $value5 = 'Array';
    $value6 = 'File';
    expect(SettingTypeEnum::STRING->label())->toBe($value1)
        ->and(SettingTypeEnum::NUMBER->label())->toBe($value2)
        ->and(SettingTypeEnum::BOOLEAN->label())->toBe($value3)
        ->and(SettingTypeEnum::JSON->label())->toBe($value4)
        ->and(SettingTypeEnum::ARRAY->label())->toBe($value5)
        ->and(SettingTypeEnum::FILE->label())->toBe($value6);
});

it('setting type to array', function (): void {
    expect(SettingTypeEnum::toArray())->toBeArray();
});

it('setting type cast value', function (): void {
    expect(SettingTypeEnum::STRING->castValue('test'))->toBe('test')
        ->and(SettingTypeEnum::NUMBER->castValue(123))->toBe(123.0)
        ->and(SettingTypeEnum::BOOLEAN->castValue(true))->toBeTrue()
        ->and(SettingTypeEnum::JSON->castValue(['key' => 'value']))->toBe(['key' => 'value'])
        ->and(SettingTypeEnum::ARRAY->castValue(['item1', 'item2']))->toBe(['item1', 'item2'])
        ->and(SettingTypeEnum::FILE->castValue('file_path'))->toBe('file_path');
});

it('returns string as-is', function (): void {
    expect(SettingTypeEnum::STRING->castValue('hello'))
        ->toBe('hello');
});

it('converts array to JSON string', function (): void {
    $array = ['key' => 'value', 'nested' => ['data']];
    $result = SettingTypeEnum::STRING->castValue($array);

    expect($result)
        ->toBeString()
        ->toBe(json_encode($array));
});

it('converts number to string', function (): void {
    expect(SettingTypeEnum::STRING->castValue(123))
        ->toBe('123')
        ->and(SettingTypeEnum::STRING->castValue(45.67))
        ->toBe('45.67');
});

it('converts boolean to string', function (): void {
    expect(SettingTypeEnum::STRING->castValue(true))
        ->toBe('1')
        ->and(SettingTypeEnum::STRING->castValue(false))
        ->toBe('');
});

it('converts null to empty string', function (): void {
    expect(SettingTypeEnum::STRING->castValue(null))
        ->toBe('');
});

it('throws on invalid JSON encoding', function (): void {
    $invalid = ["\xB1\x31"]; // Invalid UTF-8
    SettingTypeEnum::STRING->castValue($invalid);
})->throws(JsonException::class);

it('returns integer as float', function (): void {
    expect(SettingTypeEnum::NUMBER->castValue(42))
        ->toBe(42.0)
        ->toBeFloat();
});

it('returns float as-is', function (): void {
    expect(SettingTypeEnum::NUMBER->castValue(3.14))
        ->toBe(3.14);
});

it('converts numeric string to float', function (): void {
    expect(SettingTypeEnum::NUMBER->castValue('123.45'))
        ->toBe(123.45)
        ->and(SettingTypeEnum::NUMBER->castValue('99'))
        ->toBe(99.0);
});

it('returns 0.0 for non-numeric values', function (): void {
    expect(SettingTypeEnum::NUMBER->castValue('not a number'))
        ->toBe(0.0)
        ->and(SettingTypeEnum::NUMBER->castValue([]))
        ->toBe(0.0)
        ->and(SettingTypeEnum::NUMBER->castValue(null))
        ->toBe(0.0);
});

it('handles boolean as number', function (): void {
    expect(SettingTypeEnum::NUMBER->castValue(true))
        ->toBe(0.0)
        ->and(SettingTypeEnum::NUMBER->castValue(false))
        ->toBe(0.0);
});

it('returns true for truthy values', function (): void {
    expect(SettingTypeEnum::BOOLEAN->castValue(true))
        ->toBeTrue()
        ->and(SettingTypeEnum::BOOLEAN->castValue(1))
        ->toBeTrue()
        ->and(SettingTypeEnum::BOOLEAN->castValue('yes'))
        ->toBeTrue()
        ->and(SettingTypeEnum::BOOLEAN->castValue([1, 2]))
        ->toBeTrue();
});

it('returns false for falsy values', function (): void {
    expect(SettingTypeEnum::BOOLEAN->castValue(false))
        ->toBeFalse()
        ->and(SettingTypeEnum::BOOLEAN->castValue(0))
        ->toBeFalse()
        ->and(SettingTypeEnum::BOOLEAN->castValue(''))
        ->toBeFalse()
        ->and(SettingTypeEnum::BOOLEAN->castValue([]))
        ->toBeFalse()
        ->and(SettingTypeEnum::BOOLEAN->castValue(null))
        ->toBeFalse();
});

it('decodes JSON string to array', function (): void {
    $json = '{"name":"John","age":30}';
    $result = SettingTypeEnum::JSON->castValue($json);

    expect($result)
        ->toBeArray()
        ->toBe(['name' => 'John', 'age' => 30]);
});

it('returns array as-is', function (): void {
    $array = ['foo' => 'bar', 'baz' => [1, 2, 3]];

    expect(SettingTypeEnum::JSON->castValue($array))
        ->toBe($array);
});

it('returns empty array for non-array/non-string', function (): void {
    expect(SettingTypeEnum::JSON->castValue(123))
        ->toBe([])
        ->and(SettingTypeEnum::JSON->castValue(true))
        ->toBe([])
        ->and(SettingTypeEnum::JSON->castValue(null))
        ->toBe([]);
});

it('throws on invalid JSON string', function (): void {
    SettingTypeEnum::JSON->castValue('{"invalid": json}');
})->throws(JsonException::class);

it('handles nested JSON structures', function (): void {
    $json = '{"user":{"name":"Jane","roles":["admin","editor"]}}';
    $result = SettingTypeEnum::JSON->castValue($json);

    expect($result)
        ->toHaveKey('user')
        ->and($result['user'])
        ->toHaveKey('roles')
        ->and($result['user']['roles'])
        ->toBe(['admin', 'editor']);
});

it('returns empty array for non-array values', function (): void {
    expect(SettingTypeEnum::ARRAY->castValue('string'))
        ->toBe([])
        ->and(SettingTypeEnum::ARRAY->castValue(123))
        ->toBe([])
        ->and(SettingTypeEnum::ARRAY->castValue(true))
        ->toBe([])
        ->and(SettingTypeEnum::ARRAY->castValue(null))
        ->toBe([]);
});

it('returns empty string for non-string values', function (): void {
    expect(SettingTypeEnum::FILE->castValue([]))
        ->toBe('')
        ->and(SettingTypeEnum::FILE->castValue(123))
        ->toBe('')
        ->and(SettingTypeEnum::FILE->castValue(null))
        ->toBe('');
});
