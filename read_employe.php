<?php
session_start();
include 'functions.php';
$pdo = pdo_connect_mysql();
$msg = '';

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();


// AFFICHAGE MODERATEUR

// Get the page via GET request (URL param: page), if non exists default the page to 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Number of records to show on each page
$records_per_page = 10;

// Prepare the SQL statement and get records from our MODERATOR table, LIMIT will determine the page
$query = "SELECT * FROM moderateur WHERE ID=".$_SESSION['id'];
$params = $search= "";
$sortable = ["nom_complet"];
$pag = "";
$ligne = 0;
$numero_page = "";
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (moderateur.NOM_COMPLET LIKE $params)";
}

//organisation
$pag = " ORDER BY moderateur.ID_MODO DESC";
if(!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)){
    $direction = $_GET['dir'] ?? 'asc';
    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $pag = " ORDER BY " . $_GET['sort'] . " $direction";
}

$pag .= " LIMIT :current_page, :record_per_page";
$query = $query.$search.$pag;
// Prepare the SQL statement and get records from our MODERATOR table, LIMIT will determine the page
$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$modos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of VENTE
$num_modo = $pdo->query("SELECT COUNT(*) FROM moderateur WHERE ID='".$_SESSION['id']."'".$search)->fetchColumn();
$numero_page = "";
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_modo / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Tableau vide</h3>";

// Check if POST data is not empty
   // Define variables and initialize with empty values
    $nom = $tel = $pseudo = $pswd = $pswdcof = $photo =  "";
    $nom_err =  $tel_err = $pseudo_err = $pswd_err = $pswdcof_err = $photo_err = $pswdmodo_err = "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";
    $upload_dir = 'uploads/moderateur/';

    $icon_type = "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";    

// INSCRIPTION MODERATEUR
try {

    // Processing form data when form is submitted
    if(isset($_POST["submit"])) {
        function data_input($data) {
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }        
        
        // Validate Nom
        $input_nom = data_input($_POST["nom"]);

        if(empty($input_nom)){
           $nom_err = "Veuillez insérer votre nom";
        } elseif (!preg_match("/^[a-zA-Z-' ]*$/",$input_nom)) {
           $nom_err = "Seulement les lettres et les espaces sont acceptés"; 
        } else{
           $nom = $input_nom;
           $nom = strtolower($nom);
        }
           
        // Validate Pseudo
        $input_pseudo = data_input($_POST["pseudo"]);

        if(empty($input_pseudo)){
            $pseudo_err = "Veuillez insérer votre Pseudo";
        }elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_pseudo)) {
           $pseudo_err = "Seulement les lettres et les chiffres sont acceptés";
        } else{
            $pseudo = $input_pseudo;
        } 
        // check if pseudo is already used
        $query_inscription = $pdo->prepare("SELECT * FROM inscription WHERE PSEUDO='".$pseudo."'");
        $query_modo = $pdo->prepare("SELECT * FROM moderateur WHERE PSEUDO='".$pseudo."'");

        $query_inscription->execute();
        $query_inscription->fetch(PDO::FETCH_BOTH);
        $query_modo->execute();
        $query_modo->fetch(PDO::FETCH_BOTH);

        if ($query_modo->rowCount()>0 || $query_inscription->rowCount()>0 ) {
            # code...
            $pseudo_err = "Ce pseudo est déjà utilisé";
        }

        // Validate PSWD
        $input_pswd = data_input($_POST["pswd"]);
        if(empty($input_pswd)){
            $pswd_err = "Définir votre mot de passe";
        }
        // Validate CONFIRMATION PSWD
        $input_pswdcof = data_input($_POST["pswdcof"]);
        if(empty($input_pswdcof)){
            $pswdcof_err = "Confirmer votre mot de passe";
        } elseif ($input_pswd === $input_pswdcof) {
            $pswd = $input_pswdcof;
        } else {
            $pswdcof_err = "Mot de passe non-identique";
        }

        // Validate tel
        $input_tel = data_input($_POST["tel"]);

        if(empty($input_tel)){
            $tel_err = "Inserer votre numero téléphone";
        } elseif (!preg_match("/^[0+1+2+3+4+5+6+7+8+9+ ]*$/",$input_tel)) {
           $tel_err = "Seulement les chiffres sont acceptés";  // 1= erreur

        }else{
            $tel = $input_tel;
        }       
        
