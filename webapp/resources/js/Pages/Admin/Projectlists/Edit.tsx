import { Head, Link, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Project, Projectlist } from '@/types';

interface Props extends PageProps {
    projectlist: Projectlist;
    assignedProjects: Project[];
    availableProjects: Project[];
}

export default function AdminProjectlistEdit({ projectlist, assignedProjects, availableProjects }: Props) {
    const { t } = useTranslation();
    const [selectedAdd, setSelectedAdd] = useState<number[]>([]);
    const [selectedRemove, setSelectedRemove] = useState<number[]>([]);

    const { data, setData, patch, errors, processing } = useForm({ name: projectlist.name });

    const toggleAdd = (id: number, checked: boolean) =>
        setSelectedAdd(checked ? [...selectedAdd, id] : selectedAdd.filter(i => i !== id));

    const toggleRemove = (id: number, checked: boolean) =>
        setSelectedRemove(checked ? [...selectedRemove, id] : selectedRemove.filter(i => i !== id));

    return (
        <AppLayout>
            <Head title={projectlist.name} />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('admin.projectlist.index')} className="text-sm text-gray-500 hover:underline">
                    ← {t('common.back')}
                </Link>
                <h1 className="text-xl font-bold">{projectlist.name}</h1>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Name form */}
                <form
                    onSubmit={(e) => { e.preventDefault(); patch(route('admin.projectlist.update', projectlist.id)); }}
                    className="bg-white rounded border p-6 space-y-4"
                >
                    <div>
                        <label className="block text-sm font-medium mb-1">{t('projectlist.name')}</label>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="w-full border rounded px-3 py-2 text-sm"
                            required
                        />
                        {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {t('common.save')}
                    </button>
                </form>

                {/* Projects assignment */}
                <div className="bg-white rounded border p-6 space-y-6">
                    <div>
                        <p className="text-sm font-semibold mb-2">{t('projectlist.assigned_projects')}</p>
                        {assignedProjects.length === 0 ? (
                            <p className="text-gray-400 text-sm">{t('projectlist.no_projects')}</p>
                        ) : (
                            <>
                                <div className="space-y-1 max-h-48 overflow-y-auto">
                                    {assignedProjects.map((p) => (
                                        <label key={p.id} className="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={selectedRemove.includes(p.id)}
                                                onChange={(e) => toggleRemove(p.id, e.target.checked)}
                                            />
                                            <span className="text-gray-400 text-xs w-10">{p.id}</span>
                                            {p.name}
                                        </label>
                                    ))}
                                </div>
                                <button
                                    type="button"
                                    onClick={() => {
                                        if (selectedRemove.length === 0) return;
                                        router.patch(route('admin.projectlist.removeProjects', projectlist.id), { project_ids: selectedRemove });
                                        setSelectedRemove([]);
                                    }}
                                    className="mt-2 text-xs text-red-500 hover:underline"
                                >
                                    {t('projectlist.remove_projects')}
                                </button>
                            </>
                        )}
                    </div>

                    <div>
                        <p className="text-sm font-semibold mb-2">{t('projectlist.other_projects')}</p>
                        {availableProjects.length === 0 ? (
                            <p className="text-gray-400 text-sm">–</p>
                        ) : (
                            <>
                                <div className="space-y-1 max-h-48 overflow-y-auto">
                                    {availableProjects.map((p) => (
                                        <label key={p.id} className="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={selectedAdd.includes(p.id)}
                                                onChange={(e) => toggleAdd(p.id, e.target.checked)}
                                            />
                                            <span className="text-gray-400 text-xs w-10">{p.id}</span>
                                            {p.name}
                                        </label>
                                    ))}
                                </div>
                                <button
                                    type="button"
                                    onClick={() => {
                                        if (selectedAdd.length === 0) return;
                                        router.patch(route('admin.projectlist.addProjects', projectlist.id), { project_ids: selectedAdd });
                                        setSelectedAdd([]);
                                    }}
                                    className="mt-2 text-xs text-indigo-600 hover:underline"
                                >
                                    {t('projectlist.add_projects')}
                                </button>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
