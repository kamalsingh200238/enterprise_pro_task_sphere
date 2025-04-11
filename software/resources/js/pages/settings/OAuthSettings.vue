<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    oauthEnabled: boolean;
}

const props = defineProps<Props>();
const enabled = ref(props.oauthEnabled);

// Breadcrumb for navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'OAuth Settings',
        href: '/settings/oauth',
    },
];

// Handle form submission
const updateOAuthStatus = () => {
    router.post(
        route('oauth-settings.update'),
        { enabled: enabled.value },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="OAuth Settings" />
        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="OAuth Login Configuration" description="Configure whether users can log in using OAuth providers" />

                <Card>
                    <CardContent class="pt-6">
                        <div class="space-y-6">
                            <div class="flex items-center space-x-4">
                                <Switch id="oauth-enabled" v-model="enabled" />
                                <Label for="oauth-enabled">Enable OAuth Login</Label>
                            </div>

                            <Alert :variant="enabled ? 'default' : 'destructive'" class="mt-4">
                                <AlertDescription>
                                    <p v-if="enabled">When OAuth login is enabled, users can authenticate using OAuth providers.</p>
                                    <p v-else>
                                        <strong>Warning:</strong> When OAuth login is disabled, users who have been logging in via OAuth will no
                                        longer be able to access their accounts this way. Make sure they have alternative login methods configured.
                                    </p>
                                </AlertDescription>
                            </Alert>

                            <div class="flex justify-end">
                                <Button @click="updateOAuthStatus" class="ml-auto"> Save Changes </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
