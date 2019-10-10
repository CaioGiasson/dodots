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
	$player = isset($_SESSION['player']) ? $_SESSION['player'] : newPlayer();

	// ATRIBUINDO O PLAYER AO PRIMEIRO SLOT DISPONÍVEL
	// SE NÃO TIVER SLOT DE PLAYER DISPONÍVEL ELE FICARÁ COMO ESPECTADOR
	if ( $sala->p1==null ) $sala->p1 = $player;
	else if ( $sala->p1!=$player || $sala->p2==null ) $sala->p2 = $player;

	// DEFININDO DE QUEM É A VEZ
	if ( $sala->current == null ) $sala->current = $sala->p2;
	$p1_class = $sala->current == $sala->p1 ? 'pSel' : '';
	$p2_class = $sala->current == $sala->p2 ? 'pSel' : '';	

	saveSala($sala);

	// COLOCAR OVERLAY IMPEDINDO O JOGO ENQUANTO ESTIVER FALTANDO ALGUM PLAYER CONECTAR
	// SE O ID DO PLAYER QUE CARREGOU A SALA FOR DIFERENTE DE AMBOS OS PLAYERS QUE ESTÃO JOGANDO, COLOCAR OVERLAY IMPEDINDO QUE ELE JOGUE
	// DURANTE O JOGO BASTA VERIFICAR SE O TURNO É DO PLAYER ATUAL (USANDO O ID).
	// SE NÃO FOR O ATUAL, ESPERA PELA JOGADA DO PRÓXIMO
	// QUANDO A JOGADA CHEGAR, ATUALIZA FAZENDO A ANIMAÇÃO, E ENTÃO VERIFICA SE É O SEU PRÓPRIO TURNO
	// A SALA TEM QUE RETORNAR O "CURRENT" QUANDO TERMINA O MOVIMENTO DE UM
	// SE O CURRENT NUNCA FOR IGUAL AO ID DO JOGADOR ATUAL ENTÃO ELE AUTOMATICAMENTE SÓ FICA COMO ESPECTADOR, E A OVERLAY NUNCA SAI
	// OU SEJA, ELE NUNCA CONSEGUE CLICAR

	// FUNÇÕES AUXILIARES ABAIXO
	function newPlayer(){
		$_SESSION['player'] = token(8);
		return $_SESSION['player'];
	}

	function novaSala($token = ""){
		$sala = new stdClass();
		if ( strlen($token)<4 ) $sala->token = token(8);
		else $sala->token = $token;
		$sala->p1 = null;
		$sala->p2 = null;
		$sala->current = null;
		$sala->movimentos = array();
		saveSala($sala);

		return $sala;
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

	function token($tamanho){
		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$string = '';
		$max = strlen($characters) - 1;
		for ($i = 0; $i < $tamanho; $i++) $string .= $characters[mt_rand(0, $max)];
		return $string;
	}

	function saveSala($sala){
		$nomeArq = "saves/$sala->token.txt";
		$salaFile = fopen( $nomeArq, "w+");
		$salaTexto = json_encode($sala, true);
		$try = fwrite($salaFile, $salaTexto );
		fclose($salaFile);
	}
?>
<html>
	<head>

		<style type="text/css">
			.monte { display: block; width: 100%; height: 80px; background: #eee; margin: 10px 0; text-align: center; border: solid 2px #eee; }
			.monte.mSel { border: solid 2px #aaa; }

			.dodot { display: inline-block; width: 50px; height: 50px; background: #ccc; border-radius: 50px; margin: 8px; cursor: pointer; border: solid 2px #ccc;  }
			.dodot.oculto { width: 0; height: 0; opacity: 0; margin: 0; border: solid 0px #ccc; }
			.dodot.dotSel { border: solid 2px #333; }

			.players { display: block; position: relative; width: 100%; height: 220px; clear: both; }

			.p1 { display: block; position: relative; width: 49%; background: #dfd; height: 200px; float: left; border: solid 2px #dfd; }
			.p1.pSel { border: solid 2px #090; ; }
			.p2 { display: block; position: relative; width: 49%; background: #ddf; height: 200px; float: right; text-align: right; border: solid 2px #ddf; }
			.p2.pSel { border: solid 2px #009; }

			.p h3{ padding-left: 20px;  }

			.smooth-fast { -webkit-transition: all 0.7s; -moz-transition: all 0.7s; -o-transition: all 0.7s; transition: all 0.7s; }
			.clear { display: block; position: relative; width: 100%; height: 10px; clear: both; }

			.fimTurno { display: block; position: relative; clear: both; width: 200px; height: 50px; background: #aaa; text-align: center; margin: 0 auto; overflow: hidden; color: #fff; padding-top: 15px; cursor: pointer; }
			.fimTurno.oculto { width: 0; height: 0; opacity: 0; }

			.salabox { display: block; position: relative; width: 100%; text-align: center; font-size: 14px; }
			.salabox input { display: inline-block; border: solid 1px #ccc; padding: 4px; text-align: center; margin: 0 5px; }
			.salabox .copiar { display: inline-block; border: none; background: #ccc; padding: 4px 10px; font-size: 12px; cursor: pointer; }

			.tip { display: block; position: relative; width: 100%; text-align: center; font-size: 12px; color: #777; font-style: italic; }
		</style>
	</head>
	<body onload="updateSala()">

		<div class="salabox">
			<span>ID da sala: </span>
			<input name="sala" id="sala" value="<?=$sala->token?>" />
			<input type="hidden" name="eu" id="eu" value="<?=$_SESSION['player']?>" />
			<button class="copiar" onclick="copiar()">Copiar</button>
			<span class="tip">(caso queira entrar em uma sala cole o id e aperte ENTER)</span>
			<span class="tip">Você é: <?=$_SESSION['player']?></span>
			<span class="tip">P1: <?=$sala->p1?></span>
			<span class="tip">P2: <?=$sala->p2?></span>
		</div>

		<p>&nbsp;</p>

		<div class="players">
			<div class="p p1 smooth-fast <?=$p1_class?>" data-id="<?=$sala->p1?>" title="<?=$sala->p1?>"><h3>Player 1</h3></div>
			<div class="p p2 smooth-fast <?=$p2_class?>" data-id="<?=$sala->p2?>" title="<?=$sala->p2?>"><h3>Player 2</h3></div>
		</div>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="1"></span>
		</div>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="2"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="3"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="4"></span>
		</div>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="5"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="6"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="7"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="8"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="9"></span>
		</div>
	
		<div class="monte smooth-fast">
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="10"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="11"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="12"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="13"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="14"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="15"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)" data-id="16"></span>
		</div>

		<span class="fimTurno oculto smooth-fast" onclick="terminarTurno(this)">Terminar turno</span>

		<script type="text/javascript">
			window.animando = false;

			// SORTEIO ANTIGO DE QUEM ERA A VEZ, FOI SUBSTITUÍDO PELA VERSÃO DISSO EM PHP
			// PS: NÃO É MAIS RANDOM. VISITA SEMPRE COMEÇA
			// let qual = Math.random();
			// if ( qual<0.5 ) document.getElementsByClassName("p1")[0].classList.add("pSel");
			// else document.getElementsByClassName("p2")[0].classList.add("pSel");
			
			let copiar = function(){
				let campo = document.getElementById("sala");
				let sala = campo.value;
				campo.value = `https://caini.tech/web/dodots.php?sala=${sala}`;
				campo.select();
				campo.setSelectionRange(0, 99999); /*For mobile devices*/
				document.execCommand("copy");
				campo.value = sala;
				campo.select();
		  	}

			let clicou = function(obj){
				if ( obj.classList.contains("oculto") ) return;
				if ( window.animando ) return;

				const playerAtual = document.getElementsByClassName("pSel")[0].dataset.id;
				const playerEu = document.getElementById("eu").value;
				if ( playerAtual != playerEu ) {
					alert("Calma, não é sua vez!");
					return;
				}

				if ( obj.parentElement.classList.contains("mSel") || document.getElementsByClassName("mSel").length==0 ) {
					if ( obj.classList.contains("dotSel") ){
						obj.classList.remove("dotSel");
						if ( document.getElementsByClassName("dotSel").length == 0 ){
							obj.parentElement.classList.remove("mSel");
							document.getElementsByClassName("fimTurno")[0].classList.add("oculto");
						}
					} else {
						if ( !obj.parentElement.classList.contains("mSel") )
							obj.parentElement.classList.add("mSel");

						obj.classList.add("dotSel");
						document.getElementsByClassName("fimTurno")[0].classList.remove("oculto");
					}
				}
			}

			let terminarTurno = function(btn){
				if ( btn.classList.contains("oculto") ) return;

				window.animando = true;

				let dotList = document.getElementsByClassName("dotSel");
				let idList = [];

				document.getElementsByClassName("mSel")[0].classList.remove("mSel");

				for ( dot of dotList ) {
					idList.push( dot.dataset.id );
					dot.classList.add("oculto");
					let h = document.getElementsByClassName("pSel")[0].innerHTML;
					document.getElementsByClassName("pSel")[0].innerHTML = h + `<span class="dodot smooth-fast "></span>`;
				}

				setTimeout(function(){
					while ( document.getElementsByClassName("dotSel").length>0 ){
						document.getElementsByClassName("dotSel")[0].remove();
					}
				}, 700);
				
				// SALVANDO DADOS DO TURNO EXECUTADO
				let player = document.getElementsByClassName("pSel")[0].dataset.id;
				sendTurno(player, idList);

				document.getElementsByClassName("fimTurno")[0].classList.add("oculto");
				trocaTurno();

				setTimeout(function(){
					window.animando = false;

					if ( document.getElementsByClassName("cadaDot").length==0 ) alert("gameOver");
				}, 800)
			}

			let trocaTurno = function(){
				if ( document.getElementsByClassName("p1")[0].classList.contains("pSel") ){
					document.getElementsByClassName("p1")[0].classList.remove("pSel");
					document.getElementsByClassName("p2")[0].classList.add("pSel");
				} else {
					document.getElementsByClassName("p1")[0].classList.add("pSel");
					document.getElementsByClassName("p2")[0].classList.remove("pSel");
				}
			}

			let sendTurno = function(player, dadosTurno){
				var data = new FormData();
				data.append('turno', JSON.stringify(dadosTurno) );
				data.append('player', player);
				data.append('action', 'saveTurn');

				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						console.log(xhttp.responseText);
						// document.getElementById("demo").innerHTML = xhttp.responseText;
					}
				};
				xhttp.open("POST", "req.php", true);
				xhttp.send(data);
			}

			let updateSala = function(){
				var data = new FormData();
				data.append('idSala', document.getElementById("sala").value );
				data.append('action', 'getSala');

				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						// AQUI COLOCAR CÓDIGO QUE "DÁ PLAY" NA LISTA DE MOVIMENTOS 
						// JÁ RODADOS NA HORA QUE EU CARREGUEI A SALA
						console.log(xhttp.responseText);
					}
				};
				xhttp.open("POST", "req.php", true);
				xhttp.send(data);
			}
		</script>

	</body>


</html>