<?php
include 'functions.php';
session_start();
 
// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();


// Connect to MySQL database
$pdo = pdo_connect_mysql();
// Get the page via GET request (URL param: page), if non exists default the page to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Number of records to show on each page
$records_per_page = 10;

// Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
$query = "SELECT deffectueux.ID_DEFF, deffectueux.ID_GESTION, deffectueux.ID_FOURNISSEUR, deffectueux.DESIGNATION, deffectueux.QUANTITE, deffectueux.DATE_AJOUT, deffectueux.DESCRIPTION,deffectueux.PRIX_UNITAIRE, deffectueux.FOURNISSEUR, deffectueux.AJOUTER_PAR FROM gestion_stock, deffectueux WHERE (deffectueux.ID_GESTION=gestion_stock.ID_GESTION) AND (gestion_stock.SESSION_ID=".$_SESSION['id'].")";
$params = $search= "";
$sortable = ["nom_produit", "quantite", "date_ajout", "prix_totale"];
$pag = "";
$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (deffectueux.DESIGNATION LIKE $params OR deffectueux.DATE_AJOUT LIKE $params)";
}

//organisation
$pag = " ORDER BY deffectueux.ID_DEFF DESC";
if(!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)){
    $direction = $_GET['dir'];
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $pag = " ORDER BY " . $_GET['sort'] . " $direction";
}

$pag .= " LIMIT :current_page, :record_per_page";
$query = $query.$search.$pag;

# Si le query est déjà definie
/*if (isset($_SESSION['query'])) {
  # code...
  $query=$_SESSION['query'];
  unset($_SESSION['query']);
}
*/
$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$defective = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of contacts, this is so we can determine whether there should be a next and previous button
$num_deffectueux = $pdo->query("SELECT COUNT(*) FROM gestion_stock, deffectueux WHERE (deffectueux.ID_GESTION=gestion_stock.ID_GESTION) AND (gestion_stock.SESSION_ID=".$_SESSION['id'].")".$search)->fetchColumn();


$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_deffectueux / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Aucune stock deffectueux ajouté pour le moment</h3>";

// Define variables and initialize with empty values

$icon_type = "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";
    
