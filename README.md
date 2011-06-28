#JobqueueBundle#

##Overview##
This is a bundle intended for Symfony2 that intends to offer the ability to query your Phingistrano build.xml file for build related metadata.

Currently the metadata query is the only functionality that exists.

This library is a work in progress.

##Configuration##

###autoload.php###
Add the following to your autoload.php file:

    $loader->registerNamespaces(array(
        //...
        'CodeMeme'     => __DIR__.'/../vendor',
    ));

###AppKernel.php###
Add The bundle to your kernel bootstrap sequence

    public function registerBundles()
    {
        $bundles = array(
            //...
            new CodeMeme\PhingistranoBundle\PhingistranoBundle(),
        );
        //...

        return $bundles;
    }

###deps###
    [Phingistrano]
        git=http://github.com/CodeMeme/Phingistrano.git

    [PhingistranoBundle]
        git=http://github.com/CodeMeme/PhingistranoBundle.git
        

##Command Line Usage##

In the symfony2 command console you can use the phingistrano metadata service with the following command:

    $> app/console build:metadata production

    Displaying information for production
    	credentials:
    		user: myusername
    		pass: mypassword
    	path: /var/www/deployments/phingistrano/master
    	servers:
    		172.99.99.999
    		172.99.99.998


    
likewise there are other targets available 

    $> app/console build:metadata --help
    
    Usage:
     build:metadata [--property[="..."]] [--e[="..."]] target

### Arguments ###
     target      The method to run from the metadata service
     
####Targets####
    environments: list of environments specified in the metadata
    production: info about the production servers
    staging: info about the staging servers
    testing: info about the testing servers
    query: query any metadata property

#####Query#####
######Options######
     --property (required for query) Property name to query for in project metadata.
     --e (only used with query) specifies a specific environment to look for properties.

     app/console build:metadata query --property=deploy.servers --e=staging 

     
     	

    

