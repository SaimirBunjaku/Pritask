@extends('layouts.app')

@section('title', 'Tags')

@section('content')
    <div class="page-header">
        <h1>Tags</h1>
    </div>

    <div class="card">
        <h2 class="card-section-title">New tag</h2>
        <form id="tag-create-form" action="{{ route('tags.store') }}" method="POST" data-tag-create-form>
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Bug">
                    <p class="field-error" data-error-for="name"></p>
                </div>

                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="color" name="color" id="color" class="form-control form-control-color" value="{{ old('color', '#0071e3') }}">
                    <p class="field-error" data-error-for="color"></p>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" data-tag-create-submit>Create tag</button>
            </div>
        </form>
    </div>

    <h2 class="section-heading">All tags</h2>

    <div id="tags-list">
        @forelse ($tags as $tag)
            @include('tags.partials.list-item', ['tag' => $tag])
        @empty
            <p class="empty-state" id="tags-empty">No tags yet. Create one above to start labeling issues.</p>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('tag-create-form');
            const list = document.getElementById('tags-list');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!form || !list) {
                return;
            }

            function clearErrors() {
                form.querySelectorAll('[data-error-for]').forEach((el) => {
                    el.textContent = '';
                });
            }

            function showErrors(errors) {
                Object.entries(errors).forEach(([field, messages]) => {
                    const el = form.querySelector(`[data-error-for="${field}"]`);
                    if (el) {
                        el.textContent = messages[0];
                    }
                });
            }

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearErrors();

                const submitBtn = form.querySelector('[data-tag-create-submit]');
                submitBtn.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: new FormData(form),
                    });

                    if (response.status === 422) {
                        showErrors((await response.json()).errors);
                        return;
                    }

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    document.getElementById('tags-empty')?.remove();
                    list.insertAdjacentHTML('afterbegin', data.listItem);
                    form.reset();
                    form.querySelector('#color').value = '#0071e3';

                    const viewport = document.querySelector('.board-viewport');
                    if (viewport?.dataset.allTags) {
                        try {
                            const allTags = JSON.parse(viewport.dataset.allTags);
                            allTags.push(data.tag);
                            allTags.sort((a, b) => a.name.localeCompare(b.name));
                            viewport.dataset.allTags = JSON.stringify(allTags);
                        } catch {
                            // ignore parse errors on non-board pages
                        }
                    }
                } finally {
                    submitBtn.disabled = false;
                }
            });
        })();
    </script>
@endpush
