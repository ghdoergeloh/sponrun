import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { fmt } from '@/Components/DonationCalculator';
import { PageProps, RunParticipation, SponsoredRun } from '@/types';

interface RunnerStat {
    firstname: string;
    lastname: string;
    value: number | string;
}

interface ShowSponsoredRun extends SponsoredRun {
    total_laps: number;
    total_donation_sum: number;
    most_laps_runners: RunnerStat[];
    most_sponsors_runners: RunnerStat[];
    highest_donation_runners: RunnerStat[];
    oldest_participants: RunnerStat[];
    youngest_participants: RunnerStat[];
}

interface Props extends PageProps {
    sponrun: ShowSponsoredRun;
    runParticipations: (RunParticipation & { donation_sum: number })[];
}

export default function AdminSponsoredRunShow({ sponrun, runParticipations }: Props) {
    const { t } = useTranslation();

    const deleteParticipation = (rpId: number) => {
        if (confirm(t('common.confirm_delete'))) {
            router.delete(route('admin.sponrun.runpart.destroy', [sponrun.id, rpId]));
        }
    };

    const statCard = (label: string, value: string | number, sub?: string) => (
        <div className="bg-white rounded border p-4">
            <p className="text-xs text-gray-500">{label}</p>
            <p className="text-xl font-bold">{value}</p>
            {sub && <p className="text-xs text-gray-400">{sub}</p>}
        </div>
    );

    const runnerList = (runners: RunnerStat[]) =>
        runners.map((r, i) => (
            <span key={i}>{r.firstname} {r.lastname}{runners.length - 1 > i ? ', ' : ''}</span>
        ));

    return (
        <AppLayout>
            <Head title={sponrun.name} />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('admin.sponrun.index')} className="text-sm text-gray-500 hover:underline">
                    ← {t('common.back')}
                </Link>
                <h1 className="text-xl font-bold">{sponrun.name}</h1>
                <div className="ml-auto flex gap-2">
                    <a
                        href={route('admin.sponrun.evaluation', sponrun.id)}
                        className="bg-green-600 text-white text-xs px-3 py-2 rounded hover:bg-green-700"
                    >
                        ↓ {t('run.evaluation')}
                    </a>
                    <Link
                        href={route('admin.sponrun.edit', sponrun.id)}
                        className="bg-indigo-600 text-white text-xs px-3 py-2 rounded hover:bg-indigo-700"
                    >
                        {t('common.edit')}
                    </Link>
                    {sponrun.closed ? (
                        <button
                            onClick={() => router.post(route('admin.sponrun.reopen', sponrun.id))}
                            className="border text-xs px-3 py-2 rounded hover:bg-gray-50"
                        >
                            {t('run.reopen')}
                        </button>
                    ) : (
                        <button
                            onClick={() => router.post(route('admin.sponrun.close', sponrun.id))}
                            className="border border-orange-400 text-orange-600 text-xs px-3 py-2 rounded hover:bg-orange-50"
                        >
                            {t('run.close')}
                        </button>
                    )}
                </div>
            </div>

            {/* Stats */}
            <h2 className="font-semibold mb-3">{t('run.statistics')}</h2>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                {statCard(t('run.total_participants'), runParticipations.length)}
                {statCard(t('run.total_laps'), sponrun.total_laps)}
                {statCard(t('run.total_amount'), fmt(sponrun.total_donation_sum))}
                {statCard(t('run.most_laps'), sponrun.most_laps_runners[0]?.value ?? '–',
                    runnerList(sponrun.most_laps_runners).length > 0 ? undefined : undefined)}
                {statCard(t('run.highest_donation'), sponrun.highest_donation_runners[0] ? fmt(Number(sponrun.highest_donation_runners[0].value)) : '–')}
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div className="bg-white rounded border p-4">
                    <p className="text-xs text-gray-500 mb-1">{t('run.most_laps')}</p>
                    <p className="text-sm">{sponrun.most_laps_runners.length > 0 ? runnerList(sponrun.most_laps_runners) : '–'}</p>
                </div>
                <div className="bg-white rounded border p-4">
                    <p className="text-xs text-gray-500 mb-1">{t('run.most_sponsors')}</p>
                    <p className="text-sm">{sponrun.most_sponsors_runners.length > 0 ? runnerList(sponrun.most_sponsors_runners) : '–'}</p>
                </div>
                <div className="bg-white rounded border p-4">
                    <p className="text-xs text-gray-500 mb-1">{t('run.youngest')}</p>
                    <p className="text-sm">{sponrun.youngest_participants.length > 0 ? runnerList(sponrun.youngest_participants) : '–'}</p>
                </div>
            </div>

            {/* Participants table */}
            <h2 className="font-semibold mb-3">{t('dashboard.participants')}</h2>
            <div className="bg-white rounded border overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-gray-100">
                        <tr>
                            <th className="px-4 py-2 text-left">{t('user.firstname')} {t('user.lastname')}</th>
                            <th className="px-4 py-2 text-left">{t('participation.project')}</th>
                            <th className="px-4 py-2 text-right">{t('participation.laps')}</th>
                            <th className="px-4 py-2 text-right">{t('sponsor.title')}</th>
                            <th className="px-4 py-2 text-right">{t('participation.donation_sum')}</th>
                            <th className="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {runParticipations.map((rp) => (
                            <tr key={rp.id} className="border-t hover:bg-gray-50">
                                <td className="px-4 py-2">{rp.user?.firstname} {rp.user?.lastname}</td>
                                <td className="px-4 py-2 text-gray-600">{rp.project?.name ?? '—'}</td>
                                <td className="px-4 py-2 text-right">{rp.laps}</td>
                                <td className="px-4 py-2 text-right">{rp.sponsors?.length ?? 0}</td>
                                <td className="px-4 py-2 text-right font-medium text-indigo-700">{fmt(rp.donation_sum)}</td>
                                <td className="px-4 py-2 flex gap-3 justify-end">
                                    <Link
                                        href={route('admin.sponrun.runpart.edit', [sponrun.id, rp.id])}
                                        className="text-indigo-600 hover:underline text-xs"
                                    >
                                        {t('common.edit')}
                                    </Link>
                                    <button
                                        onClick={() => deleteParticipation(rp.id)}
                                        className="text-red-500 hover:underline text-xs"
                                    >
                                        {t('common.delete')}
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
