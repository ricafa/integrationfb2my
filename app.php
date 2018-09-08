<?php 
//FIREBIRD TO MYSQL
function buscaDadoFirebird($sql){
	$servidor = 'localhost:C:/firebird/r2o/DADOS.GDB';
	if (!($dbh=ibase_connect($servidor, 'SYSDBA', 'masterkey'))){
		die('Erro ao conectar: '. ibase_errmsg());
	}
	$query= ibase_query ($dbh, $sql); 
	$arr = [];
	while ($row = ibase_fetch_object ($query)) { 
		$arr[]= $row;
	}
	ibase_free_result($query); 
	ibase_close($dbh);
	return $arr;
}

$alteracoes = buscaDadoFirebird(
	'SELECT TABELA, ACAO
    FROM ALTERACOES 
    WHERE DATA_IMPORTADO IS NULL
    group by TABELA, ACAO
    ORDER BY TABELA;'
);
$tabelas = [];
$acoes = [];
foreach($alteracoes as $alt){
	$tabelas[] = $alt->TABELA;
	$acoes[]   = $alt->ACAO;
}
//$tabelas[0] = strtoupper($tabelas[0]);
$sqlids = "
SELECT ID
FROM ALTERACOES A
WHERE TABELA LIKE '".$tabelas[0]."' AND DATA_IMPORTADO IS NULL";
echo $sqlids;
$ids = buscaDadoFirebird($sqlids);
$idString = [];
//var_dump($id);
foreach($ids as $id=>$val){	
	//var_dump($val->ID);
	$idString[]= $val->ID;
}

echo '<br/><br/>campos <br/><br/>';

$linhas = explode("\n",file_get_contents($tabelas[0].'.jurassic'));
$firebird = [];
$mysql = [];
foreach($linhas as $linha ){
	$campos = explode("=",$linha);
	if(!empty($campos[0]) && !empty($campos[1]) ){
		$mysql[] 		= $campos[0];
		$firebird[] 	= $campos[1];	 
	}
}
echo '<h1>Mysql</h1>';
var_dump($mysql);
echo '<h1>Firebird</h1>';
var_dump($firebird);
echo '<br/> Query: <br/>';
$sqlFirebird = "SELECT ".join(", ", $firebird)." FROM TABELA_CLIENTES WHERE CLI_CODIGO IN (".(join(',',$idString))."); ";

var_dump(
	$sqlFirebird
);


echo '<h1>Mysql insert from firebird</h1>';
if(strtoupper($acoes[0]) == strtoupper('insert')){
	$resultFirebird = buscaDadoFirebird($sqlFirebird);

	//var_dump(		$resultFirebird	);
	echo '<BR/>';
	$insert  =  "INSERT INTO clientes (".join(',',$mysql).") VALUES ";

	foreach($resultFirebird as $val){
		$data=[];
		foreach ($firebird as $fir) {
			if(isset($val->{trim($fir)})) {
				if(!empty($val->{trim($fir)})) {
					$data[]= '"'.$val->{trim($fir)}.'"';
				}
				else{
					$data[]='""';
				}
			}
			else{
				$data[]='""';
			}
		}
		echo $insert.'('.join(',',$data).')';
		echo '<br/></br>';

		//$insert .= "(".join(,$firebird)." );";
		}
	echo $insert;
}
?>