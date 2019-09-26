<?php
	session_start();

	if ( isset($_SESSION['jogadas'] ) ){
		$jogadas = $_SESSION['jogadas'];
		$jogadas = json_decode($jogadas, true);
	} else {
		$jogadas = array();
		$_SESSION['jogadas'] = "[]";
	}

	if ( isset($_POST['novaJogada']) ) {
		$jogada = $_POST['novaJogada'];
		
		if ( $jogada=="reset" ){
			$jogadas = array();
			$_SESSION['jogadas'] = "[]";
		} else {
			$jogadas[] = $jogada;
			$_SESSION['jogadas'] = json_encode($jogadas, true);
		}
	}
?>

<html>
  <head>
    <title>Jogo da Velha</title>
	  <style type="text/css" >
		  .jogo { display: block; position: relative; width: 612px; height: 612px; }
		  .jogo .bloco { display: inline-block; width: 200px; height: 160px; background: #ccc; cursor: pointer; margin: 0; padding: 0; border: none; text-align: center; padding-top: 40px; font-size: 60px; overflow: hidden; }
		  .jogo .bloco:hover { background: #afa; }
		  .jogo .bloco.usado:hover { background: #faa; }
	  </style>
  </head>
  <body>
	  <h3>Jogo da velha - Caio Felipe Giasson</h3>

	 <form action="velha.php" method="post" class="jogo" id="oform">
		 <div class="bloco pos-0-0" onclick="fazerJogada(this)"></div>
		 <div class="bloco pos-0-1" onclick="fazerJogada(this)"></div>
		 <div class="bloco pos-0-2" onclick="fazerJogada(this)"></div>
		 <div class="bloco pos-1-0" onclick="fazerJogada(this)"></div>
		 <div class="bloco pos-1-1" onclick="fazerJogada(this)"></div>
		 <div class="bloco pos-1-2" onclick="fazerJogada(this)"></div>
		 <div class="bloco pos-2-0" onclick="fazerJogada(this)"></div>
		 <div class="bloco pos-2-1" onclick="fazerJogada(this)"></div>
		 <div class="bloco pos-2-2" onclick="fazerJogada(this)"></div>
		 <input type="hidden" name="novaJogada" value='' id='jogada'/>
	  </form>
	  
	  <h4 id="qualVez"></h4>
	  
	  <button onclick="resetar()">Resetar</button>
	  
	  <input type="hidden" value='<?=json_encode($jogadas, true)?>' id="jogadas"/>
	  
	  <script type="text/javascript">
		let campoJogadas = document.getElementById("jogadas");
		  console.log(campoJogadas.value);
		let jogadas = JSON.parse( campoJogadas.value );
		  
		for ( j of jogadas ) {
			if ( j.length<5 ) continue;
			jj = JSON.parse(j);
			document.getElementsByClassName(jj.posicao)[0].innerHTML = jj.quem;
			document.getElementsByClassName(jj.posicao)[0].classList.add("usado");
		}
		  
		window.quemJoga = "";
		if ( jogadas.length%2==0 ) window.quemJoga = "O";
		else window.quemJoga = "X";
		  
		document.getElementById("qualVez").innerHTML = `Agora é a vez do ${window.quemJoga}`;
		  
		let fazerJogada = function(obj){
			if ( obj.classList.contains("usado") ) return;
			
			let jog = {
				quem : window.quemJoga,
				posicao : obj.classList[1],
			}
			
			document.getElementById("jogada").value = JSON.stringify(jog);
			document.getElementById("oform").submit();	
		}
		
		let resetar = function(){
			document.getElementById("jogada").value = "reset";
			document.getElementById("oform").submit();	
		}
	  </script>
  </body>
</html>
