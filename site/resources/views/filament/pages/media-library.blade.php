<x-filament-panels::page>
    <style>
        .mp{--bg:#fff;--bg2:#f9fafb;--bg3:#f3f4f6;--bgcard:#fff;--bginput:#fff;--border:#e5e7eb;--border2:#d1d5db;--border3:#f3f4f6;--text:#111827;--text2:#374151;--text3:#6b7280;--text4:#9ca3af;--primary:#8b5cf6;--primary2:#7c3aed;--primarybg:#f5f3ff;--danger:#ef4444;--folder:#f59e0b;}
        .dark .mp{--bg:#111827;--bg2:#1f2937;--bg3:#374151;--bgcard:#1f2937;--bginput:#1f2937;--border:#374151;--border2:#4b5563;--border3:#374151;--text:#f9fafb;--text2:#e5e7eb;--text3:#9ca3af;--text4:#6b7280;--primary:#a78bfa;--primary2:#8b5cf6;--primarybg:rgba(139,92,246,0.15);--danger:#f87171;--folder:#fbbf24;}
        .mp input,.mp select{background:var(--bginput);border:1px solid var(--border2);color:var(--text);border-radius:8px;padding:8px 12px;font-size:14px;}
        .mp input:focus,.mp select:focus{outline:none;border-color:var(--primary);}
        .mp-card{border-radius:8px;overflow:hidden;border:1px solid var(--border);background:var(--bgcard);}
        .mp-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;font-size:14px;font-weight:500;color:var(--text2);cursor:pointer;}
        .mp-btn:hover{background:var(--bg3);}
        .mp-btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;background:transparent;border:1px dashed var(--border2);border-radius:8px;font-size:14px;color:var(--text4);cursor:pointer;}
        .mp-btn-primary{padding:8px 16px;font-size:14px;font-weight:500;border-radius:8px;border:none;background:var(--primary);color:#fff;cursor:pointer;}
        .mp-btn-primary:hover{background:var(--primary2);}
        .mp-btn-sm{padding:4px 10px;font-size:12px;border-radius:4px;border:none;cursor:pointer;}
        .mp-link{font-size:11px;background:none;border:none;cursor:pointer;padding:0;}
        @keyframes spin{to{transform:rotate(360deg);}}
    </style>

    <div
        class="mp"
        x-data="{
            apiUrl: @js(route('admin.media-api.index')),
            uploadUrl: @js(route('admin.media-api.upload')),
            updateUrl: @js(url('admin/media-api')),
            search: '',
            currentFolder: null,
            folders: [],
            media: [],
            loading: true,
            dragover: false,
            uploading: 0,
            uploadError: null,
            creatingFolder: false,
            newFolderName: '',
            editing: null,
            editForm: { slug: '', alt: '', folder: '' },
            renamingFolder: null,
            renameFolderName: '',
            maxFileSize: 10 * 1024 * 1024,

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

            async init() { await this.loadMedia(); },

            async loadMedia() {
                this.loading = true;
                try {
                    const res = await fetch(this.apiUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();
                    this.media = data.media;
                    this.folders = data.folders;
                } catch (e) { console.error('Failed to load media', e); }
                this.loading = false;
            },

            navigateTo(folder) { this.currentFolder = folder; this.search = ''; this.creatingFolder = false; this.editing = null; this.renamingFolder = null; },
            goToRoot() { this.currentFolder = null; this.search = ''; this.creatingFolder = false; this.editing = null; this.renamingFolder = null; },

            createFolder() {
                const name = this.newFolderName.trim().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-');
                if (!name) return;
                if (!this.folders.includes(name)) { this.folders.push(name); this.folders.sort(); }
                this.creatingFolder = false; this.newFolderName = '';
                this.navigateTo(name);
            },

            startRenameFolder(folder) {
                this.renamingFolder = folder;
                this.renameFolderName = folder;
            },

            async renameFolder(oldName) {
                const newName = this.renameFolderName.trim().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-');
                if (!newName || newName === oldName) { this.renamingFolder = null; return; }
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                try {
                    const res = await fetch(this.updateUrl + '/folders/rename', {
                        method: 'PUT',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ old_name: oldName, new_name: newName }),
                    });
                    if (!res.ok) throw new Error('Rename failed');
                    const data = await res.json();
                    if (this.currentFolder === oldName) this.currentFolder = data.new_name;
                    this.renamingFolder = null;
                    await this.loadMedia();
                } catch (e) { console.error('Rename folder error', e); }
            },

            handleDrop(e) { this.dragover = false; this.uploadFiles(e.dataTransfer.files); },

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
                        const fd = new FormData();
                        fd.append('files[]', file);
                        if (this.currentFolder) fd.append('folder', this.currentFolder);
                        const res = await fetch(this.uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
                        if (!res.ok) {
                            const err = await res.json().catch(() => null);
                            const msg = err?.message || err?.errors?.['files.0']?.[0] || ('Erreur ' + res.status);
                            this.uploadError = file.name + ' : ' + msg;
                            this.uploading--;
                            continue;
                        }
                        const data = await res.json();
                        if (data.uploaded?.length) this.media.unshift(...data.uploaded);
                    } catch (e) { console.error('Upload error', e); this.uploadError = file.name + ' : erreur inattendue'; }
                    this.uploading--;
                }
                await this.loadMedia();
            },

            openEdit(item) { this.editing = item.id; this.editForm = { slug: item.slug, alt: item.alt || '', folder: item.folder || '' }; },

            async saveEdit(item) {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                try {
                    const res = await fetch(this.updateUrl + '/' + item.id, { method: 'PUT', headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify(this.editForm) });
                    if (!res.ok) throw new Error('Update failed');
                    item.slug = this.editForm.slug; item.alt = this.editForm.alt; item.folder = this.editForm.folder || null;
                    this.editing = null; await this.loadMedia();
                } catch (e) { console.error('Update error', e); }
            },

            async deleteMedia(item) {
                if (!confirm('Supprimer \u00ab ' + item.slug + ' \u00bb ?')) return;
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                try {
                    const res = await fetch(this.updateUrl + '/' + item.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('Delete failed');
                    this.media = this.media.filter(m => m.id !== item.id);
                    if (this.editing === item.id) this.editing = null;
                } catch (e) { console.error('Delete error', e); }
            },

            formatSize(bytes) {
                if (!bytes) return '';
                if (bytes < 1024) return bytes + ' o';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' Ko';
                return (bytes / 1048576).toFixed(1) + ' Mo';
            },
        }"
        @dragover.prevent="dragover = true"
        @dragleave.prevent="dragover = false"
        @drop.prevent="handleDrop($event)"
        style="position:relative;color:var(--text);"
    >
        {{-- Drag overlay --}}
        <div x-show="dragover" style="position:absolute;inset:0;z-index:10;background:var(--primarybg);border:3px dashed var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;">
            <div style="background:var(--bg);padding:20px 40px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.15);text-align:center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 8px;color:var(--primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                <p style="font-size:16px;font-weight:500;color:var(--primary);" x-text="dropLabel"></p>
            </div>
        </div>

        {{-- Upload indicator --}}
        <template x-if="uploading > 0">
            <div style="padding:10px 16px;background:var(--primarybg);border:1px solid var(--primary);border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <svg style="width:16px;height:16px;animation:spin 1s linear infinite;color:var(--primary);" viewBox="0 0 24 24"><circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span style="font-size:14px;color:var(--primary);font-weight:500;">Upload de <span x-text="uploading"></span> image(s)...</span>
            </div>
        </template>

        {{-- Upload error --}}
        <template x-if="uploadError">
            <div style="padding:10px 16px;background:rgba(239,68,68,0.1);border:1px solid var(--danger);border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;color:var(--danger);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                <span style="font-size:14px;color:var(--danger);font-weight:500;flex:1;" x-text="uploadError"></span>
                <button type="button" @click="uploadError = null" style="color:var(--danger);background:none;border:none;cursor:pointer;padding:2px;font-size:18px;">&times;</button>
            </div>
        </template>

        {{-- Toolbar --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:6px;flex:1;">
                <button type="button" @click="goToRoot()" :style="currentFolder ? 'font-size:15px;font-weight:500;color:var(--primary);background:none;border:none;cursor:pointer;padding:0;' : 'font-size:15px;font-weight:600;color:var(--text);background:none;border:none;cursor:default;padding:0;'">
                    M&eacute;dias
                </button>
                <template x-if="currentFolder && renamingFolder !== currentFolder">
                    <span style="display:flex;align-items:center;gap:6px;">
                        <span style="color:var(--text4);font-size:15px;">/</span>
                        <span style="font-size:15px;font-weight:600;color:var(--text);" x-text="currentFolder"></span>
                        <button type="button" @click="startRenameFolder(currentFolder)" style="background:none;border:none;cursor:pointer;color:var(--text4);padding:2px;" title="Renommer le dossier">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                        </button>
                    </span>
                </template>
                <template x-if="currentFolder && renamingFolder === currentFolder">
                    <form @submit.prevent="renameFolder(currentFolder)" style="display:inline-flex;align-items:center;gap:6px;">
                        <span style="color:var(--text4);font-size:15px;">/</span>
                        <input type="text" x-model="renameFolderName" x-init="$nextTick(() => $el.focus())" @keydown.escape="renamingFolder = null" style="width:180px;border-color:var(--primary);padding:6px 10px;font-size:14px;">
                        <button type="submit" class="mp-btn-primary mp-btn-sm" style="padding:6px 12px;font-size:13px;">OK</button>
                        <button type="button" @click="renamingFolder = null" style="padding:4px;background:none;border:none;color:var(--text4);cursor:pointer;font-size:18px;">&times;</button>
                    </form>
                </template>
            </div>
            <input type="text" x-model.debounce.300ms="search" placeholder="Rechercher..." style="width:220px;">
        </div>

        {{-- Folders --}}
        <template x-if="displayedFolders.length > 0 || (!currentFolder && !search)">
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
                <template x-for="f in displayedFolders" :key="f">
                    <div style="display:inline-flex;position:relative;">
                        <template x-if="renamingFolder !== f">
                            <div style="display:inline-flex;align-items:center;gap:0;">
                                <button type="button" @click="navigateTo(f)" class="mp-btn" style="border-top-right-radius:0;border-bottom-right-radius:0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;color:var(--folder);"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
                                    <span x-text="f"></span>
                                </button>
                                <button type="button" @click.stop="startRenameFolder(f)" style="display:inline-flex;align-items:center;padding:10px 10px;background:var(--bg2);border:1px solid var(--border);border-left:none;border-top-right-radius:8px;border-bottom-right-radius:8px;cursor:pointer;color:var(--text4);" title="Renommer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="renamingFolder === f">
                            <form @submit.prevent="renameFolder(f)" style="display:inline-flex;align-items:center;gap:4px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;color:var(--folder);"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
                                <input type="text" x-model="renameFolderName" x-init="$nextTick(() => $el.focus())" @keydown.escape="renamingFolder = null" style="width:160px;border-color:var(--primary);padding:6px 10px;font-size:13px;">
                                <button type="submit" class="mp-btn-primary mp-btn-sm" style="padding:8px 12px;font-size:14px;">OK</button>
                                <button type="button" @click="renamingFolder = null" style="padding:6px;background:none;border:none;color:var(--text4);cursor:pointer;font-size:18px;">&times;</button>
                            </form>
                        </template>
                    </div>
                </template>
                <template x-if="!currentFolder && !search && !creatingFolder">
                    <button type="button" @click="creatingFolder = true; $nextTick(() => $refs.newFolderInput.focus())" class="mp-btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Nouveau dossier
                    </button>
                </template>
                <template x-if="creatingFolder">
                    <form @submit.prevent="createFolder()" style="display:inline-flex;align-items:center;gap:4px;">
                        <input type="text" x-model="newFolderName" x-ref="newFolderInput" @keydown.escape="creatingFolder = false; newFolderName = ''" placeholder="nom-du-dossier" style="width:180px;border-color:var(--primary);">
                        <button type="submit" class="mp-btn-primary mp-btn-sm" style="padding:8px 12px;font-size:14px;">OK</button>
                        <button type="button" @click="creatingFolder = false; newFolderName = ''" style="padding:6px;background:none;border:none;color:var(--text4);cursor:pointer;font-size:18px;">&times;</button>
                    </form>
                </template>
            </div>
        </template>

        {{-- Loading --}}
        <template x-if="loading">
            <div style="display:flex;align-items:center;justify-content:center;padding:64px 0;color:var(--text4);">Chargement...</div>
        </template>

        {{-- Empty --}}
        <template x-if="!loading && displayedMedia.length === 0">
            <div style="text-align:center;padding:64px 0;color:var(--text4);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:48px;height:48px;margin:0 auto 12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                <p style="font-size:16px;font-weight:500;">Aucune image</p>
                <p style="font-size:14px;margin-top:4px;">Glissez-d&eacute;posez des images pour les ajouter.</p>
            </div>
        </template>

        {{-- Grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;">
            <template x-for="item in displayedMedia" :key="item.id">
                <div class="mp-card">
                    <div style="aspect-ratio:1;background:var(--bg3);position:relative;cursor:pointer;" @click="openEdit(item)">
                        <img :src="item.url" :alt="item.slug" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                    </div>

                    {{-- Info --}}
                    <div x-show="editing !== item.id" style="padding:8px 10px;">
                        <p style="font-size:12px;font-weight:500;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.slug"></p>
                        <p style="font-size:11px;color:var(--text4);margin-top:2px;">
                            <span x-text="item.width + '\u00d7' + item.height"></span>
                            <span x-show="item.size"> &middot; <span x-text="formatSize(item.size)"></span></span>
                        </p>
                        <div style="display:flex;gap:6px;margin-top:6px;">
                            <button type="button" @click="openEdit(item)" class="mp-link" style="color:var(--primary);">Modifier</button>
                            <span style="color:var(--border);">|</span>
                            <button type="button" @click="deleteMedia(item)" class="mp-link" style="color:var(--danger);">Supprimer</button>
                        </div>
                    </div>

                    {{-- Edit --}}
                    <div x-show="editing === item.id" @click.outside="editing = null" style="padding:8px 10px;">
                        <div style="margin-bottom:6px;">
                            <label style="font-size:11px;color:var(--text3);">Slug</label>
                            <input type="text" x-model="editForm.slug" style="width:100%;padding:4px 8px;font-size:12px;">
                        </div>
                        <div style="margin-bottom:6px;">
                            <label style="font-size:11px;color:var(--text3);">Alt</label>
                            <input type="text" x-model="editForm.alt" style="width:100%;padding:4px 8px;font-size:12px;">
                        </div>
                        <div style="margin-bottom:8px;">
                            <label style="font-size:11px;color:var(--text3);">Dossier</label>
                            <input type="text" x-model="editForm.folder" style="width:100%;padding:4px 8px;font-size:12px;">
                        </div>
                        <div style="display:flex;gap:6px;">
                            <button type="button" @click="saveEdit(item)" class="mp-btn-primary mp-btn-sm">Enregistrer</button>
                            <button type="button" @click="editing = null" class="mp-btn-sm" style="background:var(--bg3);color:var(--text2);">Annuler</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <template x-if="!loading && displayedMedia.length > 0">
            <div style="margin-top:20px;text-align:center;font-size:13px;color:var(--text4);" x-text="displayedMedia.length + ' image(s)'"></div>
        </template>
    </div>
</x-filament-panels::page>
