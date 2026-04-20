import { Link, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { PageProps } from '@/types';
import { PropsWithChildren } from 'react';

export default function AppLayout({ children }: PropsWithChildren) {
    const { auth, flash, appName, urlImpressum, urlPrivacy } = usePage<PageProps>().props;
    const { t } = useTranslation();
    const user = auth.user;

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">
            {/* Top nav */}
            <nav className="bg-indigo-700 text-white shadow">
                <div className="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                    <div className="flex items-center gap-6">
                        <Link href={route('dashboard')} className="font-bold text-lg tracking-tight">
                            {appName}
                        </Link>
                        {user && (
                            <>
                                <Link href={route('dashboard')} className="text-sm hover:text-indigo-200">
                                    {t('nav.home')}
                                </Link>
                                <Link href={route('runpart.index')} className="text-sm hover:text-indigo-200">
                                    {t('nav.my_runs')}
                                </Link>
                                {user.isAdmin && (
                                    <div className="relative group">
                                        <button className="text-sm hover:text-indigo-200 flex items-center gap-1">
                                            {t('nav.admin')} <span>▾</span>
                                        </button>
                                        <div className="absolute hidden group-hover:block bg-white text-gray-800 shadow-lg rounded mt-1 z-10 min-w-[160px]">
                                            <Link href={route('admin.sponrun.index')} className="block px-4 py-2 text-sm hover:bg-gray-100">
                                                {t('nav.runs')}
                                            </Link>
                                            <Link href={route('admin.project.index')} className="block px-4 py-2 text-sm hover:bg-gray-100">
                                                {t('nav.projects')}
                                            </Link>
                                            <Link href={route('admin.projectlist.index')} className="block px-4 py-2 text-sm hover:bg-gray-100">
                                                {t('nav.projectlists')}
                                            </Link>
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                    {user && (
                        <div className="flex items-center gap-4 text-sm">
                            <Link href={route('account.edit')} className="hover:text-indigo-200">
                                {user.firstname} {user.lastname}
                            </Link>
                            <Link href={route('logout')} method="post" as="button" className="hover:text-indigo-200">
                                {t('nav.logout')}
                            </Link>
                        </div>
                    )}
                </div>
            </nav>

            {/* Flash messages */}
            {(flash.success || flash.error) && (
                <div className="max-w-7xl mx-auto px-4 pt-4 w-full">
                    {flash.success && (
                        <div className="bg-green-100 border border-green-300 text-green-800 rounded px-4 py-2 text-sm">
                            {flash.success}
                        </div>
                    )}
                    {flash.error && (
                        <div className="bg-red-100 border border-red-300 text-red-800 rounded px-4 py-2 text-sm">
                            {flash.error}
                        </div>
                    )}
                </div>
            )}

            {/* Main content */}
            <main className="flex-1 max-w-7xl mx-auto px-4 py-6 w-full">
                {children}
            </main>

            {/* Footer */}
            <footer className="bg-white border-t text-xs text-gray-500 py-3">
                <div className="max-w-7xl mx-auto px-4 flex gap-4">
                    {urlImpressum && (
                        <a href={urlImpressum} target="_blank" rel="noopener noreferrer" className="hover:underline">
                            {t('nav.impressum')}
                        </a>
                    )}
                    {urlPrivacy && (
                        <a href={urlPrivacy} target="_blank" rel="noopener noreferrer" className="hover:underline">
                            {t('nav.privacy')}
                        </a>
                    )}
                </div>
            </footer>
        </div>
    );
}
