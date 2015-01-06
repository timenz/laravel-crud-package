<?php

class CrudCtl extends Controller{

    private $modelName;
    private $modelNameOri;
    private $methodName;
    private $methodNameOri;
    private $model;
    private $view_path = 'packages.timenz.crud.';

    private function setModel($model, $method, $action, $id = 0){
        $this->modelNameOri = $model;
        $xModel = explode('_', $model);
        $this->modelName = implode('', array_map('ucfirst', $xModel));

        $this->methodNameOri = $method;
        $xMethod = explode('_', $method);
        $this->methodName = implode('', array_map('ucfirst', $xMethod));

        $modelName = $this->modelName;
        $methodName = $this->methodName;

        if(!class_exists($modelName)){
            return 'page not found';
        }

        $objModel = new $modelName();
        $objModel->load($model, $method, $action, $id);

        //$page = Input::get('page') ?: 0;
        //$model->page = $page;


        $status = $objModel->$methodName();

        if($status == false){
            return false;
        }

        $this->model = $objModel;
        return true;
    }

    public function index($model, $method){
        $set = $this->setModel($model, $method, 'index');

        if($set == false){
            return $this->notValid();
        }

        $data = $this->model->getResponse();

        return View::make($this->view_path.'index', $data);
    }

    public function create($model, $method){

        $set = $this->setModel($model, $method, 'create');
        if($set == false){
            return $this->notValid();
        }

        $data = $this->model->getResponse();


        return View::make($this->view_path.'create', $data);
    }

    public function save($model, $method){
        $set = $this->setModel($model, $method, 'save');
        if($set == false){
            return $this->notValid();
        }
        $data = $this->model->getResponse();

        //return 'ok';

        if($data['status'] === false){

            return Redirect::back()
                ->withInput()
                ->with('validate_errors', $data['validate_errors'])
                ->with('message', 'There were validation errors.');
        }

        $msg = 'Data gagl disimpan';

        if($data['status']){
            $msg = 'Data berhasil disimpan.';
        }

        return Redirect::to('crud/'.$model.'/'.$method)->with('message', $msg);
    }

    public function edit($model, $method, $id){
        $set = $this->setModel($model, $method, 'edit', $id);
        if($set == false){
            return $this->notValid();
        }
        $data = $this->model->getResponse();
        //DB::commit();
        return View::make($this->view_path.'edit', $data);

    }

    public function update($model, $method, $id){
        $set = $this->setModel($model, $method, 'update', $id);
        if($set == false){
            return $this->notValid();
        }
        $data = $this->model->getResponse();

        if($data['status'] === false){

            return Redirect::back()
                ->withInput()
                ->with('validate_errors', $data['validate_errors'])
                ->with('message', 'There were validation errors.');
        }
        //return 'ok';

        $msg = 'Data gagl diupdate';

        if($data['status']){
            $msg = 'Data berhasil diupdate.';
        }

        return Redirect::to('crud/'.$model.'/'.$method)->with('message', $msg);
    }



    public function read($model, $method, $id){
        $set = $this->setModel($model, $method, 'read', $id);
        if($set == false){
            return $this->notValid();
        }
        $data = $this->model->getResponse();

        return View::make($this->view_path.'read', $data);
    }

    public function delete($model, $method, $id){

        $set = $this->setModel($model, $method, 'delete', $id);
        if($set == false){
            return $this->notValid();
        }
        $data = $this->model->getResponse();

        return Redirect::back()->with('message', 'Data berhasil di hapus.');
    }

    private function notValid(){
        return 'page not found';
    }

}