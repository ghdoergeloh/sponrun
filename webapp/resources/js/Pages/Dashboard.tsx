import { Head, router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, SponsoredRun } from '@/types';

interface Props extends PageProps {
    sponruns: SponsoredRun[];
    userParticipations: number[];
}

export default function Dashboard({ sponruns, userParticipations }: Props) {
    const { t } = useTranslation();
    const { auth } = usePage<PageProps>().props;

    const join = (runId: number) => {
        router.post(route('runpart.store'), { sponsored_run_id: runId });
    };

    const formatDate = (dateStr: string) =>
        new Date(dateStr).toLocaleString('de-DE', { dateStyle: 'medium', timeStyle: 'short' });

    return (
        <AppLayout>
            <Head title={t('nav.home')} />

            <h1 className="text-2xl font-bold mb-6">
                {t('dashboard.welcome', { name: auth.user?.firstname })}
            </h1>

            <h2 className="text-lg font-semibold mb-3">{t('dashboard.next_runs')}</h2>

            {sponruns.length === 0 ? (
                <p className="text-gray-500">{t('dashboard.no_runs')}</p>
            ) : (
                <div className="overflow-x-auto rounded border bg-white">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100 text-left">
                            <tr>
                                <th className="px-4 py-2">{t('dashboard.name')}</th>
                                <th className="px-4 py-2">{t('dashboard.date')}</th>
                                <th className="px-4 py-2">{t('dashboard.participants')}</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {sponruns.map((run) => {
                                const joined = userParticipations.includes(run.id);
                                return (
                                    <tr key={run.id} className="border-t hover:bg-gray-50">
                                        <td className="px-4 py-2 font-medium">{run.name}</td>
                                        <td className="px-4 py-2 text-gray-600">
                                            {formatDate(run.begin)}
                                        </td>
                                        <td className="px-4 py-2">{run.participants_count ?? 0}</td>
                                        <td className="px-4 py-2">
                                            {joined ? (
                                                <span className="text-indigo-600 font-medium text-xs">
                                                    ✓ {t('dashboard.already_joined')}
                                                </span>
                                            ) : (
                                                <button
                                                    onClick={() => join(run.id)}
                                                    className="bg-indigo-600 text-white text-xs px-3 py-1 rounded hover:bg-indigo-700"
                                                >
                                                    {t('dashboard.join')}
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            )}
        </AppLayout>
    );
}
