<?php

declare(strict_types=1);

use App\Http\Requests\UploadProductMediaRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

beforeEach(function (): void {
    $this->request = new UploadProductMediaRequest;
});

describe('authorization', function (): void {
    it('is always authorized', function (): void {
        expect($this->request->authorize())->toBeTrue();
    });
});

describe('validation rules', function (): void {
    it('requires file field', function (): void {
        $rules = $this->request->rules();

        expect($rules)->toHaveKey('file')
            ->and($rules['file'])->toBeArray()
            ->and($rules['file'])->toContain('required');
    });

    it('validates file is a file', function (): void {
        $validator = Validator::make(
            ['file' => 'not-a-file'],
            $this->request->rules()
        );

        expect($validator->fails())->toBeTrue();
    });

    it('accepts valid image files', function (): void {
        $file = UploadedFile::fake()->image('thumbnail.jpg');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts jpg files', function (): void {
        $file = UploadedFile::fake()->image('thumbnail.jpg');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts jpeg files', function (): void {
        $file = UploadedFile::fake()->image('thumbnail.jpeg');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts png files', function (): void {
        $file = UploadedFile::fake()->image('thumbnail.png');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts webp files', function (): void {
        $file = UploadedFile::fake()->image('thumbnail.webp');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('rejects unsupported file types', function (): void {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('file'))->toBeTrue();
    });

    it('rejects files larger than 5MB', function (): void {
        $file = UploadedFile::fake()->image('thumbnail.jpg')->size(5121);

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('file'))->toBeTrue();
    });

    it('accepts files at exactly 5MB', function (): void {
        $file = UploadedFile::fake()->image('thumbnail.jpg')->size(5120);

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts files smaller than 5MB', function (): void {
        $file = UploadedFile::fake()->image('thumbnail.jpg')->size(1024);

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });
});

describe('custom error messages', function (): void {
    it('provides custom message for required file', function (): void {
        $messages = $this->request->messages();

        expect($messages)->toHaveKey('file.required')
            ->and($messages['file.required'])->toBe('A thumbnail image is required.');
    });

    it('provides custom message for max size', function (): void {
        $messages = $this->request->messages();

        expect($messages)->toHaveKey('file.max')
            ->and($messages['file.max'])->toBe('The thumbnail must not exceed 5MB.');
    });

    it('provides custom message for mime types', function (): void {
        $messages = $this->request->messages();

        expect($messages)->toHaveKey('file.mimes')
            ->and($messages['file.mimes'])->toBe('The thumbnail must be a JPG, PNG, or WebP image.');
    });
});
