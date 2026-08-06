<div>
    <header class="topbar">
        <div>
            <h1>
                @if($step === 'choose')
                    Upload Report
                @elseif($step === 'upload')
                    Upload Scanned Document
                @else
                    {{ $entryMode === 'manual' ? 'Fill in Manually' : 'Review OCR Results' }}
                @endif
            </h1>
            <p>Digitize historical OM/DDS paper forms (Old Reports)</p>
        </div>
        <button type="button" class="btn btn-secondary" wire:click="back">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </header>

    @if($step === 'choose')
        <div class="entry-cards">
            <button type="button" class="entry-card recommended" wire:click="selectOcr">
                <span class="badge-rec">Recommended</span>
                <h3 style="margin-bottom:0.5rem;"><i class="fas fa-camera" style="color:var(--accent-blue);"></i> Upload Scanned Document</h3>
                <p style="color:var(--text-secondary); font-size:0.875rem;">Upload an image of a scanned OM/DDS form. OCR will auto-fill fields for you to verify.</p>
            </button>
            <button type="button" class="entry-card" wire:click="selectManual">
                <h3 style="margin-bottom:0.5rem;"><i class="fas fa-keyboard" style="color:var(--accent-blue);"></i> Fill in Manually</h3>
                <p style="color:var(--text-secondary); font-size:0.875rem;">Enter all form fields by hand when no scan is available.</p>
            </button>
        </div>
    @endif

    @if($step === 'upload')
        <div class="card" style="max-width:560px;">
            <h3 class="card-title">Upload scanned OM/DDS form</h3>
            <p style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:1rem;">JPEG or PNG, max 15 MB. Ensure the form is flat and well lit.</p>
            <input type="file" wire:model="scanUpload" accept="image/*" class="form-control">
            @error('scanUpload')<p class="error-text">{{ $message }}</p>@enderror
            <div wire:loading wire:target="scanUpload" style="margin-top:0.5rem; color:var(--text-secondary); font-size:0.8rem;">Uploading…</div>
            <div wire:loading wire:target="processOcr" class="ocr-loading" style="padding:1.5rem 0;">
                <div class="spinner"></div>
                <p>Running OCR on scanned document…</p>
            </div>
            <div class="form-actions" wire:loading.remove wire:target="processOcr">
                <button type="button" class="btn btn-primary" wire:click="processOcr" wire:loading.attr="disabled" wire:target="processOcr,scanUpload">
                    <i class="fas fa-wand-magic-sparkles"></i> Run OCR
                </button>
            </div>
        </div>
    @endif

    @if($step === 'form')
        @if($entryMode === 'ocr')
            <div class="archive-banner" style="border-color:rgba(231,76,60,0.4);">
                <i class="fas fa-circle-exclamation" style="color:var(--accent-red);"></i>
                Fields highlighted in red had low OCR confidence. Edit any value before saving.
            </div>
        @endif

        <form wire:submit="save">
            @include('livewire.operations.partials.paper-report-fields')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save"><i class="fas fa-save"></i> Save Archive Report</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
                <button type="button" class="btn btn-secondary" wire:click="back">Cancel</button>
            </div>
        </form>
    @endif
</div>

@script
<script>
    initDmsDatePickers($el);
</script>
@endscript
