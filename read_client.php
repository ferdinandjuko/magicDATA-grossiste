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
$query = "SELECT * FROM client WHERE ID=".$_SESSION['id'];

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
$pag = " ORDER BY client.ID_CLIENT DESC";
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
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of client, this is so we can determine whether there should be a next and previous button
$num_client = $pdo->query("SELECT COUNT(*) FROM client WHERE ID=".$_SESSION['id'].$search)->fetchColumn();
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_client / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Client vide</h3>";


///////////////////////// BOUTTON SUPPRESSION CLIENT /////////////////////////////////

if(isset($_POST["suppression_client"]) || isset($_POST['update_client']) || (isset($_POST['submit_supp_client_yes'])) || (isset($_POST['submit_client_validation_supp_yes'])) || (isset($_POST['submit_client_validation_update_yes'])) || (isset($_POST['btn_information'])) || (isset($_POST['btn_faire_vente'])) || (isset($_POST["btn_query_vente"])) ) {

        function data_input($data) {
            
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Prendre l'id client

        $input_client_id = data_input($_POST["client_id"]);


        // Detecter si le client existe
        $sql= "SELECT * FROM client WHERE ID_CLIENT = "."'".$input_client_id."'"." AND ID=".$_SESSION['id'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        //Verificatiion si le client n'existe pas == exit
        if (!$client) {
            exit('Ce client n\'existe pas!');
        }

        # Verification si le client à déjà effectué une vente
        $query_vente = $pdo->prepare("SELECT * FROM vente WHERE ID_CLIENT= '".$input_client_id."'");
        $query_vente->execute();
        $row_vente = $query_vente->fetch(PDO::FETCH_BOTH); 
        $nombre_vente = $query_vente->rowCount();
        if ($nombre_vente<>0) {
          $_SESSION['vente'] = "yes";
        } else {
          unset($_SESSION['vente']);
        }

/////////////////// SI L'utilisateur clic sur le boutton Voir les détails ventes //////////////////

        if(isset($_POST["btn_query_vente"])) {
          $_SESSION['query_vente']="SELECT * FROM vente WHERE ID_CLIENT= '".$input_client_id."' AND CLIENT= '".$row_vente['CLIENT']."'";
                header("Location: read_vente.php");
                exit;          
        }         

/////////////////////// SI L'utilisateur clic sur le boutton SUPPRIMER ///////////////////

        if(isset($_POST["suppression_client"])) {

        $nom_client = $client['NOM'];
        $ID_CLIENT = $input_client_id;

        } 

////////////////////// SI L'utilisateur clic sur le boutton MODIFIER /////////////////////
        elseif(isset($_POST['update_client'])) {

        $nom_client_update = $client['NOM'];
        $ID_CLIENT_UPDATE = $input_client_id; 

          if (!isset($_SESSION['vente'])) {
                   // code...
              $_SESSION['id_client_update'] = $ID_CLIENT_UPDATE;

                header("Location: update_client.php");
                exit;
            }       

        }

////////////////// SI L'utilisateur clic sur le boutton VALIDER pour SUPPRIMER ////////////////////

        elseif (isset($_POST['submit_client_validation_supp_yes'])) {
          // activation de session validation
          $validate = "yes"; 
  
          $nom_client_mdp = $client['NOM'];
          $ID_CLIENT_MDP = $input_client_id;

        } 
////////////////// SI L'utilisateur clic sur le boutton VALIDER POUR MODIFIER ////////////////////

        elseif (isset($_POST['submit_client_validation_update_yes'])) {
          // activation de session validation
          $validate = "yes"; 
  
          $nom_client_update_mdp = $client['NOM'];
          $ID_CLIENT_UPDATE_YES = $input_client_id;

        }         
        
//////////////// SI L'utilisateur clic sur le boutton OUI pour SUPPRESSIONV //////////////

        elseif (isset($_POST['submit_supp_client_yes'])) {
                // Get the contact from the client table
                $sql= "SELECT * FROM client WHERE ID_CLIENT = "."'".$input_client_id."'"." AND ID=".$_SESSION['id'];
                $stmt = $pdo->prepare($sql);
                //var_dump($sql);
                $stmt->execute();
                $client = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$client) {
                    exit('Ce client n\'existe pas!');
                }
              // User clicked the "Yes" button, delete record
              $stmt = $pdo->prepare("DELETE FROM client WHERE ID_CLIENT = "."'".$input_client_id."'"." AND ID=".$_SESSION['id']);
              $ex_supp_client=$stmt->execute();

               #INSERTION HISTORIQUE
                  if ($ex_supp_client <> false) {
                      // Définitions des varialbes pour l'insertion dans l'historique
                      $id_resp=$_SESSION['id'];
                      $date_historique= date("Y-m-d");
                      $heure_historique= date("H:i:s");
                      $action="supprimé un client";
                      $type="suppression";
                      $ajouter_par=$client['AJOUTE_PAR'];
                      $supprimer_par=$_SESSION['name'];
                      $designation = $client['NOM'];                      

                      $avant= "Nom : ".$client['NOM']." ,<br> "."Tel : ".$client['TEL']." ,<br> "."E-mail : ".$client['COURRIEL']." ,<br> "."Adresse : ".$client['ADRESSE']." ,<br> "."Ville : ".$client['VILLE']." ,<br> "."Pays : ".$client['PAYS']." ,<br> "."Statut : ".$client['STATUT']." ,<br> "."RCS : ".$client['RCS']." ,<br> "."NIF : ".$client['NIF']." ,<br> "."STAT : ".$client['STAT'] ;

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
              
              if ($ex_supp_client <> false) {
                  $_SESSION['alert_icon'] = "success";
                  $_SESSION['alert_title'] = "Suppression terminée";
                  $_SESSION['alert_text'] = "Le client à été supprimé avec succès!";
   
                  header("Location: read_client.php");
                  exit;

              } else {
                  $_SESSION['alert_icon'] = "error";
                  $_SESSION['alert_title'] = "Suppression échouée";
                  $_SESSION['alert_text'] = "Client non supprimé!";

                  header("Location: read_client.php");
                  exit;

              }

          }

/////////////// Si l'utilsateur clic sur le BOUTTON DETAIL CLIENT /////////////////////

      elseif (isset($_POST['btn_information'])) {

        $sql= "SELECT * FROM client WHERE ID_CLIENT = "."'".$input_client_id."'";
        $stmt = $pdo->prepare($sql);
        //var_dump($sql);
        $stmt->execute();
        $client_info = $stmt->fetch(PDO::FETCH_ASSOC);
  
      }

/////////////// Si l'utilisateur clic sur le BUTTON FAIRE UNE VENTE ///////////////////

      elseif (isset($_POST['btn_faire_vente'])) {

        // Prendre l'id client
        $input_client_id = data_input($_POST["client_id"]);
        $_SESSION ['client_faire_vente_id'] = $input_client_id;
        
        // Prendre le nom client
        $_SESSION ['client_faire_vente_nom'] = $client["NOM"];

        // Rediriger vers l'ajout de vente

          header("Location: add_vente.php");
      }

}  



/* SUPPRESSSION APRES VALIDATION PAR MOT DE PASS */

if (isset($_POST['btn-validate-del-client'])) {


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

      $input_client_id = data_input($_POST["client_id"]);

      // Detecter si le client existe
      $sql= "SELECT * FROM client WHERE ID_CLIENT = "."'".$input_client_id."'"." AND ID=".$_SESSION['id'];
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $client = $stmt->fetch(PDO::FETCH_ASSOC);

      //Verificatiion si le client n'existe pas == exit
      if (!$client) {
          exit('Ce client n\'existe pas!');
      } 

      // Définition de l'id et le nom du client à utiliser 
      $ID_CLIENT_MDP = $input_client_id;
      $id_client = $ID_CLIENT_MDP;
      $nom_client_mdp = $client['NOM'];

    // Validate PSWD
    $input_pswd = data_input($_POST["pswd"]);
    if(empty($input_pswd)) {
        $pswd_err = "Veuillez insérer votre mot de passe.";
    } else{
        $pswd = $input_pswd;
    }
    // Define Pseudo
    $pseudo=$_SESSION['username'];

    if ($_SESSION['user']<>"modo") {

        if (empty($pswd_err)) {
            // select inscription
            $sql_inscription="SELECT * FROM inscription WHERE 
            PSEUDO=? AND PSWD=? ";
            $query_inscription = $pdo->prepare($sql_inscription);
            $query_inscription->execute(array($pseudo,$pswd));
            $row_inscription = $query_inscription->fetchAll(PDO::FETCH_BOTH);

            // Select ID VENTE
    
            $sql="SELECT * FROM vente WHERE ID_CLIENT =".$id_client;
            $query_vente = $pdo->prepare($sql);
            $query_vente->execute();

            // Get the contact from the client table
            $sql= "SELECT * FROM client WHERE ID_CLIENT = '". $id_client ."' AND ID='". $_SESSION['id'] ."'";
            //var_dump($sql);
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $clients_historique = $stmt->fetch(PDO::FETCH_BOTH);

            if($query_inscription->rowCount() > 0) {

              
/////////////////// CODE DE SUPPRESSION VENTE ///////////////////////////////////////////

                    // Select VENTE 
                    $sql="SELECT * FROM vente WHERE ID_CLIENT =".$id_client;
                    $query_vente = $pdo->prepare($sql);
                    $query_vente->execute();
                    $vente = $query_vente->fetch(PDO::FETCH_BOTH);

                    # Definition nombre vente pour le boucle de suppresion
                    $nombre = $query_vente->rowCount();

                    # Initialisation variable boucle
                    $n = 1;

                    # Boucle de suppression vente
                    while ($n <= $nombre) {

                        // Select VENTE 
                        $sql="SELECT * FROM vente WHERE ID_CLIENT =".$id_client;
                        $query_vente = $pdo->prepare($sql);
                        $query_vente->execute();
                        $vente = $query_vente->fetch(PDO::FETCH_BOTH);

                        # Definition variable designation produit
                        $designation = $vente['NOM_PRODUIT']." ".$vente['REFERENCE']." ".$vente['QUANTITE_UNITE']." ".$vente['UNITE'];                 


                        $id_vente = $vente['ID_VENTE'];
                        $id_facture = $vente['ID_FACTURE'];
                        $id_gestion = $vente['ID_GESTION'];

                        // Select ID GESTION CAISSE 

                        $sql="SELECT * FROM caisse_vente WHERE ID_VENTE =".$id_vente;
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
                        $sql1 = "DELETE FROM caisse_vente WHERE ID_VENTE =  '".$id_vente."'";
                        $stmt = $pdo->prepare($sql1);
                        $ex_del_caisse_vente=$stmt->execute();

                        // 2. DELETE GESTION CAISSE
                        if ($ex_del_caisse_vente <> false) {
                        $stmt = $pdo->prepare("DELETE FROM gestion_caisse WHERE ID_GESTION_CAISSE =".$id_gestion_caisse);
                        $ex_del_gestion_caisse = $stmt->execute();                   
                        }
                        // 3. DEL VENTE
                        if ($ex_del_gestion_caisse <> false) {
                        $sql2 = "DELETE FROM vente WHERE ID_VENTE = '".$id_vente."'";
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
                          $ajouter_par = $vente['AJOUTE_PAR'];
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
                        }

                        # reinitialisation variable boucle 
                        $n++;
                        
                    } # Fin Boucle de suppression vente

///////////////////// FI CODE DE SUPPRESSION VENTE //////////////////////////////////

                  // 2. DEL CLIENT
                      if ($ex_del_vente <> false) {
                        // Definition des variable : avant et designation
                        
                          $designation = $clients_historique['NOM'];

                          if ($clients_historique['STATUT'] == "personnel") {
                            # Definition variable avant si STATUT PERSONNEL
                            $avant= "Nom : ".$clients_historique['NOM']." <br> "."Tel : ".$clients_historique['TEL']." <br> "."Ville : ".$clients_historique['VILLE']." <br> "."Pays : ".$clients_historique['PAYS']." <br> "."Statut : ".$clients_historique['STATUT']." <br> "."E-mail : ".$clients_historique['COURRIEL']." <br> "."Adresse : ".$clients_historique['ADRESSE'];

                          }elseif ($clients_historique['STATUT'] == "professionnel") {
                            # Definition variable avant si STATUT PROFESSIONNEL
                            $avant= "Nom : ".$clients_historique['NOM']." <br> "."Tel : ".$clients_historique['TEL']." <br> "."E-mail : ".$clients_historique['COURRIEL']." <br> "."Adresse : ".$clients_historique['ADRESSE']." <br> "."Ville : ".$clients_historique['VILLE']." <br> "."Pays : ".$clients_historique['PAYS']." <br> "."Statut : ".$clients_historique['STATUT']." <br> "."RCS : ".$clients_historique['RCS']." <br> "."NIF : ".$clients_historique['NIF']." <br> "."STAT : ".$clients_historique['STAT'] ;
                          }
                          

                          $sql4 = "DELETE FROM client WHERE ID_CLIENT = '".$id_client."'";
                          $stmt = $pdo->prepare($sql4);

                          $ex_del_client=$stmt->execute();
                      }
                      #INSERTION HISTORIQUE SUPPRESSION CLIENT
                      if ($ex_del_client <> false) {
                          // Définitions des varialbes pour l'insertion dans l'historique
                          $id_resp=$_SESSION['id'];
                          $date_historique= date("Y-m-d");
                          $heure_historique= date("H:i:s");
                          $action="supprimé un client";
                          $type="suppression";
                          $ajouter_par=$clients_historique['AJOUTE_PAR'];
                          $supprimer_par=$_SESSION['name'];

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


          // GESTION CAISSE RECALCULATION

          if ($ex_del_gestion_caisse <> false) {
            
              # MIN ID
            $query_min = $pdo->prepare("SELECT MIN(ID_GESTION_CAISSE) as min_id FROM gestion_caisse WHERE ID=".$_SESSION['id']);
            $query_min->execute();
            $row_min_id = $query_min->fetch(PDO::FETCH_BOTH);
            $gstock_min_id=$row_min_id['min_id'];

            # MAX ID
            $query_max = $pdo->prepare("SELECT MAX(ID_GESTION_CAISSE) as max_id FROM gestion_caisse WHERE ID=".$_SESSION['id']);
            $query_max->execute();
            $row_max_id = $query_max->fetch(PDO::FETCH_BOTH);
            $gstock_max_id=$row_max_id['max_id'];


            for ($x = $gstock_min_id; $x <= $gstock_max_id; $x++) {
                
                    $query_one = $pdo->prepare("SELECT * FROM gestion_caisse WHERE ID_GESTION_CAISSE=".$x." AND ID=".$_SESSION['id']);
                    $query_one->execute();
                    $row = $query_one->fetch(PDO::FETCH_BOTH);

                    if ($x === $gstock_min_id) {
                                               
                        $solde_actuel = $row['SOLDE_ACTUEL'];
                        $_SESSION['solde_actuel']= $solde_actuel;

                    } elseif ($query_one->rowCount()> 0 && $x > $gstock_min_id) {
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

              } # FIN GESTION CAISSE RECALCUL

              // SI SUPPRESSION AVEC SUCESS
              if ( ($ex_del_vente <> false) && ($ex_del_facture <> false) && ($ex_del_client <> false) && ($ex_del_caisse_vente <> false) ) {
                # code...
                $_SESSION['alert_icon'] = "success";
                $_SESSION['alert_title'] = "Suppression terminée";
                $_SESSION['alert_text'] = "Le client à été supprimé avec succès!";
                unset($_SESSION['query_vente']);


                header("Location: read_client.php");
                exit;
                
              } else {
                $_SESSION['alert_icon'] = "error";
                $_SESSION['alert_title'] = "Suppression échouée";
                $_SESSION['alert_text'] = "Client non supprimé!";
                unset($_SESSION['query_vente']);

                header("Location: read_client.php");
                exit;
              }

          #SI MOT DE PASS INCORRECT
          } else  {
              $pswd_err = "Mot de passe incorret!" ;
            }
        }

    } else {
            header('location:read_client.php');
            exit;

      }
  }

/* FIN SUPPRESSSION APRES VALIDATION PAR MOT DE PASS */



/* MODIFICATION APRES VALIDATION PAR MOT DE PASS  */

elseif (isset($_POST['btn-validate-update-client'])) {


    function data_input($data) {
          $data = trim($data);
          $data = stripslashes($data);
          $data = htmlspecialchars($data);
          return $data;
        } 

  // Prendre l'id client

      $input_client_id = data_input($_POST["client_id"]);

      // Detecter si le client existe
      $sql= "SELECT * FROM client WHERE ID_CLIENT = "."'".$input_client_id."'"." AND ID=".$_SESSION['id'];
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $client = $stmt->fetch(PDO::FETCH_ASSOC);

      //Verificatiion si le client n'existe pas == exit
      if (!$client) {
          exit('Ce client n\'existe pas!');
      } 


      // Define variables and initialize with empty values
    $id_client = $input_client_id;
    $pswd = "";
    $pswd_update_err = "";
    $icon_type= "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";

    // Validate PSWD
    $input_pswd = data_input($_POST["pswd_update"]);
    if(empty($input_pswd)){
        $pswd_update_err = "Veuillez insérer votre mot de passe.";
        $nom_client_update_mdp = $client['NOM'];
        $ID_CLIENT_UPDATE_YES = $input_client_id;
    } else{
        $pswd = $input_pswd;
    }
    // Define Pseudo
    $pseudo=$_SESSION['username'];

    if ($_SESSION['user']<>"modo") {

        if (empty($pswd_update_err)) {
            // select inscription
            $sql_inscription="SELECT * FROM inscription WHERE 
            PSEUDO=? AND PSWD=? ";
            $query_inscription = $pdo->prepare($sql_inscription);
            $query_inscription->execute(array($pseudo,$pswd));
            $row_inscription = $query_inscription->fetch(PDO::FETCH_BOTH);

             if($query_inscription->rowCount() > 0) {
              $_SESSION['validate_code']='yes';
              $_SESSION['id_client_update'] = $input_client_id;

              header("Location: update_client.php");
              exit;             
          } else  {
           $pswd_update_err= "Mot de passe incorret!" ;
           $nom_client_update_mdp = $client['NOM'];
           $ID_CLIENT_UPDATE_YES = $input_client_id;
       }
    }
  }
}



?>

<?=template_header('Client')?>
<?=template_content('client')?>
  
        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-user-circle"></i> VOS CLIENTS</h3>
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
                <h5><i class='fas fa-home'></i> / Clients</h5>
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
                <h2><i class="fas fa-play"></i> Informations client</h2>
                <a class="add-new" href="add_client.php" class="create-contact"><i class="fas fa-user-plus"></i> Ajouter un nouveau client</a>

                <!-- TABLEAU CLIENT --> 
                <table id="table" class="table">
                    <thead class="thead-facture">
                        <tr>
                            <td>#</td>
                            <td><?= tri('nom', 'Client', $_GET) ?></td>
                            <td>Phone</td>
                            <td><?= tri('ville', 'Ville', $_GET) ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody class="tbody">

                      <?php foreach ($clients as $client): ?>

                        <tr>
                            <td align='left'><?= ++$ligne?></td>

                            <td class="non_clt"><?=ucwords($client['NOM'])?></td>  

                            <td><?=$client['TEL']?></td>

                            <td><?=ucwords($client['VILLE'])?></td>

                            <td>
                              <form method="post">
                                <input type="text" name="client_id" value="<?=$client['ID_CLIENT']?>" hidden="true">
                                <button class="btn-detail-btn" type="submit" name="btn_information">Détails</button>
                              </form>
                            </td>  

                            <!-- LIEN FAIRE UNE VENTE -->
                            <td>
                              <form method="post">
                                <input type="text" name="client_id" value="<?=$client['ID_CLIENT']?>" hidden="true">
                                <button name="btn_faire_vente" type="submit" class="btn_faire_vente"><i class="fas fa-cart-arrow-down"></i> Faire une vente</button>
                              </form>
                            </td>

                             <!--TABLEAU ACTIONS --> 
                            <td class="actions">

                              <form method="post">  

                                <input type="" name="client_id" value="<?=$client['ID_CLIENT']?>" hidden="true">

                                <button data-toggle="tooltip" title="Modifier" class="btn-table" type="submit" class="btn btn-primary" name="update_client"><i class="fas fa-edit"></i></button>

                                <button data-toggle="tooltip" title="Supprimer" class="btn-table" type="submit" class="btn btn-primary" name="suppression_client"><i class="fas fa-trash-alt"></i></button>

                              </form>
                            </td>
                        </tr>

                      <?php endforeach; ?>

                    </tbody>
                </table>
                <?php if ($num_client < 1 || $page > $nbre_page): ?>
                  <?=$vide?>
                <?php endif; ?>                
            </div>
            <br>
            <!-- //// PAGINATION //// -->
                <?= template_pagination('client') ?>
            <!-- FIN PAGINATION -->                        
        </div>
 <!-- ///////////////////////////////// EXECUTION MODAL ///////////////////////////////// -->



        <!-- ///////////////////////// MODAL DETAILS //////////////////////////// -->
  <?php if (isset($client_info['NOM'])  ) :?>
          <div class="modal fade" id="modal-detail" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
            
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title"><img src="ilo/icons-100.png"> Details du client</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                  <div>
                    <form class="row" id="" action="" method="get">
                            <div class="bloc-form-1 col-md-6">
                                <div class="form-group">
                                    <label for="nom">NOM : </label>
                                    <p><?=ucwords($client_info['NOM'])?></p>
                                </div>
                                <div class="form-group">
                                    <label for="mail">COURRIEL : </label>
                                    <p><?=$client_info['COURRIEL']?></p>
                                </div>
                                <div class="form-group">
                                    <label for="tel">TÉLÉPHONE : </label>
                                    <p><?=$client_info['TEL']?></p>
                                </div>                                
                                <div class="form-group">
                                    <label for="adresse">ADRESSE : </label>
                                    <p><?=ucwords($client_info['ADRESSE'])?></p>
                                </div>
                                <div class="form-group">
                                    <label for="ville">VILLE : </label>
                                    <p><?=ucwords($client_info['VILLE'])?></p>
                                </div>
                                <div class="form-group">
                                    <label for="pays">PAYS : </label>
                                    <p><?=ucwords($client_info['PAYS'])?></p> 
                                </div>
                            </div> 

                            <div class="bloc-form-2 col-md-6">
                                <div class="form-group">
                                    <label for="SelectStatuClient">STATUT DU CLIENT :</label>
                                    <p><?=ucwords($client_info['STATUT'])?></p>
                                </div>
                                <div class="form-group">
                                    <label for="nif">NIF : </label>
                                    <p><?=$client_info['NIF']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="stat">STAT : </label>
                                    <p><?=$client_info['STAT']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="rcs">RCS : </label>
                                    <p><?=$client_info['RCS']?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="date_ajout">DATE DE CREATION : </label>
                                    <p><?=date("d-m-Y", strtotime($client_info['DATE_AJOUT']))?></p> 
                                </div>
                                <div class="form-group">
                                    <label for="ajoute_par">AJOUTE PAR : </label>
                                    <p><?=ucwords($client_info['AJOUTE_PAR'])?></p> 
                                </div>
                            </div>
                    </form> 
                    <hr class="transit">
                    <div class="bloc-form-3 col-md-12">
                      <div class="form-group">
                        <label for="nombre_vente">NOMBRE DE VENTE :</label>
                        <?php if($nombre_vente <= 0) : ?>
                            <div class="d-flex">    
                              <p>Ce client n'a pas encore effectué une vente</p>
                              <!-- LIEN FAIRE UNE VENTE -->                   
                             <form method="post">
                                <input type="text" name="client_id" value="<?=$client_info['ID_CLIENT']?>" hidden="true">
                                <button name="btn_faire_vente" type="submit" class="btn_faire_vente"><i class="fas fa-cart-arrow-down"></i> Faire une vente</button> 
                             </form>
                            </div>
                                                 
                          <?php elseif ($nombre_vente >= 1) : ?>

                            <div class="d-flex">
                              <!-- VOIR DETAIL VENTE -->  
                                <p class="text-left">Ce client a déjà effectué <?=$nombre_vente?> vente(s)</p>
                                <form method="post">
                                  <input type="text" name="client_id" value="<?=$client_info['ID_CLIENT']?>" hidden="true">
                                    <button name="btn_query_vente" type="submit" class="btn_faire_vente ml-5 mt-0 mb-0"><i class="far fa-question-circle"></i> Voirs les détails</button>
                            </div>
 
                            <!-- LIEN FAIRE UNE VENTE -->  
                              <form method="post">
                                <input type="text" name="client_id" value="<?=$client_info['ID_CLIENT']?>" hidden="true">
                                <button name="btn_faire_vente" type="submit" class="btn_faire_vente ml-5"><i class="fas fa-cart-arrow-down"></i> Faire une vente</button>
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

    <!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION CLIENT -->

<!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION CLIENT : Si le Client n'a pas encore effectué une vente -->
<!-- SUPPRESSION : MODAL MODO VENTE NO -->

<?php if (isset($ID_CLIENT) && (isset($nom_client)) && ($_SESSION['user'] <> "admin") && (!isset($_SESSION['vente'])) ) :?>
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
              <h3><?=ucwords($nom_client)?></h3>
              <p>Vous êtes sur de supprimer ce client ?</p>
                <form method="post">
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="client_id" value="<?=$ID_CLIENT?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_supp_client_yes"><i class="fas fa-check"></i> OUI </button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal"><i class="fas fa-times"></i> NON </button>
                  </div>                    
                </form>                

            </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION CLIENT : Si le Client à déjà éffectué une vente -->
<!-- SUPRESSION : MODAL MODO VENTE YES -->

<?php if (isset($ID_CLIENT) && (isset($nom_client)) && ($_SESSION['user'] <> "admin") && (isset($_SESSION['vente'])) ) :?>
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
              <h2><?=ucwords($nom_client)?></h2>
              <div class="d-flex">
                <p class="mb-2">Ce client à déjà effectué <span class="bold">(<?=$nombre_vente?>) vente(s) !</span>
                </p>
                <form method="post">
                  <input type="" name="client_id" value="<?=$ID_CLIENT?>" hidden="true">
                  <button name="btn_query_vente" type="submit" class="btn_faire_vente mb-2 mt-0 pt-0">Voir détail vente <i class="far fa-question-circle"></i></button>
                </form>
              </div>
              <p>Pour supprimer ce client ainsi que ses ventes veuillez contactez votre administrateur !</p>
                <button type="button" class="btn btn-danger" data-dismiss="modal"> FERMER </button>
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>


<!-- MODAL A EXECTUER POUR MODERATEUR / MODIFICATION CLIENT : Si le Client à déjà éffectué une vente -->
<!-- MODIFICATION : MODAL MODO VENTE YES -->

<?php if (isset($ID_CLIENT_UPDATE) && (isset($nom_client_update)) && ($_SESSION['user'] <> "admin") && (isset($_SESSION['vente'])) )  :?>
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
              <h2><?=ucwords($nom_client_update)?></h2>
              <div class="d-flex">
                <p class="mb-2">Ce client à déjà effectué <span class="bold">(<?=$nombre_vente?>) vente(s) !</span>
                </p>
                <form method="post">
                  <input type="" name="client_id" value="<?=$ID_CLIENT_UPDATE?>" hidden="true">
                  <button name="btn_query_vente" type="submit" class="btn_faire_vente mb-2 mt-0 pt-0">Voir détail vente <i class="far fa-question-circle"></i></button>
                </form>
              </div>
              <p>Pour modifier ce client veuillez contactez votre administrateur !</p>

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

    <!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION CLIENT -->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION CLIENT : Si le Client n'a pas encore effectué une vente -->
<!-- SUPPRESSION : MODAL ADMIN VENTE NO -->

<?php if (isset($ID_CLIENT) && (isset($nom_client)) && ($_SESSION['user'] <> "modo") && (!isset($_SESSION['vente'])) ) :?>
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
              <h2><?=ucwords($nom_client)?></h2>
              <p>Vous êtes sur de supprimer ce client ?</p>

                <form method="post">
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="client_id" value="<?=$ID_CLIENT?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_supp_client_yes"><i class="fas fa-check"></i> OUI </button>
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

<?php if (isset($ID_CLIENT) && (isset($nom_client)) && ($_SESSION['user'] <> "modo") && (isset($_SESSION['vente'])) )  :?>
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
                <h2><?=ucwords($nom_client)?></h2>
                  <div class="d-flex">
                    <p class="mb-2">Ce client à déjà effectué <span class="bold">(<?=$nombre_vente?>) vente(s) !</span>
                    </p>
                    <button name="btn_query_vente" type="submit" class="btn_faire_vente mb-2 mt-0 pt-0">Voir détail vente <i class="far fa-question-circle"></i></button>
                  </div>

                  <p>Cliquez sur <span class="bold">'VALIDER'</span> pour confirmer la suppression de ce client ainsi que ses ventes, ou <span class="bold">'ANNULER'</span> pour quitter </p>
  
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="client_id" value="<?=$ID_CLIENT?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_client_validation_supp_yes">VALIDER</button>
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

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION SUPPRESSION CLIENT AVEC VENTE PAR MOT DE PASSE -->

<?php if ((isset($ID_CLIENT_MDP)) && ($_SESSION['user'] <> "modo") && (empty($pswd_err)) && (!empty($validate))) :?>
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
              <h2><?=ucwords($nom_client_mdp)?></h2>
              <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="" name="client_id" value="<?=$ID_CLIENT_MDP?>" hidden="true">
                  </div>
                  <button  id="btn-validate-del-client" name="btn-validate-del-client" type="submit" class="btn btn-primary"> Supprimer </button>
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
              <h2><?=ucwords($nom_client_mdp)?></h2>
              
                 <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="" name="client_id" value="<?=$ID_CLIENT_MDP?>" hidden="true">
                  </div>
                  <div class="modal-error">
                    <?php if(!empty($pswd_err)) : ?>
                      <p class="error"><?= $icon_type ." ". $pswd_err?></p>
                    <?php endif; ?>   
                  </div>
                    <button  id="btn-validate-del-client" name="btn-validate-del-client" type="submit" class="btn btn-primary"> Supprimer </button>              
                </form>               
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<!-- FIN SUPPRESSION CLIENT POUR ADMIN -->


      <!-- ////////////////////////// MODAL MODIFICATION POUR ADMIN /////////////////////-->

     <!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION MODIFICATION CLIENT -->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION MODIFICATION CLIENT : Si le Client a déjà effectué une vente -->
<!-- VALIDATION MODIFICATION : MODAL ADMIN VENTE YES -->

<?php if (isset($ID_CLIENT_UPDATE) && (isset($nom_client_update)) && ($_SESSION['user'] <> "modo") && (isset($_SESSION['vente'])) )  :?>
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
                <h2><?=ucwords($nom_client_update)?></h2>
                 <div class="d-flex">
                  <p class="mb-2">Ce client à déjà effectué <span class="bold">(<?=$nombre_vente?>) vente(s) !</span>
                  </p>
                  <button name="btn_query_vente" type="submit" class="btn_faire_vente mb-2 mt-0 pt-0">Voir détail vente <i class="far fa-question-circle"></i></button>
                </div>
                <p>Cliquez sur <span class="bold">'VALIDER'</span> pour confirmer la modification de ce client, ou <span class="bold">'ANNULER'</span> pour quitter </p>
                  
                  <div class="valide-annule mt-2 mb-3">
                    <input type="" name="client_id" value="<?=$ID_CLIENT_UPDATE?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_client_validation_update_yes">VALIDER</button>
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

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION MODIFICATION CLIENT AVEC VENTE PAR MOT DE PASSE -->

<?php if ((isset($ID_CLIENT_UPDATE_YES)) && ($_SESSION['user'] <> "modo") && (empty($pswd_update_err)) ) :?>
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
              <h2><?=ucwords($nom_client_update_mdp)?></h2>
                 <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd_update" id="pswd">
                    <input type="" name="client_id" value="<?=$ID_CLIENT_UPDATE_YES?>" hidden="true">
                  </div>
                     <button  id="btn-validate-update-client" name="btn-validate-update-client" type="submit" class="btn btn-primary"> Modifier </button>           
                </form> 
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION PAR MOT DE PASSE MODIFICTION CLIENT AVEC VENTE -->

 <!-- VALIDATION PAR MOT DE PASSE MODIFICATION : VALIDATION PAR MOT DE SI MOT DE PASSE VIDE OU INCORRECT -->

<?php if ((isset($ID_CLIENT_UPDATE_YES)) && (!empty($pswd_update_err)) && ($_SESSION['user'] <> "modo")) :?>
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
              <h2><?=ucwords($nom_client_update_mdp)?></h2>
                
                   <form method="post">
                    <div class="cadre-form">
                      <label for="pswd">Insérer votre mot de passe :</label>
                      <input type="password" class="form-control" placeholder="" name="pswd_update" id="pswd">
                      <input type="" name="client_id" value="<?=$ID_CLIENT_UPDATE_YES?>" hidden="true">
                    </div>
                    <div class="modal-error">
                        <?php if(!empty($pswd_update_err)) : ?>
                          <p class="error"><?= $icon_type ." ". $pswd_update_err?></p>
                        <?php endif; ?>                      
                    </div>
                        <button  id="btn-validate-update-client" name="btn-validate-update-client" type="submit" class="btn btn-primary"> Modifier </button>
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
  unset($_SESSION['id_client']);
  unset($_SESSION['nom_client']);
  unset($_SESSION['vendu']);
  unset($_SESSION['validate']);
  unset($_SESSION['id_client_update']);
  unset($_SESSION['id_update_validate']);
  unset($_SESSION['validate_update']);
  unset($_SESSION['validate_code']);
  // Define variables and initialize with empty values
  $pswd = "";
  $pswd_err = "";

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
