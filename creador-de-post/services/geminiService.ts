
import { GoogleGenAI, Modality, Part } from "@google/genai";
import { UploadedFile } from "../types";

// FIX: Initialize GoogleGenAI once at the module level for efficiency and to align with best practices.
const ai = new GoogleGenAI({ apiKey: process.env.API_KEY as string });

interface GenerateDesignParams {
  mainImages: UploadedFile[];
  styleImage: File | null;
  stylePrompt: string;
  designInstructions: string;
  outputFormat: string;
}

interface GenerateInstructionsParams {
  mainImages: UploadedFile[];
  styleImage: File | null;
  stylePrompt: string;
}

interface AdaptDesignParams {
  baseImage: string; // The base64 data URL of the image to adapt
  newFormat: string;
  newFormatLabel: string;
  mainImages: UploadedFile[];
  styleImage: File | null;
  stylePrompt: string;
  designInstructions: string;
}

const fileToBase64 = (file: File): Promise<string> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => {
      const result = reader.result as string;
      // Remove "data:image/jpeg;base64," part
      resolve(result.split(',')[1]);
    };
    reader.onerror = (error) => reject(error);
  });
};

const dataUrlToInfo = (dataUrl: string): { base64: string; mimeType: string } => {
  const [header, base64] = dataUrl.split(',');
  const mimeType = header.match(/:(.*?);/)?.[1] || 'image/png';
  return { base64, mimeType };
};


export const generateInstructionsPrompt = async (params: GenerateInstructionsParams): Promise<string> => {
    const parts: Part[] = [];

    // FIX: Use systemInstruction for persona and task-level instructions.
    const systemInstruction = `You are an expert creative director AI. Your task is to provide design instructions for another AI designer.
Analyze the provided assets and style reference, then write a concise set of design instructions.
- The instructions should be short, clear, creative, and 2-4 sentences long.
- Use the asset tags (e.g., [img1], [logo1]) to specify which image should be used and where.
- If a style reference image is provided, analyze it deeply. Describe the style of clothing, colors, brands, poses, and camera angles/shots (e.g., close-up, full-body shot) seen in the reference. Then, instruct the designer AI to apply this style to the subject(s) in the main assets (e.g., [img1]).
- CRITICAL: If the reference image contains a person, you MUST analyze their pose, posture, expression (e.g., smiling, thoughtful, serious), and overall disposition. Your instructions must explicitly direct the designer AI to apply these specific human characteristics to the subject in the main asset (e.g., "Make the subject in [img1] adopt a seated, thoughtful pose similar to the person in the reference image."). This is a crucial requirement.
- If a style reference image was provided, also carefully examine it for any text, titles, or headings. You MUST include any text you find directly in your instructions, so the user can easily edit it. For example, if the reference image says "BIG SALE 50% OFF", your output should include something like: "Incorporate the text 'BIG SALE 50% OFF' prominently...".
- If no style image is given, describe the key details of the subjects in the main assets instead.
- Output ONLY the text for the instructions. Do not add any extra explanation, titles, or formatting like "Instructions:".
Example output (with style image): "Recreate the composition using [img1] as the main subject, making them adopt a seated and thoughtful pose like the person in the reference image. Apply the warm color palette and photographic style. Add the text 'Summer Vibes' in a similar script font at the bottom. Place [logo1] in the top-left corner."
Example output (without style image): "Use the medium shot from [img1] of the person wearing a leather jacket as the main image. Add the text 'New Collection' at the top. Place [logo1] in the bottom-right corner."`;

    let styleReferenceInfo = '';
    if (params.stylePrompt) {
      styleReferenceInfo = `The desired style is described by this text prompt: "${params.stylePrompt}".`;
    } else if (params.styleImage) {
      styleReferenceInfo = `The desired style should be inspired by the provided reference image. Analyze its colors, mood, composition, and especially any text it contains.`;
    } else {
      styleReferenceInfo = 'No specific style was provided. You can suggest a general, professional style.';
    }
  
    const assetsList = params.mainImages
      .map(img => img.tags[0])
      .join(', ');
  
    const promptText = `
**1. List of available asset tags:**
${assetsList}

**2. The desired style reference is:**
${styleReferenceInfo}
`;
    parts.push({ text: promptText });

    // Add style image if it exists
    if (params.styleImage) {
        const styleImageBase64 = await fileToBase64(params.styleImage);
        parts.push({
            inlineData: {
                data: styleImageBase64,
                mimeType: params.styleImage.type,
            },
        });
    }
    // Add main images for context
    for (const uploadedFile of params.mainImages) {
        const imageBase64 = await fileToBase64(uploadedFile.file);
        parts.push({
            inlineData: {
                data: imageBase64,
                mimeType: uploadedFile.file.type,
            },
        });
    }

    const response = await ai.models.generateContent({
        model: 'gemini-2.5-flash',
        contents: { parts: parts },
        config: {
            systemInstruction,
        },
    });

    if (response.promptFeedback?.blockReason) {
        throw new Error('No se pudieron generar las instrucciones porque la solicitud fue bloqueada por seguridad. Intenta con otras imágenes o prompts.');
    }

    return response.text;
};

