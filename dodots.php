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
		</style>
	</head>
	<body>

		<div class="players">
			<div class="p p1 smooth-fast"><h3>Player 1</h3></div>
			<div class="p p2 smooth-fast"><h3>Player 2</h3></div>
		</div>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
		</div>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
		</div>

		<div class="monte smooth-fast">
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
		</div>
	
		<div class="monte smooth-fast">
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
			<span class="dodot cadaDot smooth-fast" onclick="clicou(this)"></span>
		</div>

		<span class="fimTurno oculto smooth-fast" onclick="terminarTurno(this)">Terminar turno</span>

		<script type="text/javascript">
			window.animando = false;

			let qual = Math.random();
			if ( qual<0.5 ) document.getElementsByClassName("p1")[0].classList.add("pSel");
			else document.getElementsByClassName("p2")[0].classList.add("pSel");
			
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

				document.getElementsByClassName("mSel")[0].classList.remove("mSel");

				for ( dot of dotList ) {
					dot.classList.add("oculto");
					let h = document.getElementsByClassName("pSel")[0].innerHTML;
					document.getElementsByClassName("pSel")[0].innerHTML = h + `<span class="dodot smooth-fast "></span>`;
				}

				// for ( dot of document.getElementsByClassName("novoDot") )
				// 	dot.classList.remove("oculto");

				setTimeout(function(){
					while ( document.getElementsByClassName("dotSel").length>0 ){
						document.getElementsByClassName("dotSel")[0].remove();
					}
				}, 700);
				
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
		</script>

	</body>


</html>