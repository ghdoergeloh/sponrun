import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import SponsorFormFields from '@/Components/SponsorFormFields';
import { PageProps, RunParticipation, SponsoredRun } from '@/types';

interface Props extends PageProps {
    sponrun: SponsoredRun;
    runpart: RunParticipation;
}

export default function AdminSponsorCreate({ sponrun, runpart }: Props) {
    const { t } = useTranslation();

    const { data, setData, post, errors, processing } = useForm({
        ext_personnel_no: '',
        firstname: '',
        lastname: '',
        street: '',
        housenumber: '',
        postcode: '',
        city: '',
        phone: '',
        email: '',
        donation_per_lap: '',
        donation_static_max: '',
        wants_newsletter: false,
    });

    return (
        <AppLayout>
            <Head title={t('sponsor.add')} />

            <div className="flex items-center gap-4 mb-6">
                <Link
                    href={route('admin.sponrun.runpart.edit', [sponrun.id, runpart.id])}
                    className="text-sm text-gray-500 hover:underline"
                >
                    ← {runpart.user?.firstname} {runpart.user?.lastname}
                </Link>
                <h1 className="text-xl font-bold">{t('sponsor.add')}</h1>
            </div>

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    post(route('admin.sponrun.runpart.sponsor.store', [sponrun.id, runpart.id]));
                }}
                className="bg-white rounded border p-6 max-w-lg"
            >
                <SponsorFormFields
                    data={data}
                    setData={(key, value) => setData(key as keyof typeof data, value as never)}
                    errors={errors}
                    showPersonnelNo
                />

                <div className="mt-6 flex gap-3">
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
                </div>
            </form>
        </AppLayout>
    );
}
