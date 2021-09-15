<?php
include 'functions.php';
session_start();
$pdo = pdo_connect_mysql();
$msg = '';

# Si la session n'est pas définie, rediriger vers la page de connexion, si l'url est prédéfinie avant la connexion rediriger vers l'url prédéfinie après la connexion
//session_url();

    
// Check if POST data is not empty
   // Define variables and initialize with empty values
    $nom = $tel = $mail = $pseudo = $pswd = $pswdcof = "";
    $nom_err =  $tel_err = $mail_err = $pseudo_err = $pswd_err = $pswdcof_err = "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";

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
           $nom_err = "Veuillez insérer votre nom complet";
        } elseif (!preg_match("/^[a-zA-Z-' ]*$/",$input_nom)) {
           $nom_err = "Seulement les lettres et les espaces sont acceptés"; 
        } else{
           $nom = $input_nom;
        }
           
        // Validate Pseudo
        $input_pseudo = data_input($_POST["pseudo"]);

        if(empty($input_pseudo)){
            $pseudo_err = "Veuillez insérer votre Pseudo";
        }elseif (!preg_match("/^[0-1-2-3-4-5-6-7-8-9-a-zA-Z-' ]*$/",$input_pseudo)) {
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
            $pseudo_err = "Attention! Ce pseudo est déjà utilisé";
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
            $tel_err = "Veuillez insérer votre numero téléphone";
        } elseif (!preg_match("/^[0+1+2+3+4+5+6+7+8+9+ ]*$/",$input_tel)) {
           $tel_err = "Seulement les chiffres sont acceptés";  // 1= erreur

        }else{
            $tel = $input_tel;
        }       
        // Validate mail
        $input_mail = data_input($_POST["mail"]);

        if(empty($input_mail)){
            $mail_err = "Inserer votre mail";
        } elseif(!filter_var($input_mail, FILTER_VALIDATE_EMAIL)) {
            $mail_err = "Votre mail est invalide";
        } else{
            $mail = $input_mail;
        }         
        // Check input errors before inserting in database
        if(empty($nom_err) && empty($pseudo_err) && empty($pswd_err) && empty($pswdcof_err) && empty($tel_err) && empty($mail_err)){
                $photo = "user.png";
                $date_inscription = date("Y-m-d");
                // prepare sql and bind parameters
                $sql="INSERT INTO inscription (NOM, TEL, COURRIEL, PSEUDO, PSWD, DATE_INSCRIPTION, PHOTO)
                VALUES (:NOM, :TEL, :COURRIEL, :PSEUDO, :PSWD, :DATE_INSCRIPTION, :PHOTO)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':NOM', $nom);
                $stmt->bindParam(':TEL', $tel);
                $stmt->bindParam(':COURRIEL', $mail);
                $stmt->bindParam(':PSEUDO', $pseudo);
                $stmt->bindParam(':PSWD', $pswd);
                $stmt->bindParam(':DATE_INSCRIPTION', $date_inscription);
                $stmt->bindParam(':PHOTO', $photo);

                // insert a row
                $ex= $stmt->execute();

                // recuperer l'id gestion stock
                $last_id = $pdo->lastInsertId();

                // Attempt to execute the prepared statement
                if($ex <> false){
                      $_SESSION['inscription'] = "true";
                      $_SESSION['username'] = $pseudo;
                      $_SESSION['user'] = "admin";
                      $_SESSION['id'] = $last_id;
                      $_SESSION['name'] = $nom;
                    // Records created successfully. Redirect to landing page
                    header("location: inscription.php");
                    exit();
                } else{

                      $_SESSION['alert_icon'] = "warning";
                      $_SESSION['alert_title'] = "L'inscription a échoué";
                      $_SESSION['alert_text'] = "Une erreur s'est produite, veuillez réessayer plus tard";                     
                }
            }

            // Close statement
            $conn = null;
        }
    
    }
catch(PDOException $e)
    {
    echo $sql . "<br>" . $e->getMessage();
    }    
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link href="bootstrap-4.5.3/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link rel="icon" type="image/PNG" href="favicon.png">  
    <link rel="stylesheet" href="style/all.min.css">
    <link rel="stylesheet" href="style/inscription.css"> 
    <script src="scripts/jquery-3.5.1.min.js" type="text/javascript"></script>   
    <script src="scripts/popper.min.js" type="text/javascript"></script>     
    <script src="scripts/bootstrap.min.js"></script>        
    <script src="scripts/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="scripts/sweetalert2/dist/polyfill.js"></script>
    <script src="scripts/all.min.js" type="text/javascript"></script>        
    <script src="scripts/main.js"></script>  
    <script>

    /* FOCUS SUR LE INPUT NOM APRES CHARGEMENT */ 
       $(document).ready(function() {
            setTimeout(function(){
                $("#nom").focus();
            }, 200);
        });
    /* REDIRIGER VERS INDEX QUAND INSCRIPTION AVEC SUCCES */  
       function RedirectionJavascript(){
          document.location.href="index.php"; 
        }

    </script>

