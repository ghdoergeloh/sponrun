import { FormEventHandler, useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import LanguageSwitcher from '@/Components/LanguageSwitcher';
import { PageProps } from '@/types';

type Tab = 'sign-in' | 'sign-up';

export default function Welcome() {
    const { t } = useTranslation();
    const { appName } = usePage<PageProps>().props;
    const [tab, setTab] = useState<Tab>('sign-in');

    return (
        <>
            <Head title={t('welcome.title')} />

            <div className="min-h-screen flex flex-col bg-white">
                <HeroSection appName={appName} tab={tab} setTab={setTab} />
                <Footer appName={appName} />
            </div>
        </>
    );
}

function HeroSection({ appName, tab, setTab }: { appName: string; tab: Tab; setTab: (t: Tab) => void }) {
    const { t } = useTranslation();

    return (
        <section
            className="relative overflow-hidden flex-1"
            style={{
                backgroundImage: "url('/images/hero-track.jpg')",
                backgroundSize: 'cover',
                backgroundPosition: 'center',
            }}
        >
            {/* Gradient overlay (red -> blue) — also serves as fallback if image is missing */}
            <div
                aria-hidden
                className="absolute inset-0 bg-gradient-to-r from-red-500/80 via-red-400/50 to-blue-500/80 mix-blend-multiply"
            />
            <div aria-hidden className="absolute inset-0 bg-gradient-to-b from-transparent to-black/20" />

            <div className="relative max-w-7xl mx-auto px-6 lg:px-8 pt-6 pb-24">
                {/* Top bar */}
                <div className="flex items-center justify-between">
                    <Link href={route('welcome')} className="text-2xl font-bold text-red-500 bg-white/95 px-3 py-1 rounded shadow-sm">
                        {appName}
                    </Link>
                    <LanguageSwitcher variant="light" />
                </div>

                {/* Two-column content */}
                <div className="mt-16 grid lg:grid-cols-2 gap-12 items-start">
                    <div className="text-white">
                        <h1 className="text-4xl lg:text-5xl font-bold leading-tight drop-shadow">
                            {t('welcome.headline')}
                        </h1>
                        <p className="mt-4 text-lg lg:text-xl text-white/90 max-w-xl">
                            {t('welcome.subheadline')}
                        </p>
                        <p className="mt-6 text-sm text-white/80 max-w-xl">
                            {t('welcome.description')}
                        </p>

                        <div className="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-2xl">
                            <FeatureCard icon="📊" title={t('welcome.feature_track_title')} body={t('welcome.feature_track_body')} />
                            <FeatureCard icon="🤝" title={t('welcome.feature_sponsors_title')} body={t('welcome.feature_sponsors_body')} />
                            <FeatureCard icon="👥" title={t('welcome.feature_community_title')} body={t('welcome.feature_community_body')} />
                        </div>
                    </div>

                    <AuthCard tab={tab} setTab={setTab} />
                </div>
            </div>
        </section>
    );
}

function FeatureCard({ icon, title, body }: { icon: string; title: string; body: string }) {
    return (
        <div className="text-center">
            <div className="w-12 h-12 mx-auto rounded-full bg-white/30 backdrop-blur flex items-center justify-center text-xl">
                {icon}
            </div>
            <h3 className="mt-3 font-semibold text-white">{title}</h3>
            <p className="mt-1 text-xs text-white/80 leading-snug">{body}</p>
        </div>
    );
}

function AuthCard({ tab, setTab }: { tab: Tab; setTab: (t: Tab) => void }) {
    const { t } = useTranslation();

    return (
        <div className="bg-white rounded-xl shadow-2xl p-6 sm:p-8 lg:ml-auto lg:max-w-md w-full">
            <h2 className="text-2xl font-bold text-gray-900">{t('welcome.card_title')}</h2>
            <p className="mt-1 text-sm text-gray-500">{t('welcome.card_subtitle')}</p>

            <div className="mt-6 bg-gray-100 rounded-lg p-1 flex text-sm font-medium">
                <button
                    type="button"
                    onClick={() => setTab('sign-in')}
                    className={`flex-1 py-2 rounded-md transition ${tab === 'sign-in' ? 'bg-white shadow text-red-600' : 'text-gray-500 hover:text-gray-700'}`}
                >
                    {t('welcome.sign_in')}
                </button>
                <button
                    type="button"
                    onClick={() => setTab('sign-up')}
                    className={`flex-1 py-2 rounded-md transition ${tab === 'sign-up' ? 'bg-white shadow text-red-600' : 'text-gray-500 hover:text-gray-700'}`}
                >
                    {t('welcome.sign_up')}
                </button>
            </div>

            <div className="mt-6">
                {tab === 'sign-in' ? <SignInForm /> : <SignUpCta />}
            </div>
        </div>
    );
}

function SignInForm() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('user.email')}</label>
                <input
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-400"
                    required
                    autoFocus
                />
                {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">{t('auth.password')}</label>
                <input
                    type="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-400"
                    required
                />
                {errors.password && <p className="text-red-500 text-xs mt-1">{errors.password}</p>}
            </div>

            <div className="flex items-center justify-between text-sm">
                <label className="flex items-center gap-2 text-gray-600">
                    <input
                        type="checkbox"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                        className="rounded border-gray-300 text-red-500 focus:ring-red-400"
                    />
                    {t('auth.remember_me')}
                </label>
                <Link href={route('password.request')} className="text-red-600 hover:underline">
                    {t('auth.forgot_password')}
                </Link>
            </div>

            <button
                type="submit"
                disabled={processing}
                className="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-md transition disabled:opacity-60"
            >
                {t('welcome.sign_in')}
            </button>

            <p className="text-center text-xs text-gray-500">
                {t('welcome.no_account')}{' '}
                <Link href={route('register')} className="text-red-600 font-medium hover:underline">
                    {t('welcome.sign_up_here')}
                </Link>
            </p>
        </form>
    );
}

