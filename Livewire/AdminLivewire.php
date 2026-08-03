<?php
#App\GP247\Plugins\CheckIP\Livewire\AdminLivewire.php

namespace App\GP247\Plugins\CheckIP\Livewire;

use App\GP247\Plugins\CheckIP\Models\CheckIPAccess;
use GP247\Core\AdminShell\Infrastructure\GP247AdminComponent;

/**
 * CheckIP admin screen as a full-page Livewire component (Core 2.0 / TailAdmin).
 *
 * Replaces the legacy jQuery/pjax + SweetAlert AdminController flow with a
 * reactive add/edit/delete form driven by Livewire, extending GP247AdminComponent
 * so the screen inherits Layer-2 RBAC (read authorization on mount, per-action
 * checks) and the shared admin layout — exactly like core/front/shop screens.
 * The IP allow/deny data layer (CheckIPAccess, middleware) is unchanged.
 *
 * @aidlc-unit plugin-checkip
 * @aidlc-story US-checkip-admin
 */
class AdminLivewire extends GP247AdminComponent
{
    /**
     * Permission slug gating this component; null infers it from the registered
     * component name via the PermissionResolver convention (same as core screens).
     *
     * @var string|null
     */
    protected ?string $permission = null;

    /**
     * Id of the row currently loaded into the form, or null when adding a new IP.
     *
     * @var int|null
     */
    public ?int $editingId = null;

    /**
     * Human-readable description of the IP rule.
     *
     * @var string
     */
    public string $description = '';

    /**
     * The IP value ("*" allowed for all IPs).
     *
     * @var string
     */
    public string $ip = '';

    /**
     * Rule type: "allow" or "deny".
     *
     * @var string
     */
    public string $type = 'allow';

    /**
     * Rule status: 1 = ON (enforced), 0 = OFF.
     *
     * @var int
     */
    public int $status = 1;

    /**
     * Validation rules mirroring the legacy AdminController create/edit rules.
     *
     * @return array<string, string> Livewire validation rule set.
     */
    protected function rules(): array
    {
        return [
            'ip' => 'required|string|max:20',
            'description' => 'required|string|max:255',
            'type' => 'required|in:allow,deny',
            'status' => 'required|in:0,1',
        ];
    }

    /**
     * Livewire lifecycle hook; enforces read authorization then optionally loads a
     * row into the form when the screen is opened with an `?edit={id}` deep link
     * (preserves the legacy `admin_checkip.edit` route as a backward-compatible entry).
     *
     * @return void
     */
    public function mount(): void
    {
        parent::mount();

        $editId = request()->query('edit');
        if (!empty($editId)) {
            $this->edit((int) $editId);
        }
    }

    /**
     * Load an existing IP rule into the form for editing.
     *
     * @param int $id Primary key of the CheckIPAccess row to edit.
     * @return void
     */
    public function edit(int $id): void
    {
        $row = CheckIPAccess::find($id);
        if (!$row) {
            $this->notify('error', gp247_language_render('admin.data_not_exist'));
            return;
        }

        $this->editingId = (int) $row->id;
        $this->description = (string) $row->description;
        $this->ip = (string) $row->ip;
        $this->type = (string) $row->type;
        $this->status = (int) $row->status;
        $this->resetErrorBag();
    }

    /**
     * Validate and persist the form as a create (no editingId) or update.
     *
     * @return void
     */
    public function save(): void
    {
        $this->authorizeAction('save');
        $data = $this->validate();

        // WHY: reuse the legacy sanitiser so stored values stay consistent with
        // data written by the old controller flow.
        $clean = gp247_clean([
            'ip' => $this->ip,
            'description' => $this->description,
        ], [], true);

        $payload = [
            'ip' => $clean['ip'],
            'description' => $clean['description'],
            'type' => $this->type,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            $row = CheckIPAccess::find($this->editingId);
            if (!$row) {
                $this->notify('error', gp247_language_render('admin.data_not_exist'));
                return;
            }
            $row->update($payload);
            $this->notify('success', gp247_language_render('action.edit_success'));
        } else {
            CheckIPAccess::create($payload);
            $this->notify('success', gp247_language_render('action.create_success'));
        }

        $this->resetForm();
    }

    /**
     * Delete an IP rule and clear the form if it was being edited.
     *
     * @param int $id Primary key of the CheckIPAccess row to delete.
     * @return void
     */
    public function delete(int $id): void
    {
        $this->authorizeAction('delete');
        CheckIPAccess::destroy($id);

        if ($this->editingId === $id) {
            $this->resetForm();
        }
        $this->notify('success', gp247_language_render('action.delete_confirm_deleted_msg'));
    }

    /**
     * Reset the form back to "add new" defaults.
     *
     * @return void
     */
    public function resetForm(): void
    {
        $this->editingId = null;
        $this->description = '';
        $this->ip = '';
        $this->type = 'allow';
        $this->status = 1;
        $this->resetErrorBag();
    }

    /**
     * Render the plugin admin screen inside the shared TailAdmin layout.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        $grouped = CheckIPAccess::orderBy('id')->get()->groupBy('type');

        return view('Plugins/CheckIP::livewire', [
            'allowList' => $grouped['allow'] ?? collect(),
            'denyList' => $grouped['deny'] ?? collect(),
        ])->layout('gp247-admin::layouts.admin', [
            'title' => trans('Plugins/CheckIP::lang.title'),
        ]);
    }
}
