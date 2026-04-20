import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, SponsoredRun } from '@/types';

interface Props extends PageProps {
    sponruns: (SponsoredRun & { participants_count: number })[];
}

export default function AdminSponsoredRunsIndex({ sponruns }: Props) {
    const { t } = useTranslation();

    const fmt = (d: string) => new Date(d).toLocaleString('de-DE', { dateStyle: 'medium', timeStyle: 'short' });

    const deletRun = (id: number) => {
        if (confirm(t('common.confirm_delete'))) {
            router.delete(route('admin.sponrun.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title={t('nav.runs')} />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-xl font-bold">{t('nav.runs')}</h1>
                <Link
                    href={route('admin.sponrun.create')}
                    className="bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700"
                >
                    + {t('run.create')}
                </Link>
            </div>

            {sponruns.length === 0 ? (
                <p className="text-gray-500 text-sm">{t('dashboard.no_runs')}</p>
            ) : (
                <div className="bg-white rounded border overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="px-4 py-2 text-left">{t('run.name')}</th>
                                <th className="px-4 py-2 text-left">{t('run.begin')}</th>
                                <th className="px-4 py-2 text-right">{t('dashboard.participants')}</th>
                                <th className="px-4 py-2 text-center">{t('run.closed')}</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {sponruns.map((run) => (
                                <tr key={run.id} className="border-t hover:bg-gray-50">
                                    <td className="px-4 py-2 font-medium">
                                        <Link href={route('admin.sponrun.show', run.id)} className="hover:underline text-indigo-700">
                                            {run.name}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2 text-gray-600">{fmt(run.begin)}</td>
                                    <td className="px-4 py-2 text-right">{run.participants_count}</td>
                                    <td className="px-4 py-2 text-center">
                                        {run.closed ? (
                                            <span className="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">✗</span>
                                        ) : (
                                            <span className="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">✓</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-2 flex gap-3 justify-end">
                                        <Link href={route('admin.sponrun.show', run.id)} className="text-indigo-600 hover:underline text-xs">
                                            {t('run.statistics')}
                                        </Link>
                                        <Link href={route('admin.sponrun.edit', run.id)} className="text-indigo-600 hover:underline text-xs">
                                            {t('common.edit')}
                                        </Link>
                                        <button onClick={() => deletRun(run.id)} className="text-red-500 hover:underline text-xs">
                                            {t('common.delete')}
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </AppLayout>
    );
}
