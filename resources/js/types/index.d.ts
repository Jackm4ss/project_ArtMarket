export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    avatar_url?: string | null;
    roles?: string[];
    can_manage_store?: boolean;
    seller?: {
        id: number;
        store_name: string;
        slug: string;
    } | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    cart?: {
        total_items?: number;
    };
    notifications?: {
        unread_count?: number;
    };
    messages?: {
        unread_count?: number;
    };
};
