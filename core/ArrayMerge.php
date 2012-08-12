<?php
namespace beaba\core;

/**
 * An simple value structure
 */
class ValueObject
{
    private $_value;

    /**
     * Initialize a value wrapper
     * @param mixed $value
     */
    public function __construct( $value )
    {
        $this->_value = $value;
    }

    /**
     * Converts as code
     * @return string
     */
    public function __toString()
    {
        if ( is_string( $this->_value ) ) {
            return '\'' . addslashes($this->_value) . '\'';
        } elseif (is_bool( $this->_value ) ) {
            return $this->_value ? 'true' : 'false';
        } elseif ( is_numeric( $this->_value ) ) {
            return $this->_value;
        } else {
            throw new \InvalidArgumentException(
                'Unable to convert as code the specified scallar value'
            );
        }
    }
}

/**
 * The array structure
 */
class ArrayObject extends ValueObject
{
    private $_values = array();
    private $_keys = array();

    /**
     * Initialize a new array
     */
    public function __construct()
    {
        parent::__construct( null );
    }

    /**
     * Sets a key/value pair
     * @param ValueObject $key
     * @param ValueObject $value
     * @return ArrayObject
     */
    public function set( ValueObject $key, ValueObject $value )
    {
        if ( $key instanceof EmptyObject ) {
            $this->_keys[] = $key;
            $this->_values[] = $value;
        } else {
            $k = $key->__toString();
            if ( 
                isset($this->_keys[ $k ]) 
                && $this->_values[ $k ] instanceof ArrayObject
                && $value instanceof ArrayObject
            ) {
                // merge with current array
                foreach($value->getKeys() as $key) {
                    $this->_values[ $k ]->set(
                        $key, $value->get( $key )
                    );
                }
            } else {
                $this->_keys[ $k ] = $key;
                $this->_values[ $k ] = $value;
            }
        }
    }

    /**
     * Retrieves a list of keys
     */
    public function getKeys()
    {
        return $this->_keys;
    }

    /**
     * Gets a value from the specified key
     * @param ValueObject $key
     * @return ValueObject|null
     */
    public function get( ValueObject $key )
    {
        $k = $key->__toString();
        if ( isset( $this->_keys[ $k ] ) ) {
            return $this->_values[ $k ];
        } else {
            return null;
        }
    }

    /**
     * Shows an array contents code
     * @return string
     */
    public function __toString()
    {
        $result = 'array(';
        foreach( $this->_keys as $offset => $value ) {
            if ( !($value instanceof EmptyObject) ) {
                $result .= $value->__toString();
                $result .= ' => ';
            }
            $result .= $this->_values[ $offset ]->__toString() . ','."\n";
        }
        return $result . ')';
    }
}

/**
 * Description of ArrayMerge
 *
 * @author ichiriac
 */
class ArrayMerge extends ArrayObject
{

    /**
     * Parse the specified file and merge to current data
     * @param type $filename
     * @return ArrayMerge
     */
    public function addFile( $filename )
    {
        $tokens = token_get_all( file_get_contents( $filename ) );
        $found = false;
        foreach( $tokens as $offset => $tok ) {
            if ( $tok[0] === T_RETURN ) {
                $tokens = array_slice($tokens, $offset + 2);
                $found = true;
                break;
            }
        }
        if ( !$found ) {
            throw new \Exception(
                'Required a T_RETURN statement'
            );
        }
        return $this->parseTokens($tokens);
    }

    private function debug($tok) {
        if ( is_array($tok) ) {
            echo token_name($tok[0]) . ' - ' . $tok[1] . "\n";
        } else {
            echo $tok . "\n";
        }
    }
    
    /**
     *
     * @param type $tokens
     * @param type $offset
     * @return ArrayMerge 
     */
    public function parseTokens( $tokens, $offset = 2 ) {
        if ( $tokens[$offset - 2][0] !== T_ARRAY ) {
            throw new \Exception(
                'Unexpected ' . token_name($tokens[$offset - 2][0]) 
                . ' - expect a T_ARRAY'
            );
        }
        /*if ( $offset === 2) {
            // debug
            foreach( $tokens as $tok ) {
                $this->debug( $tok );
            }
            echo '-----------'."\n";
        }*/
        $size = count( $tokens );
        $key = array();
        $value = array();
        for($i = $offset; $i < $size; $i++ ) {
            $i = $this->eatKey( $tokens, $i, $key );
            //$this->debug( $tokens[$i] );
            if ( 
                $tokens[$i] === ')' 
                || $tokens[$i] === ','
            ) {
                $value = $key;
                $key = array();
                //echo 'Empty key with : ';
                //$this->debug($value[0]);
            } else {
                $i = $this->eatValue( $tokens, $i, $value );
            }
            // convert the key to object
            if ( empty($key) ) {
                $key = new EmptyObject();
            } elseif ( 
                count($key) === 1 
                && (
                    $key[0][0] === T_CONSTANT_ENCAPSED_STRING
                    || $key[0][0] === T_LNUMBER
                )
            ) {
                if ( $key[0][0] === T_CONSTANT_ENCAPSED_STRING ) {
                    $key = new ValueObject( 
                        stripslashes(
                            substr(
                                $key[0][1], 1, 
                                strlen($key[0][1]) - 2
                            ) 
                        )
                    );
                } else {
                    $key = new ValueObject( (int)$key[0][1] );
                }
                
            } else {
                $key = new TokenObject( $key );
            }
            // convert the value
            if ( 
                count($value) === 1 
                && $value[0] instanceof ArrayMerge
            ) {
                $value = $value[0];
            } else {
                //print_r($value);
                $value = new TokenObject( $value );
            }
            //echo ' * ' . $key->__toString() . ' : ' . $value->__toString() . "\n\n";
            $this->set($key, $value);
            if ( $tokens[$i][0] === ')' ) {
                //echo '-- Result : ' . $this->__toString()."\n---\n";
                return $i;
            }
        }
        throw new \Exception('Unexpected end');
    }
    
