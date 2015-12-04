<?php

namespace Timenz\Crud;

class CrudEntity{
    public $allowCreate = true;
    public $allowRead = true;
    public $allowDelete = true;
    public $allowEdit = true;
    public $allowMassDelete = false;
    public $allowMultipleSelect = false;
    public $allowExport = true;
    public $exportMaxLimit = 5000;
    public $exportFields;
    public $allowSearch = true;
    public $allowOrder = true;
    public $columnDisplay = array();
    public $title = 'Crud';
    public $errorText;
    public $log;
    public $abort = false;

    public $orderBy;
    public $dataType = array();
    public $action = null;
    public $uri;
    public $actions = array();
    public $actionLists = null;
    public $table = '';
    public $columns = array();
    public $allColumns = array();
    public $customColumns = array();
    public $customValues = array();
    public $fields = array();
    public $createFields = array();
    public $editFields = array();
    public $readFields = array();
    public $readonlyFields = array();
    public $validateRules = array();
    public $validateErrors = array();
    public $lists = array();
//    public $crudField;
    public $perPage = 20;
    public $ids = 0;
    public $response = array();
    public $row = array();
    public $changeType = array();
    public $setJoin = array();
    public $status = true;
    public $pagingLinks;
    public $postCreateData = array();
    public $postUpdateData = array();
    public $masterData;
    public $arrWhere = array();
    public $masterBlade = '';
    public $externalLink = array();
    public $orderByCustom;
    public $responseType;
    public $tbCount = 1;
    public $indexSession = array();
    public $filter;
    public $exportFilter;
    public $isLoadMceLibs = false;
    public $isLoadMapLibs = false;


    public $joinNN;
    public $joinNNColumn;
    public $joinNNColumnTitle = array();
    public $joinNNOption;
    public $insertNN;
    public $delNN;


    public $exportTotal;
    public $exportPaging;

    public $csv;
    
}