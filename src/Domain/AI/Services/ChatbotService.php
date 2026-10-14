<?php

namespace Src\Domain\AI\Services;

use Src\Domain\AI\AIConversation;
use Src\Domain\AI\AIModel;
use Src\Domain\AI\Enums\AIProvider;
use Src\Domain\AI\Enums\ConversationStatus;
use Src\Domain\AI\Repositories\AIConversationRepositoryInterface;
use Src\Domain\AI\Repositories\AIModelRepositoryInterface;
use Src\Domain\AI\Events\ConversationStarted;
use Src\Domain\AI\Events\ConversationCompleted;
use Src\Domain\AI\Events\ConversationEscalated;
use Src\Domain\AI\ValueObjects\AIMessage;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\SharedKernel\Events\EventDispatcherInterface;

class ChatbotService
{
    public function __construct(
        private readonly AIConversationRepositoryInterface $conversationRepository,
        private readonly AIModelRepositoryInterface $modelRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ExternalAIConnector $aiConnector
    ) {}

    public function createConversation(
        string $name,
        string $module,
        ?Uuid $userId = null,
        ?array $context = null
    ): AIConversation {
        $conversation = AIConversation::create(
            name: $name,
            module: $module,
            provider: AIProvider::OPENAI, // Default provider
            createdBy: $userId
        );

        if ($context) {
            $conversation->setContext($context);
        }

        $this->conversationRepository->save($conversation);

        $this->eventDispatcher->dispatch(new ConversationStarted(
            conversationId: $conversation->id,
            module: $module,
            userId: $userId
        ));

        return $conversation;
    }

    public function sendMessage(
        Uuid $conversationId,
        string $message,
        ?array $metadata = null
    ): AIConversation {
        $conversation = $this->conversationRepository->findById($conversationId);

        if (!$conversation) {
            throw new \DomainException('Conversation not found');
        }

        if ($conversation->status !== ConversationStatus::ACTIVE) {
            throw new \DomainException('Conversation is not active');
        }

        // Add user message
        $conversation->addUserMessage($message, $metadata);

        // Get AI model for this module
        $model = $this->getModelForModule($conversation->module);
        $conversation->setModel($model->id);

        // Build context for AI
        $context = $this->buildContext($conversation);

        // Call external AI
        $response = $this->aiConnector->chat([
            'model' => $model->provider->value,
            'messages' => $context,
            'temperature' => $model->getConfig()['temperature'] ?? 0.7,
        ]);

        // Add assistant response
        $conversation->addAssistantMessage(
            $response['content'],
            $response['metadata'] ?? null,
            $response['confidence'] ?? null
        );

        // Check if escalation is needed
        if ($this->shouldEscalate($response)) {
            $conversation->escalate();
            $this->eventDispatcher->dispatch(new ConversationEscalated(
                conversationId: $conversation->id
            ));
        }

        $this->conversationRepository->save($conversation);

        return $conversation;
    }

    public function completeConversation(Uuid $conversationId): void
    {
        $conversation = $this->conversationRepository->findById($conversationId);

        if (!$conversation) {
            throw new \DomainException('Conversation not found');
        }

        $conversation->complete();
        $this->conversationRepository->save($conversation);

        $this->eventDispatcher->dispatch(new ConversationCompleted(
            conversationId: $conversation->id,
            messageCount: $conversation->getMessageCount()
        ));
    }

    public function getConversation(Uuid $conversationId): ?AIConversation
    {
        return $this->conversationRepository->findById($conversationId);
    }

    public function getActiveConversations(string $module): array
    {
        return $this->conversationRepository->findActiveByModule($module);
    }

    public function getConversationHistory(Uuid $conversationId, int $limit = 50): array
    {
        $conversation = $this->conversationRepository->findById($conversationId);

        if (!$conversation) {
            return [];
        }

        return array_slice($conversation->getMessages(), -$limit);
    }

    private function getModelForModule(string $module): AIModel
    {
        $model = $this->modelRepository->findActiveByTypeAndModule(
            \Src\Domain\AI\Enums\AIModelType::CHATBOT,
            $module
        );

        if (!$model) {
            // Fallback to default model
            $model = $this->modelRepository->findDefaultChatbot();
        }

        if (!$model) {
            throw new \DomainException("No chatbot model configured for module: {$module}");
        }

        return $model;
    }

    private function buildContext(AIConversation $conversation): array
    {
        $messages = [];

        // Add system prompt based on module
        $systemPrompt = $this->getSystemPrompt($conversation->module);
        $messages[] = AIMessage::system($systemPrompt)->toArray();

        // Add conversation context
        $context = $conversation->getContext();
        if (!empty($context)) {
            $contextPrompt = "Context: " . json_encode($context);
            $messages[] = AIMessage::system($contextPrompt)->toArray();
        }

        // Add previous messages (limited)
        $previousMessages = array_slice($conversation->getMessages(), -10);
        foreach ($previousMessages as $msg) {
            $messages[] = $msg;
        }

        return $messages;
    }

    private function getSystemPrompt(string $module): string
    {
        $prompts = [
            'NOC' => "You are an AI assistant for the Network Operations Center (NOC). You help with network monitoring, incident analysis, and troubleshooting. Provide clear, actionable insights based on network data and alerts.",
            'CRM' => "You are an AI assistant for Customer Relationship Management (CRM). You help with customer inquiries, service recommendations, and support ticket management. Be helpful, empathetic, and solution-oriented.",
            'Billing' => "You are an AI assistant for the Billing department. You help with invoice inquiries, payment processing, and subscription management. Provide accurate billing information and clear explanations.",
            'CustomerService' => "You are an AI assistant for Customer Service. You help customers with their internet service issues, account management, and general inquiries. Be friendly, patient, and helpful.",
            'Finance' => "You are an AI assistant for the Finance department. You help with financial analysis, revenue forecasting, and budget planning. Provide data-driven insights and professional recommendations.",
        ];

        return $prompts[$module] ?? "You are a helpful AI assistant for an ISP management system. Provide accurate, helpful responses to user inquiries.";
    }

    private function shouldEscalate(array $response): bool
    {
        // Check if response contains escalation triggers
        $escalationTriggers = [
            'supervisor',
            'manager',
            'human agent',
            'escalate',
            'specialist',
        ];

        $content = strtolower($response['content'] ?? '');

        foreach ($escalationTriggers as $trigger) {
            if (str_contains($content, $trigger)) {
                return true;
            }
        }

        // Check confidence
        if (($response['confidence'] ?? 1.0) < 0.3) {
            return true;
        }

        return false;
    }
}

class ExternalAIConnector
{
    public function chat(array $params): array
    {
        // Placeholder for actual AI API calls
        // In real implementation, this would call OpenAI, Anthropic, etc.

        return [
            'content' => 'This is a simulated AI response. In production, this would call the actual AI API.',
            'confidence' => 0.95,
            'metadata' => [
                'model' => $params['model'] ?? 'unknown',
                'tokens_used' => 100,
            ],
        ];
    }

    public function classify(string $text, string $model): array
    {
        // Placeholder for text classification
        return [
            'class' => 'support',
            'confidence' => 0.9,
        ];
    }

    public function analyzeSentiment(string $text): array
    {
        // Placeholder for sentiment analysis
        return [
            'sentiment' => 'positive',
            'confidence' => 0.85,
        ];
    }
}
