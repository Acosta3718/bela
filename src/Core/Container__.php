<?php

namespace App\Core;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

class Container
{
    /** @var array<string, callable> */
    private array $definitions = [];

    /** @var array<string, mixed> */
    private array $resolved = [];

    public function set(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }

    public function has(string $id): bool
    {
        return isset($this->resolved[$id]) || isset($this->definitions[$id]) || class_exists($id);
    }

    public function get(string $id)
    {
        if (isset($this->resolved[$id])) {
            return $this->resolved[$id];
        }

        if (isset($this->definitions[$id])) {
            $this->resolved[$id] = $this->definitions[$id]($this);
            return $this->resolved[$id];
        }

        if (!class_exists($id)) {
            throw new \InvalidArgumentException("Servicio '{$id}' no encontrado en el contenedor.");
        }

        return $this->resolved[$id] = $this->build($id);
    }

    private function build(string $class)
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $exception) {
            throw new \RuntimeException("No se pudo construir la clase {$class}.", 0, $exception);
        }

        if (!$reflection->isInstantiable()) {
            throw new \RuntimeException("La clase {$class} no es instanciable.");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                throw new \RuntimeException(
                    sprintf('No se puede resolver la dependencia %s de la clase %s', $parameter->getName(), $class)
                );
            }

            $dependencies[] = $this->get($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}