<?php namespace Iesod\Request;

class InputFile {
    private $file;
    private $multiFiles = false;
    private $pathUpload = './';
    public function __construct($file){
        if( is_array($file['error']) ){
            $this->multiFiles = true;
            foreach ($file['error'] as $code){
                $this->checkUploadError( $file['error'] );
            }
        } else {
            $this->checkUploadError( $file['error'] );
        }
        $this->file = $file;
    }
    private function checkUploadError($code){
        switch($code){
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;
            default:
                return true;
                break;
        }
        
        throw new \Exception($message,$code);
        return false;
    }
    public function setPathUpload($path){
        $this->pathUpload = $path;
        
        return $this;
    }
    public function multiFiles(){
        return $this->multiFiles;
    }
    public function extension($extensions,$msgError = null,$codError = null){
        if($this->multiFiles){
            foreach ($this->file['name'] as $name){
                if(!preg_match ("/.*[.]({$extensions})$/" , strtolower($name) ))
                    throw new \Exception($msgError??"File upload stopped by extension" ,$codError??UPLOAD_ERR_EXTENSION);
            }
        } else {
            if(!preg_match ("/.*[.]({$extensions})$/" , strtolower($this->file['name']) ))
                throw new \Exception($msgError??"File upload stopped by extension" ,$codError??UPLOAD_ERR_EXTENSION);
        }
            
        return $this;
    }
    public function type($index = null){
        if($this->multiFiles){
            return is_null($index)? $this->file['type'] : $this->file['type'][$index];
        } else {
            return $this->file['type'];
        }
    }
    public function size($index = null){
        if($this->multiFiles){
            return is_null($index)? $this->file['size'] : $this->file['size'][$index];
        } else {
            return $this->file['size'];
        }
    }
    public function name($index = null){
        if($this->multiFiles){
            return is_null($index)? $this->file['name'] : $this->file['name'][$index];
        } else {
            return $this->file['name'];
        }
    }
    public function move($filename, $index = null){
        if(!is_dir($this->pathUpload))
            throw new \Exception("Path upload not found" ,0);
        
        $filename = str_replace("/", DIRECTORY_SEPARATOR, realpath($this->pathUpload).'/'.$filename );
        
        if($this->multiFiles){
            if( is_null($index) )
                throw new \Exception("Index of file is required" ,0);
            
            return move_uploaded_file($this->file['tmp_name'][$index], $filename);
        } else {
            return move_uploaded_file($this->file['tmp_name'],  $filename);
        }
        
    }
}
