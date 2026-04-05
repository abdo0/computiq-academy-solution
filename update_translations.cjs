const fs = require('fs');

const arAdditions = {
    "Invoice Details": "تفاصيل الفاتورة",
    "Invoice": "الفاتورة",
    "INVOICE": "الفاتورة",
    "Print Invoice": "طباعة الفاتورة",
    "Print": "طباعة",
    "View": "عرض",
    "View Invoice": "عرض الفاتورة",
    "No items": "لا توجد عناصر",
    "Thank you for choosing Computiq Academy": "شكراً لاختيارك أكاديمية كومبيوتك"
};

const kuAdditions = {
    "Invoice Details": "وردەکارییەکانی پسووڵە",
    "Invoice": "پسووڵە",
    "INVOICE": "پسووڵە",
    "Print Invoice": "چاپکردنی پسووڵە",
    "Print": "چاپکردن",
    "View": "پیشاندان",
    "View Invoice": "پیشاندانی پسووڵە",
    "No items": "هیچ بڕگەیەک نییە",
    "Thank you for choosing Computiq Academy": "سوپاس بۆ هەڵبژاردنی ئەکادیمیای کۆمپیوتک"
};

const enAdditions = {
    "Invoice Details": "Invoice Details",
    "Invoice": "Invoice",
    "INVOICE": "INVOICE",
    "Print Invoice": "Print Invoice",
    "Print": "Print",
    "View": "View",
    "View Invoice": "View Invoice",
    "No items": "No items",
    "Thank you for choosing Computiq Academy": "Thank you for choosing Computiq Academy"
};

const updateFile = (file, additions) => {
    let data = {};
    if (fs.existsSync(file)) {
        data = JSON.parse(fs.readFileSync(file, 'utf8'));
    }
    for (const [key, val] of Object.entries(additions)) {
        // overwrite or add
        data[key] = val;
    }
    fs.writeFileSync(file, JSON.stringify(data, null, 4));
};

updateFile('lang/ar.json', arAdditions);
updateFile('lang/en.json', enAdditions);
updateFile('lang/ku.json', kuAdditions);
console.log("Translations added.");
