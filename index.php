<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require 'vendor/autoload.php';
require 'classes/DatabaseServiceProvider.php';
require 'classes/FormServiceProvider.php';

use \Silex\Application;
use \Silex\Provider\TwigServiceProvider;
use \Symfony\Component\HttpFoundation\Cookie;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\RedirectResponse;

$app = new Application();

$app['cookie.set'] = $app->protect(function ($response, $name, $value) {
    $cookie = new Cookie($name, $value);
    $response->headers->setCookie($cookie, time() * 3600 * 24 * 7);
    return $response;
});

$app->register(new FormServiceProvider(), []);

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ .'/templates'
]);

$app->register(new DatabaseServiceProvider(), [
    'db.dsn'     => 'mysql:host=127.0.0.1;dbname=Blog',
    'db.user'    => 'root',
    'db.pass'    => '435555iei',
    'db.options' => [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
]);

$app->get('/', function(Request $req) use ($app) {
    return $app['twig']->render('main.twig', ['req' => $req]);
});

$app->get('/register', function(Request $req) use ($app) {
    return $app['twig']->render('register.twig', ['req' => $req]);
});

$app->post('/register', function(Request $req) use ($app) {
    $fields['nick'] = $req->get('nick');
    $fields['pass'] = $req->get('pass');
    $fields['confirmPass'] = $req->get('confirmPass');
    $fields['email'] = $req->get('email');
    $fields['age'] = $req->get('age');

    $errors = $app['form']->checkRegister($fields);

    if (count($errors) != 0) {
        return $app['twig']->render('register.twig', [
            'req' => $req, 'errors' => $errors, 'fields' => $fields
        ]);
    }

    $date = "{$fields['age']['year']}-{$fields['age']['month']}-{$fields['age']['day']}";
    $app['db']->addUser([$fields['nick'], $fields['email'], $fields['pass'], $date]);

    $response = new RedirectResponse($req->getBasePath());
    $response = $app['cookie.set']($response, 'nick', $fields['nick']);

    return $response;
});

$app->get('/login', function(Request $req) use ($app) {
    return $app['twig']->render('login.twig', ['req' => $req]);
});

$app->post('/login', function(Request $req) use ($app) {
    $fields['nick'] = $req->get('nick');
    $fields['pass'] = $req->get('pass');

    $errors = $app['form']->checkAuth($fields);

    if (count($errors) != 0) {
        return $app['twig']->render('login.twig', [
            'req' => $req, 'errors' => $errors, 'fields' => $fields
        ]);
    }

    $response = new RedirectResponse($req->getBasePath());
    $response = $app['cookie.set']($response, 'nick', $fields['nick']);

    return $response;
});

$app->post('/addPost', function (Request $req) use ($app) {
    $nick = $req->cookies->get('nick');
    $title = $req->get('title');
    $text = $req->get('text');
    $date = date('Y-m-d H:i:s');
    $postId = md5(uniqid(rand(), true));

    $app['db']->addPost([$postId, $nick, $title, $text, $date]);

    return $app->redirect($req->getBasePath() ."/blog/$nick");
});

$app->get('/exit', function(Request $req) use ($app) {
    $response = new RedirectResponse($req->getBasePath());
    $response->headers->clearCookie('nick');
    return $response;
});

$app->get('/blog/{nick}', function(Request $req, $nick) use ($app) {
    $posts = $app['db']->getAllPostsByNick($nick);

    return $app['twig']->render('blog.twig', ['req' => $req, 'posts' => $posts, 'owner' => $nick]);
});

$app->post('/blog/{owner}/{postId}/addComment', function(Request $req, $owner, $postId) use ($app) {
    $text = $req->get('text');
    $date = date('Y-m-d H:i:s');
    $nick = $req->cookies->get('nick');
    $commentId = md5(uniqid(rand(), true));

    $app['db']->addComment([$commentId, $postId, $nick, $text, $date]);

    return $app->redirect($req->getBasePath() ."/blog/$owner/$postId");
});

$app->get('/blog/{owner}/{postId}', function(Request $req, $owner, $postId) use ($app) {
    $post = $app['db']->getPostByPostId($postId);
    $comments = $app['db']->getAllCommentsByPostId($postId);

    return $app['twig']->render('post.twig',
        ['req' => $req, 'post' => $post, 'comments' => $comments, 'owner' => $owner]
    );
});

$app->post('/delete_post/{postId}', function (Request $req, $postId) use ($app) {
    $nick = $req->cookies->get('nick');
    $app['db']->deletePostByPostId($postId);

    return $app->redirect($req->getBasePath() ."/blog/$nick");
});

$app->post('/delete_comment/{postId}/{commentId}', function (Request $req, $postId, $commentId) use ($app) {
    $nick = $req->cookies->get('nick');
    $app['db']->deleteCommentByCommentId($commentId);

    return $app->redirect($req->getBasePath() ."/blog/$nick/$postId");
});

$app->get('/users', function (Request $req) use ($app) {
    $users = $app['db']->getLastUsers(100);

    return $app['twig']->render('users.twig',
        ['req' => $req, 'users' => $users]
    );
});

$app->run();