<?php

namespace Timenz\Crud;

use DB;
use Input;
use Session;
use Validator;

class CrudProcess{

    private $entity;

    public function __construct(CrudEntity $crudEntity){
        $this->entity = $crudEntity;
    }

    public function setDefaultColumn(){
        $dbName = DB::getDatabaseName();
        $schema = DB::select("select column_name, data_type, character_maximum_length, numeric_precision, numeric_scale, column_type
          from information_schema.columns where table_schema = ? and table_name = ?",
            array($dbName, $this->entity->table));

        $columns = array();
        $allColumns = array();

        if(count($schema) < 1){
            $this->entity->abort = true;
            $this->entity->errorText = 'Base table '.$this->entity->table.' not found';
            return false;
        }


        $this->populateField($schema);

        $dataType = $this->entity->dataType;

        foreach($dataType as $item){
            $allColumns[] = $item['column_name'];
            if($item['column_name'] == 'id'){
                continue;
            }
            $columns[] = $item['column_name'];
        }

        $this->entity->allColumns = $allColumns;


        if(count($this->entity->columns) < 1){
            $this->entity->columns = $columns;
        }

    }

    private function populateField($schema){

        $columnDisplay = $this->entity->columnDisplay;
        $columns = $this->entity->columns;


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
                'is_readonly' => false,
                'options' => array()
            );



            if(array_key_exists($item->column_name, $columnDisplay)){
                $dataColumn['column_text'] = $columnDisplay[$item->column_name];
            }



            switch($item->data_type){
                case 'int':
                    $dataColumn['max_length'] = (int)$item->numeric_precision;
                    $dataColumn['input_type'] = 'numeric';
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['equal','greater-than', 'less-than', 'between', 'contain'];
                    break;
                case 'varchar':
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['contain', 'equal'];

                    break;

                case 'text':
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['contain'];
                    $dataColumn['input_type'] = 'textarea';
                    break;

                case 'mediumtext':
                    $dataColumn['input_type'] = 'textarea';
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['contain'];
                    break;

                case 'longtext':
                    $dataColumn['input_type'] = 'textarea';
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['contain'];
                    break;

                case 'decimal':
                    $dataColumn['max_length'] = (int)$item->numeric_precision;
                    $dataColumn['input_type'] = 'decimal';
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['equal','greater-than', 'less-than', 'between', 'contain'];
                    break;

                case 'double':
                    $dataColumn['max_length'] = (int)$item->numeric_precision;
                    $dataColumn['input_type'] = 'decimal';
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['equal','greater-than', 'less-than', 'between', 'contain'];
                    break;

                case 'date':
                    $dataColumn['input_type'] = 'date';
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['date-equal','date-greater-than', 'date-less-than', 'date-between', 'contain'];
                    break;

                case 'datetime':
                    $dataColumn['input_type'] = 'datetime';
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['date-equal','date-greater-than', 'date-less-than', 'date-between', 'contain'];
                    break;

                case 'timestamp':
                    $dataColumn['input_type'] = 'datetime';
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['date-equal','date-greater-than', 'date-less-than', 'date-between', 'contain'];
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
                    $dataColumn['allow_search'] = true;
                    $dataColumn['search_condition'] = ['contain'];
                    break;


            }

            $dataColumn = $this->applyNewType($item->column_name, $dataColumn);

