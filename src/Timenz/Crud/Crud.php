<?php

namespace Timenz\Crud;

use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;

class Crud{

    private $schema;
    private $dataType = array();
    private $modelName = '';
    private $methodName = '';
    private $title = 'Crud';
    private $action = null;
    private $table = '';
    private $columns = array();
    private $allColumns = array();
    private $fields = array();
    private $createFields = array();
    private $editFields = array();
    private $validateRules = array();
    private $validateErrors = array();
    private $lists = array();
    private $crudField;
    private $perPage = 20;
    private $id = 0;
    private $response = array();
    private $row = array();
    private $changeType = array();
    private $setJoin = array();
    private $status = true;
    private $pagingLinks;
    private $postCreateData = array();
    private $postUpdateData = array();

    protected $allowCreate = true;
    protected $allowRead = true;
    protected $allowDelete = true;
    protected $allowMassDelete = false;
    protected $allowEdit = true;
    private $masterBlade = 'admin.master';
    protected $listCreateText = 'tambah';
    protected $listReadText = 'detail';
    protected $listEditText = 'ubah';
    protected $listMassDeleteText = 'hapus terpilih';
    protected $listDeleteText = 'hapus';
    protected $createBtnText = 'tambah';
    protected $editBtnText = 'ubah';
    protected $backBtnText = 'kembali';
    protected $columnDisplay = array();
    private $masterData;
    private $arrWhere = array();


    protected function init($table){
        $this->table = $table;
        $postData = Input::all();
        $this->postCreateData = $postData;
        $this->postUpdateData = $postData;

    }

    protected function execute(){
        if($this->action == null){
            return false;
        }

//        $this->modelName = $modelName;
//        $this->title = ucwords(str_replace('_', ' ', $modelName));

        $this->setDefaultColumn();

        switch($this->action){
            case 'index':
                $status = $this->actionIndex();
                if(!$status){
                    return false;
                }
                break;

            case 'create':
                $this->actionCreate();
                break;

            case 'save':
                $this->actionSave();
                break;

            case 'edit':
                $this->getOneRow();
                $this->actionEdit();
                break;

            case 'update':
                $this->actionUpdate();
                break;

            case 'read':
                $this->getOneRow();
                $this->actionRead();
                break;

            case 'delete':
                //$this->getOneRow();
                $this->actionDelete();
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
    }

    protected function changeType($field, $newType, $option = array(), $renewOnUpdate = false){
        $changeType = $this->changeType;

        switch($newType){
            case 'money':
                $changeType[$field] = array(
                    'new_type' => $newType
                );
                break;

            case 'textarea':
                $changeType[$field] = array(
                    'new_type' => $newType
                );
                break;

            case 'select':
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

        }

        $this->changeType = $changeType;
    }

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
            case 'hidden':
                $dataColumn['default_value'] = $changeType[$columnName]['default_value'];
                $dataColumn['renew_on_update'] = $changeType[$columnName]['renew_on_update'];
                break;
        }

