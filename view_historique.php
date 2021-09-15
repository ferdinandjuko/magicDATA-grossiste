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
$records_per_page = 8;

if(isset($_SESSION['id_histo'])) {
    $ID_HISTORIQUE =$_SESSION['id_histo'];
  // Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
 $query = "SELECT * FROM historique WHERE ID_HISTORIQUE =".$ID_HISTORIQUE." AND ID=".$_SESSION['id'];

} else {
    # REDIRIGER VERS SITORIQUE SI ID NON DEFINIE
    header("location: read_historique.php");
    exit();
}


$params = $search= "";
$sortable = ["date_historique"];
$pag = "";
$ligne = 0;
$numero_page = "";
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (DATE_HISTORIQUE LIKE $params OR ACTION LIKE $params OR TYPE LIKE $params OR AJOUTER_PAR LIKE $params OR AVANT LIKE $params OR APRES LIKE $params OR MODIFIER_PAR LIKE $params OR SUPPRIMER_PAR LIKE $params)";
}

//organisation
$pag = " ORDER BY historique.ID_HISTORIQUE DESC";
if(!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)){
    $direction = $_GET['dir'];
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $pag = " ORDER BY " . $_GET['sort'] . " $direction";
}

$pag .= " LIMIT :current_page, :record_per_page";
$query = $query.$search.$pag;
$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$historique = $stmt->fetch(PDO::FETCH_BOTH);

$num_historique = $pdo->query("SELECT COUNT(*) FROM historique WHERE ID_HISTORIQUE =".$ID_HISTORIQUE." AND ID=".$_SESSION['id'].$search)->fetchColumn();
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_historique / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Aucun historique à afficher</h3>";
?>

<?=template_header('Historique')?>
<?=template_content('historique')?>
  
        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-user-circle"></i> VOS RAPPORTS</h3>
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
                <h5><i class='fas fa-home'></i> / Rapports</h5>
                <div class="navigation-retour-active">
                  <a href="index.php" ><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-avancer-active">
                  <a href="add_client.php" href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
          <?php if ($_SESSION['user']<>"modo"): ?>
            <div  class="container">
                <h2><i class="fas fa-history"></i> Détails Historiques</h2>
                <div id="cadre-add-new">
                    <form method="post">
                        <button class="supp_histo"><i class="fas fa-trash-alt"> </i> Supprimer</button>
                    </form>
                </div>
                <!-- TABLEAU CLIENT --> 
                <table id="table_historique" class="table">
                    <thead class="thead-facture">
                        <tr>
                            <td>#</td>
                            <td class=""><?= tri('date_historique', 'Date & Heure', $_GET) ?></td>
                            <td class="align-center">Action</td>                      
                        </tr>
                    </thead>
                    <tbody class="tbody historique">

                        <tr>
                            <td class="list_histo" align='left'><?="<p><i class='fas fa-circle'></i></p>"?></td>
                            <td class="date_histo"><?="<p>Le ".date("d-m-Y", strtotime($historique['DATE_HISTORIQUE']))." à ".$historique['HEURE_HISTORIQUE']."</p>"?></td>
                          <?php if($historique['TYPE']=="suppression") : ?>
                            <td class="text_histo"><?=$historique['SUPPRIMER_PAR']." a ".$historique['ACTION']." : ".$historique['DESIGNATION'].". <br>"."<a href='#'>Voir détails historique</a>"?></td>
                          <?php else : ?>
                            <td class="text_histo"><?=$historique['MODIFIER_PAR']." a ".$historique['ACTION']." : ".$historique['DESIGNATION'].". <br>"."<a href='#'>Voir détails historique</a>"?></td>
                          <?php endif; ?>                     
                        </tr>

                    </tbody>
                    <thead class="thead-facture">
                        <tr>
                            <td>#</td>
                            <td class="align-center">AVANT</td>
                            <?php if($historique['TYPE']=="modification") : ?>
                            <td class="align-center">APRES</td>
                            <td class="align-center">Modifié par</td>
                            <?php else : ?>
                            <td class="align-center">Supprimé par</td>
                            <?php endif; ?>                     
                        </tr>
                    </thead>
                    <tbody class="tbody historique">


                        <tr>
                            <td class="list_histo" align='left'><?="<p><i class='fas fa-circle'></i></p>"?></td>
                          <?php if($historique['TYPE']=="suppression") : ?>
                            <td class="text_histo"><?=$historique['AVANT']?></td>
                            <td class="text_histo"><?=$historique['SUPPRIMER_PAR']?></td>
                          <?php else : ?>
                            <td class="text_histo"><?=$historique['AVANT']?></td>
                            <td class="text_histo"><?=$historique['APRES']?></td>
                            <td class="text_histo"><?=$historique['MODIFIER_PAR']?></td>
                          <?php endif; ?>                     
                        </tr>

                    </tbody>                    
                </table>        
            </div><br>

            <?php elseif ($_SESSION['user']<>"admin"): ?>
            <div class="container mt-5 pt-4">
                <div class="d-flex justify-content-center mt-3">
                  <img src="ilo\browser-attention.png">
                </div>
                <div class="d-flex justify-content-center mt-5">
                  <h2>Désolé, vous n'êtes pas autorisé à accéder à cette page</h2>  
                </div>
            </div>
            <?php endif; ?>   

        </div>
 

<!-- /////////// EFFACEMENT SESSION ////////// -->
<?php 
  unset($_SESSION['id_histo']);
  unset($_SESSION['id_client']);
  unset($_SESSION['nom_client']);
  unset($_SESSION['vendu']);
  unset($_SESSION['validate']);
  unset($_SESSION['id_client_update']);
  unset($_SESSION['id_update_validate']);
  unset($_SESSION['validate_update']);
  unset($_SESSION['validate_code']);
 ?>                
 
<!-- // SWEET ALERT NOTIFICATION // -->
<?=sweet_alert_notification()?>

<?=template_footer()?>
