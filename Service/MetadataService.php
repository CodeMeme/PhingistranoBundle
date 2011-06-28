<?php

namespace CodeMeme\PhingistranoBundle\Service;

use Symfony\Component\Config\FileLocator;

class MetadataService
{
    protected $metadata;
    
    public function __construct($buildfile = null)
    {
        try {
            $this->metadata = new \DomDocument;
            $this->metadata->load($buildfile);
        } catch(\Exception $e) {
            echo "Exception detected in " 
                 . get_class($this) . "::_construct \"" 
                 . $e->getMessage() . "\"";
        }
    }
    
    public function getProduction()
    {
        return $this->getInfo('production');
    }
    
    public function getStaging()
    {
        return $this->getInfo('staging');
    }
    
    public function getTesting()
    {
        return $this->getInfo('testing');
    }
    
    public function getInfo($env = 'production')
    {
        return array('Displaying information for '. $env,
            array(
            'credentials' => array(
                'user'    => $this->queryProperty('deploy.user', $env),
                'pass'    => $this->queryProperty('deploy.password', $env),
            ),
            'path'    => $this->queryProperty('deploy.path', $env),
            'servers' => $this->queryProperty('deploy.servers', $env),
        ));
    }
    
    protected function getTargets()
    {
        return $this->metadata->getElementsByTagName('target');
    }
    
    protected function getProperties()
    {
        return $this->metadata->getElementsByTagName('property');
    }
    
    public function getEnvironments()
    {
        $environments = array('production');
        foreach ($this->getTargets() as $target) {
            $name = $target->attributes->getNamedItem("name")->nodeValue;
            if (strpos($name, '.properties')) {
                $pieces = explode('.', $name);
                array_push($environments, $pieces[0]);
            }
        }
        return array('environments' => $environments);
    }
    
    public function queryProperty($propertyName, $env = 'production')
    {
        if ($env != 'production') {
            $result = $this->queryEnvProperty($env, $propertyName);
            if (!empty($result)) {
                return $result;
            }
        }
        
        foreach ($this->getProperties() as $property)
        {
            $name = $property->attributes->getNamedItem("name")->nodeValue;
            if (strpos($name, $propertyName) !== false) {
                $value = $property->attributes->getNamedItem("value")->nodeValue;
                $pieces = array_map('trim',explode(",", $value));
                if (count($pieces) > 1) {
                    return $pieces;
                } else if ($pieces[0]) {
                    return $pieces[0];
                } else {
                    return null;
                }
            }
        }
    } 
    
    public function queryEnvProperty($env = 'production', $propertyName)
    {
        //if not production look through the document for the env node
        foreach ($this->getTargets() as $target) {
            $name = $target->attributes->getNamedItem("name")->nodeValue;
            if (strpos($name, $env.'.properties') !== false) {
                $node = $target;
            }
        }
        
        //parse through properties only in this node
        foreach ($node->getElementsByTagName('property') as $property)
        {
            $name = $property->attributes->getNamedItem("name")->nodeValue;
            if (strpos($name, $propertyName) !== false) {
                $value = $property->attributes->getNamedItem("value")->nodeValue;
                $pieces = array_map('trim',explode(",", $value));
                if (count($pieces) > 1) {
                    return $pieces;
                } else if ($pieces[0]) {
                    return $pieces[0];
                } else {
                    return null;
                }
            }
        }
        
        return null;
    }
}