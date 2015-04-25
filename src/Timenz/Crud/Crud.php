<?php

namespace Timenz\Crud;

use DB;
use Input;
use Session;
use Validator;
use Controller;
use Route;
use View;
use Redirect;
use Response;
use Log;

class Crud extends Controller{

    private $schema;
    private $dataType = array();
    private $action = null;
    private $uri = null;
    private $actions = array();
    private $actionLists = null;
    private $table = '';
    private $columns = array();
    private $allColumns = array();
    private $customColumns = array();
    private $customValues = array();
    private $fields = array();
    private $createFields = array();
    private $editFields = array();
    private $readFields = array();
    private $validateRules = array();
    private $validateErrors = array();
    private $lists = array();
//    private $crudField;
    private $perPage = 20;
    private $ids = 0;
    private $response = array();
    private $row = array();
    private $changeType = array();
    private $setJoin = array();
    private $status = true;
    private $pagingLinks;
    private $postCreateData = array();
    private $postUpdateData = array();
    private $masterData;
    private $arrWhere = array();
    private $masterBlade = '';
    private $externalLink = array();
    private $orderBy;
    private $orderByCustom;
    private $responseType;
    private $tbCount = 1;
    private $indexSession = array();
    private $filter;
    private $exportFilter;
    private $isLoadMceLibs = false;

    protected $allowCreate = true;
    protected $allowRead = true;
    protected $allowDelete = true;
    private $allowMassDelete = false;
    protected $allowEdit = true;
    protected $allowMultipleSelect = false;
    protected $allowExport = false;
    protected $exportMaxLimit = 1000;
    protected $allowSearch = true;
    protected $allowOrder = true;
    protected $listExportText = 'export';
    protected $listSearchText = 'search';
    protected $listCreateText = 'tambah';
    protected $listReadText = 'detail';
    protected $listEditText = 'ubah';
    protected $listMassDeleteText = 'hapus terpilih';
    protected $listDeleteText = 'hapus';
    protected $createBtnText = 'tambah';
    protected $editBtnText = 'ubah';
    protected $backBtnText = 'kembali';
    protected $columnDisplay = array();
    protected $title = 'Crud';
    protected $subTitleIndex = 'List';
    protected $subTitleCreate = 'Tambah';
    protected $subTitleRead = 'Detail';
    protected $subTitleEdit = 'Ubah';
    protected $errorText = '';
    protected $log;


    /**
     * @param $table
     */
    protected function init($table){
        $this->table = $table;
        $postData = Input::all();
        $this->postCreateData = $postData;
        $this->postUpdateData = $postData;

    }

    /**
     * @return bool
     */
    private function execute(){

        if($this->action == null){
            return false;
        }


        if($this->masterBlade == ''){
            return false;
        }


        $this->setDefaultColumn();

        switch($this->action){
            case 'index':
                $status = $this->actionIndex();
                if(!$status){
                    return false;
                }
                $this->applyJoinNN();
                break;

            case 'create':
                $this->applyJoinNN();
                $this->actionCreate();
                break;

            case 'save':
                $this->applyJoinNN();
                $this->actionSave();
                break;

            case 'edit':
                $this->applyJoinNN();
                $this->getOneRow();
                $this->actionEdit();
                break;

            case 'update':
                $this->applyJoinNN();
                $this->getOneRow();
                $this->actionUpdate();
                break;

            case 'read':
                $this->applyJoinNN();
                $this->getOneRow();
                $this->actionRead();
                break;

            case 'delete':
                $this->applyJoinNN();
                $this->getOneRow();
                $this->actionDelete();
                break;

            case 'prepare_export':
                $this->actionPrepareExport();
                break;

            case 'export':
                $this->actionExport();
                break;


            default:
                return false;
                break;

        }


        if($this->allowMassDelete){
            $this->allowDelete = false;
        }



        return true;

    }

    /**
     *
     */
    private function setDefaultColumn(){
        $dbName = DB::getDatabaseName();
        $schema = DB::select("select column_name, data_type, character_maximum_length, numeric_precision, numeric_scale, column_type
          from information_schema.columns where table_schema = ? and table_name = ?",
            array($dbName, $this->table));

        $columns = array();
        $allColumns = array();

        $this->schema = $schema;

        $this->populateField();

        $dataType = $this->dataType;

        foreach($dataType as $item){
            $allColumns[] = $item['column_name'];
            if($item['column_name'] == 'id'){
                continue;
            }
            $columns[] = $item['column_name'];
        }

        $this->allColumns = $allColumns;


        if(count($this->columns) < 1){
            $this->columns = $columns;
        }
//        else{
//            foreach($this->columns as $item){
//                if(!isset($dataType[$item])){
//
//                }
//            }
//        }
    }

