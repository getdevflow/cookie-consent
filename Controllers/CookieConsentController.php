<?php

declare(strict_types=1);

namespace Plugin\CookieConsent\Controllers;

use App\Application\Devflow;
use App\Infrastructure\Persistence\Database;
use App\Infrastructure\Services\Options;
use App\Infrastructure\Services\UserAuth;
use Codefy\Framework\Http\BaseController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Qubus\Exception\Data\TypeException;
use Qubus\Exception\Exception;
use Qubus\Http\ServerRequest;
use Qubus\Http\Session\SessionException;
use Qubus\Http\Session\SessionService;
use Qubus\Routing\Router;
use Qubus\View\Renderer;
use ReflectionException;

use function App\Shared\Helpers\admin_url;
use function Qubus\Security\Helpers\t__;

class CookieConsentController extends BaseController
{
    public function __construct(
        SessionService $sessionService,
        Router $router,
        protected UserAuth $user,
        protected Database $dfdb,
        ?Renderer $view = null
    ) {
        parent::__construct($sessionService, $router, $view);
    }

    /**
     * @param ServerRequest $request
     * @return string|ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws SessionException
     * @throws TypeException
     */
    public function index(ServerRequest $request): string|ResponseInterface
    {
        if (false === $this->user->can(permissionName: 'manage:plugins', request: $request)) {
            Devflow::inst()::$APP->flash->error(
                message: t__(msgid: 'Access denied.', domain: 'cookie-consent')
            );

            return $this->redirect(admin_url());
        }

        if ($request->getMethod() === 'POST') {
            $update = Options::factory()->massUpdate($request->getParsedBody());

            if ($update === false) {
                Devflow::inst()::$APP->flash->error(
                    message: t__(msgid: 'Update error.', domain: 'cookie-consent')
                );
            } else {
                Devflow::inst()::$APP->flash->success(
                    message: t__(msgid: 'Updated successfully.', domain: 'cookie-consent')
                );
            }

            return $this->redirect($request->getServerParams()['HTTP_REFERER']);
        }

        return $this->view->render('plugin::CookieConsent/view/index');
    }
}
