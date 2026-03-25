import { GoogleGenAI } from "@google/genai";
import { useTranslation } from '../contexts/TranslationProvider';

// Access API key safely
const apiKey = import.meta.env.VITE_GEMINI_API_KEY || '';

// Initialize Gemini AI
let ai: GoogleGenAI | null = null;
try {
  ai = new GoogleGenAI({ apiKey });
} catch (e) {
  console.error("Failed to initialize GoogleGenAI:", e);
}

export const generateBotResponse = async (
  history: { role: string; parts: { text: string }[] }[],
  userMessage: string,
  language: 'ar' | 'en' | 'ku'
): Promise<string> => {
  if (!ai) {
    return "AI service is not available.";
  }

  try {
    const model = 'gemini-2.5-flash';

    const systemInstructionAr = `أنت مساعد ذكي لـ "منصة أكاديمية كمبيوتيك".
مهمتك هي مساعدة الزوار في العثور على الدورات التعليمية، الإجابة على استفساراتهم، وشرح أهداف المنصة الفنية والتعليمية.
        تحدث بلغة عربية فصحى سهلة ومحببة، وشجع الناس على التعلم وتطوير مهاراتهم بلطف.
        لا تطلب معلومات حساسة أبداً.
        إذا سألك أحد عن كيفية التسجيل، وجهه للضغط على زر "حساب جديد" أو "الدورات التدريبية" في الموقع.`;

    const systemInstructionEn = `You are an AI assistant for "Computiq Academy" platform.
Your mission is to help visitors find educational courses, answer their queries, and explain the platform's goals in technology and software development.
        Speak in friendly, clear English and encourage people to learn and develop their skills.
        Never ask for sensitive information.
        If someone asks how to register, guide them to click the "Sign Up" or "Courses" button on the site.`;

    const systemInstructionKu = `تۆ یاریدەدەرێکی زیرەکیت بۆ پلاتفۆرمی "Computiq Academy".
        ئەرکی تۆ یارمەتیدانی سەردانیکەرانە بۆ ئەوەی خولە پەروەردەییەکان بدۆزنەوە، و ڕوونکردنەوەی ئامانجەکانی پلاتفۆرمەکە لە بواری تەکنەلۆجیا.
        بە کوردییەکی سۆرانی ڕوون و دۆستانە قسە بکە و هانی خەڵک بدە بۆ فێربوون.
        هەرگیز داوای زانیاری هەستیار مەکە.
        ئەگەر کەسێک پرسیاری کرد چۆن خۆی تۆمار بکات، ڕێنمایی بکە بۆ لێدانی دوگمەی "خۆتۆمارکردن" لە ماڵپەڕەکە.`;

    let systemInstruction = systemInstructionAr;
    if (language === 'en') systemInstruction = systemInstructionEn;
    if (language === 'ku') systemInstruction = systemInstructionKu;

    const response = await ai.models.generateContent({
      model: model,
      contents: [
        ...history.map(h => ({
          role: h.role,
          parts: h.parts
        })),
        {
          role: 'user',
          parts: [{ text: userMessage }]
        }
      ],
      config: {
        systemInstruction: systemInstruction,
        temperature: 0.7,
      }
    });

    const fallback = language === 'ar' ? "عذراً، لم أتمكن من معالجة طلبك حالياً." :
      language === 'ku' ? "ببورە، لە ئێستادا نەمتوانی داواکارییەکەت جێبەجێ بکەم." :
        "Sorry, I couldn't process your request.";

    return response.text || fallback;
  } catch (error) {
    console.error("Error calling Gemini API:", error);
    const errorMsg = language === 'ar' ? "واجهت مشكلة تقنية بسيطة، يرجى المحاولة مرة أخرى لاحقاً." :
      language === 'ku' ? "کێشەیەکی تەکنیکیم هەیە، تکایە دواتر هەوڵ بدەرەوە." :
        "I encountered a technical issue, please try again later.";
    return errorMsg;
  }
};