    /**
     * @param $field
     * @param $newType
     * @param array $option
     * @param bool $renewOnUpdate
     */
    protected function changeType($field, $newType, $option = array(), $renewOnUpdate = false){
        $changeType = $this->changeType;

        switch($newType){
            case 'money':
                $changeType[$field] = array(
                    'new_type' => $newType
                );
                break;

            case 'image':
                if(is_array($option) or $option == ''){
                    break;
                }

                if(!is_writable(public_path($option))){
                    Log::warning('apply new type for '.$field.' failed . Directory '.$option.' is not exist and or not writeable.');
                    break;
                }
                $changeType[$field] = array(
                    'new_type' => $newType,
                    'target_dir' => $option
                );
                break;

            case 'textarea':
                $changeType[$field] = array(
                    'new_type' => $newType
                );
                break;

            case 'richarea':
                $changeType[$field] = array(
                    'new_type' => $newType
                );
                $this->isLoadMceLibs = true;
                break;

            case 'select':
                if(!is_array($option)){
                    break;
                }
                $changeType[$field] = array(
                    'new_type' => $newType,
                    'options' => $option
                );
                break;

            case 'enum':
                if(!is_array($option)){
                    break;
                }
                $changeType[$field] = array(
                    'new_type' => $newType,
                    'options' => $option
                );
                break;

            case 'hidden':
                $value = '';
                if(!is_array($option)){
                    $value = $option;
                }

                $changeType[$field] = array(
                    'new_type' => $newType,
                    'default_value' => $value,
                    'renew_on_update' => $renewOnUpdate
                );
                break;

            case 'text':
                $value = '';
                if(!is_array($option)){
                    $value = $option;
                }

                $changeType[$field] = array(
                    'new_type' => $newType,
                    'default_value' => $value,
                    'renew_on_update' => $renewOnUpdate
                );
                break;

        }

        $this->changeType = $changeType;
    }

    /**
     * @param $columnName
     * @param $dataColumn
     * @return mixed
     */
    private function applyNewType($columnName, $dataColumn){
        $changeType = $this->changeType;

        if(!array_key_exists($columnName, $changeType) ){
            return $dataColumn;
        }

        $dataColumn['input_type'] = $changeType[$columnName]['new_type'];

        switch($changeType[$columnName]['new_type']){
            case 'join':
                $dataColumn['related_field'] = $changeType[$columnName]['related_field'];
                $dataColumn['options'] = $changeType[$columnName]['options'];
                break;
            case 'enum':
                $dataColumn['options'] = $changeType[$columnName]['options'];
                break;
            case 'select':
                $dataColumn['options'] = $changeType[$columnName]['options'];
                break;
            case 'text':
                $dataColumn['default_value'] = $changeType[$columnName]['default_value'];
                $dataColumn['renew_on_update'] = $changeType[$columnName]['renew_on_update'];
                break;
            case 'image':
                $dataColumn['target_dir'] = $changeType[$columnName]['target_dir'];
                break;
            case 'hidden':
                $dataColumn['default_value'] = $changeType[$columnName]['default_value'];
                $dataColumn['renew_on_update'] = $changeType[$columnName]['renew_on_update'];
                break;
        }

        return $dataColumn;
    }

    /**
     * @param $field
     * @param $joinTable
     * @param $joinField
     * @param array $arrayWhere
     */
    protected function setJoin($field, $joinTable, $joinField, $arrayWhere = array()){

        $this->setJoin[$field] = array($joinTable, $joinField, $arrayWhere, 't'.$this->tbCount);
        $this->tbCount++;

        $newType = array(
            'new_type' => 'join',
            'related_field' => $joinField,
            'options' => array()
        );

        if($this->action == 'create' or $this->action == 'edit'){
            $newType['options'] = DB::table($joinTable)->select(array('id', $joinField))->limit(1000)->get();
        }

        $this->changeType[$field] = $newType;
    }

    private $joinNN;
    private $joinNNColumn;
    private $joinNNOption;
    private $insertNN;
    private $delNN;

    protected function setJoinNN($columnName, $joinField, $joinTable, $relationTable, $fieldRel, $joinFieldRel){

        $this->joinNN[] = array(
            'column_name' => $columnName,
            'join_field' => $joinField,
            'join_table' => $joinTable,
            'relation_table' => $relationTable,
            'field_rel' => $fieldRel,
            'join_field_rel' => $joinFieldRel
        );


    }


    private function applyJoinNN(){


        if(count($this->joinNN) < 1){
            return false;
        }


        $action = $this->action;
        $joinNNColumn = array();
        $joinNNOption = array();
        $insertNN = array();
        $delNN = array();

        foreach($this->joinNN as $join){
            if($action == 'index'){
                foreach($this->lists as $item){
                    if(!isset($item->id)){continue;}
                    $get = DB::table($join['relation_table'])
                        ->selectRaw('group_concat(`'.$join['join_field'].'`) as '.$join['column_name'])
                        ->rightJoin($join['join_table'], $join['join_table'].'.id', '=', $join['join_field_rel'])
                        ->where($join['relation_table'].'.'.$join['field_rel'], '=', $item->id)
                        ->groupBy($join['relation_table'].'.'.$join['field_rel'])
                        ->first();

                    if($get != null){
                        $joinNNColumn[$item->id][$join['column_name']] = $get->{$join['column_name']};
                    }
                }
            }

            if($action == 'edit' or $action == 'read'){

                $get = DB::table($join['relation_table'])
                    ->selectRaw('group_concat(`'.$join['join_field'].'`) as '.$join['column_name'])
                    ->rightJoin($join['join_table'], $join['join_table'].'.id', '=', $join['join_field_rel'])
                    ->where($join['relation_table'].'.'.$join['field_rel'], '=', $this->ids)
                    ->groupBy($join['relation_table'].'.'.$join['field_rel'])
                    ->first();
                if($get != null){
                    $joinNNColumn[$join['column_name']] = $get->{$join['column_name']};
                }
            }

            if($action == 'edit' or $action == 'create'){

                $get = DB::table($join['join_table'])
                    ->select('id', $join['join_field'].' as option')
                    ->limit(1000)
                    ->get();
                if($get != null){
                    $joinNNOption[$join['column_name']] = $get;
                }
            }

            if($action == 'save' or $action == 'update'){

                $delNN[$join['column_name']] = array(
                    'relation_table' => $join['relation_table'],
                    'field_rel' => $join['field_rel'],
                    'join_field_rel' => $join['join_field_rel']
                );

                if(!Input::has($join['column_name'])){
                    continue;
                }
                $insertNN[$join['column_name']] = array(
                    'relation_table' => $join['relation_table'],
                    'field_rel' => $join['field_rel'],
                    'join_field_rel' => $join['join_field_rel'],
                    'values' => Input::get($join['column_name'])
                );
            }
        }



        $this->joinNNColumn = $joinNNColumn;
        $this->joinNNOption = $joinNNOption;
        $this->insertNN = $insertNN;
        $this->delNN = $delNN;


    }