            $dataType[$item->column_name] = $dataColumn;


        }

        /**
         * give additional field that not from table a data-type
         */

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
                'options' => array(),
                'allow_search' => false,
            );

            $dataColumn = $this->applyNewType($item, $dataColumn);

            $dataType[$item] = $dataColumn;
        }

        $this->entity->dataType = $dataType;

    }


    /**
     * @param $columnName
     * @param $dataColumn
     * @return mixed
     */
    private function applyNewType($columnName, $dataColumn){
        $changeType = $this->entity->changeType;

        if(!array_key_exists($columnName, $changeType) ){
            return $dataColumn;
        }

        $dataColumn['input_type'] = $changeType[$columnName]['new_type'];

        switch($changeType[$columnName]['new_type']){
            case 'join':
                $dataColumn['related_field'] = $changeType[$columnName]['related_field'];
                $dataColumn['options'] = $changeType[$columnName]['options'];
                $dataColumn['allow_search'] = false;
                break;
            case 'enum':
                $dataColumn['options'] = $changeType[$columnName]['options'];
                $dataColumn['allow_search'] = true;
                $dataColumn['search_condition'] = ['contain'];
                break;
            case 'select':
                $dataColumn['options'] = $changeType[$columnName]['options'];
                $dataColumn['allow_search'] = false;
                break;
            case 'text':
                $dataColumn['default_value'] = $changeType[$columnName]['default_value'];
                $dataColumn['renew_on_update'] = $changeType[$columnName]['renew_on_update'];
                $dataColumn['allow_search'] = true;
                $dataColumn['search_condition'] = ['contain'];
                break;
            case 'image':
                $dataColumn['target_dir'] = $changeType[$columnName]['target_dir'];
                $dataColumn['allow_search'] = false;
                break;
            case 'file':
                $dataColumn['target_dir'] = $changeType[$columnName]['target_dir'];
                $dataColumn['allow_search'] = false;
                break;
            case 'hidden':
                $dataColumn['default_value'] = $changeType[$columnName]['default_value'];
                $dataColumn['renew_on_update'] = $changeType[$columnName]['renew_on_update'];
                $dataColumn['allow_search'] = true;
                $dataColumn['search_condition'] = ['contain'];
                break;
            case 'location':
                $dataColumn['allow_search'] = false;
                $dataColumn['search_condition'] = [];
                break;
        }

        return $dataColumn;
    }

    public function applyJoinNN(){


        if(count($this->entity->joinNN) < 1){
            return false;
        }


        $action = $this->entity->action;
        $joinNNColumn = array();
        //$joinNNColumnTitle = array();
        $joinNNOption = array();
        $insertNN = array();
        $delNN = array();

        foreach($this->entity->joinNN as $join){
            //$joinNNColumnTitle[] = $join['column_name'];
            if($action == 'index'){
                foreach($this->entity->lists as $item){
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
                    ->where($join['relation_table'].'.'.$join['field_rel'], '=', $this->entity->ids)
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



        $this->entity->joinNNColumn = $joinNNColumn;
        //$this->joinNNColumnTitle = $joinNNColumnTitle;
        $this->entity->joinNNOption = $joinNNOption;
        $this->entity->insertNN = $insertNN;
        $this->entity->delNN = $delNN;

        return false;
    }




    /**
     * @return bool
     */
    public function actionIndex($action = 'index'){
        $selected = array('t0.*');


        $lists = DB::table($this->entity->table.' as t0');

        foreach($this->entity->arrWhere as $item){
            $lists->where($item[0], $item[1], $item[2]);
        }

        if($this->entity->filter != null){
            foreach($this->entity->filter as $key=>$item){
                switch($item[0]){
                    case 'equal':
                        $lists->where('t0.'.$key, '=', $item[1]);
                        break;

                    case 'greater-than':
                        $lists->where('t0.'.$key, '>', $item[1]);
                        break;

                    case 'less-than':
                        $lists->where('t0.'.$key, '<', $item[1]);
                        break;

                    case 'contain':
                        $lists->where('t0.'.$key, 'like', '%'.$item[1].'%');
                        break;

                    case 'date-equal':
                        $lists->whereRaw("date(`t0`.`$key`) = ?", [$item[1]]);
                        break;

                    case 'date-greater-than':
                        $lists->whereRaw("date(`t0`.`$key`) > ?", [$item[1]]);
                        break;

                    case 'date-less-than':
                        $lists->whereRaw("date(`t0`.`$key`) < ?", [$item[1]]);
                        break;

                    case 'date-between':
                        $lists->whereRaw("date(`t0`.`$key`) between ? and ?", [$item[1], $item[2]]);
                        break;

                    case 'between':
                        $lists->whereBetween('t0.'.$key, [$item[1], $item[2]]);
                        break;

                }
            }
        }


        foreach($this->entity->setJoin as $key=>$item){
            $selected[] = $item[3].'.'.$item[1];
            $lists->leftJoin($item[0].' as '.$item[3] , 't0.'.$key, '=', $item[3].'.id');
        }

        $lists->select($selected);

        if($this->entity->orderBy != null and $this->entity->orderByCustom == null){
            $order = $this->entity->orderBy;
            $lists->orderBy($order[0], $order[1]);
        }

        if($this->entity->orderByCustom != null){
            $order = $this->entity->orderByCustom;
            $lists->orderBy($order[0], $order[1]);
        }

        if($action == 'export'){
            $page = Input::get('page');
            $this->entity->csv = $lists->limit($this->entity->exportMaxLimit)->offset($page)->get();
            return false;
        }

        if($action == 'prepare_export'){
            return $lists->count();
        }

        $lists = $lists->paginate($this->entity->perPage);




        if($lists == null){
            return false;
        }

        $this->entity->pagingLinks = (string)$lists->render();
//        $this->entity->pagingLinks = '';


        foreach ($lists as $item) {
            $this->setActions($item);
            $this->setCustomColumns($item);
        }

        $this->entity->lists = $lists;

        return true;
    }


    /**
     * @param $row
     */
    private function setActions($row){

        foreach ($this->entity->actions as $item) {
            $url = $item['callback_url']($row, $row->id);

            if(!$url){continue;}

            $this->entity->actionLists[$row->id][] = array(
                'title' => $item['title'],
                'class' => $item['class'],
                'url' => $url,
            );
        }

    }

    private function setCustomColumns($row){
        foreach($this->entity->customColumns as $key=>$item){
            isset($row->{$key}) ? $keyVal = $row->{$key} : $keyVal = null;
            $value = $item['callback']($row, $keyVal);
            if(!$value){continue;}


            $this->entity->customValues[$key][$row->id] = $value;
        }
    }

    public function actionCreate(){
        $this->initCreateFields();
    }

    private function initCreateFields(){
        $createFields = array();
        if(count($this->entity->createFields) < 1 and count($this->entity->fields) > 0){
            $createFields = $this->entity->fields;
        }else if(count($this->entity->createFields) < 1){
            $createFields = $this->entity->allColumns;
        }

        $dataType = $this->entity->dataType;

        if($this->entity->joinNN != null){
            foreach($this->entity->joinNN as $item){
                $createFields[] = $item['column_name'];
                $options = array();
                if($this->entity->action == 'create'){
                    $options = $this->entity->joinNNOption[$item['column_name']];
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

        $this->entity->createFields = $createFields;
        $this->entity->dataType = $dataType;


    }

    /**
     *
     */
    private function initReadFields(){

        $readFields = $this->entity->allColumns;
        
        if(count($this->entity->fields) > 0){
            $readFields = $this->entity->fields;
        }

        if(count($this->entity->readFields) > 0){
            $readFields = $this->entity->readFields;
        }
        $dataType = $this->entity->dataType;

        if($this->entity->joinNN != null){
            foreach($this->entity->joinNN as $item){
                $readFields[] = $item['column_name'];
                $options = array();
                if($this->entity->action == 'edit'){
                    $options = $this->entity->joinNNOption[$item['column_name']];
                }

                $values = DB::table($item['relation_table'])
                    ->select($item['join_table'].'.'.$item['join_field'].' as value')
                    ->where($item['field_rel'], '=', $this->entity->ids)
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

        $this->entity->readFields = $readFields;
        $this->entity->dataType = $dataType;

    }


    /**
     * @return bool
     */
    public function actionSave(){
        $this->initCreateFields();
        $createFields = $this->entity->createFields;


        $insertData = array();
        $validateData = array();
        $postData = $this->entity->postCreateData;

        foreach($createFields as $item){
            if($item == 'id'){continue;}
            if(!isset($postData[$item])){continue;}
            $value = $postData[$item];

            $changeType = $this->entity->changeType;

            if(isset($changeType[$item])){
                $type = $changeType[$item];

                if($type['new_type'] == 'image'){
                    $val = $this->uploadFile($item, $type['target_dir']);
                    if(!$val){continue;}else{$value = $val;}
                }

                if($type['new_type'] == 'file'){
                    $val = $this->uploadFile($item, $type['target_dir'], 'insert', 'file');
                    if(!$val){continue;}else{$value = $val;}
                }


            }


            $validateData[$item] = $value;

            if($this->entity->dataType[$item]['input_type'] == 'join_nn'){
                continue;
            }

            $insertData[$item] = $value;
        }

        if($this->entity->status == false){
            return false;
        }

        $status = false;
        $valid = true;


        if(count($this->entity->validateRules) > 0){
            $validator = Validator::make($insertData, $this->entity->validateRules);
            if($validator->fails()){
                $this->entity->validateErrors = $validator->messages()->toArray();
                $valid = false;
            }
        }


        if($this->entity->allowCreate and $valid){
            $insertNN = $this->entity->insertNN;
            $status = DB::transaction(function() use($insertData, $insertNN) {
                $insertId = DB::table($this->entity->table)->insertGetId($insertData);

                if($insertNN != null){
                    $bulkInsert = array();
                    $table = '';
                    foreach($insertNN as $item){
                        foreach($item['values'] as $val){
                            $bulkInsert[] = array(
                                $item['field_rel'] => $insertId,
                                $item['join_field_rel'] => $val
                            );
                        }

                        $table = $item['relation_table'];

                    }


                    if(count($bulkInsert) > 0){
                        DB::table($table)->insert($bulkInsert);
                    }

                }

                return true;
            });

        }

        $this->entity->status = $status;
        return false;
    }


    /**
     * @return bool
     */
    public function getOneRow(){

        $selected = array();

        foreach($this->entity->allColumns as $item){
            $selected[] = 't0.'.$item;
        }


        if($this->entity->ids < 1){
            return false;
        }

        //$row = $this->find($this->ids);
        $row = DB::table($this->entity->table.' as t0')->select('t0.*');
        foreach($this->entity->setJoin as $key=>$item){
            $selected[] = $item[3].'.'.$item[1];
            $row->leftJoin($item[0].' as '.$item[3], 't0.'.$key, '=', $item[3].'.id');

        }
        $row->select($selected);

        $row = $row->where('t0.id', '=', $this->entity->ids)->first();

        if($row == null){
            return false;
        }

        $row = (array)$row;

        $dataType = $this->entity->dataType;

        foreach($dataType as $key=>$item){
            if($dataType[$key]['input_type'] == 'join' and $this->entity->action != 'edit'){

                $dataType[$key]['value'] = $row[$dataType[$key]['related_field']];

            }else{
                $dataType[$key]['value'] = $row[$key];
            }

        }



        $this->entity->dataType = $dataType;
        $this->entity->row = $row;
        return false;
    }

    /**
     *
     */
    public function actionEdit(){
        $this->initEditFields();
    }


    /**
     *
     */
    private function initEditFields(){


        if(count($this->entity->editFields) < 1 and count($this->entity->fields) > 0){
            $editFields = $this->entity->fields;
        }else if(count($this->entity->editFields) < 1){
            $editFields = $this->entity->allColumns;
        }else{
            $editFields = $this->entity->editFields;
        }

        $dataType = $this->entity->dataType;

        if($this->entity->joinNN != null){
            foreach($this->entity->joinNN as $item){
                $editFields[] = $item['column_name'];
                $options = array();
                if($this->entity->action == 'edit'){
                    $options = $this->entity->joinNNOption[$item['column_name']];
                }

                $values = DB::table($item['relation_table'])
                    ->select($item['relation_table'].'.'.$item['join_field_rel'].' as id')
                    ->where($item['field_rel'], '=', $this->entity->ids)
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
                    'is_readonly' => false,
                    'options' => $options,
                    'value' => $value
                );

            }


        }

        if(count($this->entity->readonlyFields) > 0){
            foreach ($dataType as $key=>$item) {
                if(in_array($item['column_name'], $this->entity->readonlyFields)){
                    $dataType[$key]['is_readonly'] = true;
                }
            }

        }


        $this->entity->editFields = $editFields;
        $this->entity->dataType = $dataType;
    }



    /**
     * @return bool
     */
    public function actionUpdate(){

        $this->initEditFields();

        $editFields = $this->entity->editFields;

        $updateData = array();
        $validateData = array();
        $postData = $this->entity->postUpdateData;
        $changeType = $this->entity->changeType;

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
                    if(!$val){}else{$value = $val;}
                }

                if($type['new_type'] == 'file'){

                    $val = $this->uploadFile($item, $type['target_dir'], 'update', 'file');
                    if(!$val){}else{$value = $val;}
                }
            }


            $validateData[$item] = $value;

            if($this->entity->dataType[$item]['input_type'] == 'join_nn'){
                continue;
            }

            $updateData[$item] = $value;
        }

        //$id = Input::get('id');

        if(count($updateData) < 1){
            return false;
        }

        if($this->entity->status == false){
            return false;
        }

        $status = false;
        $valid = true;

        $validateRules = $this->entity->validateRules;

        if(count($this->entity->readonlyFields) > 0){
            foreach ($this->entity->readonlyFields as $item) {
                unset($validateRules[$item]);
            }

        }

        if(count($validateRules) > 0){
            $validator = Validator::make($validateData, $validateRules);
            if($validator->fails()){
                $this->entity->validateErrors = $validator->messages()->toArray();
                $valid = false;
            }
        }

        if($this->entity->allowEdit and $valid){
            $insertNN = $this->entity->insertNN;

            $insertId = $this->entity->ids;
            $dataType = $this->entity->dataType;
            $status = DB::transaction(function() use($updateData, $insertNN, $insertId, $dataType) {

                DB::table($this->entity->table)->where(array('id' => $this->entity->ids))->update($updateData);

                if($insertNN != null){


                    $bulkInsert = array();
                    $table = '';

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

                        $table = $item['relation_table'];


                    }


                    if(count($bulkInsert) > 0){
                        DB::table($table)->insert($bulkInsert);
                    }

                }


                if($this->entity->delNN != null){
                    foreach($this->entity->delNN as $key=>$del){

                        if(isset($this->entity->dataType[$key])){
                            if(count($this->entity->dataType[$key]['value']) > 0){
                                foreach($this->entity->dataType[$key]['value'] as $val){
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
                                            $del['field_rel'] => $this->entity->ids,
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





        $this->entity->status = $status;
        return false;
    }

    /**
     *
     */
    public function actionRead(){
        $this->initReadFields();
    }



    /**
     *
     */
    public function actionDelete(){
        if($this->entity->allowDelete){
            foreach($this->entity->changeType as $key=>$item){
                if($item['new_type'] == 'image' or $item['new_type'] == 'file'){
                    $targetDir = public_path($item['target_dir']);
                    $row = $this->entity->row;
                    $oldFile = $targetDir.'/'.$row[$key];

                    if(file_exists($oldFile) and is_file($oldFile)){
                        if(is_writable($oldFile)){
                            unlink($oldFile);
                        }
                    }
                }
            }

            $deleteNN = null;

            if($this->entity->joinNN != null){


                foreach($this->entity->joinNN as $item){


                    $x = DB::table($item['relation_table'])
                        ->select($item['relation_table'].'.id')
                        ->where($item['field_rel'], '=', $this->entity->ids)
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

                DB::table($this->entity->table)->delete($this->entity->ids);
            });



        }

    }



    public function actionPrepareExport(){

//        $row = DB::table($this->entity->table)->selectRaw("count(*) as aggregate");
//
//        if(Session::has('crud-'.$this->entity->uri)){
//            $session = Session::get('crud-'.$this->entity->uri);
//
//            if(isset($session['export-from']) and isset($session['export-to']) and $this->entity->exportFilter != null){
//                $row->whereBetween($this->entity->exportFilter, array($session['export-from'], $session['export-to']));
//            }
//        }
//
//        $row = $row->first();
//
//        $total = $row->aggregate * 1;

        $total = $this->actionIndex('prepare_export');

        $paging = true;

        if($total > $this->entity->exportMaxLimit){
            $paging = true;
        }

        $this->entity->exportTotal = $total;
        $this->entity->exportPaging = $paging;

        $this->entity->responseType = 'json';
    }

    public function actionExport(){

        $this->actionIndex('export');

        $rows = $this->entity->csv;

        $fields = $this->entity->exportFields;

        if($fields === null){
            $fields = $this->entity->columns;
        }

        $out = [];

        foreach($rows as $item){
            $data = [];

            foreach($fields as $field){
                if(isset($item->$field)){
                    $data[$field] = $this->parseExportField($field, $item);
                }
            }

            $out[] = $data;
        }

        $this->entity->csv = $out;

        $this->entity->responseType = 'csv';
    }

    private function parseExportField($field, $item){
        if(!isset($this->entity->dataType[$field])){
            return $item->$field;
        }

        $type = $this->entity->dataType[$field];

        if($type['input_type'] == 'join'){
            return $item->{$type['related_field']};
        }

        return $item->$field;
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
                    $row = $this->entity->row;
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


}