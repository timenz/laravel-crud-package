<?php

namespace Timenz\Crud;

use DB;
use Illuminate\Routing\Controller;
use Input;
use Session;
use Validator;
use Route;
use View;
use Redirect;
use Response;
use Log;

class Crud extends Controller{

    private $entity;
    private $view_path = 'vendor.timenz.crud.';


    public function __construct(){

        $this->entity = new CrudEntity();
    }


    /**
     * @return bool
     */
    private function execute(){

        if($this->entity->action == null){
            return false;
        }


        if($this->entity->masterBlade == ''){
            return false;
        }

        $process = new CrudProcess($this->entity);


        $status = $process->setDefaultColumn();

        if($status === false){
            return false;
        }

        switch($this->entity->action){
            case 'index':
                $status = $process->actionIndex();
                if(!$status){
                    return false;
                }
                $process->applyJoinNN();
                break;

            case 'create':
                $process->applyJoinNN();
                $process->actionCreate();
                break;

            case 'save':
                $process->applyJoinNN();
                $process->actionSave();
                break;

            case 'edit':
                $process->applyJoinNN();
                $process->getOneRow();
                $process->actionEdit();
                break;

            case 'update':
                $process->applyJoinNN();
                $process->getOneRow();
                $process->actionUpdate();
                break;

            case 'read':
                $process->applyJoinNN();
                $process->getOneRow();
                $process->actionRead();
                break;

            case 'delete':
                $process->applyJoinNN();
                $process->getOneRow();
                $process->actionDelete();
                break;

            case 'prepare_export':
                $process->actionPrepareExport();
                break;

            case 'export':
                $process->actionExport();
                break;


            default:
                return false;
                break;

        }


        if($this->entity->allowMassDelete){
            $this->entity->allowDelete = false;
        }



        return true;

    }


    /**
     * @return array
     */
    private function getResponse(){


        $response = array(
            'data_type' => $this->entity->dataType,
            'action' => $this->entity->action,
            'uri' => $this->entity->uri,
            'status' => $this->entity->status,
        );


        switch($this->entity->action){
            case 'index':
                $indexResponse = array(
                    'lists' => $this->entity->lists->toArray(),
                    'custom_values' => $this->entity->customValues,
                    'index_session' => $this->entity->indexSession,
                    'action_lists' => $this->entity->actionLists,
                    'columns' => $this->entity->columns,
                    'allow_create' => $this->entity->allowCreate,
                    'allow_read' => $this->entity->allowRead,
                    'allow_edit' => $this->entity->allowEdit,
                    'allow_delete' => $this->entity->allowDelete,
                    'allow_export' => $this->entity->allowExport,
                    'export_max_limit' => $this->entity->exportMaxLimit,
                    'export_filter' => $this->entity->exportFilter,
                    'allow_search' => $this->entity->allowSearch,
                    'allow_order' => $this->entity->allowOrder,
                    'allow_mass_delete' => $this->entity->allowMassDelete,
                    'message' => Session::get('message'),
                    'list_export_text' => $this->entity->listExportText,
                    'list_search_text' => $this->entity->listSearchText,
                    'list_create_text' => $this->entity->listCreateText,
                    'list_edit_text' => $this->entity->listEditText,
                    'list_read_text' => $this->entity->listReadText,
                    'list_delete_text' => $this->entity->listDeleteText,
                    'list_mass_delete_text' => $this->entity->listMassDeleteText,
                    'title' => $this->entity->subTitleIndex.' '.$this->entity->title,
                    'master_blade' => $this->entity->masterBlade,
                    'paging_links' => $this->entity->pagingLinks,
                    'external_link' => $this->entity->externalLink,
                    'allow_multiple_select' => $this->entity->allowMultipleSelect,
                    'join_nn_column' => $this->entity->joinNNColumn,
                    'join_nn_column_title' => $this->entity->joinNNColumnTitle

                );
                $response = array_merge($response, $indexResponse);
                break;
            case 'create':
                $createResponse = array(
                    'create_fields' => $this->entity->createFields,
                    'create_btn_text' => $this->entity->createBtnText,
                    'title' => $this->entity->subTitleCreate.' '.$this->entity->title,
                    'master_blade' => $this->entity->masterBlade,
                    'back_btn_text' => $this->entity->backBtnText,
                    'external_link' => $this->entity->externalLink,
                    'is_load_mce_libs' => $this->entity->isLoadMceLibs,
                    //'join_nn' => $this->joinNN,
                    //'join_nn_option' => $this->joinNNOption,
                    'errors' => Session::get('validate_errors')

                );
                $response = array_merge($response, $createResponse);

                break;
            case 'save':
                $saveResponse = array(
                    'allow_create' => $this->entity->allowCreate,
                    'validate_errors' => $this->entity->validateErrors,

                );
                $response = array_merge($response, $saveResponse);
                break;
            case 'edit':
                $editResponse = array(
                    'edit_fields' => $this->entity->editFields,
                    'edit_btn_text' => $this->entity->editBtnText,
                    'id' => $this->entity->ids,
                    'title' => $this->entity->subTitleEdit.' '.$this->entity->title,
                    'master_blade' => $this->entity->masterBlade,
                    'back_btn_text' => $this->entity->backBtnText,
                    'external_link' => $this->entity->externalLink,
                    'is_load_mce_libs' => $this->entity->isLoadMceLibs,
                    'errors' => Session::get('validate_errors')

                );
                $response = array_merge($response, $editResponse);

                break;
            case 'update':

                $updateResponse = array(
                    'allow_create' => $this->entity->allowCreate,
                    'validate_errors' => $this->entity->validateErrors,

                );
                $response = array_merge($response, $updateResponse);
                break;
            case 'read':

                $readResponse = array(
                    'title' => $this->entity->subTitleRead.' '.$this->entity->title,
                    'master_blade' => $this->entity->masterBlade,
                    'read_fields' => $this->entity->readFields,
                    'back_btn_text' => $this->entity->backBtnText,
                    'external_link' => $this->entity->externalLink,

                );
                $response = array_merge($response, $readResponse);
                break;

            case 'error':
                $response['error_text'] = $this->entity->errorText;
                $response['master_blade'] = $this->entity->masterBlade;
                break;

            case 'prepare_export':


                $response = array(
                    'action' => $this->entity->action,
                    'uri' => $this->entity->uri,
                    'status' => $this->entity->status,
                    'total' => $this->entity->exportTotal,
                    'paging' => $this->entity->exportPaging

                );

                return $response;

        }

//        \Debugbar::info($response);

        $this->entity->masterData['crud'] = $response;

        $this->entity->response = $this->entity->masterData;


        return $this->entity->response;
    }

