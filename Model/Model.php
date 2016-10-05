<?php
	require("connect.php");

	function connect_db(){
		$dsn="mysql:dbname=".BASE.";host=".SERVER;
		try{
			$connexion =new PDO($dsn ,USER , PASSWD );
		}catch(PDOException $e){
			printf ("Echec connexion : %s\n", $e->getMessage());
			exit ();
		}
		return $connexion ;
	}

	function getSubjectsFromCategory($category){
		$connexion = connect_db();

		$subjects=Array();
		$sql="SELECT titre, dateSujet, description, nomCat, pseudo FROM Sujet s INNER JOIN Categorie c ON s.idCat = c.idCat INNER JOIN Utilisateur u ON u.idUtil = s.idCreateur WHERE c.nomCat = :category";
		$req=$connexion->prepare($sql);
		$req->bindParam(':category', $category, PDO::PARAM_STR);
		$req->execute();
		foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $row) {
			$subjects[] = $row;
		}

		return $subjects;
	}

	function getPostsFromSubject($subject, $category){
		$connexion = connect_db();
		
		$posts=Array();
		$sql="SELECT texte, datePost, nomCat, titre, pseudo FROM Reponse r INNER JOIN Sujet s ON r.idSujet = s.idSujet INNER JOIN Categorie c ON s.idCat = c.idCat INNER JOIN Utilisateur u ON u.idUtil = r.idCreateur WHERE s.titre = :subject AND c.nomCat = :category ORDER BY datePost";
		$req=$connexion->prepare($sql);
		$req->bindParam(':subject',$subject,PDO::PARAM_STR);
		$req->bindParam(':category',$category,PDO::PARAM_STR);
		$req->execute();
		foreach($req->fetchAll(PDO::FETCH_ASSOC) as $row){
			$posts[] = $row;
		}

		return $posts;
	}

	function insertNewSubject($title, $description, $category, $user){
		$connexion = connect_db();

		$sql="INSERT INTO Sujet(titre, description, idCat, idCreateur) VALUES (:titre, :description, (SELECT idCat FROM Categorie WHERE nomCat = :categorie), (SELECT idUtil FROM Utilisateur WHERE pseudo = :creat));";
		$stmt=$connexion->prepare($sql);
		$stmt->bindParam(':titre', $title, PDO::PARAM_STR);
		$stmt->bindParam(':description', $description, PDO::PARAM_STR);
		$stmt->bindParam(':categorie', $category, PDO::PARAM_STR);
		$stmt->bindParam(':creat', $user, PDO::PARAM_STR);
		$stmt->execute();
	}

	function insertNewPost($post, $subject, $user){
		$connexion = connect_db();

		$sql="INSERT INTO Reponse(texte, idSujet, idCreateur) VALUES (:texte, (SELECT idSujet FROM Sujet WHERE titre = :sujet), (SELECT idUtil FROM Utilisateur WHERE pseudo = :creat));";
		$stmt=$connexion->prepare($sql);
		$stmt->bindParam(':texte',$post,PDO::PARAM_STR);
		$stmt->bindParam(':sujet',$subject,PDO::PARAM_STR);
		$stmt->bindParam(':creat',$user,PDO::PARAM_STR);
		$stmt->execute();
	}

    function insert_user($pseudo, $passwd, $nom, $prenom, $date, $email){
        $bdd = connect_db();

        $sql = "INSERT INTO Utilisateur(pseudo, mdp, nomUtil, prenom, dateNaissance, courriel, derniereConnexion) VALUES(:pseudo, :passwd, :nom, :prenom, :date, :email, NOW())";
        $result = $bdd->prepare($sql);
        $result->bindParam(':pseudo', $pseudo);
        $result->bindParam(':passwd', $passwd);
        $result->bindParam(':nom', $nom);
        $result->bindParam(':prenom', $prenom);
        $result->bindParam(':date', $date);
        $result->bindParam(':email', $email);

        $inscript = $result->execute();

        return $inscript;
    }

    /** Partie back-office */

    function get_post($id){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Reponse r INNER JOIN Utilisateur u ON r.idCreateur = u.idUtil INNER JOIN Sujet s ON s.idSujet = r.idSujet WHERE idRep = :id";
    	$result = $bdd->prepare($sql);
        $result->bindParam(':id', $id);
        $result->execute();
        $post = $result->fetch(PDO::FETCH_OBJ);
        
        return $post;
    }

    function get_user($id){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Utilisateur u WHERE idUtil = :id";
    	$result = $bdd->prepare($sql);
        $result->bindParam(':id', $id);
        $result->execute();
        $user = $result->fetch(PDO::FETCH_OBJ);
        
        return $user;
    }

    function delete_user($id){
        $bdd = connect_db();

        $sql = "DELETE FROM Utilisateur WHERE idUtil = :id";
        $result = $bdd->prepare($sql);
        $result->bindParam(':id', $id);

        $deluser = $result->execute();

        return $deluser;
    }

    function update_user($id, $banni, $admin, $modo){
        $bdd = connect_db();

        $sql = "UPDATE Utilisateur SET banni = :banni, moderateur = :modo, administrateur = :admin WHERE idUtil = :id";
        $result = $bdd->prepare($sql);
        $result->bindParam(':banni', $banni);
        $result->bindParam(':modo', $modo);
        $result->bindParam(':admin', $admin);
        $result->bindParam(':id', $id);

        $upuser = $result->execute();

        return $upuser;
    }

    function get_logs($login, $passwd){
        $bdd = connect_db();

        $sql = "SELECT pseudo, passwd, administrateur, moderateur, banni FROM Utilisateur WHERE pseudo = :login AND passwd = :passwd";
        $result = $bdd->prepare($sql);
        $result->bindParam(':login', $login);
        $result->bindParam(':passwd', $passwd);
        $result->execute();
        $log = $result->fetch(PDO::FETCH_OBJ);

        return $log;
    }
    
    function get_topic_from_cat($idcat){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Sujet s INNER JOIN Categorie c ON s.idCat = c.idCat WHERE idCat = :idcat";
    	$result = $bdd->prepare($sql);
        $result->bindParam(':idcat', $idcat);
        $result->execute();
        $topic = $result->fetch(PDO::FETCH_OBJ);
        
        return $topic;
    }


    function get_topic($id){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Sujet s INNER JOIN Categorie c ON s.idCat = c.idCat WHERE idSujet = :id";
    	$result = $bdd->prepare($sql);
        $result->bindParam(':id', $id);
        $result->execute();
        $topic = $result->fetch(PDO::FETCH_OBJ);
        
        return $topic;
    }

    function delete_topic($id){
        $bdd = connect_db();

        $sql = "DELETE FROM Reponse WHERE idSujet = :id; DELETE FROM Sujet WHERE idSujet = :id;";
        $result = $bdd->prepare($sql);
        $result->bindParam(':id', $id);

        $deltopic = $result->execute();

        return $deltopic;
    }

    function get_categ($id){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Categorie WHERE idCat = :id";
    	$result = $bdd->prepare($sql);
        $result->bindParam(':id', $id);
        $result->execute();
        $categ = $result->fetch(PDO::FETCH_OBJ);
        
        return $categ;
    }

    function get_all_posts(){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Reponse r INNER JOIN Utilisateur u ON r.idCreateur = u.idUtil INNER JOIN Sujet s ON s.idSujet = r.idSujet";
    	$results = $bdd->query($sql);
    	$posts = $results->fetchAll(PDO::FETCH_OBJ);

    	return $posts;
    }

    function get_posts_from_topic($id){
        $bdd = connect_db();

        $sql = "SELECT * FROM Reponse r INNER JOIN Utilisateur u ON r.idCreateur = u.idUtil INNER JOIN Sujet s ON s.idSujet = r.idSujet WHERE r.idSujet = :id";
        $results = $bdd->prepare($sql);
        $results->bindParam(':id', $id);
        $results->execute();
        $poststp = $results->fetchAll(PDO::FETCH_OBJ);

        return $poststp;
    }

    function get_posts_from_categ($id){
        $bdd = connect_db();

        $sql = "SELECT * FROM Reponse r INNER JOIN Utilisateur u ON r.idCreateur = u.idUtil INNER JOIN Sujet s ON s.idSujet = r.idSujet INNER JOIN Categorie c ON s.idCat = c.idCat WHERE c.idCat = :id";
        $results = $bdd->prepare($sql);
        $results->bindParam(':id', $id);
        $results->execute();
        $postscat = $results->fetchAll(PDO::FETCH_OBJ);

        return $postscat;
    }

    function delete_post($id){
        $bdd = connect_db();

        $sql = "DELETE FROM Reponse WHERE idRep = :id";
        $result = $bdd->prepare($sql);
        $result->bindParam(':id', $id);

        $delpost = $result->execute();

        return $delpost;
    }

    function get_all_users(){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Utilisateur";
    	$results = $bdd->query($sql);
    	$users = $results->fetchAll(PDO::FETCH_OBJ);

    	return $users;
    }

    function get_all_topics(){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Sujet s INNER JOIN Categorie c ON s.idCat = c.idCat INNER JOIN Utilisateur u ON u.idUtil = s.idCreateur";
    	$results = $bdd->query($sql);
    	$topics = $results->fetchAll(PDO::FETCH_OBJ);

    	return $topics;
    }

    function update_topic($titre, $idcat, $id){
        $bdd = connect_db();

        $sql = "UPDATE Sujet SET titre = :titre, idCat = :idcat WHERE idSujet = :id";
        $result = $bdd->prepare($sql);
        $result->bindParam(':titre', $titre);
        $result->bindParam(':idcat', $idcat);
        $result->bindParam(':id', $id);

        $uptopic = $result->execute();

        return $uptopic;
    }

    function get_all_categ(){
    	$bdd = connect_db();

    	$sql = "SELECT * FROM Categorie";
    	$results = $bdd->query($sql);
    	$cats = $results->fetchAll(PDO::FETCH_OBJ);

    	return $cats;
    }

    function create_categ($nom_cat){
        $bdd = connect_db();

        $sql = "INSERT INTO Categorie(nomCat) VALUES(:nom_cat)";
        $result = $bdd->prepare($sql);
        $result->bindParam(':nom_cat', $nom_cat);

        $crcat = $result->execute();

        return $crcat;
    }

    function update_categ($nom_cat, $id){
        $bdd = connect_db();

        $sql = "UPDATE Categorie SET nomCat = :nom_cat WHERE idCat = :id";
        $result = $bdd->prepare($sql);
        $result->bindParam(':nom_cat', $nom_cat);
        $result->bindParam(':id', $id);

        $upcat = $result->execute();

        return $upcat;
    }

    function delete_categ($id){
        $bdd = connect_db();

        $sql = "DELETE FROM Categorie WHERE idCat = :id";
        $result = $bdd->prepare($sql);
        $result->bindParam(':id', $id);

        $delcat = $result->execute();

        return $delcat;
    }

    /** Fin de la partie back-office */

?>

