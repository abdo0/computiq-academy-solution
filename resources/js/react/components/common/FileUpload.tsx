import React, { useRef, useState } from 'react';
import { Upload, X, FileText, AlertCircle } from 'lucide-react';
import { useLanguage } from '../../contexts/LanguageContext';
import { useTranslation } from '../../contexts/TranslationProvider';

interface FileUploadProps {
    label: string;
    required?: boolean;
    accept?: string;
    value: File | null;
    onChange: (file: File | null) => void;
    error?: string;
    hint?: string;
    multiple?: boolean;
    onMultipleChange?: (files: File[]) => void;
}

const FileUpload: React.FC<FileUploadProps> = ({
    label,
    required = false,
    accept = '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png,image/jpg',
    value,
    onChange,
    error,
    hint,
    multiple = false,
    onMultipleChange,
}) => {
    const { __ } = useTranslation();
    const [isDragging, setIsDragging] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleDragEnter = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(true);
    };

    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);

        const files = Array.from(e.dataTransfer.files);
        const validFiles = files.filter((file) => {
            const extension = '.' + file.name.split('.').pop()?.toLowerCase();
            const mimeType = file.type;
            const acceptedExtensions = accept.split(',').map(acc => acc.trim().toLowerCase());
            const acceptedMimeTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            
            return acceptedExtensions.some((acc) => acc === extension) || 
                   acceptedMimeTypes.includes(mimeType.toLowerCase()) ||
                   (extension === '.pdf' && mimeType === 'application/pdf') ||
                   (['.jpg', '.jpeg'].includes(extension) && mimeType.startsWith('image/jpeg')) ||
                   (extension === '.png' && mimeType === 'image/png');
        });

        if (validFiles.length > 0) {
            if (multiple && onMultipleChange) {
                onMultipleChange(validFiles);
            } else if (!multiple && validFiles[0]) {
                onChange(validFiles[0]);
            }
        }
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            if (multiple && onMultipleChange) {
                onMultipleChange(Array.from(files));
            } else {
                onChange(files[0]);
            }
        }
    };

    const handleRemove = (e: React.MouseEvent) => {
        e.stopPropagation();
        onChange(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const formatFileSize = (bytes: number): string => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };

    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {label} {required && <span className="text-red-500">*</span>}
            </label>
            
            <div
                onDragEnter={handleDragEnter}
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onDrop={handleDrop}
                onClick={() => fileInputRef.current?.click()}
                className={`
                    relative border-2 border-dashed rounded-sm p-6 transition-all cursor-pointer
                    ${isDragging 
                        ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' 
                        : 'border-gray-300 dark:border-gray-600 hover:border-brand-400 dark:hover:border-brand-500'
                    }
                    ${error ? 'border-red-500 dark:border-red-500' : ''}
                    ${value ? 'bg-gray-50 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-700'}
                `}
            >
                <input
                    ref={fileInputRef}
                    type="file"
                    accept={accept}
                    multiple={multiple}
                    onChange={handleFileChange}
                    className="hidden"
                    required={required && !value}
                />

                {value ? (
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-brand-100 dark:bg-brand-900/30 rounded-sm flex items-center justify-center">
                            <FileText className="w-5 h-5 text-brand-600 dark:text-brand-400" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {value.name}
                            </p>
                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                {formatFileSize(value.size)}
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={handleRemove}
                            className="p-1 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                        >
                            <X size={18} />
                        </button>
                    </div>
                ) : (
                    <div className="text-center">
                        <Upload className={`w-10 h-10 mx-auto mb-3 ${isDragging ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400 dark:text-gray-500'}`} />
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span className="text-brand-600 dark:text-brand-400 font-medium">
                                {isDragging ? (__('Drop file here') || 'Drop file here') : (__('Click to upload') || 'Click to upload')}
                            </span>
                            {' '}
                            {__('Or drag drop') || 'or drag and drop'}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-500">
                            {hint || accept}
                        </p>
                    </div>
                )}
            </div>

            {hint && !value && (
                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {hint}
                </p>
            )}

            {error && (
                <p className="mt-1 text-sm text-red-500 flex items-center gap-1">
                    <AlertCircle size={14} />
                    {error}
                </p>
            )}
        </div>
    );
};

export default FileUpload;

