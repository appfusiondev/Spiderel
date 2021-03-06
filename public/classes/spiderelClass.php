<?php
class spiderel
{
    static public $mysql;
    static public $error;
    static public $config;
    static public $robots;
    static public $pagerank;
    static function load_config() {
        spiderel::$config = new Config();
    }
    static public function init() {
        $query = "TRUNCATE TABLE `links`";
        mysql_query( $query ) or die(mysql_error());
        $query = "TRUNCATE TABLE `reports`";
        mysql_query( $query ) or die(mysql_error());
        $url = spiderel::get_config("url");
        $browser = spiderel::get_config("agent");
        spiderel::$error = new Error();
        spiderel::$robots = new Robots();
        spiderel::$pagerank = new Pagerank;
        if (spiderel::test(spiderel::get_config('url'))) {
            spiderel::finish();
        } else {
            spiderel::$error->eexit("The url can't be accesed");
        }
    }
    static public function test($url) {
        $http = new HttpRequest;
        if ($http->get($url)) {
            return true; 
        } else {
            return false; 
        }
    }
    static public function crawl() {
        $q = new Queue;
        $q->add(spiderel::get_config('url'));
        $i = 0;
        while ($q->active != "0") {
            $i++;
            $nextUrl = $q->get_next();
            if($nextUrl != false) {
                $page = new Page($nextUrl);
                if ($page->status) {
            		$links = $page->get_links();
                    $page->add_to_db();
                    $id = $page->get_db_id();
                    $q->add_array($links);
                    spiderel::$pagerank->add_link($nextUrl,$links,$id);
                }
           }
        }
        spiderel::$pagerank->calculate();
    }
    static public function add_error($error)
    {
        spiderel::$error->add($error);

    }
    static public function get_config($key)
    {
        return self::$config->get($key);
    }
    static public function finish() {
        //  $errors = spiderel::$error->ereturn();    
        // foreach ($errors as $row) {
        //    echo $row . "<br>";
        //  }
    }
    static public function init_db() {
        include(ROOT . DS . "config" . DS . "mysql.php");
        mysql_connect($mysql_host, $mysql_user, $mysql_password) or die("Database connect failed");
        mysql_select_db($mysql_database) or die("Database load failed");
    }
    static public function create_tables()
    {
        $tables_file = ROOT . DS . "config" . DS . "tables.sql";
        $f = fopen( $tables_file, "r");
        $query = fread( $f, filesize( $tables_file) ) ;
        fclose( $f );

        $sql = explode(";",file_get_contents($tables_file));// 
        $errors = 0;
        foreach($sql as $query)
        {
            mysql_query($query) or $errors = 1;;
        }
        return true;
    }
    static public function add_invalid_response( $path, $status)
    {
        $type = "response: " . $status;
        $query = "INSERT INTO `reports` (
        `id` ,
        `path` ,
        `type`
        )
        VALUES (
        NULL , '" . $path . "', '" . $type . "'
        );";
        mysql_query( $query ) or die( mysql_error() );
    }
    
    static public function add_invalid_content( $path, $content )
    {
        $type = "content: " . $content;
        $query = "INSERT INTO `reports` (
        `id` ,
        `path` ,
        `type`
        )
        VALUES (
        NULL , '" . $path . "', '" . $type . "'
        );";
        mysql_query( $query ) or die( mysql_error() );
    }
 
}		
