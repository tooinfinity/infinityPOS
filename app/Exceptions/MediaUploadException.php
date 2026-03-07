<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

final class MediaUploadException extends Exception
{
    public static function fileTooLarge(string $filename): self
    {
        return new self("File [$filename] exceeds the allowed size limit.");
    }

    public static function unsupportedType(string $type): self
    {
        return new self("File type [$type] is not supported for this resource.");
    }

    public static function storageFailed(string $reason): self
    {
        return new self("Media storage failed: $reason");
    }

    public static function notFound(): self
    {
        return new self('No media found for this resource.');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
