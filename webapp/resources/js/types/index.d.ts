export interface User {
    id: number;
    ext_personnel_no: number | null;
    firstname: string;
    lastname: string;
    email: string;
    phone: string | null;
    birthday: string;
    street: string;
    housenumber: string;
    postcode: string;
    city: string;
    gender: 'm' | 'f';
    wants_newsletter: boolean | null;
    isAdmin?: boolean;
}

export interface SponsoredRun {
    id: number;
    name: string;
    begin: string;
    end: string;
    closed: boolean;
    with_tshirt: boolean;
    street: string | null;
    housenumber: string | null;
    postcode: string | null;
    city: string | null;
    description: string | null;
    participants_count?: number;
}

export interface RunParticipation {
    id: number;
    user_id: number;
    sponsored_run_id: number;
    project_id: number | null;
    laps: number;
    hash: string;
    tshirt_size: 'XS' | 'S' | 'M' | 'L' | 'XL' | 'XXL' | null;
    share_link?: string;
    donation_sum?: number;
    sponsors_count?: number;
    sponsors?: Sponsor[];
    user?: User;
    sponsored_run?: SponsoredRun;
    project?: Project | null;
}

export interface Sponsor {
    id: number;
    run_participation_id: number;
    user_id: number;
    ext_personnel_no: number | null;
    firstname: string;
    lastname: string;
    email: string | null;
    phone: string | null;
    street: string;
    housenumber: string;
    postcode: string;
    city: string;
    donation_per_lap: string;
    donation_static_max: string;
    wants_newsletter: boolean | null;
}

export interface Project {
    id: number;
    name: string;
    scope: 'person' | 'project';
}

export interface Projectlist {
    id: number;
    name: string;
    projects?: Project[];
    projects_count?: number;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: { user: User | null };
    flash: { success?: string; error?: string };
    appName: string;
    newsletterOptional: boolean;
    urlImpressum?: string;
    urlPrivacy?: string;
};
