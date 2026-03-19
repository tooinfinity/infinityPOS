<?php

declare(strict_types=1);

use App\Exceptions\MediaUploadException;

describe(MediaUploadException::class, function (): void {
    it('creates file too large exception', function (): void {
        $exception = MediaUploadException::fileTooLarge('large-image.jpg');

        expect($exception)->toBeInstanceOf(MediaUploadException::class)
            ->and($exception->getMessage())
            ->toBe('File [large-image.jpg] exceeds the allowed size limit.');
    });

    it('creates unsupported type exception', function (): void {
        $exception = MediaUploadException::unsupportedType('application/exe');

        expect($exception)->toBeInstanceOf(MediaUploadException::class)
            ->and($exception->getMessage())
            ->toBe('File type [application/exe] is not supported for this resource.');
    });

    it('creates storage failed exception', function (): void {
        $exception = MediaUploadException::storageFailed('disk full');

        expect($exception)->toBeInstanceOf(MediaUploadException::class)
            ->and($exception->getMessage())
            ->toBe('Media storage failed: disk full');
    });

    it('creates not found exception', function (): void {
        $exception = MediaUploadException::notFound();

        expect($exception)->toBeInstanceOf(MediaUploadException::class)
            ->and($exception->getMessage())
            ->toBe('No media found for this resource.');
    });

    describe('render', function (): void {
        it('renders json response with message', function (): void {
            $exception = MediaUploadException::fileTooLarge('test.jpg');
            $response = $exception->render();

            expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class)
                ->and($response->getStatusCode())->toBe(422)
                ->and($response->getData(true))->toBe([
                    'message' => 'File [test.jpg] exceeds the allowed size limit.',
                ]);
        });

        it('renders json response for different exception types', function (): void {
            $exception = MediaUploadException::notFound();
            $response = $exception->render();

            expect($response->getData(true))->toBe([
                'message' => 'No media found for this resource.',
            ]);
        });
    });
});
