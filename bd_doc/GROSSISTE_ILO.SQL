-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 26 mars 2021 à 15:22
-- Version du serveur :  10.4.14-MariaDB
-- Version de PHP : 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+03:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `grossiste_ilo`
--


# -----------------------------------------------------------------------------
#       TABLE : MODERATEUR
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS MODERATEUR
 (
   ID_MODO INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID INTEGER(50) NOT NULL  ,
   NOM_COMPLET VARCHAR(128) NOT NULL  ,
   TEL VARCHAR(128) NOT NULL  ,
   PSEUDO BLOB NOT NULL  ,
   PSWD BLOB NOT NULL  ,
   ADRESSSE VARCHAR(128) NOT NULL  ,
   DATE_INSCRIPTION DATE NULL  ,
   PHOTO VARCHAR(128) NULL  
   , PRIMARY KEY (ID_MODO) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE MODERATEUR
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_MODERATEUR_INSCRIPTION
     ON MODERATEUR (ID ASC);

# -----------------------------------------------------------------------------
#       TABLE : FOURNISSEUR
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS FOURNISSEUR
 (
   ID_FOURNISSEUR INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID INTEGER(50) NOT NULL  ,
   NOM VARCHAR(128) NULL  ,
   COURRIEL VARCHAR(128) NULL  ,
   TEL VARCHAR(128) NULL  ,
   ADRESSE VARCHAR(128) NULL  ,
   VILLE VARCHAR(128) NULL  ,
   PAYS VARCHAR(128) NULL  ,
   STATUT VARCHAR(128) NULL  ,
   RCS VARCHAR(128) NULL  ,
   NIF VARCHAR(128) NULL  ,
   STAT VARCHAR(128) NULL  ,
   DATE_AJOUT DATE NULL  ,
   AJOUTE_PAR VARCHAR(128) NULL  
   , PRIMARY KEY (ID_FOURNISSEUR) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE FOURNISSEUR
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_FOURNISSEUR_INSCRIPTION
     ON FOURNISSEUR (ID ASC);

# -----------------------------------------------------------------------------
#       TABLE : FACTURE
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS FACTURE
 (
   ID_FACTURE INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID_CLIENT INTEGER(50) NOT NULL  ,
   DATE_ECHEANCE VARCHAR(11) NOT NULL  ,
   HEURE_ECHEANCE TIME NOT NULL  ,
   CLIENT VARCHAR(128) NOT NULL  ,
   DESIGNATION VARCHAR(128) NOT NULL  ,
   QUANTITE INTEGER(5) NOT NULL  ,
   PRIX_UNITAIRE REAL(50,2) NOT NULL  ,
   STATUT VARCHAR(128) NOT NULL  
   , PRIMARY KEY (ID_FACTURE) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : INSCRIPTION
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS INSCRIPTION
 (
   ID INTEGER(50) NOT NULL AUTO_INCREMENT ,
   NOM VARCHAR(128) NOT NULL  ,
   TEL VARCHAR(128) NOT NULL  ,
   COURRIEL VARCHAR(128) NOT NULL  ,
   PSEUDO BLOB NOT NULL  ,
   PSWD BLOB NOT NULL  ,
   DATE_INSCRIPTION DATE NULL  ,
   PHOTO VARCHAR(128) NULL  
   , PRIMARY KEY (ID) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : VENTE
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS VENTE
 (
   ID_VENTE INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID_FACTURE INTEGER(50) NOT NULL  ,
   ID_GESTION BIGINT(50) NOT NULL  ,
   ID_CLIENT INTEGER(50) NOT NULL  ,
   NOM_PRODUIT VARCHAR(128) NOT NULL  ,
   REFERENCE VARCHAR(128) NOT NULL  ,
   UNITE VARCHAR(128) NOT NULL  ,
   QUANTITE_UNITE VARCHAR(10) NOT NULL  ,
   PRIX_UNITAIRE REAL(50,2) NOT NULL  ,
   QUANTITE INTEGER(5) NOT NULL  ,
   BENEFICE REAL(10,2) NOT NULL  ,
   DATE_VENTE VARCHAR(11) NOT NULL  ,
   HEURE_VENTE TIME NOT NULL  ,
   CLIENT VARCHAR(128) NOT NULL  ,
   STATUT VARCHAR(15) NOT NULL  ,
   AJOUTE_PAR VARCHAR(128) NOT NULL  
   , PRIMARY KEY (ID_VENTE) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE VENTE
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_VENTE_FACTURE
     ON VENTE (ID_FACTURE ASC);

CREATE  INDEX I_FK_VENTE_GESTION_STOCK
     ON VENTE (ID_GESTION ASC);

CREATE  INDEX I_FK_VENTE_CLIENT
     ON VENTE (ID_CLIENT ASC);

# -----------------------------------------------------------------------------
#       TABLE : GESTION_CAISSE
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS GESTION_CAISSE
 (
   ID_GESTION_CAISSE BIGINT(50) NOT NULL AUTO_INCREMENT ,
   SESSION_ID INTEGER(50) NOT NULL  ,
   DATE_AJOUT VARCHAR(128) NOT NULL  ,
   SOLDE_ACTUEL REAL(50,2) NOT NULL  ,
   SOLDE REAL(50,2) NOT NULL  ,
   ETAT VARCHAR(128) NOT NULL  ,
   MOTIF VARCHAR(200) NULL  ,
   RESPONSABLE VARCHAR(128) NOT NULL  ,
   ID_FOURNISSEUR_RESP CHAR(32) NULL  ,
   ID_CLIENT_RESP CHAR(32) NULL  ,
   CLIENT_RESP CHAR(32) NULL  ,
   FOURNISSEUR_RESP CHAR(32) NULL  
   , PRIMARY KEY (ID_GESTION_CAISSE) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : CLIENT
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS CLIENT
 (
   ID_CLIENT INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID INTEGER(50) NOT NULL  ,
   NOM VARCHAR(128) NULL  ,
   COURRIEL VARCHAR(128) NULL  ,
   TEL VARCHAR(128) NULL  ,
   ADRESSE VARCHAR(128) NULL  ,
   VILLE VARCHAR(128) NULL  ,
   PAYS VARCHAR(128) NULL  ,
   STATUT CHAR(32) NULL  ,
   RCS VARCHAR(128) NULL  ,
   NIF VARCHAR(128) NULL  ,
   STAT CHAR(32) NULL  ,
   DATE_AJOUT DATE NULL  ,
   AJOUTE_PAR VARCHAR(128) NULL  
   , PRIMARY KEY (ID_CLIENT) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE CLIENT
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_CLIENT_INSCRIPTION
     ON CLIENT (ID ASC);

# -----------------------------------------------------------------------------
#       TABLE : HISTORIQUE
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS HISTORIQUE
 (
   ID_HISTORIQUE INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID INTEGER(50) NOT NULL  ,
   DATE_HISTORIQUE DATE NOT NULL  ,
   HEURE_HISTORIQUE TIME NOT NULL  ,
   ACTION VARCHAR(128) NOT NULL  ,
   TYPE VARCHAR(128) NOT NULL  ,
   AJOUTER_PAR VARCHAR(128) NOT NULL  ,
   DESIGNATION VARCHAR(1000) NOT NULL  ,
   AVANT VARCHAR(1000) NOT NULL  ,
   APRES VARCHAR(1000) NULL  ,
   MODIFIER_PAR VARCHAR(200) NULL  ,
   SUPPRIMER_PAR VARCHAR(200) NULL  
   , PRIMARY KEY (ID_HISTORIQUE) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE HISTORIQUE
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_HISTORIQUE_INSCRIPTION
     ON HISTORIQUE (ID ASC);

# -----------------------------------------------------------------------------
#       TABLE : CAISSE_VENTE
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS CAISSE_VENTE
 (
   ID_CAISSE_VENTE BIGINT(50) NOT NULL AUTO_INCREMENT ,
   ID_GESTION_CAISSE BIGINT(50) NOT NULL  ,
   ID_VENTE INTEGER(50) NOT NULL  ,
   ID_CLIENT INTEGER(50) NOT NULL  ,
   SESSION_ID INTEGER(50) NOT NULL  ,
   DATE_AJOUT VARCHAR(11) NOT NULL  ,
   HEURE TIME NOT NULL  ,
   DESIGNATION VARCHAR(128) NOT NULL  ,
   SORTIE REAL(50,2) NOT NULL  ,
   VENDU_PAR VARCHAR(128) NOT NULL  ,
   CLIENT CHAR(32) NOT NULL  
   , PRIMARY KEY (ID_CAISSE_VENTE) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE CAISSE_VENTE
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_CAISSE_VENTE_GESTION_CAISSE
     ON CAISSE_VENTE (ID_GESTION_CAISSE ASC);

CREATE  INDEX I_FK_CAISSE_VENTE_VENTE
     ON CAISSE_VENTE (ID_VENTE ASC);

# -----------------------------------------------------------------------------
#       TABLE : STOCK
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS STOCK
 (
   ID_STOCK INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID_GESTION BIGINT(50) NOT NULL  ,
   ID_FOURNISSEUR INTEGER(50) NOT NULL  ,
   DATE_AJOUT VARCHAR(11) NOT NULL  ,
   NOM_PRODUIT VARCHAR(128) NOT NULL  ,
   REFERENCE VARCHAR(128) NOT NULL  ,
   UNITE VARCHAR(128) NOT NULL  ,
   QUANTITE_UNITE VARCHAR(10) NOT NULL  ,
   PRIX_UNITAIRE REAL(50,2) NOT NULL  ,
   QUANTITE INTEGER(5) NOT NULL  ,
   DATE_ACHAT VARCHAR(11) NOT NULL  ,
   FOURNISSEUR VARCHAR(128) NOT NULL  ,
   DESCRIPTION VARCHAR(128) NOT NULL  ,
   AJOUTE_PAR VARCHAR(128) NOT NULL  ,
   IMAGE VARCHAR(128) NULL  
   , PRIMARY KEY (ID_STOCK) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE STOCK
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_STOCK_GESTION_STOCK
     ON STOCK (ID_GESTION ASC);

CREATE  INDEX I_FK_STOCK_FOURNISSEUR
     ON STOCK (ID_FOURNISSEUR ASC);

# -----------------------------------------------------------------------------
#       TABLE : DEFFECTUEUX
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS DEFFECTUEUX
 (
   ID_DEFF INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID_GESTION BIGINT(50) NOT NULL  ,
   ID_FOURNISSEUR INTEGER(50) NOT NULL  ,
   DESIGNATION VARCHAR(128) NOT NULL  ,
   QUANTITE INTEGER(5) NOT NULL  ,
   DATE_AJOUT VARCHAR(11) NOT NULL  ,
   DESCRIPTION VARCHAR(128) NOT NULL  ,
   PRIX_UNITAIRE REAL(50,2) NOT NULL  ,
   FOURNISSEUR VARCHAR(128) NOT NULL  ,
   AJOUTER_PAR VARCHAR(128) NULL  
   , PRIMARY KEY (ID_DEFF) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE DEFFECTUEUX
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_DEFFECTUEUX_GESTION_STOCK
     ON DEFFECTUEUX (ID_GESTION ASC);

# -----------------------------------------------------------------------------
#       TABLE : CAISSE_STOCK
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS CAISSE_STOCK
 (
   ID_CAISSE_STOCK INTEGER(50) NOT NULL AUTO_INCREMENT ,
   ID_GESTION BIGINT(50) NOT NULL  ,
   ID_GESTION_CAISSE BIGINT(50) NOT NULL  ,
   ID_FOURNISSEUR INTEGER(50) NOT NULL  ,
   ID_STOCK INTEGER(50) NOT NULL  ,
   SESSION_ID BIGINT(50) NOT NULL  ,
   DATE_AJOUT VARCHAR(11) NOT NULL  ,
   HEURE TIME NOT NULL  ,
   DESIGNATION VARCHAR(128) NOT NULL  ,
   ENTRER REAL(50,2) NOT NULL  ,
   AJOUTER_PAR VARCHAR(128) NOT NULL  ,
   FOURNISSEUR VARCHAR(128) NOT NULL  
   , PRIMARY KEY (ID_CAISSE_STOCK) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       INDEX DE LA TABLE CAISSE_STOCK
# -----------------------------------------------------------------------------


CREATE  INDEX I_FK_CAISSE_STOCK_GESTION_STOCK
     ON CAISSE_STOCK (ID_GESTION ASC);

CREATE  INDEX I_FK_CAISSE_STOCK_GESTION_CAISSE
     ON CAISSE_STOCK (ID_GESTION_CAISSE ASC);

# -----------------------------------------------------------------------------
#       TABLE : GESTION_STOCK
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS GESTION_STOCK
 (
   ID_GESTION BIGINT(50) NOT NULL AUTO_INCREMENT ,
   ID_FOURNISSEUR INTEGER(50) NOT NULL  ,
   SESSION_ID INTEGER(50) NOT NULL  ,
   NOM_PRODUIT VARCHAR(128) NOT NULL  ,
   REFERENCE VARCHAR(128) NOT NULL  ,
   UNITE VARCHAR(128) NOT NULL  ,
   QUANTITE_UNITE VARCHAR(10) NOT NULL  ,
   PRIX_UNITAIRE REAL(50,2) NOT NULL  ,
   QUANTITE BIGINT(4) NOT NULL  ,
   FOURNISSEUR VARCHAR(128) NOT NULL  
   , PRIMARY KEY (ID_GESTION) 
 ) 
 comment = "";


# -----------------------------------------------------------------------------
#       CREATION DES REFERENCES DE TABLE
# -----------------------------------------------------------------------------


ALTER TABLE MODERATEUR 
  ADD FOREIGN KEY FK_MODERATEUR_INSCRIPTION (ID)
      REFERENCES INSCRIPTION (ID) ;


ALTER TABLE FOURNISSEUR 
  ADD FOREIGN KEY FK_FOURNISSEUR_INSCRIPTION (ID)
      REFERENCES INSCRIPTION (ID) ;


ALTER TABLE VENTE 
  ADD FOREIGN KEY FK_VENTE_FACTURE (ID_FACTURE)
      REFERENCES FACTURE (ID_FACTURE) ;


ALTER TABLE VENTE 
  ADD FOREIGN KEY FK_VENTE_GESTION_STOCK (ID_GESTION)
      REFERENCES GESTION_STOCK (ID_GESTION) ;


ALTER TABLE VENTE 
  ADD FOREIGN KEY FK_VENTE_CLIENT (ID_CLIENT)
      REFERENCES CLIENT (ID_CLIENT) ;


ALTER TABLE CLIENT 
  ADD FOREIGN KEY FK_CLIENT_INSCRIPTION (ID)
      REFERENCES INSCRIPTION (ID) ;


ALTER TABLE HISTORIQUE 
  ADD FOREIGN KEY FK_HISTORIQUE_INSCRIPTION (ID)
      REFERENCES INSCRIPTION (ID) ;


ALTER TABLE CAISSE_VENTE 
  ADD FOREIGN KEY FK_CAISSE_VENTE_GESTION_CAISSE (ID_GESTION_CAISSE)
      REFERENCES GESTION_CAISSE (ID_GESTION_CAISSE) ;


ALTER TABLE CAISSE_VENTE 
  ADD FOREIGN KEY FK_CAISSE_VENTE_VENTE (ID_VENTE)
      REFERENCES VENTE (ID_VENTE) ;


ALTER TABLE STOCK 
  ADD FOREIGN KEY FK_STOCK_GESTION_STOCK (ID_GESTION)
      REFERENCES GESTION_STOCK (ID_GESTION) ;


ALTER TABLE STOCK 
  ADD FOREIGN KEY FK_STOCK_FOURNISSEUR (ID_FOURNISSEUR)
      REFERENCES FOURNISSEUR (ID_FOURNISSEUR) ;


ALTER TABLE DEFFECTUEUX 
  ADD FOREIGN KEY FK_DEFFECTUEUX_GESTION_STOCK (ID_GESTION)
      REFERENCES GESTION_STOCK (ID_GESTION) ;


ALTER TABLE CAISSE_STOCK 
  ADD FOREIGN KEY FK_CAISSE_STOCK_GESTION_STOCK (ID_GESTION)
      REFERENCES GESTION_STOCK (ID_GESTION) ;


ALTER TABLE CAISSE_STOCK 
  ADD FOREIGN KEY FK_CAISSE_STOCK_GESTION_CAISSE (ID_GESTION_CAISSE)
      REFERENCES GESTION_CAISSE (ID_GESTION_CAISSE) ;

