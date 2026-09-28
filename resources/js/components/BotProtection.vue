<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';

const page = usePage();

const protection = computed(() => page.props.botProtection);

const error = computed(
    () =>
        page.props.errors[protection.value.validFromField] ??
        page.props.errors[protection.value.field],
);
</script>

<template>
    <div class="hidden" aria-hidden="true">
        <input
            type="text"
            :name="protection.field"
            tabindex="-1"
            autocomplete="off"
            value=""
        />
    </div>
    <input
        type="hidden"
        :name="protection.validFromField"
        :value="protection.validFrom"
    />
    <InputError :message="error" />
</template>
