<?php

declare(strict_types=1);

use App\Http\Requests\UploadBrandLogoRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

beforeEach(function (): void {
    $this->request = new UploadBrandLogoRequest;
});

describe('authorization', function (): void {
    it('is always authorized', function (): void {
        expect($this->request->authorize())->toBeTrue();
    });
});

describe('validation rules', function (): void {
    it('requires logo field', function (): void {
        $rules = $this->request->rules();

        expect($rules)->toHaveKey('logo')
            ->and($rules['logo'])->toBeArray()
            ->and($rules['logo'])->toContain('required');
    });

    it('validates logo is a file', function (): void {
        $validator = Validator::make(
            ['logo' => 'not-a-file'],
            $this->request->rules()
        );

        expect($validator->fails())->toBeTrue();
    });

    it('accepts valid image files', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts jpg files', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts jpeg files', function (): void {
        $file = UploadedFile::fake()->image('logo.jpeg');

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts png files', function (): void {
        $file = UploadedFile::fake()->image('logo.png');

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts webp files', function (): void {
        $file = UploadedFile::fake()->image('logo.webp');

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('rejects unsupported file types', function (): void {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('logo'))->toBeTrue();
    });

    it('rejects files larger than 2MB', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg')->size(2049);

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('logo'))->toBeTrue();
    });

    it('accepts files at exactly 2MB', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg')->size(2048);

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts files smaller than 2MB', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg')->size(1024);

        $validator = Validator::make(
            ['logo' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('custom error messages', function (): void {
    it('provides custom message for required logo', function (): void {
        $messages = $this->request->messages();

        expect($messages)->toHaveKey('logo.required')
            ->and($messages['logo.required'])->toBe('A logo file is required.');
    });

    it('provides custom message for max size', function (): void {
        $messages = $this->request->messages();

        expect($messages)->toHaveKey('logo.max')
            ->and($messages['logo.max'])->toBe('The logo must not exceed 2MB.');
    });

    it('provides custom message for mime types', function (): void {
        $messages = $this->request->messages();

        expect($messages)->toHaveKey('logo.mimes')
            ->and($messages['logo.mimes'])->toBe('The logo must be a JPG, PNG, or WebP image.');
    });
});
