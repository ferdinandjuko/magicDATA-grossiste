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

// Prepare the SQL statement and get records from our FACTURE table, LIMIT will determine the page
$query = "SELECT facture.ID_FACTURE, facture.ID_CLIENT, facture.DATE_ECHEANCE, facture.HEURE_ECHEANCE, facture.CLIENT, facture.DESIGNATION, facture.QUANTITE, facture.PRIX_UNITAIRE, facture.STATUT FROM client, facture, vente WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (facture.ID_FACTURE=vente.ID_FACTURE) AND (client.ID=".$_SESSION['id'].")";
$params = $search= "";
$sortable = ["date_echeance", "client", "designation"];
$pag = "";
$ligne = 0;
$numero_page = "";
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (facture.DATE_ECHEANCE LIKE $params OR facture.CLIENT LIKE $params OR facture.DESIGNATION LIKE $params OR facture.HEURE_ECHEANCE LIKE $params OR facture.QUANTITE LIKE $params OR facture.PRIX_UNITAIRE LIKE $params OR facture.STATUT LIKE $params)";
}

//organisation
$pag = " ORDER BY facture.ID_FACTURE DESC";
if(!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)){
    $direction = $_GET['dir'];
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $pag = " ORDER BY " . $_GET['sort'] . " $direction";
}

$pag .= " LIMIT :current_page, :record_per_page";
$query = $query.$search.$pag;

// Prepare the SQL statement and get records from our FACTURE table, LIMIT will determine the page
$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of FACTURE
$num_facture = $pdo->query("SELECT COUNT(*) FROM client, facture, vente WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (facture.ID_FACTURE=vente.ID_FACTURE) AND (client.ID=".$_SESSION['id'].") AND (vente.ID_FACTURE=facture.ID_FACTURE)")->fetchColumn();

$numero_page = "";
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_facture / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Aucune facture ajoutée pour le moment</h3>";

?>

<?=template_header('Facture')?>
<?=template_content('fichier')?>
   
        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-user-circle"></i> FICHIERS</h3>
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
                <h5><i class='fas fa-home'></i> / Factures</h5>
                <div class="navigation-retour-active">
                  <a href="<?=$_SERVER['HTTP_REFERER']?>"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
          <div class="container">
        	<h2><i class="fas fa-play"></i> FACTURE</h2>
        	<table id="table" class="table">
                <thead class="thead-facture">
                    <tr>
                        <td><?= tri('designation', 'Produit', $_GET) ?></td>
                        <td>Prix total</td>                        
                        <td>Date Livraison</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead> 
                <tbody class="tbody">
                    <?php foreach ($factures as $facture): ?>
                    <tr>
                        <td><i class="fas fa-circle"></i> <?=$facture['DESIGNATION']?></td>  
                        <td><?=$facture['PRIX_UNITAIRE']*$facture['QUANTITE']." Ar"?></td>
                        <td><?=$facture['DATE_ECHEANCE']?></td>
                        <td><a class="detail-btn" href="view_facture.php?facture_id=<?=$facture['ID_FACTURE']?>">Détails</a></td>
                        <td><a class="download-pdf" onclick="" href="facture_template2.php?facture_id=<?=$facture['ID_FACTURE']?>"><img src="ilo/pdf.png"> Télécharger</a></td>                  
                
                        <td class="actions">
                            <!-- <a href="update_facture.php?id=<?=$facture['ID_FACTURE']?>" class="edit"><i class="fas fa-edit"></i></a> -->
                            <a href="delete_facture.php?id=<?=$facture['ID_FACTURE']?>" class="trash"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                  <?php if ($num_facture < 1 || $page > $nbre_page): ?>
                      <?=$vide?>
                  <?php endif; ?>            
            </div> 
            <br>
            <!-- PAGINATION -->
                <?= template_pagination('facture') ?>
            <!-- FIN PAGINATION -->           
        	</div>  
        </div>


<?=template_footer()?>
