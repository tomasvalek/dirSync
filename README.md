dirSync
=======

A simple library for directory synchronization by a given JSON string with an option of a simple actions.

Info about Sleeping barber problem: http://en.wikipedia.org/wiki/Sleeping_barber_problem

Installation:
--------------
    $ git clone https://github.com/tomasvalek/dirSync.git

Requirement:
    PHP TODO

Using:
-------------
{
    /**
     * will create a directory "src" 
     */
    "src": null,

    /**
     * will create a directory "vendor" but the 
     * directory content is ignored
     */
    "vendor": false, 

    /*
     * will create a directory "log"
     */
    "log": { 

        /**
         * will create a directory "apache" in the "log" directory
         */
        "apache": null
    },

    /*
     * will create a directory "test"
     */
    "test": { 

        /**
         * will run the action copy with the parameters "['dst', 'src']" 
         * where the \DirSync instance does not care what
         * parameters are passed
         */
        "#copy": ['dst', 'src'] 
    }

    /**
     * directory which is not in JSON will be removed
     */
}
