
  $(document).ready(function(){
     $("#toggler-icon").click(function () {
        $(".slidebar").toggleClass("slidebar_none");
        $("#content").toggleClass("content-100");
        $("#navbar").toggleClass("navbar-100");
        $("#slidenav").toggleClass("slidenav-100");
      });

  });

  $("#contact-btn").click(function(){
    $("#contact").slideToggle();
    $("#inventaire").slideUp();
    $("#inventaire-arrow").removeClass("fa-caret-down");
    $("#inventaire-arrow").removeClass("fa-caret-right");  
    $("#inventaire-arrow").addClass("fa-caret-down");  
    $("#contact-arrow").toggleClass("fa-caret-down");
    $("#contact-arrow").toggleClass("fa-caret-right");
  }); 
  $("#inventaire-btn").click(function(){
    $("#inventaire").slideToggle();
    $("#contact").slideUp();
    $("#contact-arrow").removeClass("fa-caret-down");
    $("#contact-arrow").removeClass("fa-caret-right");  
    $("#contact-arrow").addClass("fa-caret-down");  
    $("#inventaire-arrow").toggleClass("fa-caret-down");
    $("#inventaire-arrow").toggleClass("fa-caret-right"); 
  }); 

   $(document).ready(function() {
        setTimeout(function(){
            $("#pseudolog").focus();
            $("#name_inscription").focus();
        }, 200);
    });


/* SCRIPT POUR EXECUTION DE MODAL */

   $(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('#myModal').modal('show');
    $('#modal-detail').modal('show');
  }); 


/**Retourne la valeur du select selectId*/
function getSelectValue()
{
    /**On récupère l'élement html <select>*/
    var selectElmt = document.getElementById("SelectStatu");
    /**
    selectElmt.options correspond au tableau des balises <option> du select
    selectElmt.selectedIndex correspond à l'index du tableau options qui est actuellement sélectionné
    */
    var value = selectElmt.options[selectElmt.selectedIndex].value;
    console.log(value);

    if (value=="personnel") {
        // desactivation input
         $("#nif").attr("disabled", "disabled");
         $("#stat").attr("disabled", "disabled");
         $("#rcs").attr("disabled", "disabled");
         // Effacement erreur 
          $("#erreur-stat").css("display", "none");
          $("#erreur-rcs").css("display", "none");
          $("#erreur-nif").css("display", "none");               
    }else if (value=="professionnel" ) {
        // activation input
         $("#nif").removeAttr("disabled");
         $("#stat").removeAttr("disabled");
         $("#rcs").removeAttr("disabled");
    }else if (value=="vide" ) {
        // desactivation input
         $("#nif").attr("disabled", "disabled");
         $("#stat").attr("disabled", "disabled");
         $("#rcs").attr("disabled", "disabled");
         // Effacement erreur 
          $("#erreur-stat").css("display", "none");
          $("#erreur-rcs").css("display", "none");
          $("#erreur-nif").css("display", "none");               
    } 

    if ((nif.value !=='') && ((value=="vide") || (value=="personnel"))) {
        nif.value = '';
    }
    if ((stat.value !=='') && ((value=="vide") || (value=="personnel"))) {
        stat.value = '';
    }   
    if ((rcs.value !=='') && ((value=="vide") || (value=="personnel"))) {
        rcs.value = '';
    }  
}

/* -------- INPUT CLIENT ET FOURNISSEUR ------------ */


// CONTROL INPUT NAME
function controlInputNOM() {
  $("#erreur-nom").css("display", "none");
     if($('#nom').val() == ''){
        $("#erreur-nom").css("display", "block");
   }
}
// CONTROL INPUT TELEPHONE
function controlInputTEL() {
  $("#erreur-tel").css("display", "none");
     if($('#tel').val() == ''){
        $("#erreur-tel").css("display", "block");
   }
}
// CONTROL INPUT VILLE
function controlInputVILLE() {
  $("#erreur-ville").css("display", "none");
     if($('#ville').val() == ''){
        $("#erreur-ville").css("display", "block");
   }
}
// CONTROL INPUT PAYS
function controlInputPAYS() {
  $("#erreur-pays").css("display", "none");
     if($('#pays').val() == ''){
        $("#erreur-pays").css("display", "block");
   }
}
// CONTROL INPUT MAIL
function controlInputCOURRIEL() {
  $("#erreur-courriel").css("display", "none");
     if($('#courriel').val() == ''){
        $("#erreur-courriel").css("display", "block");
   }
}


// CONTROL INPUT STATUT
function controlInputSTATUT() {
  $("#erreur-select").css("display", "none");
     if($('#SelectStatu').val() == 'vide'){
        $("#erreur-select").css("display", "block");
   }
}
// CONTROL INPUT RCS
function controlInputRCS() {
  $("#erreur-rcs").css("display", "none");
     if($('#rcs').val() == ''){
        $("#erreur-rcs").css("display", "block");
   }
}
// CONTROL INPUT NIF
function controlInputNIF() {
  $("#erreur-nif").css("display", "none");
     if($('#nif').val() == ''){
        $("#erreur-nif").css("display", "block");
   }
}
// CONTROL INPUT STAT
function controlInputSTAT() {
  $("#erreur-stat").css("display", "none");
     if($('#stat').val() == ''){
        $("#erreur-stat").css("display", "block");
   }
}

///// INPUT COMPTE ET PASSWORD ///// 

// CONTROL INPUT PSEUDO
function controlInputPSEUDO() {
  $("#erreur-pseudo").css("display", "none");
     if($('#pseudo').val() == ''){
        $("#erreur-pseudo").css("display", "block");
   }
}

