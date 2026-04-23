import { useTranslation } from 'react-i18next';

const LANGUAGES = [
    { code: 'de', label: 'DE' },
    { code: 'en', label: 'EN' },
    { code: 'fr', label: 'FR' },
    { code: 'es', label: 'ES' },
    { code: 'pt', label: 'PT' },
    { code: 'ru', label: 'RU' },
];

export default function LanguageSwitcher({ variant = 'light' }: { variant?: 'light' | 'dark' }) {
    const { i18n } = useTranslation();
    const current = i18n.resolvedLanguage ?? i18n.language;

    const base = variant === 'light'
        ? 'border-white/40 text-white hover:bg-white/20'
        : 'border-gray-300 text-gray-700 hover:bg-gray-100';
    const active = variant === 'light'
        ? 'bg-white text-red-600 border-white'
        : 'bg-red-500 text-white border-red-500';

    return (
        <div className="flex flex-wrap gap-1">
            {LANGUAGES.map((lang) => (
                <button
                    key={lang.code}
                    type="button"
                    onClick={() => i18n.changeLanguage(lang.code)}
                    className={`text-xs font-semibold px-2.5 py-1 rounded border transition ${
                        current === lang.code ? active : base
                    }`}
                    aria-pressed={current === lang.code}
                >
                    {lang.label}
                </button>
            ))}
        </div>
    );
}
