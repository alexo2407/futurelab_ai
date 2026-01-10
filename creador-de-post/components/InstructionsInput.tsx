
import React, { useMemo, useRef, useLayoutEffect } from 'react';
import type { UploadedFile } from '../types';

interface InstructionsInputProps {
  value: string;
  onChange: (event: React.ChangeEvent<HTMLTextAreaElement>) => void;
  placeholder: string;
  mainFiles: UploadedFile[];
  onDrop: (e: React.DragEvent<HTMLTextAreaElement>) => void;
  onDragOver: (e: React.DragEvent<HTMLTextAreaElement>) => void;
  onDragEnter: (e: React.DragEvent<HTMLTextAreaElement>) => void;
  onDragLeave: (e: React.DragEvent<HTMLTextAreaElement>) => void;
  isDraggingOver: boolean;
  hasTags: boolean;
}

const InstructionsInput = React.forwardRef<HTMLTextAreaElement, InstructionsInputProps>(
  ({ value, onChange, placeholder, mainFiles, onDrop, onDragOver, onDragEnter, onDragLeave, isDraggingOver, hasTags }, ref) => {
    
    const highlighterRef = useRef<HTMLDivElement>(null);
    const textAreaRef = ref as React.RefObject<HTMLTextAreaElement>;

    useLayoutEffect(() => {
      const textarea = textAreaRef.current;
      if (textarea) {
        // Reset height to allow the textarea to shrink if text is deleted
        textarea.style.height = 'auto';
        // Set height to the scroll height to fit the content
        textarea.style.height = `${textarea.scrollHeight}px`;
      }
    }, [value]);

    const validTags = useMemo(() => {
      const tags = new Set<string>();
      mainFiles.forEach(file => {
        file.tags.forEach(tag => tags.add(tag));
      });
      return tags;
    }, [mainFiles]);

    const handleScroll = () => {
      if (highlighterRef.current && textAreaRef.current) {
        highlighterRef.current.scrollTop = textAreaRef.current.scrollTop;
        highlighterRef.current.scrollLeft = textAreaRef.current.scrollLeft;
      }
    };

    const renderHighlightedText = () => {
      // Regex to split by tags or quoted strings, keeping the delimiters
      const parts = value.split(/(\[img\d+\]|\[logo\d+\]|\[icon\d+\]|"[^"]*")/g).filter(Boolean);
      
      return parts.map((part, index) => {
        const isTag = part.startsWith('[') && part.endsWith(']');
        const isQuoted = part.startsWith('"') && part.endsWith('"');

        if (isTag) {
          if (validTags.has(part)) {
            return <span key={index} className="font-semibold text-blue-600 bg-blue-500/10 rounded-sm px-0.5">{part}</span>;
          } else {
            return <span key={index} className="font-semibold text-red-500 underline decoration-red-500 decoration-wavy" title={`Asset ${part} no encontrado`}>{part}</span>;
          }
        }

        if (isQuoted) {
          return <span key={index} className="text-emerald-700">{part}</span>;
        }

        return <span key={index}>{part}</span>;
      });
    };
    
    // Shared classes for font, padding, and layout to ensure perfect alignment
    const commonClasses = "w-full p-3 whitespace-pre-wrap break-words font-sans text-base leading-relaxed focus:outline-none";

    // Classes for the container
    const containerClasses = `relative w-full bg-white/20 border rounded-xl text-gray-800 placeholder-gray-500/80 focus-within:ring-2 focus-within:ring-blue-400/60 focus-within:border-blue-400 transition-all duration-300 ${
        isDraggingOver
        ? 'ring-2 ring-blue-500 ring-offset-2 border-blue-500'
        : hasTags
        ? 'border-blue-400/80 shadow-[inset_0_2px_4px_rgba(59,130,246,0.1)]'
        : 'border-white/40'
    }`;
    
    return (
      <div className={containerClasses}>
        <div 
          ref={highlighterRef}
          aria-hidden="true"
          className={`${commonClasses} absolute top-0 left-0 h-full overflow-auto pointer-events-none text-gray-800`}
        >
          {renderHighlightedText()}
           {/* Add a space at the end to ensure the container has the correct height for the last line */}
          {' '}
        </div>
        <textarea
          ref={ref}
          value={value}
          onChange={onChange}
          onScroll={handleScroll}
          placeholder={placeholder}
          className={`${commonClasses} relative bg-transparent text-transparent caret-blue-600 resize-none min-h-[140px] overflow-hidden`}
          onDrop={onDrop}
          onDragOver={onDragOver}
          onDragEnter={onDragEnter}
          onDragLeave={onDragLeave}
          spellCheck="false"
        />
      </div>
    );
  }
);

InstructionsInput.displayName = 'InstructionsInput';

export default InstructionsInput;
