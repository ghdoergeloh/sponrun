import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

import de from './locales/de/translation.json';
import en from './locales/en/translation.json';
import ru from './locales/ru/translation.json';
import es from './locales/es/translation.json';
import fr from './locales/fr/translation.json';
import pt from './locales/pt/translation.json';

i18n
    .use(LanguageDetector)
    .use(initReactI18next)
    .init({
        fallbackLng: 'de',
        supportedLngs: ['de', 'en', 'ru', 'es', 'fr', 'pt'],
        resources: {
            de: { translation: de },
            en: { translation: en },
            ru: { translation: ru },
            es: { translation: es },
            fr: { translation: fr },
            pt: { translation: pt },
        },
        interpolation: { escapeValue: false },
    });

export default i18n;
