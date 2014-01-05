<?php
namespace Innomatic\Webservices\Xmlrpc;

// by Edd Dumbill (C) 1999-2002
// <edd@usefulinc.com>
// $Id: xmlrpc.inc,v 1.169 2008/03/06 18:47:24 ggiunta Exp $

// Copyright (c) 1999,2000,2002 Edd Dumbill.
// All rights reserved.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions
// are met:
//
//    * Redistributions of source code must retain the above copyright
//      notice, this list of conditions and the following disclaimer.
//
//    * Redistributions in binary form must reproduce the above
//      copyright notice, this list of conditions and the following
//      disclaimer in the documentation and/or other materials provided
//      with the distribution.
//
//    * Neither the name of the "XML-RPC for PHP" nor the names of its
//      contributors may be used to endorse or promote products derived
//      from this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
// "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
// LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
// FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
// REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
// (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
// HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
// STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
// ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
// OF THE POSSIBILITY OF SUCH DAMAGE.

    // G. Giunta 2005/01/29: declare global these variables,
    // so that xmlrpc.inc will work even if included from within a function
    // Milosch: 2005/08/07 - explicitly request these via $GLOBALS where used.
    $GLOBALS['xmlrpcI4']='i4';
    $GLOBALS['xmlrpcInt']='int';
    $GLOBALS['xmlrpcBoolean']='boolean';
    $GLOBALS['xmlrpcDouble']='double';
    $GLOBALS['xmlrpcString']='string';
    $GLOBALS['xmlrpcDateTime']='dateTime.iso8601';
    $GLOBALS['xmlrpcBase64']='base64';
    $GLOBALS['xmlrpcArray']='array';
    $GLOBALS['xmlrpcStruct']='struct';
    $GLOBALS['xmlrpcValue']='undefined';

    $GLOBALS['xmlrpcTypes']=array(
        $GLOBALS['xmlrpcI4']       => 1,
        $GLOBALS['xmlrpcInt']      => 1,
        $GLOBALS['xmlrpcBoolean']  => 1,
        $GLOBALS['xmlrpcString']   => 1,
        $GLOBALS['xmlrpcDouble']   => 1,
        $GLOBALS['xmlrpcDateTime'] => 1,
        $GLOBALS['xmlrpcBase64']   => 1,
        $GLOBALS['xmlrpcArray']    => 2,
        $GLOBALS['xmlrpcStruct']   => 3
    );

    $GLOBALS['xmlrpc_valid_parents'] = array(
        'VALUE' => array('MEMBER', 'DATA', 'PARAM', 'FAULT'),
        'BOOLEAN' => array('VALUE'),
        'I4' => array('VALUE'),
        'INT' => array('VALUE'),
        'STRING' => array('VALUE'),
        'DOUBLE' => array('VALUE'),
        'DATETIME.ISO8601' => array('VALUE'),
        'BASE64' => array('VALUE'),
        'MEMBER' => array('STRUCT'),
        'NAME' => array('MEMBER'),
        'DATA' => array('ARRAY'),
        'ARRAY' => array('VALUE'),
        'STRUCT' => array('VALUE'),
        'PARAM' => array('PARAMS'),
        'METHODNAME' => array('METHODCALL'),
        'PARAMS' => array('METHODCALL', 'METHODRESPONSE'),
        'FAULT' => array('METHODRESPONSE'),
        'NIL' => array('VALUE') // only used when extension activated
    );

    // define extra types for supporting NULL (useful for json or <NIL/>)
    $GLOBALS['xmlrpcNull']='null';
    $GLOBALS['xmlrpcTypes']['null']=1;

    // Not in use anymore since 2.0. Shall we remove it?
    /// @deprecated
    $GLOBALS['xmlEntities']=array(
        'amp'  => '&',
        'quot' => '"',
        'lt'   => '<',
        'gt'   => '>',
        'apos' => "'"
    );

    // tables used for transcoding different charsets into us-ascii xml

    $GLOBALS['xml_iso88591_Entities']=array();
    $GLOBALS['xml_iso88591_Entities']['in'] = array();
    $GLOBALS['xml_iso88591_Entities']['out'] = array();
    for ($i = 0; $i < 32; $i++)
    {
        $GLOBALS['xml_iso88591_Entities']['in'][] = chr($i);
        $GLOBALS['xml_iso88591_Entities']['out'][] = '&#'.$i.';';
    }
    for ($i = 160; $i < 256; $i++)
    {
        $GLOBALS['xml_iso88591_Entities']['in'][] = chr($i);
        $GLOBALS['xml_iso88591_Entities']['out'][] = '&#'.$i.';';
    }

    /// @todo add to iso table the characters from cp_1252 range, i.e. 128 to 159?
    /// These will NOT be present in true ISO-8859-1, but will save the unwary
    /// windows user from sending junk (though no luck when reciving them...)
  /*
    $GLOBALS['xml_cp1252_Entities']=array();
    for ($i = 128; $i < 160; $i++)
    {
        $GLOBALS['xml_cp1252_Entities']['in'][] = chr($i);
    }
    $GLOBALS['xml_cp1252_Entities']['out'] = array(
        '&#x20AC;', '?',        '&#x201A;', '&#x0192;',
        '&#x201E;', '&#x2026;', '&#x2020;', '&#x2021;',
        '&#x02C6;', '&#x2030;', '&#x0160;', '&#x2039;',
        '&#x0152;', '?',        '&#x017D;', '?',
        '?',        '&#x2018;', '&#x2019;', '&#x201C;',
        '&#x201D;', '&#x2022;', '&#x2013;', '&#x2014;',
        '&#x02DC;', '&#x2122;', '&#x0161;', '&#x203A;',
        '&#x0153;', '?',        '&#x017E;', '&#x0178;'
    );
  */

    $GLOBALS['xmlrpcerr'] = array(
    'unknown_method'=>1,
    'invalid_return'=>2,
    'incorrect_params'=>3,
    'introspect_unknown'=>4,
    'http_error'=>5,
    'no_data'=>6,
    'no_ssl'=>7,
    'curl_fail'=>8,
    'invalid_request'=>15,
    'no_curl'=>16,
    'server_error'=>17,
    'multicall_error'=>18,
    'multicall_notstruct'=>9,
    'multicall_nomethod'=>10,
    'multicall_notstring'=>11,
    'multicall_recursion'=>12,
    'multicall_noparams'=>13,
    'multicall_notarray'=>14,

    'cannot_decompress'=>103,
    'decompress_fail'=>104,
    'dechunk_fail'=>105,
    'server_cannot_decompress'=>106,
    'server_decompress_fail'=>107
    );

    $GLOBALS['xmlrpcstr'] = array(
    'unknown_method'=>'Unknown method',
    'invalid_return'=>'Invalid return payload: enable debugging to examine incoming payload',
    'incorrect_params'=>'Incorrect parameters passed to method',
    'introspect_unknown'=>"Can't introspect: method unknown",
    'http_error'=>"Didn't receive 200 OK from remote server.",
    'no_data'=>'No data received from server.',
    'no_ssl'=>'No SSL support compiled in.',
    'curl_fail'=>'CURL error',
    'invalid_request'=>'Invalid request payload',
    'no_curl'=>'No CURL support compiled in.',
    'server_error'=>'Internal server error',
    'multicall_error'=>'Received from server invalid multicall response',
    'multicall_notstruct'=>'system.multicall expected struct',
    'multicall_nomethod'=>'missing methodName',
    'multicall_notstring'=>'methodName is not a string',
    'multicall_recursion'=>'recursive system.multicall forbidden',
    'multicall_noparams'=>'missing params',
    'multicall_notarray'=>'params is not an array',

    'cannot_decompress'=>'Received from server compressed HTTP and cannot decompress',
    'decompress_fail'=>'Received from server invalid compressed HTTP',
    'dechunk_fail'=>'Received from server invalid chunked HTTP',
    'server_cannot_decompress'=>'Received from client compressed HTTP request and cannot decompress',
    'server_decompress_fail'=>'Received from client invalid compressed HTTP request'
    );

    // The charset encoding used by the server for received messages and
    // by the client for received responses when received charset cannot be determined
    // or is not supported
    $GLOBALS['xmlrpc_defencoding']='UTF-8';

    // The encoding used internally by PHP.
    // String values received as xml will be converted to this, and php strings will be converted to xml
    // as if having been coded with this
    $GLOBALS['xmlrpc_internalencoding']='ISO-8859-1';

    $GLOBALS['xmlrpcName']='XML-RPC for PHP';
    $GLOBALS['xmlrpcVersion']='2.2.1';

    // let user errors start at 800
    $GLOBALS['xmlrpcerruser']=800;
    // let XML parse errors start at 100
    $GLOBALS['xmlrpcerrxml']=100;

    // formulate backslashes for escaping regexp
    // Not in use anymore since 2.0. Shall we remove it?
    /// @deprecated
    $GLOBALS['xmlrpc_backslash']=chr(92).chr(92);

    // set to true to enable correct decoding of <NIL/> values
    $GLOBALS['xmlrpc_null_extension']=false;

    // used to store state during parsing
    // quick explanation of components:
    //   ac - used to accumulate values
    //   isf - used to indicate a parsing fault (2) or xmlrpcresp fault (1)
    //   isf_reason - used for storing xmlrpcresp fault string
    //   lv - used to indicate "looking for a value": implements
    //        the logic to allow values with no types to be strings
    //   params - used to store parameters in method calls
    //   method - used to store method name
    //   stack - array with genealogy of xml elements names:
    //           used to validate nesting of xmlrpc elements
    $GLOBALS['_xh']=null;

    /**
    * Convert a string to the correct XML representation in a target charset
    * To help correct communication of non-ascii chars inside strings, regardless
    * of the charset used when sending requests, parsing them, sending responses
    * and parsing responses, an option is to convert all non-ascii chars present in the message
    * into their equivalent 'charset entity'. Charset entities enumerated this way
    * are independent of the charset encoding used to transmit them, and all XML
    * parsers are bound to understand them.
    * Note that in the std case we are not sending a charset encoding mime type
    * along with http headers, so we are bound by RFC 3023 to emit strict us-ascii.
    *
    * @todo do a bit of basic benchmarking (strtr vs. str_replace)
    * @todo    make usage of iconv() or recode_string() or mb_string() where available
    */
    function xmlrpc_encode_entitites($data, $src_encoding='', $dest_encoding='')
    {
        if ($src_encoding == '')
        {
            // lame, but we know no better...
            $src_encoding = $GLOBALS['xmlrpc_internalencoding'];
        }

        switch(strtoupper($src_encoding.'_'.$dest_encoding))
        {
            case 'ISO-8859-1_':
            case 'ISO-8859-1_US-ASCII':
                $escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                $escaped_data = str_replace($GLOBALS['xml_iso88591_Entities']['in'], $GLOBALS['xml_iso88591_Entities']['out'], $escaped_data);
                break;
            case 'ISO-8859-1_UTF-8':
                $escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                $escaped_data = utf8_encode($escaped_data);
                break;
            case 'ISO-8859-1_ISO-8859-1':
            case 'US-ASCII_US-ASCII':
            case 'US-ASCII_UTF-8':
            case 'US-ASCII_':
            case 'US-ASCII_ISO-8859-1':
            case 'UTF-8_UTF-8':
            //case 'CP1252_CP1252':
                $escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                break;
            case 'UTF-8_':
            case 'UTF-8_US-ASCII':
            case 'UTF-8_ISO-8859-1':
    // NB: this will choke on invalid UTF-8, going most likely beyond EOF
    $escaped_data = '';
    // be kind to users creating string xmlrpcvals out of different php types
    $data = (string) $data;
    $ns = strlen ($data);
    for ($nn = 0; $nn < $ns; $nn++)
    {
        $ch = $data[$nn];
        $ii = ord($ch);
        //1 7 0bbbbbbb (127)
        if ($ii < 128)
        {
            /// @todo shall we replace this with a (supposedly) faster str_replace?
            switch($ii){
                case 34:
                    $escaped_data .= '&quot;';
                    break;
                case 38:
                    $escaped_data .= '&amp;';
                    break;
                case 39:
                    $escaped_data .= '&apos;';
                    break;
                case 60:
                    $escaped_data .= '&lt;';
                    break;
                case 62:
                    $escaped_data .= '&gt;';
                    break;
                default:
                    $escaped_data .= $ch;
            } // switch
        }
        //2 11 110bbbbb 10bbbbbb (2047)
        else if ($ii>>5 == 6)
        {
            $b1 = ($ii & 31);
            $ii = ord($data[$nn+1]);
            $b2 = ($ii & 63);
            $ii = ($b1 * 64) + $b2;
            $ent = sprintf ('&#%d;', $ii);
            $escaped_data .= $ent;
            $nn += 1;
        }
        //3 16 1110bbbb 10bbbbbb 10bbbbbb
        else if ($ii>>4 == 14)
        {
            $b1 = ($ii & 31);
            $ii = ord($data[$nn+1]);
            $b2 = ($ii & 63);
            $ii = ord($data[$nn+2]);
            $b3 = ($ii & 63);
            $ii = ((($b1 * 64) + $b2) * 64) + $b3;
            $ent = sprintf ('&#%d;', $ii);
            $escaped_data .= $ent;
            $nn += 2;
        }
        //4 21 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
        else if ($ii>>3 == 30)
        {
            $b1 = ($ii & 31);
            $ii = ord($data[$nn+1]);
            $b2 = ($ii & 63);
            $ii = ord($data[$nn+2]);
            $b3 = ($ii & 63);
            $ii = ord($data[$nn+3]);
            $b4 = ($ii & 63);
            $ii = ((((($b1 * 64) + $b2) * 64) + $b3) * 64) + $b4;
            $ent = sprintf ('&#%d;', $ii);
            $escaped_data .= $ent;
            $nn += 3;
        }
    }
                break;
/*
            case 'CP1252_':
            case 'CP1252_US-ASCII':
                $escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                $escaped_data = str_replace($GLOBALS['xml_iso88591_Entities']['in'], $GLOBALS['xml_iso88591_Entities']['out'], $escaped_data);
                $escaped_data = str_replace($GLOBALS['xml_cp1252_Entities']['in'], $GLOBALS['xml_cp1252_Entities']['out'], $escaped_data);
                break;
            case 'CP1252_UTF-8':
                $escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                /// @todo we could use real UTF8 chars here instead of xml entities... (note that utf_8 encode all allone will NOT convert them)
                $escaped_data = str_replace($GLOBALS['xml_cp1252_Entities']['in'], $GLOBALS['xml_cp1252_Entities']['out'], $escaped_data);
                $escaped_data = utf8_encode($escaped_data);
                break;
            case 'CP1252_ISO-8859-1':
                $escaped_data = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), $data);
                // we might as well replave all funky chars with a '?' here, but we are kind and leave it to the receiving application layer to decide what to do with these weird entities...
                $escaped_data = str_replace($GLOBALS['xml_cp1252_Entities']['in'], $GLOBALS['xml_cp1252_Entities']['out'], $escaped_data);
                break;
*/
            default:
                $escaped_data = '';
                error_log("Converting from $src_encoding to $dest_encoding: not supported...");
        }
        return $escaped_data;
    }

    /// xml parser handler function for opening element tags
    function xmlrpc_se($parser, $name, $attrs, $accept_single_vals=false)
    {
        // if invalid xmlrpc already detected, skip all processing
        if ($GLOBALS['_xh']['isf'] < 2)
        {
            // check for correct element nesting
            // top level element can only be of 2 types
            /// @todo optimization creep: save this check into a bool variable, instead of using count() every time:
            ///       there is only a single top level element in xml anyway
            if (count($GLOBALS['_xh']['stack']) == 0)
            {
                if ($name != 'METHODRESPONSE' && $name != 'METHODCALL' && (
                    $name != 'VALUE' && !$accept_single_vals))
                {
                    $GLOBALS['_xh']['isf'] = 2;
                    $GLOBALS['_xh']['isf_reason'] = 'missing top level xmlrpc element';
                    return;
                }
                else
                {
                    $GLOBALS['_xh']['rt'] = strtolower($name);
                }
            }
            else
            {
                // not top level element: see if parent is OK
                $parent = end($GLOBALS['_xh']['stack']);
                if (!array_key_exists($name, $GLOBALS['xmlrpc_valid_parents']) || !in_array($parent, $GLOBALS['xmlrpc_valid_parents'][$name]))
                {
                    $GLOBALS['_xh']['isf'] = 2;
                    $GLOBALS['_xh']['isf_reason'] = "xmlrpc element $name cannot be child of $parent";
                    return;
                }
            }

            switch($name)
            {
                // optimize for speed switch cases: most common cases first
                case 'VALUE':
                    /// @todo we could check for 2 VALUE elements inside a MEMBER or PARAM element
                    $GLOBALS['_xh']['vt']='value'; // indicator: no value found yet
                    $GLOBALS['_xh']['ac']='';
                    $GLOBALS['_xh']['lv']=1;
                    $GLOBALS['_xh']['php_class']=null;
                    break;
                case 'I4':
                case 'INT':
                case 'STRING':
                case 'BOOLEAN':
                case 'DOUBLE':
                case 'DATETIME.ISO8601':
                case 'BASE64':
                    if ($GLOBALS['_xh']['vt']!='value')
                    {
                        //two data elements inside a value: an error occurred!
                        $GLOBALS['_xh']['isf'] = 2;
                        $GLOBALS['_xh']['isf_reason'] = "$name element following a {$GLOBALS['_xh']['vt']} element inside a single value";
                        return;
                    }
                    $GLOBALS['_xh']['ac']=''; // reset the accumulator
                    break;
                case 'STRUCT':
                case 'ARRAY':
                    if ($GLOBALS['_xh']['vt']!='value')
                    {
                        //two data elements inside a value: an error occurred!
                        $GLOBALS['_xh']['isf'] = 2;
                        $GLOBALS['_xh']['isf_reason'] = "$name element following a {$GLOBALS['_xh']['vt']} element inside a single value";
                        return;
                    }
                    // create an empty array to hold child values, and push it onto appropriate stack
                    $cur_val = array();
                    $cur_val['values'] = array();
                    $cur_val['type'] = $name;
                    // check for out-of-band information to rebuild php objs
                    // and in case it is found, save it
                    if (@isset($attrs['PHP_CLASS']))
                    {
                        $cur_val['php_class'] = $attrs['PHP_CLASS'];
                    }
                    $GLOBALS['_xh']['valuestack'][] = $cur_val;
                    $GLOBALS['_xh']['vt']='data'; // be prepared for a data element next
                    break;
                case 'DATA':
                    if ($GLOBALS['_xh']['vt']!='data')
                    {
                        //two data elements inside a value: an error occurred!
                        $GLOBALS['_xh']['isf'] = 2;
                        $GLOBALS['_xh']['isf_reason'] = "found two data elements inside an array element";
                        return;
                    }
                case 'METHODCALL':
                case 'METHODRESPONSE':
                case 'PARAMS':
                    // valid elements that add little to processing
                    break;
                case 'METHODNAME':
                case 'NAME':
                    /// @todo we could check for 2 NAME elements inside a MEMBER element
                    $GLOBALS['_xh']['ac']='';
                    break;
                case 'FAULT':
                    $GLOBALS['_xh']['isf']=1;
                    break;
                case 'MEMBER':
                    $GLOBALS['_xh']['valuestack'][count($GLOBALS['_xh']['valuestack'])-1]['name']=''; // set member name to null, in case we do not find in the xml later on
                    //$GLOBALS['_xh']['ac']='';
                    // Drop trough intentionally
                case 'PARAM':
                    // clear value type, so we can check later if no value has been passed for this param/member
                    $GLOBALS['_xh']['vt']=null;
                    break;
                case 'NIL':
                    if ($GLOBALS['xmlrpc_null_extension'])
                    {
                        if ($GLOBALS['_xh']['vt']!='value')
                        {
                            //two data elements inside a value: an error occurred!
                            $GLOBALS['_xh']['isf'] = 2;
                            $GLOBALS['_xh']['isf_reason'] = "$name element following a {$GLOBALS['_xh']['vt']} element inside a single value";
                            return;
                        }
                        $GLOBALS['_xh']['ac']=''; // reset the accumulator
                        break;
                    }
                    // we do not support the <NIL/> extension, so
                    // drop through intentionally
                default:
                    /// INVALID ELEMENT: RAISE ISF so that it is later recognized!!!
                    $GLOBALS['_xh']['isf'] = 2;
                    $GLOBALS['_xh']['isf_reason'] = "found not-xmlrpc xml element $name";
                    break;
            }

            // Save current element name to stack, to validate nesting
            $GLOBALS['_xh']['stack'][] = $name;

            /// @todo optimization creep: move this inside the big switch() above
            if($name!='VALUE')
            {
                $GLOBALS['_xh']['lv']=0;
            }
        }
    }

    /// Used in decoding xml chunks that might represent single xmlrpc values
    function xmlrpc_se_any($parser, $name, $attrs)
    {
        xmlrpc_se($parser, $name, $attrs, true);
    }

    /// xml parser handler function for close element tags
    function xmlrpc_ee($parser, $name, $rebuild_xmlrpcvals = true)
    {
        if ($GLOBALS['_xh']['isf'] < 2)
        {
            // push this element name from stack
            // NB: if XML validates, correct opening/closing is guaranteed and
            // we do not have to check for $name == $curr_elem.
            // we also checked for proper nesting at start of elements...
            $curr_elem = array_pop($GLOBALS['_xh']['stack']);

            switch($name)
            {
                case 'VALUE':
                    // This if() detects if no scalar was inside <VALUE></VALUE>
                    if ($GLOBALS['_xh']['vt']=='value')
                    {
                        $GLOBALS['_xh']['value']=$GLOBALS['_xh']['ac'];
                        $GLOBALS['_xh']['vt']=$GLOBALS['xmlrpcString'];
                    }

                    if ($rebuild_xmlrpcvals)
                    {
                        // build the xmlrpc val out of the data received, and substitute it
                        $temp = new XmlRpcVal($GLOBALS['_xh']['value'], $GLOBALS['_xh']['vt']);
                        // in case we got info about underlying php class, save it
                        // in the object we're rebuilding
                        if (isset($GLOBALS['_xh']['php_class']))
                            $temp->_php_class = $GLOBALS['_xh']['php_class'];
                        // check if we are inside an array or struct:
                        // if value just built is inside an array, let's move it into array on the stack
                        $vscount = count($GLOBALS['_xh']['valuestack']);
                        if ($vscount && $GLOBALS['_xh']['valuestack'][$vscount-1]['type']=='ARRAY')
                        {
                            $GLOBALS['_xh']['valuestack'][$vscount-1]['values'][] = $temp;
                        }
                        else
                        {
                            $GLOBALS['_xh']['value'] = $temp;
                        }
                    }
                    else
                    {
                        /// @todo this needs to treat correctly php-serialized objects,
                        /// since std deserializing is done by php_xmlrpc_decode,
                        /// which we will not be calling...
                        if (isset($GLOBALS['_xh']['php_class']))
                        {
                        }

                        // check if we are inside an array or struct:
                        // if value just built is inside an array, let's move it into array on the stack
                        $vscount = count($GLOBALS['_xh']['valuestack']);
                        if ($vscount && $GLOBALS['_xh']['valuestack'][$vscount-1]['type']=='ARRAY')
                        {
                            $GLOBALS['_xh']['valuestack'][$vscount-1]['values'][] = $GLOBALS['_xh']['value'];
                        }
                    }
                    break;
                case 'BOOLEAN':
                case 'I4':
                case 'INT':
                case 'STRING':
                case 'DOUBLE':
                case 'DATETIME.ISO8601':
                case 'BASE64':
                    $GLOBALS['_xh']['vt']=strtolower($name);
                    /// @todo: optimization creep - remove the if/elseif cycle below
                    /// since the case() in which we are already did that
                    if ($name=='STRING')
                    {
                        $GLOBALS['_xh']['value']=$GLOBALS['_xh']['ac'];
                    }
                    else if ($name=='DATETIME.ISO8601')
                    {
                        if (!preg_match('/^[0-9]{8}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $GLOBALS['_xh']['ac']))
                        {
                            error_log('XML-RPC: invalid value received in DATETIME: '.$GLOBALS['_xh']['ac']);
                        }
                        $GLOBALS['_xh']['vt']=$GLOBALS['xmlrpcDateTime'];
                        $GLOBALS['_xh']['value']=$GLOBALS['_xh']['ac'];
                    }
                    else if ($name=='BASE64')
                    {
                        /// @todo check for failure of base64 decoding / catch warnings
                        $GLOBALS['_xh']['value']=base64_decode($GLOBALS['_xh']['ac']);
                    }
                    else if ($name=='BOOLEAN')
                    {
                        // special case here: we translate boolean 1 or 0 into PHP
                        // constants true or false.
                        // Strings 'true' and 'false' are accepted, even though the
                        // spec never mentions them (see eg. Blogger api docs)
                        // NB: this simple checks helps a lot sanitizing input, ie no
                        // security problems around here
                        if ($GLOBALS['_xh']['ac']=='1' || strcasecmp($GLOBALS['_xh']['ac'], 'true') == 0)
                        {
                            $GLOBALS['_xh']['value']=true;
                        }
                        else
                        {
                            // log if receiveing something strange, even though we set the value to false anyway
                            if ($GLOBALS['_xh']['ac']!='0' && strcasecmp($_xh[$parser]['ac'], 'false') != 0)
                                error_log('XML-RPC: invalid value received in BOOLEAN: '.$GLOBALS['_xh']['ac']);
                            $GLOBALS['_xh']['value']=false;
                        }
                    }
                    else if ($name=='DOUBLE')
                    {
                        // we have a DOUBLE
                        // we must check that only 0123456789-.<space> are characters here
                        // NOTE: regexp could be much stricter than this...
                        if (!preg_match('/^[+-eE0123456789 \t.]+$/', $GLOBALS['_xh']['ac']))
                        {
                            /// @todo: find a better way of throwing an error than this!
                            error_log('XML-RPC: non numeric value received in DOUBLE: '.$GLOBALS['_xh']['ac']);
                            $GLOBALS['_xh']['value']='ERROR_NON_NUMERIC_FOUND';
                        }
                        else
                        {
                            // it's ok, add it on
                            $GLOBALS['_xh']['value']=(double)$GLOBALS['_xh']['ac'];
                        }
                    }
                    else
                    {
                        // we have an I4/INT
                        // we must check that only 0123456789-<space> are characters here
                        if (!preg_match('/^[+-]?[0123456789 \t]+$/', $GLOBALS['_xh']['ac']))
                        {
                            /// @todo find a better way of throwing an error than this!
                            error_log('XML-RPC: non numeric value received in INT: '.$GLOBALS['_xh']['ac']);
                            $GLOBALS['_xh']['value']='ERROR_NON_NUMERIC_FOUND';
                        }
                        else
                        {
                            // it's ok, add it on
                            $GLOBALS['_xh']['value']=(int)$GLOBALS['_xh']['ac'];
                        }
                    }
                    //$GLOBALS['_xh']['ac']=''; // is this necessary?
                    $GLOBALS['_xh']['lv']=3; // indicate we've found a value
                    break;
                case 'NAME':
                    $GLOBALS['_xh']['valuestack'][count($GLOBALS['_xh']['valuestack'])-1]['name'] = $GLOBALS['_xh']['ac'];
                    break;
                case 'MEMBER':
                    //$GLOBALS['_xh']['ac']=''; // is this necessary?
                    // add to array in the stack the last element built,
                    // unless no VALUE was found
                    if ($GLOBALS['_xh']['vt'])
                    {
                        $vscount = count($GLOBALS['_xh']['valuestack']);
                        $GLOBALS['_xh']['valuestack'][$vscount-1]['values'][$GLOBALS['_xh']['valuestack'][$vscount-1]['name']] = $GLOBALS['_xh']['value'];
                    } else
                        error_log('XML-RPC: missing VALUE inside STRUCT in received xml');
                    break;
                case 'DATA':
                    //$GLOBALS['_xh']['ac']=''; // is this necessary?
                    $GLOBALS['_xh']['vt']=null; // reset this to check for 2 data elements in a row - even if they're empty
                    break;
                case 'STRUCT':
                case 'ARRAY':
                    // fetch out of stack array of values, and promote it to current value
                    $curr_val = array_pop($GLOBALS['_xh']['valuestack']);
                    $GLOBALS['_xh']['value'] = $curr_val['values'];
                    $GLOBALS['_xh']['vt']=strtolower($name);
                    if (isset($curr_val['php_class']))
                    {
                        $GLOBALS['_xh']['php_class'] = $curr_val['php_class'];
                    }
                    break;
                case 'PARAM':
                    // add to array of params the current value,
                    // unless no VALUE was found
                    if ($GLOBALS['_xh']['vt'])
                    {
                        $GLOBALS['_xh']['params'][]=$GLOBALS['_xh']['value'];
                        $GLOBALS['_xh']['pt'][]=$GLOBALS['_xh']['vt'];
                    }
                    else
                        error_log('XML-RPC: missing VALUE inside PARAM in received xml');
                    break;
                case 'METHODNAME':
                    $GLOBALS['_xh']['method']=preg_replace('/^[\n\r\t ]+/', '', $GLOBALS['_xh']['ac']);
                    break;
                case 'NIL':
                    if ($GLOBALS['xmlrpc_null_extension'])
                    {
                        $GLOBALS['_xh']['vt']='null';
                        $GLOBALS['_xh']['value']=null;
                        $GLOBALS['_xh']['lv']=3;
                        break;
                    }
                    // drop through intentionally if nil extension not enabled
                case 'PARAMS':
                case 'FAULT':
                case 'METHODCALL':
                case 'METHORESPONSE':
                    break;
                default:
                    // End of INVALID ELEMENT!
                    // shall we add an assert here for unreachable code???
                    break;
            }
        }
    }

    /// Used in decoding xmlrpc requests/responses without rebuilding xmlrpc values
    function xmlrpc_ee_fast($parser, $name)
    {
        xmlrpc_ee($parser, $name, false);
    }

    /// xml parser handler function for character data
    function xmlrpc_cd($parser, $data)
    {
        // skip processing if xml fault already detected
        if ($GLOBALS['_xh']['isf'] < 2)
        {
            // "lookforvalue==3" means that we've found an entire value
            // and should discard any further character data
            if($GLOBALS['_xh']['lv']!=3)
            {
                // G. Giunta 2006-08-23: useless change of 'lv' from 1 to 2
                //if($GLOBALS['_xh']['lv']==1)
                //{
                    // if we've found text and we're just in a <value> then
                    // say we've found a value
                    //$GLOBALS['_xh']['lv']=2;
                //}
                // we always initialize the accumulator before starting parsing, anyway...
                //if(!@isset($GLOBALS['_xh']['ac']))
                //{
                //    $GLOBALS['_xh']['ac'] = '';
                //}
                $GLOBALS['_xh']['ac'].=$data;
            }
        }
    }

    /// xml parser handler function for 'other stuff', ie. not char data or
    /// element start/end tag. In fact it only gets called on unknown entities...
    function xmlrpc_dh($parser, $data)
    {
        // skip processing if xml fault already detected
        if ($GLOBALS['_xh']['isf'] < 2)
        {
            if(substr($data, 0, 1) == '&' && substr($data, -1, 1) == ';')
            {
                // G. Giunta 2006-08-25: useless change of 'lv' from 1 to 2
                //if($GLOBALS['_xh']['lv']==1)
                //{
                //    $GLOBALS['_xh']['lv']=2;
                //}
                $GLOBALS['_xh']['ac'].=$data;
            }
        }
        return true;
    }

    class xmlrpc_client
    {
        var $path;
        var $server;
        var $port=0;
        var $method='http';
        var $errno;
        var $errstr;
        var $debug=0;
        var $username='';
        var $password='';
        var $authtype=1;
        var $cert='';
        var $certpass='';
        var $cacert='';
        var $cacertdir='';
        var $key='';
        var $keypass='';
        var $verifypeer=true;
        var $verifyhost=1;
        var $no_multicall=false;
        var $proxy='';
        var $proxyport=0;
        var $proxy_user='';
        var $proxy_pass='';
        var $proxy_authtype=1;
        var $cookies=array();
        /**
        * List of http compression methods accepted by the client for responses.
        * NB: PHP supports deflate, gzip compressions out of the box if compiled w. zlib
        *
        * NNB: you can set it to any non-empty array for HTTP11 and HTTPS, since
        * in those cases it will be up to CURL to decide the compression methods
        * it supports. You might check for the presence of 'zlib' in the output of
        * curl_version() to determine wheter compression is supported or not
        */
        var $accepted_compression = array();
        /**
        * Name of compression scheme to be used for sending requests.
        * Either null, gzip or deflate
        */
        var $request_compression = '';
        /**
        * CURL handle: used for keep-alive connections (PHP 4.3.8 up, see:
        * http://curl.haxx.se/docs/faq.html#7.3)
        */
        var $xmlrpc_curl_handle = null;
        /// Wheter to use persistent connections for http 1.1 and https
        var $keepalive = false;
        /// Charset encodings that can be decoded without problems by the client
        var $accepted_charset_encodings = array();
        /// Charset encoding to be used in serializing request. NULL = use ASCII
        var $request_charset_encoding = '';
        /**
        * Decides the content of xmlrpcresp objects returned by calls to send()
        * valid strings are 'xmlrpcvals', 'phpvals' or 'xml'
        */
        var $return_type = 'xmlrpcvals';

        /**
        * @param string $path either the complete server URL or the PATH part of the xmlrc server URL, e.g. /xmlrpc/server.php
        * @param string $server the server name / ip address
        * @param integer $port the port the server is listening on, defaults to 80 or 443 depending on protocol used
        * @param string $method the http protocol variant: defaults to 'http', 'https' and 'http11' can be used if CURL is installed
        */
        function __construct($path, $server='', $port='', $method='')
        {
            // allow user to specify all params in $path
            if($server == '' and $port == '' and $method == '')
            {
                $parts = parse_url($path);
                $server = $parts['host'];
                $path = isset($parts['path']) ? $parts['path'] : '';
                if(isset($parts['query']))
                {
                    $path .= '?'.$parts['query'];
                }
                if(isset($parts['fragment']))
                {
                    $path .= '#'.$parts['fragment'];
                }
                if(isset($parts['port']))
                {
                    $port = $parts['port'];
                }
                if(isset($parts['scheme']))
                {
                    $method = $parts['scheme'];
                }
                if(isset($parts['user']))
                {
                    $this->username = $parts['user'];
                }
                if(isset($parts['pass']))
                {
                    $this->password = $parts['pass'];
                }
            }
            if($path == '' || $path[0] != '/')
            {
                $this->path='/'.$path;
            }
            else
            {
                $this->path=$path;
            }
            $this->server=$server;
            if($port != '')
            {
                $this->port=$port;
            }
            if($method != '')
            {
                $this->method=$method;
            }

            // if ZLIB is enabled, let the client by default accept compressed responses
            if(function_exists('gzinflate') || (
                function_exists('curl_init') && (($info = curl_version()) &&
                ((is_string($info) && strpos($info, 'zlib') !== null) || isset($info['libz_version'])))
            ))
            {
                $this->accepted_compression = array('gzip', 'deflate');
            }

            // keepalives: enabled by default ONLY for PHP >= 4.3.8
            // (see http://curl.haxx.se/docs/faq.html#7.3)
            if(version_compare(phpversion(), '4.3.8') >= 0)
            {
                $this->keepalive = true;
            }

            // by default the xml parser can support these 3 charset encodings
            $this->accepted_charset_encodings = array('UTF-8', 'ISO-8859-1', 'US-ASCII');
        }

        /**
        * Enables/disables the echoing to screen of the xmlrpc responses received
        * @param integer $debug values 0, 1 and 2 are supported (2 = echo sent msg too, before received response)
        * @access public
        */
        function setDebug($in)
        {
            $this->debug=$in;
        }

        /**
        * Add some http BASIC AUTH credentials, used by the client to authenticate
        * @param string $u username
        * @param string $p password
        * @param integer $t auth type. See curl_setopt man page for supported auth types. Defaults to CURLAUTH_BASIC (basic auth)
        * @access public
        */
        function setCredentials($u, $p, $t=1)
        {
            $this->username=$u;
            $this->password=$p;
            $this->authtype=$t;
        }

        /**
        * Add a client-side https certificate
        * @param string $cert
        * @param string $certpass
        * @access public
        */
        function setCertificate($cert, $certpass)
        {
            $this->cert = $cert;
            $this->certpass = $certpass;
        }

        /**
        * Add a CA certificate to verify server with (see man page about
        * CURLOPT_CAINFO for more details
        * @param string $cacert certificate file name (or dir holding certificates)
        * @param bool $is_dir set to true to indicate cacert is a dir. defaults to false
        * @access public
        */
        function setCaCertificate($cacert, $is_dir=false)
        {
            if ($is_dir)
            {
                $this->cacertdir = $cacert;
            }
            else
            {
                $this->cacert = $cacert;
            }
        }

        /**
        * Set attributes for SSL communication: private SSL key
        * NB: does not work in older php/curl installs
        * Thanks to Daniel Convissor
        * @param string $key The name of a file containing a private SSL key
        * @param string $keypass The secret password needed to use the private SSL key
        * @access public
        */
        function setKey($key, $keypass)
        {
            $this->key = $key;
            $this->keypass = $keypass;
        }

        /**
        * Set attributes for SSL communication: verify server certificate
        * @param bool $i enable/disable verification of peer certificate
        * @access public
        */
        function setSSLVerifyPeer($i)
        {
            $this->verifypeer = $i;
        }

        /**
        * Set attributes for SSL communication: verify match of server cert w. hostname
        * @param int $i
        * @access public
        */
        function setSSLVerifyHost($i)
        {
            $this->verifyhost = $i;
        }

        /**
        * Set proxy info
        * @param string $proxyhost
        * @param string $proxyport Defaults to 8080 for HTTP and 443 for HTTPS
        * @param string $proxyusername Leave blank if proxy has public access
        * @param string $proxypassword Leave blank if proxy has public access
        * @param int $proxyauthtype set to constant CURLAUTH_NTLM to use NTLM auth with proxy
        * @access public
        */
        function setProxy($proxyhost, $proxyport, $proxyusername = '', $proxypassword = '', $proxyauthtype = 1)
        {
            $this->proxy = $proxyhost;
            $this->proxyport = $proxyport;
            $this->proxy_user = $proxyusername;
            $this->proxy_pass = $proxypassword;
            $this->proxy_authtype = $proxyauthtype;
        }

        /**
        * Enables/disables reception of compressed xmlrpc responses.
        * Note that enabling reception of compressed responses merely adds some standard
        * http headers to xmlrpc requests. It is up to the xmlrpc server to return
        * compressed responses when receiving such requests.
        * @param string $compmethod either 'gzip', 'deflate', 'any' or ''
        * @access public
        */
        function setAcceptedCompression($compmethod)
        {
            if ($compmethod == 'any')
                $this->accepted_compression = array('gzip', 'deflate');
            else
                $this->accepted_compression = array($compmethod);
        }

        /**
        * Enables/disables http compression of xmlrpc request.
        * Take care when sending compressed requests: servers might not support them
        * (and automatic fallback to uncompressed requests is not yet implemented)
        * @param string $compmethod either 'gzip', 'deflate' or ''
        * @access public
        */
        function setRequestCompression($compmethod)
        {
            $this->request_compression = $compmethod;
        }

        /**
        * Adds a cookie to list of cookies that will be sent to server.
        * NB: setting any param but name and value will turn the cookie into a 'version 1' cookie:
        * do not do it unless you know what you are doing
        * @param string $name
        * @param string $value
        * @param string $path
        * @param string $domain
        * @param int $port
        * @access public
        *
        * @todo check correctness of urlencoding cookie value (copied from php way of doing it...)
        */
        function setCookie($name, $value='', $path='', $domain='', $port=null)
        {
            $this->cookies[$name]['value'] = urlencode($value);
            if ($path || $domain || $port)
            {
                $this->cookies[$name]['path'] = $path;
                $this->cookies[$name]['domain'] = $domain;
                $this->cookies[$name]['port'] = $port;
                $this->cookies[$name]['version'] = 1;
            }
            else
            {
                $this->cookies[$name]['version'] = 0;
            }
        }

        /**
        * Send an xmlrpc request
        * @param mixed $msg The message object, or an array of messages for using multicall, or the complete xml representation of a request
        * @param integer $timeout Connection timeout, in seconds, If unspecified, a platform specific timeout will apply
        * @param string $method if left unspecified, the http protocol chosen during creation of the object will be used
        * @return xmlrpcresp
        * @access public
        */
        function& send($msg, $timeout=0, $method='')
        {
            // if user deos not specify http protocol, use native method of this client
            // (i.e. method set during call to constructor)
            if($method == '')
            {
                $method = $this->method;
            }

            if(is_array($msg))
            {
                // $msg is an array of xmlrpcmsg's
                $r = $this->multicall($msg, $timeout, $method);
                return $r;
            }
            else if(is_string($msg))
            {
                $n = new XmlRpcMsg('');
                $n->payload = $msg;
                $msg = $n;
            }

            // where msg is an xmlrpcmsg
            $msg->debug=$this->debug;

            if($method == 'https')
            {
                $r =& $this->sendPayloadHTTPS(
                    $msg,
                    $this->server,
                    $this->port,
                    $timeout,
                    $this->username,
                    $this->password,
                    $this->authtype,
                    $this->cert,
                    $this->certpass,
                    $this->cacert,
                    $this->cacertdir,
                    $this->proxy,
                    $this->proxyport,
                    $this->proxy_user,
                    $this->proxy_pass,
                    $this->proxy_authtype,
                    $this->keepalive,
                    $this->key,
                    $this->keypass
                );
            }
            else if($method == 'http11')
            {
                $r =& $this->sendPayloadCURL(
                    $msg,
                    $this->server,
                    $this->port,
                    $timeout,
                    $this->username,
                    $this->password,
                    $this->authtype,
                    null,
                    null,
                    null,
                    null,
                    $this->proxy,
                    $this->proxyport,
                    $this->proxy_user,
                    $this->proxy_pass,
                    $this->proxy_authtype,
                    'http',
                    $this->keepalive
                );
            }
            else
            {
                $r =& $this->sendPayloadHTTP10(
                    $msg,
                    $this->server,
                    $this->port,
                    $timeout,
                    $this->username,
                    $this->password,
                    $this->authtype,
                    $this->proxy,
                    $this->proxyport,
                    $this->proxy_user,
                    $this->proxy_pass,
                    $this->proxy_authtype
                );
            }

            return $r;
        }

        /**
        * @access private
        */
        function &sendPayloadHTTP10($msg, $server, $port, $timeout=0,
            $username='', $password='', $authtype=1, $proxyhost='',
            $proxyport=0, $proxyusername='', $proxypassword='', $proxyauthtype=1)
        {
            if($port==0)
            {
                $port=80;
            }

            // Only create the payload if it was not created previously
            if(empty($msg->payload))
            {
                $msg->createPayload($this->request_charset_encoding);
            }

            $payload = $msg->payload;
            // Deflate request body and set appropriate request headers
            if(function_exists('gzdeflate') && ($this->request_compression == 'gzip' || $this->request_compression == 'deflate'))
            {
                if($this->request_compression == 'gzip')
                {
                    $a = @gzencode($payload);
                    if($a)
                    {
                        $payload = $a;
                        $encoding_hdr = "Content-Encoding: gzip\r\n";
                    }
                }
                else
                {
                    $a = @gzcompress($payload);
                    if($a)
                    {
                        $payload = $a;
                        $encoding_hdr = "Content-Encoding: deflate\r\n";
                    }
                }
            }
            else
            {
                $encoding_hdr = '';
            }

            // thanks to Grant Rauscher <grant7@firstworld.net> for this
            $credentials='';
            if($username!='')
            {
                $credentials='Authorization: Basic ' . base64_encode($username . ':' . $password) . "\r\n";
                if ($authtype != 1)
                {
                    error_log('XML-RPC: xmlrpc_client::send: warning. Only Basic auth is supported with HTTP 1.0');
                }
            }

            $accepted_encoding = '';
            if(is_array($this->accepted_compression) && count($this->accepted_compression))
            {
                $accepted_encoding = 'Accept-Encoding: ' . implode(', ', $this->accepted_compression) . "\r\n";
            }

            $proxy_credentials = '';
            if($proxyhost)
            {
                if($proxyport == 0)
                {
                    $proxyport = 8080;
                }
                $connectserver = $proxyhost;
                $connectport = $proxyport;
                $uri = 'http://'.$server.':'.$port.$this->path;
                if($proxyusername != '')
                {
                    if ($proxyauthtype != 1)
                    {
                        error_log('XML-RPC: xmlrpc_client::send: warning. Only Basic auth to proxy is supported with HTTP 1.0');
                    }
                    $proxy_credentials = 'Proxy-Authorization: Basic ' . base64_encode($proxyusername.':'.$proxypassword) . "\r\n";
                }
            }
            else
            {
                $connectserver = $server;
                $connectport = $port;
                $uri = $this->path;
            }

            // Cookie generation, as per rfc2965 (version 1 cookies) or
            // netscape's rules (version 0 cookies)
            $cookieheader='';
            if (count($this->cookies))
            {
                $version = '';
                foreach ($this->cookies as $name => $cookie)
                {
                    if ($cookie['version'])
                    {
                        $version = ' $Version="' . $cookie['version'] . '";';
                        $cookieheader .= ' ' . $name . '="' . $cookie['value'] . '";';
                        if ($cookie['path'])
                            $cookieheader .= ' $Path="' . $cookie['path'] . '";';
                        if ($cookie['domain'])
                            $cookieheader .= ' $Domain="' . $cookie['domain'] . '";';
                        if ($cookie['port'])
                            $cookieheader .= ' $Port="' . $cookie['port'] . '";';
                    }
                    else
                    {
                        $cookieheader .= ' ' . $name . '=' . $cookie['value'] . ";";
                    }
                }
                $cookieheader = 'Cookie:' . $version . substr($cookieheader, 0, -1) . "\r\n";
            }

            $op= 'POST ' . $uri. " HTTP/1.0\r\n" .
                'User-Agent: ' . $GLOBALS['xmlrpcName'] . ' ' . $GLOBALS['xmlrpcVersion'] . "\r\n" .
                'Host: '. $server . ':' . $port . "\r\n" .
                $credentials .
                $proxy_credentials .
                $accepted_encoding .
                $encoding_hdr .
                'Accept-Charset: ' . implode(',', $this->accepted_charset_encodings) . "\r\n" .
                $cookieheader .
                'Content-Type: ' . $msg->content_type . "\r\nContent-Length: " .
                strlen($payload) . "\r\n\r\n" .
                $payload;

            if($this->debug > 1)
            {
                print "<PRE>\n---SENDING---\n" . htmlentities($op) . "\n---END---\n</PRE>";
                // let the client see this now in case http times out...
                flush();
            }

            if($timeout>0)
            {
                $fp=@fsockopen($connectserver, $connectport, $this->errno, $this->errstr, $timeout);
            }
            else
            {
                $fp=@fsockopen($connectserver, $connectport, $this->errno, $this->errstr);
            }
            if($fp)
            {
                if($timeout>0 && function_exists('stream_set_timeout'))
                {
                    stream_set_timeout($fp, $timeout);
                }
            }
            else
            {
                $this->errstr='Connect error: '.$this->errstr;
                $r= new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['http_error'], $this->errstr . ' (' . $this->errno . ')');
                return $r;
            }

            if(!fputs($fp, $op, strlen($op)))
            {
                $this->errstr='Write error';
                $r= new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['http_error'], $this->errstr);
                return $r;
            }
            else
            {
                // reset errno and errstr on succesful socket connection
                $this->errstr = '';
            }
            // G. Giunta 2005/10/24: close socket before parsing.
            // should yeld slightly better execution times, and make easier recursive calls (e.g. to follow http redirects)
            $ipd='';
            while($data=fread($fp, 32768))
            {
                // shall we check for $data === false?
                // as per the manual, it signals an error
                $ipd.=$data;
            }
            fclose($fp);
            $r =& $msg->parseResponse($ipd, false, $this->return_type);
            return $r;

        }

        /**
        * @access private
        */
        function &sendPayloadHTTPS($msg, $server, $port, $timeout=0, $username='',
            $password='', $authtype=1, $cert='',$certpass='', $cacert='', $cacertdir='',
            $proxyhost='', $proxyport=0, $proxyusername='', $proxypassword='', $proxyauthtype=1,
            $keepalive=false, $key='', $keypass='')
        {
            $r =& $this->sendPayloadCURL($msg, $server, $port, $timeout, $username,
                $password, $authtype, $cert, $certpass, $cacert, $cacertdir, $proxyhost, $proxyport,
                $proxyusername, $proxypassword, $proxyauthtype, 'https', $keepalive, $key, $keypass);
            return $r;
        }

        /**
        * Contributed by Justin Miller <justin@voxel.net>
        * Requires curl to be built into PHP
        * NB: CURL versions before 7.11.10 cannot use proxy to talk to https servers!
        * @access private
        */
        function &sendPayloadCURL($msg, $server, $port, $timeout=0, $username='',
            $password='', $authtype=1, $cert='', $certpass='', $cacert='', $cacertdir='',
            $proxyhost='', $proxyport=0, $proxyusername='', $proxypassword='', $proxyauthtype=1, $method='https',
            $keepalive=false, $key='', $keypass='')
        {
            if(!function_exists('curl_init'))
            {
                $this->errstr='CURL unavailable on this install';
                $r= new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['no_curl'], $GLOBALS['xmlrpcstr']['no_curl']);
                return $r;
            }
            if($method == 'https')
            {
                if(($info = curl_version()) &&
                    ((is_string($info) && strpos($info, 'OpenSSL') === null) || (is_array($info) && !isset($info['ssl_version']))))
                {
                    $this->errstr='SSL unavailable on this install';
                    $r= new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['no_ssl'], $GLOBALS['xmlrpcstr']['no_ssl']);
                    return $r;
                }
            }

            if($port == 0)
            {
                if($method == 'http')
                {
                    $port = 80;
                }
                else
                {
                    $port = 443;
                }
            }

            // Only create the payload if it was not created previously
            if(empty($msg->payload))
            {
                $msg->createPayload($this->request_charset_encoding);
            }

            // Deflate request body and set appropriate request headers
            $payload = $msg->payload;
            if(function_exists('gzdeflate') && ($this->request_compression == 'gzip' || $this->request_compression == 'deflate'))
            {
                if($this->request_compression == 'gzip')
                {
                    $a = @gzencode($payload);
                    if($a)
                    {
                        $payload = $a;
                        $encoding_hdr = 'Content-Encoding: gzip';
                    }
                }
                else
                {
                    $a = @gzcompress($payload);
                    if($a)
                    {
                        $payload = $a;
                        $encoding_hdr = 'Content-Encoding: deflate';
                    }
                }
            }
            else
            {
                $encoding_hdr = '';
            }

            if($this->debug > 1)
            {
                print "<PRE>\n---SENDING---\n" . htmlentities($payload) . "\n---END---\n</PRE>";
                // let the client see this now in case http times out...
                flush();
            }

            if(!$keepalive || !$this->xmlrpc_curl_handle)
            {
                $curl = curl_init($method . '://' . $server . ':' . $port . $this->path);
                if($keepalive)
                {
                    $this->xmlrpc_curl_handle = $curl;
                }
            }
            else
            {
                $curl = $this->xmlrpc_curl_handle;
            }

            // results into variable
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            if($this->debug)
            {
                curl_setopt($curl, CURLOPT_VERBOSE, 1);
            }
            curl_setopt($curl, CURLOPT_USERAGENT, $GLOBALS['xmlrpcName'].' '.$GLOBALS['xmlrpcVersion']);
            // required for XMLRPC: post the data
            curl_setopt($curl, CURLOPT_POST, 1);
            // the data
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);

            // return the header too
            curl_setopt($curl, CURLOPT_HEADER, 1);

            // will only work with PHP >= 5.0
            // NB: if we set an empty string, CURL will add http header indicating
            // ALL methods it is supporting. This is possibly a better option than
            // letting the user tell what curl can / cannot do...
            if(is_array($this->accepted_compression) && count($this->accepted_compression))
            {
                //curl_setopt($curl, CURLOPT_ENCODING, implode(',', $this->accepted_compression));
                // empty string means 'any supported by CURL' (shall we catch errors in case CURLOPT_SSLKEY undefined ?)
                if (count($this->accepted_compression) == 1)
                {
                    curl_setopt($curl, CURLOPT_ENCODING, $this->accepted_compression[0]);
                }
                else
                    curl_setopt($curl, CURLOPT_ENCODING, '');
            }
            // extra headers
            $headers = array('Content-Type: ' . $msg->content_type , 'Accept-Charset: ' . implode(',', $this->accepted_charset_encodings));
            // if no keepalive is wanted, let the server know it in advance
            if(!$keepalive)
            {
                $headers[] = 'Connection: close';
            }
            // request compression header
            if($encoding_hdr)
            {
                $headers[] = $encoding_hdr;
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            // timeout is borked
            if($timeout)
            {
                curl_setopt($curl, CURLOPT_TIMEOUT, $timeout == 1 ? 1 : $timeout - 1);
            }

            if($username && $password)
            {
                curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password);
                if (defined('CURLOPT_HTTPAUTH'))
                {
                    curl_setopt($curl, CURLOPT_HTTPAUTH, $authtype);
                }
                else if ($authtype != 1)
                {
                    error_log('XML-RPC: xmlrpc_client::send: warning. Only Basic auth is supported by the current PHP/curl install');
                }
            }

            if($method == 'https')
            {
                // set cert file
                if($cert)
                {
                    curl_setopt($curl, CURLOPT_SSLCERT, $cert);
                }
                // set cert password
                if($certpass)
                {
                    curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $certpass);
                }
                // whether to verify remote host's cert
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verifypeer);
                // set ca certificates file/dir
                if($cacert)
                {
                    curl_setopt($curl, CURLOPT_CAINFO, $cacert);
                }
                if($cacertdir)
                {
                    curl_setopt($curl, CURLOPT_CAPATH, $cacertdir);
                }
                // set key file (shall we catch errors in case CURLOPT_SSLKEY undefined ?)
                if($key)
                {
                    curl_setopt($curl, CURLOPT_SSLKEY, $key);
                }
                // set key password (shall we catch errors in case CURLOPT_SSLKEY undefined ?)
                if($keypass)
                {
                    curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $keypass);
                }
                // whether to verify cert's common name (CN); 0 for no, 1 to verify that it exists, and 2 to verify that it matches the hostname used
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->verifyhost);
            }

            // proxy info
            if($proxyhost)
            {
                if($proxyport == 0)
                {
                    $proxyport = 8080; // NB: even for HTTPS, local connection is on port 8080
                }
                curl_setopt($curl, CURLOPT_PROXY, $proxyhost.':'.$proxyport);
                //curl_setopt($curl, CURLOPT_PROXYPORT,$proxyport);
                if($proxyusername)
                {
                    curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyusername.':'.$proxypassword);
                    if (defined('CURLOPT_PROXYAUTH'))
                    {
                        curl_setopt($curl, CURLOPT_PROXYAUTH, $proxyauthtype);
                    }
                    else if ($proxyauthtype != 1)
                    {
                        error_log('XML-RPC: xmlrpc_client::send: warning. Only Basic auth to proxy is supported by the current PHP/curl install');
                    }
                }
            }

            // NB: should we build cookie http headers by hand rather than let CURL do it?
            // the following code does not honour 'expires', 'path' and 'domain' cookie attributes
            // set to client obj the the user...
            if (count($this->cookies))
            {
                $cookieheader = '';
                foreach ($this->cookies as $name => $cookie)
                {
                    $cookieheader .= $name . '=' . $cookie['value'] . '; ';
                }
                curl_setopt($curl, CURLOPT_COOKIE, substr($cookieheader, 0, -2));
            }

            $result = curl_exec($curl);

            if ($this->debug > 1)
            {
                print "<PRE>\n---CURL INFO---\n";
                foreach(curl_getinfo($curl) as $name => $val)
                     print $name . ': ' . htmlentities($val). "\n";
                print "---END---\n</PRE>";
            }

            if(!$result) /// @todo we should use a better check here - what if we get back '' or '0'?
            {
                $this->errstr='no response';
                $resp= new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['curl_fail'], $GLOBALS['xmlrpcstr']['curl_fail']. ': '. curl_error($curl));
                curl_close($curl);
                if($keepalive)
                {
                    $this->xmlrpc_curl_handle = null;
                }
            }
            else
            {
                if(!$keepalive)
                {
                    curl_close($curl);
                }
                $resp =& $msg->parseResponse($result, true, $this->return_type);
            }
            return $resp;
        }

        /**
        * Send an array of request messages and return an array of responses.
        * Unless $this->no_multicall has been set to true, it will try first
        * to use one single xmlrpc call to server method system.multicall, and
        * revert to sending many successive calls in case of failure.
        * This failure is also stored in $this->no_multicall for subsequent calls.
        * Unfortunately, there is no server error code universally used to denote
        * the fact that multicall is unsupported, so there is no way to reliably
        * distinguish between that and a temporary failure.
        * If you are sure that server supports multicall and do not want to
        * fallback to using many single calls, set the fourth parameter to false.
        *
        * NB: trying to shoehorn extra functionality into existing syntax has resulted
        * in pretty much convoluted code...
        *
        * @param array $msgs an array of xmlrpcmsg objects
        * @param integer $timeout connection timeout (in seconds)
        * @param string $method the http protocol variant to be used
        * @param boolean fallback When true, upon receiveing an error during multicall, multiple single calls will be attempted
        * @return array
        * @access public
        */
        function multicall($msgs, $timeout=0, $method='', $fallback=true)
        {
            if ($method == '')
            {
                $method = $this->method;
            }
            if(!$this->no_multicall)
            {
                $results = $this->_try_multicall($msgs, $timeout, $method);
                if(is_array($results))
                {
                    // System.multicall succeeded
                    return $results;
                }
                else
                {
                    // either system.multicall is unsupported by server,
                    // or call failed for some other reason.
                    if ($fallback)
                    {
                        // Don't try it next time...
                        $this->no_multicall = true;
                    }
                    else
                    {
                        if (is_a($results, 'xmlrpcresp'))
                        {
                            $result = $results;
                        }
                        else
                        {
                            $result = new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['multicall_error'], $GLOBALS['xmlrpcstr']['multicall_error']);
                        }
                    }
                }
            }
            else
            {
                // override fallback, in case careless user tries to do two
                // opposite things at the same time
                $fallback = true;
            }

            $results = array();
            if ($fallback)
            {
                // system.multicall is (probably) unsupported by server:
                // emulate multicall via multiple requests
                foreach($msgs as $msg)
                {
                    $results[] =& $this->send($msg, $timeout, $method);
                }
            }
            else
            {
                // user does NOT want to fallback on many single calls:
                // since we should always return an array of responses,
                // return an array with the same error repeated n times
                foreach($msgs as $msg)
                {
                    $results[] = $result;
                }
            }
            return $results;
        }

        /**
        * Attempt to boxcar $msgs via system.multicall.
        * Returns either an array of xmlrpcreponses, an xmlrpc error response
        * or false (when received response does not respect valid multicall syntax)
        * @access private
        */
        function _try_multicall($msgs, $timeout, $method)
        {
            // Construct multicall message
            $calls = array();
            foreach($msgs as $msg)
            {
                $call['methodName'] = new XmlRpcVal($msg->method(),'string');
                $numParams = $msg->getNumParams();
                $params = array();
                for($i = 0; $i < $numParams; $i++)
                {
                    $params[$i] = $msg->getParam($i);
                }
                $call['params'] = new XmlRpcVal($params, 'array');
                $calls[] = new XmlRpcVal($call, 'struct');
            }
            $multicall = new XmlRpcMsg('system.multicall');
            $multicall->addParam(new XmlRpcVal($calls, 'array'));

            // Attempt RPC call
            $result =& $this->send($multicall, $timeout, $method);

            if($result->faultCode() != 0)
            {
                // call to system.multicall failed
                return $result;
            }

            // Unpack responses.
            $rets = $result->value();

            if ($this->return_type == 'xml')
            {
                    return $rets;
            }
            else if ($this->return_type == 'phpvals')
            {
                ///@todo test this code branch...
                $rets = $result->value();
                if(!is_array($rets))
                {
                    return false;        // bad return type from system.multicall
                }
                $numRets = count($rets);
                if($numRets != count($msgs))
                {
                    return false;        // wrong number of return values.
                }

                $response = array();
                for($i = 0; $i < $numRets; $i++)
                {
                    $val = $rets[$i];
                    if (!is_array($val)) {
                        return false;
                    }
                    switch(count($val))
                    {
                        case 1:
                            if(!isset($val[0]))
                            {
                                return false;        // Bad value
                            }
                            // Normal return value
                            $response[$i] = new XmlRpcResp($val[0], 0, '', 'phpvals');
                            break;
                        case 2:
                            ///    @todo remove usage of @: it is apparently quite slow
                            $code = @$val['faultCode'];
                            if(!is_int($code))
                            {
                                return false;
                            }
                            $str = @$val['faultString'];
                            if(!is_string($str))
                            {
                                return false;
                            }
                            $response[$i] = new XmlRpcResp(0, $code, $str);
                            break;
                        default:
                            return false;
                    }
                }
                return $response;
            }
            else // return type == 'xmlrpcvals'
            {
                $rets = $result->value();
                if($rets->kindOf() != 'array')
                {
                    return false;        // bad return type from system.multicall
                }
                $numRets = $rets->arraysize();
                if($numRets != count($msgs))
                {
                    return false;        // wrong number of return values.
                }

                $response = array();
                for($i = 0; $i < $numRets; $i++)
                {
                    $val = $rets->arraymem($i);
                    switch($val->kindOf())
                    {
                        case 'array':
                            if($val->arraysize() != 1)
                            {
                                return false;        // Bad value
                            }
                            // Normal return value
                            $response[$i] = new XmlRpcResp($val->arraymem(0));
                            break;
                        case 'struct':
                            $code = $val->structmem('faultCode');
                            if($code->kindOf() != 'scalar' || $code->scalartyp() != 'int')
                            {
                                return false;
                            }
                            $str = $val->structmem('faultString');
                            if($str->kindOf() != 'scalar' || $str->scalartyp() != 'string')
                            {
                                return false;
                            }
                            $response[$i] = new XmlRpcResp(0, $code->scalarval(), $str->scalarval());
                            break;
                        default:
                            return false;
                    }
                }
                return $response;
            }
        }
    } // end class xmlrpc_client



    // date helpers

    /**
    * Given a timestamp, return the corresponding ISO8601 encoded string.
    *
    * Really, timezones ought to be supported
    * but the XML-RPC spec says:
    *
    * "Don't assume a timezone. It should be specified by the server in its
    * documentation what assumptions it makes about timezones."
    *
    * These routines always assume localtime unless
    * $utc is set to 1, in which case UTC is assumed
    * and an adjustment for locale is made when encoding
    *
    * @param int $timet (timestamp)
    * @param int $utc (0 or 1)
    * @return string
    */
    function iso8601_encode($timet, $utc=0)
    {
        if(!$utc)
        {
            $t=strftime("%Y%m%dT%H:%M:%S", $timet);
        }
        else
        {
            if(function_exists('gmstrftime'))
            {
                // gmstrftime doesn't exist in some versions
                // of PHP
                $t=gmstrftime("%Y%m%dT%H:%M:%S", $timet);
            }
            else
            {
                $t=strftime("%Y%m%dT%H:%M:%S", $timet-date('Z'));
            }
        }
        return $t;
    }

    /**
    * Given an ISO8601 date string, return a timet in the localtime, or UTC
    * @param string $idate
    * @param int $utc either 0 or 1
    * @return int (datetime)
    */
    function iso8601_decode($idate, $utc=0)
    {
        $t=0;
        if(preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})/', $idate, $regs))
        {
            if($utc)
            {
                $t=gmmktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
            }
            else
            {
                $t=mktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
            }
        }
        return $t;
    }

    /**
    * Takes an xmlrpc value in PHP xmlrpcval object format and translates it into native PHP types.
    *
    * Works with xmlrpc message objects as input, too.
    *
    * Given proper options parameter, can rebuild generic php object instances
    * (provided those have been encoded to xmlrpc format using a corresponding
    * option in php_xmlrpc_encode())
    * PLEASE NOTE that rebuilding php objects involves calling their constructor function.
    * This means that the remote communication end can decide which php code will
    * get executed on your server, leaving the door possibly open to 'php-injection'
    * style of attacks (provided you have some classes defined on your server that
    * might wreak havoc if instances are built outside an appropriate context).
    * Make sure you trust the remote server/client before eanbling this!
    *
    * @author Dan Libby (dan@libby.com)
    *
    * @param xmlrpcval $xmlrpc_val
    * @param array $options if 'decode_php_objs' is set in the options array, xmlrpc structs can be decoded into php objects
    * @return mixed
    */
    function php_xmlrpc_decode($xmlrpc_val, $options=array())
    {
        switch($xmlrpc_val->kindOf())
        {
            case 'scalar':
                if (in_array('extension_api', $options))
                {
                    reset($xmlrpc_val->me);
                    list($typ,$val) = each($xmlrpc_val->me);
                    switch ($typ)
                    {
                        case 'dateTime.iso8601':
                            $xmlrpc_val->scalar = $val;
                            $xmlrpc_val->xmlrpc_type = 'datetime';
                            $xmlrpc_val->timestamp = iso8601_decode($val);
                            return $xmlrpc_val;
                        case 'base64':
                            $xmlrpc_val->scalar = $val;
                            $xmlrpc_val->type = $typ;
                            return $xmlrpc_val;
                        default:
                            return $xmlrpc_val->scalarval();
                    }
                }
                return $xmlrpc_val->scalarval();
            case 'array':
                $size = $xmlrpc_val->arraysize();
                $arr = array();
                for($i = 0; $i < $size; $i++)
                {
                    $arr[] = php_xmlrpc_decode($xmlrpc_val->arraymem($i), $options);
                }
                return $arr;
            case 'struct':
                $xmlrpc_val->structreset();
                // If user said so, try to rebuild php objects for specific struct vals.
                /// @todo should we raise a warning for class not found?
                // shall we check for proper subclass of xmlrpcval instead of
                // presence of _php_class to detect what we can do?
                if (in_array('decode_php_objs', $options) && $xmlrpc_val->_php_class != ''
                    && class_exists($xmlrpc_val->_php_class))
                {
                    $obj = @new $xmlrpc_val->_php_class;
                    while(list($key,$value)=$xmlrpc_val->structeach())
                    {
                        $obj->$key = php_xmlrpc_decode($value, $options);
                    }
                    return $obj;
                }
                else
                {
                    $arr = array();
                    while(list($key,$value)=$xmlrpc_val->structeach())
                    {
                        $arr[$key] = php_xmlrpc_decode($value, $options);
                    }
                    return $arr;
                }
            case 'msg':
                $paramcount = $xmlrpc_val->getNumParams();
                $arr = array();
                for($i = 0; $i < $paramcount; $i++)
                {
                    $arr[] = php_xmlrpc_decode($xmlrpc_val->getParam($i));
                }
                return $arr;
            }
    }

    /**
    * Takes native php types and encodes them into xmlrpc PHP object format.
    * It will not re-encode xmlrpcval objects.
    *
    * Feature creep -- could support more types via optional type argument
    * (string => datetime support has been added, ??? => base64 not yet)
    *
    * If given a proper options parameter, php object instances will be encoded
    * into 'special' xmlrpc values, that can later be decoded into php objects
    * by calling php_xmlrpc_decode() with a corresponding option
    *
    * @author Dan Libby (dan@libby.com)
    *
    * @param mixed $php_val the value to be converted into an xmlrpcval object
    * @param array $options    can include 'encode_php_objs', 'auto_dates', 'null_extension' or 'extension_api'
    * @return xmlrpcval
    */
    function &php_xmlrpc_encode($php_val, $options=array())
    {
        $type = gettype($php_val);
        switch($type)
        {
            case 'string':
                if (in_array('auto_dates', $options) && preg_match('/^[0-9]{8}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $php_val))
                    $xmlrpc_val = new XmlRpcVal($php_val, $GLOBALS['xmlrpcDateTime']);
                else
                    $xmlrpc_val = new XmlRpcVal($php_val, $GLOBALS['xmlrpcString']);
                break;
            case 'integer':
                $xmlrpc_val = new XmlRpcVal($php_val, $GLOBALS['xmlrpcInt']);
                break;
            case 'double':
                $xmlrpc_val = new XmlRpcVal($php_val, $GLOBALS['xmlrpcDouble']);
                break;
                // <G_Giunta_2001-02-29>
                // Add support for encoding/decoding of booleans, since they are supported in PHP
            case 'boolean':
                $xmlrpc_val = new XmlRpcVal($php_val, $GLOBALS['xmlrpcBoolean']);
                break;
                // </G_Giunta_2001-02-29>
            case 'array':
                // PHP arrays can be encoded to either xmlrpc structs or arrays,
                // depending on wheter they are hashes or plain 0..n integer indexed
                // A shorter one-liner would be
                // $tmp = array_diff(array_keys($php_val), range(0, count($php_val)-1));
                // but execution time skyrockets!
                $j = 0;
                $arr = array();
                $ko = false;
                foreach($php_val as $key => $val)
                {
                    $arr[$key] =& php_xmlrpc_encode($val, $options);
                    if(!$ko && $key !== $j)
                    {
                        $ko = true;
                    }
                    $j++;
                }
                if($ko)
                {
                    $xmlrpc_val = new XmlRpcVal($arr, $GLOBALS['xmlrpcStruct']);
                }
                else
                {
                    $xmlrpc_val = new XmlRpcVal($arr, $GLOBALS['xmlrpcArray']);
                }
                break;
            case 'object':
                if(is_a($php_val, 'xmlrpcval'))
                {
                    $xmlrpc_val = $php_val;
                }
                else
                {
                    $arr = array();
                    while(list($k,$v) = each($php_val))
                    {
                        $arr[$k] = php_xmlrpc_encode($v, $options);
                    }
                    $xmlrpc_val = new XmlRpcVal($arr, $GLOBALS['xmlrpcStruct']);
                    if (in_array('encode_php_objs', $options))
                    {
                        // let's save original class name into xmlrpcval:
                        // might be useful later on...
                        $xmlrpc_val->_php_class = get_class($php_val);
                    }
                }
                break;
            case 'NULL':
                if (in_array('extension_api', $options))
                {
                    $xmlrpc_val = new XmlRpcVal('', $GLOBALS['xmlrpcString']);
                }
                if (in_array('null_extension', $options))
                {
                    $xmlrpc_val = new XmlRpcVal('', $GLOBALS['xmlrpcNull']);
                }
                else
                {
                    $xmlrpc_val = new XmlRpcVal();
                }
                break;
            case 'resource':
                if (in_array('extension_api', $options))
                {
                    $xmlrpc_val = new XmlRpcVal((int)$php_val, $GLOBALS['xmlrpcInt']);
                }
                else
                {
                    $xmlrpc_val = new XmlRpcVal();
                }
            // catch "user function", "unknown type"
            default:
                // giancarlo pinerolo <ping@alt.it>
                // it has to return
                // an empty object in case, not a boolean.
                $xmlrpc_val = new XmlRpcVal();
                break;
            }
            return $xmlrpc_val;
    }

    /**
    * Convert the xml representation of a method response, method request or single
    * xmlrpc value into the appropriate object (a.k.a. deserialize)
    * @param string $xml_val
    * @param array $options
    * @return mixed false on error, or an instance of either xmlrpcval, xmlrpcmsg or xmlrpcresp
    */
    function php_xmlrpc_decode_xml($xml_val, $options=array())
    {
        $GLOBALS['_xh'] = array();
        $GLOBALS['_xh']['ac'] = '';
        $GLOBALS['_xh']['stack'] = array();
        $GLOBALS['_xh']['valuestack'] = array();
        $GLOBALS['_xh']['params'] = array();
        $GLOBALS['_xh']['pt'] = array();
        $GLOBALS['_xh']['isf'] = 0;
        $GLOBALS['_xh']['isf_reason'] = '';
        $GLOBALS['_xh']['method'] = false;
        $GLOBALS['_xh']['rt'] = '';
        /// @todo 'guestimate' encoding
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, true);
        // What if internal encoding is not in one of the 3 allowed?
        // we use the broadest one, ie. utf8!
        if (!in_array($GLOBALS['xmlrpc_internalencoding'], array('UTF-8', 'ISO-8859-1', 'US-ASCII')))
        {
            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        }
        else
        {
            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $GLOBALS['xmlrpc_internalencoding']);
        }
        xml_set_element_handler($parser, '\Innomatic\Webservices\Xmlrpc\xmlrpc_se_any', '\Innomatic\Webservices\Xmlrpc\xmlrpc_ee');
        xml_set_character_data_handler($parser, '\Innomatic\Webservices\Xmlrpc\xmlrpc_cd');
        xml_set_default_handler($parser, '\Innomatic\Webservices\Xmlrpc\xmlrpc_dh');
        if(!xml_parse($parser, $xml_val, 1))
        {
            $errstr = sprintf('XML error: %s at line %d, column %d',
                        xml_error_string(xml_get_error_code($parser)),
                        xml_get_current_line_number($parser), xml_get_current_column_number($parser));
            error_log($errstr);
            xml_parser_free($parser);
            return false;
        }
        xml_parser_free($parser);
        if ($GLOBALS['_xh']['isf'] > 1) // test that $GLOBALS['_xh']['value'] is an obj, too???
        {
            error_log($GLOBALS['_xh']['isf_reason']);
            return false;
        }
        switch ($GLOBALS['_xh']['rt'])
        {
            case 'methodresponse':
                $v =& $GLOBALS['_xh']['value'];
                if ($GLOBALS['_xh']['isf'] == 1)
                {
                    $vc = $v->structmem('faultCode');
                    $vs = $v->structmem('faultString');
                    $r = new XmlRpcResp(0, $vc->scalarval(), $vs->scalarval());
                }
                else
                {
                    $r = new XmlRpcResp($v);
                }
                return $r;
            case 'methodcall':
                $m = new XmlRpcMsg($GLOBALS['_xh']['method']);
                for($i=0; $i < count($GLOBALS['_xh']['params']); $i++)
                {
                    $m->addParam($GLOBALS['_xh']['params'][$i]);
                }
                return $m;
            case 'value':
                return $GLOBALS['_xh']['value'];
            default:
                return false;
        }
    }

    /**
    * decode a string that is encoded w/ "chunked" transfer encoding
    * as defined in rfc2068 par. 19.4.6
    * code shamelessly stolen from nusoap library by Dietrich Ayala
    *
    * @param string $buffer the string to be decoded
    * @return string
    */
    function decode_chunked($buffer)
    {
        // length := 0
        $length = 0;
        $new = '';

        // read chunk-size, chunk-extension (if any) and crlf
        // get the position of the linebreak
        $chunkend = strpos($buffer,"\r\n") + 2;
        $temp = substr($buffer,0,$chunkend);
        $chunk_size = hexdec( trim($temp) );
        $chunkstart = $chunkend;
        while($chunk_size > 0)
        {
            $chunkend = strpos($buffer, "\r\n", $chunkstart + $chunk_size);

            // just in case we got a broken connection
            if($chunkend == false)
            {
                $chunk = substr($buffer,$chunkstart);
                // append chunk-data to entity-body
                $new .= $chunk;
                $length += strlen($chunk);
                break;
            }

            // read chunk-data and crlf
            $chunk = substr($buffer,$chunkstart,$chunkend-$chunkstart);
            // append chunk-data to entity-body
            $new .= $chunk;
            // length := length + chunk-size
            $length += strlen($chunk);
            // read chunk-size and crlf
            $chunkstart = $chunkend + 2;

            $chunkend = strpos($buffer,"\r\n",$chunkstart)+2;
            if($chunkend == false)
            {
                break; //just in case we got a broken connection
            }
            $temp = substr($buffer,$chunkstart,$chunkend-$chunkstart);
            $chunk_size = hexdec( trim($temp) );
            $chunkstart = $chunkend;
        }
        return $new;
    }

    /**
    * xml charset encoding guessing helper function.
    * Tries to determine the charset encoding of an XML chunk
    * received over HTTP.
    * NB: according to the spec (RFC 3023, if text/xml content-type is received over HTTP without a content-type,
    * we SHOULD assume it is strictly US-ASCII. But we try to be more tolerant of unconforming (legacy?) clients/servers,
    * which will be most probably using UTF-8 anyway...
    *
    * @param string $httpheaders the http Content-type header
    * @param string $xmlchunk xml content buffer
    * @param string $encoding_prefs comma separated list of character encodings to be used as default (when mb extension is enabled)
    *
    * @todo explore usage of mb_http_input(): does it detect http headers + post data? if so, use it instead of hand-detection!!!
    */
    function guess_encoding($httpheader='', $xmlchunk='', $encoding_prefs=null)
    {
        // discussion: see http://www.yale.edu/pclt/encoding/
        // 1 - test if encoding is specified in HTTP HEADERS

        //Details:
        // LWS:           (\13\10)?( |\t)+
        // token:         (any char but excluded stuff)+
        // header:        Content-type = ...; charset=value(; ...)*
        //   where value is of type token, no LWS allowed between 'charset' and value
        // Note: we do not check for invalid chars in VALUE:
        //   this had better be done using pure ereg as below

        /// @todo this test will pass if ANY header has charset specification, not only Content-Type. Fix it?
        $matches = array();
        if(preg_match('/;\s*charset=([^;]+)/i', $httpheader, $matches))
        {
            return strtoupper(trim($matches[1]));
        }

        // 2 - scan the first bytes of the data for a UTF-16 (or other) BOM pattern
        //     (source: http://www.w3.org/TR/2000/REC-xml-20001006)
        //     NOTE: actually, according to the spec, even if we find the BOM and determine
        //     an encoding, we should check if there is an encoding specified
        //     in the xml declaration, and verify if they match.
        /// @todo implement check as described above?
        /// @todo implement check for first bytes of string even without a BOM? (It sure looks harder than for cases WITH a BOM)
        if(preg_match('/^(\x00\x00\xFE\xFF|\xFF\xFE\x00\x00|\x00\x00\xFF\xFE|\xFE\xFF\x00\x00)/', $xmlchunk))
        {
            return 'UCS-4';
        }
        else if(preg_match('/^(\xFE\xFF|\xFF\xFE)/', $xmlchunk))
        {
            return 'UTF-16';
        }
        else if(preg_match('/^(\xEF\xBB\xBF)/', $xmlchunk))
        {
            return 'UTF-8';
        }

        // 3 - test if encoding is specified in the xml declaration
        // Details:
        // SPACE:         (#x20 | #x9 | #xD | #xA)+ === [ \x9\xD\xA]+
        // EQ:            SPACE?=SPACE? === [ \x9\xD\xA]*=[ \x9\xD\xA]*
        if (preg_match('/^<\?xml\s+version\s*=\s*'. "((?:\"[a-zA-Z0-9_.:-]+\")|(?:'[a-zA-Z0-9_.:-]+'))".
            '\s+encoding\s*=\s*' . "((?:\"[A-Za-z][A-Za-z0-9._-]*\")|(?:'[A-Za-z][A-Za-z0-9._-]*'))/",
            $xmlchunk, $matches))
        {
            return strtoupper(substr($matches[2], 1, -1));
        }

        // 4 - if mbstring is available, let it do the guesswork
        // NB: we favour finding an encoding that is compatible with what we can process
        if(extension_loaded('mbstring'))
        {
            if($encoding_prefs)
            {
                $enc = mb_detect_encoding($xmlchunk, $encoding_prefs);
            }
            else
            {
                $enc = mb_detect_encoding($xmlchunk);
            }
            // NB: mb_detect likes to call it ascii, xml parser likes to call it US_ASCII...
            // IANA also likes better US-ASCII, so go with it
            if($enc == 'ASCII')
            {
                $enc = 'US-'.$enc;
            }
            return $enc;
        }
        else
        {
            // no encoding specified: as per HTTP1.1 assume it is iso-8859-1?
            // Both RFC 2616 (HTTP 1.1) and 1945(http 1.0) clearly state that for text/xxx content types
            // this should be the standard. And we should be getting text/xml as request and response.
            // BUT we have to be backward compatible with the lib, which always used UTF-8 as default...
            return $GLOBALS['xmlrpc_defencoding'];
        }
    }

    /**
    * Checks if a given charset encoding is present in a list of encodings or
    * if it is a valid subset of any encoding in the list
    * @param string $encoding charset to be tested
    * @param mixed $validlist comma separated list of valid charsets (or array of charsets)
    */
    function is_valid_charset($encoding, $validlist)
    {
        $charset_supersets = array(
            'US-ASCII' => array ('ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4',
                'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8',
                'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-11', 'ISO-8859-12',
                'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'UTF-8',
                'EUC-JP', 'EUC-', 'EUC-KR', 'EUC-CN')
        );
        if (is_string($validlist))
            $validlist = explode(',', $validlist);
        if (@in_array(strtoupper($encoding), $validlist))
            return true;
        else
        {
            if (array_key_exists($encoding, $charset_supersets))
                foreach ($validlist as $allowed)
                    if (in_array($allowed, $charset_supersets[$encoding]))
                        return true;
                return false;
        }
    }
