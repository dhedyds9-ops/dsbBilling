<?php

namespace App\Jobs\AI;

use App\Services\AI\ChatbotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class ChatbotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;
    public int $timeout = 60;

    public function __construct(
        public readonly string $conversationId,
        public readonly string $message,
        public readonly ?string $userId = null,
        public readonly array $metadata = []
    ) {}

    public function handle(ChatbotService $chatbotService): void
    {
        Log::info("ChatbotJob: Processing message for conversation {$this->conversationId}");

        try {
            $conversation = $chatbotService->sendMessage(
                Uuid::fromString($this->conversationId),
                $this->message,
                array_merge($this->metadata, ['source' => 'queue'])
            );

            Log::info("ChatbotJob: Message processed", [
                'conversation_id' => $this->conversationId,
                'message_count' => $conversation->getMessageCount(),
            ]);

            if ($conversation->isCompleted()) {
                Log::info("ChatbotJob: Conversation {$this->conversationId} completed", [
                    'total_messages' => $conversation->getMessageCount(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("ChatbotJob: Failed to process chatbot message", [
                'conversation_id' => $this->conversationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ChatbotJob: Chatbot job permanently failed", [
            'conversation_id' => $this->conversationId,
            'error' => $exception->getMessage()
        ]);
    }
}
