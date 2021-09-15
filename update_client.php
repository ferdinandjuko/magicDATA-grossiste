
<?php
include 'functions.php';
session_start();

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();


$pdo = pdo_connect_mysql();
$msg = '';
$ligne = 0;

   // Define variables and initialize with empty values
    $nom = $tel = $mail = $adresse = $ville = $pays = $id_resp= $ajoute_par= $selectstatut= $rcs = $nif = $stat = $personnel_active = $professionnel_active = "";
    $nom_err =  $tel_err = $mail_err = $adresse_err = $ville_err = $pays_err = $selectstatut_err = $rcs_err = $nif_err = $stat_err = "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";
   

if (isset($_SESSION['id_client_update'])) {

    // Get the contact from the contacts table
    $sql= "SELECT * FROM client WHERE ID_CLIENT = "."'".$_SESSION['id_client_update']."'";
    $stmt = $pdo->prepare($sql);
    //var_dump($sql);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$client) {
            // Records created successfully. Redirect to landing page;
            header("location: read_client.php");
            exit(); 
    }

    /* SELECTION ID CLIENT S'IL A DEJA EFFECTUE UNE VENTE */
    $query_vente = $pdo->prepare("SELECT * FROM vente WHERE ID_CLIENT= ".$_SESSION['id_client_update']);
    $query_vente->execute();
    $row_vente = $query_vente->fetch(PDO::FETCH_BOTH); 
    $nombre_vente = $query_vente->rowCount();  
    
    if ($nombre_vente <> 0) {
          # code...
        if ($_SESSION['validate_code']<>'yes') {
            # code...
            // Records created successfully. Redirect to landing page;
            header("location: read_client.php");
            exit();    
        }
      }
} else {
    // si la session id client n'est pas definie
    header("location: read_client.php");
    exit();       
}
    
