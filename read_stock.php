<?php
include 'functions.php';
# Démarrage de la session
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
$query = "SELECT stock.ID_STOCK, stock.ID_FOURNISSEUR, stock.DATE_AJOUT, stock.NOM_PRODUIT, stock.REFERENCE, stock.UNITE, stock.QUANTITE_UNITE, stock.PRIX_UNITAIRE, stock.QUANTITE, stock.DATE_ACHAT, stock.FOURNISSEUR, stock.DESCRIPTION, stock.AJOUTE_PAR, stock.IMAGE FROM stock, fournisseur WHERE (stock.ID_FOURNISSEUR=fournisseur.ID_FOURNISSEUR) AND fournisseur.ID=".$_SESSION['id'];
$params = $search= $nombre_vente= "";
$sortable = ["date_ajout", "nom_produit", "reference","prix_unitaire","prix_totale" ,"date_achat", "fournisseur", "ajoute_par"];
$pag = "";
$ligne = 0;
for ($x=1;$x < $page;$x++) {
    $ligne =$ligne+$records_per_page;
}

//recherche par nom
if (!empty($_GET['q'])){
    $params = "'%" . $_GET['q'] . "%'";
    $search = " AND (stock.NOM_PRODUIT LIKE $params OR stock.DATE_AJOUT LIKE $params OR stock.REFERENCE LIKE $params OR stock.DATE_ACHAT LIKE $params OR stock.FOURNISSEUR LIKE $params OR stock.AJOUTE_PAR LIKE $params)";
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
if (isset($_SESSION['query_stock'])) {
  # code...
  $query=$_SESSION['query_stock'];
  unset($_SESSION['query_stock']);
}

$stmt = $pdo->prepare($query);
$stmt->bindValue(':current_page', ($page-1)*$records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':record_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch the records so we can display them in our template.
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get the total number of stock
$num_stock = $pdo->query("SELECT COUNT(*) FROM stock, fournisseur WHERE (stock.ID_FOURNISSEUR=fournisseur.ID_FOURNISSEUR) AND fournisseur.ID=".$_SESSION['id'].$search)->fetchColumn();

$numero_page = "";
$currentPage = ($page-1)*$records_per_page;
$nbre_page = ceil($num_stock / $records_per_page);
$before_page = $page - 1;
$after_page = $page + 1;
$vide = "<h3 class='tableau-vide-h3'>Aucun stock ajouté pour le moment</h3>";

// Define variables and initialize with empty values
$pswd = "";
$pswd_err = "";
$icon_type = "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";

///// LORQUE L'UTILISATEUR CLIC SUR LE BOUTON SUPPRESSION STOCK ///
    
if( isset($_POST["suppression_stock"]) || isset($_POST["btn_query_vente"]) || isset($_POST['submit_sup_stock_vente_non']) || isset($_POST["submit_sup_stock_yes"]) || isset($_POST['btn_validate_sup_stock_yes']) || isset($_POST["detail_stock"]) ) {

        function data_input($data) {
            
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        
        // Prendre l'id stock

        $input_stock_id = data_input($_POST["stock_id"]);


        // Trouver le nom du produit à partir de son ID

        $sql_del_stock = "SELECT stock.ID_STOCK, stock.ID_FOURNISSEUR, stock.DATE_AJOUT, stock.NOM_PRODUIT, stock.REFERENCE, stock.UNITE, stock.QUANTITE_UNITE, stock.PRIX_UNITAIRE, stock.QUANTITE, stock.DATE_ACHAT, stock.FOURNISSEUR, stock.DESCRIPTION, stock.AJOUTE_PAR, stock.IMAGE FROM stock, fournisseur WHERE (stock.ID_FOURNISSEUR=fournisseur.ID_FOURNISSEUR) AND fournisseur.ID=".$_SESSION['id']." AND stock.ID_STOCK=".$input_stock_id;   
        $query_del_stock= $pdo->prepare($sql_del_stock);
        $query_del_stock->execute();
        $row_del_stock = $query_del_stock->fetch(PDO::FETCH_BOTH);

        $designation = ucfirst($row_del_stock['NOM_PRODUIT'])." ".ucfirst($row_del_stock['REFERENCE'])." ".$row_del_stock['QUANTITE_UNITE']." ".strtoupper($row_del_stock['UNITE']);        

        //////////////////////

        # Quiter si le stock n'existe pas
        $stock_exist = $query_del_stock->rowCount();
        if ($stock_exist<=0) {

           exit("LE STOCK N'EXISTE PAS!");
        }

        // Detecter si le produit a été déjà vendu 

          # selectionner l'id gestion du stock 
          $sql_gestion_stock="SELECT * FROM gestion_stock WHERE SESSION_ID=".$_SESSION['id']." AND gestion_stock.NOM_PRODUIT='".$row_del_stock['NOM_PRODUIT']."' AND gestion_stock.REFERENCE='".$row_del_stock['REFERENCE']."' AND gestion_stock.UNITE='".$row_del_stock['UNITE']."' AND gestion_stock.QUANTITE_UNITE='".$row_del_stock['QUANTITE_UNITE']."' AND gestion_stock.PRIX_UNITAIRE='".$row_del_stock['PRIX_UNITAIRE']."' AND gestion_stock.FOURNISSEUR='".$row_del_stock['FOURNISSEUR']."'";

          $query_gestion_stock= $pdo->prepare($sql_gestion_stock);
          $query_gestion_stock->execute();
          $row_gestion_stock = $query_gestion_stock->fetch(PDO::FETCH_BOTH);
          $id_gestion = $row_gestion_stock['ID_GESTION'];


          # detecter si le stock est déjà vendu à partir de l'id gestion
          $sql_vente = "SELECT * FROM vente, client WHERE (client.ID_CLIENT=vente.ID_CLIENT) AND (client.ID=".$_SESSION['id'].") AND vente.ID_GESTION=".$row_gestion_stock['ID_GESTION'];
          $query_vente= $pdo->prepare($sql_vente);
          $query_vente->execute();
          $row_vente = $query_vente->fetch(PDO::FETCH_BOTH);
          $ID_VENTE = $row_vente['ID_VENTE'];
          $nombre_vente=$query_vente->rowCount();     

        // LE VARIABLE ET SESSION AVANT D'EXECUTER LE MODAL 

/////////////////// SI L'utilisateur clic sur le boutton Voir les détails ventes //////////////////

        if(isset($_POST["btn_query_vente"])) {
            $_SESSION['query_vente']="SELECT * FROM vente WHERE ID_GESTION = '".$id_gestion."'";
                header("Location: read_vente.php");
                exit;           
        } 

/////////////////// SI L'utilisateur clic sur le boutton Voir les détails STOCK //////////////////

        if(isset($_POST["detail_stock"])) {
            $_SESSION['id_viewstock'] = $input_stock_id;
            header("Location: view_stock.php");
            exit;           
        } 
        

////////// SI L'utilisateur clic sur le boutton OUI suppression stock sans vente ////////////////

        // SUPPRESSION STOCK, ET RECALCULATION GESTION CAISSE
        if (isset($_POST['submit_sup_stock_vente_non'])) {

                   $ID_STOCK = $input_stock_id ;


                    // SELECT ID CAISSE STOCK ET ID GESTION CAISSE
                    $sql="SELECT * FROM caisse_stock WHERE ID_GESTION =".$id_gestion." AND ID_STOCK=".$ID_STOCK;
                    $query_caisse_stock = $pdo->prepare($sql);
                    $query_caisse_stock->execute();
                    $row_caisse_stock = $query_caisse_stock->fetch(PDO::FETCH_BOTH);
                    $id_gestion_caisse = $row_caisse_stock['ID_GESTION_CAISSE'];
                    $id_caisse_stock = $row_caisse_stock['ID_CAISSE_STOCK'];

                   #DELETE CAISSE STOCK
                    $stmt = $pdo->prepare("DELETE FROM caisse_stock WHERE ID_CAISSE_STOCK =".$id_caisse_stock);
                    $ex_del_caisse_stock = $stmt->execute();

                    if($ex_del_caisse_stock<>true){

                        exit("CAISSE STOCK");
                    }
                               
                // GESTION CAISSE DELETE ET RECALCUL

                    #DELETE GESTION CAISSE
                    $stmt = $pdo->prepare("DELETE FROM gestion_caisse WHERE ID_GESTION_CAISSE =".$id_gestion_caisse);
                    $ex_del_gestion_caisse = $stmt->execute(); 

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
                                $query = $pdo->prepare("UPDATE gestion_caisse SET SOLDE_ACTUEL = '$solde_actuel' WHERE ID_GESTION_CAISSE =".$x." AND SESSION_ID=".$_SESSION['id']);
                                $ex_calcul_gestion_caisse =$query->execute();
                                # Mettre en session le solde précédent
                                $_SESSION['solde_actuel']= $solde_actuel;
                            }


                        } 


                    // AVOIR LE QUANTITE DE STOCK DANS LA STOCK
                    $stock = $pdo->prepare("SELECT * FROM stock WHERE ID_STOCK = ".$ID_STOCK);
                    $stock->execute();
                    $row_stock = $stock->fetch(PDO::FETCH_BOTH);
                    $QUANTITE_STOCK = $row_stock['QUANTITE'];

                    // AVOIR LE QUANTITE DE STOCK DANS LA STOCK
                    $stock = $pdo->prepare("SELECT * FROM stock WHERE ID_STOCK = ".$ID_STOCK);
                    $stock->execute();
                    $row_stock = $stock->fetch(PDO::FETCH_BOTH);
                    $QUANTITE_STOCK = $row_stock['QUANTITE'];                                         
                    // AVOIR LA QUANTITE DE STOCK DANS LA GESTION STOCK
                    $gestion_stock = $pdo->prepare("SELECT * FROM gestion_stock WHERE ID_GESTION = ".$id_gestion);
                    $gestion_stock->execute();
                    $row_gestion_stock = $gestion_stock->fetch(PDO::FETCH_BOTH);
                    $QUANTITE_STOCK_GSTOCK = $row_gestion_stock['QUANTITE'];

                    #DELETE STOCK
                    $stmt = $pdo->prepare("DELETE FROM stock WHERE ID_STOCK = ".$ID_STOCK);
                    $ex_del_stock = $stmt->execute();            

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

                    #DELETE DEFFECTUEUX
                    $stmt = $pdo->prepare("DELETE FROM deffectueux WHERE ID_STOCK = ".$ID_STOCK);
                    $ex_del_deff = $stmt->execute();

                    #INSERTION HISTORIQUE SUPPRESSION STOCK
                    if ($ex_del_stock <> false) {
                          // Définitions des varialbes pour l'insertion dans l'historique
                          $id_resp = $_SESSION['id'];
                          $date_historique = date("Y-m-d");
                          $heure_historique = date("H:i:s");
                          $action = "supprimé un stock";
                          $type = "suppression";
                          $ajouter_par = $row_del_stock['AJOUTE_PAR'];
                          $supprimer_par = $_SESSION['name'];                 

                          $avant = "Designation : ".$designation." <br> "."Prix_unitaire : ".$row_del_stock['PRIX_UNITAIRE']." Ar <br> "."Quantité : ".$row_del_stock['QUANTITE']." <br> "."Prix Total : ".$row_del_stock['PRIX_TOTALE']." Ar <br> "."Date achat : ".$row_del_stock['DATE_ACHAT']." <br> "."Fournisseur : ".$row_del_stock['FOURNISSEUR']." <br> "."Ajouté par : ".$row_del_stock['AJOUTE_PAR']." <br> "."Descritpion : ".$row_del_stock['DESCRIPTION'];

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
                    // SI LA SUPPRESSION A BIEN DEROULÉ SANS PROBLEME
                    if (($ex_del_caisse_stock <> false) && ($ex_del_gestion_caisse <> false) && ($ex_del_gestion <> false) && ($ex_del_stock <> false)) {
                            # ALERT SI SUPRRESSION AVEC SUCCES 
                              $_SESSION['alert_icon'] = "success";
                              $_SESSION['alert_title'] = "Suppresion terminée";
                              $_SESSION['alert_text'] = "Le stock a été supprimé avec succès!";  
                            # REDIRIGER VERS LE STOCK SI SUPRESSION AVEC SUCCES 
                            header("location: read_stock.php");
                            exit();
                    } else {
                            # ALERT SI SUPRRESSION AVEC SUCCES 
                              $_SESSION['alert_icon'] = "warning";
                              $_SESSION['alert_title'] = "Suppression échoué!";
                              $_SESSION['alert_text'] = "La suppresion a échoué, le stock n'a pas été supprimé!";  
                            # REDIRIGER VERS LE STOCK SI SUPRESSION AVEC SUCCES 
                            header("location: read_stock.php");
                            exit();               
                    }


        } //FIN [submit_sup_stock_vente_non]



//////////////////////////////// CODE DE SUPPRESION STOCK ////////////////////////////////////

        // SUPPRESSION STOCK POUR ADMIN VENTE YES

        /* DELETE AFTER VALIDATE CODE */

        if (isset($_POST['btn_validate_sup_stock_yes'])) {

            // Define variables and initialize with empty values
            //$id_client=$_SESSION['id_clt'];
            $pswd = "";
            $pswd_err = "";
            $icon_type = "<i id='icon-error' class='fas fa-exclamation-triangle'></i>";

               // Validate PSWD
            $input_pswd = data_input($_POST["pswd"]);
            if(empty($input_pswd)) {
                $pswd_err = "Veuillez insérer votre mot de passe.";
            } else{
                $pswd = $input_pswd;
            }
            // Define Pseudo
            $pseudo=$_SESSION['username'];
            
            if ($_SESSION['user'] <> "modo" && empty($pswd_err)) {

                // select inscription
                $sql_inscription="SELECT * FROM inscription WHERE 
                PSEUDO=? AND PSWD=? ";
                $query_inscription = $pdo->prepare($sql_inscription);
                $query_inscription->execute(array($pseudo,$pswd));
                $row_inscription = $query_inscription->fetch(PDO::FETCH_BOTH);

                if($query_inscription->rowCount() > 0) {


/////////////////// CODE DE SUPPRESSION VENTE ///////////////////////////////////////////

                    // Select VENTE 
                    $sql="SELECT * FROM vente WHERE ID_GESTION =".$id_gestion;
                    $query_vente = $pdo->prepare($sql);
                    $query_vente->execute();
                    $nombre = $query_vente->rowCount();

                    # Initialisation variable boucle
                    $n = 1;

                    # Boucle de suppression vente
                    while ($n <= $nombre) {
                        // Select VENTE 
                        $sql="SELECT * FROM vente WHERE ID_GESTION =".$id_gestion;
                        $query_vente = $pdo->prepare($sql);
                        $query_vente->execute();
                        $vente = $query_vente->fetch(PDO::FETCH_BOTH);

                        $id_vente = $vente['ID_VENTE'];
                        $id_facture = $vente['ID_FACTURE'];

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

////////////////////// SUPPRESSION DU STOCK /////////////////////////

                   $ID_STOCK = $input_stock_id ;


                    // SELECT ID CAISSE STOCK ET ID GESTION CAISSE
                    $sql="SELECT * FROM caisse_stock WHERE ID_GESTION =".$id_gestion." AND STOCK=".$ID_STOCK;
                    $query_caisse_stock = $pdo->prepare($sql);
                    $query_caisse_stock->execute();
                    $row_caisse_stock = $query_caisse_stock->fetch(PDO::FETCH_BOTH);
                    $id_gestion_caisse = $row_caisse_stock['ID_GESTION_CAISSE'];
                    $id_caisse_stock = $row_caisse_stock['ID_CAISSE_STOCK'];

                   #DELETE CAISSE STOCK
                    $stmt = $pdo->prepare("DELETE FROM caisse_stock WHERE ID_CAISSE_STOCK =".$id_caisse_stock);
                    $ex_del_caisse_stock = $stmt->execute();

                    if ($ex_del_caisse_stock<>true){
                        exit("CAISSE STOCK");
                    }
                               

                    #DELETE GESTION CAISSE
                    $stmt = $pdo->prepare("DELETE FROM gestion_caisse WHERE ID_GESTION_CAISSE =".$id_gestion_caisse);
                    $ex_del_gestion_caisse = $stmt->execute(); 


                    // AVOIR LE QUANTITE DE STOCK DANS LA STOCK
                    $stock = $pdo->prepare("SELECT * FROM stock WHERE ID_STOCK = ".$ID_STOCK);
                    $stock->execute();
                    $row_stock = $stock->fetch(PDO::FETCH_BOTH);
                    $QUANTITE_STOCK = $row_stock['QUANTITE'];                

                    // AVOIR LE QUANTITE DE STOCK DANS LA GESTION STOCK
                    $gestion_stock = $pdo->prepare("SELECT * FROM gestion_stock WHERE ID_GESTION = ".$id_gestion);
                    $gestion_stock->execute();
                    $row_gestion_stock = $gestion_stock->fetch(PDO::FETCH_BOTH);
                    $QUANTITE_STOCK_GSTOCK = $row_gestion_stock['QUANTITE'];

                    #DELETE STOCK
                    $stmt = $pdo->prepare("DELETE FROM stock WHERE ID_STOCK = ".$ID_STOCK);
                    $ex_del_stock = $stmt->execute();            

                    if ($QUANTITE_STOCK_GSTOCK >= $QUANTITE_STOCK ) {
                        # code... 
                        $QUANTITE = ($QUANTITE_STOCK_GSTOCK) - ($QUANTITE_STOCK);

                        #UPDATE GESTION STOCK
                        $stmt = $pdo->prepare("UPDATE gestion_stock SET QUANTITE = ".$QUANTITE." WHERE ID_GESTION =".$id_gestion);
                        $ex_del_gestion = $stmt->execute();                     


                    } elseif ($QUANTITE_STOCK > $QUANTITE_STOCK_GSTOCK) {
                        # code...
                        #DELETE GESTION STOCK
                        $stmt = $pdo->prepare("DELETE FROM gestion_stock WHERE ID_GESTION = ".$id_gestion);
                        $ex_del_gestion = $stmt->execute(); 
                    }

                    #DELETE DEFFECTUEUX
                    $stmt = $pdo->prepare("DELETE FROM deffectueux WHERE ID_STOCK = ".$ID_STOCK);
                    $ex_del_deff = $stmt->execute();

                    #INSERTION HISTORIQUE SUPPRESSION STOCK
                    if ($ex_del_stock <> false) {
                          // Définitions des varialbes pour l'insertion dans l'historique
                          $id_resp = $_SESSION['id'];
                          $date_historique = date("Y-m-d");
                          $heure_historique = date("H:i:s");
                          $action = "supprimé un stock";
                          $type = "suppression";
                          $ajouter_par = $row_del_stock['AJOUTE_PAR'];
                          $supprimer_par = $_SESSION['name'];                 

                          $avant = "Designation : ".$designation." <br> "."Prix_unitaire : ".$row_del_stock['PRIX_UNITAIRE']." Ar <br> "."Quantité : ".$row_del_stock['QUANTITE']." <br> "."Prix Total : ".$row_del_stock['PRIX_TOTALE']." Ar <br> "."Date achat : ".$row_del_stock['DATE_ACHAT']." <br> "."Fournisseur : ".$row_del_stock['FOURNISSEUR']." <br> "."Ajouté par : ".$row_del_stock['AJOUTE_PAR']." <br> "."Descritpion : ".$row_del_stock['DESCRIPTION'];

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

/////////////// FIN CODE SUPPRESSION DU STOCK //////////////////////                 

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

                    } # FIN GESTION CAISSE RECALCULATION

                      // SI SUPPRESSION AVEC SUCESS
                      if ( ($ex_del_vente <> false) && ($ex_del_facture <> false) && ($ex_del_caisse_vente <> false) && ($ex_del_caisse_stock <> false) && ($ex_del_gestion_caisse <> false) && ($ex_del_gestion <> false) && ($ex_del_stock <> false) ) {

                        # Définition session sweet alert 
                        $_SESSION['alert_icon'] = "success";
                        $_SESSION['alert_title'] = "Suppression terminée";
                        $_SESSION['alert_text'] = "Le vente à été supprimé avec succès!";
                        unset($_SESSION['query_vente']);

                        header("Location: read_stock.php");
                        exit;
                        
                      } else {
                        $_SESSION['alert_icon'] = "error";
                        $_SESSION['alert_title'] = "Suppression échouée";
                        $_SESSION['alert_text'] = "Client non supprimé!";
                        unset($_SESSION['query_vente']);

                        header("Location: read_stock.php");
                        exit;
                      } 
 //////////////////// FIN CODE DE SUPPRESSION VENTE //////////////////////////////


                } else {
                   $pswd_err = "Votre mot de passe est incorrect.";

                }

            }

        }


}


?>

<?=template_header('Stocks')?>
<?=template_content('stocks')?>
   
        <!-- CONTENU -->
        <div id="container-page" class="container">
            <div id="search-cadre" class="row">
                <div class="col-md-6">
                    <h3><i id="fa-user-circle" class="fas fa-layer-group"></i> HISTORIQUE STOCKS</h3>
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
                <h5><i class='fas fa-home'></i> / Inventaire / Historique Stocks</h5>
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
            <div>
                <div class="cadre-title">
                    <h2><i class="fas fa-play"></i> Historique Stocks</h2>
                    <div class="cadre-autre-title">
                        <h2><a class="autre-btn" href="read_deffectueux.php"><i class="fas fa-stream"></i> Voir Stock defectueux</a></h2>
                        <h2><a class="autre-btn" href="read_gestion_stock.php"><i class="fas fa-layer-group"></i> Voir Gestion stock</a></h2>                     
                    </div>                   
                </div>
                <div id="cadre-add-new">
                    <a class="add-new" href="add_stock.php" class="create-contact"><i class="fas fa-cart-plus"></i> Ajouter nouveau stock</a>
                </div>

                <table id="table" class="table">
                    <thead class="thead-facture">
                        <tr>
                            <td>#</td>
                            <td><?= tri('date_ajout', 'Date d\'ajout', $_GET) ?></td>
                            <td><?= tri('reference', 'Designation', $_GET) ?></td>
                            <td><?= tri('prix_unitaire', 'Prix unitaire', $_GET) ?></td>
                            <td>Quantité</td>
                            <td><?= tri('prix_totale', 'Prix totale', $_GET) ?></td>
                            <td></td>                
                            <td></td>
                        </tr>
                    </thead>
                    <tbody class="tbody">
                        <?php foreach ($stocks as $stock): ?>
                        <tr>
                            <td align='right'><?= ++$ligne?></td>
                            <td><?=date("d-m-Y", strtotime($stock['DATE_AJOUT']))?></td>
                            <td><?=ucfirst($stock['NOM_PRODUIT'])." ".ucfirst($stock['REFERENCE'])." ".$stock['QUANTITE_UNITE']." ".strtoupper($stock['UNITE'])?></td>
                            <td><?=price($stock['PRIX_UNITAIRE'])?></td>
                            <td><?=$stock['QUANTITE']?></td>
                            <td><?=price($stock['PRIX_UNITAIRE']*$stock['QUANTITE'])?></td>
                            <td>
                              <form method="post">
                                <input type="text" name="stock_id" value="<?=$stock['ID_STOCK']?>" hidden="true">
                                <button class="detail-btn" type="submit" name="detail_stock">Détails</button>
                              </form>
                            </td>
                            <td class="actions">
                              <form method="post">
                                <input type="" name="stock_id" value="<?=$stock['ID_STOCK']?>" hidden="true">                               
                                <button class="btn-table" type="submit" class="btn btn-primary" name="suppression_stock"><i class="fas fa-trash-alt"></i></button>
                              </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($num_stock < 1 || $page > $nbre_page): ?>
                  <?=$vide?>
                <?php endif; ?>
                <br>
                
                <!-- PAGINATION -->
                    <?= template_pagination('stock') ?>
                <!-- FIN PAGINATION -->
                  
            </div>
        </div>

        <!-- ///////////////////////// FIN MODAL DETAIL //////////////////////////// -->

        <!-- ///////////////////////// 1) MODAL POUR MODERATEUR //////////////////////////// -->

    <!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION CLIENT -->

<!-- MODAL A EXECTUER POUR MODERATEUR / SUPPRESSION CLIENT : Si le Client n'a pas encore effectué une vente -->
<!-- SUPPRESSION : MODAL MODO VENTE NO -->

<?php if ( isset($_POST["suppression_stock"]) && ($nombre_vente == 0) && ($_SESSION['user'] <> "admin") ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/supprimer.png">SUPPRESSION STOCK</h4>
          <button name="modal-dismiss" type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=$designation?></h2>
              <p>Vous êtes sur de supprimer ce stock ?</p>

                <form method="post">
                  <div class="yesno mt-2 mb-3">
                    <input type="" name="stock_id" value="<?=$input_stock_id?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_sup_stock_vente_non">OUI</button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal">NON</button>
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

<?php if ( isset($_POST["suppression_stock"]) && ($nombre_vente > 0) && ($_SESSION['user'] <> "admin") ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">SUPPRESSION STOCK</h4>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=$designation?></h2>
              <p>Ce stock a déjà été vendu. Nombre de <span class="bold">vente (<?=$nombre_vente?>).</span>
              </p>
                <form method="post">
                  <input type="" name="stock_id" value="<?=$input_stock_id?>" hidden="true">
                  <button name="btn_query_vente" type="submit" class="btn_faire_vente mb-2 mt-0 pt-0">Voir détail vente <i class="far fa-question-circle"></i></button>
                </form>
              <p>Pour supprimer ce stock ainsi que ses ventes, veuillez contacter votre administrateur !</p>
                <button type="button" class="btn btn-danger" data-dismiss="modal"> FERMER </button>
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

      <!--///////////////////////// 2) MODAL POUR ADMINISTRATEUR ///////////////////////// -->

    <!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION CLIENT -->

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / SUPPRESSION CLIENT : Si le Client n'a pas encore effectué une vente -->
<!-- SUPPRESSION : MODAL ADMIN VENTE NO -->

<?php if ( isset($_POST["suppression_stock"]) && ($nombre_vente == 0) && ($_SESSION['user'] <> "modo")  ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/supprimer.png">SUPPRESSION STOCK</h4>
          <button name="modal-dismiss" type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=$designation?></h2>
              <p>Vous êtes sur de supprimer ce stock ?</p>

                <form method="post">
                  <div class="yesno mt-2 mb-3">
                    <input type="" name="stock_id" value="<?=$input_stock_id?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_sup_stock_vente_non">OUI</button>
                    <button type="button" class="btn btn-outline-secondary ml-2 mr-2 font-weight-bold" data-dismiss="modal">NON</button>
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

<?php if ( isset($_POST["suppression_stock"]) && ($nombre_vente > 0) && ($_SESSION['user'] <> "modo")  ) :?>
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
              <h2><?=$designation?></h2>
              <p>Ce stock a déjà été vendu. Nombre de <span class="bold">vente (<?=$nombre_vente?>).</span>
              </p>
                <form method="post">
                  <input type="" name="stock_id" value="<?=$input_stock_id?>" hidden="true">
                  <button name="btn_query_vente" type="submit" class="btn_faire_vente mb-2 mt-0 pt-0">Voir détail vente <i class="far fa-question-circle"></i></button>
                </form>              
              <p>Cliquez sur <span class="bold">'VALIDER'</span> pour confirmer la suppression de ce stock ainsi que ses ventes, ou <span class="bold">'ANNULER'</span> pour quitter </p>
              <form method="post">
                 <div class="valide-annule mt-4 mb-3">

                    <input type="" name="stock_id" value="<?=$input_stock_id?>" hidden="true">
                    <button type="submit" class="btn btn-outline-danger ml-2 mr-2 font-weight-bold" name="submit_sup_stock_yes">VALIDER</button>
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

<?php if ( isset($_POST["submit_sup_stock_yes"]) && ($_SESSION['user'] <> "modo") ) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">VALIDATION SUPPRESSION ok </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>
              <h2><?=$designation?></h2>
              <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="" name="stock_id" value="<?=$input_stock_id?>" hidden="true">
                    <p><?=$input_stock_id?></p>
                  </div>
                  <button  id="btn-validate-del-client" name="btn_validate_sup_stock_yes" type="submit" class="btn btn-primary"> Supprimer </button>
              </form> 
            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- MODAL A EXECTUER POUR ADMINISTRATEUR / VALIDATION PAR MOT DE PASSE SUPPRESSION CLIENT AVEC VENTE -->

 <!-- VALIDATION PAR MOT DE PASSE SUPPRESSION : VALIDATION PAR MOT DE SI MOT DE PASSE VIDE OU INCORRECT -->

<?php if (!empty($pswd_err) && ($_SESSION['user'] <> "modo")) :?>
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
              <h2><?=$designation?></h2>
              
                 <form method="post">
                  <div class="cadre-form">
                    <label for="pswd">Insérer votre mot de passe :</label>
                    <input type="password" class="form-control" placeholder="" name="pswd" id="pswd">
                    <input type="" name="stock_id" value="<?=$input_stock_id?>" hidden="true">
                    <p><?=$input_stock_id?></p>                    
                  </div>
                  <div class="modal-error">
                    <?php if(!empty($pswd_err)) : ?>
                      <p class="error"><?= $icon_type ." ". $pswd_err?></p>
                    <?php endif; ?>   
                  </div>
                    <button  id="btn-validate-del-client" name="btn_validate_sup_stock_yes" type="submit" class="btn btn-primary"> Supprimer </button>              
                </form>               
            </div>  
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<!-- FIN SUPPRESSION CLIENT POUR ADMIN -->


<!-- /////////// EFFACEMENT SESSION ////////// -->
<?php 
    unset($_SESSION['id_viewstock']);
 ?> 
 <!-- // SWEET ALERT NOTIFICATION // -->
<?=sweet_alert_notification()?>
<?=template_footer()?>