export const translateText = async (text: string, targetLanguage: 'en' | 'es'): Promise<string> => {
  if (!text.trim()) return '';
  
  const languageName = targetLanguage === 'en' ? 'English' : 'Spanish';
  
  // FIX: Use systemInstruction for clearer task definition and separate it from the main content.
  const systemInstruction = `Translate the provided text to ${languageName}. Output only the translated text, without any additional comments, formatting, or quotation marks.`;

  const response = await ai.models.generateContent({
    model: 'gemini-2.5-flash',
    contents: text,
    config: {
        systemInstruction,
    }
  });
  
  if (response.promptFeedback?.blockReason) {
    throw new Error('No se pudo traducir el texto porque la solicitud fue bloqueada por seguridad.');
  }

  return response.text.trim();
};

export const generateDesign = async (params: GenerateDesignParams): Promise<string> => {
    const parts: Part[] = [];

    // FIX: Use systemInstruction for persona and task-level instructions.
    const systemInstruction = `You are an expert graphic designer AI. Your task is to create a professional and cohesive design based on a set of assets and instructions.
- **Subject Integration:** When an asset tag (e.g., [img1]) refers to an image containing a person or a specific object, you MUST extract that subject. Your primary task is to integrate this exact subject (preserving their face, body shape, or object form) into the new scene described by the style reference and instructions. You should change the subject's clothing, pose, lighting, and the overall environment to match the new style, but the subject's core identity MUST be preserved. For example, if [img1] is a woman in a t-shirt and the style is 'formal business attire', the output should be the *same woman* but now wearing a business suit.
- You MUST ONLY use the assets whose tags are explicitly mentioned in the design instructions. Ignore any other uploaded assets that are not mentioned by their tags.
- If a tag in the instructions does not correspond to any available asset, ignore that tag silently.
- When using assets tagged as logos (e.g., [logo1]), you MUST maintain their original aspect ratio. Do not stretch, distort, or recolor them unless explicitly instructed to.
- A tag can be repeated in the instructions to use the same asset multiple times.
- Generate a single, complete, high-quality image file that combines all requirements into a final, polished design. You MUST output only the final image. Do not output any text or explanation.`;

    let styleInstruction = '';
    if (params.stylePrompt) {
      styleInstruction = `The desired style is explicitly described as: "${params.stylePrompt}".`;
    } else if (params.styleImage) {
      styleInstruction = "The image provided after the main assets is a style reference. Adapt its style, composition, and atmosphere to the new design.";
    }

    const assetsList = params.mainImages.length > 0 
      ? params.mainImages.map((img, index) => 
          `- The image at position ${index + 1} in the input is identified by the tag: ${img.tags[0]}.`
        ).join('\n')
      : 'No main image assets provided.';

    const fullPrompt = `
**Available Assets:**
You have been provided with ${params.mainImages.length} main image assets. They are provided in order before the style reference image (if any). Here is how you must identify them:
${assetsList}

**Design Task:**
1.  **Style Reference:** ${styleInstruction}
2.  **Design Instructions & Content:** Follow these instructions carefully. This is where you will find which assets to use by looking for their tags: "${params.designInstructions}".
3.  **Output Format:** The final image must have the exact dimensions: ${params.outputFormat} (width x height).
`;
    // 1. Main Images
    for (const uploadedFile of params.mainImages) {
        const imageBase64 = await fileToBase64(uploadedFile.file);
        parts.push({
            inlineData: {
                data: imageBase64,
                mimeType: uploadedFile.file.type,
            },
        });
    }

    // 2. Style Image (if provided)
    if (params.styleImage) {
        const styleImageBase64 = await fileToBase64(params.styleImage);
        parts.push({
            inlineData: {
                data: styleImageBase64,
                mimeType: params.styleImage.type,
            },
        });
    }

    // 3. Text Prompt
    parts.push({ text: fullPrompt });

    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash-image',
      contents: { parts: parts },
      config: {
        responseModalities: [Modality.IMAGE, Modality.TEXT],
        systemInstruction,
      },
    });

    if (response.promptFeedback?.blockReason) {
        let userMessage = 'La solicitud fue bloqueada. ';
        switch (response.promptFeedback.blockReason) {
            case 'SAFETY':
                userMessage += 'El contenido puede violar las políticas de seguridad. Intenta ajustar el texto o las imágenes.';
                break;
            default:
                userMessage += 'Por favor, modifica tu solicitud e inténtalo de nuevo.';
                break;
        }
        throw new Error(userMessage);
    }

    if (response.candidates && response.candidates[0] && response.candidates[0].content.parts) {
        for (const part of response.candidates[0].content.parts) {
            if (part.inlineData && part.inlineData.mimeType.startsWith('image/')) {
                const base64ImageBytes = part.inlineData.data;
                return `data:${part.inlineData.mimeType};base64,${base64ImageBytes}`;
            }
        }
    }

    throw new Error('No se pudo generar una imagen. La API no devolvió una imagen. Intenta ser más específico en las instrucciones o cambia la imagen/prompt de estilo.');
};

