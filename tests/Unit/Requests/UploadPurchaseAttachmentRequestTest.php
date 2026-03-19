<?php

declare(strict_types=1);

use App\Http\Requests\UploadPurchaseAttachmentRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

beforeEach(function (): void {
    $this->request = new UploadPurchaseAttachmentRequest;
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

    it('accepts jpg files', function (): void {
        $file = UploadedFile::fake()->image('attachment.jpg');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts jpeg files', function (): void {
        $file = UploadedFile::fake()->image('attachment.jpeg');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts png files', function (): void {
        $file = UploadedFile::fake()->image('attachment.png');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts webp files', function (): void {
        $file = UploadedFile::fake()->image('attachment.webp');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts pdf files', function (): void {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts doc files', function (): void {
        $file = UploadedFile::fake()->create('document.doc', 100, 'application/msword');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts docx files', function (): void {
        $file = UploadedFile::fake()->create('document.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('rejects unsupported file types', function (): void {
        $file = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('file'))->toBeTrue();
    });

    it('rejects files larger than 20MB', function (): void {
        $file = UploadedFile::fake()->create('document.pdf', 20481, 'application/pdf');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('file'))->toBeTrue();
    });

    it('accepts files at exactly 20MB', function (): void {
        $file = UploadedFile::fake()->create('document.pdf', 20480, 'application/pdf');

        $validator = Validator::make(
            ['file' => $file],
            $this->request->rules()
        );

        expect($validator->passes())->toBeTrue();
    });

    it('accepts files smaller than 20MB', function (): void {
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

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
            ->and($messages['file.required'])->toBe('An attachment file is required.');
    });

    it('provides custom message for max size', function (): void {
        $messages = $this->request->messages();

        expect($messages)->toHaveKey('file.max')
            ->and($messages['file.max'])->toBe('The attachment must not exceed 20MB.');
    });

    it('provides custom message for mime types', function (): void {
        $messages = $this->request->messages();

        expect($messages)->toHaveKey('file.mimes')
            ->and($messages['file.mimes'])->toBe('The attachment must be an image (JPG, PNG, WebP), PDF, or Word document (DOC, DOCX).');
    });
});
