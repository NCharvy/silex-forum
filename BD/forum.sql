CREATE TABLE Utilisateur(
	idUtil int PRIMARY KEY AUTO_INCREMENT,
	pseudo varchar(30) NOT NULL,
	nomUtil varchar(30),
	prenom varchar(30),
	dateNaissance date,
	courriel varchar(30) NOT NULL,
	mdp varchar(30) NOT NULL,
	derniereConnexion datetime DEFAULT NOW() NOT NULL,
	banni boolean NOT NULL,
	moderateur boolean NOT NULL,
	administrateur boolean NOT NULL
)DEFAULT CHARSET=utf8;

CREATE TABLE Categorie(
	idCat int PRIMARY KEY AUTO_INCREMENT,
	nomCat varchar(15) NOT NULL
)DEFAULT CHARSET=utf8;

CREATE TABLE Sujet(
    idSujet int PRIMARY KEY AUTO_INCREMENT,
    titre varchar(35) NOT NULL,
    dateSujet datetime NOT NULL DEFAULT NOW(),
    description varchar(50) NOT NULL,
    idCat int NOT NULL,
    idCreateur int NOT NULL,
    FOREIGN KEY(idCat) REFERENCES Categorie(idCat),
    FOREIGN KEY(idCreateur) REFERENCES Utilisateur(idUtil)
)DEFAULT CHARSET=utf8;

CREATE TABLE Reponse(
	idRep int PRIMARY KEY AUTO_INCREMENT,
	texte varchar(300),
	datePost datetime NOT NULL DEFAULT NOW(),
	idSujet int NOT NULL,
	idCreateur int NOT NULL,
	FOREIGN KEY(idSujet) REFERENCES Sujet(idSujet),
	FOREIGN KEY(idCreateur) REFERENCES Utilisateur(idUtil)
)DEFAULT CHARSET=utf8;
