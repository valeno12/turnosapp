<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-h-[80vh] max-w-md">
            <DialogHeader>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="currentView === 'details'"
                        variant="ghost"
                        size="icon"
                        @click="backToList"
                        class="h-8 w-8"
                    >
                        <ArrowLeft :size="18" />
                    </Button>
                    <DialogTitle>{{ dialogTitle }}</DialogTitle>
                </div>
            </DialogHeader>

            <!-- Vista: Lista de clases -->
            <DayClassesList
                v-if="currentView === 'list'"
                :classes="classes"
                :initial-open-service="initialOpenService"
                @select-class="handleSelectClass"
            />

            <!-- Vista: Detalles de clase -->
            <ClassDetailsView
                v-else-if="currentView === 'details' && selectedClass"
                :class-data="selectedClass"
                @view-students="handleViewStudents"
                @manage="handleManage"
            />
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import ClassDetailsView from '@/components/admin/schedules/ClassDetailsView.vue';
import DayClassesList from '@/components/admin/schedules/DayClassesList.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Schedule } from '@/types/model';
import { ArrowLeft } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface ClassData {
    schedule: Schedule;
    bookingsCount: number;
    isCancelled: boolean;
    date: string;
}

interface Props {
    open: boolean;
    date: string | null;
    classes: ClassData[];
    initialOpenService?: string | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'viewStudents', classData: ClassData): void;
    (e: 'manage', classData: ClassData): void;
}>();

// Estado de navegación
type ViewType = 'list' | 'details';
const currentView = ref<ViewType>('list');
const selectedClass = ref<ClassData | null>(null);

// Título dinámico del modal
const dialogTitle = computed(() => {
    if (currentView.value === 'details' && selectedClass.value) {
        return selectedClass.value.schedule.service?.name || 'Clase';
    }
    return `Clases del ${formatDateLong(props.date)}`;
});

// Navegación entre vistas
const handleSelectClass = (classData: ClassData) => {
    selectedClass.value = classData;
    currentView.value = 'details';
};

const backToList = () => {
    currentView.value = 'list';
    selectedClass.value = null;
};

const handleClose = () => {
    // Resetear estado antes de cerrar
    currentView.value = 'list';
    selectedClass.value = null;
    emit('close');
};

// Acciones de la vista de detalles
const handleViewStudents = () => {
    if (selectedClass.value) {
        emit('viewStudents', selectedClass.value);
    }
};

const handleManage = () => {
    if (selectedClass.value) {
        emit('manage', selectedClass.value);
    }
};

// Resetear al abrir/cerrar el modal
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            currentView.value = 'list';
            selectedClass.value = null;
        }
    },
);

// Helper
const formatDateLong = (dateStr: string | null): string => {
    if (!dateStr) return '';
    const [year, month, day] = dateStr.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('es-AR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });
};
</script>
