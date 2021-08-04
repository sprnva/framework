<?php

namespace App\Core\Database\Eloquent;

use App\Core\Database\Connection;
use App\Core\App;
use App\Core\Database\Eloquent\Model;
use Exception;
use PDO;

class Builder
{
    /**
     * The base query builder instance.
     *
     */
    protected $query;

    protected $table;

    protected $nameSpace;

    protected $modelTableName;

    protected $modelClass;

    protected $result;

    protected $relationships = [];

    protected $defaultPrimaryKey = 'id';

    public $listen = [];

    /**
     * additional where params for with() method
     *
     */
    protected $whereWith;
    protected $withColumns;

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The table which the query is targeting.
     *
     * @var string
     */
    protected $from;

    protected $whereClause;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    protected $limit;

    /**
     * The query union statements.
     *
     * @var array
     */
    protected $unions;

    /**
     * Create a new Eloquent query builder instance.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return void
     */
    public function __construct($modelClass, $nameSpace, $hasRelations)
    {
        $this->pdo = Connection::make(App::get('config')['database']);
        $this->nameSpace = $nameSpace;
        $this->modelClass = "App\\Models\\{$modelClass}";
        $this->relationships = (!empty($hasRelations))
            ? $hasRelations
            : $this->relationships;
    }

    public function bootModel($modelClass)
    {
        $table = $this->getModelTable($modelClass);

        return $this->from($table)
            ->result()
            ->getData()
            ->ezLoadRelations();
    }

    public function ezLoadRelations()
    {
        if (!empty($this->relationships)) {

            $relations = [];
            foreach ($this->relationships as $relation => $keys) {

                $relationTable = $this->getModelTable($relation);

                $relations[$relationTable] =  [

                    (empty($keys['foreignKey']))
                        ? $this->getForeignKey($relation)
                        : $keys['foreignKey'],

                    (empty($keys['localKey']))
                        ? $this->primaryKey($relation)
                        : $keys['localKey']
                ];
            }

            return $this->with($relations)->result;
        }

        return $this->result;
    }

    public function getData()
    {
        try {
            $statement = $this->pdo->prepare($this->query);
            $statement->execute();

            $this->listen[] = $this->query;

            $this->result = $statement->fetchAll(PDO::FETCH_CLASS, "{$this->nameSpace}");
            return $this;
        } catch (Exception $e) {
            throwException("Whoops! error occurred.", $e);
        }
    }

    public function select()
    {
        $columns = [];
        foreach (func_get_args() as $column) {
            $columns = $column;
        }

        $this->columns =  $columns;
        return $this;
    }

    public function where($wheres)
    {
        $probedWheres = '';
        if (is_array($wheres)) {
            $countr = 1;
            foreach ($wheres as $where) {

                $ands = ($countr < count($wheres)) ? ' AND ' : '';

                $probedWheres .= $where . $ands;

                $countr++;
            }
        } else {
            $probedWheres = $wheres;
        }

        $this->whereClause = $probedWheres;
        return $this;
    }

    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    public function result()
    {
        $query[] = "SELECT";
        // if the selectables array is empty, select all
        if (empty($this->columns)) {
            $query[] = "*";
        }
        // else select according to selectables
        else {
            $query[] = join(', ', $this->columns);
        }

        $query[] = "FROM";
        $query[] = $this->table;

        if (!empty($this->whereClause)) {
            $query[] = "WHERE";
            $query[] = $this->whereClause;
        }

        // if (!empty($this->limit)) {
        //     $query[] = "LIMIT";
        //     $query[] = $this->limit;
        // }

        $this->query = join(' ', $query);
        return $this;
    }

    public function all()
    {
        return $this->get();
    }

    public function get()
    {
        return $this->bootModel($this->modelClass);
    }

    public function getModelTable($modelClass)
    {
        $model = $this->model($modelClass);

        return (!empty($model->table))
            ? $model->table
            : strtolower(end(explode("\\", $modelClass))) . "s";
    }

    public function primaryKey($modelClass)
    {
        $model = $this->model($modelClass);

        return (!empty($model->primaryKey))
            ? $model->primaryKey
            : $this->defaultPrimaryKey;
    }

    public function getForeignKey($modelClass)
    {
        $model = $this->model($modelClass);

        return (!empty($model->foreignKey))
            ? $model->foreignKey
            : $this->getDefaultForeignKey($modelClass);
    }

    public function getDefaultForeignKey($modelClass)
    {
        return $this->getModelTable($modelClass) . "_id";
    }

    public function model($modelClass)
    {
        $classModelName = end(explode("\\", $modelClass));
        $model = "App\\Models\\{$classModelName}";
        return new $model;
    }