    /**
     *
     */
    private function populateField(){
        $schema = $this->schema;
        $columnDisplay = $this->columnDisplay;
        $columns = $this->columns;


        $dataType = array();
        foreach($schema as $item){
            $display = ucwords(str_replace('_', ' ', $item->column_name));
            $input_type = 'text';



            $dataColumn = array(
                'column_name' => $item->column_name,
                'column_text' => $display,
                'max_length' => (int)$item->character_maximum_length,
                'dec_length' => (int)$item->numeric_scale,
                'input_type' => $input_type,
                'related_field' => '',
                'options' => array()
            );



            if(array_key_exists($item->column_name, $columnDisplay)){
                $dataColumn['column_text'] = $columnDisplay[$item->column_name];
            }



            switch($item->data_type){
                case 'int':
                    $dataColumn['max_length'] = (int)$item->numeric_precision;
                    $dataColumn['input_type'] = 'numeric';
                    break;
                case 'varchar':
                    if($dataColumn['max_length'] == 255){
                        //$dataColumn['input_type'] = 'textarea';
                    }

                    break;
                case 'decimal':
                    $dataColumn['max_length'] = (int)$item->numeric_precision;
                    $dataColumn['input_type'] = 'decimal';
                    break;
                case 'enum':
                    $options = array();
                    $xstr = explode(',', substr($item->column_type, 5, -1));
                    foreach($xstr as $str){
                        if(substr($str, 0, 1) == "'"){
                            $options[] = substr($str, 1, -1);
                        }else{
                            $options[] = $str;
                        }
                    }


                    $dataColumn['input_type'] = 'enum';
                    $dataColumn['options'] = $options;
                    break;


            }

            $dataColumn = $this->applyNewType($item->column_name, $dataColumn);

            $dataType[$item->column_name] = $dataColumn;


        }

        foreach($columns as $item){
            if(isset($dataType[$item])){continue;}
            $display = ucwords(str_replace('_', ' ', $item));

            $dataColumn = array(
                'column_name' => $item,
                'column_text' => $display,
                'max_length' => 0,
                'dec_length' => 0,
                'input_type' => 'text',
                'related_field' => '',
                'type' => 'additional',
                'options' => array()
            );

            $dataColumn = $this->applyNewType($item, $dataColumn);

            $dataType[$item] = $dataColumn;
        }

        $this->dataType = $dataType;

    }

    /**
     * @return bool
     */
    private function actionIndex(){
        //$this->processJoin();
        $selected = array('t0.*');
//        foreach($this->columns as $item){
//            $selected[] = 't0.'.$item;
//        }
//
//        $selected[] = 't0.id';

        $lists = DB::table($this->table.' as t0');

        foreach($this->arrWhere as $item){
            $lists->where($item[0], $item[1], $item[2]);
        }

        if($this->filter != null){
            foreach($this->filter as $key=>$item){
                if($item[0] == 'contain'){
                    // still using t0, column from other tb not included
                    $lists->where('t0.'.$key, 'LIKE', '%'.$item[1].'%');
                }
            }
        }

        foreach($this->setJoin as $key=>$item){
            $selected[] = $item[3].'.'.$item[1];
            $lists->leftJoin($item[0].' as '.$item[3] , 't0.'.$key, '=', $item[3].'.id');
        }

        $lists->select($selected);

        if($this->orderBy != null and $this->orderByCustom == null){
            $order = $this->orderBy;
            $lists->orderBy($order[0], $order[1]);
        }

        if($this->orderByCustom != null){
            $order = $this->orderByCustom;
            $lists->orderBy($order[0], $order[1]);
        }

        $lists = $lists->paginate($this->perPage);

        $this->lists = $lists;


        foreach ($lists as $item) {
            $this->setActions($item);
            $this->setCostomColumns($item);
        }


        if($this->lists == null){
            return false;
        }

        $this->pagingLinks = $this->lists->links();


        return true;
    }

    /**
     *
     */
    private function actionCreate(){
        $this->initCreateFields();
    }

    /**
     * @return bool
     */
    private function actionSave(){
        $this->initCreateFields();
        $createFields = $this->createFields;


        $insertData = array();
        $validateData = array();
        $postData = $this->postCreateData;

        foreach($createFields as $item){
            if($item == 'id'){continue;}
            if(!isset($postData[$item])){continue;}
            $value = $postData[$item];

            $changeType = $this->changeType;

            if(isset($changeType[$item])){
                $type = $changeType[$item];
                if($type['new_type'] == 'image'){
                    $val = $this->uploadFile($item, $type['target_dir']);
                    if(!$val){continue;}else{$value = $val;}
                }


            }


            $validateData[$item] = $value;

            if($this->dataType[$item]['input_type'] == 'join_nn'){
                continue;
            }

            $insertData[$item] = $value;
        }

        if($this->status == false){
            return false;
        }

        $status = false;
        $valid = true;


        if(count($this->validateRules) > 0){
            $validator = Validator::make($insertData, $this->validateRules);
            if($validator->fails()){
                $this->validateErrors = $validator->messages()->toArray();
                $valid = false;
            }
        }


        if($this->allowCreate and $valid){
            $insertNN = $this->insertNN;
            $status = DB::transaction(function() use($insertData, $insertNN) {
                $insertId = DB::table($this->table)->insertGetId($insertData);

                if($insertNN != null){
                    $bulkInsert = array();
                    foreach($insertNN as $item){
                        foreach($item['values'] as $val){
                            $bulkInsert[] = array(
                                $item['field_rel'] => $insertId,
                                $item['join_field_rel'] => $val
                        );
                        }

                    }


                    if(count($bulkInsert) > 0){
                        DB::table($item['relation_table'])->insert($bulkInsert);
                    }

                }

                return true;
            });

        }

        $this->status = $status;
        return false;
    }

