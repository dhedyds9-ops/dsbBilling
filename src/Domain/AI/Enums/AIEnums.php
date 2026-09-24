<?php

namespace Src\Domain\AI\Enums;

enum AIModelType: string
{
    // NLP Models
    case CHATBOT = 'chatbot';
    case TEXT_CLASSIFICATION = 'text_classification';
    case SENTIMENT_ANALYSIS = 'sentiment_analysis';
    case NER = 'named_entity_recognition'; // Named Entity Recognition

    // Prediction Models
    case CHURN_PREDICTION = 'churn_prediction';
    case REVENUE_FORECAST = 'revenue_forecast';
    case PAYMENT_PREDICTION = 'payment_prediction';
    case DEMAND_FORECAST = 'demand_forecast';

    // Anomaly Detection
    case NETWORK_ANOMALY = 'network_anomaly';
    case FRAUD_DETECTION = 'fraud_detection';
    case CAPACITY_ANOMALY = 'capacity_anomaly';

    // Recommendation
    case CUSTOMER_RECOMMENDATION = 'customer_recommendation';
    case CAPACITY_RECOMMENDATION = 'capacity_recommendation';

    // Image/Video
    case IMAGE_CLASSIFICATION = 'image_classification';

    public function getLabel(): string
    {
        return match($this) {
            self::CHATBOT => 'Chatbot / Assistant',
            self::TEXT_CLASSIFICATION => 'Text Classification',
            self::SENTIMENT_ANALYSIS => 'Sentiment Analysis',
            self::NER => 'Named Entity Recognition',
            self::CHURN_PREDICTION => 'Churn Prediction',
            self::REVENUE_FORECAST => 'Revenue Forecast',
            self::PAYMENT_PREDICTION => 'Payment Prediction',
            self::DEMAND_FORECAST => 'Demand Forecast',
            self::NETWORK_ANOMALY => 'Network Anomaly Detection',
            self::FRAUD_DETECTION => 'Fraud Detection',
            self::CAPACITY_ANOMALY => 'Capacity Anomaly Detection',
            self::CUSTOMER_RECOMMENDATION => 'Customer Recommendation',
            self::CAPACITY_RECOMMENDATION => 'Capacity Recommendation',
            self::IMAGE_CLASSIFICATION => 'Image Classification',
        };
    }

    public function getCategory(): string
    {
        return match($this) {
            self::CHATBOT, self::TEXT_CLASSIFICATION, self::SENTIMENT_ANALYSIS, self::NER => 'NLP',
            self::CHURN_PREDICTION, self::REVENUE_FORECAST, self::PAYMENT_PREDICTION, self::DEMAND_FORECAST => 'Prediction',
            self::NETWORK_ANOMALY, self::FRAUD_DETECTION, self::CAPACITY_ANOMALY => 'Anomaly Detection',
            self::CUSTOMER_RECOMMENDATION, self::CAPACITY_RECOMMENDATION => 'Recommendation',
            self::IMAGE_CLASSIFICATION => 'Computer Vision',
        };
    }

    public function requiresTraining(): bool
    {
        return match($self) {
            self::CHURN_PREDICTION, self::REVENUE_FORECAST, self::PAYMENT_PREDICTION,
            self::NETWORK_ANOMALY, self::FRAUD_DETECTION => true,
            default => false,
        };
    }
}

enum AIProvider: string
{
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';
    case GOOGLE = 'google';
    case HUGGINGFACE = 'huggingface';
    case LOCAL = 'local'; // Local model or self-hosted
    case CUSTOM = 'custom'; // Custom trained model

    public function getLabel(): string
    {
        return match($this) {
            self::OPENAI => 'OpenAI',
            self::ANTHROPIC => 'Anthropic Claude',
            self::GOOGLE => 'Google AI',
            self::HUGGINGFACE => 'Hugging Face',
            self::LOCAL => 'Local / Self-hosted',
            self::CUSTOM => 'Custom Model',
        };
    }
}

enum ConversationStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case ABANDONED = 'abandoned';
    case ESCALATED = 'escalated';
}

enum PredictionStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}

enum AnomalySeverity: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function getLabel(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
        };
    }

    public function getScoreThreshold(): float
    {
        return match($this) {
            self::LOW => 0.3,
            self::MEDIUM => 0.5,
            self::HIGH => 0.7,
            self::CRITICAL => 0.9,
        };
    }
}

enum ChurnRiskLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case VERY_HIGH = 'very_high';

    public function getLabel(): string
    {
        return match($this) {
            self::LOW => 'Low Risk',
            self::MEDIUM => 'Medium Risk',
            self::HIGH => 'High Risk',
            self::VERY_HIGH => 'Very High Risk',
        };
    }

    public static function fromScore(float $score): self
    {
        if ($score >= 0.8) {
            return self::VERY_HIGH;
        } elseif ($score >= 0.6) {
            return self::HIGH;
        } elseif ($score >= 0.4) {
            return self::MEDIUM;
        }
        return self::LOW;
    }
}