export const adaptDesign = async (params: AdaptDesignParams): Promise<string> => {
    const parts: Part[] = [];
  
    // FIX: Use systemInstruction for persona and task-level instructions.
    const systemInstruction = `You are an expert graphic designer AI specializing in content adaptation.
Your task is to take the very first image provided (the base design) and intelligently adapt it to a new aspect ratio.
- Preserve the core subject, style, colors, and mood of the base design.
- Intelligently recompose the elements to fit the new dimensions. You can extend backgrounds, reposition elements, or adjust typography size, but the essence must remain the same.
- Generate a single, complete, high-quality image file that is the adapted version of the base design. You MUST output only the final image. Do not output any text or explanation.`;

    const fullPrompt = `
**Base Design:**
The first image in the input is the design you need to adapt.

**Task:**
Adapt the base design to a new format. Do not simply crop or stretch the image. Intelligently recompose the elements (text, logos, background, subjects) to fit the new canvas while preserving the original style and composition.

**Required Output Format:**
- **Description:** ${params.newFormatLabel}
- **Dimensions (width x height):** ${params.newFormat} pixels
The output MUST strictly match these dimensions. For example, a 1920x1080 image must be exactly 1920 pixels wide and 1080 pixels high.

**Original Context:**
The original design was created with these instructions: "${params.designInstructions}". This context is important to maintain the design's core message.
The other images provided are the original assets. You can use them to regenerate parts of the scene if needed for a high-quality adaptation.`;
    
    // 1. Image to adapt
    const { base64: baseImageBase64, mimeType } = dataUrlToInfo(params.baseImage);
    parts.push({
      inlineData: {
        data: baseImageBase64,
        mimeType: mimeType,
      },
    });
  
    // 2. Main Images (for context)
    for (const uploadedFile of params.mainImages) {
      const imageBase64 = await fileToBase64(uploadedFile.file);
      parts.push({
        inlineData: {
          data: imageBase64,
          mimeType: uploadedFile.file.type,
        },
      });
    }
  
    // 3. Style Image (for context)
    if (params.styleImage) {
      const styleImageBase64 = await fileToBase64(params.styleImage);
      parts.push({
        inlineData: {
          data: styleImageBase64,
          mimeType: params.styleImage.type,
        },
      });
    }
  
    // 4. Text Prompt
    parts.push({ text: fullPrompt });
  
    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash-image',
      contents: { parts: parts },
      config: {
        responseModalities: [Modality.IMAGE, Modality.TEXT],
        systemInstruction,
      },
    });
  
    if (response.promptFeedback?.blockReason) {
        let userMessage = 'La solicitud para adaptar la imagen fue bloqueada. ';
        switch (response.promptFeedback.blockReason) {
            case 'SAFETY':
                userMessage += 'El contenido puede violar las políticas de seguridad. Intenta con otra imagen base.';
                break;
            default:
                userMessage += 'Por favor, inténtalo de nuevo.';
                break;
        }
        throw new Error(userMessage);
    }

    if (response.candidates && response.candidates[0] && response.candidates[0].content.parts) {
      for (const part of response.candidates[0].content.parts) {
        if (part.inlineData && part.inlineData.mimeType.startsWith('image/')) {
          const base64ImageBytes = part.inlineData.data;
          return `data:${part.inlineData.mimeType};base64,${base64ImageBytes}`;
        }
      }
    }
  
    throw new Error('No se pudo adaptar la imagen. La API no devolvió una imagen. Inténtalo de nuevo o con un formato diferente.');
  };
