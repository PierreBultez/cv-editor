/**
 * Props partagees par `HandleInertiaRequests::share()`, declarees ici pour que
 * `usePage().props` soit typé dans les composants.
 */
declare module '@inertiajs/core' {
    interface PageProps {
        flash: {
            status: string | null;
        };
    }
}

export {};
