<?php 
/**
 * MIT License
 *
 * Copyright (c) 2017 Jonas Silva
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author    Jonas R. Silva
 * @copyright 2017 Jonas R. Silva
 * @license   MIT License
 * */
namespace Iesod;

use \DOMElement;
class HttpCurl {
    private $url; 
    private $SOAPAction;
    private $data;
    private $headers;
    /** Configura url da Chamada http
     * 
     * @param string $url
     */
    public function setUrl($url){
        $this->url = $url;
    }
    /** Configura SOAPAction da Chamada http
     *
     * @param string $action
     */
    public function setSOAPAction($action){
        $this->SOAPAction = $action;
    }
    /** Coloca a mensagem SOAP dentro de um envelope SOAP
     * 
     * @param DOMElement $body
     */
    public function envelopar(DOMElement $body){
        

        $envelope = new \DOMDocument('1.0', 'utf-8');
        $Envelope = $envelope->createElement('soap:Envelope');
        $Envelope->setAttributeNS( "http://www.w3.org/2000/xmlns/",'xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
        $Envelope->setAttributeNS( "http://www.w3.org/2000/xmlns/",'xmlns:xsd','http://www.w3.org/2001/XMLSchema');
        $Envelope->setAttributeNS( "http://www.w3.org/2000/xmlns/",'xmlns:soap','http://schemas.xmlsoap.org/soap/envelope/');
        
        $Body = $envelope->createElement('soap:Body');
        $body = $envelope->importNode($body, true);
        $Body->appendChild( $body );
        
        $Envelope->appendChild($Body);
        
        $envelope->appendChild($Envelope);
        $this->data = $envelope->saveXML();

        $this->headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ".$this->SOAPAction,
            "Content-length: ".strlen($this->data),
        );
    }
    public function setData($data)
    {
        $this->data = $data;
    }
    public function setHeaders($headers = []){
        $this->headers = $headers;
    }
    /** Executa a chamada
     * 
     * @throws \Exception
     * @return mixed
     */
    public function send($post = true){
        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, $post);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        
        $response = curl_exec($ch);
        if($response===false)
            throw new \Exception( curl_error($ch), curl_errno($ch) );
        
        
        curl_close($ch);
        
        return $response;
    }
}