    private function processOther($uri){

        $session = array();
        if(Session::has('crud-'.$uri)){
            $session = Session::get('crud-'.$uri);
            $this->entity->indexSession = $session;
            if(isset($session['order'])){
                $this->entity->orderByCustom = array($session['order'][0], $session['order'][1]);
            }
            if(isset($session['filter'])){
                $this->entity->filter = $session['filter'];
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
                        $this->entity->filter = $filter;
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

            $this->entity->action = $action;

            $run = $this->run();

            if($run === false){
                return 'on k';
            }

            if($this->entity->errorText != ''){
                return $this->entity->errorText;
            }

            $this->execute();

            if($this->entity->responseType == 'json'){
                return Response::json($this->getResponse());
            }

            if($this->entity->responseType == 'csv'){
                $headers = [
                    'Content-type'        => 'application/csv',
                    'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                    'Content-Disposition' => 'attachment; filename='.$this->uri.'-'.time().'.csv',
                    'Expires'             => '0',
                    'Pragma'              => 'public'
                ];

                $csv = $this->entity->csv;

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


        }

        return true;
    }

    /* ##################################### START PROTECTED METHOD ################################################ */

    protected function run(){
        return false;
    }


    /**
     * @param string $table
     * @param string $masterBlade
     * @param string $masterData
     */
    protected function init($table, $masterBlade = '', $masterData = ''){
        $this->entity->table = $table;
        $this->entity->masterBlade = $masterBlade;
        $this->entity->masterData = $masterData;

        $postData = Input::all();
        $this->entity->postCreateData = $postData;
        $this->entity->postUpdateData = $postData;

    }


    /**
     * @param $field
     * @param $newType
     * @param array $option
     * @param bool $renewOnUpdate
     */
    protected function changeType($field, $newType, $option = array()){
        $changeType = $this->entity->changeType;

        switch($newType){
            case 'readonly':
                $changeType[$field] = array(
                    'new_type' => $newType,
                    'is_readonly' => true
                );
                break;

            case 'money':
                $changeType[$field] = array(
                    'new_type' => $newType
                );
                break;

            case 'image':
                if(!isset($option['target_dir'])){
                    Log::alert('Could not change-type to IMAGE for field "'.$field.'" option TARGET_DIR not defined');
                    return false;
                }

                $path = public_path($option['target_dir']);

                $createDir = false;

                if(file_exists($path)){
                    if(!is_dir($path)){
                        $createDir = true;
                    }
                }else{
                    $createDir = true;
                }

                if($createDir){
                    try{
                        mkdir($path, 0755, true);
                    }catch (\Exception $ex){
                        Log::warning('failed to create dir '.$path);
                    }

                }

                if(!is_writable($path)){
                    Log::warning('apply new type for '.$field.' failed . Directory '.$option['target_dir'].' is not exist and or not writeable.');
                    break;
                }
                $changeType[$field] = array(
                    'new_type' => $newType,
                    'target_dir' => $option['target_dir']
                );
                break;

            case 'file':
                if(!is_array($option)){
                    break;
                }

                $path = public_path($option['dir']);

                $createDir = false;

                if(file_exists($path)){
                    if(!is_dir($path)){
                        $createDir = true;
                    }
                }else{
                    $createDir = true;
                }

                if($createDir){
                    try{
                        mkdir($path, 0755, true);
                    }catch (\Exception $ex){
                        Log::warning('failed to create dir '.$path);
                    }

                }

                if(!is_writable($path)){
                    Log::warning('apply new type for '.$field.' failed . Directory '.$option.' is not exist and or not writeable.');
                    break;
                }
                $changeType[$field] = array(
                    'new_type' => $newType,
                    'target_dir' => $option['dir']
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
                $this->entity->isLoadMceLibs = true;
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
                $renewOnUpdate = false;

                if(isset($option['value'])){
                    $value = $option['value'];
                }

                if(isset($option['allow_update'])){
                    $renewOnUpdate = $option['allow_update'];
                }


                $changeType[$field] = array(
                    'new_type' => $newType,
                    'default_value' => $value,
                    'renew_on_update' => $renewOnUpdate
                );
                break;

            case 'text':
                $value = '';
                $renewOnUpdate = false;

                if(isset($option['value'])){
                    $value = $option['value'];
                }

                if(isset($option['allow_update'])){
                    $renewOnUpdate = $option['allow_update'];
                }

                $changeType[$field] = array(
                    'new_type' => $newType,
                    'default_value' => $value,
                    'renew_on_update' => $renewOnUpdate
                );
                break;

        }

        $this->entity->changeType = $changeType;
    }


    /**
     * @param string $field
     * @param string $joinTable
     * @param string $joinField
     * @param array $arrayWhere
     */
    protected function setJoin($field, $joinTable, $joinField, $arrayWhere = array()){

        $this->entity->setJoin[$field] = array($joinTable, $joinField, $arrayWhere, 't'.$this->entity->tbCount);
        $this->entity->tbCount++;

        $newType = array(
            'new_type' => 'join',
            'related_field' => $joinField,
            'options' => array()
        );

        if($this->entity->action == 'create' or $this->entity->action == 'edit'){
            $newType['options'] = DB::table($joinTable)->select(array('id', $joinField))->limit(1000)->get();
        }

        $this->entity->changeType[$field] = $newType;
    }

    /**
     * @param array $arrNN
     */

    protected function setVisibleNN(array $arrNN){
        $this->entity->joinNNColumnTitle = $arrNN;
    }

    /**
     * @param string $columnName
     * @param string $joinField
     * @param string $joinTable
     * @param string $relationTable
     * @param string $fieldRel
     * @param string $joinFieldRel
     */

    protected function setJoinNN($columnName, $joinField, $joinTable, $relationTable, $fieldRel, $joinFieldRel){

        $this->entity->joinNN[] = array(
            'column_name' => $columnName,
            'join_field' => $joinField,
            'join_table' => $joinTable,
            'relation_table' => $relationTable,
            'field_rel' => $fieldRel,
            'join_field_rel' => $joinFieldRel
        );


    }


    /**
     * @param array $columns
     */
    protected function columns(array $columns){
        $this->entity->columns = $columns;
    }

    /**
     * @param array $fields
     */
    protected function fields(array $fields){
        $this->entity->fields = $fields;
    }

    /**
     * @param array $createFields
     */
    protected function createFields(array $createFields){
        $this->entity->createFields = $createFields;
    }

    /**
     * @param array $editFields
     */
    protected function editFields(array $editFields){
        $this->entity->editFields = $editFields;
    }

    /**
     * @param array $readFields
     */
    protected function readFields(array $readFields){
        $this->entity->readFields = $readFields;
    }

    /**
     * @param array $rules
     */
    protected function validateRules(array $rules){
        if(is_array($rules)){
            $this->entity->validateRules = $rules;
        }

    }

    /**
     * @param callable $callback
     * @return bool
     */
    protected function callbackBeforeUpdate(callable $callback){
        if($this->entity->action != 'update'){
            return false;
        }
        $result = $callback($this->entity->postUpdateData, $this->entity->ids);

        if($result === false){
            return $this->entity->status = false;
        }

        $this->entity->postUpdateData = $result;
        return false;
    }

    /**
     * @param callable $callback
     * @return bool
     */

    protected function callbackBeforeSave(callable $callback){
        if($this->entity->action != 'save'){
            return false;
        }
        $result = $callback($this->entity->postCreateData);

        if($result === false){
            return $this->entity->status = false;
        }

        $this->entity->postCreateData = $result;
        return false;
    }

    /**
     * @param array $masterData
     */
    protected function setMasterData(array $masterData){
        $this->entity->masterData = $masterData;
    }

    /**
     * @param string $masterBlade
     */
    protected function setMasterBlade($masterBlade){
        $this->entity->masterBlade = $masterBlade;
    }

    /**
     * @param $title
     * @param $class
     * @param $callbackUrl
     * @return bool
     */
    protected function addAction($title, $class, $callbackUrl){
        if($this->entity->action != 'index'){
            //return false;
        }

        $this->entity->actions[] = array(
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

        $this->entity->externalLink[] = $ext;

        return false;
    }


    /**
     * @param string $field
     * @param string $condition
     * @param string $value
     */
    protected function where($field, $condition, $value){
        $this->entity->arrWhere[] = array($field, $condition, $value);
    }

    /**
     * @param string $field
     * @param string $direction
     */

    protected function orderBy($field, $direction = 'asc'){
        $this->entity->orderBy = array($field, $direction);
    }

    protected function getDataType(){
        return $this->entity->dataType;
    }

    /**
     * @param string $columnName
     * @param callable $callback
     */

    protected function callbackColumn($columnName, callable $callback){
        $this->entity->customColumns[$columnName] = array(
            'callback' => $callback
        );
    }

    /**
     * @param string $columnName
     * @param string $displayAs
     */

    protected function displayAs($columnName, $displayAs){
        $this->entity->columnDisplay[$columnName] = $displayAs;
    }

    /**
     * @param array $fields
     */


    protected function readonlyFields(array $fields){
        $this->entity->readonlyFields = $fields;
    }

    /**
     * @param array $field
     */

    protected function setExportFilter(array $field){
        $this->entity->allowExport = true;
        $this->entity->exportFilter = $field;
    }

    protected function getAction(){
        return $this->entity->action;
    }

    protected function afterRun(){

    }

    /**
     * List, Create, Read, Edit, Delete, Search, eXport
     * @param string $permission
     */

    protected function disAllow($permission = 'LCREDSX'){
        $list = ['L', 'C', 'R', 'E', 'D', 'S', 'X'];
        foreach($list as $item){
            if(strpos($permission, $item) > -1){
                switch($item){
                    case 'C':
                        $this->entity->allowCreate = false;
                        break;

                    case 'E':
                        $this->entity->allowEdit = false;
                        break;

                    case 'R':
                        $this->entity->allowRead = false;
                        break;

                    case 'D':
                        $this->entity->allowDelete = false;
                        break;

                    case 'X':
                        $this->entity->allowExport = false;
                        break;

                    case 'S':
                        $this->entity->allowSearch = false;
                        break;


                }
            }
        }

    }

    /**
     * @param string $msg
     */

    protected function setErrorMsg($msg){
        $msg = (string)$msg;

        $this->entity->errorText = $msg;
    }

    /**
     * method setTitle
     *
     * @param string $title
     */

    protected function setTitle($title){
        $title = (string)$title;

        $this->entity->title = $title;
    }

    /* ##################################### END PROTECTED METHOD ################################################ */


    private function error($silent = false){
        if($silent){
            return Response::make($this->entity->errorText);
        }
        $this->entity->action = 'error';
        return View::make($this->view_path.'error', $this->getResponse());
    }

    public function index(){

        $uri = \Request::decodedPath();

        $this->entity->uri = $uri;
        $this->entity->action = 'index';

        $other = $this->processOther($uri);

        if($other !== true){
            return $other;
        }


        $run = $this->run();

        if($run !== true and $run !== false){
            return $run;
        }

        if($run === false){
            if($this->entity->errorText === null){
                $this->entity->errorText = 'no message';
            }
            return $this->error();
        }

        $this->execute();

        if($this->entity->abort === true){

            return $this->error();
        }

        $data = $this->getResponse();
        return View::make($this->view_path.'index', $data);

    }

    public function create(){
        $uri = \Request::decodedPath();
        $xuri = explode('/', $uri, -1);

        $uri = join('/', $xuri);


        $this->entity->uri = $uri;
        $this->entity->action = 'create';


        $run = $this->run();

        if($run !== true and $run !== false){
            return $run;
        }

        if($run === false){
            if($this->entity->errorText === null){
                $this->entity->errorText = 'no message';
            }
            return $this->error();
        }

        $this->execute();

        if($this->entity->abort === true){

            return $this->error();
        }


        $data = $this->getResponse();
        return View::make($this->view_path.'create', $data);
    }

    public function store(){
        $uri = \Request::decodedPath();
        $this->entity->uri = $uri;
        $this->entity->action = 'save';

        $run = $this->run();

        if($run !== true and $run !== false){
            return $run;
        }

        if($run === false){
            if($this->entity->errorText === null){
                $this->entity->errorText = 'no message';
            }
            return $this->error();
        }

        $this->execute();

        if($this->entity->abort === true){

            return $this->error();
        }

        $data = $this->getResponse();

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

//        $id = str_replace($uri.'/', '', $uri1);


        $this->entity->uri = $uri;
        $this->entity->action = 'read';
        $this->entity->ids = $id;

        $run = $this->run();

        if($run !== true and $run !== false){
            return $run;
        }

        if($run === false){
            if($this->entity->errorText === null){
                $this->entity->errorText = 'no message';
            }
            return $this->error();
        }

        $this->execute();

        if($this->entity->abort === true){

            return $this->error();
        }

        $response = $this->getResponse();
        return View::make($this->view_path.'read', $response);
    }



    public function edit($id){
        $uri1 = str_replace('/edit', '', \Request::decodedPath());
        $xuri = explode('/', $uri1, -1);

        $uri = join('/', $xuri);

//        $id = str_replace($uri.'/', '', $uri1);

        $this->entity->uri = $uri;
        $this->entity->action = 'edit';
        $this->entity->ids = $id;

        $run = $this->run();

        if($run !== true and $run !== false){
            return $run;
        }

        if($run === false){
            if($this->entity->errorText === null){
                $this->entity->errorText = 'no message';
            }
            return $this->error();
        }

        $this->execute();

        if($this->entity->abort === true){

            return $this->error();
        }
        return View::make($this->view_path.'edit', $this->getResponse());
    }



    public function update($id){

        $uri1 = \Request::decodedPath();
        $xuri = explode('/', $uri1, -1);

        $uri = join('/', $xuri);

//        $id = str_replace($uri.'/', '', $uri1);


        $this->entity->uri = $uri;
        $this->entity->action = 'update';
        $this->entity->ids = $id;

        $run = $this->run();

        if($run !== true and $run !== false){
            return $run;
        }

        if($run === false){
            if($this->entity->errorText === null){
                $this->entity->errorText = 'no message';
            }
            return $this->error();
        }

        $this->execute();

        if($this->entity->abort === true){

            return $this->error();
        }

        $data = $this->getResponse();

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

        $this->entity->uri = $uri;
        $this->entity->action = 'delete';
        $this->entity->ids = $id;

        $run = $this->run();

        if($run !== true and $run !== false){
            return $run;
        }

        if($run === false){
            if($this->entity->errorText === null){
                $this->entity->errorText = 'no message';
            }
            return $this->error();
        }

        $this->execute();

        if($this->entity->abort === true){

            return $this->error();
        }

        $this->getResponse();
        return Redirect::back()->with('message', 'Data berhasil di hapus.');
    }

}