if ( isset($_POST['submit_modification_client']) || isset($_POST['update_client']) ) {

        // Effacement de la session validation code avant lancement modification
        unset($_SESSION['validate_code']);

        // 1- SI L'utilisateur clic sur le bouton MODIFIER 
        if (isset($_POST['submit_modification_client'])) {

            function data_input($data) {
                  $data = trim($data);
                  $data = stripslashes($data);
                  $data = htmlspecialchars($data);
                  return $data;
                }
            
            // Validate Nom
            $input_nom = data_input($_POST["nom"]);

            if(empty($input_nom)){
               $nom_err = "Veuillez insérer le client";
            } elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ&' ]*$/",$input_nom)) {
               $nom_err = "Les caractères spéciaux ne sont pas acceptés"; 
               # mettre en session le donné inséré en cas d'erreur
               $_SESSION['nom'] = $input_nom;           
            } else{
               $nom = $input_nom;
               # conversion en lettre minuscul utf-8 de donné inséré
               $nom = mb_strtolower($nom, 'UTF-8');
               # mettre en session le donné inséré pour l'inserer en cas de doublon
               $_SESSION['nom'] = $nom; 
            }
               
            // Validate ADRESSE
            $input_adresse = data_input($_POST["adresse"]);

            if(empty($input_adresse)){
                $adresse = "";
            }elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_adresse)) {
               $adresse_err = "Les caractères spéciaux ne sont pas acceptés"; 
               # mettre en session le donné inséré en cas d'erreur
               $_SESSION['adresse'] = $input_adresse;
            } else{
                $adresse = $input_adresse;
                # conversion en lettre minuscul utf-8 de donné inséré
                $adresse = mb_strtolower($adresse, 'UTF-8');
                # mettre en session le donné inséré pour l'inserer en cas de doublon
                $_SESSION['adresse'] = $adresse;
            } 
            // Validate VILLE
            $input_ville = data_input($_POST["ville"]);
            if(empty($input_ville)){
                $ville_err = "Veuillez insérer une ville";
            } elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_ville)) {
               $ville_err = "Les caractères spéciaux ne sont pas acceptés"; 
               # mettre en session le donné inséré en cas d'erreur
               $_SESSION['ville'] = $input_ville;
            } else{
                $ville = $input_ville;
                # conversion en lettre minuscul utf-8 de donné inséré
                $ville = mb_strtolower($ville, 'UTF-8');
                # mettre en session le donné inséré pour l'inserer en cas de doublon
                $_SESSION['ville'] = $ville;
            }
            // Validate PAYS
            $input_pays = data_input($_POST["pays"]);
            if(empty($input_pays)){
                $pays_err = "Veuillez insérer un pays";
            }elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_pays)) {
               $pays_err = "Les caractères spéciaux ne sont pas acceptés"; 
               # mettre en session le donné inséré en cas d'erreur
               $_SESSION['pays'] = $input_pays;          
            } else {
                $pays = $input_pays;
                # conversion en lettre minuscul utf-8 de donné inséré
                $pays = mb_strtolower($pays, 'UTF-8');
                # mettre en session le donné inséré pour l'inserer en cas de doublon
                $_SESSION['pays'] = $pays;            
            }
            // Validate TELPHONE
            $input_tel = data_input($_POST["tel"]);

            if(empty($input_tel)){
                $tel_err = "Veuillez insérer un numero téléphone";
            } elseif (!preg_match("/^[0+1+2+3+4+5+6+7+8+9+ ]*$/",$input_tel)) {
               $tel_err = "Seulement les chiffres sont acceptés";  // 1= erreur
               # mettre en session le donné inséré en cas d'erreur
               $_SESSION['tel'] = $input_tel;
            }else{
                $tel = $input_tel;
                # conversion en lettre minuscul utf-8 de donné inséré
                $tel = mb_strtolower($tel, 'UTF-8');
                # mettre en session le donné inséré pour l'inserer en cas de doublon
                $_SESSION['tel'] = $tel;             

            }       
            // Validate mail
            $input_mail = data_input($_POST["mail"]);

            if(empty($input_mail)){
                $mail = "";
            } elseif(!filter_var($input_mail, FILTER_VALIDATE_EMAIL)) {
                $mail_err = "Votre mail est invalide";
               # mettre en session le donné inséré en cas d'erreur
               $_SESSION['mail'] = $input_mail;            
            } else{
                $mail = $input_mail;
                # conversion en lettre minuscul utf-8 de donné inséré
                $mail = mb_strtolower($mail, 'UTF-8');
                # mettre en session le donné inséré pour l'inserer en cas de doublon
                $_SESSION['mail'] = $mail;
            }

            // Validate SELECT STATUT CLIENT
            $input_selectstatut = data_input($_POST["selectstatut"]);

            if ($input_selectstatut == "vide"){
                $selectstatut_err = "Selectionner la statut du client";   
                # mettre en session le donné inséré pour l'inserer en cas de doublon
                $_SESSION['selectstatut'] = $input_selectstatut;                                           
            } else{
                $selectstatut = $input_selectstatut;
                # conversion en lettre minuscul utf-8 de donné inséré
                $selectstatut = mb_strtolower($selectstatut, 'UTF-8');
                # mettre en session le donné inséré pour l'inserer en cas de doublon
                $_SESSION['selectstatut'] = $selectstatut;
            }

            # SI LE CLIENT EST PROFESSIONNEL :
            if ($selectstatut=="professionnel") {

                // Validate RCS
                   $input_rcs = data_input($_POST["rcs"]);

                    if ( (empty($input_rcs)) && ($selectstatut=="professionnel") ){
                       $rcs_err = "Insérer le numero RCS";                         
                    }  elseif(!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_rcs)) {
                        $rcs_err = "RCS invalide";
                       # mettre en session le donné inséré en cas d'erreur
                       $_SESSION['rcs'] = $input_rcs;                
                    } else{
                        $rcs = $input_rcs;
                        # conversion en lettre minuscul utf-8 de donné inséré
                        $rcs = mb_strtolower($rcs, 'UTF-8');
                        # mettre en session le donné inséré pour l'inserer en cas de doublon
                        $_SESSION['rcs'] = $rcs;
                    } 

                // Validate NIF
                    $input_nif = data_input($_POST["nif"]);

                    if(empty($input_nif) && ($selectstatut=="professionnel") ){
                        $nif_err = "Insérer le NIF";
                    } elseif(!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_nif)) {
                        $nif_err = "NIF invalide";
                        # mettre en session le donné inséré en cas d'erreur
                       $_SESSION['nif'] = $input_nif;                                     
                    } else{
                        $nif = $input_nif;
                        # conversion en lettre minuscul utf-8 de donné inséré
                        $nif = mb_strtolower($nif, 'UTF-8');
                        # mettre en session le donné inséré pour l'inserer en cas de doublon
                        $_SESSION['nif'] = $nif;
                    } 

                // Validate STAT
                    $input_stat = data_input($_POST["stat"]);

                    if(empty($input_stat)){
                        $stat_err = "Insérer le STAT";                          
                    } elseif(!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_stat)) {
                        $stat_err = "STAT invalide";
                        # mettre en session le donné inséré en cas d'erreur
                       $_SESSION['stat'] = $input_stat;                      
                    } else{
                        $stat = $input_stat;
                        $_SESSION['stat'] = $stat;
                        # conversion en lettre minuscul utf-8 de donné inséré
                        $stat = mb_strtolower($stat, 'UTF-8');
                        # mettre en session le donné inséré pour l'inserer en cas de doublon
                        $_SESSION['stat'] = $stat;                   
                    }  
            // METTRE EN SESSION LE SELECT STATU EN CAS D'ERREUR INPUT RCS, NIF et STAT
            if ($selectstatut=="professionnel") {
                # mettre en session le donné inséré en cas d'erreur
                $professionnel_active = "active";
                $client['STATUT'] = "professionnel";
            }

            # SI LE CLIENT EST PERSONNEL :        
            } elseif ($selectstatut=="personnel") {
                // Validate RCS
                        $rcs = "";
                // Validate NIF
                        $nif = "";
                // Validate STAT
                        $stat = ""; 
            }   

            // DETECTER SI LE CLIENT A INSERER EXISTE DEJA DANS LE TABLE CLIENT 

                    $query_doublon = "SELECT * FROM client WHERE ID = ".$_SESSION['id']." AND NOM = :NOM";

                    $stmt = $pdo->prepare($query_doublon);
                    $stmt->bindParam(':NOM', $nom);
                    $stmt->execute();
                    $dclients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $nombre = $stmt->rowCount();

                    if( ($nombre > 0 &&  $client['NOM'] <> $nom  && ( empty($nom_err) ) && empty($adresse_err) && empty($ville_err) && empty($pays_err) && empty($tel_err) && empty($mail_err) && empty($selectstatut_err) && empty($rcs_err) && empty($stat_err) && empty($nif_err) ) )  {

                        $_SESSION['client_existe'] = "yes";
                        $_SESSION['NOM'] = $nom;
                        $_SESSION['nombre'] = $nombre;

                    } else {
                        unset($_SESSION['client_existe']);
                    }

        //2- Si l'utilisateur clic sur le bouton modifier meme s'il y a doublon
        } elseif ( isset($_POST['update_client']) ) {

            #Définir les variables par les sessions sauvegardé

            $nom = $_SESSION['nom'];
            $adresse = $_SESSION['adresse'];
            $ville = $_SESSION['ville'];
            $pays = $_SESSION['pays'];
            $tel = $_SESSION['tel'];
            $mail = $_SESSION['mail'];
            $selectstatut = $_SESSION['statut'];
            $rcs = $_SESSION['rcs'];
            $nif = $_SESSION['nif'];
            $stat = $_SESSION['stat'];
            $_SESSION['validate_code'] = "yes";           

        }


        // MODIFICATION S'IL N'Y A PAS D'ERREUR ou S'IL Y A DOUBLON MAIS NOUS MODIFIONS QUAND MEME

        if ( (empty($nom_err) && empty($adresse_err) && empty($ville_err) && empty($pays_err) && empty($tel_err) && empty($mail_err) && empty($selectstatut_err) && empty($rcs_err) && empty($stat_err) && empty($nif_err) && !isset($_SESSION['client_existe'])) ) {
                // prepare sql and bind parameters

                $ajoute_par = $_SESSION['name'];
                $id_resp = $_SESSION['id'];
                $date_ajout = date("Y-m-d");

                $sql="UPDATE client SET NOM = :NOM, TEL =:TEL, COURRIEL =:COURRIEL, ADRESSE = :ADRESSE, VILLE = :VILLE, PAYS = :PAYS, STATUT = :STATUT, RCS = :RCS, NIF = :NIF, STAT = :STAT WHERE ID_CLIENT ="."'".$_SESSION['id_client_update']."'";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':NOM', $nom);
                $stmt->bindParam(':TEL', $tel);
                $stmt->bindParam(':COURRIEL', $mail);
                $stmt->bindParam(':ADRESSE', $adresse);
                $stmt->bindParam(':VILLE', $ville);
                $stmt->bindParam(':PAYS', $pays);
                $stmt->bindParam(':STATUT', $selectstatut);
                $stmt->bindParam(':RCS', $rcs);
                $stmt->bindParam(':NIF', $nif);
                $stmt->bindParam(':STAT', $stat);

                // Update the record
                $ex_update = $stmt->execute();

                if (($nombre_vente<>0) && ($client['NOM'] <> $nom)) {
                    $sql="UPDATE vente SET CLIENT =:NOM WHERE ID_CLIENT ="."'".$_SESSION['id_client_update']."'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':NOM', $nom);

                    $ex_update_vente=$stmt->execute();
                }

              #INSERTION HISTORIQUE
                if ($ex_update <> false) {
                    //definition autre variable
                    $date_historique= date("Y-m-d");
                    $heure_historique= date("H:i:s");
                    $action="modifié un client";
                    $type="modification";
                    $modifier_par = $_SESSION['name'];
                    $ajouter_par=$client['AJOUTE_PAR'];
                    $designation = $client['NOM'];                    
                    
                    if ( ($client['NOM'] <> $nom) || ($client['TEL'] <> $tel) || ($client['COURRIEL'] <> $mail) || ($client['ADRESSE'] <> $adresse) || ($client['VILLE'] <> $ville) || ($client['PAYS'] <> $pays) || ($client['STATUT'] <> $selectstatut) || ($client['RCS'] <> $rcs) || ($client['NIF'] <> $nif) || ($client['STAT'] <> $stat) ) 
                    {
                            // definition de variables APRES et AVANT modification
                            // APRES
                            $nom = "Nom : ".$nom ;
                            $tel = "Tel : ".$tel ;
                            $mail = "E-mail : ".$mail ;
                            $adresse = "Adresse : ".$adresse ;
                            $ville = "Ville : ".$ville ;
                            $pays = "Pays : ".$pays ;
                            $selectstatut = "Statut : ".$selectstatut ;
                            $rcs = "RCS : ".$rcs ;
                            $nif = "NIF : ".$nif ;
                            $stat = "STAT : ".$stat ;
                            // AVANT
                            $client['NOM'] = "Nom : ".$client['NOM'] ;
                            $client['TEL'] = "Tel : ".$client['TEL'] ;
                            $client['COURRIEL'] = "E-mail : ".$client['COURRIEL'] ;
                            $client['ADRESSE'] = "Adresse : ".$client['ADRESSE'] ;
                            $client['VILLE'] = "Ville : ".$client['VILLE'] ;
                            $client['PAYS'] = "Pays : ".$client['PAYS'] ;
                            $client['STATUT'] = "Statut : ".$client['STATUT'] ;
                            $client['RCS'] = "RCS : ".$client['RCS'] ;
                            $client['NIF'] = "NIF : ".$client['NIF'] ;
                            $client['STAT'] = "STAT : ".$client['STAT'] ;  
                        // reinitialistion variable avant et apres :
                            $apres = $avant = "";                        
                        if (($client['NOM'] <> $nom)) {
                          
                            # si le NOM à été modifié
                            $avant=$avant.$client['NOM']." <br> ";
                            $apres=$apres.$nom." <br> ";
                        }
                        if (($client['TEL'] <> $tel)) {
                        # si le TEL à été modifié
                            $avant=$avant.$client['TEL']." <br> ";
                            $apres=$apres.$tel." <br> ";
                        }
                        if (($client['COURRIEL'] <> $mail)) {
                                                 
                            # si le COURRIEL été modifié
                            $avant=$avant.$client['COURRIEL']." <br> ";
                            $apres=$apres.$mail." <br> ";
                        }
                        if (($client['ADRESSE'] <> $adresse)) {
                                                 
                            # si le ADRESSE été modifié
                            $avant=$avant.$client['ADRESSE']." <br> ";
                            $apres=$apres.$adresse." <br> ";
                        }  
                        if (($client['VILLE'] <> $ville)) {
                                                 
                            # si le VILLE à été modifié
                            $avant=$avant.$client['VILLE']." <br> ";
                            $apres=$apres.$ville." <br> ";
                        }
                        if (($client['PAYS'] <> $pays)) {
                                                 
                            # si le PAYS à été modifié
                            $avant=$avant.$client['PAYS']." <br> ";
                            $apres=$apres.$pays." <br> ";
                        }   
                        if (($client['STATUT'] <> $selectstatut)) {
                                                 
                            # si le STATUT à été modifié
                            $avant=$avant.$client['STATUT']." <br> ";
                            $apres=$apres.$selectstatut." <br> ";
                        }
                       if (($client['NIF'] <> $nif)) {
                                                 
                            # si le NIF à été modifié
                            $avant=$avant.$client['NIF']." <br> ";
                            $apres=$apres.$nif." <br> ";
                        }  
                        if (($client['RCS'] <> $rcs)) {
                                                 
                            # si le RCS à été modifié
                            $avant=$avant.$client['RCS']." <br> ";
                            $apres=$apres.$rcs." <br> ";
                        }
                        if (($client['STAT'] <> $stat)) {
                                                 
                            # si le STAT à été modifié
                            $avant=$avant.$client['STAT']." ";
                            $apres=$apres.$stat." ";
                        }                                                                      

                        # SI MODIFICATION A ETE APPORTEE

                        // prepare sql and bind parameters
                        $sql="INSERT INTO historique (ID, DATE_HISTORIQUE, HEURE_HISTORIQUE, ACTION, TYPE, DESIGNATION, AJOUTER_PAR, AVANT, APRES, MODIFIER_PAR)
                        VALUES (:ID, :DATE_HISTORIQUE, :HEURE_HISTORIQUE, :ACTION, :TYPE, :DESIGNATION, :AJOUTER_PAR, :AVANT, :APRES, :MODIFIER_PAR)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':ID', $id_resp);
                        $stmt->bindParam(':DATE_HISTORIQUE', $date_historique);
                        $stmt->bindParam(':HEURE_HISTORIQUE', $heure_historique);                    
                        $stmt->bindParam(':ACTION', $action);
                        $stmt->bindParam(':TYPE', $type);
                        $stmt->bindParam(':DESIGNATION', $designation);                        
                        $stmt->bindParam(':AJOUTER_PAR', $ajouter_par);
                        $stmt->bindParam(':AVANT', $avant);
                        $stmt->bindParam(':APRES', $apres);
                        $stmt->bindParam(':MODIFIER_PAR', $modifier_par);
                        // Update the record
                        $ex_historique = $stmt->execute();
                    }
 
                }


            // Attempt to execute the prepared statement
                if($ex_update <> false){

                    if ( ($client['NOM'] <> $nom) || ($client['TEL'] <> $tel) || ($client['COURRIEL'] <> $mail) || ($client['ADRESSE'] <> $adresse) || ($client['VILLE'] <> $ville) || ($client['PAYS'] <> $pays) || ($client['STATUT'] <> $selectstatut) || ($client['RCS'] <> $rcs) || ($client['NIF'] <> $nif) || ($client['STAT'] <> $stat) ) {
                        # ALERT SI MODIFICATION A ETE APPORTEE
                        $_SESSION['alert_icon'] = "success";
                        $_SESSION['alert_title'] = "Modification terminée";
                        $_SESSION['alert_text'] = "Le client à été modifier avec succès!"; 


                            unset($_SESSION['client_existe']);
                            unset($_SESSION['NOM']);
                            unset($_SESSION['nombre']);
                            unset($_SESSION['query_detail']);

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

                    // Records created successfully. Redirect to landing page;
                    header("location: update_client.php");
                    exit();
                    } else  {

                        # ALERT SI AUCUNE MODIFICATION

                        $_SESSION['alert_icon'] = "info";
                        $_SESSION['alert_title'] = "Aucune modification a été apportée";
                        $_SESSION['alert_text'] = "L\'information du client n\'a pas été modifiée!";
                    // Records created successfully. Redirect to landing page;
                    /*header("location: update_client.php");
                    exit();*/
                    }

                } else {
                        $_SESSION['alert_icon'] = "error";
                        $_SESSION['alert_title'] = "Erreur modification";
                        $_SESSION['alert_text'] = "Une erreur a été trouvée. Veuillez vous connecter à nouveau!";
                } 

        
         } else {

            # Redefinition ID et code de valisation avant de reactualisation

            $_SESSION['id_client_update'] = $_SESSION['id_client_update'];
            $_SESSION['validate_code'] ='yes';

         }
        


       // VOIR DETAIL DOUBLON
     } elseif (isset($_POST['detail_client'])) {

              $nom =($_SESSION['nom']);
              $_SESSION['query_detail'] = "SELECT * FROM client WHERE ID=".$_SESSION['id']." AND NOM='".$nom."'";
                unset($_SESSION['client_existe']);
                unset($_SESSION['adresse']);
                unset($_SESSION['ville']);
                unset($_SESSION['pays']);
                unset($_SESSION['tel']);
                unset($_SESSION['mail']);
                unset($_SESSION['statut']);
                unset($_SESSION['rcs']);
                unset($_SESSION['nif']);
                unset($_SESSION['stat']);
                header("location: read_client.php");
                exit();                  
        }


