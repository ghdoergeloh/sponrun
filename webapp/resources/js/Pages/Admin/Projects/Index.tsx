import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Project } from '@/types';

interface Props extends PageProps {
    projects: Project[];
}

export default function AdminProjectsIndex({ projects }: Props) {
    const { t } = useTranslation();

    const deleteProject = (id: number) => {
        if (confirm(t('common.confirm_delete'))) {
            router.delete(route('admin.project.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title={t('nav.projects')} />

            <div className="flex items-center justify-between mb-6">
                <h1 className="text-xl font-bold">{t('nav.projects')}</h1>
                <Link
                    href={route('admin.project.create')}
                    className="bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700"
                >
                    + {t('project.create')}
                </Link>
            </div>

            <div className="bg-white rounded border overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-gray-100">
                        <tr>
                            <th className="px-4 py-2 text-left">{t('project.id')}</th>
                            <th className="px-4 py-2 text-left">{t('project.name')}</th>
                            <th className="px-4 py-2 text-left">{t('project.scope')}</th>
                            <th className="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {projects.map((p) => (
                            <tr key={p.id} className="border-t hover:bg-gray-50">
                                <td className="px-4 py-2 text-gray-500">{p.id}</td>
                                <td className="px-4 py-2 font-medium">{p.name}</td>
                                <td className="px-4 py-2">
                                    {p.scope === 'person' ? t('project.scope_person') : t('project.scope_project')}
                                </td>
                                <td className="px-4 py-2 flex gap-3 justify-end">
                                    <Link href={route('admin.project.edit', p.id)} className="text-indigo-600 hover:underline text-xs">
                                        {t('common.edit')}
                                    </Link>
                                    <button onClick={() => deleteProject(p.id)} className="text-red-500 hover:underline text-xs">
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
