<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model
{
	const SESSION = "User";
	const SECRET = "HcodePhp7_SecretHcodePhp7_Secret";
	const AES_IV = 'THLFOrnEzyKXtjwb';
	const OPTION = 0;

	public static function listAll(){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_users a INNER JOIN  tb_persons b USING(idperson) ORDER BY desperson");

		if ($result === 0) throw new \Exception("Nenhum usuário encontrado");

		return $result; 
	}

	public function get($iduser)
	{
		$sql = new Sql();
		$result = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", [
			":iduser" => $iduser
		]);
		//var_dump($result);exit;
		$this->setData($result[0]);

		return $this->getValues();
	}

	public function update(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)",
		[
			":iduser" => $this->getiduser(),
			":desperson" => $this->getdesperson(),
			":deslogin" => $this->getdeslogin(),
			":despassword" => $this->getdespassword(),
			":desemail" => $this->getdesemail(),
			":nrphone" => $this->getnrphone(),
			":inadmin" => $this->getinadmin()
		]);

		$this->setData($result[0]);
		
		
		//return $result; 
	}

	public function delete(){

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", [":iduser" => $this->getiduser()]);
	}




	public function save(){

		$sql = new Sql();
		$passwordhash = password_hash($this->getdespassword(),  PASSWORD_ARGON2I);
		
		$result = $sql->select("CALL sp_users_save(:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)",
		[
			":desperson" => $this->getdesperson(),
			":deslogin" => $this->getdeslogin(),
			":despassword" => $passwordhash,
			":desemail" => $this->getdesemail(),
			":nrphone" => $this->getnrphone(),
			":inadmin" => $this->getinadmin()
		]);

		$this->setData($result[0]);
		
		
		return $result; 
	}
	
	public static function login($user,$password)
	{


		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :login", [

			":login" => $user
		]);
		// echo count($results);
		// var_dump($results);exit;
		if (count($results) === 0) throw new \Exception("Usuário inexistente ou senha inválida no login");

		$result = $results[0];
		 //  echo "<pre>";
		 // var_dump($result);
		
		$verify = password_verify($password, $result["despassword"]);
	

		if(!$verify) throw new \Exception("Usuário inexistente ou senha inválida");


		$user = new User();

		$user->setData($result);

		$_SESSION[User::SESSION] = $user->getValues();

	

		return $user;
	}

	public static function verifyLogin($inadmin = true)
	 {
	  
		if(
			!isset($_SESSION[User::SESSION]) 
			||
			!$_SESSION[User::SESSION] 
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		 
		) {	
			
			header("Location: /admin/login");
			exit();
		}

		
	}


	public static function logOut()
	{
		//unset($_SESSION[User::SESSION]);
		//echo "chegou";exit;
		$_SESSION[User::SESSION] = NULL;

		//var_dump($_SESSION[User::SESSION]);exit;

	} 

	public  function getForgot($mail)
	{
		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE b.desemail = :email", [":email" => $mail]);

		
			//var_dump($result);
		if(count($result) === 0) throw new \Exception("Não foi possível recuperar a senha");

		$data = $result[0];

		$result2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)",[
			":iduser" => $data["iduser"],
			":desip" => $_SERVER["REMOTE_ADDR"]
		]);

		if(count($result2)  === 0 ) throw new \Exception("Não foi possível recuperar a senha");

		$dataRecovery = $result2[0];

		//$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
		$code = base64_encode(openssl_encrypt(
			$dataRecovery["idrecovery"], 'aes-256-cbc', User::SECRET,User::OPTION,User::AES_IV));

			

		$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

		$mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinir senha Hcode Store","forgot", [
			"name" => $data["desperson"],
			"link" => $link
		]);

		$mailer->send();

		return $data;

	}
	public static function validForgotDecrypt($code){

		
			$idrecovery = openssl_decrypt(base64_decode($code), 'aes-256-cbc', User::SECRET,User::OPTION,User::AES_IV);


			$sql = new Sql();

			$result = $sql->select("
				SELECT *
				FROM tb_userspasswordsrecoveries a
				INNER JOIN tb_users b USING(iduser)
				INNER JOIN tb_persons c USING(idperson)
				WHERE 
					a.idrecovery = :idrecovery
					AND
					a.dtrecovery IS NULL
					AND
					DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
				", [ ":idrecovery" => $idrecovery ]);

		
			

				if(count($result) === 0) throw new \Exception("Não foi possível resetar a senha");
				
				return $result[0];

				// header("Location: /admin/forgot/success");
			 //    exit();
	}

	public static function setForgotUsed($idrecovery){

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = now() 
				WHERE idrecovery = :idrecovery", [":idrecovery" => $idrecovery]);

	}

	public function setPassword($password){
	

		$sql = new Sql();

		$res = $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser",
			array(
				":password" => $password,
				":iduser" => $this->getiduser(),
			));

	}
















}
?>