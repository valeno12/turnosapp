<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertCircle, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface PricingRule {
    day_start: number;
    day_end: number;
    multiplier: number;
}

interface Props {
    modelValue: PricingRule[];
    disabled?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: PricingRule[]): void;
}>();

const rules = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

// Validaciones
const hasOverlap = (index: number) => {
    const current = rules.value[index];
    return rules.value.some((rule, i) => {
        if (i === index) return false;
        return (
            (current.day_start <= rule.day_end &&
                current.day_end >= rule.day_start) ||
            (rule.day_start <= current.day_end &&
                rule.day_end >= current.day_start)
        );
    });
};

const hasError = (index: number) => {
    const rule = rules.value[index];
    return (
        rule.day_start > rule.day_end ||
        rule.day_start < 1 ||
        rule.day_start > 31 ||
        rule.day_end < 1 ||
        rule.day_end > 31 ||
        hasOverlap(index)
    );
};

const addRule = () => {
    const lastRule = rules.value[rules.value.length - 1];
    const newStart = lastRule ? Math.min(lastRule.day_end + 1, 31) : 1;

    rules.value = [
        ...rules.value,
        {
            day_start: newStart,
            day_end: Math.min(newStart + 9, 31),
            multiplier: 1.0,
        },
    ];
};

const removeRule = (index: number) => {
    if (rules.value.length > 1) {
        rules.value = rules.value.filter((_, i) => i !== index);
    }
};

const updateRule = (index: number, field: keyof PricingRule, value: number) => {
    const updated = [...rules.value];
    const clampedValue =
        field === 'multiplier'
            ? Math.max(1, Math.min(2, value))
            : Math.max(1, Math.min(31, value));
    updated[index] = { ...updated[index], [field]: clampedValue };
    rules.value = updated;
};

const getPercentage = (multiplier: number) => {
    return ((multiplier - 1) * 100).toFixed(0);
};

const setPercentage = (index: number, percentage: number) => {
    const clampedPercentage = Math.max(0, Math.min(100, percentage));
    const multiplier = 1 + clampedPercentage / 100;
    updateRule(index, 'multiplier', multiplier);
};

const getBadgeVariant = (percentage: string) => {
    const num = Number(percentage);
    if (num === 0) return 'outline';
    if (num <= 5) return 'secondary';
    return 'destructive';
};
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="(rule, index) in rules"
            :key="index"
            class="relative rounded-lg border-2 p-4 transition-colors"
            :class="[
                hasError(index)
                    ? 'border-destructive bg-destructive/5'
                    : Number(getPercentage(rule.multiplier)) > 0
                      ? 'border-amber-200 bg-amber-50/50'
                      : 'border-border',
            ]"
        >
            <!-- Botón eliminar -->
            <Button
                v-if="rules.length > 1"
                type="button"
                variant="ghost"
                size="icon"
                :disabled="disabled"
                @click="removeRule(index)"
                class="absolute top-2 right-2 h-8 w-8"
                title="Eliminar período"
            >
                <Trash2 class="h-4 w-4" />
            </Button>

            <div class="grid gap-4 pr-8 sm:grid-cols-3">
                <!-- Días del mes -->
                <div class="sm:col-span-2">
                    <Label class="mb-2 text-sm font-medium"
                        >Período del mes</Label
                    >
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <Label
                                :for="`day_start_${index}`"
                                class="text-xs text-muted-foreground"
                                >Día</Label
                            >
                            <Input
                                :id="`day_start_${index}`"
                                type="number"
                                min="1"
                                max="31"
                                :model-value="rule.day_start"
                                @update:model-value="
                                    (v) =>
                                        updateRule(
                                            index,
                                            'day_start',
                                            Number(v),
                                        )
                                "
                                :disabled="disabled"
                                class="mt-1 text-center font-semibold"
                                :class="{
                                    'border-destructive': hasError(index),
                                }"
                            />
                        </div>
                        <span class="mt-6 text-muted-foreground">al</span>
                        <div class="flex-1">
                            <Label
                                :for="`day_end_${index}`"
                                class="text-xs text-muted-foreground"
                                >Día</Label
                            >
                            <Input
                                :id="`day_end_${index}`"
                                type="number"
                                min="1"
                                max="31"
                                :model-value="rule.day_end"
                                @update:model-value="
                                    (v) =>
                                        updateRule(index, 'day_end', Number(v))
                                "
                                :disabled="disabled"
                                class="mt-1 text-center font-semibold"
                                :class="{
                                    'border-destructive': hasError(index),
                                }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Recargo -->
                <div>
                    <Label class="mb-2 text-sm font-medium">Recargo</Label>
                    <div class="flex items-center gap-2">
                        <Input
                            :id="`multiplier_${index}`"
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                            :model-value="getPercentage(rule.multiplier)"
                            @update:model-value="
                                (v) => setPercentage(index, Number(v))
                            "
                            :disabled="disabled"
                            class="text-center text-lg font-bold"
                        />
                        <span
                            class="text-lg font-semibold text-muted-foreground"
                            >%</span
                        >
                    </div>
                    <Badge
                        class="mt-2 w-full justify-center"
                        :variant="
                            getBadgeVariant(getPercentage(rule.multiplier))
                        "
                    >
                        {{
                            Number(getPercentage(rule.multiplier)) === 0
                                ? 'Sin recargo'
                                : `+${getPercentage(rule.multiplier)}% de mora`
                        }}
                    </Badge>
                </div>
            </div>

            <!-- Errores -->
            <div
                v-if="hasError(index)"
                class="mt-3 flex items-start gap-2 rounded-md bg-destructive/10 p-2 text-sm text-destructive"
            >
                <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                <div class="space-y-1">
                    <p v-if="rule.day_start > rule.day_end">
                        El día inicio debe ser menor o igual al día fin
                    </p>
                    <p v-if="rule.day_start > 31 || rule.day_end > 31">
                        Los días deben estar entre 1 y 31
                    </p>
                    <p v-if="hasOverlap(index)">
                        Este período se solapa con otro
                    </p>
                </div>
            </div>
        </div>

        <!-- Agregar período -->
        <Button
            type="button"
            variant="outline"
            :disabled="disabled || rules.length >= 5"
            @click="addRule"
            class="w-full"
        >
            <Plus class="mr-2 h-4 w-4" />
            Agregar Período (máx. 5)
        </Button>

        <p class="text-xs text-muted-foreground">
            💡 Los períodos no pueden solaparse. Ejemplo: días 1-10 sin recargo,
            11-20 con +5%, 21-31 con +10%
        </p>
    </div>
</template>
