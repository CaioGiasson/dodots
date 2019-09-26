<?php
	session_start();

	$action = $_POST['action'];
	$idSala = $_POST['idSala'];
	$sala = getSala($idSala);
	$player = $_SESSION['player'];

	switch($action){
		
		// OBTEM OS MOVIMENTOS (ATÉ AGORA) DE UMA DETERMINADA SALA
		case 'getSala':
			echo json_encode($sala, true);
			break;

		// SALVA OS MOVIMENTOS DO TURNO ATUAL
		case 'saveTurn':
			if ( $player!=$sala->p1 && $player!=$sala->p2 ) break;

			$turno = json_decode($_POST['turno']);
			echo "1";
			break;

		default: break;
	}

	// FALTA FAZER SALVAR EM ARQUIVO, DENTRO DA PASTA saves/
	// PODE SER SÓ UM ARQUIVO .sav PRA CADA SALA MESMO, MAIS FÁCIL, MAS DENTRO É SÓ UM ASCII NORMAL, COM O JSON DA PARTIDA
	function getSala($sala){
		return $_SESSION['sala'];
	}

	function saveTurno($turno){
		$_SESSION['sala']->movimentos[] = $turno;
	}



