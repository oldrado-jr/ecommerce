<?php

use Hcode\ErrorHandler;
use Hcode\Model\Order;
use Hcode\Model\OrderStatus;
use Hcode\Model\User;
use Hcode\PageAdmin;
use Hcode\SuccessHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

ErrorHandler::create(Order::SESSION_ERROR);
SuccessHandler::create(Order::SESSION_SUCCESS);

$app->group('/admin/orders', function (RouteCollectorProxy $group) {
	$group->group('/{idorder:[0-9]+}', function (RouteCollectorProxy $subgroup) {
		$subgroup->group('/status', function (RouteCollectorProxy $subgroup) {
			$subgroup->get('', function (Request $request, Response $response, array $args) {
				User::verifyLogin();

				$order = new Order();
				$order->get($args['idorder']);

				$page = new PageAdmin();
				$page->setTpl('order-status', [
					'order' => $order->getValues(),
					'status' => OrderStatus::listAll(),
					'msgError' => ErrorHandler::getMsgError(),
					'msgSuccess' => SuccessHandler::getMsgSuccess()
				]);

				return $response;
			});

			$subgroup->post('', function (Request $request, Response $response, array $args) {
				User::verifyLogin();
				$params = $request->getParsedBody();

				if (!is_numeric($params['idstatus']) || (int) $params['idstatus'] <= 0) {
					ErrorHandler::setMsgError('Informe o status atual!');
					return $response->withHeader('Location', "/admin/orders/{$args['idorder']}/status")->withStatus(302);
				}

				$order = new Order();
				$order->get($args['idorder']);
				$order->setIdstatus((int) $params['idstatus']);
				$order->save();

				SuccessHandler::setMsgSuccess('Status atualizado com sucesso!');
				return $response->withHeader('Location', "/admin/orders/{$args['idorder']}/status")->withStatus(302);
			});
		});

		$subgroup->get('/delete', function (Request $request, Response $response, array $args) {
			User::verifyLogin();

			$order = new Order();
			$order->get($args['idorder']);
			$order->delete();

			return $response->withHeader('Location', "/admin/orders")->withStatus(302);
		});

		$subgroup->get('', function (Request $request, Response $response, array $args) {
			User::verifyLogin();

			$order = new Order();
			$order->get($args['idorder']);

			$cart = $order->getCart();

			$page = new PageAdmin();
			$page->setTpl('order', [
				'order' => $order->getValues(),
				'cart' => $cart->getValues(),
				'products' => $cart->getProducts()
			]);

			return $response;
		});
	});

	$group->get('', function (Request $request, Response $response) {
		User::verifyLogin();
		$params = $request->getQueryParams();

		$search = (isset($params['search'])) ? htmlentities(trim(strip_tags($params['search'])), ENT_QUOTES) : '';
		$page = (isset($params['page'])) ? (int) $params['page'] : 1;
		$pagination = Order::getPageSearch($search, $page);
		$pages = [];

		for ($i = 1; $i <= $pagination['pages']; $i++) {
			$pages[] = [
				'href' => '/admin/orders?' . http_build_query([
					'page' => $i,
					'search' => $search
				]),
				'text' => $i
			];
		}

		$page = new PageAdmin();
		$page->setTpl('orders', [
			'orders' => $pagination['data'],
			'search' => $search,
			'pages' => $pages
		]);

		return $response;
	});
});