//////////// PHOTO //////////////

        // Validate Photo 

         if (empty($pseudo_err)) {

                $imgName = $_FILES['image']['name'];
                $imgTmp = $_FILES['image']['tmp_name'];
                $imgSize = $_FILES['image']['size'];

                if($imgName) {

                  $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));

                  $allowExt  = array('jpeg', 'jpg', 'png', 'gif');

                  $photo = time().'_'.rand(1000,9999).'_'.$pseudo.'.'.$imgExt;


                  if(in_array($imgExt, $allowExt)){

                    if($imgSize < 5000000){
                        move_uploaded_file($imgTmp ,$upload_dir.$photo); 
                    } else {
                     $photo_err = 'La taille de votre photo est trop grande. Taille maximum 5mo';
                    }
                  } else {
                    $photo_err = 'Insérer une photo valide !';    
                  }
                } else {
                    $photo = "user.png";
            }

        } 

////////////////////////////////////        



        // Check input errors before inserting in database
        if(empty($nom_err) && empty($pseudo_err) && empty($pswd_err) && empty($pswdcof_err) && empty($tel_err) && empty($photo_err) ){
                $date_inscription = date("Y-m-d");
                $id= $_SESSION['id'];
                // prepare sql and bind parameters
                $sql="INSERT INTO moderateur (ID, NOM_COMPLET, TEL, PSEUDO, PSWD, DATE_INSCRIPTION, PHOTO)
                VALUES (:ID, :NOM_COMPLET, :TEL, :PSEUDO, :PSWD, :DATE_INSCRIPTION, :PHOTO)";
                $stmt = $pdo->prepare($sql);

                $stmt->bindParam(':ID', $id);
                $stmt->bindParam(':NOM_COMPLET', $nom);
                $stmt->bindParam(':TEL', $tel);
                $stmt->bindParam(':PSEUDO', $pseudo);
                $stmt->bindParam(':PSWD', $pswd);
                $stmt->bindParam(':DATE_INSCRIPTION', $date_inscription); 
                $stmt->bindParam(':PHOTO', $photo);                
                // insert a row
                $ex= $stmt->execute();

                // Attempt to execute the prepared statement
                if($ex <> false){

                      /*$_SESSION['alert_icon'] = "success";
                      $_SESSION['alert_title'] = "Inscription employé terminé";
                      $_SESSION['alert_text'] = "L'inscription de votre employé à été fait avec succès!";   */                  
                    // Records created successfully. Redirect to landing page
                    header("location: read_employe.php");
                    exit();

                } else{

                      $_SESSION['alert_icon'] = "warning";
                      $_SESSION['alert_title'] = "L'inscription a échoué";
                      $_SESSION['alert_text'] = "Une erreur s'est produite, veuillez réessayer plus tard";   

                }
            }

             /*$_SESSION['nom_err'] = $nom_err;
             $_SESSION['pseudo_err'] = $pseudo_err;
             $_SESSION['tel_err'] = $tel_err;
             $_SESSION['pswd_err'] = $pswd_err;
             $_SESSION['pswdcof_err'] = $pswdcof_err;

            header("location: read_employe.php#inscription");                     
            exit(); */    
        }
    
    if ( isset($_POST["suppression_moderateur"]) || isset($_POST["submit_supp_modo_oui"]) || isset($_POST["btn-validate-del-modo"]) ) {

        function data_input($data) {
            
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Prendre l'id stock

        $modo_id = data_input($_POST["modo_id"]);

         // code...

        $sql = "SELECT * FROM moderateur WHERE ID_MODO =".$modo_id." AND ID =".$_SESSION['id'];   
        $query= $pdo->prepare($sql);
        $query->execute();
        $moderateur = $query->fetch(PDO::FETCH_BOTH);

        if (!$moderateur) {
            // code...
            exit("Ce employé n'existe pas");
        }

        ////////////////////////////////////

        if (isset($_POST["btn-validate-del-modo"])) {
            // Validate PSWD

            $input_pswdmodo = data_input($_POST["pswd"]);

            # Si mot de passe vide
            if(empty($input_pswdmodo)) {
                $pswdmodo_err = "Veuillez insérer votre mot de passe.";
            } else{
                $pswd = $input_pswdmodo;
            }
            // Definiton variable Pseudo
            $pseudo=$_SESSION['username'];
            
            if ($_SESSION['user'] <> "modo" && empty($pswdmodo_err)) {

                // select inscription
                $sql_inscription="SELECT * FROM inscription WHERE 
                PSEUDO=? AND PSWD=? ";
                $query_inscription = $pdo->prepare($sql_inscription);
                $query_inscription->execute(array($pseudo,$pswd));
                $row_inscription = $query_inscription->fetch(PDO::FETCH_BOTH);

                if($query_inscription->rowCount() > 0) { 

                    // 1. SUPPRESSION MODERATEUR

                    $sql = "DELETE FROM moderateur WHERE ID_MODO = '".$modo_id."'";
                    $stmt = $pdo->prepare($sql);
                    $ex_del_modo = $stmt->execute();

                    if ($ex_del_modo <> false) {
                        // code...
                        if ($moderateur['PHOTO'] <> "user.png") {
                            // code...
                            unlink($upload_dir.$moderateur['PHOTO']);
                        }
                                             
                        // Records created successfully. Redirect to landing page
                        header("location: read_employe.php");
                        exit();                        
                    }
             
                # Si mot de passe incorrect
                }else {

                   $pswdmodo_err = "Votre mot de passe est incorrect.";

                }
            }           
        }
                

     } 



}
catch(PDOException $e)
{
echo $sql . "<br>" . $e->getMessage();
}    
?>


