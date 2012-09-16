<?php

namespace beaba\core {

    /**
     * Defines a storage exception from CRUD actions
     */
    class StorageException extends \Exception
    {
        
    }

    /**
     * Defines a default request engine over the specified storage
     * 
     * Sample :
     * 
     * - count all books
     * $books->query('SELECT b FROM books b')->count();
     * 
     * - select books with criteria
     * $books->query(
     *      'SELECT b FROM books b WHERE b.title LIKE %pattern%',
     *      array(
     *          'pattern' => '%search%'
     *      )
     * );
     * 
     * 
     */
    class StorageRequest implements IStorageRequest
    {

        /**
         * @var IStorageDriver
         */
        protected $_storage;

        /**
         * @var IModel
         */
        protected $_model;

        /**
         * @var Request
         */
        protected $_request;

        /**
         * @var IStorageStatement
         */
        protected $_reader;

        /**
         * @var The resultset size
         */
        protected $_count = false;

        protected $_buffer;
        
        /**
         * Init the request
         * @param IStorageDriver $storage
         * @param IModel $model
         * @param array $statement
         */
        final public function __construct(
        IStorageDriver $storage, IModel $model, storage\Request $request
        )
        {
            $this->_storage = $storage;
            $this->_model = $model;
            $this->_request = $request;
            $this->_init();
        }

        /**
         * Called after the object initialization
         */
        protected function _init()
        {
            
        }

        /**
         * Gets the reader statement
         * @return IStorageStatement
         */
        protected function _getReader()
        {
            if (!$this->_reader) {
                $this->_reader = $this->_storage->query(
                    $this->_request->getSql()
                );
            }
            return $this->_reader;
        }

        /**
         * The requested model
         * @return IModel
         */
        public function getModel()
        {
            return $this->_model;
        }

        /**
         * The storage instance
         * @return IStorageDriver
         */
        public function getStorage()
        {
            return $this->_storage;
        }

        /**
         * Check if the reader has results
         * @returns boolean
         */
        public function hasResults()
        {
            return false; // @todo
        }

        
        /**
         * Count number to results in the current recordset
         * @return integer
         */
        public function count()
        {
            if ($this->_count === false) {
                $column = 'countResultSize';
                $result = $this->_storage->query(
                    $this->_request->setCount($column)->getSql()
                );
                $this->_request->removeColumn($column);
                $result = $result->next();
                $this->_count = (int) $result[$column];
            }
            return $this->_count;
        }

        /**
         * Gets the first item
         * @return mixed 
         */
        public function first() {
            if ( !is_array($this->_buffer) ) {
                if ( !$this->_reader ) {
                    $this->_request->setLimit(0, 1);
                }
                $this->rewind();
                $this->current();
            }
            return $this->_buffer[0];
        }
        
        public function current()
        {
            // @todo
        }

        public function key()
        {
            // @todo
        }

        public function next()
        {
            // @todo
        }

        public function rewind()
        {
            // @todo
        }

        public function valid()
        {
            // @todo
        }

    }

    /**
     * @inheritdoc
     */
    abstract class StorageDriver implements IStorageDriver
    {

        protected $_parser;

        /**
         * @return beaba\core\storage\ParamParser
         */
        protected function getParser()
        {
            if (!$this->_parser) {
                $this->_parser = new storage\ParamParser();
            }
            return $this->_parser;
        }

        /**
         * @inheritdoc
         */
        public function select(
        IModel $target, $statement, array $parameters = null
        )
        {
            $request = $this->getParser()->read(
                $statement
            );
            $request->setModel( $target );
            if ($parameters) {
                foreach ($parameters as $name => $value) {
                    $request->setParam($name, $value);
                }
            }
            return $this->createRequest($target, $request);
        }

        /**
         * Creates a request object
         * @param storage\Request $request 
         */
        protected function createRequest(
        IModel $target, storage\Request $request
        )
        {
            return new \beaba\core\StorageRequest(
                    $this, $target, $request
            );
        }

    }

}

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 * @link https://github.com/ichiriac/sql-parser
 */

namespace beaba\core\storage {