function SignUpCta() {
    const { t } = useTranslation();
    return (
        <div className="text-center py-4">
            <p className="text-sm text-gray-600 mb-4">{t('welcome.sign_up_cta')}</p>
            <Link
                href={route('register')}
                className="inline-block bg-red-500 hover:bg-red-600 text-white font-semibold px-6 py-2.5 rounded-md transition"
            >
                {t('welcome.go_to_register')}
            </Link>
        </div>
    );
}

function Footer({ appName }: { appName: string }) {
    const { t } = useTranslation();
    const { urlImpressum, urlPrivacy } = usePage<PageProps>().props;

    return (
        <footer className="bg-white border-t">
            <div className="max-w-7xl mx-auto px-6 lg:px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
                <div>
                    <p className="text-red-500 font-bold text-lg">{appName}</p>
                    <p className="mt-3 text-gray-600 max-w-xs">{t('welcome.footer_about')}</p>
                </div>
                <div>
                    <h4 className="font-semibold text-gray-800">{t('welcome.footer_track_heading')}</h4>
                    <ul className="mt-3 space-y-1.5 text-gray-600">
                        <li>{t('welcome.footer_track_1')}</li>
                        <li>{t('welcome.footer_track_2')}</li>
                        <li>{t('welcome.footer_track_3')}</li>
                        <li>{t('welcome.footer_track_4')}</li>
                    </ul>
                </div>
                <div>
                    <h4 className="font-semibold text-gray-800">{t('welcome.footer_community_heading')}</h4>
                    <ul className="mt-3 space-y-1.5 text-gray-600">
                        <li>{t('welcome.footer_community_1')}</li>
                        <li>{t('welcome.footer_community_2')}</li>
                        <li>{t('welcome.footer_community_3')}</li>
                        <li>{t('welcome.footer_community_4')}</li>
                    </ul>
                </div>
            </div>
            <div className="border-t">
                <div className="max-w-7xl mx-auto px-6 lg:px-8 py-4 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 gap-2">
                    <p>© {new Date().getFullYear()} {appName}. {t('welcome.footer_rights')}</p>
                    <div className="flex gap-4">
                        {urlPrivacy && <a href={urlPrivacy} className="hover:underline">{t('nav.privacy')}</a>}
                        {urlImpressum && <a href={urlImpressum} className="hover:underline">{t('nav.impressum')}</a>}
                    </div>
                </div>
            </div>
        </footer>
    );
}
