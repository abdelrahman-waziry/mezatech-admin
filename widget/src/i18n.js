const translations = {
    en: {
        'trade-in': 'Trade-in valuation',
        'select-device': 'Select your device',
        'choose-device': 'Choose your model and storage to get started.',
        model: 'Model',
        'choose-model': 'Choose a model',
        variant: 'Storage / Variant',
        'choose-variant': 'Choose a variant',
        continue: 'Continue',
        back: 'Back',
        next: 'Next',
        step: 'Step',
        of: 'of',
        'almost-done': 'Almost done',
        details: 'Your details',
        'contact-subtitle': "We'll use these to send your trade-in quote.",
        'full-name': 'Full name',
        email: 'Email',
        'phone-number': 'Phone number',
        calculating: 'Calculating...',
        'get-quote': 'Get my quote',
        offer: 'Your offer',
        valuation: 'Trade-in valuation',
        decline: 'Decline',
        'accept-offer': 'Accept offer',
        result: 'Result',
        'not-eligible': "We're unable to provide a quote",
        'not-eligible-subtitle': "Based on your answers, this device isn't eligible for trade-in right now.",
        'start-over': 'Start over',
        'repair-details': '3rd-party repair details',
        'repaired-parts': 'Repaired parts',
        'repair-subtitle': 'You told us this device was repaired locally / by a 3rd party. Tell us which parts were replaced.',
        'no-parts': 'No parts found for this device.',
        'replaced-part': 'Replaced by a local / 3rd-party shop',
        'device-value': 'Device value',
        'parts-adjustment': 'Parts adjustment',
        currency: 'EGP',
        'loading-questionnaire': 'Loading questionnaire...',
        'generic-error': 'Something went wrong.',
        'error-missing-client': 'This calculator is not configured correctly. The embed client ID is missing.',
        'error-invalid-client': 'This embed client ID is invalid or expired. Please ask the website owner to generate a new key from the vendor dashboard.',
        'error-suspended-client': 'This trade-in calculator is temporarily unavailable because the vendor account is suspended.',
        'error-origin': 'This calculator is not authorized to run on this website. Please contact the website owner.',
        'error-rate-limit': 'Too many requests were sent. Please wait a moment and try again.',
        questions: {
            power: { title: 'Power & Functionality', question: 'Is your device functional and able to turn on?', options: { yes: 'Yes, it works perfectly', no: "No, it won't turn on" } },
            repair_history: { title: 'Repair History', question: 'Has your device been repaired before?', options: { never: 'Never repaired', authorized: 'Authorised Service', local: 'Local / 3rd Party' } },
            battery_health: { title: 'Battery Health', question: 'What is the maximum capacity of your battery?', options: { '90+': '90% or higher', '85-89': '85% - 89%', '80-84': '80% - 84%', below80: 'Below 80% / Service' } },
            screen_body: { title: 'Screen & Body', question: 'Is the screen and back glass completely intact with NO cracks?', options: { yes: 'Yes, perfect condition', no: 'No, there are cracks' }, checkboxes: { 'Front Screen': 'Front Screen', 'Back Glass': 'Back Glass' } },
            cosmetics: { title: 'Cosmetics', question: 'Are there any scratches on the body or screen?', options: { none: 'No scratches (Mint)', light: 'Light scratches', visible: 'Visible / Deep' } },
            hardware: { title: 'Hardware Check', question: 'Do all buttons, speakers, and ports work properly?', options: { yes: 'Yes, all working', no: 'No, some issues' }, checkboxes: { 'Charging Port': 'Charging Port', 'Volume Buttons': 'Volume Buttons', 'Power Button': 'Power Button', Earpiece: 'Earpiece', Loudspeaker: 'Loudspeaker', 'Vibration Motor': 'Vibration Motor' } },
            cameras: { title: 'Cameras', question: 'Test your cameras. Do they work without spots or shaking?', options: { yes: 'Yes, crystal clear', no: 'No, I see issues' }, checkboxes: { 'Front Camera': 'Front Camera', 'Back Camera': 'Back Camera' } },
            connectivity: { title: 'Connectivity', question: 'Do Wi-Fi, Bluetooth, GPS, and NFC work?', options: { yes: 'Yes, all connect', no: 'No, connectivity issues' }, checkboxes: { Network: 'Network', WiFi: 'Wi-Fi', Bluetooth: 'Bluetooth', NFC: 'NFC', GPS: 'GPS' } },
            biometrics: { title: 'Biometrics', question: 'Does Face ID or Fingerprint sensor work properly?', options: { yes: 'Yes, works fine', no: 'No, not working' } },
        },
    },
    ar: {
        'trade-in': 'تقييم الجهاز',
        'select-device': 'اختر جهازك',
        'choose-device': 'اختر موديل الجهاز والسعة للبدء.',
        model: 'الموديل',
        'choose-model': 'اختر الموديل',
        variant: 'السعة / الإصدار',
        'choose-variant': 'اختر الإصدار',
        continue: 'متابعة',
        back: 'رجوع',
        next: 'التالي',
        step: 'الخطوة',
        of: 'من',
        'almost-done': 'أوشكت على الانتهاء',
        details: 'بياناتك',
        'contact-subtitle': 'سنستخدم هذه البيانات لإرسال عرض الاستبدال الخاص بك.',
        'full-name': 'الاسم الكامل',
        email: 'البريد الإلكتروني',
        'phone-number': 'رقم الهاتف',
        calculating: 'جاري الحساب...',
        'get-quote': 'احصل على عرض السعر',
        offer: 'عرضك',
        valuation: 'تقييم الجهاز',
        decline: 'رفض',
        'accept-offer': 'قبول العرض',
        result: 'النتيجة',
        'not-eligible': 'لا يمكننا تقديم عرض سعر',
        'not-eligible-subtitle': 'بناءً على إجاباتك، هذا الجهاز غير مؤهل للاستبدال حالياً.',
        'start-over': 'البدء من جديد',
        'repair-details': 'تفاصيل الإصلاح الخارجي',
        'repaired-parts': 'الأجزاء التي تم إصلاحها',
        'repair-subtitle': 'ذكرت أن الجهاز تم إصلاحه محلياً أو لدى طرف ثالث. حدد الأجزاء التي تم استبدالها.',
        'no-parts': 'لا توجد أجزاء لهذا الجهاز.',
        'replaced-part': 'تم استبداله لدى محل إصلاح محلي أو طرف ثالث',
        'device-value': 'قيمة الجهاز',
        'parts-adjustment': 'تعديل الأجزاء',
        currency: 'جنيه',
        'loading-questionnaire': 'جاري تحميل الأسئلة...',
        'generic-error': 'حدث خطأ ما.',
        'error-missing-client': 'لم يتم إعداد حاسبة الاستبدال بشكل صحيح. مفتاح العميل غير موجود.',
        'error-invalid-client': 'مفتاح العميل غير صحيح أو منتهي الصلاحية. يرجى مطالبة مسؤول الموقع بإنشاء مفتاح جديد من لوحة تحكم البائع.',
        'error-suspended-client': 'حاسبة الاستبدال غير متاحة مؤقتاً لأن حساب البائع موقوف.',
        'error-origin': 'هذه الحاسبة غير مصرح لها بالعمل على هذا الموقع. يرجى التواصل مع مسؤول الموقع.',
        'error-rate-limit': 'تم إرسال طلبات كثيرة. يرجى الانتظار قليلاً ثم المحاولة مرة أخرى.',
        questions: {
            power: { title: 'الطاقة والوظائف', question: 'هل الجهاز يعمل ويمكن تشغيله؟', options: { yes: 'نعم، يعمل بشكل مثالي', no: 'لا، لا يعمل' } },
            repair_history: { title: 'سجل الإصلاح', question: 'هل تم إصلاح الجهاز من قبل؟', options: { never: 'لم يتم إصلاحه', authorized: 'مركز خدمة معتمد', local: 'إصلاح محلي / طرف ثالث' } },
            battery_health: { title: 'صحة البطارية', question: 'ما السعة القصوى لبطارية الجهاز؟', options: { '90+': '90% أو أكثر', '85-89': '85% - 89%', '80-84': '80% - 84%', below80: 'أقل من 80% / تحتاج إلى خدمة' } },
            screen_body: { title: 'الشاشة والهيكل', question: 'هل الشاشة والزجاج الخلفي سليمان تماماً وبدون كسور؟', options: { yes: 'نعم، بحالة ممتازة', no: 'لا، توجد كسور' }, checkboxes: { 'Front Screen': 'الشاشة الأمامية', 'Back Glass': 'الزجاج الخلفي' } },
            cosmetics: { title: 'المظهر الخارجي', question: 'هل توجد خدوش على الهيكل أو الشاشة؟', options: { none: 'لا توجد خدوش (كالجديد)', light: 'خدوش خفيفة', visible: 'خدوش واضحة / عميقة' } },
            hardware: { title: 'فحص الأجهزة', question: 'هل تعمل الأزرار والسماعات والمنافذ بشكل صحيح؟', options: { yes: 'نعم، كلها تعمل', no: 'لا، توجد بعض المشاكل' }, checkboxes: { 'Charging Port': 'منفذ الشحن', 'Volume Buttons': 'أزرار الصوت', 'Power Button': 'زر التشغيل', Earpiece: 'سماعة المكالمات', Loudspeaker: 'السماعة الخارجية', 'Vibration Motor': 'محرك الاهتزاز' } },
            cameras: { title: 'الكاميرات', question: 'اختبر الكاميرات. هل تعمل بدون بقع أو اهتزاز؟', options: { yes: 'نعم، الصورة واضحة', no: 'لا، توجد مشاكل' }, checkboxes: { 'Front Camera': 'الكاميرا الأمامية', 'Back Camera': 'الكاميرا الخلفية' } },
            connectivity: { title: 'الاتصال', question: 'هل تعمل شبكة الهاتف والواي فاي والبلوتوث وGPS وNFC؟', options: { yes: 'نعم، كلها تعمل', no: 'لا، توجد مشاكل في الاتصال' }, checkboxes: { Network: 'الشبكة', WiFi: 'الواي فاي', Bluetooth: 'البلوتوث', NFC: 'NFC', GPS: 'GPS' } },
            biometrics: { title: 'المقاييس الحيوية', question: 'هل تعمل بصمة الوجه أو بصمة الإصبع بشكل صحيح؟', options: { yes: 'نعم، تعمل جيداً', no: 'لا، لا تعمل' } },
        },
    },
};

export function normalizeLanguage(value) {
    return String(value || 'en').toLowerCase().startsWith('ar') ? 'ar' : 'en';
}

export function createTranslator(language) {
    const locale = translations[normalizeLanguage(language)];
    return (key) => locale[key] ?? translations.en[key] ?? key;
}

export function translateQuestionnaireStep(step, language) {
    const locale = translations[normalizeLanguage(language)].questions[step.key];
    if (!locale) return step;

    return {
        ...step,
        title: locale.title,
        question: locale.question,
        options: step.options.map((option) => ({
            ...option,
            label: locale.options?.[option.value] ?? option.label,
            conditional_checkboxes: option.conditional_checkboxes?.map((checkbox) => ({
                ...checkbox,
                label: locale.checkboxes?.[checkbox.value] ?? checkbox.label,
            })),
        })),
    };
}

export function isRtl(language) {
    return normalizeLanguage(language) === 'ar';
}