?>

<?=template_header('Read')?>

<?=template_content('client')?>

    <!-- CONTENU -->
        <div id="container-page" class="container">

    <!-- FIN FONCTION -->  

            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><i class='fas fa-home'></i> / Clients / Modification</h5>
                <div class="navigation-retour-active">
                  <a href="index.php" ><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-avancer-active">
                  <a href="add_client.php" href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                
            </div>
                <!-- Fin navigation --> 
        <div id="cadre-contenu">
            <div class="title-cadre">
                <h2><i class="fas fa-play"></i> Modification du client :</h2>
                <h2 class="float-right"># <?=ucwords($client['NOM'])?></h2>
            </div>
            <div class="container">
                    <form id="form-update" action="" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-inline">
                                    <label for="nom">FOURNISSEUR </label>
                                                         
                                    <input class="form-control" type="text" name="nom" value="<?php if(isset($_SESSION['nom'])) {echo ucwords($_SESSION['nom']); } else {echo ucwords($client['NOM']);} ?>" oninput="controlInputNOM();" id="nom">
                                    <?php if(!empty($nom_err)) : ?>
                                    <p id="erreur-nom" class="erreur-p"><?= $icon_type ." ". $nom_err?></p>
                                    <?php endif; ?>                                       
                                </div>
                                <div class="form-inline">
                                    <label for="nom">TELEPHONE </label>
                                      
                                    <input class="form-control" type="text" name="tel" value="<?php if(isset($_SESSION['tel'])) {echo $_SESSION['tel']; } else {echo ucwords($client['TEL']);} ?>" oninput="controlInputTEL();" id="tel">
                                    <?php if(!empty($tel_err)) : ?>
                                    <p id="erreur-tel" class="erreur-p"><?= $icon_type ." ". $tel_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                                <div class="form-inline">
                                    <label for="nom">VILLE </label>
                                      
                                    <input class="form-control" type="text" name="ville" value="<?php if(isset($_SESSION['ville'])) {echo ucwords($_SESSION['ville']); } else {echo ucwords($client['VILLE']);} ?>" oninput="controlInputVILLE();" id="ville">
                                    <?php if(!empty($ville_err)) : ?>
                                    <p id="erreur-ville" class="erreur-p"><?= $icon_type ." ". $ville_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                                <div class="form-inline">
                                    <label for="nom">PAYS </label>
                                      
                                    <input class="form-control" type="text" name="pays" value="<?php if(isset($_SESSION['pays'])) {echo $_SESSION['pays']; } else {echo ucwords($client['PAYS']);} ?>" oninput="controlInputPAYS();" id="pays"> 
                                    <?php if(!empty($pays_err)) : ?>
                                    <p id="erreur-pays" class="erreur-p"><?= $icon_type ." ". $pays_err?></p>
                                    <?php endif; ?>                                    
                                </div>                                
                                <div class="form-inline">
                                    <label for="nom">E-MAIL </label>
                                      
                                    <input class="form-control" type="text" name="mail" id="courriel" value="<?php if(isset($_SESSION['mail'])) {echo $_SESSION['mail']; } else {echo ucwords($client['COURRIEL']);} ?>" oninput="controlInputCOURRIEL();" >
                                    <?php if(!empty($mail_err)) : ?>
                                    <p id="erreur-courriel" class="erreur-p"><?=$icon_type ." ". $mail_err?></p>
                                    <?php endif; ?>                               
                                </div>
                                <div class="form-inline"> 
                                    <label for="nom">ADRESSE </label>
                                      
                                    <input class="form-control" type="text" name="adresse" value="<?php if(isset($_SESSION['adresse'])) {echo $_SESSION['adresse']; } else {echo ucwords($client['ADRESSE']);} ?>" id="adresse">
                                    <?php if(!empty($adresse_err)) : ?>
                                    <p class="erreur-p"><?=$icon_type ." ". $adresse_err?></p>
                                    <?php endif; ?>                                
                                </div>

                            </div>
                            <div id="form-col-2" class="col-md-6">
                            <?php if( ($client['STATUT']=="personnel")  ) : ?>    
                                <div class="form-inline"> 
                                <label for="nom">STATUT </label>
                                                                  
                                  <select class="custom-select mr-sm-2"  id="SelectStatu" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="selectstatut">
                                    <option value="personnel">PERSONNEL</option>
                                    <option value="vide">----------- CHOISIR -----------</option>
                                    <option value="personnel">PERSONNEL</option>
                                    <option value="professionnel">PROFESSIONNEL</option>
                                  </select>
                                    <?php if(!empty($selectstatut_err)) : ?>
                                    <p id="erreur-select" class="erreur-p"><?= $icon_type ." ". $selectstatut_err?></p>
                                    <?php endif; ?>                                  
                                </div>  
                            <?php elseif ( empty($client['STATUT'])  ) : ?> 
                                <div class="form-inline"> 
                                <label for="nom">STATUT </label>
                                                                 
                                  <select class="custom-select mr-sm-2"  id="SelectStatu" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="selectstatut">
                                    <option value="vide">----------- CHOISIR -----------</option>
                                    <option value="personnel">PERSONNEL</option>
                                    <option value="professionnel">PROFESSIONNEL</option>
                                  </select>
                                    <?php if(!empty($selectstatut_err)) : ?>
                                    <p id="erreur-select" class="erreur-p"><?= $icon_type ." ". $selectstatut_err?></p>
                                    <?php endif; ?>                                  
                                </div>
                            <?php elseif ( ($client['STATUT']=="professionnel") ): ?> 
                                <div class="form-inline"> 
                                    <label for="nom">STATUT </label>
                                                                     
                                  <select class="custom-select mr-sm-2"  id="SelectStatu" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="selectstatut">
                                    <option value="professionnel">PROFESSIONNEL</option>
                                    <option value="vide">----------- CHOISIR -----------</option>
                                    <option value="personnel">PERSONNEL</option>
                                    <option value="professionnel">PROFESSIONNEL</option>
                                  </select>
                                    <?php if(!empty($selectstatut_err)) : ?>
                                    <p id="erreur-select" class="erreur-p"><?= $icon_type ." ". $selectstatut_err?></p>
                                    <?php endif; ?>                                  
                                </div>                                
                            <?php endif; ?> 
                                
                            <!-- NIF -->
                            <!-- NIF SI ERREUR -->
                            <?php if(!empty($nif_err)) : ?> 
                                <div class="form-inline">
                                    <label for="nom">NIF </label>
                                    <input class="form-control" type="text" name="nif" value="<?php if(isset($_SESSION['nif'])) {echo $_SESSION['nif'];}else{echo $client['NIF'];}?>" oninput="controlInputNIF();" id="nif">
                                    <p id="erreur-nif"><?= $icon_type ." ". $nif_err?></p> 
                                </div>                            
       
                            <!-- NIF SI STATUT AUTRE QUE PROFESSIONNEL -->
                            <?php elseif( ($client['STATUT']<>"professionnel") ) : ?> 
                                <div class="form-inline">
                                    <label for="nom">NIF </label>
                                      
                                    <input disabled class="form-control" type="text" name="nif" value="<?=$client['NIF']?>" id="nif">
                                </div>

                            <!-- NIF SI PROFESSIONNEL -->
                            <?php else : ?> 
                                <div class="form-inline">
                                    <label for="nom">NIF </label>
                                       
                                    <input class="form-control" type="text" name="nif" value="<?php if(isset($_SESSION['nif'])) {echo $_SESSION['nif'];}else{echo $client['NIF'];}?>" id="nif">
                                </div>                                
                            <?php endif; ?>                                                             
                            <!-- RCS -->
                            <!-- RCS SI ERREUR -->
                            <?php if(!empty($rcs_err)) : ?> 
                                <div class="form-inline">
                                    <label for="nom">RCS </label>
                                      
                                    <input class="form-control" type="text" name="rcs" value="<?php if(isset($_SESSION['rcs'])) {echo $_SESSION['rcs'];}else{echo $client['RCS'];}?>" oninput="controlInputRCS();" id="rcs">
                                    <p id="erreur-rcs"><?= $icon_type ." ". $rcs_err?></p>
                                </div>                            
       
                            <!-- RCS SI STATUT AUTRE QUE PROFESSIONNEL -->
                            <?php elseif( ($client['STATUT']<>"professionnel") ) : ?> 
                                <div class="form-inline">
                                    <label for="nom">RCS </label>
                                      
                                    <input disabled class="form-control" type="text" name="rcs" value="<?=$client['RCS']?>" id="rcs">
                                </div>

                            
                            <!-- RCS SI PROFESSIONNEL -->
                            <?php else : ?> 
                                <div class="form-inline">
                                    <label for="nom">RCS </label>
                                      
                                    <input class="form-control" type="text" name="rcs" value="<?php if(isset($_SESSION['rcs'])) {echo $_SESSION['rcs'];}else{echo $client['RCS'];}?>" id="rcs">
                                </div>                                
                            <?php endif; ?> 

                            <!-- STAT -->
                            <!-- STAT SI ERREUR -->
                            <?php if(!empty($stat_err)) : ?> 
                                <div class="form-inline">
                                    <label for="nom">STAT </label>
                                      
                                    <input class="form-control" type="text" name="stat" value="<?php if(isset($_SESSION['stat'])) {echo $_SESSION['stat'];}else{echo $client['STAT'];}?>" oninput="controlInputSTAT();" id="stat">
                                    <p id="erreur-stat"><?= $icon_type ." ". $stat_err?></p>
                                </div>                            
       
                            <!-- RCS SI STATUT AUTRE QUE PROFESSIONNEL -->
                            <?php elseif( ($client['STATUT']<>"professionnel") ) : ?> 
                                <div class="form-inline">
                                    <label for="nom">STAT </label>
                                      
                                    <input disabled class="form-control" type="text" name="stat" value="<?=$client['STAT']?>" id="stat">
                                </div>

                            
                            <!-- RCS SI PROFESSIONNEL -->
                            <?php else : ?> 
                                <div class="form-inline">
                                    <label for="nom">STAT </label>
                                      
                                    <input class="form-control" type="text" name="stat" value="<?php if(isset($_SESSION['stat'])) {echo $_SESSION['stat'];}else{echo $client['STAT'];}?>" id="stat">
                                </div>                                
                            <?php endif; ?>    
                            
                                <div id="update-btn" class="form-group">
                                  <input class="btn btn-primary" type="submit" name="submit_modification_client" value="MODIFIER">  
                                </div>                                 
                            </div> 
                        </div>   
                    </form>          
                </div>

        </div>

 
