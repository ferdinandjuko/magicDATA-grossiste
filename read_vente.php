
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

// Prepare the SQL statement and get records from our VENTE table, LIMIT will determine the page
$query = "SELECT * FROM vente, client WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (client.ID=".$_SESSION['id'].")";
$params = $search= "";
$sortable = ["nom_produit", "prix_unitaire", "quantite", "prix_totale", "benefice", "date_vente", "client"];
$pag = "";
$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (vente.NOM_PRODUIT LIKE $params OR vente.REFERENCE LIKE $params OR vente.PRIX_UNITAIRE LIKE $params OR vente.QUANTITE LIKE $params OR vente.PRIX_TOTALE LIKE $params OR vente.BENEFICE LIKE $params OR vente.DATE_VENTE LIKE $params OR vente.CLIENT LIKE $params OR vente.STATUT LIKE $params OR vente.AJOUTE_PAR LIKE $params)";
}

//organisation
if(!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)){
    $direction = $_GET['dir'] ?? 'asc';
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $pag = " ORDER BY " . $_GET['sort'] . " $direction";
}

$pag .= " LIMIT :current_page, :record_per_page";
$query = $query.$search.$pag;

# Si le query est déjà definie
if (isset($_SESSION['query_vente'])) {
  $query=$_SESSION['query_vente'];
  unset($_SESSION['query_vente']);
}

// Prepare the SQL statement and get records from our VENTE table, LIMIT will determine the page
$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of VENTE
$num_vente = $pdo->query("SELECT COUNT(*) FROM vente, client WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (client.ID=".$_SESSION['id'].")".$search)->fetchColumn();

$numero_page = "";
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_vente / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Aucune vente ajouté pour le moment</h3>";

// Define variables and initialize with empty values

$icon_type = "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";
$ID_VENTE = "";
    
