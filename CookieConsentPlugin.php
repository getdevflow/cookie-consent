<?php

declare(strict_types=1);

namespace Plugin\CookieConsent;

use App\Application\Devflow;
use App\Infrastructure\Services\Plugin;
use App\Shared\Services\Registry;
use App\Shared\Services\Utils;
use Plugin\CookieConsent\Controllers\CookieConsentController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Qubus\EventDispatcher\ActionFilter\Action;
use Qubus\Exception\Data\TypeException;
use Qubus\Exception\Exception;
use Qubus\Http\ServerRequest;
use Qubus\Routing\Exceptions\TooLateToAddNewRouteException;
use ReflectionException;

use function App\Shared\Helpers\add_plugins_submenu;
use function App\Shared\Helpers\cms_enqueue_css;
use function App\Shared\Helpers\cms_enqueue_js;
use function App\Shared\Helpers\plugin_basename;
use function App\Shared\Helpers\plugin_dir_path;
use function App\Shared\Helpers\plugin_url;
use function dirname;
use function Qubus\Security\Helpers\esc_html__;

final class CookieConsentPlugin extends Plugin
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function meta(): array
    {
        $plugin = [
            'name' => esc_html__(string: 'Cookie Consent', domain: 'cookie-consent'),
            'id' => 'cookie-consent',
            'author' => 'Joshua Parker',
            'version' => '2.0.0',
            'description' => 'Cookie Consent helps you comply with the EU regulations 
            regarding the usage of website cookies.',
            'basename' => plugin_basename(dirname(__FILE__)),
            'path' => plugin_dir_path(dirname(__FILE__)),
            'url' => plugin_url('', __CLASS__),
            'pluginUri' => 'https://github.com/getdevflow/cookie-consent',
            'authorUri' => 'https://nomadicjosh.com/',
            'className' => get_class($this),
        ];

        Registry::getInstance()->set('cc-plugin', $plugin);

        return $plugin;
    }

    /**
     * @throws ReflectionException
     */
    public function handle(): void
    {
        Action::getInstance()->addAction('cms_head', [$this, 'enqueueFrontEndCss']);
        Action::getInstance()->addAction('cms_footer', [$this, 'enqueueFrontEndJs']);
        Action::getInstance()->addAction('cms_admin_head', [$this, 'enqueueBackEndCss']);
        Action::getInstance()->addAction('cms_admin_footer', [$this, 'enqueueBackEndJs']);
        Action::getInstance()->addAction('plugins_submenu', [$this, 'registerSubmenu']);
        Action::getInstance()->addAction('plugins_loaded', [$this, 'render'], 2);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws TypeException
     */
    public function registerSubmenu(): void
    {
        echo add_plugins_submenu(
            menuTitle: $this->meta()['name'],
            menuRoute: 'plugin/' . $this->meta()['id'],
            screen: $this->meta()['id'],
            permission: 'manage:plugins'
        );
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function enqueueFrontEndCss(): void
    {
        if (Utils::isAdmin()) {
            return;
        }

        if ($this->option->read('icc_popup_enabled') === 1) {
            cms_enqueue_css(
                config: 'plugin',
                asset: $this->url() . '/css/cookieconsent.min.css',
                slug: $this->id()
            );
        }
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function enqueueFrontEndJs(): void
    {
        if (Utils::isAdmin()) {
            return;
        }

        if ($this->option->read('icc_popup_enabled') === 1) {
            cms_enqueue_js(
                config: 'plugin',
                asset: $this->url() . '/js/cookieconsent.min.js',
                slug: $this->id()
            );

            if ($this->option->read('icc_popup_enabled') === 1 && $this->option->read('icc_popup_options')) {
                if (Utils::isAdmin()) {
                    return;
                }
                $config = $this->option->read('icc_popup_options');
                echo '<script>window.cookieconsent.initialise(' . $config . ');</script>';
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function enqueueBackEndCss(): void
    {
        if (!Utils::isAdmin()) {
            return;
        }

        if (
                !str_starts_with(
                    Utils::getPathInfo(
                        '/admin/plugin/' . $this->id() . '/'
                    ),
                    '/admin/plugin/' . $this->id() . '/'
                )
        ) {
            return;
        }

        cms_enqueue_css(
            config: 'plugin',
            asset: $this->url() . '/css/admin.css',
            slug: $this->id()
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function enqueueBackEndJs(): void
    {
        if (!Utils::isAdmin()) {
            return;
        }

        if (
                !str_starts_with(
                    Utils::getPathInfo(
                        '/admin/plugin/' . $this->id() . '/'
                    ),
                    '/admin/plugin/' . $this->id() . '/'
                )
        ) {
            return;
        }

        cms_enqueue_js(
            config: 'plugin',
            asset: $this->url() . '/js/scripts.js',
            slug: $this->id()
        );
    }

    /**
     * @return void
     * @throws TooLateToAddNewRouteException
     */
    public function render(): void
    {
        $router = Devflow::$PHP->router;

        $router->map(['GET', 'POST'], '/admin/plugin/cookie-consent/', function (ServerRequest $request, CookieConsentController $controller) {
            return $controller->index($request);
        });
    }
}
