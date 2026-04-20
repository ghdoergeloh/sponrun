import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Register() {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors, reset } = useForm({
        firstname: '',
        lastname: '',
        email: '',
        password: '',
        password_confirmation: '',
        birthday: '',
        phone: '',
        street: '',
        housenumber: '',
        postcode: '',
        city: '',
        gender: 'm' as 'm' | 'f',
        wants_newsletter: false,
    });

    const field = (name: keyof typeof data, label: string, type = 'text', req = true) => (
        <div key={name}>
            <label className="block text-sm font-medium mb-1">
                {label}{!req && <span className="text-gray-400 text-xs ml-1">({t('common.optional')})</span>}
            </label>
            <input
                type={type}
                value={String(data[name])}
                onChange={(e) => setData(name, e.target.value as never)}
                className="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400"
                required={req}
            />
            {errors[name] && <p className="text-red-500 text-xs mt-1">{errors[name]}</p>}
        </div>
    );

    return (
        <GuestLayout>
            <Head title={t('auth.register')} />

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    post(route('register'), { onFinish: () => reset('password', 'password_confirmation') });
                }}
                className="space-y-4"
            >
                <div className="grid grid-cols-2 gap-4">
                    {field('firstname', t('user.firstname'))}
                    {field('lastname', t('user.lastname'))}
                </div>

                {field('email', t('user.email'), 'email')}
                {field('birthday', t('user.birthday'), 'date')}
                {field('phone', t('user.phone'), 'tel', false)}

                <div className="grid grid-cols-3 gap-4">
                    {field('street', t('user.street'))}
                    {field('housenumber', t('user.housenumber'))}
                    {field('postcode', t('user.postcode'))}
                </div>
                {field('city', t('user.city'))}

                <div>
                    <label className="block text-sm font-medium mb-1">{t('user.gender')}</label>
                    <select
                        value={data.gender}
                        onChange={(e) => setData('gender', e.target.value as 'm' | 'f')}
                        className="w-full border rounded px-3 py-2 text-sm"
                    >
                        <option value="m">{t('user.gender_m')}</option>
                        <option value="f">{t('user.gender_f')}</option>
                    </select>
                </div>

                {field('password', t('user.password'), 'password')}
                {field('password_confirmation', t('user.password_confirmation'), 'password')}

                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={data.wants_newsletter}
                        onChange={(e) => setData('wants_newsletter', e.target.checked as never)}
                    />
                    {t('user.wants_newsletter')}
                </label>

                <div className="flex items-center justify-between pt-2">
                    <Link href={route('login')} className="text-sm text-gray-600 hover:underline">
                        {t('auth.login')}
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {t('auth.register')}
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}