    /**
     * Defines a parser exception
     */
    class ParseException extends \Exception
    {
        
    }

    /**
     * The parser only supports CRUD requests
     */
    class UnsuportedMethod extends \Exception
    {
        
    }

    /**
     * The SQL storage parser
     */
    class Parser
    {

        /**
         * Reads the specified statement
         * @param string $statement
         * @return array
         */
        public function read($statement)
        {
            // split the statement into tokens
            $tokens = $this->tokenize($statement);
            $size = count($tokens);
            $offset = 0;
            $result = array();
            // parsing the result and analyse the request
            foreach ($this->analyze($tokens, $size, $offset) as $f => $prop) {
                switch ($f[0]) {
                    case '-':
                        switch ($f[1]) {
                            case 's':
                                $result[$f] = $this->parseSelect($prop);
                                break;
                            default:
                                throw new UnsuportedMethod(
                                    'Unable to handle : ' . $f
                                );
                        }
                        break;
                    case 'f':
                        $result[$f] = $this->parseFrom($prop);
                        break;
                    case 'j':
                        if (empty($result['j']))
                            $result['j'] = array();
                        unset($result[$f]);
                        $join = $this->parseJoin($prop);
                        if (empty($join['a'])) {
                            $result['j'][] = $join;
                        } else {
                            $result['j'][$join['a']] = $join;
                        }
                        break;
                    case 'w':
                        $result[$f] = $this->parseCriteria($prop);
                        break;
                    case 'o':
                        $result[$f] = $this->parseOrders($prop);
                        break;
                    case 'g':
                        $result[$f] = $this->parseGroup($prop);
                        break;
                    case 'l':
                        $result[$f] = $this->parseLimit($prop);
                        break;
                    default:
                        list($f, $prop) = $this->handleParse($f, $prop);
                        $result[$f] = $prop;
                }
            }
            return $result;
        }

        /**
         * Automatically parse the data
         * @param string $method
         * @param array $props
         * @return array 
         */
        protected function handleParse($method, $props)
        {
            return array($method, $props);
        }

        /**
         * Parse the group by statement
         * @param array $props
         * @return array 
         */
        protected function parseGroup(array $props)
        {
            $result = array();
            if (strtolower($props[0]) === 'by') {
                array_shift($props);
            }
            $len = count($props);
            for ($i = 0; $i < $len; $i+=2) {
                $result[] = $props[$i];
                if (!empty($props[$i + 1]) && $props[$i + 1] !== ',') {
                    throw new ParseException(
                        'Expect [,] to separe group by columns'
                    );
                }
            }
            return $result;
        }

        /**
         * Parsing a list of orders statement
         * @param array $props
         * @return array
         */
        protected function parseOrders(array $props)
        {
            $result = array();
            if (strtolower($props[0]) === 'by') {
                array_shift($props);
            }
            $len = count($props);
            for ($i = 0; $i < $len; $i+=2) {
                $item = array(
                    $props[$i]
                );
                if (!empty($props[$i + 1])) {
                    $next = strtolower($props[$i + 1]);
                    if ($next === ',') {
                        $item[] = true;
                    } elseif ($next === 'asc' || $next === 'desc') {
                        $i++;
                        $item[] = $next === 'asc';
                    } else {
                        throw new ParseException(
                            'Unexpected order token ' . $next
                        );
                    }
                    if (!empty($props[$i + 1]) && $props[$i + 1] !== ',') {
                        throw new ParseException(
                            'Expect [,] to separe order columns'
                        );
                    }
                }
                $result[] = $item;
            }
            return $result;
        }

        /**
         * Parsing the limit statement
         * @param array $props
         * @return array
         */
        protected function parseLimit(array $props)
        {
            $len = count($props);
            if ($len === 1) {
                return array(
                    0, $props[0]
                );
            } elseif ($len === 3) {
                return array(
                    $props[0], $props[2]
                );
            } else {
                throw new ParseException(
                    'Bad limit syntax'
                );
            }
        }

