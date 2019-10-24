<?php
	session_start();

	// GERANDO OU RECUPERANDO OS DADOS DA SALA
	if ( isset($_GET['sala']) ) {
		$_SESSION['sala'] = $_GET['sala'];
		$sala = getSala($_GET['sala']);
	}
	else if ( isset($_SESSION['sala']) ) $sala = getSala($_SESSION['sala']);
	else $sala = novaSala();

	// ANOTANDO O TURNO ATUAL
	$turnoAtual = count($sala->movimentos);

	// GERANDO OU RECUPERANDO A TOKEN DO PLAYER
	$player = getPlayer();

	// ATRIBUINDO O PLAYER AO PRIMEIRO SLOT DISPONÍVEL
	// SE NÃO TIVER SLOT DE PLAYER DISPONÍVEL ELE FICARÁ COMO ESPECTADOR
	if ( $sala->p1==null ) $sala->p1 = $player;
	else if ( $sala->p1!=$player || $sala->p2==null ) $sala->p2 = $player;

	// DEFININDO DE QUEM É A VEZ
	if ( $sala->current == null ) $sala->current = $sala->p2;
	$p1_class = $sala->current == $sala->p1 ? 'pSel' : '';
	$p2_class = $sala->current == $sala->p2 ? 'pSel' : '';	

	if ( $sala->p2==null ) $fraseVoce = "AGUARDANDO SEU OPONENTE";
	else if ( $sala->current == $player ) $fraseVoce = "É A SUA VEZ!";
	else if ( $sala->p1==$player || $sala->p2==$player ) $fraseVoce = "É A VEZ DO SEU OPONENTE!";
	else $fraseVoce = "VOCÊ ESTÁ ASSISTINDO A PARTIDA";
	
	saveSala($sala);

	// FICAR PERGUNTANDO SE "JÁ CHEGOU MINHA VEZ" QUANDO NÃO FOR MEU TURNO
	// RECONHECER QUANDO O OPONENTE ENTROU NA SALA
	// DAR UM AVISO SONORO A CADA JOGADA

	// FUNÇÕES AUXILIARES ABAIXO
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
		<link rel="icon"  type="image/png"  href="img/balao.png">
		<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
		<title>Dodots</title>
		<style type="text/css">
			* { font-family: 'Roboto', sans-serif; } 
			body { margin: 0; }

			.fundo { display: block; position: absolute; top: 0; left: 0; height: 100%; z-index: -10; }

			.monte { display: block; width: 100%; height: 60px; background: linear-gradient( #88d2fd, #a5c3fb); margin: 10px 0; text-align: center; border: solid 2px #eee; z-index: 10; }
			.monte.mSel { border: solid 2px #aaa; }

			.dodot { display: inline-block; width: 50px; height: 50px; background: none; border: none; margin: 5px; cursor: pointer; z-index: 10;  }
			.dodot img { display: block; position: relative; height: 100%; }
			.dodot.oculto { width: 0; height: 0; opacity: 0; margin: 0; border: solid 0px #ccc; }
			.dodot.dotSel { }

			.players { display: block; position: relative; width: 100%; height: 220px; clear: both; z-index: 10; }

			.p1 { display: block; position: relative; width: 49%; background: linear-gradient( #61ff7b, #ceffa7); height: 200px; float: left; border: solid 2px #dfd; color: #4dbe00; }
			.p1.pSel { border: solid 2px #090; ; }
			.p2 { display: block; position: relative; width: 49%; background: linear-gradient( #a7fff2, #61b5ff); height: 200px; float: right; text-align: right; border: solid 2px #ddf; color: #3fb7ff; }
			.p2.pSel { border: solid 2px #009; }

			.p h3{ padding-left: 20px;  }

			.smooth-fast { -webkit-transition: all 0.7s; -moz-transition: all 0.7s; -o-transition: all 0.7s; transition: all 0.7s; }
			.clear { display: block; position: relative; width: 100%; height: 10px; clear: both; }

			.fimTurno { display: block; position: relative; clear: both; width: 200px; height: 50px; background: #aaa; text-align: center; margin: 0 auto; overflow: hidden; color: #fff; padding-top: 15px; cursor: pointer; z-index: 10;  }
			.fimTurno.oculto { width: 0; padding-top: 0; opacity: 0; height: 65px; }

			.salabox { display: block; position: relative; width: 100%; text-align: center; font-size: 14px; z-index: 10; margin: 0; background: #ff8783; padding: 5px 0;}
			.salabox input { display: inline-block; border: solid 1px #ccc; padding: 4px; text-align: center; margin: 0 5px; }
			.salabox .copiar,.salabox .novaSala, .salabox .irSala { display: inline-block; border: none; background: #ccc; padding: 4px 10px; font-size: 12px; cursor: pointer; }

			.tip { display: block; position: relative; width: 100%; text-align: center; font-size: 12px; color: #777; font-style: italic; z-index: 10;  }
			.fraseVoce { color: #999; text-align: center; }

			.preload { display: none; }
		</style>
	</head>
	<body>

		<img src="img/balaoOutline.png" class="preload" />

		<img class="fundo" src="img/fundo.png"/>

		<div class="salabox">
			<span>ID da sala: </span>
			<input name="sala" id="sala" value="<?=$sala->token?>" />
			<input type="hidden" name="eu" id="eu" value="<?=$_SESSION['player']?>" />
			<input type="hidden" name="turnoAtual" id="turnoAtual" value="<?=$turnoAtual?>" />
			<button class="copiar" onclick="copiar()">Copiar</button>
			<button class="novaSala" onclick="novaSala()">Criar sala</button>
			<button class="irSala" onclick="irSala()">Acessar sala</button>
			<!--
				INSPEÇÃO DE TURNOS VIA JSON
				<span class="tip"><?= json_encode($sala, true); ?> <br />VC <?=$_SESSION['player']?></span>
			-->
		</div>

		<p id="fraseVoce" class="fraseVoce"><?=$fraseVoce?></p>

		<div class="players">
			<div class="p p1 smooth-fast <?=$p1_class?>"  id="p<?=$sala->p1?>" data-id="<?=$sala->p1?>" title="<?=$sala->p1?>"><h3>PLAYER 1</h3></div>
			<div class="p p2 smooth-fast <?=$p2_class?>"  id="p<?=$sala->p2?>" data-id="<?=$sala->p2?>" title="<?=$sala->p2?>"><h3>PLAYER 2</h3></div>
		</div>

		<span class="fimTurno oculto smooth-fast" onclick="terminarTurno(this)">Terminar turno</span>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="1"><img src="img/balao.png" /></span>
		</div>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="2"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="3"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="4"><img src="img/balao.png" /></span>
		</div>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="5"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="6"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="7"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="8"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="9"><img src="img/balao.png" /></span>
		</div>
	
		<div class="monte smooth-fast">
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="10"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="11"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="12"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="13"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="14"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="15"><img src="img/balao.png" /></span>
			<span class="dodot cadaDot" onclick="clicou(this)" data-id="16"><img src="img/balao.png" /></span>
		</div>

		<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

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
				if ( obj.classList.contains("coletado") ) return;
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
						obj.innerHTML = `<img src="img/balao.png" />`;
						if ( document.getElementsByClassName("dotSel").length == 0 ){
							obj.parentElement.classList.remove("mSel");
							document.getElementsByClassName("fimTurno")[0].classList.add("oculto");
						}
					} else {
						if ( !obj.parentElement.classList.contains("mSel") )
							obj.parentElement.classList.add("mSel");

						obj.classList.add("dotSel");
						obj.innerHTML = `<img src="img/balaoOutline.png" />`;
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
					document.getElementsByClassName("pSel")[0].innerHTML = h + `<span class="dodot smooth-fast "><img src="img/balao.png" /></span>`;
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

			let setTurno = function(p){
				for ( player of document.getElementsByClassName("p") )
					player.classList.remove("pSel");

				setTimeout(function(){ document.getElementById(`p${p}`).classList.add("pSel"); }, 100);
			}


			let sendTurno = function(player, dadosTurno){
				var data = {
					turno : JSON.stringify(dadosTurno),
					player : player,
					action : 'saveTurn'
				};

				$.post(`req.php`, data, function(result){
					// location.reload();
				});
			}

			let novaSala = function(){
				let sala = prompt("Digite um nome para a sala.\nUse somente letras e números.");
				sala = sala.replace(/\W/g, '');
				document.location = `https://caini.tech/web/dodots.php?sala=${sala}`;
			}

			let irSala = function(){
				let sala = prompt("Digite o nome da sala que deseja acessar.");
				sala = sala.replace(/\W/g, '');
				document.location = `https://caini.tech/web/dodots.php?sala=${sala}`;
			}

			let updateSala = function(){
				if ( window.stopUpdate ) return;

				const playerEu = document.getElementById("eu").value;
				const turnoAtual = 1 * document.getElementById("turnoAtual").value;

				$.post(`req.php`, {
					action: `getSala`
				}, function(dadosSala){
					const p1 = getP1();
					const p2 = getP2();
					if ( ( !p2 || p1==p2 ) && (dadosSala.p2) && (dadosSala.p1!=dadosSala.p2) ) location.reload();

					const semOponente = dadosSala.p1==dadosSala.p2;
					if ( turnoAtual == dadosSala.movimentos.length ){
						// ATUALIZANDO A CONTAGEM DE TURNOS
						document.getElementById("turnoAtual").value = parseInt(turnoAtual) + 1;

						// ATUALIZANDO A MENSAGEM DO USUÁRIO
						let msg = ``;
						if ( dadosSala.p2==null || semOponente ) msg = "AGUARDANDO SEU OPONENTE";
						else if ( dadosSala.current == playerEu ) msg = "É A SUA VEZ!";
						else if ( dadosSala.current == dadosSala.p1 || dadosSala.current == dadosSala.p2 ) msg = "É A VEZ DO SEU OPONENTE!";
						else msg =  "VOCÊ ESTÁ ASSISTINDO A PARTIDA";
						document.getElementById("fraseVoce").innerHTML = msg;

						let p, dot;
						for ( mov of dadosSala.movimentos ) {
							p = document.getElementById(`p${mov.player}`);
							for ( b of mov.turno ) {
								getDot(b).remove();
								p.innerHTML = p.innerHTML + `<span class="dodot cadaDot coletado" data-id="${b}"><img src="img/balao.png" /></span>`;
							}
						}

						if ( !semOponente ) {
							checkVitoria(dadosSala.current);
							setTurno(dadosSala.current);
						}
					} else if ( turnoAtual==1 && !semOponente && document.getElementById(`p${dadosSala.current}`).length==0 ) {
						location.reload();
					}

				});
			}

			let checkVitoria = function(playerAtual){
				const playerEu = document.getElementById("eu").value;
				const turnoAtual = 1 * document.getElementById("turnoAtual").value;

				if ( document.getElementsByClassName("coletado").length==16 ){
					// JOGO ACABOU!!!
					const vencedor = playerAtual == playerEu ? `VOCÊ` : `SEU OPONENTE`;

					// const msg = `FIM DO JOGO!\n\nO VENCEDOR FOI ${vencedor}!\n\nO JOGO DUROU ${turnoAtual} TURNOS.`;
					// alert(msg);

					const msgHtml = `FIM DO JOGO! O VENCEDOR FOI ${vencedor}! O JOGO DUROU ${turnoAtual} TURNOS.`;
					document.getElementById("fraseVoce").innerHTML = msgHtml;
					for ( monte of document.getElementsByClassName("monte") ){
						monte.innerHTML = `<p>${msgHtml}</p>`;
					}
					window.stopUpdate = true;
				}
			}

			let getDot = function(dotId){
				let listaDots = document.getElementsByClassName("cadaDot");
				for ( dot of listaDots ) if ( dotId==dot.dataset.id ) return dot;
				return null;
			}

			let getP1 = function(){
				return document.getElementsByClassName(`p1`)[0].dataset.id;
			}

			let getP2 = function(){
				return document.getElementsByClassName(`p2`)[0].dataset.id;
			}

			// COMEÇANDO O UPDATE AUTOMÁTICO DA SALA
			// window.stopUpdate = true;
			setInterval(() => {
				updateSala();
			}, 500);
		</script>

	</body>


</html>