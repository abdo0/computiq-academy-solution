import fs from 'fs';
import https from 'https';

// Translate a single text
async function translateText(text, targetLang) {
    if (!text || text === text.toUpperCase() || !isNaN(text) || text.length < 2) return text;
    
    // Convert target lang for Google Translate API
    const langCode = targetLang === 'ku' ? 'ckb' : targetLang;
    
    return new Promise((resolve) => {
        // Find placeholders like :count, :name, {var}, {{var}}
        const placeholders = [];
        let index = 0;
        
        let maskedText = text.replace(/(:\w+)|(\{\w+\})|(\{\{\w+\}\})/g, (match) => {
            const placeholder = `__PH${index}__`;
            placeholders.push({ placeholder, original: match });
            index++;
            return placeholder;
        });

        const url = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${langCode}&dt=t&q=${encodeURIComponent(maskedText)}`;
        
        https.get(url, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    const parsed = JSON.parse(data);
                    let translated = '';
                    if (parsed && parsed[0]) {
                        parsed[0].forEach(item => {
                            if (item[0]) translated += item[0];
                        });
                    }
                    
                    // Restore placeholders
                    placeholders.forEach(({ placeholder, original }) => {
                        // Google Translate sometimes adds spaces around or changes case of placeholders
                        const regex = new RegExp(`__\\s*P\\s*H\\s*${placeholder.replace('__PH', '').replace('__', '')}\\s*__`, 'ig');
                        translated = translated.replace(regex, original);
                        // Also try exact match just in case
                        translated = translated.replace(placeholder, original);
                    });
                    
                    resolve(translated || text);
                } catch (e) {
                    resolve(text);
                }
            });
        }).on('error', () => resolve(text));
    });
}

// Process translations
async function processLang(langCode) {
    const file = `./lang/${langCode}.json`;
    const json = JSON.parse(fs.readFileSync(file, 'utf8'));
    
    let updated = 0;
    const keys = Object.keys(json);
    
    console.log(`Processing ${langCode}.json (${keys.length} keys)...`);
    
    for (let i = 0; i < keys.length; i++) {
        const key = keys[i];
        const val = json[key];
        
        // If the value is identical to the key, it means it was just synced and needs translation
        if (val === key) {
            process.stdout.write(`Translating [${langCode}] ${i+1}/${keys.length}: "${key}"...`);
            const translated = await translateText(key, langCode);
            json[key] = translated;
            updated++;
            console.log(` -> "${translated}"`);
            
            // Save every 10 updates
            if (updated % 10 === 0) {
                fs.writeFileSync(file, JSON.stringify(json, null, 4));
            }
        }
    }
    
    // Final save
    fs.writeFileSync(file, JSON.stringify(json, null, 4));
    console.log(`✅ ${langCode}.json complete. Translated ${updated} new keys.`);
}

async function run() {
    await processLang('ar');
    await processLang('ku');
}

run();
