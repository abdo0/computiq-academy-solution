import fs from 'fs';
import https from 'https';
import path from 'path';
import { fileURLToPath } from 'url';

// Load .env file manually
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const envPath = path.join(__dirname, '.env');
if (fs.existsSync(envPath)) {
    const envContent = fs.readFileSync(envPath, 'utf8');
    envContent.split('\n').forEach(line => {
        const match = line.match(/^\s*([\w\.\-]+)\s*=\s*(.*)?\s*$/);
        if (match) {
            let key = match[1];
            let value = match[2] || '';
            if (value.length > 0 && value.charAt(0) === '"' && value.charAt(value.length - 1) === '"') {
                value = value.replace(/\\n/gm, '\n');
            }
            value = value.replace(/(^['"]|['"]$)/g, '').trim();
            process.env[key] = value;
        }
    });
}

const GEMINI_API_KEY = process.env.VITE_GEMINI_API_KEY || process.env.GEMINI_API_KEY;

if (!GEMINI_API_KEY) {
    console.error('❌ Error: VITE_GEMINI_API_KEY or GEMINI_API_KEY is not defined in .env');
    process.exit(1);
}

// Function to translate a batch of texts using Gemini
async function translateBatchWithGemini(keysToTranslate, targetLang) {
    if (!keysToTranslate || keysToTranslate.length === 0) return {};

    const langName = targetLang === 'ar' ? 'Arabic' : 'Kurdish (Sorani)';
    
    // Create the prompt instructions
    const prompt = `
You are an expert software localization translator. Translate the following English UI strings into natural, contextually accurate ${langName}.
Rules:
1. Maintain the exact same tone (professional, encouraging, e-learning platform context).
2. DO NOT translate placeholders starting with ":" (like :count, :name) or wrapped in brackets (like {name}). Keep them EXACTLY as they are.
3. Return ONLY a valid JSON object where keys are the original English strings and values are the ${langName} translations. Do not include markdown blocks like \`\`\`json.

Strings to translate:
${JSON.stringify(keysToTranslate, null, 2)}
`;

    const requestBody = JSON.stringify({
        contents: [{ parts: [{ text: prompt }] }],
        generationConfig: {
            temperature: 0.1,
            responseMimeType: "application/json"
        }
    });

    const options = {
        hostname: 'generativelanguage.googleapis.com',
        path: `/v1beta/models/gemini-2.5-flash:generateContent?key=${GEMINI_API_KEY}`,
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Content-Length': Buffer.byteLength(requestBody)
        }
    };

    return new Promise((resolve) => {
        const req = https.request(options, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    const parsed = JSON.parse(data);
                    if (parsed.candidates && parsed.candidates.length > 0) {
                        const responseText = parsed.candidates[0].content.parts[0].text;
                        // Clean up markdown just in case the model ignored the instruction
                        const cleanJson = responseText.replace(/```json\n?/g, '').replace(/```\n?/g, '').trim();
                        const resultMap = JSON.parse(cleanJson);
                        resolve(resultMap);
                    } else {
                        console.error('Gemini returned an unexpected response structure:', data);
                        resolve({});
                    }
                } catch (e) {
                    console.error('Failed to parse Gemini response:', e.message);
                    console.error('Raw Response:', data);
                    resolve({});
                }
            });
        });

        req.on('error', (e) => {
            console.error(`Problem with request: ${e.message}`);
            resolve({});
        });

        req.write(requestBody);
        req.end();
    });
}

// Process translations
async function processLang(langCode) {
    const file = path.join(__dirname, 'lang', `${langCode}.json`);
    
    if (!fs.existsSync(file)) {
        console.error(`File not found: ${file}`);
        return;
    }
    
    const json = JSON.parse(fs.readFileSync(file, 'utf8'));
    
    let keysToTranslate = [];
    const keys = Object.keys(json);
    
    // Find missing keys
    for (let i = 0; i < keys.length; i++) {
        const key = keys[i];
        const val = json[key];
        
        // If the value is identical to the key, it means it was just synced and needs translation
        if (val === key && isNaN(key) && key !== key.toUpperCase() && key.length > 1) {
            keysToTranslate.push(key);
        }
    }
    
    if (keysToTranslate.length === 0) {
         console.log(`✅ ${langCode}.json is already fully translated.`);
         return;
    }

    console.log(`⏳ Processing ${langCode}.json (${keysToTranslate.length} new keys)...`);
    
    // Process in batches of 20 to avoid overwhelming the token limit
    const batchSize = 20;
    let updatedCount = 0;
    
    for (let i = 0; i < keysToTranslate.length; i += batchSize) {
        const batchKeys = keysToTranslate.slice(i, i + batchSize);
        console.log(`   - Translating batch ${Math.floor(i/batchSize) + 1}/${Math.ceil(keysToTranslate.length/batchSize)} ...`);
        
        const translatedMap = await translateBatchWithGemini(batchKeys, langCode);
        
        for (const [enKey, translatedText] of Object.entries(translatedMap)) {
            if (json[enKey] && translatedText && typeof translatedText === 'string') {
                json[enKey] = translatedText;
                updatedCount++;
            }
        }
        
        // Save intermediate results
        fs.writeFileSync(file, JSON.stringify(json, null, 4));
    }
    
    console.log(`✅ ${langCode}.json complete. Translated ${updatedCount}/${keysToTranslate.length} new keys using Gemini 2.5 Flash.`);
}

async function run() {
    await processLang('ar');
    console.log('-----------------------------------');
    await processLang('ku');
}

run();
