import { useTranslation } from 'react-i18next';
import { calcDonation, fmt } from '@/Components/DonationCalculator';

interface Props {
    data: Record<string, string | boolean | number | null | undefined>;
    setData: (key: string, value: string | boolean) => void;
    errors: Partial<Record<string, string>>;
    showPersonnelNo?: boolean;
    newsletterOptional?: boolean;
    previewLaps?: number;
}

export default function SponsorFormFields({ data, setData, errors, showPersonnelNo = false, newsletterOptional = false, previewLaps = 20 }: Props) {
    const { t } = useTranslation();

    const perLap = parseFloat(String(data.donation_per_lap ?? '0').replace(',', '.')) || 0;
    const staticMax = parseFloat(String(data.donation_static_max ?? '0').replace(',', '.')) || 0;
    const preview = calcDonation(perLap, staticMax, previewLaps);
    const hasDonation = perLap > 0 || staticMax > 0;

    const inp = (name: string, label: string, type = 'text', req = false) => (
        <div key={name}>
            <label className="block text-sm font-medium mb-1">
                {label}{!req && <span className="text-gray-400 text-xs ml-1">({t('common.optional')})</span>}
            </label>
            <input
                type={type}
                value={String(data[name] ?? '')}
                onChange={(e) => setData(name, e.target.value)}
                className="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400"
            />
            {errors[name] && <p className="text-red-500 text-xs mt-1">{errors[name]}</p>}
        </div>
    );

    return (
        <div className="space-y-4">
            {showPersonnelNo && inp('ext_personnel_no', t('user.ext_personnel_no'))}
            <div className="grid grid-cols-2 gap-4">
                {inp('firstname', t('user.firstname'), 'text', true)}
                {inp('lastname', t('user.lastname'), 'text', true)}
            </div>
            <div className="grid grid-cols-3 gap-4">
                {inp('street', t('user.street'), 'text', true)}
                {inp('housenumber', t('user.housenumber'), 'text', true)}
                {inp('postcode', t('user.postcode'), 'text', true)}
            </div>
            {inp('city', t('user.city'), 'text', true)}
            {inp('phone', t('user.phone'))}
            {inp('email', t('user.email'), 'email')}
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <label className="block text-sm font-medium mb-1">{t('sponsor.donation_per_lap')}</label>
                    <input
                        type="text"
                        value={String(data.donation_per_lap ?? '')}
                        onChange={(e) => setData('donation_per_lap', e.target.value)}
                        className="w-full border rounded px-3 py-2 text-sm"
                        placeholder="0,50"
                    />
                    {errors.donation_per_lap && <p className="text-red-500 text-xs mt-1">{errors.donation_per_lap}</p>}
                </div>
                <div>
                    <label className="block text-sm font-medium mb-1">{t('sponsor.donation_static_max')}</label>
                    <input
                        type="text"
                        value={String(data.donation_static_max ?? '')}
                        onChange={(e) => setData('donation_static_max', e.target.value)}
                        className="w-full border rounded px-3 py-2 text-sm"
                        placeholder="0"
                    />
                    <p className="text-xs text-gray-400 mt-1">{t('sponsor.donation_static_max_hint')}</p>
                    {errors.donation_static_max && <p className="text-red-500 text-xs mt-1">{errors.donation_static_max}</p>}
                </div>
            </div>
            {hasDonation && (
                <div className="bg-indigo-50 border border-indigo-200 rounded px-3 py-2 text-sm">
                    <span className="text-gray-700">{t('sponsor.donation_preview', { laps: previewLaps })}: </span>
                    <span className="font-semibold text-indigo-700">{fmt(preview)}</span>
                </div>
            )}
            {newsletterOptional && (
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={Boolean(data.wants_newsletter)}
                        onChange={(e) => setData('wants_newsletter', e.target.checked)}
                    />
                    {t('user.wants_newsletter')}
                </label>
            )}
        </div>
    );
}
