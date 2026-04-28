@props([
    'formSchema' => [],
    'routes'     => [],
    'size'       => '',
])

<div class="modal fade" id="createRecordModal" tabindex="-1" aria-labelledby="createRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog {{ $size ? 'modal-'.$size : '' }} modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            {{-- Modal Header --}}
            <div class="modal-header bg-body border-bottom p-3">
                <h6 class="modal-title fw-bold text-body mb-0" id="createRecordModalLabel">
                    {{ __('messages.create_new_record') }}
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>

            {{-- Modal Form --}}
            <form 
                action="{{ $routes['store'] ?? '#' }}" 
                method="POST" 
                id="create-modal-form" 
                enctype="multipart/form-data"
            >
                @csrf
                
                {{-- Error Messages --}}
                <div id="modal-form-error-container"></div>
                
                {{-- Form Fields --}}
                <div class="modal-body p-4 custom-scrollbar">
                    @foreach($formSchema as $field)
                        <x-form-field :field="$field" />
                    @endforeach
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer bg-body border-top p-3">
                    <button 
                        type="button" 
                        class="btn btn-sm btn-body border text-body-secondary shadow-none" 
                        data-bs-dismiss="modal"
                    >
                        {{ __('messages.cancel') }}
                    </button>
                    <button 
                        type="submit" 
                        class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center" 
                        style="background-color: #4f46e5; border-color: #4f46e5;"
                    >
                        {{ __('messages.save_record') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>