// CONTROL INPUT PASSWORD
function controlInputPSWD() {
  $("#erreur-pswd").css("display", "none");
     if($('#pswd').val() == ''){
        $("#erreur-pswd").css("display", "block");
   }

  $("#show_pswd").css("display", "block");
    if($('#pswd').val() == ''){
        $("#show_pswd").css("display", "none");
   }else {
    $("#show_pswd").css("display", "block");
   }

}

// CONTROL INPUT NEW PASSWORD
function controlInputNEWPSWD() {
  $("#erreur-newpswd").css("display", "none");
     if($('#newpswd').val() == ''){
        $("#erreur-newpswd").css("display", "block");
   }

  $("#show_newpswd").css("display", "block");
    if($('#newpswd').val() == '' & $('#newpswdcof').val() == ''){
        $("#show_newpswd").css("display", "none");
        $("#erreur-newpswdcof").css("display", "block");
   } 


}
 
/* -------- INPUT ADD VENTE ET QUELQUE STOCK ------------ */

// CONTROL INPUT NOM PRODUIT
function controlInputNOM_PRODUIT() {
  $("#erreur-nom_produit").css("display", "none");
     if($('#nom_produit').val() == ''){
        $("#erreur-nom_produit").css("display", "block");
   }
}
// CONTROL INPUT PRIX UNITAIRE
function controlInputPRIX_UNITAIRE() {
  $("#erreur-prix_unitaire").css("display", "none");
     if($('#prix_unitaire').val() == ''){
        $("#erreur-prix_unitaire").css("display", "block");
   }
}
// CONTROL INPUT QUANTITE
function controlInputQUANTITE() {
  $("#erreur-quantite").css("display", "none");
     if($('#quantite').val() == ''){
        $("#erreur-quantite").css("display", "block");
   }
}
// CONTROL INPUT STATUT
function controlInputSTATUT() {
  $("#erreur-statut").css("display", "none");
     if($('#statut').val() == ''){
        $("#erreur-statut").css("display", "block");
   }
}
// CONTROL INPUT CLIENT
function controlInputCLIENT() {
  $("#erreur-client").css("display", "none");
     if($('#client').val() == 'vide'){
        $("#erreur-client").css("display", "block");
   }
}


/* -------- INPUT ADD STOCK ------------ */

// CONTROL INPUT REFERENCE
function controlInputREFERENCE() {
  $("#erreur-reference").css("display", "none");
     if($('#reference').val() == ''){
        $("#erreur-reference").css("display", "block");
   }
}
// CONTROL INPUT QUANTITE UNITE
function controlInputQUANTITE_UNITE() {
  $("#erreur-quantite_unite").css("display", "none");
     if($('#quantite_unite').val() == ''){
        $("#erreur-quantite_unite").css("display", "block");
   }
}
// CONTROL INPUT UNITE
function controlInputUNITE() {
  $("#erreur-unite").css("display", "none");
     if($('#unite').val() == 'vide'){
        $("#erreur-unite").css("display", "block");
   }
}
// CONTROL INPUT PRIX UNITAIRE
function controlInputPRIX_UNITAIRE() {
  $("#erreur-prix_unitaire").css("display", "none");
     if($('#prix_unitaire').val() == ''){
        $("#erreur-prix_unitaire").css("display", "block");
   }
}

// CONTROL INPUT DATE ACHAT
function controlInputDATE_ACHAT() {
  $("#erreur-date_achat").css("display", "none");
     if($('#date_achat').val() == ''){
        $("#erreur-date_achat").css("display", "block");
   }
}
// CONTROL INPUT FOURNISSEUR
function controlInputFOURNISSEUR() {
  $("#erreur-fournisseur").css("display", "none");
     if($('#SelectFournisseur').val() == 'vide'){
        $("#erreur-fournisseur").css("display", "block");
   }
}
// CONTROL INPUT DESCRIPTION
function controlInputDESCRIPTION() {
  $("#erreur-description").css("display", "none");
     if($('#description').val() == 'vide'){
        $("#erreur-description").css("display", "block");
   }
}



 // ACTION SI PASSWORD ET CONFIRMATION INPUT DIFFERENT DE VIDE
 $(document).ready(function(){
    if($('#pswd').val() != ''){
        $("#show_pswd").css("display", "block");
   }

    if($('#newpswd').val() != ''){
        $("#show_newpswd").css("display", "block");
   } 
    if($('#newpswdcof').val() != ''){
        $("#show_newpswd").css("display", "block");
   }  


   if ($('#SelectStatu').val() == 'professionnel') {
        // activation input
         $("#nif").removeAttr("disabled");
         $("#stat").removeAttr("disabled");
         $("#rcs").removeAttr("disabled");
    } 

  });

 // ACTION ONINPUT PASSWORD INPUT
   function Toggle1() { 
        var temp = document.getElementById("pswd"); 
        if (temp.type === "password") { 
            temp.type = "text";
            $("#show_pswd").toggleClass("fa-eye-slash");
        } 
        else { 
            temp.type = "password";
            $("#show_pswd").toggleClass("fa-eye");
        } 
    } 
 // ACTION ONINPUT NEW PASSWORD AND CONFIRMATION INPUT     
    function Toggle2() { 
        var temp = document.getElementById("newpswd");

        if (temp.type === "password") { 
            temp.type = "text";
            $("#show_newpswd").toggleClass("fa-eye-slash");
        } 
        else { 
            temp.type = "password";
            $("#show_newpswd").toggleClass("fa-eye");
        } 

        var temp = document.getElementById("newpswdcof"); 

        if (temp.type === "password") { 
            temp.type = "text";
            $("#show_newpswdcof").toggleClass("fa-eye-slash");
        } 
        else { 
            temp.type = "password";
            $("#show_newpswdcof").toggleClass("fa-eye");
        }         
    } 


 