        /**
         * Parse list of join properties
         * @param array $props
         * @return array
         */
        protected function parseJoin(array $props)
        {
            $result = array(
                '$' => $props[0],
                't' => $props[1]
            );
            if (strtolower($props[2]) === 'as') {
                $result['a'] = $props[3];
                $offset = 4;
            } elseif (strtolower($props[2]) !== 'on') {
                $result['a'] = $props[2];
                $offset = 3;
            } else {
                $offset = 2;
            }
            if (strtolower($props[$offset]) !== 'on') {
                throw new ParseException(
                    'Expect ON statement in join'
                );
            }
            $result['c'] = $this->parseCriteria($props, $offset + 1);
            return $result;
        }

        /**
         * Parsing a list of criterias
         * @param array $props
         */
        protected function parseCriteria(array $props, $offset = 0)
        {
            $result = array();
            $compare = array('<', '>', '=');
            $len = count($props);
            for (; $offset < $len; $offset += 4) {
                $criteria = array(
                    'f' => $props[$offset]
                );
                if (is_array($props[$offset + 1])) {
                    $offset--;
                    $criteria['c'] = '';
                } else {
                    $criteria['c'] = strtolower($props[$offset + 1]);
                }
                if (in_array($props[$offset + 2], $compare)) {
                    $offset += 1;
                    $criteria['c'] .= $props[$offset + 1];
                }
                if ($criteria['c'] === 'between') {
                    $offset++;
                    if (strtolower($props[$offset + 2]) !== 'and') {
                        throw new ParseException(
                            'Expect AND for the between statement'
                        );
                    }
                    $offset++;
                    $props[$offset + 2] = array(
                        $props[$offset],
                        $props[$offset + 2]
                    );
                } elseif ($criteria['c'] === 'is') {
                    if (strtolower($props[$offset + 2]) === 'not') {
                        $offset++;
                        $criteria['c'] .= ' not';
                    }
                }
                if (is_array($props[$offset + 2])) {
                    $criteria['c'] .= strtolower($props[$offset + 2]['$']);
                    $criteria['v'] = $props[$offset + 2]['?'];
                } else {
                    $criteria['v'] = $props[$offset + 2];
                }

                $result[] = $criteria;
                if (!empty($props[$offset + 3])) {
                    if (is_array($props[$offset + 3])) {
                        $result[] = strtolower($props[$offset + 3]['$']);
                        $result[] = $this->parseCriteria($props[$offset + 3]['?']);
                        $offset++;
                        if (!empty($props[$offset + 3])) {
                            $result[] = strtolower($props[$offset + 3]);
                        }
                    } else {
                        $result[] = strtolower($props[$offset + 3]);
                    }
                }
            }
            return $result;
        }

        /**
         * Parsing a select statement
         * @param array $props
         * @return array 
         */
        protected function parseSelect(array $props)
        {
            $result = array();
            $len = count($props);
            for ($i = 0; $i < $len; $i += 2) {
                $tok = $props[$i];
                if (!empty($props[$i + 1])) {
                    $next = $props[$i + 1];
                    if ($next === ',') {
                        $result[] = $tok;
                    } elseif (strtolower($next) === 'as') {
                        // field as alias
                        $i += 2;
                        $result[$props[$i]] = $tok;
                    } else {
                        // field alias
                        $i += 1;
                        $result[$next] = $tok;
                        if (!empty($props[$i + 1]) && $props[$i + 1] !== ',') {
                            throw new ParseException(
                                'Bad select syntax : unexpected ['
                                . $props[$i + 1]
                                . '] expecting [,]'
                            );
                        }
                    }
                } else {
                    $result[] = $tok;
                }
            }
            return $result;
        }

        /**
         * Parsing a from statement
         * @param array $props 
         */
        protected function parseFrom(array $props)
        {
            $result = array();
            $len = count($props);
            for ($i = 0; $i < $len; $i += 2) {
                $tok = $props[$i];
                if (!empty($props[$i + 1])) {
                    $next = $props[$i + 1];
                    if ($next === ',') {
                        $result[] = $tok;
                    } elseif (strtolower($next) === 'as') {
                        // field as alias
                        $i += 2;
                        $result[$props[$i]] = $tok;
                    } else {
                        // field alias
                        $i += 1;
                        $result[$next] = $tok;
                        if (!empty($props[$i + 1]) && $props[$i + 1] !== ',') {
                            throw new ParseException(
                                'Bad from syntax : expecting [,]'
                            );
                        }
                    }
                } else {
                    $result[] = $tok;
                }
            }
            return $result;
        }

