<template>
    <div class="flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
            Mostrando {{ from || 0 }} a {{ to || 0 }} de {{ total }} resultados
        </div>

        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="currentPage === 1"
                @click="$emit('change-page', currentPage - 1)"
            >
                <ChevronLeft class="h-4 w-4" />
                Anterior
            </Button>

            <div class="flex gap-1">
                <Button
                    v-for="page in visiblePages"
                    :key="page"
                    variant="outline"
                    size="sm"
                    :class="{
                        'bg-primary text-primary-foreground':
                            page === currentPage,
                    }"
                    @click="$emit('change-page', page)"
                >
                    {{ page }}
                </Button>
            </div>

            <Button
                variant="outline"
                size="sm"
                :disabled="currentPage === lastPage"
                @click="$emit('change-page', currentPage + 1)"
            >
                Siguiente
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    currentPage: number;
    lastPage: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<Props>();

defineEmits<{
    'change-page': [page: number];
}>();

const visiblePages = computed((): number[] => {
    const current = props.currentPage;
    const last = props.lastPage;
    const delta = 2;
    const range: number[] = [];
    const rangeWithDots: (number | string)[] = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, '...');
    } else {
        rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (current + delta < last - 1) {
        rangeWithDots.push('...', last);
    } else if (last > 1) {
        rangeWithDots.push(last);
    }

    return rangeWithDots.filter((p): p is number => typeof p === 'number');
});
</script>
