import { router } from '@inertiajs/react';
import { FileImage, Loader2, Upload, X } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';

import ConfirmDialog from '@/components/confirm-dialog';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import BrandMediaController from '@/wayfinder/App/Http/Controllers/Products/BrandMediaController';
import ProductMediaController from '@/wayfinder/App/Http/Controllers/Products/ProductMediaController';
import PurchaseAttachmentController from '@/wayfinder/App/Http/Controllers/Purchases/PurchaseAttachmentController';

export interface MediaData {
    id: number;
    url: string;
    thumb?: string;
    size?: string;
    name?: string;
}

interface MediaUploaderProps {
    modelId: number;
    modelType: 'brand' | 'product' | 'purchase';
    collection: 'logo' | 'thumbnail' | 'attachment';
    currentMedia?: MediaData | null;
    accept?: string;
    maxSizeMB?: number;
    disabled?: boolean;
    onSuccess?: (media: MediaData) => void;
    onDelete?: () => void;
    className?: string;
}

interface ApiResponse {
    success: boolean;
    message: string;
    media?: MediaData;
    errors?: Record<string, string[]>;
}

function getRouteConfig(modelType: 'brand' | 'product' | 'purchase') {
    switch (modelType) {
        case 'brand':
            return {
                store: BrandMediaController.store,
                destroy: BrandMediaController.destroy,
                param: 'brand' as const,
            };
        case 'product':
            return {
                store: ProductMediaController.store,
                destroy: ProductMediaController.destroy,
                param: 'product' as const,
            };
        case 'purchase':
            return {
                store: PurchaseAttachmentController.store,
                destroy: PurchaseAttachmentController.destroy,
                param: 'purchase' as const,
            };
    }
}

