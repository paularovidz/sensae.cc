<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <style>
        .mp{--bg:#fff;--bg2:#f9fafb;--bg3:#f3f4f6;--bgcard:#fff;--bginput:#fff;--border:#e5e7eb;--border2:#d1d5db;--border3:#f3f4f6;--text:#111827;--text2:#374151;--text3:#6b7280;--text4:#9ca3af;--primary:#8b5cf6;--primary2:#7c3aed;--primarybg:#f5f3ff;--danger:#ef4444;--folder:#f59e0b;}
        .dark .mp{--bg:#111827;--bg2:#1f2937;--bg3:#374151;--bgcard:#1f2937;--bginput:#1f2937;--border:#374151;--border2:#4b5563;--border3:#374151;--text:#f9fafb;--text2:#e5e7eb;--text3:#9ca3af;--text4:#6b7280;--primary:#a78bfa;--primary2:#8b5cf6;--primarybg:rgba(139,92,246,0.15);--danger:#f87171;--folder:#fbbf24;}
        .mp input{background:var(--bginput);border:1px solid var(--border2);color:var(--text);border-radius:8px;padding:6px 12px;font-size:13px;}
        .mp input:focus{outline:none;border-color:var(--primary);}
        @keyframes spin{to{transform:rotate(360deg);}}
    </style>

    <div
        class="mp"
        x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'),
            preview: @js($getPreviewUrl()),
            apiUrl: @js(route('admin.media-api.index')),
            uploadUrl: @js(route('admin.media-api.upload')),
            open: false,
            search: '',
            currentFolder: null,
            folders: [],
            media: [],
            selected: null,
            loading: false,
            dragover: false,
            uploading: 0,
            uploadError: null,
            maxFileSize: 10 * 1024 * 1024,
            creatingFolder: false,
            newFolderName: '',

            get displayedMedia() {
                let items = this.currentFolder
                    ? this.media.filter(i => i.folder === this.currentFolder)
                    : this.media.filter(i => !i.folder);
                if (this.search) {
                    const s = this.search.toLowerCase();
                    items = items.filter(i =>
                        i.slug.toLowerCase().includes(s) ||
                        (i.original_name && i.original_name.toLowerCase().includes(s))
                    );
                }
                return items;
            },

            get displayedFolders() {
                if (this.currentFolder || this.search) return [];
                return this.folders;
            },

            get dropLabel() {
                return this.currentFolder
                    ? 'D\u00e9poser dans \u00ab ' + this.currentFolder + ' \u00bb'
                    : 'D\u00e9poser dans la racine';
            },

            init() {
                this.$watch('state', (v) => {
                    if (v) {
                        const item = this.media.find(m => m.slug === v);
                        if (item) this.preview = item.url;
                    } else {
                        this.preview = null;
                    }
                });
            },

            async openModal() {
                this.open = true;
                this.selected = this.state;
                await this.loadMedia();
            },

            async loadMedia() {
                this.loading = true;
                try {
                    const res = await fetch(this.apiUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();
                    this.media = data.media;
                    this.folders = data.folders;
                } catch (e) {
                    console.error('Failed to load media', e);
                }
                this.loading = false;
            },

            navigateTo(folder) {
                this.currentFolder = folder;
                this.search = '';
                this.creatingFolder = false;
            },

            goToRoot() {
                this.currentFolder = null;
                this.search = '';
                this.creatingFolder = false;
            },

            createFolder() {
                const name = this.newFolderName.trim().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-');
                if (!name) return;
                if (!this.folders.includes(name)) {
                    this.folders.push(name);
                    this.folders.sort();
                }
                this.creatingFolder = false;
                this.newFolderName = '';
                this.navigateTo(name);
            },

            confirm() {
                this.state = this.selected;
                const item = this.media.find(m => m.slug === this.selected);
                if (item) this.preview = item.url;
                this.open = false;
            },

            remove() {
                this.state = null;
                this.preview = null;
                this.selected = null;
            },

            handleDrop(e) {
                this.dragover = false;
                this.uploadFiles(e.dataTransfer.files);
            },

            async uploadFiles(fileList) {
                const files = Array.from(fileList).filter(f => f.type.startsWith('image/'));
                if (!files.length) return;
                this.uploadError = null;
                const tooLarge = files.filter(f => f.size > this.maxFileSize);
                if (tooLarge.length) {
                    const names = tooLarge.map(f => f.name + ' (' + (f.size / 1024 / 1024).toFixed(1) + ' Mo)').join(', ');
                    this.uploadError = 'Fichier(s) trop volumineux (max 10 Mo) : ' + names;
                    return;
                }
                this.uploading = files.length;
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                for (const file of files) {
                    try {
                        const formData = new FormData();
                        formData.append('files[]', file);
                        if (this.currentFolder) formData.append('folder', this.currentFolder);
                        const res = await fetch(this.uploadUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData,
                        });
                        if (!res.ok) {
                            const err = await res.json().catch(() => null);
                            const msg = err?.message || err?.errors?.['files.0']?.[0] || ('Erreur ' + res.status);
                            this.uploadError = file.name + ' : ' + msg;
                            this.uploading--;
                            continue;
                        }
                        const data = await res.json();
                        if (data.uploaded?.length) {
                            this.media.unshift(...data.uploaded);
                            this.selected = data.uploaded[0].slug;
                        }
                    } catch (e) {
                        console.error('Upload error', e);
                        this.uploadError = file.name + ' : erreur inattendue';
                    }
                    this.uploading--;
                }
                await this.loadMedia();
            },
        }"
    >
        {{-- Preview + Button --}}
        <div style="display:flex;align-items:center;gap:12px;">
            <template x-if="preview">
                <img :src="preview" style="max-width:100px;max-height:100px;object-fit:cover;border-radius:8px;border:1px solid var(--border2);">
            </template>

            <div style="display:flex;flex-direction:column;gap:6px;">
                <button
                    type="button"
                    @click="openModal()"
                    style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:14px;font-weight:500;border-radius:8px;border:1px solid var(--border2);background:var(--bg);color:var(--text2);cursor:pointer;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" /></svg>
                    S&eacute;lectionner une image
                </button>

                <template x-if="state">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:12px;color:var(--text3);" x-text="state"></span>
                        <button type="button" @click="remove()" style="color:var(--danger);cursor:pointer;background:none;border:none;padding:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Modal --}}
        <template x-teleport="body">
            <div x-show="open" x-transition.opacity.duration.200ms style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;" x-cloak>
                <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);" @click="open = false"></div>

                <div
                    class="mp"
                    @dragover.prevent="dragover = true"
                    @dragleave.prevent="dragover = false"
                    @drop.prevent="handleDrop($event)"
                    style="position:relative;background:var(--bg);border-radius:12px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);width:100%;max-width:960px;max-height:85vh;display:flex;flex-direction:column;color:var(--text);"
                    @click.stop
                >
                    {{-- Drag overlay --}}
                    <div
                        x-show="dragover"
                        style="position:absolute;inset:0;z-index:10;background:var(--primarybg);border:3px dashed var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;"
                    >
                        <div style="background:var(--bg);padding:16px 32px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.15);text-align:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:32px;height:32px;margin:0 auto 8px;color:var(--primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                            <p style="font-size:14px;font-weight:500;color:var(--primary);" x-text="dropLabel"></p>
                        </div>
                    </div>

                    {{-- Header: breadcrumb + search + close --}}
                    <div style="display:flex;align-items:center;gap:12px;padding:16px 24px;border-bottom:1px solid var(--border);">
                        {{-- Breadcrumb --}}
                        <div style="display:flex;align-items:center;gap:4px;flex:1;min-width:0;">
                            <button
                                type="button"
                                @click="goToRoot()"
                                :style="currentFolder
                                    ? 'font-size:14px;font-weight:500;color:var(--primary);background:none;border:none;cursor:pointer;padding:0;'
                                    : 'font-size:14px;font-weight:600;color:var(--text);background:none;border:none;cursor:default;padding:0;'"
                            >
                                M&eacute;dias
                            </button>
                            <template x-if="currentFolder">
                                <span style="display:flex;align-items:center;gap:4px;">
                                    <span style="color:var(--text4);">/</span>
                                    <span style="font-size:14px;font-weight:600;color:var(--text);" x-text="currentFolder"></span>
                                </span>
                            </template>
                        </div>

                        {{-- Search --}}
                        <input
                            type="text"
                            x-model.debounce.300ms="search"
                            placeholder="Rechercher..."
                            style="width:200px;"
                        >

                        {{-- Close --}}
                        <button type="button" @click="open = false" style="padding:4px;color:var(--text4);background:none;border:none;cursor:pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Upload indicator --}}
                    <template x-if="uploading > 0">
                        <div style="padding:8px 24px;background:var(--primarybg);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
                            <svg style="width:16px;height:16px;animation:spin 1s linear infinite;color:var(--primary);" viewBox="0 0 24 24"><circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span style="font-size:13px;color:var(--primary);font-weight:500;">Upload de <span x-text="uploading"></span> image(s)...</span>
                        </div>
                    </template>

                    {{-- Upload error --}}
                    <template x-if="uploadError">
                        <div style="padding:8px 24px;background:rgba(239,68,68,0.1);border-bottom:1px solid var(--danger);display:flex;align-items:center;gap:8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;color:var(--danger);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                            <span style="font-size:13px;color:var(--danger);font-weight:500;flex:1;" x-text="uploadError"></span>
                            <button type="button" @click="uploadError = null" style="color:var(--danger);background:none;border:none;cursor:pointer;padding:2px;font-size:16px;">&times;</button>
                        </div>
                    </template>

                    {{-- Content --}}
                    <div style="flex:1;overflow-y:auto;padding:16px 24px;">
                        {{-- Folders (root only, no search) --}}
                        <template x-if="displayedFolders.length > 0 || (!currentFolder && !search)">
                            <div style="margin-bottom:16px;">
                                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                    <template x-for="f in displayedFolders" :key="f">
                                        <button
                                            type="button"
                                            @click="navigateTo(f)"
                                            style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;font-size:13px;font-weight:500;color:var(--text2);cursor:pointer;"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;color:var(--folder);"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
                                            <span x-text="f"></span>
                                        </button>
                                    </template>

                                    {{-- New folder button / input --}}
                                    <template x-if="!currentFolder && !search && !creatingFolder">
                                        <button
                                            type="button"
                                            @click="creatingFolder = true; $nextTick(() => $refs.newFolderInput.focus())"
                                            style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:transparent;border:1px dashed var(--border2);border-radius:8px;font-size:13px;color:var(--text4);cursor:pointer;"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                            Nouveau dossier
                                        </button>
                                    </template>
                                    <template x-if="creatingFolder">
                                        <form @submit.prevent="createFolder()" style="display:inline-flex;align-items:center;gap:4px;">
                                            <input
                                                type="text"
                                                x-model="newFolderName"
                                                x-ref="newFolderInput"
                                                @keydown.escape="creatingFolder = false; newFolderName = ''"
                                                placeholder="nom-du-dossier"
                                                style="width:160px;border-color:var(--primary);"
                                            >
                                            <button type="submit" style="padding:6px 10px;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;">OK</button>
                                            <button type="button" @click="creatingFolder = false; newFolderName = ''" style="padding:6px;background:none;border:none;color:var(--text4);cursor:pointer;font-size:16px;">&times;</button>
                                        </form>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Loading --}}
                        <template x-if="loading">
                            <div style="display:flex;align-items:center;justify-content:center;padding:48px 0;color:var(--text4);">
                                Chargement...
                            </div>
                        </template>

                        {{-- Empty state --}}
                        <template x-if="!loading && displayedMedia.length === 0">
                            <div style="text-align:center;padding:48px 0;color:var(--text4);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                                <p style="font-size:14px;">Aucune image ici. Glissez-d&eacute;posez pour en ajouter.</p>
                            </div>
                        </template>

                        {{-- Images grid --}}
                        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;">
                            <template x-for="item in displayedMedia" :key="item.slug">
                                <div
                                    @click="selected = item.slug"
                                    :style="selected === item.slug
                                        ? 'cursor:pointer;border-radius:8px;overflow:hidden;border:2px solid var(--primary);position:relative;background:var(--bgcard);'
                                        : 'cursor:pointer;border-radius:8px;overflow:hidden;border:1px solid var(--border);position:relative;background:var(--bgcard);'"
                                >
                                    <div style="aspect-ratio:1;background:var(--bg3);">
                                        <img :src="item.url" :alt="item.slug" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                                    </div>
                                    <div style="padding:4px 6px;">
                                        <p style="font-size:11px;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.slug"></p>
                                    </div>
                                    <div x-show="selected === item.slug" style="position:absolute;top:6px;right:6px;background:var(--primary);color:#fff;border-radius:50%;padding:2px;">
                                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div style="padding:12px 24px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:13px;color:var(--text3);" x-text="displayedMedia.length + ' image(s)'"></span>
                        <div style="display:flex;gap:8px;">
                            <button type="button" @click="open = false" style="padding:8px 16px;font-size:14px;font-weight:500;border-radius:8px;border:1px solid var(--border2);background:var(--bg);color:var(--text2);cursor:pointer;">
                                Annuler
                            </button>
                            <button
                                type="button"
                                @click="confirm()"
                                :disabled="!selected"
                                :style="selected
                                    ? 'padding:8px 16px;font-size:14px;font-weight:500;border-radius:8px;border:none;background:var(--primary2);color:#fff;cursor:pointer;'
                                    : 'padding:8px 16px;font-size:14px;font-weight:500;border-radius:8px;border:none;background:var(--bg3);color:var(--text4);cursor:not-allowed;'"
                            >
                                S&eacute;lectionner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
