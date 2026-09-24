<div x-data="remediationTracker()" 
     @remediation-required.window="openModal($event.detail)"
     class="relative z-50" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true" 
     x-show="isOpen" 
     style="display: none;">
     
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
         x-show="isOpen" 
         x-transition.opacity></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                 x-show="isOpen"
                 x-transition>
                
                <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/50 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Warning Icon -->
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                                Remediasi ONU Diperlukan
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    ONU tidak siap untuk layanan <strong x-text="targetCapability"></strong>. 
                                    Sistem telah merancang rencana remediasi berdasarkan profil perangkat.
                                </p>

                                <!-- Plan Details -->
                                <div x-show="!isExecuting && plan" class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-md border border-gray-200 dark:border-gray-700">
                                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Dry-Run Plan (Otomatis):</h4>
                                    <ul class="space-y-3">
                                        <template x-for="(step, index) in steps" :key="index">
                                            <li class="flex items-start">
                                                <span class="flex-shrink-0 h-5 w-5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-primary-600 flex items-center justify-center text-xs font-bold mr-2 mt-0.5" x-text="index + 1"></span>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="step.action"></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="step.description"></p>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                    
                                    <div x-show="requiresApproval" class="mt-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 rounded text-red-700 text-xs flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Plan ini membutuhkan Approval Administrator karena mengandung High-Risk Action (Unlock/Firmware).
                                    </div>
                                </div>

                                <!-- Execution Tracker -->
                                <div x-show="isExecuting" class="mt-4">
                                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Status Eksekusi:</h4>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-primary-700" x-text="jobStatus"></span>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400" x-show="jobStatus === 'WAITING_RECONNECT'">Menunggu ONU online...</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-primary-600 h-2.5 rounded-full transition-all duration-500" :style="`width: ${progressPercent}%`"></div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="statusMessage"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" 
                            x-show="!isExecuting"
                            @click="executePlan()"
                            class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">
                        Approve & Execute
                    </button>
                    
                    <button type="button" 
                            x-show="isExecuting && isFinished"
                            @click="closeModal()"
                            class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">
                        Tutup & Lanjutkan
                    </button>

                    <button type="button" 
                            x-show="!isExecuting"
                            @click="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-900/50 sm:mt-0 sm:w-auto">
                        Batal
                    </button>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
function remediationTracker() {
    return {
        isOpen: false,
        onuId: null,
        customerServiceId: null,
        plan: null,
        steps: [],
        targetCapability: '',
        requiresApproval: false,
        
        isExecuting: false,
        jobUuid: null,
        jobStatus: 'PENDING',
        statusMessage: 'Menyiapkan eksekusi...',
        progressPercent: 0,
        isFinished: false,
        pollInterval: null,

        openModal(data) {
            this.targetCapability = data.target_capability;
            this.customerServiceId = data.customer_service_id;
            this.onuId = data.onu_id; // Passed from parent event
            this.plan = data.remediation_plan;
            this.steps = this.plan.steps || [];
            this.requiresApproval = this.plan.requires_approval || false;
            
            this.isExecuting = false;
            this.isFinished = false;
            this.progressPercent = 0;
            this.isOpen = true;
        },

        closeModal() {
            this.isOpen = false;
            if (this.pollInterval) clearInterval(this.pollInterval);
        },

        async executePlan() {
            this.isExecuting = true;
            this.statusMessage = 'Memulai job remediasi...';
            this.progressPercent = 10;

            try {
                // Panggil endpoint eksekusi (Phase 6.1)
                const response = await axios.post(`/api/v1/isp/onus/${this.onuId}/remediate`, {
                    plan_hash: this.plan.plan_hash,
                    target_capability: this.targetCapability,
                    plan: this.plan,
                    customer_service_id: this.customerServiceId
                });

                this.jobUuid = response.data.job_uuid;
                this.jobStatus = response.data.job_status;
                
                if (this.jobStatus === 'APPROVAL_REQUIRED') {
                    this.statusMessage = 'Akses ditolak: Membutuhkan peran Approval Administrator.';
                    this.isFinished = true;
                    return;
                }

                // Mulai Polling status job
                this.startPolling();

            } catch (error) {
                this.jobStatus = 'ERROR';
                this.statusMessage = error.response?.data?.message || 'Gagal memulai remediasi';
                this.isFinished = true;
            }
        },

        startPolling() {
            this.pollInterval = setInterval(async () => {
                try {
                    // Endpoint GET status job (harus ditambahkan di controller nanti)
                    // Sementara kita asumsikan strukturnya
                    const res = await axios.get(`/api/v1/isp/remediation-jobs/${this.jobUuid}`);
                    const job = res.data.data;
                    
                    this.jobStatus = job.status;
                    
                    // Kalkulasi progress dasar
                    if (['PENDING', 'VALIDATING'].includes(this.jobStatus)) this.progressPercent = 20;
                    if (this.jobStatus === 'EXECUTING') this.progressPercent = 50;
                    if (this.jobStatus === 'WAITING_RECONNECT') this.progressPercent = 60;
                    if (['REDISCOVERING', 'VERIFYING'].includes(this.jobStatus)) this.progressPercent = 80;

                    if (this.jobStatus === 'COMPLETED') {
                        this.progressPercent = 100;
                        this.statusMessage = 'Remediasi selesai! Provisioning dilanjutkan di background.';
                        this.isFinished = true;
                        clearInterval(this.pollInterval);
                    } else if (['FAILED', 'STALE', 'CANCELLED', 'MANUAL_REVIEW', 'BLOCKED'].includes(this.jobStatus)) {
                        this.statusMessage = `Job terhenti dengan status: ${this.jobStatus}. ${job.error_message || ''}`;
                        this.isFinished = true;
                        clearInterval(this.pollInterval);
                    }

                } catch (error) {
                    console.error('Polling error', error);
                }
            }, 3000);
        }
    }
}
</script>






