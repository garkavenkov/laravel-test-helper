<?php

namespace TestHelper;

/**
 * Model manipulation helper
 */
class ModelHelper
{
    /**
     * Model's mamespace
     *
     * @var string
     */
    private $namespace;

    /**
     * Model
     *
     * @var string
     */
    private $model;

    /**
     * Override values
     *
     * @var array
     */
    private $attributes;

    /**
     * Factory states
     *
     * @var string
     */
    private $states;

    /**
     * Contructor
     *
     * @param string $namespace
     */
    public function __construct($namespace='')
    {        
        $this->attributes = [];
        $this->model = '';
        $this->states = '';

        $this->namespace = $this->parseNamespace($namespace);
    }

    /**
     * Parses namespace. Added endign slash if not exists
     *
     * @param string $namespace
     * @return string
     */
    private function parseNamespace($namespace)
    {
        if ($namespace) {
            if (substr($namespace, -1) != "\\") {
                $namespace = $namespace . "\\";
            }
        } else {
            $namespace = "App\\";
        }
        return $namespace;
    }

    /**
     * Sets model 
     *
     * @param string $model
     * @param string $namespace
     * @return ModelHelper
     */
    public function instance($model, $namespace='')
    {       
        $this->model = $namespace ? $this->parseNamespace($namespace).$model :  $this->namespace . $model;
        
        return $this;
    }

    /**
     * Makes associative array from instance
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @return array
     */
    private function  parseInstance($instance)
    {
        $className = (new \ReflectionClass(get_class($instance)))->getShortName();
        $className =  strtolower($className) . '_id';                    

        return [$className => $instance->id];
    }

    /**
     * Overrides attributes
     *
     * @param array $attributes
     * @return ModelHelper
     */
    public function override($attributes)
    {
        if (is_array($attributes)) {
            
            foreach($attributes as $key => $value) {
                
                if ($value instanceof \Illuminate\Database\Eloquent\Model) {                    
                    $this->attributes = array_merge($this->attributes ,$this->parseInstance($value));
                } else {
                    $this->attributes[$key] = $value;
                }
            }           
            
        } else {
            $this->attributes = array_merge($this->attributes ,$this->parseInstance($attributes));            
        }        
        
        return $this;
    }
    
    /**
     * Factory states
     *
     * @param string $states
     * @return ModelHelper
     */
    public function states($states = null)
    {
        $this->states = $states;
        return $this;
    }

    /**
     * Creates and persists model
     *
     * @param integer $count
     * @return mixed
     */
    public function create($count = null)
    {     
        if ($this->states) {
            $instance =  factory($this->model, $count)->states($this->states)->create($this->attributes);    
        } else {
            $instance =  factory($this->model, $count)->create($this->attributes);    
        }        
        
        $this->attributes = [];
        $this->states = '';

        return $instance;
    }

    /**
     * Creates and persists model. Ruturs model as an array
     *
     * @param integer $count
     * @return array
     */
    public function createArray($count = null)
    {
        return $this->create($count)->toArray();
    }

    /**
     * Makes and returns model
     *
     * @param integer $count
     * @return mixed
     */
    public function make($count = null)
    {
        $instance =  factory($this->model, $count)->make($this->attributes);
        $this->attributes = [];
        return $instance;

    }

    /**
     * Makes and returns model as an array
     *
     * @param integer $count
     * @return array
     */
    public function makeArray($count = null)
    {
        return $this->make($count)->toArray();
    }
}