<?=template_header('Employés')?>
<?=template_content('employes')?>

        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-user-circle"></i> EMPLOYÉS</h3>
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

    <div id="contenu-employe" class="container">
        <div class="container">
           <?php if($_SESSION['user']<>"admin"): ?>
                <div class="container pt-5 mt-5 pb-3">
                    <div class="d-flex justify-content-center pt-4">
                      <img src="ilo\browser-attention.png">
                    </div>
                    <div class="d-flex justify-content-center align-self-center mt-5 mb-5 pb-5">
                      <h2>Désolé, vous n'êtes pas autorisé à accéder à cette page</h2>  
                    </div>
                </div>
            <?php else: ?>   
                <div>
                    <h2><i class="fas fa-play"></i> VOS MODERATEUR</h2>
                <?php if($num_modo <= 0): ?>
                <div class=""><br>
                  <h4>Aucun modérateur inscrit pour le moment</h4>
                </div>
                <?php else: ?> 

                <table id="table" class="table">
                    <thead class="thead">
                        <tr class="tr-thead">
                            <td class="td-nom"><?= tri('nom_complet', 'Nom complet', $_GET) ?></td>
                            <td class="td-tel">Téléphone</td> 
                            <td class="td-actions">Modifier</td>          
                        </tr>
                    </thead>
                    <tbody class="tbody">
                        <?php foreach ($modos as $modo): ?>
                        <tr class="tr-tbody">
                            <td class="td-nom-contenu"><i class="far fa-circle"></i><i class="far fa-dot-circle"></i><img class="userPic" src="<?=$upload_dir.$modo['PHOTO']?>"><?=ucwords($modo['NOM_COMPLET'])?></td>
                            <td class="td-tel-contenu"><?=$modo['TEL']?></td> 
                            <td class="td-actions-contenu">
                                <form method="post">  

                                    <input type="text" name="modo_id" value="<?=$modo['ID_MODO']?>" hidden="true">
                                    <button data-toggle="tooltip" title="Supprimer" class="btn-table" type="submit" class="btn btn-primary" name="suppression_moderateur"><i class="fas fa-trash-alt"></i></button>

                                </form>
                            </td>                                     
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
            <?php endif; ?>                     
                </table>
              </div>
                    <!-- PAGINATION -->
                        
                            <div class="pied">
                              <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="prev"><a href="read_employe.php?page=<?=$page-1?>"><i class="fas fa-angle-double-left fa-sm"></i> Précédent</a></li>
                                <?php elseif($num_modo < 1) : ?>

                                <?php elseif($page == 1) : ?>
                                    <li class="prev disable"><a href="read_employe.php?page=<?=$page-1?>"><i class="fas fa-angle-double-left fa-sm"></i> Précédent</a></li>
                                <?php endif; ?>
                                <?php for($i = 1; $i <= $nbre_page; $i++) : ?>
                                    <?php if($i == $page) : ?>
                                        <li class="num_page active"><a href="read_employe.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                                    <?php else : ?>
                                        <li class="num_page"><a href="read_employe.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <?php if ($page*$records_per_page < $num_modo): ?>
                                    <li class="next"><a href="read_employe.php?page=<?=$page+1?>">Suivant <i class="fas fa-angle-double-right fa-sm"></i></a></li>
                                <?php elseif($num_modo < 1) : ?>

                                <?php elseif($page*$records_per_page >= $num_modo) : ?>
                                    <li class="next disable"><a href="read_employe.php?page=<?=$page+1?>">Suivant <i class="fas fa-angle-double-right fa-sm"></i></a></li>
                                <?php endif; ?>
                            </ul>
                           </div>
                <!-- FIN PAGINATION -->              
            </div>
    </div>
    <div id="contenu-inscription-employe" class="container">
        <span id="inscription"></span>
        <h2><i class="fas fa-play"></i> INSCRIPTION MODERATEUR</h2>
        <form action="read_employe.php" enctype="multipart/form-data" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                      <input type="text" class="form-control" id="name" placeholder="NOM COMPLET" name="nom">
                      <?php if (!empty($nom_err)) : ?>
                      <p><?=$icon_type." ".$nom_err;?></p>
                      <?php endif; ?>
                    </div>
                    <div class="form-group">
                      <input type="text" class="form-control" id="pseudo" placeholder="PSEUDO" name="pseudo">
                      <?php if (!empty($pseudo_err)) : ?>
                      <p><?=$icon_type." ".$pseudo_err;?></p>
                      <?php endif; ?>
                    </div>
                    <div class="form-group">
                      <input type="text" class="form-control" id="tel" placeholder="TELEPHONE" name="tel">
                      <?php if (!empty($tel_err)) : ?>
                      <p><?=$icon_type." ".$tel_err;?></p>
                      <?php endif; ?>
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control" id="pswd" placeholder="MOT DE PASSE" name="pswd">
                      <?php if (!empty($pswd_err)) : ?>
                      <p><?=$icon_type." ".$pswd_err;?></p>
                      <?php endif; ?>
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control" id="pswdcof" placeholder="CONFIRMATION MOT DE PASSE" name="pswdcof">
                      <?php if (!empty($pswdcof_err)) : ?>
                      <p><?=$icon_type." ".$pswdcof_err;?></p>
                      <?php endif; ?>
                    </div>
                    <div class="form-group">
                      <input type="file" class="form-control" id="image" placeholder="VOTRE PHOTOS" name="image">
                      <?php if (!empty($photo_err)) : ?>
                      <p><?=$icon_type." ".$photo_err;?></p>
                      <?php endif; ?>                      
                    </div>                             
                </div>
                <div class="col-md-6"> 
                    <div class="form-group">                   
                        <h4><i class="fas fa-play"></i> loreim ipsum bla bli bal bal ok bola bla loreim ipsum bla bli bal bal ok bola bal</h4>
                    </div>
                    <div class="form-group">
                        <br>                  
                    </div>
                    <div class="form-group"> 
                        <h4><i class="fas fa-play"></i> loreim ipsum bla bli bal bal ok bola bla loreim ipsum bla bli bal bal ok bola bla</h4>
                        <br>                                      
                    </div>                    
                    <div class="form-group">                   
                        <input id="create-modo-btn" type="submit" name="submit" value="INSCRIPTION">
                    </div>
                        <?php if ($msg): ?>
                        <p><?=$msg?></p>
                        <?php endif; ?>
                <?php endif; ?>
                <?php
                    // Close statement
                    $conn = null;
                ?>      
                </div>
                    
            </div>
        </form>              
    </div>


        <!-- ///////////////////////// FIN MODAL DETAIL //////////////////////////// -->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION SUPPRESSION FOURNISSEUR AVEC STOCK PAR MOT DE PASSE -->

