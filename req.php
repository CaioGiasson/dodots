<?php
	session_start();

	// GERANDO OU RECUPERANDO OS DADOS DA SALA
	if ( isset($_GET['sala']) ) {
		$_SESSION['sala'] = $_GET['sala'];
		$sala = getSala($_GET['sala']);
	}
	else if ( isset($_SESSION['sala']) ) $sala = getSala($_SESSION['sala']);
	else $sala = novaSala();

	// GERANDO OU RECUPERANDO A TOKEN DO PLAYER
	$player = getPlayer();

	// ATRIBUINDO O PLAYER AO PRIMEIRO SLOT DISPONÍVEL
	// SE NÃO TIVER SLOT DE PLAYER DISPONÍVEL ELE FICARÁ COMO ESPECTADOR
	if ( $sala->p1==null ) $sala->p1 = $player;
	else if ( $sala->p1!=$player || $sala->p2==null ) $sala->p2 = $player;

	$action = $_POST['action'];
	switch($action){

		// OBTÉM O PLAYER ATUAL
		case 'getPlayer':
			echo $sala->current;
			break;
		
		// OBTEM OS MOVIMENTOS (ATÉ AGORA) DE UMA DETERMINADA SALA
		case 'getSala':
			header("Content-Type: application/json");
			$sala->quando = $_POST['quando'];

			echo json_encode($sala, true);
			break;

		// SALVA OS MOVIMENTOS DO TURNO ATUAL
		case 'saveTurn':
			if ( $player!=$sala->p1 && $player!=$sala->p2 ) {
				echo -2;
				exit;
			}

			$t = json_decode($_POST['turno'], true);
			$turno = [];
			foreach($t as $m) $turno[] = $m*1;
			// $turno = json_encode($turno, true);

			$player = $_POST['player'];

			$mov = new stdClass();
			$mov->turno = $turno;
			$mov->player = $player;

			if ( $sala->current != $mov->player ) {
				echo -1;
				exit;
			}

			$sala->movimentos[] = $mov;
			$sala->current = $sala->current == $sala->p1 ? $sala->p2 : $sala->p1;

			saveSala($sala);

			echo 1;
			break;

		default: break;
	}

	function getSala($token){
		$nomeArq = "saves/$token.txt";
		$salaFile = fopen( $nomeArq, "r");
		$sala = fgets($salaFile);
		$sala = str_replace("\"", '"', $sala);
		fclose($salaFile);
		if ( strlen($sala)==0 ){
			$sala = novaSala($token);
			saveSala($sala);
		} else $sala = json_decode($sala, true);
		return (object) $sala;
	}

	function saveSala($sala){
		$nomeArq = "saves/$sala->token.txt";
		$salaFile = fopen( $nomeArq, "w+");
		$salaTexto = json_encode($sala, true);
		$try = fwrite($salaFile, $salaTexto );
		fclose($salaFile);
	}

	function getPlayer(){
		if( isset($_COOKIE['player'])) {
			$_SESSION['player'] = $_COOKIE['player'];
			return $_SESSION['player'];
		}

		$player = newPlayer();
		$_SESSION['player'] = $player;
		setcookie('player', $player);
		return $player;
	}

	function newPlayer(){
		$_SESSION['player'] = token(8);
		return $_SESSION['player'];
	}

