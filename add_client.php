<?php
 
include 'functions.php';
session_start();
$pdo = pdo_connect_mysql();
$msg = '';

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();


   // Define variables and initialize with empty values
    $nom = $tel = $mail = $adresse = $ville = $pays = $id_resp= $ajoute_par= $selectstatut= $rcs = $nif = $stat = $personnel_active = $professionnel_active = "";
    $nom_err =  $tel_err = $mail_err = $adresse_err = $ville_err = $pays_err = $selectstatut_err = $rcs_err = $nif_err = $stat_err = "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";

    $ligne = 0;


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
            $selectstatut_err = "Veuillez selectionner la statut de votre client";             
        } else {
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
                    $rcs_err = "Veuillez insérer le numero RCS";
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
                    $nif_err = "Veuillez insérer le Numéro d'immatriculation fiscale";
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
                    $stat_err = "Veuillez insérer le STAT";
                } elseif(!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_stat)) {
                    $stat_err = "STAT invalide";
                    # mettre en session le donné inséré en cas d'erreur
                    $_SESSION['stat'] = $input_stat;                    
                } else{
                    $stat = $input_stat;
                    # conversion en lettre minuscul utf-8 de donné inséré
                    $stat = mb_strtolower($stat, 'UTF-8');
                    # mettre en session le donné inséré pour l'inserer en cas de doublon
                    $_SESSION['stat'] = $stat;                   
                }

            // METTRE EN SESSION LE SELECT STATU EN CAS D'ERREUR INPUT RCS, NIF et STAT
            if ($selectstatut=="professionnel") {
                # mettre en session le donné inséré en cas d'erreur
                $_SESSION['selectstatut'] = "professionnel";
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

    $query_doublon = "SELECT * FROM client WHERE ID=".$_SESSION['id']." AND NOM=:NOM";
    $stmt = $pdo->prepare($query_doublon);
    $stmt->bindParam(':NOM', $nom);
    $stmt->execute();
    $dclients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $nombre = $stmt->rowCount();

    if($nombre <> 0 AND (empty($nom_err) && empty($adresse_err) && empty($ville_err) && empty($pays_err) && empty($tel_err) && empty($mail_err) && empty($selectstatut_err) && empty($rcs_err) && empty($stat_err) && empty($nif_err))) {

        $_SESSION['client_existe'] = "yes";
        $_SESSION['nom'] = $nom;
        $_SESSION['nombre'] = $nombre;

    } else {
        unset($_SESSION['client_existe']);
    }

       
        // Check input errors before inserting in database
        if(empty($nom_err) && empty($adresse_err) && empty($ville_err) && empty($pays_err) && empty($tel_err) && empty($mail_err) && empty($selectstatut_err) && empty($rcs_err) && empty($stat_err) && empty($nif_err) && !isset($_SESSION['client_existe']) ) {

                $ajoute_par = $_SESSION['name'];
                $id_resp = $_SESSION['id'];
                $date_ajout = date("Y-m-d");
                // prepare sql and bind parameters
                $sql="INSERT INTO client (ID, NOM, TEL, COURRIEL, ADRESSE, VILLE, PAYS, STATUT, RCS, NIF, STAT, DATE_AJOUT, AJOUTE_PAR)
                VALUES (:ID, :NOM, :TEL, :COURRIEL, :ADRESSE, :VILLE, :PAYS, :STATUT, :RCS, :NIF, :STAT, :DATE_AJOUT, :AJOUTE_PAR)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':ID', $id_resp);
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
                $stmt->bindParam(':DATE_AJOUT', $date_ajout); 
                $stmt->bindParam(':AJOUTE_PAR', $ajoute_par);                

                // insert a row
                $ex= $stmt->execute();

                // Attempt to execute the prepared statement
                if($ex <> false){
                    // Records created successfully. Redirect to landing page
                    $_SESSION['alert_icon'] = "success";
                    $_SESSION['alert_title'] = "Création nouveau client terminée";
                    $_SESSION['alert_text'] = "Votre client à été ajouté avec succès!";
                    header("location: read_client.php");
                    exit();
                } else{
                    $_SESSION['alert_icon'] = "error";
                    $_SESSION['alert_title'] = "Création nouveau client échouée";
                    $_SESSION['alert_text'] = "Un problème est survenu. Veuillez réessayer plus tard ou veuillez vous reconnecter";
                    header("location: update_client.php");
                    exit();                    
                }
            }

            // Close statement
            $conn = null;
        }
        // SI LE DOUBLON EXISTE MAIS ON INSERE QUAND MEME LE CLIENT
        elseif (isset($_POST['add_client'])) {
                #définition de variable par le session sauvegardé
                $nom = $_SESSION['nom'];
                $tel = $_SESSION['tel'];
                $ville = $_SESSION['ville'];
                $pays = $_SESSION['pays'];
                if (isset($_SESSION['adresse'])) {
                    // Si le adresse est definie
                    $adresse = $_SESSION['adresse'];
                }else {
                    $adresse = "";
                }
                if (isset($_SESSION['mail'])) {
                    // Si le mail est définie
                    $mail = $_SESSION['mail'];
                }else {
                    $mail = "";
                }
                
                $selectstatut = $_SESSION['selectstatut'];

                if ($selectstatut == "professionnel") {
                    // Si le select statut est professionnel
                    $rcs = $_SESSION['rcs'];
                    $nif = $_SESSION['nif'];
                    $stat = $_SESSION['stat'];
                } else {
                    $rcs = "";
                    $nif = "";
                    $stat = "";                     
                }
            

                $ajoute_par = $_SESSION['name'];
                $id_resp = $_SESSION['id'];
                $date_ajout = date("Y-m-d");
                // prepare sql and bind parameters
                $sql="INSERT INTO client (ID, NOM, TEL, COURRIEL, ADRESSE, VILLE, PAYS, STATUT, RCS, NIF, STAT, DATE_AJOUT, AJOUTE_PAR)
                VALUES (:ID, :NOM, :TEL, :COURRIEL, :ADRESSE, :VILLE, :PAYS, :STATUT, :RCS, :NIF, :STAT, :DATE_AJOUT, :AJOUTE_PAR)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':ID', $id_resp);
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
                $stmt->bindParam(':DATE_AJOUT', $date_ajout); 
                $stmt->bindParam(':AJOUTE_PAR', $ajoute_par);                


                // insert a row
                $ex= $stmt->execute();

                // Attempt to execute the prepared statement
                if($ex <> false){
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
                    // Records created successfully. Redirect to landing page
                    $_SESSION['alert_icon'] = "success";
                    $_SESSION['alert_title'] = "Création nouveau client terminée";
                    $_SESSION['alert_text'] = "Votre client à été ajouté avec succès!";
                    header("location: read_client.php");
                    exit();
                } else{
                    $_SESSION['alert_icon'] = "error";
                    $_SESSION['alert_title'] = "Création nouveau client échouée";
                    $_SESSION['alert_text'] = "Un problème est survenu. Veuillez réessayer plus tard ou veuillez vous reconnecter";
                    header("location: add_client.php");
                    exit();                    
                }

           } 
           // VOIR DETAIL DOUBLON
           elseif (isset($_POST['detail_client'])) {

                  $nom =($_SESSION['nom']);
                  $_SESSION['query_detail'] = "SELECT * FROM client WHERE ID=".$_SESSION['id']." AND NOM='".$nom."'";
                    unset($_SESSION['client_existe']);
                    unset($_SESSION['adresse']);
                    unset($_SESSION['ville']);
                    unset($_SESSION['pays']);
                    unset($_SESSION['tel']);
                    unset($_SESSION['mail']);
                    unset($_SESSION['selectstatut']);
                    unset($_SESSION['rcs']);
                    unset($_SESSION['nif']);
                    unset($_SESSION['stat']);
                    header("location: read_client.php");
                    exit();                  
              }   
                // Close statement
            $conn = null;
    
    }
