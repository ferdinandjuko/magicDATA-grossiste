<?php
include 'functions.php';
session_start();

// VERIFICATION SI L'UTILISATEUR EXISTE TOUJOURS DANS LA BASE DE DONNÉE
verif_session();
# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
session_url();


$pdo = pdo_connect_mysql();
$msg = '';

   // Define variables and initialize with empty values
    $nom = $tel = $courriel = $pseudo = $pswd = $newpswd = $newpswdcof = $changecode = $changepseudo = $nonchangementpseudo=  "";
    $nom_err =  $tel_err = $courriel_err = $pseudo_err = $pswd_err = $newpswd_err = $newpswdcof_err = $code_err= "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";


    // DETECTER SI L'UTILISATEUR EST ADMINISTRATEUR OU MODERATEUR
    if (isset($_SESSION['user'])) {
        // Si l'utilsateur est administrateur
        if($_SESSION['user'] <> "modo") {

            // Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
            $stmt = $pdo->prepare("SELECT * FROM inscription WHERE ID=".$_SESSION['id']);
            $stmt->execute();
            // Fetch the records so we can display them in our template.
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        } 
        // Si l'utilisateur est autre que administrateur
        else {

            // Prepare the SQL statement and get records from our contacts table, LIMIT will determine the page
            $stmt = $pdo->prepare("SELECT * FROM moderateur WHERE ID_MODO = ".$_SESSION['id_modo']." AND ID = ".$_SESSION['id']);
            $stmt->execute();
            // Fetch the records so we can display them in our template.
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);            

        }
    }


    if (!empty($_POST)) {

        function data_input($data) {
              $data = trim($data);
              $data = stripslashes($data);
              $data = htmlspecialchars($data);
              return $data;
            }
        // SI l'utilsateur clic sur le bouton modifier information utilisateur
        if(isset($_POST['submit_info_utilisateur'])) {

               // VALIDATION Nom
                $input_nom = data_input($_POST["nom"]);
                if(empty($input_nom)){
                   $nom_err = "Veuillez insérer votre nom";
                } elseif (!preg_match("/^[0-9-a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ' ]*$/",$input_nom)) {
                   $nom_err = "Les caractères spéciaux ne sont pas acceptés";
                   # mettre en session le donné inséré
                   $_SESSION['nom'] = $input_nom;
                } else{   
                   $nom = $input_nom;
                   $nom = mb_strtolower($nom, 'UTF-8');
                   
                }

                // VALIDATION TELEPHONE
                $input_tel = data_input($_POST["tel"]);
                if(empty($input_tel)){
                    $tel_err = "Veuillez insérer votre numero";
                } elseif (!preg_match("/^[0+1+2+3+4+5+6+7+8+9+ ]*$/",$input_tel)) {
                   $tel_err = "Seuls les chiffres sont acceptés"; 
                   # mettre en session le donné inséré
                   $_SESSION['tel'] = $input_tel;

                }else{
                    $tel = $input_tel;
                }  

                // VALIDATION mail

                // Si l'utilsateur est administrateur
                if($_SESSION['user'] <> "modo") {        
                    $input_courriel = data_input($_POST["courriel"]);
                    if(empty($input_courriel)){
                        $courriel = "";
                    } elseif(!filter_var($input_courriel, FILTER_VALIDATE_EMAIL)) {
                        $courriel_err = "Votre mail est invalide";
                        # mettre en session le donné inséré
                        $_SESSION['courriel'] = $input_courriel;                        
                    } else{
                        $courriel = $input_courriel;
                        $courriel = mb_strtolower($courriel, 'UTF-8');
                    }
                }
        }

        // SI l'utilsateur clic sur le bouton modifier compte (mot de passe ou pseudo) utilisateur
        elseif(isset($_POST['submit_compte_utilisateur'])) { 


               // Validate PSEUDO
                $input_pseudo = data_input($_POST["pseudo"]);
                if(empty($input_pseudo)){
                   $pseudo_err = "Veuillez insérer votre pseudo";
                } elseif (!preg_match("/^[0-9-a-zA-Z ]*$/",$input_pseudo)) {
                   $pseudo_err = "Les caractères spéciaux ne sont pas acceptés";
                    # mettre en session le donné inséré
                    $_SESSION['pseudo'] = $input_pseudo;                     
                } else{
                   $pseudo = strtolower($input_pseudo);
                }

                // Detecter si le pseudo est déjà utilisé

                if ($_SESSION['user'] <> "modo") {
                    // Detecter si le pseudo est déjà utilisé dans le table incription
                    $query_inscription = $pdo->prepare("SELECT * FROM inscription WHERE PSEUDO = '".$pseudo."' AND ID <> ".$utilisateur['ID']);
                    $query_inscription->execute();
                    $query_inscription->fetch(PDO::FETCH_BOTH);

                   // Detecter si le pseudo est déjà utilisé dans le table moderateur
                    $query_moderateur = $pdo->prepare("SELECT * FROM moderateur WHERE PSEUDO ='".$pseudo."'");
                    $query_moderateur->execute();
                    $query_moderateur->fetch(PDO::FETCH_BOTH);                       


                } else {
                    // Detecter si le pseudo est déjà utilisé dans le table moderateur
                    $query_moderateur = $pdo->prepare("SELECT * FROM moderateur WHERE PSEUDO ='".$pseudo."' AND ID_MODO <> ".$utilisateur['ID_MODO']);
                    $query_moderateur->execute();
                    $query_moderateur->fetch(PDO::FETCH_BOTH);  

                    // Detecter si le pseudo est déjà utilisé dans le table incription
                    $query_inscription = $pdo->prepare("SELECT * FROM inscription WHERE PSEUDO = '".$pseudo."'");
                    $query_inscription->execute();
                    $query_inscription->fetch(PDO::FETCH_BOTH);                                     

                }
       

                if ($query_inscription->rowCount() > 0 || $query_moderateur->rowCount() > 0) {
                    # code...
                    $pseudo_err = "Ce pseudo est déjà utilisé";
                    # mettre en session le donné inséré
                    $_SESSION['pseudo'] = $input_pseudo;                     
                } 

                # ACTIVE LE VARIABLE $changepseudo SI LA MODIFICATION DE PSEUDO EST VALIDE 
                if (empty($pseudo_err) && ($utilisateur['PSEUDO'] <> $pseudo)  ) {
                    $changepseudo = "ok";
                } elseif (empty($pseudo_err) && ($utilisateur['PSEUDO'] == $pseudo)) {
                    $nonchangementpseudo= "ok";
                }                    

                // Validate PSWD
                $input_pswd = data_input($_POST["pswd"]);

                // Validate NEW PSWD
                $input_newpswd = data_input($_POST["newpswd"]);
                // Validate CONFIRMATION PSWD
                $input_newpswdcof = data_input($_POST["newpswdcof"]);                

                if(empty($input_pswd) && empty($input_newpswd) && empty($input_newpswdcof)){
                    $code_err = "vide";
                }                     
                if(!empty($input_pswd) && (empty($input_newpswd) || empty($input_newpswdcof))){
                    $newpswd_err = "Confirmer votre mot de passe";
                    # mettre en session le donné inséré
                    $_SESSION['pswd'] = $input_pswd;              
                }                      
                if(empty($input_pswd) && !empty($input_newpswd) && !empty($input_newpswdcof)){
                    $pswd_err = "Insérez votre mot de passe";
                    # mettre en session le donné inséré
                    $_SESSION['newpswd'] = $input_newpswd; 
                    $_SESSION['newpswdcof'] = $input_newpswdcof;                        
                }
                if(!empty($input_pswd) && !empty($input_newpswd) && !empty($input_newpswdcof) && $utilisateur['PSWD'] <> $input_pswd && (($input_newpswd == $input_newpswdcof) ||($input_newpswd <> $input_newpswdcof) )   ) {
                    $pswd_err = "Mots de passe incorrect!";  
                    # mettre en session le donné inséré
                    $_SESSION['pswd'] = $input_pswd;
                    $_SESSION['newpswdcof'] = $input_newpswdcof;
                    $_SESSION['newpswd'] = $input_newpswd;                                                 
                }
                if(!empty($input_pswd) && !empty($input_newpswd) && !empty($input_newpswdcof) && $input_newpswd <> $input_newpswdcof ) {
                    $newpswdcof_err = "Vos mots de passe ne sont pas identique";  
                    # mettre en session le donné inséré
                    $_SESSION['pswd'] = $input_pswd;
                    $_SESSION['newpswdcof'] = $input_newpswdcof;
                    $_SESSION['newpswd'] = $input_newpswd;                                                   
                }

                if(!empty($input_pswd) && !empty($input_newpswd) && !empty($input_newpswdcof) && $utilisateur['PSWD'] == $input_pswd && $input_newpswd == $input_newpswdcof ) {
                    $newpswd = $input_newpswd;
                    # ACTIVE LE VARIABLE $changecode SI LA MODIFICATION DE PSEUDO EST VALIDE 
                    $changecode = "ok";

                }

 


            }

        // MODIFICATION DANS LE BASE DE DONNÉE SI AUCUNE ERREUR
        if(empty($nom_err) && empty($tel_err) && empty($courriel_err) && empty($pseudo_err) && empty($pswd_err) && empty($newpswd_err)  && empty($newpswdcof_err)) {

            // 1 ) SI l'utilsateur clic sur le bouton modifier information utilisateur
            if(isset($_POST['submit_info_utilisateur'])) {
                // Si l'utilsateur est administrateur
                if($_SESSION['user'] <> "modo" ) {
                    $sql="UPDATE inscription SET NOM = :NOM, TEL = :TEL, COURRIEL = :COURRIEL WHERE ID = "."'".$_SESSION['id']."'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':NOM', $nom);
                    $stmt->bindParam(':TEL', $tel);
                    $stmt->bindParam(':COURRIEL', $courriel);
                    // Update the record
                    $ex_update = $stmt->execute();
                }
                // Si l'utilsateur est moderateur
                else {
                    
                    $sql="UPDATE moderateur SET NOM_COMPLET = :NOM, TEL = :TEL  WHERE ID_MODO = ".$_SESSION['id_modo']." AND ID = ".$_SESSION['id'];
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':NOM', $nom);
                    $stmt->bindParam(':TEL', $tel);
                    // Update the record
                    $ex_update = $stmt->execute();

                }
            }

            // 2) SI l'utilsateur clic sur le bouton modifier compte (mot de passe et pseudo) utilisateur
            elseif(isset($_POST['submit_compte_utilisateur']) ) {
                // Si l'utilsateur est administrateur
                if($_SESSION['user'] <> "modo" ) {

                    if (!empty($changecode) && empty($changepseudo)) {
                        // SQL SI CHANGEMENT MOT DE PASSE SEULEMENT
                        $sql="UPDATE inscription SET PSWD =:PSWD WHERE ID ="."'".$_SESSION['id']."'";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':PSWD', $newpswd); 
                        // Update the record
                        $ex_update = $stmt->execute();                                               
                    }elseif(!empty($changepseudo) && empty($changecode)) {
                        // SQL SI CHANGEMENT PSEUDO SEULEMENT
                        $sql="UPDATE inscription SET PSEUDO =:PSEUDO WHERE ID ="."'".$_SESSION['id']."'";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':PSEUDO', $pseudo);
                        // Update the record
                        $ex_update = $stmt->execute();                                               
                    } elseif(!empty($changepseudo) && !empty($changecode)) {
                        // SQL SI CHANGEMENT PSEUDO et MOT DE PASSE
                        $sql="UPDATE inscription SET PSWD =:PSWD, PSEUDO =:PSEUDO WHERE ID ="."'".$_SESSION['id']."'";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':PSEUDO', $pseudo);
                        $stmt->bindParam(':PSWD', $newpswd);
                        // Update the record
                        $ex_update = $stmt->execute();
                    } elseif (!empty($nonchangementpseudo)) {
                        // SQL SI CHANGEMENT PSEUDO SEULEMENT
                        $sql="UPDATE inscription SET PSEUDO =:PSEUDO WHERE ID ="."'".$_SESSION['id']."'";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':PSEUDO', $pseudo);
                        // Update the record
                        $ex_update = $stmt->execute();                        
                    }

                }
                // Si l'utilsateur est moderateur
                else {

                    if (!empty($changecode) && empty($changepseudo)) {
                        // SQL SI CHANGEMENT MOT DE PASSE SEULEMENT
                        $sql="UPDATE moderateur  SET PSWD =:PSWD WHERE ID_MODO =".$_SESSION['id_modo']." AND ID = ".$_SESSION['id'];
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':PSWD', $pswd);
                        // Update the record
                        $ex_update = $stmt->execute();                         
                    }elseif(!empty($changepseudo) && empty($changecode)) {
                        // SQL SI CHANGEMENT PSEUDO SEULEMENT
                        $sql="UPDATE moderateur  SET PSEUDO =:PSEUDO WHERE ID_MODO =".$_SESSION['id_modo']." AND ID = ".$_SESSION['id'];
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':PSEUDO', $pseudo);
                        // Update the record
                        $ex_update = $stmt->execute();                          
                    } elseif(!empty($changepseudo) && !empty($changecode)) {
                        // SQL SI CHANGEMENT PSEUDO et MOT DE PASSE
                        $sql="UPDATE moderateur  SET PSWD =:PSWD, PSEUDO =:PSEUDO WHERE ID_MODO =".$_SESSION['id_modo']." AND ID = ".$_SESSION['id'];
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':PSEUDO', $pseudo);
                        $stmt->bindParam(':PSWD', $pswd);
                        // Update the record
                        $ex_update = $stmt->execute();                        
                    } elseif (!empty($nonchangementpseudo)) {
                        // SQL SI CHANGEMENT PSEUDO SEULEMENT
                        $sql="UPDATE moderateur  SET PSEUDO =:PSEUDO WHERE ID_MODO =".$_SESSION['id_modo']." AND ID = ".$_SESSION['id'];
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':PSEUDO', $pseudo);
                        // Update the record
                        $ex_update = $stmt->execute();                        
                    }

                }
            }

        }        
           // EXECUTION ALERT 

                // EXECUTION ALERT POUR MODIFICARTION INFORMATION
                if(isset($ex_update) && isset($_POST['submit_info_utilisateur'])) {

                    if ($_SESSION['user'] <> "modo" ) {

                        if ( ($utilisateur['NOM'] <> $nom) || ($utilisateur['TEL'] <> $tel) || ($utilisateur['COURRIEL'] <> $courriel) ) {
                            
                                // code...
                                $_SESSION['name'] = $nom;
                                # ALERT SI MODIFICATION A ETE APPORTEE
                                $_SESSION['alert_icon'] = "success";
                                $_SESSION['alert_title'] = "Mis à jour information terminée";
                                $_SESSION['alert_text'] = "Votre information a été modifier avec succès!"; 
                                // Records created successfully. Redirect to landing page;
                                header("location: parametre.php");
                                exit();
                        // Si l'utilsateur est moderateur
                        } else  {

                            # ALERT SI AUCUNE MODIFICATION
                            $_SESSION['alert_icon'] = "info";
                            $_SESSION['alert_title'] = "Aucune modification a été apportée";
                            $_SESSION['alert_text'] = "Votre information n\' a pas été modifiée!";
                            // Records created successfully. Redirect to landing page;
                            header("location: parametre.php");
                            exit();
                        }
                    } else {

                        if ( ($utilisateur['NOM_COMPLET'] <> $nom) || ($utilisateur['TEL'] <> $tel) ) {
                            
                                // code...
                                $_SESSION['name'] = $nom;
                                # ALERT SI MODIFICATION A ETE APPORTEE
                                $_SESSION['alert_icon'] = "success";
                                $_SESSION['alert_title'] = "Mis à jour information terminée";
                                $_SESSION['alert_text'] = "Votre information a été modifier avec succès!"; 
                                // Records created successfully. Redirect to landing page;
                                header("location: parametre.php");
                                exit();

                        } else  {

                            # ALERT SI AUCUNE MODIFICATION
                            $_SESSION['alert_icon'] = "info";
                            $_SESSION['alert_title'] = "Aucune modification a été apportée";
                            $_SESSION['alert_text'] = "Votre information n\' a pas été modifiée!";
                            // Records created successfully. Redirect to landing page;
                            header("location: parametre.php");
                            exit();
                        }                        
                    }
                    
                // EXECUTION ALERT POUR MODIFICARTION COMPTE (MDP et PSEUDO)
                } elseif( isset($ex_update) && isset($_POST['submit_compte_utilisateur']) ) {

                        if ( !empty($changepseudo) || !empty($changecode) ) {

                            # ALERT SI MODIFICATION A ETE APPORTEE
                            $_SESSION['alert_icon'] = "success";
                            $_SESSION['alert_title'] = "Mis à jour information terminée";
                            $_SESSION['alert_text'] = "Votre compte a été modifier avec succès!"; 
                            // Records created successfully. Redirect to landing page;
                            header("location: parametre.php");
                            exit();
                        } else  {

                            # ALERT SI AUCUNE MODIFICATION
                            $_SESSION['alert_icon'] = "info";
                            $_SESSION['alert_title'] = "Aucune modification a été apportée";
                            $_SESSION['alert_text'] = "Votre compte n\' a pas été modifiée!";
                            // Records created successfully. Redirect to landing page;
                            header("location: parametre.php");
                            exit();
                        }

                } elseif(!isset($ex_update) && empty($code_err) && empty($nom_err)  && empty($tel_err) && empty($courriel_err) && empty($code_err) && empty($pseudo_err) && empty($pswd_err) && empty($newpswd_err) && empty($newpswdcof_err) ) {

                        $_SESSION['alert_icon'] = "error";
                        $_SESSION['alert_title'] = "Erreur modification";
                        $_SESSION['alert_text'] = "Une erreur a été trouvée. Veuillez vous connecter à nouveau!";
                    }


        
    }


