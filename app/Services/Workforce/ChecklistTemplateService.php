<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\ChecklistTemplateRepository;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\ChecklistTemplate;
use Src\Domain\Workforce\ChecklistItem;
use Src\Domain\Workforce\Enums\TaskType;

readonly class ChecklistTemplateService {
    public function __construct(
        private ChecklistTemplateRepository $templateRepository,
    ) {}

    public function createChecklistTemplate(
        string $name,
        TaskType $taskType,
        string $description = '',
    ): ChecklistTemplate {
        $template = ChecklistTemplate::create($name, $taskType, $description);
        $this->templateRepository->save($template);
        return $template;
    }

    public function addItemToTemplate(Uuid $templateId, ChecklistItem $item): ChecklistTemplate {
        $template = $this->templateRepository->findById($templateId);
        if (!$template) throw new \InvalidArgumentException("Template not found");
        $template->addItem($item);
        return $this->templateRepository->save($template);
    }

    public function removeItemFromTemplate(Uuid $templateId, Uuid $itemId): ChecklistTemplate {
        $template = $this->templateRepository->findById($templateId);
        if (!$template) throw new \InvalidArgumentException("Template not found");
        $template->removeItem($itemId);
        return $this->templateRepository->save($template);
    }

    public function updateTemplateName(Uuid $templateId, string $name): ChecklistTemplate {
        $template = $this->templateRepository->findById($templateId);
        if (!$template) throw new \InvalidArgumentException("Template not found");
        $template->updateName($name);
        return $this->templateRepository->save($template);
    }

    public function updateTemplateDescription(Uuid $templateId, string $description): ChecklistTemplate {
        $template = $this->templateRepository->findById($templateId);
        if (!$template) throw new \InvalidArgumentException("Template not found");
        $template->updateDescription($description);
        return $this->templateRepository->save($template);
    }

    public function activateTemplate(Uuid $templateId): ChecklistTemplate {
        $template = $this->templateRepository->findById($templateId);
        if (!$template) throw new \InvalidArgumentException("Template not found");
        $template->activate();
        return $this->templateRepository->save($template);
    }

    public function deactivateTemplate(Uuid $templateId): ChecklistTemplate {
        $template = $this->templateRepository->findById($templateId);
        if (!$template) throw new \InvalidArgumentException("Template not found");
        $template->deactivate();
        return $this->templateRepository->save($template);
    }

    public function findTemplateById(Uuid $templateId): ?ChecklistTemplate {
        return $this->templateRepository->findById($templateId);
    }

    public function findTemplatesByTaskType(TaskType $taskType): array {
        return $this->templateRepository->findByTaskType($taskType);
    }
}