catch(PDOException $e)
    {
    echo $sql . "<br>" . $e->getMessage();
    }    

?>

<?=template_header('Création de client')?>
    
<?=template_content('client')?>
  
        <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Clients / Création nouveau client</h5>
                <div class="navigation-retour-active">
                  <a href="read_client.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                
            </div>
                <!-- Fin navigation --> 
            <div id="cadre-contenu">
                <div class="cadre-title">
                    <h2><i class="fas fa-plus-circle"></i> AJOUTER UN CLIENT</h2>
                </div>
                <div class="container">
                    <form name="myForm" id="form-add" action="add_client.php" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input class="form-control" type="text" name="nom" placeholder="CLIENT *" value="<?php if(isset($_SESSION['nom'])) {echo ucwords($_SESSION['nom']);}?>" oninput="controlInputNOM();" id="nom">
                                    <?php if(!empty($nom_err)) : ?>
                                    <p id="erreur-nom" class="erreur-p"><?= $icon_type ." ". $nom_err?></p>
                                    <?php endif; ?>                                       
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="text" name="tel" placeholder="TELEPHONE *" value="<?php if(isset($_SESSION['tel'])) {echo $_SESSION['tel'];}?>" oninput="controlInputTEL();" id="tel">
                                    <?php if(!empty($tel_err)) : ?>
                                    <p id="erreur-tel" class="erreur-p"><?= $icon_type ." ". $tel_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="text" name="ville" placeholder="VILLE *" value="<?php if(isset($_SESSION['ville'])) {echo ucwords($_SESSION['ville']);}?>" oninput="controlInputVILLE();" id="ville">
                                    <?php if(!empty($ville_err)) : ?>
                                    <p id="erreur-ville" class="erreur-p"><?= $icon_type ." ". $ville_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                                <div class="form-groupe">
                                    <input class="form-control" type="text" name="pays" placeholder="PAYS *"
                                    value="<?php if(isset($_SESSION['pays'])) {echo $_SESSION['pays'];}?>" oninput="controlInputPAYS();" id="pays"> 
                                    <?php if(!empty($pays_err)) : ?>
                                    <p id="erreur-pays" class="erreur-p"><?= $icon_type ." ". $pays_err?></p>
                                    <?php endif; ?>                                    
                                </div>                                
                                <div class="form-group">
                                    <input class="form-control" type="text" name="mail" placeholder="COURRIEL" id="courriel" value="<?php if(isset($_SESSION['mail'])) {echo $_SESSION['mail'];}?>" oninput="controlInputCOURRIEL();">
                                    <?php if(!empty($mail_err)) : ?>
                                    <p id="erreur-courriel" class="erreur-p"><?= $icon_type ." ". $mail_err?></p>
                                    <?php endif; ?>                                    
                                </div>
                                <div class="form-group"> 
                                    <input class="form-control" type="text" name="adresse" value="<?php if(isset($_SESSION['adresse'])) {echo ucwords($_SESSION['adresse']);}?>" placeholder="ADRESSE" id="adresse">
                                    <?php if(!empty($adresse_err)) : ?>
                                    <p class="erreur-p"><?= $icon_type ." ". $adresse_err?></p>
                                    <?php endif; ?>                                    
                                </div>

                            </div>
                            <div class="col-md-6">
                                
                            <?php if( $selectstatut=="professionnel"  ) : ?>
                                <div class="form-group">                                
                                  <select class="custom-select mr-sm-2"  id="SelectStatu" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="selectstatut">
                                    <option value="professionnel">PROFESSIONNEL</option>
                                    <option value="vide">---CHOISIR LE STATUT DU CLIENT--- *</option>
                                    <option value="personnel">PERSONNEL</option>
                                  </select>
                                    <?php if(!empty($selectstatut_err)) : ?>
                                    <p id="erreur-select" class="erreur-p"><?= $icon_type ." ". $selectstatut_err?></p>
                                    <?php endif; ?>                                  
                                </div> 
                            <?php elseif( $selectstatut=="personnel"  ) : ?>
                                <div class="form-group">                                
                                  <select class="custom-select mr-sm-2"  id="SelectStatu" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="selectstatut">
                                    <option value="personnel">PERSONNEL</option>
                                    <option value="professionnel">PROFESSIONNEL</option>
                                    <option value="vide">---CHOISIR LE STATUT DU CLIENT--- *</option>
                                  </select>
                                    <?php if(!empty($selectstatut_err)) : ?>
                                    <p id="erreur-select" class="erreur-p"><?= $icon_type ." ". $selectstatut_err?></p>
                                    <?php endif; ?>                                  
                                </div>                               
                            <?php else : ?> 
                                <div class="form-group">                                
                                  <select class="custom-select mr-sm-2"  id="SelectStatu" onchange="getSelectValue();" oninput="controlInputSTATUT();" name="selectstatut">
                                    <option value="vide">---CHOISIR LE STATUT DU CLIENT--- *</option>
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
                                <div class="form-group">
                                    <input class="form-control" type="text" name="nif" placeholder="NIF *" value="<?php if(isset($_SESSION['nif'])) {echo $_SESSION['nif'];}?>" oninput="controlInputNIF();" id="nif">
                                    <p id="erreur-nif"><?= $icon_type ." ". $nif_err?></p>
                                </div>
                            <!-- NIF SI ERREUR VIDE -->
                            <?php else : ?> 
                                <div class="form-group"> 
                                    <input class="form-control" type="text" name="nif" placeholder="NIF *" value="<?php if(isset($_SESSION['nif'])) {echo $_SESSION['nif'];}?>" id="nif">                                  
                                </div>                                
                            <?php endif; ?>                                  

                            <!-- RCS -->
                            <!-- RCS SI ERREUR -->
                            <?php if(!empty($rcs_err)) : ?> 
                                <div class="form-group">
                                    <input class="form-control" type="text" name="rcs" placeholder="RCS *" value="<?php if(isset($_SESSION['rcs'])) {echo $_SESSION['rcs'];}?>" oninput="controlInputRCS();" id="rcs">
                                    <p id="erreur-rcs"><?= $icon_type ." ". $rcs_err?></p>
                                </div>
                            <!-- RCS SI ERREUR VIDE -->
                            <?php else : ?>    
                                <div class="form-group">
                                    <input class="form-control" type="text" name="rcs" placeholder="RCS *" value="<?php if(isset($_SESSION['rcs'])) {echo $_SESSION['rcs'];}?>" id="rcs">                                 
                                </div>                                 
                            <?php endif; ?> 

                            <!-- STAT -->
                            <!-- STAT SI ERREUR -->
                            <?php if(!empty($stat_err)) : ?> 
                                <div class="form-group">
                                    <input class="form-control" type="text" name="stat" placeholder="STAT *" value="<?php if(isset($_SESSION['stat'])) {echo $_SESSION['stat'];}?>" oninput="controlInputSTAT();" id="stat">
                                    <p id="erreur-stat"><?= $icon_type ." ". $stat_err?></p>
                                </div>
                            <!-- STAT SI ERREUR VIDE -->
                            <?php else : ?>  
                                <div class="form-group">
                                    <input class="form-control" type="text" name="stat" placeholder="STAT *" value="<?php if(isset($_SESSION['stat'])) {echo $_SESSION['stat'];}?>" id="stat">                               
                                </div>                                 
                            <?php endif; ?>    
                              
                                <div id="add-btn" class="form-group">
                                  <input class="btn btn-primary" type="submit" name="submit" value="+ AJOUTER">  
                                </div>                                 
                            </div> 
                        </div>   
                    </form>          
                </div>
                
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
     
                    <button type="submit" class="btn btn-danger ml-2 mr-2" name="add_client">Insérer</button>                    

                  </form>                

            </div>  
          </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- FIN MODAL -->

<?php


    unset($_SESSION['query_detail']);
    unset($_SESSION['nombre']);

 if (!isset($_SESSION['client_existe'])) {
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
 } else {
    unset($_SESSION['client_existe']);
 }
          
?>

<!--SWEET ALERT -->
<?=sweet_alert_notification()?>

<?=template_footer()?>
