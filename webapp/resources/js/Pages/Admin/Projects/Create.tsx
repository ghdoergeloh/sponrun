import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps } from '@/types';

export default function AdminProjectCreate(_: PageProps) {
    const { t } = useTranslation();

    const { data, setData, post, errors, processing } = useForm({
        id: '',
        name: '',
        scope: 'project' as 'project' | 'person',
    });

    return (
        <AppLayout>
            <Head title={t('project.create')} />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('admin.project.index')} className="text-sm text-gray-500 hover:underline">
                    ← {t('common.back')}
                </Link>
                <h1 className="text-xl font-bold">{t('project.create')}</h1>
            </div>

            <form
                onSubmit={(e) => { e.preventDefault(); post(route('admin.project.store')); }}
                className="bg-white rounded border p-6 max-w-md space-y-4"
            >
                <div>
                    <label className="block text-sm font-medium mb-1">{t('project.id')}</label>
                    <input
                        type="number"
                        value={data.id}
                        onChange={(e) => setData('id', e.target.value)}
                        className="w-full border rounded px-3 py-2 text-sm"
                        required
                    />
                    {errors.id && <p className="text-red-500 text-xs mt-1">{errors.id}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium mb-1">{t('project.name')}</label>
                    <input
                        type="text"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        className="w-full border rounded px-3 py-2 text-sm"
                        required
                    />
                    {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium mb-1">{t('project.scope')}</label>
                    <select
                        value={data.scope}
                        onChange={(e) => setData('scope', e.target.value as 'project' | 'person')}
                        className="w-full border rounded px-3 py-2 text-sm"
                    >
                        <option value="project">{t('project.scope_project')}</option>
                        <option value="person">{t('project.scope_person')}</option>
                    </select>
                    {errors.scope && <p className="text-red-500 text-xs mt-1">{errors.scope}</p>}
                </div>

                <div className="flex gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {t('common.save')}
                    </button>
                    <Link href={route('admin.project.index')} className="text-sm text-gray-500 px-4 py-2 rounded border hover:bg-gray-50">
                        {t('common.cancel')}
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
