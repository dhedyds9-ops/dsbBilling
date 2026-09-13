<?php

namespace App\Services\Support;

use App\Models\CRM\Customer;
use App\Models\Support\Ticket;
use App\Services\Auth\UserQueryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Domain\Support\Events\TicketAssignedEvent;
use Src\Domain\Support\Events\TicketClosedEvent;
use Src\Domain\Support\Events\TicketCreatedEvent;
use Src\Domain\Support\Events\TicketResolvedEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketService
{
    public function list(array $filters, string $search, string $sortField, string $sortDirection, int $perPage)
    {
        $query = Ticket::with(['customer', 'assignedTo']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['due_from'])) {
            $query->whereDate('due_date', '>=', $filters['due_from']);
        }
        if (!empty($filters['due_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_to']);
        }
        if (!empty($filters['ignored'])) {
            // Tiket terabaikan (contoh: status open/in_progress dan sudah lebih dari 24 jam tidak di-update)
            $query->whereIn('status', ['open', 'in_progress'])
                  ->where('updated_at', '<=', now()->subHours(24));
        }

        if ($search) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('uuid', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('customer', fn($sq) => $sq->where('name', 'like', $like)->orWhere('email', 'like', $like))
                    ->orWhereHas('assignedTo', fn($sq) => $sq->where('name', 'like', $like));
            });
        }

        $allowed = ['id', 'title', 'status', 'priority', 'due_date', 'created_at', 'updated_at'];
        $field = in_array($sortField, $allowed) ? $sortField : 'created_at';
        $query->orderBy($field, $sortDirection);

        return $perPage > 0 ? $query->paginate($perPage) : $query->get();
    }

    public function summary(): array
    {
        $dailyChart = Ticket::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return [
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'pending_customer' => Ticket::where('status', 'pending_customer')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'daily_chart' => $dailyChart,
        ];
    }

    public function kanbanByStatus(): array
    {
        $statuses = ['open', 'in_progress', 'pending_customer', 'resolved', 'closed'];
        $out = [];
        foreach ($statuses as $s) {
            $out[$s] = Ticket::with(['customer', 'assignedTo'])
                ->where('status', $s)
                ->orderByDesc('priority')
                ->orderBy('created_at')
                ->limit(30)
                ->get();
        }
        return $out;
    }

    public function create(array $data, int $userId): Ticket
    {
        return DB::transaction(function () use ($data, $userId) {
            $ticket = Ticket::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $data['customer_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'category' => $data['category'] ?? 'other',
                'priority' => $data['priority'] ?? 'medium',
                'status' => $data['status'] ?? 'open',
                'assigned_to' => $data['assigned_to'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            Event::dispatch(TicketCreatedEvent::create(
                (string) $ticket->id,
                $ticket->uuid,
                (string) $ticket->customer_id,
                $ticket->title,
                $ticket->priority,
                (string) $userId,
            ));

            return $ticket;
        });
    }

    public function update(Ticket $ticket, array $data, int $userId): Ticket
    {
        $ticket->update([
            'title' => $data['title'] ?? $ticket->title,
            'description' => $data['description'] ?? $ticket->description,
            'category' => $data['category'] ?? $ticket->category,
            'priority' => $data['priority'] ?? $ticket->priority,
            'status' => $data['status'] ?? $ticket->status,
            'due_date' => $data['due_date'] ?? $ticket->due_date,
        ]);
        return $ticket;
    }

    public function delete(Ticket $ticket): void
    {
        $ticket->delete();
    }

    public function assign(int $ticketId, int $assigneeId, int $userId): Ticket
    {
        return DB::transaction(function () use ($ticketId, $assigneeId, $userId) {
            $ticket = Ticket::findOrFail($ticketId);
            $ticket->update([
                'assigned_to' => $assigneeId,
                'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
            ]);
            Event::dispatch(TicketAssignedEvent::create(
                (string) $ticketId,
                (string) $assigneeId,
                (string) $userId,
            ));
            Log::info('Ticket assigned', ['ticket_id' => $ticketId, 'assignee' => $assigneeId, 'by' => $userId]);
            return $ticket;
        });
    }

    public function bulkAssign(array $ids, int $assigneeId, int $userId): int
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->assign((int) $id, $assigneeId, $userId);
                $count++;
            } catch (\Throwable $e) {
                Log::warning('Bulk assign skip', ['id' => $id, 'err' => $e->getMessage()]);
            }
        }
        return $count;
    }

    public function resolve(int $ticketId, ?string $note, int $userId): Ticket
    {
        return DB::transaction(function () use ($ticketId, $note, $userId) {
            $ticket = Ticket::findOrFail($ticketId);
            $ticket->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);
            Event::dispatch(TicketResolvedEvent::create(
                (string) $ticketId,
                (string) $userId,
                $note,
            ));
            return $ticket;
        });
    }

    public function bulkResolve(array $ids, ?string $note, int $userId): int
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->resolve((int) $id, $note, $userId);
                $count++;
            } catch (\Throwable $e) {
                Log::warning('Bulk resolve skip', ['id' => $id, 'err' => $e->getMessage()]);
            }
        }
        return $count;
    }

    public function close(int $ticketId, int $userId): Ticket
    {
        return DB::transaction(function () use ($ticketId, $userId) {
            $ticket = Ticket::findOrFail($ticketId);
            $ticket->update(['status' => 'closed']);
            Event::dispatch(TicketClosedEvent::create(
                (string) $ticketId,
                (string) $userId,
            ));
            return $ticket;
        });
    }

    public function bulkClose(array $ids, int $userId): int
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->close((int) $id, $userId);
                $count++;
            } catch (\Throwable $e) {
                Log::warning('Bulk close skip', ['id' => $id, 'err' => $e->getMessage()]);
            }
        }
        return $count;
    }

    public function bulkDelete(array $ids): int
    {
        return Ticket::whereIn('id', $ids)->delete();
    }

    public function exportCsv($rows): StreamedResponse
    {
        $filename = 'Tickets_' . now()->format('YmdHis') . '.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
        return response()->stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Ticket ID', 'UUID', 'Title', 'Customer', 'Category', 'Priority', 'Status', 'Assignee', 'Due Date', 'Last Update']);
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->uuid,
                    $r->title,
                    $r->customer?->name ?? '-',
                    $r->category,
                    $r->priority,
                    $r->status,
                    $r->assignedTo?->name ?? '-',
                    $r->due_date,
                    $r->updated_at,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Dapatkan daftar assignee untuk ticket.
     * Assignee adalah administrator dan manager yang aktif.
     * DILARANG: supervisor/operator sebagai role — gunakan permission ticket.assign.
     */
    public function getAssigneeOptions(): array
    {
        return app(UserQueryService::class)->getEligibleAssigneesForDropdown();
    }

    /**
     * Dapatkan daftar customer untuk ticket dari model CRM\Customer.
     * DILARANG: query User dengan role='customer' — customer entity ada di members table.
     */
    public function getCustomerOptions(): array
    {
        return Customer::where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function timeline(int $ticketId): array
    {
        $ticket = Ticket::with(['customer', 'assignedTo'])->findOrFail($ticketId);
        $events = [
            ['time' => $ticket->created_at, 'type' => 'create', 'title' => 'Tiket dibuat', 'by' => $ticket->customer?->name ?? 'Sistem', 'detail' => $ticket->description],
        ];
        if ($ticket->assignedTo) {
            $events[] = ['time' => $ticket->created_at->addMinutes(30), 'type' => 'assign', 'title' => 'Ditetapkan petugas', 'by' => 'Admin', 'detail' => 'Petugas: ' . $ticket->assignedTo->name];
        }
        if ($ticket->resolved_at) {
            $events[] = ['time' => $ticket->resolved_at, 'type' => 'resolve', 'title' => 'Tiket diselesaikan', 'by' => $ticket->assignedTo?->name ?? 'Petugas', 'detail' => 'Masalah telah diperbaiki'];
        }
        if ($ticket->status === 'closed') {
            $events[] = ['time' => $ticket->updated_at, 'type' => 'close', 'title' => 'Tiket ditutup', 'by' => 'Admin', 'detail' => 'Tiket selesai dan ditutup'];
        }
        usort($events, fn($a, $b) => $a['time'] <=> $b['time']);
        return ['ticket' => $ticket, 'events' => $events];
    }
}
