<?php

namespace App\Services\Workforce;

use App\Repositories\Workforce\WorkAssignmentRepository;
use Illuminate\Support\Facades\Event;
use Src\Domain\SharedKernel\ValueObjects\Uuid;
use Src\Domain\Workforce\Enums\AssignmentStatus;
use Src\Domain\Workforce\Events\TechnicianAcceptedEvent;
use Src\Domain\Workforce\Events\TechnicianAssignedEvent;
use Src\Domain\Workforce\Events\WorkCompletedEvent;
use Src\Domain\Workforce\Events\WorkStartedEvent;
use Src\Domain\Workforce\ValueObjects\AssignmentPriority;
use Src\Domain\Workforce\WorkAssignment;

readonly class AssignmentService {
    public function __construct(
        private WorkAssignmentRepository $assignmentRepository,
    ) {}

    public function createAssignment(
        Uuid $workOrderId,
        Uuid $technicianId,
        ?Uuid $dispatcherId = null,
        ?AssignmentPriority $priority = null,
        ?string $notes = null,
    ): WorkAssignment {
        $assignment = WorkAssignment::create(
            $workOrderId,
            $technicianId,
            $dispatcherId,
            $priority,
            $notes,
        );
        $this->assignmentRepository->save($assignment);
        
        $event = TechnicianAssignedEvent::create(
            $assignment->id,
            $workOrderId,
            $technicianId,
            $dispatcherId,
        );
        Event::dispatch($event);
        
        return $assignment;
    }

    public function acceptAssignment(Uuid $assignmentId): WorkAssignment {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) throw new \InvalidArgumentException("Assignment not found");
        
        $assignment->accept();
        $this->assignmentRepository->save($assignment);
        
        $event = TechnicianAcceptedEvent::create($assignment->id, $assignment->workOrderId, $assignment->technicianId);
        Event::dispatch($event);
        
        return $assignment;
    }

    public function rejectAssignment(Uuid $assignmentId): WorkAssignment {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) throw new \InvalidArgumentException("Assignment not found");
        
        $assignment->reject();
        $this->assignmentRepository->save($assignment);
        return $assignment;
    }

    public function completeAssignment(Uuid $assignmentId): WorkAssignment {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) throw new \InvalidArgumentException("Assignment not found");
        
        $assignment->complete();
        $this->assignmentRepository->save($assignment);
        
        $event = WorkCompletedEvent::create($assignment->workOrderId, $assignment->technicianId);
        Event::dispatch($event);
        
        return $assignment;
    }
}
