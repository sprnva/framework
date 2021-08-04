<?php

namespace App\Core\Database\Eloquent;

use App\Core\Database\Eloquent\Builder;

class Model
{
    protected $classModel;
    protected $namespace;
    protected $parentId;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct()
    {
        $this->classModel = end(explode("\\", get_class($this)));
        $this->namespace = "App\\Models\\{$this->classModel}";
    }

    public function query()
    {
        return new Builder($this->classModel, $this->namespace, $this->hasRelationTo);
    }

    public static function insert($form_data, $last_id = 'N')
    {
        $self = new static;
        return $self->query()->insert($form_data, $last_id);
    }

    public static function update($form_data, $where_clause = '')
    {
        $self = new static;
        return $self->query()->update($form_data, $where_clause);
    }

    public static function delete($where_clause = '')
    {
        $self = new static;
        return $self->query()->delete($where_clause);
    }

    public function __call($name, $arguments)
    {
        return $this->query()->{$name}($arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $self = new static;
        return $self->query()->{$name}($arguments);
    }

    // public static function listen()
    // {
    //     $self = new static;
    //     return $self->query()->listen();
    // }
}
