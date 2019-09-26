<?php
	session_start();

	// GERANDO OU RECUPERANDO A TOKEN DO PLAYER
	$player = isset($_SESSION['player']) ? $_SESSION['player'] : newPlayer();

	// GERANDO OU RECUPERANDO OS DADOS DA SALA
	$sala = isset($_GET['sala']) ? $_GET['sala'] : getSala();

	// ATRIBUINDO O PLAYER AO PRIMEIRO SLOT DISPONÍVEL
	// SE NÃO TIVER SLOT DE PLAYER DISPONÍVEL ELE FICARÁ COMO ESPECTADOR
	if ( $sala->p1==null ) $sala->p1 = $player;
	else if ( $sala->p2==null ) $sala->p2 = $player;

	// FALTA:

	// SE A SALA FOR NOVA (OU SEJA, SE TIVER 0 MOVIMENTOS SALVOS) SORTEAR AQUI QUAL PLAYER COMEÇA
	// NO HTML, EM VEZ DE SORTEAR, COMO ERA ANTES, É NECESSÁRIO SÓ SELECIONAR O PLAYER ATUAL
	// USAR O ID DE PLAYER 1 E PLAYER 2 EM CADA "CAIXA" DE PLAYER
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

	function getSala(){
		if ( isset($_SESSION['sala']) ) return $_SESSION['sala'];

		$sala = new stdClass();
		$sala->token = token(8);
		$sala->p1 = null;
		$sala->p2 = null;
		$sala->current = null;
		$sala->movimentos = array();

		$_SESSION['sala'] = $sala;
		return $sala;
	}

	function token($tamanho){
		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$string = '';
		$max = strlen($characters) - 1;
		for ($i = 0; $i < $tamanho; $i++) $string .= $characters[mt_rand(0, $max)];
		return $string;
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

		<div class="players">
			<div class="p p1 smooth-fast" data-id="1"><h3>Player 1</h3></div>
			<div class="p p2 smooth-fast" data-id="2"><h3>Player 2</h3></div>
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

		<div class="salabox">
			<span>ID da sala: </span>
			<input name="sala" id="sala" value="<?=$sala->token?>" />
			<button class="copiar" onclick="copiar()">Copiar</button>
			<span class="tip">(caso queira entrar em uma sala cole o id e aperte ENTER)</span>
		</div>

		<span class="fimTurno oculto smooth-fast" onclick="terminarTurno(this)">Terminar turno</span>

		<script type="text/javascript">
			window.animando = false;

			let qual = Math.random();
			if ( qual<0.5 ) document.getElementsByClassName("p1")[0].classList.add("pSel");
			else document.getElementsByClassName("p2")[0].classList.add("pSel");
			
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