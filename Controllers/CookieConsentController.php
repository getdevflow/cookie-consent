<?php

declare(strict_types=1);

namespace Plugin\CookieConsent\Controllers;

use App\Application\Devflow;
use App\Infrastructure\Services\Options;
use Codefy\Framework\Http\BaseController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Qubus\Exception\Data\TypeException;
use Qubus\Exception\Exception;
use Qubus\Http\ServerRequest;
use ReflectionException;

use function App\Shared\Helpers\admin_url;
use function App\Shared\Helpers\current_user_can;
use function Codefy\Framework\Helpers\view;
use function Qubus\Security\Helpers\t__;

class CookieConsentController extends BaseController
{
    /**
     * @param ServerRequest $request
     * @return string|ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws TypeException
     * @throws \Exception
     */
    public function index(ServerRequest $request): string|ResponseInterface
    {
        if (false === current_user_can(perm: 'manage:plugins')) {
            Devflow::$PHP->flash->error(
                message: t__(msgid: 'Access denied.', domain: 'cookie-consent')
            );

            return $this->redirect(admin_url());
        }

        if ($request->getMethod() === 'POST') {
            $update = Options::factory()->massUpdate($request->getParsedBody());

            if ($update === false) {
                Devflow::$PHP->flash->error(
                    message: t__(msgid: 'Update error.', domain: 'cookie-consent')
                );
            } else {
                Devflow::$PHP->flash->success(
                    message: t__(msgid: 'Updated successfully.', domain: 'cookie-consent')
                );
            }

            return $this->redirect($request->getHeaderLine(name: 'Referer'));
        }

        return view('plugin::CookieConsent/view/index');
    }
}
