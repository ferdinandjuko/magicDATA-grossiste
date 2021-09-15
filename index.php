<?php
	session_start();
	include 'functions.php';
	// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
	verif_session();
	# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
	session_url();

?>

<?=template_header('Ilo grossiste')?>
<?=template_content('index')?>

		<!-- CONTENU -->
		<div id="container-page" class="container">
			<div id="search-cadre" class="row">
				<div class="col-md-6">
					<h3>Bienvenue dans Ilo grossiste</h3>
				</div>
                <div class="col-md-6">
                  <form action="" method="get">
                    <div id="input-group" class="input-group">
                      <input id="search" type="text" class="form-control" name="q" placeholder="Rechercher" value="<?= htmlentities($_GET['q'] ?? null) ?>"><button class="fa-search-btn"><i class="fas fa-search"></i></button>
                    </div>
                  </form>                   
                </div> 			
			</div>
	<!-- FIN FONCTION -->	

				<!-- navigation -->	
			<div id="navigation" class="row">
				<h5><i class='fas fa-home'></i> / Tableau de bord</h5>
			</div>
				<!-- Fin navigation -->	

			<div id="menu-tableau-de-bord" class="container">
				<div class="row">
				<hr id="hr">
				</div>
				<div class="row row1">
					  <div class="col colonne"><a href="read_stock.php"><img src="ilo/stock.png"><br><span class="tableau-label">Stocks</span></a></div>
					  <div class="col colonne"><a href="read_deffectueux.php"><img src="ilo/deffectueux.png"><br><span class="tableau-label">Deffectueux</span></a></div>
					  <div class="col colonne"><a href="read_vente.php"><img src="ilo/vendre.png"><br><span class="tableau-label">Ventes</span></a></div>
					  <div class="col colonne"><a href="read_caisse.php"><img src="ilo/caisses.png"><br><span class="tableau-label">Caisses</span></a></div>
					  <div class="col colonne"><a href="read_employe.php"><img src="ilo/employees.png"><br><span class="tableau-label">Employés</span></a></div>
					  <div class="col colonne"><a href="read_client.php"><img src="ilo/clients.png"><br><span class="tableau-label">Clients</span></a></div>
					  <div class="col colonne"><a href="read_fournisseur.php"><img src="ilo/fournisseur.png"><br><span class="tableau-label">Fournisseurs</span></a></div>
					  <div class="col colonne"><a href="read_facture.php"><img src="ilo/fichiers.png"><br><span class="tableau-label">Fichiers</span></a></div>
					  <div class="col colonne"><a href="read_historique.php"><img src="ilo/rapport.png"><br><span class="tableau-label">Rapports</span></a></div>					  
					  <div class="col colonne"><a href="read_aide.php"><img src="ilo/aides.png"><br><span class="tableau-label">Aides</span></a></div>				  
				</div>			
			</div>
			<div id="statistique" class="container">
				<div class="row">
					<div id="stat-vente" class="col">
						<h4>Statistique de Ventes</h4> <i class="far fa-question-circle icon-rigth"></i>
						<hr class="mb-0 pb-0">
						<!-- <button>test</button> -->
						<img src="graph1.php" id="graph" alt="">
					</div>
					
				</div>
				<div class="row">
					<div id="stat-evolution" class="col">
						<h4>Evolution</h4> <i class="far fa-question-circle icon-rigth"></i>
						<hr class="mb-0 pb-0">
						<img src="graph2.php" id="graph" alt="">
					</div>
					
				</div>
				
			</div>

<?php 
  unset($_SESSION['validate_code']);
 ?> 
			
<?=sweet_alert_notification()?>
<!--DEBUT FOOTER FONCTION-->
<?=template_footer()?>