        return $dataColumn;
    }

    protected function setJoin($field, $joinTable, $joinField, $arrayWhere = array()){

        $this->setJoin[$field] = array($joinTable, $joinField, $arrayWhere);

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

    private function populateField(){
        $schema = $this->schema;
        $columnDisplay = $this->columnDisplay;


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

        $this->dataType = $dataType;

    }

    private function actionIndex(){
        //$this->processJoin();
        $selected = array();
        foreach($this->columns as $item){
            $selected[] = $this->table.'.'.$item;
        }

        $selected[] = $this->table.'.id';

        $lists = DB::table($this->table);

        foreach($this->arrWhere as $item){
            $lists->where($item[0], $item[1], $item[2]);
        }

        foreach($this->setJoin as $key=>$item){
            $selected[] = $item[0].'.'.$item[1];
            $lists->leftJoin($item[0], $this->table.'.'.$key, '=', $item[0].'.id');
        }

        $lists->select($selected);

        $lists = $lists->paginate($this->perPage);

        $this->lists = $lists;



        if($this->lists == null){
            return false;
        }

        $this->pagingLinks = $this->lists->links();


        return true;
    }

    private function actionCreate(){
        $this->initCreateFields();
    }

    private function actionSave(){
        $this->initCreateFields();
        $createFields = $this->createFields;


        $insertData = array();
        $postData = $this->postUpdateData;

        foreach($createFields as $item){
            $insertData[$item] = $postData[$item];
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
            DB::table($this->table)->insert($insertData);
            $status = true;
        }

        $this->status = $status;
    }

    private function actionEdit(){
        $this->initEditFields();
    }

    private function actionUpdate(){
        $this->initEditFields();

        $editFields = $this->editFields;


        $updateData = array();
        $postData = $this->postUpdateData;

        foreach($editFields as $item){
            $updateData[$item] = $postData[$item];
        }

        //$id = Input::get('id');

        if($this->status == false){
            return false;
        }

        $status = false;
        $valid = true;

        if(count($this->validateRules) > 0){
            $validator = Validator::make($updateData, $this->validateRules);
            if($validator->fails()){
                $this->validateErrors = $validator->messages()->toArray();
                $valid = false;
            }
        }




        if($this->allowEdit and $valid){
            DB::table($this->table)->where(array('id' => $this->id))->update($updateData);
            $status = true;
        }


        $this->status = $status;
    }

    private function actionRead(){

    }

    private function actionDelete(){
        if($this->allowDelete){
            DB::table($this->table)->delete($this->id);
        }

    }

    private function getOneRow(){
        $selected = $this->allColumns;
        unset($selected[0]);
        $selected[] = $this->table.'.id';

        if($this->id < 1){
            return false;
        }

        //$row = $this->find($this->id);
        $row = DB::table($this->table)->select($this->table.'.*');
        foreach($this->setJoin as $key=>$item){
            $selected[] = $item[0].'.'.$item[1];
            $row->leftJoin($item[0], $this->table.'.'.$key, '=', $item[0].'.id');

        }
        $row->select($selected);

        $row = $row->where($this->table.'.id', '=', $this->id)->first();

        if($row == null){
            return false;
        }

        $row = (array)$row;

        $dataType = $this->dataType;

        foreach($dataType as $key=>$item){
            if($dataType[$key]['input_type'] == 'join'){
                $dataType[$key]['value'] = $row[$dataType[$key]['related_field']];
            }else{
                $dataType[$key]['value'] = $row[$key];
            }

        }



        $this->dataType = $dataType;
        $this->row = $row;
    }

    private function initEditFields(){

        if(count($this->editFields) < 1 and count($this->fields) > 0){
            $this->editFields = $this->fields;
        }else if(count($this->editFields) < 1){
            $this->editFields = $this->allColumns;
        }
    }

    private function initCreateFields(){

        if(count($this->createFields) < 1 and count($this->fields) > 0){
            $this->createFields = $this->fields;
        }else if(count($this->createFields) < 1){
            $this->createFields = $this->allColumns;
        }
    }


    public function setId($id){
        $this->id = $id;
    }

    public function load($model, $method, $action, $id = 0){
        $this->action = $action;
        $this->modelName = $model;
        $this->methodName = $method;
        $this->id = $id;

    }

    public function getResponse(){

        $response = array(
            'data_type' => $this->dataType,
            'model_name' => $this->modelName,
            'method_name' => $this->methodName,
            'action' => $this->action,
            'status' => $this->status,
        );

        switch($this->action){
            case 'index':
                $indexResponse = array(
                    'lists' => $this->lists->toArray(),
                    'columns' => $this->columns,
                    'allow_create' => $this->allowCreate,
                    'allow_read' => $this->allowRead,
                    'allow_edit' => $this->allowEdit,
                    'allow_delete' => $this->allowDelete,
                    'allow_mass_delete' => $this->allowMassDelete,
                    'message' => Session::get('message'),
                    'list_create_text' => $this->listCreateText,
                    'list_edit_text' => $this->listEditText,
                    'list_read_text' => $this->listReadText,
                    'list_delete_text' => $this->listDeleteText,
                    'list_mass_delete_text' => $this->listMassDeleteText,
                    'title' => $this->title,
                    'master_blade' => $this->masterBlade,
                    'paging_links' => $this->pagingLinks,

                );
                $response = array_merge($response, $indexResponse);
                break;
            case 'create':
                $createResponse = array(
                    'create_fields' => $this->createFields,
                    'create_btn_text' => $this->createBtnText,
                    'title' => $this->title,
                    'master_blade' => $this->masterBlade,
                    'back_btn_text' => $this->backBtnText,
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
                    'id' => $this->id,
                    'title' => $this->title,
                    'master_blade' => $this->masterBlade,
                    'back_btn_text' => $this->backBtnText,
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
                    'title' => $this->title,
                    'master_blade' => $this->masterBlade,
                    'back_btn_text' => $this->backBtnText,

                );
                $response = array_merge($response, $readResponse);
                break;
        }

        $this->masterData['crud'] = $response;

        $this->response = $this->masterData;

        //DebugBar::info($this->response);

        return $this->response;
    }

    protected function columns($columns){
        $this->columns = $columns;
    }
//
//    protected function setTable($table){
//        $this->table = $table;
//    }

    protected function fields($fields){
        $this->fields = $fields;
    }

    protected function createFields($createFields){
        $this->createFields = $createFields;
    }

    protected function validateRules($rules){
        if(is_array($rules)){
            $this->validateRules = $rules;
        }

    }

    protected function callbackBeforeUpdate($callback){
        if($this->action != 'update'){
            return false;
        }
        $result = $callback($this->postUpdateData, $this->id);

        if($result === false){
            return $this->status = false;
        }

        $this->postUpdateData = $result;
    }

    protected function setMasterData($masterData){
        $this->masterData = $masterData;
    }

    protected function setMasterBlade($masterBlade){
        $this->masterBlade = $masterBlade;
    }

    protected function where($field, $condition, $value){
        $this->arrWhere[] = array($field, $condition, $value);
    }




}
