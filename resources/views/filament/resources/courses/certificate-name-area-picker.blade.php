@php
    $record = $record ?? $getRecord();
    $imageUrl = $record?->getFirstMediaUrl('template_image', 'preview') ?: $record?->getFirstMediaUrl('template_image');
    $sampleName = __('Student Name');
@endphp

<div
    x-data="window.certificateNameAreaPicker({
        imageUrl: @js($imageUrl),
        x1: @js($record?->x1 ?? 0.22),
        y1: @js($record?->y1 ?? 0.44),
        x2: @js($record?->x2 ?? 0.78),
        y2: @js($record?->y2 ?? 0.58),
        sampleName: @js($sampleName),
    })"
    x-init="init()"
    class="space-y-3"
>
    <div class="text-sm text-gray-500">
        {{ __('Click once to set the first corner, then click again to set the opposite corner of the student-name box.') }}
    </div>

    <template x-if="!imageUrl">
        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
            {{ __('Upload a certificate image first, then reopen this action to mark the name area.') }}
        </div>
    </template>

    <template x-if="imageUrl">
        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-3">
            <div class="relative mx-auto overflow-hidden rounded-xl bg-white shadow-sm" style="max-width: 900px;" @click="selectPoint($event)">
                <img :src="imageUrl" alt="Certificate Template" class="block w-full h-auto select-none">

                <div
                    x-show="hasSelection"
                    class="absolute border-2 border-brand-500 bg-brand-500/10"
                    :style="selectionStyle"
                ></div>

                <div
                    x-show="hasSelection"
                    class="absolute flex items-center justify-center px-3 text-center text-sm font-semibold text-brand-700"
                    :style="previewTextStyle"
                >
                    <span x-text="sampleName"></span>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    window.certificateNameAreaPicker = window.certificateNameAreaPicker || function (config) {
        return {
            imageUrl: config.imageUrl,
            sampleName: config.sampleName,
            firstPoint: null,
            secondPoint: null,
            selectionStyle: '',
            previewTextStyle: '',
            hasSelection: false,
            init() {
                this.firstPoint = { x: Number(config.x1), y: Number(config.y1) };
                this.secondPoint = { x: Number(config.x2), y: Number(config.y2) };
                this.refreshStyles();
            },
            selectPoint(event) {
                const rect = event.currentTarget.getBoundingClientRect();
                const x = Math.min(Math.max((event.clientX - rect.left) / rect.width, 0), 1);
                const y = Math.min(Math.max((event.clientY - rect.top) / rect.height, 0), 1);

                if (!this.firstPoint || (this.firstPoint && this.secondPoint)) {
                    this.firstPoint = { x, y };
                    this.secondPoint = null;
                    this.hasSelection = false;
                    this.setField('x1', x);
                    this.setField('y1', y);
                    this.setField('x2', x);
                    this.setField('y2', y);
                    this.refreshStyles();
                    return;
                }

                this.secondPoint = { x, y };
                this.setField('x2', x);
                this.setField('y2', y);
                this.refreshStyles();
            },
            setField(field, value) {
                const scope = this.$root.closest('[role="dialog"]') || document;
                const wrapper = scope.querySelector(`[data-cert-coordinate="${field}"]`);

                if (!wrapper) {
                    return;
                }

                const input = wrapper.querySelector('input');

                if (!input) {
                    return;
                }

                input.value = Number(value).toFixed(4);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            },
            refreshStyles() {
                if (!this.firstPoint || !this.secondPoint) {
                    this.selectionStyle = '';
                    this.previewTextStyle = '';
                    this.hasSelection = false;
                    return;
                }

                const left = Math.min(this.firstPoint.x, this.secondPoint.x) * 100;
                const top = Math.min(this.firstPoint.y, this.secondPoint.y) * 100;
                const width = Math.max(Math.abs(this.secondPoint.x - this.firstPoint.x) * 100, 1);
                const height = Math.max(Math.abs(this.secondPoint.y - this.firstPoint.y) * 100, 1);

                this.selectionStyle = `left:${left}%;top:${top}%;width:${width}%;height:${height}%;`;
                this.previewTextStyle = `left:${left}%;top:${top}%;width:${width}%;height:${height}%;`;
                this.hasSelection = true;
            },
        };
    };
</script>