if(isset($_POST["detail_vente"]) || isset($_POST['update_vente']) || isset($_POST['suppression_vente']) || isset($_POST["submit_supp_vente_yes"]) ) {

        function data_input($data) {
            
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Prendre l'id deffectueux

        $input_detail_id = data_input($_POST["vente_id"]);


        // Trouver le nom du produit à partir de son ID

        $sql_detail_vente = "SELECT vente.ID_VENTE, vente.ID_FACTURE, vente.ID_GESTION, vente.ID_CLIENT, vente.NOM_PRODUIT, vente.REFERENCE, vente.UNITE, vente.QUANTITE_UNITE, vente.PRIX_UNITAIRE, vente.QUANTITE, vente.BENEFICE, vente.DATE_VENTE, vente.HEURE_VENTE, vente.CLIENT, vente.STATUT, vente.AJOUTE_PAR FROM vente, CLIENT WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND vente.ID_VENTE=".$input_detail_id." AND client.ID= ".$_SESSION['id'];

        $query_detail_vente= $pdo->prepare($sql_detail_vente);
        $query_detail_vente->execute();
        $row_detail_vente = $query_detail_vente->fetch(PDO::FETCH_BOTH);

        $designation = $row_detail_vente['NOM_PRODUIT']." ".$row_detail_vente['REFERENCE']." ".$row_detail_vente['QUANTITE_UNITE']." ".$row_detail_vente['UNITE'];

        $statut = $row_detail_vente['STATUT'];

    // LANCEMENT MODAL DETAIL 
    if (isset($_POST["detail_vente"])) {
        // code...
        $ID_DETAIL_VENTE = $input_detail_id;
    }
        
    // RECUPERATION EN SESSION ID VENTE POUR MODIFICATION
    if (isset($_POST['update_vente'])) {
        // code...
        if ($_SESSION['user'] == 'admin' || $statut <> "paye") {
            // code...
            $_SESSION['id_vente'] = $input_detail_id;

            header("location: update_vente.php");
            exit();            
        } 
    }

    // SUPPRESSION VENTE 
    if (isset($_POST['suppression_vente']) || (isset($_POST["submit_supp_vente_yes"]))) {

      $ID_VENTE = $input_detail_id;

    }

      
}
if (isset($_POST['btn-validate-del-vente'])) {

    // Define variables and initialize with empty values
    $pswd = "";
    $pswd_err = "";
    $icon_type = "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";

    function data_input($data) {
          $data = trim($data);
          $data = stripslashes($data);
          $data = htmlspecialchars($data);
          return $data;
        } 

  // Prendre l'id client

      $input_vente_id = data_input($_POST["vente_id"]);

      // Detecter si le client existe
      $sql= "SELECT vente.ID_VENTE, vente.ID_FACTURE, vente.ID_GESTION, vente.ID_CLIENT, vente.NOM_PRODUIT, vente.REFERENCE, vente.UNITE, vente.QUANTITE_UNITE, vente.PRIX_UNITAIRE, vente.QUANTITE, vente.BENEFICE, vente.DATE_VENTE, vente.HEURE_VENTE, vente.CLIENT, vente.STATUT, vente.AJOUTE_PAR FROM vente, CLIENT WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND vente.ID_VENTE=".$input_vente_id." AND client.ID= ".$_SESSION['id'];

      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $vente = $stmt->fetch(PDO::FETCH_BOTH);

      $designation = $vente['NOM_PRODUIT']." ".$vente['REFERENCE']." ".$vente['QUANTITE_UNITE']." ".$vente['UNITE'];


      //Verificatiion si le client n'existe pas == exit
      if (!$vente) {
          exit('Ce vente n\'existe pas!');
      } 

    // Validate PSWD
    $input_pswd = data_input($_POST["pswd"]);
    if(empty($input_pswd)) {
        $pswd_err = "Veuillez insérer votre mot de passe.";
        $vente_id_mdp_err =  $input_vente_id;
    } else{
        $pswd = $input_pswd;
    }
    // Define Pseudo
    $pseudo = $_SESSION['username'];

    if ($_SESSION['user'] <> "modo" && empty($pswd_err)) {

        // select inscription
        $sql_inscription="SELECT * FROM inscription WHERE 
        PSEUDO=? AND PSWD=? ";
        $query_inscription = $pdo->prepare($sql_inscription);
        $query_inscription->execute(array($pseudo,$pswd));
        $row_inscription = $query_inscription->fetchAll(PDO::FETCH_BOTH);

        if($query_inscription->rowCount() > 0) {

              # CODE DE SUPPRESSION VENTE :

                // Select VENTE 
                $sql="SELECT * FROM vente WHERE ID_VENTE =".$input_vente_id;
                $query_vente = $pdo->prepare($sql);
                $query_vente->execute();
                $row_vente = $query_vente->fetch(PDO::FETCH_BOTH);

                $id_client = $row_vente['ID_CLIENT'];
                $id_gestion = $row_vente['ID_GESTION'];
                $id_facture = $row_vente['ID_FACTURE'];

                // Select ID GESTION CAISSE 

                $sql="SELECT * FROM caisse_vente WHERE ID_VENTE =".$input_vente_id;
                $query_gestion_caisse = $pdo->prepare($sql);
                $query_gestion_caisse->execute();
                $row_gestion_caisse = $query_gestion_caisse->fetch(PDO::FETCH_BOTH);

                $id_gestion_caisse = $row_gestion_caisse['ID_GESTION_CAISSE'];
                $id_caisse_vente = $row_gestion_caisse['ID_CAISSE_VENTE'];

                  /*
                    SUPP VENTE
                    1.CAISSE VENTE
                    2.GESTION CAISSE
                    3.VENTE
                    4.FACTURE
                  */

                // 1. DEL CAISSE VENTE
                $sql1 = "DELETE FROM caisse_vente WHERE ID_VENTE =  '".$input_vente_id."'";
                $stmt = $pdo->prepare($sql1);
                $ex_del_caisse_vente=$stmt->execute();

                // 2. DELETE GESTION CAISSE
                if ($ex_del_caisse_vente <> false) {
                $stmt = $pdo->prepare("DELETE FROM gestion_caisse WHERE ID_GESTION_CAISSE =".$id_gestion_caisse);
                $ex_del_gestion_caisse = $stmt->execute();                   
                }
                // 3. DEL VENTE
                if ($ex_del_gestion_caisse <> false) {
                $sql2 = "DELETE FROM vente WHERE ID_VENTE = '".$input_vente_id."'";
                $stmt = $pdo->prepare($sql2);
                $ex_del_vente=$stmt->execute();
                }

                // 4. DEL FACTURE
                if ($ex_del_vente <> false) {
                $sql3 = "DELETE FROM facture WHERE ID_FACTURE = '".$id_facture."'";
                $stmt = $pdo->prepare($sql3);
                $ex_del_facture=$stmt->execute();
                }  

              #INSERTION HISTORIQUE SUPPRESSION VENTE
              if ($ex_del_vente <> false) {
                  // Définitions des varialbes pour l'insertion dans l'historique
                  $id_resp = $_SESSION['id'];
                  $date_historique = date("Y-m-d");
                  $heure_historique = date("H:i:s");
                  $action = "supprimé une vente";
                  $type = "suppression";
                  $ajouter_par = $row_vente['AJOUTE_PAR'];
                  $supprimer_par = $_SESSION['name'];                 

                  $avant = "Designation : ".$designation." <br> "."Prix_unitaire : ".$vente['PRIX_UNITAIRE']." Ar <br> "."Quantité : ".$vente['QUANTITE']." <br> "."Prix Total : ".$vente['PRIX_TOTALE']." Ar <br> "."Bénéfice : ".$vente['BENEFICE']." Ar <br> "."Date et heure vente : ".$vente['DATE_VENTE']." ".$vente['HEURE_VENTE']." <br> "."Client : ".$vente['CLIENT']." <br> "."Statut : ".$vente['STATUT']." <br> "."Ajouté par : ".$vente['AJOUTE_PAR'];

                    // prepare sql and bind parameters
                    $sql="INSERT INTO historique (ID, DATE_HISTORIQUE, HEURE_HISTORIQUE, ACTION, TYPE, DESIGNATION, AJOUTER_PAR, AVANT, SUPPRIMER_PAR)
                    VALUES (:ID, :DATE_HISTORIQUE, :HEURE_HISTORIQUE, :ACTION, :TYPE, :DESIGNATION, :AJOUTER_PAR, :AVANT, :SUPPRIMER_PAR)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':ID', $id_resp);
                    $stmt->bindParam(':DATE_HISTORIQUE', $date_historique);
                    $stmt->bindParam(':HEURE_HISTORIQUE', $heure_historique);                    
                    $stmt->bindParam(':ACTION', $action);
                    $stmt->bindParam(':TYPE', $type);
                    $stmt->bindParam(':DESIGNATION', $designation);
                    $stmt->bindParam(':AJOUTER_PAR', $ajouter_par);
                    $stmt->bindParam(':AVANT', $avant);
                    $stmt->bindParam(':SUPPRIMER_PAR', $supprimer_par);
                    // Update the record
                    $ex_historique = $stmt->execute();
                  }            


              // GESTION CAISSE RECALCUL

              if ($ex_del_gestion_caisse <> false) {
                
                  # MIN ID
                $query_min = $pdo->prepare("SELECT MIN(ID_GESTION_CAISSE) as min_id FROM gestion_caisse WHERE SESSION_ID=".$_SESSION['id']);
                $query_min->execute();
                $row_min_id = $query_min->fetch(PDO::FETCH_BOTH);
                $gstock_min_id=$row_min_id['min_id'];

                # MAX ID
                $query_max = $pdo->prepare("SELECT MAX(ID_GESTION_CAISSE) as max_id FROM gestion_caisse WHERE SESSION_ID=".$_SESSION['id']);
                $query_max->execute();
                $row_max_id = $query_max->fetch(PDO::FETCH_BOTH);
                $gstock_max_id=$row_max_id['max_id'];


                for ($x = $gstock_min_id; $x <= $gstock_max_id; $x++) {
                    
                        $query_one = $pdo->prepare("SELECT * FROM gestion_caisse WHERE ID_GESTION_CAISSE=".$x." AND SESSION_ID=".$_SESSION['id']);
                        $query_one->execute();
                        $row = $query_one->fetch(PDO::FETCH_BOTH);

                        if ($x === $gstock_min_id) {
                                                   
                            $solde_actuel = $row['SOLDE_ACTUEL'];
                            $_SESSION['solde_actuel'] = $solde_actuel;

                        } elseif ($query_one->rowCount()> 0 && $x > $gstock_min_id) {
                            $solde = $row['SOLDE'];
                            $etat = $row['ETAT'];
                            if (stripos($etat, '+') !== FALSE) {
                                $solde_actuel= ($_SESSION['solde_actuel'])+($solde);
                            }elseif (stripos($etat, '-') !== FALSE) {
                                $solde_actuel= ($_SESSION['solde_actuel'])-($solde);
                            }
                            
                            // UPDATE
                            $query = $pdo->prepare("UPDATE gestion_caisse SET SOLDE_ACTUEL = '$solde_actuel' WHERE ID_GESTION_CAISSE =".$x." AND SESSION_ID=".$_SESSION['id']);
                            $ex_calcul_gestion_caisse =$query->execute();
                            # Mettre en session le solde précédent
                            $_SESSION['solde_actuel'] = $solde_actuel;
                        }

                    } 

                } # FIN GESTION CAISSE RECALCUL

                  // SI SUPPRESSION AVEC SUCESS
                  if ( ($ex_del_vente <> false) && ($ex_del_facture <> false) && ($ex_del_caisse_vente <> false) ) {


                   //// RESTAURATION DE STOCK APRES SUPRESSION VENTE ////

                    // Select VENTE 
                    $sql="SELECT * FROM gestion_stock WHERE ID_GESTION =".$id_gestion;
                    $query = $pdo->prepare($sql);
                    $query->execute();
                    $row_gestion_stock = $query->fetch(PDO::FETCH_BOTH);  

                    // Définition variable
                    $quantite_en_vente = $vente['QUANTITE'];
                    $quantite_en_stock = $row_gestion_stock['QUANTITE'];
                    $prix_unitaire_produit = $row_gestion_stock['PRIX_UNITAIRE'];

                    // Calculation
                    $nouveau_qte = ($quantite_en_stock + $quantite_en_vente) ;
                    $nouveau_pt = ($prix_unitaire_produit * $nouveau_qte); 

                    // Restauration
                    $sql="UPDATE gestion_stock SET QUANTITE=".$nouveau_qte.", PRIX_TOTALE=".$nouveau_pt." WHERE ID_GESTION =".$id_gestion;
                    $query = $pdo->prepare($sql);
                    $query->execute();


                    # Définition session sweet alert 
                    $_SESSION['alert_icon'] = "success";
                    $_SESSION['alert_title'] = "Suppression terminée";
                    $_SESSION['alert_text'] = "Le vente à été supprimé avec succès!";
                    unset($_SESSION['query_vente']);

                    header("Location: read_vente.php");
                    exit;
                    
                  } else {
                    $_SESSION['alert_icon'] = "error";
                    $_SESSION['alert_title'] = "Suppression échouée";
                    $_SESSION['alert_text'] = "Client non supprimé!";
                    unset($_SESSION['query_vente']);

                    header("Location: read_vente.php");
                    exit;
                  } 

        //SI MOT DE PASSE INCORRECT
        } else {
            $pswd_err = "Mot de passe incorret!";
            $vente_id_mdp_err =  $input_vente_id;
        }

    }


}

