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
    private $responseType;
    private $tbCount = 1;

    protected $allowCreate = true;
    protected $allowRead = true;
    protected $allowDelete = true;
    protected $allowMassDelete = false;
    protected $allowEdit = true;
    protected $allowMultipleSelect = false;
    protected $allowExport = true;
    protected $listExportText = 'export';
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

            case 'prepare_export':
                $this->actionPrepareExport();
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

            case 'textarea':
                $changeType[$field] = array(
                    'new_type' => $newType
                );
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

    /**
     *
     */
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

    /**
     * @return bool
     */
    private function actionIndex(){
        //$this->processJoin();
        $selected = array();
        foreach($this->columns as $item){
            $selected[] = 't0.'.$item;
        }

        $selected[] = 't0.id';

        $lists = DB::table($this->table.' as t0');

        foreach($this->arrWhere as $item){
            $lists->where($item[0], $item[1], $item[2]);
        }

        foreach($this->setJoin as $key=>$item){
            $selected[] = $item[3].'.'.$item[1];
            $lists->leftJoin($item[0].' as '.$item[3] , 't0.'.$key, '=', $item[3].'.id');
        }

        $lists->select($selected);

        if($this->orderBy != null){
            $order = $this->orderBy;
            $lists->orderBy($order[0], $order[1]);
        }

        $lists = $lists->paginate($this->perPage);

        $this->lists = $lists;


        foreach ($lists as $item) {
            $this->setActions($item);
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
        $postData = $this->postUpdateData;

        foreach($editFields as $item){
            if($item == 'id'){continue;}
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
            DB::table($this->table)->where(array('id' => $this->ids))->update($updateData);
            $status = true;
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
            DB::table($this->table)->delete($this->ids);
        }

    }

    private function actionPrepareExport(){
        $this->responseType = 'json';
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

        if(count($this->editFields) < 1 and count($this->fields) > 0){
            $this->editFields = $this->fields;
        }else if(count($this->editFields) < 1){
            $this->editFields = $this->allColumns;
        }
    }

    /**
     *
     */
    private function initCreateFields(){

        if(count($this->createFields) < 1 and count($this->fields) > 0){
            $this->createFields = $this->fields;
        }else if(count($this->createFields) < 1){
            $this->createFields = $this->allColumns;
        }
    }

    /**
     *
     */
    private function initReadFields(){

        if(count($this->readFields) < 1 and count($this->fields) > 0){
            $this->readFields = $this->fields;
        }else if(count($this->readFields) < 1){
            $this->readFields = $this->allColumns;
        }
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
                    'action_lists' => $this->actionLists,
                    'columns' => $this->columns,
                    'allow_create' => $this->allowCreate,
                    'allow_read' => $this->allowRead,
                    'allow_edit' => $this->allowEdit,
                    'allow_delete' => $this->allowDelete,
                    'allow_export' => $this->allowExport,
                    'allow_mass_delete' => $this->allowMassDelete,
                    'message' => Session::get('message'),
                    'list_export_text' => $this->listExportText,
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

                $row = DB::table($this->table)->selectRaw("count(*) as aggregate")->first();

                $total = $row->aggregate * 1;

                $paging = false;

                if($total > 1000){
                    $paging = true;
                }

                $response = array(
                    'action' => $this->action,
                    'uri' => $this->uri,
                    'status' => $this->status,
                    'total' => $total,
                    'paging' => $paging

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


    public function index(){
        $uri = Route::getCurrentRoute()->uri();
        $this->setUri($uri);

        $extAction = array('prepare_export', 'export');
        $action = Input::get('action');

        if(in_array($action, $extAction)){
            $this->setAction($action);

            $this->execute();

            if($this->responseType == 'json'){
                return Response::json($this->getResponse());
            }

            return 'ok';
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
        return View::make($this->view_path.'index', $data);

    }

    public function create(){
        $uri = Route::getCurrentRoute()->uri();
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
        return View::make($this->view_path.'create', $data);
    }

    public function store(){
        $uri = Route::getCurrentRoute()->uri();
        $this->setAction('save');

        $this->setUri($uri);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();

        $data = $this->getResponse();
        //return 'ok';

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
        $uri = Route::getCurrentRoute()->uri();

        $xuri = explode('/', $uri, -1);

        $uri = join('/', $xuri);

        $this->setAction('read');
        $this->setUri($uri);
        $this->setId($id);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();
        return View::make($this->view_path.'read', $this->getResponse());
    }



    public function edit($id){
        $uri = Route::getCurrentRoute()->uri();
        $xuri = explode('/', $uri, -2);

        $uri = join('/', $xuri);

        $this->setAction('edit');
        $this->setUri($uri);
        $this->setId($id);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();

        return View::make($this->view_path.'edit', $this->getResponse());
    }



    public function update($id){

        $uri = Route::getCurrentRoute()->uri();
        $xuri = explode('/', $uri, -1);

        $uri = join('/', $xuri);
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


        $uri = Route::getCurrentRoute()->uri();
        $xuri = explode('/', $uri, -1);

        $uri = join('/', $xuri);
        $this->setAction('delete');

        $this->setUri($uri);
        $this->setId($id);
        $this->run();

        if($this->errorText != ''){
            return $this->errorText;
        }
        $this->execute();

        $this->getResponse();

        return Redirect::back()->with('message', 'Data berhasil di hapus.');
    }

    protected function getDataType(){
        return $this->dataType;
    }
}