<?php if ( isset($_POST["suppression_moderateur"]) ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/supprimer.png">SUPPRESSION EMPLOYÉ</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($moderateur['NOM_COMPLET'])?></h2>
              <p>Vous êtes sur de supprimer ce fournisseur ?</p>

                <form method="post">
                  <div class="valide-annule mt-2 mb-3">
                    <input type="text" name="modo_id" value="<?=$moderateur['ID_MODO']?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_supp_modo_oui"><i class="fas fa-check"></i> OUI </button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal"><i class="fas fa-times"></i> NON </button>
                  </div>                    
                </form>                     

            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>


<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION SUPPRESSION FOURNISSEUR AVEC STOCK PAR MOT DE PASSE -->

<?php if (isset($_POST["submit_supp_modo_oui"]) && (empty($pswdmodo_err))) :?>
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
              <h2><?=ucwords($moderateur['NOM_COMPLET'])?></h2>
              <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="text" name="modo_id" value="<?=$moderateur['ID_MODO']?>" hidden="true">
                  </div>
                  <button  id="btn-validate-del-fournisseur" name="btn-validate-del-modo" type="submit" class="btn btn-primary"> Supprimer </button>
              </form> 
            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>

 <!-- VALIDATION PAR MOT DE PASSE MODIFICATION : VALIDATION PAR MOT DE SI MOT DE PASSE VIDE OU INCORRECT -->

<?php if (isset($_POST["btn-validate-del-modo"]) && (!empty($pswdmodo_err)) ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">VALIDATION MODIFICATION</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($moderateur['NOM_COMPLET'])?></h2>
                
                   <form method="post">
                    <div class="cadre-form">
                      <label for="pswd">Insérer votre mot de passe :</label>
                      <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                      <input type="text" name="modo_id" value="<?=$moderateur['ID_MODO']?>" hidden="true">
                    </div>
                    <div class="modal-error">
                        <?php if(!empty($pswdmodo_err)) : ?>
                          <p class="error"><?= $icon_type ." ". $pswdmodo_err?></p>
                        <?php endif; ?>                      
                    </div>
                        <button  id="btn-validate-update-fournisseur" name="btn-validate-del-modo" type="submit" class="btn btn-primary"> Supprimer </button>
                  </form> 
                
            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>


 <!-- // SWEET ALERT NOTIFICATION // -->
<?=sweet_alert_notification()?>            
<?=template_footer()?>
