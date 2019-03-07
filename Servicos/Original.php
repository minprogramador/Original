<?php

require_once('config.php');
require_once("checkAuth.php");
if(isset($_SESSION['TrocarSenha'])) { die('Acesso negado'); }
if(!isset($_SESSION['liberado'])){ die('Acesso negado'); }

$servicoNome = 'Original';
$idUsuario = $util->xss($_SESSION['getId']);

$servico = new Sistema_Servicos();
$servico->setServico($servicoNome);

$control = new Sistema_Control();
$control->setServico($servico->getServico());
$control->setIdUser($idUsuario);

if($control->Permissao() != true){ header('Location: ../Painel?negado'); die; }
else
{
	$control->setServico($servico->getServico());
	$a = $control->getLimites();

    if($a['status'] != '1'){ $erroy = true; }
    if($a['usado'] >= $a['limite']){ $erroy = true; }
}

if(isset($erroy)) {
    $msg = file_get_contents('../tpls/outros/pagina_servicos_pontos.html');
    $msg = str_replace('{{msg}}', $servicoNome, $msg);
    echo $msg;
    die;
}
session_write_close();

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

//	curl_setopt($ch, CURLOPT_PROXY, '177.70.93.36:3128');
        
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

$url = 'http://181.215.238.197/assert/';

if(isset($_REQUEST['dados']) && isset($_REQUEST['tipo'])){
	header('Content-type: application/json');

	$tipo  = $_REQUEST['tipo'];
	$dados = $_REQUEST['dados'];
	$payload = array(
		$tipo => $dados
	);

	$post = http_build_query($payload);
	$dados = curl($url, null, $post, false);

	if(stristr($dados, 'localizePorNomeOuEndereco')) {
		$dados = json_decode($dados, true);
		$dados['dados'] = $dados['localizePorNomeOuEndereco'];
		unset($dados['localizePorNomeOuEndereco']);
		$dados = json_encode($dados);
	}
	elseif(stristr($dados, 'localizePorEmail')) {
		$dados = json_decode($dados, true);
		$dados['dados'] = $dados['localizePorEmail'];
		unset($dados['localizePorEmail']);
		$dados = json_encode($dados);
	}
	elseif(stristr($dados, 'localizePorTelefone')) {
		$dados = json_decode($dados, true);
		$dados['dados'] = $dados['localizePorTelefone'];
		unset($dados['localizePorTelefone']);
		$dados = json_encode($dados);
	}
	elseif(stristr($dados, 'cadastro')){
		$control->saveConsulta();
	}else{
		echo json_encode(['error'=>true, 'msg'=> 'Nada encontrado']);
	}
	
	echo $dados;
	die;

}elseif(isset($_REQUEST['nome'])){
	header('Content-type: application/json');
	$post = http_build_query($_POST);
	$dados = curl($url, null, $post, false);

	if(stristr($dados, 'localizePorNomeOuEndereco')){
		$dados = json_decode($dados, true);
		$dados['dados'] = $dados['localizePorNomeOuEndereco'];
		unset($dados['localizePorNomeOuEndereco']);
		$dados = json_encode($dados);
	}
	else{
		$dados = json_encode(['error'=>true, 'msg'=> 'Nada encontrado']);
	}
	
	echo $dados;
	die;
}else{
	include('tpls/Original/main.html');
	die;

}

