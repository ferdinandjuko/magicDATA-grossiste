<?php 

include 'functions.php';
session_start();
$pdo = pdo_connect_mysql();
$msg = '';

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();


 
?>

<?=template_header('help')?>

<?=template_content('index')?>
        
        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Aides</h5>
                <div class="navigation-retour-active">
                  <a href="index.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                 
            </div>
                <!-- Fin navigation --> 
            <div id="cadre-contenu">
                <div class="cadre-title">
                    <!-- <h2><i class="fas fa-plus-circle"></i> PAGES D'AIDES</h2> -->
                </div>
                <div class="container">
                    <br><br><br><br>    
                     <h1 class="error">EN COURS DE MAINTENANCE</h1>         
                </div>
                
            </div>
        </div>


<?=template_footer()?>

