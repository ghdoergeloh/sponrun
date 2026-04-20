import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import GuestLayout from '@/Layouts/GuestLayout';
import SponsorFormFields from '@/Components/SponsorFormFields';
import { PageProps } from '@/types';

interface RunpartInfo {
    hash: string;
    runner: string;
    project: string | null;
    run: string;
}

interface Props extends PageProps {
    runpart: RunpartInfo;
}

export default function PublicCreate({ runpart }: Props) {
    const { t } = useTranslation();
    const { flash, newsletterOptional } = usePage<PageProps>().props;

    const { data, setData, post, errors, processing } = useForm({
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
        <GuestLayout>
            <Head title={runpart.run} />

            <div className="max-w-lg mx-auto py-8 px-4">
                {flash?.success && (
                    <div className="mb-6 bg-green-50 border border-green-300 text-green-800 rounded p-4 text-sm">
                        {flash.success}
                    </div>
                )}

                <div className="mb-6">
                    <h1 className="text-xl font-bold">
                        {t('sponsor.public_intro', { name: runpart.runner })}
                        {runpart.project && (
                            <span className="font-normal text-gray-600">
                                {t('sponsor.public_intro_project', { project: runpart.project })}
                            </span>
                        )}
                    </h1>
                    <p className="text-gray-600 text-sm mt-1">{t('sponsor.public_run', { run: runpart.run })}</p>
                </div>

                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        post(route('run.sponsor.store', runpart.hash));
                    }}
                    className="bg-white rounded border p-6"
                >
                    <SponsorFormFields
                        data={data}
                        setData={(key, value) => setData(key as keyof typeof data, value as never)}
                        errors={errors}
                        newsletterOptional={newsletterOptional}
                    />

                    <div className="mt-6">
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {t('common.save')}
                        </button>
                    </div>
                </form>
            </div>
        </GuestLayout>
    );
}
