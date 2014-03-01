<?php
namespace Innomatic\Webservices\Xmlrpc;

class XmlRpcMsg
{
	var $payload;
	var $methodname;
	var $params=array();
	var $debug=0;
	var $content_type = 'text/xml';

	/**
	 * @param string $meth the name of the method to invoke
	 * @param array $pars array of parameters to be paased to the method (xmlrpcval objects)
	 */
	function __construct($meth, $pars=0)
	{
		$this->methodname=$meth;
		if(is_array($pars) && count($pars)>0)
		{
			for($i=0; $i<count($pars); $i++)
			{
			$this->addParam($pars[$i]);
			}
			}
			}

			/**
			* @access private
			*/
			function xml_header($charset_encoding='')
			{
			if ($charset_encoding != '')
			{
			return "<?xml version=\"1.0\" encoding=\"$charset_encoding\" ?" . ">\n<methodCall>\n";
			}
			else
				{
					return "<?xml version=\"1.0\"?" . ">\n<methodCall>\n";
			}
			}

					/**
        * @access private
					*/
					function xml_footer()
						{
							return '</methodCall>';
					}

					/**
        * @access private
							*/
							function kindOf()
							 {
							 return 'msg';
			}

			/**
			* @access private
				*/
				function createPayload($charset_encoding='')
			{
				if ($charset_encoding != '')
				$this->content_type = 'text/xml; charset=' . $charset_encoding;
				else
					$this->content_type = 'text/xml';
				$this->payload=$this->xml_header($charset_encoding);
						$this->payload.='<methodName>' . $this->methodname . "</methodName>\n";
						$this->payload.="<params>\n";
						for($i=0; $i<count($this->params); $i++)
						{
						$p=$this->params[$i];
								$this->payload.="<param>\n" . $p->serialize($charset_encoding) .
								"</param>\n";
						}
										$this->payload.="</params>\n";
										$this->payload.=$this->xml_footer();
						}

						/**
						* Gets/sets the xmlrpc method to be invoked
							* @param string $meth the method to be set (leave empty not to set it)
							* @return string the method that will be invoked
									* @access public
									*/
									function method($meth='')
				{
				if($meth!='')
				{
				$this->methodname=$meth;
				}
				return $this->methodname;
				}

				/**
				* Returns xml representation of the message. XML prologue included
				* @return string the xml representation of the message, xml prologue included
				* @access public
				*/
				function serialize($charset_encoding='')
					{
					$this->createPayload($charset_encoding);
					return $this->payload;
					}

					/**
					* Add a parameter to the list of parameters to be used upon method invocation
					* @param xmlrpcval $par
					* @return boolean false on failure
					* @access public
					*/
					function addParam($par)
					{
					// add check: do not add to self params which are not xmlrpcvals
					if(is_object($par) && is_a($par, '\Innomatic\Webservices\Xmlrpc\XmlRpcVal'))
					{
					$this->params[]=$par;
					return true;
					}
					else
					 {
					  return false;
					  }
					  }

					  /**
					  * Returns the nth parameter in the message. The index zero-based.
					  * @param integer $i the index of the parameter to fetch (zero based)
					* @return xmlrpcval the i-th parameter
						* @access public
						*/
						function getParam($i) { return $this->params[$i]; }

						/**
						* Returns the number of parameters in the messge.
						* @return integer the number of parameters currently set
						* @access public
						*/
							function getNumParams() { return count($this->params); }

							/**
							* Given an open file handle, read all data available and parse it as axmlrpc response.
							* NB: the file handle is not closed by this function.
							* @access public
							* @return xmlrpcresp
							* @todo add 2nd & 3rd param to be passed to ParseResponse() ???
							*/
							function &parseResponseFile($fp)
							 {
							 $ipd='';
							 while($data=fread($fp, 32768))
							 {
							 $ipd.=$data;
							 }
							 //fclose($fp);
							$r =& $this->parseResponse($ipd);
							return $r;
			}

