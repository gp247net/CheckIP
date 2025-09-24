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
    
    public function index()
    {
        $data = [
            'title' => gp247_language_render('Plugins/CheckIP::lang.admin.list'),
            'title_action' => '<i class="fa fa-plus" aria-hidden="true"></i> ' . gp247_language_render('Plugins/CheckIP::lang.admin.add_new_title'),
            'subTitle' => '',
            'icon' => 'fa fa-indent',
            'urlDeleteItem' => gp247_route_admin('admin_checkip.delete'),
            'removeList' => 0, // 1 - Enable function delete list item
            'buttonRefresh' => 0, // 1 - Enable button refresh
            'buttonSort' => 0, // 1 - Enable button sort
            'css' => '',
            'js' => '',
            'url_action' => gp247_route_admin('admin_checkip.create'),
        ];

        $listTh = [
            'id' => '#',
            'ip' => gp247_language_render('Plugins/CheckIP::lang.ip'),
            'description' => gp247_language_render('Plugins/CheckIP::lang.description'),
            'status' => gp247_language_render('Plugins/CheckIP::lang.status'),
            'action' => gp247_language_render('action.title'),
        ];

        $obj = new CheckIPAccess;
        $obj = $obj->orderBy('id')
                ->get()
                ->groupBy('type');
        $dataTmp = $obj;

        $dataTrAllow = [];
        $dataTrDeny = [];
        if(!empty($dataTmp['allow']) && count($dataTmp['allow'])) {
            foreach ($dataTmp['allow'] as $type => $row) {
                $dataTrAllow[] = [
                    'id' => $row['id'],
                    'ip' => $row['ip'],
                    'description' => $row['description'],
                    'status' => empty($row['status']) ? '<span class="badge bg-secondary">OFF</span>' : '<span class="badge bg-success">ON</span>',
                    'action' => '
                        <a href="' . gp247_route_admin('admin_checkip.edit', ['id' => $row['id']]) . '"><span title="' . gp247_language_render('action.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
    
                      <span  onclick="deleteItem(' . $row['id'] . ');"  title="' . gp247_language_render('action.delete') . '" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>
                      ',
                ];
            }
        }

        if(!empty($dataTmp['deny']) && count($dataTmp['deny'])) {
            foreach ($dataTmp['deny'] as $type => $row) {
                $dataTrDeny[] = [
                    'id' => $row['id'],
                    'ip' => $row['ip'],
                    'description' => $row['description'],
                    'status' => empty($row['status']) ? '<span class="badge bg-secondary">OFF</span>' : '<span class="badge bg-success">ON</span>',
                    'action' => '
                        <a href="' . gp247_route_admin('admin_checkip.edit', ['id' => $row['id']]) . '"><span title="' . gp247_language_render('action.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
    
                      <span  onclick="deleteItem(' . $row['id'] . ');"  title="' . gp247_language_render('action.delete') . '" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>
                      ',
                ];
            }
        }
        $data['ipRow'] = [];
        $data['listTh'] = $listTh;
        $data['dataTrAllow'] = $dataTrAllow;
        $data['dataTrDeny'] = $dataTrDeny;
        $data['pagination'] = '';
        $data['resultItems'] = '';

        $data['layout'] = 'index';
        return view($this->plugin->appPath.'::Admin')
            ->with($data);
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
     * Form edit
     */
    public function edit($id)
    {
        $ipRow = CheckIPAccess::find($id);
        if (!$ipRow) {
            return 'No data';
        }
        $data = [
        'title' => gp247_language_render('Plugins/CheckIP::lang.admin.list'),
        'title_action' => '<i class="fa fa-edit" aria-hidden="true"></i> ' . gp247_language_render('action.edit'),
        'subTitle' => '',
        'icon' => 'fa fa-indent',
        'urlDeleteItem' => gp247_route_admin('admin_checkip.delete'),
        'removeList' => 0, // 1 - Enable function delete list item
        'buttonRefresh' => 0, // 1 - Enable button refresh
        'buttonSort' => 0, // 1 - Enable button sort
        'css' => '',
        'js' => '',
        'url_action' => gp247_route_admin('admin_checkip.edit', ['id' => $ipRow['id']]),
        'ipRow' => $ipRow,
        ];

        $listTh = [
            'id' => 'ID',
            'ip' => gp247_language_render('Plugins/CheckIP::lang.ip'),
            'description' => gp247_language_render('Plugins/CheckIP::lang.description'),
            'action' => gp247_language_render('action.title'),
        ];
        
        $obj = new CheckIPAccess;
        $obj = $obj->orderBy('id')
                ->get()
                ->groupBy('type');
        $dataTmp = $obj;

        $dataTrAllow = [];
        $dataTrDeny = [];
        if(!empty($dataTmp['allow']) && count($dataTmp['allow'])) {
            foreach ($dataTmp['allow'] as $type => $row) {
                $dataTrAllow[] = [
                    'id' => $row['id'],
                    'ip' => $row['ip'],
                    'description' => $row['description'],
                    'action' => '
                        <a href="' . gp247_route_admin('admin_checkip.edit', ['id' => $row['id']]) . '"><span title="' . gp247_language_render('action.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
    
                      <span onclick="deleteItem(' . $row['id'] . ');"  title="' . gp247_language_render('action.delete') . '" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>
                      ',
                ];
            }
        }

        if(!empty($dataTmp['deny']) && count($dataTmp['deny'])) {
            foreach ($dataTmp['deny'] as $type => $row) {
                $dataTrDeny[] = [
                    'id' => $row['id'],
                    'ip' => $row['ip'],
                    'description' => $row['description'],
                    'action' => '
                        <a href="' . gp247_route_admin('admin_checkip.edit', ['id' => $row['id']]) . '"><span title="' . gp247_language_render('action.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
    
                      <span onclick="deleteItem(' . $row['id'] . ');"  title="' . gp247_language_render('action.delete') . '" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>
                      ',
                ];
            }
        }

        $data['listTh'] = $listTh;
        $data['dataTrAllow'] = $dataTrAllow;
        $data['dataTrDeny'] = $dataTrDeny;
        $data['layout'] = 'edit';
        return view($this->plugin->appPath.'::Admin')
        ->with($data);
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