<body>
    <div id="container" style="background-image: url(img/img3.jpg);" class="wrapper">
        <div class="recalibre row">
            <div class="tete-gauche col-lg-6">
                <img src="img/img2.png" id="img2">
                <div class="title"><span style="color: white;">ILO</span> <span style="color: #FCA331;">- GROSSISTE</span>
                </div>
            </div>
            <div class="tete-droite col-lg-6">
                <a href="#">FONCTIONNALITE <i class="fas fa-caret-down"></i></a>
                <a href="login.php">CONNEXION</a>
            </div>
        </div>
        <div class="recalibre2 row">
            <div class="gauche col-lg-6">
                <span class="sous-titre"><h1>. A PROPOS</h1></span>
                <h3>Lorem ipsum dolor sit amet consectetur, adipisicing, elit. Ex doloremque corrupti esse voluptate nostrum ratione enim illo consequatur itaque, sequi dolorum in!</h3>
                 <h3>Lorem ipsum dolor sit amet consectetur, adipisicing, elit. Ex doloremque corrupti esse voluptate nostrum ratione enim.</h3>
            </div>
            <div class="content update col-lg-6">
                <span class="sous-titre"><h1>INSCRIPTION</h1></span>
                <form id="inscription-form" action="inscription.php" method="post" class="droite">
                    <div class="form-group">
                        <input class="form-control" type="text" name="nom" placeholder="NOM COMPLET" value="" id="nom"><br>
                        <?php if(!empty($nom_err)) : ?>
                        <p><?= $icon_type ." ". $nom_err?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="text" name="tel" placeholder="TELEPHONE" id="tel"><br>
                        <?php if(!empty($tel_err)) : ?>
                        <p><?= $icon_type ." ". $tel_err?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="text" name="mail" placeholder="COURRIER ELECTRONIQUE" id="email"><br>
                        <?php if(!empty($mail_err)) : ?>
                        <p><?= $icon_type ." ". $mail_err?></p>
                        <?php endif; ?>                        
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="text" name="pseudo" placeholder="PSEUDO" id="title"><br>
                        <?php if(!empty($pseudo_err)) : ?>
                        <p><?= $icon_type ." ". $pseudo_err?></p>
                        <?php endif; ?>                        
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="password" name="pswd" placeholder="MOT DE PASSE" id="title"><br>
                        <?php if(!empty($pswd_err)) : ?>
                        <p><?= $icon_type ." ". $pswd_err?></p>
                        <?php endif; ?>                        
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="password" name="pswdcof" placeholder="CONFIRMATION MOT DE PASSE" id="title"><br>
                        <?php if(!empty($pswdcof_err)) : ?>
                        <p><?= $icon_type ." ". $pswdcof_err?></p>
                        <?php endif; ?>                        
                    </div>
                    <div class="boutton">
                        <span class="subscribe"><button type="submit" name="submit" class="btn-inscrit">Inscription</button></span>
                    </div>

                    
                </form>
                <?php if ($msg): ?>
                <p><?=$msg?></p>
                <?php endif; ?>
            </div>
        </div>
        <div>
           <p class="langue">français <i class="fas fa-caret-down"></i></p>   
        </div>
    </div>

<!-- // SWEET ALERT NOTIFICATION // -->

 <?php if (isset($_SESSION['inscription']) ): ?>
    <script>

        let timerInterval
        Swal.fire({
          title: 'Inscription terminé avec succès!',
          html: 'Vous allez vous connecter automatiquement dans 3 secondes.',
          icon: 'success',
          timer: 3500,
          timerProgressBar: true,
          didOpen: () => {
            Swal.showLoading()
            timerInterval = setInterval(() => {
              const content = Swal.getHtmlContainer()
              if (content) {
                const b = content.querySelector('b')
                if (b) {
                  b.textContent = Swal.getTimerLeft()
                }
              }
            }, 100)
          },
          willClose: () => {
            clearInterval(timerInterval)
          }
        }).then((result) => {
          /* Read more about handling dismissals below */
          if (result.dismiss === Swal.DismissReason.timer) {
            console.log('I was closed by the timer')
            RedirectionJavascript()
          }
        })

    </script>
    <?php
        unset($_SESSION['inscription']);
     ?>
  <?php endif; ?> 

  </body>
</html>
