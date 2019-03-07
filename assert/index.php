<?php

require('config.php');


function curl($url, $cookies, $post, $header=true, $referer=null, $auth=false, $tipo=null, $proxy=false) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, $header);
	if ($cookies) curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	if(isset($referer)){ curl_setopt($ch, CURLOPT_REFERER,$referer); }
	else{ curl_setopt($ch, CURLOPT_REFERER,$url); }
	if ($post)
	{
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}

	if($auth) {
		$header = array('Authorization: '. $auth );

		if($tipo != null) { $header[] = 'Content-Type: '.$tipo; } else { $header[] = 'Content-Type: application/x-www-form-urlencoded'; }
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}
	if(!stristr(PROXYASSERT, ':')) {
		die('redeoff');
	}
	curl_setopt($ch, CURLOPT_PROXY, PROXYASSERT);
        
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

	$res = curl_exec( $ch);
	curl_close($ch);
	#return utf8_decode($res);
	return ($res);
}

function corta($str, $left, $right){
    $str = substr ( stristr ( $str, $left ), strlen ( $left ) );
    @$leftLen = strlen ( stristr ( $str, $right ) );
    $leftLen = $leftLen ? - ($leftLen) : strlen ( $str );
    $str = substr ( $str, 0, $leftLen );
    return $str;
}

function getCookies($get){
    preg_match_all('/Set-Cookie: (.*);/U',$get,$temp);
    $cookie = $temp[1];
    $cookies = implode('; ',$cookie);
    return $cookies;
}

function save($dados) {
	$name = 'asserDados.txt';
	$file = fopen($name, 'w+');
	fwrite($file, $dados);
	fclose($file);
}

function ler() {
	$res = file_get_contents('asserDados.txt');
	return $res;
}

function checkSessao($dados) {
	if(stristr($dados, 'foi encontrada. entre no sis')) {
		return false;
	} 

	return true;
}

function consultaCpf($auth, $cpf) {
	$url  = 'https://managerbigdata.assertivasolucoes.com.br/localize/1002/consultar';
	$ref  = 'https://portal.assertivasolucoes.com.br/localize';
	$post = 'cpf=' . $cpf;
	$tipo = 'application/x-www-form-urlencoded';

	$result = curl($url, null, $post, false, $ref, $auth, $tipo);


	if(checkSessao($result) == false){ return false; }

	if(stristr($result, 'cadastro"')) {
		$remov = corta($result, '"cabecalho"', '"cadastro');
		$result = str_replace($remov, '', $result);
		$result = str_replace('"cabecalho"', '', $result);
		#$result = str_replace('null', '', $result);
		return $result;
	} else {
		return false;
	}
}

function consultaCnpj($auth, $cnpj) {
	$url  = 'https://managerbigdata.assertivasolucoes.com.br/localize/1003/consultar';
	$ref  = 'https://portal.assertivasolucoes.com.br/localize';
	$tipo = 'application/x-www-form-urlencoded';
	$post = 'cnpj=' . $cnpj;

	$result = curl($url, null, $post, false, $ref, $auth, $tipo);

	if(checkSessao($result) == false){ return false; }

	if(stristr($result, 'cadastro"')) {
		$remov = corta($result, '"cabecalho"', '"cadastro');
		$result = str_replace($remov, '', $result);
		$result = str_replace('"cabecalho"', '', $result);
		return $result;
	} else {
		return false;
	}
}

function consultaNome($auth, $payload) {
	$url  = 'https://managerbigdata.assertivasolucoes.com.br/localize/1006/consultar';
	$ref  = 'https://portal.assertivasolucoes.com.br/localize';
	$tipo = 'application/json;charset=utf-8';

	$post = json_encode($payload);

	$result = curl($url, null, $post, false, $ref, $auth, $tipo);
	

	if(checkSessao($result) == false){ return false; }

	if(stristr($result, 'localizePorNomeOuEndereco"')) {
		$remov = corta($result, '"cabecalho"', '"localizePorNomeOuEndereco');
		$result = str_replace($remov, '', $result);
		$result = str_replace('"cabecalho"', '', $result);
		return $result;
	} else {
		return false;
	}

}

function consultaEmail($auth, $email) {
	$url  = 'https://managerbigdata.assertivasolucoes.com.br/localize/1004/consultar';
	$ref  = 'https://portal.assertivasolucoes.com.br/localize';
	$tipo = 'application/x-www-form-urlencoded';
	$post = 'email='. $email;
	$result = curl($url, null, $post, false, $ref, $auth, $tipo);

	if(checkSessao($result) == false){ return false; }

	if(stristr($result, 'localizePorEmail"')) {
		$remov = corta($result, '"cabecalho"', '"localizePorEmail');
		$result = str_replace($remov, '', $result);
		$result = str_replace('"cabecalho"', '', $result);
		return $result;
	} else {
		return false;
	}

	return $result;
}

