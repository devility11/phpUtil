<?php


class Util {
    
    /**
     * search in a multi array based on the value and the field name and then remove it by the key
     * 
     */
    public function checkValueInArrayByKey() {
        
        $myarray = array(
            "0" => array("whichField" => "ssss", "whichFieldNot" => "sssssa"), 
            "1" => array("whichField" => "bbbb", "whichFieldNot" => "bbbbx"), 
            "2" => array("whichField" => "whatIneed", "whichFieldNot" => "eeee") 
        );
        //result : 2
        if(array_search('whatIneed', array_column($myarray, 'whichField')) !== false) {
            $key = array_search('whatIneed', array_column($myarray, 'whichField'));
            unset($myarray[$key]);
        }
    }
    
    
    /**
     * 
     * Read big files line by line to avoid memory problems
     * 
     * @param string $dir
     * @return string
     */
    public function readFileLineByLine(string $dir): string{
        $buffer = "";
        $handle = fopen($dir, "r");
        if ($handle) {
            while (!feof($handle)) {
                $buffer .= stream_get_line($handle, 4096);
            }
            fclose($handle);
        }
        
        return $buffer;
    }

    /**
     * 
     * Clean the string
     * 
     * @param type $string
     * @return string
     */
    function clean($string): string {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

     /**
     * 
     *  Check the file and/or size duplications in multidimensional arrays
     * 
     * @param array $data
     * @return array
     */
    public function checkFileDuplications(array $data): array{
        
        $result = array();
        foreach ($data as $current_key => $current_array) {
            foreach ($data as $search_key => $search_array) {
                if ( $search_array['filename'] == $current_array['filename'] ) {
                    if ($search_key != $current_key) {
                        if( $current_array['size'] == $search_array['size'] ){
                            $return = array("errorType" => "Duplicate_File_And_Size", "filename" => $current_array['filename'], "dir" => $current_array['dir']);
                        }else {
                            $return = array("errorType" => "Duplicate_File", "filename" => $current_array['filename'], "dir" => $current_array['dir']);
                        }
                    }
                }
            }
        }
        return $result;
    }


    /**
     * 
     * Check windows and linux directorys
     * 
     * @param string $dir
     * @return bool
     */
    public function checkDirectoryNameValidity(string $dir): bool {
        
        if(preg_match("#^(?:[a-zA-Z]:|\.\.?)?(?:[\\\/][a-zA-Z0-9_.\'\"-]*)+$#", $dir) !== 1){
            return false;
        }else{
            return true;
        }

    }
     
    /**
     * 
     * Array Unique function to multidimensional arrays
     * 
     * @param array $data
     * @param string $key
     * @return array
     */
    public function arrUniqueToMultiArr(array $data, string $key): array{
        
        if(empty($data) || empty($key)){ return array(); }
        
        $return = array();
        
        foreach ($data as $d) {
            $return[] = $d[$key];
        }
        $return = array_unique($return);
        
        return $return;
        
    }
    
    
     /**
     * 
     * Check the array if there is a string inside it
     * 
     * @param array $data
     * @param string $str
     * @return bool
     */
    public function checkArrayForValue(array $data, string $str):bool {
        
        if(count($data) > 0){
            foreach($data as $item){
                if(strpos($item, $str)!== false){
                    return true;
                }
            }
        }
        
        return false;
    }
    
    
    
    /**
     * 
     * Check the value in the array
     * 
     * @param type $needle -> strtolower value of the string
     * @param type $haystack -> the array where the func should serach
     * @param type $strict
     * @return boolean
     */
    function checkMultiDimArrayForValue($needle, $haystack, $strict = false) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->checkMultiDimArrayForValue($needle, $item, $strict))) {
                return true;
            }
        }
        return false;
    }
    
    function in_array_r(string $needle, array $haystack, bool $strict = false, array &$keys): bool {
        foreach ($haystack as $key => $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict, $keys))) {
                //we checking only the propertys
                if (strpos($key, ':') !== false) {
                    $keys[$key] = $needle;
                }
                return true;
            }
        }
        return false;
    }
    
    
    /**
     * 
     * THis func is generating a child based array from a single array
     * 
     * @param array $flat
     * @param type $idField
     * @param type $parentIdField
     * @param type $childNodesField
     * @return type
     */
    public function convertToTree(
        array $flat, $idField = 'id', $parentIdField = 'parentId',
        $childNodesField = 'children') {
        
        $indexed = array();
        // first pass - get the array indexed by the primary id  
        foreach ($flat as $row) {
            $indexed[$row[$idField]] = $row;
            $indexed[$row[$idField]][$childNodesField] = array();
        }
   
        //second pass  
        $root = null;
        foreach ($indexed as $id => $row) {
            $indexed[$row[$parentIdField]][$childNodesField][] =& $indexed[$id];
            if (!$row[$parentIdField] || empty($row[$parentIdField])) {
                
                $root = $id;
            }
        }
        return array($indexed[$root]);
    }
    
    
     /**
     * 
     * Calculate the estimated Download time for the collection
     * 
     * @param int $binarySize
     * @return string
     */
    public function estDLTime(int $binarySize): string{
        
        $result = "";
        if($binarySize < 1){ return $result; }
        
        $kb=1024;
        flush();
        $time = explode(" ",microtime());
        $start = $time[0] + $time[1];
        for( $x=0; $x < $kb; $x++ ){
            str_pad('', 1024, '.');
            flush();
        }
        $time = explode(" ",microtime());
        $finish = $time[0] + $time[1];
        $deltat = $finish - $start;
        
        $input = (($binarySize / 512) * $deltat);
        $input = floor($input / 1000);
        $seconds = $input;
        
        if($seconds > 0){
            //because of the zip time we add
            $result = round($seconds * 1.35) * 4;
            return $result;
        }
        
        return $result;
    }
    
     /**
     * 
     * Create nice format from file sizes
     * 
     * @param type $bytes
     * @return string
     */
    public function formatSizeUnits(string $bytes): string
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
    
     /**
     * 
     * Get the keys from a multidimensional array
     * 
     * @param array $arr
     * @return array
     */
    public function getKeysFromMultiArray(array $arr): array{
     
        foreach($arr as $key => $value) {
            $return[] = $key;
            if(is_array($value)) $return = array_merge($return, $this->getKeysFromMultiArray($value));
        }
        
        //remove the duplicates
        $return = array_unique($return);
        
        //remove the integers from the values, we need only the strings
        foreach($return as $key => $value){
            if(is_numeric($value)) unset($return[$key]);
        }
        
        return $return;
    }
    
    
    
}