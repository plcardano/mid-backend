<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const errors = ref({
    email: '',
    password: '',
    general: ''
});
const isProcessing = ref(false);

const form = ref({
    email: '',
    password: '',
    remember: false,
});

const submit = async () => {
    isProcessing.value = true;
    errors.value = { email: '', password: '', general: '' };
    
    try {
        const response = await axios.post('/api/v1/auth/login', {
            email: form.value.email,
            password: form.value.password
        });
        
        // Store the JWT token in localStorage or a cookie
        const token = response.data.access_token;
        const user = response.data.user;
        
        // Store in localStorage (you might want a more secure approach in production)
        localStorage.setItem('auth_token', token);
        localStorage.setItem('auth_user', JSON.stringify(user));
        
        // If remember me is checked, store the refresh token or extend token validity
        if (form.value.remember) {
            localStorage.setItem('remember_auth', 'true');
        }
        
        // Redirect to dashboard or home page
        window.location.href = '/dashboard'; // Using window.location for a full page refresh
    } catch (error) {
        if (error.response) {
            if (error.response.status === 401) {
                errors.value.general = 'Invalid email or password';
            } else if (error.response.data.errors) {
                // Handle validation errors
                if (error.response.data.errors.email) {
                    errors.value.email = error.response.data.errors.email[0];
                }
                if (error.response.data.errors.password) {
                    errors.value.password = error.response.data.errors.password[0];
                }
            } else {
                errors.value.general = 'An error occurred. Please try again.';
            }
        } else {
            errors.value.general = 'Network error. Please check your connection.';
        }
    } finally {
        isProcessing.value = false;
        form.value.password = ''; // Reset password field
    }
};
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div v-if="errors.general" class="rounded-md bg-red-50 p-4 text-sm text-red-500">
                {{ errors.general }}
            </div>
            
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                            Forgot password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between" :tabindex="3">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model:checked="form.remember" :tabindex="4" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="isProcessing">
                    <LoaderCircle v-if="isProcessing" class="h-4 w-4 animate-spin" />
                    Log in
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>