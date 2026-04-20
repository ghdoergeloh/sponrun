import { Head, Link, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Projectlist, SponsoredRun } from '@/types';

interface Props extends PageProps {
    sponrun: SponsoredRun;
    assignedProjectlists: Projectlist[];
    availableProjectlists: Projectlist[];
}

export default function AdminSponsoredRunEdit({ sponrun, assignedProjectlists, availableProjectlists }: Props) {
    const { t } = useTranslation();
    const [selectedAdd, setSelectedAdd] = useState<number[]>([]);
    const [selectedRemove, setSelectedRemove] = useState<number[]>([]);

    const toDatetimeLocal = (d: string) => {
        if (!d) return '';
        const dt = new Date(d);
        const pad = (n: number) => String(n).padStart(2, '0');
        return `${dt.getFullYear()}-${pad(dt.getMonth() + 1)}-${pad(dt.getDate())}T${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
    };

    const { data, setData, patch, errors, processing } = useForm({
        name: sponrun.name,
        begin: toDatetimeLocal(sponrun.begin),
        end: toDatetimeLocal(sponrun.end),
        with_tshirt: sponrun.with_tshirt,
        street: sponrun.street ?? '',
        housenumber: sponrun.housenumber ?? '',
        postcode: sponrun.postcode ?? '',
        city: sponrun.city ?? '',
        description: sponrun.description ?? '',
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

    const addProjectlists = () => {
        if (selectedAdd.length === 0) return;
        router.patch(route('admin.sponrun.addProjectlists', sponrun.id), { projectlist_ids: selectedAdd });
        setSelectedAdd([]);
    };

    const removeProjectlists = () => {
        if (selectedRemove.length === 0) return;
        router.patch(route('admin.sponrun.removeProjectlists', sponrun.id), { projectlist_ids: selectedRemove });
        setSelectedRemove([]);
    };

    return (
        <AppLayout>
            <Head title={t('run.edit')} />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('admin.sponrun.index')} className="text-sm text-gray-500 hover:underline">
                    ← {t('common.back')}
                </Link>
                <h1 className="text-xl font-bold">{sponrun.name}</h1>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Run form */}
                <form
                    onSubmit={(e) => { e.preventDefault(); patch(route('admin.sponrun.update', sponrun.id)); }}
                    className="bg-white rounded border p-6 space-y-4"
                >
                    <h2 className="font-semibold">{t('run.edit')}</h2>
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
                            className="w-full border rounded px-3 py-2 text-sm"
                        />
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={data.with_tshirt}
                            onChange={(e) => setData('with_tshirt', e.target.checked as never)}
                        />
                        {t('run.with_tshirt')}
                    </label>

                    <div className="flex gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {t('common.save')}
                        </button>
                        {sponrun.closed ? (
                            <button
                                type="button"
                                onClick={() => router.post(route('admin.sponrun.reopen', sponrun.id))}
                                className="border text-sm px-4 py-2 rounded hover:bg-gray-50"
                            >
                                {t('run.reopen')}
                            </button>
                        ) : (
                            <button
                                type="button"
                                onClick={() => router.post(route('admin.sponrun.close', sponrun.id))}
                                className="border border-orange-400 text-orange-600 text-sm px-4 py-2 rounded hover:bg-orange-50"
                            >
                                {t('run.close')}
                            </button>
                        )}
                    </div>
                </form>

                {/* Projectlists */}
                <div className="bg-white rounded border p-6 space-y-4">
                    <h2 className="font-semibold">{t('run.projectlists')}</h2>

                    <div>
                        <p className="text-xs font-medium text-gray-500 mb-2">{t('run.assigned_projectlists')}</p>
                        {assignedProjectlists.length === 0 ? (
                            <p className="text-gray-400 text-sm">{t('projectlist.no_projects')}</p>
                        ) : (
                            <div className="space-y-1">
                                {assignedProjectlists.map((pl) => (
                                    <label key={pl.id} className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={selectedRemove.includes(pl.id)}
                                            onChange={(e) => setSelectedRemove(
                                                e.target.checked
                                                    ? [...selectedRemove, pl.id]
                                                    : selectedRemove.filter(id => id !== pl.id)
                                            )}
                                        />
                                        {pl.name}
                                    </label>
                                ))}
                                <button
                                    type="button"
                                    onClick={removeProjectlists}
                                    className="mt-2 text-xs text-red-500 hover:underline"
                                >
                                    {t('common.remove')}
                                </button>
                            </div>
                        )}
                    </div>

                    <div>
                        <p className="text-xs font-medium text-gray-500 mb-2">{t('run.available_projectlists')}</p>
                        {availableProjectlists.length === 0 ? (
                            <p className="text-gray-400 text-sm">–</p>
                        ) : (
                            <div className="space-y-1">
                                {availableProjectlists.map((pl) => (
                                    <label key={pl.id} className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={selectedAdd.includes(pl.id)}
                                            onChange={(e) => setSelectedAdd(
                                                e.target.checked
                                                    ? [...selectedAdd, pl.id]
                                                    : selectedAdd.filter(id => id !== pl.id)
                                            )}
                                        />
                                        {pl.name}
                                    </label>
                                ))}
                                <button
                                    type="button"
                                    onClick={addProjectlists}
                                    className="mt-2 text-xs text-indigo-600 hover:underline"
                                >
                                    {t('common.add')}
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