/* FIN SUPPRESSSION APRES VALIDATION PAR MOT DE PASS */

   


?>

<?=template_header('Vente')?>
<?=template_content('vente')?>
     
        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-user-circle"></i> GESTION VENTE</h3>
                </div>
                <!-- RECHERCHE --> 
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
                <h5><i class='fas fa-home'></i> / Vente</h5>
                <div class="navigation-retour-active">
                  <a href="index.php" ><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-avancer-active">
                  <a href="add_client.php" href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
            <div  class="container">
                <h2><i class="fas fa-play"></i> Historique vente</h2>
                <a class="add-new" href="add_vente.php" class="create-contact"><i class="fas fa-cart-plus"></i> Ajouter nouveau vente</a>

                <!-- TABLEAU CLIENT --> 
             <table id="table" class="table">
                <thead class="thead-facture">
                    <tr>
                        <td>#</td>
                        <td><?= tri('nom_produit', 'Designation', $_GET) ?></td>
                        <td><?= tri('prix_unitaire', 'Prix Unitaire', $_GET) ?></td>
                        <td><?= tri('quantite', 'Quantité', $_GET) ?></td>
                        <td><?= tri('prix_totale', 'Prix Totale', $_GET) ?></td>
                        <td><?= tri('benefice', 'Bénéfice', $_GET) ?></td>
                        <td><?= tri('date_vente', 'Date Vente', $_GET) ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody class="tbody">
                    <?php foreach ($ventes as $vente): ?>
                    <tr>
                        <td align='right'><?= ++$ligne?></td>
                        <td class="non_vente"><?=$vente['NOM_PRODUIT']." ".$vente['REFERENCE']." ".$vente['QUANTITE_UNITE']." ".$vente['UNITE']?></td>
                        <td><?=$vente['PRIX_UNITAIRE']?></td>
                        <td><?=$vente['QUANTITE']?></td>
                        <td><?=$vente['PRIX_UNITAIRE']*$vente['QUANTITE']?></td>
                        <td><?=$vente['BENEFICE']?></td>
                        <td><?=$vente['DATE_VENTE']?></td>
                        <td>
                          <form method="post">
                            <input type="" name="vente_id" value="<?=$vente['ID_VENTE']?>" hidden="true">
                            <button class="detail-btn" type="submit" name="detail_vente">Détails</button>
                          </form>
                        </td>
                        <td class="actions">
                          <form method="post">
                            <input type="" name="vente_id" value="<?=$vente['ID_VENTE']?>" hidden="true">
                            <button class="btn-table" type="submit" class="btn btn-primary" name="update_vente"><i class="fas fa-edit"></i></button>
                            <button class="btn-table" type="submit" class="btn btn-primary" name="suppression_vente"><i class="fas fa-trash-alt"></i></button>
                          </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($num_vente < 1 || $page > $nbre_page): ?>
              <?=$vide?>
            <?php endif; ?> 
            <br>
            <!-- PAGINATION -->
                <?= template_pagination('vente') ?>
            <!-- FIN PAGINATION -->
             
        </div>