        /**
         * Cut the statement in tokens
         * @param string $statement
         * @return array
         */
        protected function tokenize($statement)
        {
            // tokenizer
            $ignore = array(' ', "\n", "\t");
            $separators = array(',', '=', '<', '>', '(', ')');
            $tokens = array();
            $statement = trim($statement);
            $len = strlen($statement);
            $offset = 0;
            $next = false;
            $textMode = null;
            for ($i = 0; $i < $len; $i++) {
                $char = $statement[$i];
                if (!$textMode) {
                    if (in_array($char, $ignore)) {
                        $next = true;
                        continue;
                    }
                    if (in_array($char, $separators)) {
                        $tokens[++$offset] = $char;
                        $next = true;
                        continue;
                    }
                    if ($next) {
                        $offset++;
                        $next = false;
                    }
                    if (empty($tokens[$offset]))
                        $tokens[$offset] = '';
                } else {
                    if ($char === '\\') {
                        $char .= $statement[++$i];
                    }
                }
                if ($char === '\'' || $char === '\"') {
                    if ($textMode === $char) {
                        $next = true;
                        $textMode = null;
                    } else {
                        $textMode = $char;
                    }
                }
                $tokens[$offset] .= $char;
            }
            return $tokens;
        }

        /**
         * Analyse the tokens structure and build a request tree
         * @param array $tokens
         * @param integer $size
         * @param integer $offset
         * @return array 
         */
        protected function analyze(array $tokens, $size, &$offset)
        {
            // reads the request type
            switch (strtolower($tokens[$offset])) {
                case 'select':
                    $key = '-s';
                    break;
                case 'update':
                    $key = '-u';
                    break;
                case 'delete':
                    $key = '-d';
                    break;
                case 'insert':
                    $key = '-i';
                    break;
                default:
                    $key = '?';
            }
            if ($key[0] === '-')
                $offset++;
            // building a parsing tree
            $structure = array();
            for (; $offset < $size; $offset++) {
                $tok = $tokens[$offset];
                if ($k = $this->isKeyword(
                    strtolower($tok), $tokens, $offset, $structure
                )) {
                    $key = $k;
                } else {
                    if (empty($structure[$key]))
                        $structure[$key] = array();
                    if ($tok === '(') {
                        $tok = $this->analyze($tokens, $size, ++$offset);
                        $tok['$'] = array_pop($structure[$key]);
                        $structure[$key][] = $tok;
                        continue;
                    }
                    if ($tok === ')') {
                        return $structure;
                    }
                    $structure[$key][] = $this->addToken($tok);
                }
            }
            return $structure;
        }

        /**
         * @var integer Join incrementation
         */
        private $j = 0;

        /**
         * Check if the specified token is a keyword
         * @param string $token
         * @param integer $offset
         * @param array $structure
         * @return string 
         */
        protected function isKeyword($token, &$tokens, &$offset, &$structure)
        {
            switch ($token) {
                case 'from':
                    return 'f';
                case 'inner':
                case 'outter':
                case 'left':
                case 'right':
                    if (strtolower($tokens[$offset + 1]) === 'join') {
                        $offset++;
                        $key = 'j' . ($this->j++);
                        $structure[$key] = array($token);
                        return $key;
                    } else {
                        return false;
                    }
                case 'join':
                    $key = 'j' . ($j++);
                    $structure[$key] = array('left');
                    return $key;
                case 'where':
                    return 'w';
                case 'order':
                    return 'o';
                case 'limit':
                    return 'l';
                case 'group':
                    return 'g';
            }
            return false;
        }

        /**
         * Add a token
         * @param string $token
         * @return mixed
         */
        protected function addToken($token)
        {
            return $token;
        }

    }

    /**
     * Defines a parser that handles named parameters
     */
    class ParamParser extends Parser
    {

        protected $params = array();

