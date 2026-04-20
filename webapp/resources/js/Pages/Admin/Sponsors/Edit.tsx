import { Head, Link, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import SponsorFormFields from '@/Components/SponsorFormFields';
import { PageProps, RunParticipation, Sponsor, SponsoredRun } from '@/types';

interface Props extends PageProps {
    sponrun: SponsoredRun;
    runpart: RunParticipation;
    sponsor: Sponsor;
}

export default function AdminSponsorEdit({ sponrun, runpart, sponsor }: Props) {
    const { t } = useTranslation();

    const { data, setData, patch, errors, processing } = useForm({
        ext_personnel_no: String(sponsor.ext_personnel_no ?? ''),
        firstname: sponsor.firstname,
        lastname: sponsor.lastname,
        street: sponsor.street,
        housenumber: sponsor.housenumber,
        postcode: sponsor.postcode,
        city: sponsor.city,
        phone: sponsor.phone ?? '',
        email: sponsor.email ?? '',
        donation_per_lap: String(sponsor.donation_per_lap),
        donation_static_max: String(sponsor.donation_static_max),
        wants_newsletter: sponsor.wants_newsletter ?? false,
    });

    const deleteSponsor = () => {
        if (confirm(t('common.confirm_delete'))) {
            router.delete(route('admin.sponrun.runpart.sponsor.destroy', [sponrun.id, runpart.id, sponsor.id]));
        }
    };

    return (
        <AppLayout>
            <Head title={`${sponsor.firstname} ${sponsor.lastname}`} />

            <div className="flex items-center gap-4 mb-6">
                <Link
                    href={route('admin.sponrun.runpart.edit', [sponrun.id, runpart.id])}
                    className="text-sm text-gray-500 hover:underline"
                >
                    ← {runpart.user?.firstname} {runpart.user?.lastname}
                </Link>
                <h1 className="text-xl font-bold">{sponsor.firstname} {sponsor.lastname}</h1>
            </div>

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    patch(route('admin.sponrun.runpart.sponsor.update', [sponrun.id, runpart.id, sponsor.id]));
                }}
                className="bg-white rounded border p-6 max-w-lg"
            >
                <SponsorFormFields
                    data={data}
                    setData={(key, value) => setData(key as keyof typeof data, value as never)}
                    errors={errors}
                    showPersonnelNo
                />

                <div className="mt-6 flex gap-3 items-center">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {t('common.save')}
                    </button>
                    <Link
                        href={route('admin.sponrun.runpart.edit', [sponrun.id, runpart.id])}
                        className="text-sm text-gray-500 px-4 py-2 rounded border hover:bg-gray-50"
                    >
                        {t('common.cancel')}
                    </Link>
                    <button
                        type="button"
                        onClick={deleteSponsor}
                        className="ml-auto text-red-500 text-sm hover:underline"
                    >
                        {t('common.delete')}
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}
