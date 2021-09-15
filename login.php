<?php
require 'functions.php';
session_start(); 

$pdo = pdo_connect_mysql();
    // Define variables and initialize with empty values
    $pseudo = $pswd = "";
    $pseudo_err = $pswd_err = "";
    $icon_type= "<i class='fas fa-exclamation-triangle'></i>";



if(isset($_POST['login'])) {

    $ps = $_POST['pseudo'];
    $pass = $_POST['pswd'];
    function data_input($data) {
          $data = trim($data);
          $data = stripslashes($data);
          $data = htmlspecialchars($data);
          return $data;
        }	

    // Validate UNAME
    $input_pseudo = data_input($_POST["pseudo"]);

    if(empty($input_pseudo)){
        $pseudo_err = "Inserer votre nom d'utilisateur.";
    } else{
        $pseudo = $input_pseudo;

    } 
        // Validate PSWD
    $input_pswd = data_input($_POST["pswd"]);
    if(empty($input_pswd)){
        $pswd_err = "Veuillez insérer votre mot de passe";
    } else{
        $pswd = $input_pswd;
    }

	if(empty($pswd) || empty($pseudo)) {
        $pswd_err = "Veuillez insérer votre mot de passe";
        $pseudo_err = "Inserer votre nom d'utilisateur.";
	} else {
  // select inscription
	$sql_inscription="SELECT * FROM inscription WHERE 
	PSEUDO=? AND PSWD=? ";
	$query_inscription = $pdo->prepare($sql_inscription);
	$query_inscription->execute(array($pseudo,$pswd));
	$row_inscription = $query_inscription->fetch(PDO::FETCH_BOTH);

  // select moderateur
  $sql_modo="SELECT * FROM moderateur WHERE 
  PSEUDO=? AND PSWD=? ";
  $query_modo = $pdo->prepare($sql_modo);
  $query_modo->execute(array($pseudo,$pswd));
  $row_modo = $query_modo->fetch(PDO::FETCH_BOTH);  


		if($query_inscription->rowCount() > 0) {
      if(isset($_POST['rememberme'])){
      setcookie('pseudo',$ps,time()+365*24*3600,null,null,false,true);
      setcookie('pswd',$pass,time()+365*24*3600,null,null,false,true);
      }

      /*if(!isset($_SESSION[id]) AND isset($_COOKIE['pseudo'],$_COOKIE['pswd']) AND !empty($_COOKIE['pseudo']) AND !empty($_COOKIE['pswd'])){
        
      }*/
		  $_SESSION['username'] = $pseudo;
      $_SESSION['id'] = $row_inscription['ID'];
      $_SESSION['name'] = $row_inscription['NOM'];
      $_SESSION['user'] = "admin";
      $_SESSION['pseudo'] = $ps;

      // SI LA SESSION URL A ETE DEJA PREDEFINI AVANT LA CONNEXION
      if (isset($_SESSION['url'])) {
        # rédiriger avec l'url prédefini
        header('location:'.$_SESSION['url']);
        exit;
      } else {
        # si la session url n'est pas prédefini rédiriger vers l'index
        header('location:index.php');

        exit;
      }

      

		} elseif ($query_modo->rowCount() > 0) {
      $_SESSION['username'] = $pseudo;
      $_SESSION['id'] = $row_modo['ID'];
      $_SESSION['id_modo'] = $row_modo['ID_MODO'];
      $_SESSION['name'] = $row_modo['NOM_COMPLET'];
      $_SESSION['user'] = "modo"; 
      $_SESSION['pseudo'] = $ps;


      // SI LA SESSION URL A ETE DEJA PREDEFINI AVANT LA CONNEXION
      if (isset($_SESSION['url'])) {
        # rédiriger avec l'url prédefini
        header('location:'.$_SESSION['url']);
        exit;
      } else {
        # si la session url n'est pas prédefini rédiriger vers l'index
        header('location:index.php');

        exit;
      }

    } else {
		  $message = "<span style='color: red'>Username/Password is wrong</span><br>";

		}

	}

}

?>

<!DOCTYPE html>
<html>
<head>
	  <meta charset="utf-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link href="bootstrap-4.5.3/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="style/login.css">
    <link rel="icon" type="image/PNG" href="favicon.png">
    <!-- JQUERY FORM VALIDATION -->
    <script src="scripts/jquery-3.5.1.min.js" type="text/javascript"></script>   
    <script src="scripts/popper.min.js" type="text/javascript"></script>     
    <script src="scripts/bootstrap.min.js"></script>        
    <script src="scripts/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="scripts/sweetalert2/dist/polyfill.js"></script>
    <script src="scripts/all.min.js" type="text/javascript"></script>        
    <script src="scripts/main.js"></script>  
</head>
<body>
    <div style="background-image: url(img/img1.jpg);" class="regroupement text-center">
      <p><img src="img/img2.png" class="img2 mx-auto d-block"></p>
      <h2><p><b><span style="color: white;">ILO</span> <span style="color: #FCA331;">-
                      GROSSISTE</span></b></p></h2>
     <form class="bordure mx-auto" action="login.php" style="" method="POST">
        <div id="bordure">
          <div class="form-group">
            <input type="text" name="pseudo" class="input1 form-control text-center" id="pseudolog" placeholder="Pseudo">
            <?php if(!empty($pseudo_err)) : ?>
            <p class="error"><?= $icon_type ." ". $pseudo_err?></p>
            <?php endif; ?>
          </div>
          <br>
          <div class="form-group">
            <input type="password" name="pswd" class="input1 form-control text-center" id="pswd" placeholder="Mot de passe">
            <?php if(!empty($pswd_err)) : ?>
            <p class="error"><?= $icon_type ." ". $pswd_err?></p>
            <?php endif; ?>
          </div>
          <div class="check-box-group">
            <label class="custom-checkbox" tab-index="0" aria-label="Checkbox Label">
              <input id="souvenir" type="checkbox" name="rememberme" value="1"><span class="checkmark"></span>
            </label>
            <span class="souvenir">Remember me</span>
          </div>
          <?php
        if(isset($message)) {
        echo $message;
        }
        ?>
          <p><button type="submit" name="login" class="conn">CONNEXION</button></p>
          <p><a href="inscription.php" id="inscrit"> Inscription?</a></p>
        </div>
    </form> 
  </div>

  <script>
       $(document).ready(function() {
          setTimeout(function(){
              $("#pseudolog").focus();
          }, 200);
      });
  </script>  

</body>
</html>