<!-- ///////////////////////////////// EXECUTION MODAL ///////////////////////////////// -->



        <!-- ///////////////////////// MODAL DETAILS //////////////////////////// -->
  <?php if (isset($ID_DETAIL_VENTE)  ) :?>
          <div class="modal fade" id="modal-detail" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
            
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title"><img src="ilo/icons-100.png"> Details de la vente</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                  <div>
                    <form class="row" id="" action="" method="get" style="text-align: left;">
                            <div class="bloc-form-1 col-md-6">
                                <div class="form-group">
                                    <label for="designation">DESIGNATION : </label>
                                    <p><?=$row_detail_vente['NOM_PRODUIT']." ".$row_detail_vente['REFERENCE']." ".$row_detail_vente['QUANTITE_UNITE']." ".$row_detail_vente['UNITE']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="prix_unitaire">PRIX UNITAIRE : </label>
                                    <p><?=$row_detail_vente['PRIX_UNITAIRE']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="quantite">QUANTITE : </label>
                                    <p><?=$row_detail_vente['QUANTITE']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="prix_totale">PRIX TOTAL : </label>
                                    <p><?=$row_detail_vente['PRIX_UNITAIRE']*$row_detail_vente['QUANTITE']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="benefice">BENEFICE : </label>
                                    <p><?=$row_detail_vente['BENEFICE']?></p> 
                                </div>
                            </div>
                            <div class="bloc-form-2 col-md-6">
                                <div class="form-group">
                                    <label for="date_vente">DATE VENTE : </label>
                                    <p><?=date("d-m-Y", strtotime($row_detail_vente['DATE_VENTE']))?></p>
                                </div>
                                <div class="form-group">
                                    <label for="heure_vente">HEURE DE LA VENTE : </label>
                                    <p><?=$row_detail_vente['HEURE_VENTE']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="client">CLIENT : </label>
                                    <p><?=$row_detail_vente['CLIENT']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="statut">STATUT : </label>
                                    <p><?=$row_detail_vente['STATUT']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="ajoute_par">AJOUTE PAR : </label>
                                    <p><?=$row_detail_vente['AJOUTE_PAR']?></p> 
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
  
        <!-- ///////////////////////// FIN MODAL DETAIL //////////////////////////// -->


        <!-- ///////////////////////// 1) MODAL POUR MODERATEUR //////////////////////////// -->

    <!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION VENTE -->