        protected function isKeyword($token, &$tokens, &$offset, &$structure)
        {
            if ($token === 'with') {
                return '+';
            } else {
                return parent::isKeyword($token, $tokens, $offset, $structure);
            }
        }

        protected function handleParse($method, $props)
        {
            if ($method === '+') {
                $result = array();
                foreach ($props as $prop) {
                    $result[$prop['$']] = $prop;
                }
                return array(
                    '+', $result
                );
            } else {
                parent::handleParse($method, $props);
            }
        }

        // Injecting the request object
        public function read($statement)
        {
            $this->params = array();
            return new Request(
                    parent::read($statement),
                    $this->params
            );
        }

        // lazy load any parameter
        protected function getParam($name)
        {
            if (!isset($this->params[$name])) {
                $this->params[$name] = new Param($name);
            }
            return $this->params[$name];
        }

        // intercept parameters from token parsing 
        protected function addToken($token)
        {
            if ($token[0] === ':') {
                return $this->getParam(substr($token, 1));
            } else {
                return parent::addToken($token);
            }
        }

    }

    // defines a simple parameter class
    class Param
    {

        protected $name;
        protected $value;

        public function __construct($name)
        {
            $this->name = $name;
        }

        public function setValue($value)
        {
            $this->value = $value;
        }

    }

    // defines a simple request class
    class Request
    {

        protected $_request;
        protected $_params;
        protected $_sql;
        /**
         * @var IModel
         */
        protected $_model;

        public function __construct($request, $params)
        {
            $this->_request = $request;
            $this->_params = $params;
        }

        public function setModel( \beaba\core\IModel $model ) {
            $this->_model = $model;
            if ( empty($this->_request['-s']) ) {
                $this->_request['-s'] = array('*');
            }
            if ( empty($this->_request['f']) ) {
                $this->_request['f'] = array(
                    $model->getIdentifier()
                );
            }
        }
        
        public function setParam($name, $value)
        {
            if (!isset($this->_params[$name])) {
                throw new \OutOfBoundsException(
                    'Undefined parameter : ' . $name
                );
            }
            $this->_params[$name]->setValue($value);
            return $this;
        }

        /**
         * Gets a function definition
         * @param type $name
         * @param array $args
         * @return type 
         */
        public function getFunction( $name, array $args = null ) {
            return array(
                '?' => empty($args) ? array() : $args
                ,'$' => $name
            );
        }
        
        /**
         * Adds a count column
         * @param string $alias 
         * @return Request
         */
        public function setCount( $alias = 0 ) {
            $this->_request['-s'] = array(
                $alias => $this->getFunction(
                    'count', array('*')
                )
            );
            return $this;
        }
        
        public function setLimit( $start, $size ) {
            
        }
        /**
         * Gets the SQL statement
         * @return string
         */
        public function getSql()
        {
            print_r($this->_request);
            $sql = 'SELECT ';
            // SELECT FIELDS
            foreach($this->_request['-s'] as $alias => $def) {
                if ( is_array($def) ) {
                    $sql .= $def['$'].'('.implode(',', $def['?']).')';
                } else {
                    $sql .= $def;
                }
                if ( !is_numeric($alias) ) {
                    $sql .= ' as ' . $alias;
                }
                $sql .= ', ';
            }
            // FROM TABLES
            $sql = rtrim($sql, ', ') . ' FROM ';
            foreach($this->_request['f'] as $alias => $def) {
                $sql .= $this->_model->getApplication()->getModel(
                    $def
                )->getName();
                if ( !is_numeric($alias) ) {
                    $sql .= ' as ' . $alias;
                }
                $sql .= ', ';
            }
            $sql = rtrim($sql, ', ');
            // WHERE
            $where = '';
            foreach( $this->_request['w'] as $criteria ) {
                if ( is_string($criteria) ) {
                    $where .= ' ' . $criteria;
                } elseif ( !empty($criteria['f']) ) {
                    
                }
            }
            if ( !empty($where) ) {
                $sql .= ' WHERE ' . $where;
            }
            echo "\n------\n" . $sql . "\n-------\n";
            /*if ( empty() ) {
                
            }*/
            // @todo
            return $sql;
        }

    }
}