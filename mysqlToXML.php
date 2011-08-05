<?php

/* File         mysqlToXML.php (MODx snippet)
 * Created on   MAR 20, 2010
 * Project      shawn_wilkerson
 * @package     MODx Revolution Scripts
 * @version     2
 * @category    snippet
 * @author      W. Shawn Wilkerson
 * @link        http://www.shawnwilkerson.com
 * @copyright  Copyright (c) 2010, W. Shawn Wilkerson.  All rights reserved.
 * @license
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 ************************************************
 * Purpose: to utilize the xPDO 2.0 Generator.writeSchema function to parse existing
 * mysql database tables into xPDO schema files
 *
 * xPDO Documentation:
 * http://svn.modxcms.com/docs/display/xPDO20/xPDOGenerator.writeSchema (modified to become snippet call)
 *
 * Caution: Every execution of this snippet will overwrite files created with identical snippet calls.
 *
 * Examples:
 *      The following will output a schema to wsw/
 *          [[!convert?d=`wsw_projects`&package=`wsw_zipCode`&out=`wsw`]]
 *      The following will output a schema to MODX_BASE_PATH.'/schemas/
 *          [[!convert?d=`wsw_projects`&package=`wsw_zipCode`]]
 *      The following will create a schema on the full database, because prefix is not provided
 *          [[!convert?d=`wsw_projects`&package=`wsw_zipCode`&restrict="true"]]
 *      The following will create a schema on tables in the database matching the provided prefix
 *          [[!convert?d=`wsw_projects`&package=`wsw_zipCode`&prefix=`wsw_`&restrict="true"]]
 *
 * Requiremenets:
 *      Database name to parse to create schema, as shown with ?d= in the examples
 *      Package name to attach the schema to, as shown with &package in the examples
 *      Prefix name if user wants to restrict schema creation to only the tables with that prefix
 *
 */
if (!function_exists(ssPhpPrep))
{
    function ssPhpPrep($value)
    {
        /**
         *  To purposely alter anything remotely looking like source code
         *  to ModParser, apache, or php
         */
        $value= (strpos($value, "<") !== false) ? htmlentities($value) : $value;
        $value= str_replace("<", "&#060;", $value);
        $value= str_replace(">", "&#062;", $value);
        $value= str_replace("[", "&#091;", $value);
        $value= str_replace("]", "&#093;", $value);
        $value= str_replace("{", "&#123;", $value);
        $value= str_replace("}", "&#125;", $value);
        return $value;
    }
}

/**
 * User provided values
 ******************************************/

/**
 * @var string A user string which provides the database to parse. Required.
 */
if ($d)
{
    /* if $d is left empty and this blocker wasn' t in place we would simply be dumping the MODx schema * /
     */

    /**
    * @var $dsn defaults to current MODx revolution database: type, host, user, and password, but can be overridden by user with &t=` databaseType `, &h=` 10.0 .0 .87 `, &d=` dataBaseName ` respectively
    */
    $dsn= ($t) ? $t : 'mysql';
    $dsn .= ($h) ? ':host='.$h : ':host='.$modx->db->config['host'];
    $dsn .= ($d) ? ';dbname='.$d : ';dbname='.str_replace('` ', ' ', $modx->db->config[' dbase ']);

    /**
     * @var string A user string that stores the MODx database username or the username provided at snippet call with &amp;u=`user`.
     */
    $user= ($u) ? $u : $modx->db->config['user'];

    /**
     * @var string A user string that stores the MODx database password or the user provided password at snippet call with &amp;p=`password`.
     */
    $pass= ($p) ? $p : $modx->db->config['pass'];

    /**
    * @var string A user string which provides the package name (ie: wayfinder, randomimages). Required to create XML
    * .
    */

    if ($package)
    {

        /**
        * @var string A user string that stores system path to where the schema is to be written to. Does not use ending /. Defaults to schema directory, previously created by user in MODX_BASE_PATH (see /core/config/config.inc.php for location). All paths will be set under MODX_BASE_PATH to help prevent MODx and other files from being overwritten.
        */
        $out= ($out) ? ($out) : 'schemas';
        $file= '/'.$package.'.schema.xml';
        $schema= MODX_BASE_PATH.$out.$file;

        /**
         * @var string A user string that stores the user provided database table prefix with &amp;pre=`prefix_`.
         */
        $prefix= ($prefix) ? $prefix : '';
        if ($prefix === '')
        {

            /**
             * @var boolean 1|0 A user boolean that stores the user desire to restrict the schema to only the tables matching the prefix.
             * Defaults to using the entire database provided by user in snippet call.
             */
            $restrict= false;

            $msg= 'Warning: restrict set to false -- no prefix provided -- full schema settings active';
        } else
        {
            $restrict= ($restrict) ? $restrict : false;
        };
        /**
        * @var object An xPDO object
        */
        $xpdo= new xPDO($dsn, $user, $pass, $prefix);
        $xpdo->setLogLevel(xPDO :: LOG_LEVEL_INFO);
        $xpdo->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
        $manager= $xpdo->getManager();
        $generator= $manager->getGenerator();
        $xml= ($reloadxml === 1) ? '' : $generator->writeSchema($schema, $package, 'xPDOObject', $prefix, $restrict);
        if ($xml)
        {
            $msg .= ($msg) ? '<br />' : '';
            $msg .= 'Schema contents from '.$schema;
            /**
             * get the contents of the file so we can display it on the screen
             */
            $o .= file_get_contents($schema);
            $o= ssPhpPrep($o);
        } else
        {
            $msg .= ($msg) ? '<br />' : '';
            $msg .= '<br />Operation failed -- check the manager logs';
        }
    } else
    { # no package name
        $msg .= ($msg) ? '<br />' : '';
        $msg .= 'Package name required';
    }
} else
{ # no database
    $msg .= ($msg) ? '<br />' : '';
    $msg .= 'Database name required';
}
$modx->toPlaceholder('message', $msg, 'sw');
return $o;