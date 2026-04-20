import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Projectlist } from '@/types';

interface Props extends PageProps {
    projectlists: (Projectlist & { projects_count: number })[];
}

export default function AdminProjectlistsIndex({ projectlists }: Props) {
    const { t } = useTranslation();

    const deleteList = (id: number) => {
        if (confirm(t('common.confirm_delete'))) {
            router.delete(route('admin.projectlist.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title={t('nav.projectlists')} />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-xl font-bold">{t('nav.projectlists')}</h1>
                <Link
                    href={route('admin.projectlist.create')}
                    className="bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700"
                >
                    + {t('projectlist.create')}
                </Link>
            </div>

            <div className="bg-white rounded border overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-gray-100">
                        <tr>
                            <th className="px-4 py-2 text-left">{t('projectlist.name')}</th>
                            <th className="px-4 py-2 text-right">{t('nav.projects')}</th>
                            <th className="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {projectlists.map((pl) => (
                            <tr key={pl.id} className="border-t hover:bg-gray-50">
                                <td className="px-4 py-2 font-medium">{pl.name}</td>
                                <td className="px-4 py-2 text-right">{pl.projects_count}</td>
                                <td className="px-4 py-2 flex gap-3 justify-end">
                                    <Link href={route('admin.projectlist.edit', pl.id)} className="text-indigo-600 hover:underline text-xs">
                                        {t('common.edit')}
                                    </Link>
                                    <button onClick={() => deleteList(pl.id)} className="text-red-500 hover:underline text-xs">
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
