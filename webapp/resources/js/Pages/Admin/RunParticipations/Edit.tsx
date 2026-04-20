import { Head, Link, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { fmt, totalDonation } from '@/Components/DonationCalculator';
import { PageProps, RunParticipation, Sponsor, SponsoredRun } from '@/types';

interface Props extends PageProps {
    sponrun: SponsoredRun;
    runpart: RunParticipation;
    projects: Record<string, string>;
    donationSum: number;
}

export default function AdminRunParticipationEdit({ sponrun, runpart, projects }: Props) {
    const { t } = useTranslation();
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

    return (
        <AppLayout>
            <Head title={`${runpart.user?.firstname} ${runpart.user?.lastname}`} />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('admin.sponrun.show', sponrun.id)} className="text-sm text-gray-500 hover:underline">
                    ← {sponrun.name}
                </Link>
                <h1 className="text-xl font-bold">
                    {runpart.user?.firstname} {runpart.user?.lastname}
                </h1>
            </div>

            <div className="bg-white rounded border p-5 mb-6 max-w-lg">
                <form
                    onSubmit={(e) => { e.preventDefault(); patch(route('admin.sponrun.runpart.update', [sponrun.id, runpart.id])); }}
                    className="space-y-4"
                >
                    {Object.keys(projects).length > 1 && (
                        <div>
                            <label className="block text-sm font-medium mb-1">{t('participation.project')}</label>
                            <select value={data.project_id} onChange={(e) => setData('project_id', e.target.value)}
                                    className="w-full border rounded px-3 py-2 text-sm">
                                <option value="">{t('project.select')}</option>
                                {Object.entries(projects).map(([id, label]) => (
                                    <option key={id} value={id}>{label}</option>
                                ))}
                            </select>
                        </div>
                    )}

                    {sponrun.with_tshirt && (
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

                    <div>
                        <label className="block text-sm font-medium mb-1">{t('participation.laps')}</label>
                        <input
                            type="number"
                            min={0}
                            value={data.laps}
                            onChange={(e) => setData('laps', parseInt(e.target.value) || 0)}
                            className="w-32 border rounded px-3 py-2 text-sm"
                        />
                        {errors.laps && <p className="text-red-500 text-xs mt-1">{errors.laps}</p>}
                    </div>

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
                    <button onClick={copyLink} className="bg-indigo-600 text-white text-xs px-3 py-2 rounded hover:bg-indigo-700">
                        {copied ? t('participation.copied') : t('participation.copy_link')}
                    </button>
                </div>
            </div>

            {/* Sponsors */}
            <div className="bg-white rounded border p-5 mb-4">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="font-semibold">{t('sponsor.title')}</h2>
                    <Link
                        href={route('admin.sponrun.runpart.sponsor.create', [sponrun.id, runpart.id])}
                        className="bg-green-600 text-white text-xs px-3 py-2 rounded hover:bg-green-700"
                    >
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
                                const total = max > 0 ? Math.min(perLap * runpart.laps, max) : perLap * runpart.laps;
                                return (
                                    <tr key={s.id} className="border-t">
                                        <td className="py-1">{s.firstname} {s.lastname}</td>
                                        <td className="py-1 text-right">{s.donation_per_lap} €</td>
                                        <td className="py-1 text-right">{parseFloat(String(s.donation_static_max)) > 0 ? s.donation_static_max + ' €' : '—'}</td>
                                        <td className="py-1 text-right font-medium">{fmt(total)}</td>
                                        <td className="py-1 pl-2">
                                            <Link
                                                href={route('admin.sponrun.runpart.sponsor.edit', [sponrun.id, runpart.id, s.id])}
                                                className="text-indigo-500 hover:underline text-xs"
                                            >
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
        </AppLayout>
    );
}