if(isset($_POST["detail_deffectueux"])) {

        function data_input($data) {
            
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Prendre l'id deffectueux

        $input_detail_id = data_input($_POST["detaildeff_id"]);


        // Trouver le nom du produit à partir de son ID

        $sql_detail_deffectueux = "SELECT * FROM deffectueux WHERE deffectueux.ID_DEFF=".$input_detail_id;   
        $query_detail_deffectueux= $pdo->prepare($sql_detail_deffectueux);
        $query_detail_deffectueux->execute();
        $row_detail_deffectueux = $query_detail_deffectueux->fetch(PDO::FETCH_BOTH);

        $_SESSION["detaildeff_id"] = $input_detail_id;

 }          

?>

<?=template_header('Deffectueux')?>
<?=template_content('deffectueux')?>
     
        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-layer-group"></i> GESTION STOCKS</h3>
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
                <h5><i class='fas fa-home'></i> / Inventaire / Stocks déffectueux</h5>
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
          <div>
            <div class="cadre-title">
                <h2><i class="fas fa-play"></i> Stocks déffectueux</h2>
                <div class="cadre-autre-title">
                    <h2><a class="autre-btn" href="read_stock.php"><i class="fas fa-stream"></i> Voir Historique Stocks</a></h2>
                    <h2><a class="autre-btn" href="read_gestion_stock.php"><i class="fas fa-layer-group"></i> Voir Gestion stock</a></h2>                     
                </div>                   
            </div>
            <div id="cadre-add-new">
                <a class="add-new" href="add_deffectueux.php" class="create-contact"><i class="fas fa-cart-plus"></i> Ajouter un stock deffectueux</a>
            </div>
            
        	<table id="table" class="table"> 
                <thead class="thead-facture">
                    <tr>
                        <td>#</td>
                        <td><?= tri('date_ajout', 'Date d\'ajout', $_GET) ?></td>
                        <td><?= tri('nom_produit', 'Nom du produit', $_GET) ?></td>
                        <td>Quantité</td>
                        <td>Prix total deff.</td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody class="tbody">
                    <?php foreach ($defective as $deffectueux): ?>
                    <tr>
                        <td align='right'><?= ++$ligne?></td>
                        <td><?=$deffectueux['DATE_AJOUT']?></td>
                        <td><?=ucfirst($deffectueux['DESIGNATION'])?></td>
                        <td><?=$deffectueux['QUANTITE']?></td>
                        <td><?=price($deffectueux['QUANTITE']*$deffectueux['PRIX_UNITAIRE'])?></td>
                        <td class="actions">
                          <form method="post">
                            <input type="" name="detaildeff_id" value="<?=$deffectueux['ID_DEFF']?>" hidden="true">
                            <button class="detail-btn" type="submit" name="detail_deffectueux">Détails</button>
                          </form>
                        </td>
                        <td class="actions">
                          <form method="post">
                            <a href="update_deffectueux.php" class="edit"><i class="fas fa-edit"></i></a>
                            <input type="" name="supdeff_id" value="<?=$deffectueux['ID_DEFF']?>" hidden="true">
                            <button class="btn-table" type="submit" class="btn btn-primary" name="suppression_deffectueux"><i class="fas fa-trash-alt"></i></button>
                          </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($num_deffectueux < 1 || $page > $nbre_page): ?>
              <?=$vide?>
            <?php endif; ?>
            <br>
            <!-- //// PAGINATION //// -->
                <?= template_pagination('deffectueux') ?>
            <!-- FIN PAGINATION -->  
        	
        </div>


<!-- ///////////////////////////////// EXECUTION MODAL ///////////////////////////////// -->



        <!-- ///////////////////////// MODAL DETAILS //////////////////////////// -->
  <?php if (isset($_SESSION["detaildeff_id"] )  ) :?>
          <div class="modal fade" id="modal-detail" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
            
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title"><img src="ilo/icons-100.png"> Details du stocks deffectueux</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                  <div>
                    <form class="row" id="" action="" method="get" style="text-align: left;">
                            <div class="bloc-form-1 col-md-6">
                                <div class="form-group">
                                    <label for="designation">DESIGNATION : </label>
                                    <p><?=$row_detail_deffectueux['DESIGNATION']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="quantite">QUANTITE : </label>
                                    <p><?=$row_detail_deffectueux['QUANTITE']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="date_ajout">DATE AJOUT : </label>
                                    <p><?=date("d-m-Y", strtotime($row_detail_deffectueux['DATE_AJOUT']))?></p>
                                </div>
                                <div class="form-group">
                                    <label for="description">FOURNISSEUR : </label>
                                    <p><?=$row_detail_deffectueux['FOURNISSEUR']?></p>
                                </div>                                
                            </div>
                            <div class="bloc-form-2 col-md-6">

                                <div class="form-group">
                                    <label for="prix_totale">PRIX TOTAL : </label>
                                    <p><?=$row_detail_deffectueux['PRIX_UNITAIRE']*$row_detail_deffectueux['QUANTITE']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="ajoute_par">AJOUTE PAR : </label>
                                    <p><?=$row_detail_deffectueux['AJOUTER_PAR']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="description">DESCRIPTION : </label>
                                    <p><?=$row_detail_deffectueux['DESCRIPTION']?></p>
                                </div>                                
                            </div>
                    </form> 
                  </div>
                </div>
                <div class="modal-footer">

                </div>
              </div>
            </div>
          </div>
  <?php endif; ?>
  <?php 
    unset($_SESSION["detaildeff_id"] );
  ?>

<!--SWEET ALERT -->
<?=sweet_alert_notification()?>

<?=template_footer()?>