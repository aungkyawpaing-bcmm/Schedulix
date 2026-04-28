<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectWbsItem;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbsBuilderService
{
    public function __construct(private readonly AuditLogService $auditLogs)
    {
    }

    public function create(Project $project, array $data, ?Request $request = null): ProjectWbsItem
    {
        return DB::transaction(function () use ($project, $data, $request) {
            $parent = null;
            if (! empty($data['parent_id'])) {
                $parent = ProjectWbsItem::query()
                    ->where('project_id', $project->id)
                    ->findOrFail($data['parent_id']);
            }

            $level = $parent ? $parent->level + 1 : 1;
            if ($level > 4) {
                throw new DomainException('WBS level cannot exceed 4.');
            }

            $sortOrder = $data['sort_order'] ?? (
                ProjectWbsItem::query()
                    ->where('project_id', $project->id)
                    ->where('parent_id', $parent?->id)
                    ->max('sort_order') + 1
            );

            $item = $project->wbsItems()->create([
                ...$data,
                'parent_id' => $parent?->id,
                'level' => $level,
                'sort_order' => $sortOrder,
                'wbs_number' => 'pending',
            ]);

            $this->renumberProject($project);
            $item = $item->fresh(['parent', 'taskMaster', 'children']);

            $this->auditLogs->record('created', $item, [], $item->toArray(), $request);

            return $item;
        });
    }

    public function update(ProjectWbsItem $item, array $data, ?Request $request = null): ProjectWbsItem
    {
        return DB::transaction(function () use ($item, $data, $request) {
            $old = $item->toArray();
            $project = $item->project;
            $parent = null;

            if (! empty($data['parent_id'])) {
                $parent = ProjectWbsItem::query()
                    ->where('project_id', $project->id)
                    ->whereKeyNot($item->id)
                    ->findOrFail($data['parent_id']);

                if ($this->isDescendantOf($parent, $item)) {
                    throw new DomainException('A WBS item cannot be moved under its own subtree.');
                }
            }

            $level = $parent ? $parent->level + 1 : 1;
            if ($level > 4) {
                throw new DomainException('WBS level cannot exceed 4.');
            }

            $item->update([
                ...$data,
                'parent_id' => $parent?->id,
                'level' => $level,
            ]);

            $this->renumberProject($project);
            $item = $item->fresh(['parent', 'taskMaster', 'children']);

            $this->auditLogs->record('updated', $item, $old, $item->toArray(), $request);

            return $item;
        });
    }

    public function delete(ProjectWbsItem $item, ?Request $request = null): void
    {
        if ($item->children()->exists()) {
            throw new DomainException('Delete child items first before removing this WBS item.');
        }

        if ($item->assignment()->exists()) {
            throw new DomainException('This WBS item already has an assignment and cannot be deleted.');
        }

        $old = $item->toArray();
        $project = $item->project;

        $item->delete();
        $this->renumberProject($project);
        $this->auditLogs->record('deleted', $item, $old, [], $request);
    }

    public function renumberProject(Project $project): void
    {
        $this->assignTemporaryNumbers($project);

        $roots = ProjectWbsItem::query()
            ->where('project_id', $project->id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($roots as $index => $root) {
            $this->applyNumber($root, ($index + 1).'.0');
        }
    }

    private function applyNumber(ProjectWbsItem $item, string $number): void
    {
        $item->forceFill(['wbs_number' => $number])->saveQuietly();

        $base = str_ends_with($number, '.0') ? substr($number, 0, -2) : $number;

        $children = $item->children()->orderBy('sort_order')->orderBy('id')->get();
        foreach ($children as $index => $child) {
            $this->applyNumber($child, $base.'.'.($index + 1));
        }
    }

    private function isDescendantOf(ProjectWbsItem $candidateParent, ProjectWbsItem $item): bool
    {
        $current = $candidateParent;

        while ($current->parent_id !== null) {
            if ((int) $current->parent_id === (int) $item->id) {
                return true;
            }

            $current = ProjectWbsItem::query()->findOrFail($current->parent_id);
        }

        return false;
    }

    private function assignTemporaryNumbers(Project $project): void
    {
        ProjectWbsItem::query()
            ->where('project_id', $project->id)
            ->orderBy('id')
            ->get()
            ->each(function (ProjectWbsItem $item) {
                $item->forceFill([
                    'wbs_number' => '__tmp__'.$item->id,
                ])->saveQuietly();
            });
    }
}