function consultaTelefone($auth, $telefone) {
	$url  = 'https://managerbigdata.assertivasolucoes.com.br/localize/1005/consultar';
	$ref  = 'https://portal.assertivasolucoes.com.br/localize';
	$tipo = 'application/x-www-form-urlencoded';
	$post = 'telefone=' . $telefone;

	$result = curl($url, null, $post, false, $ref, $auth, $tipo);

	if(checkSessao($result) == false){ return false; }

	if(stristr($result, 'localizePorTelefone"')) {
		$remov = corta($result, '"cabecalho"', '"localizePorTelefone');
		$result = str_replace($remov, '', $result);
		$result = str_replace('"cabecalho"', '', $result);
		return $result;
	} else {
		return false;
	}

	return $result;
}

function logar() {
	$url   = 'https://auth.assertivasolucoes.com.br/auth/get-key-session-user';
	$ref   = 'https://portal.assertivasolucoes.com.br/basecerta';
	$post  = 'empresa='.EMASSERT.'&usuario='.USASSERT.'&senha='.PWASSERT;
	$logar = curl($url, null, $post, false, $ref);
	$res   = json_decode($logar, true);
	if($res['response']) {
		return $res['response'];
	}else {
		return false;
	}
}

function test($auth) {
	$url  = 'https://managerbigdata.assertivasolucoes.com.br/localize/ultimas-consultas/consultar';
	$reg  = 'https://portal.assertivasolucoes.com.br/localize';
	$tipo = 'application/x-www-form-urlencoded';
	$post = 'consulta=1002';
	$result = curl($url, null, $post, false, $ref, $auth, $tipo);
	$result = json_decode($result, true);
	if(array_key_exists("erro", $result)) {
		return false;
	}elseif(array_key_exists("localizeUltimasConsultas", $result)){
		return true;
	}else{
		return 'error';
	}

}

$token = ler();
if(strlen($token) < 10) {
	$token = logar();
	if($token) {
		save($token);
	}		
}

header('Content-type: application/json');

if(isset($_REQUEST['cpf'])) {
	$cpf = $_REQUEST['cpf'];
	$dados = consultaCpf($token, $cpf);
	if($dados == false) {
		$token = logar();
		if($token){
			save($token);
		}
		$dados = consultaCpf($token, $cpf);
	}

	if($dados) {

		echo $dados;		
	}
}
elseif(isset($_REQUEST['cnpj'])) {
	$cnpj = $_REQUEST['cnpj'];
	$dados = consultaCnpj($token, $cnpj);
	if($dados == false) {
		$token = logar();
		if($token){
			save($token);
		}
		$dados = consultaCnpj($token, $cnpj);
	}

	echo $dados;
}
elseif(isset($_REQUEST['nome'])) {


	if(strlen($_REQUEST['dataNascimento']) > 6) {
		$var = $_REQUEST['dataNascimento'];
		$date = str_replace('/', '-', $var);
		$_REQUEST['dataNascimento'] = date('Y-m-d', strtotime($date));
	}

	$payload = array(
		"nome" 			 => $_REQUEST['nome'],
		"dataNascimento" => $_REQUEST['dataNascimento'],
		"sexo"   		 => $_REQUEST['sexo'],
		"uf"     	     => $_REQUEST['uf'],
		"cidade" 	     => $_REQUEST['cidade'],
		"bairro"         => $_REQUEST['bairro'],
		"complemento"    => $_REQUEST['complemento'],
		"enderecoOuCep"  => $_REQUEST['enderecoOuCep'],
		"numeroInicial"  => $_REQUEST['numeroInicial'],
		"numeroFinal"    => $_REQUEST['numeroFinal'],
		"tipoVeiculo"    => $_REQUEST['tipoVeiculo'],
		"isCpf" 		 => $_REQUEST['isCpf'],
		"errorNumeroInicial" => $_REQUEST['errorNumeroInicial'],
		"errorNumeroFinal"   => $_REQUEST['errorNumeroFinal'],
		"tipoDoc" 		     => $_REQUEST['tipoDoc'],
		"nomeMatchCompleto"  => $_REQUEST['nomeMatchCompleto']
	);

	$dados = consultaNome($token, $payload);

	if($dados == false) {
		$token = logar();
		if($token){
			save($token);
		}
		$dados = consultaNome($token, $payload);
	}

	echo $dados;
}
elseif(isset($_REQUEST['email'])) {
	$email = $_REQUEST['email'];
	$dados = consultaEmail($token, $email);
	if($dados == false) {
		$token = logar();
		if($token){
			save($token);
		}
		$dados = consultaEmail($token, $email);
	}
	echo $dados;
}
elseif(isset($_REQUEST['telefone'])) {
	$telefone = $_REQUEST['telefone'];
	$dados = consultaTelefone($token, $telefone);
	if($dados == false) {
		$token = logar();
		if($token){
			save($token);
		}
		$dados = consultaTelefone($token, $telefone);
	}
	echo $dados;
}
else
{
	echo json_encode('Api v 0.2 07/03/2019 by puttyoe');
}