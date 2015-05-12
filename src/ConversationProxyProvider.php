<?php

namespace ETNA\Silex\Provider\ConversationProxy;

use Guzzle\Http\Client;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ConversationProxyProvider implements ServiceProviderInterface
{
    private $controller_instance = null;

    public function __construct($controller_instance = null)
    {
        if (null !== $controller_instance) {
            if (false === is_subclass_of($controller_instance, "ETNA\Silex\Provider\ConversationProxy\DumbMethodsProxy")) {
                throw new \Exception("Controller given to ConversationProxyProvider have to inherit from DumbMethodsProxy");
            }
            $this->controller_instance = $controller_instance;
        } else {
            $this->controller_instance = new DumbMethodsProxy();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        return $app;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app["conversation_proxy"] = $app->share(function($app) {
            $conversation_api_url = getenv("CONVERSATION_API_URL");
            if (false === $conversation_api_url) {
                throw new \Exception("ConversationProxyProvider needs env var CONVERSATION_API_URL");
            }
            return new Client("{$conversation_api_url}", []);
        });

        $app["conversations"] = $app->share(function($app) {
            return new ConversationManager($app);
        });

        $app->mount("/", $this->controller_instance);
    }
}
