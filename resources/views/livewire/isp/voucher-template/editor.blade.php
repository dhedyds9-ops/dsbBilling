                <!-- Editor -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Source Code (HTML/CSS)</h3>
                        <button wire:click="validateContent" class="text-sm bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded hover:bg-slate-200">
                            Validasi Syntax
                        </button>
                    </div>

                    @if(!empty($validationErrors))
                        <div class="mb-4 bg-red-50 dark:bg-red-900/30 p-4 rounded-md">
                            <h4 class="text-red-800 font-bold text-sm mb-2">Error Ditemukan:</h4>
                            <ul class="list-disc pl-5 text-sm text-red-700">
                                @foreach($validationErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="border rounded-md border-slate-300 dark:border-slate-600">
                        
                        <div wire:ignore class="w-full h-[500px] border-0">
                            <div id="monaco-container" class="w-full h-full"></div>
                        </div>
                        <input type="hidden" wire:model.defer="template_code" id="hidden_template_code">







