<?php
class AZController extends Controller
{
    function index()
    {
        $alphabet = str_split("ABCDEFGHIKLMNOPQRSTUVWXYZ");
        $this->set("alphabet",$alphabet);
    }
    function letter()
    {
        $letter = $_GET['letter'];
        if( strlen( $letter) != 1 ) 
        {
            $this->redirect("AZ","index","0");
        }
        $query = "SELECT * FROM links WHERE `title` LIKE '$letter%'";
        $result = mysql_query( $query ) or die( mysql_error() );
        if( mysql_num_rows( $result ) == 0 )
        {
            if( $this->get_format() == "xml" )
                $this->redirect("AZ","noresult.xml","0");
            else
                $this->redirect( "AZ" , "index", "0");
        }
        $urls = array();
        while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) )
        {
            array_push( $urls, $row );
        }
        $this->set( "c_letter", $letter );
        $this->set( "urls", $urls );
        $alphabet = str_split("ABCDEFGHIKLMNOPQRSTUVWXYZ");
        $this->set("alphabet",$alphabet);
 
    }
    
    function noresult()
    {
    }
}
