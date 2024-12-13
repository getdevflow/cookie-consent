<?php

declare(strict_types=1);

namespace Plugin\CookieConsent;

use App\Infrastructure\Services\Plugin;
use App\Shared\Services\Registry;
use App\Shared\Services\Utils;
use Codefy\CommandBus\Exceptions\CommandPropertyNotFoundException;
use Codefy\Framework\Codefy;
use Codefy\QueryBus\UnresolvableQueryHandlerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Qubus\EventDispatcher\ActionFilter\Action;
use Qubus\EventDispatcher\ActionFilter\Filter;
use Qubus\Exception\Data\TypeException;
use Qubus\Exception\Exception;
use ReflectionException;

use function App\Shared\Helpers\add_plugins_submenu;
use function App\Shared\Helpers\cms_enqueue_css;
use function App\Shared\Helpers\cms_enqueue_js;
use function App\Shared\Helpers\load_plugin_textdomain;
use function App\Shared\Helpers\plugin_basename;
use function App\Shared\Helpers\plugin_dir_path;
use function App\Shared\Helpers\plugin_url;
use function dirname;
use function Qubus\Security\Helpers\esc_html__;
use function strpos;

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
            'version' => '1.0.0',
            'description' => 'Cookie Consent helps you comply with the EU regulations 
            regarding the usage of website cookies.',
            'basename' => plugin_basename(dirname(__FILE__)),
            'path' => plugin_dir_path(__FILE__),
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
        Action::getInstance()->addAction('plugins_loaded', [$this, 'setLocale']);
        Action::getInstance()->addAction('cms_head', [$this, 'enqueueFrontEndCss']);
        Action::getInstance()->addAction('cms_footer', [$this, 'enqueueFrontEndJs']);
        Action::getInstance()->addAction('cms_admin_head', [$this, 'enqueueBackEndCss']);
        Action::getInstance()->addAction('cms_admin_footer', [$this, 'enqueueBackEndJs']);
        Action::getInstance()->addAction('plugins_submenu', [$this, 'registerSubmenu']);
        Action::getInstance()->addAction('plugins_loaded', [$this, 'render']);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function setLocale(): void
    {
        load_plugin_textdomain($this->id(), $this->path() . 'locale');
    }

    /**
     * @return void
     * @throws CommandPropertyNotFoundException
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws TypeException
     * @throws UnresolvableQueryHandlerException
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
                asset: $this->url() . '/assets/css/cookieconsent.min.css',
                minify: false,
                pluginSlug: $this->id()
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
                asset: $this->url() . '/assets/js/cookieconsent.min.js',
                minify: false,
                pluginSlug: $this->id()
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
     * @throws ReflectionException
     */
    public function enqueueBackEndCss(): void
    {
        if (!Utils::isAdmin()) {
            return;
        }

        if (
            strpos(
                Utils::getPathInfo(
                    '/admin/plugin/' . $this->id() . '/'
                ),
                '/admin/plugin/' . $this->id() . '/'
            ) !== 0
        ) {
            return;
        }

        cms_enqueue_css(
            config: 'plugin',
            asset: $this->url() . '/assets/css/admin.css',
            minify: false,
            pluginSlug: $this->id()
        );
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     */
    public function enqueueBackEndJs(): void
    {
        if (!Utils::isAdmin()) {
            return;
        }

        if (
                strpos(
                    Utils::getPathInfo(
                        '/admin/plugin/' . $this->id() . '/'
                    ),
                    '/admin/plugin/' . $this->id() . '/'
                ) !== 0
        ) {
            return;
        }

        cms_enqueue_js(
            config: 'plugin',
            asset: $this->url() . '/assets/js/scripts.js',
            minify: false,
            pluginSlug: $this->id()
        );
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function render(): void
    {
        Filter::getInstance()->addFilter('plugin_route', function ($router) {
            $router->setDefaultNamespace('\\Plugin\\CookieConsent\\Controllers');
            $router->map(['GET', 'POST'], '/admin/plugin/cookie-consent/', 'CookieConsentController@index');
        }, 5);
    }
}
