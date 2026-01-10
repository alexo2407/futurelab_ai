
export type StyleSource = 'image' | 'prompt';

export interface UploadedFile {
  file: File;
  tags: string[];
  preview: string;
}

// FIX: Added the missing 'OutputFormat' type to resolve an import error.
export interface OutputFormat {
  value: string;
  label: string;
}
