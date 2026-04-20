import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, RunParticipation, Sponsor } from '@/types';
import { fmt, totalDonation } from '@/Components/DonationCalculator';

interface Props extends PageProps {
    runpart: RunParticipation;
    projects: Record<string, string>;
    donationSum: number;
}

export default function RunParticipationEdit({ runpart, projects, donationSum }: Props) {
    const { t } = useTranslation();
    const { auth } = usePage<PageProps>().props;
    const [copied, setCopied] = useState(false);

    const { data, setData, patch, errors, processing } = useForm({
        project_id: String(runpart.project_id ?? ''),
        tshirt_size: runpart.tshirt_size ?? '',
        laps: runpart.laps,
    });

    const liveTotal = totalDonation(runpart.sponsors ?? [], data.laps);

    const copyLink = () => {
        navigator.clipboard.writeText(runpart.share_link ?? '');
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const deleteParticipation = () => {
        if (confirm(t('participation.withdraw_confirm'))) {
            router.delete(route('runpart.destroy', runpart.id));
        }
    };

    return (
        <AppLayout>
            <Head title={runpart.sponsored_run?.name ?? t('participation.my_participations')} />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('runpart.index')} className="text-sm text-gray-500 hover:underline">
                    ← {t('participation.my_participations')}
                </Link>
                <h1 className="text-xl font-bold">{runpart.sponsored_run?.name}</h1>
            </div>

            {/* Participation form */}
            <div className="bg-white rounded border p-5 mb-6 max-w-lg">
                <form onSubmit={(e) => { e.preventDefault(); patch(route('runpart.update', runpart.id)); }}
                      className="space-y-4">
                    {Object.keys(projects).length > 1 && (
                        <div>
                            <label className="block text-sm font-medium mb-1">{t('participation.project')}</label>
                            <select value={data.project_id} onChange={(e) => setData('project_id', e.target.value)}
                                    className="w-full border rounded px-3 py-2 text-sm">
                                {Object.entries(projects).map(([id, label]) => (
                                    <option key={id} value={id}>{label}</option>
                                ))}
                            </select>
                        </div>
                    )}

                    {runpart.sponsored_run?.with_tshirt && (
                        <div>
                            <label className="block text-sm font-medium mb-1">{t('participation.tshirt_size')}</label>
                            <select value={data.tshirt_size} onChange={(e) => setData('tshirt_size', e.target.value)}
                                    className="w-full border rounded px-3 py-2 text-sm">
                                <option value="">{t('project.select')}</option>
                                {['XS', 'S', 'M', 'L', 'XL', 'XXL'].map(s => (
                                    <option key={s} value={s}>{s}</option>
                                ))}
                            </select>
                        </div>
                    )}

                    {auth.user?.isAdmin && (
                        <div>
                            <label className="block text-sm font-medium mb-1">{t('participation.laps')}</label>
                            <input type="number" min={0} value={data.laps}
                                   onChange={(e) => setData('laps', parseInt(e.target.value) || 0)}
                                   className="w-32 border rounded px-3 py-2 text-sm" />
                        </div>
                    )}

                    <button type="submit" disabled={processing}
                            className="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50">
                        {t('common.save')}
                    </button>
                </form>
            </div>

            {/* Share link */}
            <div className="bg-indigo-50 border border-indigo-200 rounded p-4 mb-6 max-w-lg">
                <p className="text-sm font-medium text-indigo-800 mb-2">{t('participation.share_link')}</p>
                <div className="flex items-center gap-2">
                    <input readOnly value={runpart.share_link ?? ''} className="flex-1 border rounded px-3 py-1 text-sm bg-white" />
                    <button onClick={copyLink}
                            className="bg-indigo-600 text-white text-xs px-3 py-2 rounded hover:bg-indigo-700">
                        {copied ? t('participation.copied') : t('participation.copy_link')}
                    </button>
                </div>
            </div>

            {/* Sponsors list */}
            <div className="bg-white rounded border p-5 mb-4">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="font-semibold">{t('sponsor.title')}</h2>
                    <Link href={route('runpart.sponsor.create', runpart.id)}
                          className="bg-green-600 text-white text-xs px-3 py-2 rounded hover:bg-green-700">
                        + {t('sponsor.add')}
                    </Link>
                </div>

                <div className="mb-3 text-sm font-medium text-indigo-700">
                    {t('participation.laps')}: {runpart.laps} | {t('participation.donation_sum')}: {fmt(liveTotal)}
                </div>

                {(runpart.sponsors?.length ?? 0) === 0 ? (
                    <p className="text-gray-400 text-sm">{t('sponsor.no_sponsors')}</p>
                ) : (
                    <table className="min-w-full text-sm">
                        <thead className="text-left text-xs text-gray-500">
                            <tr>
                                <th className="py-1">{t('user.firstname')} {t('user.lastname')}</th>
                                <th className="py-1 text-right">{t('sponsor.donation_per_lap')}</th>
                                <th className="py-1 text-right">{t('sponsor.donation_static_max')}</th>
                                <th className="py-1 text-right">{t('sponsor.total')}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {runpart.sponsors!.map((s: Sponsor) => {
                                const perLap = parseFloat(String(s.donation_per_lap).replace(',', '.'));
                                const max = parseFloat(String(s.donation_static_max).replace(',', '.'));
                                const total = Math.min(
                                    perLap === 0 ? max : perLap === 0 ? 0 : perLap * runpart.laps,
                                    max > 0 ? max : Infinity
                                );
                                return (
                                    <tr key={s.id} className="border-t">
                                        <td className="py-1">{s.firstname} {s.lastname}</td>
                                        <td className="py-1 text-right">{s.donation_per_lap} €</td>
                                        <td className="py-1 text-right">{parseFloat(String(s.donation_static_max)) > 0 ? s.donation_static_max + ' €' : '—'}</td>
                                        <td className="py-1 text-right font-medium">{fmt(total)}</td>
                                        <td className="py-1 pl-2">
                                            <Link href={route('runpart.sponsor.edit', [runpart.id, s.id])}
                                                  className="text-indigo-500 hover:underline text-xs mr-2">
                                                {t('common.edit')}
                                            </Link>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                )}
            </div>

            <button onClick={deleteParticipation}
                    className="text-red-500 text-sm hover:underline">
                {t('participation.withdraw')}
            </button>
        </AppLayout>
    );
}
