<?php
	session_start();

	$action = $_POST['action'];
	$sala = $_SESSION['sala'];

	switch($action){
		
		// OBTEM OS MOVIMENTOS (ATÉ AGORA) DE UMA DETERMINADA SALA
		case 'getSala':
			$movimentos = getMovimentos($sala);
			echo json_encode($movimentos, true);
			break;

		// SALVA OS MOVIMENTOS DO TURNO ATUAL
		case 'saveTurn':
			echo gettype($_POST['turno']);
			break;

		
		default: break;
	}


	// AS FUNÇÕES ABAIXOS ESTÃO SÓ NOS COOKIES MAS DEPOIS PRECISA TROCAR POR CONSULTAS AO BANCO
	function getMovimentos($sala){
		return $_SESSION['movimentos'];
	}