<!-- MODALE SI DOUBLON DETECTÉ -->

<?php if (isset($_SESSION['client_existe'])) :?>
  <!-- The Modal -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"><img src="ilo/warning.png">VALIDATION</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
        <!-- Fin navigation --> 
            <div>

              <h5>DOUBLON DETECTÉ! (<?= $_SESSION['nombre']?>)</h5> 
                <p><span class="bold">" <?=ucwords($nom)?> " </span> existe déjà dans le table CLIENT </p>
                <p><span class="bold">" Tel " </span> = <?=ucwords($tel)?> , <span class="bold">" Ville " </span> = <?=ucwords($ville)?></p>

               <!-- TABLEAU CLIENT --> 
                <table id="table" class="table">
                    <thead class="thead-facture">
                        <tr>
                            <td>#</td>
                            <td>NOM</td>
                            <td>Phone</td>
                            <td>Ville</td>
                        </tr>
                    </thead>
                    <tbody class="tbody">

                      <?php foreach ($dclients as $dclient): ?>

                        <tr>
                            <td align='left'><?= ++$ligne?></td>

                            <td class="non_clt"><?=ucwords($dclient['NOM'])?></td>  

                            <td><?=$dclient['TEL']?></td>

                            <td><?=ucwords($dclient['VILLE'])?></td>

                        </tr>

                      <?php endforeach; ?>

                    </tbody>
                </table>              

                  <form method="post">

                    <button type="submit" class="btn btn-outline-secondary ml-2 mr-2" name="detail_client">Détails</button>                            
     
                    <button type="submit" class="btn btn-danger ml-2 mr-2" name="update_client">Insérer</button>                    

                  </form>                

            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>


<!-- FIN MODAL -->
  <?php
    unset($_SESSION['client_existe']);
    unset($_SESSION['NOM']);
    unset($_SESSION['nombre']);
    unset($_SESSION['query_detail']);

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

<?=sweet_alert_notification()?>

<?=template_footer()?>