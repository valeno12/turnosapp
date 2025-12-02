<template>
    <form @submit.prevent="$emit('submit')">
        <Card>
            <CardHeader>
                <CardTitle>{{ cardTitle }}</CardTitle>
                <CardDescription v-if="cardDescription">
                    {{ cardDescription }}
                </CardDescription>
            </CardHeader>

            <CardContent class="space-y-4">
                <slot />
            </CardContent>

            <CardFooter class="flex justify-between">
                <Button
                    type="button"
                    variant="outline"
                    @click="$emit('cancel')"
                    :disabled="isSubmitting"
                >
                    Cancelar
                </Button>

                <Button type="submit" :disabled="isSubmitting || !isDirty">
                    <Loader2
                        v-if="isSubmitting"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ isSubmitting ? 'Guardando...' : submitText }}
                </Button>
            </CardFooter>
        </Card>
    </form>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Loader2 } from 'lucide-vue-next';

interface Props {
    cardTitle: string;
    cardDescription?: string;
    entityName: string;
    isSubmitting?: boolean;
    isDirty?: boolean;
    submitText?: string;
}

withDefaults(defineProps<Props>(), {
    isSubmitting: false,
    isDirty: true,
    submitText: 'Guardar',
});

defineEmits<{
    submit: [];
    cancel: [];
}>();
</script>
