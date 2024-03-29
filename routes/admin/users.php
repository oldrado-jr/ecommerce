<?php

use Hcode\ErrorHandler;
use Hcode\Model\User;
use Hcode\PageAdmin;
use Hcode\SuccessHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

ErrorHandler::create(User::ERROR);
SuccessHandler::create(User::SUCCESS);

$app->group('/admin/users', function (RouteCollectorProxy $group) {
	$group->group('/{iduser:[0-9]+}', function (RouteCollectorProxy $subgroup) {
		$subgroup->get('/delete', function (Request $request, Response $response, array $args) {
			User::verifyLogin();

			$user = new User();
			$user->get($args['iduser']);
			$user->delete();

			return $response->withHeader('Location', '/admin/users')->withStatus(302);
		});

		$subgroup->get('/password', function (Request $request, Response $response, array $args) {
			User::verifyLogin();

			$user = new User();
			$user->get($args['iduser']);

			$page = new PageAdmin();
			$page->setTpl('users-password', [
				'msgError' => ErrorHandler::getMsgError(),
				'msgSuccess' => SuccessHandler::getMsgSuccess(),
				'user' => $user->getValues()
			]);

			return $response;
		});

		// TODO: Refactor password validation and redirect
		$subgroup->post('/password', function (Request $request, Response $response, array $args) {
			User::verifyLogin();
			$params = $request->getParsedBody();
			$errorMsg = '';

			if (empty($params['despassword'])) {
				$errorMsg = 'Digite a nova senha!';
			} elseif (empty($params['despassword-confirm'])) {
				$errorMsg = 'Confirme a nova senha!';
			} elseif ($params['despassword-confirm'] !== $params['despassword']) {
				$errorMsg = 'As senhas não coincidem!';
			}

			if (!empty($errorMsg)) {
				return $response->withHeader('Location', "/admin/users/{$args['iduser']}/password")->withStatus(302);
			}

			$user = new User();
			$user->get($args['iduser']);

			if (password_verify($params['despassword'], $user->getDespassword())) {
				ErrorHandler::setMsgError('A sua nova senha deve ser diferente da atual!');
				return $response->withHeader('Location', "/admin/users/{$args['iduser']}/password")->withStatus(302);
			}

			$user->setDespassword($params['despassword']);
			$user->update();
			SuccessHandler::setMsgSuccess('Senha alterada com sucesso!');

			return $response->withHeader('Location', "/admin/users/{$args['iduser']}/password")->withStatus(302);
		});

		$subgroup->get('', function (Request $request, Response $response, array $args) {
			User::verifyLogin();

			$user = new User();
			$user->get($args['iduser']);

			$page = new PageAdmin();
			$page->setTpl('users-update', ['user' => $user->getValues()]);

			return $response;
		});

		$subgroup->post('', function (Request $request, Response $response, array $args) {
			User::verifyLogin();
			$params = $request->getParsedBody();

			if (!isset($params['inadmin'])) {
				$params['inadmin'] = 0;
			}

			$user = new User();
			$user->get($args['iduser']);
			$user->setData($params);
			$user->update();

			return $response->withHeader('Location', '/admin/users')->withStatus(302);
		});
	});

	$group->group('/create', function (RouteCollectorProxy $subgroup) {
		$subgroup->get('', function (Request $request, Response $response) {
			User::verifyLogin();
			$page = new PageAdmin();
			$page->setTpl('users-create');
			return $response;
		});

		$subgroup->post('', function (Request $request, Response $response) {
			User::verifyLogin();
			$params = $request->getParsedBody();

			if (!isset($params['inadmin'])) {
				$params['inadmin'] = 0;
			}

			$user = new User();
			$user->setData($params);
			$user->save();

			return $response->withHeader('Location', '/admin/users')->withStatus(302);
		});
	});

	$group->get('', function (Request $request, Response $response) {
		User::verifyLogin();
		$params = $request->getQueryParams();

		$search = (isset($params['search'])) ? htmlentities(trim(strip_tags($params['search'])), ENT_QUOTES) : '';
		$page = (isset($params['page'])) ? (int) $params['page'] : 1;
		$pagination = User::getPageSearch($search, $page);
		$pages = [];

		for ($i = 1; $i <= $pagination['pages']; $i++) {
			$pages[] = [
				'href' => '/admin/users?' . http_build_query([
					'page' => $i,
					'search' => $search
				]),
				'text' => $i
			];
		}

		$page = new PageAdmin();
		$page->setTpl('users', [
			'users' => $pagination['data'],
			'search' => $search,
			'pages' => $pages
		]);

		return $response;
	});
});
