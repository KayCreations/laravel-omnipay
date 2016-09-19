<?php namespace Ignited\LaravelOmnipay;

use Closure;
use Omnipay\Common\GatewayFactory;
use Omnipay\Common\Helper;
use Omnipay\Common\CreditCard;

class LaravelOmnipayManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Omnipay Factory Instance
     *
     * @var \Omnipay\Common\GatewayFactory
     */
    protected $factory;

    /**
     * The current gateway to use
     *
     * @var string
     */
    protected $gateway;

    /**
     * The array of resolved queue connections.
     *
     * @var array
     */
    protected $gateways = [];

    /**
     * Create a new omnipay manager instance.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @param $factory
     */
    public function __construct($app, $factory)
    {
        $this->app = $app;
        $this->factory = $factory;
    }

    /**
     * Get an instance of the specified gateway
     * @param null $name
     * @param null $httpClient
     * @param null $httpRequest
     * @return \Omnipay\Common\AbstractGateway
     * @internal param of $index config array to use
     */
    public function gateway($name = null, $httpClient = null, $httpRequest = null)
    {
        $name = $name ?: $this->getGateway();

        if (!isset($this->gateways[$name])) {
            $this->gateways[$name] = $this->resolve($name, $httpClient, $httpRequest);
        }

        return $this->gateways[$name];
    }

    /**
     * @param string $name
     * @param null $httpClient
     * @param null $httpRequest
     * @return \Omnipay\Common\GatewayInterface
     */
    protected function resolve($name, $httpClient = null, $httpRequest = null)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new \UnexpectedValueException("Gateway [" . $name . "] is not defined.");
        }

        $gateway = $this->factory->create($config['driver'], $httpClient, $httpRequest);

        $class = trim(Helper::getGatewayClassName($config['driver']), "\\");

        $reflection = new \ReflectionClass($class);

        foreach ($config['options'] as $optionName => $value) {
            $method = 'set' . ucfirst($optionName);

            if ($reflection->hasMethod($method)) {
                $gateway->{$method}($value);
            }
        }

        return $gateway;
    }

    /**
     * @param $cardInput
     * @return CreditCard
     */
    public function creditCard($cardInput)
    {
        return new CreditCard($cardInput);
    }

    /**
     * @return mixed
     */
    protected function getDefault()
    {
        return $this->app['config']['omnipay.default'];
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function getConfig($name)
    {
        return $this->app['config']["omnipay.gateways.{$name}"];
    }

    /**
     * @return mixed|string
     */
    public function getGateway()
    {
        if (!isset($this->gateway)) {
            $this->gateway = $this->getDefault();
        }

        return $this->gateway;
    }

    /**
     * @param $name
     */
    public function setGateway($name)
    {
        $this->gateway = $name;
    }

    /**
     * @param $method
     * @param $parameters
     * @return \Omnipay\Common\AbstractGateway
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->gateway(), $method)) {
            return call_user_func_array([$this->gateway(), $method], $parameters);
        }

        throw new \BadMethodCallException("Method [" . $method . "] is not supported by the gateway [" . $this->gateway . "].");
    }
}