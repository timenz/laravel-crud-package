## Laravel Admin Crud

Laravel Controller extended, to easier create crud page.

### Limitation and Requirement
- Laravel 5.1
- Mysql/Mariadb database
- Bootstrap css framework
- only 1 crud on one page

### Install

Add to composer.json

	{
		"require": {
            "timenz/crud": "dev-master"
		}
	}

then update

    composer update

add to $app['providers']

    \Timenz\Crud\CrudServiceProvider::class,
    \Pqb\FilemanagerLaravel\FilemanagerLaravelServiceProvider::class,
    \KevBaldwyn\Image\Providers\Laravel\ImageServiceProvider::class,

publish view and asset

	php artisan vendor:publish

	
Javascript Libs Dependency

	{
    	"bootstrap": "~3.3.0",
    	"bootstrap-datepicker": "1.3.0",
        "chosen": "~1.4.2"
    }

Master blade view, this view basically from bootstrap sample page.

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
    
        <title>Crud Master Example</title>
    
        <link href="{{ asset('libs/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('libs/bootstrap-datepicker/css/datepicker3.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/bootstrap-chosen.css') }}" rel="stylesheet">
    
        <link href="{{ asset('assets/css/navbar-fixed-top.css') }}" rel="stylesheet">
    
        @yield('crud_css')
    
    </head>
    
    <body>
    
    
    
    <div class="container-fluid">
    
        @yield('crud_konten')
    
    </div>
    
    <script src="{{ asset('libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('libs/chosen/chosen.jquery.min.js') }}"></script>
    <script src="{{ asset('libs/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>
    
    @yield('crud_js')
    </body>
    </html>



### Mininal code example


create resource route

	Route::resource('article', 'ArticleCrud');
	
then create controller

	<?php
    
    namespace App\Http\Controllers;
    
    use Timenz\Crud\Crud;
    
    class ArticleCrud extends Crud{
    
        protected function run(){
            $this->init('article', 'crud_master', []);
    
            return true;
        }
    }
    

## Documentation

### init
[wajib] Main init
	
	// parameter => table name, master blade and parameter for master blade
	$this->init('users');

### title
set crud title
    //parameter => crud title
	$this->setTitle('Article Crud');
	
### disAllow
Limit permission of crud
    
    //parameter L = list
    $this->disAllow('LCREDSXO');

### columns
Specify fields in listing at index page.
	
	//parameter => array of fields 
	$this->columns(array(
		'title',
	));

### validateRules
Specify validate rules, rules that used in this validation is same as default laravel validation rules 
and you can follow reference from [laravel documentation]( https://laravel.com/docs/5.1/validation#available-validation-rules)
	
	$this->validateRules(array(
		'title' => 'required',
	));
	
### fields
Specify fields at create, edit dan read page

	$this->fields(array(
		'title',
	));
	
### displayAs
Modify title of field

	$this->displayAs('title', 'The Title');
	
### createFields
Specify fields at create page

	$this->createFields(array(
		'title',
	));
	
### editFields
Specify fields at edit page

	$this->editFields(array(
		'title',
	));
	
### readFields
Specify fields at  read page

	$this->readFields(array(
		'title',
	));
	
### where
Filters visible data by using sql where
	
	//parameter => field, condition, value
	$this->where('id', '=', 1);

### setJoin
Set a 1-n database relationship, this function only can be use for relation that refers to another
table on field 'id', example field 'id_user' from initial table refers to field 'id' on target table.
	
	// parameter
	// 1. field from initial table that refers to target table
	// 2. another table that want to join to
	// 3. field name from refered table that want to set visible on initial field
	// 4. 'where' array to limit join data
	$this->setJoin('id_user', 'users', 'full_name');

### orderBy
Set order listing data
	
	//parameter 1. field name, 2. sort (asc/desc)
	$this->orderBy('id', 'asc');




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
	
### changeType

By default package will set the field type by type at table definition, but we can change the field type
to another custom field type.
This function has 3 parameter.

1. Field name
2. Change type
3. Change type option in array

To avoid coding error, I have created two classes to specify available change type 
and change type option parameter.

#### ChangeTypeOption
This is third parameter of changeType function, here some available ChangeType option :
- **value** or ChangeTypeOption::VALUE, default value of field, for example you can see hidden field type
- **allow_update**, if you are not specify this option to true, then the edit page will not
 change this field value
- **target_dir**, used for *image* and *file* to set directory to save the file in public directory 
- **select_option**, list of option for *enum* and *select* type of field

#### hidden
hide the field
	
	$this->changeType('field_name', 'hidden', ['value' => 1]);	
	//or
	$this->changeType('field_name', ChangeType::HIDDEN, [ChangeTypeOption::VALUE => 1]);

available option is 'value' and

#### money
field type with number formated display
	
	$this->changeType('field_name', 'money');

#### textarea

	$this->changeType('field_name', 'textarea');
	
#### enum
dropdown/select field type
	
	$this->changeType('field_name', 'enum', ['select_option' => ['yes', 'no'] ]);
	
#### select
dropdown/select field type
	
	$option = array(
		'0' => 'yes',
		'1' => 'no'
	);
	$this->changeType('nama_field', 'enum', $option);
	$this->changeType('field_name', 'enum', ['select_option' => ['yes', 'no'] ]);


#### image
buat field gambar

	$this->changeType('nama_field', 'image', 'image/path-in-public');

#### file
buat field gambar

	$this->changeType('nama_field', 'file', ['dir' => 'image/path-in-public']);
	