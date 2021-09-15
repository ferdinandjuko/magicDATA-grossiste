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
$query = "SELECT * FROM fournisseur WHERE ID=".$_SESSION['id'];

$params = $search= "";
$sortable = ["nom", "ville", "pays"];
$pag = $validate = "";
$ligne = 0;
$numero_page = "";
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (NOM LIKE $params OR VILLE LIKE $params)";
}

//organisation
$pag = " ORDER BY fournisseur.ID_FOURNISSEUR DESC";
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
if (isset($_SESSION['query_detail']) && isset($_SESSION['nom'])) {
  # code...
  $query=$_SESSION['query_detail'];
  $_GET['q'] = $_SESSION['nom'];

  unset($_SESSION['query_delail']);
  unset($_SESSION['nom']);
}

$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of fournisseur, this is so we can determine whether there should be a next and previous button
$num_fournisseur = $pdo->query("SELECT COUNT(*) FROM fournisseur WHERE ID=".$_SESSION['id'])->fetchColumn();
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_fournisseur / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Fournisseur vide</h3>";


///////////////////////// BOUTTON SUPPRESSION FOURNISSEUR /////////////////////////////////

if(isset($_POST["suppression_fournisseur"]) || isset($_POST['update_fournisseur']) || (isset($_POST['submit_supp_fournisseur_yes'])) || (isset($_POST['submit_fournisseur_validation_supp_yes'])) || (isset($_POST['submit_fournisseur_validation_update_yes'])) || (isset($_POST['btn_information'])) || (isset($_POST['btn_faire_stock'])) || (isset($_POST["btn_query_stock"])) ) {

        function data_input($data) {
            
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Prendre l'id fournisseur

        $input_fournisseur_id = data_input($_POST["fournisseur_id"]);


        // Detecter si le fournisseur existe
        $sql= "SELECT * FROM fournisseur WHERE ID_FOURNISSEUR = "."'".$input_fournisseur_id."'"." AND ID=".$_SESSION['id'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

        //Verificatiion si le fournisseur n'existe pas == exit
        if (!$fournisseur) {
            exit('Ce fournisseur n\'existe pas!');
        }

        # Verifie si le fournisseur est déjà enregistré dans le stock 
        $query_stock = $pdo->prepare("SELECT * FROM stock WHERE ID_FOURNISSEUR = '".$input_fournisseur_id."'");
        $query_stock->execute();
        $row_stock = $query_stock->fetch(PDO::FETCH_BOTH); 
        $nombre_stock = $query_stock->rowCount(); 

        if ($nombre_stock<>0) {
          $_SESSION['stock'] = "yes";
        } else {
          unset($_SESSION['stock']);
        }

/////////////////// SI L'utilisateur clic sur le boutton Voir les détails stocks //////////////////

        if(isset($_POST["btn_query_stock"])) {
          $_SESSION['query_stock']="SELECT * FROM stock WHERE ID_FOURNISSEUR = '".$input_fournisseur_id."'AND FOURNISSEUR = '".$row_stock['FOURNISSEUR']."'";
                header("Location: read_stock.php");
                exit;          
        }         

/////////////////////// SI L'utilisateur clic sur le boutton SUPPRIMER ///////////////////

        if(isset($_POST["suppression_fournisseur"])) {

        $nom_fournisseur = $fournisseur['NOM'];
        $ID_FOURNISSEUR = $input_fournisseur_id;

        } 

////////////////////// SI L'utilisateur clic sur le boutton MODIFIER /////////////////////
        elseif(isset($_POST['update_fournisseur'])) {

        $nom_fournisseur_update = $fournisseur['NOM'];
        $ID_FOURNISSEUR_UPDATE = $input_fournisseur_id; 

          if (!isset($_SESSION['stock'])) {
                   // code...
              $_SESSION['id_fournisseur_update'] = $ID_FOURNISSEUR_UPDATE;

                header("Location: update_fournisseur.php");
                exit;
            }       

        }

////////////////// SI L'utilisateur clic sur le boutton VALIDER pour SUPPRIMER ////////////////////

        elseif (isset($_POST['submit_fournisseur_validation_supp_yes'])) {
          // activation de session validation
          $validate = "yes"; 
  
          $nom_fournisseur_mdp = $fournisseur['NOM'];
          $ID_FOURNISSEUR_MDP = $input_fournisseur_id;

        } 
////////////////// SI L'utilisateur clic sur le boutton VALIDER POUR MODIFIER ////////////////////

        elseif (isset($_POST['submit_fournisseur_validation_update_yes'])) {
          // activation de session validation
          $validate = "yes"; 
  
          $nom_fournisseur_update_mdp = $fournisseur['NOM'];
          $ID_FOURNISSEUR_UPDATE_YES = $input_fournisseur_id;

        }         
        
//////////////// SI L'utilisateur clic sur le boutton OUI pour SUPPRESSIONV //////////////

        elseif (isset($_POST['submit_supp_fournisseur_yes'])) {
                // Get the contact from the fournisseur table
                $sql= "SELECT * FROM fournisseur WHERE ID_FOURNISSEUR = "."'".$input_fournisseur_id."'"." AND ID=".$_SESSION['id'];
                $stmt = $pdo->prepare($sql);
                //var_dump($sql);
                $stmt->execute();
                $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$fournisseur) {
                    exit('Ce fournisseur n\'existe pas!');
                }
              // User clicked the "Yes" button, delete record
              $stmt = $pdo->prepare("DELETE FROM fournisseur WHERE ID_FOURNISSEUR = "."'".$input_fournisseur_id."'"." AND ID=".$_SESSION['id']);
              $ex_supp_fournisseur =$stmt->execute();

               #INSERTION HISTORIQUE
                  if ($ex_supp_fournisseur <> false) {
                      // Définitions des varialbes pour l'insertion dans l'historique
                      $id_resp = $_SESSION['id'];
                      $date_historique = date("Y-m-d");
                      $heure_historique = date("H:i:s");
                      $action ="supprimé un fournisseur";
                      $type ="suppression";
                      $ajouter_par = $fournisseur['AJOUTE_PAR'];
                      $supprimer_par=$_SESSION['name'];
                      $designation = $fournisseur['NOM']; 


                      # Definition du variable AVANT pour insertion dans le Historique
                      if ($fournisseur['STATUT'] == "personnel") {
                        # Definition variable avant si STATUT PERSONNEL
                        $avant= "Nom : ".$fournisseur['NOM']." <br> "."Tel : ".$fournisseur['TEL']." <br> "."Ville : ".$fournisseur['VILLE']." <br> "."Pays : ".$fournisseur['PAYS']." <br> "."Statut : ".$clients_historique['STATUT']." <br> "."E-mail : ".$fournisseur['COURRIEL']." <br> "."Adresse : ".$fournisseur['ADRESSE'];

                      }elseif ($fournisseur['STATUT'] == "professionnel") {
                        # Definition variable avant si STATUT PROFESSIONNEL
                        $avant= "Nom : ".$fournisseur['NOM']." <br> "."Tel : ".$fournisseur['TEL']." <br> "."E-mail : ".$fournisseur['COURRIEL']." <br> "."Adresse : ".$fournisseur['ADRESSE']." <br> "."Ville : ".$fournisseur['VILLE']." <br> "."Pays : ".$fournisseur['PAYS']." <br> "."Statut : ".$fournisseur['STATUT']." <br> "."RCS : ".$fournisseur['RCS']." <br> "."NIF : ".$fournisseur['NIF']." <br> "."STAT : ".$fournisseur['STAT'] ;
                      }


                      /*$avant = "Nom : ".$fournisseur['NOM']." <br> "."Tel : ".$fournisseur['TEL']." <br> "."E-mail : ".$fournisseur['COURRIEL']." <br> "."Adresse : ".$fournisseur['ADRESSE']." <br> "."Ville : ".$fournisseur['VILLE']." <br> "."Pays : ".$fournisseur['PAYS']." <br> "."Statut : ".$fournisseur['STATUT']." <br> "."RCS : ".$fournisseur['RCS']." <br> "."NIF : ".$fournisseur['NIF']." <br> "."STAT : ".$fournisseur['STAT'] ;*/

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
              
              if ($ex_supp_fournisseur <> false) {
                  $_SESSION['alert_icon'] = "success";
                  $_SESSION['alert_title'] = "Suppression terminée";
                  $_SESSION['alert_text'] = "Le fournisseur à été supprimé avec succès!";
   
                  header("Location: read_fournisseur.php");
                  exit;

              } else {
                  $_SESSION['alert_icon'] = "error";
                  $_SESSION['alert_title'] = "Suppression échouée";
                  $_SESSION['alert_text'] = "Fournisseur non supprimé!";

                  header("Location: read_fournisseur.php");
                  exit;

              }

          }

/////////////// Si l'utilsateur clic sur le BOUTTON DETAIL FOURNISSEUR /////////////////////

      elseif (isset($_POST['btn_information'])) {

        $sql= "SELECT * FROM fournisseur WHERE ID_FOURNISSEUR = "."'".$input_fournisseur_id."'";
        $stmt = $pdo->prepare($sql);
        //var_dump($sql);
        $stmt->execute();
        $fournisseur_info = $stmt->fetch(PDO::FETCH_ASSOC);
  
      }

/////////////// Si l'utilisateur clic sur le BUTTON AJOUTER STOCK ///////////////////

      elseif (isset($_POST['btn_faire_stock'])) {

        // Prendre l'id fournisseur
        $input_fournisseur_id = data_input($_POST["fournisseur_id"]);
        $_SESSION ['fournisseur_faire_stock_id'] = $input_fournisseur_id;
        
        // Prendre le nom fournisseur
        $_SESSION ['fournisseur_faire_stock_nom'] = $fournisseur["NOM"];

        // Rediriger vers l'ajout de stock

          header("Location: add_stock.php");
      }

}  



/* SUPPRESSSION APRES VALIDATION PAR MOT DE PASS */

if (isset($_POST['btn-validate-del-fournisseur'])) {


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

  // Prendre l'id fournisseur

      $input_fournisseur_id = data_input($_POST["fournisseur_id"]);

      // Detecter si le fournisseur existe
      $sql= "SELECT * FROM fournisseur WHERE ID_FOURNISSEUR = "."'".$input_fournisseur_id."'"." AND ID=".$_SESSION['id'];
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

      //Verificatiion si le fournisseur n'existe pas == exit
      if (!$fournisseur) {
          exit('Ce fournisseur n\'existe pas!');
      } 

      // Définition de l'id et le nom du fournisseur à utiliser 
      $ID_FOURNISSEUR_MDP = $input_fournisseur_id;
      $id_fournisseur = $ID_FOURNISSEUR_MDP;
      $nom_fournisseur_mdp = $fournisseur['NOM'];

    // Validate PSWD
    $input_pswd = data_input($_POST["pswd"]);
    if(empty($input_pswd)) {
        $pswd_err = "Veuillez insérer votre mot de passe.";
    } else{
        $pswd = $input_pswd;
    }
    // Define Pseudo
    $pseudo = $_SESSION['username'];


    # Si SESSION utilisateur est pas ADMIN //////////////////////////// 
    if ($_SESSION['user']<>"modo") {

    # Si Mot de passe n'est pas vide //////////////////////////// 
        if (empty($pswd_err)) {
            // select inscription
            $sql_inscription="SELECT * FROM inscription WHERE 
            PSEUDO = ? AND PSWD = ? ";
            $query_inscription = $pdo->prepare($sql_inscription);
            $query_inscription->execute(array($pseudo,$pswd));
            $row_inscription = $query_inscription->fetch(PDO::FETCH_BOTH);


            $sql = "SELECT * FROM stock WHERE ID_FOURNISSEUR = ".$id_fournisseur;
            $query_stock = $pdo->prepare($sql);
            $query_stock->execute();  
            $nombre_stock = $query_stock->rowCount(); 

////////////////// CODE DE SUPPRESSION STOCK //////////////////////////////////    

          if($query_inscription->rowCount() > 0) {

              // Select ID STOCK
              $sql = "SELECT * FROM stock WHERE ID_FOURNISSEUR = ".$id_fournisseur;
              $query_stock = $pdo->prepare($sql);
              $query_stock->execute();
              $row_stock = $query_stock->fetch(PDO::FETCH_BOTH);            

              # Definition nombre vente pour le boucle de suppresion
              $nombre = $query_stock->rowCount();

              # Initialisation variable boucle
              $n = 1;   

              var_dump("NOMBRE STOCK =".$nombre);

              # BOUCLE DE SUPRESSION STOCK
              while ($n <= $nombre) {

                  // Select ID STOCK
                  $sql = "SELECT * FROM stock WHERE ID_FOURNISSEUR =".$id_fournisseur;
                  $query_stock = $pdo->prepare($sql);
                  $query_stock->execute();
                  $row_stock = $query_stock->fetch(PDO::FETCH_BOTH);

                  $designation = ucfirst($row_stock['NOM_PRODUIT'])." ".ucfirst($row_stock['REFERENCE'])." ".$row_stock['QUANTITE_UNITE']." ".strtoupper($row_stock['UNITE']);              

                  # definition des variables
                  $id_stock = $row_stock['ID_STOCK'];
                  $nom_produit = $row_stock['NOM_PRODUIT'];
                  $ref = $row_stock['REFERENCE'];
                  $unite = $row_stock['UNITE'];
                  $quantite_unite = $row_stock['QUANTITE_UNITE'];
                  $prix_unitaire = $row_stock['PRIX_UNITAIRE'];

                  // Select ID GESTION STOCK
              
                  $sql_gestion_stock="SELECT gestion_stock.ID_GESTION, gestion_stock.SESSION_ID, gestion_stock.NOM_PRODUIT, gestion_stock.REFERENCE, gestion_stock.UNITE, gestion_stock.QUANTITE_UNITE, gestion_stock.PRIX_UNITAIRE, gestion_stock.QUANTITE, gestion_stock.PRIX_TOTALE FROM gestion_stock WHERE (gestion_stock.SESSION_ID=".$_SESSION['id'].") AND gestion_stock.NOM_PRODUIT='$nom_produit' AND gestion_stock.REFERENCE='$ref' AND gestion_stock.UNITE='$unite' AND gestion_stock.QUANTITE_UNITE='$quantite_unite' AND gestion_stock.PRIX_UNITAIRE='$prix_unitaire'";

                  $query_gestion_stock= $pdo->prepare($sql_gestion_stock);
                  $query_gestion_stock->execute();
                  $row_gestion_stock = $query_gestion_stock->fetch(PDO::FETCH_BOTH);
                  $id_gestion_stock = $row_gestion_stock['ID_GESTION'];

                  // Select ID STOCK DEFFECTUEUX
                  $sql = "SELECT * FROM deffectueux WHERE ID_GESTION = '".$id_gestion_stock."'";
                  $query_deff = $pdo->prepare($sql);
                  $query_deff->execute();
                  $row_deffectueux = $query_deff->fetch(PDO::FETCH_BOTH);


                  // SELECTION VENTE // SELECTIONNER SI LE STOCK A ETE DEJA VENDU
                  $query_vente= $pdo->prepare("SELECT * FROM vente WHERE ID_GESTION=".$id_gestion_stock);
                  $query_vente->execute();
                  $row_vente = $query_vente->fetch(PDO::FETCH_BOTH);

 /////////////////////////////////////////////////////////////////////////////////////               
                  /*if ($query_vente->rowCount() > 0) {

                      exit('Stock déjà vendu');

                  }*/
 /////////////////////////////////////////////////////////////////////////////////////

                  // Select ID GESTION CAISSE ET CAISSE STOCK

                  $sql="SELECT * FROM caisse_stock WHERE ID_GESTION =".$id_gestion_stock;
                  $query_caisse_stock = $pdo->prepare($sql);
                  $query_caisse_stock->execute();
                  $row_caisse_stock = $query_caisse_stock->fetch(PDO::FETCH_BOTH);
                  $id_gestion_caisse = $row_caisse_stock['ID_GESTION_CAISSE'];
                  $id_caisse_stock = $row_caisse_stock['ID_CAISSE_STOCK'];  
                  $id_gestion = $row_caisse_stock['ID_GESTION'];             

                    # CODE DE SUPPRESSION STOCK :

                    /*
                      1. SUPPRESION CAISSE STOCK
                      2. SUPPRESION GESTION CAISSE   
                      3. SUPPRESION STOCK
                      4. SUPPRESION DEFFECTUEUX
                      5. SUPPRESION GESTION STOCK
                      6. SUPPRESION FOURNISSEUR
                    */

                      // 1. SUPPRESION CAISSE STOCK
                    $sql1 = "DELETE FROM caisse_stock WHERE ID_CAISSE_STOCK =  '".$id_caisse_stock."'";
                    $stmt = $pdo->prepare($sql1);
                    $ex_del_caisse_stock = $stmt->execute();

                    if ($ex_del_caisse_stock <> false) {
                      // 2. SUPPRESION GESTION CAISSE
                        $sql = "DELETE FROM gestion_caisse WHERE ID_GESTION_CAISSE =".$id_gestion_caisse;
                        $stmt = $pdo->prepare($sql);
                        $ex_del_gestion_caisse = $stmt->execute();                        
                    }

                    // 3. SUPPRESION STOCK
                    if ($ex_del_gestion_caisse  <> false) {
                        $sql4 = "DELETE FROM stock WHERE ID_STOCK = '".$id_stock."'";
                        $stmt = $pdo->prepare($sql4);
                        $ex_del_stock = $stmt->execute();
                    }

                    // 4. SUPPRESION DEFFECTUEUX
                    if ($ex_del_stock  <> false) {

                        $sql3 = "DELETE FROM deffectueux WHERE ID_GESTION = '".$id_gestion."'";
                        $stmt = $pdo->prepare($sql3);
                        $ex_del_deffectueux = $stmt->execute();

                        var_dump($sql3);
                        var_dump($ex_del_deffectueux);

                     #INSERTION HISTORIQUE SUPPRESSION STOCK

                        // Définitions des varialbes pour l'insertion dans l'historique
                        $id_resp = $_SESSION['id'];
                        $date_historique = date("Y-m-d");
                        $heure_historique = date("H:i:s");
                        $action = "supprimé un stock";
                        $type = "suppression";
                        $ajouter_par = $row_stock['AJOUTE_PAR'];
                        $supprimer_par = $_SESSION['name'];                 

                        $avant = "Designation : ".$designation." <br> "."Prix_unitaire : ".$row_stock['PRIX_UNITAIRE']." Ar <br> "."Quantité : ".$row_stock['QUANTITE']." <br> "."Prix Total : ".$row_stock['PRIX_TOTALE']." Ar <br> "."Date achat : ".$row_stock['DATE_ACHAT']." <br> "."Fournisseur : ".$row_stock['FOURNISSEUR']." <br> "."Ajouté par : ".$row_stock['AJOUTE_PAR']." <br> "."Descritpion : ".$row_stock['DESCRIPTION'];

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

                    if ($ex_del_deffectueux  <> false) {

                      #INSERTION HISTORIQUE SUPPRESSION STOCK DEFFECTUEUX

                        // Définitions des varialbes pour l'insertion dans l'historique
                        $id_resp = $_SESSION['id'];
                        $date_historique = date("Y-m-d");
                        $heure_historique = date("H:i:s");
                        $action = "supprimé un stock deffectueux";
                        $type = "suppression";
                        $ajouter_par = $row_deffectueux['AJOUTE_PAR'];
                        $supprimer_par = $_SESSION['name'];                 

                        $avant = "Designation : ".$row_deffectueux['DESIGNATION']." <br> "."Quantité : ".$row_deffectueux['QUANTITE']." <br> "."Prix Total : ".$row_deffectueux['PRIX_TOTALE']." Ar <br> "."Date d'ajout : ".$row_deffectueux['DATE_AJOUT']." <br> "."Ajouté par : ".$row_deffectueux['AJOUTE_PAR']." <br> "."Descritpion : ".$row_deffectueux['DESCRIPTION'];

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
                          var_dump($ex_historique);

                    }


                    // DETECTER LE NOMBRE DE LA QUANTITE DE STOCK DANS LA GESTION STOCK
                    $gestion_stock = $pdo->prepare("SELECT * FROM gestion_stock WHERE ID_GESTION = ".$id_gestion);
                    $gestion_stock->execute();
                    $row_gestion_stock = $gestion_stock->fetch(PDO::FETCH_BOTH);
                    $QUANTITE_STOCK_GSTOCK = $row_gestion_stock['QUANTITE'];       

                    // 5. SUPPRESION GESTION STOCK

                    # Si la quantité du stock dans la gestion stock est supérieure ou egal à la quantité du stock à supprimer                    
                    if ($QUANTITE_STOCK_GSTOCK >= $QUANTITE_STOCK ) {

                        # Calcul du nouveau quantité du gestion stock
                        $QUANTITE = ($QUANTITE_STOCK_GSTOCK) - ($QUANTITE_STOCK);

                        #UPDATE GESTION STOCK
                        $stmt = $pdo->prepare("UPDATE gestion_stock SET QUANTITE = ".$QUANTITE." WHERE ID_GESTION =".$id_gestion);
                        $ex_del_gestion = $stmt->execute();                     

                    # Si la quantité du stock à supprimer est supérieure à la quantité de la gestion stock  
                    } elseif ($QUANTITE_STOCK > $QUANTITE_STOCK_GSTOCK) {

                        #SUPPRESSION GESTION STOCK
                        $stmt = $pdo->prepare("DELETE FROM gestion_stock WHERE ID_GESTION = ".$id_gestion);
                        $ex_del_gestion = $stmt->execute(); 
                    }                                                            
                       
                # reinitialisation variable boucle 
                $n++;

              } # FIN BOUCLE DE SUPPRESION STOCK


                // 6. SUPPRESION FOURNISSEUR
                if ($ex_del_stock  <> false) {
                    $sql4 = "DELETE FROM fournisseur WHERE ID_FOURNISSEUR = '".$id_fournisseur."'";
                    $stmt = $pdo->prepare($sql4);
                    $ex_del_fournisseur=$stmt->execute();
                }

                #INSERTION HISTORIQUE SUPPRESSION FOURNISSEUR
                if ($ex_del_fournisseur <> false) {
                      // Définitions des varialbes pour l'insertion dans l'historique
                      $id_resp = $_SESSION['id'];
                      $date_historique = date("Y-m-d");
                      $heure_historique = date("H:i:s");
                      $action ="supprimé un fournisseur";
                      $type ="suppression";
                      $ajouter_par = $fournisseur['AJOUTE_PAR'];
                      $supprimer_par=$_SESSION['name'];
                      $designation = $fournisseur['NOM']; 


                      # Definition du variable AVANT pour insertion dans le Historique
                      if ($fournisseur['STATUT'] == "personnel") {
                        # Definition variable avant si STATUT PERSONNEL
                        $avant= "Nom : ".$fournisseur['NOM']." <br> "."Tel : ".$fournisseur['TEL']." <br> "."Ville : ".$fournisseur['VILLE']." <br> "."Pays : ".$fournisseur['PAYS']." <br> "."Statut : ".$fournisseur['STATUT']." <br> "."E-mail : ".$fournisseur['COURRIEL']." <br> "."Adresse : ".$fournisseur['ADRESSE'];

                      }elseif ($fournisseur['STATUT'] == "professionnel") {
                        # Definition variable avant si STATUT PROFESSIONNEL
                        $avant= "Nom : ".$fournisseur['NOM']." <br> "."Tel : ".$fournisseur['TEL']." <br> "."E-mail : ".$fournisseur['COURRIEL']." <br> "."Adresse : ".$fournisseur['ADRESSE']." <br> "."Ville : ".$fournisseur['VILLE']." <br> "."Pays : ".$fournisseur['PAYS']." <br> "."Statut : ".$fournisseur['STATUT']." <br> "."RCS : ".$fournisseur['RCS']." <br> "."NIF : ".$fournisseur['NIF']." <br> "."STAT : ".$fournisseur['STAT'] ;
                      }


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

                ////// GESTION CAISSE RECALCUL //////

                if ($ex_del_gestion_caisse <> false) {
                  
                    # MIN ID
                  $query_min = $pdo->prepare("SELECT MIN(ID_GESTION_CAISSE) as min_id FROM gestion_caisse WHERE ID=".$_SESSION['id']);
                  $query_min->execute();
                  $row_min_id = $query_min->fetch(PDO::FETCH_BOTH);
                  $gcaisse_min_id=$row_min_id['min_id'];

                  # MAX ID
                  $query_max = $pdo->prepare("SELECT MAX(ID_GESTION_CAISSE) as max_id FROM gestion_caisse WHERE ID=".$_SESSION['id']);
                  $query_max->execute();
                  $row_max_id = $query_max->fetch(PDO::FETCH_BOTH);
                  $gcaisse_max_id=$row_max_id['max_id'];

                  // BOUCLE RECALCUL GESTION CAISSE
                  for ($x = $gcaisse_min_id; $x <= $gcaisse_max_id; $x++) {
                      
                          $query_one = $pdo->prepare("SELECT * FROM gestion_caisse WHERE ID_GESTION_CAISSE=".$x." AND ID=".$_SESSION['id']);
                          $query_one->execute();
                          $row = $query_one->fetch(PDO::FETCH_BOTH);

                          if ($x === $gcaisse_min_id) {
                                                     
                              $solde_actuel = $row['SOLDE_ACTUEL'];
                              $_SESSION['solde_actuel']= $solde_actuel;

                          } elseif ($query_one->rowCount()> 0 && $x > $gcaisse_min_id) {
                              $solde = $row['SOLDE'];
                              $etat = $row['ETAT'];
                              if (stripos($etat, '+') !== FALSE) {
                                  $solde_actuel= ($_SESSION['solde_actuel'])+($solde);
                              }elseif (stripos($etat, '-') !== FALSE) {
                                  $solde_actuel= ($_SESSION['solde_actuel'])-($solde);
                              }
                              
                              // UPDATE
                              $query = $pdo->prepare("UPDATE gestion_caisse SET SOLDE_ACTUEL = '$solde_actuel' WHERE ID_GESTION_CAISSE =".$x." AND ID=".$_SESSION['id']);
                              $ex_calcul_gestion_caisse =$query->execute();
                              # Mettre en session le solde précédent
                              $_SESSION['solde_actuel']= $solde_actuel;
                          }

                      } 

                }               
            exit();
              // SI SUPPRESSION AVEC SUCESS
              if ( ($ex_del_caisse_stock <> false) && ($ex_del_gestion_stock  <> false) && ($ex_del_gestion_caisse <> false) && ($ex_del_stock<> false) && ($ex_del_fournisseur <> false) ) {
                # code...
                $_SESSION['alert_icon'] = "success";
                $_SESSION['alert_title'] = "Suppression terminée";
                $_SESSION['alert_text'] = "L\'information du fournisseur à été supprimé avec succès!";
                unset($_SESSION['query_stock']);
                header('Location: read_fournisseur.php');
                exit; 
              } else {
                $_SESSION['alert_icon'] = "error";
                $_SESSION['alert_title'] = "Suppression échouée";
                $_SESSION['alert_text'] = "Fournisseur non supprimé!";
                unset($_SESSION['query_stock']);
                header('Location: read_fournisseur.php');
                exit;  
              }


          } else  {
              $pswd_err= "Mot de passe incorret!" ;
          }
////////////////// FIN CODE DE SUPPRESSION STOCK //////////////////////////////////

      } # FIN Si Mot de passe n'est pas vide //////////////////////////// 

    } else {
            header('location:read_fournisseur.php');
            exit;

      }# FIN si SESSION utilisateur n'est pas ADMIN /////////////////////////  

  }

/* FIN SUPPRESSSION APRES VALIDATION PAR MOT DE PASS */



/* MODIFICATION APRES VALIDATION PAR MOT DE PASS  */

elseif (isset($_POST['btn-validate-update-fournisseur'])) {


    function data_input($data) {
          $data = trim($data);
          $data = stripslashes($data);
          $data = htmlspecialchars($data);
          return $data;
        } 

  // Prendre l'id fournisseur

      $input_fournisseur_id = data_input($_POST["fournisseur_id"]);

      // Detecter si le fournisseur existe
      $sql= "SELECT * FROM fournisseur WHERE ID_FOURNISSEUR = "."'".$input_fournisseur_id."'"." AND ID=".$_SESSION['id'];
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $fournisseur = $stmt->fetch(PDO::FETCH_ASSOC);

      //Verificatiion si le fournisseur n'existe pas == exit
      if (!$fournisseur) {
          exit('Ce fournisseur n\'existe pas!');
      } 


      // Define variables and initialize with empty values
    $id_fournisseur = $input_fournisseur_id;
    $pswd = "";
    $pswd_update_err = "";
    $icon_type= "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";

    // Validate PSWD
    $input_pswd = data_input($_POST["pswd_update"]);
    if(empty($input_pswd)){
        $pswd_update_err = "Veuillez insérer votre mot de passe.";
        $nom_fournisseur_update_mdp = $fournisseur['NOM'];
        $ID_FOURNISSEUR_UPDATE_YES = $input_fournisseur_id;
    } else{
        $pswd = $input_pswd;
    }
    // Define Pseudo
    $pseudo = $_SESSION['username'];

    if ($_SESSION['user']<>"modo") {

        if (empty($pswd_update_err)) {
            // select inscription
            $sql_inscription = "SELECT * FROM inscription WHERE 
            PSEUDO=? AND PSWD=? ";
            $query_inscription = $pdo->prepare($sql_inscription);
            $query_inscription->execute(array($pseudo,$pswd));
            $row_inscription = $query_inscription->fetch(PDO::FETCH_BOTH);

             if($query_inscription->rowCount() > 0) {
              $_SESSION['validate_code']='yes';
              $_SESSION['id_fournisseur_update'] = $input_fournisseur_id;

              header("Location: update_fournisseur.php");
              exit;             
          } else  {
           $pswd_update_err= "Mot de passe incorret!" ;
           $nom_fournisseur_update_mdp = $fournisseur['NOM'];
           $ID_FOURNISSEUR_UPDATE_YES = $input_fournisseur_id;
       }
    }
  }
}



?>

<?=template_header('Fournisseur')?>
<?=template_content('fournisseur')?>
  
        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-user-circle"></i> VOS FOURNISSEURS</h3>
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
                <h5><i class='fas fa-home'></i> / Fournisseurs</h5>
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
            <h2><i class="fas fa-play"></i> Informations fournisseur</h2>
            <a class="add-new" href="add_fournisseur.php" class="create-contact"><i class="fas fa-user-plus"></i> Ajouter un nouveau fournisseur</a>
            <table id="table" class="table">
                  <thead class="thead-facture">
                      <tr>
                          <td>#</td>
                          <td><?= tri('nom', 'Nom fournisseur', $_GET) ?></td>
                          <td>Phone</td>
                          <td><?= tri('ville', 'Ville', $_GET) ?></td>
                          <td></td>    
                          <td></td>
                          <td></td>
                      </tr>
                  </thead>
                  <tbody class="tbody">
                      <?php foreach ($fournisseurs as $fournisseur): ?>
                      <tr>
                          <td align='right'><?= ++$ligne?></td>
                          <td class="non_fournisseur"><?=ucwords($fournisseur['NOM'])?></td>
                          <td><?=$fournisseur['TEL']?></td>
                          <td><?=ucwords($fournisseur['VILLE'])?></td>

                            <td>
                              <form method="post">
                                <input type="text" name="fournisseur_id" value="<?=$fournisseur['ID_FOURNISSEUR']?>" hidden="true">
                                <button class="btn-detail-btn" type="submit" name="btn_information">Détails</button>
                              </form>
                            </td>  

                            <!-- LIEN AJOUTER UN STOCK -->
                            <td>
                              <form method="post">
                                <input type="text" name="fournisseur_id" value="<?=$fournisseur['ID_FOURNISSEUR']?>" hidden="true">
                                <button name="btn_faire_stock" type="submit" class="btn_faire_stock"><i class="fas fa-cart-arrow-down"></i> Ajouter un stock</button>
                              </form>
                            </td>

                             <!--TABLEAU ACTIONS --> 
                            <td class="actions">

                              <form method="post">  

                                <input type="" name="fournisseur_id" value="<?=$fournisseur['ID_FOURNISSEUR']?>" hidden="true">

                                <button data-toggle="tooltip" title="Modifier" class="btn-table" type="submit" class="btn btn-primary" name="update_fournisseur"><i class="fas fa-edit"></i></button>

                                <button data-toggle="tooltip" title="Supprimer" class="btn-table" type="submit" class="btn btn-primary" name="suppression_fournisseur"><i class="fas fa-trash-alt"></i></button>

                              </form>
                            </td>
                        </tr>

                      <?php endforeach; ?>

                    </tbody>
                </table>
                <?php if ($num_fournisseur < 1 || $page > $nbre_page): ?>
                  <?=$vide?>
                <?php endif; ?>                
            </div>
            <br>
            <!-- //// PAGINATION //// -->
                <?= template_pagination('fournisseur') ?>
            <!-- FIN PAGINATION -->                        
        </div>
 <!-- ///////////////////////////////// EXECUTION MODAL ///////////////////////////////// -->



        <!-- ///////////////////////// MODAL DETAILS //////////////////////////// -->
  <?php if (isset($fournisseur_info['NOM'])  ) :?>
          <div class="modal fade" id="modal-detail" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
            
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title"><img src="ilo/icons-100.png"> Details du fournisseur</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                  <div>
                    <form class="row" id="" action="" method="get">
                            <div class="bloc-form-1 col-md-6">
                                <div class="form-group">
                                    <label for="nom">NOM : </label>
                                    <p><?=ucwords($fournisseur_info['NOM'])?></p>
                                </div>
                                <div class="form-group">
                                    <label for="mail">COURRIEL : </label>
                                    <p><?=$fournisseur_info['COURRIEL']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="tel">TÉLÉPHONE : </label>
                                    <p><?=$fournisseur_info['TEL']?></p>
                                </div>                                
                                <div class="form-group">
                                    <label for="adresse">ADRESSE : </label>
                                    <p><?=ucwords($fournisseur_info['ADRESSE'])?></p>
                                </div>
                                <div class="form-group">
                                    <label for="ville">VILLE : </label>
                                    <p><?=ucwords($fournisseur_info['VILLE'])?></p>
                                </div>
                                <div class="form-group">
                                    <label for="pays">PAYS : </label>
                                    <p><?=ucwords($fournisseur_info['PAYS'])?></p> 
                                </div>
                            </div> 

                            <div class="bloc-form-2 col-md-6">
                                <div class="form-group">
                                    <label for="SelectStatuFournisseur">STATUT DU FOURNISSEUR :</label>
                                    <p><?=ucwords($fournisseur_info['STATUT'])?></p>
                                </div>
                                <div class="form-group">
                                    <label for="nif">NIF : </label>
                                    <p><?=$fournisseur_info['NIF']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="stat">STAT : </label>
                                    <p><?=$fournisseur_info['STAT']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="rcs">RCS : </label>
                                    <p><?=$fournisseur_info['RCS']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="date_ajout">DATE DE CREATION : </label>
                                    <p><?=date("d-m-Y", strtotime($fournisseur_info['DATE_AJOUT']))?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="ajoute_par">AJOUTE PAR : </label>
                                    <p><?=ucwords($fournisseur_info['AJOUTE_PAR'])?></p> 
                                </div>
                            </div>
                    </form> 
                    <hr class="transit">
                    <div class="bloc-form-3 col-md-12">
                      <div class="form-group">
                        <label for="nombre_stock">NOMBRE DE STOCK :</label>
                        <?php if($nombre_stock <= 0) : ?>
                            <div class="d-flex">    
                              <p>Ce fournisseur n'est pas enregistré dans le stock</p>
                              <!-- LIEN AJOUTER UN STOCK -->                   
                             <form method="post">
                                <input type="text" name="fournisseur_id" value="<?=$fournisseur_info['ID_FOURNISSEUR']?>" hidden="true">
                                <button name="btn_faire_stock" type="submit" class="btn_faire_stock"><i class="fas fa-cart-arrow-down"></i> Ajouter un stock</button> 
                             </form>
                            </div>
                                                 
                          <?php elseif ($nombre_stock >= 1) : ?>

                            <div class="d-flex">
                              <!-- VOIR DETAIL STOCK -->  
                                <p class="text-left">Ce fournisseur est déjà enregistré dans <?=$nombre_stock?> stock(s)</p>
                                <form method="post">
                                  <input type="text" name="fournisseur_id" value="<?=$fournisseur_info['ID_FOURNISSEUR']?>" hidden="true">
                                    <button name="btn_query_stock" type="submit" class="btn_faire_stock ml-2 mt-0 mb-0"><i class="far fa-question-circle"></i> Voirs les détails</button>
                            </div>
 
                            <!-- LIEN AJOUTER STOCK -->  
                              <form method="post">
                                <input type="text" name="fournisseur_id" value="<?=$fournisseur_info['ID_FOURNISSEUR']?>" hidden="true">
                                <button name="btn_faire_stock" type="submit" class="btn_faire_stock ml-5"><i class="fas fa-cart-arrow-down"></i> Ajouter un stock</button>
                              </form>
                                                
    
                        <?php endif; ?>
                      </div>
                    </div>
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

    <!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION FOURNISSEUR -->

<!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION FOURNISSEUR : Si le fournisseur n'est pas encore enregistré dans un stock -->
<!-- SUPPRESSION : MODAL MODO STOCK NO -->

<?php if (isset($ID_FOURNISSEUR) && (isset($nom_fournisseur)) && ($_SESSION['user'] <> "admin") && (!isset($_SESSION['stock'])) ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/supprimer.png">SUPPRESSION</h4>
          <button name="modal-dismiss" type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($nom_fournisseur)?></h2>
              <p>Vous êtes sur de supprimer ce fournisseur ?</p>
                <form method="post">
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_supp_fournisseur_yes"><i class="fas fa-check"></i> OUI </button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal"><i class="fas fa-times"></i> NON </button>
                  </div>                    
                </form>                

            </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION FOURNISSEUR : Si le Fournisseur est déjà enregistré dans un stock -->
<!-- SUPRESSION : MODAL MODO STOCK YES -->

<?php if (isset($ID_FOURNISSEUR) && (isset($nom_fournisseur)) && ($_SESSION['user'] <> "admin") && (isset($_SESSION['stock'])) ) :?>
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
              <h2><?=ucwords($nom_fournisseur)?></h2>
              <div class="d-flex">
                <p class="mb-2">Ce fournisseur est déjà enregistré dans <span class="bold">(<?=$nombre_stock?>) stock(s) !</span>
                </p>
                <form method="post">
                  <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR?>" hidden="true">
                  <button name="btn_query_stock" type="submit" class="btn_faire_stock mb-2 mt-0 pt-0">Voir détail stock <i class="far fa-question-circle"></i></button>
                </form>
              </div>
              <p>Pour supprimer ce fournisseur ainsi que ses stocks veuillez contactez votre administrateur !</p>
                <button type="button" class="btn btn-danger" data-dismiss="modal"> FERMER </button>
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>


<!-- MODAL A EXECTUER POUR MODERATEUR / MODIFICATION FOURNISSEUR : Si le fournisseur est déjà enregistré dans un stock -->
<!-- MODIFICATION : MODAL MODO STOCK YES -->

<?php if (isset($ID_FOURNISSEUR_UPDATE) && (isset($nom_fournisseur_update)) && ($_SESSION['user'] <> "admin") && (isset($_SESSION['stock'])) )  :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">VALIDATION MODIFICATION</h4>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=ucwords($nom_fournisseur_update)?></h2>
              <div class="d-flex">
                <p class="mb-2">Ce fournisseur est déjà enregistré dans <span class="bold">(<?=$nombre_stock?>) stock(s) !</span>
                </p>
                <form method="post">
                  <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR_UPDATE?>" hidden="true">
                  <button name="btn_query_stock" type="submit" class="btn_faire_stock mb-2 mt-0 pt-0">Voir détail stock <i class="far fa-question-circle"></i></button>
                </form>
              </div>
              <p>Pour modifier ce fournisseur veuillez contactez votre administrateur !</p>

                <div>
                    <button type="button" class="btn btn-danger" data-dismiss="modal"> FERMER </button>
                </div>                                

            </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<!-- FIN MODAL POUR MODERATEUR -->


      <!--///////////////////////// 2) MODAL POUR ADMINISTRATEUR ///////////////////////// -->

    <!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION FOURNISSEUR -->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION FOURNISSEUR : Si le fournisseur n'est pas encore enregistré dans un stock -->
<!-- SUPPRESSION : MODAL ADMIN STOCK NO -->

<?php if (isset($ID_FOURNISSEUR) && (isset($nom_fournisseur)) && ($_SESSION['user'] <> "modo") && (!isset($_SESSION['stock'])) ) :?>
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
              <h2><?=ucwords($nom_fournisseur)?></h2>
              <p>Vous êtes sur de supprimer ce fournisseur ?</p>

                <form method="post">
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_supp_fournisseur_yes"><i class="fas fa-check"></i> OUI </button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal"><i class="fas fa-times"></i> NON </button>
                  </div>                    
                </form>                     

            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>


<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION FOURNISSEUR : Si le fournisseur est déjà enregistré dans un stock -->
<!-- VALIDATION SUPPRESSION : MODAL ADMIN STOCK YES -->

<?php if (isset($ID_FOURNISSEUR) && (isset($nom_fournisseur)) && ($_SESSION['user'] <> "modo") && (isset($_SESSION['stock'])) )  :?>
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
              <form method="post">
                <h2><?=ucwords($nom_fournisseur)?></h2>
                  <div class="d-flex">
                    <p class="mb-2">Ce fournisseur est déjà enregistré dans <span class="bold">(<?=$nombre_stock?>) stock(s) !</span>
                    </p>
                    <button name="btn_query_stock" type="submit" class="btn_faire_stock mb-2 mt-0 pt-0">Voir détail stock <i class="far fa-question-circle"></i></button>
                  </div>

                  <p>Cliquez sur <span class="bold">'VALIDER'</span> pour confirmer la suppression de ce fournisseur ainsi que ses stocks, ou <span class="bold">'ANNULER'</span> pour quitter </p>
  
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_fournisseur_validation_supp_yes">VALIDER</button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal">ANNULER</button>
                  </div>                    
              </form>                            

            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!--///////////////// SUPPRESSION VALIDATION PAR MOT DEPASSE //////////////////////-->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION SUPPRESSION FOURNISSEUR AVEC STOCK PAR MOT DE PASSE -->

<?php if ((isset($ID_FOURNISSEUR_MDP)) && ($_SESSION['user'] <> "modo") && (empty($pswd_err)) && (!empty($validate))) :?>
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
              <h2><?=ucwords($nom_fournisseur_mdp)?></h2>
              <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR_MDP?>" hidden="true">
                  </div>
                  <button  id="btn-validate-del-fournisseur" name="btn-validate-del-fournisseur" type="submit" class="btn btn-primary"> Supprimer </button>
              </form> 
            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION PAR MOT DE PASSE SUPPRESSION FOURNISSEUR AVEC STOCK -->

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
              <h2><?=ucwords($nom_fournisseur_mdp)?></h2>
              
                 <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR_MDP?>" hidden="true">
                  </div>
                  <div class="modal-error">
                    <?php if(!empty($pswd_err)) : ?>
                      <p class="error"><?= $icon_type ." ". $pswd_err?></p>
                    <?php endif; ?>   
                  </div>
                    <button  id="btn-validate-del-fournisseur" name="btn-validate-del-fournisseur" type="submit" class="btn btn-primary"> Supprimer </button>              
                </form>               
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<!-- FIN SUPPRESSION FOURNISSEUR POUR ADMIN -->


      <!-- ////////////////////////// MODAL MODIFICATION POUR ADMIN /////////////////////-->

     <!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION MODIFICATION FOURNISSEUR -->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION MODIFICATION FOURNISSEUR : Si le fournisseur est déjà enregistré dans un stock -->
<!-- VALIDATION MODIFICATION : MODAL ADMIN STOCK YES -->

<?php if (isset($ID_FOURNISSEUR_UPDATE) && (isset($nom_fournisseur_update)) && ($_SESSION['user'] <> "modo") && (isset($_SESSION['stock'])) )  :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">VALIDATION MODIFICATION</h4>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <form method="post">
                <h2><?=ucwords($nom_fournisseur_update)?></h2>
                 <div class="d-flex">
                  <p class="mb-2">Ce fournisseur est déjà enregistré dans <span class="bold">(<?=$nombre_stock?>) stock(s) !</span>
                  </p>
                  <button name="btn_query_stock" type="submit" class="btn_faire_stock mb-2 mt-0 pt-0">Voir détail stock <i class="far fa-question-circle"></i></button>
                </div>
                <p>Cliquez sur <span class="bold">'VALIDER'</span> pour confirmer la modification de ce fournisseur, ou <span class="bold">'ANNULER'</span> pour quitter </p>
                  
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR_UPDATE?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_fournisseur_validation_update_yes">VALIDER</button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal">ANNULER</button>
                  </div>                    
              </form>  

            </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!--///////////////// MODIFICATION VALIDATION PAR MOT DEPASSE //////////////////////-->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION MODIFICATION FOURNISSEUR AVEC FOURNISSEUR PAR MOT DE PASSE -->

<?php if ((isset($ID_FOURNISSEUR_UPDATE_YES)) && ($_SESSION['user'] <> "modo") && (empty($pswd_update_err)) ) :?>
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
              <h2><?=ucwords($nom_fournisseur_update_mdp)?></h2>
                 <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd_update" id="pswd">
                    <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR_UPDATE_YES?>" hidden="true">
                  </div>
                     <button  id="btn-validate-update-fournisseur" name="btn-validate-update-fournisseur" type="submit" class="btn btn-primary"> Modifier </button>           
                </form> 
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION PAR MOT DE PASSE MODIFICTION FOURNISSEUR AVEC STOCK -->

 <!-- VALIDATION PAR MOT DE PASSE MODIFICATION : VALIDATION PAR MOT DE SI MOT DE PASSE VIDE OU INCORRECT -->

<?php if ((isset($ID_FOURNISSEUR_UPDATE_YES)) && (!empty($pswd_update_err)) && ($_SESSION['user'] <> "modo")) :?>
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
              <h2><?=ucwords($nom_fournisseur_update_mdp)?></h2>
                
                   <form method="post">
                    <div class="cadre-form">
                      <label for="pswd">Insérer votre mot de passe :</label>
                      <input type="password" class="form-control" placeholder="" name="pswd_update" id="pswd">
                      <input type="" name="fournisseur_id" value="<?=$ID_FOURNISSEUR_UPDATE_YES?>" hidden="true">
                    </div>
                    <div class="modal-error">
                        <?php if(!empty($pswd_update_err)) : ?>
                          <p class="error"><?= $icon_type ." ". $pswd_update_err?></p>
                        <?php endif; ?>                      
                    </div>
                        <button  id="btn-validate-update-fournisseur" name="btn-validate-update-fournisseur" type="submit" class="btn btn-primary"> Modifier </button>
                  </form> 
                
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
  unset($_SESSION['id_fournisseur']);
  unset($_SESSION['nom_fournisseur']);
  unset($_SESSION['vendu']);
  unset($_SESSION['validate']);
  unset($_SESSION['id_fournisseur_update']);
  unset($_SESSION['id_update_validate']);
  unset($_SESSION['validate_update']);
  unset($_SESSION['validate_code']);
  // Define variables and initialize with empty values
  $pswd = "";
  $pswd_err = "";
     // code...
    unset($_SESSION['nom']);
    unset($_SESSION['adresse']);
    unset($_SESSION['ville']);
    unset($_SESSION['pays']);
    unset($_SESSION['tel']);
    unset($_SESSION['mail']);
    unset($_SESSION['selectstatut']);
    unset($_SESSION['rcs']);
    unset($_SESSION['nif']);
    unset($_SESSION['stat']);   
 ?>                
 
 <!-- // SWEET ALERT NOTIFICATION // -->
<?=sweet_alert_notification()?>

<?=template_footer()?>
