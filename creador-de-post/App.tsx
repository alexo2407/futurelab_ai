
import React, { useState, useCallback, useRef, useEffect } from 'react';
import { generateDesign, generateInstructionsPrompt, translateText, adaptDesign } from './services/geminiService';
import type { StyleSource, UploadedFile } from './types';
import Header from './components/Header';
import ImageUploader from './components/ImageUploader';
import Spinner from './components/Spinner';
import InstructionsInput from './components/InstructionsInput';
import { ImageIcon, DownloadIcon, TrashIcon, SparklesIcon, AspectRatioHorizontalIcon, AspectRatioVerticalIcon, AspectRatioSquareIcon } from './components/Icons';

const App: React.FC = () => {
  const [mainFiles, setMainFiles] = useState<UploadedFile[]>([]);
  const [styleSource, setStyleSource] = useState<StyleSource>('prompt');
  const [styleImage, setStyleImage] = useState<File | null>(null);
  const [stylePrompt, setStylePrompt] = useState<string>('Estilo fotográfico, luz natural suave, colores pastel.');
  const [designInstructions, setDesignInstructions] = useState<string>("Describe el diseño que quieres. Usa las etiquetas como [img1] o [logo1] para posicionar tus imágenes. Por ejemplo: 'Usa [img1] de fondo, pon el [logo1] arriba a la derecha y añade el texto \"Verano 2025\" en el centro.'");
  const [outputFormat] = useState<string>('1080x1080');

  const [generatedImage, setGeneratedImage] = useState<string | null>(null);
  const [history, setHistory] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [adaptingFormat, setAdaptingFormat] = useState<string | null>(null);
  const [isGeneratingInstructions, setIsGeneratingInstructions] = useState<boolean>(false);
  const [isTranslating, setIsTranslating] = useState<'en' | 'es' | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isDraggingOverInstructions, setIsDraggingOverInstructions] = useState(false);
  const [hasTagsInInstructions, setHasTagsInInstructions] = useState(false);

  const instructionsTextareaRef = useRef<HTMLTextAreaElement>(null);
  const nextImgIndex = useRef(1);
  const nextLogoIndex = useRef(1);
  const nextIconIndex = useRef(1);

  useEffect(() => {
    const tagRegex = /\[(img|logo|icon)\d+\]/;
    setHasTagsInInstructions(tagRegex.test(designInstructions));
  }, [designInstructions]);

  const handleMainFilesSelect = useCallback(async (newFilesArray: File[]) => {
    const processedNewFiles: UploadedFile[] = await Promise.all(
        newFilesArray.map(async (file) => {
            let tag = '';
            const fileNameLower = file.name.toLowerCase();
            
            if (fileNameLower.includes('logo')) {
                tag = `[logo${nextLogoIndex.current}]`;
                nextLogoIndex.current++;
            } else if (fileNameLower.includes('icon')) {
                tag = `[icon${nextIconIndex.current}]`;
                nextIconIndex.current++;
            } else {
                tag = `[img${nextImgIndex.current}]`;
                nextImgIndex.current++;
            }
            
            const tags = [tag];

            const preview = await new Promise<string>((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result as string);
                reader.onerror = error => reject(error);
            });
            
            return { file, tags, preview };
        })
    );
    setMainFiles(prev => [...prev, ...processedNewFiles]);
  }, []);

  const handleRemoveFile = (indexToRemove: number) => {
    setMainFiles(prev => prev.filter((_, index) => index !== indexToRemove));
  };

  const insertTagIntoInstructions = useCallback((tag: string) => {
    const textarea = instructionsTextareaRef.current;
    if (textarea) {
      textarea.focus();
      const { selectionStart, selectionEnd, value } = textarea;
      const textToInsert = `${tag} `;
      const newValue = value.substring(0, selectionStart) + textToInsert + value.substring(selectionEnd);
      
      setDesignInstructions(newValue);

      setTimeout(() => {
        const newCursorPos = selectionStart + textToInsert.length;
        if (instructionsTextareaRef.current) {
            instructionsTextareaRef.current.setSelectionRange(newCursorPos, newCursorPos);
        }
      }, 0);
    }
  }, []);

  const handleTagClick = (tag: string) => {
    insertTagIntoInstructions(tag);
  };

  const handleTagDragStart = (e: React.DragEvent<HTMLButtonElement>, tag: string) => {
    e.dataTransfer.setData('text/plain', tag);
    e.dataTransfer.effectAllowed = 'copy';
  };

  const handleInstructionsDrop = (e: React.DragEvent<HTMLTextAreaElement>) => {
    e.preventDefault();
    setIsDraggingOverInstructions(false);
    const tag = e.dataTransfer.getData('text/plain');
    if (tag && tag.startsWith('[') && tag.endsWith(']')) {
      insertTagIntoInstructions(tag);
    }
  };
  
  const handleInstructionsDragOver = (e: React.DragEvent<HTMLTextAreaElement>) => {
    e.preventDefault();
  };

  const handleInstructionsDragEnter = (e: React.DragEvent<HTMLTextAreaElement>) => {
    e.preventDefault();
    setIsDraggingOverInstructions(true);
  };
  
  const handleInstructionsDragLeave = (e: React.DragEvent<HTMLTextAreaElement>) => {
    e.preventDefault();
    setIsDraggingOverInstructions(false);
  };

  const handleGenerateInstructionsClick = useCallback(async () => {
    if (mainFiles.length === 0) {
      setError('Sube al menos una imagen principal para generar instrucciones.');
      return;
    }
    setError(null);
    setIsGeneratingInstructions(true);
    
    try {
      const instructions = await generateInstructionsPrompt({
        mainImages: mainFiles,
        styleImage: styleSource === 'image' ? styleImage : null,
        stylePrompt: styleSource === 'prompt' ? stylePrompt : '',
      });
      setDesignInstructions(instructions);
    } catch (err: unknown) {
      if (err instanceof Error) {
        setError(err.message);
      } else {
        setError('Ocurrió un error desconocido al generar instrucciones.');
      }
    } finally {
      setIsGeneratingInstructions(false);
    }
  }, [mainFiles, styleImage, stylePrompt, styleSource]);

  const handleTranslateClick = useCallback(async (targetLang: 'en' | 'es') => {
    if (isTranslating || !designInstructions.trim()) return;
    setError(null);
    setIsTranslating(targetLang);
    try {
      const translated = await translateText(designInstructions, targetLang);
      setDesignInstructions(translated);
    } catch (err) {
      if (err instanceof Error) {
        setError(err.message);
      } else {
        setError('Error al traducir las instrucciones.');
      }
    } finally {
      setIsTranslating(null);
    }
  }, [designInstructions, isTranslating]);
  
  const handleGenerateClick = useCallback(async () => {
    if (mainFiles.length === 0) {
      setError('Debes subir al menos una imagen principal.');
      return;
    }
    setError(null);
    setIsLoading(true);

    try {
      const result = await generateDesign({
        mainImages: mainFiles,
        styleImage: styleSource === 'image' ? styleImage : null,
        stylePrompt: styleSource === 'prompt' ? stylePrompt : '',
        designInstructions,
        outputFormat: outputFormat,
      });
      setGeneratedImage(result);
      setHistory(prev => [result, ...prev]);
    } catch (err: unknown) {
      if (err instanceof Error) {
        setError(err.message);
      } else {
        setError('Ocurrió un error desconocido al generar el diseño.');
      }
    } finally {
      setIsLoading(false);
    }
  }, [mainFiles, styleSource, styleImage, stylePrompt, designInstructions, outputFormat]);

  const handleAdaptImage = useCallback(async (newFormat: string, newFormatLabel: string) => {
    if (!generatedImage || isLoading) return;

    setError(null);
    setAdaptingFormat(newFormat);
    setIsLoading(true);

    try {
      const result = await adaptDesign({
        baseImage: generatedImage,
        newFormat: newFormat,
        newFormatLabel: newFormatLabel,
        mainImages: mainFiles,
        styleImage: styleSource === 'image' ? styleImage : null,
        stylePrompt: styleSource === 'prompt' ? stylePrompt : '',
        designInstructions,
      });
      setGeneratedImage(result);
      setHistory(prev => [result, ...prev]);
    } catch (err: unknown) {
      if (err instanceof Error) {
        setError(err.message);
      } else {
        setError('Ocurrió un error desconocido al adaptar el diseño.');
      }
    } finally {
      setAdaptingFormat(null);
      setIsLoading(false);
    }
  }, [generatedImage, isLoading, mainFiles, styleSource, styleImage, stylePrompt, designInstructions]);

  const handleClearHistory = () => {
    setHistory([]);
    setGeneratedImage(null);
  };

  const handleSelectFromHistory = (image: string) => {
    setGeneratedImage(image);
  };

  const isGenerateButtonDisabled = mainFiles.length === 0 || isLoading || isGeneratingInstructions || !!isTranslating;

  const SectionTitle: React.FC<{ children: React.ReactNode }> = ({ children }) => (
    <h2 className="text-lg font-semibold text-blue-900/70 mb-4">{children}</h2>
  );
  
  const supportedFormats = [
    { format: '1920x1080', label: 'Horizontal', icon: <AspectRatioHorizontalIcon className="w-5 h-5" /> },
    { format: '1080x1080', label: 'Cuadrado', icon: <AspectRatioSquareIcon className="w-5 h-5" /> },
    { format: '1080x1920', label: 'Vertical', icon: <AspectRatioVerticalIcon className="w-5 h-5" /> },
  ];

  return (
    <div className="min-h-screen text-gray-800 font-sans">
      <Header />
      <main className="container mx-auto px-4 pb-12">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          {/* --- Form Section --- */}
          <div className="bg-white/40 backdrop-blur-3xl border border-white/50 border-t-white/70 border-l-white/70 rounded-3xl shadow-2xl shadow-blue-200/50 p-8 space-y-8">
            <section>
              <SectionTitle>1. Imágenes Principales (Assets)</SectionTitle>
               {mainFiles.length > 0 && (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-4">
                  {mainFiles.map((uploadedFile, index) => (
                    <div key={index} className="flex flex-col gap-2">
                        <div className="relative group bg-white/20 p-2 rounded-xl border border-white/40 aspect-square flex items-center justify-center">
                            <img src={uploadedFile.preview} alt={`Preview ${index}`} className="max-w-full max-h-full object-contain rounded-md" />
                            <button onClick={() => handleRemoveFile(index)} title="Eliminar asset" className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-all hover:bg-red-600 hover:scale-110 shadow-lg">
                                <TrashIcon className="w-4 h-4"/>
                            </button>
                        </div>
                        <div className="flex flex-wrap gap-1 justify-center">
                          {uploadedFile.tags.map(tag => (
                            <button 
                                key={tag} 
                                onClick={() => handleTagClick(tag)} 
                                draggable="true"
                                onDragStart={(e) => handleTagDragStart(e, tag)}
                                title={`Click para insertar, arrastra para mover: ${tag}`} 
                                className="bg-blue-500/80 px-1.5 py-0.5 rounded text-white hover:bg-blue-400 transition-colors text-xs cursor-grab active:cursor-grabbing"
                            >
                                {tag}
                            </button>
                          ))}
                        </div>
                    </div>
                  ))}
                </div>
              )}
              <ImageUploader id="main-image" onFilesSelect={handleMainFilesSelect} required={mainFiles.length === 0} multiple />
            </section>

            <section>
              <SectionTitle>2. Fuente de Estilo</SectionTitle>
              <div className="flex bg-blue-500/5 rounded-xl p-1 mb-4 border border-white/30">
                <button
                  onClick={() => setStyleSource('prompt')}
                  className={`w-1/2 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-300 ${styleSource === 'prompt' ? 'bg-white/50 text-blue-800 font-semibold shadow-lg shadow-blue-200/50' : 'text-blue-900/60 hover:bg-white/20'}`}
                  aria-pressed={styleSource === 'prompt'}
                >
                  Prompt de Texto
                </button>
                <button
                  onClick={() => setStyleSource('image')}
                  className={`w-1/2 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-300 ${styleSource === 'image' ? 'bg-white/50 text-blue-800 font-semibold shadow-lg shadow-blue-200/50' : 'text-blue-900/60 hover:bg-white/20'}`}
                  aria-pressed={styleSource === 'image'}
                >
                  Imagen de Referencia
                </button>
              </div>
              {styleSource === 'image' ? (
                 <ImageUploader id="style-image" onFilesSelect={(files) => setStyleImage(files[0] || null)} />
              ) : (
                <textarea
                  value={stylePrompt}
                  onChange={(e) => setStylePrompt(e.target.value)}
                  placeholder="Ej: 'Estilo fotográfico, luz natural suave'"
                  className="w-full bg-white/20 border border-white/40 rounded-xl p-3 text-gray-800 placeholder-gray-500/80 focus:ring-2 focus:ring-blue-400/60 focus:border-blue-400 transition-shadow duration-300"
                  rows={3}
                />
              )}
            </section>

            <section>
              <div className="flex justify-between items-center mb-4 gap-2">
                  <h2 className="text-lg font-semibold text-blue-900/70 shrink-0">3. Instrucciones</h2>
                  <div className="flex items-center gap-2">
                    <button 
                      onClick={handleGenerateInstructionsClick}
                      disabled={mainFiles.length === 0 || isLoading || isGeneratingInstructions}
                      className="flex items-center gap-2 text-sm bg-white/40 hover:bg-white/80 text-blue-800 font-semibold py-1 px-3 rounded-full border border-white/60 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-200/30"
                      title="Generar sugerencia de instrucciones con IA"
                    >
                      {isGeneratingInstructions ? (
                        <Spinner size="sm" />
                      ) : (
                        <SparklesIcon className="w-4 h-4" />
                      )}
                      <span className="hidden sm:inline">Sugerir</span>
                    </button>
                    <div className="flex items-center gap-1 bg-white/20 p-0.5 rounded-full border border-white/40">
                      <button
                        onClick={() => handleTranslateClick('en')}
                        disabled={!designInstructions.trim() || !!isTranslating}
                        className="px-2 py-0.5 rounded-full text-sm font-semibold text-blue-800/80 disabled:opacity-50 transition-colors hover:bg-white/50 flex items-center justify-center w-8 h-6"
                        title="Traducir a Inglés"
                      >
                        {isTranslating === 'en' ? <Spinner size="sm" /> : 'EN'}
                      </button>
                      <button
                        onClick={() => handleTranslateClick('es')}
                        disabled={!designInstructions.trim() || !!isTranslating}
                        className="px-2 py-0.5 rounded-full text-sm font-semibold text-blue-800/80 disabled:opacity-50 transition-colors hover:bg-white/50 flex items-center justify-center w-8 h-6"
                        title="Traducir a Español"
                      >
                        {isTranslating === 'es' ? <Spinner size="sm" /> : 'ES'}
                      </button>
                    </div>
                  </div>
              </div>
              <InstructionsInput
                ref={instructionsTextareaRef}
                value={designInstructions}
                onChange={(e) => setDesignInstructions(e.target.value)}
                placeholder={`Describe el diseño que quieres. Usa las etiquetas como [img1] o [logo1] para posicionar tus imágenes. Por ejemplo: 'Usa [img1] de fondo, pon el [logo1] arriba a la derecha y añade el texto "Verano 2025" en el centro.'`}
                mainFiles={mainFiles}
                onDrop={handleInstructionsDrop}
                onDragOver={handleInstructionsDragOver}
                onDragEnter={handleInstructionsDragEnter}
                onDragLeave={handleInstructionsDragLeave}
                isDraggingOver={isDraggingOverInstructions}
                hasTags={hasTagsInInstructions}
              />
            </section>

            <button
              onClick={handleGenerateClick}
              disabled={isGenerateButtonDisabled}
              className="w-full bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold py-4 px-4 rounded-xl text-lg transition-all duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-3 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 hover:scale-[1.02] focus:outline-none focus:ring-4 focus:ring-blue-500/50"
            >
              {isLoading && !adaptingFormat ? <Spinner /> : <span>Generar Diseño</span>}
            </button>
            {error && <p className="text-red-600 font-medium mt-4 text-center bg-red-100/50 border border-red-200 p-3 rounded-lg">{error}</p>}
          </div>

          {/* --- Result Section --- */}
          <div className="bg-white/40 backdrop-blur-3xl border border-white/50 border-t-white/70 border-l-white/70 rounded-3xl shadow-2xl shadow-blue-200/50 p-8 flex flex-col min-h-[500px] lg:min-h-full">
            <SectionTitle>Resultado</SectionTitle>
            <div className="w-full flex-grow flex items-center justify-center bg-blue-100/10 rounded-2xl border border-white/30 p-4">
              {isLoading && <Spinner size="lg" />}
              {!isLoading && !generatedImage && (
                <div className="text-center text-blue-900/50">
                  <ImageIcon className="w-16 h-16 mx-auto mb-4 opacity-50" />
                  <p>El diseño generado aparecerá aquí</p>
                </div>
              )}
              {generatedImage && !isLoading && (
                <div className="relative group w-full h-full flex items-center justify-center">
                    <img src={generatedImage} alt="Diseño generado" className="max-w-full max-h-full object-contain rounded-lg shadow-lg" />
                    <a
                        href={generatedImage}
                        download="ai_design.png"
                        className="absolute bottom-4 right-4 bg-white/30 backdrop-blur-md text-gray-800 p-3 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-blue-500 hover:text-white hover:scale-110 border border-white/50"
                        title="Descargar imagen"
                    >
                       <DownloadIcon className="w-6 h-6" />
                    </a>
                </div>
              )}
            </div>

            {generatedImage && (
              <div className="w-full mt-6">
                <h3 className="text-md font-semibold text-blue-900/70 text-center mb-3">Adaptar formato</h3>
                <div className="flex justify-center gap-3">
                    {supportedFormats.map(({ format, label, icon }) => (
                        <button
                            key={format}
                            onClick={() => handleAdaptImage(format, label)}
                            disabled={isLoading}
                            className="flex-1 flex items-center justify-center gap-2 bg-white/40 hover:bg-white/80 text-blue-800 font-semibold py-2 px-3 rounded-xl border border-white/60 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-200/30"
                            title={`Adaptar a formato ${label}`}
                        >
                            {isLoading && adaptingFormat === format ? (
                            <Spinner size="sm" />
                            ) : (
                            icon
                            )}
                            <span>{label}</span>
                        </button>
                    ))}
                </div>
              </div>
            )}
            
            {history.length > 0 && (
              <div className="w-full mt-6">
                <div className="flex justify-between items-center mb-3">
                  <h3 className="text-md font-semibold text-blue-900/70">Historial</h3>
                  <button 
                    onClick={handleClearHistory} 
                    className="flex items-center gap-1.5 text-sm text-red-500 hover:text-red-700 transition-colors font-medium p-1 rounded-md hover:bg-red-500/10" 
                    title="Limpiar historial"
                  >
                    <TrashIcon className="w-4 h-4" />
                    <span>Limpiar</span>
                  </button>
                </div>
                <div className="flex gap-3 overflow-x-auto pb-2 -mx-2 px-2 thin-scrollbar">
                  {history.map((histImage, index) => (
                    <button 
                      key={index}
                      onClick={() => handleSelectFromHistory(histImage)}
                      className={`relative shrink-0 w-24 h-24 rounded-xl p-1 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-50/50 focus:ring-blue-500 ${generatedImage === histImage ? 'bg-blue-500 shadow-md' : 'bg-white/30 hover:bg-white/60'}`}
                    >
                      <img 
                        src={histImage} 
                        alt={`Diseño generado ${history.length - index}`} 
                        className="w-full h-full object-cover rounded-lg" 
                      />
                    </button>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
      </main>
    </div>
  );
};

export default App;
