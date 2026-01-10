import React, { useState, useCallback, DragEvent } from 'react';
import { UploadIcon } from './Icons';

interface ImageUploaderProps {
  id: string;
  onFilesSelect: (files: File[]) => void;
  required?: boolean;
  multiple?: boolean;
}

const ImageUploader: React.FC<ImageUploaderProps> = ({ id, onFilesSelect, required = false, multiple = false }) => {
  const [preview, setPreview] = useState<string | null>(null);
  const [isDragging, setIsDragging] = useState<boolean>(false);

  const handleFiles = useCallback((files: FileList | null) => {
    const filesArray = files ? Array.from(files) : [];
    if (filesArray.length > 0) {
      if (!multiple) {
        // Single file mode: show preview
        const file = filesArray[0];
        if (file && file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onloadend = () => {
            setPreview(reader.result as string);
          };
          reader.readAsDataURL(file);
        } else {
            setPreview(null);
        }
      } else {
          // In multiple mode, App.tsx handles previews, so we don't show one here.
          setPreview(null);
      }
      onFilesSelect(filesArray);
    } else {
      setPreview(null);
      onFilesSelect([]);
    }
  }, [onFilesSelect, multiple]);

  const onDragEnter = (e: DragEvent<HTMLLabelElement>) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(true);
  };
  const onDragLeave = (e: DragEvent<HTMLLabelElement>) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
  };
  const onDragOver = (e: DragEvent<HTMLLabelElement>) => {
    e.preventDefault();
    e.stopPropagation();
  };
  const onDrop = (e: DragEvent<HTMLLabelElement>) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      handleFiles(e.dataTransfer.files);
    }
  };

  return (
    <div>
      <label
        htmlFor={id}
        onDragEnter={onDragEnter}
        onDragLeave={onDragLeave}
        onDragOver={onDragOver}
        onDrop={onDrop}
        className={`flex justify-center items-center w-full min-h-[12rem] px-6 transition-all duration-300 bg-white/20 backdrop-blur-lg border-2 border-dashed border-white/40 rounded-2xl appearance-none cursor-pointer hover:border-blue-400/50 focus:outline-none ${isDragging ? 'border-solid border-blue-500 shadow-[0_0_25px_rgba(59,130,246,0.5),inset_0_0_15px_rgba(59,130,246,0.2)]' : ''}`}
      >
        {preview && !multiple ? (
          <img src={preview} alt="Preview" className="max-h-44 max-w-full object-contain rounded-lg" />
        ) : (
          <span className="flex flex-col items-center space-y-2">
            <UploadIcon className="w-10 h-10 text-gray-600/80" />
            <span className="font-medium text-gray-700 text-center">
              Arrastra y suelta archivos o{' '}
              <span className="text-blue-600 underline">búscalo en tu equipo</span>
            </span>
          </span>
        )}
      </label>
      <input
        id={id}
        type="file"
        accept="image/*"
        multiple={multiple}
        onChange={(e) => handleFiles(e.target.files)}
        className="hidden"
        required={required}
      />
    </div>
  );
};

export default ImageUploader;
