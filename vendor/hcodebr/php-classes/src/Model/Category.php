<?php
namespace Hcode\Model;

use Hcode\Model;
use \Hcode\DB\Sql;


class Category extends Model
{
	public $sql;

	public function __constructor() 
	{
		$this->sql = new Sql();

	}
	public static function listAll(){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM  tb_categories ORDER BY descategory");

		if ($result === 0) throw new \Exception("Nenhuma categoria  encontrada");

		return $result; 
	}

	public function save(){

		$sql = new Sql();
		
		
		$result = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",
		[
			":idcategory" => $this->getidcategory(),
			":descategory" => $this->getdescategory()
			
		]);

		$this->setData($result[0]);
	
		
		return $result; 
	}
	public function get($idcategory){

		$sql = new Sql();
		$result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",[":idcategory" => $idcategory]);

		$this->setData($result[0]);
	}

	public function delete(){

		$sql = new Sql();
		//var_dump($this->sql);exit;	
		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [":idcategory" => $this->getidcategory()]);
	}

	public function update(){

		$sql = new Sql();

		$sql->query("UPDATE FROM tb_categories SET descategory = :descategory WHERE idcategory = :idcategory",[
			":idcategory" => $this->getidcategory(),
			":descategory" => $this->getdescategory()]);
	}
}