<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

/*!
 @discussion XML parser class provides XML parsing OO functions. It server as a base
 for other classes, extending this one.
 */
abstract class XMLParser
{
    protected $parser = false;
    protected $positions = array();
    protected $path = '';

    public function __construct()
    {
        if ( function_exists( 'xml_parser_create' ) ) {
            $this->parser = xml_parser_create();
        }
    }

    /*!
     @abstract Parses the given data.
     @param data string - the data to be parsed.
     @result True if the data has been succesfully parsed.
     */
    public function parse($data)
    {
        // The following statements arent' located in the constructor
        // due to a PHP bug
        //
        xml_set_object( $this->parser, $this );
        $this->setOption(XML_OPTION_CASE_FOLDING, true );
        xml_set_element_handler( $this->parser, 'tagOpen', 'tagClose' );
        xml_set_character_data_handler( $this->parser, 'cdata' );
        //
        return ( $this->parser ? xml_parse( $this->parser, $data ) : false );
    }

    /*!
     @function tagOpen
     @abstract Open tag handler.
     @param parser integer - parser id.
     @param tag string - tag name.
     @param attributes array - tag attributes
     */
    public function tagOpen($parser, $tag, &$attributes)
    {
        if ( strcmp( $this->path, '' ) ) {
            $element = $this->structure[$this->path]['Components'];
            $this->structure[$this->path]['Components']++;
            $this->path .= ','.$element;
            //echo $this->path;
        } else {
            $element = 0;
            $this->path = '0';
        }
        $data = array(
                      'Tag'        => $tag,
                      'Components'   => 0,
                      'Attributes' => $attributes
        );
        $this->setelementdata( $this->path, $data );

        return( $this->parser ? $this->doTagOpen( $tag, $attributes ) : false );
    }

    // Close tag handler
    //
    public function tagClose($parser, $tag)
    {
        $this->path = ( ( $position = strrpos( $this->path, ',' ) ) ? substr( $this->path, 0, $position ) : '' );

        return( $this->parser ? $this->doTagClose( $tag ) : false );
    }

    // Character data handler
    //
    public function cdata($parser, $data)
    {
        $element = $this->structure[$this->path]['Components'];
        $previous = $this->path.','.strval( $element - 1 );
        if ( $element > 0 && GetType( $this->structure[$previous] ) == 'string' ) $this->structure[$previous] .= $data;
        else {
            $this->setelementdata( $this->path.','.$element, $data );
            $this->structure[$this->path]['Components']++;
        }

        return ( $this->parser ? $this->doCdata( $data ) : false );
    }

    // Sets element data
    //
    public function setElementData($path, $data)
    {
        $this->structure[$path] = $data;
    }

    // Sets a xml option
    //
    public function setOption($option, $value)
    {
        return ( $this->parser ? xml_parser_set_option( $this->parser, $option, $value ) : false );
    }

    // Gets a xml options
    //
    public function getOption($option)
    {
        return ( $this->parser ? xml_parser_get_option( $this->parser, $option ) : false );
    }

    // Frees the parser
    //
    public function free()
    {
        if ( $this->parser ) {
            if ( xml_parser_free( $this->parser ) ) {
                $this->parser = false;
                return true;
            } else return false;
        }
    }

    /*!
     @function _tagOpen
     */
    abstract public function doTagOpen($tag, $attrs);
    /*!
     @function _tagClose
     */
    abstract public function doTagClose($tag);

    /*!
     @function _cdata
     */
    abstract public function doCdata($data);

    public static function getChildren(
    $vals,
    &$i
    )
    {
        $children = array();

        if ( isset($vals[$i]['value'] ) and $vals[$i]['value'] ) array_push(
        $children,
        $vals[$i]['value']
        );

        while ( ++$i < count($vals) ) {
            switch ( $vals[$i]['type'] ) {
                case 'cdata':
                    array_push(
                    $children,
                    $vals[$i]['value']
                    );
                    break;

                case 'complete':
                    array_push(
                    $children,
                    array(
                    'tag' => $vals[$i]['tag'],
                    'attributes' => isset($vals[$i]['attributes'] ) ? $vals[$i]['attributes'] : '',
                    'value' => isset($vals[$i]['value'] ) ? $vals[$i]['value'] : ''
                    ) );
                    break;

                case 'open':
                    array_push(
                    $children,
                    array(
                    'tag' => $vals[$i]['tag'],
                    'attributes' => isset($vals[$i]['attributes'] ) ? $vals[$i]['attributes'] : '',
                    'children' => XMLParser::getChildren(
                    $vals,
                    $i
                    ) ) );
                    break;

                case 'close':
                    return $children;
            }
        }
    }

    public static function getXmlTree($data)
    {
        $p = xml_parser_create();
        xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);

        xml_parse_into_struct(
        $p,
        $data,
        $vals,
        $index
        );

        $error = xml_get_error_code( $p );
        if ( $error != XML_ERROR_NONE ) {
            $pieces = explode( "\n", $data );

            /*
            $errorstring = xml_error_string( $error );
            $linenumber = xml_get_current_line_number( $p );
            $linetext = $pieces[xml_get_current_line_number( $p ) - 1];
            */
        }

        xml_parser_free( $p );

        $tree = array();
        $i = 0;
        array_push(
        $tree,
        array(
            'tag' => $vals[$i]['tag'],
            'attributes' => isset($vals[$i]['attributes'] ) ? $vals[$i]['attributes'] : '',
            'children' => XMLParser::getChildren(
        $vals,
        $i
        ) ) );
        return $tree;
    }
}