    /**
     *
     */
    private function actionEdit(){
        $this->initEditFields();
    }

    /**
     * @return bool
     */
    private function actionUpdate(){

        $this->initEditFields();

        $editFields = $this->editFields;

        $updateData = array();
        $validateData = array();
        $postData = $this->postUpdateData;
        $changeType = $this->changeType;

        foreach($editFields as $item){
            if($item == 'id'){continue;}
            if(!isset($postData[$item])){
                continue;
            }

            $value = $postData[$item];

            if(isset($changeType[$item])){

                $type = $changeType[$item];

                if($type['new_type'] == 'image'){

                    $val = $this->uploadFile($item, $type['target_dir'], 'update');
                    if(!$val){continue;}else{$value = $val;}
                }
            }


            $validateData[$item] = $value;

            if($this->dataType[$item]['input_type'] == 'join_nn'){
                continue;
            }

            $updateData[$item] = $value;
        }

        //$id = Input::get('id');

        if(count($updateData) < 1){
            return false;
        }

        if($this->status == false){
            return false;
        }

        $status = false;
        $valid = true;

        if(count($this->validateRules) > 0){
            $validator = Validator::make($validateData, $this->validateRules);
            if($validator->fails()){
                $this->validateErrors = $validator->messages()->toArray();
                $valid = false;
            }
        }

        if($this->allowEdit and $valid){
            $insertNN = $this->insertNN;

            $insertId = $this->ids;
            $dataType = $this->dataType;
            $status = DB::transaction(function() use($updateData, $insertNN, $insertId, $dataType) {

                DB::table($this->table)->where(array('id' => $this->ids))->update($updateData);

                if($insertNN != null){


                    $bulkInsert = array();

                    foreach($insertNN as $key=>$item){
                        $oldNNValues = $dataType[$key]['value'];

                        foreach($item['values'] as $val){

                            $skipInsert = false;
                            if(count($oldNNValues) > 0){
                                foreach($oldNNValues as $old){
                                    if($old == $val){
                                        $skipInsert = true;
                                        break;
                                    }
                                }

                            }

                            if(!$skipInsert){
                                $bulkInsert[] = array(
                                    $item['field_rel'] => $insertId,
                                    $item['join_field_rel'] => $val
                                );
                            }

                        }


                    }


                    if(count($bulkInsert) > 0){
                        DB::table($item['relation_table'])->insert($bulkInsert);
                    }

                }


                if($this->delNN != null){
                    foreach($this->delNN as $key=>$del){

                        if(isset($this->dataType[$key])){
                            if(count($this->dataType[$key]['value']) > 0){
                                foreach($this->dataType[$key]['value'] as $val){
                                    $skipDel = false;
                                    if($insertNN != null){
                                        if(isset($insertNN[$key])){
                                            foreach($insertNN[$key]['values'] as $dval){
                                                if($dval == $val){
                                                    $skipDel = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    if(!$skipDel){
                                        $arrDel = array(
                                            $del['field_rel'] => $this->ids,
                                            $del['join_field_rel'] => $val
                                        );


                                        DB::table($del['relation_table'])->where($arrDel)->delete();
                                    }



                                }
                            }
                        }
                    }
                }




                return true;
            });

        }





        $this->status = $status;
        return false;
    }

    /**
     *
     */
    private function actionRead(){
        $this->initReadFields();
    }

    /**
     *
     */
    private function actionDelete(){
        if($this->allowDelete){
            foreach($this->changeType as $key=>$item){
                if($item['new_type'] == 'image'){
                    $targetDir = public_path($item['target_dir']);
                    $row = $this->row;
                    $oldFile = $targetDir.'/'.$row[$key];

                    if(file_exists($oldFile) and is_file($oldFile)){
                        if(is_writable($oldFile)){
                            unlink($oldFile);
                        }
                    }
                }
            }

            $deleteNN = null;

            if($this->joinNN != null){


                foreach($this->joinNN as $item){


                    $x = DB::table($item['relation_table'])
                        ->select($item['relation_table'].'.id')
                        ->where($item['field_rel'], '=', $this->ids)
                        ->join($item['join_table'], $item['join_table'].'.id', '=', $item['relation_table'].'.'.$item['join_field_rel'])
                        ->get();

                    if($x != null){
                        $deleteNN[] = array(
                            'table' => $item['relation_table'],
                            'data' => $x
                        );
                    }


                }


            }

            $use = $this;

            DB::transaction(function() use($use, $deleteNN){
                if($deleteNN != null){
                    foreach($deleteNN as $item){
                        foreach($item['data'] as $x){
                            DB::table($item['table'])->delete($x->id);
                        }

                    }
                }

                DB::table($use->table)->delete($use->ids);
            });



        }

    }

    private $exportTotal;
    private $exportPaging;

    private function actionPrepareExport(){

        $row = DB::table($this->table)->selectRaw("count(*) as aggregate");

        if(Session::has('crud-'.$this->uri)){
            $session = Session::get('crud-'.$this->uri);

            if(isset($session['export-from']) and isset($session['export-to']) and $this->exportFilter != null){
                $row->whereBetween($this->exportFilter, array($session['export-from'], $session['export-to']));
            }
        }

        $row = $row->first();

        $total = $row->aggregate * 1;

        $paging = true;

        if($total > 1000){
            $paging = true;
        }

        $this->exportTotal = $total;
        $this->exportPaging = $paging;

        $this->responseType = 'json';
    }

    private function actionExport(){
        $csv = DB::table('booked_ticket_his')->orderBy('id', 'desc')->limit(10)->get();

        $this->csv = $csv;
        $this->responseType = 'csv';
    }

    /**
     * @return bool
     */
    private function getOneRow(){

        $selected = array();

        foreach($this->allColumns as $item){
            $selected[] = 't0.'.$item;
        }


        if($this->ids < 1){
            return false;
        }

        //$row = $this->find($this->ids);
        $row = DB::table($this->table.' as t0')->select('t0.*');
        foreach($this->setJoin as $key=>$item){
            $selected[] = $item[3].'.'.$item[1];
            $row->leftJoin($item[0].' as '.$item[3], 't0.'.$key, '=', $item[3].'.id');

        }
        $row->select($selected);

        $row = $row->where('t0.id', '=', $this->ids)->first();

        if($row == null){
            return false;
        }

        $row = (array)$row;

        $dataType = $this->dataType;

        foreach($dataType as $key=>$item){
            if($dataType[$key]['input_type'] == 'join' and $this->action != 'edit'){

                $dataType[$key]['value'] = $row[$dataType[$key]['related_field']];

            }else{
                $dataType[$key]['value'] = $row[$key];
            }

        }



        $this->dataType = $dataType;
        $this->row = $row;
        return false;
    }

    /**
     *
     */
    private function initEditFields(){

        $editFields = array();

        if(count($this->editFields) < 1 and count($this->fields) > 0){
            $editFields = $this->fields;
        }else if(count($this->editFields) < 1){
            $editFields = $this->allColumns;
        }

        $dataType = $this->dataType;

        if($this->joinNN != null){
            foreach($this->joinNN as $item){
                $editFields[] = $item['column_name'];
                $options = array();
                if($this->action == 'edit'){
                    $options = $this->joinNNOption[$item['column_name']];
                }

                $values = DB::table($item['relation_table'])
                    ->select($item['relation_table'].'.'.$item['join_field_rel'].' as id')
                    ->where($item['field_rel'], '=', $this->ids)
                    ->join($item['join_table'], $item['join_table'].'.id', '=', $item['relation_table'].'.'.$item['join_field_rel'])
                    ->get();
                $value = null;

                if($values != null){
                    $value = array();

                    foreach($values as $val){
                        $value[] = $val->id;
                    }
                }


                $dataType[$item['column_name']] = array(
                    'column_name' => $item['column_name'],
                    'column_text' => ucwords(str_replace('_', ' ', $item['column_name'])),
                    'max_length' => 0,
                    'dec_length' => 0,
                    'input_type' => 'join_nn',
                    'related_field' => '',
                    'options' => $options,
                    'value' => $value
                );

            }


        }

        $this->editFields = $editFields;
        $this->dataType = $dataType;
    }

    /**
     *
     */
    private function initCreateFields(){
        $createFields = array();
        if(count($this->createFields) < 1 and count($this->fields) > 0){
            $createFields = $this->fields;
        }else if(count($this->createFields) < 1){
            $createFields = $this->allColumns;
        }

        $dataType = $this->dataType;

        if($this->joinNN != null){
            foreach($this->joinNN as $item){
                $createFields[] = $item['column_name'];
                $options = array();
                if($this->action == 'create'){
                    $options = $this->joinNNOption[$item['column_name']];
                }


                $dataType[$item['column_name']] = array(
                    'column_name' => $item['column_name'],
                    'column_text' => ucwords(str_replace('_', ' ', $item['column_name'])),
                    'max_length' => 0,
                    'dec_length' => 0,
                    'input_type' => 'join_nn',
                    'related_field' => '',
                    'options' => $options
                );

            }
        }

        $this->createFields = $createFields;
        $this->dataType = $dataType;


    }

    /**
     *
     */
    private function initReadFields(){

        $readFields = array();

        if(count($this->readFields) < 1 and count($this->fields) > 0){
            $readFields = $this->fields;
        }else if(count($this->readFields) < 1){
            $readFields = $this->allColumns;
        }
        $dataType = $this->dataType;

        if($this->joinNN != null){
            foreach($this->joinNN as $item){
                $readFields[] = $item['column_name'];
                $options = array();
                if($this->action == 'edit'){
                    $options = $this->joinNNOption[$item['column_name']];
                }

                $values = DB::table($item['relation_table'])
                    ->select($item['join_table'].'.'.$item['join_field'].' as value')
                    ->where($item['field_rel'], '=', $this->ids)
                    ->join($item['join_table'], $item['join_table'].'.id', '=', $item['relation_table'].'.'.$item['join_field_rel'])
                    ->get();
                $value = null;

                if($values != null){
                    $value = '';

                    foreach($values as $val){
                        $value .= $val->value.', ';
                    }
                }

                if($value != ''){
                    $value = substr($value, 0, -2);
                }


                $dataType[$item['column_name']] = array(
                    'column_name' => $item['column_name'],
                    'column_text' => ucwords(str_replace('_', ' ', $item['column_name'])),
                    'max_length' => 0,
                    'dec_length' => 0,
                    'input_type' => 'join_nn',
                    'related_field' => '',
                    'options' => $options,
                    'value' => $value
                );

            }


        }

        $this->readFields = $readFields;
        $this->dataType = $dataType;

    }


    /**
     * @param $id
     */
    protected function setId($id){
        $this->ids = $id;
    }

    private function setAction($action){
        $this->action = $action;
    }

    private function setUri($uri){
        $this->uri = $uri;
    }

//    private function setIds($id){
//        $this->ids = $id;
//    }

    /**
     * @return array
     */
    private function getResponse(){


        $response = array(
            'data_type' => $this->dataType,
            'action' => $this->action,
            'uri' => $this->uri,
            'status' => $this->status,
        );


        switch($this->action){
            case 'index':
                $indexResponse = array(
                    'lists' => $this->lists->toArray(),
                    'custom_values' => $this->customValues,
                    'index_session' => $this->indexSession,
                    'action_lists' => $this->actionLists,
                    'columns' => $this->columns,
                    'allow_create' => $this->allowCreate,
                    'allow_read' => $this->allowRead,
                    'allow_edit' => $this->allowEdit,
                    'allow_delete' => $this->allowDelete,
                    'allow_export' => $this->allowExport,
                    'export_max_limit' => $this->exportMaxLimit,
                    'export_filter' => $this->exportFilter,
                    'allow_search' => $this->allowSearch,
                    'allow_order' => $this->allowOrder,
                    'allow_mass_delete' => $this->allowMassDelete,
                    'message' => Session::get('message'),
                    'list_export_text' => $this->listExportText,
                    'list_search_text' => $this->listSearchText,
                    'list_create_text' => $this->listCreateText,
                    'list_edit_text' => $this->listEditText,
                    'list_read_text' => $this->listReadText,
                    'list_delete_text' => $this->listDeleteText,
                    'list_mass_delete_text' => $this->listMassDeleteText,
                    'title' => $this->subTitleIndex.' '.$this->title,
                    'master_blade' => $this->masterBlade,
                    'paging_links' => $this->pagingLinks,
                    'external_link' => $this->externalLink,
                    'allow_multiple_select' => $this->allowMultipleSelect,

                );
                $response = array_merge($response, $indexResponse);
                break;
            case 'create':
                $createResponse = array(
                    'create_fields' => $this->createFields,
                    'create_btn_text' => $this->createBtnText,
                    'title' => $this->subTitleCreate.' '.$this->title,
                    'master_blade' => $this->masterBlade,
                    'back_btn_text' => $this->backBtnText,
                    'external_link' => $this->externalLink,
                    'is_load_mce_libs' => $this->isLoadMceLibs,
                    //'join_nn' => $this->joinNN,
                    //'join_nn_option' => $this->joinNNOption,
                    'errors' => Session::get('validate_errors')

                );
                $response = array_merge($response, $createResponse);

                break;
            case 'save':
                $saveResponse = array(
                    'allow_create' => $this->allowCreate,
                    'validate_errors' => $this->validateErrors,

                );
                $response = array_merge($response, $saveResponse);
                break;
            case 'edit':
                $editResponse = array(
                    'edit_fields' => $this->editFields,
                    'edit_btn_text' => $this->editBtnText,
                    'id' => $this->ids,
                    'title' => $this->subTitleEdit.' '.$this->title,
                    'master_blade' => $this->masterBlade,
                    'back_btn_text' => $this->backBtnText,
                    'external_link' => $this->externalLink,
                    'is_load_mce_libs' => $this->isLoadMceLibs,
                    'errors' => Session::get('validate_errors')

                );
                $response = array_merge($response, $editResponse);

                break;
            case 'update':

                $updateResponse = array(
                    'allow_create' => $this->allowCreate,
                    'validate_errors' => $this->validateErrors,

                );
                $response = array_merge($response, $updateResponse);
                break;
            case 'read':

                $readResponse = array(
                    'title' => $this->subTitleRead.' '.$this->title,
                    'master_blade' => $this->masterBlade,
                    'read_fields' => $this->readFields,
                    'back_btn_text' => $this->backBtnText,
                    'external_link' => $this->externalLink,

                );
                $response = array_merge($response, $readResponse);
                break;

            case 'prepare_export':


                $response = array(
                    'action' => $this->action,
                    'uri' => $this->uri,
                    'status' => $this->status,
                    'total' => $this->exportTotal,
                    'paging' => $this->exportPaging

                );

                return $response;

        }

        $this->masterData['crud'] = $response;

        $this->response = $this->masterData;




        return $this->response;
    }

    /**
     * @param $columns
     */
    protected function columns($columns){
        $this->columns = $columns;
    }

    /**
     * @param $fields
     */
    protected function fields($fields){
        $this->fields = $fields;
    }

    /**
     * @param $createFields
     */
    protected function createFields($createFields){
        $this->createFields = $createFields;
    }

    /**
     * @param $editFields
     */
    protected function editFields($editFields){
        $this->editFields = $editFields;
    }

    /**
     * @param $readFields
     */
    protected function readFields($readFields){
        $this->readFields = $readFields;
    }

    /**
     * @param $rules
     */
    protected function validateRules($rules){
        if(is_array($rules)){
            $this->validateRules = $rules;
        }

    }

    /**
     * @param $callback
     * @return bool
     */
    protected function callbackBeforeUpdate($callback){
        if($this->action != 'update'){
            return false;
        }
        $result = $callback($this->postUpdateData, $this->ids);

        if($result === false){
            return $this->status = false;
        }

        $this->postUpdateData = $result;
        return false;
    }

    protected function callbackBeforeSave($callback){
        if($this->action != 'save'){
            return false;
        }
        $result = $callback($this->postCreateData);

        if($result === false){
            return $this->status = false;
        }

        $this->postCreateData = $result;
        return false;
    }

    /**
     * @param $masterData
     */
    protected function setMasterData($masterData){
        $this->masterData = $masterData;
    }

    /**
     * @param $masterBlade
     */
    protected function setMasterBlade($masterBlade){
        $this->masterBlade = $masterBlade;
    }

    /**
     * @param $title
     * @param $class
     * @param $callbackUrl
     * @return bool
     */
    protected function addAction($title, $class, $callbackUrl){
        if($this->action != 'index'){
            //return false;
        }

        $this->actions[] = array(
            'title' => $title,
            'class' => $class,
            'callback_url' => $callbackUrl
        );


    }

    /**
     * @param $title
     * @param $url
     * @param string $class
     * @param bool $openNewPage
     * @param bool $showAtIndex
     * @param bool $showAtRead
     * @param bool $showAtCreate
     * @param bool $showAtEdit
     * @return bool
     */
    protected function addExternalLink($title, $url, $class = 'btn btn-default', $openNewPage = false, $showAtIndex = true, $showAtRead = true, $showAtCreate = true, $showAtEdit = true){
        if(is_null($class)){$class = 'btn btn-default';}
        if(is_null($openNewPage)){$openNewPage = false;}
        if(is_null($showAtIndex)){$showAtIndex = true;}
        if(is_null($showAtRead)){$showAtRead = true;}
        if(is_null($showAtCreate)){$showAtCreate = true;}
        if(is_null($showAtEdit)){$showAtEdit = true;}


        $ext = array(
            'title' => $title,
            'url' => $url,
            'class' => $class,
            'target' => '_self',
            'show_at_index' => $showAtIndex,
            'show_at_read' => $showAtRead,
            'show_at_create' => $showAtCreate,
            'show_at_edit' => $showAtEdit,
        );

        if($openNewPage){
            $ext['target'] = '_blank';
        }

        $this->externalLink[] = $ext;

        return false;
    }

    /**
     * @param $row
     */
    private function setActions($row){

        foreach ($this->actions as $item) {
            $url = $item['callback_url']($row, $row->id);

            if(!$url){continue;}

            $this->actionLists[$row->id][] = array(
                'title' => $item['title'],
                'class' => $item['class'],
                'url' => $url,
            );
        }

    }

    private function setCostomColumns($row){
        foreach($this->customColumns as $key=>$item){
            isset($row->{$key}) ? $keyVal = $row->{$key} : $keyVal = null;
            $value = $item['callback']($row, $keyVal);
            if(!$value){continue;}


            $this->customValues[$key][$row->id] = $value;
        }
    }

    /**
     * @param $field
     * @param $condition
     * @param $value
     */
    protected function where($field, $condition, $value){
        $this->arrWhere[] = array($field, $condition, $value);
    }

    protected function orderBy($field, $direction = 'asc'){
        $this->orderBy = array($field, $direction);
    }

    private $view_path = 'packages.timenz.crud.';

    protected function run(){

    }

    protected function afterRun(){

    }

    private $csv;

    public function index(){
        //$uri = Route::getCurrentRoute()->uri();
        $uri = \Request::decodedPath();

        $this->setUri($uri);

        $session = array();
        if(Session::has('crud-'.$uri)){
            $session = Session::get('crud-'.$uri);
            $this->indexSession = $session;
            if(isset($session['order'])){
                $this->orderByCustom = array($session['order'][0], $session['order'][1]);
            }
            if(isset($session['filter'])){
                $this->filter = $session['filter'];
            }
        }

        $extAction = array(
            'prepare_export',
            'export',
            'limit_export',
            'set_order',
            'reset_order',
            'search',
            'reset_search'
        );
        $action = Input::get('action');

        if(in_array($action, $extAction)){
            if($action == 'set_order'){
                $session['order'] = array(Input::get('sort_field'), Input::get('direction'));

                Session::set('crud-'.$uri, $session);
                return Redirect::back();

            }
            if($action == 'search'){

                if(Input::has('search')){
                    $filter = array();
                    foreach(Input::get('search') as $key=>$item){
                        if($item['value'] == ''){continue;}
                        $filter[$key] = array($item['filter'], $item['value']);
                    }

                    if(count($filter) > 0){
                        $this->filter = $filter;
                        $session['filter'] = $filter;
                        Session::set('crud-'.$uri, $session);
                    }
                }
                return Redirect::back();

            }

            if($action == 'reset_order'){
                if(isset($session['order'])){
                    unset($session['order']);
                }

                Session::set('crud-'.$uri, $session);
                return Redirect::back();

            }

            if($action == 'reset_search'){
                if(isset($session['filter'])){
                    unset($session['filter']);
                }

                Session::set('crud-'.$uri, $session);
                return Redirect::back();

            }

            if($action == 'limit_export'){
                $session['export-from'] = Input::get('from');
                $session['export-to'] = Input::get('to');

                Session::set('crud-'.$uri, $session);
                return Redirect::back()->with('show-modal-export', true);

            }

            $this->setAction($action);

            $run = $this->run();

            if($run === false){
                return 'on k';
            }

            if($this->errorText != ''){
                return $this->errorText;
            }

            $this->execute();

            if($this->responseType == 'json'){
                return Response::json($this->getResponse());
            }

            if($this->responseType == 'csv'){
                $headers = [
                    'Content-type'        => 'application/csv'
                    ,   'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
                    ,   'Content-type'        => 'text/csv'
                    ,   'Content-Disposition' => 'attachment; filename='.$this->uri.'-'.time().'.csv'
                    ,   'Expires'             => '0'
                    ,   'Pragma'              => 'public'
                ];

                $csv = $this->csv;

                if($csv != null){
                    //$list = $list->toArray();


                    # add headers for each column in the CSV download
                    array_unshift($csv, array_keys((array)$csv[0]));



                    $callback = function() use ($csv)
                    {
                        $FH = fopen('php://output', 'w');
                        foreach ($csv as $row) {
                            fputcsv($FH, (array)$row);
                        }
                        fclose($FH);
                    };


                    return Response::stream($callback, 200, $headers);
                }

            }

            return 'okai';
        }

        $this->setAction('index');

        $run = $this->run();

        if($run === false){
            return 'on k';
        }

        if($this->errorText != ''){
            return $this->errorText;
        }

        $this->execute();
        $data = $this->getResponse();
        $this->afterRun();
        return View::make($this->view_path.'index', $data);

    }

    public function create(){
        $uri = \Request::decodedPath();
        $xuri = explode('/', $uri, -1);

        $uri = join('/', $xuri);

        $this->setAction('create');
        $this->setUri($uri);

        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();
        $data = $this->getResponse();
        $this->afterRun();
        return View::make($this->view_path.'create', $data);
    }

    public function store(){
        $uri = \Request::decodedPath();
        $this->setAction('save');

        $this->setUri($uri);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();

        $data = $this->getResponse();
        //return 'ok';
        $this->afterRun();

        if($data['crud']['status'] === false){

            return Redirect::back()
                ->withInput()
                ->with('validate_errors', $data['crud']['validate_errors'])
                ->with('message', 'There were validation errors.');
        }

        $msg = 'Data gagl disimpan';

        if($data['crud']['status']){
            $msg = 'Data berhasil disimpan.';
        }

        return Redirect::to($uri)->with('message', $msg);
    }



    public function show($id){
        //$uri = Route::getCurrentRoute()->uri();
        $uri1 = \Request::decodedPath();

        $xuri = explode('/', $uri1, -1);

        $uri = join('/', $xuri);
        $id = str_replace($uri.'/', '', $uri1);

        $this->setAction('read');
        $this->setUri($uri);
        $this->setId($id);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();
        $this->afterRun();
        return View::make($this->view_path.'read', $this->getResponse());
    }



    public function edit($id){
        $uri1 = str_replace('/edit', '', \Request::decodedPath());
        $xuri = explode('/', $uri1, -1);

        $uri = join('/', $xuri);

        $id = str_replace($uri.'/', '', $uri1);

        $this->setAction('edit');
        $this->setUri($uri);
        $this->setId($id);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();

        $this->afterRun();
        return View::make($this->view_path.'edit', $this->getResponse());
    }



    public function update($id){

        $uri1 = \Request::decodedPath();
        $xuri = explode('/', $uri1, -1);

        $uri = join('/', $xuri);

        $id = str_replace($uri.'/', '', $uri1);

        $this->setAction('update');

        $this->setUri($uri);
        $this->setId($id);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();

        $data = $this->getResponse();
        //return 'ok';
        $this->afterRun();

        if($data['crud']['status'] === false){

            return Redirect::back()
                ->withInput()
                ->with('validate_errors', $data['crud']['validate_errors'])
                ->with('message', 'There were validation errors.');
        }

        $msg = 'Data gagl diupdate';

        if($data['crud']['status']){
            $msg = 'Data berhasil diupdate.';
        }

        return Redirect::to($uri)->with('message', $msg);
    }

    public function destroy($id){
        $uri1 = \Request::decodedPath();
        $xuri = explode('/', $uri1, -1);

        $uri = join('/', $xuri);

        $id = str_replace($uri.'/', '', $uri1);

        $this->setAction('delete');

        $this->setUri($uri);
        $this->setId($id);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();

        $this->getResponse();

        $this->afterRun();
        return Redirect::back()->with('message', 'Data berhasil di hapus.');
    }

    protected function getDataType(){
        return $this->dataType;
    }

    protected function callbackColumn($columnName, $callback){
        $this->customColumns[$columnName] = array(
            'callback' => $callback
        );
    }

    protected function displayAs($columnName, $displayAs){
        $this->columnDisplay[$columnName] = $displayAs;
    }

    private function uploadFile($item, $targetDir, $action = 'insert', $type = 'image'){


        $file = Input::file($item);

        $value = '';
        if($file != null){
            if($file->isValid()){
                $mime = explode('/', $file->getClientMimeType());
                $extension = '.jpg';
                if($mime[0] != 'image' and $type == 'image'){
                    return false;
                }

                $targetDir = public_path($targetDir);

                if($action == 'update'){
                    $row = $this->row;
                    $oldFile = $targetDir.'/'.$row[$item];

                    if(file_exists($oldFile) and is_file($oldFile)){
                        if(is_writable($oldFile)){
                            unlink($oldFile);
                        }
                    }
                }

                if(isset($mime[1])){$extension = '.'.$mime[1];}

                $fileName = $file->getClientOriginalName();

                while(file_exists($targetDir.'/'.$fileName)){
                    $fileName = str_replace($extension, '', $fileName).'.cc'.$extension;
                }
                $file->move($targetDir, $fileName);
                $value = $fileName;




            }

        }
        if($value != ''){
            return $value;
        }
        return false;
    }

    protected function setExportFilter($field){
        $this->allowExport = true;
        $this->exportFilter = $field;
    }
}
