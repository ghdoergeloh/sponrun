import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, RunParticipation } from '@/types';
import { fmt } from '@/Components/DonationCalculator';

interface Props extends PageProps { participations: RunParticipation[]; }

export default function RunParticipationsIndex({ participations }: Props) {
    const { t } = useTranslation();

    const formatDate = (d: string) => new Date(d).toLocaleDateString('de-DE');

    return (
        <AppLayout>
            <Head title={t('participation.my_participations')} />
            <h1 className="text-xl font-bold mb-6">{t('participation.my_participations')}</h1>

            {participations.length === 0 ? (
                <p className="text-gray-500">{t('participation.no_participations')}</p>
            ) : (
                <div className="bg-white rounded border overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="px-4 py-2 text-left">{t('run.name')}</th>
                                <th className="px-4 py-2 text-left">{t('run.begin')}</th>
                                <th className="px-4 py-2 text-right">{t('participation.laps')}</th>
                                <th className="px-4 py-2 text-right">{t('participation.sponsor_count')}</th>
                                <th className="px-4 py-2 text-right">{t('participation.donation_sum')}</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {participations.map((rp) => (
                                <tr key={rp.id} className="border-t hover:bg-gray-50">
                                    <td className="px-4 py-2 font-medium">{rp.sponsored_run?.name}</td>
                                    <td className="px-4 py-2 text-gray-600">
                                        {rp.sponsored_run?.begin ? formatDate(rp.sponsored_run.begin) : '—'}
                                    </td>
                                    <td className="px-4 py-2 text-right">{rp.laps}</td>
                                    <td className="px-4 py-2 text-right">{rp.sponsors_count ?? 0}</td>
                                    <td className="px-4 py-2 text-right font-medium text-indigo-700">
                                        {fmt(rp.donation_sum ?? 0)}
                                    </td>
                                    <td className="px-4 py-2">
                                        <Link href={route('runpart.edit', rp.id)}
                                              className="text-indigo-600 hover:underline text-xs">
                                            {t('common.edit')}
                                        </Link>
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
