<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller{

	private $app;

	public function __construct(){
		$this->app = new Application();
		$this->app['debug'] = true;
		$this->app->register(new Silex\Provider\TwigServiceProvider(), array(
			'twig.path' => __DIR__.'/../View'
		));
	}

    public function accesAccueil(Request $req){
        $session = $req->getSession();
        $categories = get_all_categ();
        if(null !== $session->get('pseudo')){
            $pseudo = $session->get('pseudo');
            return $this->app['twig']->render('/Accueil/accueil.html.twig', array('categories' => $categories, 'pseudo' => $pseudo));
        }
        else if(null !== $req->get('error')){
            $error = $req->get('error');
            return $this->app['twig']->render('/Accueil/accueil.html.twig', array('categories' => $categories, 'error' => $error));
        }
        else{
            return $this->app['twig']->render('/Accueil/accueil.html.twig', array('categories' => $categories));
        }
    }

    public function connection(Request $req){
        $session = $req->getSession();
        $login = $req->get('pseudo');
        $passwd = sha1($req->get('pwd'));
            
        $testco = get_logs($login, $passwd);
            
        if(($testco->pseudo === $login) && ($testco->passwd === $passwd)){
            $session->set('pseudo', array('pseudoforum' => $login, 'admin' => $testco->administrateur, 'modo' => $testco->moderateur, 'banni' => $testco->banni));
            $pseudo = $session->get('pseudo');
       }
       else{
           $session->set('error', 'Erreur d\'authentification !');
        }

        return $this->app->redirect('/');
    }

    public function deconnection(Request $req){
        $session = $req->getSession();
        $session->invalidate();
        return $this->app->redirect('/');
    }

	public function liste_sujets($categorie){
		return $this->app['twig']->render('/Discussion/liste_sujets.html.twig');
    }

	public function discussion_liste_sujets(Request $req, $categorie){
        $session = $req->getSession();
		$sujets = getSubjectsFromCategory($categorie);
        if(null !== $session->get('pseudo')){
          return $this->app['twig']->render('/Discussion/liste_sujets.html.twig', array('subjects' => $sujets, 'categorie' => $categorie, 'pseudo' => $session->get('pseudo')));
        } else {
            return $this->app['twig']->render('/Discussion/liste_sujets.html.twig', array('subjects' => $sujets, 'categorie' => $categorie));
        }
	}

	public function discussion_add_subject(Request $request, $categorie){
        $session = $request->getSession();
		$description = $request->get('description');
		$titre = $request->get('titre');
		insertNewSubject($titre, $description, $categorie, $session->get('pseudo')['pseudoforum']);
		return $this->discussion_liste_sujets($request, $categorie);
    }

	public function discussion(Request $req, $categorie, $sujet){
        $session = $req->getSession();
		$posts = getPostsFromSubject($sujet, $categorie);
        if(null !== $session->get('pseudo')){
		  return $this->app['twig']->render('/Discussion/discussion.html.twig', array('posts' => $posts, 'sujet' => $sujet, 'categorie' => $categorie, 'pseudo' => $session->get('pseudo')));
        }
        else{
            return $this->app['twig']->render('/Discussion/discussion.html.twig', array('posts' => $posts, 'sujet' => $sujet, 'categorie' => $categorie));
        }
	}

	public function discussion_add_post(Request $request, $categorie, $sujet){
        $session = $request->getSession();
		$post = $request->get('new_post');
		insertNewPost($post, $sujet, $session->get('pseudo')['pseudoforum']);
		return $this->discussion($request, $categorie, $sujet);
	}

    public function post_inscription(Request $req){
        $pseudo = utf8_decode(addslashes($req->get('username')));
        $prenom = utf8_decode(addslashes($req->get('firstname')));
        $nom = utf8_decode(addslashes($req->get('name')));
        $date = $req->get('birthdate');
        $passwd = sha1($req->get('passwd'));
        $email = $req->get('email');
        $inscription = insert_user($pseudo, $passwd, $nom, $prenom, $date, $email);
        if(!$inscription){
            return "Erreur lors de l'inscription de l'utilisateur";
        }
        else{
            return $this->app->redirect('/');
        }
    }

    public function display_inscription(){
        return $this->app['twig']->render('/Inscription/inscription.html.twig');
    }

    /** Partie admin */
    
    public function display_login(Request $req){
    	$session = $req->getSession();
        $session->invalidate();
		return $this->app['twig']->render('/Admin/login.html.twig');
    }
    
    public function post_login(Request $req){
        $session = $req->getSession();
        $login = $req->get('login');
        $passwd = sha1($req->get('passwd'));
        
        $testco = get_logs($login, $passwd);
        
        if(($testco->pseudo === $login) && ($testco->passwd === $passwd) && ($testco->administrateur == 1)){
            $session->set('user', array('username' => $login));
            return $this->app->redirect('/forum-admin');
        }
        else{
            return $this->app->redirect('/');
        }
    }

    public function display_admin(Request $req){
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $posts = get_all_posts();
		return $this->app['twig']->render('/Admin/admin.html.twig', array('user' => $user, 'posts' => $posts));
    }

    public function display_admincat(Request $req){
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $cats = get_all_categ();
        return $this->app['twig']->render('/Admin/back-cat.html.twig', array('user' => $user, 'cats' => $cats));
    }

    public function display_admintopic(Request $req){
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $topics = get_all_topics();
        return $this->app['twig']->render('/Admin/back-topic.html.twig', array('user' => $user, 'topics' => $topics));
    }

    public function display_adminuser(Request $req){
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $users = get_all_users();
        return $this->app['twig']->render('/Admin/back-user.html.twig', array('user' => $user, 'users' => $users));
    }

    public function display_admincat_create(Request $req){
        $referer = $_SERVER['HTTP_REFERER'];
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        return $this->app['twig']->render('/Admin/create-cat.html.twig', array('user' => $user, 'referer' => $referer));
    }

    public function display_adminpost(Request $req, $id){
        $referer = $_SERVER['HTTP_REFERER'];
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $post = get_post($id);
        return $this->app['twig']->render('/Admin/see-post.html.twig', array('user' => $user, 'post' => $post, 'referer' => $referer));
    }

    public function get_postdelete(Request $req, $id){
        $idpost = $id;
        $delpost = delete_post($idpost);
        if(!$delpost){
            return "Erreur lors de la suppression du post";
        }
        else{
            return $this->app->redirect('/forum-admin');
        }
    }

    public function display_admin_user(Request $req, $id){
        $referer = $_SERVER['HTTP_REFERER'];
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $auser = get_user($id);
        return $this->app['twig']->render('/Admin/see-user.html.twig', array('user' => $user, 'auser' => $auser, 'referer' => $referer));
    }

    public function get_userdelete(Request $req, $id){
        $iduser = $id;
        $deluser = delete_user($iduser);
        if(!$deluser){
            return "Erreur lors de la suppression de l'utilisateur";
        }
        else{
            return $this->app->redirect('/admin-user');
        }
    }

    public function display_admin_categ(Request $req, $id){
        $referer = $_SERVER['HTTP_REFERER'];
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $categ = get_categ($id);
        return $this->app['twig']->render('/Admin/see-categ.html.twig', array('user' => $user, 'categ' => $categ, 'referer' => $referer));
    }

    public function get_topicdelete(Request $req, $id){
        $idtopic = $id;
        $deltopic = delete_topic($idtopic);
        if(!$deltopic){
            return "Erreur lors de la suppression du sujet";
        }
        else{
            return $this->app->redirect('/admin-topic');
        }
    }

    public function display_up_adminpost(Request $req, $id){
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $post = get_post($id);
        return $this->app['twig']->render('/Admin/up-post.html.twig', array('user' => $user, 'post' => $post));
    }

    public function display_up_adminuser(Request $req, $id){
        $referer = $_SERVER['HTTP_REFERER'];
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $auser = get_user($id);
        return $this->app['twig']->render('/Admin/up-user.html.twig', array('user' => $user, 'auser' => $auser, 'referer' => $referer));
    }

    public function post_userupdate(Request $req){
        $iduser = $req->get('iduser');
        $banni = $req->get('banni');
        $role = $req->get('role');
        if($role == "admin"){
            $admin = 1;
            $modo = 0;
        }
        else if($role == "modo"){
            $admin = 0;
            $modo = 1;
        }
        else{
            $admin = 0;
            $modo = 0;
        }
        $upuser = update_user($iduser, $banni, $admin, $modo);
        if(!$upuser){
            return "Erreur lors de la modification de l'utilisateur";
        }
        else{
            return $this->app->redirect('/admin-user');
        }
    }

    public function display_up_admincateg(Request $req, $id){
        $referer = $_SERVER['HTTP_REFERER'];
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $cat = get_categ($id);
        return $this->app['twig']->render('/Admin/up-categ.html.twig', array('user' => $user, 'cat' => $cat, 'referer' => $referer));
    }

    public function post_catcreate(Request $req){
        $nomcat = utf8_decode(addslashes($req->get('nomCat')));
        $crcat = create_categ($nomcat);
        if(!$crcat){
            return "Erreur lors de la création de la catégorie";
        }
        else{
            return $this->app->redirect('/admin-cat');
        }
    }

    public function post_catupdate(Request $req){
        $nomcat = utf8_decode(addslashes($req->get('nomCat')));
        $idcat = $req->get('idCat');
        $upcat = update_categ($nomcat, $idcat);
        if(!$upcat){
            return "Erreur lors de la mise à jour de la catégorie";
        }
        else{
            return $this->app->redirect('/admin-cat');
        }
    }

    public function get_catdelete(Request $req, $id){
        $idcat = $id;
        $delcat = delete_categ($idcat);
        if(!$delcat){
            return "Erreur lors de la suppression de la catégorie";
        }
        else{
            return $this->app->redirect('/admin-cat');
        }
    }

    public function display_up_admintopic(Request $req, $id){
        $referer = $_SERVER['HTTP_REFERER'];
        $session = $req->getSession();
        if(null === $user = $session->get('user')){
            return $this->app->redirect('/login');
        }
        $topic = get_topic($id);
        $cats = get_all_categ();
        return $this->app['twig']->render('/Admin/up-topic.html.twig', array('user' => $user, 'topic' => $topic, 'cats' => $cats, 'referer' => $referer));
    }

    public function post_topicupdate(Request $req){
        $nomtopic = utf8_decode(addslashes($req->get('titre')));
        $idtopic = $req->get('idSujet');
        $idcat = $req->get('categ');
        $uptopic = update_topic($nomtopic, $idcat, $idtopic);
        if(!$uptopic){
            return "Erreur lors de la mise à jour de la catégorie";
        }
        else{
            return $this->app->redirect('/admin-topic');
        }
    }

    public function save_posts_from_topic(Request $req, $id){
        $posts = get_posts_from_topic($id);
    
        $xmldoc = new DomDocument("1.0", "ISO-8859-1");
        $xmldoc->preserveWhiteSpace = false;
        $xmldoc->formatOutput = true;
        $topic = $xmldoc->createElement("topic-" . $id);
        $xmldoc->appendChild($topic);
        foreach($posts as $post){
            $tpost = $xmldoc->createElement('post');
            $tpost->setAttribute('id', $post->idRep);
            $topic->appendChild($tpost);
            $author = $xmldoc->createElement('author');
            $tpost->appendChild($author);
            $tauthor = $xmldoc->createTextNode(utf8_encode($post->pseudo));
            $author->appendChild($tauthor);
            $body = $xmldoc->createElement('body');
            $tpost->appendChild($body);
            $text = $xmldoc->createTextNode(utf8_encode($post->texte));
            $body->appendChild($text);
            $date = $xmldoc->createElement('date');
            $tpost->appendChild($date);
            $tdate = $xmldoc->createTextNode($post->datePost);
            $date->appendChild($tdate);
        }
        $filename = "topic-" . $id . "_" . date("j-m-y") . ".xml";
        $file = fopen(__DIR__ . '/../Saves/Topics/' . $filename, 'w+');
        $writed = fwrite($file, $xmldoc->saveXML());
        fclose($file);
        if(!$writed){
            return "Erreur lors de la création de la sauvegarde !";
        }
        else{
            return $this->app->redirect('/admin-topic');
        }
    }

    public function save_posts_from_categ(Request $req, $id){
        $posts = get_posts_from_categ($id);

        $xmldoc = new DomDocument("1.0", "ISO-8859-1");
        $xmldoc->preserveWhiteSpace = false;
        $xmldoc->formatOutput = true;
        $cat = $xmldoc->createElement("category-" . $id);
        $xmldoc->appendChild($cat);
        foreach($posts as $post){
            $tpost = $xmldoc->createElement('post');
            $tpost->setAttribute('id', $post->idRep);
            $cat->appendChild($tpost);
            $author = $xmldoc->createElement('author');
            $tpost->appendChild($author);
            $tauthor = $xmldoc->createTextNode(utf8_encode($post->pseudo));
            $author->appendChild($tauthor);
            $body = $xmldoc->createElement('body');
            $tpost->appendChild($body);
            $text = $xmldoc->createTextNode(utf8_encode($post->texte));
            $body->appendChild($text);
            $date = $xmldoc->createElement('date');
            $tpost->appendChild($date);
            $tdate = $xmldoc->createTextNode($post->datePost);
            $date->appendChild($tdate);
        }
        $filename =  "category-". $id . "_" . date("j-m-y") . ".xml";
        $file = fopen(__DIR__ . '/../Saves/Categories/' . $filename, 'w+');
        $writed = fwrite($file, $xmldoc->saveXML());
        fclose($file);
        if(!$writed){
            return "Erreur lors de la création de la sauvegarde !";
        }
        else{
            return $this->app->redirect('../admin-cat');
        }
        return $elemcat;
    }


    /** Fin de la partie admin */
}
?>
