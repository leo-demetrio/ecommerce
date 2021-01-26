<?php 
session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User; 
use \Hcode\Model\Category; 

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {

	$page = new Page();

	$page->setTpl("index");
	//chama o footer no destruct automaticamente
   
});

$app->get('/admin', function() {
	
	User::verifyLogin();
	
	$page = new PageAdmin();
	
	$page->setTpl("index");
   
});
//login
$app->get('/admin/login', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");
   
});

//login
$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);
	
	
	header("Location: /admin");
	exit;
});

$app->get('/admin/logout', function(){

	User::logOut();

	header("Location: /admin/login");
	exit;

});

$app->get("/admin/users", function(){

	//User::verifyLogin();

	$users = User::listAll();
	
	$page = new PageAdmin();

	$page->setTpl("users", compact("users"));

});
//criar
$app->get("/admin/users/create", function(){

	//User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});

//delete
$app->get("/admin/users/:iduser/delete", function($iduser){

	//User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

//update
$app->get("/admin/users/:iduser", function($iduser){
	
	//User::verifyLogin();
	
	
	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);


	$page = new PageAdmin();

	$page->setTpl("users-update",["user" => $user->getValues()] );

});
//criar
$app->post("/admin/users/create", function(){

	//User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
});
//update
$app->post("/admin/users/:iduser", function($iduser){

	//User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;

});

$app->get("/admin/forgot", function() {

	$page = new PageAdmin([
			"header"=>false,
			"footer"=>false
	]);

	$page->setTpl("forgot");

});

$app->post("/admin/forgot", function() {
	
	$user = User::getForgot($_POST["email"]);
	
	header("Location: /admin/forgot/sent");
	exit;
});

$app->get('/admin/forgot/sent', function () {	

	$page = new PageAdmin([
			"header"=>false,
			"footer"=>false
		]);

	$page->setTpl("forgot-sent");

});

$app->get('/admin/forgot/reset', function () {


	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
			"header"=>false,
			"footer"=>false
		]);

	$page->setTpl("forgot-reset", [

	 "name" => $user["desperson"],
	 "code" => $_GET["code"]

	]);

});

$app->post('/admin/forgot/reset', function () {

	$forgot = User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot['iduser']);

	$user->setPassword($_POST["password"]);

	$page = new PageAdmin([
			"header"=>false,
			"footer"=>false
		]);

	$page->setTpl("forgot-reset-success");

});

//CATEGORIAS
$app->get('/admin/categories', function () {

	$tpl = new PageAdmin();
	$categories = Category::listAll();
	$tpl->setTpl("categories", ["categories" => $categories]);

});

$app->get('/admin/categories/create', function () {

	$tpl = new PageAdmin();

	$tpl->setTpl("categories-create");
});

$app->post('/admin/categories/create', function () {

	$category = new Category();

	$category->setData($_POST);
	$category->save();

	header('Location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory/delete', function ($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory', function ($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);		
	
	$page = new PageAdmin();
	$page->setTpl("categories-update",["category" => $category->getvalues()]);
});

$app->post('/admin/categories/:idcategory', function ($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);
		
	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});


$app->run();
	
	
	
?>