export default function MediaUploader({
    modelId,
    modelType,
    currentMedia,
    accept = 'image/*',
    maxSizeMB = 5,
    disabled = false,
    onSuccess,
    onDelete,
    className,
}: MediaUploaderProps) {
    const [isDragging, setIsDragging] = useState(false);
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [isUploading, setIsUploading] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);

    const fileInputRef = useRef<HTMLInputElement>(null);

    const { store, destroy, param } = getRouteConfig(modelType);

    const handleFileSelect = useCallback(
        (file: File) => {
            setUploadError(null);

            if (file.size > maxSizeMB * 1024 * 1024) {
                setUploadError(`File size must be less than ${maxSizeMB}MB`);
                return;
            }

            setSelectedFile(file);

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) =>
                    setPreviewUrl(e.target?.result as string);
                reader.readAsDataURL(file);
            } else {
                setPreviewUrl(null);
            }
        },
        [maxSizeMB],
    );

    const handleDrop = useCallback(
        (e: React.DragEvent) => {
            e.preventDefault();
            setIsDragging(false);

            if (disabled || isUploading) return;

            const file = e.dataTransfer.files[0];
            if (file) {
                handleFileSelect(file);
            }
        },
        [disabled, isUploading, handleFileSelect],
    );

    const handleDragOver = useCallback(
        (e: React.DragEvent) => {
            e.preventDefault();
            if (!disabled && !isUploading) {
                setIsDragging(true);
            }
        },
        [disabled, isUploading],
    );

    const handleDragLeave = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
    }, []);

    const handleInputChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            const file = e.target.files?.[0];
            if (file) {
                handleFileSelect(file);
            }
        },
        [handleFileSelect],
    );

    const handleUpload = useCallback(() => {
        if (!selectedFile || isUploading) return;

        setIsUploading(true);
        setUploadError(null);

        const fileFieldName = modelType === 'brand' ? 'logo' : 'file';
        const formData = new FormData();
        formData.append(fileFieldName, selectedFile);

        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        router.post(store({ [param]: modelId } as any), formData, {
            preserveScroll: true,
            onSuccess: (page) => {
                setIsUploading(false);
                setSelectedFile(null);
                setPreviewUrl(null);

                const response = page.props as unknown as ApiResponse;
                if (response?.media) {
                    onSuccess?.(response.media);
                }
            },
            onError: (errors) => {
                setIsUploading(false);
                const firstError = Object.values(errors)[0];
                setUploadError(
                    Array.isArray(firstError) ? firstError[0] : firstError,
                );
            },
        });
    }, [
        selectedFile,
        isUploading,
        modelType,
        store,
        param,
        modelId,
        onSuccess,
    ]);

    const handleRemoveSelection = useCallback(() => {
        setSelectedFile(null);
        setPreviewUrl(null);
        setUploadError(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    }, []);

    const handleDelete = useCallback(() => {
        onDelete?.();
        setDeleteDialogOpen(false);
    }, [onDelete]);

    const isImageType =
        selectedFile?.type.startsWith('image/') ||
        currentMedia?.url?.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i);

    const formatFileSize = (bytes: number): string => {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    };

    return (
        <div className={cn('space-y-3', className)}>
            {currentMedia && !selectedFile ? (
                <div className="relative inline-block">
                    {isImageType ? (
                        <img
                            src={currentMedia.thumb || currentMedia.url}
                            alt="Current media"
                            className="h-24 w-24 rounded-lg border object-contain"
                        />
                    ) : (
                        <div className="flex h-24 w-24 items-center justify-center rounded-lg border bg-muted">
                            <FileImage className="h-8 w-8 text-muted-foreground" />
                        </div>
                    )}
                    <Button
                        type="button"
                        variant="destructive"
                        size="icon-xs"
                        className="absolute -top-2 -right-2"
                        onClick={() => setDeleteDialogOpen(true)}
                        disabled={disabled}
                    >
                        <X className="h-3 w-3" />
                    </Button>
                </div>
            ) : (
                <div
                    className={cn(
                        'relative rounded-lg border-2 border-dashed p-6 transition-colors',
                        isDragging
                            ? 'border-primary bg-primary/5'
                            : 'border-muted-foreground/25',
                        (disabled || isUploading) &&
                            'cursor-not-allowed opacity-50',
                    )}
                    onDrop={handleDrop}
                    onDragOver={handleDragOver}
                    onDragLeave={handleDragLeave}
                >
                    <input
                        ref={fileInputRef}
                        type="file"
                        accept={accept}
                        onChange={handleInputChange}
                        className="absolute inset-0 cursor-pointer opacity-0"
                        disabled={disabled || isUploading}
                    />

                    {isUploading ? (
                        <div className="flex flex-col items-center gap-2">
                            <Loader2 className="h-8 w-8 animate-spin text-primary" />
                            <span className="text-sm text-muted-foreground">
                                Uploading...
                            </span>
                        </div>
                    ) : selectedFile ? (
                        <div className="flex flex-col items-center gap-3">
                            {previewUrl ? (
                                <img
                                    src={previewUrl}
                                    alt="Preview"
                                    className="h-20 w-20 rounded-lg border object-contain"
                                />
                            ) : (
                                <FileImage className="h-10 w-10 text-muted-foreground" />
                            )}
                            <div className="text-center">
                                <p className="max-w-50 truncate text-sm font-medium">
                                    {selectedFile.name}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {formatFileSize(selectedFile.size)}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    size="sm"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        handleUpload();
                                    }}
                                >
                                    <Upload className="mr-1.5 h-3.5 w-3.5" />
                                    Upload
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        handleRemoveSelection();
                                    }}
                                >
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    ) : (
                        <div className="flex flex-col items-center gap-2">
                            <Upload className="h-8 w-8 text-muted-foreground" />
                            <div className="text-center">
                                <p className="text-sm font-medium">
                                    Drop file here or click to upload
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Max size: {maxSizeMB}MB
                                </p>
                            </div>
                        </div>
                    )}
                </div>
            )}

            {uploadError && (
                <p className="text-xs text-destructive">{uploadError}</p>
            )}

            <ConfirmDialog
                open={deleteDialogOpen}
                onOpenChange={setDeleteDialogOpen}
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                deleteRoute={destroy.url({ [param]: modelId } as any)}
                title="Remove this file?"
                description="This action cannot be undone."
                onSuccess={handleDelete}
            />
        </div>
    );
}