?>


<?=template_header('Paramètre')?>

<script>


</script>


<?=template_content('tableau de bord')?>

       <!-- CONTENU -->
        <div id="container-page" class="container">
    <!-- FIN FONCTION -->  
            <!-- navigation --> 
            <div id="navigation" class="row">
                <h5><a href="index.php"><i class='fas fa-home'></i></a> / Paramètre</h5>
                <div class="navigation-retour-active">
                  <a href="read_caisse.php"><i class="fas fa-chevron-circle-left"></i></a>
                </div>
                <div class="navigation-retour-non-active">
                  <a href=""><i class="fas fa-chevron-circle-right"></i></a>
                </div>                 
            </div>
                <!-- Fin navigation -->
 

        <div id="cadre-contenu">
            <div class="title-cadre">
                <h2><i class="fas fa-cog"></i> CONFIGURATION </h2>
            </div>
            <div class="container">
                    <div class="row">
                                             
                            <div class="col-md-6">
                                <form class ="form-conf" action="" method="post"> 
                                    <h4 class="d-flex justify-content-start text-secondary mb-3 mt-3"><i class="fas fa-user-cog mr-2"></i> INFORMATION </h4>
                                    <!-- Si l'utilisateur est admin  -->
                                    <?php if($_SESSION['user'] <> "modo") : ?>
                                    <div class="form-group">
                                        <label for="nom"> Nom complet </label> 
                                                             
                                        <input class="form-control" type="text" name="nom" value="<?php if(isset($_SESSION['nom'])) {echo $_SESSION['nom']; } else {echo ucwords($utilisateur['NOM']);}  ?>" oninput="controlInputNOM();" id="nom">
                                        <?php if(!empty($nom_err)) : ?>
                                        <p id="erreur-nom" class="erreur-p"><?= $icon_type ." ". $nom_err?></p>
                                        <?php endif; ?>                                        
                                    </div>
                                    <!-- Si l'utilisateur est moderateur  -->
                                    <?php else: ?>
                                    <div class="form-group">
                                        <label for="nom"> Nom complet </label> 
                                                             
                                        <input class="form-control" type="text" name="nom" value="<?php if(isset($_SESSION['nom'])) {echo $_SESSION['nom']; } else {echo ucwords($utilisateur['NOM_COMPLET']);} ?>" oninput="controlInputNOM();" id="nom">
                                        <?php if(!empty($nom_err)) : ?>
                                        <p id="erreur-nom" class="erreur-p"><?= $icon_type ." ". $nom_err?></p>
                                        <?php endif; ?>                                       
                                    </div>                                
                                    <?php endif; ?> 
                                    <div class="form-group">
                                        <label for="tel"> Téléphone </label>
                                          
                                        <input class="form-control" type="text" name="tel" value="<?php if(isset($_SESSION['tel'])) {echo $_SESSION['tel']; } else {echo ucwords($utilisateur['TEL']);} ?>" oninput="controlInputTEL();" id="tel">
                                        <?php if(!empty($tel_err)) : ?>
                                        <p id="erreur-tel" class="erreur-p"><?= $icon_type ." ". $tel_err?></p>
                                        <?php endif; ?>                                     
                                    </div>
                                    <!-- Si l'utilisateur est admin  -->
                                    <?php if($_SESSION['user'] <> "modo") : ?>
                                    <div class="form-group">
                                        <label for="courriel"> Courriel </label>
                                          
                                        <input class="form-control" type="text" name="courriel" id="courriel" value="<?php if(isset($_SESSION['courriel'])) {echo $_SESSION['courriel']; } else {echo $utilisateur['COURRIEL'];} ?>" oninput="controlInputCOURRIEL();">
                                        <?php if(!empty($courriel_err)) : ?>
                                        <p id="erreur-courriel" class="erreur-p-fin"><?=$icon_type ." ". $courriel_err?></p>
                                        <?php endif; ?>                              
                                    </div>
                                    <?php endif; ?>  
                                    <div id="update-btn-conf" class="form-group">
                                      <input class="btn btn-primary" type="submit" name="submit_info_utilisateur" value="MODIFIER">  
                                    </div>                                                                
                                </form> 
                            </div>
                        
                            <div class="col-md-6"> 
                               <form class ="form-conf" action="" method="post">
                                    <h4 class="d-flex justify-content-start text-secondary mb-3 mt-3"><i class="fas fa-user mr-2"></i> COMPTE </h4>  
                                    <div class="form-group">
                                        <label for="pseudo"> Pseudo </label>
                                          
                                        <input class="form-control" type="text" name="pseudo" value="<?php if(isset($_SESSION['pseudo'])) {echo $_SESSION['pseudo']; } else {echo $utilisateur['PSEUDO'];} ?>" oninput="controlInputPSEUDO();" id="pseudo">
                                        <?php if(!empty($pseudo_err)) : ?>
                                        <p id="erreur-pseudo" class="erreur-p"><?= $icon_type ." ". $pseudo_err?></p>
                                        <?php endif; ?>                                    
                                    </div>
                                    <div class="form-group">
                                        <label for="pswd"> Mot de passe actuel </label>
                                        <div class="d-flex">
                                            <input class="form-control" type="password" name="pswd" value="<?php if(isset($_SESSION['pswd'])) {echo $_SESSION['pswd']; } ?>" oninput="controlInputPSWD();" id="pswd">
                                            <i id="show_pswd" onclick="Toggle1()" class="fas fa-eye"></i>
                                            
                                        </div>
                                        <?php if(!empty($pswd_err)) : ?>
                                        <p id="erreur-pswd" class="erreur-p "><?= $icon_type ." ". $pswd_err?></p>
                                        <?php endif; ?>                                    
                                    </div>                                
                                    <div class="form-group"> 
                                        <label for="pswdcof">Nouveau et Confirmation mot de passe </label>
                                        <div class="d-flex">

                                            <input class="form-control" type="password" name="newpswd" value="<?php if(isset($_SESSION['newpswd'])) {echo $_SESSION['newpswd']; } ?>" id="newpswd" oninput="controlInputNEWPSWD();">

                                            <input class="form-control" type="password" name="newpswdcof" value="<?php if(isset($_SESSION['newpswdcof'])) {echo $_SESSION['newpswdcof']; } ?>" id="newpswdcof" oninput="controlInputNEWPSWD();">
                                            <i id="show_newpswd" onclick="Toggle2()" class="fas fa-eye"></i>
                                        </div>
                                            <?php if(!empty($newpswd_err)) : ?>
                                            <p id="erreur-newpswd" class="erreur-p-fin"><?=$icon_type ." ". $newpswd_err?></p>
                                            <?php endif; ?> 
                                            <?php if(!empty($newpswdcof_err)) : ?>
                                            <p id="erreur-newpswdcof" class="erreur-p-fin"><?=$icon_type ." ". $newpswdcof_err?></p>
                                            <?php endif; ?> 
                              
                                    </div>
                                    
                                    <div id="update-btn-conf" class="form-group ">
                                      <input class="btn btn-primary" type="submit" name="submit_compte_utilisateur" value="MODIFIER">  
                                    </div> 
                                </form>                                
                            </div>                        
                         
                    </div>             
                </div>

        </div>
<?php

    unset($_SESSION['nom']);
    unset($_SESSION['tel']);
    unset($_SESSION['courriel']);
    unset($_SESSION['pseudo']);
    unset($_SESSION['pswd']);
    unset($_SESSION['newpswd']);
    unset($_SESSION['newpswdcof']);

 ?>


 <!-- /////////// SWEET ALERT NOTIFICATION ////////// -->

 <?php if (isset($_SESSION['alert_text']) && $_SESSION['alert_text']!='') : ?>
    <script>
      Swal.fire({
          icon: '<?=$_SESSION['alert_icon']?>',
          title: '<?=$_SESSION['alert_title']?>',
          text: '<?=$_SESSION['alert_text']?>',
          timer: 9000,
        })
    </script>
    <?php 
      unset($_SESSION['alert_icon']);
      unset($_SESSION['alert_title']);
      unset($_SESSION['alert_text']);
     ?>
  <?php endif; ?> 


<?=template_footer()?>
