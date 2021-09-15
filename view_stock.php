<?php
include 'functions.php';
# Démarrage de la session
session_start();

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();

$upload_dir = 'uploads/stock/';
// Connect to MySQL database
$pdo = pdo_connect_mysql();

// Check if the contact id exists, for example update.php?id=1 will get the contact with the id of 1
if (isset($_SESSION['id_viewstock']) ) {
    # code...
    // Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
    $query = "SELECT * FROM stock WHERE ID_STOCK=".$_SESSION['id_viewstock'];
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Si le ID stock n'est pas défini
    header("location: read_stock.php");
    exit(); 
}


$icon_type = "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";
    
       
?>

<?=template_header('Detail stock')?>

<?=template_content('stocks')?>
        
        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Inventaire / Historiques Stocks / Détails stock</h5>
                <div class="navigation-retour-active">
                  <a href="read_stock.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                 
            </div>
                <!-- Fin navigation --> 
            <div id="cadre-contenu">
                <div class="cadre-title">
                    <h2><i class="fas fa-plus-circle"></i> DÉTAILS DU STOCK</h2>
                </div>
                <div class="container">
                    <form name="myForm" id="form-add2" action="" method="post">
                        <div class="row">
                            <div class="col-md-6" id="limit_detail">
                                <div class="form-group d-flex"> 
                                    <div class="col-md-5">
                                       <label for="nom_produit">NOM DE PRODUIT</label> 
                                    </div>
                                    <div class="col-md-7">
                                       <?=$stock['NOM_PRODUIT'] ;?>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group d-flex"> 
                                    <div class="col-md-5">
                                       <label for="reference">DESIGNATION</label> 
                                    </div>
                                    <div class="col-md-7">
                                       <?=$stock['REFERENCE']." ".$stock['QUANTITE_UNITE']." ".$stock['UNITE'];?> 
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group d-flex"> 
                                    <div class="col-md-5">
                                       <label for="prix_unitaire">PRIX UNITAIRE</label> 
                                    </div>
                                    <div class="col-md-7">
                                       <?=$stock['PRIX_UNITAIRE'] ;?>
                                    </div>
                                </div>
                                <hr> 
                                <div class="form-group d-flex"> 
                                    <div class="col-md-5">
                                       <label for="quantite">QUANTITE</label> 
                                    </div>
                                    <div class="col-md-7">
                                       <?=$stock['QUANTITE'] ;?> 
                                    </div>
                                </div> 
                                <hr>                             
                                <div class="form-group d-flex"> 
                                    <div class="col-md-5">
                                       <label for="date_achat">DATE D'ACHAT</label> 
                                    </div>
                                    <div class="col-md-7">
                                       <?=$stock['DATE_ACHAT'] ;?> 
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group d-flex"> 
                                    <div class="col-md-5">
                                       <label for="fournisseur">FOURNISSEUR</label> 
                                    </div>
                                    <div class="col-md-7">
                                       <?=$stock['FOURNISSEUR'] ;?> 
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group d-flex"> 
                                    <div class="col-md-5">
                                       <label for="description">DESCRIPTION</label> 
                                    </div>
                                    <div class="col-md-7">
                                       <?=$stock['DESCRIPTION'] ;?>  
                                    </div>
                                </div>                               
                            </div>
                            <div class="col-md-6">
                                <img src="<?=$upload_dir.$stock['IMAGE']?>" id="image_stock" alt="image stock">                                
                            </div> 
                        </div>   
                    </form>          
                </div>
                
            </div>
        </div>


<?=template_footer()?>