    protected function eatKey( &$tokens, $offset, &$key ) 
    {
        $key = array();
        $size = count( $tokens );
        for($i = $offset; $i < $size; $i++ ) {
            $tok = $tokens[$i];
            if ( $tok[0] === ',' || $tok[0] === ')' ) return $i;
            // calculate the array key
            if ( $tok[0] !== T_DOUBLE_ARROW ) {
                if ( $tok[0] !== T_WHITESPACE ) {
                    $key[] = $tok;
                }
            } else {
                return $i + 1;
            }
        }
        throw new \Exception('Unexpected end');
    }
    
    protected function eatValue( &$tokens, $offset, &$value ) 
    {
        $value = array();
        $size = count( $tokens );
        $para = 0;
        for($i = $offset; $i < $size; $i++ ) {
            $tok = $tokens[$i];
            // calculate the array key
            if ( $tok[0] ===  T_WHITESPACE ) continue;
            if ( $tok[0] ===  T_ARRAY ) {
                $array = new ArrayMerge();
                $i = $array->parseTokens($tokens, $i + 2);
                $value[] = $array;
            } elseif ( $tok[0] ===  T_FUNCTION ) {
                $i = $this->eatFunction($tokens, $i, $value);
            } else {
                if ( $tok[0] === '(') $para ++;
                if ( $para === 0 && ($tok[0] === ',' || $tok[0] === ')') ) {
                    return $i;
                } else {
                    if ( $tok[0] === ')') $para --;
                    $value[] = $tok;
                }
            }
        }
        throw new \Exception('Unexpected value end');
    }
    
    protected function eatFunction( &$tokens, $offset, &$value ) 
    {
        $size = count( $tokens );
        $level = 0;
        for( $i = $offset; $i < $size; $i++ ) {
            $tok = $tokens[$i];
            $value[] = $tok;
            if ( $tok[0] === '{' ) {
                $level ++;
            } elseif ( $tok[0] === '}' ) {
                $level --;
                if ( $level === 0 ) {
                    return $i;
                }
            }
        }
        throw new \Exception('Unexpected end');
    }
    /**
     * Merge current object with the specified informations data
     * @param array $data
     * @return ArrayMerge
     */
    public function addData( array $data )
    {
        foreach( $data as $key => $value )
        {
            if ( is_numeric( $key ) ) {
                $this->set(
                    new EmptyObject(),
                    $this->_createValue( $value )
                );
            } else {
                $this->set(
                    new ValueObject($key),
                    $this->_createValue( $value )
                );
            }
        }
        return $this;
    }

    /**
     * Creates a ValueObject from the specified value
     * @param mixed $value
     * @return ValueObject
     */
    protected function _createValue( $value ) {
        if ( is_array( $value ) ) {
            $result = new ArrayObject();
            foreach( $value as $key => $item ) {
                if ( is_numeric( $key ) ) {
                    $result->set(
                        new EmptyObject(),
                        $this->_createValue( $item )
                    );
                } else {
                    $result->set(
                        new ValueObject( $key ),
                        $this->_createValue( $item )
                    );
                }
            }
            return $result;
        } else {
            return new ValueObject( $value );
        }
    }
}

/**
 * An empty object structure
 */
class EmptyObject extends ValueObject
{
    public function __construct() {
        parent::__construct( null );
    }

    public function __toString() {
        return null;
    }
}

/**
 * A tokenized structure (used by closure values)
 */
class TokenObject extends ValueObject
{
    private $_tokens = array();
    /**
     * Initialize the token object
     * @param array $tokens
     */
    public function __construct( array $tokens )
    {
        $this->_tokens = $tokens;
    }

    /**
     * Returns the php code of a tokenized object
     * @return string
     */
    public function __toString()
    {
        $result = '';
        if ( !empty($this->_tokens) ) {
            foreach( $this->_tokens as $tok ) {
                if ( is_string($tok) ) {
                    $result .= $tok;
                } else {
                    $result .= $tok[1];
                }
            }
        } else {
            $result = 'null';
        }
        return $result;
    }
}