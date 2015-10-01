## Crud Library for Laravel

Adalah crud library untuk laravel, almost awesome .

### Install

Tambahkan variabel berikut pada composer

	{
		"require": {
		  	"timenz/crud": "dev-master",
            "pqb/filemanager-laravel": "1.*" 
		},
	    "repositories": {
		  	"vcs_crud": {
			  "type": "vcs",
			  "url": "https://bitbucket.org/timenz/laravel-crud-package.git"
			}
	    },
	}
	
	// follow additional library readme
	// pqb/filemanager-laravel, use if you goin to implement richarea

trus run

    composer update

add to $app['providers']

	'Timenz\Crud\CrudServiceProvider',

publish view

	php artisan view:publish timenz/crud

publish view on-dev

	php artisan view:publish --path="workbench/timenz/crud/src/views" timenz/crud
	
Javascript Libs Dependency

	{
    	"bootstrap": "~3.3.0",
    	"tinymce": "~4.1",
    	"bootstrap-datepicker": "1.3.0",
        "chosen": "~1.4.2"
    }

### Mininal code example


buat route

	Route::resource('ok', 'TestCrud');
	
code taruh sembarang asal kebaca composer

	<?php
    
    use \Timenz\Crud\Crud;
    
    class TestCrud extends Crud{
    
        protected function run(){
            $this->init('users');
    
            $master = array('title' => 'my');
            $this->setMasterData($master);
            $this->setMasterBlade('admin.master');
        }
    }
    
## Dokumentasi

### init
[wajib] Inisialisasi tabel utama
	
	// parameter => nama tabel
	$this->init('users');

### setMasterBlade
[wajib]View bawaan tidak bisa langsung tampil, harus nunut ke view utama, ato kalo crudnya pengen uniq, buat aja sub-view yang bisa dicustom, sebelum extend ke view utama.
	
	// parameter => nama blade yang akan diextend
	$this->setMasterBlade('admin.master');
	
### setMasterData
[wajib] Karena load datanya dari crud, maka crud minta data yang dibutuhin master view, agar nanti bisa dikirim bareng.
Note : ada reserved parameter => 'crud', jadi jangan gunakan parameter tsb, di master view nya.

	$master = array('title' => 'my');
	// parameter => array data master
	$this->setMasterData($master);

### title
set crud title

	// default => ''
	$this->title = 'Judul Crud';
	$this->allowDelete = false;
	$this->allowEdit = false;
	$this->allowRead = false;
	$this->allowCreate = false;
	
### allowCreate
bolehin buat data baru apa enggak
	
	// default => true
	$this->allowCreate = false;
	
### allowRead
bolehin baca data apa enggak
	
	// default => true
	$this->allowRead = false;
	
### allowEdit
bolehin edit data apa enggak
	
	// default => true
	$this->allowEdit = false;
	
### allowDelete
bolehin hapus data apa enggak
	
	// default => true
	$this->allowDelete = false;0
	

### columns
Menspesifikasikan field-field yang akan ditampilkan pada list data
	
	// parameter => array column yang ingin ditampilkan di list data
	$this->columns(array(
		'id_karyawan',
		'username',
		'id_role',
	));

### validateRules
Menspesifikasikan validasi form
	
	$this->validateRules(array(
		'id_karyawan' => 'required',
	));
	
### fields
Menspesifikasikan field-field yang akan tampil di form create, edit dan read

	$this->fields(array(
		'id_karyawan',
		'username',
		'id_role',
	));
	
### displayAs
Modifikasi nama kolom

	$this->displayAs('id_airlines', 'Maskapai');
	
### createFields
Menspesifikasikan field-field yang akan tampil di form create saja

	$this->createFields(array(
		'id_karyawan',
		'username',
		'id_role',
	));
	
### editFields
Menspesifikasikan field-field yang akan tampil di form edit saja

	$this->editFields(array(
		'id_karyawan',
		'username',
		'id_role',
	));
	
### readFields
Menspesifikasikan field-field yang akan tampil di halaman read saja

	$this->readFields(array(
		'id_karyawan',
		'username',
		'id_role',
	));
	
### where
mysql where untuk filter data utama
	
	// parameter => 1. nama field, 2. kondisi ('=', '!=', '>', dst), 3. nilai
	where('nama_field', '=', 'dia');

### setJoin
join tabel utama dengan tabel lain
	
	// parameter
	// 1. nama field yang akan dijoinkan
	// 2. join ke tabel apa
	// 3. nama field di tabel ke dua
	// 4. array where lainnya
	$this->setJoin('id_karyawan', 'karyawan', 'nama');

### orderBy
Pengurutan pada list data
	
	// parameter 1. nama field 2. sort (asc/desc)
	$this->orderBy('nama_field', 'asc');


### changeType

buat ngganti tipe field dari yang standard, parameter (nama field, tipe field baru, option), berikut detail list pilihan tipe field:

	
#### money
ngubah tipe field jadi bentuk money => 100.000,00
	
	// syarat yang diubah harus numeric
	$this->changeType('nama_field', 'money');

#### textarea
ngubah jadi textarea

	$this->changeType('nama_field', 'textarea');
	
#### enum
ngubah jadi pilihan dropdown
	
	// param 3 adl pilihan dropdown-nya
	$this->changeType('nama_field', 'enum', array('satu', 'dua'));
	
#### select
Dropdown juga cuman beda bentuk array-nya
	
	$option = array(
		'1' => 'satu',
		'2' => 'dua'
	);
	$this->changeType('nama_field', 'enum', $option);

#### hidden
Menyembunyikan field
	
	// param 3 adl isian default waktu insert
	$this->changeType('nama_field', 'hidden', 'ndelik');
	

#### image
buat field gambar

	$this->changeType('nama_field', 'image', 'image/path-in-public');

#### file
buat field gambar

	$this->changeType('nama_field', 'file', ['dir' => 'image/path-in-public']);

### callbackColumn
Custom format kolom field pada list
	
	
	$this->callbackColumn('nama_field', function($row, $val){
		return number_format($val);
	});


### callbackBeforeSave
Melakukan operasi, atau untuk mengubah input data, sebelum data disimpan, sewaktu membuat data baru

	$this->callbackBeforeSave(function($post_data){
		
		//encript password sebelum disimpan ke db
		$post_data['password'] = Hash::make($post_data['password']);
		return $post_data;
	});
          

### callbackBeforeUpdate
Melakukan operasi, atau untuk mengubah input data, sebelum data disimpan, sewaktu meng-edit data

	$this->callbackBeforeUpdate(function($post_data){
		
		//encript password sebelum disimpan ke db
		$post_data['password'] = Hash::make($post_data['password']);
		return $post_data;
	});

### addAction
tambahan aksi selain edit, baca, delete
	
	// parameter => 1. judul aksi, 2. kelas link aksi, 3. url
	// input pada callback => 1. data per row, 2. id
	addAction('Aksi Khusus', 'btn', function($row_data, $id){
		return url('aksi/'.$id);
	})
	
### addExternalLink
tambahan link statis
	
	// Parameter 1. title, 2. link url, dst dibawah beserta isi defaultnya
	//$class = 'btn btn-default', $openNewPage = false, $showAtIndex = true, $showAtRead = true, $showAtCreate = true, $showAtEdit = true
	addExternalLink('judul', url('link'), 
	