<!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION VENTE  -->

<?php if (isset($_POST['suppression_vente']) && ($_SESSION['user'] <> "admin") ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">SUPPRESSION</h4>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($designation)?></h2>
              <p>Pour supprimer cette vente veuillez contacter votre administrateur !</p>
                <button type="button" class="btn btn-danger" data-dismiss="modal"> FERMER </button>
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- FIN MODAL POUR MODERATEUR -->


      <!--///////////////////////// 2) MODAL POUR ADMINISTRATEUR ///////////////////////// -->

    <!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION VENTE -->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION CLIENT : Si le Client n'a pas encore effectué une vente -->
<!-- SUPPRESSION : MODAL ADMIN VENTE NO -->

<?php if (isset($_POST['suppression_vente']) && ($_SESSION['user'] <> "modo") ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/supprimer.png">SUPPRESSION</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($designation)?></h2>
              <p>Vous êtes sur de supprimer cette vente ?</p>

                <form method="post">
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="vente_id" value="<?=$ID_VENTE?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_supp_vente_yes"><i class="fas fa-check"></i> OUI </button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal"><i class="fas fa-times"></i> NON </button>
                  </div>                    
                </form>                     

            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>


<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION CLIENT : Si le Client a déjà effectué une vente -->
<!-- VALIDATION SUPPRESSION : MODAL ADMIN VENTE YES -->

<?php if ((isset($_POST["submit_supp_vente_yes"])) )  :?>

  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">VALIDATION SUPPRESSION </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($designation)?></h2>
              <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="" name="vente_id" value="<?=$ID_VENTE?>" hidden="true">
                  </div>
                  <button  id="btn-validate-del-client" name="btn-validate-del-vente" type="submit" class="btn btn-primary"> Supprimer </button>
              </form> 
            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION PAR MOT DE PASSE SUPPRESSION CLIENT AVEC VENTE -->

 <!-- VALIDATION PAR MOT DE PASSE SUPPRESSION : VALIDATION PAR MOT DE SI MOT DE PASSE VIDE OU INCORRECT -->

<?php if ((!empty($pswd_err)) && ($_SESSION['user'] <> "modo")) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">VALIDATION SUPPRESSION </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($designation)?></h2>
              
                 <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="" name="vente_id" value="<?=$vente_id_mdp_err?>" hidden="true">
                  </div>
                  <div class="modal-error">
                    <?php if(!empty($pswd_err)) : ?>
                      <p class="error"><?= $icon_type ." ". $pswd_err?></p>
                    <?php endif; ?>   
                  </div>
                    <button  id="btn-validate-del-client" name="btn-validate-del-vente" type="submit" class="btn btn-primary"> Supprimer </button>              
                </form>               
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<!-- FIN SUPPRESSION CLIENT POUR ADMIN -->

      <!-- ////////////////////////// MODAL MODIFICATION POUR MODERATEUR /////////////////////-->

<!-- MODAL A EXECTUER POUR MODERATEUR / AVEC VENTE DEJA PAYE-->

<?php if (isset($_POST['update_vente']) && ($_SESSION['user'] <> "admin") && $statut == "paye" ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">MODIFICATION VENTE</h4>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($designation)?></h2>
              <p>Vous ne pouvez pas modifier une vente qui est déjà PAYÉE. Pour modifier cette vente, veuillez contacter votre administrateur !</p>
                <button type="button" class="btn btn-danger" data-dismiss="modal"> FERMER </button>
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- FIN VALIDATION MOT DE PASS MODIFICATION -->

<!-- FIN EXECUTION MODAL -->


<!-- /////////// EFFACEMENT SESSION ////////// -->
<?php 
  // Define variables and initialize with empty values
  $pswd = "";
  $pswd_err = "";
 ?>                
 
 <!-- // SWEET ALERT NOTIFICATION // -->
<?=sweet_alert_notification()?>

<?=template_footer()?>
