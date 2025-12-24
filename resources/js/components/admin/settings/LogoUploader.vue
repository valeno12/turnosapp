<template>
    <div class="space-y-4">
        <!-- Vista previa del logo actual o nuevo -->
        <div
            v-if="currentLogo || previewUrl"
            class="flex flex-col items-center gap-4"
        >
            <img
                :src="previewUrl || `/storage/${currentLogo}`"
                alt="Logo"
                class="h-64 w-64 rounded-xl border-2 object-contain p-4 shadow-sm"
            />
            <div class="text-center">
                <span class="text-sm font-medium text-muted-foreground">
                    {{
                        previewUrl ? 'Nuevo logo (sin guardar)' : 'Logo actual'
                    }}
                </span>
                <Button
                    v-if="previewUrl"
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="ml-2"
                    @click="clearPreview"
                >
                    Cancelar
                </Button>
            </div>
        </div>

        <!-- Botón para subir -->
        <div>
            <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleFileSelect"
            />
            <Button
                type="button"
                variant="outline"
                size="lg"
                @click="triggerFileInput"
            >
                {{ currentLogo || previewUrl ? 'Cambiar Logo' : 'Subir Logo' }}
            </Button>
        </div>

        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>

        <!-- Diálogo del cropper -->
        <Dialog v-model:open="showCropper">
            <DialogContent
                class="flex max-h-[90vh] max-w-3xl flex-col overflow-hidden"
            >
                <DialogHeader>
                    <DialogTitle>Recortar Logo</DialogTitle>
                </DialogHeader>

                <!-- Cropper con altura responsiva -->
                <div class="my-4 min-h-0 flex-1">
                    <Cropper
                        ref="cropperRef"
                        :src="imageSource"
                        :stencil-props="{
                            aspectRatio: 1,
                        }"
                        class="h-full w-full"
                        :style="{ maxHeight: '400px' }"
                    />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="handleCancel"
                    >
                        Cancelar
                    </Button>
                    <Button type="button" @click="handleCrop"> Aplicar </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ref, watch } from 'vue';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

interface Props {
    currentLogo?: string | null;
    error?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update', file: File | null): void;
    (e: 'clear-preview'): void;
}>();

const showCropper = ref(false);
const imageSource = ref('');
const cropperRef = ref();
const fileInputRef = ref<HTMLInputElement>();
const previewUrl = ref<string>('');

// Limpiar preview cuando el logo externo cambia (después de guardar)
watch(
    () => props.currentLogo,
    (newLogo, oldLogo) => {
        if (newLogo !== oldLogo && previewUrl.value) {
            // Se guardó exitosamente, limpiar preview
            if (previewUrl.value) {
                URL.revokeObjectURL(previewUrl.value);
            }
            previewUrl.value = '';
        }
    },
);

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        // Validar tamaño (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('El archivo no puede superar 2MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            imageSource.value = e.target?.result as string;
            showCropper.value = true;
        };
        reader.readAsDataURL(file);
    }

    // Limpiar input para permitir subir el mismo archivo
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const handleCrop = async () => {
    if (!cropperRef.value) return;

    const result = cropperRef.value.getResult();
    if (!result?.canvas) return;

    result.canvas.toBlob((blob: Blob | null) => {
        if (blob) {
            const file = new File([blob], 'logo.png', { type: 'image/png' });

            // Crear preview URL
            if (previewUrl.value) {
                URL.revokeObjectURL(previewUrl.value);
            }
            previewUrl.value = URL.createObjectURL(blob);

            emit('update', file);
            showCropper.value = false;
        }
    }, 'image/png');
};

const handleCancel = () => {
    showCropper.value = false;
    imageSource.value = '';
};

const clearPreview = () => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
    previewUrl.value = '';
    emit('update', null);
};
</script>