    /**
     * this will solve n+1 problem
     * will get the data of the foreign id in the current table
     * 
     */
    public function with($params = [])
    {
        $currentTableDatas = $this->result;

        $collectedIdFrom = [];
        foreach ($params as $relationTable => $param) {
            $id = $param[0];
            $foreignIdFromCurrentTable = [];
            foreach ($currentTableDatas as $key => $currentTableData) {
                if (is_object($currentTableData)) {
                    $foreignIdFromCurrentTable[] = $currentTableData->$id;
                } else {
                    $foreignIdFromCurrentTable[] = $currentTableDatas->$id;
                }
            }
            $collectedIdFrom[$relationTable] = $foreignIdFromCurrentTable;
        }

        $relationDatas = [];
        foreach ($params as $relationTable => $primaryColumn) {
            $relationPrimaryColumn = $primaryColumn[1];
            $implodedIds = implode("','", array_unique($collectedIdFrom[$relationTable]));

            $andFilter = (!empty($this->whereWith[$relationTable])) ? $this->whereWith[$relationTable] . " AND " : '';

            $cols = (!empty($this->withColumns[$relationTable])) ? $this->withColumns[$relationTable] : '*';

            $this->listen[] = "SELECT {$cols} FROM `{$relationTable}` WHERE {$andFilter} `$relationPrimaryColumn` IN('$implodedIds')";

            $statement = $this->pdo->prepare("SELECT {$cols} FROM `{$relationTable}` WHERE {$andFilter} `$relationPrimaryColumn` IN('$implodedIds')");
            $statement->execute();

            $relationDatas[$relationTable] = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        $newResultSet = [];
        foreach ($currentTableDatas as $currentTableData) {
            foreach ($params as $relationTable => $primaryColumn) {
                $f_id = $primaryColumn[0];
                $p_id = $primaryColumn[1];
                $datas = [];
                $countRealtionDatas = count($relationDatas[$relationTable]);
                foreach ($relationDatas[$relationTable] as $key => $relationData) {
                    if (is_object($currentTableData)) {
                        if ($currentTableData->$f_id == $relationData[$p_id]) {
                            if ($countRealtionDatas > 1) {
                                $currentTableData->$relationTable[] = (object)$relationData;
                            } else {
                                $currentTableData->$relationTable = (object)$relationData;
                            }
                        }
                    } else {
                        if ($currentTableDatas[$primaryColumn[0]] == $relationData[$primaryColumn[1]]) {
                            $currentTableDatas[$relationTable][] = $relationData;
                        }
                    }
                }
            }

            if (is_object($currentTableData)) {
                $newResultSet[] = $currentTableData;
            } else {
                $newResultSet = $currentTableDatas;
            }
        }

        $this->result = $newResultSet;
        return $this;
    }

    /**
     * add extra where params to the with() method
     * 
     */
    public function whereWith($andFilter = [])
    {
        $this->whereWith = $andFilter;
        return $this;
    }

    /**
     * select a column/s to the with() method
     * 
     */
    public function selectWith($cols = [])
    {
        $this->withColumns =  $cols;
        return $this;
    }

    /**
     * insert record to a database table.
     *
     * @param string $table_name
     * @param array $form_data
     * @param string $last_id
     */
    public function insert($form_data, $last_id = 'N')
    {
        $table_name = $this->getModelTable($this->modelClass);
        $fields = array_keys($form_data);

        $sql = "INSERT INTO " . $table_name . "(`" . implode('`,`', $fields) . "`) VALUES ('" . implode("','", $form_data) . "')";

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute();

            $this->listen[] = "INSERT INTO " . $table_name . "(`" . implode('`,`', $fields) . "`) VALUES ('" . implode("','", $form_data) . "')";
            $lastID = $this->pdo->lastInsertId();
            if ($last_id == 'Y') {
                if ($statement) {
                    return $lastID;
                } else {
                    return 0;
                }
            } else {
                if ($statement) {
                    return 1;
                } else {
                    return 0;
                }
            }
        } catch (Exception $e) {
            throwException("Whoops! error occurred.", $e);
        }
    }

    /**
     * update a record from a database table.
     *
     * @param string $table_name
     * @param array $form_data
     * @param string $where_clause
     */
    public function update($form_data, $where_clause = '')
    {
        $table_name = $this->getModelTable($this->modelClass);

        $whereSQL = '';
        if (!empty($where_clause)) {
            if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {
                $whereSQL = " WHERE " . $where_clause;
            } else {
                $whereSQL = " " . trim($where_clause);
            }
        }
        $sql = "UPDATE " . $table_name . " SET ";
        $sets = array();
        foreach ($form_data as $column => $value) {
            $sets[] = "`" . $column . "` = '" . $value . "'";
        }
        $sql .= implode(', ', $sets);
        $sql .= $whereSQL;

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute();

            $this->listen[] = $sql;

            if ($statement) {
                return 1;
            } else {
                return 0;
            }
        } catch (Exception $e) {
            throwException("Whoops! error occurred.", $e);
        }
    }

    /**
     * delete a record from a database table.
     *
     * @param string $table_name
     * @param string $where_clause
     */
    public function delete($where_clause = '')
    {
        $table_name = $this->getModelTable($this->modelClass);

        $whereSQL = '';
        if (!empty($where_clause)) {
            if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {
                $whereSQL = " WHERE " . $where_clause;
            } else {
                $whereSQL = " " . trim($where_clause);
            }
        }

        $sql = "DELETE FROM " . $table_name . $whereSQL;

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute();

            $this->listen[] = $sql;

            if ($statement) {
                return 1;
            } else {
                return 0;
            }
        } catch (Exception $e) {
            throwException("Whoops! error occurred.", $e);
        }
    }

    /**
     * Listens for all the database queries
     * 
     */
    public function listen()
    {
        return $this->listen;
    }
}
