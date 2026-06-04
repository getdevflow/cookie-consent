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
use Qubus\Http\Factories\HtmlResponseFactory;
use Qubus\Http\ServerRequest;
use ReflectionException;

use function App\Shared\Helpers\admin_url;
use function App\Shared\Helpers\current_user_can;
use function App\Shared\Helpers\get_option;
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

    /**
     * @return ResponseInterface
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws \Exception
     */
    public function config(): ResponseInterface
    {
        $config = get_option(key: 'icc_popup_options');

        if (is_string($config)) {
            $decoded = json_decode($config, true);
            $config = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (!is_array($config)) {
            $config = [];
        }

        $payload = json_encode(
            $config,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_APOS
            | JSON_HEX_AMP
            | JSON_HEX_QUOT
        );

        $response = HtmlResponseFactory::create($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
