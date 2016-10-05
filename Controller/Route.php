<?php
	use Silex\Application;

	require_once './Model/Model.php';
	require_once './Controller/Controller.php';

	class Route {
		private $app;
		
		public function __construct(){
			$this->app = new Application();
			$this->app['debug'] = true;
			$this->app->register(new Silex\Provider\TwigServiceProvider(), array(
				'twig.path' => __DIR__.'/../View'
			));
			$this->app->register(new Silex\Provider\SessionServiceProvider());
            
              	$this->app->before(function($req){
                  	$req->getSession()->start();
              	});
		}

		public function routing(){
			$this->app->get('/', 'Controller::accesAccueil');

			$this->app->get('/inscription', 'Controller::display_inscription');
			$this->app->post('/inscription', 'Controller::post_inscription');

                  $this->app->post('/connect', 'Controller::connection');
                  $this->app->get('/deconnect', 'Controller::deconnection');
            
                  /** Partie admin */

                  $this->app->get('/login', 'Controller::display_login');
            
                  $this->app->post('/login/post', 'Controller::post_login');

                  $this->app->get('/forum-admin', 'Controller::display_admin');

                  $this->app->get('/admin-cat', 'Controller::display_admincat');

                  $this->app->get('/admin-topic', 'Controller::display_admintopic');

                  $this->app->get('/admin-user', 'Controller::display_adminuser');

                  $this->app->get('/admin-cat/create-form', 'Controller::display_admincat_create');
                  $this->app->get('/admin-categ/up/{id}', 'Controller::display_up_admincateg');
                  $this->app->post('/category-create', 'Controller::post_catcreate');
                  $this->app->post('/category-update', 'Controller::post_catupdate');
                  $this->app->get('/category-delete/{id}', 'Controller::get_catdelete');
                  $this->app->get('/save-cposts/{id}', 'Controller::save_posts_from_categ');

                  $this->app->get('/admin-post/see/{id}', 'Controller::display_adminpost');
                  $this->app->get('/post-delete/{id}', 'Controller::get_postdelete');

                  $this->app->get('/admin-topic/up/{id}', 'Controller::display_up_admintopic');
                  $this->app->get('/topic-delete/{id}', 'Controller::get_topicdelete');
                  $this->app->post('/topic-update', 'Controller::post_topicupdate');
                  $this->app->get('/save-posts/{id}', 'Controller::save_posts_from_topic');

                  $this->app->get('/admin-user/see/{id}', 'Controller::display_admin_user');
                  $this->app->get('/admin-user/up/{id}', 'Controller::display_up_adminuser');
                  $this->app->get('/user-delete/{id}', 'Controller::get_userdelete');
                  $this->app->post('/user-update', 'Controller::post_userupdate');

                  /** Partie Discussion */

                  $this->app->post('/validerIns', 'Controller::post_inscription');

                  $this->app->get('/{categorie}/{sujet}', 'Controller::discussion');
                  $this->app->post('/{categorie}/{sujet}', 'Controller::discussion_add_post');

            	$this->app->get('/{categorie}', 'Controller::discussion_liste_sujets');
            	$this->app->post('/{categorie}', 'Controller::discussion_add_subject');

            	$this->app->run();

	     }
	}
?>
