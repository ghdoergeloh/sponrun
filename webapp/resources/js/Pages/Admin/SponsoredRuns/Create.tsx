import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps } from '@/types';

export default function AdminSponsoredRunCreate(_: PageProps) {
    const { t } = useTranslation();

    const { data, setData, post, errors, processing } = useForm({
        name: '',
        begin: '',
        end: '',
        with_tshirt: false,
        street: '',
        housenumber: '',
        postcode: '',
        city: '',
        description: '',
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
            />
            {errors[name] && <p className="text-red-500 text-xs mt-1">{errors[name]}</p>}
        </div>
    );

    return (
        <AppLayout>
            <Head title={t('run.create')} />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('admin.sponrun.index')} className="text-sm text-gray-500 hover:underline">
                    ← {t('common.back')}
                </Link>
                <h1 className="text-xl font-bold">{t('run.create')}</h1>
            </div>

            <form
                onSubmit={(e) => { e.preventDefault(); post(route('admin.sponrun.store')); }}
                className="bg-white rounded border p-6 max-w-lg space-y-4"
            >
                {field('name', t('run.name'))}
                <div className="grid grid-cols-2 gap-4">
                    {field('begin', t('run.begin'), 'datetime-local')}
                    {field('end', t('run.end'), 'datetime-local')}
                </div>
                <div className="grid grid-cols-3 gap-4">
                    {field('street', t('run.street'), 'text', false)}
                    {field('housenumber', t('run.housenumber'), 'text', false)}
                    {field('postcode', t('run.postcode'), 'text', false)}
                </div>
                {field('city', t('run.city'), 'text', false)}
                <div>
                    <label className="block text-sm font-medium mb-1">{t('run.description')}</label>
                    <textarea
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        rows={3}
                        className="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400"
                    />
                    {errors.description && <p className="text-red-500 text-xs mt-1">{errors.description}</p>}
                </div>
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={data.with_tshirt}
                        onChange={(e) => setData('with_tshirt', e.target.checked as never)}
                    />
                    {t('run.with_tshirt')}
                </label>
                {errors.with_tshirt && <p className="text-red-500 text-xs">{errors.with_tshirt}</p>}

                <div className="flex gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {t('common.save')}
                    </button>
                    <Link
                        href={route('admin.sponrun.index')}
                        className="text-sm text-gray-500 px-4 py-2 rounded border hover:bg-gray-50"
                    >
                        {t('common.cancel')}
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
