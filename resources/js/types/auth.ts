import type { App } from '@/wayfinder/types';

export type User = App.Models.User & {
    avatar?: string;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
