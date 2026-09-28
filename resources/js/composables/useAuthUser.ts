import { usePage } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed } from 'vue';
import type { User } from '@/types';

/**
 * The authenticated user, for components that only render behind the auth middleware.
 */
export function useAuthUser(): ComputedRef<User> {
    const page = usePage();

    return computed(() => {
        const user = page.props.auth.user;

        if (user === null) {
            throw new Error('useAuthUser() requires an authenticated user.');
        }

        return user;
    });
}