			/**
			* Parses HTTP headers and separates them from data.
			* @access private
			*/
			function &parseResponseHeaders(&$data, $headers_processed=false)
			{
			// Support "web-proxy-tunelling" connections for https through proxies
			if(preg_match('/^HTTP\/1\.[0-1] 200 Connection established/', $data))
			{
			// Look for CR/LF or simple LF as line separator,
			// (even though it is not valid http)
			$pos = strpos($data,"\r\n\r\n");
				if($pos || is_int($pos))
				{
				$bd = $pos+4;
			}
			else
			{
			$pos = strpos($data,"\n\n");
			if($pos || is_int($pos))
			{
			$bd = $pos+2;
			 }
			 else
			 {
			 // No separation between response headers and body: fault?
			 $bd = 0;
			}
			}
			if ($bd)
			{
			// this filters out all http headers from proxy.
				// maybe we could take them into account, too?
				$data = substr($data, $bd);
			}
			else
			{
			error_log('XML-RPC: xmlrpcmsg::parseResponse: HTTPS via proxy error, tunnel connection possibly failed');
				$r=new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['http_error'], $GLOBALS['xmlrpcstr']['http_error']. ' (HTTPS via proxy error, tunnel connection possibly failed)');
				return $r;
			}
			}

			// Strip HTTP 1.1 100 Continue header if present
				while(preg_match('/^HTTP\/1\.1 1[0-9]{2} /', $data))
				{
				$pos = strpos($data, 'HTTP', 12);
					// server sent a Continue header without any (valid) content following...
					// give the client a chance to know it
						if(!$pos && !is_int($pos)) // works fine in php 3, 4 and 5
						{
						break;
				}
				$data = substr($data, $pos);
				}
					if(!preg_match('/^HTTP\/[0-9.]+ 200 /', $data))
				{
				$errstr= substr($data, 0, strpos($data, "\n")-1);
				error_log('XML-RPC: xmlrpcmsg::parseResponse: HTTP error, got response: ' .$errstr);
				$r=new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['http_error'], $GLOBALS['xmlrpcstr']['http_error']. ' (' . $errstr . ')');
				return $r;
			}

			$GLOBALS['_xh']['headers'] = array();
			$GLOBALS['_xh']['cookies'] = array();

			// be tolerant to usage of \n instead of \r\n to separate headers and data
			// (even though it is not valid http)
                $pos = strpos($data,"\r\n\r\n");
					if($pos || is_int($pos))
			{
			$bd = $pos+4;
			}
			else
			{
				$pos = strpos($data,"\n\n");
				if($pos || is_int($pos))
					{
                        $bd = $pos+2;
			}
				else
					{
					// No separation between response headers and body: fault?
					// we could take some action here instead of going on...
					$bd = 0;
			}
			}
			// be tolerant to line endings, and extra empty lines
			$ar = split("\r?\n", trim(substr($data, 0, $pos)));
			while(list(,$line) = @each($ar))
			{
			// take care of multi-line headers and cookies
			$arr = explode(':',$line,2);
			if(count($arr) > 1)
			{
			$header_name = strtolower(trim($arr[0]));
			/// @todo some other headers (the ones that allow a CSV list of values)
				/// do allow many values to be passed using multiple header lines.
			/// We should add content to $GLOBALS['_xh']['headers'][$header_name]
			/// instead of replacing it for those...
			if ($header_name == 'set-cookie' || $header_name == 'set-cookie2')
			{
			if ($header_name == 'set-cookie2')
			{
                                // version 2 cookies:
					// there could be many cookies on one line, comma separated
					$cookies = explode(',', $arr[1]);
			}
							else
							{
							$cookies = array($arr[1]);
			}
			foreach ($cookies as $cookie)
			{
			// glue together all received cookies, using a comma to separate them
			// (same as php does with getallheaders())
			if (isset($GLOBALS['_xh']['headers'][$header_name]))
			$GLOBALS['_xh']['headers'][$header_name] .= ', ' . trim($cookie);
			else
				$GLOBALS['_xh']['headers'][$header_name] = trim($cookie);
			// parse cookie attributes, in case user wants to correctly honour them
			// feature creep: only allow rfc-compliant cookie attributes?
				// @todo support for server sending multiple time cookie with same name, but using different PATHs
				$cookie = explode(';', $cookie);
				foreach ($cookie as $pos => $val)
				{
			$val = explode('=', $val, 2);
			$tag = trim($val[0]);
				$val = trim(@$val[1]);
				/// @todo with version 1 cookies, we should strip leading and trailing " chars
				if ($pos == 0)
				{
				$cookiename = $tag;
				$GLOBALS['_xh']['cookies'][$tag] = array();
				$GLOBALS['_xh']['cookies'][$cookiename]['value'] = urldecode($val);
			}
			else
			{
			if ($tag != 'value')
			{
			$GLOBALS['_xh']['cookies'][$cookiename][$tag] = $val;
			}
			}
			}
			}
			}
			else
			{
			$GLOBALS['_xh']['headers'][$header_name] = trim($arr[1]);
			}
			}
			else if(isset($header_name))
			{
			///    @todo version1 cookies might span multiple lines, thus breaking the parsing above
			$GLOBALS['_xh']['headers'][$header_name] .= ' ' . trim($line);
			}
			}

			$data = substr($data, $bd);

			if($this->debug && count($GLOBALS['_xh']['headers']))
			{
			print '<PRE>';
			foreach($GLOBALS['_xh']['headers'] as $header => $value)
			{
			print htmlentities("HEADER: $header: $value\n");
			}
			foreach($GLOBALS['_xh']['cookies'] as $header => $value)
			{
			print htmlentities("COOKIE: $header={$value['value']}\n");
			}
			print "</PRE>\n";
			}

				// if CURL was used for the call, http headers have been processed,
				// and dechunking + reinflating have been carried out
				if(!$headers_processed)
				{
				// Decode chunked encoding sent by http 1.1 servers
				if(isset($GLOBALS['_xh']['headers']['transfer-encoding']) && $GLOBALS['_xh']['headers']['transfer-encoding'] == 'chunked')
				{
				if(!$data = decode_chunked($data))
					{
						error_log('XML-RPC: xmlrpcmsg::parseResponse: errors occurred when trying to rebuild the chunked data received from server');
						$r = new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['dechunk_fail'], $GLOBALS['xmlrpcstr']['dechunk_fail']);
				return $r;
			}
			}

				// Decode gzip-compressed stuff
				// code shamelessly inspired from nusoap library by Dietrich Ayala
			if(isset($GLOBALS['_xh']['headers']['content-encoding']))
			{
			$GLOBALS['_xh']['headers']['content-encoding'] = str_replace('x-', '', $GLOBALS['_xh']['headers']['content-encoding']);
			if($GLOBALS['_xh']['headers']['content-encoding'] == 'deflate' || $GLOBALS['_xh']['headers']['content-encoding'] == 'gzip')
			{
			// if decoding works, use it. else assume data wasn't gzencoded
			if(function_exists('gzinflate'))
							 {
							 if($GLOBALS['_xh']['headers']['content-encoding'] == 'deflate' && $degzdata = @gzuncompress($data))
							 {
							 $data = $degzdata;
							 if($this->debug)
							 	print "<PRE>---INFLATED RESPONSE---[".strlen($data)." chars]---\n" . htmlentities($data) . "\n---END---</PRE>";
							 }
							 else if($GLOBALS['_xh']['headers']['content-encoding'] == 'gzip' && $degzdata = @gzinflate(substr($data, 10)))
							 {
							 $data = $degzdata;
							 if($this->debug)
							 	print "<PRE>---INFLATED RESPONSE---[".strlen($data)." chars]---\n" . htmlentities($data) . "\n---END---</PRE>";
							 }
							 else
							 {
							  
							 error_log('XML-RPC: xmlrpcmsg::parseResponse: errors occurred when trying to decode the deflated data received from server');
							 $r = new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['decompress_fail'], $GLOBALS['xmlrpcstr']['decompress_fail']);
							 return $r;
							 }
							 }
							 else
							 {
							  
							 error_log('XML-RPC: xmlrpcmsg::parseResponse: the server sent deflated data. Your php install must have the Zlib extension compiled in to support this.');
							 $r = new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['cannot_decompress'], $GLOBALS['xmlrpcstr']['cannot_decompress']);
							 return $r;
							 }
							 }
							 }
							 } // end of 'if needed, de-chunk, re-inflate response'

							 // real stupid hack to avoid PHP 4 complaining about returning NULL by ref
							 $r = null;
							 $r =& $r;
							 	return $r;
							 }

							 		/**
							 		* Parse the xmlrpc response contained in the string $data and return an xmlrpcresp object.
							 		* @param string $data the xmlrpc response, eventually including http headers
							 			* @param bool $headers_processed when true prevents parsing HTTP headers for interpretation of content-encoding and consequent decoding
							 			* @param string $return_type decides return type, i.e. content of response->value(). Either 'xmlrpcvals', 'xml' or 'phpvals'
							 			* @return xmlrpcresp
							 			* @access public
							 				*/
							 				function &parseResponse($data='', $headers_processed=false, $return_type='xmlrpcvals')
							 				{
							 					if($this->debug)
							 					{
							 						//by maHo, replaced htmlspecialchars with htmlentities
							 					print "<PRE>---GOT---\n" . htmlentities($data) . "\n---END---\n</PRE>";
							 					}

							 						if($data == '')
							 						{
							 						error_log('XML-RPC: xmlrpcmsg::parseResponse: no response received from server.');
							 						$r = new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['no_data'], $GLOBALS['xmlrpcstr']['no_data']);
							 						return $r;
			}

			$GLOBALS['_xh']=array();

			$raw_data = $data;
			// parse the HTTP headers of the response, if present, and separate them from data
				if(substr($data, 0, 4) == 'HTTP')
							 						{
							 							$r =& $this->parseResponseHeaders($data, $headers_processed);
							 									if ($r)
							 									{
							 										// failed processing of HTTP response headers
							 										// save into response obj the full payload received, for debugging
                    $r->raw_data = $data;
                    return $r;
							 										}
							 										}
							 										else
							 										{
							 											$GLOBALS['_xh']['headers'] = array();
							 											$GLOBALS['_xh']['cookies'] = array();
							 										}

							 										if($this->debug)
							 										{
							 										$start = strpos($data, '<!-- SERVER DEBUG INFO (BASE64 ENCODED):');
							 										if ($start)
							 										{
							 										$start += strlen('<!-- SERVER DEBUG INFO (BASE64 ENCODED):');
							 										$end = strpos($data, '-->', $start);
							 										$comments = substr($data, $start, $end-$start);
							 												print "<PRE>---SERVER DEBUG INFO (DECODED) ---\n\t".htmlentities(str_replace("\n", "\n\t", base64_decode($comments)))."\n---END---\n</PRE>";
				}
				}

				// be tolerant of extra whitespace in response body
					$data = trim($data);

					/// @todo return an error msg if $data=='' ?

					// be tolerant of junk after methodResponse (e.g. javascript ads automatically inserted by free hosts)
							// idea from Luca Mariano <luca.mariano@email.it> originally in PEARified version of the lib
							$bd = false;
						// Poor man's version of strrpos for php 4...
						$pos = strpos($data, '</methodResponse>');
						while($pos || is_int($pos))
						{
                $bd = $pos+17;
						$pos = strpos($data, '</methodResponse>', $bd);
            }
								if($bd)
								{
								$data = substr($data, 0, $bd);
					}

					// if user wants back raw xml, give it to him
							if ($return_type == 'xml')
							 												{
							 												$r = new XmlRpcResp($data, 0, '', 'xml');
						$r->hdrs = $GLOBALS['_xh']['headers'];
						$r->_cookies = $GLOBALS['_xh']['cookies'];
						$r->raw_data = $raw_data;
                return $r;
					}

					// try to 'guestimate' the character encoding of the received response
						$resp_encoding = guess_encoding(@$GLOBALS['_xh']['headers']['content-type'], $data);

						$GLOBALS['_xh']['ac']='';
							//$GLOBALS['_xh']['qt']=''; //unused...
							$GLOBALS['_xh']['stack'] = array();
            $GLOBALS['_xh']['valuestack'] = array();
            $GLOBALS['_xh']['isf']=0; // 0 = OK, 1 for xmlrpc fault responses, 2 = invalid xmlrpc
            		$GLOBALS['_xh']['isf_reason']='';
            				$GLOBALS['_xh']['rt']=''; // 'methodcall or 'methodresponse'

							// if response charset encoding is not known / supported, try to use
							// the default encoding and parse the xml anyway, but log a warning...
								if (!in_array($resp_encoding, array('UTF-8', 'ISO-8859-1', 'US-ASCII')))
            // the following code might be better for mb_string enabled installs, but
            // makes the lib about 200% slower...
								//if (!is_valid_charset($resp_encoding, array('UTF-8', 'ISO-8859-1', 'US-ASCII')))
								{
										error_log('XML-RPC: xmlrpcmsg::parseResponse: invalid charset encoding of received response: '.$resp_encoding);
								$resp_encoding = $GLOBALS['xmlrpc_defencoding'];
			}
				$parser = xml_parser_create($resp_encoding);
				xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, true);
				// G. Giunta 2005/02/13: PHP internally uses ISO-8859-1, so we have to tell
				// the xml parser to give us back data in the expected charset.
				// What if internal encoding is not in one of the 3 allowed?
				// we use the broadest one, ie. utf8
				// This allows to send data which is native in various charset,
				// by extending xmlrpc_encode_entitites() and setting xmlrpc_internalencoding
				if (!in_array($GLOBALS['xmlrpc_internalencoding'], array('UTF-8', 'ISO-8859-1', 'US-ASCII')))
				{
				xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
				}
				else
				{
				xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $GLOBALS['xmlrpc_internalencoding']);
			}

			if ($return_type == 'phpvals')
				{
				xml_set_element_handler($parser, '\Innomatic\Webservices\Xmlrpc\xmlrpc_se', '\Innomatic\Webservices\Xmlrpc\xmlrpc_ee_fast');
				}
						else
						{
						xml_set_element_handler($parser, '\Innomatic\Webservices\Xmlrpc\xmlrpc_se', '\Innomatic\Webservices\Xmlrpc\xmlrpc_ee');
            }

            xml_set_character_data_handler($parser, '\Innomatic\Webservices\Xmlrpc\xmlrpc_cd');
            		xml_set_default_handler($parser, '\Innomatic\Webservices\Xmlrpc\xmlrpc_dh');

								// first error check: xml not well formed
								if(!xml_parse($parser, $data, count($data)))
								{
								// thanks to Peter Kocks <peter.kocks@baygate.com>
								if((xml_get_current_line_number($parser)) == 1)
								{
									$errstr = 'XML error at line 1, check URL';
								}
								else
								{
								$errstr = sprintf('XML error: %s at line %d, column %d',
									xml_error_string(xml_get_error_code($parser)),
									xml_get_current_line_number($parser), xml_get_current_column_number($parser));
								}
								error_log($errstr);
								$r=new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['invalid_return'], $GLOBALS['xmlrpcstr']['invalid_return'].' ('.$errstr.')');
								xml_parser_free($parser);
								if($this->debug)
								{
								print $errstr;
			}
			$r->hdrs = $GLOBALS['_xh']['headers'];
			$r->_cookies = $GLOBALS['_xh']['cookies'];
			$r->raw_data = $raw_data;
			return $r;
			}
			xml_parser_free($parser);
            // second error check: xml well formed but not xml-rpc compliant
            if ($GLOBALS['_xh']['isf'] > 1)
            {
            if ($this->debug)
            {
                    /// @todo echo something for user?
			}

			$r = new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['invalid_return'],
					$GLOBALS['xmlrpcstr']['invalid_return'] . ' ' . $GLOBALS['_xh']['isf_reason']);
			}
			// third error check: parsing of the response has somehow gone boink.
			// NB: shall we omit this check, since we trust the parsing code?
			else if ($return_type == 'xmlrpcvals' && !is_object($GLOBALS['_xh']['value']))
								{
								// something odd has happened
								// and it's time to generate a client side error
								// indicating something odd went on
								$r=new XmlRpcResp(0, $GLOBALS['xmlrpcerr']['invalid_return'],
										$GLOBALS['xmlrpcstr']['invalid_return']);
			}
			else
			{
								if ($this->debug)
								{
								print "<PRE>---PARSED---\n";
										// somehow htmlentities chokes on var_export, and some full html string...
										//print htmlentitites(var_export($GLOBALS['_xh']['value'], true));
										print htmlspecialchars(var_export($GLOBALS['_xh']['value'], true));
										print "\n---END---</PRE>";
										}

										// note that using =& will raise an error if $GLOBALS['_xh']['st'] does not generate an object.
										$v =& $GLOBALS['_xh']['value'];

										if($GLOBALS['_xh']['isf'])
										{
										/// @todo we should test here if server sent an int and a string,
										/// and/or coerce them into such...
										if ($return_type == 'xmlrpcvals')
										{
												$errno_v = $v->structmem('faultCode');
											$errstr_v = $v->structmem('faultString');
												$errno = $errno_v->scalarval();
												$errstr = $errstr_v->scalarval();
			}
			else
												{
												$errno = $v['faultCode'];
												$errstr = $v['faultString'];
			}

			if($errno == 0)
			{
				// FAULT returned, errno needs to reflect that
						$errno = -1;
			}

			$r = new XmlRpcResp(0, $errno, $errstr);
			}
			else
			{
			$r=new XmlRpcResp($v, 0, '', $return_type);
			}
			}

			$r->hdrs = $GLOBALS['_xh']['headers'];
			$r->_cookies = $GLOBALS['_xh']['cookies'];
			$r->raw_data = $raw_data;
					return $r;
			}
			}
