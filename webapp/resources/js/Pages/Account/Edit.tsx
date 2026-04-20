import { Head, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, User } from '@/types';

interface Props extends PageProps { user: User; }

export default function AccountEdit({ user }: Props) {
    const { t } = useTranslation();
    const { data, setData, patch, errors, processing } = useForm({
        firstname: user.firstname,
        lastname: user.lastname,
        phone: user.phone ?? '',
        birthday: user.birthday,
        street: user.street,
        housenumber: user.housenumber,
        postcode: user.postcode,
        city: user.city,
        gender: user.gender,
        wants_newsletter: user.wants_newsletter ?? false,
    });

    const field = (name: keyof typeof data, label: string, type = 'text') => (
        <div key={name}>
            <label className="block text-sm font-medium mb-1">{label}</label>
            <input
                type={type}
                value={String(data[name])}
                onChange={(e) => setData(name, e.target.value as never)}
                className="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400"
            />
            {errors[name] && <p className="text-red-500 text-xs mt-1">{errors[name]}</p>}
        </div>
    );

    return (
        <AppLayout>
            <Head title={t('nav.account')} />
            <h1 className="text-xl font-bold mb-6">{t('nav.account')}</h1>

            <form onSubmit={(e) => { e.preventDefault(); patch(route('account.update')); }}
                  className="bg-white rounded border p-6 max-w-lg space-y-4">
                <div className="grid grid-cols-2 gap-4">
                    {field('firstname', t('user.firstname'))}
                    {field('lastname', t('user.lastname'))}
                </div>
                {field('birthday', t('user.birthday'), 'date')}
                {field('phone', t('user.phone'))}
                <div className="grid grid-cols-3 gap-4">
                    {field('street', t('user.street'))}
                    {field('housenumber', t('user.housenumber'))}
                    {field('postcode', t('user.postcode'))}
                </div>
                {field('city', t('user.city'))}

                <div>
                    <label className="block text-sm font-medium mb-1">{t('user.gender')}</label>
                    <select value={data.gender} onChange={(e) => setData('gender', e.target.value as 'm' | 'f')}
                            className="w-full border rounded px-3 py-2 text-sm">
                        <option value="m">{t('user.gender_m')}</option>
                        <option value="f">{t('user.gender_f')}</option>
                    </select>
                </div>

                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={Boolean(data.wants_newsletter)}
                           onChange={(e) => setData('wants_newsletter', e.target.checked as never)} />
                    {t('user.wants_newsletter')}
                </label>

                <button type="submit" disabled={processing}
                        className="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50">
                    {t('common.save')}
                </button>
            </form>
        </AppLayout>
    );
}
