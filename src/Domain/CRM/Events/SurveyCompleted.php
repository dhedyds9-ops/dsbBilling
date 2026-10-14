<?php

namespace Src\Domain\CRM\Events;

use Src\Domain\SharedKernel\Events\DomainEvent;

class SurveyCompleted extends DomainEvent
{
    public function __construct(
        public readonly string $surveyId,
        public readonly string $prospectId,
        public readonly string $userId,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'survey.completed';
    }
}
