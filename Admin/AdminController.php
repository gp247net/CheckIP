<?php
#App\GP247\Plugins\CheckIP\Admin\AdminController.php

namespace App\GP247\Plugins\CheckIP\Admin;

use GP247\Core\Controllers\RootAdminController;
use App\GP247\Plugins\CheckIP\AppConfig;
use App\GP247\Plugins\CheckIP\Models\CheckIPAccess;
use Illuminate\Support\Facades\Validator;

class AdminController extends RootAdminController
{
    public $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->plugin = new AppConfig;
    }
    
    /**
     * Legacy admin index (fallback only).
     *
     * Core 2.0 routes the admin screen to the Livewire component; this controller
     * action is reached only when that class is unavailable (see Route.php guard),
     * rendering a static TailAdmin stub with no jQuery/AdminLTE dependency.
     */
    public function index()
    {
        return view($this->plugin->appPath.'::Admin')
            ->with('title', gp247_language_render('Plugins/CheckIP::lang.admin.list'));
    }


    
    /**
     * Post create
     * @return [type] [description]
     */
    public function postCreate()
    {
        $data = request()->all();
        $dataOrigin = request()->all();
        $validator = Validator::make($dataOrigin, [
            'ip' => 'required|string|max:20',
            'type' => 'required',
            'description' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dataInsert = [
            'ip' => $data['ip'],
            'description' => $data['description'],
            'type' => $data['type'],
            'status' => isset($data['status']) ? (int)$data['status'] : 1,
        ];
        $dataInsert = gp247_clean($dataInsert, [], true);
        $obj = CheckIPAccess::create($dataInsert);

        return redirect()->route('admin_checkip.edit', ['id' => $obj['id']])->with('success', gp247_language_render('action.create_success'));
    }

    /**
     * Form edit (legacy route).
     *
     * Core 2.0 renders the admin screen with the Livewire component, so this legacy
     * GET route deep-links into that screen (`?edit={id}`) instead of the removed
     * AdminLTE view — keeping old bookmarks/links working.
     */
    public function edit($id)
    {
        return redirect()->route('admin_checkip.index', ['edit' => $id]);
    }

    /**
     * update
     */
    public function postEdit($id)
    {
        $obj = CheckIPAccess::find($id);
        $data = request()->all();
        $dataOrigin = request()->all();
        $validator = Validator::make($dataOrigin, [
            'ip' => 'required|string|max:20',
            'type' => 'required',
            'description' => 'required|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dataUpdate = [
            'ip' => $data['ip'],
            'description' => $data['description'],
            'type' => $data['type'],
            'status' => isset($data['status']) ? (int)$data['status'] : $obj->status,
        ];
        $dataUpdate = gp247_clean($dataUpdate, [], true);
        $obj->update($dataUpdate);

    
        return redirect()->back()->with('success', gp247_language_render('action.edit_success'));
    }

    /*
        Delete list item
        Need mothod destroy to boot deleting in model
     */
    public function deleteList()
    {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => gp247_language_render('admin.method_not_allow')]);
        } else {
            $ids = request('ids');
            $arrID = explode(',', $ids);
            CheckIPAccess::destroy($arrID);
            return response()->json(['error' => 0, 'msg' => '']);
        }
    }
}
