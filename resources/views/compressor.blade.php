<x-guest-layout>
    <div class="max-w-3xl mx-auto py-12">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-500 p-[1px] shadow">
            <div class="rounded-2xl bg-white">
                <div class="px-6 py-8 sm:px-10">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Image Compressor</h1>
                            <p class="mt-1 text-sm text-gray-500">Lossless PNG/WebP recompression. JPEGs are passed through unchanged.</p>
                        </div>
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4"><path d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm.53 6.22 3 3a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 1 1-1.06-1.06l1.72-1.72H8.25a.75.75 0 0 1 0-1.5h4.94l-1.72-1.72a.75.75 0 1 1 1.06-1.06Z"/></svg>
                            <span>Login for API key</span>
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="compress-form" action="{{ route('compressor.compress') }}" method="POST" enctype="multipart/form-data" class="mt-6">
                        @csrf

                        <div id="dropzone" class="relative rounded-xl border border-dashed border-gray-300 bg-gray-50/80 p-6 transition hover:bg-gray-50">
                            <input id="image" name="image" type="file" accept="image/png,image/webp,image/jpeg" class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0" />
                            <div class="pointer-events-none flex flex-col items-center justify-center text-center">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-7 text-indigo-600"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h13.5A2.25 2.25 0 0 1 21 8.25V12M3 16.5A2.25 2.25 0 0 0 5.25 18.75h7.5M3 16.5l5.159-3.439a2.25 2.25 0 0 1 2.45 0l2.663 1.775M21 12l-3-3m0 0-3 3m3-3v9"/></svg>
                                </div>
                                <p class="mt-3 text-sm text-gray-700"><span class="font-medium text-indigo-600">Click to upload</span> or drag & drop</p>
                                <p class="mt-1 text-xs text-gray-500">PNG, WebP, JPEG up to 10MB</p>
                                <div id="file-meta" class="mt-3 hidden rounded-md bg-white/70 px-3 py-2 text-xs text-gray-600 shadow-sm"></div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-3 sm:items-center">
                            <div class="sm:col-span-2">
                                <label for="quality" class="block text-sm font-medium text-gray-700">Quality (JPEG/WebP)</label>
                                <div class="mt-1 flex items-center gap-3">
                                    <input id="quality" name="quality" type="range" min="30" max="95" value="75" class="w-full accent-indigo-600" />
                                    <span id="qualityValue" class="w-10 text-right text-sm text-gray-600">75</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Lower = smaller file, higher = better quality.</p>
                            </div>
                            <button id="submit-btn" type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50">
                                <svg id="spinner" class="hidden size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                <span>Compress & Download</span>
                            </button>
                            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700 sm:hidden">Login for API key →</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-8 grid gap-6 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-medium text-gray-900">Tips</h2>
                <ul class="mt-3 list-disc list-inside text-sm text-gray-600">
                    <li>PNG/WebP are recompressed losslessly.</li>
                    <li>JPEGs are passed through unchanged.</li>
                    <li>Max file size is 10MB.</li>
                </ul>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-medium text-gray-900">API</h2>
                <p class="mt-2 text-sm text-gray-600">Create an API token after login and call:</p>
<pre class="mt-3 overflow-x-auto rounded-lg bg-gray-900 p-3 text-xs text-gray-100"><code>POST /api/v1/compress
Authorization: Bearer &lt;token&gt;
Content-Type: multipart/form-data

image: &lt;file&gt;</code></pre>
            </div>
        </div>
    </div>

    <script>
        const input = document.getElementById('image');
        const dropzone = document.getElementById('dropzone');
        const meta = document.getElementById('file-meta');
        const form = document.getElementById('compress-form');
        const submitBtn = document.getElementById('submit-btn');
        const spinner = document.getElementById('spinner');

        const maxBytes = 10 * 1024 * 1024;
        const allowed = ['image/png','image/webp','image/jpeg'];
        const qualityInput = document.getElementById('quality');
        const qualityValue = document.getElementById('qualityValue');

        function renderMeta(file){
            if(!file) { meta.classList.add('hidden'); meta.textContent=''; return; }
            const kb = (file.size/1024).toFixed(1);
            meta.textContent = `${file.name} · ${kb} KB · ${file.type || 'unknown'}`;
            meta.classList.remove('hidden');
        }

        function validate(file){
            if(!allowed.includes(file.type)) { alert('Only PNG, WebP, JPEG are allowed.'); return false; }
            if(file.size > maxBytes) { alert('Max file size is 10MB.'); return false; }
            return true;
        }

        input.addEventListener('change', (e)=>{
            const file = e.target.files[0];
            if(file && validate(file)) { renderMeta(file); }
        });

        ;['dragenter','dragover'].forEach(evt=>{
            dropzone.addEventListener(evt, (e)=>{ e.preventDefault(); dropzone.classList.add('bg-gray-100'); });
        });
        ;['dragleave','drop'].forEach(evt=>{
            dropzone.addEventListener(evt, (e)=>{ e.preventDefault(); dropzone.classList.remove('bg-gray-100'); });
        });
        dropzone.addEventListener('drop', (e)=>{
            const files = e.dataTransfer.files;
            if(files && files[0]){
                const f = files[0];
                if(!validate(f)) return;
                input.files = files;
                renderMeta(f);
            }
        });

        let reenableTimer;
        function reenable() {
            submitBtn.disabled = false;
            spinner.classList.add('hidden');
            if (reenableTimer) clearTimeout(reenableTimer);
        }

        // Re-enable on return to page or after a timeout fallback
        window.addEventListener('pageshow', reenable);
        document.addEventListener('visibilitychange', ()=>{ if (!document.hidden) reenable(); });

        form.addEventListener('submit', ()=>{
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            // Fallback in case streaming download keeps page open
            reenableTimer = setTimeout(reenable, 15000);
        });

        qualityInput.addEventListener('input', ()=>{
            qualityValue.textContent = qualityInput.value;
        });
    </script>
</x-guest-layout>


